<?php
// This file is part of the Arup Course Management system
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

namespace mod_tapsenrol;

use coursemetadatafield_arup\arupmetadata;
use mod_tapsenrol\event\class_created;
use mod_tapsenrol\event\class_updated;

require_once($CFG->libdir . '/formslib.php');

/**
 *
 * @package     local_coursemanager
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class cmform_class extends \moodleform {
    protected $hasattendedenrolments;

    const CLASS_STATUS_NORMAL = 'Normal';
    const CLASS_STATUS_PLANNED = 'Planned';

    public function definition() {
        global $PAGE;

        $data = $this->_customdata;
        $mform = $this->_form;

        $mform->addElement("hidden", "id");
        $mform->setType('id', PARAM_INT);
        $mform->addElement("hidden", "courseid");
        $mform->setType('courseid', PARAM_INT);
        $mform->addElement("hidden", "cmid");
        $mform->setType('cmid', PARAM_INT);

        $classtypes = [
                enrolclass::TYPE_CLASSROOM => get_string('classroom', 'tapsenrol'),
                enrolclass::TYPE_ELEARNING => get_string('elearning', 'tapsenrol')
        ];

        $classtypeelem = $mform->createElement("select", "classtype", $this->str('classtype'), $classtypes);

        if (isset($data->courseid)) {
            $metadata = arupmetadata::fetch(['course' => $data->courseid]);
            if ($metadata->classtypelocked()) {
                $classtypeelem = $mform->createElement("hidden", "classtype", $metadata->get_default_class_type());
            }
        }

        $this->_form->addElement($classtypeelem);
        $mform->setType('classtype', PARAM_INT);

        $classstatus = array(
                self::CLASS_STATUS_NORMAL  => get_string('class_normal', 'tapsenrol'),
                self::CLASS_STATUS_PLANNED => get_string('class_planned', 'tapsenrol')
        ); // 'Planned' indicates waiting list.

        $this->add_element("classstatus", "select", PARAM_TEXT, $classstatus);

        $mform->registerNoSubmitButton('updatetypestatus');
        $mform->addElement('submit', 'updatetypestatus', get_string('courseformatudpate'));

        $PAGE->requires->js_call_amd('mod_tapsenrol/editclass', 'initialise');

        $this->add_element("classname", "text", PARAM_TEXT, null, null, true);

        $this->add_element("classduration", "text", PARAM_FLOAT);
        $taps = new \mod_tapsenrol\taps();
        $durationunits = $taps->get_durationunitscode();
        array_shift($durationunits);
        array_unshift($durationunits, get_string('form:class:classdurationunits', 'tapsenrol'));

        $this->add_element("classdurationunitscode", "select", null, $durationunits);

        // Set automatically based on classstarttime (this field existed because some TAPS returned timezones needed to be normalised/converted).
        // Allow users to set this one.
        $this->add_element("usedtimezone", "select", null, $this->get_timezones(), 'Europe/London', true);
        // Need to be set in conjunction with start/end times and timezone.
        // For legacy compatibility startdate is midnight (UTC) on day class starts and end is 23:59:59 (UTC) at end of day class ends.
        // Force UTC for incoming timestamps - converted in get_data() based on chosen timezone.
        // Optional flag must be set for date_time_selector due to use of addElement() - bug in core element code?
        $this->add_element("classstarttime", "date_time_selector", null, ['timezone' => 'UTC', 'optional' => false]);
        $this->add_element("classendtime", "date_time_selector", null, ['timezone' => 'UTC', 'optional' => false]);
        $this->add_element("enrolmentstartdate", "date_selector", null, ['timezone' => 'UTC'], null, true);
        // Needs to be able to be zero (never ends). add tickbox.
        // UTC forced in custom element addition, setting option here would cause issues.
        $this->add_element("enrolmentenddate", "date_selector_optional", null, null, null, true);
        $this->add_element("classhidden", "checkbox", null, null, null, true);

        // City or office. Should be select in version 2. then we could add locations if needed and search for existing.
        $this->add_element("location", "text", PARAM_TEXT);
        $this->add_element("trainingcenter", "text", PARAM_TEXT);
        $this->add_element("maximumattendees", "text", PARAM_INT);

        $this->add_element("currencycode", "select", null, $taps->get_classcostcurrency());
        // Float? Decimal(20,2) in DB.
        $this->add_element("price", "text", PARAM_TEXT);
        $this->add_element("jobnumber", "text", PARAM_TEXT);
        $this->add_element("classsuppliername", "text", PARAM_TEXT);

        $mform->addRule('classname', get_string('required', 'tapsenrol'), 'required', null, 'client');

        if (!empty($data->id)) {
            $this->add_action_buttons(true, $this->str('updateclass'));
        } else {
            $this->add_action_buttons(true, $this->str('saveclass'));
        }
    }

    public function add_element($fieldname, $fieldtype, $settype = null, $options = null, $default = null, $help = false) {
        $mform = $this->_form;
        if ($options) {
            $mform->addElement($fieldtype, $fieldname, $this->str($fieldname), $options);
        } else if ($fieldtype == 'date_selector_optional') {
            $fieldtype = 'date_selector';
            $availablefromgroup = array();
            // Force UTC for all 'date' timestamps.
            $availablefromgroup[] =& $mform->createElement('date_selector', $fieldname, '', ['timezone' => 'UTC']);
            $availablefromgroup[] =& $mform->createElement('checkbox', $fieldname . 'enabled', '', get_string('enable'));
            $mform->addGroup($availablefromgroup, $fieldname . 'group', $this->str($fieldname), ' ', false);
            $mform->disabledIf($fieldname . 'group', $fieldname . 'enabled');
            return '';
        } else {
            $mform->addElement($fieldtype, $fieldname, $this->str($fieldname));
        }
        if ($help) {
            $mform->addHelpButton($fieldname, 'form:class:' . $fieldname, 'tapsenrol');
        }
        if ($settype) {
            $mform->setType($fieldname, $settype);
        }
        if ($default) {
            $mform->setDefault($fieldname, $default);
        }
    }

    public function get_timezones() {
        global $DB, $USER;
        $timezones = array($this->str('selecttimezone'));
        $timezones['UTC'] = $this->str('globaltime');
        $aruptimezones = $DB->get_records('local_timezones');
        if (count($aruptimezones) > 0) {
            foreach ($aruptimezones as $atz) {
                $timezones[$atz->timezone] = $atz->display;
            }
        } else {
            $mdltz = \core_date::get_list_of_timezones($USER->timezone, true);
            foreach ($mdltz as $mtz) {
                $timezones[$mtz] = $mtz;
            }
        }
        return $timezones;
    }

    /**
     * Validate the form
     */
    public function validation($data, $files) {
        global $DB;
        $errors = array();

        if (isset($data['price'])) {
            if (strpos($data['price'], ",")) {
                $errors['price'] = $this->str('priceerror');
            }
        }
        if ($data['usedtimezone'] == "0") {
            $errors['usedtimezone'] = get_string('required', 'tapsenrol');
        }

        $sql = 'SELECT id FROM {local_taps_class}
                 WHERE courseid = :courseid
                   AND LOWER(classname) = LOWER(:classname)
                   AND NOT id  = :id';
        $id = isset($data['id']) ? $data['id'] : -1;
        $dupes = $DB->get_records_sql($sql,
                array('courseid' => $data['courseid'], 'classname' => $data['classname'], 'id' => $id));
        if (count($dupes) > 0) {
            $errors['classname'] = get_string('duplicateclassname', 'tapsenrol');
        }
        // Don't want to use empty() to check classendtime as need it to actually be set.
        if (!empty($this->hasattendedenrolments)
                && isset($this->_customdata->classendtime) && !$this->_customdata->classendtime
                && $this->_customdata->classtype == enrolclass::TYPE_ELEARNING && !empty($data['classendtimeenabled'])
        ) {
            // Need to offset UTC timestamps based on chosen timezone.
            try {
                $timezone = new \DateTimeZone($data['usedtimezone']);
            } catch (\Exception $e) {
                $timezone = new \DateTimeZone(date_default_timezone_get());
            }
            $classendtimestring = gmdate('Y-m-d H:i', $data['classendtime']);
            $classendtime = new \DateTime($classendtimestring, $timezone);
            if ($classendtime->getTimestamp() < time()) {
                $errors['classendtimegroup'] = get_string('classendtime:past', 'tapsenrol');
            }
        }
        return $errors;
    }

    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return $data;
        }
        if (empty($data->price)) {
            $data->price = null;
        }
        // Need to offset UTC timestamps based on chosen timezone for start and end dates/times.
        try {
            $timezone = new \DateTimeZone($data->usedtimezone);
        } catch (\Exception $e) {
            $timezone = new \DateTimeZone(date_default_timezone_get());
        }
        if (!empty($data->classstarttime)) {
            $classstarttimestring = gmdate('Y-m-d H:i', $data->classstarttime);
            $classstartdatestring = gmdate('Y-m-d 00:00:00', $data->classstarttime);

            $classstarttime = new \DateTime($classstarttimestring, $timezone);
            $data->classstarttime = $classstarttime->getTimestamp();

            $classstartdate = new \DateTime($classstartdatestring, $timezone);
            $data->classstartdate = $classstartdate->getTimestamp();
        }
        if (!empty($data->classendtime)) {
            $classendtimestring = gmdate('Y-m-d H:i', $data->classendtime);
            $classenddatestring = gmdate('Y-m-d 23:59:59', $data->classendtime);

            $classendtime = new \DateTime($classendtimestring, $timezone);
            $data->classendtime = $classendtime->getTimestamp();

            $classenddate = new \DateTime($classenddatestring, $timezone);
            $data->classenddate = $classenddate->getTimestamp();
        }
        if (!empty($data->enrolmentstartdate)) {
            $enrolmentstartdatestring = gmdate('Y-m-d 00:00:00', $data->enrolmentstartdate);
            $enrolmentstartdate = new \DateTime($enrolmentstartdatestring, $timezone);
            $data->enrolmentstartdate = $enrolmentstartdate->getTimestamp();
        }
        if (!empty($data->enrolmentenddate)) {
            $enrolmentenddatestring = gmdate('Y-m-d 23:59:59', $data->enrolmentenddate);
            $enrolmentenddate = new \DateTime($enrolmentenddatestring, $timezone);
            $data->enrolmentenddate = $enrolmentenddate->getTimestamp();
        }
        return $data;
    }

    public function str($string) {
        return get_string('form:class:' . $string, 'mod_tapsenrol');
    }

    public function optional_time_selector($fieldname) {
        $mform = $this->_form;
        $availablefromgroup = array();
        // Force UTC for incoming timestamps - converted in get_data() based on chosen timezone.
        $availablefromgroup[] =&
                $mform->createElement('date_time_selector', $fieldname, '', ['timezone' => 'UTC', 'optional' => false]);
        $availablefromgroup[] =& $mform->createElement('checkbox', $fieldname . 'enabled', '', get_string('enable'));
        $group = $this->createGroup($availablefromgroup, $fieldname . 'group', $this->str($fieldname), ' ', false);
        $mform->disabledIf($fieldname . 'group', $fieldname . 'enabled');
        return $group;
    }

    public function create_element($fieldname, $fieldtype, $options = null) {
        $mform = $this->_form;
        if ($options) {
            return $mform->createElement($fieldtype, $fieldname, $this->str($fieldname), $options);
        } else {
            return $mform->createElement($fieldtype, $fieldname, $this->str($fieldname));
        }
    }

    private function &createGroup($elements, $name = null, $groupLabel = '', $separator = null, $appendName = true) {
        $mform = $this->_form;
        static $anonGroups = 1;

        if (0 == strlen($name)) {
            $name = 'qf_group_' . $anonGroups++;
            $appendName = false;
        }
        $group =& $mform->createElement('group', $name, $groupLabel, $elements, $separator, $appendName);
        return $group;
    }

    public function set_data($default_values) {
        if (is_object($default_values)) {
            $default_values = (array)$default_values;
        }

        if (!isset($default_values['maximumattendees']) || $default_values['maximumattendees'] == -1) {
            $default_values['unlimitedattendees'] = true;
        } else {
            $default_values['unlimitedattendees'] = false;
        }

        if (!empty($default_values['classstarttime'])) {
            $default_values['classstarttime'] = $this->shift_timestamp_to_timezone(
                    $default_values['classstarttime'],
                    empty($default_values['usedtimezone']) ? date_default_timezone_get() : $default_values['usedtimezone']
            );
        }
        if (!empty($default_values['classendtime'])) {
            $default_values['classendtime'] = $this->shift_timestamp_to_timezone(
                    $default_values['classendtime'],
                    empty($default_values['usedtimezone']) ? date_default_timezone_get() : $default_values['usedtimezone']
            );
        }
        // Date based on timezone
        // Output
        // Parse out timestamp

        parent::set_data($default_values);
    }

    private function shift_timestamp_to_timezone($timestamp, $timezone) {
        $timezone = new \DateTimeZone($timezone);
        $date = new \DateTime();
        $date->setTimestamp($timestamp);
        $date->setTimezone($timezone);
        $shifteddate = \DateTime::createFromFormat('Y-m-d H:i', $date->format('Y-m-d H:i'));
        return $shifteddate->getTimestamp();
    }

    public function definition_after_data() {
        global $DB;
        $mform = $this->_form;
        if (!empty($this->_customdata->id)) {
            // Check for attended enrolments and unset duration/time fields so they are not updated.
            $taps = new \mod_tapsenrol\taps();
            list($insql, $params) = $DB->get_in_or_equal($taps->get_statuses('attended'), SQL_PARAMS_NAMED, 'status');
            $sql = "SELECT COUNT(id)
                  FROM {tapsenrol_class_enrolments}
                  WHERE
                    classid = :classid
                    AND (archived = 0 OR archived IS NULL)
                    AND {$DB->sql_compare_text('bookingstatus')} {$insql}";

            $params['classid'] = $this->_customdata->id;
            $this->hasattendedenrolments = $DB->count_records_sql($sql, $params);
            if ($this->hasattendedenrolments) {
                $elements = [
                        'classstarttime',
                        'classstarttimegroup',
                        'classendtime',
                        'classendtimegroup',
                        'usedtimezone'
                ];
                if (isset($this->_customdata->classtype)
                        && $this->_customdata->classtype == enrolclass::TYPE_ELEARNING
                        && empty($this->_customdata->classendtime)
                ) {
                    // Do not freeze class end time for self paced classes if not already set.
                    $elements = array_diff($elements, ['classendtime', 'classendtimegroup']);
                }
                foreach ($elements as $element) {
                    if ($mform->elementExists($element)) {
                        $mform->freeze($element);
                    }
                }
                if ($mform->elementExists('classstatus')) {
                    // As can be in this form for self paced!
                    // We disable for consistency with disabling single_select.
                    $mform->getElement('classstatus')->updateAttributes('disabled="disabled"');
                }
            }
        }
    }

    /**
     * @param $classid
     * @return cmform_class
     */
    public static function get_form_instance($class) {
        if ($class->classtype == \mod_tapsenrol\enrolclass::TYPE_ELEARNING) {
            $form = new cmform_class_selfpaced(null, $class);
        } else if ($class->classtype == \mod_tapsenrol\enrolclass::TYPE_CLASSROOM && $class->classstatus == self::CLASS_STATUS_PLANNED) {
            $form = new cmform_class_scheduled_planned(null, $class);
        } else if ($class->classtype == \mod_tapsenrol\enrolclass::TYPE_CLASSROOM && $class->classstatus == self::CLASS_STATUS_NORMAL) {
            $form = new cmform_class_scheduled_normal(null, $class);
        } else if ($class->classtype == false && $class->classstatus == false) {
            $form = new cmform_class_selfpaced(null, $class);
        }

        return $form;
    }

    public $alertrequired = false;
    public $classid = null;

    /**
     * Store the date passed from our current form
     *
     * @param object $data formdata
     */
    public function store_data($data) {
        global $DB;

        $taps = new taps();

        $course = get_course($data->courseid);
        $data->coursename = $course->fullname;
        if (!isset($data->enrolmentenddateenabled)) {
            $data->enrolmentenddate = 0;
        }
        if (!isset($data->classhidden)) {
            $data->classhidden = 0;
        }
        if ($data->classdurationunitscode !== "0") {
            $tapsdurationunits = $taps->get_durationunitscode();
            $data->classdurationunits = $tapsdurationunits[$data->classdurationunitscode];
        }
        if ($data->classtype == \mod_tapsenrol\enrolclass::TYPE_CLASSROOM && $data->classstatus == self::CLASS_STATUS_PLANNED) {
            if (!isset($data->classstarttimeenabled)) {
                $data->classstarttime = 0;
                $data->classstartdate = 0;
            }
            if (!isset($data->classendtimeenabled)) {
                $data->classendtime = 0;
                $data->classenddate = 0;
            }
        }
        if ($data->classtype == \mod_tapsenrol\enrolclass::TYPE_ELEARNING) {
            if (!isset($data->classendtimeenabled)) {
                $data->classendtime = 0;
                $data->classenddate = 0;
            }
        }
        if (!empty($data->unlimitedattendees)) {
            $data->maximumattendees = -1;
        }

        $update = !empty($data->id);

        if ($update && $data->id > 0) {
            // Check for attended enrolments and unset duration/time fields so they are not updated.
            list($insql, $params) = $DB->get_in_or_equal($taps->get_statuses('attended'), SQL_PARAMS_NAMED, 'status');
            $sql = "SELECT COUNT(id)
                  FROM {tapsenrol_class_enrolments}
                  WHERE
                    classid = :classid
                    AND (archived = 0 OR archived IS NULL)
                    AND {$DB->sql_compare_text('bookingstatus')} {$insql}";

            $params['classid'] = $data->id;
            $hasattendedenrolments = $DB->count_records_sql($sql, $params);
            if (!empty($hasattendedenrolments)) {
                unset($data->classtype);
                unset($data->classstatus);
                unset($data->classstarttime);
                unset($data->classstartdate);
                unset($data->usedtimezone);
                // Check/unset classendtime later as need to check if can be edited or not once old record is loaded.
            }
        }
        $oldfields = [];

        $record = \mod_tapsenrol\enrolclass::fetch(['id' => $data->id]);
        if ($record) {
            if (!empty($hasattendedenrolments) && $record->classtype == enrolclass::TYPE_ELEARNING && $record->classendtime > 0) {
                unset($data->classendtime);
                unset($data->classenddate);
            }
            foreach ($data as $key => $value) {
                if (isset($record->$key) && $record->$key != $value) {
                    $oldfields[$key] = $record->$key;
                }
                $record->$key = $value;
            }
            $record->timemodified = time();
            $DB->update_record('local_taps_class', $record);
        } else {
            $data->timemodified = time();
            $data->id = $DB->insert_record('local_taps_class', $data);
        }
        $this->classid = $data->id;

        if ($record) {
            // Reset required unset data from DB record;
            $data->classtype = $record->classtype;
            $data->classstatus = $record->classstatus;
            $data->classstarttime = $record->classstarttime;
        }

        if ($data->classtype == \mod_tapsenrol\enrolclass::TYPE_CLASSROOM && $data->classstatus == self::CLASS_STATUS_NORMAL &&
                $data->classstarttime > time()
        ) {
            // Any 'placed' enrolments?
            $statuses = array_merge($taps->get_statuses('placed'));
            list($insql, $inparams) = $DB->get_in_or_equal($statuses, SQL_PARAMS_NAMED, 'status');
            $sql = "SELECT COUNT(*)
                          FROM {tapsenrol_class_enrolments}
                          WHERE classid = :classid AND {$DB->sql_compare_text('bookingstatus')} {$insql}";
            $inparams['classid'] = $data->id;
            if ($DB->count_records_sql($sql, $inparams)) {
                $this->alertrequired = true;
            }
        }

        $eventdata = [
                'objectid' => $data->id,
                'other'    => ['classid' => $data->id, 'oldfields' => $oldfields]
        ];
        if (!isset($data->id)) {
            $event = class_created::create($eventdata);
        } else {
            $event = class_updated::create($eventdata);
        }
        $event->trigger();

        return true;
    }
}