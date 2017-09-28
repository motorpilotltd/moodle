$(document).ready(function(){
    /* Prevent double form submission */
    $('.path-mod-arupapplication form.mform').submit(function(e){
        var that = $(this);
        if (that.data('submitted') === true) {
            e.preventDefault();
        } else {
            that.data('submitted', true);
        }
    });

});