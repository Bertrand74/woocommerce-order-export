<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class WC_Order_Export_Table_Abstract extends WP_List_Table {

	var $current_destination = '';
	public $tab_name = null;

	private $_actions = array(
		'activate',
		'deactivate',
		'delete',
	);

	protected function get_bulk_actions() {
		$actions               = array();
		$actions['activate']   = __( 'Activate', 'woocommerce-order-export' );
		$actions['deactivate'] = __( 'Deactivate', 'woocommerce-order-export' );
		$actions['delete']     = __( 'Delete', 'woocommerce-order-export' );

		return $actions;
	}

	/**
	 * Output the report
	 */
	public function output() {
		$this->prepare_items();
		?>

        <div class="wp-wrap">
			<?php
			$this->display();
			?>
        </div>
		<?php
	}

	/**
	 * Generate the table rows
	 *
	 */
	public function display_rows() {
		usort( $this->items, function ( $a, $b ) {
			$a['priority'] = isset( $a['priority'] ) ? $a['priority'] : 0;
			$b['priority'] = isset( $b['priority'] ) ? $b['priority'] : 0;

			$compare_results = $a['priority'] - $b['priority'];

			return apply_filters( "woe_sort_displayed_jobs", $compare_results, $a, $b );
		} );

		foreach ( $this->items as $item ) {
			$this->single_row( $item );
		}
	}

	/**
	 * Generates content for a single row of the table
	 */
	public function single_row( $item ) {
		echo '<tr data-job_id="' . $item["id"] . '">';
		$this->single_row_columns( $item );
		echo '</tr>';
	}

	protected function bulk_actions( $which = '' ) {
		?>
        <div class="tablenav <?php echo esc_attr( $which ); ?>">
			<?php if ( $this->has_items() ): ?>
                <div class="actions bulkactions">
					<?php parent::bulk_actions( $which ); ?>
                </div>
			<?php endif; ?>
            <br class="clear"/>
        </div>
		<?php
	}


	protected function display_tablenav( $which ) {
	}

	protected function display_reorder_buttons() {
		if ( count( $this->items ) < 2 ) //don't show extra controls
		{
			return;
		}
		?>
		<div style="display: inline-block;">
			<div class="pipe" style="height: 100%">&#124;</div>
			<div id="reorder_section">
				<input id="start_reorder" class="button-secondary" type="button"
				       value="<?php _e( 'Reorder', 'woocommerce-order-export' ); ?>">
				<input id="apply_reorder" class="button-secondary" type="button"
				       value="<?php _e( 'Confirm sorting', 'woocommerce-order-export' ); ?>">
				<input id="cancel_reorder" class="button-secondary" type="button"
				       value="<?php _e( 'Cancel sorting', 'woocommerce-order-export' ); ?>">
			</div>
		</div>
		<?php
	}

	public function get_columns() {
		$columns          = array();
		$columns['cb']    = '<input type="checkbox" />';
		$columns['title'] = '';

		return $columns;
	}

	protected function display_active_column_default( $item ) {
		if ( isset( $item['active'] ) && $item['active'] ) {
			echo '<span class="status-enabled tips" data-tip="' . esc_attr__( 'Enabled',
					'woocommerce' ) . '">' . esc_html__( 'Yes', 'woocommerce' ) . '</span>';
		} else {
			echo '<span class="status-disabled tips" data-tip="' . esc_attr__( 'Disabled',
					'woocommerce' ) . '">-</span>';
		}
	}

	protected function column_default( $item, $column_name ) {
	}

	/**
	 * Handles the checkbox column output.
	 *
	 */
	protected function column_cb( $item ) {
		?>
        <span class="cb_part">
                    <label class="screen-reader-text" for="cb-select-<?php echo $item['id']; ?>"></label>
        <input type="checkbox" name="profiles" id="cb-select-<?php echo $item['id']; ?>"
               value="<?php echo $item['id']; ?>"/>
        </span>
        <span class="order_part">
            <div class="woe-row-sort-handle"><span class="dashicons dashicons-menu" style="margin: 0 0 5px 5px"></span></div>
        </span>

		<?php
	}

	protected function column_order( $item ) {
		echo '<div class="woe-row-sort-handle"><span class="dashicons dashicons-menu"></span></div>';
	}

	protected function get_destination( $item ) {
		$al = array(
			'ftp'    => __( 'FTP', 'woocommerce-order-export' ),
			'sftp'   => __( 'SFTP', 'woocommerce-order-export' ),
			'http'   => __( 'HTTP POST', 'woocommerce-order-export' ),
			'email'  => __( 'Email', 'woocommerce-order-export' ),
			'folder' => __( 'Directory', 'woocommerce-order-export' ),
			'zapier' => __( 'Zapier', 'woocommerce-order-export' ),
		);
		if ( isset( $item['destination']['type'] ) ) {
			if ( ! is_array( $item['destination']['type'] ) ) {
				$item['destination']['type'] = array( $item['destination']['type'] );
			}
			$type = array_map( function ( $type ) use ( $al ) {
				return $al[ $type ];
			}, $item['destination']['type'] );

			return implode( $type, ', ' );
		}

		return '';
	}

	protected function get_destination_details( $item ) {
		if ( isset( $item['destination']['type'] ) ) {
			if ( ! is_array( $item['destination']['type'] ) ) {
				$item['destination']['type'] = array( $item['destination']['type'] );
			}

			$details = array();
			foreach ( $item['destination']['type'] as $destination ) {
				if ( $destination == 'http' ) {
					$details[] = esc_html( $item['destination']['http_post_url'] );
				}
				if ( $destination == 'email' ) {
					$email_details   = array();
					$email_details[] = __( 'Subject:',
							'woocommerce-order-export' ) . ' ' . esc_html( $item['destination']['email_subject'] );
					if ( ! empty( $item['destination']['email_recipients'] ) ) {
						$email_details[] = __( 'To:',
								'woocommerce-order-export' ) . ' ' . esc_html( $item['destination']['email_recipients'] );
					}
					if ( ! empty( $item['destination']['email_recipients_cc'] ) ) {
						$email_details[] = __( 'CC:',
								'woocommerce-order-export' ) . ' ' . esc_html( $item['destination']['email_recipients_cc'] );
					}
					$details[] = join( "<br>", $email_details );
				}
				if ( $destination == 'ftp' ) {
					$details[] = esc_html( $item['destination']['ftp_user'] ) . "@" . esc_html( $item['destination']['ftp_server'] ) . $item['destination']['ftp_path'];
				}
				if ( $destination == 'sftp' ) {
					$details[] = esc_html( $item['destination']['sftp_user'] ) . "@" . esc_html( $item['destination']['sftp_server'] ) . $item['destination']['sftp_path'];
				}
				if ( $destination == 'folder' ) {
					$details[] = esc_html( $item['destination']['path'] );
				}
				if ( $destination == 'zapier' ) {
					$zap_modes = array(
						'order'       => __( 'Single Order', 'woocommerce-order-export' ),
						'order_items' => __( 'Order Items', 'woocommerce-order-export' ),
						'file'        => __( 'File', 'woocommerce-order-export' ),
					);

					$is_online = ! empty( $item['destination']['zapier_target_url'] ) ? sprintf( '<a href="https://zapier.com/app/history" target=_blank>%s</a>',
						'(connected to Zapier)' ) : '(not connected to Zapier)';
					$details[] = $zap_modes[ $item['destination']['zapier_export_type'] ] . ' ' . $is_online;
				}
			}

			return implode( $details, ', ' );
		}

		return '';
	}

}
