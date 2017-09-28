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
 * Utility JS for editing page.
 *
 * @package    mod_arupadvert
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */
define(['jquery'], function($) {
    return /** @alias module:mod_arupadvert/edit */ {
        // Public variables and functions.
        /**
         * Add event handlers for showing 'type' fields.
         *
         * @method initialise
         */
        initialise: function() {
            $('select#id_datatype').change(function(){
                var selected = $(this).val();
                $('fieldset[id^="arupadvertdatatype"]').hide();
                $('fieldset#arupadvertdatatype_' + selected).hide();
            });
        }
    };
});
