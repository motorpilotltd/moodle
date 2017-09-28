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
 * The main arupapplication configuration form
 *
 * It uses the standard core Moodle formslib. For more info about them, please
 * visit: http://docs.moodle.org/en/Development:lib/formslib.php
 *
 * @package    mod_arupapplication
 * @copyright  2014 Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

//It must be included from a Moodle page
if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');
}

require_once("$CFG->libdir/formslib.php");

class sponsorstatement_form extends moodleform {
    public function definition() {
	global $CFG, $USER, $DB;

        $mform =& $this->_form;
        $contextid = $this->_customdata['contextid'];
        $cmid = $this->_customdata['cmid'];
        $applicationid = $this->_customdata['applicationid'];
        $submissionid = $this->_customdata['submissionid'];
        $sponsorrequired = $this->_customdata['sponsorrequired'];
        $sponsormessage_hint = $this->_customdata['sponsormessage_hint'];
        $sponsor_audit = $this->_customdata['sponsor_audit'];
        $submission_hint = $this->_customdata['submission_hint'];
        $footermessage = $this->_customdata['footermessage'];
        $submissionstate = $this->_customdata['submissionstate'];
        $sponsor_audit = $this->_customdata['sponsor_audit'];

        $submissionvalidated = $this->validatethisdata($contextid, $cmid, $applicationid, $submissionid);

        $mform->addElement('html', $submissionvalidated);
        $mform->addElement('hidden', 'errorinform', ($submissionvalidated ? 1 : 0));
        $mform->setType('errorinform', PARAM_INT);

        if ($sponsorrequired) {
            $mform->addElement('html', '<h2>' . get_string('heading:sponsorstatement', 'arupapplication') . '</h2>');
            $mform->addElement('html', html_writer::tag('div', $sponsormessage_hint, array('class' => 'hint')));

            $mform->addElement('text', 'sponsor_email', get_string('sponsoremail', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"');
            $mform->setType('sponsor_email', PARAM_EMAIL);
            $mform->addHelpButton('sponsor_email', 'sponsoremail', 'arupapplication');

            $mform->addElement('textarea', 'sponsor_message', get_string('sponsormessage', 'arupapplication'), 'wrap="virtual" rows="5" cols="65"');
            $mform->setType('sponsor_message', PARAM_NOTAGS);
            $mform->addHelpButton('sponsor_message', 'sponsormessage', 'arupapplication');

            $mform->addElement('html', html_writer::start_tag('div', array('class' => 'footermessage')));
            $mform->addElement('static', 'footermessage');
            $mform->setDefault('footermessage', $footermessage);
            $mform->addElement('html', html_writer::end_tag('div'));

            if (!empty($submissionvalidated)) {
                $mform->addElement('hidden', 'validateform', 0);
                $mform->setType('validateform', PARAM_INT);
            }
        } else {
            $mform->addElement('html', '<h2>' . get_string('heading:applicantdetails', 'arupapplication') . '</h2>');
            if (!empty($submissionvalidated)) {
                $mform->addElement('hidden', 'validateform', 1);
                $mform->setType('validateform', PARAM_INT);
            }
        }

        $mform->addElement('html', html_writer::tag('div', $submission_hint, array('class' => 'hint')));

        $buttonarray=array();

        if ($submissionstate == 6) {
            $buttonarray[] = &$mform->createElement('submit', 'submitapplication', get_string('button:resend', 'arupapplication'), array('class' => 'btn-primary'));
            $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('button:exit', 'arupapplication'), array('class' => 'btn-default'));
        } else {
            $buttonarray[] = &$mform->createElement('submit', 'gopreviouspage', get_string('button:saveback', 'arupapplication'), array('class' => 'btn-default'));
            $buttonarray[] = &$mform->createElement('submit', 'submitapplication', get_string('button:submitapplication', 'arupapplication'), array('class' => 'btn-primary'));
            $buttonarray[] = &$mform->createElement('submit', 'savevalues', get_string('button:saveexit', 'arupapplication'), array('class' => 'btn-default'));
            $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('button:exitnosave', 'arupapplication'), array('class' => 'btn-danger'));
        }

        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');

        if (!empty($sponsor_audit)) {

            $mform->addElement('header', 'general', get_string('heading:previousemails', 'arupapplication'));

            $records = explode('$$$', $sponsor_audit);
            $table = new html_table();
            $table->cellpadding = 4;
            $table->attributes['class'] = 'generaltable boxalignleft';
            $table->head = array(get_string('heading:to', 'arupapplication'), get_string('heading:from', 'arupapplication'), get_string('heading:date', 'arupapplication'), get_string('heading:message', 'arupapplication'));
            foreach($records as $record) {
                if ($record) {
                    $table->data[] = new html_table_row(explode('||', $record));
                }
            }
            $mform->addElement('html', html_writer::table($table));
        }

        $mform->addElement('hidden', 'subid', $submissionid);
        $mform->setType('subid', PARAM_INT);
        $mform->addElement('hidden', 'gopage', 5);
        $mform->setType('gopage', PARAM_INT);
        $mform->addElement('hidden', 'thispage', 'sponsor');
        $mform->setType('thispage', PARAM_ALPHA);
    }

