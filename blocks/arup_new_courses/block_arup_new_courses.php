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

defined('MOODLE_INTERNAL') || die();

define('BLOCK_ARUP_NEW_COURSES_DEFAULTNUMBEROFCOURSES', 5);

class block_arup_new_courses extends block_base {

    public function init() {
        $this->title   = get_string('pluginname', 'block_arup_new_courses');
    }

    public function applicable_formats() {
        return array('site' => true);
    }

    public function specialization() {
        if (!empty($this->config->title)) {
            $this->title = $this->config->title;
        } else {
            $this->title = get_string('title', 'block_arup_new_courses');
        }
    }

    public function instance_can_be_docked() {
        return false;
    }

    public function get_content() {
        global $CFG, $DB, $USER;
        $now = time();

        if ($this->content !== null) {
            return $this->content;
        }

        if (empty($this->config->numberofcourses)) {
            if (empty($this->config)) {
                $this->config = new stdClass();
            }
            $this->config->numberofcourses = BLOCK_ARUP_NEW_COURSES_DEFAULTNUMBEROFCOURSES;
        }

        $this->content = new stdClass();
        $this->content->footer = '';

        $table = new html_table();
        $table->attributes['class'] = 'arup-new-courses-table';

        if ($this->_has_methodologies()) {
            $table->attributes['class'] .= ' arup-has-methodologies';
        }

        $table->data = array();

        if (!empty($USER->auth) && $USER->auth == 'saml') {
            $params = array();

            $regjoin = '';
            $regwhere = '';
            if (get_config('local_regions', 'version')) {
                require_once($CFG->dirroot.'/local/regions/lib.php');
                local_regions_load_data_user($USER);
                $regjoin .= "
                    LEFT JOIN {local_regions_reg_cou} lrrc
                    ON c.id = lrrc.courseid
                ";
                if (!empty($USER->regions_field_region)) {
                    $regwhere .= " AND (lrrc.regionid = :region OR lrrc.regionid IS NULL)";
                    $params['region'] = $USER->regions_field_region;
                } else {
                    $regwhere .= " AND lrrc.regionid IS NULL";
                }
            }

            // Hidden categories won't be included dependent on capabilities.
            $catccselect = ", " . context_helper::get_preload_record_columns_sql('ctx');
            $catccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = cc.id AND ctx.contextlevel = :contextlevel)";
            $catsql = "SELECT cc.* $catccselect
                    FROM {course_categories} cc
                    $catccjoin
                    ORDER BY cc.sortorder ASC";
            $catparams = array('contextlevel' => CONTEXT_COURSECAT);
            $categories = array();
            $catrs = $DB->get_recordset_sql($catsql, $catparams);
            foreach ($catrs as $cat) {
                context_helper::preload_from_record($cat);
                $catcontext = context_coursecat::instance($cat->id);
                if ($cat->visible || has_capability('moodle/category:viewhiddencategories', $catcontext)) {
                    $categories[$cat->id] = $cat;
                }
            }
            $catrs->close();
            // But want to show courses user is enrolled on.
            $enrolledcourses = enrol_get_my_courses();

            $ccselect = ", " . context_helper::get_preload_record_columns_sql('ctx');
            $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
            $params['contextlevel'] = CONTEXT_COURSE;
            $sql = "
                SELECT
                    c.* {$ccselect}
                FROM
                    {course} c
                {$regjoin}
                {$ccjoin}
                WHERE
                    c.id <> :siteid
                    AND c.startdate < :now
                    {$regwhere}
                ORDER BY
                    c.startdate DESC
            ";
            $params['siteid'] = SITEID;
            $params['now'] = $now;
            $rs = $DB->get_recordset_sql($sql, $params);

            $twoweeksago = $now - 14 * 24 * 60 * 60;
            $courses = array();
            $count = 0;
            $prevcourseid = 0;
            foreach ($rs as $course) {
                if ($count == $this->config->numberofcourses) {
                    break;
                }

                if ($course->id === $prevcourseid) {
                    continue;
                }
                $prevcourseid = $course->id;

                $visible = true;
                $skip = array_key_exists($course->id, $enrolledcourses);

                if (!$skip) {
                    context_helper::preload_from_record($course);
                    $coursecontext = context_course::instance($course->id);
                    if (!$course->visible) {
                        // No need to check all categories.
                        $visible = false;
                    } else if ($course->category != 0) {
                        // Check for any hidden category in the tree for this course.
                        // If catgeory not in array then is hidden.
                        if (!isset($categories[$course->category])) {
                            $visible = false;
                        } else {
                            $parentcategories = explode('/', $categories[$course->category]->path);
                            unset($parentcategories[0]);
                            foreach ($parentcategories as $parentcategory) {
                                if (!isset($categories[$parentcategory]) || !$categories[$parentcategory]->visible) {
                                    $visible = false;
                                    break;
                                }
                            }
                        }
                    }
                }

                if ($visible || has_capability('moodle/course:viewhiddencourses', $coursecontext)) {
                    if ($count < $this->config->numberofcourses) {
                        $courses[$course->id] = $course;
                    }
                    $count++;
                }
            }

            $rs->close();

            foreach ($courses as $course) {
                $cells = array();

                $cell = new html_table_cell();
                $cell->attributes['class'] = 'outer-spacer';
                $cell->text = '';
                $cells[] = clone($cell);

                if ($this->_has_methodologies()) {
                    $cell = new html_table_cell();
                    $cell->attributes['class'] = 'text-center';
                    $cell->text = $this->_get_methodology($course->id);
                    $cells[] = clone($cell);
                }

                $linktext = format_string($course->fullname);
                $linkurl = new moodle_url('/course/view.php', array('id' => $course->id));
                $new = '';
                if ($course->startdate > $twoweeksago) {
                    $new = html_writer::tag('span', get_string('new', 'block_arup_new_courses'), array('class' => 'new'));
                }

                $summary = trim(strip_tags($course->summary));
                $summary = html_writer::tag('div', $summary, array('class' => 'summary'));

                $cells[] = html_writer::link(
                    $linkurl,
                    $linktext . $new . $summary
                );

                $cell = new html_table_cell();
                $cell->attributes['class'] = 'outer-spacer';
                $cell->text = '';
                $cells[] = clone($cell);

                $table->data[] = new html_table_row($cells);
            }
        }

