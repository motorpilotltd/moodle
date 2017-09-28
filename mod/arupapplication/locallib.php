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

/**
 * Internal library of functions for module arupapplication
 *
 * All the arupapplication specific functions, needed to implement the module
 * logic, should go here. Never include this file from your lib.php!
 *
 * @package    mod_arupapplication
 * @copyright  2011 Your Name
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

define('ARUPAPPLICATION_MAX_STATEMENTQUESTIONS', 10);
define('ARUPAPPLICATION_MAX_DECLARATIONS', 5);
define('ARUPAPPLICATION_MAX_FILESIZE', 3145728);
define('ARUPAPPLICATION_MAX_FILES', 1);
define('ARUPAPPLICATION_MAX_FILETYPE', '.pdf');

define('ARUPAPPLICATION_CV_FILEAREA', 'submission');
define('ARUPAPPLICATION_APP_FILEAREA', 'appform');
define('ARUPAPPLICATION_APP_CV_MERGE_FILEAREA', 'appformcv');


function arupapplication_init_arupapplication_session() {
    //initialize the arupapplication-Session - not nice at all!!
    global $SESSION;
    if (!empty($SESSION)) {
        if (!isset($SESSION->arupapplication) OR !is_object($SESSION->arupapplication)) {
            $SESSION->arupapplication = new stdClass();
        }
    }
}

function arupapplication_userregion($userid = 0) {
    global $USER, $DB;

    if ($userid == 0) {
        $userid = $USER->id;
    }

    $sql = "SELECT name
        FROM {user} u
        INNER JOIN {local_regions_use} ru ON u.id = ru.userid
        INNER JOIN {local_regions_reg} rr ON ru.geotapsregionid = rr.id
        WHERE u.id = ?";
    $userregion = $DB->get_record_sql($sql, array($userid));

    if ($userregion) {
        return $userregion->name;
    } else {
        return false;
    }
}

/**
     * Delete this instance from the database
     *
     * @return bool false if an error occurs
     */
function delete_instance($contextid = 0, $applicationid = 0) {
    global $CFG, $DB;
    $result = true;

    // delete files associated with this assignment
    $fs = get_file_storage();
    if (! $fs->delete_area_files($contextid, 'mod_arupapplication', 'submission') ) {
        $result = false;
    }

    // delete_records will throw an exception if it fails - so no need for error checking here

    $DB->delete_records('arupstatementquestions', array('applicationid'=>$applicationid));
    $DB->delete_records('arupdeclarations', array('applicationid'=>$applicationid));
    $DB->delete_records('arupapplication_tracking', array('applicationid'=>$applicationid));
    $DB->delete_records('arupstatementanswers', array('applicationid'=>$applicationid));
    $DB->delete_records('arupdeclarationanswers', array('applicationid'=>$applicationid));
    $DB->delete_records('arupsubmissions', array('applicationid'=>$applicationid));

    // delete the instance
    $DB->delete_records('arupapplication', array('id'=>$applicationid));

    return $result;
}

/**
 * this decreases the position of the given record
 *
 * @global object
 * @param object $item
 * @return bool
 */
function arupapplication_moveup_item($record, $thistable) {
    global $DB;

    if ($record->sortorder == 1) {
        return true;
    }

    $params = array('applicationid'=>$record->applicationid);
    if (!$records = $DB->get_records($thistable, $params, 'sortorder')) {
        return false;
    }
    $recordbefore = null;
    foreach ($records as $i) {
        if ($i->id == $record->id) {
            if (is_null($recordbefore)) {
                return true;
            }
            $recordbefore->sortorder = $record->sortorder;
            $record->sortorder--;
            arupapplication_update_record($recordbefore, $thistable);
            arupapplication_update_record($record, $thistable);
            arupapplication_renumber_records($record->applicationid, $thistable);
            return true;
        }
        $recordbefore = $i;
    }
    return false;
}

/**
 * this increased the position of the given record
 *
 * @global object
 * @param object $item
 * @return bool
 */
