<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Order_Export_Admin_Tab_Schedule_Jobs extends WC_Order_Export_Admin_Tab_Abstract {
	const KEY = 'schedules';

	public function __construct() {
		$this->title = __( 'Scheduled jobs', 'woocommerce-order-export' );
	}

	public function render() {
		$this->render_template( 'tab/schedules' );
	}

}