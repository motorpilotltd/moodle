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
 * Warn the user/restrict access.
 *
 * @package    block_arup_scormmonitor
 * @copyright  2017 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */

define(['jquery', 'core/str', 'core/notification'], function($, str, notification) {
    return /** @alias module:block_arup_scormmonitor/scormmonitor */ {
        // Public variables and functions.
        /**
         * Restrict/Warn
         *
         * @method initialise
         */
        init: function(args) {
            var viewform = $('form#scormviewform');
            if (args.hide) {
                str.get_string('launch:hide', 'block_arup_scormmonitor').done(function(s) {
                    viewform.after(s).hide();
                }).fail(notification.exception);
            } else if (args.warn) {
                str.get_string('launch:warn', 'block_arup_scormmonitor').done(function(s) {
                    viewform.before(s);
                }).fail(notification.exception);
            }
        }
    };
});