function arupapplication_movedown_item($record, $thistable) {
    global $DB;

    $params = array('applicationid'=>$record->applicationid);
    if (!$records = $DB->get_records($thistable, $params, 'sortorder')) {
        return false;
    }

    $movedownrecord = null;
    foreach ($records as $i) {
        if (!is_null($movedownrecord) AND $movedownrecord->id == $record->id) {
            $movedownrecord->sortorder = $i->sortorder;
            $i->sortorder--;
            arupapplication_update_record($movedownrecord, $thistable);
            arupapplication_update_record($i, $thistable);
            arupapplication_renumber_records($record->applicationid, $thistable);
            return true;
        }
        $movedownrecord = $i;
    }
    return false;
}

/**
 * here the position of the given item will be set to the value in $pos
 *
 * @global object
 * @param object $moveitem
 * @param int $pos
 * @return boolean
 */
function arupapplication_move_record($moverecord, $pos, $thistable) {
    global $DB;

    $params = array('applicationid'=>$moverecord->applicationid);
    if (!$allrecords = $DB->get_records($thistable, $params, 'sortorder')) {
        return false;
    }
    if (is_array($allrecords)) {
        $index = 1;
        foreach ($allrecords as $record) {
            if ($index == $pos) {
                $index++;
            }
            if ($record->id == $moverecord->id) {
                $moverecord->sortorder = $pos;
                arupapplication_update_record($moverecord,$thistable);
                continue;
            }
            $record->sortorder = $index;
            arupapplication_update_record($record, $thistable);
            $index++;
        }
        return true;
    }
    return false;
}

/**
 * Checks if techinical referece / sponsor form has been filledin
 *
 *
 * @param $applicationid int
 * @param $userid int
 * @param $type string
 * @return boolean true if submissions are in progress for this arupapplication instance
 */
function arupapplication_referencesponsorfeedback($applicationid = 0, $userid = 0, $type = '') {
    global $DB;

    $sql = "SELECT id
        FROM {arupsubmissions}
        WHERE applicationid = " . $applicationid .
        " AND userid = " . $userid;

    switch ($type) {
        case 'referee':
            $sql .= " AND referencesubmitted = 1";
            break;
        case 'sponsor':
            $sql .= " AND sponsorsubmitted = 1";
            break;
        default:
           $sql .= " AND 2 = 1";
            break;
    }

    $record = $DB->get_record_sql($sql);

    if ($record) {
        return true;
    } else {
        return false;
    }
}

/**
 * Get all the applications
 *
 * @param stdClass $cm the course module object
 * @param stdClass $context the arupapplication's context
 * @return string - all submissions details
 */
