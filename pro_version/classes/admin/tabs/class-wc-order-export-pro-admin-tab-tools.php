<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Order_Export_Pro_Admin_Tab_Tools extends WC_Order_Export_Admin_Tab_Tools {
	public function __construct() {

		parent::__construct();

		add_filter( 'woe_tools_page_get_type_labels', array( $this, 'get_tools_page_type_labels' ), 10, 1 );
	}

	public function get_tools_page_type_labels( $labels ) {

		return array_merge( $labels, array(
			WC_Order_Export_Pro_Manage::EXPORT_PROFILE      => __( 'Profiles', 'woocommerce-order-export' ),
			WC_Order_Export_Pro_Manage::EXPORT_ORDER_ACTION => __( 'Status change jobs', 'woocommerce-order-export' ),
			WC_Order_Export_Pro_Manage::EXPORT_SCHEDULE     => __( 'Scheduled jobs', 'woocommerce-order-export' ),
		) );
	}
}