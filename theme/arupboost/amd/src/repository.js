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
 * A javascript module to retrieve enrolled coruses from the server.
 *
 * @package    theme_arupboost
 * @copyright  2019 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
define(['core/ajax'], function(Ajax) {

    /**
     * Retrieve the current timezone.
     *
     * @method theme_arupboost_set_timezone
     * @param {object} args The request arguments
     * @return {promise} Resolved with an array of courses
     */
    var setTimeZone = function(args) {

        var request = {
            methodname: 'theme_arupboost_set_timezone',
            args: args
        };

        var promise = Ajax.call([request])[0];

        return promise;
    };

    /**
     * Retrieve the footer content.
     *
     * @method theme_arupboost_set_timezone
     * @param {object} args The request arguments
     * @return {promise} Resolved with an array of courses
     */
    var getFooterContent = function(args) {

        var request = {
            methodname: 'theme_arupboost_get_footer_contents',
            args: args
        };
        var promise = Ajax.call([request])[0];
        return promise;
    };

    /**
     * Save an image from the image handler
     * @param {object} args The request arguments
     * @return {promise} Resolved with an array file the stored file url.
     */
    var saveImage = function(args) {
        var request = {
            methodname: 'theme_arupboost_saveimage',
            args: args
        };

        var promise = Ajax.call([request])[0]
            .fail(Notification.exception);

        return promise;
    };

    return {
        saveImage: saveImage,
        setTimeZone: setTimeZone,
        getFooterContent: getFooterContent
    };
});
