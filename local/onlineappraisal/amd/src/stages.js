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
 * Stage processing for appraisal.
 *
 * @package    local_onlineappraisal
 * @copyright  2016 Motorpilot Ltd, Sonsbeekmedia
 * @author     Simon Lewis, Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */

define(['jquery', 'local_onlineappraisal/autosize'],
       function($, autosize) {
    return /** @alias module:local_onlineappraisal/stages */ {
        // Public variables and functions.
        /**
         * Stage processing for appraisal.
         *
         * @method init
         */
        init: function() {
            $('#oa-overview-form').hide().removeClass('hidden');
            
            $('.oa-overview-buttons button').click(function(e){
                e.preventDefault();
                var self = $(this);
                if (self.hasClass('disabled')) {
                    return;
                }
                $('.oa-overview-buttons button').hide();
                // Take advantage of fact .toggle() can accept boolean.
                $('#oa-overview-return').toggle(self.data('return') > 0);
                $('#oa-overview-submit').toggle(self.data('submit') > 0);
                $('label[for="oa-overview-comment"]').html(self.data('label'));
                $('#oa-overview-form').show();
                autosize($('#oa-overview-comment'));
                $('#oa-overview-comment').focus();
            });
            
            $('#oa-overview-cancel').click(function(e){
                e.preventDefault();
                var self = $(this);
                var form = self.closest('form');
                var comment = form.find('#oa-overview-comment');
                comment.val('');
                form.hide();
                $('.oa-overview-buttons button').show();
            });

            $('#oa-overview-return').click(function(e){
                e.preventDefault();
                var self = $(this);
                
                var form = self.closest('form');
                var comment = form.find('#oa-overview-comment');
                var formgroup = comment.closest('.form-group');
                if ($.trim(comment.val()) === '') {
                    formgroup.addClass('has-error');
                    formgroup.find('.help-block').hide().removeClass('hidden').slideDown();
                    self.blur();
                    return false;
                }
                // Necessary as button info not sent otherwise.
                form.append('<input type="hidden" name="button" value="return" />');
                form.submit();
            });
        }
    };
});