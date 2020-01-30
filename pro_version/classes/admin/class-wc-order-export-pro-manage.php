<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Order_Export_Pro_Manage extends WC_Order_Export_Manage {

	static function clone_export_settings( $mode, $id ) {
		return self::advanced_clone_export_settings( $id, $mode, $mode );
	}

	static function advanced_clone_export_settings(
		$id,
		$mode_in = self::EXPORT_SCHEDULE,
		$mode_out = self::EXPORT_SCHEDULE
	) {
		$all_jobs_in = self::get_export_settings_collection( $mode_in );
		//new settings
		$settings         = $all_jobs_in[ $id ];
		$settings['mode'] = $mode_out;

		if ( $mode_in !== $mode_out ) {
			$all_jobs_out = self::get_export_settings_collection( $mode_out );
		} else {
			$mode_out          = $mode_in;
			$all_jobs_out      = $all_jobs_in;
			$settings['title'] .= " [cloned]"; //add note
		}

		if ( $mode_in === self::EXPORT_PROFILE && $mode_out === self::EXPORT_SCHEDULE ) {
			if ( ! isset( $settings['destination'] ) ) {
				$settings['destination'] = array(
					'type' => 'folder',
					'path' => get_home_path(),
				);
			}

			if ( ! isset( $settings['export_rule'] ) ) {
				$settings['export_rule'] = 'last_run';
			}

			if ( ! isset( $settings['export_rule_field'] ) ) {
				$settings['export_rule_field'] = 'modified';
			}

			if ( ! isset( $settings['schedule'] ) ) {
				$settings['schedule'] = array(
					'type'   => 'schedule-1',
					'run_at' => '00:00',
				);
			}

			unset( $settings['use_as_bulk'] );
		}

		$next_id                  = max(array_keys( $all_jobs_out )) + 1;
		$all_jobs_out[ $next_id ] = $settings;

		self::save_export_settings_collection( $mode_out, $all_jobs_out );

		return $next_id;
	}

}
