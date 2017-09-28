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
 * The local_taps add course form.
 *
 * @package    local_taps
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');

/**
 * The local_taps add course form class.
 *
 * @package    local_taps
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_taps_addcourse_form extends moodleform {

    /** @var array TAPS courses. */
    protected $_tapscourses = array();

    /**
     * Form definition.
     *
     * @return void
     */
    public function definition() {
        global $DB;

        $mform =& $this->_form;

        $category = $this->_customdata['category'];
        $tapscourseid = $this->_customdata['courseid'];

        $mform->addElement('hidden', 'category', null);
        $mform->setType('category', PARAM_INT);
        $mform->setConstant('category', $category->id);

        $mform->addElement('header', 'coursedetails', get_string('coursedetails', 'local_taps', get_string('course')));

        $mform->addElement('text', 'shortname', get_string('shortnamecourse', 'local_taps'), 'maxlength="30" size="50"');
        $mform->addHelpButton('shortname', 'shortnamecourse', 'local_taps');
        $mform->setType('shortname', PARAM_TEXT);

        $mform->addElement('header', 'activities', get_string('activities', 'local_taps'));

        $filemanageroptions = array();
        $filemanageroptions['return_types'] = 3;
        $filemanageroptions['accepted_types'] = array('web_image');
        $filemanageroptions['maxbytes'] = 0;
        $filemanageroptions['maxfiles'] = 1;
        $filemanageroptions['mainfile'] = false;

        $mform->addElement('filemanager', 'advertblockimage', get_string('advertblockimage', 'arupadvert'), null, $filemanageroptions);
        $mform->addHelpButton('advertblockimage', 'advertblockimage', 'arupadvert');

        $arupadvertalreadyused = $DB->get_records_menu('arupadvertdatatype_taps', array(), '', 'tapscourseid as id, tapscourseid');
        $tapsenrolalreadyused = $DB->get_records_menu('tapsenrol', array(), '', 'tapscourse as id, tapscourse');
        $tapscompletionalreadyused = $DB->get_records_menu('tapscompletion', array(), '', 'tapscourse as id, tapscourse');
        $alreadyused = $arupadvertalreadyused + $tapsenrolalreadyused + $tapscompletionalreadyused;

        $select = '';
        $params = array();
        if (!empty($alreadyused)) {
            list($in, $params) = $DB->get_in_or_equal(
                    $alreadyused,
                    SQL_PARAMS_NAMED,
                    'alreadyused',
                    false);
            $select = 'courseid '.$in;
        }
        $tapscourses = $DB->get_records_select('local_taps_course', $select, $params, '', 'courseid, courseregion, coursename, coursecode');
        foreach ($tapscourses as $tapscourse) {
            $coursecode = $tapscourse->coursecode ? ' ['.$tapscourse->coursecode.']' : '';
            $this->_tapscourses[$tapscourse->courseid] = $tapscourse->courseregion .
                ' - ' .
                $tapscourse->coursename .
                $coursecode;
        }
        natcasesort($this->_tapscourses);

        $selectoptions = array('' => get_string('choosedots')) + $this->_tapscourses;
        $mform->addElement('select', 'tapscourse', get_string('tapscourse', 'local_taps'), $selectoptions, array('style' => 'max-width:100%'));
        $mform->setDefault('tapscourse', $tapscourseid);
        $mform->addRule('tapscourse', null, 'required', null, 'client');

        $options = array('' => get_string('choosedots'));
        $iws = $DB->get_records_menu('tapsenrol_iw', null, 'name ASC', 'id, name');
        $mform->addElement('select', 'internalworkflowid', get_string('internalworkflow', 'tapsenrol'), $options + $iws);
        $mform->addRule('internalworkflowid', null, 'required', null, 'client');
        $mform->addHelpButton('internalworkflowid', 'internalworkflow', 'tapsenrol');

        // Alternative word.
        $mform->addElement('text', 'altword', get_string('altword', 'arupadvert', core_text::strtolower(get_string('course'))), array('size' => '64'));
        $mform->setType('altword', PARAM_TEXT);
        $mform->addRule('altword', null, 'maxlength', 255, 'client', false, false);

        $mform->addElement('header', 'enrolment', get_string('enrolment', 'local_taps'));
        $enrolmentroles = array('' => get_string('choosedots')) + get_default_enrol_roles(context_course::instance(SITEID));
        $mform->addElement('select', 'enrolmentrole', get_string('enrolmentrole', 'local_taps'), $enrolmentroles);
        $mform->addRule('enrolmentrole', null, 'required', null, 'client');
        $mform->addHelpButton('enrolmentrole', 'enrolmentrole', 'local_taps');
        $mform->setDefault('enrolmentrole', get_config('local_taps', 'taps_enrolment_role'));

        $dbregionoptions = $DB->get_records_select_menu('local_regions_reg', 'userselectable = 1', array(), 'name DESC', 'id, name');
        $regionoptions = array(0 => get_string('global', 'local_regions')) + $dbregionoptions;
        $size = min(array(count($regionoptions), 10));
        $regionattributes = array('size' => $size, 'style' => 'min-width:200px');

        $mform->addElement('header', 'catalogue_region_mapping', get_string('catalogue_region_mapping', 'local_taps'));
        $mform->setExpanded('catalogue_region_mapping', true, true);
        $catalogueregion = &$mform->addElement('select', 'catalogueregion', get_string('regions', 'local_regions'), $regionoptions, $regionattributes);
        $catalogueregion->setMultiple(true);
        $cataloguehint = html_writer::tag('div', get_string('overrideregions', 'local_taps'), array('class' => 'felement fselect'));
        $mform->addElement('html', html_writer::tag('div', $cataloguehint, array('class' => 'fitem')));

        $mform->addElement('header', 'enrolment_region_mapping', get_string('enrolment_region_mapping', 'tapsenrol'));
        $mform->setExpanded('enrolment_region_mapping', true, true);
        $enrolmentregion = &$mform->addElement('select', 'enrolmentregion', get_string('regions', 'local_regions'), $regionoptions, $regionattributes);
        $enrolmentregion->setMultiple(true);
        $enrolmenthint = html_writer::tag('div', get_string('overrideregions', 'local_taps'), array('class' => 'felement fselect'));
        $mform->addElement('html', html_writer::tag('div', $enrolmenthint, array('class' => 'fitem')));

        coursemetadata_definition($mform);

        $this->add_action_buttons(true, get_string('addcourse:submit', 'local_taps'));
    }

    /**
     * Form validation.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function validation($data, $files) {
        global $DB;

        $errors = parent::validation($data, $files);

        $foundcourses = $DB->get_records('course', array('shortname' => $data['shortname']));
        if ($foundcourses) {
            if (!empty($data['id'])) {
                unset($foundcourses[$data['id']]);
            }
            if (!empty($foundcourses)) {
                foreach ($foundcourses as $foundcourse) {
                    $foundcoursenames[] = $foundcourse->fullname;
                }
                $foundcoursenamestring = implode(',', $foundcoursenames);
                $errors['shortname'] = get_string('shortnametaken', '', $foundcoursenamestring);
            }
        }

        if (!isset($data['tapscourse']) || !array_key_exists($data['tapscourse'], $this->_tapscourses)) {
            $errors['tapscourse'] = get_string('invalidtapscourse', 'local_taps');
        }

        return $errors;
    }

    /**
     * Get data override.
     *
     * @return mixed
     */
    public function get_data() {
        $data = parent::get_data();

        if (!$data) {
            return false;
        }

        return $data;
    }
}