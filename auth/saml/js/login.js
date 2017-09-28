var samlregexp = new RegExp('@arup\.com$', 'i');
var samltimeout;

$(function() {
    $('#username').on('change blur', function(){
        that = $(this);
        if (samlregexp.test(that.val())) {
            $('#form-field-password').hide();
            $('#password').prop('disabled', true);
            $('#loginbtn').prop('disabled', true);
            $('#redirect-message').hide().removeClass('hide').show();
            wantsurl = $('#wantsurl').val();
            window.location.href = M.cfg.wwwroot + '/auth/saml/index.php?wantsurl=' + wantsurl;
        }
    });
});