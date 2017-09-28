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

use local_custom_certification\certification;
use local_custom_certification\message;

/**
 * @author Artur Rietz <artur.rietz@webanywhere.co.uk>
 */
class local_custom_certification_renderer extends \plugin_renderer_base
{

    public function display_searched_rows($items, $certifid, $param, $coursesetid = 0, $certificationtype = "")
    {
        global $OUTPUT;
        $output = '';
        $items = array_values($items);

        if ($param == 'individuals') {
            foreach ($items as $key => $item) {
                $output .= html_writer::start_div(($key % 2 == 0) ? 'user-row odd' : 'user-row', ['data-id' => $item->id]);
                $output .= html_writer::tag('span', $key + 1, ['id' => 'counter']);
                $output .= html_writer::start_div('user-data');
                $output .= html_writer::div($OUTPUT->user_picture($item, array('link' => false)), 'user-photo', []);
                $output .= html_writer::start_div('user-personal');
                $output .= html_writer::tag('p', $item->firstname . ' ' . $item->lastname, []);
                $output .= html_writer::tag('p', $item->email, []);
                $output .= html_writer::end_div();
                $output .= html_writer::end_div();
                $output .= html_writer::start_div('enrolbtn');
                $output .= html_writer::tag('input', null, ['type' => 'button', 'value' => get_string('enrollbtn', 'local_custom_certification'), 'onclick' => "addAssignmentsToTable('" . $item->id . "','individuals','" . $certifid . "');"]);
                $output .= html_writer::end_div();
                $output .= html_writer::end_div();
            }
        } elseif ($param == 'cohorts') {
            foreach ($items as $key => $item) {
                $output .= html_writer::start_div(($key % 2 == 0) ? 'user-row odd' : 'user-row', ['data-id' => $item->id]);
                $output .= html_writer::tag('span', $key + 1, ['id' => 'counter']);
                $output .= html_writer::start_div('user-data');
                $output .= html_writer::start_div('user-personal');
                $output .= html_writer::tag('p', $item->name, []);
                $output .= html_writer::end_div();
                $output .= html_writer::end_div();
                $output .= html_writer::start_div('enrolbtn');
                $output .= html_writer::tag('input', null, ['type' => 'button', 'value' => get_string('addcohortbtn', 'local_custom_certification'), 'onclick' => "addAssignmentsToTable('" . $item->id . "','cohorts','" . $certifid . "');"]);
                $output .= html_writer::end_div();
                $output .= html_writer::end_div();
            }
        } elseif ($param == 'coursesets') {
            foreach ($items as $key => $item) {
                $output .= html_writer::start_div(($key % 2 == 0) ? 'user-row odd' : 'user-row', ['data-coursesetid' => $coursesetid, 'data-id' => $item->id]);
                $output .= html_writer::tag('span', $key + 1, ['id' => 'counter']);
                $output .= html_writer::start_div('user-data');
                $output .= html_writer::start_div('user-personal');
                $output .= html_writer::tag('p', $item->fullname, []);
                $output .= html_writer::end_div();
                $output .= html_writer::end_div();
                $output .= html_writer::start_div('enrolbtn');
                $output .= html_writer::tag('input', null, ['type' => 'button', 'value' => get_string('enrollbtn', 'local_custom_certification'), 'onclick' => "createCourseset('" . $certifid . "','" . $item->id . "','" . $certificationtype . "');"]);
                $output .= html_writer::end_div();
                $output .= html_writer::end_div();
            }
        }
        return $output;
    }

