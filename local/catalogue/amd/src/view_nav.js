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
 * Manage the timeline view navigation for the overview block.
 *
 * @package    local_catalogue
 * @copyright  2019 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(
[
    'jquery',
    'core/custom_interaction_events',
    'core/log',
    'local_catalogue/view',
    'local_catalogue/selectors'
],
function(
    $,
    CustomEvents,
    Log,
    View,
    Selectors
) {

    var SELECTORS = {
        FILTERS: '[data-region="filter"]',
        METADATA: '[data-region="metadata"]',
        CAT: '[data-region="select-category"]',
        SELECT_CAT: '[data-action="select-category"]',
        CAT_CONTAINER: '.categories-container',
        SEARCH_CONTAINER: '[data-region="search-container"]',
        SEARCH: '[data-action="search-catalogue"]',
        SEARCH_BUTTON: '[data-action="search-button"]',
        SELECT_FILTER: '[data-action="select-filter"]',
        FILTER_OPTION: '[data-filter]',
        DISPLAY: '[data-region="select-display"]',
        DISPLAY_OPTION: '[data-display-option]',
        SELECT_BTN: '#selectcategorydropdown',
        CATACTIONS: '[data-region="catactions"]',
        FILTERS_BTN: '[data-action="showfilters"]',
        FILTERS_CONTAINER: '[data-region="catfilters"]',
        FILTERS_ACTIVE: '[data-region="filtersactive"]',
        CLOSE_FILTERS_BTN: '[data-action="close-filters"]',
        SUBCATEGORY_FILTERS: '[data-region="subcategoryfilters"]',
        LOAD_MORE: '[data-action="loadmore"]',
        EXPAND_FILTERS: '[data-action="expand"]',
        COLLAPSE_FILTERS: '[data-action="collapse"]',
    };


    /**
     * Check the boxes matching your stored filter preferences
     *
     * @param  {Object} root The root element for the catalogue
     */
    var checkPreferenceSelection = function(root) {

        var courseView = root.find(Selectors.courseView.region);

        var metadata = courseView.attr('data-metadata');
        if (metadata === '[{}]') {
            try {
                metadata = localStorage.getItem('data-metadata');
            } catch(error) {
                Log.debug(error);
            }
        }

        var storedPreferences = [];
        try {
            storedPreferences = JSON.parse(metadata);
        } catch(error) {
            courseView.attr('data-metadata', '[{}]');
            try {
                localStorage.setItem('data-metadata', '[{}]');
            } catch(error) {
                Log.debug(error);
            }
        }
        if (storedPreferences) {
            storedPreferences.forEach(function(pref) {
                var check = root.find('[data-type="' + pref.type + '"][data-value="' + pref.value + '"]');
                check.prop("checked", true);
                check.addClass('active');
                check.closest('.custom-control').addClass('show');
                check.closest('.filter-types').addClass('showcollapsed');
            });
        }

        // Highlight the stored view preference
        var pref = '';
        try {
            pref = localStorage.getItem("data-display");
        } catch(error) {
            Log.debug(error);
        }
        if (pref) {
            var displaySelector = root.find(SELECTORS.DISPLAY);
            displaySelector.find(SELECTORS.DISPLAY_OPTION).each(function(_, d) {
                var btn = $(d);
                btn.removeClass('active');
                btn.removeClass('btn-secondary');
                btn.addClass('btn-outline-secondary');
                if (btn.attr('data-pref') == pref) {
                    btn.removeClass('btn-outline-secondary');
                    btn.addClass('active');
                    btn.addClass('btn-secondary');
                }
            });
        }
    };

    var setSearch = function(root) {
        var inputbox = root.find(SELECTORS.SEARCH);
        if (localStorage.getItem("data-search")) {
            var searchVal = localStorage.getItem("data-search");
            inputbox.val(searchVal);
        }
    };

    /**
     * Event listener for the Display filter (cards, list).
     *
     * @param {object} root The root element for the catalogue
     */
    var registerSelector = function(root) {

        var Metadata = root.find(SELECTORS.METADATA);

        // Activate and save filters in user preference.
        CustomEvents.define(Metadata, [CustomEvents.events.activate]);
        Metadata.on(
            CustomEvents.events.activate,
            SELECTORS.SELECT_FILTER,
            function(e) {
                var option = $(e.target);

                if (option.hasClass('active')) {
                    option.removeClass('active');
                    option.closest('.custom-control').removeClass('show');
                    if (!option.closest('.filter-types').find('.custom-control.show').length) {
                        option.closest('.filter-types').removeClass('showcollapsed');
                    }
                } else {
                    option.addClass('active');
                    option.closest('.custom-control').addClass('show');
                    if (!option.closest('.filter-types').hasClass('forced')) {
                        option.closest('.filter-types').addClass('showcollapsed');
                    }
                }

                var setFilters = [];

                var hasfilters = false;

                root.find(SELECTORS.SELECT_FILTER).each(function(_, f) {
                    var cb = $(f);
                    if (cb.hasClass('active')) {
                        var filter = {
                            type: cb.attr('data-type'),
                            value: cb.attr('data-value')
                        };
                        setFilters.push(filter);
                        hasfilters = true;
                    }
                });

                var courseView = root.find(Selectors.courseView.region);

                var encodedFilters = JSON.stringify(setFilters);

                courseView.attr('data-metadata', encodedFilters);

                if (hasfilters) {
                    root.find(SELECTORS.FILTERS_ACTIVE).removeClass('hidden');
                } else {
                    root.find(SELECTORS.FILTERS_ACTIVE).addClass('hidden');
                }

                localStorage.setItem("data-metadata", encodedFilters);

                var newUrl = M.cfg.wwwroot + '/local/catalogue/index.php?showcatid=' +
                    courseView.attr('data-category') + '&page=incatalogue';
                if (history.replaceState) {
                    history.replaceState(null, null, newUrl);
                }

                View.init(root);
            }
        );

        Metadata.on(
            CustomEvents.events.activate,
            SELECTORS.COLLAPSE_FILTERS,
            function(e, data) {
                var collapsebtn = $(e.target);
                collapsebtn.closest('.filter-types').addClass('showcollapsed');
                collapsebtn.closest('.filter-types').addClass('forced');
                data.originalEvent.preventDefault();
            }
        );

        Metadata.on(
            CustomEvents.events.activate,
            SELECTORS.EXPAND_FILTERS,
            function(e, data) {
                var expandbtn = $(e.target);
                expandbtn.closest('.filter-types').removeClass('showcollapsed');
                expandbtn.closest('.filter-types').addClass('forced');
                data.originalEvent.preventDefault();
            }
        );
        // Open close catalogue filters (small devices).
        CustomEvents.define(root, [CustomEvents.events.activate]);
        root.on(
            CustomEvents.events.activate,
            SELECTORS.FILTERS_BTN,
            function() {
                filtersContainer.addClass('showfilters');
            }
        );

        // Open close catalogue filters (small devices).
        var filtersContainer = root.find(SELECTORS.FILTERS_CONTAINER);
        CustomEvents.define(filtersContainer, [CustomEvents.events.activate]);
        filtersContainer.on(
            CustomEvents.events.activate,
            SELECTORS.CLOSE_FILTERS_BTN,
            function() {
                filtersContainer.removeClass('showfilters');
            }
        );

        // Load more subcategories.
        var subcategoryFilters = root.find(SELECTORS.SUBCATEGORY_FILTERS);
        CustomEvents.define(subcategoryFilters, [CustomEvents.events.activate]);
        subcategoryFilters.on(
            CustomEvents.events.activate,
            SELECTORS.LOAD_MORE,
            function (e, data) {
                subcategoryFilters.removeClass('subcatshowsubset');
                data.originalEvent.preventDefault();
            }
        );

        // Display selector.
        var displaySelector = root.find(SELECTORS.DISPLAY);
        CustomEvents.define(displaySelector, [CustomEvents.events.activate]);
        displaySelector.on(
            CustomEvents.events.activate,
            SELECTORS.DISPLAY_OPTION,
            function(e, data) {

                var option = $(e.target).closest(SELECTORS.DISPLAY_OPTION);

                displaySelector.find(SELECTORS.DISPLAY_OPTION).each(function(_, d) {
                    $(d).removeClass('active');
                    $(d).removeClass('btn-secondary');
                    $(d).addClass('btn-outline-secondary');
                });

                option.removeClass('btn-outline-secondary');
                option.addClass('active');
                option.addClass('btn-secondary');

                var pref = option.attr('data-pref');

                root.find(Selectors.courseView.region).attr('data-display', pref);

                localStorage.setItem("data-display", pref);

                View.init(root);
                data.originalEvent.preventDefault();
            }
        );

        var searchCatalogue = root.find(SELECTORS.SEARCH_CONTAINER);
        CustomEvents.define(searchCatalogue, [CustomEvents.events.activate, CustomEvents.events.enter]);
        searchCatalogue.on(
            CustomEvents.events.enter,
            SELECTORS.SEARCH,
            function(e, data) {
                var input = $(e.target);
                var courseView = root.find(Selectors.courseView.region);

                var searchval = input.val();
                courseView.attr('data-search', searchval);
                localStorage.setItem("data-search", searchval);
                input.prop("disabled", true);
                View.init(root);
                data.originalEvent.preventDefault();
            }
        );

        searchCatalogue.on(
            CustomEvents.events.activate,
            SELECTORS.SEARCH_BUTTON,
            function(e, data) {
                var input = root.find(SELECTORS.SEARCH);
                var courseView = root.find(Selectors.courseView.region);

                var searchval = input.val();
                courseView.attr('data-search', searchval);
                View.init(root);
                data.originalEvent.preventDefault();
            }
        );
    };

    /**
     * Initialise the timeline view navigation by adding event listeners to
     * the navigation elements.
     *
     * @param {object} root The root element for the myoverview block
     */
    var init = function(root) {
        root = $(root);
        checkPreferenceSelection(root);
        setSearch(root);
        registerSelector(root);
    };

    return {
        init: init
    };
});
