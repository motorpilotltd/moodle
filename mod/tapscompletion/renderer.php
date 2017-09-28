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

class mod_tapscompletion_renderer extends plugin_renderer_base {
    public function index($course, $tapscompletions, $modinfo, $usesections, $sections) {
        global $OUTPUT;

        $strsectionname  = get_string('sectionname', 'format_'.$course->format);
        $strname         = get_string('name');
        $strlastmodified = get_string('lastmodified');

        $table = new html_table();
        $table->attributes['class'] = 'generaltable mod_index';

        if ($usesections) {
            $table->head  = array ($strsectionname, $strname);
            $table->align = array ('center', 'left');
        } else {
            $table->head  = array ($strlastmodified, $strname);
            $table->align = array ('left', 'left');
        }

        $currentsection = '';
        foreach ($tapscompletions as $tapscompletion) {
            $cm = $modinfo->cms[$tapscompletion->coursemodule];
            if ($usesections) {
                $printsection = '';
                if ($tapscompletion->section !== $currentsection) {
                    if ($tapscompletion->section) {
                        $printsection = get_section_name($course, $sections[$tapscompletion->section]);
                    }
                    if ($currentsection !== '') {
                        $table->data[] = 'hr';
                    }
                    $currentsection = $tapscompletion->section;
                }
            } else {
                $printsection = '<span class="smallinfo">'.userdate($tapscompletion->timemodified)."</span>";
            }

            $extra = empty($cm->extra) ? '' : $cm->extra;
            $icon = '';
            if (!empty($cm->icon)) {
                $icon = '<img src="'.$OUTPUT->pix_url($cm->icon).'" class="activityicon" alt="'.get_string('modulename', $cm->modname).'" /> ';
            }

            $class = $tapscompletion->visible ? '' : 'class="dimmed"'; // Hidden modules are dimmed.
            $table->data[] = array (
                $printsection,
                "<a {$class} {$extra} href=\"view.php?id={$cm->id}\">".$icon.format_string($tapscompletion->name)."</a>");
        }

        return html_writer::table($table);
    }

