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

require_once($CFG->dirroot.'/lib/formslib.php');
require_once($CFG->dirroot.'/lib/coursecatlib.php');
require_once('lib.php');

/**
 * Based upon the mform class for creating and editing a calendar
 *
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class lunchandlearn_form extends moodleform {
    /**
     * The form definition
     */
    public function definition () {
        global $CFG, $USER, $DB;
        $mform = $this->_form;
        $mform->disable_form_change_checker();
        $lal = $this->_customdata;

        // Show recorded session entry at top of page when editing a past event...
        // as this is most likely the thing we'll be looking for.
        if (true === $lal->scheduler->has_past()) {
            $this->define_recorded_session_fields($mform);
        }

        $mform->addElement('header', 'general', get_string('general'));

        // Add some hidden fields.
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->setDefault('id', 0);

        $mform->addElement('hidden', 'courseid');
        $mform->setType('courseid', PARAM_INT);

        $mform->addElement('hidden', 'eventid');
        $mform->setType('eventid', PARAM_INT);

        $mform->addElement('hidden', 'eventtype');
        $mform->setType('eventtype', PARAM_ALPHA);
        $mform->setDefault('eventtype', 'lunchandlearn');

        $mform->addElement('hidden', 'instance');
        $mform->setType('instance', PARAM_INT);
        $mform->setDefault('instance', 0);

        $mform->addElement('hidden', 'action');
        $mform->setType('action', PARAM_ALPHANUMEXT);

        // Whether to resend invites on an edit.
        $mform->addElement('hidden', 'resendinvites');
        $mform->setType('resendinvites', PARAM_INT);
        $mform->setDefault('resendinvites', 0);

        // Normal fields.
        $mform->addElement('text', 'name', get_string('eventname','local_lunchandlearn'), array('size' => 50, 'maxlength' => 80));
        $mform->addRule('name', get_string('required'), 'required');
        $mform->setType('name', PARAM_TEXT);

        $regions = $DB->get_records_menu('local_regions_reg', array('userselectable' => 1));
        $regions[0] = 'Global';
        $mform->addElement('select', 'regionid', get_string('eventregion', 'local_lunchandlearn'), array('' => get_string('choose').'...') + $regions);

        $mform->addElement('text', 'office', get_string('office', 'local_lunchandlearn'), array('maxlength' => 80));
        $mform->setType('office', PARAM_TEXT);

        $mform->addElement('text', 'room', get_string('meetingroom', 'local_lunchandlearn'));
        $mform->setType('room', PARAM_TEXT);

        $mform->addElement('text', 'supplier', get_string('supplier', 'local_lunchandlearn'), array('maxlength' => 80));
        $mform->setType('supplier', PARAM_TEXT);
        $mform->setDefault('supplier', 'Moodle');

        $mform->addElement('select', 'categoryid', get_string('eventcategory', 'local_lunchandlearn'), lunchandlearn_get_categories_list());
        $mform->addHelpButton('categoryid', 'eventcategory','local_lunchandlearn');


        $mform->addElement('textarea', 'sessioninfo', get_string('sessioninfo','local_lunchandlearn'), array('cols' => '80','rows' => '5'));
        $mform->setType('sessioninfo', PARAM_TEXT);
        $mform->addHelpButton('sessioninfo', 'sessioninfo', 'local_lunchandlearn');

        $mform->addElement('textarea', 'summary', get_string('eventsummary','local_lunchandlearn'), array('cols' => '80','rows' => 10,'maxlength' => 150));
        // Old code: ->setValue(array('text' => $lal->get_summary()));?
        $mform->setType('summary', PARAM_RAW);
        $mform->addHelpButton('summary', 'eventsummary','local_lunchandlearn');
        $mform->addRule('summary', get_string('error:summarylen', 'local_lunchandlearn', 150), 'maxlength', 150, 'client');
        $mform->addRule('summary', get_string('required'), 'required');

        $mform->addElement('editor', 'description', get_string('eventdescription','local_lunchandlearn'))
              ->setValue(array('text' => $lal->get_description()));
        $mform->setType('description', PARAM_RAW);
        $mform->addHelpButton('description', 'eventdescription', 'local_lunchandlearn');

        $mform->addElement('header', 'schedulegroup', get_string('schedulegroup', 'local_lunchandlearn'));

        // In person capacity.
        $mform->addElement('advcheckbox', 'availableinperson', get_string('label:availableinperson', 'local_lunchandlearn'));
        $mform->addElement('text', 'capacity', get_string('label:capacity', 'local_lunchandlearn'));
        $mform->setType('capacity', PARAM_INT);
        $mform->addHelpButton('capacity', 'label:capacity','local_lunchandlearn');
        $mform->addElement('advcheckbox', 'overbookinperson', get_string('label:overbookinperson', 'local_lunchandlearn'));
        $mform->addHelpButton('overbookinperson', 'label:overbookinperson','local_lunchandlearn');
        $mform->disabledIf('capacity', 'availableinperson');
        $mform->disabledIf('overbookinperson', 'availableinperson');
        $mform->disabledIf('overbookinperson', 'capacity', 'eq', 0);

        $timezones = array();
        foreach (explode(',',$CFG->lunchandlearntimezones) as $tz) {
            $test = new DateTime();
            $test->setTimezone(new DateTimeZone($tz));
            $timezones[$tz] = $test->format('P (T)') .' - '.$tz;
        }
        $opts= array('showtz' => true, 'optional' => false, 'timezones' => $timezones);
        $opts['timezone'] = $lal->scheduler->get_timezone();
        $mform->addElement('date_time_selector', 'timestart', get_string('date'), $opts);
        $mform->addRule('timestart', get_string('required'), 'required');

        $mform->addElement('text', 'timedurationminutes', get_string('durationminutes', 'local_lunchandlearn'));
        $mform->setType('timedurationminutes', PARAM_INT);


        $mform->addElement('header', 'onlinegroup', get_string('onlinegroup', 'local_lunchandlearn'));

        // Online capacity.
        $mform->addElement('advcheckbox', 'availableonline', get_string('label:availableonline', 'local_lunchandlearn'));
        $mform->addElement('text', 'onlinecapacity', get_string('label:onlinecapacity', 'local_lunchandlearn'));
        $mform->setType('onlinecapacity', PARAM_INT);
        $mform->addHelpButton('onlinecapacity', 'label:onlinecapacity','local_lunchandlearn');
        $mform->addElement('advcheckbox', 'overbookonline', get_string('label:overbookonline', 'local_lunchandlearn'));
        $mform->addHelpButton('overbookonline', 'label:overbookonline','local_lunchandlearn');
        $mform->disabledIf('onlinecapacity', 'availableonline');
        $mform->disabledIf('overbookonline', 'availableonline');
        $mform->disabledIf('overbookonline', 'onlinecapacity', 'eq', 0);

        $mform->addElement('editor', 'joindetail', get_string('eventjoindetail','local_lunchandlearn'))
              ->setValue(array('text' => $lal->get_joindetail()));
        $mform->setType('joindetail', PARAM_RAW);
        $mform->addHelpButton('joindetail', 'eventjoindetail', 'local_lunchandlearn');

        $mform->addElement('header', 'sessionmaterial', get_string('sessionmaterials', 'local_lunchandlearn'));
        $mform->addElement('filemanager', 'attachments', get_string('sessionmaterials', 'local_lunchandlearn'));
        $mform->addHelpButton('attachments', 'sessionmaterials', 'local_lunchandlearn');

        if (false === $lal->scheduler->has_past()) {
            $this->define_recorded_session_fields($mform);
        }
        $modalprops = array();
        if ($lal->attendeemanager->get_attendee_count() > 0) {
            $modalprops['data-toggle'] = "modal";
            $modalprops['data-target'] = "#myModal";
        }

        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechanges'), $modalprops);
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
        //Old code: $this->add_action_buttons(false, get_string('savechanges'));.
    }

    public function validation($data, $files) {

         $errors = array();

        // Availability - must be one of online or inperson.
        if (empty($data['availableinperson']) && empty($data['availableonline'])) {
            $errors['availableinperson'] = get_string('selectoneonlineinperson', 'local_lunchandlearn');
            $errors['availableonline'] = get_string('selectoneonlineinperson', 'local_lunchandlearn');
        }

        // Byte length limits for taps.
        $bytelimits = array(
            'name' => 80,
            'office' => 80,
            'supplier' => 80,
            'summary' => 150,
            'timedurationminutes' => 12
        );
        foreach ($bytelimits as $field => $limit) {
            if (!empty($data[$field])) {
                $testdata = str_ireplace(array("\r\n", "\n", "\r"), ' ', $data[$field]);
                // Purposeful use of non-mb aware strlen() as Oracle is max bytes.
                $actuallength = strlen($testdata);
                if ($actuallength > $limit) {
                    $a = new stdClass();
                    $a->maxlength = $limit;
                    $a->actuallength = $actuallength;
                    $errors[$field] = get_string('error:maxcharlength', 'block_arup_mylearning', $a);
                }
            }
        }
        return $errors;
    }


    public function define_recorded_session_fields(MoodleQuickForm $mform) {
        $mform->addElement('header', 'recordedsession', get_string('recordedsession', 'local_lunchandlearn'));

        $mform->addElement('editor', 'recorded_editor', get_string('recordedsession', 'local_lunchandlearn'), array('rows' => 10), array(
                    'trusttext' => true,
                    'collapsed' => true,
                    'subdirs' => true,
                    'maxfiles' => 5,
                    'maxbytes' => get_max_upload_file_size(),
                    'context' => context_system::instance()));
        $mform->setType('recorded', PARAM_RAW);
    }

    public function menuify($records, $default='', $key='id', $value='name') {
        $r = array();
        $r[0] = $default;
        foreach ($records as $record) {
            $r[$record->$key] = $record->$value;
        }
        return $r;
    }

}
