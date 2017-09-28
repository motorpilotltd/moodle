/**
 * Cancel exemption form
 */
$(document).on('click', '.cancelbtn', function(){
   $('.exemption-modal-wrapper').remove();
});

/**
 * Remove exemption for given user and certification
 */
$(document).on('click', '.deletebtn', function(){
    var userid = $('input[name="exemption_userid"]').val();
    var certifid = $('input[name="exemption_certifid"]').val();
    
    $.ajax({
        type: "POST",
        url: M.cfg.wwwroot + '/blocks/certification_report/ajax/exemption.php',
        dataType: "HTML",
        data: {
            action: 'deleteexmption',
            userid: userid,
            certifid: certifid
        },
        success: function (response) {
            location.reload();
        }
    });

});

/**
 * Save exemption for given user and certification
 */
$(document).on('click', '.savebtn', function(){
    var reason = $('#id_reason').val();
    var userid = $('input[name="exemption_userid"]').val();
    var certifid = $('input[name="exemption_certifid"]').val();
    var timeexpires = '';
    if(reason == ''){
        $('#id_reason').parent().find('.error').remove();
        $('#id_reason').parent().append('<span class="error">' + M.util.get_string('reasonrequired', 'block_certification_report') + '</span>');
    }else{
        if($('#id_timeexpires_enabled').is(':checked')){
            timeexpires = $('#id_timeexpires_year').val() + "-" +
                         $('#id_timeexpires_month').val() + "-" +
                         $('#id_timeexpires_day').val();
        }

        $.ajax({
            type: "POST",
            url: M.cfg.wwwroot + '/blocks/certification_report/ajax/exemption.php',
            dataType: "HTML",
            data: {
                action: 'saveexemption',
                reason: reason,
                timeexpires: timeexpires,
                userid: userid,
                certifid: certifid
            },
            success: function (response) {
                var html = '<a href="#" class="setexemption" data-userid="' + userid + '" data-certifid="' + certifid + '">' + M.util.get_string('notrequired', 'block_certification_report') + '</a>';
                $('#certif_data_' + userid + '_' + certifid).html(html);
                $('.exemption-modal-wrapper').remove();
            }
        });
    }

});

/**
 * Enable date selects
 */
$(document).on('click', '#id_cancel', function(){
    $('input[name="fullname"]').val('');
    $('input[name="region').val('');
    $('input[name="costcentre').val('');
    $('input[name="cohort').val('');
    $("#id_certifications option:selected").removeAttr("selected");
    $("#id_categories option:selected").removeAttr("selected");
});
/**
 * Enable date selects
 */
$(document).on('click', '#id_timeexpires_enabled', function(){
    var group = $(this).parent().parent();
    if($(this).is(":checked")) {
        group.find('select').removeAttr('disabled');
    }else{
        group.find('select').attr('disabled', 'disabled');
    }
});

/**
 * Print popup with exemption settings
 */
$(document).on('click', '.setexemption', function () {
    var userid = $(this).data('userid');
    var certifid = $(this).data('certifid');

    $.ajax({
        type: "GET",
        url: M.cfg.wwwroot + '/blocks/certification_report/ajax/exemption.php',
        dataType: "HTML",
        data: {
            action: 'getexemptionform',
            userid: userid,
            certifid: certifid
        },
        success: function (response) {
            /**
             * Append modal window
             */
            $('.exemption-modal-wrapper').remove();
            $('.block_certification_report_data').after(response);

            if($('#id_timeexpires_enabled').is(":checked")) {
            }else{
                $('.exemption-modal').find('select').attr('disabled', 'disabled');
            }
        }
    });
});

/**
 * Remove exemption for given user and certification
 */
$(document).on('click', '.reset-certification', function(){
    $(this).removeClass('fa-undo').addClass('fa-spinner fa-spin');
    var userid = $(this).data('userid');
    var certifid = $(this).data('certifid');

    $.ajax({
        type: "POST",
        url: M.cfg.wwwroot + '/blocks/certification_report/ajax/reset.php',
        dataType: "HTML",
        data: {
            action: 'resetcertification',
            userid: userid,
            certifid: certifid
        },
        success: function (response) {
            location.reload();
        }
    });

});