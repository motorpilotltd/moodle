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
 * @package    mod_tapsenrol
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */
define(['jquery'], function($) {
    return /** @alias module:mod_tapsenrol/editclass */ {
        // Public variables and functions.
        /**
         * Add event handlers for check boxes.
         *
         * @method initialise
         */
        initialise: function() {
            var updatebut = $('#id_updatetypestatus');
            updatebut.addClass('hidden');

            var arupclasstypeselect = $('#id_classtype');
            arupclasstypeselect.on('change', function() {
                updatebut.trigger( "click" );
            });

            var arupclassstatusselect = $('#id_classstatus');
            arupclassstatusselect.on('change', function() {
                updatebut.trigger( "click" );
            });
        }
    };
});
