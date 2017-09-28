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
 * The local_coursemetadata base field.
 *
 * @package    local_coursemetadata
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursemetadata;

defined('MOODLE_INTERNAL') || die();

/**
 * The local_coursemetadata base field class.
 *
 * @package    local_coursemetadata
 * @since      Moodle 3.0
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class field_base {

    // These 2 variables are really what we're interested in.
    // Everything else can be extracted from them.
    /** @var int */
    public $fieldid;

    /** @var int */
    public $userid;

    /** @var stdClass */
    public $field;

    /** @var string */
    public $inputname;

    /** @var mixed */
    public $data;

    /** @var string */
    public $dataformat;

    /** @var bool */
    protected $_canupdatecourse = false;

    /** @var bool */
    protected $_restrictededit = false;

    /** @var bool */
    protected $_accessall = false;

    /**
     * Constructor method.
     *
     * @param int $fieldid id of the coursemetadata from the course_info_field table
     * @param int $courseid id of the course for which we are displaying data
     */
    public function __construct($fieldid = 0, $courseid = 0) {
        $this->set_fieldid($fieldid);
        $this->set_courseid($courseid);

        $context = $courseid ? \context_course::instance($courseid) : \context_system::instance();
        $this->_canupdatecourse = has_any_capability(array('moodle/course:create', 'moodle/course:update'), $context);
        $this->_restrictededit = has_capability('local/coursemetadata:restricted', $context);
        $this->_accessall = has_capability('local/coursemetadata:accessall', $context);

        $this->load_data();
    }

    /**
     * Abstract method: Adds the coursemetadata field to the moodle form class.
     *
     * @param moodleform $mform instance of the moodleform class
     */
    abstract public function edit_field_add($mform);

    /**
     * Display the data for this field.
     *
     * @return string
     */
    public function display_data() {
        $options = new \stdClass();
        $options->para = false;
        return format_text($this->data, FORMAT_MOODLE, $options);
    }

    /**
     * Print out the form field in the edit course page.
     *
     * @param moodleform $mform instance of the moodleform class
     * $return bool
     */
    public function edit_field($mform) {
        if (($this->field->visible == COURSEMETADATA_VISIBLE_ALL || $this->_canupdatecourse)
            && ($this->field->restricted == 0 || $this->_restrictededit)) {

            $this->edit_field_add($mform);
            $this->edit_field_set_default($mform);
            $this->edit_field_set_required($mform);
            return true;
        }
        return false;
    }

    /**
     * Tweaks the edit form.
     *
     * @param moodleform $mform instance of the moodleform class
     * $return bool
     */
    public function edit_after_data($mform) {
        if (($this->field->visible == COURSEMETADATA_VISIBLE_ALL || $this->_canupdatecourse)
            && ($this->field->restricted == 0 || $this->_restrictededit)) {
            $this->edit_field_set_locked($mform);
            return true;
        }
        return false;
    }

    /**
     * Saves the data coming from form.
     *
     * @param stdClass $coursenew data coming from the form
     * @return mixed returns data id if success of db insert/update, false on fail, 0 if not permitted
     */
    public function edit_save_data($coursenew) {
        global $DB;

        if (!isset($coursenew->{$this->inputname})) {
            // Field not present in form, probably locked and invisible - skip it.
            return;
        }

        $data = new \stdClass();

        $coursenew->{$this->inputname} = $this->edit_save_data_preprocess($coursenew->{$this->inputname}, $data);

        $data->course  = $coursenew->id;
        $data->fieldid = $this->field->id;
        $data->data    = $coursenew->{$this->inputname};

        if ($dataid = $DB->get_field('coursemetadata_info_data', 'id', array('course' => $data->course, 'fieldid' => $data->fieldid))) {
            $data->id = $dataid;
            $DB->update_record('coursemetadata_info_data', $data);
        } else {
            $DB->insert_record('coursemetadata_info_data', $data);
        }
    }

    /**
     * Validate the form field from edit course page.
     *
     * @param stdClass $coursenew
     * @return string contains error message otherwise NULL
     **/
    public function edit_validate_field($coursenew) {
        global $DB;

        $errors = array();
        // Get input value.
        if (isset($coursenew->{$this->inputname})) {
            if (is_array($coursenew->{$this->inputname}) && isset($coursenew->{$this->inputname}['text'])) {
                $value = $coursenew->{$this->inputname}['text'];
            } else {
                $value = $coursenew->{$this->inputname};
            }
        } else {
            $value = '';
        }

        // Check for uniqueness of data if required.
        if ($this->is_unique() && (($value !== '') || $this->is_required())) {
            $data = $DB->get_records_sql('
                    SELECT id, course
                      FROM {coursemetadata_info_data}
                     WHERE fieldid = ?
                       AND ' . $DB->sql_compare_text('data', 255) . ' = ' . $DB->sql_compare_text('?', 255),
                    array($this->field->id, $value));
            if ($data) {
                $existing = false;
                foreach ($data as $v) {
                    if ($v->course == $coursenew->id) {
                        $existing = true;
                        break;
                    }
                }
                if (!$existing) {
                    $errors[$this->inputname] = get_string('valuealreadyused');
                }
            }
        }
        return $errors;
    }

    /**
     * Sets the default data for the field in the form object.
     *
     * @param moodleform $mform instance of the moodleform class
     */
    public function edit_field_set_default($mform) {
        if (!empty($this->field->defaultdata)) {
            $mform->setDefault($this->inputname, $this->field->defaultdata);
        }
    }

    /**
     * Sets the required flag for the field in the form object.
     *
     * @param moodleform $mform instance of the moodleform class
     */
    public function edit_field_set_required($mform) {
        if ($this->is_required()) {
            $mform->addRule($this->inputname, get_string('required'), 'required', null, 'client');
        }
    }

    /**
     * HardFreeze the field if locked.
     *
     * @param moodleform $mform instance of the moodleform class
     */
    public function edit_field_set_locked($mform) {
        if (!$mform->elementExists($this->inputname)) {
            return;
        }
        if ($this->is_locked() and !$this->_canupdatecourse) {
            $mform->hardFreeze($this->inputname);
            $mform->setConstant($this->inputname, $this->data);
        }
    }

    /**
     * Hook for child classess to process the data before it gets saved in database.
     *
     * @param stdClass $data
     * @param stdClass $datarecord The object that will be used to save the record
     * @return mixed
     */
    public function edit_save_data_preprocess($data, $datarecord) {
        return $data;
    }

    /**
     * Loads a course object with data for this field ready for editing.
     *
     * @param stdClass $course a course object
     */
    public function edit_load_course_data($course) {
        if ($this->data !== null) {
            $course->{$this->inputname} = $this->data;
        }
    }

    /**
     * Check if the field data should be loaded into the course object.
     *
     * By default it is, but for field types where the data may be potentially
     * large, the child class should override this and return false.
     *
     * @return bool
     */
    public function is_course_object_data() {
        return true;
    }


    /**
     * Any extra deletion tasks required when a field is removed.
     */
    public function delete_field() {
        // Override in child if extra deletion functionality required.
    }

    /**
     * Accessor method: set the courseid for this instance.
     *
     * @param int $courseid id from the course table
     */
    public function set_courseid($courseid) {
        $this->courseid = $courseid;
    }

    /**
     * Accessor method: set the fieldid for this instance.
     *
     * @param int $fieldid id from the course_info_field table
     */
    public function set_fieldid($fieldid) {
        $this->fieldid = $fieldid;
    }

    /**
     * Accessor method: Load the field record and course data associated with the object's fieldid and courseid.
     */
    public function load_data() {
        global $DB;

        // Load the field object.
        if (($this->fieldid == 0) or (!($field = $DB->get_record('coursemetadata_info_field', array('id' => $this->fieldid))))) {
            $this->field = null;
            $this->inputname = '';
        } else {
            $this->field = $field;
            $this->inputname = 'coursemetadata_field_'.$field->shortname;
        }

        if (!empty($this->field)) {
            if ($data = $DB->get_record('coursemetadata_info_data', array('course' => $this->courseid, 'fieldid' => $this->fieldid), 'data, dataformat')) {
                $this->data = $data->data;
                $this->dataformat = $data->dataformat;
            } else {
                $this->data = $this->field->defaultdata;
                $this->dataformat = FORMAT_HTML;
            }
        } else {
            $this->data = null;
        }
    }

    /**
     * Check if the field data is visible to the current user.
     *
     * @return bool
     */
    public function is_visible() {
        switch ($this->field->visible) {
            case COURSEMETADATA_VISIBLE_ALL:
                return true;
            case COURSEMETADATA_VISIBLE_PRIVATE:
            default:
                return $this->_canupdatecourse;
        }
    }

    /**
     * Check if the field data is considered empty.
     *
     * @return bool
     */
    public function is_empty() {
        return (($this->data != '0') and empty($this->data));
    }

    /**
     * Check if the field is required on the edit course page.
     *
     * @return bool
     */
    public function is_required() {
        return (boolean)$this->field->required;
    }

    /**
     * Check if the field is locked on the edit course page.
     *
     * @return bool
     */
    public function is_locked() {
        return (boolean)$this->field->locked;
    }

    /**
     * Check if the field data should be unique.
     *
     * @return bool
     */
    public function is_unique() {
        return (boolean)$this->field->forceunique;
    }


}
