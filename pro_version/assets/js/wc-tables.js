jQuery( document ).ready( function ( $ ) {

	$( "#the-list" ).sortable( {handle: '.woe-row-sort-handle'} );

	$start_reorder_button = $( '#start_reorder' );
	$apply_reorder_button = $( '#apply_reorder' );
	$cancel_reorder_button = $( '#cancel_reorder' );
	$start_reorder_button.click( function ( e ) {

		$order_ids = $( "#the-list" ).sortable( "toArray", {attribute: 'data-job_id'} );

		$start_reorder_button.hide();
		$apply_reorder_button.show();
		$cancel_reorder_button.show();

		$( '.check-column .cb_part' ).hide();
		$( '.check-column .order_part' ).show();
	} );

	$apply_reorder_button.click( function ( e ) {

		$start_reorder_button.show();
		$apply_reorder_button.hide();
		$cancel_reorder_button.hide();

		$( '.check-column .cb_part' ).show();
		$( '.check-column .order_part' ).hide();

		jQuery.ajax( {
			url: ajaxurl,
			data: {
				'action': "order_exporter",
				'method': 'reorder_jobs',
				'new_jobs_order': $( "#the-list" ).sortable( "toArray", {attribute: 'data-job_id'} ),
				'tab_name': $tab_name,
				woe_nonce: woe_nonce,
				tab: woe_active_tab,
			},
			error: function ( response ) {
			},
			dataType: 'json',
			type: 'POST',
			success: function () {

			}
		} );

	} );

	$cancel_reorder_button.click( function ( e ) {

		$start_reorder_button.show();
		$apply_reorder_button.hide();
		$cancel_reorder_button.hide();

		$( '.check-column .cb_part' ).show();
		$( '.check-column .order_part' ).hide();

		$( $order_ids ).each( function ( $key, $job_id ) {
			$element = $( '[data-job_id="' + $job_id + '"' ).detach();
			$( "#the-list" ).append( $element );
		} );
	} );

	$( '.check-column .order_part' ).hide();

} );

