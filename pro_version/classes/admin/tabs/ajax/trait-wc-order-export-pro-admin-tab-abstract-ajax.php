<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

trait WC_Order_Export_Pro_Admin_Tab_Abstract_Ajax {
	use WC_Order_Export_Pro_Admin_Tab_Abstract_Ajax_Jobs;

	public function ajax_test_destination() {

		$settings = WC_Order_Export_Pro_Manage::make_new_settings( $_POST );

		unset( $settings['destination']['type'] );

		$settings['destination']['type'][] = $_POST['destination'];

		// use unsaved settings
		do_action( 'woe_start_test_job', $_POST['id'], $settings );

		if ( isset( $settings['change_order_status_to'] ) ) {
		    unset( $settings['change_order_status_to'] );
		}

		$main_settings = WC_Order_Export_Main_Settings::get_settings();

		$result = WC_Order_Export_Pro_Engine::build_files_and_export( $settings, '', $main_settings['limit_button_test'] );

		echo implode("\n\r", array_map(function ($v) { return $v['text']; }, $result));
	}

	public function ajax_reorder_jobs() {

		if ( ! empty( $_REQUEST['new_jobs_order'] ) AND ! empty( $_REQUEST['tab_name'] ) ) {

			if ( $_REQUEST['tab_name'] == 'schedule' ) {
				$mode = WC_Order_Export_Pro_Manage::EXPORT_SCHEDULE;
			} elseif ( $_REQUEST['tab_name'] == 'profile' ) {
				$mode = WC_Order_Export_Pro_Manage::EXPORT_PROFILE;
			} elseif ( $_REQUEST['tab_name'] == 'order_action' ) {
				$mode = WC_Order_Export_Pro_Manage::EXPORT_ORDER_ACTION;
			} else {
				echo json_encode( array( 'result' => false ) );
				die();
			}

			//skip zero ids
			foreach ( array_filter( $_REQUEST['new_jobs_order'] ) as $index => $job_id ) {
				$job             = WC_Order_Export_Pro_Manage::get( $mode, $job_id );
				$job['priority'] = $index + 1;
				WC_Order_Export_Pro_Manage::save_export_settings( $mode, $job_id, $job );
			}
			echo json_encode( array( 'result' => true ) );
		} else {
			echo json_encode( array( 'result' => false ) );
		}
	}

}