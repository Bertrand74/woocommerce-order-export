<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WOE_Export_Zapier extends WOE_Export {

	private $zapier_save_path;
	private $zapier_save_url;
	private $target_url;
	private $file_timeout;

	public function __construct( $destination ) {
		parent::__construct( $destination );
		$upload_dir             = wp_upload_dir( 'woe', true );
		$this->zapier_save_path = $upload_dir['path'];
		$this->zapier_save_url  = $upload_dir['url'];
		$this->target_url       = isset( $this->destination['zapier_target_url'] ) ? $this->destination['zapier_target_url'] : "";

		$global_settings    = WC_Order_Export_Main_Settings::get_settings();
		$this->file_timeout = $global_settings['zapier_file_timeout'];

		if ( ! file_exists( $this->zapier_save_path ) ) {
			if ( @ ! mkdir( $this->zapier_save_path, 0777, true ) ) {
				return sprintf( __( "Can't create folder '%s'. Check permissions.", 'woocommerce-order-export' ),
					$this->zapier_save_path );
			}
		}

		if ( ! is_writable( $this->zapier_save_path ) ) {
			return sprintf( __( "Folder '%s' is not writable. Check permissions.", 'woocommerce-order-export' ),
				$this->zapier_save_path );
		}
	}

	public function prepare( $filename, $filepath ) {
		$body = $this->prepare_post( $filename, $filepath );

		if ( isset( $body['result'] ) ) {
			update_option( WC_Order_Export_Zapier_Engine::$last_prepared_export_option, $body['result'], false );
		} else {
			update_option( WC_Order_Export_Zapier_Engine::$last_prepared_export_option, "", false );
		}

		return isset( $body['text'] ) ? $body['text'] : false;
	}

	private function prepare_post( $filename, $filepath ) {

		if ( in_array( $this->destination['zapier_export_type'], array( 'order', 'order_items' ) ) ) {
			$filepath .= '-zapier';

			return array(
				'result' => json_decode( file_get_contents( $filepath ) ),
				'text'   => sprintf( __( "Zapier Exporter prepared successful", 'woocommerce-order-export' ) ),
			);
		} elseif ( 'file' == $this->destination['zapier_export_type'] ) {
			if ( ! is_writable( $this->zapier_save_path ) ) {
				return array(
					'text' => sprintf( __( "Folder '%s' is not writable. Check permissions.",
						'woocommerce-order-export' ),
						$this->zapier_save_path ),
				);
			}

			$new_filename = $filename;

			if ( @ ! copy( $filepath, $this->zapier_save_path . "/" . $new_filename ) ) {
				return array(
					'text' => sprintf( __( "Can't save zapier file to '%s'. Check permissions.",
						'woocommerce-order-export' ),
						$this->zapier_save_path ),
				);
			}

			wp_schedule_single_event( time() + $this->file_timeout * 60, 'woe_zapier_delete_file',
				array( $this->zapier_save_path . $new_filename ) );

			$url = $this->zapier_save_url . $new_filename;

			return array(
				'result' => array( array( 'file' => $url ) ),
				//                'text'   => sprintf( __( "The file '%s' is available at '%s'", 'woocommerce-order-export' ), $filename,
				//                    $url ),
				'text'   => sprintf( __( "Zapier Exporter prepared successful", 'woocommerce-order-export' ) ),
			);
		} else {
			return false;
		}
	}

	public function run_export( $filename, $filepath, $is_last_order = true ) {

		if ( empty( $this->target_url ) ) {
			return __( "Cannot find any subscriptions", 'woocommerce-order-export' );
		}
		$body = $this->prepare_post( $filename, $filepath );
		if ( ! isset( $body['result'] ) ) {
			return sprintf( __( "Error with prepare", 'woocommerce-order-export' ),
				$this->zapier_save_path );
		}

		$args = apply_filters( 'wc_order_export_order_zapier_args', array(
			'timeout'     => 5,
			'redirection' => 0,
			'httpversion' => '1.0',
			'sslverify'   => false,
			'blocking'    => true,
			'body'        => json_encode( $body['result'] ),
			'cookies'     => array(),
			'user-agent'  => "WordPress " . $GLOBALS['wp_version'],
		), $filename, $filepath );

		$response = apply_filters( 'woe_export_order_zapier_custom_action', false, $this->target_url,
			$this->destination['zapier_export_type'], $args );
		if ( ! $response ) {
			$response = wp_remote_post( $this->target_url, $args );
		}

		// check for errors
		if ( is_wp_error( $response ) ) {
			return $response->get_error_message();
		}

		$this->finished_successfully = true;

		return apply_filters( 'woe_export_zapier_result', $response['body'] );
	}

}
