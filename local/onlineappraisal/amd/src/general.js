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
 * General JS used across many pages.
 *
 * @package    local_onlineappraisal
 * @copyright  2016 Motorpilot Ltd, Sonsbeekmedia
 * @author     Simon Lewis, Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */

define(['jquery', 'core/config', 'core/str', 'core/notification'], function($, cfg, str, notification) {
    return /** @alias module:local_onlineappraisal/general */ {
        // Public variables and functions.
        /**
         * Genral JS.
         *
         * @method init
         */
        init: function() {
            // Hijack logo link (normally to home page).
            $('a.navbar-brand').click(function(e) {
                e.preventDefault();
                window.location = cfg.wwwroot + '/local/onlineappraisal/index.php';
            });

            // Automatically dismiss alerts with buttons after 10s.
            $('div.alert:has(button)').delay(10000).slideUp();

            // Confirm form cancellation.
            var strs = [
                { key: 'form:confirm:cancel:title', component: 'local_onlineappraisal'},
                { key: 'form:confirm:cancel:question', component: 'local_onlineappraisal'},
                { key: 'form:confirm:cancel:yes', component: 'local_onlineappraisal'},
                { key: 'form:confirm:cancel:no', component: 'local_onlineappraisal'},
                { key: 'form:cancel', component: 'local_onlineappraisal'}
            ];
            str.get_strings(strs).done(function(s) {
                $('#id_cancelbutton').click(function(e){
                e.preventDefault();
                var self = $(this);
                var form = self.closest('form');
                    notification.confirm(s[0], s[1], s[2], s[3],
                        function(){
                            // Avoid regular leaving page notification.
                            M.core_formchangechecker.set_form_submitted();
                            var input = $('<input type="hidden" name="cancelbutton" value="' + s[4] + '" />');
                            form.append(input).submit();
                        }
                    );
                });
            }).fail(notification.exception);
        }
    };
});