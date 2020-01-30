<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

trait WC_Order_Export_Pro_Admin_Tab_Abstract_Ajax_Jobs {
	public function ajax_run_one_job() {

		if ( ! empty( $_REQUEST['schedule'] ) ) {
			$settings = WC_Order_Export_Pro_Manage::get( WC_Order_Export_Pro_Manage::EXPORT_SCHEDULE, $_REQUEST['schedule'] );
		} elseif ( ! empty( $_REQUEST['profile'] ) ) {
			if ( $_REQUEST['profile'] == 'now' ) {
				$settings = WC_Order_Export_Pro_Manage::get( WC_Order_Export_Pro_Manage::EXPORT_NOW );
			} else {
				$settings = WC_Order_Export_Pro_Manage::get( WC_Order_Export_Pro_Manage::EXPORT_PROFILE, $_REQUEST['profile'] );
			}
		} else {
			_e( 'Schedule or profile required!', 'woocommerce-order-export' );
		}

		$woe_order_post_type = isset($settings['post_type']) ? $settings['post_type'] : 'shop_order';

		WC_Order_Export_Pro_Admin::set_order_post_type($woe_order_post_type);

		$filename = WC_Order_Export_Pro_Engine::build_file_full( $settings );
		WC_Order_Export_Pro_Manage::set_correct_file_ext( $settings );

		$this->send_headers( $settings['format'], WC_Order_Export_Pro_Engine::make_filename( $settings['export_filename'] ) );
		$this->send_contents_delete_file( $filename );
	}

	public function ajax_run_one_scheduled_job() {
		WC_Order_Export_Cron::run_one_scheduled_job();
	}

	public function ajax_run_cron_jobs() {
		WC_Order_Export_Cron::wc_export_cron_global_f();
	}

}