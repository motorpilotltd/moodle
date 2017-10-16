<?php
// This file is part of the Arup Course Management system
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
 *
 * @package     local_coursemanager
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursemanager\output\dashboard;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use moodle_url;
use renderer_base;
use html_writer;

class overview extends base {

      /**
     * Export this data so it can be used as the context for a mustache template.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $OUTPUT;
        $data = new stdClass();
        
        $courselist = $this->coursemanager->courselist;

        $course = array_pop($this->coursemanager->courselist);
        $data->heading = array();
        $sortfields = array('coursename', 'courseregion', 'startdate');
        $page = $this->coursemanager->get_current_pageobject();

        // [id]
        // [courseid]
        // [coursecode]
        // [coursename]
        // [startdate]
        // [enddate]
        // [courseregion]
        // [coursedescription]
        // [courseobjectives]
        // [courseaudience]
        // [globallearningstandards]
        // [onelinedescription]
        // [businessneed]
        // [accreditationgivendate]
        // [tapsurllink]
        // [keywords]
        // [duration]
        // [durationunits]
        // [durationunitscode]
        // [sponsorname]
        // [courseadminempno]
        // [courseadminempname]
        // [maximumattendees]
        // [minimumattendees]
        // [futurereviewdate]
        // [jobnumber]
        // [activecourse]
        // [usedtimezone]
        // [timemodified]
        $showfields = array('coursecode', 'classes', 'coursename', 'startdate', 'duration', 'courseregion', 'keywords');
        $datefields = array('startdate', 'enddate', 'timemodified');
        $numericfields = array('classes', 'duration');

        $data->setfilters = $this->coursemanager->setfilters;

        $th = new stdClass();
        $th->name = get_string('active', 'local_coursemanager');
        $th->key = '';
        $th->nosort = true;
        $data->heading[] = $th;

        $th = new stdClass();
        $th->name = get_string('moodlepage', 'local_coursemanager');
        $th->key = '';
        $th->nosort = true;
        $data->heading[] = $th;

        foreach ($showfields as $fieldname) {
            $th = new stdClass();
            $th->key = $fieldname;
            $th->name = get_string('form:course:'.$fieldname, 'local_coursemanager');
            if (in_array($fieldname, $sortfields)) {
                $th->nosort = false;
                $th->sortdesc = false;
                $th->sortasc = false;
                if ($this->coursemanager->sort == $fieldname) {
                    if ($this->coursemanager->direction == 'ASC') {
                        $myparams = array('page' => 'overview', 'sort' => $fieldname, 'dir' => 'DESC');
                        $params = array_merge($myparams, $this->coursemanager->searchparams);
                        $th->sortdesc = new moodle_url('/local/coursemanager/index.php', $params);
                        $th->iconsort = $OUTPUT->pix_icon('t/sort_asc', 'Sort ASC');
                    } else {
                        $myparams = array('page' => 'overview', 'sort' => $fieldname, 'dir' => 'ASC');
                        $params = array_merge($myparams, $this->coursemanager->searchparams);
                        $th->sortdesc = new moodle_url('/local/coursemanager/index.php', $params);
                        $th->iconsort = $OUTPUT->pix_icon('t/sort_desc', 'Sort DESC');
                    }
                } else {
                    $myparams = array('page' => 'overview', 'sort' => $fieldname, 'dir' => 'ASC');
                    $params = array_merge($myparams, $this->coursemanager->searchparams);
                    $th->sortasc = new moodle_url('/local/coursemanager/index.php', $params);
                }
            } else {
                $th->nosort = true;
            }
            $th->class = '';
            if (in_array($fieldname, $numericfields)) {
                $th->class = 'numeric';
            }
            $data->heading[] = $th;
        }

        if ($page->add) {
            $th = new stdClass();
            $th->name = get_string('actions', 'local_coursemanager');
            $th->key = '';
            $th->nosort = true;
            $data->heading[] = $th;
        }

        $coursecode = '';
        $prevcourseid = '';
        foreach ($courselist as $course) {
            if ($course->archived) {
                continue;
            }
            $showcourse = new stdClass();
            $values = array();

            $value = new stdClass();
            $value->hasicon = true;
            if ($course->enddate > time() || $course->enddate == 0) {
                $value->icon = 'fa-circle';
                $value->tooltip = get_string('coursenotended', 'local_coursemanager');
            } else {
                $value->icon = 'fa-circle-o';
                $value->tooltip = get_string('courseended', 'local_coursemanager');
            }
            $value->value = '';
            $value->class = 'numeric';
            $values[] = $value;

            $value = new stdClass();
            if ($course->moodlecourse) {
                $value->hasicon = true;
                $value->iconlink = $moodlecourseurl = new moodle_url('/course/view.php', array('id' => $course->moodlecourse->id));
                if ($course->moodlecourse->visible) {
                    $value->tooltip = get_string('moodlecourseavailable', 'local_coursemanager');
                    $value->icon = 'fa-eye';
                } else {
                    $value->tooltip = get_string('moodlecoursehidden', 'local_coursemanager');
                    $value->icon = 'fa-eye-slash';
                }
            }
            $value->value = '';
            $value->class = 'numeric';
            $values[] = $value;

            foreach ($showfields as $fieldname) {
                $value = new stdClass();
                $value->value = $course->$fieldname;
                if (in_array($fieldname, $datefields)) {
                    $value->value = userdate($value->value, get_string('strftimedate'), 'UTC');
                }
                if ($fieldname == 'coursecode') {
                    $value->value .= '<a id="course'.$course->id.'" class="cmanchor">';
                }
                if ($fieldname == 'duration') {
                    $value->value .= ' ' . $course->durationunits;
                }
                // Create link to form
                if ($fieldname == 'coursecode') {
                    $myparams = array('page' => 'course', 'cmcourse' => $course->id, 'start' => $this->coursemanager->start);
                    $params = array_merge($this->coursemanager->searchparams, $myparams);
                    $link = new moodle_url('/local/coursemanager/index.php', $params);
                    $value->value = html_writer::link($link, $value->value);
                }
                $value->class = '';
                if (in_array($fieldname, $numericfields)) {
                    $value->class = 'numeric';
                }
                $values[] = $value;

            }
            if ($page->add) {
                $value = new stdClass();

                $editparams = array('page' => 'course', 'cmcourse' => $course->id, 'start' => $this->coursemanager->start);
                $params = array_merge($this->coursemanager->searchparams, $editparams);
                $editlink = new moodle_url('/local/coursemanager/index.php', $params);
                $edit = $OUTPUT->action_icon($editlink, new \pix_icon('i/edit', get_string('edit')));
                $value->value = $edit;

                $deleteparams = array('action' => 'deletecourse',
                    'cmcourse' => $course->id,
                    'page' => 'overview',
                    'start' => $this->coursemanager->start,
                    'limit' => $this->coursemanager->limit,
                    'sort' => $this->coursemanager->sort,
                    'dir' => $this->coursemanager->direction);
                $params = array_merge($this->coursemanager->searchparams, $deleteparams);
                $urldelete = new moodle_url('/local/coursemanager/index.php', $params);
                $urldelete->set_anchor('course' . $prevcourseid);
                $prevcourseid = $course->id;
                $delete = $OUTPUT->action_icon($urldelete, new \pix_icon('t/delete', get_string('delete')));
                $value->value .= $delete;
                $values[] = $value;
            }
            $showcourse->pendingdelete = false;
            if ($this->coursemanager->pendingdeletecourse == $course->id) {
                $showcourse->pendingdelete = true;
            }
            $showcourse->values = $values;
            $data->courselist[] = $showcourse;
        }

        $data->pagination = $this->pagination();

        $data->canedit = false;
        if ($page->add) {
            $data->canedit = true;
        }

        $data->addcourse = $page->addcourse;
        $addcourseparams = array('page' => 'course', 'cmcourse' => $this->coursemanager->cmcourse->id);
        $data->addcourseurl = new moodle_url('/local/coursemanager', $addcourseparams);
        $data->hassearch = $this->coursemanager->hassearch;
        $data->filteroptions = $this->coursemanager->filteroptions;
        $data->setfilters = $this->coursemanager->setfilters;
        $data->searchvalue = "";
        $active = optional_param('active', 0, PARAM_INT);
        if ($active) {
            $data->searchvalue = optional_param('searchvalue' . $active, '', PARAM_TEXT);
        }
        $data->textsearch = true;
        if (in_array($this->coursemanager->currentsearch, $datefields)) {
            $data->datesearch = true;
            $data->datesearchform = $this->coursemanager->get_date_search_form();
            $data->textsearch = false;
        }
        $data->start = $this->coursemanager->start;
        $data->searchlevel = $this->coursemanager->searchlevel;
        $data->currentsearch = $this->coursemanager->currentsearch;
        return $data;
    }


    private function pagination() {
        global $OUTPUT;
        if (!$this->coursemanager->numrecords) {
            return '';
        }
        if ($this->coursemanager->numrecords == 1) {
            return '';
        }

        $pagination = new stdClass();
        $pagination->pages = $this->pagination_pages();
        if (count($pagination->pages) <= 1) {
            return '';
        }
        $params = $this->coursemanager->searchparams;
        $params['page'] = 'overview';
        $params['sort'] = $this->coursemanager->sort;
        $params['dir'] = $this->coursemanager->direction;
        $params['start'] = $this->coursemanager->start - 1;
        $pagination->prevurl = new moodle_url('/local/coursemanager/index.php', $params);
        $params['start'] = $this->coursemanager->start + 1;
        $pagination->nexturl = new moodle_url('/local/coursemanager/index.php', $params);
        
        $leftalt = get_string('previous', 'local_coursemanager');
        $licon = new \pix_icon('t/left', $leftalt, '', array('title' => $leftalt));
        $pagination->leftarrow = $OUTPUT->render($licon);

        $rightalt = get_string('next', 'local_coursemanager');
        $ricon = new \pix_icon('t/right', $rightalt, '', array('title' => $rightalt));
        $pagination->rightarrow = $OUTPUT->render($ricon);

        return $pagination;
    }

    public function pagination_pages() {
        $numpages = ceil($this->coursemanager->numrecords / 100);
        $pages = array();
        for ($i = 0 ; $i <= $numpages ; $i++) {
            $page = new stdClass();
            $params = $this->coursemanager->searchparams;
            $params['page'] = 'overview';
            $params['sort'] = $this->coursemanager->sort;
            $params['dir'] = $this->coursemanager->direction;
            $params['start'] = $this->coursemanager->start - 1;
            $params['start'] = $i;
            $page->link = new moodle_url('/local/coursemanager/index.php', $params);
            $page->active = '';
            if ($this->coursemanager->start == $i) {
                $page->active = 'active';
            }

            $page->number = $i + 1;
            $pages[] = $page;
        }
        return $pages;
    }
}