    public function display_assignment_modal($items, $certifid, $type, $coursesetid = 0, $certificationtype = '')
    {
        global $OUTPUT;
        $items = array_values($items);
        $output = null;
        if ($type == 'coursesets') {
            $output = html_writer::start_div('assignment-modal-container courseset-modal');
        } else {
            $output = html_writer::start_div('assignment-modal-container');
        }
        if ($coursesetid == null) {
            $coursesetid = 0;
        }

        $output .= html_writer::start_div('assignment-modal-shadow');
        $output .= html_writer::end_div();
        $output .= html_writer::start_div('assignment-modal', ['id' => 'assignment-modal', 'data-certifid' => $certifid]);
        $output .= html_writer::start_div('assignment-modal-header');
        if ($type == 'individuals') {
            $output .= html_writer::tag('p', get_string('enrolusers', 'local_custom_certification'), []);
        } elseif ($type == 'cohorts') {
            $output .= html_writer::tag('p', get_string('enrolcohorts', 'local_custom_certification'), []);
        } elseif ($type == 'coursesets') {
            $output .= html_writer::tag('p', get_string('enrolcourses', 'local_custom_certification'), []);
        }

        $output .= html_writer::div(' ', 'close', ['onclick' => "closeAssignmentModal();", 'data-reload' => 0]);
        $output .= html_writer::end_div();
        $output .= html_writer::start_div('assignment-modal-search');
        $output .= html_writer::tag('input', '', ['class' => 'search_input', 'id' => 'search-input']);
        $output .= html_writer::tag('input', '', ['value' => get_string('searchbtn', 'local_custom_certification'), 'id' => 'search-btn', 'type' => 'button', 'onclick' => "search('" . $type . "','" . $certifid . "','search','" . $coursesetid . "','" . $certificationtype . "');"]);
        $output .= html_writer::tag('input', '', ['value' => get_string('resetbtn', 'local_custom_certification'), 'id' => 'reset-btn', 'data-coursesetid' => $coursesetid, 'type' => 'button', 'onclick' => "search('" . $type . "','" . $certifid . "','reset','" . $coursesetid . "','" . $certificationtype . "');"]);
        $output .= html_writer::end_div();
        $output .= html_writer::start_div('counter');
        $output .= html_writer::tag('span', count($items), ['id' => 'count-value']);
        if ($type == 'individuals') {
            $output .= html_writer::tag('span', get_string('enrolleduserscounter', 'local_custom_certification'), []);
        } elseif ($type == 'cohorts') {
            $output .= html_writer::tag('span', get_string('enrolledcohortscounter', 'local_custom_certification'), []);
        } elseif ($type == 'coursesets') {
            $output .= html_writer::tag('span', get_string('enrolledcoursescounter', 'local_custom_certification'), []);
        }

        $output .= html_writer::end_div();
        $output .= html_writer::start_div('modal_body');

        foreach ($items as $key => $item) {
            if ($type == strtolower('individuals')) {
                $output .= html_writer::start_div(($key % 2 == 0) ? 'user-row odd' : 'user-row', ['data-id' => $item->id]);
                $output .= html_writer::tag('span', $key + 1, ['id' => 'counter']);
                $output .= html_writer::start_div('user-data');
                $output .= html_writer::div($OUTPUT->user_picture($item, array('link' => false)), 'user-photo', []);
                $output .= html_writer::start_div('user-personal');
                $output .= html_writer::tag('p', $item->firstname . ' ' . $item->lastname, []);
                $output .= html_writer::tag('p', $item->email, []);
                $output .= html_writer::end_div();
                $output .= html_writer::end_div();
                $output .= html_writer::start_div('enrolbtn');
                $output .= html_writer::tag('input', null, ['type' => 'button', 'value' => get_string('enrollbtn', 'local_custom_certification'), 'onclick' => "addAssignmentsToTable('" . $item->id . "','individuals','" . $certifid . "');"]);
                $output .= html_writer::end_div();
                $output .= html_writer::end_div();
            } elseif ($type == strtolower('cohorts')) {
                $output .= html_writer::start_div(($key % 2 == 0) ? 'user-row odd' : 'user-row', ['data-id' => $item->id]);
                $output .= html_writer::tag('span', $key + 1, ['id' => 'counter']);
                $output .= html_writer::start_div('user-data');
                $output .= html_writer::start_div('user-personal');
                $output .= html_writer::tag('p', $item->name, []);
                $output .= html_writer::end_div();
                $output .= html_writer::end_div();
                $output .= html_writer::start_div('enrolbtn');
                $output .= html_writer::tag('input', null, ['type' => 'button', 'value' => get_string('addcohortbtn', 'local_custom_certification'), 'onclick' => "addAssignmentsToTable('" . $item->id . "','cohorts','" . $certifid . "');"]);
                $output .= html_writer::end_div();
                $output .= html_writer::end_div();
            } elseif ($type == strtolower('coursesets')) {
                $output .= html_writer::start_div(($key % 2 == 0) ? 'user-row odd' : 'user-row', ['data-coursesetid' => $coursesetid, 'data-id' => $item->id]);
                $output .= html_writer::tag('span', $key + 1, ['id' => 'counter']);
                $output .= html_writer::start_div('user-data');
                $output .= html_writer::start_div('user-personal');
                $output .= html_writer::tag('p', $item->fullname, []);
                $output .= html_writer::end_div();
                $output .= html_writer::end_div();
                $output .= html_writer::start_div('enrolbtn');
                if($type == 'coursesets'){
                    $output .= html_writer::tag('input', null, ['type' => 'button', 'value' => get_string('addbtn', 'local_custom_certification'), 'onclick' => "createCourseset('" . $certifid . "','" . $item->id . "','" . $certificationtype . "');"]);
                }else{
                    $output .= html_writer::tag('input', null, ['type' => 'button', 'value' => get_string('enrollbtn', 'local_custom_certification'), 'onclick' => "createCourseset('" . $certifid . "','" . $item->id . "','" . $certificationtype . "');"]);
                }
                $output .= html_writer::end_div();
                $output .= html_writer::end_div();
            }
        }
        $output .= html_writer::end_div();
        $output .= html_writer::start_div('assignment-modal-footer');
        if ($type == 'individuals') {
            $output .= html_writer::tag('input', null, ['type' => 'button', 'value' => get_string('finishenrollusersbtn', 'local_custom_certification'), 'onclick' => "closeAssignmentModal();"]);
        } elseif ($type == 'cohorts') {
            $output .= html_writer::tag('input', null, ['type' => 'button', 'value' => get_string('finishenrollcohortsbtn', 'local_custom_certification'), 'onclick' => "closeAssignmentModal();"]);
        } elseif ($type == 'coursesets') {
            $output .= html_writer::tag('input', null, ['type' => 'button', 'value' => get_string('finishenrollcoursesbtn', 'local_custom_certification'), 'onclick' => "closeAssignmentModal();"]);
        }
        $output .= html_writer::end_div();
        $output .= html_writer::end_div();
        $output .= html_writer::end_div();
        return $output;
    }

