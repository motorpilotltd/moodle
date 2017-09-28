<?php

/**
 * This page prints a particular instance of threesixty by redirecting
 * to the most appropriate page based on the user's capabilities.
 *
 * @author  Francois Marier <francois@catalyst.net.nz>
 * @package mod/threesixty
 */

require_once '../../config.php';

$id     = optional_param('id', 0, PARAM_INT); // course_module ID, or
$a      = optional_param('a', 0, PARAM_INT);  // threesixty instance ID
$userid = optional_param('userid', 0, PARAM_INT);

if ($id) {
    if (! $cm = get_coursemodule_from_id('threesixty', $id)) {
        print_error('invalidcoursemodule');
    }
    if (! $course = $DB->get_record('course', array('id' => $cm->course))) {
        print_error('coursemisconf');
    }
    if (! $threesixty = $DB->get_record('threesixty', array('id' => $cm->instance))) {
        print_error('invalidthreesixtyid', 'threesixty');
    }
    // move require_course_login here to use forced language for course
    // fix for MDL-6926
    require_course_login($course, true, $cm);
    $strthreesixtys = get_string('modulenameplural', 'threesixty');
    $strthreesixty = get_string('modulename', 'threesixty');
} else if ($a) {

    if (! $threesixty = $DB->get_record('threesixty', array('id' => $a))) {
        print_error('invalidthreesixtyid', 'threesixty');
    }
    if (! $course = $DB->get_record('course', array('id' => $threesixty->course))) {
        print_error('coursemisconf');
    }

    if (!$cm = get_coursemodule_from_instance('threesixty', $threesixty->id, $course->id)) {
        print_error('missingparameter');
    }
    // move require_course_login here to use forced language for course
    // fix for MDL-6926
    require_course_login($course, true, $cm);
    $strthreesixtys = get_string('modulenameplural', 'threesixty');
    $strthreesixty = get_string('modulename', 'threesixty');
} else {
    print_error('missingparameter');
}

$context = context_module::instance($cm->id);

require_login($course, true, $cm);
require_capability('mod/threesixty:view', $context);

$urlparams = array('a' => $threesixty->id);
if ($userid) {
    $urlparams['userid'] = $userid;
}

if (has_capability('mod/threesixty:viewreports', $context)) {
    $redirecturl = new moodle_url('/mod/threesixty/report.php', $urlparams);
} else {
    $redirecturl = new moodle_url('/mod/threesixty/profiles.php', $urlparams);
}

redirect($redirecturl);