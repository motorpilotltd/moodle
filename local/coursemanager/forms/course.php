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

class cmform_course extends moodleform {
    public function definition() {
        $data = $this->_customdata;
        $mform = $this->_form;

        $mform->addElement('hidden', 'page', 'course');
        $mform->setType('page', PARAM_TEXT);

        $tab = required_param('tab', PARAM_INT);
        $mform->addElement('hidden', 'tab', $tab);
        $mform->settype('tab', PARAM_INT);

        $mform->addElement('hidden', 'edit', 1);
        $mform->settype('edit', PARAM_INT);

        $this->add_element("start", "hidden", PARAM_INT);
        $this->add_element("id", "hidden", PARAM_INT);
        $this->add_element("cmcourse", "hidden", PARAM_INT);
        // To be set (increment) on creation then not updateable (to match original TAPS).
        // See \local_taps\taps for use of do...while to avoid possible clashes.
        $this->add_element("courseid", "hidden", PARAM_INT);
        // Should be unique.
        // Needs help
        if ($tab == 1) {
            $this->add_element("coursecode", "text", PARAM_TEXT, null, null, true);
            $this->add_element("coursename", "text", PARAM_TEXT);
            // Force UTC for incoming timestamps - converted in get_data() based on chosen timezone.
            $this->add_element("startdate", "date_selector", null, ['timezone' => 'UTC']);
            // Needs to be able to be zero (never ends).
            // Disabled by default. Needs tickbox for disabled.
            // UTC forced in custom element addition, setting option here would cause issues.
            $this->add_element("enddate", "date_selector_optional");
            $this->add_element("courseregion", "select", null, $this->get_regions());
            // Needs help
            $this->add_element("duration", "text", PARAM_FLOAT);
            // Duration units and durationunitscode can be set via single dropdown.
            // See variables in \local_taps\taps
            $taps = new \local_taps\taps();
            $durationunits = $taps->get_durationunitscode();
            array_shift($durationunits);
            array_unshift($durationunits, get_string('form:course:getdurationunits', 'local_coursemanager'));

            $this->add_element("durationunits", "select", null, $durationunits);
            $this->add_element("onelinedescription", "textarea", PARAM_RAW, null, null, true);

            // Required fields
            $mform->addRule('coursecode', get_string('required', 'local_coursemanager'), 'required', null, 'client');
            $mform->addRule('coursename', get_string('required', 'local_coursemanager'), 'required', null, 'client');
            $mform->addRule('courseregion', get_string('required', 'local_coursemanager'), 'required', null, 'client');
            $mform->addRule('onelinedescription', get_string('required', 'local_coursemanager'), 'required', null, 'client');
        }

        if ($tab == 2) {
            $this->add_element("coursecode", "hidden", PARAM_TEXT);
            $this->add_element("coursedescription", "editor", PARAM_RAW);
            $this->add_element("courseobjectives", "editor", PARAM_RAW);
            // Needs help
            $this->add_element("courseaudience", "editor", PARAM_RAW, null, null, true);

            // Needs a help field. Comma separated list.
            $this->add_element("keywords", "textarea", PARAM_TEXT, 'rows="5" cols="30"', null, true);
        }

        if ($tab == 3) {
            $this->add_element("coursecode", "hidden", PARAM_TEXT);
            // Will need to check options for this. See variables taps plugin, keep them central in taps. Linked to accreditationgivendate and futurereviewdate. 
            $this->add_element("globallearningstandards", "advcheckbox");
            // Needs to be able to be null/zero (not accredited). add tickbox. Only available when globallearningstandards is selected.
            // Force UTC for incoming timestamps - converted in get_data() based on chosen timezone.
            $this->add_element("accreditationgivendate", "date_selector", null, ['timezone' => 'UTC']);
            // Not required. Can be removed.
            // Needs to be able to be null/zero (no review required).
            // Only available when globallearningstandards is selected.
            // Force UTC for incoming timestamps - converted in get_data() based on chosen timezone.
            $this->add_element("futurereviewdate", "date_selector", null, ['timezone' => 'UTC']);
            // Leave visible.
            $this->add_element("jobnumber", "text", PARAM_TEXT);
            $mform->disabledIf('accreditationgivendate', 'globallearningstandards', 'eq', 0);
            $mform->disabledIf('futurereviewdate', 'globallearningstandards', 'eq', 0);
        }

        if ($data->id > 0) {
            $this->add_action_buttons(true, $this->str('updatecourse'));
        } else {
            if ($tab == 1 || $tab == 2) {
                $this->add_action_buttons(true, $this->str('nextstep'));
            }
            if ($tab == 3) {
                $this->add_action_buttons(true, $this->str('savecourse'));
            }
        }
    }

