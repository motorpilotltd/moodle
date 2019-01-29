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

defined('MOODLE_INTERNAL') || die();

function tapsenrol_add_instance($data, $mform) {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = $data->timecreated;

    $data->internalworkflowid = $data->internalworkflowid == -1 ? 0 : $data->internalworkflowid;

    $data->id = $DB->insert_record('tapsenrol', $data);

    // Update region mapping.
    tapsenrol_region_mapping($data);

    return $data->id;
}

function tapsenrol_update_instance($data, $mform) {
    global $DB;

    $data->timemodified = time();
    $data->id = $data->instance;

    $data->internalworkflowid = $data->internalworkflowid == -1 ? 0 : $data->internalworkflowid;

    $current = $DB->get_record('tapsenrol', array('id' => $data->id), '*', MUST_EXIST);

    $result = $DB->update_record('tapsenrol', $data);

    if (!$data->internalworkflowid) {
        $DB->delete_records('tapsenrol_iw_email_custom', array('coursemoduleid' => $data->coursemodule, 'internalworkflowid' => null));
    }

    // Update region mapping.
    tapsenrol_region_mapping($data);

    return $result;
}

function tapsenrol_region_mapping($data) {
    global $DB;

    if (in_array(0, $data->region)) {
        $DB->delete_records('tapsenrol_region', array('tapsenrolid' => $data->id));
        return;
    }

    $allregions = $DB->get_records_menu('local_regions_reg', array('userselectable' => 1), '', 'id as id, id as id2');
    if (empty($allregions)) {
        return;
    }

    foreach ($allregions as $regionid) {
        if (!in_array($regionid, $data->region)) {
            $DB->delete_records('tapsenrol_region', array('regionid' => $regionid, 'tapsenrolid' => $data->id));
        } else if (!$DB->get_record('tapsenrol_region', array('regionid' => $regionid, 'tapsenrolid' => $data->id))) {
            $regionmapping = new stdClass();
            $regionmapping->tapsenrolid = $data->id;
            $regionmapping->regionid = $regionid;
            $DB->insert_record('tapsenrol_region', $regionmapping);
        }
    }
}

function tapsenrol_delete_instance($id) {
    global $DB;

    if (!$tapsenrol = $DB->get_record('tapsenrol', array('id' => $id))) {
        return false;
    }

    $DB->delete_records('tapsenrol', array('id' => $tapsenrol->id));
    $DB->delete_records('tapsenrol_completion', array('tapsenrolid' => $tapsenrol->id));
    $DB->delete_records('tapsenrol_region', array('tapsenrolid' => $tapsenrol->id));

    $sql = <<<EOS
SELECT
    cm.id
FROM
    {course_modules} cm
WHERE cm.instance = :tid
    AND cm.module = (
        SELECT
            id
        FROM {modules}
        WHERE
            name = :module
    )
EOS;
    $params = array(
        'tid' => $id,
        'module' => 'tapsenrol'
    );
    $cmid = $DB->get_field_sql($sql, $params);
    if ($cmid) {
        $DB->delete_records('tapsenrol_iw_email_custom', array('coursemoduleid' => $cmid, 'internalworkflowid' => null));
    }

    return true;
}

function tapsenrol_cm_info_dynamic(cm_info $cm) {
    global $CFG, $COURSE, $PAGE;
    if ($COURSE->id == $cm->course) {
        // We're on the actual course page.
        require_once($CFG->dirroot . '/course/modlib.php');
        if (!$PAGE->user_is_editing() || !can_update_moduleinfo($cm)) {
            $cm->set_no_view_link();
        }
        // In here as needs to be handled before view function to inject into head.
        if (!$PAGE->requires->is_head_done()) {
            $PAGE->requires->jquery();
        }
    }
}

