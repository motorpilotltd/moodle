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

class mod_tapsenrol_renderer extends plugin_renderer_base {
    public function index($course, $tapsenrols, $modinfo, $usesections, $sections) {
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
        foreach ($tapsenrols as $tapsenrol) {
            $cm = $modinfo->cms[$tapsenrol->coursemodule];
            if ($usesections) {
                $printsection = '';
                if ($tapsenrol->section !== $currentsection) {
                    if ($tapsenrol->section) {
                        $printsection = get_section_name($course, $sections[$tapsenrol->section]);
                    }
                    if ($currentsection !== '') {
                        $table->data[] = 'hr';
                    }
                    $currentsection = $tapsenrol->section;
                }
            } else {
                $printsection = '<span class="smallinfo">'.userdate($tapsenrol->timemodified)."</span>";
            }

            $extra = empty($cm->extra) ? '' : $cm->extra;
            $icon = '';
            if (!empty($cm->icon)) {
                $icon = '<img src="'.$OUTPUT->pix_url($cm->icon).'" class="activityicon" alt="'.get_string('modulename', $cm->modname).'" /> ';
            }

            $class = $tapsenrol->visible ? '' : 'class="dimmed"'; // Hidden modules are dimmed.
            $table->data[] = array (
                $printsection,
                "<a {$class} {$extra} href=\"view.php?id={$cm->id}\">".$icon.format_string($tapsenrol->name)."</a>");
        }

        return html_writer::table($table);
    }

    public function alert($message = '', $type = 'alert-warning', $exitbutton = true) {
        $class = "alert fade in {$type}";
        $button = '';
        if ($exitbutton) {
            $button .= html_writer::tag('button', '&times;', array('class' => 'close', 'data-dismiss' => 'alert', 'type' => 'button'));
        }
        return html_writer::tag('div', $button.$message, array('class' => $class));
    }

