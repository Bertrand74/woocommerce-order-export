<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Order_Export_Pro_Admin_Tab_Settings extends WC_Order_Export_Admin_Tab_Abstract {
	use WC_Order_Export_Pro_Admin_Tab_Abstract;

	const KEY = 'settings';

	public function __construct() {
		$this->title = __( 'Settings', 'woocommerce-order-export' );
	}

	public function render() {

		$settings = WC_Order_Export_Main_Settings::get_settings();

		$this->render_template( 'tab/settings', array(
			'ajaxurl'  => admin_url( 'admin-ajax.php' ),
			'settings' => $settings,
		), $this->get_template_path() );
	}

	public function ajax_save_settings() {

		$settings = filter_input_array( INPUT_POST, array(
			'default_tab'                          => FILTER_SANITIZE_STRING,
			'cron_tasks_active'                    => FILTER_VALIDATE_BOOLEAN,
			'show_export_status_column'            => FILTER_VALIDATE_BOOLEAN,
			'show_export_actions_in_bulk'          => FILTER_VALIDATE_BOOLEAN,
			'show_export_in_status_change_job'     => FILTER_VALIDATE_BOOLEAN,
			'show_date_time_picker_for_date_range' => FILTER_VALIDATE_BOOLEAN,
			'display_html_report_in_browser'       => FILTER_VALIDATE_BOOLEAN,
			'default_date_range_for_export_now'    => FILTER_SANITIZE_STRING,
			'default_html_css'		       => FILTER_SANITIZE_STRING,
			'autocomplete_products_max'            => FILTER_VALIDATE_INT,
			'ajax_orders_per_step'                 => FILTER_VALIDATE_INT,
			'limit_button_test'                    => FILTER_SANITIZE_STRING,
			'cron_key'                             => FILTER_SANITIZE_STRING,
			'ipn_url'                              => FILTER_SANITIZE_STRING,
			'zapier_api_key'                       => FILTER_SANITIZE_STRING,
			'zapier_file_timeout'                  => FILTER_SANITIZE_NUMBER_INT,
			'notify_failed_jobs'		       => FILTER_VALIDATE_BOOLEAN,
			'notify_failed_jobs_email_subject'     => FILTER_SANITIZE_STRING,
			'notify_failed_jobs_email_recipients'  => FILTER_SANITIZE_STRING,
		) );

		WC_Order_Export_Pro_Main_Settings::save_settings( $settings );
	}

}