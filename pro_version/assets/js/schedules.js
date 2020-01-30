jQuery( document ).ready( function ( $ ) {

	$( '.wc_oe-select-interval' ).on( 'change', function () {
		$( '#custom_interval' ).toggle( $( this ).val() == 'custom' );
	} ).trigger( 'change' );

	$( '.wc-oe-schedule-type' ).on( 'change', function () {

		$( '.d-scheduled-block' ).addClass( 'disabled' );
		$( 'input, select', $( '.d-scheduled-block' ) ).attr( 'disabled', true );

		$( this ).siblings( '.d-scheduled-block' ).removeClass( 'disabled' );
		$( 'input, select', $( this ).siblings( '.d-scheduled-block' ) ).attr( 'disabled', false );
	} );

	$( '.wc-oe-schedule-type:checked' ).trigger('change');

	$( '#d-schedule-3 .btn-add' ).click( function ( e ) {

		var times = $( 'input[name="settings[schedule][times]"]' ).val();
		var weekday = $( '#d-schedule-3 .wc_oe-select-weekday' ).val();
		var time = $( '#d-schedule-3 .wc_oe-select-time' ).val();

		if ( times.indexOf( weekday + ' ' + time ) != - 1 ) {
			return;
		}

		var data = [];
		if ( times != '' ) {
			data = times.split( ',' ).map( function ( time ) {
				var arr = time.split( ' ' );
				return {weekday: arr[0], time: arr[1]};
			} );
		}

		data.push( {weekday: weekday, time: time} );

		var weekdays = {
			'Sun': 1,
			'Mon': 2,
			'Tue': 3,
			'Wed': 4,
			'Thu': 5,
			'Fri': 6,
			'Sat': 7,
		};

		data.sort( function ( a, b ) {
			if ( weekdays[a.weekday] == weekdays[b.weekday] ) {
				return new Date( '1970/01/01 ' + a.time ) - new Date( '1970/01/01 ' + b.time );
			} else {
				return weekdays[a.weekday] - weekdays[b.weekday];
			}
		} );

		var html = data.map( function ( elem ) {
			var weekday = settings_form.day_names[elem.weekday];
			return '<div class="time"><span class="btn-delete">×</span>'
			       + weekday + ' ' + elem.time + '</div>';
		} ).join( '' );

		times = data.map( function ( elem ) {
			return elem.weekday + ' ' + elem.time;
		} ).join();

		$( '#d-schedule-3 .input-times' ).html( html );
		$( '#d-schedule-3 .btn-delete' ).click( shedule3_time_delete );

		$( 'input[name="settings[schedule][times]"]' ).val( times );
	} );

	$( '#d-schedule-3 .input-times' ).ready( function () {

		var times = $( 'input[name="settings[schedule][times]"]' ).val();
		if ( ! times || times == '' ) {
			return;
		}
		var data = times.split( ',' );
		var html = data.map( function ( elem ) {
			var x = elem.split( ' ' );
			var weekday = settings_form.day_names[x[0]] + ' ' + x[1];
			return '<div class="time"><span class="btn-delete">×</span>' + weekday + '</div>';
		} ).join( '' );
		$( '#d-schedule-3 .input-times' ).html( html );
		$( '#d-schedule-3 .btn-delete' ).click( shedule3_time_delete );
	} );

	function shedule3_time_delete( e ) {
		var index = $( this ).parent().index();
		var data = $( 'input[name="settings[schedule][times]"]' ).val().split( ',' );
		data.splice( index, 1 );
		$( 'input[name="settings[schedule][times]"]' ).val( data.join() );
		$( this ).parent().remove();
	}


	$( '#d-schedule-4 .datetimes-date' ).datepicker( {
		dateFormat: 'yy-mm-dd',
		constrainInput: false,
		minDate: 0,
	} );

	$( '#d-schedule-4 .btn-add' ).click( function ( e ) {

		var times = $( 'input[name="settings[schedule][date_times]"]' ).val();
		var date = $( '#d-schedule-4 .datetimes-date' ).val();
		var time = $( '#d-schedule-4 .wc_oe-select-time' ).val();

		if ( times.indexOf( date + ' ' + time ) !== - 1 ) {
			return;
		}

		var data = [];
		if ( times !== '' ) {
			data = times.split( ',' ).map( function ( time ) {
				var arr = time.split( ' ' );
				return {date: arr[0], time: arr[1]};
			} );
		}

		data.push( {date: date, time: time} );

		data.sort( function ( a, b ) {
			return new Date( a.date + ' ' + a.time ) - new Date( b.date + ' ' + b.time );
		} );

		var html = data.map( function ( elem ) {
			return '<div class="time"><span class="btn-delete">×</span>'
			       + elem.date + ' ' + elem.time + '</div>';
		} ).join( '' );

		times = data.map( function ( elem ) {
			return elem.date + ' ' + elem.time;
		} ).join();

		$( '#d-schedule-4 .input-date-times' ).html( html );
		$( '#d-schedule-4 .btn-delete' ).click( shedule4_time_delete );

		$( 'input[name="settings[schedule][date_times]"]' ).val( times );
	} );

	$( '#d-schedule-4 .input-date-times' ).ready( function () {

		var times = $( 'input[name="settings[schedule][date_times]"]' ).val();

		if ( ! times || times == '' ) {
			return;
		}

		var data = times.split( ',' );

		var html = data.map( function ( elem ) {
			return '<div class="time"><span class="btn-delete">×</span>' + elem + '</div>';
		} ).join( '' );

		$( '#d-schedule-4 .input-date-times' ).html( html );
		$( '#d-schedule-4 .btn-delete' ).click( shedule4_time_delete );
	} );

	function shedule4_time_delete( e ) {
		var index = $( this ).parent().index();
		var data = $( 'input[name="settings[schedule][date_times]"]' ).val().split( ',' );
		data.splice( index, 1 );
		$( 'input[name="settings[schedule][date_times]"]' ).val( data.join() );
		$( this ).parent().remove();
	}

} );