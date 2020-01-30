<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

include_once 'class-wc-order-export-table-abstract.php';

class WC_Table_Order_Actions extends WC_Order_Export_Table_Abstract {

	public $tab_name = 'order_action';


	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'action', 'woocommerce-order-export' ),
			'plural'   => __( 'actions', 'woocommerce-order-export' ),
			'ajax'     => true,
		) );
	}

	public function display_tablenav( $which ) {
		if ( 'top' != $which ) {
			return;
		}
		?>
		<div class="tablenav top">
			<div>
				<select name="post_type" class="woe-order-post-type" style="display: none">
					<option value="shop_order">
						<?php _e( 'Order', 'woocommerce-order-export' ); ?>
					</option>
					<?php if ( class_exists( 'WC_Subscriptions' ) ): ?>
						<option value="shop_subscription">
							<?php _e( 'Order Subscription', 'woocommerce-order-export' ); ?>
						</option>
					<?php endif; ?>
					<option value="shop_order_refund">
						<?php _e( 'Order Refund', 'woocommerce-order-export' ); ?>
					</option>
				</select>
				<input type="button" class="button-secondary"
				       value="<?php _e( 'Add job', 'woocommerce-order-export' ); ?>" data-action="add-order-action">
			</div>
			<?php $this->display_reorder_buttons(); ?>
		</div>
		<?php
		$this->bulk_actions( $which );
	}

	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = array();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->items = WC_Order_Export_Pro_Manage::get( WC_Order_Export_Pro_Manage::EXPORT_ORDER_ACTION );

		foreach ( $this->items as $index => $item ) {
			$this->items[ $index ]['id'] = $index;
		}
	}

	public function get_columns() {
		$columns                        = parent::get_columns();
		$columns['title']               = __( 'Title', 'woocommerce-order-export' );
		$columns['format']              = __( 'Format', 'woocommerce-order-export' );
		$columns['from_status']         = __( 'From status', 'woocommerce-order-export' );
		$columns['to_status']           = __( 'To status', 'woocommerce-order-export' );
		$columns['destination']         = __( 'Destination', 'woocommerce-order-export' );
		$columns['destination_details'] = __( 'Destination Details', 'woocommerce-order-export' );
		$columns['actions']             = __( 'Actions', 'woocommerce-order-export' );

		return $columns;
	}

	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'title':
				if ( ! isset( $item['active'] ) || $item['active'] ) {
					$html = '<span class="status-enabled tips" style="display: inline-block" data-tip="' . esc_attr__( 'Enabled',
							'woocommerce' ) . '">' . esc_html__( 'Yes', 'woocommerce' ) . '</span>';
				} else {
					$html = '<span class="status-disabled tips" style="display: inline-block" data-tip="' . esc_attr__( 'Disabled',
							'woocommerce' ) . '">-</span>';
				}
				$html .= ' <a href="admin.php?page=wc-order-export&tab=order_actions&wc_oe=edit_action&action_id=' . $item['id'] . '">' . $item[ $column_name ] . '</a>';

				return $html;
			case 'from_status':
			case 'to_status':
				$data         = array();
				$all_statuses = wc_get_order_statuses();

				$statuses = isset( $item[ $column_name ] ) ? $item[ $column_name ] : array();
				if ( empty( $statuses ) ) {
					$data[] = __( 'Any', 'woocommerce-order-export' );
				} else {
					foreach ( $statuses as $status ) {
						$data[] = $all_statuses[ $status ];
					}
				}

				return implode( ', ', $data );
			case 'destination':
				return $this->get_destination( $item );
			case 'destination_details':
				return $this->get_destination_details( $item );
			case 'actions':
				return "<div class='button-secondary' title='" . __( 'Edit',
						'woocommerce-order-export' ) . "'   data-id='{$item['id']}' data-action='edit-order-action'><span class='dashicons dashicons-edit'></span></div>&nbsp;" .
				       "<div class='button-secondary' title='" . __( 'Clone',
						'woocommerce-order-export' ) . "'   data-id='{$item['id']}' data-action='clone-order-action'><span class='dashicons dashicons-admin-page'></span></div>&nbsp;" .
				       "<div class='button-secondary' title='" . __( 'Delete',
						'woocommerce-order-export' ) . "' data-id='{$item['id']}' data-action='delete-order-action'><span class='dashicons dashicons-trash'></span></div>&nbsp;";
				break;
			default:
				return isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';
		}
	}
}
