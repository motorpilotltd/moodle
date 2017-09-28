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
 * Utility JS for course<->region mapping.
 *
 * @package    local_regions
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */
define(['jquery'], function($) {
    return /** @alias module:local_regions/regions_course */ {
        // Public variables and functions.
        /**
         * Add event handlers/ajax for course<-> region mapping.
         *
         * @method initialise
         */
        initialise: function() {
            $('select#id_regions_field_region').change(function(){
                var regionsfield = $(this);
                var globalregion = regionsfield.find('option[value="0"]');

                if (regionsfield.find('option:selected').length === 0) {
                    globalregion.prop('selected', true);
                }

                if (globalregion.is(':selected')) {
                    globalregion.siblings('option').prop('selected', false);
                    $('div#fitem_id_regions_field_subregion_all').hide();
                    $('div#fitem_id_regions_field_subregion').hide();
                } else {
                    $('div#fitem_id_regions_field_subregion_all').show();
                    $('div#fitem_id_regions_field_subregion').show();
                }

                regionsfield.find('option').each(function(){
                    var regionsfieldoption = $(this);
                    var label = regionsfieldoption.text();
                    var optgroups = $('select#id_regions_field_subregion').find('optgroup[label="' + label + '"]');
                    if (regionsfieldoption.is(':selected')) {
                        optgroups.css('display', 'block');
                    } else {
                        optgroups.css('display', 'none').find('option').each(function(){
                            $(this).prop('selected', false);
                        });
                    }
                });

                var visibleoptions = $('select#id_regions_field_subregion option:visible');
                var visibleoptgroups = $('select#id_regions_field_subregion optgroup:visible[label!=""]');
                var visiblelength = visibleoptions.length + visibleoptgroups.length;
                if (visiblelength > 1) {
                    $('div#fitem_id_regions_field_subregion_all').show();
                    $('div#fitem_id_regions_field_subregion').show();
                    $('select#id_regions_field_subregion').prop('size', Math.min(visiblelength, 10));
                    $('select#id_regions_field_subregion option').prop('selected', false);
                    var subregion = $('select#id_regions_field_subregion');
                    regionsfield.find('option:selected').each(function(){
                        subregion.find('optgroup[label="' + $(this).text() + '"]').children('option').prop('selected', true);
                    });
                } else {
                    $('div#fitem_id_regions_field_subregion_all').hide();
                    $('div#fitem_id_regions_field_subregion').hide();
                }
            });

            $('input#id_regions_field_subregion_all').change(function(){
                var that = $(this);
                $('select#id_regions_field_subregion option').prop('selected', false);
                if (that.is(':checked')) {
                    var subregion = $('select#id_regions_field_subregion');
                    $('select#id_regions_field_region option:selected').each(function(){
                        var that = $(this);
                        subregion.find('optgroup[label="' + that.text() + '"]').children('option').prop('selected', true);
                    });
                }
            });

            $('select#id_regions_field_region').change();
        }
    };
});