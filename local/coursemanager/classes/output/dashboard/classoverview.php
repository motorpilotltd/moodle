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
use context_course;

class classoverview extends base {

      /**
     * Export this data so it can be used as the context for a mustache template.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $OUTPUT, $DB;
        $data = new stdClass();

        $data->editing = $this->editing;

        $page = $this->coursemanager->get_current_pageobject();
        $currentcourse = $this->coursemanager->get_current_courseobject($this->coursemanager->cmcourse->id);
        if (!empty($currentcourse->moodlecourse)) {
            $tapsenrolid = $this->tapsenrol_id($currentcourse->moodlecourse->id);
        }
        $data->addclass = $page->addclass;
        $addclassparams = array('page' => 'class', 'cmcourse' => $currentcourse->id, 'cmclass' => -1);
        $data->addclassurl = new moodle_url($this->coursemanager->baseurl, $addclassparams);
        
        $data->canedit = false;
        if ($page->add) {
            $data->canedit = true;
        }

        $showfields = array(
            'classname',
            'classstatus',
            'classstarttime',
            'classendtime',
            'maximumattendees',
            'attending');
        $sortfields = array(
            'classname',
            'classstarttime',
            'classendtime',
            'classstatus');
        $numericfields = array('classid', 'maximumattendees');
        $timefields = array('classstarttime', 'classendtime');

        foreach ($showfields as $fieldname) {
            $th = new stdClass();
            $th->key = $fieldname;
            $th->name = get_string('form:class:'.$fieldname, 'local_coursemanager');
            if (in_array($fieldname, $sortfields)) {
                $th->nosort = false;
                $th->sortdesc = false;
                $th->sortasc = false;
                $myparams = array('page' => 'classoverview', 'classsort' => $fieldname, 'cmcourse' => $currentcourse->id, 'dir' => 'ASC');
                $params = array_merge($this->coursemanager->searchparams, $myparams);
                if ($this->coursemanager->classsort == $fieldname) {
                    if ($this->coursemanager->direction == 'ASC') {
                        $params['dir'] = 'DESC';
                        $th->sortdesc = new moodle_url($this->coursemanager->baseurl, $params);
                        $th->iconsort = $OUTPUT->pix_icon('t/sort_asc', 'Sort ASC');
                    } else {
                        $th->sortdesc = new moodle_url($this->coursemanager->baseurl, $params);
                        $th->iconsort = $OUTPUT->pix_icon('t/sort_desc', 'Sort DESC');
                    }
                } else {
                    $th->sortasc = new moodle_url($this->coursemanager->baseurl, $params);
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

        $classes = $this->coursemanager->classlist;
        $data->classes = array();
        $prevclassid = '';
        foreach ($classes as $class) {
            if ($class->archived == 1) {
                continue;
            }
            try {
                $timezone = new \DateTimeZone($class->usedtimezone);
            } catch (Exception $e) {
                $timezone = new \DateTimeZone(date_default_timezone_get());
            }
            $showclass = new stdClass();
            $values = array();
            foreach ($showfields as $fieldname) {
                $value = new stdClass();
                $value->value = $class->$fieldname;
                if (in_array($fieldname, $timefields)) {
                    if ($value->value == 0) {
                        $value->value = '';
                    } else {
                        $date = new \DateTime(null, $timezone);
                        $date->setTimestamp($value->value);
                        $value->value = str_ireplace([' ', 'UTC'], ['&nbsp;', 'GMT'], $date->format('d M Y H:i T'));
                    }
                }

                $value->class = '';
                if (in_array($fieldname, $numericfields)) {
                    $value->class = 'numeric';
                    if ($fieldname == 'maximumattendees' && $value->value == -1 ) {
                        $value->value = get_string('unlimited', 'local_coursemanager');
                    }
                }
                if ($fieldname == 'classname') {
                    $viewparams = array('page' => 'class',
                        'cmcourse' => $currentcourse->id,
                        'cmclass' => $class->id,
                        'start' => $this->coursemanager->start);
                    $params = array_merge($this->coursemanager->searchparams, $viewparams);
                    $viewurl = new moodle_url($this->coursemanager->baseurl, $params);
                    $value->value = html_writer::link($viewurl, $value->value);
                    $value->value .= '<a id="class'.$class->id.'" class="cmanchor">';
                }
                $values[] = $value;
            }
            if ($page->add) {
                $value = new stdClass();

                // Edit.
                $editparams = array('page' => 'class',
                    'cmcourse' => $currentcourse->id,
                    'cmclass' => $class->id,
                    'start' => $this->coursemanager->start,
                    'edit' => 1);
                $params = array_merge($this->coursemanager->searchparams, $editparams);
                $urledit = new moodle_url($this->coursemanager->baseurl, $params);
                $edit = $OUTPUT->action_icon($urledit, new \pix_icon('i/edit', get_string('edit')));
                $value->value = $edit;

                // Delegate list.
                if ($realcourse = $DB->get_record('course', array('idnumber' => $currentcourse->courseid))) {
                    $rcc = context_course::instance($realcourse->id);
                    $params = array('contextid' => $rcc->id, 'classid' => $class->classid);
                    $urldelegate = new moodle_url('/local/delegatelist/index.php', $params);
                    $delegateiconclass = 'action-icon';
                } else {
                    $urldelegate = '#';
                    $delegateiconclass = 'action-icon disabled';
                }
                $delegate = $OUTPUT->action_icon($urldelegate, new \pix_icon('i/users', get_string('users')), null, array('class' => $delegateiconclass));
                $value->value .= $delegate;

                // Duplicate
                $duplicateparams = array('duplicate' => true,
                    'cmcourse' => $currentcourse->id,
                    'cmclass' => $class->id,
                    'page' => 'class',
                    'start' => $this->coursemanager->start,
                    'edit' => 1);
                $params = array_merge($this->coursemanager->searchparams, $duplicateparams);
                $urlduplicate = new moodle_url($this->coursemanager->baseurl, $params);
                $urlduplicate->set_anchor('class' . $prevclassid);
                $duplicate = $OUTPUT->action_icon($urlduplicate, new \pix_icon('e/copy', get_string('duplicate')));
                $value->value .= $duplicate;

                // Delete.
                $deleteparams = array('action' => 'deleteclass',
                    'cmcourse' => $currentcourse->id,
                    'cmclass' => $class->id,
                    'page' => 'classoverview',
                    'start' => $this->coursemanager->start,
                    'limit' => $this->coursemanager->limit,
                    'sort' => $this->coursemanager->sort,
                    'classsort' => $this->coursemanager->classsort,
                    'dir' => $this->coursemanager->direction);
                $params = array_merge($this->coursemanager->searchparams, $deleteparams);
                $urldelete = new moodle_url($this->coursemanager->baseurl, $params);
                $urldelete->set_anchor('class' . $prevclassid);
                $prevclassid = $class->id;
                $delete = $OUTPUT->action_icon($urldelete, new \pix_icon('t/delete', get_string('delete')));
                $value->value .= $delete;

                $values[] = $value;
            }
            $showclass->pendingdelete = false;
            if ($this->coursemanager->pendingdeleteclass == $class->id) {
                $showclass->pendingdelete = true;
                $sqldelegates = "SELECT * from {local_taps_enrolment}
                                   WHERE classid = ?
                                     AND (archived is NULL OR archived = 0)";
                if ($delegates = $DB->get_records_sql($sqldelegates,
                    array($class->classid))) {
                    $showclass->numdelegates = count($delegates);
                }
                if (!empty($tapsenrolid)) {
                    $enrolparams = array('id' => $tapsenrolid,
                        'classid' => $class->classid);
                    $showclass->urldelete = new moodle_url('/mod/tapsenrol/manage_enrolments.php', $enrolparams);

                } else {
                    // Create URL to force deletion
                    $deleteparams = array('action' => 'forcedeleteclass',
                        'cmcourse' => $currentcourse->id,
                        'cmclass' => $class->id,
                        'page' => 'classoverview',
                        'start' => $this->coursemanager->start,
                        'limit' => $this->coursemanager->limit,
                        'sort' => $this->coursemanager->sort,
                        'classsort' => $this->coursemanager->classsort,
                        'dir' => $this->coursemanager->direction);
                    $params = array_merge($this->coursemanager->searchparams, $deleteparams);
                    $showclass->urldelete = new moodle_url($this->coursemanager->baseurl, $params);
                }

                
                // Cancel this action
                $cancelparams = array(
                    'cmcourse' => $currentcourse->id,
                    'page' => 'classoverview',
                    'start' => $this->coursemanager->start);
                $params = array_merge($this->coursemanager->searchparams, $cancelparams);

                $showclass->urlcancel = new moodle_url($this->coursemanager->baseurl, $params);
            }

            $showclass->values = $values;
            $data->classlist[] = $showclass;
        }
        $resendinvites = optional_param('resendinvites', 0, PARAM_INT);
        if ($resendinvites && !empty($tapsenrolid)) {
            $data->resendinvitesurl = new moodle_url(
                    '/mod/tapsenrol/resend_invites.php',
                    ['id' => $tapsenrolid, 'classid' => $resendinvites]
                    );
        }
        return $data;
    }

    function tapsenrol_id($courseid) {
        global $DB;
        if (empty($courseid)) {
            return false;
        }
        $sql = "SELECT cm.*, m.name as modname
                  FROM {modules} m, {course_modules} cm
                 WHERE cm.course = ?
                   AND cm.module = m.id
                   AND m.visible = 1
                   AND m.name = 'tapsenrol'";
        if ($tapsenrols = $DB->get_records_sql($sql, array($courseid))) {
            $tapsenrol = array_pop($tapsenrols);
            return $tapsenrol->id;
        } else {
            return false;
        }
    }
}
