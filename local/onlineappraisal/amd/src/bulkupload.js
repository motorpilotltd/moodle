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
 * Bulk Upload JS.
 *
 * @package    local_onlineappraisal
 * @copyright  2016 Motorpilot Ltd, Sonsbeekmedia
 * @author     Simon Lewis, Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */

define(['jquery', 'core/config', 'core/str', 'core/notification', 'local_onlineappraisal/datepicker'],
       function($, cfg, str, notification, dp) {

    return /** @alias module:local_onlineappraisal/admin */ {
        // Public variables and functions.
        /**
         * Admin JS.
         *
         * @method init
         */
        init: function(args) {
            // Datepicker.
            var initdatepicker = $('#oa-bulkupload-duedate');
            initdatepicker.prop('readonly', true).css({
                backgroundColor: '#ffffff',
                cursor: 'pointer'
            });
            initdatepicker.datepicker({
                autoclose: true,
                format: {
                    toDisplay: function (date, format, language) {
                        return dp.toDisplay(date, format.format, language);
                    },
                    toValue: function (date, format, language, assumeNearby) {
                        return dp.toValue(date, format.format, language, assumeNearby);
                    },
                    format: args.dateformat
                },
                todayHighlight: true,
                startDate: new Date(),
                // In case of fixed navbar.
                zIndexOffset: 1030,
                language: $('html').prop('lang').replace('-', '_')
            });
            initdatepicker.next('.input-group-addon').on('click', function (){
                initdatepicker.focus();
            });

            // Step Two Processing.
            $('#oa-bulkupload-steptwo-submit').click(function(){
                // Set up variables.
                var self = $(this);

                // Disable button.
                var btn = self.html();
                self.prop('disabled', true);
                str.get_string('admin:processingdots', 'local_onlineappraisal').done(function(s) {
                    self.html('<i class="fa fa-lg fa-fw fa-spinner fa-spin"></i><span class="sr-only">' + s + '</span>');
                }).fail(notification.exception);
                
                // Get due date.
                var datepicker = $('#oa-bulkupload-duedate');
                var duedate = datepicker.datepicker('getUTCDate');

                // No due date.
                if (Object.prototype.toString.call(duedate) !== "[object Date]") {
                    var headeroffset = $('#header').outerHeight(true);
                    datepicker.closest('.form-group').addClass('has-error').find('.help-block').hide().removeClass('hidden').show();
                    $('html, body').animate({
                        scrollTop: datepicker.offset().top - headeroffset
                    }, 500);
                    self.html(btn);
                    self.prop('disabled', false);
                    self.blur();
                    datepicker.datepicker().on('changeDate', function(){
                        datepicker.closest('.form-group').removeClass('has-error').find('.help-block').hide();
                        $('html, body').animate({
                            scrollTop: self.offset().top - headeroffset
                        }, 500);
                    });
                    return false;
                }

                // Add the due date to the query string.
                var href = self.prop('href') + '&duedate=' + (duedate.getTime() / 1000);
                self.prop('href', href);
                return true;
            });

            // General processing.
            $('.oa-bulkupload-generic-submit').click(function(){
                // Set up variables.
                var self = $(this);

                // Disable button.
                self.prop('disabled', true);
                str.get_string('admin:processingdots', 'local_onlineappraisal').done(function(s) {
                    self.html('<i class="fa fa-lg fa-fw fa-spinner fa-spin"></i><span class="sr-only">' + s + '</span>');
                }).fail(notification.exception);

                return true;
            });
        }
    };
});