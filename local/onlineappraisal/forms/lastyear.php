<?php
// This file is part of the appraisal plugin for Moodle
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
 * @package    mod_appraisal
 * @copyright  2015 Sonsbeekmedia
 * @author     Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class apform_lastyear extends moodleform {
    public function definition() {
        global $PAGE;
        
        $data = $this->_customdata;
        $mform = $this->_form;

        $mform->updateAttributes(array('class' => $mform->getAttribute('class').' oa-save-session-check'));

        $mform->addElement('hidden', 'formid', $data->formid);
        $mform->setType('formid', PARAM_INT);

        $mform->addElement('hidden', 'page', 'lastyear');
        $mform->setType('page', PARAM_TEXT);

        $mform->addElement('hidden', 'userid', $data->userid);
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('hidden', 'appraisalid', $data->appraisalid);
        $mform->setType('appraisalid', PARAM_INT);

        $mform->addElement('hidden', 'view', $data->viewingas);
        $mform->setType('view', PARAM_TEXT);

        $mform->addElement('hidden', 'appraiseeedit', $data->appraiseeedit);
        $mform->setType('appraiseeedit', PARAM_INT);

        $mform->addElement('hidden', 'appraiseredit', $data->appraiseredit);
        $mform->setType('appraiseredit', PARAM_INT);

        $mform->addElement('html', '<hr class="tophr">');
        $appraiseename = fullname($data->appraisal->appraisee);
        $mform->addElement('html', html_writer::tag('div',
            get_string('tagline', 'local_onlineappraisal', strtoupper($appraiseename)),
            array('class' => 'oa-tagline')));
        $mform->addElement('html', html_writer::tag('h2', $this->str('title')));

        $mform->addElement('html', html_writer::tag('div', $this->str('intro'), array('class' => 'm-b-20')));

        $lastyear = $this->get_lastyears_appraisal($data->appraisalid, $data->appraisal->appraisee->id);

        if (!$lastyear) {
            $file = $this->get_file_link('appraisalfile', $data->appraisal);

            if ($data->viewingas == 'appraisee' && $data->appraiseeedit == APPRAISAL_FIELD_EDIT) {
                if (!$file) {
                    $mform->addElement('html', html_writer::tag('div', $this->str('nolastyear'), array('class' => 'alert alert-warning')));
                }
                $mform->addElement('filemanager', 'appraisalfile', $this->str('upload'), null,
                            array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 1,
                                  'accepted_types' => array('.doc','.docx','.pdf')));
            } else if ($file) {
                    $mform->addElement('html', html_writer::tag('div', $this->str('file', $file), array('class' => 'm-b-20')));
            }
        } else if (local_onlineappraisal\permissions::is_allowed('appraisal:print', $lastyear->permissionsid, $data->appraisal->viewingas, $lastyear->archived, $lastyear->legacy)) {
            $printurl = new moodle_url('/local/onlineappraisal/print.php', array('appraisalid' => $lastyear->id, 'view' => $data->appraisal->viewingas, 'print' => 'appraisal', 'inline' => 1));
            $mform->addElement('html', html_writer::tag('div', $this->str('printappraisal', $printurl->out(false)), array('class' => 'm-b-20')));
        }

        $performance = $development = '';

        if ($lastyear) {
            $performance = $this->get_lastyear('performance');
            $development = $this->get_lastyear('development');
        }

        $appraiseelocked = '';
        if ($data->appraiseeedit == APPRAISAL_FIELD_LOCKED) {
            $appraiseelocked = ' locked="yes"';
        }

        $appraiserlocked = '';
        if ($data->appraiseredit == APPRAISAL_FIELD_LOCKED) {
            $appraiserlocked = ' locked="yes"';
        }

        $mform->addElement('textarearup', 'appraiseereview', $this->str('appraiseereview') . $performance, 'rows="15" cols="70"' . $appraiseelocked, $this->str('appraiseereviewhelp'), 'appraisee');
        $mform->setType('appraiseereview', PARAM_RAW);
        $mform->disabledIf('appraiseereview', 'appraiseeedit', 'eq', APPRAISAL_FIELD_LOCKED);

        $mform->addElement('textarearup', 'appraiserreview', $this->str('appraiserreview'), 'rows="15" cols="70"' . $appraiserlocked, $this->str('appraiserreviewhelp'), 'appraiser');
        $mform->setType('appraiserreview', PARAM_RAW);
        $mform->disabledIf('appraiserreview', 'appraiseredit', 'eq', APPRAISAL_FIELD_LOCKED);

        $mform->addElement('textarearup', 'appraiseedevelopment', $this->str('appraiseedevelopment') . $development, 'rows="15" cols="70"' . $appraiseelocked, $this->str('appraiseedevelopmenthelp'), 'appraisee');
        $mform->setType('appraiseedevelopment', PARAM_RAW);
        $mform->disabledIf('appraiseedevelopment', 'appraiseeedit', 'eq', APPRAISAL_FIELD_LOCKED);

        $mform->addElement('textarearup', 'appraiseefeedback', $this->str('appraiseefeedback'), 'rows="15" cols="70"' . $appraiseelocked, $this->str('appraiseefeedbackhelp'), 'appraisee');
        $mform->setType('appraiseefeedback', PARAM_RAW);
        $mform->disabledIf('appraiseefeedback', 'appraiseeedit', 'eq', APPRAISAL_FIELD_LOCKED);

        if ($data->appraiseeedit == APPRAISAL_FIELD_EDIT || $data->appraiseredit == APPRAISAL_FIELD_EDIT) {
            $buttonarray=array();
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('form:save', 'local_onlineappraisal'));
            $buttonarray[] = &$mform->createElement('submit', 'submitcontinue', get_string('form:submitcontinue', 'local_onlineappraisal'));
            $buttonarray[] = &$mform->createElement('cancel', 'cancelbutton', get_string('form:cancel', 'local_onlineappraisal'), array('class' => 'm-l-5'));
            $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);

            // Saving nag modal.
            $renderer = $PAGE->get_renderer('local_onlineappraisal');
            $mform->addElement('html', $renderer->render_from_template('local_onlineappraisal/modal_save_nag', new stdClass()));
        } else {
            $mform->addElement('html', html_writer::link($data->nexturl,
                get_string('form:nextpage', 'local_onlineappraisal'), array('class' => 'btn btn-success')));
        }
    }

    private function str($string, $placeholder = null) {
        return get_string('form:lastyear:' . $string, 'local_onlineappraisal', $placeholder);
    }

    /**
     *
     */
    private function get_file_link($fieldname, $appraisal) {
        global $DB, $CFG;

        if (\local_onlineappraisal\permissions::is_allowed('lastyear:view', $appraisal->permissionsid, $appraisal->viewingas, $appraisal->archived, $appraisal->legacy)) {
            $files = $DB->get_records('files',
                array('component' => 'local_onlineappraisal', 'filearea' => $fieldname, 'itemid' => $appraisal->id));

            foreach ($files as $file) {
                if ($file->filename == '.') {
                    continue;
                }
                $file->path = $CFG->wwwroot . '/pluginfile.php/' . $file->contextid . '/local_onlineappraisal/' . $fieldname . '/' . $file->itemid  . '/' .$file->filename;
                return $file;
            }
        }
        return false;
    }

    /**
     * Retrieve information about older appraisals for this user.
     * @param int $appraisalid
     * @param int $userid
     */
    private function get_lastyears_appraisal($appraisalid, $userid) {
        global $DB;

        if (isset($this->_customdata->oldappraisal)) {
            return $this->_customdata->oldappraisal;
        }

        $params = array('appraisee_userid' => $userid, 'deleted' => 0);

        $allappraisals = $DB->get_records('local_appraisal_appraisal', $params, 'created_date DESC');

        if (count($allappraisals) > 1) {
            foreach ($allappraisals as $appraisal) {
                if ($appraisal->id == $appraisalid) {
                    continue;
                } else {
                    // Fetches the next record
                    $this->_customdata->oldappraisal = $appraisal;
                    return $appraisal;
                }
            }
        }
        return false;
    }

    /**
     * Get last year info.
     */
    private function get_lastyear($type) {
        if ($this->_customdata->oldappraisal) {
            $oldappraisal = $this->_customdata->oldappraisal;
            if ($oldappraisal->legacy) {
                return $this->get_lastyear_legacy($type, $oldappraisal);
            } else {
                return $this->get_lastyear_new($type, $oldappraisal);
            }
        }
    }

    private function get_lastyear_legacy($type, $oldappraisal) {
        global $DB, $PAGE;

        if ($type == 'performance') {
            $table = 'local_appraisal_per_objectiv';
        } else if ($type == 'development') {
            $table = 'local_appraisal_dev_objectiv';
        } else {
            return false;
        }

        $renderer = $PAGE->get_renderer('local_onlineappraisal', 'lastyear');

        if ($objectives = $DB->get_records($table, array('appraisalid' => $oldappraisal->id,
            'previous_appraisal' => 0))) {
            $template = new \local_onlineappraisal\output\lastyear\lastyear_legacy($objectives, $type);
            return $renderer->render($template);
        }
    }

    /**
     *
     * @global \moodle_database $DB
     * @global type $PAGE
     * @param type $type
     * @param type $oldappraisal
     * @return boolean
     */
    private function get_lastyear_new($type, $oldappraisal) {
        global $DB, $PAGE;

        if ($type == 'performance') {
            $forms = array('impactplan' => array('impact', 'support'));
        } else if ($type == 'development') {
            $forms = array('development' => array('seventy', 'twenty', 'ten'));
        } else {
            return false;
        }

        $renderer = $PAGE->get_renderer('local_onlineappraisal', 'lastyear');

        $lastyear = array();
        foreach ($forms as $form => $fields) {
            $formrec = $DB->get_record(
                    'local_appraisal_forms',
                    array(
                        'appraisalid' => $oldappraisal->id,
                        'user_id' => $oldappraisal->appraisee_userid,
                        'form_name' => $form
                    ));
            if (!$formrec) {
                continue;
            }
            list($in, $params) = $DB->get_in_or_equal($fields, SQL_PARAMS_NAMED);
            $params['formid'] = $formrec->id;
            $fieldrecs = $DB->get_records_select('local_appraisal_data', "form_id = :formid AND name {$in}", $params);
            foreach ($fieldrecs as $fieldrec) {
                $out = new stdClass();
                $out->question = get_string("form:{$form}:{$fieldrec->name}", 'local_onlineappraisal');
                if ($fieldrec->type == 'array') {
                    $out->isarray = true;
                    $out->data = unserialize($fieldrec->data);
                } else {
                    $out->data = $fieldrec->data;
                }
                $lastyear[] = clone($out);
            }
        }

        $template = new \local_onlineappraisal\output\lastyear\lastyear($lastyear, $type);
        return $renderer->render($template);
    }

    public function definition_after_data() {
        global $USER;
        $mform =& $this->_form;
        $data = $this->_customdata;
        if ($data->userid != $USER->id) {
            $mform->hardFreeze();
        }
    }
}
