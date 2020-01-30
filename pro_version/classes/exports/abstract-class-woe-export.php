<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

abstract class WOE_Export {
	var $destination;
	var $finished_successfully = false;

	public function __construct( $destination ) {
		$this->destination = $destination;
	}

	//must be imlemented
	abstract public function run_export( $filename, $filepath, $is_last_order = true );


	public function get_num_of_retries() {
		return 1;
	}
}
