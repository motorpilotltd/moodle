// This file is part of Moodle - http://moodle.org/
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
 * View page JS.
 * 
 * @package    local_onlineappraisal
 * @copyright  2016 Motorpilot Ltd, Sonsbeekmedia
 * @author     Simon Lewis, Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */
define(['jquery', 'core/config', 'core/str', 'core/notification', 'theme_bootstrap/bootstrap'],
       function($, cfg, str, notification) {

    return /** @alias module:local_onlineappraisal/view */ {
        init: function(appraisalid, view, page, statusid) {
            if (page === 'userinfo' && (view === 'appraisee' || view === 'appraiser') && statusid < 5) {
                var classes, graderefresh, jobtitlerefresh;
                var grade = $('#id_grade');
                var jobtitle = $('#id_jobtitle');
                str.get_strings([
                        {key : 'form:userinfo:refresh', component : 'local_onlineappraisal'},
                        {key : 'form:userinfo:refresh:tooltip', component : 'local_onlineappraisal'}
                    ]).done(function(s) {
                        // Inject ability to update fields from datahub.
                        classes = 'oa-refresh-datahub fa fa-2x fa-refresh m-l-10 pull-left';
                        graderefresh = $('<i class="' + classes + '"><span class="sr-only">' + s[0] + '</span></i>');
                        graderefresh.css('cursor', 'pointer').prop('title', s[1]).data({
                            appraisalid: appraisalid,
                            view: view
                        });
                        jobtitlerefresh = graderefresh.clone(true); // Want data cloned.
                        grade.addClass('pull-left').after(graderefresh);
                        jobtitle.addClass('pull-left').after(jobtitlerefresh);
                        graderefresh.data('field', 'grade');
                        jobtitlerefresh.data('field', 'jobtitle');
                        $('.oa-refresh-datahub').tooltip();
                    }
                ).fail(notification.exception);

                // Processing.
                $('.felement').on('click', '.oa-refresh-datahub', function(){
                    // Set up variables.
                    var self = $(this);

                    // Return if already running.
                    if (self.data('processing')) {
                        return;
                    }

                    // Disable further clicks.
                    self.data('processing', true);
                    self.addClass('fa-spin');

                    var alert = $('<div class="alert clear" role="alert"></div><').hide();

                    str.get_string('error:request', 'local_onlineappraisal').done(function(s) {
                        $.ajax({
                            type: 'POST',
                            url: cfg.wwwroot + '/local/onlineappraisal/ajax.php?sesskey=' + cfg.sesskey,
                            data: {
                                c: 'appraisal',
                                a: 'userinfo_datahub_update',
                                appraisalid: self.data('appraisalid'),
                                view: self.data('view'),
                                field: self.data('field')
                            },
                            success: function(data) {
                                // Failure if object not returned.
                                if (typeof data !== 'object') {
                                    data = {
                                        success: false,
                                        message: s,
                                        data: ''
                                    };
                                }
                                if (data.success) {
                                    alert.removeClass('alert-danger').addClass('alert-success');
                                    // Update fields.
                                    if (typeof data.data.grade !== 'undefined') {
                                        grade.val(data.data.grade);
                                    }
                                    if (typeof data.data.jobtitle !== 'undefined') {
                                        jobtitle.val(data.data.jobtitle);
                                    }
                                } else {
                                    alert.removeClass('alert-success').addClass('alert-danger');
                                }
                                self.data('processing', false).removeClass('fa-spin');
                                alert.html(data.message);
                                self.next('.alert').remove();
                                alert.insertAfter(self)
                                    .slideDown()
                                    .delay(5000)
                                    .slideUp(function(){
                                        $(this).remove();
                                    });
                            },
                            error: function(){
                                alert.removeClass('alert-success').addClass('alert-danger');
                                alert.html(s);
                                self.next('.alert').remove();
                                alert.insertAfter(self)
                                    .slideDown()
                                    .delay(5000)
                                    .slideUp(function(){
                                        $(this).remove();
                                    });
                                self.data('processing', false).removeClass('fa-spin');
                            }
                        });
                    }).fail(notification.exception);
                });
            }

            if ($('#oa-save-nag-modal').length) {
                // Want to nag after 15 mins and check before trying to save.
                setTimeout(function() {
                    $('#oa-save-nag-modal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                }, 900000);

                $('.oa-save-nag-modal-save').click(function(){
                    $('#oa-save-nag-modal').modal('hide');
                    window.scrollTo(0, $('#id_submitbutton').offset().top);
                    $(this).closest('form').find('#id_submitbutton').click();
                });

                $('input[type="submit"]').click(function(){
                    // Need to ensure clicked button is set.
                    var self = $(this);
                    var form = self.closest('form');
                    form.data('clicked-name', self.prop('name'));
                    form.data('clicked-value', self.prop('value'));
                });

                str.get_string('error:sessioncheck', 'local_onlineappraisal').done(function(s) {
                    $('.oa-save-session-check').submit(function(e){
                        var self = $(this);

                        // Don't worry if cancelling.
                        if (self.data('clicked-name') === 'cancelbutton') {
                            var input = $('<input type="hidden" />')
                                .prop('name', self.data('clicked-name'))
                                .prop('value', self.data('clicked-value'));
                            input.appendTo(self);
                            return;
                        }

                        // Should we continue with form submission.
                        var submitform = false;

                        // Set up variables.
                        var buttonar = $(this).find('#fgroup_id_buttonar');
                        var buttons = buttonar.find('input');
                        var alert = $('<div class="alert m-l-15" role="alert"></div>').hide();

                        // Disable button.
                        buttons.prop('disabled', true);

                        $.ajax({
                            // Don't want it to be async so we can capture result before continuing.
                            async: false,
                            type: 'POST',
                            url: cfg.wwwroot + '/local/onlineappraisal/ajax.php?sesskey=' + cfg.sesskey,
                            data: {
                                c: 'appraisal',
                                a: 'check_session'
                            },
                            success: function(data) {
                                // Failure if object not returned.
                                if (typeof data !== 'object') {
                                    data = {
                                        success: false,
                                        message: s,
                                        data: ''
                                    };
                                }
                                if (data.success) {
                                    // OK to submit form.
                                    submitform = true;
                                    // Ensure submit button data added.
                                    var input = $('<input type="hidden" />')
                                        .prop('name', self.data('clicked-name'))
                                        .prop('value', self.data('clicked-value'));
                                    input.appendTo(self);
                                } else {
                                    alert.removeClass('alert-success').addClass('alert-danger');
                                    alert.html(s);
                                    buttonar.find('.alert').remove();
                                    alert.appendTo(buttonar).slideDown();
                                    buttons.prop('disabled', false);
                                    buttons.blur();
                                }

                            },
                            error: function(){
                                alert.removeClass('alert-success').addClass('alert-danger');
                                alert.html(s);
                                buttonar.find('.alert').remove();
                                alert.appendTo(buttonar).slideDown();
                                buttons.prop('disabled', false);
                                buttons.blur();
                            }
                        });

                        if (!submitform) {
                            // Stop submission.
                            e.preventDefault();
                        }
                    });
                }).fail(notification.exception);
            }
        }
    };
});
