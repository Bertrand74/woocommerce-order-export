<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Order_Export_Pro_Ajax extends WC_Order_Export_Ajax {
	use WC_Order_Export_Pro_Admin_Tab_Abstract_Ajax_Jobs;

	protected function get_settings_from_bulk_request() {
		$settings = parent::get_settings_from_bulk_request();

		if ( ! empty( $_REQUEST['export_bulk_profile'] ) && $_REQUEST['export_bulk_profile'] !== 'now' ) {
			$settings = WC_Order_Export_Pro_Manage::get( WC_Order_Export_Pro_Manage::EXPORT_PROFILE, $_REQUEST['export_bulk_profile'] );
		}

		return $settings;
	}

}