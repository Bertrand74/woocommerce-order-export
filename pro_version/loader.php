<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
} // Exit if accessed directly

define( 'WOE_PRO_PLUGIN_BASEPATH', dirname( __FILE__ ) );

include WOE_PRO_PLUGIN_BASEPATH . '/classes/updater/class-wc-order-export-updater.php';
include WOE_PRO_PLUGIN_BASEPATH . '/classes/updater/class-wc-order-export-edd.php';

define( 'WOE_MAIN_URL', WC_Order_Export_EDD::woe_get_main_url() );
define( 'WOE_STORE_URL', 'https://algolplus.com/plugins/' );
define( 'WOE_ITEM_NAME', 'Advanced Order Export For WooCommerce (Pro)' );
define( 'WOE_AUTHOR', 'AlgolPlus' );

include WOE_PRO_PLUGIN_BASEPATH . '/classes/admin/tabs/ajax/trait-wc-order-export-pro-admin-tab-abstract-ajax-jobs.php';
include WOE_PRO_PLUGIN_BASEPATH . '/classes/admin/tabs/ajax/trait-wc-order-export-pro-admin-tab-abstract-ajax.php';
include WOE_PRO_PLUGIN_BASEPATH . '/classes/admin/tabs/ajax/class-wc-order-export-pro-ajax.php';
include WOE_PRO_PLUGIN_BASEPATH . '/classes/admin/tabs/trait-wc-order-export-pro-admin-tab-abstract.php';
include WOE_PRO_PLUGIN_BASEPATH . '/classes/admin/tabs/class-wc-order-export-pro-admin-tab-export-now.php';
include WOE_PRO_PLUGIN_BASEPATH . '/classes/admin/tabs/class-wc-order-export-pro-admin-tab-help.php';
include WOE_PRO_PLUGIN_BASEPATH . '/classes/admin/tabs/class-wc-order-export-pro-admin-tab-profiles.php';
include WOE_PRO_PLUGIN_BASEPATH . '/classes/admin/tabs/class-wc-order-export-pro-admin-tab-schedule-jobs.php';
include WOE_PRO_PLUGIN_BASEPATH . '/classes/admin/tabs/class-wc-order-export-pro-admin-tab-status-change-jobs.php';
include WOE_PRO_PLUGIN_BASEPATH . '/classes/admin/tabs/class-wc-order-export-pro-admin-tab-tools.php';
include WOE_PRO_PLUGIN_BASEPATH . '/classes/admin/tabs/class-wc-order-export-pro-admin-tab-settings.php';
include WOE_PRO_PLUGIN_BASEPATH . '/classes/admin/tabs/class-wc-order-export-pro-admin-tab-license.php';

include WOE_PRO_PLUGIN_BASEPATH . '/classes/core/class-wc-order-export-pro-engine.php';
include WOE_PRO_PLUGIN_BASEPATH . '/classes/core/class-wc-order-export-subscription.php';
include WOE_PRO_PLUGIN_BASEPATH . '/classes/admin/class-wc-order-export-pro-manage.php';
include WOE_PRO_PLUGIN_BASEPATH . '/classes/admin/class-wc-order-export-pro-main-settings.php';
include WOE_PRO_PLUGIN_BASEPATH . '/classes/admin/class-wc-order-export-cron.php';
include WOE_PRO_PLUGIN_BASEPATH . '/classes/admin/class-wc-order-export-zapier-engine.php';
include WOE_PRO_PLUGIN_BASEPATH . '/classes/helpers/class-woe-helper-date-range-export-now.php';
include WOE_PRO_PLUGIN_BASEPATH . '/classes/class-wc-order-export-pro-admin.php';

new WC_Order_Export_Pro_Admin();