function arupapplication_listsubmissions($cm, $context) {
    global $CFG, $DB;

    if ($context->contextlevel != CONTEXT_MODULE) {
        return false;
    }
    $usernamefields = get_all_user_name_fields(true, 'u');
    $sql = "SELECT s.*, u.id as userid, u.idnumber, {$usernamefields}, t.completed, a.sponsorstatementreq
        FROM {arupsubmissions} s
        INNER JOIN {arupapplication} a ON s.applicationid = a.id
        INNER JOIN {user} u ON s.userid = u.id
        INNER JOIN {arupapplication_tracking} t ON u.id = t.userid AND a.id = t.applicationid
        WHERE a.id =?
        ORDER BY s.timecreated DESC";
    $records = $DB->get_records_sql($sql, array($cm->instance));

    if ($records) {
        $table = new html_table();
        $table->cellpadding = 4;
        $table->attributes['class'] = 'generaltable boxalignleft';
        $table->align = array(
            'left',
            'left',
            'left',
            'center',
            'center',
            'center',
        );
        $table->head = array(
            get_string('heading:name', 'arupapplication'),
            get_string('heading:staffid', 'arupapplication'),
            get_string('heading:status', 'arupapplication'),
            get_string('heading:technicalreference', 'arupapplication'),
            get_string('heading:sponsorstatement', 'arupapplication'),
            get_string('actions', 'arupapplication')
        );

        foreach($records as $record) {
            $userfullname = fullname($record);

            $viewlink = '<a href="' . $CFG->wwwroot . '/mod/arupapplication/edit.php?id=' . $cm->id . '&submissionid=' . $record->id . '">' . get_string('action:view', 'arupapplication') . '</a>';
            $editlink = '<a href="' . $CFG->wwwroot . '/mod/arupapplication/edit.php?id=' . $cm->id . '&edit=1&submissionid=' . $record->id . '">' . get_string('action:edit', 'arupapplication') . '</a>';
            $printlink = '<a href="' . $CFG->wwwroot . '/mod/arupapplication/print.php?id=' . $cm->id . '&submissionid=' . $record->id . '">' . get_string('action:print', 'arupapplication') . '</a>';
            if (has_capability('mod/arupapplication:deleteapplication', $context)) {
                $deletelink = '<a href="' . $CFG->wwwroot . '/mod/arupapplication/delete.php?id=' . $cm->id . '&submissionid=' . $record->id . '">' . get_string('action:delete', 'arupapplication') . '</a>';
            }

            switch ($record->completed) {
                case '0':
                    $status = get_string('progress:started', 'arupapplication');
                    break;
                case 1:
                case 2:
                case 3:
                case 4:
                case 5:
                    $status = get_string('progress:inprogress', 'arupapplication');
                    break;
                case 6:
                    $status = get_string('progress:applicationsubmitted', 'arupapplication');
                    break;
                case 7:
                    $status = get_string('progress:complete', 'arupapplication');
                    break;
                default:
                    $status = get_string('progress:notstarted', 'arupapplication');
                    break;
            }
            $table->data[] = array(
                $userfullname,
                $record->idnumber,
                $status,
                arupapplication_yesno($record->referencesubmitted),
                arupapplication_yesno($record->sponsorsubmitted),
                $viewlink . ' | ' . $editlink . ' | ' .$printlink . (isset($deletelink) ? ' | ' .$deletelink : '')
            );
        }
        return html_writer::table($table);

    } else {
        return false;
    }
}

function arupapplication_yesno($yes) {
    global $OUTPUT;
    if ($yes) {
        return html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/valid')));
    } else {
        return html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/invalid')));
    }
}

/**
 * Get the submission details
 *
 * @param int $submissionid - submission id
 * @return object - all submissions details
 */
function arupapplication_submissionsdetails($submissionid = 0) {
    global $DB;
    $usernamefields = get_all_user_name_fields(true, 'u');
    $sql = "SELECT s.*, u.id as userid, u.idnumber, {$usernamefields}, t.completed, a.sponsordeclarationlabel
        FROM {arupsubmissions} s
        INNER JOIN {arupapplication} a ON s.applicationid = a.id
        INNER JOIN {user} u ON s.userid = u.id
        INNER JOIN {arupapplication_tracking} t ON u.id = t.userid AND a.id = t.applicationid
        WHERE s.id =?";

    return $DB->get_record_sql($sql, array($submissionid));
}

/**
 * Get the submission statement answers
 *
 * @param int $arupapplicationid - application id
 * @return object - all submissions statements answers
 */
function arupapplication_submissionsstatementans($arupapplicationid = 0, $userid = 0) {
    global $DB, $USER;

    if ($userid == 0) {
        $userid = $USER->id;
    }

    //Statement question answers
    $sql = "SELECT sqs.id as questionid, sqs.question, ap.id as applicationid, san.answer, san.id
FROM   {arupapplication} ap
INNER JOIN {arupstatementquestions} sqs ON ap.id = sqs.applicationid
LEFT JOIN {arupstatementanswers} san ON sqs.id = san.questionid AND san.userid = ?
WHERE ap.id = ?";
    return $questionsanswers = $DB->get_records_sql($sql, array($userid, $arupapplicationid));
}

/**
 * Get the submission declaration answers
 *
 * @param int $arupapplicationid - application id
 * @return object - all submissions declaration answers
 */