    public function display_coursesetbox($coursesets, $certifid, $certificationtype)
    {
        global $OUTPUT;
        $output = "";
        $coursesetiterator = 1;
        $allcoursesetscount = count($coursesets);
        $selectnextoperatoroptions = [];
        $selectnextoperatoroptions[certification::NEXTOPERATOR_AND] = get_string('andoperator', 'local_custom_certification');
        $selectnextoperatoroptions[certification::NEXTOPERATOR_OR] = get_string('oroperator', 'local_custom_certification');
        $selectnextoperatoroptions[certification::NEXTOPERATOR_THEN] = get_string('thenoperator', 'local_custom_certification');
        foreach ($coursesets as $coursesetarraykey => $courseset) {
            $output .= html_writer::start_div('coursesetbox', ['data-coursesetid' => $courseset->id, 'data-certificationtype' => $courseset->certifpath]);
            $output .= html_writer::tag('p', $courseset->label, ['class' => 'headerinfo']);
            $output .= html_writer::start_div('coursesetoptions');
            if ($coursesetiterator > 1) {
                $output .= html_writer::tag('input', '', ['class' => 'moveupcourseset', 'type' => 'button', 'value' => get_string('moveupbtn', 'local_custom_certification'), 'onclick' => "coursesetControl('" . $courseset->id . "','0','coursesetsort','moveup','" . $certifid . "','" . $certificationtype . "');"]);
            }
            if ($coursesetiterator < $allcoursesetscount) {
                $output .= html_writer::tag('input', '', ['class' => 'movedowncourseset', 'type' => 'button', 'value' => get_string('movedownbtn', 'local_custom_certification'), 'onclick' => "coursesetControl('" . $courseset->id . "','0','coursesetsort','movedown','" . $certifid . "','" . $certificationtype . "');"]);
            }
            $output .= html_writer::tag('input', '', ['class' => 'deletecourseset', 'type' => 'button', 'value' => get_string('deletebtn', 'local_custom_certification'), 'onclick' => "deleteCourseset('" . $courseset->id . "','" . $courseset->certifpath . "','" . $certifid . "');"]);

            $output .= html_writer::end_div();
            $output .= html_writer::start_div('content');
            $output .= html_writer::start_div('labels');
            $output .= html_writer::tag('p', get_string('coursesetsetname', 'local_custom_certification'), []);
            $output .= html_writer::tag('p', get_string('coursesetcompletiontype', 'local_custom_certification'), []);
            $output .= html_writer::tag('p', get_string('coursesetmincourses', 'local_custom_certification'), ['class' => 'coursesetmincourses']);
            $output .= html_writer::tag('p', get_string('coursesetmincourseserror', 'local_custom_certification'), ['class' => 'coursesetmincourseserror custom-error']);
            $output .= html_writer::tag('p', get_string('coursesetcourses', 'local_custom_certification'), []);
            $output .= html_writer::end_div();

            $output .= html_writer::start_div('inputs');
            $output .= html_writer::tag('input', '', ['value' => $courseset->label, 'class' => 'coursesetname', 'data-coursesetid' => $courseset->id]);


            $selectoptions[certification::COMPLETION_TYPE_ALL] = get_string('allcoursesoption', 'local_custom_certification');
            $selectoptions[certification::COMPLETION_TYPE_ANY] = get_string('onecoursesoption', 'local_custom_certification');
            $selectoptions[certification::COMPLETION_TYPE_SOME] = get_string('somecoursesoption', 'local_custom_certification');

            $output .= html_writer::select($selectoptions, '', $courseset->completiontype, []);

            if ($courseset->completiontype == certification::COMPLETION_TYPE_SOME) {
                $output .= html_writer::tag('input', '', ['id' => 'completioncount', 'value' => $courseset->mincourses == 0 ? '' : $courseset->mincourses, 'class' => 'completioncount', 'data-count' => count($courseset->courses)]);
            } else {
                $output .= html_writer::tag('input', '', ['id' => 'completioncount', 'value' => $courseset->mincourses == 0 ? '' : $courseset->mincourses, 'disabled' => '', 'class' => 'completioncount', 'data-count' => count($courseset->courses)]);
            }
            $output .= html_writer::tag('p', get_string('completioncounterror', 'local_custom_certification'), ['class' => 'custom-error']);

            $output .= html_writer::start_div('courses-list', []);
            $courseiterator = 1;
            $coursescount = count($courseset->courses);
            if (isset($courseset->courses)) {
                foreach ($courseset->courses as $coursearraykey => $course) {
                    $output .= html_writer::start_div('course', ['data-courseid' => $course->courseid]);
                    $output .= html_writer::tag('span', $course->fullname, []);
                    $output .= html_writer::tag('img', '', ['src' => $OUTPUT->pix_url("/t/delete"), 'onclick' => "deleteCourse('" . $courseset->id . "','" . $course->courseid . "','" . $courseset->certifpath . "','" . $certifid . "');"]);

                    if ($courseiterator < $coursescount) {
                        $output .= html_writer::tag('img', '', ['src' => $OUTPUT->pix_url("/t/down"), 'onclick' => "coursesetControl('" . $courseset->id . "','" . $course->courseid . "','coursesort','movedown','" . $certifid . "','" . $certificationtype . "');"]);
                    }
                    if ($courseiterator > 1) {
                        $output .= html_writer::tag('img', '', ['src' => $OUTPUT->pix_url("/t/up"), 'onclick' => "coursesetControl('" . $courseset->id . "','" . $course->courseid . "','coursesort','moveup','" . $certifid . "','" . $certificationtype . "');"]);
                    }

                    $output .= html_writer::end_div();
                    $courseiterator++;
                }
            }

            $output .= html_writer::end_div();
            $output .= html_writer::tag('input', '', ['id' => 'addcoursebtn', 'class' => 'form-submit', 'type' => 'button', 'value' => get_string('addcourse', 'local_custom_certification'), 'onclick' => "search('coursesets','" . $certifid . "','append','" . $courseset->id . "','" . $certificationtype . "');"]);
            $output .= html_writer::end_div();

            $output .= html_writer::end_div();
            $output .= html_writer::end_div();

            if ($allcoursesetscount > $coursesetiterator) {
                $output .= html_writer::select($selectnextoperatoroptions, '', $courseset->nextoperator, '');
            }

            $coursesetiterator++;
        }


        return $output;
    }
    public function display_coursesetbox_summary_editform($coursesets){
        $output = '';
        if(count($coursesets) > 0){
            $i = 0;
            $selectnextoperatoroptions = [];
            $selectnextoperatoroptions[certification::NEXTOPERATOR_AND] = get_string('andoperator', 'local_custom_certification');
            $selectnextoperatoroptions[certification::NEXTOPERATOR_OR] = get_string('oroperator', 'local_custom_certification');
            $selectnextoperatoroptions[certification::NEXTOPERATOR_THEN] = get_string('thenoperator', 'local_custom_certification');
            foreach ($coursesets as $coursesetarraykey => $courseset) {
                if (!isset($coursesetgroups[$i])) {
                    $coursesetgroups[$i] = [];
                }
                $coursesetgroups[$i][] = $courseset;
                if ($courseset->nextoperator == certification::NEXTOPERATOR_OR) {
                    $i++;
                }
            }
            $output .= html_writer::start_div('summary-details');
            $output .= html_writer::start_div('labels');
            $output .= \html_writer::tag('p', get_string('instructions:usermustcomplete', 'local_custom_certification'), ['class' => 'instructions']);
            $output .= html_writer::end_div();
            $output .= html_writer::start_div('inputs');
            $output .= html_writer::start_tag('p');
            foreach($coursesetgroups as $k => $courssetgroup){
                foreach($courssetgroup as $kk => $courseset){
                    $output .= $courseset->label.' ';
                    if($kk < count($courssetgroup) - 1){
                        $output .= html_writer::span($selectnextoperatoroptions[isset($courseset->nextoperator) ? $courseset->nextoperator : certification::NEXTOPERATOR_AND].' ', 'bold');
                    }
                }
                if($k < count($coursesetgroups) - 1){
                    $output .= html_writer::end_tag('p');
                    $output .= html_writer::tag('p', $selectnextoperatoroptions[certification::NEXTOPERATOR_OR].' ', ['class' => 'bold']);
                    $output .= html_writer::start_tag('p');
                }
            }
            $output .= html_writer::end_tag('p');
            $output .= html_writer::end_div();
            $output .= html_writer::end_div();
        }

        return $output;
    }

    
    public function display_coursesetbox_summary($coursesets){
        $output = '';
        if(count($coursesets) > 0){
            $i = 0;
            $selectnextoperatoroptions = [];
            $selectnextoperatoroptions[certification::NEXTOPERATOR_AND] = get_string('andoperator', 'local_custom_certification');
            $selectnextoperatoroptions[certification::NEXTOPERATOR_OR] = get_string('oroperator', 'local_custom_certification');
            $selectnextoperatoroptions[certification::NEXTOPERATOR_THEN] = get_string('thenoperator', 'local_custom_certification');
            foreach ($coursesets as $coursesetarraykey => $courseset) {
                if (!isset($coursesetgroups[$i])) {
                    $coursesetgroups[$i] = [];
                }
                $coursesetgroups[$i][] = $courseset;
                if ($courseset->nextoperator == certification::NEXTOPERATOR_OR) {
                    $i++;
                }
            }
            $output .= html_writer::start_div('summary-details');
            $output .= html_writer::start_div('labels');
            $output .= \html_writer::tag('p', get_string('instructions:youmustcomplete', 'local_custom_certification'), ['class' => 'instructions']);
            $output .= html_writer::end_div();
            $output .= html_writer::start_div('inputs');
            $output .= html_writer::start_tag('span', ['class' => 'critera-wrapper']);
            foreach($coursesetgroups as $k => $courssetgroup){
                foreach($courssetgroup as $kk => $courseset){
                    $output .= html_writer::span($courseset->label, 'criterium');
                    if($kk < count($courssetgroup) - 1){
                        $output .= html_writer::span($selectnextoperatoroptions[isset($courseset->nextoperator) ? $courseset->nextoperator : certification::NEXTOPERATOR_AND].' ', 'operator');
                    }
                }
                if($k < count($coursesetgroups) - 1){
                    $output .= html_writer::end_tag('span');
                    $output .= html_writer::tag('span', $selectnextoperatoroptions[certification::NEXTOPERATOR_OR].' ', ['class' => 'operator']);
                    $output .= html_writer::start_tag('span', ['class' => 'critera-wrapper']);
                }
            }
            $output .= html_writer::end_tag('span');
            $output .= html_writer::end_div();
            $output .= html_writer::end_div();
        }

        return $output;
    }

