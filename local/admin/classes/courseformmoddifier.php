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

namespace local_admin;

class courseformmoddifier {
    public static function alter_definition(\MoodleQuickForm $mform) {
        global $PAGE;

        $PAGE->requires->js_call_amd('local_admin/enhance', 'initialise');

        $courseid = $mform->getElementValue('id');

        $systemcontext = \context_system::instance();
        if (!has_capability("local/admin:createnonarupcourse", $systemcontext)) {
            self::freezeandhideunwantedelements($mform);

            if (empty($courseid)) {
                self::addarupelements($mform);
            }
        } else {
            $arupdefaultcourse = $mform->createElement('selectyesno', 'arupdefaultcourse', get_string('arupdefaultcourse', 'local_admin'));
            $mform->insertElementBefore($arupdefaultcourse, 'fullname');
            $mform->setDefault('arupdefaultcourse', true);

            $mform->registerNoSubmitButton('updatearupdefaultcourse');
            $mform->addElement('submit', 'updatearupdefaultcourse', get_string('updatearupdefaultcourse', 'local_admin'));

            $default = $mform->getElementValue('arupdefaultcourse');
            if (isset($default[0]) && !empty($default[0])) {
                if (empty($courseid)) {
                    self::addarupelements($mform);
                }

                self::freezeandhideunwantedelements($mform);
            }
        }
    }

    // If data is null then we create an arup default course
    public static function post_creation($course, $data = null) {
        global $DB;

        if (isset($data)) {
            $internalworkflowid = $data->internalworkflowid == -1 ? 0 : $data->internalworkflowid;
            $enrolmentregion = !empty($data->enrolmentregion) ? $data->enrolmentregion : array();
            $enrolmentrole = $data->enrolmentrole;

            if ($data->arupdefaultcourse == false) {
                return;
            }
        } else {
            $internalworkflowid = 0;
            $enrolmentregion = [];
            $enrolmentrole = get_config('local_admin', 'default_enrolment_role');
        }

        $transaction = $DB->start_delegated_transaction();
        self::add_default_enrols($course, $enrolmentrole);

        self::add_default_activities_complation($course, $internalworkflowid, $enrolmentregion);
        $transaction->allow_commit();
    }

