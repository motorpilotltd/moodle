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
                $('input[type="submit"][name="submitbutton"]').click(function(e){
                    // Preparing validity, value is counted as months
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
                    var isvalid = true;
                    if (typeof requirevalidityperiod != 'undefined' && requirevalidityperiod == 1) {
                        var uservalidityperiod = parseInt($('#id_validityperiod').val());
                        var uservalidityperiodunit = ($('#id_validityperiodunit').val() == 'y') ? 12 : 1;
                        uservalidity = uservalidityperiod * uservalidityperiodunit;
                        if (uservalidity < validity) {
                            isvalid = false;
                        }
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

                        var expectedexpiry = validitydateinfo(completion_date, vpnum, validityperiodunit);

                        if (expiry_date > expectedexpiry['minexpiry'] && expiry_date < expectedexpiry['maxexpiry'] ) {
                            isvalid = true;
                        } else {
                            isvalid = false;
                        }

                    }

                    if (!isvalid && expiry_date > completion_date ) {
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

    /**
     * Get the validity dates
     *
     * @param completionDate
     * @param validitynum
     * @param validtyunit
     * @param min bool
     * @returns
     */
    function validitydateinfo(completionDate, validitynum, validtyunit, minmax) {
        // Return undefiend if first argument isn't a Date object
        if (!(completionDate instanceof Date)) {
            return(undefined);
        }
        var validityinfo = [];
        var newDate = '';
        switch(validtyunit) {
            case 'm':
                // Get current date
                var oldDate = completionDate.getDate();

                // Increment months (handles year rollover)
                var newDate = new Date(completionDate);

                if (typeof minmax !== 'undefined' && minmax == true) {
                    // getting the min validity
                    newDate.setMonth(completionDate.getMonth() - validitynum);
                } else {
                    newDate.setMonth(completionDate.getMonth() + validitynum);
                }

                if (newDate.getDate() != oldDate) {
                    newDate.setDate(0);
                }

                // Handle Leap year
                if (completionDate.getDate() == 29 && !isLeapYear(newDate.getFullYear())) {
                    newDate.setMonth(1);
                    newDate.setDate(28);
                }
                validityinfo['expirydate'] = newDate;
                break;
            case 'y':
                // Increment years
                var newDate = new Date(completionDate);
                newDate.setFullYear(completionDate.getFullYear() + validitynum);

                // Handle Leap year
                if (completionDate.getDate() == 29 && !isLeapYear(newDate.getFullYear())) {
                    newDate.setMonth(1);
                    newDate.setDate(28);
                }
                validityinfo['expirydate'] = newDate;
                break;
            default:
                var newDate = new Date(completionDate);
                newDate.setTime(completionDate.getTime() + validitynum);
                validityinfo['expirydate'] = newDate;
        }

        if (typeof validityinfo['expirydate'] !== "undefined" && typeof minmax == "undefined") {
            validityinfo['minexpiry'] = validitydateinfo(validityinfo['expirydate'], 1, 'm', true);
            validityinfo['maxexpiry'] = validitydateinfo(validityinfo['expirydate'], 1, 'm', false);

            return validityinfo;
        } else {
            return newDate;
        }
    }

    function isLeapYear(year) {
        return (((year % 4 === 0) && (year % 100 !== 0)) || (year % 400 === 0));
    }

    function  computedatemonthdiff (date1, date2) {
        var diff =(date1.getTime() - date2.getTime()) / 1000;
        diff /= (60 * 60 * 24 * 7 * 4);
        if (diff <= 0) {
            return 0;
        }
        return Math.abs(Math.round(diff));
    }
});