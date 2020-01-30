<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

include_once 'class-wc-order-export-table-abstract.php';

class WC_Table_Profiles extends WC_Order_Export_Table_Abstract {

	public $tab_name = 'profile';

	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'profile', 'woocommerce-order-export' ),
			'plural'   => __( 'profiles', 'woocommerce-order-export' ),
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
				<select name="post_type" class="woe-order-post-type">
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
				       value="<?php _e( 'Add Profile', 'woocommerce-order-export' ); ?>" id="add_profile">
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

		$this->items = (array) WC_Order_Export_Pro_Manage::get( WC_Order_Export_Pro_Manage::EXPORT_PROFILE );

		foreach ( $this->items as $index => $item ) {
			$this->items[ $index ]['id'] = $index;
		}
	}

	public function get_columns() {
		$columns                = parent::get_columns();
		$columns['bulk_action'] = __( 'Use as bulk action', 'woocommerce-order-export' );
		$columns['title']       = __( 'Title', 'woocommerce-order-export' );
		$columns['format']      = __( 'Format', 'woocommerce-order-export' );
		$columns['from_date']   = __( 'From Date', 'woocommerce-order-export' );
		$columns['to_date']     = __( 'To Date', 'woocommerce-order-export' );
		$columns['actions']     = __( 'Actions', 'woocommerce-order-export' );

		return $columns;
	}

	function column_default( $item, $column_name ) {
		switch ( $column_name ) {
			case 'bulk_action':
				if ( isset( $item['use_as_bulk'] ) && $item['use_as_bulk'] == 'on' ) {
					echo '<span class="status-enabled tips" data-tip="' . esc_attr__( 'Enabled',
							'woocommerce' ) . '">' . esc_html__( 'Yes', 'woocommerce' ) . '</span>';
				} else {
					echo '<span class="status-disabled tips" data-tip="' . esc_attr__( 'Disabled',
							'woocommerce' ) . '">-</span>';
				}
				break;
			case 'title':
				return '<a href="admin.php?page=wc-order-export&tab=profiles&wc_oe=edit_profile&profile_id=' . $item['id'] . '">' . $item[ $column_name ] . '</a>';
				break;
			case 'actions':
				return
					'<div class="btn-edit button-secondary" data-id="' . $item['id'] . '" title="' . __( 'Edit',
						'woocommerce-order-export' ) . '"><span class="dashicons dashicons-edit"></span></div>&nbsp;' .
					'<div class="btn-clone button-secondary" data-id="' . $item['id'] . '" title="' . __( 'Clone',
						'woocommerce-order-export' ) . '"><span class="dashicons dashicons-admin-page"></span></div>&nbsp;' .
					'<div class="btn-to-actions button-secondary" data-id="' . $item['id'] . '" title="' . __( 'Copy to a Status change job',
						'woocommerce-order-export' ) . '"><span class="dashicons dashicons-migrate"></span></div>&nbsp;' .
					'<div class="btn-to-scheduled button-secondary" data-id="' . $item['id'] . '" title="' . __( 'Copy to a Scheduled job',
						'woocommerce-order-export' ) . '"><span class="dashicons dashicons-clock"></span></div>&nbsp;' .
					'<div class="btn-trash button-secondary" data-id="' . $item['id'] . '" title="' . __( 'Delete',
						'woocommerce-order-export' ) . '"><span class="dashicons dashicons-trash"></span></div>&nbsp;&nbsp;&nbsp;' .
					'<div class="btn-export button-secondary" data-id="' . $item['id'] . '" title="' . __( 'Export',
						'woocommerce-order-export' ) . '"><span class="dashicons dashicons-download"></span></div>';
				break;
			default:
				return isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';
		}
	}

}
