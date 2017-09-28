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
 * Iconsingle coursemetadata field.
 *
 * @package    coursemetadatafield_iconsingle
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class coursemetadata_field_iconsingle.
 *
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class coursemetadata_field_iconsingle extends \local_coursemetadata\field_base {

    /** @var array $options */
    public $options;

    /** @var mixed $datakey */
    public $datakey;

    /**
     * Constructor method.
     *
     * Pulls out the options for the menu from the database and sets the the corresponding key for the data if it exists
     *
     * @param int $fieldid
     * @param int $courseid
     */
    public function __construct($fieldid = 0, $courseid = 0) {
        // First call parent constructor.
        parent::__construct($fieldid, $courseid);

        // Param 1 for menu type is the options.
        if (isset($this->field->param1)) {
            $options = explode("\n", $this->field->param1);
        } else {
            $options = array();
        }
        $this->options = array();
        if (!empty($this->field->required)) {
            $this->options[''] = get_string('choose').'...';
        }
        foreach ($options as $key => $option) {
            $this->options[$option] = format_string($option); // Multilang formatting with filters.
        }

        // Set the data key.
        if ($this->data !== null) {
            $key = $this->data;
            if (isset($this->options[$key]) || ($key = array_search($key, $this->options)) !== false) {
                $this->data = $key;
                $this->datakey = $key;
            }
        }
    }

    /**
     * Create the code snippet for this field instance.
     *
     * Overwrites the base class method.
     *
     * @param moodleform $mform Moodle form instance
     */
    public function edit_field_add($mform) {
        $mform->addElement('select', $this->inputname, format_string($this->field->name), $this->options);
    }

    /**
     * Set the default value for this field instance.
     *
     * Overwrites the base class method.
     *
     * @param moodleform $mform Moodle form instance
     */
    public function edit_field_set_default($mform) {
        $key = $this->field->defaultdata;
        if (isset($this->options[$key]) || ($key = array_search($key, $this->options)) !== false) {
            $defaultkey = $key;
        } else {
            $defaultkey = '';
        }
        $mform->setDefault($this->inputname, $defaultkey);
    }

    /**
     * The data from the form returns the key.
     *
     * This should be converted to the respective option string to be saved in database.
     *
     * Overwrites base class accessor method.
     *
     * @param mixed $data The key returned from the select input in the form
     * @param stdClass $datarecord The object that will be used to save the record
     * @return string
     */
    public function edit_save_data_preprocess($data, $datarecord) {
        return isset($this->options[$data]) ? $this->options[$data] : '';
    }

    /**
     * When passing the course object to the form class for the edit course page we should load the key for the saved data.
     *
     * Overwrites the base class method.
     *
     * @param stdClass $course Course object
     */
    public function edit_load_course_data($course) {
        $course->{$this->inputname} = $this->datakey;
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
        if ($this->is_locked() and !$this->_accessall) {
            $mform->hardFreeze($this->inputname);
            $mform->setConstant($this->inputname, format_string($this->datakey));
        }
    }

    /**
     * Displays the data.
     */
    public function display_data() {
        global $CFG, $PAGE;

        $types = array('.png', '.jpg', '.jpeg', '.gif');
        if ($PAGE->theme->use_svg_icons()) {
            array_unshift($types, '.svg');
        }
        $contextid = context_system::instance()->id;
        $fs = get_file_storage();
        $imagesrc = '';
        $filearea = "icons_{$this->field->id}";
        foreach ($types as $type) {
            if ($fs->file_exists($contextid, 'local_coursemetadata', $filearea, 0, '/', $this->data.$type)) {
                $imagesrc = "{$CFG->wwwroot}/pluginfile.php/{$contextid}/local_coursemetadata/{$filearea}/{$this->data}{$type}";
                break;
            }
        }
        if ($imagesrc) {
            return html_writer::empty_tag(
                'img',
                array(
                    'src' => $imagesrc,
                    'alt' => $this->data,
                    'title' => $this->data,
                    'class' => 'coursemetadata iconsingle'
                )
            );
        } else {
            return html_writer::tag('span', $this->data, array('class' => 'coursemetadata iconsingle'));
        }
    }

    /**
     * Actions on deleting the field.
     */
    public function delete_field() {
        $contextid = context_system::instance()->id;
        $filearea = "icons_{$this->field->id}";

        $fs = get_file_storage();
        $fs->delete_area_files($contextid, 'local_coursemetadata', $filearea, 0);
    }
}


