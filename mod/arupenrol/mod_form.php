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
 * Instance add/edit form
 *
 * @package    mod_arupenrol
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_arupenrol_mod_form extends moodleform_mod {

    protected $_keyvalue;

    public function definition() {
        global $CFG;

        $mform =& $this->_form;

        // Check completion is enabled.
        $this->_check_completion();

        // General Settings.

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name', 'arupenrol'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');

        $this->standard_intro_elements(get_string('introeditor', 'arupenrol'));

        $mform->addElement('checkbox', 'shownamebefore', get_string('functionality:shownamebefore', 'arupenrol'), '', 1);

        $mform->addElement('checkbox', 'showdescriptionbefore', get_string('functionality:showdescriptionbefore', 'arupenrol'), '', 1);

        $mform->addElement('checkbox', 'shownameafter', get_string('functionality:shownameafter', 'arupenrol'), '', 1);

        $mform->addElement('checkbox', 'showdescriptionafter', get_string('functionality:showdescriptionafter', 'arupenrol'), '', 1);

        // Functionality settings.

        $mform->addElement('header', 'functionalityheader', get_string('functionality:header', 'arupenrol'));
        $mform->setExpanded('functionalityheader', true, true);

        $actionsgroup = array();
        $actionsgroup[] = $mform->createElement('radio', 'action', '', get_string('functionality:action:1', 'arupenrol'), 1);
        $actionsgroup[] = $mform->createElement('radio', 'action', '', get_string('functionality:action:2', 'arupenrol'), 2);
        $actionsgroup[] = $mform->createElement('radio', 'action', '', get_string('functionality:action:3', 'arupenrol'), 3);
        $mform->addGroup($actionsgroup, 'actions', '', array('<br />'), false);
        // Default activity action.
        $mform->setDefault('action', 3);

        $mform->addElement('html', html_writer::tag('p', '&nbsp;'));

        $mform->addElement('text', 'keylabel', get_string('functionality:keylabel', 'arupenrol'), array('size' => '64'));
        $mform->setType('keylabel', PARAM_TEXT);
        $mform->disabledIf('keylabel', 'action', 'neq', 2);

        $mform->addElement('checkbox', 'usegroupkeys', get_string('functionality:usegroupkeys', 'arupenrol'), '', 1);
        $mform->addHelpButton('usegroupkeys', 'functionality:usegroupkeys', 'arupenrol');
        $mform->disabledIf('usegroupkeys', 'action', 'neq', 2);

        $this->_keyvalue = (string) mt_rand(100000, 999999);
        $keygroup = array();
        for ($i = 0; $i < 6; $i++) {
            $keygroup[$i] = $mform->createElement('select', "digit[{$i}]", "Digit {$i}", range(0, 9));
        }
        $mform->addGroup($keygroup, 'key', get_string('functionality:keyvalue', 'arupenrol'), array(''), false);
        $mform->disabledIf('key', 'action', 'neq', 2);
        $mform->disabledIf('key', 'usegroupkeys', 'checked');
        $mform->addElement('hidden', 'keyvalue', $this->_keyvalue);
        $mform->setType('keyvalue', PARAM_ALPHANUM);

        $mform->addElement('checkbox', 'keytransform', get_string('functionality:keytransform', 'arupenrol'), '', 1);
        $mform->addHelpButton('keytransform', 'functionality:keytransform', 'arupenrol');
        $mform->disabledIf('keytransform', 'action', 'neq', 2);
        $mform->disabledIf('keytransform', 'usegroupkeys', 'checked');

        $mform->addElement('checkbox', 'enroluser', get_string('functionality:enroluser', 'arupenrol'), '', 1);
        $mform->addHelpButton('enroluser', 'functionality:enroluser', 'arupenrol');
        $mform->disabledIf('enroluser', 'action', 'eq', 1);
        $mform->setDefault('enroluser', 1);

        $mform->addElement('text', 'buttontext', get_string('functionality:buttontext', 'arupenrol'), array('size' => '64'));
        $mform->setType('buttontext', PARAM_TEXT);
        $mform->disabledIf('buttontext', 'action', 'eq', 1);

        $buttontypes = array(
            'default',
            'primary',
            'info',
            'success',
            'warning',
            'danger',
            'inverse',
        );
        $buttontypesgroup = array();
        foreach ($buttontypes as $buttontype) {
            $buttontypesgroup[] = $mform->createElement('radio', 'buttontype', '', '<a class="btn btn-'.$buttontype.'">'.ucfirst($buttontype).'</a>', $buttontype);
        }
        $mform->addGroup($buttontypesgroup, 'buttontypes', get_string('functionality:buttontype', 'arupenrol'), array(' '), false);
        $mform->setDefault('buttontype', 'default');
        $mform->disabledIf('buttontypes', 'action', 'eq', 1);

        $mform->addElement('textarea', 'successmessage', get_string('functionality:successmessage', 'arupenrol'));
        $mform->setType('successmessage', PARAM_RAW); // No XSS prevention here, users must be trusted.
        $mform->addHelpButton('successmessage', 'functionality:successmessage', 'arupenrol');
        $mform->disabledIf('successmessage', 'action', 'eq', 1);

        $mform->addElement('editor', 'outroeditor', get_string('functionality:outroeditor', 'arupenrol'), array('rows' => 10), array('maxfiles' => EDITOR_UNLIMITED_FILES,
            'noclean' => true, 'context' => $this->context, 'collapsed' => true));
        $mform->setType('outroeditor', PARAM_RAW); // No XSS prevention here, users must be trusted.
        $mform->addHelpButton('outroeditor', 'functionality:outroeditor', 'arupenrol');

        $mform->addElement('checkbox', 'unenroluser', get_string('functionality:unenroluser', 'arupenrol'), '', 1);
        $mform->addHelpButton('unenroluser', 'functionality:unenroluser', 'arupenrol');
        $mform->disabledIf('unenroluser', 'action', 'eq', 1);
        $mform->disabledIf('unenroluser', 'enroluser', 'notchecked');

        $mform->addElement('text', 'unenrolbuttontext', get_string('functionality:unenrolbuttontext', 'arupenrol'), array('size' => '64'));
        $mform->setType('unenrolbuttontext', PARAM_TEXT);
        $mform->disabledIf('unenrolbuttontext', 'action', 'eq', 1);
        $mform->disabledIf('unenrolbuttontext', 'enroluser', 'notchecked');
        $mform->disabledIf('unenrolbuttontext', 'unenroluser', 'notchecked');

        $unenrolbuttontypesgroup = array();
        foreach ($buttontypes as $buttontype) {
            $unenrolbuttontypesgroup[] = $mform->createElement('radio', 'unenrolbuttontype', '', '<a class="btn btn-'.$buttontype.'">'.ucfirst($buttontype).'</a>', $buttontype);
        }
        $mform->addGroup($unenrolbuttontypesgroup, 'unenrolbuttontypes', get_string('functionality:unenrolbuttontype', 'arupenrol'), array(' '), false);
        $mform->setDefault('unenrolbuttontype', 'default');
        $mform->disabledIf('unenrolbuttontypes', 'action', 'eq', 1);
        $mform->disabledIf('unenrolbuttontypes', 'enroluser', 'notchecked');
        $mform->disabledIf('unenrolbuttontypes', 'unenroluser', 'notchecked');

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    public function data_preprocessing(&$defaultvalues) {
        if ($this->current->instance) {
            $draftideditor = file_get_submitted_draft_itemid('outroeditor');
            $currentoutro = file_prepare_draft_area($draftideditor, $this->context->id, 'mod_arupenrol', 'outro', 0, array('subdirs' => true), $defaultvalues['outro']);
            $defaultvalues['outroeditor'] = array('text' => $currentoutro, 'format' => $defaultvalues['outroformat'], 'itemid' => $draftideditor);
        } else {
            $draftideditor = file_get_submitted_draft_itemid('outroeditor');
            file_prepare_draft_area($draftideditor, null, null, null, null);
            $defaultvalues['outroeditor'] = array('text' => '', 'format' => FORMAT_HTML, 'itemid' => $draftideditor);
        }

        if (!empty($defaultvalues['keyvalue'])) {
            $this->_keyvalue = str_pad($defaultvalues['keyvalue'], 6, '0', STR_PAD_LEFT);
        }

        $keylength = strlen($this->_keyvalue);
        for ($i = 0; $i < $keylength; $i++) {
            $defaultvalues["digit[{$i}]"] = $this->_keyvalue[$i];
        }

        $trimelements = array('keylabel', 'buttontext');
        foreach ($trimelements as $trimelement) {
            if (!empty($defaultvalues[$trimelement])) {
                $defaultvalues[$trimelement] = trim($defaultvalues[$trimelement]);
            }
        }

        parent::data_preprocessing($defaultvalues);
    }

    public function definition_after_data() {
        parent::definition_after_data();
        $mform =& $this->_form;

        // Remove completion settings as this is forced/automatic.
        if ($mform->elementExists('activitycompletionheader')) {
            $mform->removeElement('activitycompletionheader');
        }
    }

    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }
        // Force completion tracking.
        $data->completion = COMPLETION_TRACKING_AUTOMATIC;

        $checkboxes = array(
            'shownamebefore', 'showdescriptionbefore',
            'shownameafter', 'showdescriptionafter',
            'usegroupkeys', 'keytransform',
            'enroluser', 'unenroluser'
        );
        foreach ($checkboxes as $checkbox) {
            if (empty($data->{$checkbox})) {
                $data->{$checkbox} = 0;
            }
        }

        $buttontypes = array('buttontype', 'unenrolbuttontype');
        foreach ($buttontypes as $buttontype) {
            if (!isset($data->{$buttontype})) {
                $data->{$buttontype} = 'default';
            }
        }

        $data->keyvalue = '';
        foreach ($data->digit as $digit) {
            $data->keyvalue .= $digit;
        }

        $trimelements = array('keylabel', 'buttontext');
        foreach ($trimelements as $trimelement) {
            if (!empty($data->{$trimelement})) {
                $data->{$trimelement} = trim($data->{$trimelement});
            }
        }

        // Editor content cleaning.
        $editors = array('introeditor', 'outroeditor');
        foreach ($editors as $editor) {
            $text = preg_replace('/\s/u', '', strip_tags($data->{$editor}['text']));
            if (empty($text)) {
                $data->{$editor}['text'] = '';
            }
        }

        $data->outro       = $data->outroeditor['text'];
        $data->outroformat = $data->outroeditor['format'];

        return $data;
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        if (in_array($data['action'], array(2, 3)) && isset($data['enroluser'])) {
            $result = $this->_check_enrolment_plugins();
            if ($result !== true) {
                switch ($data['action']) {
                    case 2:
                        $errors['enroluser'] = $result;
                        break;
                    case 3:
                        $errors['actions'] = $result;
                        break;
                }
            }
        }

        $keylabel = empty($data['keylabel']) ? '' : trim($data['keylabel']);
        if ($data['action'] == 2 && empty($keylabel)) {
            $errors['keylabel'] = get_string('err_required', 'form');
        }

        $buttontext = empty($data['buttontext']) ? '' : trim($data['buttontext']);
        if (in_array($data['action'], array(2, 3)) && empty($buttontext)) {
            $errors['buttontext'] = get_string('err_required', 'form');
        }

        $unenrolbuttontext = empty($data['unenrolbuttontext']) ? '' : trim($data['unenrolbuttontext']);
        if (in_array($data['action'], array(2, 3)) && isset($data['unenroluser']) && empty($unenrolbuttontext)) {
            $errors['unenrolbuttontext'] = get_string('err_required', 'form');
        }

        return $errors;
    }

    protected function _check_enrolment_plugins() {
        global $COURSE;

        $changeurl = new moodle_url('/enrol/instances.php', array('id' => $COURSE->id));
        $changelink = html_writer::link($changeurl, get_string('enrolplugins:edit', 'arupenrol'), array('target' => '_blank'));
        $basemessage = html_writer::empty_tag('br') .
            get_string('enrolplugins:requirements', 'arupenrol') .
            html_writer::empty_tag('br') .
            $changelink;

        // Should be manual, self (not auto), guest.
        $self = new stdClass();
        $self->field = 'customchar1';
        $self->logic = 'notequal';
        $self->value = 'y';
        $requiredenrolments = array(
            'manual' => array(),
            'self' => array($self),
            'guest' => array(),
        );

        $enrolinstances = enrol_get_instances($COURSE->id, true);
        if (count($enrolinstances) != count($requiredenrolments)) {
            return get_string('enrolplugins:countmismatch', 'arupenrol').$basemessage;
        }
        foreach ($requiredenrolments as $enrolplugin => $requirements) {
            $current = array_shift($enrolinstances);
            if ($current->enrol != $enrolplugin) {
                return get_string('enrolplugins:incorrectorder', 'arupenrol').$basemessage;
            } else {
                foreach ($requirements as $requirement) {
                    switch ($requirement->logic) {
                        case 'notequal' :
                            if (!($current->{$requirement->field} != $requirement->value)) {
                                return get_string('enrolplugins:incorrectsettings', 'arupenrol').$basemessage;
                            }
                            break;
                    }
                }
            }
        }

        return true;
    }

    protected function _check_completion() {
        global $COURSE;

        // Completion must be enabled for this plugin to work.
        $completion = new completion_info($COURSE);
        if (!$completion->is_enabled()) {
            $this->_trigger_notice(get_string('completionnotenabled', 'arupenrol', core_text::strtolower(get_string('course'))));
        }
    }

    protected function _trigger_notice($message) {
        $url = new moodle_url('/course/view.php', array('id' => $this->current->course));
        notice($message, $url);
        exit;
    }
}