    public function enrolment_history($tapsenrol, $enrolments, $classes, $cmid) {
        global $DB, $SESSION, $OUTPUT, $USER;

        $overallclasstype = 'unknown';
        $classtypes = [];
        foreach (['classes', 'enrolments'] as $var) {
            foreach ($$var as $value) {
                $classtypes[] = $overallclasstype = $tapsenrol->taps->get_classtype_type($value->classtype);
                if (count(array_unique($classtypes)) > 1) {
                    $overallclasstype = 'mixed';
                    break 2;
                }
            }
        }

        // Generate tables.
        $checkseats = false;
        $hideadvert = false;
        $enrolledclasses = array();
        $activeclasses = 0;
        $completedclasses = array();
        $data = array();

        $delegateurl = new moodle_url('/local/delegatelist/index.php', array('contextid' => $tapsenrol->context->course->id));
        $delegateicon = html_writer::tag('i', '', array('class' => 'fa fa-users'));

        if ($enrolments) {
            $hideadvert = true;
            foreach ($enrolments as $enrolment) {
                $delegateurl->param('classid', $enrolment->classid);
                $delegatebutton = html_writer::link($delegateurl, $delegateicon, array('class' => 'delegate-button'));
                $enrolmentstatustype = $tapsenrol->taps->get_status_type($enrolment->bookingstatus);
                switch ($enrolmentstatustype) {
                    case 'requested':
                    case 'waitlisted':
                    case 'placed' :
                        if (!$enrolment->active) {
                            continue;
                        }
                        $enrolledclasses[] = $enrolment->classid;
                        $activeclasses++;
                        break;
                    case 'attended' :
                        $completedclasses[] = $enrolment->classid;
                        break;
                }

                $cells = array();
                $cells[] = $enrolment->classname;
                $enrolmentclasstype = $tapsenrol->taps->get_classtype_type($enrolment->classtype);
                $classtype = $enrolmentclasstype ? $enrolmentclasstype : 'unknown';
                if ($classtype == 'elearning') {
                    $cells[] = get_string('online', 'tapsenrol');
                    if ($overallclasstype == 'mixed') {
                        $cells[] = '-';
                        $cells[] = '-';
                    }
                } else {
                    $cells[] = $enrolment->location ? $enrolment->location : get_string('tbc', 'tapsenrol');
                    if ($enrolment->classstarttime) {
                        try {
                            $timezone = new DateTimeZone($enrolment->usedtimezone);
                        } catch (Exception $e) {
                            $timezone = new DateTimeZone(date_default_timezone_get());
                        }

                        $startdatetime = new DateTime(null, $timezone);
                        $startdatetime->setTimestamp($enrolment->classstarttime);
                        $enddatetime = new DateTime(null, $timezone);
                        $enddatetime->setTimestamp($enrolment->classendtime);

                        if ($enddatetime->format('Ymd') > $startdatetime->format('Ymd')) {
                            $cells[] = $startdatetime->format('d M Y').' - '.$enddatetime->format('d M Y');
                        } else {
                            $cells[] = $startdatetime->format('d M Y');
                        }
                        if ($enrolment->classstarttime != $enrolment->classstartdate) {
                            $to = get_string('to', 'tapsenrol');
                            $time = $startdatetime->format('H:i')." {$to} ".$enddatetime->format('H:i T');
                            // Show UTC as GMT for clarity.
                            $cells[] = str_replace('UTC', 'GMT', $time);
                        } else {
                            $cells[] = '-';
                        }
                    } else {
                        $cell = new html_table_cell(get_string("waitinglist:{$classtype}", 'tapsenrol'));
                        $cell->colspan = 2;
                        $cells[] = $cell;
                    }
                }
                $cells[] = $enrolment->duration ? (float) $enrolment->duration.' '.$enrolment->durationunits : '-';
                $seatsremaining = $tapsenrol->taps->get_seats_remaining($enrolment->classid);
                $cells[] = ($seatsremaining === -1 ? get_string('unlimited', 'tapsenrol') : $seatsremaining) . $delegatebutton;
                if (!isset($enrolment->price)) {
                    $class = $tapsenrol->taps->get_class_by_id($enrolment->classid);
                    $enrolment->price = $class ? $class->price : null;
                    $enrolment->currencycode = $class ? $class->currencycode : null;
                }
                $cells[] = $enrolment->price ? $enrolment->price.' '.$enrolment->currencycode : '-';

                $status = $tapsenrol->taps->get_status_type($enrolment->bookingstatus);
                switch ($status) {
                    case 'requested' :
                        $checkseats = $enrolment;
                        $cells[] = $this->dropdown(get_string("status:{$classtype}:requested", 'tapsenrol'),
                            array(
                                array(
                                    'url' => new moodle_url('/mod/tapsenrol/cancel.php', array('id' => $cmid, 'enrolmentid' => $enrolment->enrolmentid)),
                                    'title' => get_string("status:dropdown:cancel", 'tapsenrol')
                                    )
                            ),
                            'warning');
                        if (!isset($SESSION->tapsenrol->alert)) {
                            $SESSION->tapsenrol->alert = new stdClass();
                        }
                        $iwtrack = false;
                        if ($tapsenrol->tapsenrol->internalworkflowid) {
                            $iwtrack = $DB->get_record('tapsenrol_iw_tracking', array('enrolmentid' => $enrolment->enrolmentid));
                        }
                        if ($iwtrack) {
                            $a = new stdClass();
                            $a->sponsorname = $iwtrack->sponsorfirstname . ' ' . $iwtrack->sponsorlastname;
                            $a->sponsoremail = $iwtrack->sponsoremail;
                            $a->requestdate = gmdate('d M Y', $iwtrack->timeenrolled);
                            $a->cancelafter = $tapsenrol->iw->cancelafter / (24 * 60 * 60) . ' days';
                            $a->cancelbefore = $tapsenrol->iw->cancelbefore / (60 * 60) . ' hours';
                            if ($tapsenrol->iw->enroltype == 'apply') {
                                $SESSION->tapsenrol->alert->message = html_writer::tag('p', get_string("alert:iw:{$classtype}:requested:apply", 'tapsenrol', $a));
                            } else {
                                $SESSION->tapsenrol->alert->message = html_writer::tag('p', get_string("alert:iw:{$classtype}:requested", 'tapsenrol', $a));
                            }
                        } else {
                            $SESSION->tapsenrol->alert->message = html_writer::tag('p', get_string("alert:{$classtype}:requested", 'tapsenrol'));
                        }
                        $SESSION->tapsenrol->alert->type = 'alert-warning';
                        break;
                    case 'waitlisted' :
                        $checkseats = $enrolment;
                        $cells[] = $this->dropdown(get_string("status:{$classtype}:waitlisted", 'tapsenrol'),
                            array(
                                array(
                                    'url' => new moodle_url('/mod/tapsenrol/cancel.php', array('id' => $cmid, 'enrolmentid' => $enrolment->enrolmentid)),
                                    'title' => get_string("status:dropdown:cancel:waitlisted", 'tapsenrol')
                                    )
                            ),
                            'info');
                        break;
                    case 'placed' :
                        $cells[] = $this->dropdown(get_string("status:{$classtype}:placed", 'tapsenrol'),
                            array(
                                array('url' => new moodle_url('/mod/tapsenrol/cancel.php', array('id' => $cmid, 'enrolmentid' => $enrolment->enrolmentid)),
                                    'title' => get_string("status:dropdown:cancel", 'tapsenrol')
                                    )
                            ),
                            'success');
                        break;
                    case 'attended' :
                        $cells[] = get_string("status:{$classtype}:attended", 'tapsenrol');
                        break;
                    case 'cancelled' :
                        $cells[] = get_string("status:{$classtype}:cancelled", 'tapsenrol');
                        break;
                }

                $data[] = new html_table_row($cells);
            }
        }

        if ($classes) {
            foreach ($classes as $index => $class) {
                $classclasstype = $tapsenrol->taps->get_classtype_type($class->classtype);
                $classtype = $classclasstype ? $classclasstype : 'unknown';
                $delegateurl->param('classid', $class->classid);
                $delegatebutton = html_writer::link($delegateurl, $delegateicon, array('class' => 'delegate-button'));
                // Allow completed if elearning.
                if (($classtype != 'elearning' && in_array($class->classid, $completedclasses)) || in_array($class->classid, $enrolledclasses)) {
                    unset($classes[$index]);
                    continue;
                }
                $cells = array();
                $cells[] = $class->classname;
                if ($classtype == 'elearning') {
                    $cells[] = get_string('online', 'tapsenrol');
                    if ($overallclasstype == 'mixed') {
                        $cells[] = '-';
                        $cells[] = '-';
                    }
                } else {
                    $cells[] = $class->location ? $class->location : get_string('tbc', 'tapsenrol');
                    if ($class->classstarttime) {
                        try {
                            $timezone = new DateTimeZone($class->usedtimezone);
                        } catch (Exception $e) {
                            $timezone = new DateTimeZone(date_default_timezone_get());
                        }

                        $startdatetime = new DateTime(null, $timezone);
                        $startdatetime->setTimestamp($class->classstarttime);
                        $enddatetime = new DateTime(null, $timezone);
                        $enddatetime->setTimestamp($class->classendtime);

                        if ($enddatetime->format('Ymd') > $startdatetime->format('Ymd')) {
                            $cells[] = $startdatetime->format('d M Y').' - '.$enddatetime->format('d M Y');
                        } else {
                            $cells[] = $startdatetime->format('d M Y');
                        }
                        if ($class->classstarttime != $class->classstartdate) {
                            $to = get_string('to', 'tapsenrol');
                            $time = $startdatetime->format('H:i')." {$to} ".$enddatetime->format('H:i T');
                            // Show UTC as GMT for clarity.
                            $cells[] = str_replace('UTC', 'GMT', $time);
                        } else {
                            $cells[] = '-';
                        }
                    } else {
                        $cell = new html_table_cell(get_string("waitinglist:{$classtype}", 'tapsenrol'));
                        $cell->colspan = 2;
                        $cells[] = $cell;
                    }
                }
                $cells[] = $class->classduration ? (float) $class->classduration.' '.$class->classdurationunits : '-';
                $seatsremaining = $tapsenrol->taps->get_seats_remaining($class->classid);
                $cells[] = ($seatsremaining === -1 ? get_string('unlimited', 'tapsenrol') : $seatsremaining) . $delegatebutton;
                $cells[] = $class->price ? $class->price.' '.$class->currencycode : '-';
                if ($classtype == 'classroom'
                        && $tapsenrol->tapsenrol->internalworkflowid
                        && $tapsenrol->iw->closeenrolment
                        && $class->classstarttime
                        && $class->classstarttime < (time() + $tapsenrol->iw->closeenrolment)) {
                    $helpicon = $OUTPUT->help_icon('enrol:closed', 'tapsenrol');
                    $cells[] = html_writer::tag('span', get_string('enrol:closed', 'tapsenrol') . '&nbsp;' . $helpicon, array('class' => 'nowrap'));
                } else if ($seatsremaining == 0) {
                    $enrolurl = new moodle_url('/mod/tapsenrol/enrol.php', array('id' => $cmid, 'classid' => $class->classid));
                    $enroltext = get_string('enrol:waitinglist', 'tapsenrol') . ' ' . html_writer::tag('span', '', array('class' => 'caret caret-right'));
                    $cells[] = get_string('classfull', 'tapsenrol') .
                        html_writer::empty_tag('br') .
                        html_writer::link($enrolurl, $enroltext, array('class' => 'btn btn-small btn-default'));
                } else if (!$class->classstarttime) {
                    $enrolurl = new moodle_url('/mod/tapsenrol/enrol.php', array('id' => $cmid, 'classid' => $class->classid));
                    $enroltext = get_string('enrol:planned', 'tapsenrol') . ' ' . html_writer::tag('span', '', array('class' => 'caret caret-right'));
                    $cells[] = html_writer::link($enrolurl, $enroltext, array('class' => 'btn btn-small btn-default'));
                } else {
                    $enrolstring = ($classtype == 'elearning' && in_array($class->classid, $completedclasses)) ? 'enrol:reenrol' : 'enrol';
                    $enrolurl = new moodle_url('/mod/tapsenrol/enrol.php', array('id' => $cmid, 'classid' => $class->classid));
                    $enroltext = get_string($enrolstring, 'tapsenrol') . ' ' . html_writer::tag('span', '', array('class' => 'caret caret-right'));
                    $cells[] = html_writer::link($enrolurl, $enroltext, array('class' => 'btn btn-primary btn-small'));
                }
                $row = new html_table_row($cells);
                $data[] = $row;
            }
        }

        $table = new html_table();
        if ($overallclasstype == 'elearning') {
            $table->head = array(
                get_string('classname', 'tapsenrol'),
                get_string('location', 'tapsenrol'),
                get_string('duration', 'tapsenrol'),
                get_string('seatsremaining', 'tapsenrol'),
                get_string('cost', 'tapsenrol'),
                get_string('bookingstatus', 'tapsenrol')
            );
            $table->data = $data;
        } else {
            $table->head = array(
                get_string('classname', 'tapsenrol'),
                get_string('location', 'tapsenrol'),
                get_string('date', 'tapsenrol'),
                get_string('time', 'tapsenrol'),
                get_string('duration', 'tapsenrol'),
                get_string('seatsremaining', 'tapsenrol'),
                get_string('cost', 'tapsenrol'),
                get_string('bookingstatus', 'tapsenrol')
            );
            foreach ($table->head as $index => $value) {
                $table->head[$index] = str_ireplace(' ', '&nbsp;', $value);
            }
            $table->data = $data;
        }

        // Begin HTML.
        $html = '';
        if (!empty($data)) {
            $html .= html_writer::tag('h3', get_string("whenandwhere:{$overallclasstype}", 'tapsenrol'));
        }

        if ($activeclasses) {
            $html .= html_writer::tag('p', get_string('enrolledmessage', 'tapsenrol'));
        } else if ($classes) {
            switch ($overallclasstype) {
                case 'elearning' :
                case 'classroom' :
                case 'mixed' :
                    $html .= html_writer::tag('p', get_string("classes:{$overallclasstype}", 'tapsenrol'));
                    break;
                default :
                    break;
            }
            $alreadyattended = $tapsenrol->already_attended($USER);
            if ($alreadyattended->attended) {
                $string = html_writer::tag('p', get_string('enrol:alert:alreadyattended', 'tapsenrol'));
                $string .= empty($alreadyattended->certifications) ? '' : html_writer::tag('p', get_string('enrol:alert:alreadyattended:certification', 'tapsenrol', implode('<br>', $alreadyattended->certifications)));
                $html .= $this->alert($string, 'alert-warning', false);
            }
        }

        if (!empty($data)) {
            $html .= html_writer::table($table);
        }

        if ($classes && $checkseats) {
            foreach ($classes as $class) {
                if ($class->classid == $checkseats->classid) {
                    $status = $tapsenrol->taps->get_status_type($checkseats->bookingstatus);
                    $seatsremaining = $tapsenrol->taps->get_seats_remaining($class->classid);
                    if ($seatsremaining == 0 && $status == 'requested') {
                        $html .= $this->alert(get_string('status:requested:fullclass', 'tapsenrol'), 'alert-warning');
                    } else if ($seatsremaining == 0 && $status == 'waitlisted') {
                        $html .= $this->alert(get_string('status:waitlisted:fullclass', 'tapsenrol'), 'alert-warning');
                    } else if ($class->classstatus == 'Normal' && $status == 'waitlisted') {
                        $html .= $this->alert(get_string('status:waitlisted:plannedclass', 'tapsenrol'), 'alert-warning');
                    }
                    break;
                }
            }
        }

        if (!$classes && !$activeclasses) {
            $html .= $this->alert(get_string('noclasses', 'tapsenrol'), 'alert-warning', false);
        }

        $html .= html_writer::tag(
            'span',
            '',
            array(
                'id' => 'tapsenrol_arupadvert_hide_trigger',
                'class' => 'arupadvert_hide_trigger',
                'data-hideadvert' => $hideadvert,
            )
        );

        return $html;
    }