        if (!empty($table->data)) {
            $this->content->text = html_writer::table($table);
        } else {
            $this->content->text = html_writer::tag('div', get_string('nonewcourses', 'block_arup_new_courses'), array('class' => 'no-new'));
        }

        $footerurl = new moodle_url('/course/index.php');
        $this->content->footer = html_writer::link($footerurl, get_string('viewcatalogue', 'block_arup_new_courses'));

        return $this->content;
    }

    protected function _has_methodologies() {
        global $DB;

        if (!isset($this->_methodologyfield)) {
            $this->_methodologyfield = false;

            $fieldid = isset($this->config->methodologyfield) ? $this->config->methodologyfield : 0;
            if ($fieldid && get_config('local_coursemetadata', 'version')) {
                $this->_methodologyfield = $DB->get_record('coursemetadata_info_field', array('id' => $fieldid));
            }
        }
        return $this->_methodologyfield;
    }

    protected function _get_methodology($courseid) {
        global $CFG;

        if (!$this->_has_methodologies()) {
            return '';
        }

        require_once("{$CFG->dirroot}/local/coursemetadata/lib.php");
        require_once("{$CFG->dirroot}/local/coursemetadata/field/{$this->_methodologyfield->datatype}/field.class.php");
        $fieldclassname = 'coursemetadata_field_'.$this->_methodologyfield->datatype;
        $fieldclass = new $fieldclassname($this->_methodologyfield->id, $courseid);

        return $fieldclass->display_data();
    }
}
