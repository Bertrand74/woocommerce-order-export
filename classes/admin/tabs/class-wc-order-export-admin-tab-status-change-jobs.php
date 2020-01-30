<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Order_Export_Admin_Tab_Status_Change_Jobs extends WC_Order_Export_Admin_Tab_Abstract {
	const KEY = 'order_actions';

	public function __construct() {
		$this->title = __( 'Status change jobs', 'woocommerce-order-export' );
	}

	public function render() {
		$this->render_template( 'tab/order-actions' );
	}

}