    public function review_enrolment($tapsenrol, $class) {
        try {
            $timezone = new DateTimeZone($class->usedtimezone);
        } catch (Exception $e) {
            $timezone = new DateTimeZone(date_default_timezone_get());
        }

        $startdatetime = new DateTime(null, $timezone);
        $startdatetime->setTimestamp($class->classstarttime);

        $html = html_writer::tag('p', get_string('reviewenrolment:areyousure', 'tapsenrol'));
        $html .= html_writer::start_tag('div', array('class' => 'tapsenrol_review_info mform'));

        $classname = html_writer::tag('div', get_string('classname', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $classname .= html_writer::tag('div', $class->classname, array('class' => 'felement'));
        $html .= html_writer::tag('div', $classname, array('class' => 'fitem'));

        $duration = html_writer::tag('div', get_string('duration', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $durationvalue = ($class->classduration) ? (float) $class->classduration . ' ' . $class->classdurationunits : get_string('tbc', 'tapsenrol');
        $duration .= html_writer::tag('div', $durationvalue, array('class' => 'felement'));
        $html .= html_writer::tag('div', $duration, array('class' => 'fitem'));

        if ($tapsenrol->taps->is_classtype($class->classtype, 'classroom')) {
            $date = html_writer::tag('div', get_string('date', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
            if (!$class->classstarttime) {
                $datevalue = get_string('waitinglist:classroom', 'tapsenrol');
            } else {
                $datevalue = $startdatetime->format('d M Y');
                $datevalue .= ($class->classstarttime != $class->classstartdate) ? $startdatetime->format(' H:i T') : '';
                // Show UTC as GMT for clarity.
                $datevalue = str_replace('UTC', 'GMT', $datevalue);
            }
            $date .= html_writer::tag('div', $datevalue, array('class' => 'felement'));
            $html .= html_writer::tag('div', $date, array('class' => 'fitem'));
        }

        $location = html_writer::tag('div', get_string('location', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        if ($tapsenrol->taps->is_classtype($class->classtype, 'elearning')) {
            $locationvalue = get_string('online', 'tapsenrol');
        } else {
            $locationvalue = $class->location ? $class->location : get_string('tbc', 'tapsenrol');
        }
        $location .= html_writer::tag('div', $locationvalue, array('class' => 'felement'));
        $html .= html_writer::tag('div', $location, array('class' => 'fitem'));

        if ($class->trainingcenter) {
            $trainingcenter = html_writer::tag('div', get_string('trainingcenter', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
            $trainingcenter .= html_writer::tag('div', $class->trainingcenter, array('class' => 'felement'));
            $html .= html_writer::tag('div', $trainingcenter, array('class' => 'fitem'));
        }

        $cost = html_writer::tag('div', get_string('cost', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $costvalue = $class->price ? $class->price.' '.$class->currencycode : '-';
        $cost .= html_writer::tag('div', $costvalue, array('class' => 'felement'));
        $html .= html_writer::tag('div', $cost, array('class' => 'fitem'));

        $html .= html_writer::end_tag('div'); // End div tapsenrol_review_info.

        if ($tapsenrol->iw && $tapsenrol->iw->enrolinfo) {
            $html .= html_writer::start_tag('div', array('class' => 'tapsenrol_iw_enrolinfo mform'));
            $enrolinfo = html_writer::tag('div', '', array('class' => 'fitemtitle'));
            $enrolinfo .= html_writer::tag('div', nl2br($tapsenrol->iw->enrolinfo), array('class' => 'felement'));
            $html .= html_writer::tag('div', $enrolinfo, array('class' => 'fitem'));
            $html .= html_writer::end_tag('div');
        }

        return $html;
    }

    public function cancel_enrolment($tapsenrol, $enrolment) {
        try {
            $timezone = new DateTimeZone($enrolment->usedtimezone);
        } catch (Exception $e) {
            $timezone = new DateTimeZone(date_default_timezone_get());
        }

        $startdatetime = new DateTime(null, $timezone);
        $startdatetime->setTimestamp($enrolment->classstarttime);

        $html = '';
        $html .= html_writer::tag('p', get_string('cancelenrolment:areyousure', 'tapsenrol'));
        $html .= html_writer::start_tag('div', array('class' => 'tapsenrol_review_info mform'));

        $classname = html_writer::tag('div', get_string('classname', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $classname .= html_writer::tag('div', $enrolment->classname, array('class' => 'felement'));
        $html .= html_writer::tag('div', $classname, array('class' => 'fitem'));

        $duration = html_writer::tag('div', get_string('duration', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $durationvalue = ($enrolment->duration) ? (float) $enrolment->duration . ' ' . $enrolment->durationunits : get_string('tbc', 'tapsenrol');
        $duration .= html_writer::tag('div', $durationvalue, array('class' => 'felement'));
        $html .= html_writer::tag('div', $duration, array('class' => 'fitem'));

        if ($tapsenrol->taps->is_classtype($enrolment->classtype, 'classroom')) {
            $date = html_writer::tag('div', get_string('date', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
            if (!$enrolment->classstarttime) {
                $datevalue = get_string('waitinglist:classroom', 'tapsenrol');
            } else {
                $datevalue = $startdatetime->format('d M Y');
                $datevalue .= ($enrolment->classstarttime != $enrolment->classstartdate) ? $startdatetime->format(' H:i T') : '';
                // Show UTC as GMT for clarity.
                $datevalue = str_replace('UTC', 'GMT', $datevalue);
            }
            $date .= html_writer::tag('div', $datevalue, array('class' => 'felement'));
            $html .= html_writer::tag('div', $date, array('class' => 'fitem'));
        }

        $location = html_writer::tag('div', get_string('location', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        if ($tapsenrol->taps->is_classtype($enrolment->classtype, 'elearning')) {
            $locationvalue = get_string('online', 'tapsenrol');
        } else {
            $locationvalue = $enrolment->location ? $enrolment->location : get_string('tbc', 'tapsenrol');
        }
        $location .= html_writer::tag('div', $locationvalue, array('class' => 'felement'));
        $html .= html_writer::tag('div', $location, array('class' => 'fitem'));

        if ($enrolment->trainingcenter) {
            $trainingcenter = html_writer::tag('div', get_string('trainingcenter', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
            $trainingcenter .= html_writer::tag('div', $enrolment->trainingcenter, array('class' => 'felement'));
            $html .= html_writer::tag('div', $trainingcenter, array('class' => 'fitem'));
        }

        $cost = html_writer::tag('div', get_string('cost', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $costvalue = $enrolment->price ? $enrolment->price.' '.$enrolment->currencycode : '-';
        $cost .= html_writer::tag('div', $costvalue, array('class' => 'felement'));
        $html .= html_writer::tag('div', $cost, array('class' => 'fitem'));

        $html .= html_writer::end_tag('div'); // End div tapsenrol_review_info.

        if ($tapsenrol->iw && $tapsenrol->iw->cancelinfo) {
            $html .= html_writer::start_tag('div', array('class' => 'tapsenrol_iw_cancelinfo mform'));
            $cancelinfo = html_writer::tag('div', '', array('class' => 'fitemtitle'));
            $cancelinfo .= html_writer::tag('div', nl2br($tapsenrol->iw->cancelinfo), array('class' => 'felement'));
            $html .= html_writer::tag('div', $cancelinfo, array('class' => 'fitem'));
            $html .= html_writer::end_tag('div');
        }

        return $html;
    }

    public function back_to_module($courseid) {
        $url = new moodle_url('/course/view.php', array('id' => $courseid));
        $link = html_writer::link($url, get_string('backtomodule', 'tapsenrol'));
        return html_writer::tag('p', $link);
    }

    public function back_to_coursemanager($tapscourseid) {
        $url = new moodle_url('/local/coursemanager/index.php', array('page' => 'course', 'cmcourse' => $tapscourseid));
        $link = html_writer::link($url, get_string('backtocoursemanager', 'tapsenrol'));
        return html_writer::tag('p', $link);
    }

    public function dropdown($title, array $links, $type = '', $small = true) {
        $small = $small ? 'btn-small' : '';
        $allowedtypes = array('primary', 'info', 'success', 'warning', 'danger', 'inverse');
        $type = in_array($type, $allowedtypes) ? "btn-{$type}" : '';

        $html = html_writer::start_tag('div', array('class' => 'btn-group'));
        $caret = html_writer::tag('span', '', array('class' => 'caret'));
        $html .= html_writer::tag('button', $title.' '.$caret, array('class' => "btn {$small} {$type} dropdown-toggle", 'data-toggle' => 'dropdown'));
        $html .= html_writer::start_tag('ul', array('class' => 'dropdown-menu'));
        foreach ($links as $link) {
            $link = html_writer::link($link['url'], $link['title']);
            $html .= html_writer::tag('li', $link);
        }
        $html .= html_writer::end_tag('ul');
        $html .= html_writer::end_tag('div');

        return $html;
    }

    public function iw_emails($hascap) {
        if ($hascap['global']) {
            $url = new moodle_url('/mod/tapsenrol/admin/emails.php');
            $link = html_writer::link($url, get_string('iw:emails:title:global', 'tapsenrol'), array('class' => 'btn btn-primary'));
            return html_writer::tag('p', $link);
        } else {
            return '';
        }
    }

    public function iw_current($iws, $hascap) {
        global $DB, $OUTPUT;

        $html = $OUTPUT->heading(get_string('iw:current', 'tapsenrol'), '3');

        $table = new html_table();
        $table->head = array(
            get_string('iw:id', 'tapsenrol'),
            get_string('iw:name', 'tapsenrol'),
            get_string('iw:sponsorentry', 'tapsenrol'),
            get_string('iw:rejectioncomments', 'tapsenrol'),
            get_string('iw:customenrolmentinfo', 'tapsenrol'),
            get_string('iw:customemails', 'tapsenrol'),
            get_string('iw:customapprovalinfo', 'tapsenrol'),
            get_string('iw:locked', 'tapsenrol'),
            get_string('iw:emails', 'tapsenrol'),
            get_string('iw:actions', 'tapsenrol'),
        );
        $table->align = array(
            'left', 'left', 'left',
            'center', 'center', 'center', 'center', 'center', 'center', 'center'
        );

        if (empty($iws)) {
            $cells = array();
            $cell = new html_table_cell();
            $cell->colspan = count($table->head);
            $cell->text = get_string('iw:nocurrent', 'tapsenrol');
            $cells[] = clone($cell);

            $table->data[] = new html_table_row($cells);
        } else {
            foreach ($iws as $iw) {
                $cells = array();
                $cell = new html_table_cell();

                // ID.
                $cell->text = $iw->id;
                $cells[] = clone($cell);

                // Name.
                $cell->text = $iw->name;
                $cell->attributes['class'] = 'name';
                $cells[] = clone($cell);

                // Sponsors.
                if (empty($iw->approvalrequired)) {
                    $cell->text = 'No approver';
                } else if (!empty($iw->sponsors)) {
                    $cell->text = 'Selection';
                } else {
                    $cell->text = 'Free entry';
                }
                $cell->attributes['class'] = '';
                $cells[] = clone($cell);

                // Attributes for remaingin cells.
                $cell->attributes['class'] = 'actions';

                // Rejection comments.
                $cell->text = $this->_yes_no_icon($iw->rejectioncomments);
                $cells[] = clone($cell);

                // Custom enrolment info.
                $cell->text = $this->_yes_no_icon($iw->enrolinfo);
                $cells[] = clone($cell);

                // Custom emails.
                $cell->text = $this->_yes_no_icon($DB->get_records('tapsenrol_iw_email_custom', array('internalworkflowid' => $iw->id, 'coursemoduleid' => null)));
                $cells[] = clone($cell);

                // Custom approval info.
                $cell->text = $this->_yes_no_icon($iw->approveinfo || $iw->rejectinfo || $iw->eitherinfo);
                $cells[] = clone($cell);

                // Locked.
                $lockurl = new moodle_url('/mod/tapsenrol/admin/internalworkflow.php', array('action' => 'lock', 'id' => $iw->id));
                if ($iw->locked) {
                    $lockurl->param('lock', 0);
                    $lockicon = html_writer::tag('i', '', array('class' => 'fa fa-lock'));
                } else {
                    $lockurl->param('lock', 1);
                    $lockicon = html_writer::tag('i', '', array('class' => 'fa fa-unlock'));
                }
                if ($hascap['global'] || $hascap['lock']) {
                    $cell->text = html_writer::link($lockurl, $lockicon);
                } else {
                    $cell->text = $lockicon;
                }
                $cells[] = clone($cell);

                // Emails on/off.
                $cell->text = $this->_yes_no_icon(!$iw->emailsoff);
                $cells[] = clone($cell);

                // Actions.
                $actions = array();

                if ($hascap['global'] || $hascap['edit']) {
                    $editurl = new moodle_url('/mod/tapsenrol/admin/internalworkflow.php', array('action' => 'edit', 'id' => $iw->id));
                    $editfaicon = $iw->locked ? 'fa-eye' : 'fa-pencil';
                    $editicon = html_writer::tag('i', '', array('class' => "fa $editfaicon"));
                    $actions[] = html_writer::link($editurl, $editicon);

                    $duplicateurl = new moodle_url('/mod/tapsenrol/admin/internalworkflow.php', array('action' => 'duplicate', 'id' => $iw->id));
                    $duplicateicon = html_writer::tag('i', '', array('class' => "fa fa-copy"));
                    $actions[] = html_writer::link($duplicateurl, $duplicateicon);

                    if (!$DB->get_records('tapsenrol', array('internalworkflowid' => $iw->id))) {
                        $deleteurl = new moodle_url('/mod/tapsenrol/admin/internalworkflow.php', array('action' => 'delete', 'id' => $iw->id));
                        $deleteicon = html_writer::tag('i', '', array('class' => 'fa fa-trash'));
                        $actions[] = html_writer::link($deleteurl, $deleteicon, array('class' => 'text-error'));
                    }
                }

                $cell->attributes['class'] = 'text-center actions';
                $cell->text = implode($actions);
                $cells[] = $cell;

                $table->data[] = new html_table_row($cells);
            }
        }

        $html .= html_writer::table($table);

        return $html;
    }

    protected function _yes_no_icon($yes = true) {
        if ($yes) {
            return html_writer::tag('i', '', array('class' => 'fa fa-check text-success'));
        } else {
            return html_writer::tag('i', '', array('class' => 'fa fa-times text-error'));
        }
    }

    public function iw_new($hascap) {
        if ($hascap['global'] || $hascap['edit']) {
            $url = new moodle_url('/mod/tapsenrol/admin/internalworkflow.php', array('action' => 'edit', 'id' => 0));
            $link = html_writer::link($url, get_string('iw:add', 'tapsenrol'), array('class' => 'btn btn-primary'));
            return html_writer::tag('p', $link);
        } else {
            return '';
        }
    }

    public function enrolments_for_approval($enrolments) {
        global $OUTPUT;

        $canviewall = has_capability('mod/tapsenrol:viewallapprovals', context_system::instance());

        $html = $OUTPUT->heading(get_string('approve:outstandingapprovals', 'tapsenrol'), 3);

        $table = new html_table();
        $table->head = array();
        $table->head[] = get_string('approve:name', 'tapsenrol');
        $table->head[] = get_string('approve:class', 'tapsenrol');
        $table->head[] = get_string('approve:requestcomments', 'tapsenrol');
        $table->head[] = get_string('approve:requested', 'tapsenrol');
        if ($canviewall) {
            $table->head[] = get_string('approve:sponsor', 'tapsenrol');
            $table->head[] = get_string('approve:sponsoremail', 'tapsenrol');
        }
        $table->head[] = get_string('approve:actions', 'tapsenrol');

        if (empty($enrolments)) {
            $cells = array();
            $cell = new html_table_cell();
            $cell->colspan = count($table->head);
            $cell->text = get_string('approve:nooutstanding', 'tapsenrol');
            $cells[] = clone($cell);

            $table->data[] = new html_table_row($cells);
        } else {
            foreach ($enrolments as $enrolment) {
                $cells = array();
                $cell = new html_table_cell();

                // Name.
                $cell->text = fullname($enrolment);
                $cell->attributes['class'] = 'text-left nowrap';
                $cells[] = clone($cell);

                // Class.
                $cell->text = "{$enrolment->coursename} ({$enrolment->classname})";
                $cell->attributes['class'] = 'text-left';
                $cells[] = clone($cell);

                // Request comments.
                $cell->text = $enrolment->requestcomments;
                $cell->attributes['class'] = 'text-left tapsenrol-request-comments';
                $cells[] = clone($cell);

                // Date of request.
                $cell->text = gmdate('d M Y', $enrolment->timeenrolled);
                $cell->attributes['class'] = 'text-left tapsenrol-request-comments';
                $cells[] = clone($cell);

                if ($canviewall) {
                    // Sponsor name.
                    $cell->text = "{$enrolment->sponsorfirstname} {$enrolment->sponsorlastname}";
                    $cell->attributes['class'] = 'text-left nowrap';
                    $cells[] = clone($cell);

                    // Sponsor email.
                    $cell->text = $enrolment->sponsoremail;
                    $cell->attributes['class'] = 'text-left';
                    $cells[] = clone($cell);
                }

                // Actions.
                $actions = array();
                $approveurl = new moodle_url('/mod/tapsenrol/approve.php', array('id' => $enrolment->enrolmentid, 'action' => 'approve'));
                $actions[] = html_writer::link($approveurl, get_string('approve:approve', 'tapsenrol'));

                $rejecturl = new moodle_url('/mod/tapsenrol/approve.php', array('id' => $enrolment->enrolmentid, 'action' => 'reject'));
                $actions[] = html_writer::link($rejecturl, get_string('approve:reject', 'tapsenrol'));

                $cell->attributes['class'] = 'text-center nowrap';
                $cell->text = implode(' | ', $actions);
                $cells[] = $cell;

                $table->data[] = new html_table_row($cells);
            }
        }

        $html .= html_writer::table($table);

        return $html;
    }

    public function approval_history() {
        global $CFG, $OUTPUT, $USER;

        require_once($CFG->dirroot.'/mod/tapsenrol/classes/tables.php');

        echo $OUTPUT->heading(get_string('approve:history', 'tapsenrol'), 3);

        $table = new tapsenrol_table_sql('tapsenrol-approval-history');

        $usernamefields = get_all_user_name_fields(true, 'u');
        $fields = "tit.*, u.id as userid, {$usernamefields}, lte.coursename, lte.classname, lte.classstarttime, lte.usedtimezone, lte.bookingstatus";
        $from = <<<EOF
    {tapsenrol_iw_tracking} tit
JOIN
    {local_taps_enrolment} lte
    ON lte.enrolmentid = tit.enrolmentid
JOIN
    {tapsenrol} t
    ON t.tapscourse = lte.courseid
JOIN
    {tapsenrol_iw} ti
    ON ti.id = t.internalworkflowid
JOIN
    {user} u
    ON u.idnumber = lte.staffid
EOF;
        $where = '(lte.archived = 0 OR lte.archived IS NULL) AND tit.approved IS NOT null';

        $params = array();
        if (!has_capability('mod/tapsenrol:viewallapprovals', context_system::instance())) {
            $useremail = strtolower($USER->email);
            $where .= ' AND tit.sponsoremail = :sponsoremail ';
            $params['sponsoremail'] = $useremail;
        }

        $table->set_sql($fields, $from, $where, $params);

        $table->define_columns(array('fullname', 'coursename', 'classname', 'classstarttime', 'timeapproved', 'approved', 'bookingstatus'));
        $table->text_sorting('coursename');
        $table->text_sorting('classname');
        $table->text_sorting('bookingstatus');
        $table->define_headers(
            array(
                get_string('approve:name', 'tapsenrol'),
                get_string('approve:course', 'tapsenrol'),
                get_string('approve:class', 'tapsenrol'),
                get_string('approve:classstartdate', 'tapsenrol'),
                get_string('approve:approvaldate', 'tapsenrol'),
                get_string('approve:status', 'tapsenrol'),
                get_string('approve:bookingstatus', 'tapsenrol')
            )
        );
        $url = new moodle_url('/mod/tapsenrol/approve.php');
        $table->define_baseurl($url);

        $table->sortable(true, 'approvaldate', SORT_DESC);
        $table->is_collapsible = false;

        $table->useridfield = 'userid';

        $table->out(10, false);
    }

    public function review_approval($title, $info, $iwtrack, $user, $enrolment, $course) {
        global $OUTPUT;

        try {
            $timezone = new DateTimeZone($enrolment->usedtimezone);
        } catch (Exception $e) {
            $timezone = new DateTimeZone(date_default_timezone_get());
        }

        $startdatetime = new DateTime(null, $timezone);
        $startdatetime->setTimestamp($enrolment->classstarttime);

        $html = $OUTPUT->heading($title, 2);

        $html .= html_writer::tag('p', $info);

        $html .= html_writer::start_tag('div', array('class' => 'tapsenrol_review_info mform'));

        $applicant = html_writer::tag('div', get_string('applicant', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $applicant .= html_writer::tag('div', fullname($user), array('class' => 'felement'));
        $html .= html_writer::tag('div', $applicant, array('class' => 'fitem'));

        $courseurl = new moodle_url('/course/view.php', array('id' => $course->id));
        $courselink = html_writer::link($courseurl, $course->fullname, array('onclick' => 'this.target="_blank"'));
        $newwindow = html_writer::tag('span', get_string('newwindow', 'tapsenrol'), array('class' => 'tapsenrol-new-window'));
        $course = html_writer::tag('div', get_string('course'). ':', array('class' => 'fitemtitle'));
        $course .= html_writer::tag('div', $courselink . $newwindow, array('class' => 'felement'));
        $html .= html_writer::tag('div', $course, array('class' => 'fitem'));

        $classname = html_writer::tag('div', get_string('classname', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $classname .= html_writer::tag('div', $enrolment->classname, array('class' => 'felement'));
        $html .= html_writer::tag('div', $classname, array('class' => 'fitem'));

        $duration = html_writer::tag('div', get_string('duration', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $durationvalue = ($enrolment->duration) ? (float) $enrolment->duration . ' ' . $enrolment->durationunits : get_string('tbc', 'tapsenrol');
        $duration .= html_writer::tag('div', $durationvalue, array('class' => 'felement'));
        $html .= html_writer::tag('div', $duration, array('class' => 'fitem'));

        $date = html_writer::tag('div', get_string('date', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        if (!$enrolment->classstarttime) {
            $datevalue = get_string('waitinglist:classroom', 'tapsenrol');
        } else {
            $datevalue = $startdatetime->format('d M Y');
            $datevalue .= ($enrolment->classstarttime != $enrolment->classstartdate) ? $startdatetime->format(' H:i T') : '';
            // Show UTC as GMT for clarity.
            $datevalue = str_replace('UTC', 'GMT', $datevalue);
        }
        $date .= html_writer::tag('div', $datevalue, array('class' => 'felement'));
        $html .= html_writer::tag('div', $date, array('class' => 'fitem'));

        $location = html_writer::tag('div', get_string('location', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $locationvalue = $enrolment->location ? $enrolment->location : get_string('tbc', 'tapsenrol');
        $location .= html_writer::tag('div', $locationvalue, array('class' => 'felement'));
        $html .= html_writer::tag('div', $location, array('class' => 'fitem'));

        if ($enrolment->trainingcenter) {
            $trainingcenter = html_writer::tag('div', get_string('trainingcenter', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
            $trainingcenter .= html_writer::tag('div', $enrolment->trainingcenter, array('class' => 'felement'));
            $html .= html_writer::tag('div', $trainingcenter, array('class' => 'fitem'));
        }

        $cost = html_writer::tag('div', get_string('cost', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $costvalue = $enrolment->price ? $enrolment->price.' '.$enrolment->currencycode : '-';
        $cost .= html_writer::tag('div', $costvalue, array('class' => 'felement'));
        $html .= html_writer::tag('div', $cost, array('class' => 'fitem'));

        $comments = html_writer::tag('div', get_string('approve:requestcomments', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $comments .= html_writer::tag('div', nl2br($iwtrack->requestcomments), array('class' => 'felement'));
        $html .= html_writer::tag('div', $comments, array('class' => 'fitem'));

        $html .= html_writer::end_tag('div'); // End div tapsenrol_review_info.

        return $html;
    }

    public function resend_invites_classes($tapsenrol, $classes) {

        $table = new html_table();
        $table->head = array(
            get_string('classname', 'tapsenrol'),
            get_string('location', 'tapsenrol'),
            get_string('date', 'tapsenrol'),
            get_string('time', 'tapsenrol'),
            get_string('duration', 'tapsenrol'),
            get_string('enrolments:placed', 'tapsenrol'),
            get_string('actions', 'tapsenrol'),
        );
        foreach ($table->head as $index => $value) {
            $table->head[$index] = str_ireplace(' ', '&nbsp;', $value);
        }

        if (empty($classes)) {
            $cells = array();
            $cell = new html_table_cell();
            $cell->colspan = count($table->head);
            $cell->text = get_string('resendinvites:noclasses', 'tapsenrol');
            $cells[] = clone($cell);

            $table->data[] = new html_table_row($cells);
        } else {
            foreach ($classes as $class) {
                $cells = array();

                $cells[] = $class->classname;

                $location = array();
                if ($class->trainingcenter) {
                    $location[] = $class->trainingcenter;
                }
                if ($class->location) {
                    $location[] = $class->location;
                }
                $cells[] = !empty($location) ? implode(', ', $location) : get_string('tbc', 'tapsenrol');

                if ($class->classstarttime) {
                    try {
                        $timezone = new DateTimeZone($class->usedtimezone);
                    } catch (Exception $e) {
                        $timezone = new DateTimeZone(date_default_timezone_get());
                    }

                    $startdatetime = new DateTime(null, $timezone);
                    $startdatetime->setTimestamp($class->classstarttime);
                    $enddatetime = new DateTime(null, $timezone);
                    $enddatetime->setTimestamp($class->classendtime);

                    if ($enddatetime->format('Ymd') > $startdatetime->format('Ymd')) {
                        $cells[] = $startdatetime->format('d M Y').' - '.$enddatetime->format('d M Y');
                    } else {
                        $cells[] = $startdatetime->format('d M Y');
                    }
                    if ($class->classstarttime != $class->classstartdate) {
                        $to = get_string('to', 'tapsenrol');
                        $time = $startdatetime->format('H:i')." {$to} ".$enddatetime->format('H:i T');
                        // Show UTC as GMT for clarity.
                        $cells[] = str_replace('UTC', 'GMT', $time);
                    } else {
                        $cells[] = '-';
                    }
                } else {
                    $cell = new html_table_cell(get_string('waitinglist:classroom', 'tapsenrol'));
                    $cell->colspan = 2;
                    $cells[] = $cell;
                }

                $cells[] = $class->classduration ? (float) $class->classduration.' '.$class->classdurationunits : '-';

                $cells[] = $class->enrolments;

                $resendurl = new moodle_url('/mod/tapsenrol/resend_invites.php', array('id' => $tapsenrol->cm->id, 'classid' => $class->classid));
                $resendtext = get_string('resendinvites', 'tapsenrol') . ' ' . html_writer::tag('span', '', array('class' => 'caret caret-right'));
                $cells[] = html_writer::link($resendurl, $resendtext, array('class' => 'btn btn-success btn-small'));

                $table->data[] = new html_table_row($cells);
            }
        }

        return html_writer::table($table);
    }

    public function resend_invites_class_info($class) {
        $html = '';

        try {
            $timezone = new DateTimeZone($class->usedtimezone);
        } catch (Exception $e) {
            $timezone = new DateTimeZone(date_default_timezone_get());
        }

        $startdatetime = new DateTime(null, $timezone);
        $startdatetime->setTimestamp($class->classstarttime);

        $html .= html_writer::start_tag('div', array('class' => 'tapsenrol_resend_invites_class mform'));

        $classname = html_writer::tag('div', get_string('classname', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $classname .= html_writer::tag('div', $class->classname, array('class' => 'felement'));
        $html .= html_writer::tag('div', $classname, array('class' => 'fitem'));

        $duration = html_writer::tag('div', get_string('duration', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $durationvalue = ($class->classduration) ? (float) $class->classduration . ' ' . $class->classdurationunits : get_string('tbc', 'tapsenrol');
        $duration .= html_writer::tag('div', $durationvalue, array('class' => 'felement'));
        $html .= html_writer::tag('div', $duration, array('class' => 'fitem'));

        $date = html_writer::tag('div', get_string('date', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        if (!$class->classstarttime) {
            $datevalue = get_string('waitinglist:classroom', 'tapsenrol');
        } else {
            $datevalue = $startdatetime->format('d M Y');
            $datevalue .= ($class->classstarttime != $class->classstartdate) ? $startdatetime->format(' H:i T') : '';
            // Show UTC as GMT for clarity.
            $datevalue = str_replace('UTC', 'GMT', $datevalue);
        }
        $date .= html_writer::tag('div', $datevalue, array('class' => 'felement'));
        $html .= html_writer::tag('div', $date, array('class' => 'fitem'));

        $location = html_writer::tag('div', get_string('location', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $locationvalue = $class->location ? $class->location : get_string('tbc', 'tapsenrol');
        $location .= html_writer::tag('div', $locationvalue, array('class' => 'felement'));
        $html .= html_writer::tag('div', $location, array('class' => 'fitem'));

        if ($class->trainingcenter) {
            $trainingcenter = html_writer::tag('div', get_string('trainingcenter', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
            $trainingcenter .= html_writer::tag('div', $class->trainingcenter, array('class' => 'felement'));
            $html .= html_writer::tag('div', $trainingcenter, array('class' => 'fitem'));
        }

        $cost = html_writer::tag('div', get_string('cost', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $costvalue = $class->price ? $class->price.' '.$class->currencycode : '-';
        $cost .= html_writer::tag('div', $costvalue, array('class' => 'felement'));
        $html .= html_writer::tag('div', $cost, array('class' => 'fitem'));

        $html .= html_writer::end_tag('div'); // End div tapsenrol_resend_invites_class.

        return $html;
    }

    public function modal($id = 'modalwindow', $title = '') {
        $output = '';

        $output .= html_writer::start_div(
            'modal fade',
            array(
                'id' => $id,
                'tabindex' => '-1',
                'role' => 'dialog',
                'aria-labelledby' => $id.'-label',
            )
        );

        $output .= html_writer::start_div('modal-dialog modal-lg');
        $output .= html_writer::start_div('modal-content');
        $output .= html_writer::start_div('modal-header');
        $output .= html_writer::tag(
            'button',
            html_writer::span('&times;', '', array('aria-hidden' => 'true')),
            array(
                'type' => 'button',
                'class' => 'close',
                'data-dismiss' => 'modal',
                'aria-label' => 'Close',
            )
        );
        $output .= html_writer::tag('h3', $title, array('id' => $id.'-label'));
        $output .= html_writer::end_div(); // End div modal-header.

        $imgsrc = $this->output->pix_url('loader', 'tapsenrol');
        $img = html_writer::empty_tag('img', array('src' => $imgsrc));
        $output .= html_writer::div($img, 'modal-body');

        $output .= html_writer::start_div('modal-footer');
        $output .= html_writer::tag(
            'button',
            get_string('close', 'tapsenrol'),
            array(
                'type' => 'button',
                'class' => 'btn btn-default',
                'data-dismiss' => 'modal',
            )
        );
        $output .= html_writer::end_div(); // End div modal-footer.
        $output .= html_writer::end_div(); // End div modal-content.
        $output .= html_writer::end_div(); // End div modal-dialog.
        $output .= html_writer::end_div(); // End div modal.

        return $output;
    }

    public function admin_links($tapsenrol) {
        $links = array();

        if ($tapsenrol->tapsenrol->internalworkflowid && has_capability('mod/tapsenrol:resendinvites', $tapsenrol->context->cm)) {
            $resendurl = new moodle_url('/mod/tapsenrol/resend_invites.php', array('id' => $tapsenrol->cm->id));
            $links[] = array(
                'url' => $resendurl,
                'title' => get_string('resendinvites', 'tapsenrol')
            );
        }

        if (has_capability('moodle/block:edit', $tapsenrol->context->cm)) {
            $enrolurl = new moodle_url('/mod/tapsenrol/enrol.php', array('id' => $tapsenrol->cm->id));
            $links[] = array(
                'url' => $enrolurl,
                'title' => get_string('admin:blocks:enrol', 'tapsenrol')
            );
            $cancelurl = new moodle_url('/mod/tapsenrol/cancel.php', array('id' => $tapsenrol->cm->id));
            $links[] = array(
                'url' => $cancelurl,
                'title' => get_string('admin:blocks:cancel', 'tapsenrol')
            );
        }

        if ($tapsenrol->tapsenrol->internalworkflowid && has_capability('mod/tapsenrol:manageenrolments', $tapsenrol->context->cm)) {
            $manageurl = new moodle_url('/mod/tapsenrol/manage_enrolments.php', array('id' => $tapsenrol->cm->id));
            $links[] = array(
                'url' => $manageurl,
                'title' => get_string('manageenrolments', 'tapsenrol')
            );
        }

        if (has_capability('local/coursemanagercourse:add', $tapsenrol->context->course)) {
            $editcourseurl = new moodle_url('/local/coursemanager/index.php', array('page' => 'course', 'cmcourse' => $tapsenrol->get_tapscourse()->id));
            $links[] = array(
                'url' => $editcourseurl,
                'title' => get_string('editcourse', 'tapsenrol')
            );
        }

        if (has_capability('local/coursemanagerclass:add', $tapsenrol->context->course)) {
            $editclassurl = new moodle_url('/local/coursemanager/index.php', array('page' => 'classoverview', 'cmcourse' => $tapsenrol->get_tapscourse()->id));
            $links[] = array(
                'url' => $editclassurl,
                'title' => get_string('editclass', 'tapsenrol')
            );
        }

        if (!empty($links)) {
            return $this->dropdown(
                get_string("admin:dropdown", 'tapsenrol'),
                $links,
                'warning',
                false
            );
        }
    }
}