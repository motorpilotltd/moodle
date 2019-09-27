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
 * @package    local_linkedinlearning
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_linkedinlearning;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/completion/data_object.php');
require_once("$CFG->dirroot/course/lib.php");
require_once("$CFG->dirroot/local/coursemetadata/lib.php");
require_once("$CFG->dirroot/local/regions/lib.php");
require_once("$CFG->dirroot/mod/arupadvert/lib.php");
require_once("$CFG->dirroot/mod/scorm/lib.php");
require_once("$CFG->dirroot/mod/scorm/locallib.php");
require_once("$CFG->dirroot/mod/tapsenrol/lib.php");
require_once("$CFG->dirroot/mod/tapscompletion/lib.php");
require_once("$CFG->dirroot/completion/criteria/completion_criteria_activity.php");
require_once $CFG->dirroot . '/completion/completion_aggregation.php';
require_once $CFG->dirroot . '/completion/criteria/completion_criteria.php';
require_once $CFG->dirroot . '/completion/completion_completion.php';
require_once $CFG->dirroot . '/completion/completion_criteria_completion.php';

class course extends \data_object {
    public $table = 'linkedinlearning_course';
    public $required_fields = ['id', 'urn', 'title', 'primaryimageurl', 'aicclaunchurl', 'publishedat', 'lastupdatedat',
            'description', 'shortdescription', 'timetocomplete', 'available'];

    public $urn;
    public $title;
    public $primaryimageurl;
    public $aicclaunchurl;
    public $publishedat;
    public $lastupdatedat;
    public $description;
    public $shortdescription;
    public $timetocomplete;
    public $available;

    /*
     * @return self
     */
    public static function fetchbyurn($urn, $skipcache = false) {
        if ($skipcache) {
            return self::fetch(['urn' => $urn]);
        }

        $cache = \cache::make('local_linkedinlearning', 'linkedinlearningcourses');
        $course = $cache->get($urn);
        if ($course === false) {
            $course = self::fetch(['urn' => $urn]);
            $cache->set($urn, $course);
        }

        return $course;
    }

    /*
     * @return self[]
     */
    public static function fetchbyurns($urns) {
        global $DB;

        list($sql, $params) = $DB->get_in_or_equal($urns);
        $datas = $DB->get_records_sql("SELECT * FROM {linkedinlearning_course} WHERE urn $sql", $params);

        $results = [];
        foreach ($datas as $data) {
            $instance = new course();
            self::set_properties($instance, $data);
            $results[$instance->urn] = $instance;
        }

        return $results;
    }

    /*
     * @return self[]
     */
    public static function fetchbyids($ids) {
        global $DB;

        list($sql, $params) = $DB->get_in_or_equal($ids);
        $datas = $DB->get_records_sql("SELECT * FROM {linkedinlearning_course} WHERE id $sql", $params);

        $results = [];
        foreach ($datas as $data) {
            $instance = new course();
            self::set_properties($instance, $data);
            $results[$instance->urn] = $instance;
        }

        return $results;
    }

    /**
     * @param array $params
     * @return self
     */
    public static function fetch($params) {
        return self::fetch_helper('linkedinlearning_course', __CLASS__, $params);
    }

    /**
     * @param array $params
     * @return self[]
     */
    public static function fetch_all($params) {
        $ret = self::fetch_all_helper('linkedinlearning_course', __CLASS__, $params);
        if (!$ret) {
            return [];
        }
        return $ret;
    }

    public function updateclassifications($classificationids) {
        global $DB, $CFG;

        $existingids =
                $DB->get_records_menu('linkedinlearning_crs_class', ['linkedinlearningcourseid' => $this->id], '',
                        'classificationid, classificationid');
        $existingids = array_keys($existingids);

        $unchanged = array_intersect($existingids, $classificationids);
        $todelete = array_diff($existingids, $unchanged);
        $toinsert = array_diff($classificationids, $unchanged);

        if (count($toinsert) > 0) {

            foreach ($toinsert as $requiredid) {

                $record = new \stdClass();
                $record->classificationid = $requiredid;
                $record->linkedinlearningcourseid = $this->id;

                $DB->insert_record('linkedinlearning_crs_class', $record);
            }
        }

        if (count($todelete) > 0) {

            list($inids, $params) = $DB->get_in_or_equal($todelete);
            $params [] = $this->id;

            $sql = "DELETE FROM {linkedinlearning_crs_class}
                    WHERE classificationid {$inids} AND linkedinlearningcourseid = ?";

            $DB->execute($sql, $params);
        }
    }

