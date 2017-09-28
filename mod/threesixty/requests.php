<?php

/**
 * Allows moodle users to check pending requests from other participants and attend them
 *
 * @author  Valery Fremaux <valery.fremaux@gmail.com>
 * @package mod-threesixty
 */

require_once '../../config.php';
require_once 'locallib.php';

define('RESPONSE_BASEURL', '/mod/threesixty/score.php');
define('DECLINE_BASEURL', '/mod/threesixty/requests.php');

$id      = optional_param('id', 0, PARAM_INT);  // coursemodule ID
$a       = optional_param('a', 0, PARAM_INT);  // threesixty instance ID
$userid  = optional_param('userid', 0, PARAM_INT);
$decline  = optional_param('decline', 0, PARAM_INT);

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
require_login($course, true, $cm);

if ($userid > 0 and !$user = $DB->get_record('user', array('id' => $userid))) {
    print_error('error:invaliduserid', 'threesixty');
}

if ($decline){
    confirm_sesskey(); // we'll be sure the decline is from the originator
    $respondent = $DB->get_record('threesixty_respondent', array('activityid' => $threesixty->id, 'userid' => $userid, 'respondentuserid' => $USER->id));
    $respondent->declined = 1;
    $respondent->declinetime = time();
    $DB->update_record('threesixty_respondent', $respondent);
}

/// Header

$url = $CFG->wwwroot.'/mod/threesixty/requests.php';

$PAGE->set_url($url);
$PAGE->set_title(format_string($threesixty->name));
$PAGE->set_heading(format_string($threesixty->name));
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(true);

$fakeblock = new block_contents(array('id' => 'threesixty_requests_page_block', 'class' => 'block'));
$fakeblock->collapsible = block_contents::VISIBLE;
$fakeblock->title = get_string('page:requests:block:title', 'threesixty');
$fakeblock->content = get_string('page:requests:block:content', 'threesixty');
$fakeblock->blockinstanceid = -1;
if (!empty($fakeblock->content)) {
    $PAGE->blocks->add_fake_block($fakeblock, 'left');
}

echo $OUTPUT->header();

/// Main content

$currenttab = 'requests';
$section = null;

include 'tabs.php';

echo get_string('page:requests:header', 'threesixty');

print_requests_table($threesixty->id, $USER->id, $context);

echo get_string('page:requests:footer', 'threesixty');

echo $OUTPUT->footer($COURSE);

/// Start local library

/**
*
*
*/
function print_requests_table($activityid, $userid, $context){
    global $OUTPUT;

    $requests = threesixty_has_requests($activityid);

    if (!empty($requests)){
        $table = new html_table();
        $table->head = array(get_string('lastname'), get_string('firstname'), get_string('actions', 'threesixty'));
        $table->align = array('left', 'left', 'center');
        foreach($requests as $r){
            if (empty($r->uniquehash)){
                print_error('error:unhashedrespondants', 'threesixty');
            }
            $assessurlparams = array('a' => $activityid, 'code' => $r->uniquehash, 'internal' => 1);
            $assessurl = new moodle_url('/mod/threesixty/score.php', $assessurlparams);
            $assesstext = get_string('assess', 'threesixty');
            $assessicon = html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/valid'), 'alt' => $assesstext, 'title' => $assesstext));
            $actions = array(
                html_writer::link($assessurl, $assessicon)
            );
            if (has_capability('mod/threesixty:declinerequest', $context)){
                $declineurlparams = array('a' => $activityid, 'decline' => $r->userid, 'sesskey' => sesskey());
                $declineurl = new moodle_url('/mod/threesixty/requests.php', $declineurlparams);
                $declinetext = get_string('decline', 'threesixty');
                $declineicon = html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/invalid'), 'alt' => $declinetext, 'title' => $declinetext));
                $actions[] = html_writer::link($declineurl, $declineicon);
            }
            $table->data[] = array($r->lastname, $r->firstname, implode('&nbsp;', $actions));
        }
        echo html_writer::table($table);
    } else {
        echo $OUTPUT->box(get_string('norequests', 'threesixty'));
    }
}

