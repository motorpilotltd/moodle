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
 * Utility JS.
 *
 * @package    mod_aruphonsetybox
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */

define(['jquery', 'core/config'], function($, config) {
    return /** @alias module:mod_aruphonsetybox/main */ {
        // Public variables and functions.
        /**
         * AUtility JS.
         *
         * @method initialise
         */
        initialise: function() {
            var currentInput = null;

            $('li.aruphonestybox').on('click', 'input[type="checkbox"].tapscomplete', function () {
                currentInput = $(this);

                if (currentInput.data('locked')) {
                    return false;
                }

                $('#myModal').modal({show: true});
                return false;
            });

            var _wastext;

            var setModalButtons = function(_button, sending) {
                if (sending === true) {
                    _wastext = _button.text();
                    _button.removeClass('btn-success').addClass('btn-default');
                    _button.text('Sending...');
                } else {
                    _button.text(_wastext);
                    _button.addClass('btn-success').removeClass('btn-default');
                }
            };

            $('.modal').on('click', '.btn-success', function () {
                var _button = $(this);
                setModalButtons(_button, true);
                // Send.
                var _instance = currentInput.data('instance');
                var _course = $('body').attr('class').toString().replace(/^.*course-(\d+)\s.*$/, '$1');


                $.post(config.wwwroot + '/mod/aruphonestybox/taps.php', {
                    id:_instance,
                    course: _course,
                }).done(function (datastr) {
                    var data = $.parseJSON(datastr);
                    setModalButtons(_button, false);
                    var $mod = currentInput.parents('.aruphonestybox');
                    $mod.find('.alert-warning').fadeOut(500);
                    if (data.success) {
                        currentInput.prop('checked', true);
                        $mod.find('.alert-success').hide().removeClass('hide').fadeIn(500);
                        currentInput.parent('div').fadeOut(500);
                    } else {
                        $mod.find('.alert-danger').hide().removeClass('hide').fadeIn(500);
                        currentInput.prop('checked', false);
                    }
                    $('#myModal').modal('hide');
                })
                .fail(function () {
                    setModalButtons(_button, false);
                });

            });
        }
    };
});