    private static function getmethodologyfield() {
        global $DB;
        return $DB->get_record('coursemetadata_info_field', array('shortname' => 'Methodology'));
    }

    private function getmoodlecourse() {
        global $DB;

        $sql = "select c.* from {course} c 
                inner JOIN {arupadvert} aa on aa.course = c.id
                inner JOIN {arupadvertdatatype_taps} ladt on aa.id = ladt.arupadvertid
                inner join {local_taps_course} ltc on ladt.tapscourseid = ltc.courseid
                where ltc.coursecode = :urn";
        return $DB->get_record_sql($sql, ['urn' => $this->urn]);
    }

    public function setregionstate($regionid, $state) {
        global $DB;
        $course = $this->getmoodlecourse();

        if (!$course) {
            $course = $this->build_moodle_course();
        }

        local_regions_load_data_course($course);
        if ($state) {
            if ($regionid != 0) {
                unset($course->regions_field_region[0]);
            }
            $course->regions_field_region[$regionid] = $regionid;
        } else {
            unset($course->regions_field_region[$regionid]);
        }

        if (empty($course->regions_field_region)) {
            $course->visible = false;
            $course->regions_field_region = [0 => 0];
            $DB->update_record('course', $course);
        } else if (empty($course->visible)) {
            $course->visible = true;
            $DB->update_record('course', $course);
        }
        local_regions_save_data_course($course);

        $data = new \stdClass();
        $data->region = $course->regions_field_region;
        $data->id = $DB->get_field('tapsenrol', 'id', ['course' => $course->id]);
        tapsenrol_region_mapping_override($data);
    }

