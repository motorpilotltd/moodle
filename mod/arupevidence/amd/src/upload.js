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
 * Utility JS adding form enhancement via select2.
 *
 * @package    mod_arupevidence
 * @copyright  2018 Xantico Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @since      3.0
 */

define(['jquery', 'core/config', 'theme_bootstrap/bootstrap', 'mod_arupevidence/select2'],
    function($, cfg) {
        return /** @alias module:mod_arupevidence/upload */ {
            // Public variables and functions.
            // Add form enhancement via select2.
            init: function(args) {
                var courseid = args;
                $('select.select2').select2({
                    width: '75%'
                });
                $('select.select2-user').select2({
                    width: '75%',
                    minimumInputLength: 2,
                    allowClear: true,
                    ajax: {
                        url: cfg.wwwroot + '/mod/arupevidence/ajax.php',
                        dataType: 'json',
                        delay: 250,
                        data: function(params) {
                            return {
                                q: params.term,
                                courseid: courseid,
                                page: params.page
                            };
                        },
                        processResults: function(data, params) {
                            params.page = params.page || 1;

                            return {
                                results: data.items,
                                pagination: {
                                    more: (params.page * 25) < data.totalcount
                                }
                            };
                        },
                        cache: true
                    }
                });
            }
        };
    });