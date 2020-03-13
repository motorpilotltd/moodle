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
 * Arup Search
 *
 * @package    theme_arupboost
 * @copyright  2019 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(
[
    'jquery',
    'core/custom_interaction_events'
],
function(
    $,
    CustomEvents
) {

    var SELECTORS = {
        SEARCH: '[data-action="show-search"]',
        SEARCH_MODAL: '[data-region="search-modal"]',
        SEARCH_INPUT: '[data-region="search-input"]'
    };

    /**
     * Listen to, and handle events for the arupboost theme
     *
     * @param {Object} root The event nodes container element.
     */
    var registerEventListeners = function(root) {
        CustomEvents.define(root, [
            CustomEvents.events.activate
        ]);
        root.on(CustomEvents.events.activate, SELECTORS.SEARCH, function(e, data) {
            var searchModal = root.find(SELECTORS.SEARCH_MODAL);
            searchModal.modal('show');
            $(searchModal).on('shown.bs.modal', function () {
                $(this).find(SELECTORS.SEARCH_INPUT).focus();
            });
            data.originalEvent.preventDefault();
        });
    };

    /**
     * Intialise the search modal.
     *
     * @param {object} root The root element containing the timezone modal.
     */
    var init = function(root) {
        root = $(root);

        if (!root.attr('data-init-search')) {
            registerEventListeners(root);
            root.attr('data-init-search', true);
        }
    };

    return {
        init: init
    };
});