function arupapplication_declarationans($arupapplicationid = 0, $userid = 0) {
    global $DB, $USER;

    if ($userid == 0) {
        $userid = $USER->id;
    }
    //Declaration statements
    $sql = "SELECT dqs.id as declarationid, dqs.declaration, ap.id as applicationid, dac.answer, dac.id
FROM {arupapplication} ap
INNER JOIN {arupdeclarations} dqs ON ap.id = dqs.applicationid
LEFT JOIN {arupdeclarationanswers} dac ON dqs.id = dac.declarationid AND dac.userid = ?
WHERE ap.id = ?";
    return $declarationanswers = $DB->get_records_sql($sql, array($userid, $arupapplicationid));
}

/**
 * Validate the technical referee / sponsor's email address
 *
 * @param string $emailaddress - Sponsor's email address
 * @return mixed a fieldset object containing the first matching record, false or exception if error not found
 */

function arupapplication_validate_emailaddress($emailaddress = '') {

    if (!filter_var($emailaddress, FILTER_VALIDATE_EMAIL)) {
        return false;
    }
    if (!is_enabled_auth('saml')) {
        return false;
    }
    $samlauth = get_auth_plugin('saml');
    $users = $samlauth->ldap_get_userlist('(mail='.$emailaddress.')');
    return !empty($users);
}

/**
 * Sends text to output given the following params.
 *
 * @param stdClass $pdf
 * @param int $x horizontal position
 * @param int $y vertical position
 * @param char $align L=left, C=center, R=right
 * @param string $font any available font in font directory
 * @param char $style ''=normal, B=bold, I=italic, U=underline
 * @param int $size font size in points
 * @param string $text the text to print
 */
function arupapplication_print_text($pdf, $x, $y, $align, $font='freeserif', $style, $size=10, $text) {
    $pdf->setFont($font, $style, $size);
    $pdf->SetXY($x, $y);
    $pdf->writeHTMLCell(0, 0, '', '', $text, 0, 0, 0, true, $align);
}

/**
 * Retrieve file path from hash
 *
 * @param array $contenthash
 * @return string the path
 */
function arupapplication_path_from_hash($contenthash) {
    $l1 = $contenthash[0].$contenthash[1];
    $l2 = $contenthash[2].$contenthash[3];
    return "$l1/$l2";
}

/**
 * Retrieve file path from hash
 *
 * @param stdClass $pdf
 * @param int $submissionid
 * @param int $contextid
 * @return string the path
 */