    public function display_message_box($messageid, $messagename, $messagetype, $recipient = 0, $recipientemail = null, $messagesubject = null, $messagebody = null, $messagetriggertime = null)
    {
        global $OUTPUT;
        $output = '';
        !empty($messageid) && $messageid > 0 ? $id = $messageid : $id = 0;
        $output .= html_writer::start_div('message-box', ['data-messagetype' => $messagetype, 'data-id' => $id]);
        $output .= html_writer::tag('p', strtoupper($messagename), ['class' => 'headerinfo']);
        $output .= html_writer::start_tag('input', ['onclick' => "if(confirm('" . get_string('removemessageconfirmdialog', 'local_custom_certification') . "')) deleteMessage(this);", 'class' => 'delete-message', 'type' => 'button', 'value' => get_string('deletebtn', 'local_custom_certification'), 'data-id' => $messageid]);

        $output .= html_writer::start_div('message-content');
        $output .= html_writer::start_div('helpbox');
        $output .= html_writer::tag('span', get_string('subject', 'local_custom_certification'), ['class' => 'message-label']);
        $output .= $OUTPUT->help_icon('subject', 'local_custom_certification');
        $output .= html_writer::end_div();
        $output .= html_writer::tag('p', get_string('error:subjectemptyfield', 'local_custom_certification'), ['class' => 'emptysubject custom-error']);
        !empty($messagesubject) ? $subjectvalue = $messagesubject : $subjectvalue = '';
        $output .= html_writer::tag('input', '', ['name' => 'messagesubject', 'class' => 'messagesubject', 'type' => 'text', 'value' => $subjectvalue]);
        $output .= html_writer::start_div('helpbox');
        $output .= html_writer::tag('span', get_string('messagelabel', 'local_custom_certification'), ['class' => 'message-label']);
        $output .= $OUTPUT->help_icon('message', 'local_custom_certification');
        $output .= html_writer::end_div();
        !empty($messagebody) ? $bodyvalue = $messagebody : $bodyvalue = '';
        $output .= html_writer::tag('textarea', $bodyvalue, ['rows' => '4', 'cols' => '50', 'class' => 'messagetext']);

        if (($messagetype == message::TYPE_RECERTIFICATION_WINDOW_OPEN) || ($messagetype == message::TYPE_CERTIFICATION_BEFORE_EXPIRATION)) {
            $output .= html_writer::start_div('helpbox');
            $output .= html_writer::tag('span', get_string('daysbeforelabel', 'local_custom_certification'), ['class' => 'message-label']);
            $output .= $OUTPUT->help_icon('before', 'local_custom_certification');
            $output .= html_writer::end_div();
            !empty($messagetriggertime) ? $daysvalue = date('z', $messagetriggertime) : $daysvalue = 0;
            $output .= html_writer::tag('input', '', ['class' => 'messagetriggertime', 'type' => 'text', 'value' => $daysvalue]);
        }

        $output .= html_writer::start_div('helpbox');
        $output .= html_writer::tag('span', get_string('additionalchecklabel', 'local_custom_certification'), ['class' => 'message-label']);
        $output .= $OUTPUT->help_icon('additionalcheckhelp', 'local_custom_certification');
        $output .= html_writer::end_div();

        if ($recipient == 1) {
            $recipientvalue = true;
            $displaynoneclass = '';
        } else {
            $displaynoneclass = 'notconfirmed';
            $recipientvalue = false;
        }
        $output .= html_writer::checkbox('additionalcheck', '', $recipientvalue, false, ['class' => 'additionalcheck']);

        $output .= html_writer::start_div('additionalrecipientbox ' . $displaynoneclass);
        $output .= html_writer::start_div('helpbox additional');
        $output .= html_writer::tag('span', get_string('additionalrecipientlabel', 'local_custom_certification'));
        $output .= $OUTPUT->help_icon('additionalrecipient', 'local_custom_certification');
        $output .= html_writer::end_div();
        !empty($recipientemail) ? $recipientemailvalue = $recipientemail : $recipientemailvalue = '';
        $output .= html_writer::tag('p', get_string('error:emailemptyfield', 'local_custom_certification'), ['class' => 'emptyemail custom-error']);
        $output .= html_writer::tag('p', get_string('error:emailinvalid', 'local_custom_certification'), ['class' => 'invalidemail custom-error']);
        $output .= html_writer::tag('input', '', ['name' => 'additionalinput', 'class' => 'additionalinput', 'type' => 'text', 'value' => $recipientemailvalue]);
        $output .= html_writer::end_div();
        $output .= html_writer::end_div();

        $output .= html_writer::end_div();

        return $output;
    }

