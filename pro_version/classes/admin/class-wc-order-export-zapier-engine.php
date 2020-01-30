<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}


class WC_Order_Export_Zapier_Engine {
	private $settings;
	private $zapier_api_key;

	public static $last_prepared_export_option = 'woe_last_prepare_zapier_export';

	private $class_name = 'WOE_Formatter_Zapier';
	private $job_settings;

	/**
	 * @var WOE_Formatter_Zapier
	 */
	private $formatter_instance;

	public function __construct( $settings ) {
		$this->settings       = $settings;
		$this->zapier_api_key = $this->settings['zapier_api_key'];
		if ( woe_is_zapier_connecting() ) {
			add_action( 'init', array( $this, 'init' ) );
		}

		add_action( 'woe_init_custom_formatter', array( $this, 'woe_init_custom_formatter' ), 10, 9 );

		// for delete file after timeout
		add_action( 'woe_zapier_delete_file', function ( $path ) {
			wp_delete_file( $path );
		}, 10, 1 );
	}

	public function init() {
		if ( ! empty( $this->zapier_api_key ) and $this->zapier_api_key == $_GET['woe_zapier_export_api_key'] ) {
			$body = json_decode( file_get_contents( "php://input" ), true );

			if ( ! empty( $_GET['woe_zapier_export_auth'] ) ) {
				wp_send_json_success( array( 'website' => get_bloginfo( 'url' ) ), 200 );

			} elseif ( ! empty( $_GET['woe_zapier_export_jobs_list'] ) ) {
				$zap_modes = array(
					'order'       => __( 'Single Order', 'woocommerce-order-export' ),
					'order_items' => __( 'Order Items', 'woocommerce-order-export' ),
					'file'        => __( 'File', 'woocommerce-order-export' ),
				);
				$modes     = array(
					WC_Order_Export_Pro_Manage::EXPORT_SCHEDULE,
					WC_Order_Export_Pro_Manage::EXPORT_ORDER_ACTION,
				);
				$result    = array();
				foreach ( $modes as $mode ) {
					foreach ( WC_Order_Export_Pro_Manage::get( $mode ) as $settings_export ) {
						if ( isset( $settings_export['id'] ) AND ! empty( $settings_export['destination']['type'] ) AND in_array( 'zapier',
								$settings_export['destination']['type'] ) ) {
							$result[] = array(
								'id'    => $mode . '_' . $settings_export['id'],
								'title' => sprintf( __( "%s [%s mode]", 'woocommerce-order-export' ),
									$settings_export['title'],
									$zap_modes[ $settings_export['destination']['zapier_export_type'] ] ),
							);
						}
					}
				}
				wp_send_json_success( $result, 200 );
			} elseif ( ! empty( $_GET['woe_zapier_export_subscribe'] ) AND ! empty( $_GET['woe_zapier_export_current_job'] ) ) {
				if ( ! empty( $body['target_url'] ) ) {
					list( $mode, $id ) = $this->parse_current_job_param( $_GET['woe_zapier_export_current_job'] );

					$current_job                                     = WC_Order_Export_Pro_Manage::get( $mode, $id );
					$current_job['destination']['zapier_target_url'] = $body['target_url'];
					WC_Order_Export_Pro_Manage::save_export_settings( $mode, $id, $current_job );

					wp_send_json_success( array(), 200 );
				} else {
					wp_send_json_success( array(
						'status' => __( 'Error. Target url is missing.', 'woocommerce-order-export' ),
					),
						401 );
				}
			} elseif ( ! empty( $_GET['woe_zapier_export_unsubscribe'] ) AND ! empty( $_GET['woe_zapier_export_current_job'] ) ) {
				list( $mode, $id ) = $this->parse_current_job_param( $_GET['woe_zapier_export_current_job'] );

				$current_job = WC_Order_Export_Pro_Manage::get( $mode, $id );
				if ( ! empty( $current_job['destination']['zapier_target_url'] ) ) {
					unset( $current_job['destination']['zapier_target_url'] );
				}
				WC_Order_Export_Pro_Manage::save_export_settings( $mode, $id, $current_job );

				wp_send_json_success( array(), 200 );

			} elseif ( ! empty( $_GET['woe_zapier_export_new_export'] ) AND ! empty( $_GET['woe_zapier_export_current_job'] ) ) {
				list( $mode, $id ) = $this->parse_current_job_param( $_GET['woe_zapier_export_current_job'] );

				$current_job = WC_Order_Export_Pro_Manage::get( $mode, $id );
				unset( $current_job['schedule']['last_run'] );
				unset( $current_job['schedule']['last_report_sent'] );
				// export only ONE order for "status change" job OR upto 5 for scheduled job
				$limit  = ( $mode == "order-action" ) ? 1 : 5;
				$result = WC_Order_Export_Pro_Engine::build_files_and_prepare( $current_job, "", $limit );

				if ( is_array( $result ) ) {
//                    $result = implode( "<br>\r\n", $result );
					$result = get_option( self::$last_prepared_export_option );
				}

				wp_send_json_success( $result, 200 );
			} else {
				wp_send_json_success( array(
					'status' => __( 'Error. Incorrect request.', 'woocommerce-order-export' ),
				),
					401 );
			}
		} else {
			wp_send_json_success( array(
				'status' => __( 'Error. Incorrect API key, check the Order Export Plugin settings page.',
					'woocommerce-order-export' ),
			), 401 );
		}

	}

	private function parse_current_job_param( $current_job ) {
		$exploded = explode( '_', $current_job );
		if ( 2 == count( $exploded ) ) {
			return array( $exploded[0], $exploded[1] );
		} else {
			return false;
		}
	}


	public function woe_init_custom_formatter(
		$mode,
		$fname,
		$format_settings,
		$format,
		$labels,
		$field_formats,
		$date_format,
		$settings,
		$offset
	) {
		if ( 'preview' != $mode AND isset( $settings['destination']['type'] ) AND in_array( 'zapier',
				$settings['destination']['type'] ) ) {
			add_action( 'woe_start_custom_formatter', array( $this, 'woe_start_custom_formatter' ) );
			add_action( 'woe_finish_custom_formatter', array( $this, 'woe_finish_custom_formatter' ), 10, 2 );
			add_action( 'woe_formatter_output_custom_formatter',
				array( $this, 'woe_formatter_output_custom_formatter' ),
				10, 6 );

			$this->job_settings = $settings;
			include_once dirname( dirname( __FILE__ ) ) . "/formats/class-woe-formatter-zapier.php";
			$class_name = $this->class_name;

			$this->formatter_instance = new $class_name( $mode, $fname . '-zapier', $format_settings, $format, $labels,
				$field_formats, $date_format, $offset );
		}

	}

	public function woe_start_custom_formatter() {
		if ( ! empty( $this->formatter_instance ) ) {
			$this->formatter_instance->start();
		}
	}

	public function woe_finish_custom_formatter() {
		if ( ! empty( $this->formatter_instance ) ) {
			$this->formatter_instance->finish();
		}
	}

	public function woe_formatter_output_custom_formatter(
		$row,
		$order_id,
		$labels,
		$export,
		$static_vals,
		$options
	) {
		if ( ! empty( $this->formatter_instance ) ) {
			if ( $row ) {
				$this->formatter_instance->output( $row );

				do_action( "woe_order_row_exported", $row, $order_id );
			}
		}
	}


}