function tapsenrol_cm_info_view(cm_info $cm) {
    global $CFG, $DB, $PAGE, $SESSION, $USER;

    require_once($CFG->dirroot.'/mod/tapsenrol/classes/tapsenrol.php');
    require_once($CFG->dirroot.'/local/regions/lib.php');

    $tapsenrol = new tapsenrol($cm->instance, 'instance');

    $renderer = $PAGE->get_renderer('mod_tapsenrol');
    $output = '';

    if (!$tapsenrol->check_installation()) {
        $output .= $renderer->alert(html_writer::tag('p', get_string('installationissue', 'tapsenrol')), 'alert-danger', false);
    } else {
        $canviewclasses = $PAGE->user_is_editing();
        $canview = true;

        $output .= $tapsenrol->enrolment_check($USER->id, true);

        $regionselect = 'id IN (SELECT regionid FROM {tapsenrol_region} WHERE tapsenrolid = :tapsenrolid)';
        $regions = $DB->get_records_select_menu('local_regions_reg', $regionselect, array('tapsenrolid' => $tapsenrol->tapsenrol->id), '', 'id, name');
        if (empty($regions)) {
            $canviewclasses = true;
        } else {
            local_regions_load_data_user($USER);
            if (isset($USER->regions_field_geotapsregionid) && array_key_exists($USER->regions_field_geotapsregionid, $regions)) {
                $canviewclasses = true;
            }
        }
        if ($canviewclasses) {
           $canviewclasses = $cm->uservisible;
        }

        $enrolmentoutput = '';
        if ($canview) {
            $classes = $tapsenrol->get_tapsclasses($canviewclasses);
            $enrolments = $tapsenrol->taps->get_enrolments($USER->id, $tapsenrol->tapsenrol->course, false, false);
            $enrolmentoutput = $renderer->enrolment_history($tapsenrol, $enrolments, $classes, $tapsenrol->cm->id);
        }

        if (!$canview || !$canviewclasses) {
            $a = new stdClass();
            $a->course = core_text::strtolower(get_string('course'));
            $a->reason = '';
            if (!$canviewclasses && !empty($cm->availableinfo)) {
                $a->reason = '<br>' . \core_availability\info::format_info($cm->availableinfo, $cm->get_course());
            } else if (!$canviewclasses && !empty($regions)) {
                $a->reason = get_string('cannotenrol:regions', 'tapsenrol', implode(', ', $regions));
            }
            $enrolmentoutput = $renderer->alert(html_writer::tag('p', get_string('cannotenrol', 'tapsenrol', $a)), 'alert-warning', false);
        }

        if (!empty($SESSION->tapsenrol->alert->message)) {
            $output .= $renderer->alert($SESSION->tapsenrol->alert->message, $SESSION->tapsenrol->alert->type);
            unset($SESSION->tapsenrol->alert);
        }

        $output .= html_writer::start_tag('div', array('class' => 'tapsenrol_info_wrapper'));
        $output .= $enrolmentoutput;
        $output .= html_writer::end_tag('div'); // End div tapsenrol_info_wrapper.
    }

    $output .= $renderer->admin_links($tapsenrol);

    $cm->set_content($output);

    $PAGE->requires->js('/mod/tapsenrol/js/tapsenrol.js', false);
}

function tapsenrol_supports($feature) {
    switch($feature) {
        case FEATURE_GRADE_HAS_GRADE:
            return false;
        case FEATURE_GRADE_OUTCOMES:
            return false;
        case FEATURE_ADVANCED_GRADING:
            return false;
        case FEATURE_CONTROLS_GRADE_VISIBILITY:
            return false;
        case FEATURE_COMPLETION_TRACKS_VIEWS:
            return false;
        case FEATURE_COMPLETION_HAS_RULES:
            return true;
        case FEATURE_NO_VIEW_LINK:
            return false;
        case FEATURE_IDNUMBER:
            return false;
        case FEATURE_GROUPS:
            return false;
        case FEATURE_GROUPINGS:
            return false;
        case FEATURE_MOD_ARCHETYPE:
            return MOD_ARCHETYPE_OTHER;
        case FEATURE_MOD_INTRO:
            return false;
        case FEATURE_MODEDIT_DEFAULT_COMPLETION:
            return false;
        case FEATURE_COMMENT:
            return false;
        case FEATURE_RATE:
            return false;
        case FEATURE_BACKUP_MOODLE2:
            return false;
        case FEATURE_SHOW_DESCRIPTION:
            return false;
        default:
            return null;
    }
}

function tapsenrol_get_completion_state($course, $cm, $userid, $type) {
    global $DB;

    $tapsenrol = $DB->get_record('tapsenrol', array('id' => $cm->instance), '*', MUST_EXIST);

    $result = $type;

    if ($tapsenrol->completionattended) {
        $completed = $DB->get_field('tapsenrol_completion', 'completed', array('tapsenrolid' => $tapsenrol->id, 'userid' => $userid));

        $result = completion_info::aggregate_completion_states($type, $result, $completed);
    }

    return $result;
}
