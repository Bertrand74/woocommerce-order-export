function woe_init_datetime_picker($el, defaults) {
    if ( ! $el.length ) {
        return;
    }

    var TIME_DELIMITER = ":";

    $el.hide();

    var datetime = $el.val().split(" ");
    var date = "";
    var hours = "";
    var minutes = "";
    var seconds = "";
    if ( typeof defaults['hours'] !== 'undefined') {
        hours = defaults['hours'];
    }
    if ( typeof defaults['minutes'] !== 'undefined') {
        minutes = defaults['minutes'];
    }
    if ( typeof defaults['seconds'] !== 'undefined') {
        seconds = defaults['seconds'];
    }


    if (typeof datetime[0] !== 'undefined') {
        date = datetime[0];
    }
    if (typeof datetime[1] !== 'undefined') {
        var hours_minutes = datetime[1].split(TIME_DELIMITER);
        if (typeof hours_minutes[0] !== 'undefined' && typeof hours_minutes[1] !== 'undefined') {
            hours = hours_minutes[0];
            minutes = hours_minutes[1];
        }
        // if (typeof hours_minutes[2] !== 'undefined') {
        //     seconds = hours_minutes[2];
        // }
    }

    var $el_date = jQuery('<input type="text" class="date">');
    $el_date.val(date);
    $el_date.datepicker({
        dateFormat: 'yy-mm-dd',
        constrainInput: false
    });

    var $el_hm_delimiter = jQuery('<div class="delimiter">' + TIME_DELIMITER + '</div>');

    var $el_hours = jQuery('<input type="number" class="hours" min="0" max="23" step="1" >');
    var $el_minutes = jQuery('<input type="number" class="minutes" min="0" max="59" step="1" >');
    var $el_seconds = jQuery('<input type="number" class="seconds" min="0" max="59" step="1" >');

    $el_hours.val(hours);
    $el_minutes.val(minutes);
    $el_seconds.val(seconds).hide();

    var $el_date_time = jQuery('<div class="datetime-picker-control"></div>');
    var $el_upper = jQuery('<div class="upper"></div>');
    var $el_footer = jQuery('<div class="footer"></div>');
    $el_upper.append($el_date);
    $el_footer.append($el_hours);
    $el_footer.append($el_hm_delimiter.clone());
    $el_footer.append($el_minutes);
    // $el_footer.append($el_hm_delimiter.clone());
    $el_footer.append($el_seconds);
    $el_date_time.append($el_upper);
    $el_date_time.append($el_footer);
    $el.after($el_date_time);

    $el_date_time.find('input').change(function () {
        var date = $el_date.val();

        var to_str = function (time_piece) {
            return time_piece ? (time_piece > 10 ? '' + time_piece : '0' + time_piece) : "00";
        };

        var hours = $el_hours.val();
        var minutes = $el_minutes.val();
        var seconds = $el_seconds.val();

        hours = hours ? parseInt(hours) : 0;
        minutes = minutes ? parseInt(minutes) : 0;
        seconds = seconds ? parseInt(seconds) : 0;

        if (date) {
            if (hours >= 24 || hours < 0) {
                hours = 0;
            }

            if (minutes >= 60 || minutes < 0) {
                minutes = 0;
            }

            if (seconds >= 60 || seconds < 0) {
                seconds = 0;
            }

            hours = to_str(hours);
            minutes = to_str(minutes);
            seconds = to_str(seconds);

            $el.attr('value', date + " " + hours + TIME_DELIMITER + minutes + TIME_DELIMITER + seconds);
        } else {
            $el.attr('value', "")
        }

        $el_date.val(date);
        $el_hours.val(hours);
        $el_minutes.val(minutes);
        $el_seconds.val(seconds);
    });
}