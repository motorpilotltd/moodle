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
 * Class for exporting a course summary from an stdClass.
 *
 * @package    local_catalogue
 * @copyright  2019 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_catalogue;
defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/cache/lib.php');

class catalogue_courses {

    const METHODOLOGY_CLASSROOM = 10; // Enrolments only accept Classroom Classes/
    const METHODOLOGY_ELEARNING = 20; // - Enrolments only accept Elearning Classes
    const METHODOLOGY_LEARNINGBURST = 40; // same as Elearning
    const METHODOLOGY_PROGRAMMES = 50; // Enrolments only accept Classroom Classes
    const METHODOLOGY_OTHER = 60; // No enrolment plugin required on setup.

    public static function get_courses($categoryids, $fields, $offset = 0, $limit = 100) {
        global $DB, $CFG;

        $basefields = array('id', 'category', 'sortorder',
                        'shortname', 'fullname', 'idnumber',
                        'startdate', 'visible',
                        'groupmode', 'groupmodeforce', 'cacherev'
                    );

        $metadata = array('methodology');

        if (is_string($fields)) {
            // Turn the fields from a string to an array.
            $fields = explode(',', $fields);
            $fields = array_map('trim', $fields);
            $fields = array_unique(array_merge($basefields, $fields));
        }

        $coursefields = 'c.' .join(',c.', $fields);

        $metadatafields = 'cmd.' .join(',cmd.', $metadata);

        list($catsql, $catparams) = $DB->get_in_or_equal($categoryids, SQL_PARAMS_NAMED, 'id');

        $wheres = array("c.id <> :siteid", "c.category " . $catsql, 'c.visible = 1', "ctx.contextlevel = " . CONTEXT_COURSE);

        $wheres = implode(" AND ", $wheres);

        $siteparams = array('siteid' => SITEID);
        $params = array_merge($siteparams, $catparams);
        $orderby = '';

        $sql = "
            SELECT $coursefields, ctx.id as contextid, $metadatafields
              FROM {course} c
         LEFT JOIN {context} ctx
                ON ctx.instanceid = c.id
         LEFT JOIN {coursemetadata_arup} cmd
                ON cmd.course = c.id
            WHERE  $wheres
                   $orderby";

        $courses = $DB->get_records_sql($sql, $params, $offset, $limit);
        return $courses;
    }

    private static function yield_courses(
        array $categoryids,
        string $fields,
        int $limit = 0,
        int $offset = 0,
        int $dbquerylimit = 100
    ) : \Generator {

        $haslimit = !empty($limit);
        $recordsloaded = 0;
        $querylimit = (!$haslimit || $limit > $dbquerylimit) ? $dbquerylimit : $limit;

        while ($courses = self::get_courses($categoryids, $fields, $offset, $querylimit)) {
            yield from $courses;

            $recordsloaded += $querylimit;

            if (count($courses) < $querylimit) {
                break;
            }
            if ($haslimit && $recordsloaded >= $limit) {
                break;
            }

            $offset += $querylimit;
        }
    }

    public static function get_filtered_courses($category, $fields, $offset, $limit, $filter = null, $search = null) {

        $filteredcourses = [];
        $numberofcoursesprocessed = 0;
        $filtermatches = 0;

        $cache = \cache::make('local_catalogue', 'courses');

        // Hack to all search in all categories.
        if ($search) {
            $category = (int) get_config('local_catalogue', 'root_category');
        }

        $topcat = \coursecat::get($category);

        $categoryids = $topcat->get_all_children_ids();
        array_push($categoryids, $category);
        // Get the generator that will return more courses if needed.
        $courses = self::yield_courses($categoryids, $fields, 0, $offset, 100);

        foreach ($courses as $course) {

            $numberofcoursesprocessed++;

            if ($filter || $search) {
                if (self::match_filters($course, $filter, $search, $cache)) {
                    $filteredcourses[] = $course;
                    $filtermatches++;
                }
            } else {
                $filteredcourses[] = $course;
                $filtermatches++;
            }

            if ($limit && $filtermatches >= $limit) {
                // We've found the number of requested courses. No need to continue searching.
                break;
            }
        }

        // Return the number of filtered courses as well as the number of courses that were searched
        // in order to find the matching courses. This allows the calling code to do some kind of
        // pagination.
        return [$filteredcourses, $numberofcoursesprocessed];
    }

    public static function match_filters($course, $filter, $search, $cache) {

        $filters = json_decode($filter);
        $matches = (object) [];

        if (!$search) {
            if (count($filters) == 0) {
                return true;
            }

            if (count($filters) == 1) {
                $filter = $filters[0];
                if (!isset($filter->value)) {
                    return true;
                }
            }
        }

        if ($search) {
            $search = trim($search);
            $matches->search = false;
            $matchfullname = strpos(strtolower($course->fullname), strtolower($search));
            $matchshortname = strpos(strtolower($course->fullname), strtolower($search));
            if ($matchfullname !== false  || $matchshortname !== false ) {
                $matches->search = true;
            }
        }

        // This is not great for performance.
        if (($cf = $cache->get($course->id)) === false) {
            $cf = self::coursemetadata_course_record($course->id);
            $cache->set($course->id, $cf);
        }

        foreach ($filters as $f) {
            if (isset($f->type) && isset($f->value)) {
                $type = $f->type;
                $value = $f->value;
                if ($type == 'region') {
                    $matches->region = !isset($matches->region) ? false : $matches->region;
                    if ($cf->region === '0') {
                        $matches->region = true;
                    }
                    if ($cf->region == $value) {
                        $matches->region = true;
                    }
                }
                if ($type == 'methodology') {
                    $matches->methodology = !isset($matches->methodology) ? false : $matches->methodology;
                    if ($cf->methodology == $value) {
                        $matches->methodology = true;
                    }
                }
                if ($type == 'level') {
                    $matches->level = !isset($matches->level) ? false : $matches->level;
                    if ($cf->level == $value) {
                        $matches->level = true;
                    }
                }
                if ($type == 'stt') {
                    $matches->stt = !isset($matches->stt) ? false : $matches->stt;
                    if ($cf->stt == $value) {
                        $matches->stt = true;
                    }
                }
                if ($type == 'subsub') {
                    $matches->subsub = !isset($matches->subsub) ? false : $matches->subsub;
                    $subcategoryids = explode('-', $value);
                    if (in_array($course->category, $subcategoryids)) {
                        $matches->subsub = true;
                    }
                }
            }
        }

        foreach ($matches as $match) {
            if (!$match) {
                return false;
            }
        }
        return true;
    }

    /**
     * Returns an object with the coursemetadata fields set for the given course.
     *
     * @param int $courseid
     * @return object
     */
    public static function coursemetadata_course_record($courseid) {
        global $CFG, $DB;

        $metdat = $DB->get_records('coursemetadata_info_data', ['course' => $courseid]);

        $cf = (object) ['region' => '', 'methodology' => '', 'level' => '', 'stt' => ''];

        foreach ($metdat as $rec) {
            if ($rec->fieldid == 1) {
                $cf->methodology = str_replace(' ', '', strtolower($rec->data));
            }
            if ($rec->fieldid == 3) {
                $cf->level = str_replace(' ', '', strtolower($rec->data));
            }
            if ($rec->fieldid == 4) {
                $cf->stt = $rec->data;
            }
        }

        if ($reg = $DB->get_records('local_regions_reg_cou', ['courseid' => $courseid])) {
            foreach ($reg as $r) {
                $cf->region = $r->regionid;
            }
        } else {
            $cf->region = '0';
        }

        return $cf;
    }

}