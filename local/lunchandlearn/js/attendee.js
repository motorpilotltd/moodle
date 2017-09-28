(function ( $ ) {

    /*
     * Check for changes on attendance, and disable/enable submit button
     */
    $('form.attendeelist').on('submit', function () {
        if($('.attendeelist .c5 input:checked').length === 0) {
            // add temporary alert
            $('#submiterror').fadeIn(500).slideDown(500, function () {
                setTimeout(function () {
                    $('#submiterror').slideUp(500);
                }, 2000);
            });
            // stop empty submit
            return false;
        } else {
            console.log('something checked');
        }
    });

    var $submitted = false;

    $('.attendee_form #id_submitbutton').on('click', function(ev) {
        $(this).attr('value', 'Submitting attendance...');
        if ($submitted) {
            return false;
        }
        $submitted = true;
    });

}( jQuery ));


