<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
$woe_edd = WC_Order_Export_EDD::getInstance();
$woe_edd->edd_woe_license_page();