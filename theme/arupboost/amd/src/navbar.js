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
    'core/custom_interaction_events',
],
function(
    $,
    CustomEvents
) {

    var SELECTORS = {
        NAVBAR: '[data-nav-status="toggle"]',
        MEGAMENU: '[data-region="megamenu"]',
        NAVDRAWER: '[data-region="drawer"]',
    };

    var previousScroll = 0;

    var currentScroll = 0;

    var hideNav = function() {
        $(SELECTORS.NAVBAR).removeClass("is-visible").addClass("is-hidden");
        $(SELECTORS.NAVDRAWER).removeClass("top-navbar").addClass("no-navbar");
    };

    var showNav = function() {
        $(SELECTORS.NAVBAR).removeClass("is-hidden").addClass("is-visible");
        $(SELECTORS.NAVDRAWER).removeClass("no-navbar").addClass("top-navbar");
    };

    var registerEventListeners = function() {
        CustomEvents.define($(SELECTORS.MEGAMENU), [CustomEvents.events.activate]);
        $(SELECTORS.MEGAMENU).on('show.bs.collapse', function() {
            $(SELECTORS.NAVBAR).removeClass("hide-megamenu").addClass("show-megamenu");
        });

        $(SELECTORS.MEGAMENU).on('hidden.bs.collapse', function() {
            $(SELECTORS.NAVBAR).removeClass("show-megamenu").addClass("hide-megamenu");
        });
    };

    var watchScroll = function() {
        $(window).scroll(function(){
            currentScroll = $(this).scrollTop();
            /*
            If the current scroll position is greater than 0 (the top) AND the current scroll position is
            less than the document height minus the window height (the bottom) run the navigation if/else statement.
            */
            if (currentScroll > 80 && currentScroll < $(document).height() - $(window).height()){

                /*
                  If the current scroll is greater than the previous scroll (i.e we're scrolling down the page), hide the nav.
                  Else we are scrolling up (i.e the previous scroll is greater than the current scroll), so show the nav.
                */
                if (currentScroll > previousScroll) {
                    window.setTimeout(hideNav, 300);
                } else {
                    window.setTimeout(showNav, 300);
                }
                /*
                  Set the previous scroll value equal to the current scroll.
                */
                previousScroll = currentScroll;
            }

        });
    };
    /**
     * Intialise the search modal.
     *
     * @param {object} root The root element containing the timezone modal.
     */
    var init = function(root) {

        if (!root.attr('data-init-scroll')) {
            watchScroll(root);
            registerEventListeners();
            root.attr('data-init-scroll', true);
        }
    };

    return {
        init: init
    };
});