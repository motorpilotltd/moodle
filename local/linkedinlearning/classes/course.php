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

use coursemetadatafield_arup\arupmetadata;
use local_coursemanager\coursemanager;

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
require_once("$CFG->dirroot/local/coursemetadata/field/arup/field.class.php");
require_once $CFG->dirroot . '/completion/completion_aggregation.php';
require_once $CFG->dirroot . '/completion/criteria/completion_criteria.php';
require_once $CFG->dirroot . '/completion/completion_completion.php';
require_once $CFG->dirroot . '/completion/completion_criteria_completion.php';

class course extends \data_object {
    public $table = 'linkedinlearning_course';
    public $required_fields = ['id', 'urn', 'title', 'primaryimageurl', 'aicclaunchurl', 'ssolaunchurl', 'publishedat', 'lastupdatedat',
            'description', 'shortdescription', 'timetocomplete', 'available', 'language'];

    public $urn;
    public $title;
    public $primaryimageurl;
    public $aicclaunchurl;
    public $ssolaunchurl;
    public $publishedat;
    public $lastupdatedat;
    public $description;
    public $shortdescription;
    public $timetocomplete;
    public $available;
    public $language;

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
        $metadata = arupmetadata::fetch(['thirdpartyreference' => $this->urn]);
        if ($metadata) {
            return $metadata->get_course();
        } else {
            return false;
        }
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
        // Force to 'Global', region is now only for catalogue.
        $data->region = [0];
        $data->id = $DB->get_field('tapsenrol', 'id', ['course' => $course->id]);
        \tapsenrol_region_mapping($data);
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

        $transaction = $DB->start_delegated_transaction();

        $classifications = classification::fetch_by_course($this->id);
        $keywords = [];
        foreach ($classifications as $classification) {
            $keywords[] = $classification->name;
        }

        $course = new \stdClass();
        $course->fullname = $this->title;
        $course->shortname = $this->create_moodle_shortname(true);
        $course->summary = $this->shortdescription;
        $course->summaryformat = FORMAT_HTML;
        $course->groupmode = VISIBLEGROUPS;
        $course->groupmodeforce = 0;
        $course->defaultgroupingid = 0;
        $course->format = 'topics';
        $course->newsitems = 0;
        $course->numsections = 3;
        $course->enablecompletion = COMPLETION_ENABLED;
        $course->completionstartonenrol = 1;
        $course->category = $this->getlinkedinlearningcategory();

        $enrolpluginsenabled = $CFG->enrol_plugins_enabled;
        $CFG->enrol_plugins_enabled = '';
        $course = \create_course($course, null);
        $CFG->enrol_plugins_enabled = $enrolpluginsenabled;

        $advert = [
                'course'              => $course->id,
                'name'                => $this->title,
                'description'         => $this->description,
                'descriptionformat'   => FORMAT_HTML,
                'timemodified'        => $this->lastupdatedat,
                'keywords'            => implode(', ', $keywords),
                'keywordsformat'      => FORMAT_HTML,
                'display'             => true,
                'methodology'         => \coursemetadatafield_arup\arupmetadata::METHODOLOGY_LINKEDINLEARNING,
                'duration'            => round($this->timetocomplete / MINSECS),//check
                'durationunits'       => 'minutes',//check
                'thirdpartyreference' => $this->urn,
        ];
        $arupmetadata = new \coursemetadatafield_arup\arupmetadata();
        \coursemetadatafield_arup\arupmetadata::set_properties($arupmetadata, $advert);
        $arupmetadata->save();

        $fs = get_file_storage();
        $context = \context_course::instance($course->id);
        $filerecord = array('contextid' => $context->id, 'component' => 'coursemetadatafield_arup', 'filearea' => 'originalblockimage',
                            'itemid'    => 0, 'filepath' => '/');
        $newimage = $fs->create_file_from_url($filerecord, $this->primaryimageurl, array('calctimeout' => true), true);
        \coursemetadata_field_arup::arupadvert_process_blockimage($context->id, $newimage);

        \local_admin\courseformmoddifier::post_creation($course);

