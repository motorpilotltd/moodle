<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2018 onwards Totara Learning Solutions LTD
 *
 * This certification is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This certification is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this certification.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Sam Hemelryk <sam.hemelryk@totaralearning.com>
 * @package totara_certification
 */

namespace local_reportbuilder\rb\display;

defined('MOODLE_INTERNAL') || die();

/**
 * Class to aid with display classes utilising rb_course_sortorder_helper.
 *
 * Required only while group concat sorting is not supported in all supported databases.
 *
 * @deprecated since Totara 12, will be removed once MSSQL 2017 is the minimum required version.
 */
abstract class certification_course_base extends base {

    /**
     * @var bool
     */
    private static $resort_required = null;

    /**
     * Returns true if the data must be resorted.
     *
     * @param bool $recalculate
     * @return bool
     */
    final protected static function resort_required($recalculate = false) {
        global $DB;

        if (self::$resort_required === null || $recalculate) {
            self::$resort_required = !\local_reportbuilder\dblib\base::getbdlib()->sql_group_concat_orderby_supported();
        }

        return self::$resort_required;
    }

    /**
     * Re-sorts the courses are given the correct order from the database.
     *
     * @param int   $certificationid
     * @param array $courses An array of content relating to courses, the key is important, it aligns with the map
     *                       param.
     * @param array $map     An array where the key matches the key in the courses param, and the value is the
     *                       courseid. This is required as the courses array data is unpredictable and we can't guess
     *                       the course id.
     *
     * @return array The courses array, but sorted correctly.
     */
    final protected static function resort($certificationid, array $courses, array $map) {
        if (count($courses) < 2) {
            // Either no records, or a single record; no need to sort anything.
            return $courses;
        }

        $order = \rb_course_sortorder_helper::get_sortorder($certificationid);
        if (!$order) {
            debugging('Unknown certification id passed to resort, ' . $certificationid, DEBUG_DEVELOPER);
            return $courses;
        }

        $return = [];
        foreach ($order as $courseid) {
            $key = array_search($courseid, $map);
            if ($key !== false) {
                $return[] = $courses[$key];
                unset($courses[$key]);
            }
        }

        if (debugging() && count($courses) > 0) {
            // The courses given exceed the number in the cache.
            // Add any that are missing to the end of the array and show a debugging notice.
            debugging('Expected certification courses count does not match cached course count, try purging your caches.', DEBUG_DEVELOPER);
            foreach ($courses as $course) {
                $return[] = $course;
            }
        }

        return $return;
    }
}