    public function display_certifications($certifications)
    {
        global $OUTPUT;

        $output = html_writer::start_div('certifications-container');
        if(count($certifications) == 0){
            $output .= html_writer::div(get_string('nocertificationfound', 'local_custom_certification'));
        }
        
        $output .= html_writer::start_tag('table', ['class' => 'generaltable certifications-table']);

        $output .= html_writer::start_tag('tr', []);
        $output .= html_writer::tag('th', get_string('idnumberprogram', 'local_custom_certification'), []);
        $output .= html_writer::tag('th', get_string('certification', 'local_custom_certification'), []);
        $output .= html_writer::tag('th', get_string('category', 'local_custom_certification'), []);
        $output .= html_writer::tag('th', get_string('visibleprogram', 'local_custom_certification'), ['class' => 'certif-actions']);
        $output .= html_writer::tag('th', get_string('headeractions', 'local_custom_certification'), ['class' => 'certif-actions']);
        $output .= html_writer::end_tag('tr');



        foreach ($certifications as $certification) {
            $output .= html_writer::start_tag('tr', []);
            $output .= html_writer::tag('td', $certification->id);
            $output .= html_writer::start_tag('td');
            $output .= html_writer::tag('a', $certification->fullname, ['href' => new moodle_url('/local/custom_certification/overview.php', ['id' => $certification->id])]);
            $output .= html_writer::end_tag('td');
            $output .= html_writer::tag('td', $certification->categoryname);
            $output .= html_writer::tag('td', ($certification->visible ? html_writer::tag('img', '', ['src' => $OUTPUT->pix_url("e/tick")]) : ''), ['class' => 'certif-actions']);
            $output .= html_writer::start_tag('td', ['class' => 'certif-actions']);

            $output .= html_writer::start_tag('a', ['href' => new moodle_url('/local/custom_certification/edit.php', ['action' => 'details', 'id' => $certification->id])]);
            $output .= html_writer::tag('img', '', ['src' => $OUTPUT->pix_url("t/edit"), 'title' => get_string('edit', 'local_custom_certification')]);
            $output .= html_writer::end_tag('a');

            $output .= html_writer::start_tag('a', ['href' => new moodle_url('/local/custom_certification/index.php', ['delete' => $certification->id]),
                'onclick' => "return confirm('" . get_string('deleteconfirm', 'local_custom_certification') . "')"]);
            $output .= html_writer::tag('img', '', ['src' => $OUTPUT->pix_url("t/delete"), 'title' => get_string('delete', 'local_custom_certification')]);
            $output .= html_writer::end_tag('a');

            $output .= html_writer::start_tag('a', ['href' => new moodle_url('/local/custom_certification/index.php', ['copy' => $certification->id])]);
            $output .= html_writer::tag('img', '', ['src' => $OUTPUT->pix_url("t/copy"), 'title' => get_string('copy', 'local_custom_certification')]);
            $output .= html_writer::end_tag('a');

            $output .= html_writer::end_tag('td');
            $output .= html_writer::end_tag('tr');
        }

        $output .= html_writer::end_tag('table');


        $output .= html_writer::start_div('certifications-footer', []);
        $output .= html_writer::start_tag('form', ['action' => new moodle_url('/local/custom_certification/add.php')]);
        $output .= html_writer::tag('input', '', ['id' => 'addcertif', 'type' => 'submit', 'value' => get_string('addcertification', 'local_custom_certification')]);
        $output .= html_writer::end_tag('form');
        $output .= html_writer::end_div();


        $output .= html_writer::end_div();
        return $output;
    }

