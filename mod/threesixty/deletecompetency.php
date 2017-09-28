<?php

require_once '../../config.php';
require_once 'locallib.php';

$id = optional_param('id', 0, PARAM_INT); // coursemodule ID
$a = optional_param('a', 0, PARAM_INT); // activity instance ID
$c = required_param('c', PARAM_INT); // competency ID
$confirm = optional_param('confirm', 0, PARAM_INT);  // commit the operation?

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
    $strthreesixtys = get_string("modulenameplural", 'threesixty');
    $strthreesixty = get_string("modulename", 'threesixty');
} else {
    print_error('missingparameter');
}

/// Security

$context = context_module::instance($cm->id);

require_login($course->id, false, $cm);
require_capability('mod/threesixty:manage', $context);

$url = $CFG->wwwroot."/mod/threesixty/deletecompetency.php?a=$threesixty->id";
$returnurl = $CFG->wwwroot."/mod/threesixty/edit.php?a=$threesixty->id&amp;section=competencies";

/// Header

$strthreesixtys = get_string('modulenameplural', 'threesixty');
$strthreesixty  = get_string('modulename', 'threesixty');

$title = get_string('deletecompetency', 'threesixty', core_text::strtolower(threesixty_get_alternative_word($threesixty, 'competency')));
if ($competency != null) {
    $title = $competency->name;
}

if ($confirm) {
    if (threesixty_delete_competency($competency->id)) {
        threesixty_reorder_competencies($threesixty->id);
        $event = \mod_threesixty\event\competency_deleted::create(array(
            'context' => $context,
            'objectid' => $competency->id,
        ));
        $event->trigger();
    } else {
        print_error('error:cannotdeletecompetency', 'threesixty', $returnurl);
    }

    redirect($returnurl);
}

$PAGE->set_url($url);
$PAGE->set_title(format_string($threesixty->name . " - $title"));
$PAGE->set_heading('');
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(true);

echo $OUTPUT->header();

// Ask for confirmation

echo $OUTPUT->confirm('<b>'.format_string($competency->name).'</b><blockquote>'.
             format_string($competency->description).'</blockquote><p>'.
             get_string('areyousuredelete', 'threesixty', core_text::strtolower(threesixty_get_alternative_word($threesixty, 'competency'))).'</p>', "deletecompetency.php?a=$threesixty->id&amp;c=$competency->id&amp;confirm=1", $returnurl);

echo $OUTPUT->footer($course);