function arupapplication_generatepdf($pdf, $submissionid = 0, $contextid = 0) {
    global $CFG, $DB;

    $submissiondetails = arupapplication_submissionsdetails($submissionid);

    if ($submissiondetails) {
        $applicationdetails = $DB->get_record('arupapplication', array('id'=>$submissiondetails->applicationid));
        $userdetails = $DB->get_record('user', array('id'=>$submissiondetails->userid));
        $questionsanswers = arupapplication_submissionsstatementans($submissiondetails->applicationid, $submissiondetails->userid);
        $declarationanswers = arupapplication_declarationans($submissiondetails->applicationid, $submissiondetails->userid);

        $pdf->SetTitle(get_string('modulename', 'arupapplication'));
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetAutoPageBreak(true, 10);
        $pdf->AddPage();
        $pdf->SetMargins(10, 10, 10);
        $pdf->SetHeaderMargin(10);
        $pdf->SetFooterMargin(10);
        $pdf->SetFontSize(10);
        $pdf->SetTextColor(0, 0, 0);
        // set color for background
        $pdf->SetFillColor(255, 255, 127);

        $styling = "<style> h2 { font-family: arial; font-size: 16pt; text-decoration: underline; }
  div { font-family: arial; font-size: 10pt; }
  span { font-family: arial; font-size: 10pt; font-weight: bold; } </style>";

        $content = html_writer::tag('h2', get_string('legend:applicantdetails:personal', 'arupapplication'));

        $content .= html_writer::tag('span', get_string('title', 'arupapplication'));
        $content .= html_writer::tag('div', $submissiondetails->title . '<br />');

        $content .= html_writer::tag('span', get_string('firstname', 'arupapplication'));
        $content .= html_writer::tag('div', $userdetails->firstname . '<br />');

        $content .= html_writer::tag('span', get_string('surname', 'arupapplication'));
        $content .= html_writer::tag('div', $userdetails->lastname . '<br />');

        $content .= html_writer::tag('span', get_string('passportname', 'arupapplication'));
        $content .= html_writer::tag('div', $submissiondetails->passportname . '<br />');

        $content .= html_writer::tag('span', get_string('knownas', 'arupapplication'));
        $content .= html_writer::tag('div', $submissiondetails->knownas . '<br />');

        $content .= html_writer::tag('span', get_string('dateofbirth', 'arupapplication'));
        $content .= html_writer::tag('div', gmdate("d/m/Y", $submissiondetails->dateofbirth) . '<br />');

        $content .= html_writer::tag('span', get_string('countryofresidence', 'arupapplication'));
        $content .= html_writer::tag('div', $submissiondetails->countryofresidence . '<br />');

        if ($submissiondetails->requirevisa == 1) {
            $requirevisa = 'Yes';
        } else {
            $requirevisa = 'No';
        }

        $content .= html_writer::tag('span', get_string('requirevisa', 'arupapplication'));
        $content .= html_writer::tag('div', $requirevisa . '<br />');

        $top = $pdf->GetY();
        $left = $pdf->GetX();

        $pdf->writeHTMLCell(85, 0, $left, $top, $styling.$content, 0, 1, false, true, 'L', true);
        $bottom = $pdf->GetY();

        $content = html_writer::tag('h2', get_string('legend:applicantdetails:arup', 'arupapplication'));

        $content .= html_writer::tag('span', get_string('staffid', 'arupapplication'));
        $content .= html_writer::tag('div', $userdetails->idnumber . '<br />');

        $content .= html_writer::tag('span', get_string('grade', 'arupapplication'));
        $content .= html_writer::tag('div', $submissiondetails->grade . '<br />');

        $content .= html_writer::tag('span', get_string('jobtitle', 'arupapplication'));
        $content .= html_writer::tag('div', $submissiondetails->jobtitle . '<br />');

        $content .= html_writer::tag('span', get_string('discipline', 'arupapplication'));
        $content .= html_writer::tag('div', $submissiondetails->discipline . '<br />');

        $content .= html_writer::tag('span', get_string('joiningdate', 'arupapplication'));
        $content .= html_writer::tag('div', gmdate("m/Y", $submissiondetails->joiningdate) . '<br />');

        $content .= html_writer::tag('span', get_string('group', 'arupapplication'));
        $content .= html_writer::tag('div', $submissiondetails->arupgroup . '<br />');

        $content .= html_writer::tag('span', get_string('businessarea', 'arupapplication'));
        $content .= html_writer::tag('div', $submissiondetails->businessarea . '<br />');

        $content .= html_writer::tag('span', get_string('region', 'arupapplication'));
        $content .= html_writer::tag('div', arupapplication_userregion($userdetails->id) . '<br />');

        $content .= html_writer::tag('span', get_string('officelocation', 'arupapplication'));
        $content .= html_writer::tag('div', $submissiondetails->officelocation . '<br />');

        if (!empty($submissiondetails->otherofficelocation)) {
            $content .= html_writer::tag('span', get_string('otherofficelocation', 'arupapplication'));
            $content .= html_writer::tag('div', $submissiondetails->otherofficelocation . '<br />');
        }

        $pdf->writeHTMLCell(85, 0, 115, $top, $styling.$content, 0, 1, false, true, 'L', true);
        $bottom = max(array($bottom, $pdf->GetY()));

        $content = html_writer::tag('h2', get_string('heading:qualifications', 'arupapplication'));

        $content .= html_writer::tag('span', get_string('degree', 'arupapplication'));
        $content .= html_writer::tag('div', $submissiondetails->degree . '<br />');

        $content .= html_writer::tag('h2', get_string('heading:declaration', 'arupapplication'));

        foreach($declarationanswers as $declarationanswer) {
            if ($declarationanswer->answer == 1) {
                $declarationstatus = 'Yes';
            } else {
                $declarationstatus = 'No';
            }
            $content .= html_writer::tag('span', $declarationanswer->declaration);
            $content .= html_writer::tag('div', $declarationstatus . '<br />');
        }

        $pdf->writeHTMLCell(0, 0, $left, $bottom, $styling.$content, 0, 1, false, true, 'L', true);

        $content = html_writer::tag('h2', get_string('heading:statement', 'arupapplication'));

        foreach($questionsanswers as $questionsanswer) {
            $content .= html_writer::tag('span', $questionsanswer->question);
            $content .= html_writer::tag('div', text_to_html($questionsanswer->answer) . '<br />');
        }

        $pdf->addPage();
        $pdf->writeHTMLCell(0, 0, $pdf->GetX(), $pdf->GetY(), $styling.$content, 0, 1, false, true, 'L', true);

        $content = html_writer::tag('h2', get_string('heading:technicalreference', 'arupapplication'));

        $content .= html_writer::tag('span', get_string('refereeemail', 'arupapplication'));
        $content .= html_writer::tag('div', $submissiondetails->referee_email . '<br />');

        $content .= html_writer::tag('span', get_string('referencephone', 'arupapplication'));
        $content .= html_writer::tag('div', $submissiondetails->reference_phone . '<br />');

        $content .= html_writer::tag('span', get_string('referenceposition', 'arupapplication'));
        $content .= html_writer::tag('div', $submissiondetails->referenceposition . '<br />');

        $content .= html_writer::tag('span', get_string('referenceknown', 'arupapplication'));
        $content .= html_writer::tag('div', $submissiondetails->referenceknown . '<br />');

        $content .= html_writer::tag('span', get_string('referenceperformance', 'arupapplication'));
        $content .= html_writer::tag('div', $submissiondetails->referenceperformance . '<br />');

        $content .= html_writer::tag('span', get_string('referencetalent', 'arupapplication'));
        $content .= html_writer::tag('div', $submissiondetails->referencetalent . '<br />');

        $content .= html_writer::tag('span', get_string('referencemotivation', 'arupapplication'));
        $content .= html_writer::tag('div', $submissiondetails->referencemotivation . '<br />');

        $content .= html_writer::tag('span', get_string('referenceknowledge', 'arupapplication'));
        $content .= html_writer::tag('div', $submissiondetails->referenceknowledge . '<br />');

        $content .= html_writer::tag('span', get_string('referencecomments', 'arupapplication'));
        $content .= html_writer::tag('div', $submissiondetails->referencecomments . '<br />');

        $pdf->addPage();
        $pdf->writeHTMLCell(0, 0, $pdf->GetX(), $pdf->GetY(), $styling.$content, 0, 1, false, true, 'L', true);

        if($applicationdetails->sponsorstatementreq) {

            $content = html_writer::tag('h2', get_string('heading:sponsorstatement', 'arupapplication'));

            $content .= html_writer::tag('span', get_string('sponsoremail', 'arupapplication'));
            $content .= html_writer::tag('div', $submissiondetails->sponsor_email . '<br />');

            $content .= html_writer::tag('span', get_string('sponsorstatement', 'arupapplication'));
            $content .= html_writer::tag('div', $submissiondetails->sponsorstatement . '<br />');

            $content .= html_writer::tag('span', $submissiondetails->sponsordeclarationlabel);

            if ($submissiondetails->sponsordeclaration == 1) {
                $sponsordeclarationstatus = 'Yes';
            } else {
                $sponsordeclarationstatus = 'No';
            }
            $content .= html_writer::tag('div', $sponsordeclarationstatus . '<br />');

            $pdf->addPage();
            $pdf->writeHTMLCell(0, 0, $pdf->GetX(), $pdf->GetY(), $styling.$content, 0, 1, false, true, 'L', true);
        }
    }

}