    public function display_overview($certif, $urlcertifid, $viewinguser, $userfullname, $assignmentdata,
                                     $capability, $enrolleduser, $isrecertif, $progress, $usercertificationdetails, $ragstatus)
    {
        if ($certif->id == null || ($certif->visible == 0 && !$capability) || $certif->deleted == 1) {
            return html_writer::tag('h2', get_string('nocertification', 'local_custom_certification', $urlcertifid), ['class' => 'no-information']);
        }

        $output = html_writer::start_div('certification-overview', []);
        if ($capability) {
            $url = new moodle_url('/local/custom_certification/edit.php', ['action' => 'details', 'id' => $certif->id]);
            $output .= html_writer::tag('button', get_string('editcertificationbtn', 'local_custom_certification'), ['onclick' => 'window.location=\''.$url->out(false).'\'', 'class' => 'btn-primary edit-cert-btn']);
        }

        if (!$enrolleduser && !$capability) {
            $output .= html_writer::tag('h5', get_string('notenrolleduser', 'local_custom_certification'), ['class' => 'not-enrolled']);
        }

        if ($capability && $viewinguser) {
            if ($enrolleduser) {
                $output .= html_writer::tag('p', get_string('viewinguser', 'local_custom_certification', $userfullname), ['class' => 'viewing-user']);
            } else {
                $output .= html_writer::tag('p', get_string('viewingusernotenrolled', 'local_custom_certification', $userfullname), ['class' => 'viewing-user viewing-user-notenrolled']);
            }
        }

        $output .= html_writer::start_div('progress-container ', []);

        if ($enrolleduser) {

            if ($usercertificationdetails->overdue) {
                $duedate = get_string('certifstatusred', 'local_custom_certification');
            } else if ($usercertificationdetails->duedate > 0) {
                $duedate = userdate($usercertificationdetails->duedate, get_string('strftimedate'));
            } else {
                $duedate = get_string('duedatenotset', 'local_custom_certification');
            }
            $class = '';
            if ($usercertificationdetails->overdue == 1 || $usercertificationdetails->windowopen == 1) {
                $class = 'certifduedatedate';
            }

            $certificationprogress = $isrecertif ? $progress['recertification'] : $progress['certification'];
            $output .= html_writer::start_div('circle-status', ['title' => get_string('certifstatus'.$ragstatus, 'local_custom_certification')]);
            $output .= html_writer::tag('span', get_string('certifduedate', 'local_custom_certification', html_writer::span($duedate, 'bold')), ['class' => $class]);

            $output .= html_writer::div('', 'custom-circle', ['class' => 'status-' . $ragstatus]);
            $output .= html_writer::end_div();
        }
        $output .= html_writer::end_div();

        $output .= html_writer::tag('h2', $certif->fullname, []);
        if($enrolleduser && $certificationprogress == 100 && $ragstatus != \local_custom_certification\completion::RAG_STATUS_GREEN){
            $output .= html_writer::div(get_string('statusnote', 'local_custom_certification'), 'alert alert-warning');
        }

        if(!empty($certif->summary)){
            $output .= format_text($certif->summary, FORMAT_MOODLE);
        }

        $contentfiles = '';
        foreach ($certif->get_files() as $file) {
            $url = moodle_url::make_pluginfile_url($file->get_contextid(), 'local_custom_certification',
                $file->get_filearea(), $certif->id, $file->get_filepath(), $file->get_filename(), true);

            $image = $this->output->pix_icon(file_file_icon($file, 24), $file->get_filename(), 'moodle');
            $filename = html_writer::tag('span', $image, array('class' => 'fp-icon')).
                html_writer::tag('span', $file->get_filename(), array('class' => 'fp-filename'));
            $contentfiles .= html_writer::tag('span',
                html_writer::link($url, $filename),
                array('class' => 'coursefile fp-filename-icon'));

        }

        $output .= html_writer::tag('p', $contentfiles);

        if ($enrolleduser) {
            $output .= html_writer::start_tag('p', ['class' => 'completioncriteriainfo']);
            $output .= get_string('instructions:completioncriteria', 'local_custom_certification');

            $output .= html_writer::start_tag('ul');
            foreach($assignmentdata as $assignment){
                if (!empty($assignment->cohortname)) {
                    $output .= html_writer::tag('li', get_string('cohortcompletioncriteria', 'local_custom_certification', $assignment->cohortname).($assignment->duedatetype == certification::DUE_DATE_NOT_SET ? ' ('.get_string('optional', 'local_custom_certification').')' : ''), ['class' => 'bold completioncriteriainfo']);
                } else {
                    $output .= html_writer::tag('li', get_string('individualcompletioncriteria', 'local_custom_certification'), ['class' => 'bold completioncriteriainfo']);
                }
            }
            $output .= html_writer::end_tag('ul');
            $output .= html_writer::end_tag('p');
        }

        if ($enrolleduser) {
            if (!$isrecertif) {
                if (!empty($certif->certificationcoursesets)) {
                    if(count($certif->certificationcoursesets) > 1){
                        $output .= $this->display_coursesetbox_summary($certif->certificationcoursesets);
                    }
                    $output .= $this->overview_courseset($certif, $certif->certificationcoursesets, $capability, $enrolleduser, $isrecertif, $progress, $ragstatus);
                } else {
                    $output .= html_writer::tag('p', get_string('certificationnotset', 'local_custom_certification'), ['class' => 'certif-label  not-set']);
                }
            } else {
                if (!empty($certif->recertificationcoursesets)) {
                    if(count($certif->recertificationcoursesets) > 1){
                        $output .= $this->display_coursesetbox_summary($certif->recertificationcoursesets);
                    }
                    $output .= $this->overview_courseset($certif, $certif->recertificationcoursesets, $capability, $enrolleduser, $isrecertif, $progress, $ragstatus);
                } else {
                    $output .= html_writer::tag('p', get_string('recertificationnotset', 'local_custom_certification'), ['class' => 'certif-label  not-set']);
                }
            }
        } else {
            if (!empty($certif->certificationcoursesets)) {
                if(count($certif->certificationcoursesets) > 1){
                    $output .= $this->display_coursesetbox_summary($certif->certificationcoursesets);
                }
                $output .= $this->overview_courseset($certif, $certif->certificationcoursesets, $capability, $enrolleduser, $isrecertif, $progress, $ragstatus);
            } else {
                $output .= html_writer::tag('p', get_string('certificationnotset', 'local_custom_certification'), ['class' => 'certif-label  not-set']);
            }

            if (!empty($certif->recertificationcoursesets)) {
                if(count($certif->recertificationcoursesets) > 1){
                    $output .= $this->display_coursesetbox_summary($certif->recertificationcoursesets);
                }
                $output .= $this->overview_courseset($certif, $certif->recertificationcoursesets, $capability, $enrolleduser, $isrecertif, $progress, $ragstatus);
            } else {
                $output .= html_writer::tag('p', get_string('recertificationnotset', 'local_custom_certification'), ['class' => 'certif-label  not-set']);
            }
        }

        $output .= html_writer::tag('p', $certif->endnote, ['class' => 'certif-value']);
        $output .= html_writer::end_div();
        return $output;
    }

