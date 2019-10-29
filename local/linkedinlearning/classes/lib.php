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

/**
 * @package    local_linkedinlearning
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_linkedinlearning;

class lib {
    public static function appendurlparamswitharray($url, $params) {
        $outurl = new \moodle_url($url); // Ensure is a Moodle URL.

        foreach ($params as $key => $val) {
            $outurl->remove_params($key); // Ensure param is removed before re-adding.
            if (is_array($val)) {
                foreach ($val as $index => $value) {
                    $outurl->param($key . '[' . $index . ']', $value);
                }
            } else {
                $outurl->param($key, $val);
            }
        }

        return $outurl;
    }

    public static function cohorts_updated() {
        global $DB;

        $like = $DB->sql_like('ltc.coursecode', ':coursecode');
        $basesql = "
            SELECT ###selectfield###
              FROM {course_modules} cm
              JOIN {modules} m ON m.id = cm.module AND m.name = :module
              JOIN {tapsenrol} te ON te.id = cm.instance
              JOIN {local_taps_course} ltc ON ltc.courseid = te.tapscourse AND {$like}";
        $cmidsql = str_ireplace('###selectfield###', 'cm.id', $basesql);
        $select = "id IN ({$cmidsql})";
        $params = [
            'module' => 'tapsenrol',
            'coursecode' => 'urn:li:lyndaCourse:%',
        ];

        // Availability.
        $cohorts = array_filter(explode(',', get_config('local_linkedinlearning', 'cohorts')));
        $children = [];
        foreach ($cohorts as $cohort) {
            $structure = new \stdClass();
            $structure->id = (int) $cohort;
            $condition = new \availability_cohort\condition($structure);
            $children[] = $condition->save();
        }
        if (!empty($children)) {
            $availability = json_encode(\core_availability\tree::get_root_json($children, \core_availability\tree::OP_OR, true));
        } else {
            $availability = null;
        }
        $DB->set_field_select('course_modules', 'availability', $availability, $select, $params);
        $cidsql = str_ireplace('###selectfield###', 'DISTINCT cm.course', $basesql);
        foreach ($DB->get_records_sql($cidsql, $params) as $cid) {
            rebuild_course_cache($cid->course);
        }
    }
}