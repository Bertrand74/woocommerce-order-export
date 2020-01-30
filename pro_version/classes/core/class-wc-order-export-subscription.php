<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Order_Export_Subscription {

    function __construct() {
	add_filter('woe_get_order_segments', array($this, 'add_order_segments'));
	add_filter('woe_get_order_fields_subscription', array($this, 'add_order_fields') );
	add_action('woe_order_export_started', array($this, 'get_subscription_details') );
	add_filter('woe_fetch_order_row', array($this, 'fill_new_columns'), 10, 2);
    }

    function add_order_segments($segments) {

	if (WC_Order_Export_Data_Extractor_UI::$object_type === 'shop_subscription') {
	    $segments['subscription'] = __( 'Subscription', 'woocommerce-order-export' );
	}

	return $segments;
    }

    function add_order_fields($fields) {

	$fields['sub_status']		= array('segment' => 'subscription', 'label' => __( 'Subscription Status', 'woocommerce-order-export' ));
	$fields['sub_start_date']	= array('segment' => 'subscription', 'label' => __( 'Subscription Start Date', 'woocommerce-order-export' ));
	$fields['sub_next_payment']	= array('segment' => 'subscription', 'label' => __( 'Subscription Next Payment', 'woocommerce-order-export' ));
	$fields['sub_last_order_date']	= array('segment' => 'subscription', 'label' => __( 'Subscription Last Order Date', 'woocommerce-order-export' ));

	return $fields;
    }

    function get_subscription_details($order_id) {

	    $this->sub = array();

	    if(WC_Order_Export_Data_Extractor::$object_type === 'shop_subscription' && function_exists('wcs_get_subscription')) {

		$sub = wcs_get_subscription($order_id);

		$this->sub['status']	= $sub->get_status();
		$this->sub['start_date']	= date_i18n( wc_date_format(), $sub->get_time( 'date_created', 'site' ) );
		$this->sub['next_payment']	= $sub->get_time( 'next_payment_date', 'site' ) ? date_i18n( wc_date_format(), $sub->get_time( 'next_payment_date', 'site' ) ) : '-';
		$this->sub['last_order_date'] = date_i18n( wc_date_format(), $sub->get_time( 'last_order_date_created', 'site' ) );
	    }

	    return $order_id;
    }

    // add new values to row
    function fill_new_columns($row, $order_id) {

	foreach($this->sub as $k => $v) {
	    if(isset($row['sub_'.$k])) {
		$row['sub_'.$k] = $v;
	    }
	}

	return $row;
    }
}