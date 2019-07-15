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

define(['jquery'], function($) {
    return {
        /**
         * Add enhancements.
         *
         * @method initialise
         */
        initialise: function() {
            var updatebut = $('#id_updatearupdefaultcourse');
            var arupdefaultselect = $('#id_arupdefaultcourse');

            updatebut.addClass('hidden');
            arupdefaultselect.on('change', function() {
                updatebut.trigger( "click" );
            });

            var methodologyselect = $('select#id_arupmeta_methodology');
            var formatselect = $('select#id_format');
            var formatupdatebut = $('#id_updatecourseformat');

            methodologyselect.on('change', function() {
                if (methodologyselect.val() == '40' && formatselect.val() == 'topics') {
                    formatselect.val('aruponepage');
                    formatupdatebut.trigger( "click" );
                }
            });
        }
    };
});