<?php

require_once '../../config.php';
require_once 'editcompetency_form.php';
require_once 'locallib.php';

$a = optional_param('a', 0, PARAM_INT); // threesixty instance id
$id = optional_param('id', 0, PARAM_INT); // threesixty instance id
$c = optional_param('c', 0, PARAM_INT); // competency id

$url = $CFG->wwwroot.'/mod/threesixty/editcompetency.php?id='.$id;

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

$competency = null;
$skills = null;

if ($c > 0) {
    if (!$competency = $DB->get_record('threesixty_competency', array('id' => $c))) {
        print_error('invalidcompetencyid', 'threesixty');
    }
    $skills = $DB->get_records('threesixty_skill', array('competencyid' => $competency->id), 'sortorder');
}

$context = context_module::instance($cm->id);

require_login($course->id, false, $cm);
require_capability('mod/threesixty:manage', $context);

$returnurl = $CFG->wwwroot."/mod/threesixty/edit.php?a=$threesixty->id&amp;section=competencies";

$mform = new mod_threesixty_editcompetency_form(null, compact('a', 'c', 'threesixty', 'skills'));

if ($mform->is_cancelled()){
    redirect($returnurl);
}

if ($fromform = $mform->get_data()) { // Form submitted

    if (empty($fromform->submitbutton)) {
        print_error('error:unknownbuttonclicked', 'threesixty', $returnurl);
    }

    if (!isset($fromform->showfeedback) ) {
        $fromform->showfeedback = 0;
    }
    $todb = new stdClass();
    $todb->activityid = $threesixty->id;
    $todb->name = trim($fromform->name);
    $todb->description = trim($fromform->description);
    $todb->showfeedback = $fromform->showfeedback;
    $todb->colour = $fromform->colour;

    $originurl = null;
    $competencyid = null;

     //SCANMSG: transactions may need additional fixing
        $transaction = $DB->start_delegated_transaction();

        // General
        if ($competency != null) {
            $competencyid = $competency->id;
            $originurl = "editcompetency.php?a=$threesixty->id&amp;c=$competencyid";
            $todb->id = $competencyid;
            if ($DB->update_record('threesixty_competency', $todb)) {
                $event = \mod_threesixty\event\competency_updated::create(array(
                    'context' => $context,
                    'objectid' => $todb->id,
                ));
                $event->trigger();
            } else {
                print_error('error:cannotupdatecompetency', 'threesixty', $returnurl);
            }
        } else {
            $originurl = "editcompetency.php?a=$threesixty->id&amp;c=0";
            //Set the sortorder to the end of the line.
            $todb->sortorder = $DB->count_records('threesixty_competency', array('activityid' => $threesixty->id));
            if ($competencyid = $DB->insert_record('threesixty_competency', $todb)) {
                $event = \mod_threesixty\event\competency_created::create(array(
                    'context' => $context,
                    'objectid' => $competencyid,
                ));
                $event->trigger();
            } else {
                print_error('error:cannotaddcompetency', 'threesixty', $returnurl);
            }
        }
        // Skills
        for ($i = 0; $i < $fromform->skill_repeats; $i++) {
            $skillid = $fromform->skillid[$i];
            $skillname = '';
            if (!empty($fromform->skillname[$i])) {
                $skillname = $fromform->skillname[$i];
            }
            $skilldescription = '';
            if (!empty($fromform->skilldescription[$i])) {
                $skilldescription = $fromform->skilldescription[$i];
            }
            $skillaltname = '';
            if (!empty($fromform->skillaltname[$i])) {
                $skillaltname = $fromform->skillaltname[$i];
            }
            $skillaltdescription = '';
            if (!empty($fromform->skillaltdescription[$i])) {
                $skillaltdescription = $fromform->skillaltdescription[$i];
            }
            $skilldelete = false;
            if (!empty($fromform->skilldelete[$i])) {
                $skilldelete = (1 == $fromform->skilldelete[$i]);
            }
            if ($skillid > 0) { // Existing skill.
                if (!empty($fromform->skilldelete[$i])) { // Delete.
                    if (threesixty_delete_skill($skillid, true)) {
                        $event = \mod_threesixty\event\skill_deleted::create(array(
                            'context' => $context,
                            'objectid' => $skillid,
                        ));
                        $event->trigger();
                    } else {
                        print_error('error:cannotdeleteskill', 'threesixty', $returnurl);
                    }
                } elseif (!empty($skillname)) { // Update.
                    $todb = new stdClass();
                    $todb->id = $skillid;
                    $todb->name = $skillname;
                    $todb->description = $skilldescription;
                    $todb->altname = $skillaltname;
                    $todb->altdescription = $skillaltdescription;
                    if ($DB->update_record('threesixty_skill', $todb)) {
                        $event = \mod_threesixty\event\skill_updated::create(array(
                            'context' => $context,
                            'objectid' => $skillid,
                        ));
                        $event->trigger();
                    }
                    else {
                        print_error('error:cannotupdateskill', 'threesixty', $returnurl);
                    }
                }
                // Skip skills without a name.
            } elseif (!$skilldelete and !empty($skillname)) { // Insert
                $todb = new stdClass();
                $todb->competencyid = $competencyid;
                $todb->name = $skillname;
                $todb->description = $skilldescription;
                $todb->altname = $skillaltname;
                $todb->altdescription = $skillaltdescription;
                $todb->sortorder = $i;
                if ($todb->id = $DB->insert_record('threesixty_skill', $todb)) {
                    $event = \mod_threesixty\event\skill_created::create(array(
                        'context' => $context,
                        'objectid' => $todb->id,
                    ));
                    $event->trigger();
                } else {
                    print_error('error:cannotaddskill', 'threesixty', $returnurl);
                }
            }
            // Skip new skills marked as deleted or with an empty name.
        }
        $transaction->allow_commit();
    redirect($returnurl);
} elseif ($competency != null) { // Edit mode

    // Set values for the form
    $toform = new stdClass();
    $toform->name = $competency->name;
    $toform->description = $competency->description;
    $toform->showfeedback = ($competency->showfeedback == 1);
    $toform->colour = $competency->colour;

    if ($skills) {
        $i = 0;
        foreach ($skills as $skill) {
            $idfield = "skillid[$i]";
            $namefield = "skillname[$i]";
            $descriptionfield = "skilldescription[$i]";
            $altnamefield = "skillaltname[$i]";
            $altdescriptionfield = "skillaltdescription[$i]";
            $sortorderfield = "skillsortorder[$i]";
            $toform->$idfield = $skill->id;
            $toform->$namefield = $skill->name;
            $toform->$descriptionfield = $skill->description;
            $toform->$altnamefield = $skill->altname;
            $toform->$altdescriptionfield = $skill->altdescription;
            $toform->$sortorderfield = $skill->sortorder;
            $i++;
        }
    }
    $mform->set_data($toform);
}

/// Header

$strthreesixtys = get_string('modulenameplural', 'threesixty');
$strthreesixty  = get_string('modulename', 'threesixty');

$title = get_string('addnewcompetency', 'threesixty', core_text::strtolower(threesixty_get_alternative_word($threesixty, 'competency')));
if ($competency != null) {
    $title = $competency->name;
}

$PAGE->set_url($url);
$PAGE->set_title(format_string($threesixty->name . " - $title"));
$PAGE->set_heading('');
$PAGE->set_focuscontrol('');
$PAGE->set_cacheable(true);

echo $OUTPUT->header();

/// Start page

include 'tabs.php';
$mform->display();

echo $OUTPUT->footer($course);
