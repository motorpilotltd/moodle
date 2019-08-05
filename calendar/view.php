<?php

/////////////////////////////////////////////////////////////////////////////
//                                                                         //
// NOTICE OF COPYRIGHT                                                     //
//                                                                         //
// Moodle - Calendar extension                                             //
//                                                                         //
// Copyright (C) 2003-2004  Greek School Network            www.sch.gr     //
//                                                                         //
// Designed by:                                                            //
//     Avgoustos Tsinakos (tsinakos@teikav.edu.gr)                         //
//     Jon Papaioannou (pj@moodle.org)                                     //
//                                                                         //
// Programming and development:                                            //
//     Jon Papaioannou (pj@moodle.org)                                     //
//                                                                         //
// For bugs, suggestions, etc contact:                                     //
//     Jon Papaioannou (pj@moodle.org)                                     //
//                                                                         //
// The current module was developed at the University of Macedonia         //
// (www.uom.gr) under the funding of the Greek School Network (www.sch.gr) //
// The aim of this project is to provide additional and improved           //
// functionality to the Asynchronous Distance Education service that the   //
// Greek School Network deploys.                                           //
//                                                                         //
// This program is free software; you can redistribute it and/or modify    //
// it under the terms of the GNU General Public License as published by    //
// the Free Software Foundation; either version 2 of the License, or       //
// (at your option) any later version.                                     //
//                                                                         //
// This program is distributed in the hope that it will be useful,         //
// but WITHOUT ANY WARRANTY; without even the implied warranty of          //
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the           //
// GNU General Public License for more details:                            //
//                                                                         //
//          http://www.gnu.org/copyleft/gpl.html                           //
//                                                                         //
/////////////////////////////////////////////////////////////////////////////

/**
 * Display the calendar page.
 * @copyright 2003 Jon Papaioannou
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package core_calendar
 */

require_once('../config.php');
require_once($CFG->dirroot.'/course/lib.php');
require_once($CFG->dirroot.'/calendar/lib.php');

$courseid = optional_param('course', SITEID, PARAM_INT);
$view = optional_param('view', 'upcoming', PARAM_ALPHA);
$day = optional_param('cal_d', 0, PARAM_INT);
$mon = optional_param('cal_m', 0, PARAM_INT);
$year = optional_param('cal_y', 0, PARAM_INT);
$time = optional_param('time', 0, PARAM_INT);
/* BEGIN CORE MOD */
$id   = optional_param('id', 0, PARAM_INT);
/* END CORE MOD */

$url = new moodle_url('/calendar/view.php');

/* BEGIN CORE MOD */
if ($id !== 0) {
    $url->param('id', $id);
}
/* END CORE MOD */

// If a day, month and year were passed then convert it to a timestamp. If these were passed
// then we can assume the day, month and year are passed as Gregorian, as no where in core
// should we be passing these values rather than the time. This is done for BC.
if (!empty($day) && !empty($mon) && !empty($year)) {
    if (checkdate($mon, $day, $year)) {
        $time = make_timestamp($year, $mon, $day);
    }
}

if (empty($time)) {
    $time = time();
}

if ($courseid != SITEID) {
    $url->param('course', $courseid);
}

if ($view !== 'upcoming') {
    $time = usergetmidnight($time);
    $url->param('view', $view);
}

$url->param('time', $time);

$PAGE->set_url($url);

if ($courseid != SITEID && !empty($courseid)) {
    // Course ID must be valid and existing.
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $courses = array($course->id => $course);
    $issite = false;
    navigation_node::override_active_url(new moodle_url('/course/view.php', array('id' => $course->id)));
} else {
    $course = get_site();
    $courses = calendar_get_default_courses();
    $issite = true;
}

require_login($course, false);

/* BEGIN CORE MOD */
if (has_capability('local/lunchandlearn:view', context_system::instance())) {
    require_once "{$CFG->dirroot}/local/lunchandlearn/lib.php";
    lunchandlearn_add_page_navigation($PAGE, $url);
    lunchandlearn_add_admin_navigation($PAGE, $url);
}
/* END CORE MOD */

$calendar = new calendar_information(0, 0, 0, $time);
$calendar->prepare_for_view($course, $courses);

$pagetitle = '';

$strcalendar = get_string('calendar', 'calendar');

