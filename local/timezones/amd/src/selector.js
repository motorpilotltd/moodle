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
 * Selector JS for setting timezones.
 *
 * @package    local_timezones
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */
require.config({
    paths: {
        "moment": M.cfg.wwwroot + "/local/timezones/vendor/moment/moment.min",
        "moment-timezone": M.cfg.wwwroot + "/local/timezones/vendor/moment/moment-timezone-with-data-2010-2020.min",
    },
    config: {
        moment: {
            noGlobal: true
        }
    }
});
define(['jquery', 'moment-timezone'], function($, moment) {
    return /** @alias module:local_timezones/selector */ {
        // Public variables and functions.
        /**
         * Add timezone selector.
         *
         * @method initialise
         */
        initialise: function() {
            $('#timezonelist').on('click', 'a.timezone', function (ev) {
                ev.stopPropagation();
                $.post($(this).attr('href'), function () {
                    location.reload(); // We do this in order to refresh any rendered times.
                });
                $('#timezonelist').fadeOut();
                return false;
             });

             $('#timezoneselect').on('click', function (ev) {
                 ev.stopPropagation();
                 $('#timezonelist').fadeIn();
             });

             $('html').on('click', function () {
                 $('#timezonelist').fadeOut(50);
             });

             var zone = $('#timezoneselect .clock').data('tz');
             if (typeof zone !== 'string' || moment.tz.zone(zone) === null) {
                 zone = 'Europe/London';
             }
             setInterval(function (){
                 $('#timezoneselect .clock').text(moment.tz(zone).format('H:mm (z)'));
             }, 500);
        }
    };
});