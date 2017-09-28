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
 * The local_taps add course page.
 *
 * @package    local_taps
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");

require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/local/taps/forms/choosecategory_form.php');
require_once($CFG->dirroot.'/local/taps/forms/addcourse_form.php');

admin_externalpage_setup('local_taps_addcourse');

$regionsinstalled = get_config('local_regions', 'version');
$coursemetadatainstalled = get_config('local_coursemetadata', 'version');
$arupadvertinstalled = $DB->get_record('modules', array('name' => 'arupadvert'));
$tapsenrolinstalled = $DB->get_record('modules', array('name' => 'tapsenrol'));
$tapscompletioninstalled = $DB->get_record('modules', array('name' => 'tapscompletion'));

$output = '';

if ($regionsinstalled && $coursemetadatainstalled && $arupadvertinstalled && $tapsenrolinstalled && $tapscompletioninstalled) {
    require_once($CFG->dirroot.'/mod/arupadvert/lib.php');
    require_once($CFG->dirroot.'/mod/tapsenrol/lib.php');
    require_once($CFG->dirroot.'/mod/tapscompletion/lib.php');
    require_once($CFG->dirroot.'/local/regions/lib.php');
    require_once($CFG->dirroot.'/local/coursemetadata/lib.php');

    $tapscourseid = optional_param('courseid', 0, PARAM_INT);
    $categoryid = optional_param('category', 0, PARAM_INT);
    if (!$categoryid) {
        $category = null;
    } else {
        $category = $DB->get_record('course_categories', array('id' => $categoryid), '*', MUST_EXIST);
        $catcontext = context_coursecat::instance($category->id);
        require_capability('moodle/course:create', $catcontext);
        $PAGE->set_context($catcontext);
    }

    if (!$category) {
        $choosecategoryform = new local_taps_choosecategory_form(null, ['courseid' => $tapscourseid]);
        ob_start();
        $choosecategoryform->display();
        $output .= ob_get_clean();
    } else {

        $courseconfig = get_config('moodlecourse');

        $addcourseform = new local_taps_addcourse_form(
                null,
                array(
                    'category' => $category,
                    'courseid' => $tapscourseid,
                    'courseconfig' => $courseconfig
                ));

        if ($addcourseform->is_cancelled()) {
            redirect($PAGE->url);
        } else if ($data = $addcourseform->get_data()) {
            require_once($CFG->libdir.'/grouplib.php');
            require_once($CFG->libdir.'/enrollib.php');
            require_once($CFG->libdir.'/completionlib.php');
            require_once($CFG->dirroot.'/completion/criteria/completion_criteria_activity.php');

            $tapscourse = $DB->get_record('local_taps_course', array('courseid' => $data->tapscourse));

            $data->fullname = strip_tags($tapscourse->coursename);
            if (!$data->shortname) {
                $data->shortname = $tapscourse->courseid;
            }
            $data->idnumber = $tapscourse->courseid;
            $data->summary = html_writer::tag(
                    'p',
                    ($tapscourse->onelinedescription ? strip_tags($tapscourse->onelinedescription) : '')
                );
            $data->summaryformat = FORMAT_HTML;
            $data->format = $courseconfig->format;
            $data->coursedisplay = $courseconfig->coursedisplay;
            $data->numsections = $courseconfig->numsections;
            $data->startdate = time() + (3600 * 24);
            $data->hiddensections = $courseconfig->hiddensections;
            $data->newsitems = $courseconfig->newsitems;
            $data->showgrades = $courseconfig->showgrades;
            $data->showreports = $courseconfig->showreports;
            $data->maxbytes = $courseconfig->maxbytes;

            if (!empty($CFG->legacyfilesinnewcourses)) {
                if (!isset($courseconfig->legacyfiles)) {
                    $courseconfig->legacyfiles = 0;
                }
                $data->legacyfiles = $courseconfig->legacyfiles;
            }

            // Visible groups, no group forcing.
            $data->groupmode = VISIBLEGROUPS;
            $data->groupmodeforce = 0;
            $data->defaultgroupingid = 0;

            $data->visible = $courseconfig->visible;
            $data->lang = $courseconfig->lang;

            $data->enablecompletion = COMPLETION_ENABLED;
            $data->completionstartonenrol = 1;

            // Temporarily disable default enrolment plugins.
            $enrolpluginsenabled = $CFG->enrol_plugins_enabled;
            $CFG->enrol_plugins_enabled = '';

            $course = create_course($data, null);

            // And reset now course is created without any enrolment plugins.
            $CFG->enrol_plugins_enabled = $enrolpluginsenabled;

            // Now setup required enrolment plugins.
            // Manual...
            $enrolmanual = enrol_get_plugin('manual');
            if ($enrolmanual) {
                $enrolmanualfields = array(
                    'status' => ENROL_INSTANCE_ENABLED,
                    'enrolperiod' => 0,
                    'roleid' => $data->enrolmentrole
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
                    'roleid'      => $data->enrolmentrole
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

            // If empty, skip, use region(s) from linked course.
            if (!empty($data->catalogueregion)) {
                $regionsdata = new stdClass();
                $regionsdata->id = $course->id;
                $regionsdata->regions_field_region = $data->catalogueregion;
                local_regions_save_data_course($regionsdata);
            }

            $coursemetadatadata = $data;
            $coursemetadatadata->id = $course->id;
            coursemetadata_save_data($coursemetadatadata);

            // Create rest of sections.
            $sections = range(1, $course->numsections);
            course_create_sections_if_missing($course->id, $sections);

            // Add advert activity.
            $newcm = new stdClass();
            $newcm->course = $course->id;
            $newcm->module = $arupadvertinstalled->id;
            $newcm->instance = 0;
            $newcm->visible = 1;
            $newcm->groupmode = VISIBLEGROUPS;
            $newcm->groupingid = 0;

            $newcm->completion = COMPLETION_TRACKING_NONE;
            $newcm->showdescription = 0;

            $transaction = $DB->start_delegated_transaction();

            $newcm->coursemodule = add_course_module($newcm);
            $newcm->section = 0;

            $modulename = $arupadvertinstalled->name;

            $arupadvert = new stdClass();
            // Main advert fields.
            $arupadvert->course = $course->id;
            $arupadvert->name = 'Advert';
            $arupadvert->datatype = 'taps';
            $arupadvert->coursemodule = $newcm->coursemodule;
            $arupadvert->advertblockimage = $data->advertblockimage;
            $arupadvert->altword = $data->altword;
            $arupadvert->showheadings = 1;
            // TAPS datatype fields.
            $arupadvert->tapscourseid = $data->tapscourse;
            $arupadvert->overrideregion = !empty($data->catalogueregion);

            $return = arupadvert_add_instance($arupadvert, null);

            if (!$return or !is_number($return)) {
                // Undo everything we can. This is not necessary for databases which
                // support transactions, but improves consistency for other databases.
                context_helper::delete_instance(CONTEXT_MODULE, $newcm->coursemodule);
                $DB->delete_records('course_modules', array('id' => $newcm->coursemodule));

                if ($e instanceof moodle_exception) {
                    throw $e;
                } else if (!is_number($return)) {
                    print_error('invalidfunction', '', course_get_url($course, $newcm->section));
                } else {
                    print_error('cannotaddnewmodule', '', course_get_url($course, $newcm->section), $modulename);
                }
            }

            $newcm->instance = $return;

            // This happens in arupadvert_add_instance but no harm leaving it here...
            $DB->set_field('course_modules', 'instance', $newcm->instance, array('id' => $newcm->coursemodule));

            course_add_cm_to_section($newcm->course, $newcm->coursemodule, $newcm->section);

            set_coursemodule_visible($newcm->coursemodule, $newcm->visible);

            $eventdata = clone $newcm;
            $eventdata->name = $arupadvert->name;
            $eventdata->modname = $modulename;
            $eventdata->id = $eventdata->coursemodule;
            $modcontext = context_module::instance($eventdata->coursemodule);
            $event = \core\event\course_module_created::create_from_cm($eventdata, $modcontext);
            $event->trigger();

            $transaction->allow_commit();

            // Add tapsenrol activity.
            $newcm = new stdClass();
            $newcm->course = $course->id;
            $newcm->module = $tapsenrolinstalled->id;
            $newcm->instance = 0;
            $newcm->visible = 1;
            $newcm->groupmode = VISIBLEGROUPS;
            $newcm->groupingid = 0;

            $newcm->completion = COMPLETION_TRACKING_AUTOMATIC;
            $newcm->showdescription = 0;

            $transaction = $DB->start_delegated_transaction();

            $tapsenrolcmid = $newcm->coursemodule = add_course_module($newcm);

            $newcm->section = 0;

            $modulename = $tapsenrolinstalled->name;

            $tapsenrol = new stdClass();
            $tapsenrol->course = $course->id;
            $tapsenrol->name = 'Linked course Enrolment';
            $tapsenrol->tapscourse = $data->tapscourse;
            $tapsenrol->completionenrolment = 1;
            $tapsenrol->internalworkflowid = $data->internalworkflowid == -1 ? 0 : $data->internalworkflowid;
            $tapsenrol->region = !empty($data->enrolmentregion) ? $data->enrolmentregion : array();
            $return = tapsenrol_add_instance($tapsenrol, null);

            if (!$return or !is_number($return)) {
                // Undo everything we can. This is not necessary for databases which
                // support transactions, but improves consistency for other databases.
                context_helper::delete_instance(CONTEXT_MODULE, $newcm->coursemodule);
                $DB->delete_records('course_modules', array('id' => $newcm->coursemodule));

                if ($e instanceof moodle_exception) {
                    throw $e;
                } else if (!is_number($return)) {
                    print_error('invalidfunction', '', course_get_url($course, $newcm->section));
                } else {
                    print_error('cannotaddnewmodule', '', course_get_url($course, $newcm->section), $modulename);
                }
            }

            $newcm->instance = $return;

            $DB->set_field('course_modules', 'instance', $newcm->instance, array('id' => $newcm->coursemodule));

            course_add_cm_to_section($newcm->course, $newcm->coursemodule, $newcm->section);

            set_coursemodule_visible($newcm->coursemodule, $newcm->visible);

            // Add to completion criteria.
            $completiondata = new stdClass();
            $completiondata->id = $course->id;
            $completiondata->criteria_activity = array(
                $newcm->coursemodule => 1
            );

            $criterion = new completion_criteria_activity();
            $criterion->update_config($completiondata);

            $eventdata = clone $newcm;
            $eventdata->name = $tapsenrol->name;
            $eventdata->modname = $modulename;
            $eventdata->id = $eventdata->coursemodule;
            $modcontext = context_module::instance($eventdata->coursemodule);
            $event = \core\event\course_module_created::create_from_cm($eventdata, $modcontext);
            $event->trigger();

            $transaction->allow_commit();

            // Add tapscompletion activity.
            $newcm = new stdClass();
            $newcm->course = $course->id;
            $newcm->module = $tapscompletioninstalled->id;
            $newcm->instance = 0;
            $newcm->visible = 1;
            $newcm->groupmode = VISIBLEGROUPS;
            $newcm->groupingid = 0;
            $newcm->groupmembersonly = 0;

            $newcm->completion = COMPLETION_TRACKING_AUTOMATIC;
            $newcm->showdescription = 0;

            $transaction = $DB->start_delegated_transaction();

            $newcm->coursemodule = add_course_module($newcm);
            $newcm->section = $course->numsections;

            $modulename = $tapscompletioninstalled->name;

            $tapscompletion = new stdClass();
            $tapscompletion->course = $course->id;
            $tapscompletion->name = 'Linked Course Completion';
            $tapscompletion->tapscourse = $data->tapscourse;
            $tapscompletion->completionattended = 1;
            $tapscompletion->autocompletion = 0;
            $tapscompletion->completiontimetype = 1;

            $return = tapscompletion_add_instance($tapscompletion, null);

            if (!$return or !is_number($return)) {
                // Undo everything we can. This is not necessary for databases which
                // support transactions, but improves consistency for other databases.
                context_helper::delete_instance(CONTEXT_MODULE, $newcm->coursemodule);
                $DB->delete_records('course_modules', array('id' => $newcm->coursemodule));

                if ($e instanceof moodle_exception) {
                    throw $e;
                } else if (!is_number($return)) {
                    print_error('invalidfunction', '', course_get_url($course, $newcm->section));
                } else {
                    print_error('cannotaddnewmodule', '', course_get_url($course, $newcm->section), $modulename);
                }
            }

            $newcm->instance = $return;

            $DB->set_field('course_modules', 'instance', $newcm->instance, array('id' => $newcm->coursemodule));

            course_add_cm_to_section($newcm->course, $newcm->coursemodule, $newcm->section);

            set_coursemodule_visible($newcm->coursemodule, $newcm->visible);

            // Add to completion criteria.
            $completiondata = new stdClass();
            $completiondata->id = $course->id;
            $completiondata->criteria_activity = array(
                $newcm->coursemodule => 1
            );

            $criterion = new completion_criteria_activity();
            $criterion->update_config($completiondata);

            // Availability.
            $structure = new \stdClass();
            $structure->cm = $tapsenrolcmid;
            $structure->e = COMPLETION_COMPLETE;
            $completioncondition = new \availability_completion\condition($structure);
            $children = array($completioncondition->save());
            $rootjson = json_encode(\core_availability\tree::get_root_json($children, \core_availability\tree::OP_AND, false));
            $DB->set_field('course_modules', 'availability', $rootjson, array('id' => $newcm->coursemodule));

            $eventdata = clone $newcm;
            $eventdata->name = $tapsenrol->name;
            $eventdata->modname = $modulename;
            $eventdata->id = $eventdata->coursemodule;
            $modcontext = context_module::instance($eventdata->coursemodule);
            $event = \core\event\course_module_created::create_from_cm($eventdata, $modcontext);
            $event->trigger();

            $transaction->allow_commit();

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

            // Handle completion aggregation (Adapted from course/completion.php).
            $criteriatypes = array(
                null,
                COMPLETION_CRITERIA_TYPE_ACTIVITY,
                COMPLETION_CRITERIA_TYPE_COURSE,
                COMPLETION_CRITERIA_TYPE_ROLE);
            
            foreach ($criteriatypes as $criteriatype) {
                $aggregation = new completion_aggregation(
                        array(
                            'course' => $course->id,
                            'criteriatype'=> $criteriatype));
                $aggregation->setMethod(COMPLETION_AGGREGATION_ALL);
                $aggregation->save();
            }

            // Trigger an event for course module completion changed.
            $event = \core\event\course_completion_updated::create(
                    array(
                        'courseid' => $course->id,
                        'context' => context_course::instance($course->id)
                        )
                    );
            $event->trigger();

            rebuild_course_cache($course->id);

            redirect(new moodle_url($CFG->wwwroot.'/course/view.php', array('id' => $course->id)));
        }
        ob_start();
        $addcourseform->display();
        $output .= ob_get_clean();
    }

} else {
    echo html_writer::tag('p', get_string('modsnotinstalled', 'local_taps'));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('addcourse', 'local_taps'));

echo $output;

echo $OUTPUT->footer();