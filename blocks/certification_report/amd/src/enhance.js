//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Utility JS adding enhancements for block_certification_report.
 *
 * @package    block_certification_report
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */

define(['jquery', 'core/config', 'core/str', 'core/notification', 'block_certification_report/select2'],
       function($, cfg, str, notification) {
    return /** @alias module:block_certification_report/enhance */ {
        // Public variables and functions.
        /**
         * Add form enhancement via select2.
         * Add exemption handling.
         *
         * @method initialise
         */
        initialise: function() {
            /**
             * Add Select2.
             */
            $('.select2').select2({
                width: '75%'
            });
            
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
                    url: cfg.wwwroot + '/blocks/certification_report/ajax/exemption.php',
                    dataType: "HTML",
                    data: {
                        action: 'deleteexmption',
                        userid: userid,
                        certifid: certifid
                    },
                    success: function () {
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
                if (reason === '') {
                    $('#id_reason').parent().find('.error').remove();
                    str.get_string('reasonrequired', 'block_certification_report').done(function(s) {
                        $('#id_reason').parent().append(
                            '<span class="error">' + s + '</span>'
                        );
                    }).fail(notification.exception);
                } else {
                    if ($('#id_timeexpires_enabled').is(':checked')) {
                        timeexpires = $('#id_timeexpires_year').val() + "-" +
                                      $('#id_timeexpires_month').val() + "-" +
                                      $('#id_timeexpires_day').val();
                    }

                    $.ajax({
                        type: "POST",
                        url: cfg.wwwroot + '/blocks/certification_report/ajax/exemption.php',
                        dataType: "HTML",
                        data: {
                            action: 'saveexemption',
                            reason: reason,
                            timeexpires: timeexpires,
                            userid: userid,
                            certifid: certifid
                        },
                        success: function () {
                            str.get_string('notrequired', 'block_certification_report').done(function(s) {
                                var html = '<a href="#" class="setexemption" data-userid="' +
                                    userid +
                                    '" data-certifid="' +
                                    certifid + '">' +
                                    s +
                                    '</a>';
                                $('#certif_data_' + userid + '_' + certifid).html(html);
                                $('.exemption-modal-wrapper').remove();
                            }).fail(notification.exception);
                        }
                    });
                }
            });

            /**
             * Clear form.
             */
            $(document).on('click', '#id_cancel', function(e){
                e.preventDefault();
                // Clear Select2 inputs.
                $('.select2, .select2-match').val(null).trigger('change');
                //
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
                    url: cfg.wwwroot + '/blocks/certification_report/ajax/exemption.php',
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

                        if (!$('#id_timeexpires_enabled').is(":checked")) {
                            $('.exemption-modal').find('select').attr('disabled', 'disabled');
                        }
                    }
                });
            });

            /**
             * Remove exemption for given user and certification
             */
            $(document).on('click', '.reset-certification', function() {
                $(this).removeClass('fa-undo').addClass('fa-spinner fa-spin');
                var userid = $(this).data('userid');
                var certifid = $(this).data('certifid');

                $.ajax({
                    type: "POST",
                    url: cfg.wwwroot + '/blocks/certification_report/ajax/reset.php',
                    dataType: "HTML",
                    data: {
                        action: 'resetcertification',
                        userid: userid,
                        certifid: certifid
                    },
                    success: function () {
                        location.reload();
                    }
                });
            });

            var modalloader;
            $(document).on('click', 'a[data-toggle=modal]', function() {
                /* Extra functionality, normal event will still trigger and load data, etc. */
                var datatarget = $(this).data('target');
                var datalabel = $(this).data('label');
                modalloader = $(datatarget + ' .modal-body').html();
                $(datatarget + '-label').text(datalabel);
                $(datatarget + ' .modal-body').load($(this).data('url'));
            });

            $(document).on('hidden.bs.modal', '.modal', function () {
                /* Revert on close */
                $(this).data('modal', null);
                $('#info-modal-label').empty();
                $(this).find('.modal-body').html(modalloader);
            });
        }
    };
});