    public function user_table($course, $completion, $criteria, $progress, $modinfo, $users, $id) {
        global $CFG, $COMPLETION_CRITERIA_TYPES, $DB, $OUTPUT;

        $html = html_writer::tag('h3', get_string('userstocomplete', 'tapscompletion'));

        if (!$users) {
            $html .= html_writer::tag('p', get_string('nousers', 'tapscompletion'));
            return $html;
        }

        $html .= html_writer::start_tag('div', array('id' => 'completion-progress-wrapper', 'class' => 'no-overflow'));

        $html .= html_writer::start_tag(
                'form',
                array(
                    'id' => 'completion-form',
                    'accept-charset' => 'utf-8',
                    'method' => 'post',
                    'action' => $CFG->wwwroot.'/mod/tapscompletion/view.php',
                    'autocomplete' => 'off'
                ));
        $html .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $id));
        $html .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));

        $html .= html_writer::start_tag('table', array(
            'id' => 'completion-progress',
            'class' => 'generaltable flexible boxalignleft completionreport',
            'style' => 'text-align: left',
            'cellpadding' => '5',
            'border' => '1'
        ));

        $html .= html_writer::start_tag('tr', array('style' => 'vertical-align: top'));
        $html .= html_writer::tag('th', get_string('criteriagroup', 'completion'), array('colspan' => 2, 'scope' => 'row', 'class' => 'rowheader'));
        $currentgroup = false;
        $colcount = 0;
        for ($i = 0; $i <= count($criteria); $i++) {
            if (isset($criteria[$i])) {
                $criterion = $criteria[$i];
                if ($currentgroup && $criterion->criteriatype === $currentgroup->criteriatype) {
                    ++$colcount;
                    continue;
                }
            }
            if ($colcount) {
                $html .= html_writer::tag('th', $currentgroup->get_type_title(), array('scope' => 'col', 'colspan' => $colcount));
            }
            if (isset($criteria[$i])) {
                $currentgroup = $criterion;
                $colcount = 1;
            }
        }
        $html .= html_writer::tag('th', get_string('course'), array('style' => 'text-align: center;'));
        $html .= html_writer::tag('th', get_string('markattendance', 'tapscompletion'), array('rowspan' => 4, 'style' => 'vertical-align: bottom;'));
        $html .= html_writer::end_tag('tr');

        $html .= html_writer::start_tag('tr', array('style' => 'vertical-align: top'));
        $html .= html_writer::tag('th', get_string('aggregationmethod', 'completion'), array('colspan' => 2, 'scope' => 'row', 'class' => 'rowheader'));
        $currentgroup = false;
        $colcount = 0;
        for ($i = 0; $i <= count($criteria); $i++) {
            if (isset($criteria[$i])) {
                $criterion = $criteria[$i];
                if ($currentgroup && $criterion->criteriatype === $currentgroup->criteriatype) {
                    ++$colcount;
                    continue;
                }
            }
            if ($colcount) {
                $hasagg = array(
                    COMPLETION_CRITERIA_TYPE_COURSE,
                    COMPLETION_CRITERIA_TYPE_ACTIVITY,
                    COMPLETION_CRITERIA_TYPE_ROLE,
                );
                if (in_array($currentgroup->criteriatype, $hasagg)) {
                    $method = $completion->get_aggregation_method($currentgroup->criteriatype) ? get_string('all') : get_string('any');
                } else {
                    $method = '-';
                }
                $html .= html_writer::tag('th', $method, array('scope' => 'col', 'colspan' => $colcount, 'class' => 'colheader aggheader'));
            }
            if (isset($criteria[$i])) {
                $currentgroup = $criterion;
                $colcount = 1;
            }
        }
        $method = $completion->get_aggregation_method() ? get_string('all') : get_string('any');
        $html .= html_writer::tag('th', $method, array('scope' => 'col', 'class' => 'colheader aggheader aggcriteriacourse'));
        $html .= html_writer::end_tag('tr');

        $html .= html_writer::start_tag('tr');
        $html .= html_writer::tag('th', get_string('criteria', 'completion'), array('colspan' => 2, 'scope' => 'row', 'class' => 'rowheader'));
        foreach ($criteria as $criterion) {
            $details = $criterion->get_title_detailed();
            $span = html_writer::tag('span', $details, array('class' => 'completion-criterianame'));
            $html .= html_writer::tag('th', $span, array('scope' => 'col', 'class' => 'colheader criterianame'));
        }
        $span = html_writer::tag('span', get_string('coursecomplete', 'completion'), array('class' => 'completion-criterianame'));
        $html .= html_writer::tag('th', $span, array('scope' => 'col', 'class' => 'colheader criterianame'));
        $html .= html_writer::end_tag('tr');

        $html .= html_writer::start_tag('tr');
        $html .= html_writer::tag('th', get_string('name'), array('scope' => 'col'));
        $html .= html_writer::tag('th', get_string('staffid', 'tapscompletion'), array('scope' => 'col'));
        foreach ($criteria as $criterion) {
            $icon = '';
            switch ($criterion->criteriatype) {
                case COMPLETION_CRITERIA_TYPE_ACTIVITY:
                    $iconsrc = $OUTPUT->pix_url('icon', $criterion->module);
                    $iconurl = new moodle_url('/mod/'.$criterion->module.'/view.php', array('id' => $criterion->moduleinstance));
                    $icontitle = $modinfo->cms[$criterion->moduleinstance]->name;
                    $iconalt = get_string('modulename', $criterion->module);
                    $iconimg = html_writer::empty_tag('img', array('src' => $iconsrc, 'alt' => $iconalt));
                    $icon = html_writer::link($iconurl, $iconimg, array('title' => $icontitle));
                    break;
                case COMPLETION_CRITERIA_TYPE_COURSE:
                    $crs = $DB->get_record('course', array('id' => $criterion->courseinstance));
                    $iconsrc = $OUTPUT->pix_url('i/'.$COMPLETION_CRITERIA_TYPES[$criterion->criteriatype]);
                    $iconurl = new moodle_url('/course/view.php', array('id' => $criterion->courseinstance));
                    $icontitle = format_string($crs->fullname, true, array('context' => get_context_instance(CONTEXT_COURSE, $crs->id, MUST_EXIST)));
                    $iconalt = format_string($crs->shortname, true, array('context' => get_context_instance(CONTEXT_COURSE, $crs->id)));
                    $iconimg = html_writer::empty_tag('img', array('src' => $iconsrc, 'alt' => $iconalt));
                    $icon = html_writer::link($iconurl, $iconimg, array('title' => $icontitle));
                    break;
                case COMPLETION_CRITERIA_TYPE_ROLE:
                    $role = $DB->get_record('role', array('id' => $criterion->role));
                    $iconsrc = $OUTPUT->pix_url('i/'.$COMPLETION_CRITERIA_TYPES[$criterion->criteriatype]);
                    $iconalt = $role->name;
                    $icon = html_writer::empty_tag('img', array('src' => $iconsrc, 'alt' => $iconalt));
                    break;
            }
            if (!$icon) {
                $iconsrc = $OUTPUT->pix_url('i/'.$COMPLETION_CRITERIA_TYPES[$criterion->criteriatype]);
                $icon = html_writer::empty_tag('img', array('src' => $iconsrc, 'alt' => ''));
            }
            $html .= html_writer::tag('th', $icon, array('class' => 'criteriaicon'));
        }
        $icon = html_writer::empty_tag('img', array(
            'src' => $OUTPUT->pix_url('i/course'),
            'class' => 'icon',
            'alt' => get_string('course'),
            'title' => get_string('coursecomplete', 'completion')
        ));
        $html .= html_writer::tag('th', $icon, array('class' => 'criteriaicon'));
        $html .= html_writer::end_tag('tr');

        $classid = 0;
        foreach ($users as $user) {
            if ($user->classid != $classid) {
                $classid = $user->classid;

                $cells = '';
                $cellcontent = get_string('classwithname', 'tapscompletion', $user->classname) .
                        html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'class['.$user->classid.']', 'value' => $user->classname));
                $cells .= html_writer::tag('th', $cellcontent, array('colspan' => count($criteria) + 3));
                $input = html_writer::empty_tag('input', array(
                    'class' => 'tapscompletion-checkbox tapscompletion-checkbox-all',
                    'type' => 'checkbox',
                    'name' => 'class-'.$user->classid,
                    'value' => $user->classid));
                $label = html_writer::label(get_string('selectallforclass', 'tapscompletion'), 'class-'.$user->classid, false);
                $cells .= html_writer::tag('th', $input.$label);
                $html .= html_writer::tag('tr', $cells);
            }

            $html .= html_writer::start_tag('tr');
            $html .= html_writer::tag('th', fullname($user), array('scope' => 'row'));
            $html .= html_writer::tag('td', $user->idnumber);
            if (isset($progress[$user->id])) {
                foreach ($criteria as $criterion) {
                    if ($criterion->criteriatype == COMPLETION_CRITERIA_TYPE_ACTIVITY) {
                        $activity = $modinfo->cms[$criterion->moduleinstance];
                        if (array_key_exists($activity->id, $progress[$user->id]->progress)) {
                            $thisprogress = $progress[$user->id]->progress[$activity->id];
                            $state = $thisprogress->completionstate;
                            $date = userdate($thisprogress->timemodified);
                        } else {
                            $state = COMPLETION_INCOMPLETE;
                            $date = '';
                        }
                        $criteriacompletion = $completion->get_user_completion($user->id, $criterion);
                        switch($state) {
                            case COMPLETION_INCOMPLETE:
                                $completiontype = 'n';
                                break;
                            case COMPLETION_COMPLETE:
                                $completiontype = 'y';
                                break;
                            case COMPLETION_COMPLETE_PASS:
                                $completiontype = 'pass';
                                break;
                            case COMPLETION_COMPLETE_FAIL:
                                $completiontype = 'fail';
                                break;
                        }

                        $completionicon = 'completion-' .
                                ($activity->completion == COMPLETION_TRACKING_AUTOMATIC ? 'auto' : 'manual') .
                                '-' . $completiontype;
                        $describe = get_string('completion-' . $completiontype, 'completion');
                        $a = new StdClass;
                        $a->state = $describe;
                        $a->date = $date;
                        $a->user = fullname($user);
                        $a->activity = strip_tags($activity->name);
                        $fulldescribe = get_string('progress-title', 'completion', $a);

                        $img = html_writer::empty_tag('img', array(
                            'src' => $OUTPUT->pix_url('i/'.$completionicon),
                            'alt' => $describe,
                            'class' => 'icon',
                            'title' => $fulldescribe
                        ));
                        $html .= html_writer::tag('td', $img, array('class' => 'completion-progresscell'));
                        continue;
                    }

                    $criteriacompletion = $completion->get_user_completion($user->id, $criterion);
                    $iscomplete = $criteriacompletion->is_complete();

                    $completiontype = $iscomplete ? 'y' : 'n';
                    $completionicon = 'completion-auto-'.$completiontype;

                    $describe = get_string('completion-' . $completiontype, 'completion');

                    $a = new stdClass();
                    $a->state = $describe;
                    $a->date = $iscomplete ? userdate($criteriacompletion->timecompleted) : '';
                    $a->user = fullname($user);
                    $a->activity = strip_tags($criterion->get_title());
                    $fulldescribe = get_string('progress-title', 'completion', $a);

                    $img = html_writer::empty_tag('img', array(
                        'src' => $OUTPUT->pix_url('i/'.$completionicon),
                        'alt' => $describe,
                        'class' => 'icon',
                        'title' => $fulldescribe
                    ));
                    $html .= html_writer::tag('td', $img, array('class' => 'completion-progresscell'));
                }

                $params = array(
                    'userid'    => $user->id,
                    'course'    => $course->id
                );

                $ccompletion = new completion_completion($params);
                $completiontype = $ccompletion->is_complete() ? 'y' : 'n';

                $describe = get_string('completion-' . $completiontype, 'completion');

                $a = new stdClass;
                $a->state = $describe;
                $a->date = !empty($ccompletion->timecompleted) ? userdate($ccompletion->timecompleted) : '';
                $a->user = fullname($user);
                $a->activity = strip_tags(get_string('coursecomplete', 'completion'));
                $fulldescribe = get_string('progress-title', 'completion', $a);

                $img = html_writer::empty_tag('img', array(
                    'src' => $OUTPUT->pix_url('i/completion-auto-'.$completiontype),
                    'alt' => $describe,
                    'class' => 'icon',
                    'title' => $fulldescribe
                ));
                $html .= html_writer::tag('td', $img, array('class' => 'completion-progresscell'));
            } else {
                $html .= html_writer::tag('td', get_string('na', 'tapscompletion'), array('colspan' => (count($criteria) + 1), 'style' => 'text-align:center;'));
            }
            $input = html_writer::empty_tag('input', array(
                'class' => 'tapscompletion-checkbox',
                'type' => 'checkbox',
                'name' => 'staffid['.$user->idnumber.']',
                'value' => $user->enrolmentid . '_' . $user->classid
            ));
            $select = html_writer::start_tag('select', ['name' => $user->enrolmentid . '_' . $user->classid, 'class' => 'hiddenifjs']);
            $select .= html_writer::tag('option', 'Full Attendance', ['value' => 'Full Attendance']);
            $select .= html_writer::tag('option', 'No Show', ['value' => 'No Show']);
            $select .= html_writer::end_tag('select');
            $html .= html_writer::tag('td', $input.$select);
            $html .= html_writer::end_tag('tr');
        }

        $html .= html_writer::end_tag('table');
        $html .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'submit', 'value' => get_string('updateusers', 'tapscompletion')));
        $html .= html_writer::end_tag('form');
        $html .= html_writer::end_tag('div');

        return $html;
    }

    public function back_to_module($courseid) {
        $url = new moodle_url('/course/view.php', array('id' => $courseid));
        $link = html_writer::link($url, get_string('backtomodule', 'tapscompletion'));
        return html_writer::tag('p', $link);
    }

    public function alert($message, $type = 'alert-warning') {
        $class = "alert {$type} fade in";
        return html_writer::tag('div', $message, array('class' => $class));
    }
}