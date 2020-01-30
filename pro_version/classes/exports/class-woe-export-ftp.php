<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WOE_Export_Ftp extends WOE_Export {

	public function get_num_of_retries() {
		return ! empty( $this->destination['ftp_max_retries'] ) ? $this->destination['ftp_max_retries'] : 1;
	}

	public function run_export( $filename, $filepath, $is_last_order = true ) {
		if ( ! extension_loaded( 'ftp' ) ) {
			return __( "Please, install/enable FTP extension for PHP", 'woocommerce-order-export' );
		}

		//use default port?
		if ( empty( $this->destination['ftp_port'] ) ) {
			$this->destination['ftp_port'] = 21;
		}
		//use default timeout?
		if ( empty( $this->destination['ftp_conn_timeout'] ) ) {
			$this->destination['ftp_conn_timeout'] = 15;
		}

		//1
		if ( empty( $this->destination['enable_ssl'] ) ) {
			$conn_id = ftp_connect( $this->destination['ftp_server'], $this->destination['ftp_port'], $this->destination['ftp_conn_timeout'] );
		} else {
			if ( function_exists('ftp_ssl_connect') ) {
				$conn_id = ftp_ssl_connect( $this->destination['ftp_server'], $this->destination['ftp_port'], $this->destination['ftp_conn_timeout'] );
			} else {
				return __( "Current PHP build requires OpenSSL support to use this mode", 'woocommerce-order-export' );
			}
		}

		if ( ! $conn_id ) {
			return sprintf( __( "Can't connect to %s using port %s", 'woocommerce-order-export' ),
				$this->destination['ftp_server'], $this->destination['ftp_port'] );
		}

		//2
		if ( ! ftp_login( $conn_id, $this->destination['ftp_user'], $this->destination['ftp_pass'] ) ) {
			return sprintf( __( "Can't login to FTP as user '%s' using password '%s'", 'woocommerce-order-export' ),
				$this->destination['ftp_user'], $this->destination['ftp_pass'] );
		}

		//3?
		if ( ! empty( $this->destination['ftp_passive_mode'] ) AND ! @ftp_pasv( $conn_id, true ) ) {
			return __( "Can't switch to Passive Mode", 'woocommerce-order-export' );
		}

		if ( $this->destination['ftp_path'] ) {
			if ( substr( $this->destination['ftp_path'], 0, 1 ) != '/' ) {
				$this->destination['ftp_path'] = '/' . $this->destination['ftp_path'];
			}
			if ( ! ftp_chdir( $conn_id, $this->destination['ftp_path'] ) ) {
				return sprintf( __( "Can't change FTP directory to '%s'", 'woocommerce-order-export' ),
					$this->destination['ftp_path'] );
			}
		}

		$files_for_upload = apply_filters( "woe_ftp_files", array( $filename => $filepath ) ); // as $remote=>$local
		
		do_action( "woe_ftp_before_upload_files", $conn_id, $files_for_upload, $this); 
		
		foreach ( $files_for_upload as $filename => $filepath ) {
			$results[] = $this->upload_file( $conn_id, $filename, $filepath );
		}

		ftp_close( $conn_id );

		return join( "\n", $results );
	}

	function upload_file( $conn_id, $filename, $filepath ) {
		//4 support append 
		if ( ! empty( $this->destination['ftp_append_existing'] ) ) {
			$ftp_files = ftp_nlist( $conn_id, $this->destination['ftp_path'] );
			$ftp_files = array_map( "basename", $ftp_files ); //some servers return full path
			//got existing file?
			if ( in_array( $filename, $ftp_files ) ) {
				$existing_file = WC_Order_Export_Pro_Engine::get_filename( "ftp" );
				if ( ! ftp_get( $conn_id, $existing_file, $filename, FTP_BINARY ) ) {
					return sprintf( __( "Can't download file '%s'", 'woocommerce-order-export' ), $filename );
				}
				//!empty file, must call merger hook
				if ( filesize( $existing_file ) ) {
					do_action( 'woe_ftp_append_' . WC_Order_Export_Pro_Engine::$current_job_settings['format'],
						$existing_file, $filepath );
				}
				unlink( $existing_file );
			}
		}

		if ( ! ftp_put( $conn_id, $filename, $filepath, FTP_BINARY ) ) {
			return sprintf( __( "Can't upload file '%s'", 'woocommerce-order-export' ), $filename );
		}

		$this->finished_successfully = true;

		return sprintf( __( "We have uploaded file '%s' to '%s'", 'woocommerce-order-export' ), $filename,
			$this->destination['ftp_server'] . $this->destination['ftp_path'] );
	}
}