    function data_preprocessing(&$default_values) {
        parent::data_preprocessing($default_values);
    }

    function definition_after_data() {
        global $CFG, $COURSE;
        $mform =& $this->_form;

        if ($mform->elementExists('validateform')) {
            $valuevalidateform = $mform->getElementValue('validateform');
            $valueerrorinform = $mform->getElementValue('errorinform');

            if ($valuevalidateform == 1) {
                if ($mform->elementExists('buttonar')) {
                    $mform->disabledIf('submitapplication', 'validateform', 'eq', 1);
                }
            }
            if ($valuevalidateform == 0) {
                $mform->removeElement('errorinform');
                $mform->getElement('validateform')->setValue(1);
            }
        }
    }

    function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        //Record the sponsor details in the database

        if (isset($data['submitapplication'])) {
            if (isset($data['sponsor_email'])) {
                if (! validate_email($data['sponsor_email'])) {
                    $errors['sponsor_email'] = get_string('invalidemail');
                } else if (!arupapplication_validate_emailaddress($data['sponsor_email'])) {
                    $errors['sponsor_email'] = get_string('invalidemail');
                }
            }
            if (isset($data['sponsor_message']) && empty($data['sponsor_message'])) {
                $errors['sponsor_message'] = get_string('required');
            }
            if (!$errors) {
                if (isset($data['sponsor_email'])) {
                    $tmp = $DB->set_field('arupsubmissions', 'sponsor_email', $data['sponsor_email'], array('id'=>$data['subid']));
                    $tmp = $DB->set_field('arupsubmissions', 'sponsor_message', $data['sponsor_message'], array('id'=>$data['subid']));
                }
            }
            if (isset($data['validateform']) && $data['validateform'] == 1) {
                $errors['errorinform'] = get_string('error:required', 'arupapplication');
            }
        } else if (isset($data['gopreviouspage'])) {
            if (isset($data['sponsor_email'])) {
                $tmp = $DB->set_field('arupsubmissions', 'sponsor_email', $data['sponsor_email'], array('id'=>$data['subid']));
            }
            if (isset($data['sponsor_message'])) {
                $tmp = $DB->set_field('arupsubmissions', 'sponsor_message', $data['sponsor_message'], array('id'=>$data['subid']));
            }
        }
        return $errors;
    }

    /**
     * Used to reformat the data from the editor component
     *
     * @return stdClass
     */
    function get_data() {
        $data = parent::get_data();
        return $data;
    }

    function validatethisdata ($contextid = 0, $cmid = 0, $applicationid = 0, $submissionid = 0) {
        global $CFG, $DB, $USER;

        $submissiondetails = $DB->get_record('arupsubmissions', array('applicationid'=>$applicationid, 'id'=>$submissionid));
        $content = '';
        $outputcontent = '';

        $table = new html_table();
        $table->cellpadding = 4;
        $table->attributes['class'] = 'generaltable boxalignleft error';
        $table->head = array(get_string('heading:field', 'arupapplication'), get_string('heading:link', 'arupapplication'), get_string('heading:error', 'arupapplication'));

        foreach ($submissiondetails as $key => $value) {
            switch($key) {
                case 'title':
                case 'passportname':
                case 'dateofbirth':
                case 'countryofresidence':
                case 'grade':
                case 'jobtitle':
                case 'discipline':
                case 'joiningdate':
                case 'businessarea':
                    if (empty($value)) {
                        $content .= get_string($key, 'arupapplication') . '||<a href="' . $CFG->wwwroot . '/mod/arupapplication/complete.php?id=' . $cmid . '&gopage=1' . '">' . get_string('heading:applicantdetails', 'arupapplication') . '</a>||' . get_string('error:required', 'arupapplication') . '$$$';
                    }
                    break;
                case 'arupgroup':
                    if (empty($value)) {
                        $content .= get_string('group', 'arupapplication') . '||<a href="' . $CFG->wwwroot . '/mod/arupapplication/complete.php?id=' . $cmid . '&gopage=1' . '">' . get_string('heading:applicantdetails', 'arupapplication') . '</a>||' . get_string('error:required', 'arupapplication') . '$$$';
                    }
                    break;
                case 'officelocation':
                case 'otherofficelocation':
                    if ($key == 'officelocation') {
                        if (empty($value)) {
                            $content .= get_string($key, 'arupapplication') . '||<a href="' . $CFG->wwwroot . '/mod/arupapplication/complete.php?id=' . $cmid . '&gopage=1' . '">' . get_string('heading:applicantdetails', 'arupapplication') . '</a>||' . get_string('error:required', 'arupapplication') . '$$$';
                        } else if ($value == 'Other' && empty($submissiondetails->otherofficelocation)) {
                            $content .= get_string('otherofficelocation', 'arupapplication') . '||<a href="' . $CFG->wwwroot . '/mod/arupapplication/complete.php?id=' . $cmid . '&gopage=1' . '">' . get_string('heading:applicantdetails', 'arupapplication') . '</a>||' . get_string('error:required', 'arupapplication') . '$$$';
                        }
                    }
                    break;
                case 'degree':

                    if (empty($value)) {
                        $content .= get_string($key, 'arupapplication') . '||<a href="' . $CFG->wwwroot . '/mod/arupapplication/complete.php?id=' . $cmid . '&gopage=3' . '">' . get_string('heading:qualifications', 'arupapplication') . '</a>||' . get_string('error:required', 'arupapplication') . '$$$';
                    }
                    break;
                case 'cv':
                    $fs = get_file_storage();
                    $files = $fs->get_area_files($contextid, 'mod_arupapplication', 'submission', $submissionid);
                    if (!$files) {
                        $content .= get_string($key, 'arupapplication') . '||<a href="' . $CFG->wwwroot . '/mod/arupapplication/complete.php?id=' . $cmid . '&gopage=3' . '">' . get_string('heading:qualifications', 'arupapplication') . '</a>||' . get_string('error:required', 'arupapplication') . '$$$';
                    }
                    break;
                default:
                    break;
            }
        }
        $compare = $DB->sql_compare_text('san.answer');
        $sql = "SELECT sqs.id as questionid, ap.id as applicationid, san.answer, san.id
FROM   {arupapplication} ap
INNER JOIN {arupstatementquestions} sqs ON ap.id = sqs.applicationid
LEFT JOIN {arupstatementanswers} san ON sqs.id = san.questionid AND san.userid = " . $USER->id . "
WHERE sqs.ismandatory = 1
AND (san.answer IS NULL OR {$compare} = '')
AND ap.id = " . $applicationid;
        $questionsanswers = $DB->get_records_sql($sql);

        if ($questionsanswers) {
            $content .= get_string('heading:statementquestions', 'arupapplication') . '||<a href="' . $CFG->wwwroot . '/mod/arupapplication/complete.php?id=' . $cmid . '&gopage=2' . '">' . get_string('heading:statementquestions', 'arupapplication') . '</a>||' . get_string('error:required', 'arupapplication') . '$$$';
        }
        $compare = $DB->sql_compare_text('dac.answer');
        $sql = "SELECT dqs.id as declarationid, ap.id as applicationid, dac.answer, dac.id
FROM {arupapplication} ap
INNER JOIN {arupdeclarations} dqs ON ap.id = dqs.applicationid
LEFT JOIN {arupdeclarationanswers} dac ON dqs.id = dac.declarationid AND dac.userid = " . $USER->id . "
WHERE (dac.answer IS NULL OR {$compare} = 0)
AND ap.id = " . $applicationid;
        $declarationsanswers = $DB->get_records_sql($sql);

        if ($declarationsanswers) {
            $content .= get_string('heading:declarations', 'arupapplication') . '||<a href="' . $CFG->wwwroot . '/mod/arupapplication/complete.php?id=' . $cmid . '&gopage=4' . '">' . get_string('heading:declarations', 'arupapplication') . '</a>||' . get_string('error:required', 'arupapplication') . '$$$';
        }

        if (!empty($content)) {
            $records = explode('$$$', $content);
            foreach($records as $record) {
                if ($record) {
                    $table->data[] = new html_table_row(explode('||', $record));
                }
            }

            $outputcontent = html_writer::tag('h2', get_string('heading:errors', 'arupapplication'), array('class' => 'error')) .
                html_writer::tag('p', get_string('error:message', 'arupapplication'), array('class' => 'error')) .
                html_writer::table($table);

            return $outputcontent;
        } else {
            return false;
        }
    }
}