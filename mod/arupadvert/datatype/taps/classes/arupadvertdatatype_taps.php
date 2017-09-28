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
 * Data type definition for arupadvertdatatype_taps.
 *
 * @package    arupadvertdatatype_taps
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace arupadvertdatatype_taps;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/completionlib.php');

global $CFG;

/**
 * Class for arupadvertdatatype_taps.
 *
 * @since      Moodle 3.0
 * @package    arupadvertdatatype_taps
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class arupadvertdatatype_taps extends \mod_arupadvert\arupadvertdatatype {

    /** @var string $type */
    public $type = 'taps';
    /** @var bool $purify */
    public $purify = true;

    /**
     * Load data.
     */
    protected function _load_data() {
        global $DB;

        $sql = <<<EOS
SELECT
    at.*,
    ltc.globallearningstandards, ltc.coursecode, ltc.courseaudience, ltc.coursedescription, ltc.coursename, ltc.courseobjectives, ltc.keywords
FROM
    {arupadvertdatatype_taps} at
JOIN
    {local_taps_course} ltc
    ON ltc.courseid = at.tapscourseid
WHERE
    at.arupadvertid = {$this->_arupadvert->id}
EOS;

        $data = $DB->get_record_sql($sql);
        if ($data) {
            $this->code = $data->coursecode;
            $this->accredited = stristr($data->globallearningstandards, 'meets');
            $this->audience = $data->courseaudience;
            $this->description = $data->coursedescription;
            $this->name = $data->coursename;
            $this->objectives = $data->courseobjectives;
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
        $mform->addElement('header', 'arupadvertdatatype_taps', get_string('header', 'arupadvertdatatype_taps'));
        $this->_elements[] = 'arupadvertdatatype_taps';

        $this->_taps_courses_select($mform, $current->instance);

        $mform->addElement('advcheckbox', 'overrideregion', get_string('overrideregion', 'arupadvertdatatype_taps'), '', array('group' => null), array(0, 1));
        $mform->addHelpButton('overrideregion', 'overrideregion', 'arupadvertdatatype_taps');
        $mform->setDefault('overrideregion', 0);
        $this->_elements[] = 'overrideregion';
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
            $current = $DB->get_record('arupadvertdatatype_taps', array('arupadvertid' => $defaultvalues['id']));
            if ($current) {
                $defaultvalues['tapscourseid'] = $current->tapscourseid;
                $defaultvalues['overrideregion'] = $current->overrideregion;
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
        // Internal Moodle validation will ensure tapscourse is from list determined by _get_taps_courses().
        return array();
    }

    /**
     * Edit instance.
     *
     * @param stdClass $data
     * @param moodleform $mform
     */
    public function edit_instance($data, $mform) {
        global $COURSE, $DB;

        $now = time();
        $current = $DB->get_record('arupadvertdatatype_taps', array('arupadvertid' => $data->id));

        if (!$current) {
            $current = new \stdClass();
            $current->arupadvertid = $data->id;
            $current->timecreated = $now;
        }

        $current->tapscourseid = $data->tapscourseid;
        $current->overrideregion = $data->overrideregion;
        $current->timemodified = time();

        if (isset($current->id)) {
            $result = $DB->update_record('arupadvertdatatype_taps', $current);
        } else {
            $result = $DB->insert_record('arupadvertdatatype_taps', $current);
        }

        // Update tapsenrol/tapscompletion.
        // Validation to allow this has already happened, no further checks carried out here.
        // @TODO : Change to event driven?
        $tapsenrolinstalled = $DB->get_record('modules', array('name' => 'tapsenrol'));
        $tapsenroltapscourse = $tapsenrolinstalled ? $DB->get_record('tapsenrol', array('course' => $COURSE->id)) : false;
        if ($tapsenroltapscourse && $tapsenroltapscourse->tapscourse != $current->tapscourseid) {
            $tapsenroltapscourse->tapscourse = $current->tapscourseid;
            $tapsenroltapscourse->timemodified = time();
            $DB->update_record('tapsenrol', $tapsenroltapscourse);
            $DB->delete_records('tapsenrol_tracking', array('tapsenrolid' => $tapsenroltapscourse->id));
            $DB->delete_records('tapsenrol_completion', array('tapsenrolid' => $tapsenroltapscourse->id));
        }
        $tapscompletioninstalled = $DB->get_record('modules', array('name' => 'tapscompletion'));
        $tapscompletiontapscourse = $tapscompletioninstalled ? $DB->get_record('tapscompletion', array('course' => $COURSE->id)) : false;
        if ($tapscompletiontapscourse && $tapscompletiontapscourse->tapscourse != $current->tapscourseid) {
            $DB->update_record('tapscompletion', $tapscompletiontapscourse);
        }

        $this->_region_mapping($data);

        $this->_update_course_details($data);

        return $result;

    }

    /**
     * Delete instance.
     *
     * @param int $arupadvertid
     */
    public function delete_instance($arupadvertid) {
        global $DB;
        return $DB->delete_records('arupadvertdatatype_taps', array('arupadvertid' => $arupadvertid));
    }

    /**
     * Generate TAPS course select element.
     *
     * @param moodleform $mform
     * @param int $arupadvertid
     */
    protected function _taps_courses_select($mform, $arupadvertid) {
        global $COURSE, $DB;

        $hint = '';

        // If tapsenrol already present (with completion data).
        $completion = new \completion_info($COURSE);
        $tapsenrolinstalled = $DB->get_record('modules', array('name' => 'tapsenrol'));
        $tapsenroltapscourse = $tapsenrolinstalled ? $DB->get_record('tapsenrol', array('course' => $COURSE->id)) : false;
        if ($tapsenroltapscourse && $completion->count_user_data(get_coursemodule_from_instance('tapsenrol', $tapsenroltapscourse->id, $COURSE->id))) {
            $tapscourses = $DB->get_records_select_menu(
                    'local_taps_course',
                    'courseid = :courseid',
                    array('courseid' => $tapsenroltapscourse->tapscourse),
                    '',
                    'courseid, coursename');
            $hint = 'tapsenrolhascompletion';
        }

        if (!isset($tapscourses)) {
            $auselect = '';
            $auparams = array();
            if ($arupadvertid) {
                $auselect .= 'arupadvertid != :arupadvertid';
                $auparams['arupadvertid'] = $arupadvertid;
            }
            $alreadyused = $DB->get_records_select_menu('arupadvertdatatype_taps', $auselect, $auparams, '', 'tapscourseid as id, tapscourseid');

            if ($tapsenrolinstalled) {
                $teselect = 'course != :course';
                $teparams = array('course' => $COURSE->id);
                $alreadyused = $alreadyused + $DB->get_records_select_menu('tapsenrol', $teselect, $teparams, '', 'tapscourse as id, tapscourse');
            }

            if ($DB->get_record('modules', array('name' => 'tapscompletion'))) {
                $tcselect = 'course != :course';
                $tcparams = array('course' => $COURSE->id);
                $alreadyused = $alreadyused + $DB->get_records_select_menu('tapscompletion', $tcselect, $tcparams, '', 'tapscourse as id, tapscourse');
            }

            $select = '';
            $params = array();
            if ($alreadyused) {
                list($in, $params) = $DB->get_in_or_equal(
                        $alreadyused,
                        SQL_PARAMS_NAMED,
                        'alreadyused',
                        false);
                $select = 'courseid '.$in;
            }
            $tapscoursesin = $DB->get_records_select('local_taps_course', $select, $params, '', 'courseid, courseregion, coursename, coursecode');
            $tapscourses = array();
            foreach ($tapscoursesin as $tapscoursein) {
                $coursecode = $tapscoursein->coursecode ? ' ['.$tapscoursein->coursecode.']' : '';
                $tapscourses[$tapscoursein->courseid] = $tapscoursein->courseregion .
                    ' - ' .
                    $tapscoursein->coursename .
                    $coursecode;
            }
            natcasesort($tapscourses);
            $hint = 'missingcourses';
        }

        $selectoptions = array('' => get_string('choosedots')) + $tapscourses;
        $mform->addElement('select', 'tapscourseid', get_string('tapscourseid', 'arupadvertdatatype_taps'), $selectoptions, array('style' => 'max-width:100%'));
        $mform->addRule('tapscourseid', null, 'required', null, 'client');
        $this->_elements[] = 'tapscourseid';
        if ($hint) {
            $a = new \stdClass();
            $a->activity = $tapsenrolinstalled ? get_string('modulename', 'tapsenrol') : '';
            $a->course = \core_text::strtolower(get_string('course'));
            $mform->addElement('static', 'tapscourseidhint', '', get_string($hint, 'arupadvertdatatype_taps', $a));
            $this->_elements[] = 'tapscourseidhint';
        }
    }

    /**
     * Map to TAPS defined regions.
     *
     * @param stdClass $data
     */
    protected function _region_mapping($data) {
        global $DB;

        if ($data->overrideregion) {
            return;
        }

        $tapscourseregion = $DB->get_field('local_taps_course', 'courseregion', array('courseid' => $data->tapscourseid));
        if ($tapscourseregion) {
            // TAPS courses do not have subregions.
            $DB->delete_records('local_regions_sub_cou', array('courseid' => $data->course));
            if (stripos($tapscourseregion, 'global') !== false) {
                $DB->delete_records('local_regions_reg_cou', array('courseid' => $data->course));
            } else {
                // Check hierarchy for Europe/UKMEA separation.
                $hierarchylike = $DB->sql_like('categoryhierarchy', ':categorysearch', false);
                $primaryflag = $DB->sql_compare_text('primaryflag', 1);
                $europeonly = $DB->count_records_select(
                    'local_taps_course_category',
                    "courseid = :courseid AND {$primaryflag} = :primaryflag AND {$hierarchylike}",
                    array('courseid' => $data->tapscourseid, 'primaryflag' => 'Y', 'categorysearch' => '%Europe Courses%')
                );
                $europeandukmea = !$europeonly && $DB->count_records_select(
                    'local_taps_course_category',
                    "courseid = :courseid AND {$primaryflag} = :primaryflag AND {$hierarchylike}",
                    array('courseid' => $data->tapscourseid, 'primaryflag' => 'N', 'categorysearch' => '%Europe Courses%')
                );
                $ukmeaonly = !$europeonly && !$europeandukmea;

                $allregions = $DB->get_records_menu('local_regions_reg', array('userselectable' => 1), '', 'id, tapsname');
                foreach ($allregions as $regionid => $regionname) {
                    $regionname = trim(str_ireplace('region', '', $regionname));
                    $regionmatch = stripos($tapscourseregion, $regionname) !== false;
                    if ($regionmatch && stripos($regionname, 'UK-MEA') !== false) {
                        $regionmatch = $regionmatch && ($europeandukmea || $ukmeaonly);
                    } else if ($regionmatch && stripos($regionname, 'Europe') !== false) {
                        $regionmatch = $regionmatch && ($europeandukmea || $europeonly);
                    }
                    if ($regionmatch) {
                        $regioncourse = $DB->get_record('local_regions_reg_cou', array('regionid' => $regionid, 'courseid' => $data->course));
                        if (!$regioncourse) {
                            $regioncourse = new \stdClass();
                            $regioncourse->regionid = $regionid;
                            $regioncourse->courseid = $data->course;
                            $DB->insert_record('local_regions_reg_cou', $regioncourse);
                        }
                    } else {
                        $DB->delete_records('local_regions_reg_cou', array('regionid' => $regionid, 'courseid' => $data->course));
                    }
                }
            }
        }
    }

    /**
     * Update course details based on TAPS data.
     *
     * @param stdClass $data
     */
    protected function _update_course_details($data) {
        global $DB;

        $course = $DB->get_record('course', array('id' => $data->course));
        $tapscourse = $DB->get_record('local_taps_course', array('courseid' => $data->tapscourseid));
        if ($course && $tapscourse) {
            $course->fullname = strip_tags($tapscourse->coursename);
            $course->idnumber = $tapscourse->courseid;
            $course->summary = \html_writer::tag('p', strip_tags($tapscourse->onelinedescription));
            $course->summaryformat = FORMAT_HTML;
            $course->timemodified = time();
            $DB->update_record('course', $course);
        }
    }
}