    /**
     * Shorthand version of adding a field to the form
     * 
     * @param string $fieldname. Unique name for field.
     * @param string $fieldtype. Type of field must be an existing mform field type.
     * @param constant $settype. Constant types like PARAM_TEXT etc.
     * @param array $option. Some fieldtypes require options, like a dropdown.
     * @param something $default. Default field value.
     * @param string $help. Helptext to add to field.
     * @throws Exception
     */
    function add_element($fieldname, $fieldtype, $settype = null, $options = null, $default = null, $help = false) {
        $mform = $this->_form;
        if ($fieldtype == 'editor') {
            if ($help) {
                $mform->addElement('static', $fieldname . 'help', '', $this->str($fieldname . '_help'));
            }
            $context = \context_system::instance();
            $textfieldoptions = array('trusttext' => true, 'subdirs' => false, 'maxfiles' => 55,
                          'maxbytes' => 0, 'context' => $context);
            $mform->addElement($fieldtype, $fieldname . '_editor', $this->str($fieldname), null, $textfieldoptions);
        } else if ($fieldtype == 'date_selector_optional') {
            $fieldtype = 'date_selector';
            $availablefromgroup=array();
            // Force UTC for all 'date' timestamps.
            $availablefromgroup[] =& $mform->createElement('date_selector', $fieldname, '', ['timezone' => 'UTC']);
            $availablefromgroup[] =& $mform->createElement('checkbox', $fieldname . 'enabled', '', get_string('enable'));
            $mform->addGroup($availablefromgroup, $fieldname . 'group', $this->str($fieldname), ' ', false);
            $mform->disabledIf($fieldname . 'group', $fieldname . 'enabled');
            return '';
        } else if ($fieldtype == 'advcheckbox') {
            $mform->addElement($fieldtype, $fieldname, $this->str($fieldname), '', array('group' => 1), array(0, 1));
        } else if ($options) {
            $mform->addElement($fieldtype, $fieldname, $this->str($fieldname), $options);
        } else {
            $mform->addElement($fieldtype, $fieldname, $this->str($fieldname));
        }
        if ($help) {
            if ($fieldtype != 'editor') {
                $mform->addHelpButton($fieldname, 'form:course:' . $fieldname, 'local_coursemanager');
            }
        }
        if ($settype) {
            $mform->setType($fieldname, $settype);
        }
        if ($default) {
            $mform->setDefault($fieldname, $default);
        }
    }

    /**
     * Validate the form
     */
    function validation($data, $files) {
        global $DB;
        $errors = parent::validation($data, $files);

        if (isset($data['courseregion']) && $data['courseregion'] == "0") {
            $errors['courseregion'] = get_string('required', 'local_coursemanager');
        }
        $sql = 'SELECT id FROM {local_taps_course}
                 WHERE LOWER(coursecode) = LOWER(:coursecode)
                   AND NOT courseid  = :courseid';
        $dupes = $DB->get_records_sql($sql, array('coursecode' => $data['coursecode'], 'courseid' => $data['courseid']));
        if (count($dupes) > 0) {
            $errors['coursecode'] =  get_string('duplicatecoursecode', 'local_coursemanager');
        }

        return $errors;
    }

    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return $data;
        }
        if (empty($data->globallearningstandards)) {
            $data->globallearningstandards = null;
            $data->accreditationgivendate = null;
            $data->futurereviewdate = null;
        } else {
            $data->globallearningstandards = 'Meets Global Learning Standards';
        }
        // Need to offset end date to end of day.
        if (!empty($data->enddate)) {
            $data->enddate = $data->enddate + (23*60*60) + (59*60) + 59;
        }
        return $data;
    }

    /**
     * Get regions from DB
     */
    function get_regions() {
        global $DB;
        $regions = array('0' => get_string('form:course:selectregion', 'local_coursemanager'));
        $regions['Global'] = 'Global';
        $dbregions = $DB->get_records('local_regions_reg', array('userselectable' => 1));
        foreach ($dbregions as $dbr) {
            $regions[$dbr->name] = $dbr->name;
        }
        return $regions;
    }

    private function str($string) {
        return get_string('form:course:' . $string, 'local_coursemanager');
    }
}