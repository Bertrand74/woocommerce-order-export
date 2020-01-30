<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WOE_Helper_DateRangeExportNow {

    const DATE_RANGE_NONE	    = '';
    const DATE_RANGE_TODAY	    = 'today';
    const DATE_RANGE_YESTERDAY	    = 'yesterday';
    const DATE_RANGE_CURRENT_WEEK   = 'current_week';
    const DATE_RANGE_LAST_WEEK	    = 'last_week';
    const DATE_RANGE_CURRENT_MONTH  = 'current_month';
    const DATE_RANGE_LAST_MONTH	    = 'last_month';

    public static function get_select_list() {
	return array(
	    self::DATE_RANGE_NONE	    => __('None', 'woocommerce-order-export'),
	    self::DATE_RANGE_TODAY	    => __('Today', 'woocommerce-order-export'),
	    self::DATE_RANGE_YESTERDAY	    => __('Yesterday', 'woocommerce-order-export'),
	    self::DATE_RANGE_CURRENT_WEEK   => __('Current week', 'woocommerce-order-export'),
	    self::DATE_RANGE_LAST_WEEK	    => __('Last week', 'woocommerce-order-export'),
	    self::DATE_RANGE_CURRENT_MONTH  => __('Current month', 'woocommerce-order-export'),
	    self::DATE_RANGE_LAST_MONTH	    => __('Last month', 'woocommerce-order-export'),
	);
    }

    public static function get_range_by_key($key) {

	$format = 'Y-m-d';
	$range  = array('start' => '', 'end' => '');

	switch($key) {
	    case self::DATE_RANGE_TODAY:
		$range = array(
		    'start' => date($format, strtotime('today', current_time('timestamp'))),
		    'end'   => date($format, strtotime('today', current_time('timestamp')))
		);
		break;

	    case self::DATE_RANGE_YESTERDAY:
		$range = array(
		    'start' => date($format, strtotime('yesterday', current_time('timestamp'))),
		    'end'   => date($format, strtotime('yesterday', current_time('timestamp')))
		);
		break;

	    case self::DATE_RANGE_CURRENT_WEEK:
		$range = array(
		    'start' => date($format, strtotime('monday this week', current_time('timestamp'))),
		    'end'   => date($format, strtotime('sunday this week', current_time('timestamp')))
		);
		break;

	    case self::DATE_RANGE_LAST_WEEK:
		$range = array(
		    'start' => date($format, strtotime('monday last week', current_time('timestamp'))),
		    'end'   => date($format, strtotime('sunday last week', current_time('timestamp')))
		);
		break;

	    case self::DATE_RANGE_CURRENT_MONTH:
		$range = array(
		    'start' => date($format, strtotime('first day of this month', current_time('timestamp'))),
		    'end'   => date($format, strtotime('last day of this month', current_time('timestamp')))
		);
		break;

	    case self::DATE_RANGE_LAST_MONTH:
		$range = array(
		    'start' => date($format, strtotime('first day of last month', current_time('timestamp'))),
		    'end'   => date($format, strtotime('last day of last month', current_time('timestamp')))
		);
		break;
	}

	return $range;
    }

}
