<?php

/**
 * Table and Spiderweb reports
 *
 * @author  Francois Marier <francois@catalyst.net.nz>
 * @package mod/threesixty
 */

require_once '../../config.php';
require_once 'locallib.php';
require_once 'report_form.php';
require_once 'reportlib.php';

define('AVERAGE_PRECISION', 1); // number of decimal places when displaying averages

$id     = optional_param('id', 0, PARAM_INT);  // coursemodule ID
$a      = optional_param('a', 0, PARAM_INT);  // threesixty instance ID
$type   = optional_param('type', 'table', PARAM_ALPHA); // report type
$userid = optional_param('userid', 0, PARAM_INT); // user's data to examine
$basetype = optional_param('base', 'self0', PARAM_ALPHANUM); // Score to do gap analysis from.

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

$context = context_module::instance($cm->id);
require_login($course, true, $cm);
if (!has_capability('mod/threesixty:viewreports', $context)) {
    if (time() < $threesixty->reportsavailable) {
        redirect(new moodle_url('/mod/threesixty/profiles.php', array('a' => $threesixty->id)));
    }
    require_capability('mod/threesixty:viewownreports', $context);
    $userid = $USER->id; // force same user
}

$user = null;
if ($userid > 0 and !$user = $DB->get_record('user', array('id' => $userid))) {
    print_error('error:invaliduserid');
}

$url = $CFG->wwwroot."/mod/threesixty/report.php?a=$threesixty->id&amp;type=$type";

$mform = null;
$selffilters = array();
$respondentfilters = array();
$showrespondentaverage = true;

if (isset($user)) {
    $currenturl = "$url&amp;userid=$user->id";
    $selftypes = explode("\n", $threesixty->selftypes);
    $respondenttypes = explode("\n", $threesixty->respondenttypes);
    if (count($selftypes) > 1) {
      foreach ($selftypes as $key => $value) {
        $value = trim($value);
        if (!empty($value)){
          $selffilters[$key] = $selftypes[$key] = $value;
        } else {
            unset($selftypes[$key]);
        }
      }
    }
    if (!empty($respondenttypes)) {
        foreach ($respondenttypes as $key => $value) {
            $value = trim($value);
            if (!empty($value)) {
                $respondentfilters[$key] = $respondenttypes[$key] = $value;
            } else {
                unset($respondenttypes[$key]);
            }
        }
    }
    $mform = new mod_threesixty_report_form(null, compact('a', 'type', 'userid', 'selffilters', 'respondentfilters'));
    // Apply the filters
    if ($fromform = $mform->get_data()) {
        foreach ($selffilters as $code => $name) {
            if (empty($fromform->selftype[$code])) {
                unset($selffilters[$code]); // 'code' is not checked, remove it
            }
        }
        foreach ($respondentfilters as $code => $name) {
            if (empty($fromform->respondenttype[$code])) {
                unset($respondentfilters[$code]); // 'code' is not checked, remove it
            }
        }
        $showrespondentaverage = $fromform->showrespondentaverage;
    }
    $event = \mod_threesixty\event\report_viewed::create(array(
        'context' => $context,
        'objectid' => $threesixty->id,
        'relateduserid' => $user->id,
    ));
    $event->trigger();

    if ($type == 'spiderweb') {
        $PAGE->requires->jquery_plugin('ui-css');
        $PAGE->requires->js_call_amd('mod_threesixty/spiderweb', 'init', array('target' => '#threesixty-spiderweb-select'));
    }
} else {
    $PAGE->requires->jquery();
    $PAGE->requires->js(new moodle_url('/mod/threesixty/js/chosen.jquery.min.js'));
    $PAGE->requires->css(new moodle_url('/mod/threesixty/css/chosen.min.css'));
    $PAGE->requires->css(new moodle_url('/mod/threesixty/css/chosen.threesixty.css'));
    $PAGE->requires->js_init_code(js_writer::function_call("jQuery('.chosen-select').chosen"), true);
}

/// Header

$strthreesixtys = get_string('modulenameplural', 'threesixty');
$strthreesixty  = get_string('modulename', 'threesixty');

/// Start outputing screen here

$PAGE->set_url($url);
$PAGE->set_title(format_string($threesixty->name));
$PAGE->set_heading(format_string($threesixty->name));
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(true);

$fakeblock = new block_contents(array('id' => 'threesixty_report_page_block', 'class' => 'block'));
$fakeblock->collapsible = block_contents::VISIBLE;
$fakeblock->title = get_string('page:report:block:title', 'threesixty');
$fakeblock->content = get_string('page:report:block:content', 'threesixty');
$fakeblock->blockinstanceid = -1;
if (!empty($fakeblock->content)) {
    $PAGE->blocks->add_fake_block($fakeblock, 'left');
}

echo $OUTPUT->header();

/// Main content

$currenttab = 'reports';
include 'tabs.php';

echo get_string('page:report:header', 'threesixty');

