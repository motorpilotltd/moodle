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
 * Utility JS for activity.
 *
 * @package    mod_tapscompletion
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */
define(['jquery'], function($) {
    return /** @alias module:mod_tapscompletion/enhance */ {
        // Public variables and functions.
        /**
         * Add event handlers for check boxes.
         *
         * @method initialise
         */
        initialise: function() {
            $('.tapscompletion-checkbox-all').change(function(){
                var that = $(this);
                var classid = that.val();
                var checkboxes = $('.tapscompletion-checkbox[value$="_' + classid + '"]');
                if (that.is(':checked')) {
                    checkboxes.prop('checked', true).change();
                } else {
                    checkboxes.prop('checked', false).change();
                }
            });
            $('.tapscompletion-checkbox[name^="staffid["').change(function(){
                var that = $(this);
                if (that.is(':checked')) {
                    that.siblings('select').removeClass('hiddenifjs').show();
                } else {
                    that.siblings('select').hide();
                }
            });
        }
    };
});
