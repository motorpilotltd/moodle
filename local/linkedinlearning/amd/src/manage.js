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
 * Utility JS adding enhancements for block_arup_mylearning.
 *
 * @package    block_arup_mylearning
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */

define(['jquery', 'core/config'],
    function ($, config) {
        var saveregion = function (regionid, courseid, state) {
            $.ajax({
                url: config.wwwroot + '/local/linkedinlearning/ajax.php',
                type: 'POST',
                data: {
                    sesskey: config.sesskey,
                    action: 'setregion',
                    regionid: regionid,
                    courseid: courseid,
                    state: state
                },
                success: function () {
                    var selector = 'input[data-regionid="' + regionid + '"][data-courseid="' + courseid + '"]';
                    $(selector).removeAttr('disabled', 'disabled');

                    if (regionid == 0) {
                        var selector = 'input:not([data-regionid="0"])[data-courseid="' + courseid + '"]';
                        $(selector).prop('checked', false);
                    } else {
                        var selector = 'input[data-regionid="0"][data-courseid="' + courseid + '"]';
                        $(selector).prop('checked', false);
                    }
                }
            });
        };
        var selectall = function (regionid, state) {
            var courseids = $('input.regioncheck').map(function () {
                return $(this).data('courseid');
            });
            $.unique(courseids);

            var courseidscsv = '';
            var first = true;
            courseids.map(function () {
                if (first) {
                    courseidscsv += this;
                    first = false;
                } else {
                    courseidscsv += ',' + this;
                }
            });

            $.ajax({
                url: config.wwwroot + '/local/linkedinlearning/ajax.php',
                type: 'POST',
                data: {
                    sesskey: config.sesskey,
                    action: 'setregions',
                    regionid: regionid,
                    courseids: courseidscsv,
                    state: state
                },
                success: function () {
                    var selector = 'input.regionselectall[data-regionid="' + regionid + '"]';
                    $(selector).removeAttr('disabled', 'disabled');
                    var selector = 'input[data-regionid="' + regionid + '"]';
                    $(selector).prop('checked', state);
                }
            });
        };

        return {
            initialise: function () {
                $(document).on('change', 'input.regioncheck', function () {
                    $(this).attr('disabled', 'disabled');
                    var regionid = $(this).data('regionid');
                    var courseid = $(this).data('courseid');
                    var state = $(this).is(':checked');
                    saveregion(regionid, courseid, state);
                });

                $('input.regionselectall').prop("indeterminate", true);

                $(document).on('change', 'input.regionselectall', function () {
                    $(this).attr('disabled', 'disabled');
                    var regionid = $(this).data('regionid');
                    var state = $(this).is(':checked');
                    selectall(regionid, state);
                });
            }
        };
    });