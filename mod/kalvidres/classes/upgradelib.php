<?php
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

namespace mod_kalvidres;
defined('MOODLE_INTERNAL') || die();

/**
 * The video_resource_viewed event class.
 *
 * @since     Moodle 2.7
 * @copyright 2015 Rex Lorenzo <rexlorenzo@gmail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class upgradelib {
    public static function add_web_services() {
        global $DB;

        $mobileservices = $DB->get_records_select('external_services', 'shortname = :official OR shortname = :local',
                ['official' => MOODLE_OFFICIAL_MOBILE_SERVICE, 'local' => 'local_mobile']);

        $functions = ['mod_kalvidres_get_kalvidres_by_courses', 'mod_kalvidres_get_ks', 'mod_kalvidres_view_kalvidres'];

        foreach ($mobileservices as $mobileservice) {
            foreach ($functions as $function) {
                $getks = ['externalserviceid' => $mobileservice->id, 'functionname' => $function];
                if (!$DB->record_exists('external_services_functions', $getks)) {
                    $DB->insert_record('external_services_functions', (object) $getks);
                }
            }
        }
    }
}