    /**
     * @param $DB
     * @param $CFG
     * @return object|\stdClass
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \file_exception
     * @throws \moodle_exception
     */
    private function build_moodle_course() {
        global $DB, $CFG;

        $classifications = classification::fetch_by_course($this->id);
        $keywords = [];
        foreach ($classifications as $classification) {
            $keywords[] = $classification->name;
        }

        $course = new \stdClass();
        $course->fullname = $this->title;
        $course->shortname = $this->title;
        $course->summary = $this->shortdescription;
        $course->summaryformat = FORMAT_HTML;
        $course->groupmode = VISIBLEGROUPS;
        $course->groupmodeforce = 0;
        $course->defaultgroupingid = 0;
        $course->format = 'topics';
        $course->newsitems = 0;
        $course->numsections = 2;
        $course->enablecompletion = COMPLETION_ENABLED;
        $course->completionstartonenrol = 1;
        $course->category = $this->getlinkedinlearningcategory();

        $enrolpluginsenabled = $CFG->enrol_plugins_enabled;
        $CFG->enrol_plugins_enabled = '';
        $course = \create_course($course, null);
        $CFG->enrol_plugins_enabled = $enrolpluginsenabled;

        $sqlmax = "SELECT max(courseid) as maxid from {local_taps_course}";
        $max = $DB->get_record_sql($sqlmax);
        $tapscourse = new \stdClass();
        $tapscourse->courseid = $max->maxid + 1;
        $tapscourse->coursecode = $this->urn;
        $tapscourse->coursename = $this->title;
        $tapscourse->coursedescription = $this->description;
        $tapscourse->onelinedescription = $this->shortdescription;
        $tapscourse->timemodified = $this->lastupdatedat;
        $tapscourse->keywords = implode(', ', $keywords);
        $tapscourse->duration = round($this->timetocomplete / MINSECS);
        $tapscourse->durationunits = 'Minute(s)';
        $tapscourse->durationunitscode = 'MINS';
        $DB->insert_record('local_taps_course', $tapscourse);

        $sqlmax = "SELECT max(classid) as maxid from {local_taps_class}";
        $max = $DB->get_record_sql($sqlmax);
        $tapsclass = new \stdClass();
        $tapsclass->classid = $max->maxid + 1;
        $tapsclass->courseid = $tapscourse->courseid;
        $tapsclass->coursename = $this->title;
        $tapsclass->classname = $this->title;
        $tapsclass->classtype = 'Self Paced';
        $tapsclass->classstatus = 'Normal';
        $tapsclass->classduration = round($this->timetocomplete / MINSECS);
        $tapsclass->classdurationunits = 'Minute(s)';
        $tapsclass->classdurationunitscode = 'MIN';
        $tapsclass->startdate = $this->publishedat;
        $tapsclass->enrolmentstartdate = $this->publishedat;
        $tapsclass->enrolmentenddate = 0;
        $tapsclass->classstartdate = $this->publishedat;
        $tapsclass->classstarttime = $this->publishedat;
        $tapsclass->classendtime = 0;
        $tapsclass->maximumattendees = -1;
        $tapsclass->classsuppliername = 'LinkedIn Learning';
        $tapsclass->timemodified = $this->lastupdatedat;
        $DB->insert_record('local_taps_class', $tapsclass);

        $section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => 1]);
        course_update_section($section->course, $section, array('name' => 'LinkedIn Learning'));

        $section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => 2]);
        course_update_section($section->course, $section, array('visible' => false));

        // Now setup required enrolment plugins.
        // Manual...
        $enrolmanual = enrol_get_plugin('manual');
        if ($enrolmanual) {
            $enrolmanualfields = array(
                    'status'      => ENROL_INSTANCE_ENABLED,
                    'enrolperiod' => 0,
                    'roleid'      => get_config('local_taps', 'taps_enrolment_role')
            );
            $enrolmanual->add_instance($course, $enrolmanualfields);
        }

        // Self...
        $enrolself = enrol_get_plugin('self');
        if ($enrolself) {
            $enrolselffields = array(
                    'customint1'  => 0,
                    'customint2'  => 0,
                    'customint3'  => 0,
                    'customint4'  => 0,
                    'enrolperiod' => 0,
                    'status'      => ENROL_INSTANCE_ENABLED,
                    'roleid'      => get_config('local_taps', 'taps_enrolment_role')
            );
            $enrolself->add_instance($course, $enrolselffields);
        }

        // Guest...
        $enrolguest = enrol_get_plugin('guest');
        if ($enrolguest) {
            $enrolguestfields = array(
                    'status' => ENROL_INSTANCE_ENABLED
            );
            $enrolguest->add_instance($course, $enrolguestfields);
        }

        $coursemetadatadata = new \stdClass();
        $coursemetadatadata->id = $course->id;
        $coursemetadatadata->{'coursemetadata_field_' . self::getmethodologyfield()->shortname} = 'LinkedIn Learning';
        \coursemetadata_save_data($coursemetadatadata);

        $arupadvertinstalled = $DB->get_record('modules', array('name' => 'arupadvert'));

        // Add advert activity.
        $arupadvertcm = new \stdClass();
        $arupadvertcm->course = $course->id;
        $arupadvertcm->module = $arupadvertinstalled->id;
        $arupadvertcm->instance = 0;
        $arupadvertcm->visible = 1;
        $arupadvertcm->groupmode = VISIBLEGROUPS;
        $arupadvertcm->groupingid = 0;
        $arupadvertcm->completion = COMPLETION_TRACKING_NONE;
        $arupadvertcm->showdescription = 0;
        $arupadvertcm->coursemodule = add_course_module($arupadvertcm);
        $arupadvertcm->section = 0;

        $modulename = $arupadvertinstalled->name;

        $classifications = classification::fetch_by_course($this->id);
        $keywords = [];
        foreach ($classifications as $classification) {
            $keywords[] = $classification->name;
        }

        $arupadvert = new \stdClass();
        // Main advert fields.
        $arupadvert->altword = '';
        $arupadvert->showheadings = 1;
        $arupadvert->course = $course->id;
        $arupadvert->name = 'Advert';
        $arupadvert->advertblockimage = null;
        $arupadvert->datatype = 'taps';
        $arupadvert->arupadvertid = $arupadvertcm->coursemodule;
        $arupadvert->coursemodule = $arupadvertcm->coursemodule;
        $arupadvert->tapscourseid = $tapscourse->courseid;
        $arupadvert->overrideregion = 1;
        $arupadvert->timecreated = $this->publishedat;
        $arupadvert->timemodified = $this->lastupdatedat;
        $arupadvertcm->instance = arupadvert_add_instance($arupadvert, null);

        $fs = get_file_storage();
        $context = \context_module::instance($arupadvertcm->coursemodule);
        $filerecord = array('contextid' => $context->id, 'component' => 'mod_arupadvert', 'filearea' => 'originalblockimage',
                            'itemid'    => 0, 'filepath' => '/');
        $newimage = $fs->create_file_from_url($filerecord, $this->primaryimageurl, array('calctimeout' => true), true);
        arupadvert_process_blockimage($context->id, $newimage);

        // This happens in arupadvert_add_instance but no harm leaving it here...
        $DB->set_field('course_modules', 'instance', $arupadvertcm->instance, array('id' => $arupadvertcm->coursemodule));

        course_add_cm_to_section($arupadvertcm->course, $arupadvertcm->coursemodule, $arupadvertcm->section);

        set_coursemodule_visible($arupadvertcm->coursemodule, $arupadvertcm->visible);

        $eventdata = clone $arupadvertcm;
        $eventdata->name = $arupadvert->name;
        $eventdata->modname = $modulename;
        $eventdata->id = $eventdata->coursemodule;
        $modcontext = \context_module::instance($eventdata->coursemodule);
        $event = \core\event\course_module_created::create_from_cm($eventdata, $modcontext);
        $event->trigger();

        // Add AICC/Scorm activity.
        $scormcm = new \stdClass();
        $scormcm->course = $course->id;
        $scormcm->module = $DB->get_field('modules', 'id', ['name' => 'scorm']);
        $scormcm->instance = 0;
        $scormcm->visible = 1;
        $scormcm->groupmode = VISIBLEGROUPS;
        $scormcm->groupingid = 0;
        $scormcm->completion = COMPLETION_TRACKING_AUTOMATIC;
        $scormcm->showdescription = 0;
        $scormcm->coursemodule = add_course_module($scormcm);
        $scormcm->section = 1;

        $cfgscorm = get_config('scorm');

        $scorm = new \stdClass();
        $scorm->course = $course->id;
        $scorm->cmidnumber = '';
        $scorm->coursemodule = $scormcm->coursemodule;
        $scorm->scormtype = SCORM_TYPE_AICCURL;
        $scorm->packageurl = $this->aicclaunchurl;
        $scorm->name = $this->title;
        $scorm->intro = $this->description;
        $scorm->introformat = FORMAT_HTML;
        $scorm->updatefreq = SCORM_UPDATE_EVERYTIME;
        $scorm->maxgrade = $cfgscorm->maxgrade;
        $scorm->grademethod = $cfgscorm->grademethod;
        $scorm->popup = true;
        $scorm->width = $cfgscorm->framewidth;
        $scorm->height = $cfgscorm->frameheight;
        $scorm->winoptgrp = $cfgscorm->winoptgrp_adv;
        $scorm->displayactivityname = $cfgscorm->displayactivityname;
        $scorm->skipview = SCORM_SKIPVIEW_ALWAYS;
        $scorm->hidebrowse = $cfgscorm->hidebrowse;
        $scorm->displaycoursestructure = $cfgscorm->displaycoursestructure;
        $scorm->hidetoc = $cfgscorm->hidetoc;
        $scorm->nav = $cfgscorm->nav;
        $scorm->navpositionleft = $cfgscorm->navpositionleft;
        $scorm->navpositiontop = $cfgscorm->navpositiontop;
        $scorm->displayattemptstatus = $cfgscorm->displayattemptstatus;
        $scorm->maxattempt = $cfgscorm->maxattempt;
        $scorm->forcenewattempt = $cfgscorm->forcenewattempt;
        $scorm->lastattemptlock = $cfgscorm->lastattemptlock;
        $scorm->forcecompleted = $cfgscorm->forcecompleted;
        $scorm->auto = $cfgscorm->auto;
        $scorm->autocommit = $cfgscorm->autocommit;
        $scorm->masteryoverride = $cfgscorm->masteryoverride;
        $scorm->completionstatusrequired = 6;

        $scormcm->instance = scorm_add_instance($scorm, null);

        // This happens in arupadvert_add_instance but no harm leaving it here...
        $DB->set_field('course_modules', 'instance', $scormcm->instance, array('id' => $scormcm->coursemodule));

        course_add_cm_to_section($scormcm->course, $scormcm->coursemodule, $scormcm->section);

        set_coursemodule_visible($scormcm->coursemodule, $scormcm->visible);

        $eventdata = clone $scormcm;
        $eventdata->name = $scorm->name;
        $eventdata->modname = 'scorm';
        $eventdata->id = $eventdata->coursemodule;
        $modcontext = \context_module::instance($eventdata->coursemodule);
        $event = \core\event\course_module_created::create_from_cm($eventdata, $modcontext);
        $event->trigger();

        // Add taps enrol activity.
        $tapsenrolcm = new \stdClass();
        $tapsenrolcm->course = $course->id;
        $tapsenrolcm->module = $DB->get_field('modules', 'id', ['name' => 'tapsenrol']);
        $tapsenrolcm->instance = 0;
        $tapsenrolcm->visible = 1;
        $tapsenrolcm->groupmode = VISIBLEGROUPS;
        $tapsenrolcm->groupingid = 0;
        $tapsenrolcm->completion = COMPLETION_TRACKING_AUTOMATIC;
        $tapsenrolcm->showdescription = 0;
        $tapsenrolcm->coursemodule = add_course_module($tapsenrolcm);
        $tapsenrolcm->section = 0;

        $tapsenrol = new \stdClass();
        $tapsenrol->course = $course->id;
        $tapsenrol->name = 'Linked course Enrolment';
        $tapsenrol->tapscourse = $tapscourse->courseid;
        $tapsenrol->completionenrolment = 1;
        $tapsenrol->internalworkflowid = -1;

        $tapsenrolcm->instance = tapsenrol_add_instance($tapsenrol, null);

        // This happens in arupadvert_add_instance but no harm leaving it here...
        $DB->set_field('course_modules', 'instance', $tapsenrolcm->instance, array('id' => $tapsenrolcm->coursemodule));

        course_add_cm_to_section($tapsenrolcm->course, $tapsenrolcm->coursemodule, $tapsenrolcm->section);

        set_coursemodule_visible($tapsenrolcm->coursemodule, $tapsenrolcm->visible);

        $eventdata = clone $tapsenrolcm;
        $eventdata->name = $tapsenrol->name;
        $eventdata->modname = 'tapsenrol';
        $eventdata->id = $eventdata->coursemodule;
        $modcontext = \context_module::instance($eventdata->coursemodule);
        $event = \core\event\course_module_created::create_from_cm($eventdata, $modcontext);
        $event->trigger();

        // Add taps completion activity.
        $tapscompletioncm = new \stdClass();
        $tapscompletioncm->course = $course->id;
        $tapscompletioncm->module = $DB->get_field('modules', 'id', ['name' => 'tapscompletion']);
        $tapscompletioncm->instance = 0;
        $tapscompletioncm->visible = 0;
        $tapscompletioncm->groupmode = VISIBLEGROUPS;
        $tapscompletioncm->groupingid = 0;
        $tapscompletioncm->completion = COMPLETION_TRACKING_AUTOMATIC;
        $tapscompletioncm->showdescription = 0;
        $tapscompletioncm->coursemodule = add_course_module($tapscompletioncm);
        $tapscompletioncm->section = 2;

        $tapscompletion = new \stdClass();
        $tapscompletion->course = $course->id;
        $tapscompletion->name = 'Linked course completionment';
        $tapscompletion->tapscourse = $tapscourse->courseid;
        $tapscompletion->autocompletion = 1;
        $tapscompletion->completiontimetype = 0;
        $tapscompletion->completionattended = 1;

        $tapscompletioncm->instance = tapscompletion_add_instance($tapscompletion, null);

        // This happens in arupadvert_add_instance but no harm leaving it here...
        $DB->set_field('course_modules', 'instance', $tapscompletioncm->instance, array('id' => $tapscompletioncm->coursemodule));

        course_add_cm_to_section($tapscompletioncm->course, $tapscompletioncm->coursemodule, $tapscompletioncm->section);

        set_coursemodule_visible($tapscompletioncm->coursemodule, $tapscompletioncm->visible);

        $eventdata = clone $tapscompletioncm;
        $eventdata->name = $tapscompletion->name;
        $eventdata->modname = 'tapscompletion';
        $eventdata->id = $eventdata->coursemodule;
        $modcontext = \context_module::instance($eventdata->coursemodule);
        $event = \core\event\course_module_created::create_from_cm($eventdata, $modcontext);
        $event->trigger();

        $data = new \stdClass();
        $data->id = $course->id;
        $data->criteria_activity = [$scormcm->coursemodule => 1];

        // Set completion criteria activity.
        $criterion = new \completion_criteria_activity();
        $criterion->update_config($data);

        $aggdata = ['course' => $course->id, 'criteriatype' => COMPLETION_CRITERIA_TYPE_ACTIVITY];
        $aggregation = new \completion_aggregation($aggdata);
        $aggregation->setMethod(COMPLETION_AGGREGATION_ALL);
        $aggregation->save();

        // Availability.
        $structure = new \stdClass();
        $structure->cm = $tapsenrolcm->coursemodule;
        $structure->e = COMPLETION_COMPLETE;
        $completioncondition = new \availability_completion\condition($structure);
        $children = array($completioncondition->save());
        $rootjson = json_encode(\core_availability\tree::get_root_json($children, \core_availability\tree::OP_AND, false));

        $sectionselect = 'course = :courseid AND section != :sectionnumber';
        $sectionparams = array('courseid' => $course->id, 'sectionnumber' => 0);
        $sections = $DB->get_records_select('course_sections', $sectionselect, $sectionparams, 'section ASC');

        if ($sections) {
            foreach ($sections as $section) {
                // Enforce section availability (same as for tapscompletion activity above).
                $section->availability = $rootjson;
                $DB->update_record('course_sections', $section);
            }
        }

        rebuild_course_cache($course->id);

        return $course;
    }

    /**
     * @param $DB
     * @param $CFG
     * @return object|\stdClass
     * @throws \coding_exception
     * @throws \dml_exception
     * @throws \file_exception
     * @throws \moodle_exception
     */
    public function update_moodle_course() {
        global $DB, $CFG;

        $course = $this->getmoodlecourse();

        if (!$course) {
            return;
        }

        $classifications = classification::fetch_by_course($this->id);
        $keywords = [];
        foreach ($classifications as $classification) {
            $keywords[] = $classification->name;
        }

        $course->visible = $this->available;
        $course->fullname = $this->title;
        $course->shortname = $this->title;
        $course->summary = $this->shortdescription;


        $tapscourse = $DB->get_records_select('local_taps_course', $DB->sql_compare_text('coursecode') . " = :urn", ['urn' => $this->urn]);
        $tapscourse = reset($tapscourse);
        $tapscourse->coursename = $this->title;
        $tapscourse->coursedescription = $this->description;
        $tapscourse->onelinedescription = $this->shortdescription;
        $tapscourse->timemodified = $this->lastupdatedat;
        $tapscourse->keywords = implode(', ', $keywords);
        $tapscourse->duration = round($this->timetocomplete / MINSECS);
        $DB->update_record('local_taps_course', $tapscourse);

        $tapsclass = $DB->get_record('local_taps_class', ['courseid' => $tapscourse->courseid]);
        $tapsclass->coursename = $this->title;
        $tapsclass->classname = $this->title;
        $tapsclass->classduration = round($this->timetocomplete / MINSECS);
        $tapsclass->startdate = $this->publishedat;
        $tapsclass->enrolmentstartdate = $this->publishedat;
        $tapsclass->classstartdate = $this->publishedat;
        $tapsclass->classstarttime = $this->publishedat;
        $tapsclass->timemodified = $this->lastupdatedat;
        $DB->update_record('local_taps_class', $tapsclass);

        $modinfo = get_fast_modinfo($course);

        $scorms = $modinfo->get_instances_of('scorm');
        if ($scorms) {
            $scormcm = reset($scorms);

            $scorm = $DB->get_record('scorm', ['id' => $scormcm->instance]);

            $scorm->packageurl = $this->aicclaunchurl;
            $scorm->name = $this->title;
            $scorm->intro = $this->description;
            $DB->update_record('scorm', $scorm);

        }

        $arupadverts = $modinfo->get_instances_of('arupadvert');
        $arupadvertcm = reset($arupadverts);
        $fs = get_file_storage();
        $context = \context_module::instance($arupadvertcm->id);
        $fs->delete_area_files($context->id, 'mod_arupadvert', 'blockimage');
        $fs->delete_area_files($context->id, 'mod_arupadvert', 'originalblockimage');
        $filerecord = array('contextid' => $context->id, 'component' => 'mod_arupadvert', 'filearea' => 'originalblockimage',
                            'itemid'    => 0, 'filepath' => '/');
        $newimage = $fs->create_file_from_url($filerecord, $this->primaryimageurl, array('calctimeout' => true), true);
        arupadvert_process_blockimage($context->id, $newimage);

        update_course($course);
    }

    /**
     * @param $DB
     * @return mixed
     * @throws \dml_exception
     */
    private function getlinkedinlearningcategory() {
        global $DB;

        return $DB->get_field('course_categories', 'id', ['idnumber' => get_config('local_linkedinlearning', 'category_idnumber')],
                MUST_EXIST);
    }
}