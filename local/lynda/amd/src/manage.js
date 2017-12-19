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
                url: config.wwwroot + '/local/lynda/ajax.php',
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
            }
        };
    });