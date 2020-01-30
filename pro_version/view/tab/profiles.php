<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

include_once WOE_PRO_PLUGIN_BASEPATH . '/classes/admin/tabs/tables/class-wc-table-profiles.php';

$t_p = new WC_Table_Profiles();
?>
<div class="tabs-content">
	<?php $t_p->output(); ?>
</div>

<script>
	var $tab_name = "<?php echo $t_p->tab_name;?>";
	var woe_active_tab = "<?php echo $active_tab; ?>";

	jQuery( document ).ready( function ( $ ) {
		$( '#add_profile' ).click( function () {

		    var post_type = $('.woe-order-post-type').val();

		    document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=profiles&wc_oe=add_profile' ) ?>' + '&woe_post_type=' + post_type;
		} )

		$( '.btn-trash' ).click( function () {
			var id = $( this ).attr( 'data-id' );
			var f = confirm(
				'<?php esc_attr_e( 'Are you sure you want to DELETE this profile?', 'woocommerce-order-export' ) ?>' )
			if ( f ) {
				document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=profiles&wc_oe=delete_profile&profile_id=' ) ?>' + id + '&woe_nonce=' + woe_nonce;
			}
		} )
		$( '.btn-export' ).click( function () {
			var id = $( this ).attr( 'data-id' );
			document.location = '<?php echo admin_url( 'admin-ajax.php?action=order_exporter&method=run_one_job&profile=' ) ?>' + id + '&tab=' + woe_active_tab;
		} )
		$( '.btn-edit' ).click( function () {
			var id = $( this ).attr( 'data-id' );
			document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=profiles&wc_oe=edit_profile&profile_id=' ) ?>' + id;
		} )
		$( '.btn-clone' ).click( function () {
			var id = $( this ).attr( 'data-id' );
			var f = confirm(
				'<?php esc_attr_e( 'Are you sure you want to CLONE this profile?', 'woocommerce-order-export' ) ?>' )
			if ( f ) {
				document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=profiles&wc_oe=copy_profile&profile_id=' ) ?>' + id + '&woe_nonce=' + woe_nonce;
			}
		} )
		$( '.btn-to-scheduled' ).click( function () {
			var id = $( this ).attr( 'data-id' );
			var f = confirm( '<?php esc_attr_e( 'Are you sure you want to COPY this profile to a Scheduled job?',
				'woocommerce-order-export' ) ?>' )
			if ( f ) {
				document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=profiles&wc_oe=copy_profile_to_scheduled&profile_id=' ) ?>' + id + '&woe_nonce=' + woe_nonce;
			}
		} )
		$( '.btn-to-actions' ).click( function () {
			var id = $( this ).attr( 'data-id' );
			var f = confirm( '<?php esc_attr_e( 'Are you sure you want to COPY this profile to a Status change job?',
				'woocommerce-order-export' ) ?>' )
			if ( f ) {
				document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=profiles&wc_oe=copy_profile_to_actions&profile_id=' ) ?>' + id + '&woe_nonce=' + woe_nonce;
			}
		} )

		$( '#doaction' ).click( function () {
			var chosen_profiles = [];

			jQuery.each( $( ' table.profiles th.check-column input ' ), function ( index, elem ) {
				if ( $( elem ).prop( "checked" ) ) {
					chosen_profiles.push( $( elem ).val() );
				}
			} );

			document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=profiles&wc_oe=change_profile_statuses&chosen_profiles=' ) ?>' + chosen_profiles + '&doaction=' + $(
				'#bulk-action-selector-top' ).val() + '&woe_nonce=' + woe_nonce;
		} );

	} )
</script>