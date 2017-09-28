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
 * Utility JS for user<->region mapping.
 *
 * @package    local_regions
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */
define(['jquery', 'core/config'], function($, config) {
    return /** @alias module:local_regions/regions_user */ {
        // Public variables and functions.
        /**
         * Add event handlers/ajax for user<-> region mapping.
         *
         * @method initialise
         */
        initialise: function() {
            $('select#id_regions_field_region').change(function(){
                var subregionid = $('select#id_regions_field_subregion option:selected').val();
                $.ajax({
                    type: "POST",
                    url: config.wwwroot + "/local/regions/ajax_regions_user.php",
                    data: {
                        regionid: $(this).find('option:selected').val()
                    },
                    success: function(html) {
                        $('select#id_regions_field_subregion').html(html);
                        $('select#id_regions_field_subregion option[value="' + subregionid + '"]').prop('selected', true);
                        if ($('select#id_regions_field_subregion option').length === 1) {
                            $('#fitem_id_regions_field_subregion').hide();
                        } else {
                            $('#fitem_id_regions_field_subregion').show();
                        }
                    }
                });
            });

            $('select#id_regions_field_region').change();
        }
    };
});