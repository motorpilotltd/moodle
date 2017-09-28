$(document).ready(function(){

    var hidetrigger = $('#tapsenrol_arupadvert_hide_trigger');
    var hidebutton = $('#arupadvert_hide_button');

    /* Reponds to click triggered by arupadvert */
    hidetrigger.click(function(){
        var tohide = $('.tapsenrol_info_wrapper > h3');
        if (hidebutton.hasClass('arupadvert_hidden')) {
            tohide.slideUp();
        } else {
            tohide.slideDown();
        }
    });

    /* Link to arupadvert to trigger hiding of data */
    if (hidetrigger.data('hideadvert')) {
        hidebutton.click();
    }

    /* Prevent double form submission on enrol page */
    $('#page-mod-tapsenrol-enrol form.mform').submit(function(e){
        var that = $(this);
        if (that.data('submitted') === true) {
            e.preventDefault();
        } else {
            that.data('submitted', true);
        }
    });

    /* Email previews */
    var modallabel;
    var modalloader;
    $(document).on('click', 'a[data-toggle=modal]', function() {
        var that = $(this);
        var datatarget = that.attr('data-target');
        var datalabel = that.attr('data-label');
        modalloader = $(datatarget + ' .modal-body').html();
        modallabel = $(datatarget + ' .modal-header h3').html();
        $(datatarget + '-label').text(datalabel);
        $(datatarget + ' .modal-body').load(that.attr('href'));
        console.log($('.modal-backdrop').height());
    });

    $(document).on('click', '[data-dismiss=modal]', function () {
        /* Revert on close */
        var modal = $(this).closest('.modal');
        setTimeout(function(){
            modal.find('.modal-header h3').html(modallabel);
            modal.find('.modal-body').html(modalloader);
        }, 500);
    });

    /* Class info on manage enrolment page */
    $('.tapsenrol_manage_enrolments_class.hide').hide().removeClass('hide');
    $('#page-mod-tapsenrol-manage_enrolments').find('select.class-select').change(function(){
        that = $(this);
        details = that.closest('fieldset').find('.tapsenrol_manage_enrolments_class');
        $.ajax({
            url: M.cfg.wwwroot+'/mod/tapsenrol/class_ajax.php',
            type: 'POST',
            data: {
                classid: that.val(),
                sesskey: M.cfg.sesskey
            },
            dataType: 'json',
            success: function(outdata) {
                if (outdata.success) {
                    details.find('.classname .felement').text(outdata.classname);
                    details.find('.location .felement').text(outdata.location);
                    // In case hidden by URL passed class.
                    trainingcenter = details.find('.trainingcenter').hide().removeClass('hide');
                    if (outdata.trainingcenter === '') {
                        trainingcenter.hide();
                    } else {
                        trainingcenter.children('.felement').text(outdata.trainingcenter);
                        trainingcenter.show();
                    }
                    details.find('.date .felement').text(outdata.date);
                    details.find('.duration .felement').text(outdata.duration);
                    details.find('.cost .felement').text(outdata.cost);
                    details.find('.seatsremaining .felement').text(outdata.seatsremaining);
                    details.find('.enrolments .felement').text(outdata.enrolments);
                    details.fadeIn();
                } else {
                    details.fadeOut();
                }
            },
            error: function() {
                details.fadeOut();
            }
        });
    });
});