(function ( $ ) {
    var iscancelclick = false;
    $('#lunchandlearnform').on('click','#id_cancel', function() {
        iscancelclick = true;
    });
    // Hand over form submission to modal so stop default event.
    $('input[type="submit"]').filter(function() {
        return $(this).data('toggle') === 'modal';
    }).closest('form').submit(function(e){
        var modalsubmit = $(this).data('modal-submit');
        if (iscancelclick === false && (typeof modalsubmit === 'undefined' || modalsubmit === false)) {
            e.preventDefault();
        }
    });

    // Set resend invites state and submit form.
    $('#myModal').on('click', '.btn', function () {
        if ($(this).attr('id')==='saveandsendbutton') {
            $('#lunchandlearnform input[name="resendinvites"]').val(1);
        } else {
            $('#lunchandlearnform input[name="resendinvites"]').val(0);
        }
        $('#lunchandlearnform').data('modal-submit', true).submit();
    });

}(jQuery));