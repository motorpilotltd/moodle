/*
 * This file is part of T0tara LMS
 *
 * Copyright (C) 2010 onwards T0tara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Simon Coggins <simon.coggins@t0taralms.com>
 * @author Eugene Venter <eugene@catalyst.net.nz>
 * @author Brian Barnes <brian.barnes@t0taralearning.com>
 * @package t0tara
 * @subpackage reportbuilder
 */

/**
 * Javascript file containing JQuery bindings for hierarchy dialog filters.
 */

define(['jquery', 'core/config', 'core/str'], function ($, mdlcfg, mdlstrings) {

    /* global totaraSingleSelectDialog totaraMultiSelectDialogRbFilter */

    var disable_filter_controls = function(event) {
        var name = $(this).attr('name');

        if (typeof name === 'undefined') {
            // Well we can't find the matching filter, we can't disable it!
            return;
        }

        name = name.substr(0, name.length - 3);// Remove _op.

        if ($(this).val() === '0') {
            $('#show-'+name+'-dialog').addClass("disabled");
            $('#show-'+name+'-dialog').prop('disabled', true);
            $('#show-'+name+'-dialog').removeAttr('href');
            $('*[data-filtername="' + name + '"] a').addClass("disabled");
            $('*[data-filtername="' + name + '"] a').prop('disabled', true);
            $('*[data-filtername="' + name + '"] a').removeAttr('href');
        } else {
            $('#show-'+name+'-dialog').removeClass("disabled");
            $('#show-'+name+'-dialog').prop('disabled', false);
            $('#show-'+name+'-dialog').attr('href', '#');
            $('*[data-filtername="' + name + '"] a').removeClass("disabled");
            $('*[data-filtername="' + name + '"] a').prop('disabled', false);
            $('*[data-filtername="' + name + '"] a').attr('href', '#');
        }
    };

    var handler = {

        // Holds items that need to be initialised.
        waitingitems: [],
        reportid: 0,

        /**
         * Module initialisation method called by php js_init_call().
         *
         * @param string    The filter to apply (hierarchy, badge, hierarchy_multi, cohort, category, course_multi)
         * @param string    The current value (may be HTML) - only used by hierarchy and badge
         * @param string    The type of the hierarchy to load - only used by hierarchy type
         * @param {string} name The name of the filter. Optional, may be undefined.
         */
        init: function(filter, value, type, name, reportid) {
            handler.waitingitems.push({
                filter: filter,
                value: value,
                hierarchytype: type,
                name: name
            });
            handler.reportid = reportid;

            if (window.dialogsInited) {
                this.rb_init_filter_dialogs();
            } else {
                // Queue it up.
                if (!$.isArray(window.dialoginits)) {
                    window.dialoginits = [];
                }

                // Only need need to add the function once as it goes through all current ones.
                if (this.waitingitems.length === 1) {
                    window.dialoginits.push(this.rb_init_filter_dialogs);
                }
            }
        },

        rb_init_filter_dialogs: function() {

            // Copy the waiting items to a holding array, and empty the waiting items array.
            // This was we know exactly what we need to initialise here.
            var waitingitems = $.extend(true, [], handler.waitingitems);
            handler.waitingitems = [];

            $.each(waitingitems, function () {
                switch (this.filter) {
                    case "badge":
                        handler.rb_load_badge_filters(this);
                        break;
                    case "cohort":
                        handler.rb_load_cohort_filters();
                        break;
                    case "category":
                        handler.rb_load_category_filters();
                        break;
                    case "course_multi":
                        handler.rb_load_course_multi_filters();
                        break;
                }
            });

            // Activate the 'delete' option next to any selected items in filters.
            $(document).on('click', '.multiselect-selected-item a', function(event) {
                event.preventDefault();

                var container = $(this).parents('div.multiselect-selected-item');
                var filtername = container.data('filtername');
                var id = container.data('id');
                var hiddenfield = $('input[name=' + filtername + ']');

                // Take this element's ID out of the hidden form field.
                var ids = hiddenfield.val();
                var id_array = ids.split(',');
                var new_id_array = $.grep(id_array, function (index) {
                    return index != id;
                });
                var new_ids = new_id_array.join(',');
                hiddenfield.val(new_ids);

                // Remove this element from the DOM.
                container.remove();
            });
        },

        rb_load_cohort_filters: function() {
            // Loop through every 'add cohort' link binding to a dialog.
            $('div.rb-cohort-add-link a').each(function() {
                var id = $(this).attr('id');
                // Remove 'show-' and '-dialog' from ID.
                id = id.substr(5, id.length - 12);

                var url = mdlcfg.wwwroot + '/local/reportbuilder/ajax/';

                mdlstrings.get_string('choosecohorts', 'totara_cohort').done(function (choosecohorts) {
                    totaraMultiSelectDialogRbFilter(
                        id,
                        choosecohorts,
                        url + 'find_cohort.php?sesskey=' + mdlcfg.sesskey,
                        url + 'save_cohort.php?sesskey=' + mdlcfg.sesskey + '&filtername=' + id + '&ids='
                    );
                });
            });
        },

        rb_load_badge_filters: function(filter) {
            // Loop through every 'add badge' link binding to a dialog.
            $('div.rb-badge-add-link a').each(function() {
                var id = $(this).attr('id');
                // Remove 'show-' and '-dialog' from ID.
                id = id.substr(5, id.length - 12);

                var url = mdlcfg.wwwroot + '/local/reportbuilder/ajax/';

                mdlstrings.get_string('choosebadges', 'badges').done(function (choosebadges) {
                    totaraMultiSelectDialogRbFilter(
                        id,
                        choosebadges,
                        url + 'find_badge.php?reportid=' + filter.value + '&sesskey=' + mdlcfg.sesskey,
                        url + 'save_badge.php?filtername=' + id + '&sesskey=' + mdlcfg.sesskey + '&ids='
                    );
                });
            });
        },

        rb_load_category_filters: function() {
            $(document).on('change', '#id_course_category-path_op', function(event) {
                event.preventDefault();
                var name = $(this).attr('name');
                name = name.substr(0, name.length - 3);// Remove _op.

                if ($(this).val() === '0') {
                    $('input[name='+name+'_rec]').prop('disabled', true);
                    $('#show-'+name+'-dialog').prop('disabled', true);
                } else {
                    $('input[name='+name+'_rec]').prop('disabled', false);
                    $('#show-'+name+'-dialog').prop('disabled', false);
                }
            });

            $('input.rb-filter-choose-category').each(function() {
                var id = $(this).attr('id');
                // Remove 'show-' and '-dialog' from ID.
                id = id.substr(5, id.length - 12);

                var url = mdlcfg.wwwroot + '/local/reportbuilder/ajax/filter/category/';

                mdlstrings.get_string('choosecatplural', 'local_reportbuilder').done(function (choosecatplural) {
                    totaraMultiSelectDialogRbFilter(
                        id,
                        choosecatplural,
                        url + 'find.php?sesskey=' + mdlcfg.sesskey,
                        url + 'save.php?filtername=' + id + '&sesskey=' + mdlcfg.sesskey +'&ids='
                    );
                });

                // Disable popup buttons if first pulldown is set to 'any value'.
                if ($('select[name='+id+'_op]').val() === '0') {
                    $('input[name='+id+'_rec]').prop('disabled',true);
                    $('#show-'+id+'-dialog').prop('disabled',true);
                }
            });
        },

        rb_load_course_multi_filters: function() {
            $(document).on('change', '#id_course-id_op', function(event) {
                event.preventDefault();
                var name = $(this).attr('name');
                name = name.substr(0, name.length - 3);// Remove _op.

                if ($(this).val() === '0') {
                    $('input[name='+name+'_rec]').prop('disabled', true);
                    $('#show-'+name+'-dialog').prop('disabled', true);
                } else {
                    $('input[name='+name+'_rec]').prop('disabled', false);
                    $('#show-'+name+'-dialog').prop('disabled', false);
                }
            });

            $('input.rb-filter-choose-course').each(function() {
                var id = $(this).attr('id');
                // Remove 'show-' and '-dialog' from ID.
                id = id.substr(5, id.length - 12);

                var url = mdlcfg.wwwroot + '/local/reportbuilder/ajax/filter/course_multi/';
                mdlstrings.get_string('coursemultiitemchoose', 'local_reportbuilder').done(function (coursemultiitemchoose) {
                    totaraMultiSelectDialogRbFilter(
                        id,
                        coursemultiitemchoose,
                        url + 'find.php?sesskey=' + mdlcfg.sesskey,
                        url + 'save.php?filtername=' + id + '&sesskey=' + mdlcfg.sesskey +'&ids='
                    );
                });

                // Disable popup buttons if first pulldown is set to 'any value'.
                if ($('select[name='+id+'_op]').val() === '0') {
                    $('input[name='+id+'_rec]').prop('disabled',true);
                    $('#show-'+id+'-dialog').prop('disabled',true);
                }
            });
        }
    };

    return handler;
});
