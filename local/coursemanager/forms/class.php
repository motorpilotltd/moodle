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

/**
 *
 * @package     local_coursemanager
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class cmform_class extends moodleform {
    public function definition() {
        $data = $this->_customdata;
        $mform = $this->_form;

        $mform->addElement('hidden', 'page', 'class');
        $mform->setType('page', PARAM_TEXT);
        $mform->addElement('hidden', 'disabletrick', 1);
        $mform->setType('disabletrick', PARAM_INT);
        $mform->addElement('hidden', 'edit', 1);
        $mform->settype('edit', PARAM_INT);

        $this->add_element("start", "hidden", PARAM_INT);
        $this->add_element("cmcourse", "hidden", PARAM_INT);
        $this->add_element("cmclass", "hidden", PARAM_INT);
        $this->add_element("courseid", "hidden", PARAM_INT);
        $this->add_element("coursename", "hidden", PARAM_INT);
        $this->add_element("classid", "hidden", PARAM_INT);
        $this->add_element("id", "hidden", PARAM_INT);

        $this->add_element("coursenamedisplay", "text", PARAM_TEXT);

        $this->add_element("classname", "text", PARAM_TEXT, null, null, true);
        
        $this->add_element("classduration", "text", PARAM_FLOAT);
        $taps = new \local_taps\taps();
        $durationunits = $taps->get_durationunitscode();
        array_shift($durationunits);
        array_unshift($durationunits, get_string('form:course:getdurationunits', 'local_coursemanager'));

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

        $mform->hardFreeze('coursenamedisplay');
        
        $mform->addRule('classname', get_string('required', 'local_coursemanager'), 'required', null, 'client');

        if ($data->id > 0) {
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
            $availablefromgroup=array();
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
            $mform->addHelpButton($fieldname, 'form:class:' . $fieldname, 'local_coursemanager');
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
            $mdltz = core_date::get_list_of_timezones($USER->timezone, true);
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
        // $errors['somefield'] = $this->str('someerror');
        if (isset($data['price'])) {
            if (strpos($data['price'], ",")) {
                $errors['price'] = $this->str('priceerror'); 
            }
        }
        if ($data['classtype'] == "0") {
            $errors['classtype'] = get_string('required', 'local_coursemanager');
        }
        if ($data['usedtimezone'] == "0") {
            $errors['usedtimezone'] = get_string('required', 'local_coursemanager');
        }
        if (!isset($data['cmcourse'])) {
            $errors['classname'] = get_string('formerror', 'local_coursemanager');
        } 
        $course = $DB->get_record('local_taps_course', array('id' => $data['cmcourse']));
        $sql = 'SELECT id FROM {local_taps_class}
                 WHERE courseid = :cmcourse
                   AND LOWER(classname) = LOWER(:classname)
                   AND NOT classid  = :classid';
        $dupes = $DB->get_records_sql($sql, array('cmcourse' => $course->courseid, 'classname' => $data['classname'], 'classid' => $data['classid']));
        if (count($dupes) > 0) {
            $errors['classname'] =  get_string('duplicateclassname', 'local_coursemanager');
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
            $timezone = new DateTimeZone($data->usedtimezone);
        } catch (Exception $e) {
            $timezone = new DateTimeZone(date_default_timezone_get());
        }
        if (!empty($data->classstarttime)) {
            $classstarttimestring = gmdate('Y-m-d H:i', $data->classstarttime);
            $classstartdatestring = gmdate('Y-m-d 00:00:00', $data->classstarttime);

            $classstarttime = new DateTime($classstarttimestring, $timezone);
            $data->classstarttime = $classstarttime->getTimestamp();

            $classstartdate = new DateTime($classstartdatestring, $timezone);
            $data->classstartdate = $classstartdate->getTimestamp();
        }
        if (!empty($data->classendtime)) {
            $classendtimestring = gmdate('Y-m-d H:i', $data->classendtime);
            $classenddatestring = gmdate('Y-m-d 23:59:59', $data->classendtime);

            $classendtime = new DateTime($classendtimestring, $timezone);
            $data->classendtime = $classendtime->getTimestamp();

            $classenddate = new DateTime($classenddatestring, $timezone);
            $data->classenddate = $classenddate->getTimestamp();
        }
        if (!empty($data->enrolmentstartdate)) {
            $enrolmentstartdatestring = gmdate('Y-m-d 00:00:00', $data->enrolmentstartdate);
            $enrolmentstartdate = new DateTime($enrolmentstartdatestring, $timezone);
            $data->enrolmentstartdate = $enrolmentstartdate->getTimestamp();
        }
        if (!empty($data->enrolmentenddate)) {
            $enrolmentenddatestring = gmdate('Y-m-d 23:59:59', $data->enrolmentenddate);
            $enrolmentenddate = new DateTime($enrolmentenddatestring, $timezone);
            $data->enrolmentenddate = $enrolmentenddate->getTimestamp();
        }
        return $data;
    }

    public function str($string) {
        return get_string('form:class:' . $string, 'local_coursemanager');
    }

    public function optional_time_selector($fieldname) {
        $mform = $this->_form;
        $availablefromgroup=array();
        // Force UTC for incoming timestamps - converted in get_data() based on chosen timezone.
        $availablefromgroup[] =& $mform->createElement('date_time_selector', $fieldname, '', ['timezone' => 'UTC', 'optional' => false]);
        $availablefromgroup[] =& $mform->createElement('checkbox', $fieldname . 'enabled', '', get_string('enable'));
        $group  = $this->createGroup($availablefromgroup, $fieldname . 'group', $this->str($fieldname), ' ', false);
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

    private function &createGroup($elements, $name=null, $groupLabel='', $separator=null, $appendName = true)
    {
        $mform = $this->_form;
        static $anonGroups = 1;

        if (0 == strlen($name)) {
            $name       = 'qf_group_' . $anonGroups++;
            $appendName = false;
        }
        $group =& $mform->createElement('group', $name, $groupLabel, $elements, $separator, $appendName);
        return $group;
    }

    public function definition_after_data() {
        global $DB;
        $mform = $this->_form;
        $classid = $mform->exportValue('classid');
        $id = $mform->exportValue('id');
        if ($id > 0 && $classid > 0) {
            // Check for attended enrolments and unset duration/time fields so they are not updated.
            $taps = new \local_taps\taps();
            list($insql, $params) = $DB->get_in_or_equal($taps->get_statuses('attended'), SQL_PARAMS_NAMED, 'status');
            $sql = "SELECT COUNT(id)
                  FROM {local_taps_enrolment}
                  WHERE
                    classid = :classid
                    AND (archived = 0 OR archived IS NULL)
                    AND {$DB->sql_compare_text('bookingstatus')} {$insql}";

            $params['classid'] = $classid;
            if ($DB->count_records_sql($sql, $params)) {
                $elements = [
                    'classstarttime',
                    'classstarttimegroup',
                    'classendtime',
                    'classendtimegroup',
                    'usedtimezone'
                ];
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
}