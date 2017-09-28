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
 * Add feedback JS.
 * 
 * @package    local_onlineappraisal
 * @copyright  2016 Motorpilot Ltd, Sonsbeekmedia
 * @author     Simon Lewis, Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */
define(['jquery', 'core/str', 'core/notification'],
       function($, str, notification) {
        return /** @alias module:local_onlineappraisal/add_feedback */ {
        /**
         * Add feedback JS.
         *
         * @method init
         */
        init: function() {
            // Submission confirmation.
            
            str.get_string('form:addfeedback:confirm', 'local_onlineappraisal').done(function(s) {
                $('input[name="_qf__apform_addfeedback"]').closest('form').submit(function(e){
                    e.preventDefault();
                    var myform = $(this);
                    var btn = myform.find("input[type=submit]:focus").attr('id');

                    if (btn == 'id_submitbutton') {
                        $('input[name=buttonclicked]').val(1);
                        notification.confirm('Submit Feedback', s, 'Ok', 'Cancel', function() {
                            myform.unbind('submit').submit();
                        });
                    }
                    if (btn == 'id_savedraft') {
                        $('input[name=buttonclicked]').val(2);
                        str.get_string('form:addfeedback:saveddraft', 'local_onlineappraisal').done(function(d) {
                            notification.alert('Save Draft', d, 'OK');
                            setTimeout( function () { 
                                myform.unbind('submit').submit();
                            }, 2000);
                        });

                    }
                });
            }).fail(notification.exception);

        }
    };
});
