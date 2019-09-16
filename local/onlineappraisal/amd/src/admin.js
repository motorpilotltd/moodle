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
 * Admin JS.
 *
 * @package    local_onlineappraisal
 * @copyright  2016 Motorpilot Ltd, Sonsbeekmedia
 * @author     Simon Lewis, Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */

define(['jquery', 'core/config', 'core/str', 'core/notification', 'local_onlineappraisal/datepicker'],
       function($, cfg, str, notification, dp) {

    /**
     * Input width calculator.
     *
     * @method getInputWidth
     * @private
     * @param {Object} input The input element.
     * @return {number}log
     */
    var getInputWidth = function(input) {
        var tmp = $('<span></span>')
            .css({
                position: 'absolute',
                left: '-99999px',
                whiteSpace: 'pre',
                width: 'auto'
            })
            .addClass(input.prop('class'))
            .html(input.val()).insertAfter(input);
        var width = tmp.width();
        tmp.remove();
        return width;
    };

    return /** @alias module:local_onlineappraisal/admin */ {
        // Public variables and functions.
        /**
         * Admin JS.
         *
         * @method init
         */
        init: function(args) {
            // Group select.
            $('form.admin_group .fitem_actionbuttons').hide();
            $('#id_groupid').change(function() {
                $(this).closest('form').submit();
            });
            $('#id_cohortid').change(function() {
                $(this).closest('form').submit();
            });
            
            // Select all toggle.
            $('#oa-appraisal-select-all').click(function(){
                // Toggle visible.
                $('.oa-appraisal-select').filter(':visible').prop('checked', $(this).prop('checked'));
                // Always de-select hidden.
                $('.oa-appraisal-select').filter(':hidden').prop('checked', false);
            });

            // Filtering (in progress table only).
            $('form#oa-filter').removeClass('hidden');
            $('#oa-inprogress-table tbody tr.oa-empty-filter').hide().removeClass('hidden');
            $('#oa-filter-select').change(function() {
                var self = $(this);
                var action = self.val();
                if (action === '0') {
                    // Show all.
                    $('#oa-inprogress-table tbody tr').show();
                } else if (action === 'action') {
                    // Show those requiring action.
                    $('#oa-inprogress-table tbody tr').hide();
                    $('#oa-inprogress-table tbody tr.info').show();
                } else {
                    // Filter on status.
                    $('#oa-inprogress-table tbody tr').hide();
                    $('#oa-inprogress-table tbody tr').filter(function() {
                        if (action >= 7) {
                            return $(this).data('status') >= action;
                        }
                        return $(this).data('status') == action;
                    }).show();
                }
                if (!$('#oa-inprogress-table tbody tr:not(".oa-empty-filter"):visible()').length) {
                    $('#oa-inprogress-table tbody tr.oa-empty-filter').show();
                } else {
                    $('#oa-inprogress-table tbody tr.oa-empty-filter').hide();
                }
                // Reset selection on filtering.
                $('.oa-appraisal-select').prop('checked', false);
                $('#oa-appraisal-select-all').prop('checked', false);
            });

            
            // Datepicker - 'allstaff' and 'initialise' pages.
            var initdatepicker = $('#oa-init-duedate');
            initdatepicker.prop('readonly', true).css({
                backgroundColor: '#ffffff',
                cursor: 'pointer'
            });
            initdatepicker.datepicker({
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
                todayHighlight: true,
                startDate: new Date(),
                // In case of fixed navbar.
                zIndexOffset: 1030,
                language: $('html').prop('lang').replace('-', '_')
            }).on('changeDate', function() {
                initdatepicker.prev('input[name=duedate]').val(initdatepicker.datepicker('getUTCDate').getTime() / 1000);
            });
            if (initdatepicker.data('y') && initdatepicker.data('m') && initdatepicker.data('d')) {
                // Subtract one off the month for JS Date...
                var originaldate = new Date(initdatepicker.data('y'), initdatepicker.data('m') - 1, initdatepicker.data('d'));
                initdatepicker.datepicker('update', originaldate);
            }
            initdatepicker.next('.input-group-addon').on('click', function (){
                initdatepicker.focus();
            });


            // Appraisal cycle page

            // Cached button strings
            var strings = [
                {key: 'form:update', component: 'local_onlineappraisal'},
                {key: 'form:add', component: 'local_onlineappraisal'}
            ];
            var strformupdate, strformadd;
            str.get_strings(strings).done(function(s) {
                strformupdate = s[0];
                strformadd    = s[1];
            }).fail(notification.exception);

            //  Availablefrom datepicker
            var initavailablefrom = $('#oa-init-availablefrom');
            // Datepicker configuration
            initavailablefrom.datepicker({
                autoclose: true,
                startDate: new Date(),
                format: {
                    toDisplay: function (date, format, language) {
                        return dp.toDisplay(date, format.format, language);
                    },
                    toValue: function (date, format, language, assumeNearby) {
                        return dp.toValue(date, format.format, language, assumeNearby);
                    },
                    format: args.dateformat
                },
                // In case of fixed navbar.
                zIndexOffset: 1030,
                defaultDate: new Date()
            }).datepicker('setDate', '0');

            initavailablefrom.on('changeDate', function() {
                    initavailablefrom.prev('input[name=availablefrom]')
                        .val(initavailablefrom.datepicker('getUTCDate').getTime() / 1000);
                });

            initavailablefrom.next('.input-group-addon').on('click', function (){
                initavailablefrom.focus();
            });

            // Appraisal cycle form add/update
            var appraisalcycleform = $('form#oa-form-appraisalcycle');
            appraisalcycleform.submit(function(e){
                // Set up variables.
                var self = $(this);
                var btnsubmit = self.find('#submitcycle');

                if (self.data('submit')) {
                    return;
                }

                e.preventDefault();
                // Set processing state and disable button.
                btnsubmit.prop('disabled', true);
                btnsubmit.html('<i class="fa fa-lg fa-fw fa-spinner fa-spin"></i><span class="sr-only">' +
                    self.data('button') +
                    '</span>');
                self.data('submit', true);
                self.submit();
            });


            // Process edit appraisal cycle
            $('.edit-cycle').click(function(){
                var self = $(this);

                appraisalcycleform.find('#inputname').val(self.data('name'));
                appraisalcycleform.find('#cancelcycle').css('display','inline');
                appraisalcycleform.find('#submitcycle').html(strformupdate);
                appraisalcycleform.find('input[name=action]').val('update');
                appraisalcycleform.find('input[name=id]').val(self.data('id'));

                initavailablefrom.datepicker('setDate', self.data('availablefromdate'));

            });
            // Cancel editing appraisal cycle
            appraisalcycleform.find('#cancelcycle').click(function(e){
                e.preventDefault();
                var self = $(this);
                initavailablefrom.datepicker('setDate', '0');
                appraisalcycleform.find('#inputname').val('');
                appraisalcycleform.find('input[name=action]').val('add');
                appraisalcycleform.find('input[name=id]').val('');
                appraisalcycleform.find('#submitcycle').html(strformadd);
                self.css('display','none');
            });
            // Start/Lock/Update appraisal cycle.
            $('form.oa-form-allstaff').submit(function(e){
                // Set up variables.
                var self = $(this);
                var btn = self.find('button');

                if (self.data('submit')) {
                    return;
                }

                e.preventDefault();

                // Set processing state and disable button.
                var btnhtml = btn.html();
                btn.prop('disabled', true);
                btn.html('<i class="fa fa-lg fa-fw fa-spinner fa-spin"></i><span class="sr-only">' +
                         self.data('button') +
                         '</span>');

                // Get due date.
                var datepicker = $('#oa-init-duedate');
                var duedate = datepicker.datepicker('getUTCDate');

                // No due date.
                if (Object.prototype.toString.call(duedate) !== "[object Date]") {
                    var headeroffset = $('#header').outerHeight(true);
                    datepicker.closest('.form-group').addClass('has-error').find('.help-block').hide().removeClass('hidden').show();
                    $('html, body').animate({
                        scrollTop: datepicker.offset().top - headeroffset
                    }, 500);
                    btn.html(btnhtml);
                    btn.prop('disabled', false);
                    btn.blur();
                    datepicker.datepicker().on('changeDate', function(){
                        datepicker.closest('.form-group').removeClass('has-error').find('.help-block').hide();
                        $('html, body').animate({
                            scrollTop: btn.offset().top - headeroffset
                        }, 500);
                    });
                    return;
                }

                if (btn.data('confirm')) {
                    var result = window.confirm(btn.data('confirm'));
                    if (!result) {
                        btn.html(btnhtml);
                        btn.prop('disabled', false);
                        btn.blur();
                        return;
                    }
                }

                self.data('submit', true);
                self.submit();
            });

            // Initialising appraisals.
            // Processing.
            $('.oa-init-appraisal').click(function(){
                // Set up variables.
                var self = $(this);
                var tr = self.closest('tr');
                var colspan = tr.find('td').length;
                var alerttr = $(
                        '<tr class="tr-alert"><td colspan="'
                        + colspan
                        + '"><div class="alert" role="alert"></div></td></tr>'
                    ).hide();
                var alert = alerttr.find('.alert');

                // Disable button.
                var btn = self.html();
                self.prop('disabled', true);
                str.get_string('admin:initialisingdots', 'local_onlineappraisal').done(function(s) {
                    self.html('<i class="fa fa-lg fa-fw fa-spinner fa-spin"></i><span class="sr-only">' + s + '</span>');
                }).fail(notification.exception);

                // Get group and cohort.
                var groupid = $('select[name=groupid]').val();
                var cohortid = $('select[name=cohortid]').val();

                // Get users.
                var appraiseeid = self.data('id');
                var appraiserid = $('#oa-appraiser-select-' + appraiseeid).val();
                var signoffid = $('#oa-signoff-select-' + appraiseeid).val();
                var groupleaderid = $('#oa-groupleader-select-' + appraiseeid).val();

                // Get due date.
                var datepicker = $('#oa-init-duedate');
                var duedate = datepicker.datepicker('getUTCDate');
                
                // No appraiser or sign off.
                if (appraiserid < 1 || signoffid < 1) {
                    str.get_string('error:selectusers', 'local_onlineappraisal').done(function(s) {
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

                // No due date.
                if (Object.prototype.toString.call(duedate) !== "[object Date]") {
                    var headeroffset = $('#header').outerHeight(true);
                    datepicker.closest('.form-group').addClass('has-error').find('.help-block').hide().removeClass('hidden').show();
                    $('html, body').animate({
                        scrollTop: datepicker.offset().top - headeroffset
                    }, 500);
                    self.html(btn);
                    self.prop('disabled', false);
                    self.blur();
                    datepicker.datepicker().on('changeDate', function(){
                        datepicker.closest('.form-group').removeClass('has-error').find('.help-block').hide();
                        $('html, body').animate({
                            scrollTop: self.offset().top - headeroffset
                        }, 500);
                    });
                    return;
                }

                str.get_string('error:request', 'local_onlineappraisal').done(function(s) {
                    $.ajax({
                        type: 'POST',
                        url: cfg.wwwroot + '/local/onlineappraisal/ajax.php?sesskey=' + cfg.sesskey,
                        data: {
                            c: 'admin',
                            a: 'initialise_appraisal',
                            groupid: groupid,
                            cohortid: cohortid,
                            appraiseeid: appraiseeid,
                            appraiserid: appraiserid,
                            signoffid: signoffid,
                            groupleaderid: groupleaderid,
                            duedate: duedate.getTime() / 1000
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
                                tr.find('select').prop('disabled', true);
                                self.remove();
                            } else {
                                alert.removeClass('alert-success').addClass('alert-danger');
                                self.html(btn);
                                self.prop('disabled', false);
                                self.blur();
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
                            self.html(btn);
                            self.prop('disabled', false);
                            self.blur();
                        }
                    });
                }).fail(notification.exception);
            });

            // Inline editing toggle.
            $('.oa-inline-edit').click(function(){
                var self = $(this);
                var tr = self.closest('tr');

                self.hide();
                self.siblings('.oa-delete').hide();
                self.siblings('.oa-inline-save, .oa-inline-undo').removeClass('hidden').show();

                tr.find('.form-group').removeClass('hidden').show().siblings('span').hide();

                // Date editing.
                var datepicker = tr.find('.datepicker');
                if (datepicker.length) {
                    var date = datepicker.siblings('span');
                    datepicker.removeClass('hidden').show();
                    date.hide();

                    datepicker.datepicker({
                        autoclose: false,
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
                        datepicker.width(getInputWidth(datepicker));
                        date.html(e.format());
                    });

                    if (datepicker.data('y') && datepicker.data('m') && datepicker.data('d')) {
                        // Subtract one off the month for JS Date...
                        var originaldate = new Date(datepicker.data('y'), datepicker.data('m') - 1, datepicker.data('d'));
                        datepicker.datepicker('update', originaldate);
                        datepicker.width(getInputWidth(datepicker));
                    } else {
                        datepicker.width(50);
                    }
                }
            });

            // Cancel inline editing.
            $('.oa-inline-undo').click(function(){
                var self = $(this);
                var tr = self.closest('tr');

                tr.find('.form-group').hide().siblings('span').show();

                self.hide();
                self.siblings('.oa-inline-save').hide();
                self.siblings('.oa-inline-edit, .oa-delete').show();

                // Date editing.
                var datepicker = tr.find('.datepicker');
                if (datepicker.length) {
                    var date = datepicker.siblings('span');
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
                    datepicker.hide();
                    date.show();
                }
            });

            // Save inline edits.
            $('.oa-inline-save').click(function(){
                // Set up variables.
                var self = $(this);
                var tr = self.closest('tr');
                var datepicker = tr.find('.datepicker');
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
                var post = {
                    c: 'admin',
                    a: 'update_appraisal',
                    appraisalid: appraisalid,
                    appraiserid: $('#oa-appraiser-select-' + appraisalid).val(),
                    signoffid: $('#oa-signoff-select-' + appraisalid).val(),
                    groupleaderid: $('#oa-groupleader-select-' + appraisalid).val()
                };

                // No appraiser or sign off.
                if (post.appraiserid < 1 || post.signoffid < 1) {
                    str.get_string('error:selectusers', 'local_onlineappraisal').done(function(s) {
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
                
                var f2fdate = null;
                if (datepicker.length) {
                    var date = datepicker.siblings('span');
                    f2fdate = datepicker.datepicker('getUTCDate');
                    if (f2fdate !== null && Object.prototype.toString.call(f2fdate) !== "[object Date]") {
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
                    } else if (f2fdate !== null) {
                        post.date = f2fdate.getTime() / 1000;
                    }
                }

                str.get_string('error:request', 'local_onlineappraisal').done(function(s) {
                    $.ajax({
                        type: 'POST',
                        url: cfg.wwwroot + '/local/onlineappraisal/ajax.php?sesskey=' + cfg.sesskey,
                        data: post,
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
                                // Update names.
                                tr.find('.form-group').each(function(){
                                    var group = $(this);
                                    var text = group.find('option').filter(':selected').text();
                                    group.hide();
                                    group.siblings('span').text(text).show();
                                });
                                // Update datepicker if date set.
                                if (f2fdate !== null) {
                                    datepicker.data('d', f2fdate.getDate());
                                    datepicker.data('m', f2fdate.getMonth() + 1); // Add one to JS date for real month.
                                    datepicker.data('y', f2fdate.getFullYear());
                                }
                                if (datepicker.length) {
                                    datepicker.datepicker('destroy');
                                    datepicker.hide();
                                    date.show();
                                }
                                self.hide();
                                self.siblings('.oa-inline-undo').hide();
                                self.siblings('.oa-inline-edit, .oa-delete').show();
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

            // Delete appraisal (flagged or permanent).
            $('.oa-delete').click(function(){
                // Set up variables.
                var self = $(this);
                var tr = self.closest('tr');
                var colspan = tr.find('td').length;
                var alerttr = $(
                        '<tr class="tr-alert"><td colspan="'
                        + colspan
                        +
                        '"><div class="alert" role="alert"></div></td></tr>'
                    ).hide();
                var alert = alerttr.find('.alert');

                // Set deleting state and disable button.
                var btn = self.html();
                self.prop('disabled', true);
                str.get_string('admin:deletingdots', 'local_onlineappraisal').done(function(s) {
                    self.html('<i class="fa fa-lg fa-fw fa-spinner fa-spin"></i><span class="sr-only">' + s + '</span>');
                }).fail(notification.exception);

                // Pre-loaded at beginning of module for speed increase.
                str.get_strings([
                        {key : 'admin:confirm:delete', component : 'local_onlineappraisal'},
                        {key : 'error:request', component : 'local_onlineappraisal'}
                ]).done(function(s) {
                    var result = window.confirm(s[0]);
                    if (!result) {
                        self.html(btn);
                        self.prop('disabled', false);
                        self.blur();
                        return;
                    }

                    // Get data.
                    var appraisalid = tr.data('appraisalid');
                    var method = self.data('method');

                    $.ajax({
                        type: 'POST',
                        url: cfg.wwwroot + '/local/onlineappraisal/ajax.php?sesskey=' + cfg.sesskey,
                        data: {
                            c: 'admin',
                            a: 'delete_appraisal',
                            appraisalid: appraisalid,
                            method: method
                        },
                        success: function(data) {
                            // Failure if object not returned.
                            if (typeof data !== 'object') {
                                data = {
                                    success: false,
                                    message: s[1],
                                    data: ''
                                };
                            }
                            if (data.success) {
                                alert.removeClass('alert-danger').addClass('alert-success');
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
                            if (data.success) {
                                // Remove row.
                                tr.remove();
                            }
                        },
                        error: function(){
                            alert.removeClass('alert-success').addClass('alert-danger');
                            alert.html(s[1]);
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

            // Bulk actions processing.
            $('#oa-bulk-actions').submit(function(e){
                e.preventDefault();
                var btn = $(this).find('button');
                var btnhtml = btn.html();
                btn.prop('disabled', true);
                str.get_string('admin:processingdots', 'local_onlineappraisal').done(function(s) {
                    btn.html('<i class="fa fa-lg fa-fw fa-spinner fa-spin"></i><span class="sr-only">' + s + '</span>');
                }).fail(notification.exception);

                var action = $('#oa-bulk-actions-select option').filter(':selected').val();
                var selected = $('.oa-bulk-table').find('input[type=checkbox].oa-appraisal-select').filter(':checked');
                var alert = $('#oa-bulk-alert');
                alert.hide().removeClass('hidden');

                if (action === '') {
                    str.get_string('error:noaction', 'local_onlineappraisal').done(function(s) {
                        alert.find('.alert-message').text(s);
                        alert.removeClass('alert-success').addClass('alert-danger');
                        alert.slideDown().delay(5000).slideUp();
                        btn.html(btnhtml);
                        btn.prop('disabled', false);
                        btn.blur();
                    }).fail(notification.exception);
                    return;
                }
                
                if (selected.length < 1) {
                    str.get_string('error:noselection', 'local_onlineappraisal').done(function(s) {
                        alert.find('.alert-message').text(s);
                        alert.removeClass('alert-success').addClass('alert-danger');
                        alert.slideDown().delay(5000).slideUp();
                        btn.html(btnhtml);
                        btn.prop('disabled', false);
                        btn.blur();
                    }).fail(notification.exception);
                    return;
                }

                if (action === 'email') {
                    // Build mailto link and redirect.
                    var emails = [];
                    var selectedrows = selected.closest('tr');
                    selectedrows.each(function(){
                        emails.push($(this).find('.appraisee').data('email'));
                    });
                    if (emails.length > 0) {
                        var mailto = 'mailto:' + emails.join('%3B') + '%3B';
                        var win = window.open(mailto, 'emailWindow');
                        if (win && win.open && !win.closed) {
                            win.close();
                        }
                    }
                    btn.html(btnhtml);
                    btn.prop('disabled', false);
                    btn.blur();
                    return;
                }
            });

            // Toggling appraisal required.
            $('table').on('click', '.oa-toggle-required', function(e){
                e.preventDefault();
                var self = $(this);
                // Return if already running.
                if (self.data('processing')) {
                    return;
                }
                var tr = self.closest('tr');

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
                var userid = self.data('userid');
                var confirm = self.data('confirm');
                if (typeof confirm === 'undefined') {
                    confirm = 0;
                }
                var reason = self.data('reason');
                if (typeof reason === 'undefined') {
                    reason = '';
                }
                // Unset flags now.
                self.removeData('confirm');
                self.removeData('reason');

                str.get_string('error:request', 'local_onlineappraisal').done(function(s) {
                    $.ajax({
                        type: 'POST',
                        url: cfg.wwwroot + '/local/onlineappraisal/ajax.php?sesskey=' + cfg.sesskey,
                        data: {
                            c: 'admin',
                            a: 'toggle_appraisal_required',
                            userid: userid,
                            confirm: confirm,
                            reason: reason
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
                                // Change checkbox according to whether required or not.
                                self.toggleClass('fa-check text-success fa-times text-danger')
                                    .data('processing', false)
                                    .show()
                                    .find('span.sr-only').html(data.data);
                                // Refresh tooltip.
                                if (self.data('toggle') === 'tooltip') {
                                    self.tooltip('hide');
                                    self.tooltip('destroy');
                                    self.removeAttr('toggle')
                                        .removeData('toggle')
                                        .removeAttr('title');
                                }
                                if (reason !== '') {
                                    self.attr('title', reason);
                                    self.data('toggle', 'tooltip');
                                    self.tooltip();
                                }
                                if (self.data('remove-toggle')) {
                                    self.removeClass('oa-toggle-required');
                                }
                                spinner.remove();
                            } else {
                                alert.removeClass('alert-success').addClass('alert-danger');
                                spinner.remove();
                                self.data('processing', false).show();
                            }
                            alert.html(data.message);
                            tr.next('.tr-alert').remove();
                            if (data.data === 'confirm' || data.data === 'reason') {
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

            // Reason required.
            $('table').on('click', '.oa-toggle-required-reason', function(e){
                e.preventDefault();
                var self = $(this);
                var tr = self.closest('tr');
                if (self.data('reason')) {
                    var reason = self.siblings('input[name=reason]').val();
                    tr.closest('table')
                        .find('.oa-toggle-required')
                        .filter(function(){
                            return $(this).data('userid') === self.data('userid');
                        })
                        .data('reason', reason)
                        .data('confirm', 1)
                        .click();
                }
                tr.slideUp(function(){
                    $(this).remove();
                });
            });

            // Confirmation click.
            $('table').on('click', '.oa-toggle-required-confirm', function(e){
                e.preventDefault();
                var self = $(this);
                var tr = self.closest('tr');
                if (self.data('confirm')) {
                    tr.closest('table')
                        .find('.oa-toggle-required')
                        .filter(function(){
                            return $(this).data('userid') === self.data('userid');
                        })
                        .data('confirm', 1)
                        .click();
                }
                tr.slideUp(function(){
                    $(this).remove();
                });
            });

            // Toggling appraisal VIP status.
            $('.oa-toggle-vip').click(function(e){
                e.preventDefault();
                var self = $(this);
                // Return if already running.
                if (self.data('processing')) {
                    return;
                }
                var tr = self.closest('tr');

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
                var userid = self.data('userid');

                str.get_string('error:request', 'local_onlineappraisal').done(function(s) {
                    $.ajax({
                        type: 'POST',
                        url: cfg.wwwroot + '/local/onlineappraisal/ajax.php?sesskey=' + cfg.sesskey,
                        data: {
                            c: 'admin',
                            a: 'toggle_appraisal_vip',
                            userid: userid
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
                                // Change class according to whether made VIP or not.
                                self.toggleClass('oa-vip-yes')
                                    .data('processing', false)
                                    .show()
                                    .find('span.sr-only').html(data.data);
                                spinner.remove();
                            } else {
                                alert.removeClass('alert-success').addClass('alert-danger');
                                spinner.remove();
                                self.data('processing', false).show();
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
                            self.data('processing', false).show();
                        }
                    });
                }).fail(notification.exception);
            });

            // On change event for signoff select (to adjust groupleader select).
            $('[id^=oa-signoff-select-]').on('change', function(){
                var self = $(this);
                var signoff = self.val();
                var glselect = self.closest('tr').find('[id^=oa-groupleader-select-]');
                glselect.prop('disabled', false);
                var allgloptions = glselect.find('option');
                var gloption = glselect.find('option[value="'+signoff+'"]');
                if (signoff !== "0" && gloption.length > 0) {
                    gloption.hide().prop('selected', false);
                    gloption.siblings().show();
                } else {
                    allgloptions.show();
                }
                if (allgloptions.filter(':visible').length === 1) {
                    glselect.prop('disabled', true);
                }
            });
            
            // Assigning appraisal.
            $('.oa-toggle-assign').click(function(e){
                e.preventDefault();
                var self = $(this);
                // Return if already running.
                if (self.data('processing')) {
                    return;
                }
                var tr = self.closest('tr');

                // Disable further clicks.
                self.data('processing', true);

                var sronly = self.next('.sr-only').html();

                str.get_string('admin:processingdots', 'local_onlineappraisal').done(function(s) {
                    self.removeClass('fa-plus-square fa-minus-square')
                        .addClass('fa-spinner fa-spin')
                        .next('.sr-only').html(s);
                }).fail(notification.exception);

                var colspan = tr.find('td').length;
                var alerttr = $(
                        '<tr class="tr-alert"><td colspan="'
                        + colspan
                        + '"><div class="alert" role="alert"></div></td></tr>'
                    ).hide();
                var alert = alerttr.find('.alert');

                // Get data.
                var assign = self.data('assign');
                var userid = self.data('userid');
                var confirm = self.data('confirm');
                if (typeof confirm === 'undefined') {
                    confirm = 0;
                }
                var reason = self.data('reason');
                if (typeof reason === 'undefined') {
                    reason = '';
                }
                // Unset flags now.
                self.removeData('confirm');
                self.removeData('reason');

                var addclass = 'fa-minus-square';
                if (assign) {
                    addclass = 'fa-plus-square';
                }

                str.get_string('error:request', 'local_onlineappraisal').done(function(s) {
                    $.ajax({
                        type: 'POST',
                        url: cfg.wwwroot + '/local/onlineappraisal/ajax.php?sesskey=' + cfg.sesskey,
                        data: {
                            c: 'admin',
                            a: 'appraisalcycle_assign',
                            userid: userid,
                            assign: assign,
                            confirm: confirm,
                            reason: reason
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
                                // Redirect to reload tables (easiest thing to do!)
                                window.location = data.data;
                            } else {
                                alert.removeClass('alert-success').addClass('alert-danger');
                                self.data('processing', false)
                                    .removeClass('fa-fw fa-spinner fa-spin').addClass(addclass)
                                    .next('.sr-only').html(sronly);
                                alert.html(data.message);
                                tr.next('.tr-alert').remove();
                                if (data.data === 'confirm' || data.data === 'reason') {
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
                            self.data('processing', false)
                                .removeClass('fa-fw fa-spinner fa-spin').addClass(addclass)
                                .next('.sr-only').html(sronly);
                        }
                    });
                }).fail(notification.exception);
            });

            // Reason required.
            $('table').on('click', '.oa-toggle-assign-reason', function(e){
                e.preventDefault();
                var self = $(this);
                var tr = self.closest('tr');
                if (self.data('reason')) {
                    var reason = self.siblings('input[name=reason]').val();
                    tr.closest('table')
                        .find('.oa-toggle-assign')
                        .filter(function(){
                            return $(this).data('userid') === self.data('userid');
                        })
                        .data('reason', reason)
                        .data('confirm', 1)
                        .click();
                }
                tr.slideUp(function(){
                    $(this).remove();
                });
            });

            // Confirmation required.
            $('table').on('click', '.oa-toggle-assign-confirm', function(e){
                e.preventDefault();
                var self = $(this);
                var tr = self.closest('tr');
                if (self.data('confirm')) {
                    tr.closest('table')
                        .find('.oa-toggle-assign')
                        .filter(function(){
                            return $(this).data('userid') === self.data('userid');
                        })
                        .data('confirm', 1)
                        .click();
                }
                tr.slideUp(function(){
                    $(this).remove();
                });
            });

            // Expand initialise table.
            $('.oa-expand-table').on('click', '.oa-expand-link', function(){
                $('.oa-initialise-table').find('select').css('width', 'auto');
                $(this).addClass('hidden');
                $(this).siblings('.oa-compress-link').removeClass('hidden');
            }).on('click', '.oa-compress-link', function(){
                $('.oa-initialise-table').find('select').css('width', '100%');
                $(this).addClass('hidden');
                $(this).siblings('.oa-expand-link').removeClass('hidden');
            });
        }
    };
});