    private function overview_courseset($certif, $coursesets, $capability, $enrolleduser, $isrecertif, $progress, $ragstatus)
    {
        $output = '';
        foreach ($coursesets as $courseset) {
            $output .= html_writer::start_div('overviewcourseset', ['class' => (count($coursesets) > 1 ? 'collapsed' : '')]);

            $completiontypeinfo = '';
            if (count($courseset->courses) > 1) {
                switch ($courseset->completiontype) {
                    case local_custom_certification\certification::COMPLETION_TYPE_ALL:
                        $completiontypeinfo = get_string('completiontypeallinfo', 'local_custom_certification');
                        break;
                    case local_custom_certification\certification::COMPLETION_TYPE_ANY:
                        $completiontypeinfo = get_string('completiontypeanyinfo', 'local_custom_certification');
                        break;
                    case local_custom_certification\certification::COMPLETION_TYPE_SOME:
                        $completiontypeinfo = get_string('completiontypesomeinfo', 'local_custom_certification');
                        break;
                }
            }
            if (count($courseset->courses) > 1 || count($coursesets) > 1) {
                $output .= html_writer::start_div('coursesetbox overview-coursesetbox', []); // START - Course set box.
                $info = ($completiontypeinfo) ? html_writer::tag('span', $completiontypeinfo, []) : '';
                $output .= html_writer::tag('h3', $courseset->label . $info, []);
            }
            $output .= html_writer::start_div('overview-coursesetbox-table');
            $output .= html_writer::start_tag('table', ['class' => 'generaltable']);
            $output .= html_writer::start_tag('tr', []);
            $output .= html_writer::tag('th', get_string('coursename', 'local_custom_certification'), []);
            $output .= html_writer::tag('th', get_string('headerprogress', 'local_custom_certification'), []);
            $output .= html_writer::tag('th', get_string('headeractions', 'local_custom_certification'), ['class' => 'certification-overview-actions text-center']);
            $output .= html_writer::end_tag('tr');


            foreach ($courseset->courses as $course) {
                $statusoutput = '';
                $progressoutput = '';
                if ($enrolleduser) {
                    $courseprogress = isset($progress['courses'][$course->courseid]) ? $progress['courses'][$course->courseid] : 0;
                    if ($courseprogress == 0) {
                        $progressoutput .= get_string('coursenotstarted', 'local_custom_certification');
                        $statusoutput .= html_writer::div('', 'custom-circle', ['class' => 'status-red']);
                    } else {
                        $progressoutput .= html_writer::start_div('custom-progressbar', ['title' => $courseprogress . '%']);
                        $progressoutput .= html_writer::div('', 'custom-progress', ['style' => 'width:' . $courseprogress . '%;', 'class' => 'status-green']);
                        $progressoutput .= html_writer::end_div();
                        $statusoutputrag = ($courseprogress == 100 ? 'green' : 'amber');
                        $statusoutput .= html_writer::div('', 'custom-circle', ['class' => 'status-' . $statusoutputrag]);
                    }
                }

                $output .= html_writer::start_tag('tr', []);
                $output .= html_writer::tag('td', $statusoutput . $course->fullname, []);

                $output .= html_writer::tag('td', $progressoutput);

                if ($capability || $enrolleduser) {
                    $output .= html_writer::start_tag('td', ['class' => 'certification-overview-actions']);
                    $urlparams = [];
                    $urlparams['id'] = $course->courseid;
                    $url = new moodle_url('/course/view.php', $urlparams);
                    $output .= html_writer::link($url, get_string('launchcourse', 'local_custom_certification'), ['class' => 'btn btn-small btn-primary']);
                    $output .= html_writer::end_tag('td');
                } else {
                    $output .= html_writer::tag('td', get_string('notavailable', 'local_custom_certification'), ['class' => 'launchcourse']);
                }

                $output .= html_writer::end_tag('tr');

            }

            $output .= html_writer::end_tag('table');
            $output .= html_writer::end_div();

            if (count($courseset->courses) > 1 || count($coursesets) > 1) {
                $output .= html_writer::end_div(); // END - Course set box.
            }

            $output .= html_writer::end_div();
        }
        return $output;
    }