    private static function add_default_enrols($course, $enrolmentrole) {
        $instances = enrol_get_instances($course->id, false);
        $plugins = enrol_get_plugins(false);

        foreach ($instances as $instance) {
            $plugin = $plugins[$instance->enrol];
            $plugin->delete_instance($instance);
        }

        // Now setup required enrolment plugins.
        // Manual...
        $enrolmanual = enrol_get_plugin('manual');
        if ($enrolmanual) {
            $enrolmanualfields = array(
                    'status'      => ENROL_INSTANCE_ENABLED,
                    'enrolperiod' => 0,
                    'roleid'      => $enrolmentrole
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
                    'roleid'      => $enrolmentrole
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
    }

    private static function add_default_activities_complation($course, $internalworkflowid, $enrolmentregion) {
        global $DB, $CFG;

        require_once("$CFG->dirroot/mod/tapsenrol/lib.php");
        require_once($CFG->dirroot . '/completion/criteria/completion_criteria_activity.php');
        require_once($CFG->dirroot . '/completion/criteria/completion_criteria.php');

        $course->enablecompletion = COMPLETION_ENABLED;
        $course->completionstartonenrol = 1;
        $course->groupmode = VISIBLEGROUPS;
        $course->groupmodeforce = 0;
        $course->defaultgroupingid = 0;
        update_course($course);

        $tapsmodule = $DB->get_record("modules", ["name" => "tapsenrol"]);

        // Add tapsenrol activity.
        $newcm = new \stdClass();
        $newcm->course = $course->id;
        $newcm->module = $tapsmodule->id;
        $newcm->instance = 0;
        $newcm->visible = 1;
        $newcm->groupmode = VISIBLEGROUPS;
        $newcm->groupingid = 0;
        $newcm->section = 0;

        $newcm->completion = COMPLETION_TRACKING_AUTOMATIC;
        $newcm->showdescription = 0;

        $cmid = add_course_module($newcm);

        $tapsenrol = new \stdClass();
        $tapsenrol->course = $course->id;
        $tapsenrol->name = 'Linked course Enrolment';
        $tapsenrol->completionenrolment = 1;
        $tapsenrol->internalworkflowid = $internalworkflowid;
        $tapsenrol->region = $enrolmentregion;
        $return = \tapsenrol_add_instance($tapsenrol, null);

        $newcm->instance = $return;

        $DB->set_field('course_modules', 'instance', $newcm->instance, array('id' => $cmid));

        course_add_cm_to_section($newcm->course, $cmid, $newcm->section);

        set_coursemodule_visible($cmid, $newcm->visible);

        // Add to completion criteria.
        $completiondata = new \stdClass();
        $completiondata->id = $course->id;
        $completiondata->criteria_activity = array(
                $cmid => 1
        );

        $criterion = new \completion_criteria_activity();
        $criterion->update_config($completiondata);

        $cm = get_coursemodule_from_id('tapsenrol', $cmid);
        $modcontext = \context_module::instance($cmid);
        $event = \core\event\course_module_created::create_from_cm($cm, $modcontext);
        $event->trigger();

        // Handle completion aggregation (Adapted from course/completion.php).
        $criteriatypes = array(
                null,
                COMPLETION_CRITERIA_TYPE_ACTIVITY,
                COMPLETION_CRITERIA_TYPE_COURSE,
                COMPLETION_CRITERIA_TYPE_ROLE);

        foreach ($criteriatypes as $criteriatype) {
            $aggregation = new \completion_aggregation(
                    array(
                            'course'       => $course->id,
                            'criteriatype' => $criteriatype));
            $aggregation->setMethod(COMPLETION_AGGREGATION_ALL);
            $aggregation->save();
        }

        // Trigger an event for course module completion changed.
        $event = \core\event\course_completion_updated::create(
                array(
                        'courseid' => $course->id,
                        'context'  => \context_course::instance($course->id)
                )
        );
        $event->trigger();

        rebuild_course_cache($course->id);
    }

    /**
     * @param \MoodleQuickForm $mform
     */
    private static function freezeandhideunwantedelements(\MoodleQuickForm $mform) {
        // Hard freeze and hide everything from courseformathdr up to but not including buttonarr.
        // Add configure standard arup course checkbox and JS
        $removestart = $mform->_elementIndex['courseformathdr'];
        $stopbefore = $mform->_elementIndex['buttonar'];

        $elements_to_freeze = array_slice($mform->_elements, $removestart, $stopbefore - $removestart, true);
        $elementnamestofreeze = [];

        foreach ($elements_to_freeze as $element) {

            if ($element->getType() == 'header') {
                $mform->removeElement($element->getName());
            } else {
                $elementnamestofreeze[] = $element->getName();

                $class = $element->getAttribute('class');
                if (empty($class)) {
                    $class = '';
                }
                $element->updateAttributes(array('class' => $class . ' hidden'));
            }
        }

        // Fake element to stop js for courseformat picker from freaking out.
        $mform->addElement('html', \html_writer::tag('span', '', ['id' => 'id_updatecourseformat'])); // Spacer.

        $systemcontext = \context_system::instance();
        if (!has_capability("local/admin:createnonarupcourse", $systemcontext)) {
            $elementnamestofreeze[] = 'arupdefaultcourse';
        }

        $mform->hardFreeze($elementnamestofreeze);
    }

    /**
     * @param \MoodleQuickForm $mform
     * @param $DB
     */
    private static function addarupelements(\MoodleQuickForm $mform) {
        global $DB;

        $mform->addElement('html', \html_writer::tag('div', ' ', ['class' => 'hidden', 'id' => 'id_updatecourseformat']));

        $enrolment = $mform->createElement('header', 'enrolment', get_string('enrolment', 'local_admin'));
        $mform->insertElementBefore($enrolment, 'courseformathdr');

        $enrolmentroles = array('' => get_string('choosedots')) + get_default_enrol_roles(\context_course::instance(SITEID));
        $enrolmentrole =
                $mform->createElement('select', 'enrolmentrole', get_string('enrolmentrole', 'local_admin'), $enrolmentroles);
        $mform->insertElementBefore($enrolmentrole, 'courseformathdr');
        $mform->addRule('enrolmentrole', null, 'required', null, 'client');
        $mform->addHelpButton('enrolmentrole', 'enrolmentrole', 'local_admin');
        $mform->setDefault('enrolmentrole', get_config('local_admin', 'default_enrolment_role'));

        $dbregionoptions =
                $DB->get_records_select_menu('local_regions_reg', 'userselectable = 1', array(), 'name DESC', 'id, name');
        $regionoptions = array(0 => get_string('global', 'local_regions')) + $dbregionoptions;
        $size = min(array(count($regionoptions), 10));
        $regionattributes = array('size' => $size, 'style' => 'min-width:200px');

        $internalworkflowhdr =
                $mform->createElement('header', 'internalworkflowhdr', get_string('internalworkflowhdr', 'local_admin'));
        $mform->insertElementBefore($internalworkflowhdr, 'courseformathdr');

        $options = array('' => get_string('choosedots'));
        $iws = $DB->get_records_menu('tapsenrol_iw', null, 'name ASC', 'id, name');
        $internalworkflowid =
                $mform->createElement('select', 'internalworkflowid', get_string('internalworkflow', 'tapsenrol'), $options + $iws);
        $mform->insertElementBefore($internalworkflowid, 'courseformathdr');
        $mform->addRule('internalworkflowid', null, 'required', null, 'client');
        $mform->addHelpButton('internalworkflowid', 'internalworkflow', 'tapsenrol');

        $enrolmentregion = $mform->createElement('select', 'enrolmentregion', get_string('enrolment_region_mapping', 'tapsenrol'),
                $regionoptions, $regionattributes);
        $mform->insertElementBefore($enrolmentregion, 'courseformathdr');
        $enrolmentregion->setMultiple(true);
        $enrolmenthint = \html_writer::tag('div', get_string('overrideregions', 'tapsenrol'), array('class' => 'felement fselect'));
        $enrolmenthint = $mform->createElement('html', \html_writer::tag('div', $enrolmenthint, array('class' => 'fitem')));
        $mform->insertElementBefore($enrolmenthint, 'courseformathdr');
    }
}