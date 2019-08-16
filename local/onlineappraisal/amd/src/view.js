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

define(['jquery', 'core/config', 'core/str', 'core/notification', 'theme_bootstrap/bootstrap', 'select2_4_0_8'],
       function($, cfg, str, notification) {

    var leadershipElements = {
        'leadership': {
            'select': $('select#id_leadership')
        },
        'roles': {
            'select': $('select#id_leadershiproles'),
            'div': $('#fitem_id_leadershiproles'),
            'links': $('#oa-leadershiproles-links')
        },
        'attributes': {
            'select': $('select#id_leadershipattributes'),
            'div': $('#fitem_id_leadershipattributes'),
            'wrapper': $('#oa-development-leadershipattributes'),
            'tables': $('#oa-development-leadershipattributes table.oa-table-leadership-attributes')
        }
    };

    var leadershipRolesDisplay = function(roleSelect) {
        if (roleSelect.val() === 'Yes') {
            leadershipElements.roles.div.show(0, function() {
                if (!leadershipElements.roles.select.hasClass('select2-hidden-accessible')) {
                    str.get_string('form:development:leadershiproles:placeholder', 'local_onlineappraisal').done(function(s) {
                        leadershipElements.roles.select.select2({
                            maximumSelectionLength: 2,
                            placeholder: s
                        });
                    });
                }
                leadershipElements.roles.links.show();
            });
            leadershipElements.attributes.wrapper.show();
            leadershipElements.attributes.div.show(0, function() {
                if (!leadershipElements.attributes.select.hasClass('select2-hidden-accessible')) {
                    leadershipElements.attributes.select.select2({maximumSelectionLength: 3});
                }
            });
        } else {
            leadershipElements.roles.select.val(null).trigger('change.select2');
            leadershipRolesCheck();
            leadershipElements.roles.links.hide();
            leadershipElements.roles.div.hide();
        }
    };

    var leadershipRolesCheck = function() {
        str.get_string('form:development:leadershiproles:answer:generic', 'local_onlineappraisal').done(function(s) {
            leadershipAttributesActivate(true);

            var selected = leadershipElements.roles.select.val();
            var selectedOther = $.inArray(s, selected);
            var currentattributes = leadershipElements.attributes.select.val();
            // If 'Other' is selected and there are more options selected should remove all other options.
            if (selected.length > 1 && selectedOther > -1) {
                leadershipElements.roles.select.val(null).val(s).trigger('change.select2');
                selected = leadershipElements.roles.select.val();
            }
            if (selectedOther > -1) {
                leadershipElements.attributes.tables.each(function() {
                    var self = $(this);
                    if (self.data('type') === 'generic') {
                        self.show();
                    } else {
                        self.find('i[data-toggle="popover"]').popover('hide');
                        dataAttributeFilter(self.find('button'), 'selected', true).each(function() {
                            var button = $(this);
                            leadershipAttributeToggleButton(button);
                            var index = $.inArray(button.data('option'), currentattributes);
                            if (index !== -1) {
                                currentattributes.splice(index, 1);
                            }
                        });
                        leadershipElements.attributes.select.val(currentattributes).trigger('change.select2');
                        self.hide();
                    }
                });
                $('#oa-development-leadershipattributes-detailed').hide();
            } else if (selected.length > 0) {
                leadershipElements.attributes.tables.each(function() {
                    var self = $(this);
                    if (self.data('type') === 'role') {
                        self.show();
                        leadershipAttributeColumns(self, selected);
                    } else {
                        self.find('i[data-toggle="popover"]').popover('hide');
                        dataAttributeFilter(self.find('button'), 'selected', true).each(function() {
                            var button = $(this);
                            leadershipAttributeToggleButton(button);
                            var index = $.inArray(button.data('option'), currentattributes);
                            if (index !== -1) {
                                currentattributes.splice(index, 1);
                            }
                        });
                        self.hide();
                    }
                });
                $('#oa-development-leadershipattributes-detailed').show();
            }

            if (selected.length > 0) {
                leadershipElements.attributes.wrapper.show();
                leadershipElements.attributes.div.show();
            } else {
                leadershipElements.attributes.select.val(null).trigger('change.select2');
                leadershipElements.attributes.tables.find('i[data-toggle="popover"]').popover('hide');
                dataAttributeFilter(leadershipElements.attributes.tables.find('button'), 'selected', true).each(function() {
                    leadershipAttributeToggleButton($(this));
                });
                leadershipElements.attributes.wrapper.hide();
                leadershipElements.attributes.div.hide();
            }

            leadershipAttributesActivate(false);
        });
    };

    var leadershipAttributeButton = function(button) {
        var selected = leadershipElements.attributes.select.val();
        var option = button.data('option');
        var index = $.inArray(option, selected);
        if (!button.data('selected') && index === -1) {
            selected.push(option);
        } else if (button.data('selected') && index !== -1) {
            selected.splice(index, 1);
        }
        if (selected.length > 3) {
            var strs = [
                {key: 'error', component: 'local_onlineappraisal'},
                {key: 'form:development:leadershipattributes:error:toomany', component: 'local_onlineappraisal'}
            ];
            str.get_strings(strs).done(function(s) {
                notification.alert(s[0], s[1], 'OK');
            });
        } else {
            leadershipAttributeToggleButton(button);
            leadershipAttributesActivate(true);
            leadershipElements.attributes.select.val(selected).trigger('change.select2');
            leadershipAttributesActivate(false);
        }
    };

    var leadershipAttributeToggleButton = function(button) {
        button.data('selected', !button.data('selected'));
        button.toggleClass('btn-default btn-primary').blur();
    };

    var leadershipAttributesSelect = function() {
        var selected = leadershipElements.attributes.select.val();
        var buttons = leadershipElements.attributes.tables.find('button');
        buttons.data('selected', false);
        buttons.removeClass('btn-primary').addClass('btn-default');
        $.each(selected, function() {
            var selectedButtons = dataAttributeFilter(buttons, 'option', this);
            selectedButtons.data('selected', true);
            selectedButtons.removeClass('btn-default').addClass('btn-primary');
        });
    };

    var leadershipAttributeColumns = function(table, selected) {
        var columns = table.find('th');
        var colcount = 0;
        columns.each(function() {
            var column = $(this);
            var position = column[0].cellIndex + 1;
            var cells = table.find('th:nth-child(' + position + '), td:nth-child(' + position + ')');
            var popovers = cells.find('i[data-toggle="popover"]');
            if ($.inArray(column.data('column'), selected) > -1) {
                colcount++;
                cells.show();
                if (colcount === 1) {
                    popovers.popover('destroy');
                    popovers.data('placement', 'left');
                } else {
                    popovers.popover('destroy');
                    popovers.data('placement', 'right');
                }
            } else {
                popovers.popover('hide');
                cells.hide();
                dataAttributeFilter(cells.find('button'), 'selected', true).each(function() {
                    leadershipAttributeButton($(this));
                });
            }
        });
        if (selected.length === 1) {
            table.removeClass('oa-multi-column').addClass('oa-single-column');
        } else {
            table.removeClass('oa-single-column').addClass('oa-multi-column');
            // Sort for same values.
        }
    };

    var dataAttributeFilter = function(elements, attribute, value) {
        return elements.filter(function() {
            return $(this).data(attribute) == value;
        });
    };

    var leadershipAttributesActivate = function(on) {
        if (on === true) {
            leadershipElements.attributes.select.prop('disabled', false);
            leadershipElements.attributes.select.trigger('change.select2');
        } else {
            leadershipElements.attributes.select.prop('disabled', true);
            leadershipElements.attributes.select.trigger('change.select2');
        }
    };

    var leadershipAttributesPopoverHide = function(popover) {
        var table = popover.parents('table');
        var type = table.data('type');
        if (type === 'role') {
            // Hide popovers in same column (role).
            var position = popover.parent('td')[0].cellIndex + 1;
            table.find('td:nth-child(' + position + ') i[data-toggle="popover"]').not(popover).popover('hide');
        } else {
            // Hide popovers in rest of table (generic).
            table.find('i[data-toggle="popover"]').not(popover).popover('hide');
        }
    };

    return /** @alias module:local_onlineappraisal/view */ {
        init: function(appraisalid, view, page, statusid) {

            // Select2 initialisation.
            $('select.select2-general').select2();
            $('select.select2-general').on('select2:opening select2:closing', function() {
                var $searchfield = $(this).parent().find('.select2-search__field');
                $searchfield.prop('disabled', true);
            });

            if (page === 'development') {
                leadershipElements.roles.div.removeClass('hiddenifjs').hide();
                leadershipElements.roles.links.removeClass('hiddenifjs').hide();
                leadershipElements.roles.select.removeClass('hiddenifjs');
                leadershipElements.attributes.wrapper.removeClass('hiddenifjs').hide();
                // Tables will be hidden if no js (force select use).
                leadershipElements.attributes.tables.removeClass('hidden').hide();
                leadershipElements.attributes.div.removeClass('hiddenifjs').hide();
                leadershipElements.attributes.select.removeClass('hiddenifjs');
                // Detail link will be hidden if no js (can't form link and load).
                $('#oa-development-leadershipattributes-detailed').removeClass('hidden').hide();

                leadershipAttributesActivate(true);
                leadershipRolesDisplay(leadershipElements.leadership.select);
                leadershipRolesCheck();
                leadershipAttributesSelect();
                leadershipAttributesActivate(false);

                leadershipElements.leadership.select.on('change', function() {
                    leadershipRolesDisplay($(this));
                    leadershipRolesCheck();
                });

                leadershipElements.roles.select.on('change', function() {
                    leadershipRolesCheck();
                });

                leadershipElements.attributes.wrapper.on('click', 'button', function(e) {
                    var self = $(this);
                    e.preventDefault();
                    if (!self.prop('disabled')) {
                        leadershipAttributeButton(self);
                    }
                });

                leadershipElements.attributes.tables.on('click', 'i[data-toggle="popover"]', function() {
                    leadershipAttributesPopoverHide($(this));
                });

                leadershipElements.attributes.select.on('change', function() {
                    leadershipAttributesSelect();
                });

                $('#oa-development-leadershipattributes-detailed').on('click', function(e) {
                    e.preventDefault();
                    var querystring = 'print=leadershipattributes&appraisalid=' + appraisalid + '&view=' + view;
                    $.each(leadershipElements.roles.select.val(), function() {
                        querystring = querystring + '&role[]=' + encodeURIComponent(this);
                    });
                    var url = cfg.wwwroot + '/local/onlineappraisal/print.php?' + querystring;
                    window.location.href = url;
                });

                $('form').on('submit', function() {
                    leadershipAttributesActivate(true);
                });
            }

            if (page === 'userinfo' && (view === 'appraisee' || view === 'appraiser') && statusid < 5) {
                var classes, graderefresh, jobtitlerefresh;
                var grade = $('#id_grade');
                var jobtitle = $('#id_jobtitle');
                str.get_strings([
                        {key: 'form:userinfo:refresh', component: 'local_onlineappraisal'},
                        {key: 'form:userinfo:refresh:tooltip', component: 'local_onlineappraisal'}
                    ]).done(function(s) {
                        // Inject ability to update fields from datahub.
                        classes = 'oa-refresh-datahub fa fa-2x fa-refresh m-l-10 pull-left';
                        graderefresh = $('<i class="' + classes + '"><span class="sr-only">' + s[0] + '</span></i>');
                        graderefresh.css('cursor', 'pointer').prop('title', s[1]).data({
                            appraisalid: appraisalid,
                            view: view
                        });
                        jobtitlerefresh = graderefresh.clone(true); // Want data cloned.
                        grade.addClass('pull-left').after(graderefresh);
                        jobtitle.addClass('pull-left').after(jobtitlerefresh);
                        graderefresh.data('field', 'grade');
                        jobtitlerefresh.data('field', 'jobtitle');
                        $('.oa-refresh-datahub').tooltip();
                    }
                ).fail(notification.exception);

                // Processing.
                $('.felement').on('click', '.oa-refresh-datahub', function() {
                    // Set up variables.
                    var self = $(this);

                    // Return if already running.
                    if (self.data('processing')) {
                        return;
                    }

                    // Disable further clicks.
                    self.data('processing', true);
                    self.addClass('fa-spin');

                    var alert = $('<div class="alert clear" role="alert"></div><').hide();

                    str.get_string('error:request', 'local_onlineappraisal').done(function(s) {
                        $.ajax({
                            type: 'POST',
                            url: cfg.wwwroot + '/local/onlineappraisal/ajax.php?sesskey=' + cfg.sesskey,
                            data: {
                                c: 'appraisal',
                                a: 'userinfo_datahub_update',
                                appraisalid: self.data('appraisalid'),
                                view: self.data('view'),
                                field: self.data('field')
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
                                    // Update fields.
                                    if (typeof data.data.grade !== 'undefined') {
                                        grade.val(data.data.grade);
                                    }
                                    if (typeof data.data.jobtitle !== 'undefined') {
                                        jobtitle.val(data.data.jobtitle);
                                    }
                                } else {
                                    alert.removeClass('alert-success').addClass('alert-danger');
                                }
                                self.data('processing', false).removeClass('fa-spin');
                                alert.html(data.message);
                                self.next('.alert').remove();
                                alert.insertAfter(self)
                                    .slideDown()
                                    .delay(5000)
                                    .slideUp(function() {
                                        $(this).remove();
                                    });
                            },
                            error: function() {
                                alert.removeClass('alert-success').addClass('alert-danger');
                                alert.html(s);
                                self.next('.alert').remove();
                                alert.insertAfter(self)
                                    .slideDown()
                                    .delay(5000)
                                    .slideUp(function() {
                                        $(this).remove();
                                    });
                                self.data('processing', false).removeClass('fa-spin');
                            }
                        });
                    }).fail(notification.exception);
                });
            }

            if (page === 'successionplan'
                    && parseInt($('#oa-sdp-islocked').val()) === 0
                    && $('#oa-sdp-view').val() !== 'appraisee') {
                // Add strength/developmentarea inputs.
                // Reveal buttons.
                $('.oa-add-repeating-element').show();
                $('.oa-add-repeating-element').click(function(e) {
                    e.preventDefault();
                    var that = $(this);
                    var index = that.data('index');
                    var newindex = parseInt(index) + 1;
                    that.data('index', newindex);
                    var type = that.data('type');
                    var clone = that.parent().find('#fitem_id_' + type + '_' + index).clone();
                    clone.prop('id', 'fitem_id_' + type + '_' + newindex);
                    var label = clone.find('label');
                    label.prop('for', 'id_' + type + '_' + newindex);
                    var input = clone.find('input');
                    input.prop('id', 'id_' + type + '_' + newindex);
                    input.prop('name', type + '[' + newindex + ']');
                    input.val('');
                    clone.insertBefore(that);
                });
            }

            // Confirm SDP unlocking.
            var strs = [
                {key: 'form:successionplan:confirm:unlock:title', component: 'local_onlineappraisal'},
                {key: 'form:successionplan:confirm:unlock:question', component: 'local_onlineappraisal'},
                {key: 'form:successionplan:confirm:unlock:yes', component: 'local_onlineappraisal'},
                {key: 'form:successionplan:confirm:unlock:no', component: 'local_onlineappraisal'},
                {key: 'form:save', component: 'local_onlineappraisal'}
            ];
            str.get_strings(strs).done(function(s) {
                $('#id_submitbutton.oa-unlock-sdp').click(function(e) {
                    e.preventDefault();
                    var self = $(this);
                    var form = self.closest('form');
                    if (form.find('#id_unlock').is(':checked') === false) {
                        return;
                    }
                    notification.confirm(s[0], s[1], s[2], s[3],
                        function() {
                            // Avoid regular leaving page notification.
                            M.core_formchangechecker.set_form_submitted();
                            var input = $('<input type="hidden" name="submitbutton" value="' + s[4] + '" />');
                            form.append(input).submit();
                        }
                    );
                });
            }).fail(notification.exception);

            if (page === 'leaderplan'
                    && parseInt($('#oa-ldp-islocked').val()) === 0
                    && $('#oa-ldp-view').val() === 'appraisee') {
                // Add ldpstrength/ldpdevelopmentarea inputs.
                // Reveal buttons.
                $('.oa-add-repeating-element').show();
                $('.oa-add-repeating-element').click(function(e) {
                    e.preventDefault();
                    var that = $(this);
                    var index = that.data('index');
                    var newindex = parseInt(index) + 1;
                    that.data('index', newindex);
                    var type = that.data('type');
                    var clone = that.parent().find('#fitem_id_' + type + '_' + index).clone();
                    clone.prop('id', 'fitem_id_' + type + '_' + newindex);
                    var label = clone.find('label');
                    label.prop('for', 'id_' + type + '_' + newindex);
                    var input = clone.find('input');
                    input.prop('id', 'id_' + type + '_' + newindex);
                    input.prop('name', type + '[' + newindex + ']');
                    input.val('');
                    clone.insertBefore(that);
                });

                // Adding alterntaive potential future role.
                $('#ldppotentialnew-add button').click(function(e) {
                    e.preventDefault();
                    var input = $(this).parent().prev('input');
                    var inputval = input.val();
                    if (inputval !== '') {
                        $('select#id_ldppotential').append($('<option>', {text: inputval, value: inputval, selected: true}));
                        input.val('');
                    }
                });
            }

            // Confirm LDP unlocking.
            var ldpstrs = [
                {key: 'form:leaderplan:confirm:unlock:title', component: 'local_onlineappraisal'},
                {key: 'form:leaderplan:confirm:unlock:question', component: 'local_onlineappraisal'},
                {key: 'form:leaderplan:confirm:unlock:yes', component: 'local_onlineappraisal'},
                {key: 'form:leaderplan:confirm:unlock:no', component: 'local_onlineappraisal'},
                {key: 'form:save', component: 'local_onlineappraisal'}
            ];
            str.get_strings(ldpstrs).done(function(s) {
                $('#id_submitbutton.oa-unlock-ldp').click(function(e) {
                    e.preventDefault();
                    var self = $(this);
                    var form = self.closest('form');
                    if (form.find('#id_ldplocked').is(':checked') !== false) {
                        return;
                    }
                    notification.confirm(s[0], s[1], s[2], s[3],
                        function() {
                            // Avoid regular leaving page notification.
                            M.core_formchangechecker.set_form_submitted();
                            var input = $('<input type="hidden" name="submitbutton" value="' + s[4] + '" />');
                            form.append(input).submit();
                        }
                    );
                });
            }).fail(notification.exception);

            if ($('#oa-save-nag-modal').length) {
                // Want to nag after 15 mins and check before trying to save.
                setTimeout(function() {
                    $('#oa-save-nag-modal').modal({
                        backdrop: 'static',
                        keyboard: false
                    });
                    // In case any popovers are showing!
                    $('[data-toggle="popover"]').popover('hide');
                }, 900000);

                $('.oa-save-nag-modal-save').click(function() {
                    $('#oa-save-nag-modal').modal('hide');
                    window.scrollTo(0, $('#id_submitbutton').offset().top);
                    $(this).closest('form').find('#id_submitbutton').click();
                });

                $('input[type="submit"]').click(function() {
                    // Need to ensure clicked button is set.
                    var self = $(this);
                    var form = self.closest('form');
                    form.data('clicked-name', self.prop('name'));
                    form.data('clicked-value', self.prop('value'));
                });

                str.get_string('error:sessioncheck', 'local_onlineappraisal').done(function(s) {
                    $('.oa-save-session-check').submit(function(e) {
                        var self = $(this);

                        // Don't worry if cancelling.
                        if (self.data('clicked-name') === 'cancelbutton') {
                            var input = $('<input type="hidden" />')
                                .prop('name', self.data('clicked-name'))
                                .prop('value', self.data('clicked-value'));
                            input.appendTo(self);
                            return;
                        }

                        // Should we continue with form submission.
                        var submitform = false;

                        // Set up variables.
                        var buttonar = $(this).find('#fgroup_id_buttonar');
                        var buttons = buttonar.find('input');
                        var alert = $('<div class="alert m-l-15" role="alert"></div>').hide();

                        // Disable button.
                        buttons.prop('disabled', true);

                        $.ajax({
                            // Don't want it to be async so we can capture result before continuing.
                            async: false,
                            type: 'POST',
                            url: cfg.wwwroot + '/local/onlineappraisal/ajax.php?sesskey=' + cfg.sesskey,
                            data: {
                                c: 'appraisal',
                                a: 'check_session'
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
                                    // OK to submit form.
                                    submitform = true;
                                    // Ensure submit button data added.
                                    var input = $('<input type="hidden" />')
                                        .prop('name', self.data('clicked-name'))
                                        .prop('value', self.data('clicked-value'));
                                    input.appendTo(self);
                                } else {
                                    alert.removeClass('alert-success').addClass('alert-danger');
                                    alert.html(s);
                                    buttonar.find('.alert').remove();
                                    alert.appendTo(buttonar).slideDown();
                                    buttons.prop('disabled', false);
                                    buttons.blur();
                                }

                            },
                            error: function() {
                                alert.removeClass('alert-success').addClass('alert-danger');
                                alert.html(s);
                                buttonar.find('.alert').remove();
                                alert.appendTo(buttonar).slideDown();
                                buttons.prop('disabled', false);
                                buttons.blur();
                            }
                        });

                        if (!submitform) {
                            // Stop submission.
                            e.preventDefault();
                        }
                    });
                }).fail(notification.exception);
            }
        }
    };
});
