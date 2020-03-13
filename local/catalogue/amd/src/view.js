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
 * Manage the courses view for the overview block.
 *
 * @package    local_catalogue
 * @copyright  2019 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(
[
    'jquery',
    'local_catalogue/repository',
    'local_catalogue/paged_content_factory',
    'local_catalogue/pubsub',
    'core/custom_interaction_events',
    'core/notification',
    'core/templates',
    'local_catalogue/paged_content_events',
],
function(
    $,
    Repository,
    PagedContentFactory,
    PubSub,
    CustomEvents,
    Notification,
    Templates,
    PagedContentEvents
) {

    var SELECTORS = {
        COURSE_VIEW: '[data-region="courses-view"]',
        COURSE_REGION: '[data-region="course-view-content"]',
        PLACEHOLDER_REGION: '[data-region="loading-placeholder-content"]',
        LOADMORE: '.catalogueloadmore',
        SEARCH: '[data-action="search-catalogue"]'
    };

    var TEMPLATES = {
        COURSES_CARDS: 'local_catalogue/view-cards',
        COURSES_LIST: 'local_catalogue/view-list',
        COURSES_SUMMARY: 'local_catalogue/view-summary',
        NOCOURSES: 'local_catalogue/no-courses'
    };

    var NUMCOURSES_PERPAGE = [12, 24, 48];

    var courseOffset = 0;


    /**
     * Get filter values from DOM.
     *
     * @param {object} root The root element for the courses view.
     * @return {filters} Set filters.
     */
    var getFilterValues = function(root) {
        var courseView = root.find(SELECTORS.COURSE_VIEW);

        var hash = window.location.hash;

        if (hash == "#new") {
            localStorage.setItem("data-metadata", '');
        }

        var metadata = localStorage.getItem("data-metadata") ?
            localStorage.getItem("data-metadata") : courseView.attr('data-metadata');

        var display = localStorage.getItem("data-display") ?
            localStorage.getItem("data-display") : courseView.attr('data-display');

        if (courseView.attr('data-search') !== '')  {
            var search = courseView.attr('data-search');
            localStorage.removeItem("data-search");
        } else {
            var search = localStorage.getItem("data-search");
        }

        return {
            display: display,
            metadata: metadata,
            category: courseView.attr('data-category'),
            search: search,
        };
    };

    // We want the paged content controls below the paged content area.
    // and the controls should be ignored while data is loading.
    var DEFAULT_PAGED_CONTENT_CONFIG = {
        ignoreControlWhileLoading: true,
        controlPlacementBottom: true,
        persistentLimitKey: 'local_catalogue_user_paging_preference'
    };

    /**
     * Get enrolled courses from backend.
     *
     * @param {object} filters The filters for this view.
     * @param {int} limit The number of courses to show.
     * @return {promise} Resolved with an array of courses.
     */
    var getMyCourses = function(filters, limit) {

        return Repository.getCoursesByClassification({
            offset: courseOffset,
            limit: limit,
            metadata: filters.metadata,
            category: filters.category,
            search: filters.search
        });
    };

    /**
     * Intialise the courses list and cards views on page load.
     *
     * @param {object} root The root element for the courses view.
     */

    /**
     * Render the dashboard courses.
     *
     * @param {object} root The root element for the courses view.
     * @param {array} coursesData containing array of returned courses.
     * @param {object} filters The filters for this view.
     * @return {promise} jQuery promise resolved after rendering is complete.
     */
    var renderCourses = function(root, coursesData, filters) {

        var currentTemplate = '';
        if (filters.display == 'cards') {
            currentTemplate = TEMPLATES.COURSES_CARDS;
        } else if (filters.display == 'list') {
            currentTemplate = TEMPLATES.COURSES_LIST;
        } else {
            currentTemplate = TEMPLATES.COURSES_SUMMARY;
        }

        if (coursesData.courses.length) {
            return Templates.render(currentTemplate, {
                courses: coursesData.courses,
                placeholderimage: M.cfg.wwwroot + '/local/catalogue/pix/placeholderimage.png'
            });
        } else {
            return Templates.render(TEMPLATES.NOCOURSES, {});
        }
    };

    var startLoading = function(root) {
        root.find(SELECTORS.COURSE_VIEW).addClass('hidden');
        root.find(SELECTORS.PLACEHOLDER_REGION).removeClass('hidden');
    };

    var stopLoading = function(root) {
        root.find(SELECTORS.COURSE_VIEW).removeClass('hidden');
        root.find(SELECTORS.PLACEHOLDER_REGION).addClass('hidden');
        root.find(SELECTORS.SEARCH).prop("disabled", false);
    };

    /**
      * Intialise the courses list and cards views on page load.
      *
      * @param {object} root The root element for the courses view.
      * @param {object} content The content element for the courses view.
      */
    var init = function(root) {

        root = $(root);
        startLoading(root);
        var courseView = root.find(SELECTORS.COURSE_VIEW);
        courseOffset = 0;
        var filters = getFilterValues(root);

        var pagedContentPromise = PagedContentFactory.createWithLimit(
            NUMCOURSES_PERPAGE,
            function(pagesData, actions) {
                var promises = [];

                pagesData.forEach(function(pageData) {
                    var pageNumber = pageData.pageNumber - 1;
                    var pagePromise = getMyCourses(
                        filters,
                        pageData.limit,
                        pageNumber
                    ).then(function(coursesData) {
                        if (coursesData.courses.length < pageData.limit) {
                            actions.allItemsLoaded(pageData.pageNumber);
                        }
                        courseOffset = coursesData.nextoffset;
                        return renderCourses(root, coursesData, filters);
                    })
                    .catch(Notification.exception);

                    promises.push(pagePromise);
                });
                return promises;
            },
            DEFAULT_PAGED_CONTENT_CONFIG
        );

        pagedContentPromise.then(function(html, js) {
            Templates.replaceNodeContents(courseView, html, js);
        }).
        catch(Notification.exception);

        PubSub.subscribe('paged-content-container' + PagedContentEvents.PAGES_SHOWN, function() {
            stopLoading(root);
        });

        PubSub.subscribe('paged-content-container' + PagedContentEvents.START_LOADING, function() {
            startLoading(root);
        });
     };

    return {
        init: init
    };
});
