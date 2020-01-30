<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Order_Export_Cron {
	const cron_process_option = 'woocommerce-order-export-cron-do';

	public static function try_install_job( $force_delete = false ) {
		if ( $force_delete ) {
			wp_clear_scheduled_hook( "wc_export_cron_global" );
		}

		if ( ! wp_get_schedule( 'wc_export_cron_global' ) ) {
			$all_jobs = WC_Order_Export_Pro_Manage::get_export_settings_collection( WC_Order_Export_Pro_Manage::EXPORT_SCHEDULE );

			//try to get next run time
			$next_runs = array();
			foreach ( $all_jobs as $job ) {
				$active = ( ! isset( $job['active'] ) || $job['active'] );
				if ( ! $active ) {
					continue;
				}
				if ( ! empty( $job['schedule']['next_run'] ) ) {
					$next_runs[] = $job['schedule']['next_run'];
				}
			}
			//have active jobs?
			if ( ! empty( $next_runs ) ) {
				$diff_utc = current_time( "timestamp" ) - current_time( "timestamp", 1 );
				$next_run = min( $next_runs ) - $diff_utc;
				wp_schedule_single_event( $next_run, 'wc_export_cron_global' );
			}
		}
	}

	//force scheduled job!
	public static function run_one_scheduled_job() {
		$job_id = @$_REQUEST['schedule'];
		if ( empty( $job_id ) ) {
			_e( 'Schedule id is required!', 'woocommerce-order-export' );
			die();
		}

		$items = WC_Order_Export_Pro_Manage::get_export_settings_collection( WC_Order_Export_Pro_Manage::EXPORT_SCHEDULE );
		if ( ! isset( $items[ $job_id ] ) ) {
			_e( 'Wrong schedule id!', 'woocommerce-order-export' );
			die();
		}

		$item   = apply_filters( 'woe_adjust_cron_job_settings_before_run', $items[ $job_id ] );
		$active = ( ! isset( $item['active'] ) || $item['active'] );
		if ( ! $active ) {
			_e( 'Job is inactive!', 'woocommerce-order-export' );
			die();
		}

		$woe_order_post_type = isset($item['post_type']) ? $item['post_type'] : 'shop_order';

		WC_Order_Export_Pro_Admin::set_order_post_type($woe_order_post_type);

		// do cron job
		$result = WC_Order_Export_Pro_Engine::build_files_and_export( $item );

		// write last_run time
		$item['schedule']['last_run'] = current_time( "timestamp", 0 );

		if ( WC_Order_Export_Pro_Engine::$orders_exported ) {
			$item['schedule']['last_report_sent'] = current_time( "timestamp", 0 );
		}
		//save back
		$items[ $job_id ] = $item;
		WC_Order_Export_Pro_Manage::save_export_settings_collection( WC_Order_Export_Pro_Manage::EXPORT_SCHEDULE, $items );

		$output = sprintf( __( 'Scheduled job #%s. Result: %s', 'woocommerce-order-export' ), $job_id, implode("<br>\n\r", array_map(function ($v) { return $v['text']; }, $result)) );
		echo $output . "<br>\n";
	}

	public static function create_custom_schedules( $schedules ) {
		$schedules['wc_export_5min_global'] = array(
			'interval' => 300,
			'display'  => __( '[exporter] Every 5 Minutes', 'woocommerce-order-export' ),
		);
		$schedules['wc_export_1min_global'] = array(
			'interval' => 60,
			'display'  => __( '[exporter] Every 1 Minute', 'woocommerce-order-export' ),
		);

		return $schedules;
	}

	public static function wc_export_cron_global_f() {
		global $wp_filter;
		$main_settings = WC_Order_Export_Main_Settings::get_settings();

		if ( ! $main_settings['cron_tasks_active'] ) {
			_e( 'Scheduled jobs are inactive!', 'woocommerce-order-export' );

			return;
		}
		$export_now = get_transient( self::cron_process_option );
		if ( $export_now ) {
			_e( 'Job is still running!', 'woocommerce-order-export' );

			return;
		} else {
			set_transient( self::cron_process_option, 1, 60 );
		}
		$time = current_time( "timestamp", 0 );

		set_time_limit( 0 );
		do_action( 'woe_start_cron_jobs' );

		$logger         = function_exists( "wc_get_logger" ) ? wc_get_logger() : false; //new logger in 3.0+
		$logger_context = array( 'source' => 'woocommerce-order-export' );

		$failed_jobs = array();

		$items = WC_Order_Export_Pro_Manage::get_export_settings_collection( WC_Order_Export_Pro_Manage::EXPORT_SCHEDULE );
		foreach ( $items as $job_id => $item ) {
			$filters = $wp_filter;

			$item = WC_Order_Export_Pro_Manage::get( WC_Order_Export_Pro_Manage::EXPORT_SCHEDULE, $job_id );
			if ( isset( $item['active'] ) && ! $item['active'] ) {
				continue;
			}
			if ( ! isset( $item['mode'] ) ) {
				$item['mode'] = WC_Order_Export_Pro_Manage::EXPORT_SCHEDULE;
			}

			$woe_order_post_type = isset($item['post_type']) ? $item['post_type'] : 'shop_order';

			WC_Order_Export_Pro_Admin::set_order_post_type($woe_order_post_type);

			do_action( 'woe_start_cron_job', $job_id, $item );
			$item = apply_filters( 'woe_adjust_cron_job_settings', $item );

			if ( ! empty( $item['schedule']['next_run'] ) && $item['schedule']['next_run'] <= $time ) {
				//if ( true) {
				$item = apply_filters( 'woe_adjust_cron_job_settings_before_run', $item );
				// do cron job
				$result = WC_Order_Export_Pro_Engine::build_files_and_export( $item );
				$output = sprintf( __( 'Scheduled job #%s "%s". Result: %s', 'woocommerce-order-export' ), $job_id,
					$item['title'], implode("<br>\n\r", array_map(function ($v) { return $v['text']; }, $result)) );
				echo $output . "<br>\n";
				// log if required
				if ( $logger AND ! empty( $item['log_results'] ) ) {
					$logger->info( $output, $logger_context );
				}

				$failed_messages = array();

				foreach ($result as $message) {
				    if (!$message['status']) {
					$failed_messages[] = $message['text'];
				    }
				}

				if ( $failed_messages ) {
				    $failed_jobs[] = array('title' => $item['title'], 'messages' => $failed_messages);
				}

				if ( WC_Order_Export_Pro_Engine::$orders_exported ) {
					$item['schedule']['last_report_sent'] = $time;
				}
				$item['schedule']['last_run'] = $time;
				$item['schedule']['next_run'] = self::next_event_timestamp_for_schedule( $item['schedule'], $job_id );
				//save back
				$items[ $job_id ] = $item;
				WC_Order_Export_Pro_Manage::save_export_settings_collection( WC_Order_Export_Pro_Manage::EXPORT_SCHEDULE,
					$items );
			}
			$wp_filter = $filters;
		}

		self::try_install_job( true ); // reset scheduled job
		delete_transient( self::cron_process_option );
		_e( 'All jobs completed', 'woocommerce-order-export' );

		self::email_failed_jobs($failed_jobs, $main_settings);
	}

	public static function next_event_timestamp_for_schedule( $schedule, $job_id = 0 ) {
		$schedule = apply_filters( "woe_modify_job_schedule", $schedule, $job_id );
		if ( $schedule['type'] == 'schedule-1' ) {
			if ( ! isset( $schedule['weekday'] ) ) // nothing selected!
			{
				$schedule['weekday'] = array();
			}

			return self::next_event_for_schedule_weekday( array_keys( $schedule['weekday'] ), $schedule['run_at'],
				true );
		} else if ( $schedule['type'] == 'schedule-2' ) {
			return self::next_event_for_schedule2( $schedule['interval'], $schedule['custom_interval'], true,
				$schedule['last_run'] );
		} else if ( $schedule['type'] == 'schedule-3' ) {
			return self::next_event_for_schedule3( $schedule['times'] );
		} else if ( $schedule['type'] == 'schedule-4' ) {
			return self::next_event_for_schedule4( $schedule['date_times'] );
		} else if ( $schedule['type'] == 'schedule-5' ) {
			return self::next_event_for_schedule5( $schedule['crontab'] );
		}
	}

	public static function next_event_for_schedule_weekday( $weekdays, $runat, $timestamp = false ) {
		$now      = current_time( "timestamp" );
		$diff_utc = current_time( "timestamp" ) - current_time( "timestamp", 1 );
		$times    = array();
		for ( $index = 0; $index <= 7; $index ++ ) {
			if ( in_array( date( "D", strtotime( "+{$index} day", $now ) ), $weekdays ) ) {
				$time = strtotime( date( "M j Y", strtotime( "+{$index} day", $now ) ) . " " . $runat );
				// some dates can be disabled by filter
				if ( $time >= $now AND apply_filters( 'woe_job_date_allowed', true, $time ) ) {
					$times[] = $time;
				}
			}
		}
		$time = $times ? min( $times ) : 0;

		if ( $timestamp ) {
			return $time;
		} else {
			return date( "D M j Y", $time ) . " at " . $runat;
		}
	}

	public static function next_event_for_schedule2( $interval, $custom_interval, $timestamp = false, $now = null ) {
		$now = empty( $now ) ? current_time( "timestamp", 0 ) : $now;
		if ( $interval == 'first_day_month' ) {
			$next_month  = strtotime( " +1 month", $now );
			$month_start = date( 'Y-m-01', $next_month );
			$time        = strtotime( $month_start );
		} elseif ( $interval == 'first_day_quarter' ) {
			$next_quarter  = strtotime( "+3 month", $now );
			$quarter_start = date( 'Y-' . WC_Order_Export_Data_Extractor::get_quarter_month( $next_quarter ) . '-01',
				$next_quarter );
			$time          = strtotime( $quarter_start );
		} elseif ( $interval != 'custom' ) {
			$schedules = wp_get_schedules();
			foreach ( $schedules as $k => $v ) {
				if ( $interval == $k ) {
					if ( isset( $v['calc_method'] ) ) 
						$time  = call_user_func( $v['calc_method'], $v['interval'] );
					else
						$time = $v['interval'] + $now ;
					break;
				}
			}
		} else {
			$time = $custom_interval * 60 + $now;
		}

		if ( $timestamp ) {
			return $time;
		} else {
			return date( "M j Y", $time ) . ' at ' . date( "G:i", $time );
		}
	}

	public static function next_event_for_schedule3( $times ) {
		$now = current_time( "timestamp" );
		if ( empty( $times ) ) {
			return '';
		}

		$times      = explode( ',', $times );
		$next_times = array();

		foreach ( $times as $time ) {
			$timestamp = strtotime( $time, $now );
			if ( $timestamp <= $now ) {
				$timestamp = strtotime( 'next ' . $time, $now );
			}

			if ( ! apply_filters( 'woe_job_date_allowed', true, $timestamp ) ) // some dates can be disabled by filter
			{
				continue;
			}

			$next_times[] = $timestamp;
		}
		$next_time = min( $next_times );

		return $next_time;
	}

	public static function next_event_for_schedule4( $times ) {

		$now = current_time( "timestamp" );
		if ( empty( $times ) ) {
			return '';
		}

		$times      = explode( ',', $times );
		$next_times = array();

		foreach ( $times as $time ) {
			$timestamp = strtotime( $time, $now );
			if ( $timestamp <= $now ) {
				continue;
			}

			if ( ! apply_filters( 'woe_job_date_allowed', true, $timestamp ) ) // some dates can be disabled by filter
			{
				continue;
			}

			$next_times[] = $timestamp;
		}
		$next_time = min( $next_times );

		return $next_time;
	}

	public static function next_event_for_schedule5( $crontab ) {
//		$diff_utc = current_time( "timestamp" ) - current_time( "timestamp", 1 );
		//init composer
		include_once WOE_PRO_PLUGIN_BASEPATH . '/vendor/autoload.php';
		$cron = Cron\CronExpression::factory($crontab);

		return $cron->getNextRunDate( current_time( "mysql" ) )->format( 'U' );  //as unix timestamp , for website zone
	}

	public static function email_failed_jobs(array $failed_jobs, $main_settings) {

	    $to = $main_settings['notify_failed_jobs_email_recipients'];

	    if ( !$main_settings['notify_failed_jobs'] || !$failed_jobs || !$to ) {
		return;
	    }

	    $subject = $main_settings['notify_failed_jobs_email_subject'];

	    if ( !$subject ) {
		$subject = __( 'Scheduled jobs failed', 'woocommerce-order-export' );
	    }

	    $headers = array("Content-Type: text/plain");

	    $errors = array();

	    foreach ($failed_jobs as $job) {
		$errors[] = sprintf('%s: %s', $job['title'], implode(', ', $job['messages']));
	    }

	    $message = implode("\n\r", $errors);

	    wp_mail($to, $subject, $message, $headers);
	}

}