switch($view) {
/* BEGIN CORE MOD */
    case 'event':
        require_once "{$CFG->dirroot}/local/lunchandlearn/lib.php";
        try {
            if (empty($id)) {
                $view = 'upcoming';
                continue;
            }
            $lal = new lunchandlearn($id);
            $PAGE->navbar->add(
                    userdate($lal->scheduler->get_date(), get_string('strftimedate')),
                    $lal->get_cal_url()
                    );
            $PAGE->navbar->add($lal->get_name());
            $pagetitle = $lal->get_name();
        } catch(Exception $ex) {
            // Print a pretty notice.
            notice($ex->getMessage());
        }
    break;
/* END CORE MOD */
    case 'day':
        $PAGE->navbar->add(userdate($time, get_string('strftimedate')));
        $pagetitle = get_string('dayviewtitle', 'calendar', userdate($time, get_string('strftimedaydate')));
    break;
    case 'month':
        $PAGE->navbar->add(userdate($time, get_string('strftimemonthyear')));
        $pagetitle = get_string('detailedmonthviewtitle', 'calendar', userdate($time, get_string('strftimemonthyear')));
    break;
    case 'upcoming':
        $pagetitle = get_string('upcomingevents', 'calendar');
    break;
}

// Print title and header
$PAGE->set_pagelayout('standard');
$PAGE->set_title("$course->shortname: $strcalendar: $pagetitle");
$PAGE->set_heading($COURSE->fullname);
/* BEGIN CORE MOD */
// $PAGE->set_button(calendar_preferences_button($course));
/* END CORE MOD */

$renderer = $PAGE->get_renderer('core_calendar');
/* BEGIN CORE MOD */
$calendar->add_sidecalendar_blocks($renderer, false, $view);
/* END CORE MOD */

echo $OUTPUT->header();
echo $renderer->start_layout();
/* BEGIN CORE MOD (added view to class */
echo html_writer::start_tag('div', array('class'=>'heightcontainer view-'.$view));
/* END CORE MOD */
echo $OUTPUT->heading(get_string('calendar', 'calendar'));

switch($view) {
/* BEGIN CORE MOD */
    case 'event':
        echo $renderer->show_event($lal);
        $event = \local_lunchandlearn\event\event_viewed::create(array(
            'objectid' => $lal->get_id(),
        ));
        $event->trigger();
    break;
/* END CORE MOD */
    case 'day':
        echo $renderer->show_day($calendar);
    break;
    case 'month':
        echo $renderer->show_month_detailed($calendar, $url);
    break;
    case 'upcoming':
        $defaultlookahead = CALENDAR_DEFAULT_UPCOMING_LOOKAHEAD;
        if (isset($CFG->calendar_lookahead)) {
            $defaultlookahead = intval($CFG->calendar_lookahead);
        }
        $lookahead = get_user_preferences('calendar_lookahead', $defaultlookahead);

        $defaultmaxevents = CALENDAR_DEFAULT_UPCOMING_MAXEVENTS;
        if (isset($CFG->calendar_maxevents)) {
            $defaultmaxevents = intval($CFG->calendar_maxevents);
        }
        $maxevents = get_user_preferences('calendar_maxevents', $defaultmaxevents);
        echo $renderer->show_upcoming_events($calendar, $lookahead, $maxevents);
    break;
}

//Link to calendar export page.
echo $OUTPUT->container_start('bottom');
if (!empty($CFG->enablecalendarexport)) {
    echo $OUTPUT->single_button(new moodle_url('export.php', array('course'=>$courseid)), get_string('exportcalendar', 'calendar'));
    if (calendar_user_can_add_event($course)) {
/* BEGIN CORE MOD */
        // echo $OUTPUT->single_button(new moodle_url('/calendar/managesubscriptions.php', array('course'=>$courseid)), get_string('managesubscriptions', 'calendar'));
/* END CORE MOD */
    }
/* BEGIN CORE MOD */
/*
    if (isloggedin()) {
        $authtoken = sha1($USER->id . $DB->get_field('user', 'password', array('id' => $USER->id)) . $CFG->calendar_exportsalt);
        $link = new moodle_url(
            '/calendar/export_execute.php',
            array('preset_what'=>'all', 'preset_time' => 'recentupcoming', 'userid' => $USER->id, 'authtoken'=>$authtoken)
        );
        echo html_writer::tag('a', 'iCal',
            array('href' => $link, 'title' => get_string('quickdownloadcalendar', 'calendar'), 'class' => 'ical-link m-l-1'));
    }
*/
/* END CORE MOD */
}

echo $OUTPUT->container_end();
echo html_writer::end_tag('div');
echo $renderer->complete_layout();
echo $OUTPUT->footer();
