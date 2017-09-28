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
 * Utility JS for mapcourses form.
 *
 * @package    local_regions
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */
define(['jquery'], function($) {
    return /** @alias module:local_regions/mapcourses */ {
        // Public variables and functions.
        /**
         * Add utilites for mapcourses form.
         *
         * @method initialise
         */
        initialise: function() {
            $('input.region-checkbox').change(function(){
                var that = $(this);
                if (that.is(':checked')) {
                    that.parents('tr').find('input.subregion-checkbox').attr('checked', true);
                } else {
                    that.parents('tr').find('input.subregion-checkbox').attr('checked', false);
                }
            });
            $('input.subregion-checkbox').change(function(){
                var that = $(this);
                if (that.is(':checked')) {
                    that.parents('tr').find('input.region-checkbox').attr('checked', true);
                } else if (that.parents('tr').find('input.subregion-checkbox:checked').length === 0) {
                    that.parents('tr').find('input.region-checkbox').attr('checked', false);
                }
            });
            $('a.regions-selectall-row').click(function(){
                $(this).parents('tr').find('input.region-checkbox').attr('checked', true);
                $(this).parents('tr').find('input.subregion-checkbox').attr('checked', true);
                return false;
            });
            $('a.region-selectall-column').click(function(){
                $('input.region-checkbox').attr('checked', true);
                return false;
            });
            $('a.subregion-selectall-column').click(function(){
                var column = /subregion-column-(\w+)/.exec(this.className);
                $('.' + column[0] + ' input.subregion-checkbox').attr('checked', true);
                return false;
            });
        }
    };
});