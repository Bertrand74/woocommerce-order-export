<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Order_Export_Pro_Admin_Tab_Status_Change_Jobs extends WC_Order_Export_Admin_Tab_Status_Change_Jobs {
	use WC_Order_Export_Pro_Admin_Tab_Abstract;

	public function render() {

		$wc_oe = isset( $_REQUEST['wc_oe'] ) ? $_REQUEST['wc_oe'] : '';

		if ( in_array( $wc_oe, array(
				'copy_action',
				'delete',
				'change_status',
				'change_statuses',
			) ) && ! check_admin_referer( 'woe_nonce', 'woe_nonce' ) ) {
			return;
		}

		$ajaxurl   = admin_url( 'admin-ajax.php' );
		$mode      = WC_Order_Export_Pro_Manage::EXPORT_ORDER_ACTION;
		$all_items = WC_Order_Export_Pro_Manage::get_export_settings_collection( $mode );
		$settings  = WC_Order_Export_Main_Settings::get_settings();
		$show      = array(
			'date_filter'         => $settings['show_export_in_status_change_job'],
			'export_button'       => $settings['show_export_in_status_change_job'],
			'export_button_plain' => $settings['show_export_in_status_change_job'],
			'preview_actions'     => false,
			'sort_orders'         => false,
			'order_filters'       => true,
			'product_filters'     => true,
			'customer_filters'    => true,
			'billing_filters'     => true,
			'shipping_filters'    => true,
		);

		switch ( $wc_oe ) {
			case 'add_action':
				end( $all_items );
				$next_id = key( $all_items ) + 1;
				add_action( 'woe_settings_form_view_top', array( $this, 'add_top_to_view' ) );
				add_action( 'woe_settings_form_view_destinations', array( $this, 'add_destinations_to_view' ), 10, 1 );
				$this->render_template( 'settings-form', array(
					'mode'    => $mode,
					'id'      => $next_id,
					'ajaxurl' => $ajaxurl,
					'show'    => $show,
				) );

				return;
			case 'edit_action':
				if ( ! isset( $_REQUEST['action_id'] ) ) {
					break;
				}
				$item_id                                       = $_REQUEST['action_id'];
				WC_Order_Export_Pro_Manage::$edit_existing_job = true;
				add_action( 'woe_settings_form_view_top', array( $this, 'add_top_to_view' ) );
				add_action( 'woe_settings_form_view_destinations', array( $this, 'add_destinations_to_view' ), 10, 1 );
				$this->render_template( 'settings-form', array(
					'mode'    => $mode,
					'id'      => $item_id,
					'ajaxurl' => $ajaxurl,
					'show'    => $show,
				) );

				return;
			case 'copy_action':
				if ( ! isset( $_REQUEST['action_id'] ) ) {
					break;
				}
				$item_id = $_REQUEST['action_id'];
				$item_id = WC_Order_Export_Pro_Manage::clone_export_settings( $mode, $item_id );

				$url = add_query_arg( array(
					'action_id' => $item_id,
					'wc_oe'     => 'edit_action',
				) );

				$url = remove_query_arg( array( 'woe_nonce' ), $url );

				wp_redirect( $url );

				return;
			case 'delete':
				if ( ! isset( $_REQUEST['action_id'] ) ) {
					break;
				}
				$item_id = $_REQUEST['action_id'];
				unset( $all_items[ $item_id ] );
				WC_Order_Export_Pro_Manage::save_export_settings_collection( $mode, $all_items );

				$url = remove_query_arg( array( 'wc_oe', 'action_id', 'woe_nonce' ) );
				wp_redirect( $url );

				break;
			case 'change_status':
				if ( ! isset( $_REQUEST['action_id'] ) ) {
					break;
				}
				$item_id                         = $_REQUEST['action_id'];
				$all_items[ $item_id ]['active'] = $_REQUEST['status'];
				WC_Order_Export_Pro_Manage::save_export_settings_collection( $mode, $all_items );
				$url = remove_query_arg( array( 'wc_oe', 'action_id', 'status', 'woe_nonce' ) );
				wp_redirect( $url );
				break;
			case 'change_statuses':
				if ( ! isset( $_REQUEST['chosen_order_actions'] ) AND ! isset( $_REQUEST['doaction'] ) AND - 1 == $_REQUEST['doaction'] ) {
					break;
				}
				$chosen_order_actions = explode( ',', $_REQUEST['chosen_order_actions'] );
				$doaction             = $_REQUEST['doaction'];

				foreach ( $chosen_order_actions as $order_action_id ) {
					if ( 'activate' == $doaction ) {
						$all_items[ $order_action_id ]['active'] = 1;
					} elseif ( 'deactivate' == $doaction ) {
						$all_items[ $order_action_id ]['active'] = 0;
					} elseif ( 'delete' == $doaction ) {
						unset( $all_items[ $order_action_id ] );
					}
				}
				WC_Order_Export_Pro_Manage::save_export_settings_collection( $mode, $all_items );
				$url = remove_query_arg( array( 'wc_oe', 'chosen_order_actions', 'doaction', 'woe_nonce' ) );
				wp_redirect( $url );
				break;
		}
		$this->render_template( 'tab/order-actions', array(
			'ajaxurl' => $ajaxurl,
			'tab'     => 'order_actions',
		), $this->get_template_path() );
	}

	public function add_top_to_view( $settings ) {
		$this->render_template( 'top-order-actions', array( 'settings' => $settings ), $this->get_template_path() );
	}

}