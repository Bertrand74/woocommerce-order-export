<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Order_Export_Pro_Admin_Tab_License extends WC_Order_Export_Admin_Tab_Abstract {
	use WC_Order_Export_Pro_Admin_Tab_Abstract;

	const KEY = 'license';

	public function __construct() {
		$this->title = __( 'License', 'woocommerce-order-export' );
	}

	public function render() {
		$this->render_template( 'tab/license', array(), $this->get_template_path() );
	}

}