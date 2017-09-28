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
 * Data type definition for arupadvertdatatype_custom.
 *
 * @package    arupadvertdatatype_custom
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace arupadvertdatatype_custom;

defined('MOODLE_INTERNAL') || die();

/**
 * Class for arupadvertdatatype_custom.
 *
 * @since      Moodle 3.0
 * @package    arupadvertdatatype_taps
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class arupadvertdatatype_custom extends \mod_arupadvert\arupadvertdatatype {

    /** @var string $type */
    public $type = 'custom';
    /** @var bool $purify */
    public $purify = false;

    /**
     * Load data.
     */
    protected function _load_data() {
        global $DB;

        $data = $DB->get_record('arupadvertdatatype_custom', array('arupadvertid' => $this->_arupadvert->id));
        if ($data) {
            $this->accredited = (bool) $data->accredited;
            $this->audience = $data->audience;
            $this->description = $data->description;
            $this->objectives = $data->objectives;
            $this->keywords = $data->keywords;
        }
    }

    /**
     * Form definition.
     *
     * @param moodleform $mform
     * @param stdClass $current
     * @param context $context
     */
    public function mod_form_definition($mform, $current, $context) {

        $altword = empty($this->_arupadvert->altword) ? \core_text::strtolower(get_string('course')) : $this->_arupadvert->altword;

        $mform->addElement('header', 'arupadvertdatatype_custom', get_string('header', 'arupadvertdatatype_custom'));
        $this->_elements[] = 'arupadvertdatatype_custom';

        $options = array(
            '' => get_string('choosedots'),
            0 => get_string('no'),
            1 => get_string('yes')
        );
        $mform->addElement('select', 'accredited', get_string('accredited', 'arupadvert'), $options);
        $mform->addRule('accredited', null, 'required', null, 'client');
        $this->_elements[] = 'accredited';

        $editoroptions = array('maxfiles' => 0, 'noclean' => true, 'context' => $context);

        $mform->addElement('editor', 'description', get_string('description', 'arupadvert', $altword), null, $editoroptions);
        $mform->setType('description', PARAM_RAW); // No XSS prevention here, users must be trusted.
        $this->_elements[] = 'description';

        $mform->addElement('editor', 'objectives', get_string('objectives', 'arupadvert', $altword), null, $editoroptions);
        $mform->setType('objectives', PARAM_RAW); // No XSS prevention here, users must be trusted.
        $this->_elements[] = 'objectives';

        $mform->addElement('editor', 'audience', get_string('audience', 'arupadvert', $altword), null, $editoroptions);
        $mform->setType('audience', PARAM_RAW); // No XSS prevention here, users must be trusted.
        $this->_elements[] = 'audience';

        $mform->addElement('editor', 'keywords', get_string('keywords', 'arupadvertdatatype_custom'), null, $editoroptions);
        $mform->setType('keywords', PARAM_RAW); // No XSS prevention here, users must be trusted.
        $this->_elements[] = 'keywords';
    }

    /**
     * Form element removal.
     *
     * @param moodleform $mform
     */
    public function mod_form_remove_elements($mform) {
        foreach ($this->_elements as $element) {
            if ($mform->elementExists($element)) {
                $mform->removeElement($element);
            }
        }
    }

    /**
     * Set form data.
     *
     * @param array $defaultvalues
     */
    public function mod_form_set_data(&$defaultvalues) {
        global $DB;
        if (isset($defaultvalues['id'])) {
            $current = $DB->get_record('arupadvertdatatype_custom', array('arupadvertid' => $defaultvalues['id']));
            if ($current) {
                $defaultvalues['accredited'] = $current->accredited;
                $defaultvalues['description']['text'] = $current->description;
                $defaultvalues['objectives']['text'] = $current->objectives;
                $defaultvalues['audience']['text'] = $current->audience;
                $defaultvalues['keywords']['text'] = $current->keywords;
            }
        }
    }

    /**
     * Form validation.
     *
     * @param array $data
     * @param array $files
     */
    public function mod_form_validation($data, $files) {
        // None required.
        return array();
    }

    /**
     * Edit instance.
     *
     * @param stdClass $data
     * @param moodleform $mform
     */
    public function edit_instance($data, $mform) {
        global $DB;

        $current = $DB->get_record('arupadvertdatatype_custom', array('arupadvertid' => $data->id));

        if ($current) {
            $current->accredited = $data->accredited;
            $current->audience = $data->audience['text'];
            $current->description = $data->description['text'];
            $current->objectives = $data->objectives['text'];
            $current->keywords = $data->keywords['text'];
            $current->timemodified = time();
            return $DB->update_record('arupadvertdatatype_custom', $current);
        } else {
            $current = new \stdClass();
            $current->arupadvertid = $data->id;
            $current->accredited = $data->accredited;
            $current->audience = $data->audience['text'];
            $current->description = $data->description['text'];
            $current->objectives = $data->objectives['text'];
            $current->keywords = $data->keywords['text'];
            $current->timecreated = time();
            $current->timemodified = $current->timecreated;
            return $DB->insert_record('arupadvertdatatype_custom', $current);
        }

    }

    /**
     * Delete instance.
     *
     * @param int $arupadvertid
     */
    public function delete_instance($arupadvertid) {
        global $DB;
        return $DB->delete_records('arupadvertdatatype_custom', array('arupadvertid' => $arupadvertid));
    }
}