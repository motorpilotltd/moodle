<?php

/**
 * Display a confirmation that the response has been accepted.
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
} else if ($a) {

    if (! $threesixty = $DB->get_record('threesixty', array('id' => $a))) {
        print_error('invalidthreesictyid', 'threesixty');
    }
    if (! $course = $DB->get_record('course', array('id' => $threesixty->course))) {
        print_error('coursemisconf');
    }

    if (!$cm = get_coursemodule_from_instance("threesixty", $threesixty->id, $course->id)) {
        print_error('invalidcoursemodule');
    }
} else {
    print_error('missingparameter');
}

$strthreesixtys = get_string("modulenameplural", 'threesixty');
$strthreesixty = get_string("modulename", 'threesixty');

// Header

$PAGE->set_url($CFG->wwwroot."/mod/threesixty/thankyou.php?a=$threesixty->id");
$PAGE->set_cm($cm);
$PAGE->set_title(format_string($threesixty->name));
$PAGE->set_heading(format_string($threesixty->name));
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(true);

$fakeblock = new block_contents(array('id' => 'threesixty_thankyou_page_block', 'class' => 'block'));
$fakeblock->collapsible = block_contents::VISIBLE;
$fakeblock->title = get_string('page:thankyou:block:title', 'threesixty');
$fakeblock->content = get_string('page:thankyou:block:content', 'threesixty');
$fakeblock->blockinstanceid = -1;
if (!empty($fakeblock->content)) {
    $PAGE->blocks->add_fake_block($fakeblock, 'left');
}

echo $OUTPUT->header();

echo get_string('page:thankyou:header', 'threesixty');

// Main content

echo $OUTPUT->box(get_string('thankyoumessage', 'threesixty'));

echo get_string('page:thankyou:footer', 'threesixty');

echo $OUTPUT->footer($course);