        $section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => 1]);
        course_update_section($section->course, $section, array('name' => 'LinkedIn Learning'));

        $section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => 2]);
        course_update_section($section->course, $section, array('name' => 'Feedback'));

        $section = $DB->get_record('course_sections', ['course' => $course->id, 'section' => 3]);
        course_update_section($section->course, $section, array('visible' => false));

        // Add AICC/Scorm activity.
        $scormcm = new \stdClass();
        $scormcm->course = $course->id;
        $scormcm->module = $DB->get_field('modules', 'id', ['name' => 'scorm'], 'MUST_EXIST');
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
        $scorm->cmidnumber = $this->urn;
        $scorm->coursemodule = $scormcm->coursemodule;
        $scorm->scormtype = SCORM_TYPE_AICCURL;
        $scorm->packageurl = $this->aicclaunchurl;
        $scorm->name = $this->title;
        $scorm->intro = $this->description;
        $scorm->introformat = FORMAT_HTML;
        $scorm->updatefreq = SCORM_UPDATE_EVERYTIME;
        $scorm->maxgrade = $cfgscorm->maxgrade;
        $scorm->grademethod = $cfgscorm->grademethod;
        $scorm->popup = false;
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

        // Add feedback activity.
        $feedbackcm = new \stdClass();
        $feedbackcm->course = $course->id;
        $feedbackcm->module = $DB->get_field('modules', 'id', ['name' => 'feedback'], 'MUST_EXIST');
        $feedbackcm->instance = 0;
        $feedbackcm->visible = 1;
        $feedbackcm->groupmode = VISIBLEGROUPS;
        $feedbackcm->groupingid = 0;
        $feedbackcm->completion = COMPLETION_TRACKING_AUTOMATIC;
        $feedbackcm->showdescription = 0;
        $feedbackcm->coursemodule = add_course_module($feedbackcm);
        $feedbackcm->section = 2;

        // Simple DB insertion as feedback_add_instance() does too much.
        $feedback = new \stdClass();
        $feedback->course = $course->id;
        $feedback->name = 'Feedback';
        $feedback->intro = '';
        $feedback->introformat = 1;
        $feedback->anonymous = 1;
        $feedback->email_notification = 0;
        $feedback->multiple_submit = 0;
        $feedback->autonumbering = 0;
        $feedback->site_after_submit = '';
        $feedback->page_after_submit = '';
        $feedback->page_after_submitformat = 1;
        $feedback->publish_stats = 0;
        $feedback->timeopen = 0;
        $feedback->timeclose = 0;
        $feedback->timemodified = time();
        $feedback->completionsubmit = 1;
        $feedback->email_addresses = null;

        $feedbackcm->instance = $feedback->id = $DB->insert_record('feedback', $feedback);
        $DB->set_field('course_modules', 'instance', $feedbackcm->instance, array('id' => $feedbackcm->coursemodule));

        course_add_cm_to_section($feedbackcm->course, $feedbackcm->coursemodule, $feedbackcm->section);

        set_coursemodule_visible($feedbackcm->coursemodule, $feedbackcm->visible);

        // Add questions to feedback activity.
        $feedbackitems = [];
        include($CFG->dirroot . '/local/linkedinlearning/data/feedback_items.php');
        foreach ($feedbackitems as $feedbackitem) {
            $feedbackitem = (object) $feedbackitem;
            $feedbackitem->feedback = $feedback->id;
            $DB->insert_record('feedback_item', $feedbackitem);
        }

        $eventdata = clone $feedbackcm;
        $eventdata->name = $feedback->name;
        $eventdata->modname = 'feedback';
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

        rebuild_course_cache($course->id);

        $transaction->allow_commit();

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
        global $DB;

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
        $course->shortname = $this->create_moodle_shortname();
        $course->summary = $this->shortdescription;

        $arupmetadata = \coursemetadatafield_arup\arupmetadata::fetch(['course'=> $course->id]);
        $advert = [
                'name'                => $this->title,
                'description'         => $this->description,
                'timemodified'        => $this->lastupdatedat,
                'keywords'            => implode(', ', $keywords),
                'duration'            => round($this->timetocomplete / MINSECS),
                'thirdpartyreference' => $this->urn,
        ];
        \coursemetadatafield_arup\arupmetadata::set_properties($arupmetadata, $advert);
        $arupmetadata->save();

        $fs = get_file_storage();
        $context = \context_course::instance($course->id);
        $fs->delete_area_files($context->id, 'coursemetadatafield_arup', 'blockimage');
        $fs->delete_area_files($context->id, 'coursemetadatafield_arup', 'originalblockimage');
        $filerecord = array('contextid' => $context->id, 'component' => 'coursemetadatafield_arup', 'filearea' => 'originalblockimage',
                            'itemid'    => 0, 'filepath' => '/');
        $newimage = $fs->create_file_from_url($filerecord, $this->primaryimageurl, array('calctimeout' => true), true);
        \coursemetadata_field_arup::arupadvert_process_blockimage($context->id, $newimage);

        $modinfo = get_fast_modinfo($course);
        $scorms = $modinfo->get_instances_of('scorm');
        if ($scorms) {
            $scormcm = reset($scorms);

            $scorm = $DB->get_record('scorm', ['id' => $scormcm->instance]);

            $scorm->reference = $this->aicclaunchurl;
            $scorm->name = $this->title;
            $scorm->intro = $this->description;
            $DB->update_record('scorm', $scorm);
        }

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

    /**
     * Returns title prefixed by the id part of the URN.
     * Truncated to 255 charcaters if needed.
     *
     * @return string
     */
    private function create_moodle_shortname($checkforcollision = false) {
        global $DB;

        $lilid = str_ireplace('urn:li:lyndaCourse:', '', $this->urn);
        $shortname = substr("{$lilid} > {$this->title}", 0, 255);

        $suffix = 2;
        $rootshortname = $shortname;
        while ($checkforcollision && $DB->record_exists('course', ['shortname' => $shortname])) {
            $shortname = $rootshortname . " ($suffix)";
            $suffix += 1;
        }
        return $shortname;
    }

    public static function get_languages() {
        global $DB;

        $options = $DB->get_records_sql_menu("SELECT language as valone, language as valtwo FROM {linkedinlearning_course} GROUP BY language");
        foreach ($options as $language) {
            if (get_string_manager()->string_exists('lang_' . $language, 'local_reportbuilder')) {
                $options[$language] = get_string('lang_' . $language, 'local_reportbuilder');
            }
        }

        return $options;
    }
}