if (isset($mform)) {
    if (!$analysis = $DB->get_record('threesixty_analysis', array('activityid' => $threesixty->id, 'userid' => $user->id))) {
        echo $OUTPUT->notification(get_string('error:nodataforuserx', 'threesixty', fullname($user)));
        $returnurl = "profiles.php?a=$threesixty->id";
        echo $OUTPUT->continue_button($returnurl);
        echo $OUTPUT->footer($course);
        die;
    }

    $sql = "
        SELECT
            COUNT(1)
        FROM
            {threesixty_respondent} r
        JOIN
            {threesixty_response} rs
            ON rs.respondentid = r.id AND rs.analysisid = r.analysisid
        WHERE
            r.analysisid = ? AND
            r.uniquehash IS NOT NULL AND
            rs.timecompleted > 0
    ";

    $currentresponses = $DB->count_records_sql($sql, array($analysis->id));
    $remainingresponses = $threesixty->requiredrespondents - $currentresponses;

    if ($remainingresponses > 0) {
        echo "<br />";
        echo $OUTPUT->notification(get_string('respondentsremaining', 'threesixty'));
    }
    print threesixty_selected_user_heading($user, $course->id, $url, has_capability('mod/threesixty:viewreports', $context), true);

    /// Display filters
    $mform->display();

    /// Display scores
    if ('table' == $type) {
        $selfscores = threesixty_get_self_scores($analysis->id, $selffilters);
        $respondentscores = threesixty_get_respondent_scores($analysis->id, $respondentfilters);
        $skillnames = threesixty_get_skill_names($threesixty->id, 'competency');
        if ($threesixty->questionorder == 'competency') {
            $orderedskillnames =& $skillnames;
        } else {
            $orderedskillnames = threesixty_get_skill_names($threesixty->id, $threesixty->questionorder);
        }
        //$feedback = threesixty_get_feedback($analysis->id);
        echo html_writer::start_div('threesixty-report-tables');
        print_aggregate_score_table($threesixty, $skillnames, $selfscores, $selftypes, $respondentscores, $respondenttypes, $showrespondentaverage);
        print_skill_score_table($threesixty, $orderedskillnames, $selfscores, $selftypes, $respondentscores, $respondenttypes, $showrespondentaverage);
        echo html_writer::end_div();
    } elseif ('spiderweb' == $type) {
        $selfscores = threesixty_get_self_scores($analysis->id, $selffilters);
        $respondentscores = threesixty_get_respondent_scores($analysis->id, $respondentfilters);
        $skillnames = threesixty_get_skill_names($threesixty->id, 'competency', true);
        $spiderweburls = get_spiderweb_urls($cm->id, $threesixty, $skillnames, $selfscores, $selftypes, $respondentscores, $respondenttypes, $showrespondentaverage);
        echo html_writer::start_div('threesixty-spiderwebs');
        $count = 0;
        $totalcount = count($spiderweburls);
        $spiderweboptions = html_writer::tag(
            'option',
            str_replace('[[x]]', 0, get_string('loadedxofy', 'threesixty', $totalcount)),
            array(
                'value' => 0,
                'data-total' => $totalcount,
                'data-count' => 0,
                'data-text' => get_string('loadedxofy', 'threesixty', $totalcount),
            )
        );
        $spiderwebhtml = '';
        foreach ($spiderweburls as $spiderweburl) {
            $count++;
            $spiderweboptions .= html_writer::tag(
                'option',
                get_string('loading', 'threesixty', $spiderweburl->title),
                array(
                    'value' => $count,
                    'disabled' => 'disabled',
                    'data-title' => $spiderweburl->title
                )
            );
            $spiderwebhtml .= html_writer::div(
                html_writer::empty_tag(
                    'img',
                    array(
                        'src' => $OUTPUT->pix_url('i/loading'),
                        'class' => 'threesixty-spiderweb',
                        'data-src' => $spiderweburl->url,
                        'data-id' => $count,
                    )
                ),
                'threesixty-spiderweb-wrapper',
                array('id' => 'threesixty-spiderweb-wrapper-'.$count)
            );
        }
        echo html_writer::tag(
            'form',
            html_writer::tag('label', get_string('viewchart', 'threesixty'), array('style' => 'margin-right: 10px;')) .
            html_writer::tag('select', $spiderweboptions),
            array('id' => 'threesixty-spiderweb-select'));
        echo $spiderwebhtml;
        echo html_writer::end_div();
    } else {
        print_error('error:invalidreporttype', 'threesixty', "view.php?a=$threesixty->id", $type);
    }
} else {
    $groupids = array();
    require_once 'group_form.php';
    $mform = new mod_threesixty_group_form(null, compact('a', 'type'));
    if ($mform->groups) {
        $fromform = $mform->get_data();
        if ($fromform && isset($fromform->groupids)) {
            $groupids = $fromform->groupids;
        }
        $mform->display();
    }
    print threesixty_user_listing($threesixty, $groupids, $url);
}

echo get_string('page:report:footer', 'threesixty');

echo $OUTPUT->footer($course);
