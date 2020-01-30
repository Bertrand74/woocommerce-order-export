<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Order_Export_EDD {
	private $license_notice_transient_name = 'woe_licence_check';
	private static $instance;

	/**
	 * WC_Order_Export_EDD constructor.
	 */
	private function __construct() {
		// EDD license actions
		add_action( 'admin_init', array( $this, 'edd_woe_plugin_updater' ), 0 );
		add_action( 'admin_init', array( $this, 'edd_woe_register_option' ) );
		add_action( 'admin_init', array( $this, 'edd_woe_activate_license' ) );
		add_action( 'admin_init', array( $this, 'edd_woe_deactivate_license' ) );

		//show warning for our pages only 
		if ( isset( $_REQUEST['page'] ) AND $_REQUEST['page'] == "wc-order-export" )
			add_action( 'wp_loaded', array( $this, 'control_license_notice' ) );
	}

	public function control_license_notice() {
		$active_tab = isset( $_REQUEST['tab'] ) ? $_REQUEST['tab'] : "";
		$noticed    = get_transient( $this->license_notice_transient_name );

		if ( ! $noticed && ( $active_tab === WC_Order_Export_Pro_Admin_Tab_License::get_key() ) ) {
			set_transient( $this->license_notice_transient_name, true, HOUR_IN_SECONDS * 24 );
			$noticed = true;
		}

		if ( ! $noticed ) {
			add_action( 'admin_init', function () {
				if ( ! $this->license_status() ) {
					add_action( 'admin_notices', array( $this, 'inactive_notice' ) );
				}
			} );
		}
	}

	public function license_status() {
		$status = get_option( 'edd_woe_license_status' );

		return ! empty( $status ) && $status === 'valid';
    }

    public function inactive_notice() {
	    $key    = WC_Order_Export_Pro_Admin_Tab_License::get_key();
	    $url    = esc_url( add_query_arg( array( 'tab' => $key ), menu_page_url( 'wc-order-export', false ) ) );
	    $link = '<a href="' . $url . '">' .__( 'Click here', 'woocommerce-order-export' ).  '</a>';
	    ?>
        <div class="notice notice-warning is-dismissible">
            <p><?php printf( __( 'The "%s" license key is not active! %s to fix this issue.', 'woocommerce-order-export' ), esc_attr( WOE_ITEM_NAME ), $link ); ?></p>
        </div>
        <?php
    }

	//***********  EDD LICENSE FUNCTIONS BEGIN  *************************************************************************************************************************************************************************************************************************************************
	function edd_woe_plugin_updater() {

		// retrieve our license key from the DB
		$license_key = trim( get_option( 'edd_woe_license_key' ) );

		// setup the updater
		$edd_updater = new WC_Order_Export_Updater( WOE_STORE_URL,
			'woocommerce-order-export/woocommerce-order-export.php', array(
				'version'   => WOE_VERSION,   // current version number
				'license'   => $license_key,  // license key (used get_option above to retrieve from DB)
				'item_name' => WOE_ITEM_NAME, // name of this plugin
				'author'    => WOE_AUTHOR     // author of this plugin
			)
		);

	}

	function edd_woe_license_page() {

		// validate license only once per hour 
		if ( ! get_transient( 'edd_woe_license_key_checked' ) ) {
			$this->edd_woe_check_license();
			set_transient( 'edd_woe_license_key_checked', 1, 1 * HOUR_IN_SECONDS );
		}

		$this->error_messages = array(
			'missing'               => __( 'not found', 'woocommerce-order-export' ),
			'license_not_activable' => __( 'is not activable', 'woocommerce-order-export' ),
			'revoked'               => __( 'revoked', 'woocommerce-order-export' ),
			'no_activations_left'   => __( 'no activations left', 'woocommerce-order-export' ),
			'expired'               => __( 'expired', 'woocommerce-order-export' ),
			'key_mismatch'          => __( 'key mismatch', 'woocommerce-order-export' ),
			'invalid_item_id'       => __( 'invalid item ID', 'woocommerce-order-export' ),
			'item_name_mismatch'    => __( 'item name mismatch', 'woocommerce-order-export' ),
		);

		$license = get_option( 'edd_woe_license_key' );
		$status  = get_option( 'edd_woe_license_status' );
		$error   = get_option( 'edd_woe_license_error' );
		if ( empty( $error ) ) {
			$error = $status;
		}
		if ( isset( $this->error_messages[ $error ] ) ) {
			$error = $this->error_messages[ $error ];
		}

		$site_url          = 'https://algolplus.com';
		$site_link_html    = sprintf( '<a target="_blank" href="%s">%s</a>', $site_url, $site_url );
		$account_url       = 'https://algolplus.com/plugins/my-account';
		$account_link_html = sprintf( '<a target="_blank" href="%s">%s</a>', $account_url, $account_url );
		$dashboard_link    = sprintf( '<a target="_blank" href="%s">%s</a>', admin_url( 'update-core.php' ),
			__( ">Dashboard > Updates", 'woocommerce-order-export' ) );
		?>
        <div class="wrap">
        <div id="license_help_text">

            <h3><?php _e( 'Licenses', 'woocommerce-order-export' ); ?></h3>

            <div class="license_paragraph"><?php printf( __( 'The license key you received when completing your purchase from %s will grant you access to updates until it expires.',
					'woocommerce-order-export' ), $site_link_html ); ?><br>
				<?php _e( 'You do not need to enter the key below for the plugin to work, but you will need to enter it to get automatic updates.',
					'woocommerce-order-export' ); ?></div>
            <div class="license_paragraph"><?php printf( __( "If you're seeing a red message telling you that your key isn't valid or is out of installs, %s visit %s to manage your installs or renew / upgrade your license.",
					'woocommerce-order-export' ), "<br>", $account_link_html ); ?></div>
            <div class="license_paragraph"><?php printf( __( 'Not seeing an update but expecting one? In WordPress, go to %s and click "Check Again".',
					'woocommerce-order-export' ), $dashboard_link ); ?></div>

        </div>
        <form method="post" action="options.php">

			<?php settings_fields( 'edd_woe_license' ); ?>

            <table class="form-table">
                <tbody>
                <tr valign="top">
                    <th scope="row" valign="top">
						<?php _e( 'License Key', 'woocommerce-order-export' ); ?>
                    </th>
                    <td>
                        <input id="edd_woe_license_key" name="edd_woe_license_key" type="text" class="regular-text"
                               value="<?php esc_attr_e( $license ); ?>"/><br>
                        <label class="description"
                               for="edd_woe_license_key"><?php _e( 'look for it inside purchase receipt (email)',
								'woocommerce-order-export' ); ?></label>
                    </td>
                </tr>
                <tr valign="top">
                    <th scope="row" valign="top">
                    </th>
                    <td>
						<?php if ( $status !== false && $status == 'valid' ) { ?>
                            <span style="color:green;"><?php _e( 'License is active',
									'woocommerce-order-export' ); ?></span><br><br>
							<?php wp_nonce_field( 'edd_woe_nonce', 'edd_woe_nonce' ); ?>
                            <input type="submit" class="button-secondary" name="edd_woe_license_deactivate"
                                   value="<?php _e( 'Deactivate License', 'woocommerce-order-export' ); ?>"/>
						<?php } else {
							if ( ! empty( $error ) ) { ?>
								<?php echo __( 'License is inactive:', 'woocommerce-order-export' ); ?>&nbsp;<span
                                        style="color:red;"><?php echo $error; ?></span><br><br>
							<?php }
							wp_nonce_field( 'edd_woe_nonce', 'edd_woe_nonce' ); ?>
                            <input type="submit" class="button-secondary" name="edd_woe_license_activate"
                                   value="<?php _e( 'Activate License', 'woocommerce-order-export' ); ?>"/>
						<?php } ?>
                    </td>
                </tr>
                </tbody>
            </table>

        </form>
		<?php
	}

	function edd_woe_register_option() {
		// creates our settings in the options table
		register_setting( 'edd_woe_license', 'edd_woe_license_key', array( $this, 'edd_sanitize_license' ) );
	}

	function edd_sanitize_license( $new ) {
		$old = get_option( 'edd_woe_license_key' );
		if ( $old && $old != $new ) {
			delete_option( 'edd_woe_license_status' ); // new license has been entered, so must reactivate
		}

		return $new;
	}


	/************************************
	 * this illustrates how to activate
	 * a license key
	 *************************************/

	function edd_woe_activate_license() {

		// listen for our activate button to be clicked
		if ( isset( $_POST['edd_woe_license_activate'] ) ) {

			// run a quick security check
			if ( ! check_admin_referer( 'edd_woe_nonce', 'edd_woe_nonce' ) ) {
				return;
			} // get out if we didn't click the Activate button

			// retrieve the license from the database
			$license = trim( $_POST['edd_woe_license_key'] );
			update_option( 'edd_woe_license_key', $license, false );


			// data to send in our API request
			$api_params = array(
				'edd_action' => 'activate_license',
				'license'    => $license,
				'item_name'  => urlencode( WOE_ITEM_NAME ), // the name of our product in EDD
				'url'        => WOE_MAIN_URL,
			);

			// Call the custom API.
			$response = wp_remote_post( WOE_STORE_URL,
				array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// make sure the response came back okay
			if ( is_wp_error( $response ) ) {
				return false;
			}

			// decode the license data
			$license_data = json_decode( wp_remote_retrieve_body( $response ) );

			// $license_data->license will be either "valid" or "invalid"

			update_option( 'edd_woe_license_status', $license_data->license, false );
			update_option( 'edd_woe_license_error', @$license_data->error, false );

		}
	}

	function edd_woe_force_deactivate_license() {
		$this->_edd_woe_deactivate_license();
	}

	function edd_woe_deactivate_license() {

		// listen for our activate button to be clicked
		if ( isset( $_POST['edd_woe_license_deactivate'] ) ) {

			// run a quick security check
			if ( ! check_admin_referer( 'edd_woe_nonce', 'edd_woe_nonce' ) ) {
				return;
			} // get out if we didn't click the Activate button

			$this->_edd_woe_deactivate_license();
		}
	}

	private function _edd_woe_deactivate_license() {
		// retrieve the license from the database
		$license = trim( get_option( 'edd_woe_license_key' ) );


		// data to send in our API request
		$api_params = array(
			'edd_action' => 'deactivate_license',
			'license'    => $license,
			'item_name'  => urlencode( WOE_ITEM_NAME ), // the name of our product in EDD
			'url'        => WOE_MAIN_URL,
		);

		// Call the custom API.
		$response = wp_remote_post( WOE_STORE_URL,
			array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) ) {
			return false;
		}

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if ( $license_data->license == 'deactivated' ) {
			delete_option( 'edd_woe_license_status' );
		}
		delete_option( 'edd_woe_license_error' );
	}

	function edd_woe_check_license() {

		global $wp_version;

		$license = trim( get_option( 'edd_woe_license_key' ) );

		$api_params = array(
			'edd_action' => 'check_license',
			'license'    => $license,
			'item_name'  => urlencode( WOE_ITEM_NAME ),
			'url'        => WOE_MAIN_URL,
		);

		// Call the custom API.
		$response = wp_remote_post( WOE_STORE_URL,
			array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

		if ( is_wp_error( $response ) ) {
			return false;
		}

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		update_option( 'edd_woe_license_status', $license_data->license, false );
		update_option( 'edd_woe_license_error', @$license_data->error, false );
	}

	public static function getInstance() {
		if ( empty( self::$instance ) ) {
			self::$instance = new self();
		}

		return self::$instance;
	}

//***********  EDD LICENSE FUNCTIONS END  *************************************************************************************************************************************************************************************************************************************************
	public static function woe_get_main_url() {
		$home_url       = home_url();
		$url_components = explode( '.', basename( $home_url ) );
		if ( count( $url_components ) > 2 ) {
			array_shift( $url_components );
		}
		$main_url = implode( '.', $url_components );
		if ( strpos( $home_url, 'https://' ) !== 0 ) {
			$main_url = "https://{$main_url}";
		} else {
			$main_url = "http://{$main_url}";
		}

		return $main_url;
	}
}

WC_Order_Export_EDD::getInstance();