jQuery( document ).ready( function ( $ ) {

	$( "#copy-to-profiles" ).click( function () {

		if ( ! woe_validate_export() ) {
			return false;
		}

		var data = 'json=' + woe_make_json_var( $( '#export_job_settings' ) )

		data = data + "&action=order_exporter&method=save_settings&mode=" + settings_form.EXPORT_PROFILE + "&id=" + '&woe_nonce=' + settings_form.woe_nonce + '&tab=' + settings_form.woe_active_tab;

		$.post( ajaxurl, data, function ( response ) {
			document.location = settings_form.copy_to_profiles_url + '&profile_id=' + response.id;
		}, "json" );

		return false;
	} );

	jQuery( "#from_status, #to_status" ).select2_i18n( {multiple: true} );

	jQuery( "#settings_title" ).focus();

} );