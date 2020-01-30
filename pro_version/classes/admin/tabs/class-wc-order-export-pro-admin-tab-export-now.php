<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

class WC_Order_Export_Pro_Admin_Tab_Export_Now extends WC_Order_Export_Admin_Tab_Export_Now {
	use WC_Order_Export_Pro_Admin_Tab_Abstract;

	public function render() {

		add_action( 'woe_settings_form_view_save_as_profile', array( $this, 'add_save_as_profile_to_view' ), 10, 1 );

		parent::render();
	}

	public function add_save_as_profile_to_view() {
		$this->render_template( 'save-as-profile', array(), $this->get_template_path() );
	}

}