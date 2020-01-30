<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Order_Export_Pro_Engine extends WC_Order_Export_Engine {

	public static function export( $settings, $filepath, $is_last_order = true ) {
		if ( empty( $settings['destination']['type'] ) ) {
			return __( "No destination selected", 'woocommerce-order-export' );
		}

		if ( ! is_array( $settings['destination']['type'] ) ) {
			$settings['destination']['type'] = array( $settings['destination']['type'] );
		}
		$results = array();
		foreach ( $settings['destination']['type'] as $export_type ) {
			$export_type = strtolower( $export_type );
			if ( ! in_array( strtoupper( $export_type ), WC_Order_Export_Admin::$export_types ) ) {
				$results[] = array(
				    'status' => false,
				    'text'   => __( "Wrong format", 'woocommerce-order-export' ),
				);
				return $results;
			}

			include_once WOE_PRO_PLUGIN_BASEPATH . "/classes/exports/abstract-class-woe-export.php";
			include_once WOE_PRO_PLUGIN_BASEPATH . "/classes/exports/class-woe-export-{$export_type}.php";

			$class    = 'WOE_Export_' . $export_type;
			$exporter = new $class( $settings['destination'] );

			$filename      = self::make_filename( $settings['export_filename'] );
			$custom_export = apply_filters( 'woe_custom_export_to_' . $export_type, false, $filename, $filepath,
				$exporter );
			if ( ! $custom_export ) {
				// try many times?
				$num_retries = 0;
				$tmp_results = array();
				while ( $num_retries < $exporter->get_num_of_retries() ) {
					$num_retries ++;
					$output = $exporter->run_export( $filename, $filepath );
					$tmp_results[] = array(
					    'status' => $exporter->finished_successfully,
					    'text'   => $output,
					);
					if ( $exporter->finished_successfully ) {
						break;
					}
				}

				if ($exporter->finished_successfully) {
				    $results[] = array_pop($tmp_results);
				} else {
				    $results = array_merge($results, $tmp_results);
				}

				do_action( "woe_export_destination_finished", $exporter->finished_successfully, $export_type, $filename,
					$filepath, $settings, $exporter );
			} else {
				$results[] = array(
				    'status' => true,
				    'text'   => $custom_export,
				);
			}
		}

		return $results;
	}

	/* Zapier will pull files! */
	public static function prepare( $settings, $filepath ) {
		if ( empty( $settings['destination']['type'] ) ) {
			return __( "No destination selected", 'woocommerce-order-export' );
		}

		if ( ! is_array( $settings['destination']['type'] ) ) {
			$settings['destination']['type'] = array( $settings['destination']['type'] );
		}
		$results = array();
		foreach ( $settings['destination']['type'] as $export_type ) {
			$export_type = strtolower( $export_type );
			if ( ! in_array( strtoupper( $export_type ), WC_Order_Export_Admin::$export_types ) ) {
				return __( "Wrong export type", 'woocommerce-order-export' );
			}

			include_once dirname( dirname( __FILE__ ) ) . "/exports/abstract-class-woe-export.php";
			include_once dirname( dirname( __FILE__ ) ) . "/exports/class-woe-export-{$export_type}.php";
			$class    = 'WOE_Export_' . $export_type;
			$exporter = new $class( $settings['destination'] );

			$filename       = self::make_filename( $settings['export_filename'] );
			$custom_prepare = apply_filters( 'woe_custom_prepare_to_' . $export_type, false, $filename, $filepath,
				$exporter );
			if ( ! $custom_prepare ) {
				if ( method_exists( $exporter, 'prepare' ) ) {
					$results[] = $exporter->prepare( $filename, $filepath );
				}
			} else {
				$results[] = $custom_prepare;
			}
		}

		return $results;
	}

	public static function build_separate_files_and_export(
		$settings,
		$filename = '',
		$limit = 0,
		$order_ids = array()
	) {
		global $wpdb;

		self::kill_buffers();
		$settings                     = self::validate_defaults( $settings );
		self::$current_job_settings   = $settings;
		self::$current_job_build_mode = 'full';
		self::$date_format            = trim( $settings['date_format'] . ' ' . $settings['time_format'] );
		self::$extractor_options      = self::_install_options( $settings );

		$filename = self::get_filename($settings['format'], $filename);

//		$format = strtolower( $settings['format'] );

		//get IDs
		$sql = WC_Order_Export_Data_Extractor::sql_get_order_ids( $settings );
		$sql .= apply_filters( "woe_sql_get_order_ids_order_by",
			" ORDER BY " . $settings['sort'] . " " . $settings['sort_direction'] );

		if ( $limit ) {
			$sql .= " LIMIT " . intval( $limit );
		}

		if ( ! $order_ids ) {
			$order_ids = apply_filters( "woe_get_order_ids", $wpdb->get_col( $sql ) );
		}

		if ( empty( $order_ids ) ) {
			return false;
		}
		
		// check it once
		self::_check_products_and_coupons_fields( $settings, $export );

		// make header moved to plain formatter

		$result = array();

		WC_Order_Export_Data_Extractor::prepare_for_export();
		self::$make_separate_orders = true;

		$last_order_id = end($order_ids);

		foreach ( $order_ids as $order_id ) {
			$order_id = apply_filters( "woe_order_export_started", $order_id );
			if ( ! $order_id ) {
				continue;
			}
			self::$order_id = $order_id;
			$formater       = self::init_formater( '', $settings, $filename, $labels, $static_vals, 0 );
			
			// prepare for XLS/CSV moved to plain formatter
			$formater->adjust_duplicated_fields_settings( array($order_id) );

			$formater->truncate();
			$formater->start();
			$row = WC_Order_Export_Data_Extractor::fetch_order_data( $order_id, $labels,
				$export, $static_vals, self::$extractor_options );
			$row = apply_filters( "woe_fetch_order_row", $row, $order_id );

			if ( $row ) {
				$formater->output( $row );
				do_action( "woe_order_row_exported", $row, $order_id );
			}
			do_action( "woe_order_exported", $order_id );
			self::$orders_exported = 1;
			self::try_modify_status( $order_id, $settings );
			self::try_mark_order( $order_id, $settings );
			$formater->finish();

			if ( $filename !== false ) {
				$result = self::export( $settings, $filename, $last_order_id === $order_id );
				//if ($result) {
				//	return $result;
				//}
			}
			self::$order_id = '';
		}

		do_action( 'woe_export_finished' );

		return $result; //return last result
	}


	public static function build_files_and_export( $settings, $filename = '', $limit = 0, $order_ids = array() ) {
		if ( ! empty( $settings['destination']['separate_files'] ) ) {
			$result = self::build_separate_files_and_export( $settings, $filename, $limit, $order_ids );
		} else {
			$file = self::build_file_full( $settings, $filename, $limit, $order_ids );
			if ( $file !== false ) {

				$result = self::export( $settings, $file );

				if ( file_exists( $file ) ) {
					unlink( $file );
				}

			} else {
				$result = array();
			}
		}

		if ( ! $result ) {
		    $result[] = array(
			'status' => false,
			'text'   => __( 'Nothing to export. Please, adjust your filters', 'woocommerce-order-export' )
		    );
		}

		return $result;
	}

	public static function build_files_and_prepare( $settings, $filename = '', $limit = 0, $order_ids = array() ) {
		$file = self::build_file_full( $settings, $filename, $limit, $order_ids );
		if ( $file !== false ) {
			$result = self::prepare( $settings, $file );

			return $result;
		} else {
			return __( 'Nothing to export. Please, adjust your filters', 'woocommerce-order-export' );
		}
	}
}