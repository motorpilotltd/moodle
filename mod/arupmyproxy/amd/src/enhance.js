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
 * Utility JS for activity.
 *
 * @package    mod_arupmyproxy
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */
define(['jquery'], function($) {
    return /** @alias module:mod_arupmyproxy/enhance */ {
        // Public variables and functions.
        /**
         * Add event handlers for hiding arupmyproxy.
         *
         * @method initialise
         */
        initialise: function() {
            var button = $('.arupmyproxy-wrapper-button');
            button.click(function(){
                var that = $(this);
                if (that.hasClass('arupmyproxy-wrapper-hidden')) {
                    that.removeClass('arupmyproxy-wrapper-hidden');
                    that.siblings('.arupmyproxy-wrapper').slideDown();
                    that.html(that.data('visible'));
                } else {
                    that.addClass('arupmyproxy-wrapper-hidden');
                    that.siblings('.arupmyproxy-wrapper').slideUp();
                    that.html(that.data('hidden'));
                }
            });
        }
    };
});
