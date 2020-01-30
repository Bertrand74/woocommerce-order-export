<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
include_once WOE_PRO_PLUGIN_BASEPATH . '/classes/admin/tabs/tables/class-wc-table-order-actions.php';

$table = new WC_Table_Order_Actions();
?>
<div class="tabs-content">
	<?php $table->output(); ?>
</div>

<script>
	var $tab_name = "<?php echo $table->tab_name;?>";

	jQuery( document ).ready( function ( $ ) {
		$( '[data-action=add-order-action]' ).click( function () {

		    var post_type = $('.woe-order-post-type').val();

		    document.location = '<?php echo admin_url( "admin.php?page=wc-order-export&tab={$tab}&wc_oe=add_action" ) ?>' + '&woe_post_type=' + post_type;
		} );
		$( '[data-action=edit-order-action]' ).click( function () {
			var id = $( this ).attr( 'data-id' );
			document.location = '<?php echo admin_url( "admin.php?page=wc-order-export&tab={$tab}&wc_oe=edit_action&action_id=" ) ?>' + id;
		} );
		$( '[data-action=clone-order-action]' ).click( function () {
			var id = $( this ).attr( 'data-id' );
			var f = confirm(
				'<?php esc_attr_e( 'Are you sure you want to CLONE this job?', 'woocommerce-order-export' ) ?>' )
			if ( f ) {
				document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=order_actions&wc_oe=copy_action&action_id=' ) ?>' + id + '&woe_nonce=' + woe_nonce;
			}
		} );
		$( '[data-action=delete-order-action]' ).click( function () {
			var id = $( this ).attr( 'data-id' );
			var f = confirm( 'Are you sure you want to DELETE this job?' );
			if ( f ) {
				document.location = '<?php echo admin_url( "admin.php?page=wc-order-export&tab={$tab}&wc_oe=delete&action_id=" ) ?>' + id + '&woe_nonce=' + woe_nonce;
			}
		} );
		$( '#doaction' ).click( function () {
			var chosen_order_actions = [];

			jQuery.each( $( ' table th.check-column input ' ), function ( index, elem ) {
				if ( $( elem ).prop( "checked" ) ) {
					chosen_order_actions.push( $( elem ).val() );
				}
			} );

			document.location = '<?php echo admin_url( 'admin.php?page=wc-order-export&tab=order_actions&wc_oe=change_statuses&chosen_order_actions=' ) ?>' + chosen_order_actions + '&doaction=' + $(
				'#bulk-action-selector-top' ).val() + '&woe_nonce=' + woe_nonce;
		} );
	} )
</script>