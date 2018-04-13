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
 * View page JS.
 *
 * @package    modal_arupevidence
 * @copyright  2018 Xantico Ltd
 * @author     Aleks Daloso
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */
define(['jquery', 'core/config', 'core/str', 'core/notification', 'theme_bootstrap/bootstrap'],
    function($, cfg, str, notification) {

    return /** @alias module:mod_arupevidence/view */ {
        init: function (validityperiod, validityperiodunit) {
            if (validityperiod != '0' && validityperiod.length != 0) {
                $('input[type="submit"]').click(function(e){
                    // Preparing validy, value is counted as months
                    var vpnum = parseInt(validityperiod);
                    var vpunit = (validityperiodunit == 'y') ? 12 : 1;
                    var validity = vpnum * vpunit;
                    var self = $(this);

                    var uservalidity = 0;

                    // Completion date
                    var completion_year = $('#id_completiondate_year').val();
                    var completion_day = $('#id_completiondate_day').val();
                    var completion_month = $('#id_completiondate_month').val();
                    completion_month = parseInt(completion_month) - 1;
                    var completion_date = new Date(completion_year, completion_month, completion_day);

                    // Expity date now as default
                    var expdate = new Date();
                    var expiry_month = expdate.getMonth();
                    var expiry_year = expdate.getFullYear();
                    var expiry_day = expdate.getDate();


                    var mustendmonth = $('input[name="mustendmonth"]').val();
                    var requirevalidityperiod = $('input[name="requirevalidityperiod"]').val();
                    if (typeof requirevalidityperiod != 'undefined' && requirevalidityperiod == 1) {
                        var uservalidityperiod = parseInt($('#id_validityperiod').val());
                        var uservalidityperiodunit = ($('#id_validityperiodunit').val() == 'y') ? 12 : 1;
                        uservalidity = uservalidityperiod * uservalidityperiodunit;
                    } else {
                        // Defining expiry_date
                        if (typeof mustendmonth != 'undefined' && mustendmonth == 1) {
                            // Expiry date
                            expiry_month = $('#id_expirymonth').val();
                            expiry_month = parseInt(expiry_month) - 1;
                            expiry_year = $('#id_expiryyear').val();
                            expiry_day = new Date(new Date(expiry_year, expiry_month, 1) - 1).getDate();
                        } else {
                            expiry_month = $('#id_expirydate_month').val();
                            expiry_month = parseInt(expiry_month) - 1;
                            expiry_day = $('#id_expirydate_day').val();
                            expiry_year = $('#id_expirydate_year').val();
                        }

                        var expiry_date = new Date(expiry_year, expiry_month, expiry_day);

                        uservalidity = computedatemonthdiff(expiry_date, completion_date);
                    }

                    // Checks if expiry date is still covered by validity
                    if (uservalidity < validity) {
                        e.preventDefault(); // stop form submission
                        $('#validity-confirm-modal').modal('show');

                        $('#validity-confirm-modal').on('click', '#validity-confirm-btn', function(){
                            $(this).modal('hide');
                            self.closest('form').submit();
                        });
                    } else {
                        self.closest('form').submit();
                    }
                });
            }
        },

    };

    function  computedatemonthdiff (date1, date2) {
        var diff =(date1.getTime() - date2.getTime()) / 1000;
        diff /= (60 * 60 * 24 * 7 * 4);
        if (diff <= 0) {
            return 0;
        }
        return Math.abs(Math.round(diff));
    }
});