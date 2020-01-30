<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

include_once 'class-wc-order-export-table-abstract.php';

class WC_Table_Schedules extends WC_Order_Export_Table_Abstract {

	public $tab_name = 'schedule';

	public function __construct() {
		parent::__construct( array(
			'singular' => __( 'job', 'woocommerce-order-export' ),
			'plural'   => __( 'jobs', 'woocommerce-order-export' ),
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
				       value="<?php _e( 'Add job', 'woocommerce-order-export' ); ?>" id="add_schedule">
			</div>
			<?php $this->display_reorder_buttons(); ?>
		</div>
		<?php
		$this->bulk_actions( $which );
	}

	public function prepare_items() {
		$columns  = $this->get_columns();
		$hidden   = array();
		$sortable = $this->get_sortable_columns();

		$this->_column_headers = array( $columns, $hidden, $sortable );

		$this->items = (array) WC_Order_Export_Pro_Manage::get( WC_Order_Export_Pro_Manage::EXPORT_SCHEDULE );

		foreach ( $this->items as $index => $item ) {
			$this->items[ $index ]['id'] = $index;
		}

		$direction = isset( $_REQUEST['order'] ) ? $_REQUEST['order'] : false;
		$column    = isset( $_REQUEST['orderby'] ) ? $_REQUEST['orderby'] : false;

		if ( $direction && $column ) {
			usort( $this->items, function ( $a, $b ) use ( $column, $direction ) {
				$a = ! isset( $a[ $column ] ) ? $a['schedule'] : $a;
				$b = ! isset( $b[ $column ] ) ? $b['schedule'] : $b;

				if ( $direction == 'asc' ) {
					return ( is_numeric( $a[ $column ] ) && is_numeric( $b[ $column ] ) ) ? $a[ $column ] - $b[ $column ] : strcmp( $a[ $column ], $b[ $column ] );
				} else {
					return ( is_numeric( $a[ $column ] ) && is_numeric( $b[ $column ] ) ) ? $b[ $column ] - $a[ $column ] : strcmp( $b[ $column ], $a[ $column ] );
				}
			} );
		}
	}

	public function get_columns() {
		$columns                        = parent::get_columns();
		$columns['title']               = __( 'Title', 'woocommerce-order-export' );
		$columns['format']              = __( 'Format', 'woocommerce-order-export' );
		$columns['destination']         = __( 'Destination', 'woocommerce-order-export' );
		$columns['destination_details'] = __( 'Destination Details', 'woocommerce-order-export' );
		$columns['recurrence']          = __( 'Recurrence', 'woocommerce-order-export' );
		$columns['last_report_sent']    = __( 'Last report sent', 'woocommerce-order-export' );
		$columns['last_run']            = __( 'Last run', 'woocommerce-order-export' );
		$columns['next_run']            = __( 'Next run', 'woocommerce-order-export' );
		$columns['actions']             = __( 'Actions', 'woocommerce-order-export' );

		return $columns;
	}

	protected function get_sortable_columns() {
		return array(
			'title'            => array( 'title', true ),
			'last_run'         => array( 'last_run', true ),
			'last_report_sent' => array( 'last_report_sent', true ),
			'next_run'         => array( 'next_run', true ),
		);
	}

	private function make_date_column( $item, $column ) {
		if ( isset( $item['active'] ) && ! $item['active'] ) {
			return __( 'Inactive', 'woocommerce-order-export' );
		}

		if ( isset( $item['schedule'][ $column ] ) ) {
			$column_value = $item['schedule'][ $column ];
			if ( $column_value ) {
				return gmdate( 'M j Y', $column_value ) . ' ' . __( 'at',
						'woocommerce-order-export' ) . ' ' . gmdate( 'G:i', $column_value );
			} else {
				return __( '', 'woocommerce-order-export' );
			}
		} else {
			return 'next_run' === $column ? __( 'Not installed', 'woocommerce-order-export' ) : __( 'Not executed', 'woocommerce-order-export' );
		}
	}

	function column_default( $item, $column_name ) {
		$active = ! isset( $item['active'] ) || $item['active'];
		switch ( $column_name ) {
			case 'title':
				if ( ! isset( $item['active'] ) || $item['active'] ) {
					$html = '<span class="status-enabled tips" style="display: inline-block" data-tip="' . esc_attr__( 'Enabled',
							'woocommerce' ) . '">' . esc_html__( 'Yes', 'woocommerce' ) . '</span>';
				} else {
					$html = '<span class="status-disabled tips" style="display: inline-block" data-tip="' . esc_attr__( 'Disabled',
							'woocommerce' ) . '">-</span>';
				}
				$html .= ' <a href="admin.php?page=wc-order-export&tab=schedules&wc_oe=edit_schedule&schedule_id=' . $item['id'] . '">' . $item[ $column_name ] . '</a>';;

				return $html;
			case 'recurrence':
				$r         = '';
				$day_names = WC_Order_Export_Pro_Manage::get_days();
				if ( isset( $item['schedule'] ) && isset( $item['schedule']['type'] ) ) {
					if ( $item['schedule']['type'] == 'schedule-1' ) {
						$r = __( 'Run', 'woocommerce-order-export' ) . ' ';
						if ( isset( $item['schedule']['weekday'] ) ) {
							$days = array_keys( $item['schedule']['weekday'] );
							foreach ( $days as $k => $d ) {
								$days[ $k ] = $day_names[ $d ];
							}
							$r .= __( "on", 'woocommerce-order-export' ) . ' ' . implode( ', ', $days );
						}
						if ( isset( $item['schedule']['run_at'] ) ) {
							$r .= ' ' . __( 'at', 'woocommerce-order-export' ) . ' ' . $item['schedule']['run_at'];
						}
						//nothing selected
						if ( empty( $days ) ) {
							$r = __( 'Never', 'woocommerce-order-export' );
						}
					} else if ( $item['schedule']['type'] == 'schedule-2' ) {
						if ( $item['schedule']['interval'] == 'first_day_month' ) {
							$r = __( "First Day Every Month", 'woocommerce-order-export' );
						} elseif ( $item['schedule']['interval'] == 'first_day_quarter' ) {
							$r = __( "First Day Every Quarter", 'woocommerce-order-export' );
						} elseif ( $item['schedule']['interval'] == 'custom' ) {
							$r = sprintf( __( "To run every %s minute(s)", 'woocommerce-order-export' ),
								$item['schedule']['custom_interval'] );
						} else {
							foreach ( wp_get_schedules() as $name => $schedule ) {
								if ( $item['schedule']['interval'] == $name ) {
									$r = $schedule['display'];
								}
							}
						}
					} else if ( $item['schedule']['type'] == 'schedule-3' ) {
						$times = explode( ',', $item['schedule']['times'] );
						foreach ( $times as $k => $t ) {
							$a = explode( " ", $t );
							if ( count( $a ) == 2 ) {
								$times[ $k ] = $day_names[ $a[0] ] . " " . $a[1];
							} // replace days
						}
						$r = __( 'Run on', 'woocommerce-order-export' ) . ' <br>' . implode( ',<br>', $times );
					} else if ( $item['schedule']['type'] == 'schedule-4' ) {
						$now   = current_time( "timestamp" );
						$times = array_filter( explode( ',', $item['schedule']['date_times'] ),
							function ( $v ) use ( $now ) {
								return $now < strtotime( $v );
							} );
						$r     = __( 'Run on', 'woocommerce-order-export' ) . ' <br>' . implode( ',<br>', $times );
					} else if ( $item['schedule']['type'] == 'schedule-5' ) {
						$r = __( 'Run on', 'woocommerce-order-export' ) . ' <br>' . $item['schedule']['crontab'];
					}
				}

				return $r;
			case 'destination':
				return $this->get_destination( $item );
			case 'destination_details':
				return $this->get_destination_details( $item );
			case 'last_report_sent':
				return $this->make_date_column( $item, 'last_report_sent' );
			case 'last_run':
				return $this->make_date_column( $item, 'last_run' );
			case 'next_run':
				return $this->make_date_column( $item, 'next_run' );
			case 'actions':
				return
					'<div class="btn-edit button-secondary" data-id="' . $item['id'] . '" title="' . __( 'Edit',
						'woocommerce-order-export' ) . '"><span class="dashicons dashicons-edit"></span></div>&nbsp;' .
					'<div class="btn-clone button-secondary" data-id="' . $item['id'] . '" title="' . __( 'Clone',
						'woocommerce-order-export' ) . '"><span class="dashicons dashicons-admin-page"></span></div>&nbsp;' .
					'<div class="btn-trash button-secondary" data-id="' . $item['id'] . '" title="' . __( 'Delete',
						'woocommerce-order-export' ) . '"><span class="dashicons dashicons-trash"></span></div>&nbsp;&nbsp;' .
					'<div class="btn-export button-secondary" data-id="' . $item['id'] . '" title="' . __( 'Export',
						'woocommerce-order-export' ) . '"><span class="dashicons dashicons-download"></span></div>';
				break;
			default:

				return isset( $item[ $column_name ] ) ? $item[ $column_name ] : '';
		}

	}
}
