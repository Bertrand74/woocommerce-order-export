<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * Class WC_Order_Export_Ajax
 *
 * Class for handle ajax requests which not require tab name to execute
 *
 */
class WC_Order_Export_Ajax {
	use WC_Order_Export_Ajax_Helpers;
	
	public function ajax_run_one_job() {

		if ( ! empty( $_REQUEST['profile'] ) AND $_REQUEST['profile'] == 'now'  ) {
			$settings = WC_Order_Export_Manage::get( WC_Order_Export_Manage::EXPORT_NOW );
		} else {
			_e( 'Profile required!', 'woocommerce-order-export' );
		}

		$filename = WC_Order_Export_Engine::build_file_full( $settings );
		WC_Order_Export_Manage::set_correct_file_ext( $settings );

		$this->send_headers( $settings['format'], WC_Order_Export_Engine::make_filename( $settings['export_filename'] ) );
		$this->send_contents_delete_file( $filename );
	}
	

	public function ajax_export_download_bulk_file() {

	    $settings = array_merge($this->get_settings_from_bulk_request(), WC_Order_Export_Manage::get_defaults_filters());

	    $this->build_and_send_file( $settings );
	}

	protected function get_settings_from_bulk_request() {
		$settings = false;
		if ( ! empty( $_REQUEST['export_bulk_profile'] ) && $_REQUEST['export_bulk_profile'] == 'now' ) {
			$settings = WC_Order_Export_Manage::get( WC_Order_Export_Manage::EXPORT_NOW );
		}

		return $settings;
	}
}