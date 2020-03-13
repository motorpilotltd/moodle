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
 * Potential course selector module.
 *
 * @module     datasource_course/form-course-selector
 * @class      form-course-selector
 * @package    datasource_course
 * @copyright  2018 Jun Pataleta
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/templates'], function($, Ajax, Templates) {

    return /** @alias module:datasource_course/form-course-selector */ {

        processResults: function(selector, results) {
            var courses = [];
            $.each(results, function(index, course) {
                courses.push({
                    value: course.id,
                    label: course._label
                });
            });
            return courses;
        },

        transport: function(selector, query, success, failure) {
            var promises = Ajax.call([{
                methodname: 'datasource_course_get_courses',
                args: {
                    query: query
                }
            }]);

            promises[0].done(function(results) {
                var resultpromises = [], i = 0;

                // Render the label.
                $.each(results, function(index, course) {
                    resultpromises.push(Templates.render('datasource_course/form-course-selector-suggestion', course));
                });

                // Apply the label to the results.
                return $.when.apply($.when, resultpromises).then(function() {
                    var args = arguments;
                    $.each(results, function(index, course) {
                        course._label = args[i];
                        i++;
                    });
                    success(results);
                    return;
                });

            }).fail(failure);
        }

    };

});
