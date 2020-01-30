<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WOE_Export_Folder extends WOE_Export {

	public function run_export( $filename, $filepath, $is_last_order = true ) {
		if ( empty( $this->destination['path'] ) ) {
			$this->destination['path'] = ABSPATH;
		}

		if ( preg_match( '#\.php$#i', $filename ) ) {
			return __( "Creating PHP files is prohibited.", 'woocommerce-order-export' );
		}

		if ( ! file_exists( $this->destination['path'] ) ) {
			if ( @ ! mkdir( $this->destination['path'], 0777, true ) ) {
				return sprintf( __( "Can't create folder '%s'. Check permissions.", 'woocommerce-order-export' ),
					$this->destination['path'] );
			}
		}
		if ( ! is_writable( $this->destination['path'] ) ) {
			return sprintf( __( "Folder '%s' is not writable. Check permissions.", 'woocommerce-order-export' ),
				$this->destination['path'] );
		}

		if ( @ ! copy( $filepath, $this->destination['path'] . "/" . $filename ) ) {
			return sprintf( __( "Can't export file to '%s'. Check permissions.", 'woocommerce-order-export' ),
				$this->destination['path'] );
		}

		$this->finished_successfully = true;

		return sprintf( __( "File '%s' has been created in folder '%s'", 'woocommerce-order-export' ), $filename,
			$this->destination['path'] );
	}

}
