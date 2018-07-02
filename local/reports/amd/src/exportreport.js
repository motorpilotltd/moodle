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
 * Reports JS.
 *
 * @package    local_reports
 * @copyright  2017 Motorpilot Ltd, Sonsbeekmedia
 * @author     Simon Lewis, Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */

define(['jquery', 'core/config', 'core/str', 'core/notification', 'core/log'], function($, cfg, str, notification, log ) {
    return /** @alias module:local_reports/exportreport */ {
        // Public variables and functions.
        /**
         * Dashboard JS.
         *
         * @method init
         */
        init: function(page) {
            $('#exportreport').click(function(e){
                e.preventDefault();
                // Return if already running.
                if ($(this).data('processing') || $(this).hasClass('disabled')) {
                    return;
                }

                $('#exportmodal').modal();

                str.get_string('error:request', 'local_onlineappraisal').done(function(s) {
                    $.ajax({
                        type: 'POST',
                        url: cfg.wwwroot + '/local/reports/ajax.php?sesskey=' + cfg.sesskey,
                        data: {
                            page: page
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
                                $('#downloadlinkcontainer').removeClass('hidden');
                                $('#downloadlink').attr('href', data.data.url);
                                $('#downloadlink').html(data.data.filename);
                                $('#closedownloadmodal').removeClass('disabled');
                            } else {
                                log.debug('nosuccess');
                            }
                        },
                        error: function(){
                            log.debug('error in ajax exportreports');
                        }
                    });
                }).fail(notification.exception);
            });
        }
    };
});
