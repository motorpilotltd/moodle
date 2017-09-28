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
 * Utility JS adding enhancements.
 *
 * @package    local_delegatelist
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */
define(['core/config', 'jquery', 'local_delegatelist/URI'], function(config, $, URI) {
    return /** @alias module:local_delegatelist/enhance */ {
        // Public variables and functions.
        /**
         * Add enhancements.
         *
         * @method initialise
         */
        initialise: function() {
            var url = config.wwwroot + '/local/delegatelist/ajax.php';
            var reloaddelegatelist = function (params, _url) {
                $('.delegate-list-container').load(_url, params, function(){
                    $('#delegate-list-class-dates').html($('#delegate-list-class-list').data('dates'));
                    $('#delegate-list-class-duration').html($('#delegate-list-class-list').data('duration'));
                    var classlocation = $('#delegate-list-class-list').data('location');
                    $('#delegate-list-class-location').html(classlocation);
                    if (classlocation === '') {
                        $('#delegate-list-class-location').parent().addClass('hide');
                    } else {
                        $('#delegate-list-class-location').parent().removeClass('hide');
                    }
                    var classtrainingcenter = $('#delegate-list-class-list').data('trainingcenter');
                    $('#delegate-list-class-trainingcenter').html(classtrainingcenter);
                    if (classtrainingcenter === '') {
                        $('#delegate-list-class-trainingcenter').addClass('hide');
                    } else {
                        $('#delegate-list-class-trainingcenter').removeClass('hide');
                    }
                    var printbutton = $('.navigationbuttons .print');
                    printbutton.data('classid', $('#delegate-list-class-list').data('classid'));
                    var uri = URI(printbutton.attr('href'));
                    uri.removeSearch('classid');
                    uri.addSearch('classid', printbutton.data('classid'));
                    printbutton.attr('href', uri.href());
                });
            };

            // CAPTURE EVENTS.
            $('.delegate-list-container').on('click', '#delegatelistform button', function () {
                var that = $(this);
                if (that.attr('id') === 'checkall') {
                    that.closest('form').find('input[type="checkbox"]').prop('checked', true);
                } else if (that.attr('id') === 'checknone') {
                    that.closest('form').find('input[type="checkbox"]').prop('checked', false);
                } else if (that.attr('id') === 'sendemail') {
                    var emails = [];
                    that.closest('form').find('input[type="checkbox"]:checked').each(function() {
                        emails.push($(this).data('email'));
                    });
                    if (emails.length > 0) {
                        var mailto = 'mailto:' + emails.join('%3B') + '%3B';
                        $('<a id="delegate-list-mailto" class="hidden" href="'+mailto+'">&nbsp;</a>').insertAfter($(this));
                        $('#delegate-list-mailto')[0].click();
                        $('#delegate-list-mailto').remove();
                    }
                }
                return false;
            });
            // No filters if class changed.
            $('#formchangeclass').on('change', 'select', function () {
                var params = {
                    contextid: $('#formchangeclass input[name="contextid"]').val(),
                    classid: $('#formchangeclass select').val()
                };
                reloaddelegatelist(params, url);
                return false;
            });
            // Delegated event necessary for filtering.
            $('.delegate-list-container').on('change', '#formchangefilter select', function () {
                var params = {
                    contextid: $('#formchangeclass input[name="contextid"]').val(),
                    filters: {bookingstatus: $('#formchangefilter select').val()},
                    classid: $('#formchangeclass select').val()
                };
                reloaddelegatelist(params, url);
                return false;
            });

            // Sorting changes
            $('#page-local-delegatelist-index .delegate-list-container').on('click', 'th a', function () {
                var _url = $(this).attr('href').replace(/index\.php/, 'ajax.php');

                var params = {
                    contextid: $('#formchangeclass input[name="contextid"]').val(),
                    filters: {bookingstatus: $('#formchangefilter select').val()},
                    classid: $('#formchangeclass select').val()
                };
                reloaddelegatelist(params, _url);
                return false;
            });

            $('#page-local-delegatelist-print .delegate-list-container').on('click', 'th a', function () {
                var _url = $(this).attr('href').replace(/print\.php/, 'ajax.php');

                var params = {
                    contextid: $('#formchangeclass input[name="contextid"]').val(),
                    filters: {bookingstatus: $('#formchangefilter select').val()},
                    classid: $('#formchangeclass select').val(),
                    function: 'print'
                };
                reloaddelegatelist(params, _url);
                return false;
            });

            // Printing.
            $('.navigationbuttons .print').on('click', function () {
                var win = window.open($(this).attr('href'));
                win.onload = function(){ win.print(); };
                return false;
            });
            $('#page-local-delegatelist-print .close-window').on('click', function () {
                window.close();
                return false;
            });
        }
    };
});