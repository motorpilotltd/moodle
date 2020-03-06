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
 * Dashboard JS.
 *
 * @package    local_onlineappraisal
 * @copyright  2016 Motorpilot Ltd, Sonsbeekmedia
 * @author     Simon Lewis, Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */

 // Load vendors js via cdn with fall back
require.config({
    enforceDefine: false,
    paths: {
        'select2_4_0_8': [
            'https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.8/js/select2.min',
            M.cfg.wwwroot + '/pluginfile.php/' + M.cfg.contextid + '/local_onlineappraisal/vendor/select2_4.0.8.min'
        ]
    }
});

define(['jquery', 'core/config', 'core/str', 'core/notification', 'local_onlineappraisal/datepicker', 'select2_4_0_8'],
       function($, cfg, str, notification, dp) {

    return /** @alias module:local_onlineappraisal/index */ {
        // Public variables and functions.
        /**
         * Dashboard JS.
         *
         * @method init
         */
        init: function(args) {
            // Group select.
            $('form.admin_group .fitem_actionbuttons').hide();
            $('#id_groupid').change(function() {
                $(this).closest('form').submit();
            });

            // Filtering.
            $('form#oa-filter').removeClass('hidden');
            $('#oa-current-table tbody tr.oa-empty-filter').hide().removeClass('hidden');
            $('#oa-filter-select').change(function() {
                var self = $(this);
                var action = self.val();
                if (action === '0') {
                    // Show all.
                    $('#oa-current-table tbody tr').show();
                } else if (action === 'action') {
                    // Show those requiring action.
                    $('#oa-current-table tbody tr').hide();
                    $('#oa-current-table tbody tr.info').show();
                } else {
                    // Filter on status.
                    $('#oa-current-table tbody tr').hide();
                    $('#oa-current-table tbody tr').filter(function() {
                        if (action >= 7) {
                            return $(this).data('status') >= action;
                        }
                        return $(this).data('status') == action;
                    }).show();
                }
                if (!$('#oa-current-table tbody tr:not(".oa-empty-filter"):visible()').length) {
                    $('#oa-current-table tbody tr.oa-empty-filter').show();
                } else {
                    $('#oa-current-table tbody tr.oa-empty-filter').hide();
                }
            });

            // Marking F2F as complete.
            $('i.oa-togglef2f').css('cursor', 'pointer');
            $('.oa-togglef2f.disabled').css('cursor', 'not-allowed');
            $('.oa-togglef2f').click(function(e){
                e.preventDefault();
                // Return if already running.
                if ($(this).data('processing') || $(this).hasClass('disabled')) {
                    return;
                }
                var tr = $(this).closest('tr');
                var checkbox = tr.find('i.oa-togglef2f');
                var link = tr.find('a.oa-togglef2f');

                // Disable further clicks.
                checkbox.data('processing', true);
                link.data('processing', true);

                var spinner;
                str.get_string('admin:processingdots', 'local_onlineappraisal').done(function(s) {
                    spinner = $('<i class="fa fa-2x fa-fw fa-spinner fa-spin"></i><span class="sr-only">' + s + '</span>');
                    checkbox.hide().after(spinner);
                }).fail(notification.exception);

                var colspan = tr.find('td').length;
                var alerttr = $(
                        '<tr class="tr-alert"><td colspan="'
                        + colspan
                        + '"><div class="alert" role="alert"></div></td></tr>'
                    ).hide();
                var alert = alerttr.find('.alert');

                // Get data.
                var appraisalid = tr.data('appraisalid');

                str.get_string('error:request', 'local_onlineappraisal').done(function(s) {
                    $.ajax({
                        type: 'POST',
                        url: cfg.wwwroot + '/local/onlineappraisal/ajax.php?sesskey=' + cfg.sesskey,
                        data: {
                            c: 'index',
                            a: 'toggle_f2f_complete',
                            appraisalid: appraisalid
                        },
                        success: function(data) {
                            // Failure if object not returned.
                            if (typeof data !== 'object') {
                                data = {
                                    success: false,
                                    message: s,
                                    data: ''
                                };
                            }
                            if (data.success) {
                                alert.removeClass('alert-danger').addClass('alert-success');
                                // Change checkbox according to whether completed or not.
                                checkbox.toggleClass('fa-square-o', !data.data)
                                        .toggleClass('fa-check-square', data.data)
                                        .data('processing', false)
                                        .show();
                                // Change link text according to whether completed or not.
                                if (data.data) {
                                    link.html(link.data('notcomplete'));
                                } else {
                                    link.html(link.data('complete'));
                                }
                                link.data('processing', false);
                                spinner.remove();
                            } else {
                                alert.removeClass('alert-success').addClass('alert-danger');
                                spinner.remove();
                                checkbox.data('processing', false).show();
                                link.data('processing', false);
                            }
                            alert.html(data.message);
                            tr.next('.tr-alert').remove();
                            alerttr.insertAfter(tr)
                                .slideDown()
                                .delay(5000)
                                .slideUp(function(){
                                    $(this).remove();
                                });
                        },
                        error: function(){
                            alert.removeClass('alert-success').addClass('alert-danger');
                            alert.html(s);
                            tr.next('.tr-alert').remove();
                            alerttr.insertAfter(tr)
                                .slideDown()
                                .delay(5000)
                                .slideUp(function(){
                                    $(this).remove();
                                });
                            spinner.remove();
                            checkbox.data('processing', false).show();
                            link.data('processing', false);
                        }
                    });
                }).fail(notification.exception);
            });

            // Inline editing toggle.
            $('.oa-f2f-edit').click(function(){
                var self = $(this);
                var tr = self.closest('tr');
                var id = '#oa-f2f-date-' + tr.data('appraisalid');
                var datepicker = $(id);
                var date = datepicker.siblings('span');

                if (self.data('clicked')) {
                    datepicker.datepicker('show');
                    return;
                }
                self.data('clicked', true);

                self.siblings('.oa-f2f-save, .oa-f2f-undo').removeClass('hidden').show();
                datepicker.datepicker({
                    autoclose: true,
                    format: {
                        toDisplay: function (date, format, language) {
                            return dp.toDisplay(date, format.format, language);
                        },
                        toValue: function (date, format, language, assumeNearby) {
                            return dp.toValue(date, format.format, language, assumeNearby);
                        },
                        format: args.dateformat
                    },
                    startDate: 0,
                    todayHighlight: true,
                    // In case of fixed navbar.
                    zIndexOffset: 1030,
                    language: $('html').prop('lang').replace('-', '_')
                }).on('changeDate', function(e){
                    date.html(e.format());
                });

                if (datepicker.data('y') && datepicker.data('m') && datepicker.data('d')) {
                    // Subtract one off the month for JS Date...
                    var originaldate = new Date(datepicker.data('y'), datepicker.data('m') - 1, datepicker.data('d'));
                    datepicker.datepicker('update', originaldate);
                }

                datepicker.datepicker('show');
            });

            // Cancel inline editing.
            $('.oa-f2f-undo').click(function(){
                var self = $(this);
                var tr = self.closest('tr');
                var id = '#oa-f2f-date-' + tr.data('appraisalid');
                var datepicker = $(id);
                var date = datepicker.siblings('span');

                self.hide();
                self.siblings('.oa-f2f-save').hide();
                self.siblings('.oa-f2f-edit').data('clicked', false);

                if (datepicker.data('y') && datepicker.data('m') && datepicker.data('d')) {
                    // Subtract one off the month for JS Date...
                    var originaldate = new Date(datepicker.data('y'), datepicker.data('m') - 1, datepicker.data('d'));
                    datepicker.datepicker('update', originaldate);
                    date.html(datepicker.datepicker('getFormattedDate'));
                } else {
                    datepicker.datepicker('update', '');
                    date.html('-');
                }
                datepicker.datepicker('destroy');
            });

            // Save inline edits.
            $('.oa-f2f-save').click(function(){
                // Set up variables.
                var self = $(this);
                var tr = self.closest('tr');
                var id = '#oa-f2f-date-' + tr.data('appraisalid');
                var datepicker = $(id);
                var colspan = tr.find('td').length;
                var alerttr = $(
                        '<tr class="tr-alert"><td colspan="'
                        + colspan
                        + '"><div class="alert" role="alert"></div></td></tr>'
                    ).hide();
                var alert = alerttr.find('.alert');

                // Set saving state and disable button.
                var btn = self.html();
                self.prop('disabled', true);
                str.get_string('admin:savingdots', 'local_onlineappraisal').done(function(s) {
                    self.html('<i class="fa fa-lg fa-fw fa-spinner fa-spin"></i><span class="sr-only">' + s + '</span>');
                }).fail(notification.exception);

                // Get data.
                var appraisalid = tr.data('appraisalid');
                var f2fdate = datepicker.datepicker('getUTCDate');

                // Date error.
                if (Object.prototype.toString.call(f2fdate) !== "[object Date]") {
                    str.get_string('error:f2fdate', 'local_onlineappraisal').done(function(s) {
                        alert.removeClass('alert-success').addClass('alert-danger');
                        alert.html(s);
                        tr.next('.tr-alert').remove();
                        alerttr.insertAfter(tr)
                            .slideDown()
                            .delay(5000)
                            .slideUp(function(){
                                $(this).remove();
                            });
                        self.html(btn);
                        self.prop('disabled', false);
                        self.blur();
                    }).fail(notification.exception);
                    return;
                }

                str.get_string('error:request', 'local_onlineappraisal').done(function(s) {
                    $.ajax({
                        type: 'POST',
                        url: cfg.wwwroot + '/local/onlineappraisal/ajax.php?sesskey=' + cfg.sesskey,
                        data: {
                            c: 'index',
                            a: 'update_f2f_date',
                            appraisalid: appraisalid,
                            date: f2fdate.getTime() / 1000
                        },
                        success: function(data) {
                            // Failure if object not returned.
                            if (typeof data !== 'object') {
                                data = {
                                    success: false,
                                    message: s,
                                    data: ''
                                };
                            }
                            if (data.success) {
                                alert.removeClass('alert-danger').addClass('alert-success');
                                self.hide();
                                self.siblings('.oa-f2f-undo').hide();
                                self.siblings('.oa-f2f-edit').data('clicked', false);
                                datepicker.data('d', f2fdate.getDate());
                                datepicker.data('m', f2fdate.getMonth() + 1); // Add one to JS date for real month.
                                datepicker.data('y', f2fdate.getFullYear());
                                datepicker.datepicker('destroy');
                            } else {
                                alert.removeClass('alert-success').addClass('alert-danger');
                            }
                            alert.html(data.message);
                            tr.next('.tr-alert').remove();
                            alerttr.insertAfter(tr)
                                .slideDown()
                                .delay(5000)
                                .slideUp(function(){
                                    $(this).remove();
                                });
                        },
                        error: function(){
                            alert.removeClass('alert-success').addClass('alert-danger');
                            alert.html(s);
                            tr.next('.tr-alert').remove();
                            alerttr.insertAfter(tr)
                                .slideDown()
                                .delay(5000)
                                .slideUp(function(){
                                    $(this).remove();
                                });

                        },
                        complete: function() {
                            self.html(btn);
                            self.prop('disabled', false);
                            self.blur();
                        }
                    });
                }).fail(notification.exception);
            });

            // Toggling successionplan.
            $('i.oa-togglesuccessionplan').css('cursor', 'pointer');
            $('.oa-togglesuccessionplan.disabled').css('cursor', 'not-allowed');
            $('.oa-togglesuccessionplan').click(function(e){
                e.preventDefault();
                var self = $(this);
                // Return if already running.
                if ($(this).data('processing') || $(this).hasClass('disabled')) {
                    return;
                }
                var tr = $(this).closest('tr');

                // Disable further clicks.
                self.data('processing', true);

                var spinner;
                str.get_string('admin:processingdots', 'local_onlineappraisal').done(function(s) {
                    spinner = $('<i class="fa fa-2x fa-fw fa-spinner fa-spin"></i><span class="sr-only">' + s + '</span>');
                    self.hide().after(spinner);
                }).fail(notification.exception);

                var colspan = tr.find('td').length;
                var alerttr = $(
                        '<tr class="tr-alert"><td colspan="'
                        + colspan
                        + '"><div class="alert" role="alert"></div></td></tr>'
                    ).hide();
                var alert = alerttr.find('.alert');

                // Get data.
                var appraisalid = self.data('appraisalid');
                var confirm = self.data('confirm');
                if (typeof confirm === 'undefined') {
                    confirm = 0;
                }
                // Unset flags now.
                self.removeData('confirm');

                str.get_string('error:request', 'local_onlineappraisal').done(function(s) {
                    $.ajax({
                        type: 'POST',
                        url: cfg.wwwroot + '/local/onlineappraisal/ajax.php?sesskey=' + cfg.sesskey,
                        data: {
                            c: 'index',
                            a: 'toggle_successionplan',
                            appraisalid: appraisalid,
                            confirm: confirm
                        },
                        success: function(data) {
                            // Failure if object not returned.
                            if (typeof data !== 'object') {
                                data = {
                                    success: false,
                                    message: s,
                                    data: ''
                                };
                            }
                            if (data.success) {
                                alert.removeClass('alert-danger').addClass('alert-success');
                                // Change checkbox according to whether completed or not.
                                self.toggleClass('fa-square-o', !data.data)
                                        .toggleClass('fa-check-square', data.data)
                                        .data('processing', false)
                                        .show();
                                spinner.remove();
                            } else {
                                alert.removeClass('alert-success').addClass('alert-danger');
                                spinner.remove();
                                self.data('processing', false).show();
                            }
                            alert.html(data.message);
                            tr.next('.tr-alert').remove();
                            if (data.data === 'confirm') {
                                alerttr.insertAfter(tr)
                                    .slideDown();
                            } else {
                                alerttr.insertAfter(tr)
                                    .slideDown()
                                    .delay(5000)
                                    .slideUp(function(){
                                        $(this).remove();
                                    });
                            }
                        },
                        error: function(){
                            alert.removeClass('alert-success').addClass('alert-danger');
                            alert.html(s);
                            tr.next('.tr-alert').remove();
                            alerttr.insertAfter(tr)
                                .slideDown()
                                .delay(5000)
                                .slideUp(function(){
                                    $(this).remove();
                                });
                            spinner.remove();
                            self.data('processing', false).show();
                        }
                    });
                }).fail(notification.exception);
            });

            // Confirmation required.
            $('table').on('click', '.oa-togglesuccessionplan-confirm', function(e){
                e.preventDefault();
                var self = $(this);
                var tr = self.closest('tr');
                if (self.data('confirm')) {
                    tr.closest('table')
                        .find('.oa-togglesuccessionplan')
                        .filter(function(){
                            return $(this).data('appraisalid') === self.data('appraisalid');
                        })
                        .data('confirm', 1)
                        .click();
                }
                tr.slideUp(function(){
                    $(this).remove();
                });
            });

            // Toggling leaderplan.
            $('i.oa-toggleleaderplan').css('cursor', 'pointer');
            $('.oa-toggleleaderplan.disabled').css('cursor', 'not-allowed');
            $('.oa-toggleleaderplan').click(function(e){
                e.preventDefault();
                var self = $(this);
                // Return if already running.
                if ($(this).data('processing') || $(this).hasClass('disabled')) {
                    return;
                }
                var tr = $(this).closest('tr');

                // Disable further clicks.
                self.data('processing', true);

                var spinner;
                str.get_string('admin:processingdots', 'local_onlineappraisal').done(function(s) {
                    spinner = $('<i class="fa fa-2x fa-fw fa-spinner fa-spin"></i><span class="sr-only">' + s + '</span>');
                    self.hide().after(spinner);
                }).fail(notification.exception);

                var colspan = tr.find('td').length;
                var alerttr = $(
                        '<tr class="tr-alert"><td colspan="'
                        + colspan
                        + '"><div class="alert" role="alert"></div></td></tr>'
                    ).hide();
                var alert = alerttr.find('.alert');

                // Get data.
                var appraisalid = self.data('appraisalid');
                var confirm = self.data('confirm');
                if (typeof confirm === 'undefined') {
                    confirm = 0;
                }
                // Unset flags now.
                self.removeData('confirm');

                str.get_string('error:request', 'local_onlineappraisal').done(function(s) {
                    $.ajax({
                        type: 'POST',
                        url: cfg.wwwroot + '/local/onlineappraisal/ajax.php?sesskey=' + cfg.sesskey,
                        data: {
                            c: 'index',
                            a: 'toggle_leaderplan',
                            appraisalid: appraisalid,
                            confirm: confirm
                        },
                        success: function(data) {
                            // Failure if object not returned.
                            if (typeof data !== 'object') {
                                data = {
                                    success: false,
                                    message: s,
                                    data: ''
                                };
                            }
                            if (data.success) {
                                alert.removeClass('alert-danger').addClass('alert-success');
                                // Change checkbox according to whether completed or not.
                                self.toggleClass('fa-square-o', !data.data)
                                        .toggleClass('fa-check-square', data.data)
                                        .data('processing', false)
                                        .show();
                                spinner.remove();
                            } else {
                                alert.removeClass('alert-success').addClass('alert-danger');
                                spinner.remove();
                                self.data('processing', false).show();
                            }
                            alert.html(data.message);
                            tr.next('.tr-alert').remove();
                            if (data.data === 'confirm') {
                                alerttr.insertAfter(tr)
                                    .slideDown();
                            } else {
                                alerttr.insertAfter(tr)
                                    .slideDown()
                                    .delay(5000)
                                    .slideUp(function(){
                                        $(this).remove();
                                    });
                            }
                        },
                        error: function(){
                            alert.removeClass('alert-success').addClass('alert-danger');
                            alert.html(s);
                            tr.next('.tr-alert').remove();
                            alerttr.insertAfter(tr)
                                .slideDown()
                                .delay(5000)
                                .slideUp(function(){
                                    $(this).remove();
                                });
                            spinner.remove();
                            self.data('processing', false).show();
                        }
                    });
                }).fail(notification.exception);
            });

            // Confirmation required.
            $('table').on('click', '.oa-toggleleaderplan-confirm', function(e){
                e.preventDefault();
                var self = $(this);
                var tr = self.closest('tr');
                if (self.data('confirm')) {
                    tr.closest('table')
                        .find('.oa-toggleleaderplan')
                        .filter(function(){
                            return $(this).data('appraisalid') === self.data('appraisalid');
                        })
                        .data('confirm', 1)
                        .click();
                }
                tr.slideUp(function(){
                    $(this).remove();
                });
            });

            // Select2 initialisation.
            var searchform = $('#oa-index-search');
            searchform.removeClass('hidden');
            $('select#oa-index-search-select').select2({
                minimumInputLength: 2,
                width: '50%',
                ajax: {
                    url: cfg.wwwroot + '/local/onlineappraisal/ajax.php?sesskey=' + cfg.sesskey,
                    dataType: 'json',
                    delay: 250,
                    data: function(params) {
                        return {
                            c: 'index',
                            a: 'search_index',
                            page: searchform.find('input[name="page"]').val(),
                            q: params.term,
                            searchpage: params.page
                        };
                    },
                    processResults: function(data, params) {
                        params.page = params.page || 1;
                        var results = data.data;
                        return {
                            results: results.items,
                            pagination: {
                                more: (params.page * 25) < results.totalcount
                            }
                        };
                    },
                    cache: true
                }
            });
        }
    };
});