    public function display_no_user_information($userid)
    {
        return html_writer::tag('h3', get_string('nouser', 'local_custom_certification', $userid), ['class' => 'no-information']);
    }

    public function get_duedate_modal($duedateform)
    {
        $output = html_writer::start_div('modal-wrapper');
        $output .= html_writer::start_div('duedate-modal');
        $output .= html_writer::start_div('assignment-modal-header');
        $output .= html_writer::tag('p', get_string('headermodalassignmentduedate', 'local_custom_certification'));
        $output .= html_writer::tag('div', '', ['class' => 'close', "onclick" => "closeDueDateModal();"]);

        $output .= html_writer::end_div();
        $output .= $duedateform;

        $output .= html_writer::end_div();
        $output .= html_writer::end_div();
        return $output;

    }

    public function get_cohort_user_duedates($cohortuserassignments)
    {
        $output = html_writer::start_div('modal-wrapper');
        $output .= html_writer::start_div('duedate-modal');
        $output .= html_writer::start_div('assignment-modal-header');
        $output .= html_writer::tag('p', get_string('headermodalassignmentduedate', 'local_custom_certification'));
        $output .= html_writer::tag('div', '', ['class' => 'close', "onclick" => "closeDueDateModal();"]);

        $output .= html_writer::end_div();
        $output .= html_writer::start_tag('table', ['class' => 'generaltable']);
        $output .= html_writer::start_tag('tr');
        $output .= html_writer::tag('th', get_string('headeruser', 'local_custom_certification'));
        $output .= html_writer::tag('th', get_string('headercohortactualduedate', 'local_custom_certification'));
        $output .= html_writer::end_tag('tr');

        foreach ($cohortuserassignments as $user) {
            $output .= html_writer::start_tag('tr');
            $output .= html_writer::tag('td', fullname($user));
            if ((int)$user->userassignmentid == 0) {
                $output .= html_writer::tag('td', get_string('notyetadded', 'local_custom_certification'));
            } elseif ($user->exist == certification::ASSIGNMENT_SAME_INSTANCE) {
                if ($user->duedate > 0) {
                    $output .= html_writer::tag('td', date('d/m/Y', $user->duedate));
                } else {
                    $output .= html_writer::tag('td', get_string('duedatenotset', 'local_custom_certification'));
                }

            } else {
                if ($user->duedate > certification::DUE_DATE_NOT_SET) {
                    $output .= html_writer::tag('td', get_string('enrolledbyothermethod', 'local_custom_certification', date('d/m/Y', $user->duedate)));
                } else {
                    $output .= html_writer::tag('td', get_string('enrolledbyothermethod', 'local_custom_certification', get_string('duedatenotset', 'local_custom_certification')));
                }
            }
            $output .= html_writer::end_tag('tr');
        }

        $output .= html_writer::end_tag('table');
        $output .= html_writer::end_div();
        $output .= html_writer::end_div();
        return $output;
    }
}