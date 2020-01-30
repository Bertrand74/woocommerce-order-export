<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

//init composer
include_once WOE_PRO_PLUGIN_BASEPATH . '/vendor/autoload.php';

use phpseclib\Net\SFTP;
use phpseclib\Crypt\RSA;

class WOE_Export_Sftp extends WOE_Export {
	var $user_errors;

	public function get_num_of_retries() {
		return ! empty( $this->destination['sftp_max_retries'] ) ? $this->destination['sftp_max_retries'] : 1;
	}

	public function run_export( $filename, $filepath, $is_last_order = true ) {
		//use default port?
		if ( empty( $this->destination['sftp_port'] ) ) {
			$this->destination['sftp_port'] = 22;
		}

		//use default timeout?
		if ( empty( $this->destination['sftp_conn_timeout'] ) ) {
			$this->destination['sftp_conn_timeout'] = 15;
		}

		//adjust path final /
		if ( substr( $this->destination['sftp_path'], - 1 ) != '/' ) {
			$this->destination['sftp_path'] .= '/';
		}

		$sftp = new SFTP( $this->destination['sftp_server'], $this->destination['sftp_port'],
			$this->destination['sftp_conn_timeout'] );

		$this->user_errors  = array();
		$prev_error_handler = set_error_handler( array( $this, 'record_user_errors' ), E_USER_NOTICE );

		do {
			//1
			if( !empty($this->destination['sftp_private_key_path']) ) {
				$key_file = $this->destination['sftp_private_key_path'];
				if( !file_exists($key_file) ) {
					$message = sprintf( __( "Can't find private key file '%s'", 'woocommerce-order-export' ), $key_file );
					break;
				}
				$key = new RSA();
				if( !empty($this->destination['sftp_pass']) )
					$key->setPassword( $this->destination['sftp_pass'] );
				$key->loadKey( file_get_contents( $key_file ) );
				//use RSA key
				$sftp_login_ok = $sftp->login( $this->destination['sftp_user'], $key );
			} else {
				$sftp_login_ok = $sftp->login( $this->destination['sftp_user'], $this->destination['sftp_pass'] );
			}
			
			if( !$sftp_login_ok ){
				$message = sprintf( __( "Can't login to SFTP as user '%s'. SFTP errors: %s",
					'woocommerce-order-export' ),
					$this->destination['sftp_user'], join( "\n", $this->get_errors( $sftp ) ) );
				break;
			}	
			//2
			if ( ! $sftp->put( $this->destination['sftp_path'] . $filename, $filepath, SFTP::SOURCE_LOCAL_FILE ) ) {
				$message = sprintf( __( "Can't upload file '%s'. SFTP errors: %s", 'woocommerce-order-export' ),
					$filename, join( "\n", $this->get_errors( $sftp ) ) );
				break;
			}
			//done
			$message                     = sprintf( __( "We have uploaded file '%s' to '%s'",
				'woocommerce-order-export' ), $filename,
				$this->destination['sftp_server'] . $this->destination['sftp_path'] );
			$this->finished_successfully = true;
		} while ( 0 );

		set_error_handler( $prev_error_handler, E_USER_NOTICE );

		return $message;
	}

	public function record_user_errors( $errno, $errstr, $errfile, $errline ) {
		$this->user_errors[] = $errstr;
	}

	public function get_errors( $sftp ) {
		return $this->user_errors + $sftp->getSFTPErrors();
	}
}