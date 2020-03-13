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
 * Select the Arup timezone
 *
 * @package    theme_arupboost
 * @copyright  2019 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(
[
    'jquery',
    'theme_arupboost/repository',
    'core/custom_interaction_events'
],
function(
    $,
    Repository,
    CustomEvents
) {

    var SELECTORS = {
        SETTIMEZONE: '[data-action="set-timezone"]',
        SETTIMEZONE_MODAL: '[data-region="timezone-modal"]',
        SETTIMEZONE_SELECTOR: '[data-region="timezone-selector"]',
        SETTIMEZONE_SUCCESS: '[data-region="updatetimezone-success"]'
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

        root.on(CustomEvents.events.activate, SELECTORS.SETTIMEZONE, function(e, data) {
            var timezonemodal = root.find(SELECTORS.SETTIMEZONE_MODAL);
            timezonemodal.modal('show');
            data.originalEvent.preventDefault();
        });

        $(SELECTORS.SETTIMEZONE_SELECTOR).change(function() {
            var selected = $(this).val();
            Repository.setTimeZone({
                timezone: selected
            }).then(function(result) {
                if (result.success == 1) {
                    root.find(SELECTORS.SETTIMEZONE_SUCCESS).removeClass('hidden');
                }
                $(SELECTORS.SETTIMEZONE).html(result.time);
            });
        });
    };

    /**
     * Intialise the timezone selector.
     *
     * @param {object} root The root element containing the timezone modal.
     */
    var init = function(root) {
        root = $(root);

        if (!root.attr('data-init')) {
            registerEventListeners(root);
            root.attr('data-init', true);
        }
    };

    return {
        init: init
    };
});