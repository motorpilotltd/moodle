<?php

/**
 * Table and Spiderweb reports
 *
 * @author  Francois Marier <francois@catalyst.net.nz>
 * @package mod/threesixty
 */

require_once '../../config.php';
require_once 'locallib.php';
require_once 'reportlib.php';
require_once 'lib/pdf.php';

define('AVERAGE_PRECISION', 1); // number of decimal places when displaying averages

$id      = optional_param('id', 0, PARAM_INT);  // coursemodule ID
$a       = optional_param('a', 0, PARAM_INT);  // threesixty instance ID
$type    = optional_param('type', 'table', PARAM_ALPHA); // report type
$userid  = optional_param('userid', 0, PARAM_INT); // user's data to examine

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

$url = $CFG->wwwroot."/mod/threesixty/print.php?a=$threesixty->id&amp;type=$type";

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
}

if (!$analysis = $DB->get_record('threesixty_analysis', array('activityid' => $threesixty->id, 'userid' => $user->id))) {
    echo $OUTPUT->notification(get_string('error:nodataforuserx', 'threesixty', fullname($user)));
    $returnurl = "profiles.php?a=$threesixty->id";
    echo $OUTPUT->continue_button($returnurl);
    echo $OUTPUT->footer($course);
    die;
}

$event = \mod_threesixty\event\analysis_printed::create(array(
    'context' => $context,
    'objectid' => $threesixty->id,
    'relateduserid' => $user->id,
));
$event->trigger();

$pdf = new threesixty_pdf('P', 'mm', 'A4', true, 'UTF-8');

$pdf->SetTitle('Influencing Spectrum Results');
$pdf->SetAutoPageBreak(true, 15);

$pdf->footer_fullname = fullname($user);

$pdf->SetMargins(10, 20);
$pdf->SetHeaderMargin(5);
$pdf->SetFooterMargin(0);
$pdf->SetFontSize(10);
$pdf->SetTextColor(38, 38, 38);
$pdf->SetFillColor(255, 255, 255);

$pdf->AddPage();
$pdf->SetFont('times', '', 24);
$pdf->setY(50);
$title = '<p align="center">Influencing Spectrum Results<br />For<br />'.fullname($user).'</p>';
$pdf->writeHTML($title);
$pdf->Image(K_PATH_IMAGES.'/mod/threesixty/pix/report_image.png', '', 111, 0, 75, 'PNG', '', '', 2, 150, 'C', false, false, 0, false, false, false, false, array());
$pdf->setY(240);
$date = '<p align="center">'.date('d M Y').'</p>';
$pdf->writeHTML($date);

$pdf->SetFont('helvetica', '', 8);

$selfscores = threesixty_get_self_scores($analysis->id, $selffilters);
$respondentscores = threesixty_get_respondent_scores($analysis->id, $respondentfilters);
$skillnames = threesixty_get_skill_names($threesixty->id, 'competency');
if ($threesixty->questionorder == 'competency') {
    $orderedskillnames =& $skillnames;
} else {
    $orderedskillnames = threesixty_get_skill_names($threesixty->id, $threesixty->questionorder);
}

$aggregatescoretable = print_aggregate_score_table($threesixty, $skillnames, $selfscores, $selftypes, $respondentscores, $respondenttypes, $showrespondentaverage, true);
$aggregatescoretable->cellpadding = '0.5mm';
$aggregatescoretable->attributes['border'] = '0.1mm';
$count = 0;
foreach ($aggregatescoretable->head as $index => $head) {
    $count ++;
    $aggregatescoretable->head[$index] = new html_table_cell(strip_tags($head));
    if ($count == 1 || $count == count($aggregatescoretable->head)) {
        $aggregatescoretable->head[$index]->attributes['width'] = "50";
    }
}
foreach ($aggregatescoretable->data as $rowindex => $row) {
    $count = 0;
    foreach ($row->cells as $cellindex => $cell) {
        $count++;
        $aggregatescoretable->data[$rowindex]->cells[$cellindex] = new html_table_cell($cell);
        if ($count == 1 || $count == count($aggregatescoretable->data[$rowindex]->cells)) {
            $aggregatescoretable->data[$rowindex]->cells[$cellindex]->attributes['width'] = "50";
        }
    }
}

$skillscoretable = print_skill_score_table($threesixty, $orderedskillnames, $selfscores, $selftypes, $respondentscores, $respondenttypes, $showrespondentaverage, true);
$skillscoretable->cellpadding = '0.5mm';
$skillscoretable->attributes['border'] = '0.1mm';
$count = 0;
foreach ($skillscoretable->head as $index => $head) {
    $count ++;
    $skillscoretable->head[$index] = new html_table_cell(strip_tags($head));
    if ($count == 1) {
        $skillscoretable->head[$index]->attributes['width'] = "15";
    } elseif ($count == count($skillscoretable->head)) {
        $skillscoretable->head[$index]->attributes['width'] = "50";
    }
}
foreach ($skillscoretable->data as $rowindex => $row) {
    $count = 0;
    foreach ($row->cells as $cellindex => $cell) {
        $count++;
        if ($count == 1) {
            $skillscoretable->data[$rowindex]->cells[$cellindex]->attributes['align'] = 'center';
            $skillscoretable->data[$rowindex]->cells[$cellindex]->attributes['width'] = "15";
        } elseif ($count == count($skillscoretable->data[$rowindex]->cells)) {
            $skillscoretable->data[$rowindex]->cells[$cellindex]->attributes['width'] = "50";
        }
    }
}

$spiderweburls = get_spiderweb_urls($cm->id, $threesixty, array_reverse($skillnames, true), $selfscores, $selftypes, $respondentscores, $respondenttypes, $showrespondentaverage, false);
foreach ($spiderweburls as $spiderweburl) {
    $filepath = "{$CFG->dataroot}/threesixty/spiderdata/{$spiderweburl->url->get_param('data')}";
    $data = unserialize(base64_decode(file_get_contents($filepath)));
    unlink($filepath);
    ob_start();
    print_spiderweb($data, $context);
    $imagestring = ob_get_clean();
    $pdf->AddPage();
    $pdf->SetFont('times', '', 20);
    $pdf->setY(30);
    $title = '<p align="center">Your Influencing Spectrum<br />'.$data->title.'</p>';
    $pdf->writeHTML($title);
    $pdf->setY($pdf->GetY() + 10);
    $pdf->SetFont('helvetica', '', 8);
    $pdf->Image('@'.$imagestring, '', '', 0, 0, 'PNG', '', '', false, 150, 'C', false, false, 0, false, false, true, false, array());
}

$pdf->AddPage();
$pdf->SetFont('times', '', 20);
$pdf->setY(30);
$title = '<p align="center">Your Influencing Spectrum<br />Summary</p>';
$pdf->writeHTML($title);
$pdf->setY($pdf->GetY() + 10);
$pdf->SetFont('helvetica', '', 8);
$pdf->writeHTML(html_writer::table($aggregatescoretable));

$pdf->AddPage();
$pdf->SetFont('times', '', 20);
$pdf->setY(30);
$title = '<p align="center">Your Influencing Spectrum</p>';
$pdf->writeHTML($title);
$pdf->setY($pdf->GetY() + 10);
$pdf->SetFont('helvetica', '', 8);
$pdf->writeHTML(html_writer::table($skillscoretable));

$pdf->Output();