/**
 * This function returns success or failure of file save
 *
 * @param string $pdf is the string contents of the pdf
 * @param int $submissionid the submission record id
 * @param string $filename pdf filename
 * @param int $contextid context id
 * @return bool return true if successful, false otherwise
 */
function arupapplication_save_pdf($pdf, $submissionid, $userid, $filename, $contextid, $filearea) {
    global $DB;

    if (empty($submissionid)) {
        return false;
    }

    if (empty($pdf)) {
        return false;
    }

    $fs = get_file_storage();

    // Prepare file record object
    $component = 'mod_arupapplication';
    $filepath = '/';
    $fileinfo = array(
        'contextid' => $contextid,   // ID of context
        'component' => $component,   // usually = table name
        'filearea'  => $filearea,     // usually = table name
        'itemid'    => $submissionid,  // usually = ID of row in table
        'filepath'  => $filepath,     // any path beginning and ending in /
        'filename'  => $filename,    // any filename
        'mimetype'  => 'application/pdf',    // any filename
        'userid'    => $userid);

    // If the file exists, delete it and recreate it. This is to ensure that the
    // latest certificate is saved on the server. For example, the student's grade
    // may have been updated. This is a quick dirty hack.
    if ($fs->file_exists($contextid, $component, $filearea, $submissionid, $filepath, $filename)) {
        $fs->delete_area_files($contextid, $component, $filearea, $submissionid);
    }

    $fs->create_file_from_string($fileinfo, $pdf);

    return true;
}

