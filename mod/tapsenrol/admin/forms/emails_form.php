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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

class emails_form extends moodleform {

    protected $_textreplacements;

    public function definition() {
        // Gather hints.
        $this->_textreplacements['general'] = get_string('iw:emails:replacements:general', 'tapsenrol');

        $mform =& $this->_form;

        $emails = $this->_customdata['emails'];
        $type = $this->_customdata['type'];

        foreach ($this->_customdata['params'] as $param => $value) {
            $mform->addElement('hidden', $param, $value);
            $mform->setType($param, PARAM_INT);
        }

        if ($this->_customdata['viewonly']) {
            global $PAGE;
            $renderer = $PAGE->get_renderer('mod_tapsenrol');
            $mform->addElement('html', $renderer->alert(get_string('iw:emails:viewonly', 'tapsenrol'), 'alert-warning', false));
        }

        $mform->addElement('html', $this->_textreplacements['general']);

        foreach ($emails['default'] as $email) {
            $mform->addElement('header', "header[$email->id]", $email->title);
            $mform->setExpanded("header[$email->id]", false);

            $mform->addElement('hidden', "email[$email->id]", $email->id);
            $mform->setType("email[$email->id]", PARAM_INT);

            $mform->addElement('html', html_writer::tag('p', $email->description));

            $currentemail = 'default';
            if (isset($emails['cm'][$email->id])) {
                $currentemail = 'cm';
            } else if (isset($emails['iw'][$email->id])) {
                $currentemail = 'iw';
            } else if (isset($emails['global'][$email->id])) {
                $currentemail = 'global';
            }
            $a = new stdClass();
            $a->currentemail = get_string("iw:emails:type:$currentemail", 'tapsenrol');
            $previewurl = new moodle_url(
                '/mod/tapsenrol/admin/email_preview.php',
                array('type' => $currentemail, 'id' => $emails[$currentemail][$email->id]->id)
            );
            $a->previewlink = html_writer::link(
                $previewurl,
                get_string('iw:emails:preview', 'tapsenrol'),
                array(
                    'data-toggle' => 'modal',
                    'data-target' => '#iw-email-modal',
                    'data-label' => get_string('iw:emails:subject', 'tapsenrol').': '.$emails[$currentemail][$email->id]->subject,
                    'data-remote' => false,
                    'data-backdrop' => 'static',
                    'data-keyboard' => false
                )
            );
            $mform->addElement('html', get_string('iw:emails:current', 'tapsenrol', $a));

            if (get_string_manager()->string_exists('iw:emails:replacements:'.$email->email, 'tapsenrol')) {
                $this->_textreplacements[$email->email] = get_string('iw:emails:replacements:'.$email->email, 'tapsenrol');
                $mform->addElement('html', $this->_textreplacements[$email->email]);
            }

            $mform->addElement('text', "subject[$email->id]", get_string('iw:emails:subject', 'tapsenrol'));
            $mform->setType("subject[$email->id]", PARAM_TEXT);
            if (isset($emails[$type][$email->id])) {
                $mform->setDefault("subject[$email->id]", $emails[$type][$email->id]->subject);
            }

            $mform->addElement('checkbox', "html[$email->id]", get_string('iw:emails:usehtml', 'tapsenrol'));
            if (isset($emails[$type][$email->id])) {
                $mform->setDefault("html[$email->id]", (bool) $emails[$type][$email->id]->html);
            }

            $mform->addElement('textarea', "body[$email->id]", get_string('iw:emails:body', 'tapsenrol'));
            $mform->setType("body[$email->id]", PARAM_RAW);
            if (isset($emails[$type][$email->id])) {
                $mform->setDefault("body[$email->id]", $emails[$type][$email->id]->body);
            }
        }

        // Submit buttons.
        if ($this->_customdata['viewonly']) {
            $mform->addElement('cancel', 'cancel', get_string('exit:viewonly', 'tapsenrol'));
            $mform->closeHeaderBefore('cancel');
        } else {
            $this->add_action_buttons();
        }
    }

    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        // Need to validate presence of both body and subject if one is set but other isn't then error, if both unset is ok (will use next up list).

        foreach ($data['email'] as $emailid) {
            if (empty($data['subject'][$emailid]) xor  empty($data['body'][$emailid])) {
                $error = get_string('iw:emails:error:emptysubjectbody', 'tapsenrol');
                if (empty($data['subject'][$emailid])) {
                    $errors["subject[$emailid]"]  = $error;
                } else {
                    $errors["body[$emailid]"] = $error;
                }
            } else if ($emailid == 'approved_invite' && !strpos($data['body'][$emailid], '[[update:extrainfo]]')) {
                $errors["body[$emailid]"] = get_string('iw:emails:error:missingplaceholder', 'tapsenrol', '[[update:extrainfo]]');
            }
        }

        return $errors;
    }

    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }

        foreach ($data->email as $emailid) {
            if (!isset($data->html[$emailid])) {
                $data->html[$emailid] = 0;
            }
        }

        return $data;
    }
}
