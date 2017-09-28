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
 * Bootstrap Datepicker Formatting JS.
 * 
 * @package    local_onlineappraisal
 * @copyright  2016 Motorpilot Ltd, Sonsbeekmedia
 * @author     Simon Lewis, Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */
define(['jquery', 'local_onlineappraisal/bootstrap-datepicker', 'local_onlineappraisal/bootstrap-datepicker-languages'],
       function($) {

    var formatmappings = [
        // First some entities to stop them being converted by datepicker.
        {find: 'd', replace: '&#100;'},
        {find: 'D', replace: '&#68;'},
        {find: 'm', replace: '&#109;'},
        {find: 'M', replace: '&#77;'},
        {find: 'y', replace: '&#121;'},
        {find: 'Y', replace: '&#89;'},
        // Now the format conversions (accounting for entities set above).
        {find: '%e', replace: 'd'}, // Numeric date, no leading zero.
        {find: '%&#100;', replace: 'dd'}, // Numeric date, leading zero.
        {find: '%a', replace: 'D'}, // Abbreviated day.
        {find: '%A', replace: 'DD'}, // Full day.
        // '' => 'm' // Numeric month, no leading zero, doesn't exist.
        {find: '%&#109;', replace: 'mm'}, // Numeric month, leading zero.
        {find: '%b', replace: 'M'}, // Abbreviated month.
        {find: '%B', replace: 'MM'}, // Full month.
        {find: '%&#121;', replace: 'yy'}, // 2-digit year.
        {find: '%&#89;', replace: 'yyyy'} // 4-digit year.
    ];

    var displaymappings = [
        // Convert entities back.
        {find: '&#100;', replace: 'd'},
        {find: '&#68;', replace: 'D'},
        {find: '&#109;', replace: 'm'},
        {find: '&#77;', replace: 'M'},
        {find: '&#121;', replace: 'y'},
        {find: '&#89;', replace: 'Y'},
    ];

    var escapeRegExp = function(string) {
        return string.replace(/[\-\[\]{}()*+?.,\\\^$|#\s]/g, '\\$&');
    };

    var multiReplace = function(string, mappings) {
        var re;
        $.each(mappings, function(i, val){
            re = new RegExp(escapeRegExp(val.find), 'g');
            string = string.replace(re, val.replace);
        });
        return string;
    };

    return /** @alias module:local_onlineappraisal/bootstrap-datepicker-format */ {
        toDisplay: function (date, format, language) {
            // Map format strftime => datepicker style.
            format = multiReplace(format, formatmappings);
            // Pass mapped format to main function.
            var display = $.fn.datepicker.DPGlobal.formatDate(date, format, language);
            // Clean up for display.
            return multiReplace(display, displaymappings);
        },

        toValue: function (date, format, language, assumeNearby) {
            // Non-strftime format parameters to clean up from format and date.
            var remove = format.split(/%.?/);
            $.each(remove, function(i, val){
                if (val === '') {
                    return;
                }
                var re = new RegExp(escapeRegExp(val), 'g');
                date = date.replace(re, ' ');
                format = format.replace(re, ' ');
            });
            // Map format strftime => datepicker style.
            format = multiReplace(format, formatmappings);
            // Pass cleaned up date and format to main function.
            return $.fn.datepicker.DPGlobal.parseDate(date, format, language, assumeNearby);
        }
    };
});
