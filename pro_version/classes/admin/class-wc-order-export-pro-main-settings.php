<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Order_Export_Pro_Main_Settings {

	const SETTINGS_NAME_COMMON = 'woocommerce-order-export-common';

	public static function get_settings( array $default_settings ) {
		$option_value = get_option( self::SETTINGS_NAME_COMMON, array() );

		if ( isset( $option_value['cron_key'] ) && null === $option_value['cron_key'] ) {
			$option_value['cron_key'] = self::generate_cron_key();
			update_option( self::SETTINGS_NAME_COMMON, $option_value );
		}

		return array_merge( $default_settings, $option_value );
	}

	public static function save_settings( array $settings = array() ) {

		update_option( self::SETTINGS_NAME_COMMON, $settings, false );

		if ( isset( $settings['ipn_url'] ) ) {
			update_option( WOE_IPN_URL_OPTION_KEY, $settings['ipn_url'], false );
		}
	}

	private static function generate_cron_key() {
		return substr( md5( mt_rand() ), 0, 4 );
	}

}