/**
 * This function sends email
 *
 * @param object $touser - User object
 * @param object $fromuser - User object
 * @param string $subject - Email subject
 * @param string $message - Email body
 * @param object $user - User object for text replacements
 * @param string $course - Course name
 * @param string $url - Course activity URL
*/

function arupapplication_sendemail($touser, $fromuser, $subject, $message, $user, $course, $url) {
    global $CFG;

    $emailbody_plain = $message;
    $emailbody_html = '';

    $emailbody_plain = str_replace('[[course]]', $course, $message);
    $emailbody_plain = str_replace('[[user]]', fullname($user), $emailbody_plain);
    $emailbody_plain = str_replace('[[user:firstname]]', $user->firstname, $emailbody_plain);
    $emailbody_plain = str_replace('[[linkurl]]', $url, $emailbody_plain);

    $emailbody_html = str_replace('[[link]]', get_string('clickhere', 'arupapplication', $url), $emailbody_plain);
    $emailbody_html = text_to_html($emailbody_html);

    $emailbody_plain = str_replace('[[link]]', $url, $emailbody_plain);
    $emailbody_plain = format_string($emailbody_plain);
    $emailbody_plain = str_replace('&amp;appid=', '&appid=', $emailbody_plain);

    email_to_user($touser, $fromuser, $subject, $emailbody_plain, $emailbody_html);
}


/**
 * function to merge
 *
 * @param object - User details
 * @param string $fromuser - Email from user
 * @param string $subject - Email subject
 * @param string $message - Email body
 * @param string $userfullname - User's fullname
 * @param string $course - Course name
 * @param string $url - Course activity URL
*/

