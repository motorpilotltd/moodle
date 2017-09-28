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
 * checkin related functions.
 *
 * @package    local_onlineappraisal
 * @copyright  2016 Motorpilot Ltd, Sonsbeekmedia
 * @author     Simon Lewis, Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */

define(['jquery', 'core/config', 'core/str', 'core/templates', 'core/notification', 'local_onlineappraisal/autosize'],
       function($, cfg, str, templates, notification, autosize) {
    return /** @alias module:local_onlineappraisal/checkin */ {
        // Public variables and functions.
        /**
         * checkin functions.
         *
         * @method init
         */
        init: function() {
            // Show more functionality.

            // Adding checkin to appraisal.
            autosize($('#oa-checkin-input'));
            $('#oa-checkin-submit').click(function(e){
                e.preventDefault();
                var self = $(this);
                var alert = $('.oa-checkins-alert');
                var form = self.closest('form');
                var checkin = form.find('#oa-checkin-input');
                var formgroup = checkin.closest('.form-group');
                if ($.trim(checkin.val()) === '') {
                    formgroup.addClass('has-error');
                    formgroup.find('.help-block').hide().removeClass('hidden').slideDown();
                    self.blur();
                    return false;
                }

                // Disable button.
                var btn = self.html();
                self.prop('disabled', true);
                str.get_string('comment:addingdots', 'local_onlineappraisal').done(function(s) {
                    self.html('<i class="fa fa-lg fa-fw fa-spinner fa-spin"></i><span class="sr-only">' + s + '</span>');
                }).fail(notification.exception);

                formgroup.removeClass('has-error');
                formgroup.find('.help-block').hide();
                str.get_string('error:request', 'local_onlineappraisal').done(function(s) {
                    $.ajax({
                        type: 'POST',
                        url: cfg.wwwroot + '/local/onlineappraisal/ajax.php?sesskey=' + cfg.sesskey,
                        data: form.serializeArray(),
                        success: function(data){
                            // Failure if object not returned.
                            if (typeof data !== 'object') {
                                data = {
                                    success: false,
                                    message: s,
                                    data: ''
                                };
                            }
                            
                            alert.hide().removeClass('hidden');
                            if (data.success) {
                                str.get_string('form:add', 'local_onlineappraisal').done(function(s) {
                                    $('#oa-checkin-submit').html(s);
                                });
                                checkin.val('');
                                autosize.update(checkin);
                                alert.removeClass('alert-danger').addClass('alert-success');
                                templates.render('local_onlineappraisal/checkin', data.data).done(function(html) {
                                    $('.oa-checkins ul').prepend(html);
                                }).fail(notification.exception);
                                $('#checkinid').val('-1');
                            } else {
                                alert.removeClass('alert-success').addClass('alert-danger');
                            }
                            alert.find('.alert-message').html(data.message);
                            alert.slideDown().delay(5000).slideUp(function(){
                                alert.find('.alert-message').html('');
                            });
                        },
                        error: function(){
                            self.html(btn);
                            alert.hide().removeClass('hidden');
                            alert.removeClass('alert-success').addClass('alert-danger');
                            alert.find('.alert-message').html(s);
                            alert.slideDown().delay(5000).slideUp(function(){
                                alert.find('.alert-message').html('');
                            });
                        },
                        complete: function() {
                            self.prop('disabled', false);
                            self.blur();
                        }
                    });
                }).fail(notification.exception);
            });
        }
    };
});