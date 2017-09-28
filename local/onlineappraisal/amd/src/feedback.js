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
 * Feedback JS.
 * 
 * @package    local_onlineappraisal
 * @copyright  2016 Motorpilot Ltd, Sonsbeekmedia
 * @author     Simon Lewis, Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */
define(['jquery', 'core/config'], function($, cfg) { 
    return /** @alias module:local_onlineappraisal/feedback */ {
        
        replacestrings: function() {
            var appraisalid = $("[name=appraisalid]").val();
            var view = $("[name=view]").val();
            var processedlangs = [];
            if (appraisalid) {
                $('#emailmsg .language').each(function() {
                    var lang = $(this).prop('class').match(/language-(\w*)/);
                    if (processedlangs[lang[1]] === true) {
                        // Continue.
                        return true;
                    }
                    processedlangs[lang[1]] = true;
                    $.ajax({
                        type: 'POST',
                        url: cfg.wwwroot + '/local/onlineappraisal/ajax.php?sesskey=' + cfg.sesskey,
                        data: {
                            c: 'feedback',
                            a: 'get_placeholders',
                            appraisalid: appraisalid,
                            view: view,
                            // Avoid 'lang' as param as can trigger Moodle language change.
                            fblang: lang[1]
                        },
                        success: function(data) {
                            var returndata = data.data;
                            $('#emailmsg .' + lang[0] + ' .placeholder').each(function() {
                                var placeholder = $(this);
                                var variable = placeholder.html().replace('{{', '').replace('}}', '');
                                if (returndata[variable]) {
                                    variable = returndata[variable];
                                }
                                placeholder.text(variable);
                                $(this).addClass('visible');
                            });
                        }
                    });
                });
            }
            $('#id_language').on('change', function() {
                var input = $(this);
                var lang = input.val();
                var visiblelang = 'language-' + lang;
                $('#emailmsg .language').each(function() {
                    var langdiv = $(this);
                    if (langdiv.hasClass(visiblelang)) {
                        langdiv.removeClass('hidden');
                    } else {
                        langdiv.addClass('hidden');
                    }
                });
            });

            $('#id_firstname').on('input', function() {
                var input = $(this);
                $('.bind_firstname').each( function() {
                    $(this).text(' ' + input.val());
                });
                if (input.val() !== "" && $('#id_lastname').val() !== "") {
                    $('#editmsg').removeClass('disabled');
                    $('#editmsg').tooltip('destroy');
                } else {
                    $('#editmsg').addClass('disabled');
                }
            });
            $('#id_lastname').on('input', function() {
                var input = $(this);
                $('.bind_lastname').each( function() {
                    $(this).text(' ' + input.val());
                });
                if (input.val() !== "" && $('#id_firstname').val() !== "") {
                    $('#editmsg').removeClass('disabled');
                    $('#editmsg').tooltip('destroy');
                } else {
                    $('#editmsg').addClass('disabled');
                }
            });
            // Removed when Firstname and Lastname are entered.
            $('#editmsg').addClass('disabled');
            $('#editmsg').tooltip();

            $('#editmsg').on('click', function() {
                if (!$(this).hasClass('disabled')) {
                    $('#emailmsg .ignoreoncopy').remove();
                    var lang = $('#id_language').val();
                    var visiblelang = '.language-' + lang;
                    $("#emailmsg p").each(function(){
                        $(this).replaceWith( $( this ).text() + "\n" );
                    });
                    var emailtextstart = $('#emailtextstart '+visiblelang).text();
                    var emailtextend = $('#emailtextend '+visiblelang).text();
                    $('#emailmsg').addClass('hidden');
                    $('#customemailmsg').removeClass('hidden');
                    $('#hascustomemail').val(1);
                    $('#id_customemailmsg').val(emailtextstart + emailtextend);
                }

            });
        }
    };
});