function arupapplication_mergepdfs ($contextid, $submissionid, $userid, $forcedownload = false) {
    global $CFG, $DB;
    require_once($CFG->dirroot . '/mod/arupapplication/lib/fpdi/fpdi.php');

    $pdfDocs = array();

    $fs = get_file_storage();

    //Read app form
    $appformfiles = $fs->get_area_files($contextid, 'mod_arupapplication', ARUPAPPLICATION_APP_FILEAREA, $submissionid);

    if ($appformfiles) {
        $file = array_pop($appformfiles);
        $filepathname = $file->get_contenthash();
        $filename = $file->get_filename();
        $sysfilename = $CFG->dataroot .'/filedir/'.arupapplication_path_from_hash($filepathname).'/'.$filepathname;
        $filetoembed = $CFG->dataroot .'/filedir/'.arupapplication_path_from_hash($filepathname).'/'.$filename;
        if (copy($sysfilename, $filetoembed)) {
            $pdfDocs[] = $filetoembed;
        } else {
            $appformfilefound = 0;
        }
    }

    //Read CV
    $cvfiles = $fs->get_area_files($contextid, 'mod_arupapplication', ARUPAPPLICATION_CV_FILEAREA, $submissionid);

    if ($cvfiles) {
        $file = array_pop($cvfiles);
        $filepathname = $file->get_contenthash();
        $filename = $file->get_filename();
        $sysfilename = $CFG->dataroot .'/filedir/'.arupapplication_path_from_hash($filepathname).'/'.$filepathname;
        $filetoembed = $CFG->dataroot .'/filedir/'.arupapplication_path_from_hash($filepathname).'/'.$filename;
        if (copy($sysfilename, $filetoembed)) {
            $pdfDocs[] = $filetoembed;
        } else {
            $cvfilefound = 0;
        }
    }

    $pdfNew = new FPDI();
    $pdfNew->setPrintHeader(false);
    $pdfNew->setPrintFooter(false);
    foreach($pdfDocs as $file) {
        $pages = $pdfNew->setSourceFile($file);
        for ($i = 1; $i <= $pages; $i++) {
            $pdfNew->AddPage();
            $pdfNew->useTemplate($pdfNew->importPage($i));
        }
    }

    $userdetails = $DB->get_record('user', array('id'=>$userid));
    $mergePdf = str_replace(' ', '_', clean_filename(fullname($userdetails). '_' . get_string('modulename', 'arupapplication') . '.pdf'));

    arupapplication_save_pdf($pdfNew->Output('', 'S'), $submissionid, $userid, $mergePdf, $contextid, ARUPAPPLICATION_APP_CV_MERGE_FILEAREA);

    if ($forcedownload) {
        $fullpath = '/'.$contextid .'/mod_arupapplication/' . ARUPAPPLICATION_APP_CV_MERGE_FILEAREA . '/' . $submissionid . '/' . $mergePdf;
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            send_file_not_found();
        }
        send_stored_file($file, 0, 0, true, array());
    }
}

function arupapplication_downloadzip($contextid, $filename, $itemids) {
    //Read app form
    $fs = get_file_storage();
    $appformfiles = $fs->get_area_files($contextid, 'mod_arupapplication', ARUPAPPLICATION_APP_CV_MERGE_FILEAREA, false);

    $fileforzipping = array();

    if ($appformfiles) {
        foreach($appformfiles as $appformfile) {
            if ($appformfile->get_filesize() != 0 && in_array($appformfile->get_itemid(), $itemids)) {
                $fileforzipping[$appformfile->get_filename()] = $appformfile;
            }
        }
        //$filename = str_replace(' ', '_', clean_filename(get_string('pluginname', 'arupapplication').'.zip'));
        if ($zipfile = arupapplication_pack_files($fileforzipping)) {
            send_temp_file($zipfile, $filename); //send file and delete after sending.
        }
    }
}
/**
* Generate zip file from array of given files
*
* @param array $filesforzipping - array of files to pass into archive_to_pathname - this array is indexed by the final file name and each element in the array is an instance of a stored_file object
* @return path of temp file - note this returned file does not have a .zip extension - it is a temp file.
*/
function arupapplication_pack_files($filesforzipping) {
    global $CFG;
    //create path for new zip file.

    $tempzip = tempnam($CFG->tempdir.'/', 'arupapplication_');
    //zip files
    $zipper = new zip_packer();
    if ($zipper->archive_to_pathname($filesforzipping, $tempzip)) {
        return $tempzip;
    }
    return false;
}

class arupapplication_user extends \core_user {
    public static function get_dummy_arupapplication_user($email = '', $firstname = '', $lastname = '') {
        $user = self::get_dummy_user_record();
        $user->maildisplay = true;
        $user->mailformat = 1;
        $user->email = $email;
        $user->firstname = $firstname;
        $user->lastname = $lastname;
        $user->username = 'arupapplicationuser';
        $user->timezone = date_default_timezone_get();
        return $user;
    }
}