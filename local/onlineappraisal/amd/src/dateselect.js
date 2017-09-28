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
 * Date selector JS.
 * 
 * @package    local_onlineappraisal
 * @copyright  2016 Motorpilot Ltd, Sonsbeekmedia
 * @author     Simon Lewis, Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */
define(['jquery', 'local_onlineappraisal/datepicker'], function($, dp) {

    return /** @alias module:local_onlineappraisal/dateselect */ {
        init: function(datepickerfield, hiddenfield, dateformat) {
            var datepicker = $(datepickerfield);

            datepicker.prop('readonly', true).css({
                backgroundColor: '#ffffff',
                cursor: 'pointer'
            }).next('.input-group-addon').css({
                cursor: 'pointer'
            });

            datepicker.datepicker({
                autoclose: true,
                format: {
                    toDisplay: function (date, format, language) {
                        return dp.toDisplay(date, format.format, language);
                    },
                    toValue: function (date, format, language, assumeNearby) {
                        return dp.toValue(date, format.format, language, assumeNearby);
                    },
                    format: dateformat
                },
                todayHighlight: true,
                zIndexOffset: 1030,
                language: $('html').prop('lang').replace('-', '_')
            }).on('changeDate', function() {
                var duedate = datepicker.datepicker('getUTCDate');
                var timestamp = duedate.getTime() / 1000;
                $("[name="+hiddenfield+"]").val(timestamp);
            });

            $('.dateselect .input-group-addon').on('click', function (){
                datepicker.focus();
            });                
        }
    };
});
