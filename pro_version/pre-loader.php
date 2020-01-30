<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

if ( ! defined( 'WOE_IPN_URL_OPTION_KEY' ) ) {
	define( 'WOE_IPN_URL_OPTION_KEY', 'woe_ipn_url_key' );
}

if ( ! function_exists( "woe_is_zapier_connecting" ) ) {
	function woe_is_zapier_connecting() {
		return ! empty( $_GET['woe_zapier_export_api_key'] );
	}
}

add_filter( 'woe_check_running_options', function ( $is_backend ) {

	$is_cron              = defined( 'DOING_CRON' );
	$is_frontend_checkout = isset( $_REQUEST['wc-ajax'] ) && $_REQUEST['wc-ajax'] === 'checkout'
	                        || isset( $_POST['woocommerce_checkout_place_order'] )
	                        || preg_match( '/\bwc\-api\b/', $_SERVER['REQUEST_URI'] ) // WC_API
	                        || preg_match( '/\bwc\/v\d+\b/', $_SERVER['REQUEST_URI'] ) // Rest API
	                        || woe_is_zapier_connecting();
	// use preg to match multilpe values
	$ipn_url = get_option( WOE_IPN_URL_OPTION_KEY );
	$is_ipn  = ( $ipn_url AND preg_match( "#($ipn_url)#i", $_SERVER['REQUEST_URI'] ) );

	return $is_backend || $is_cron || $is_frontend_checkout || $is_ipn;
} );