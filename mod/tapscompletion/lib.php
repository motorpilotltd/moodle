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

function tapscompletion_add_instance($data, $mform) {
    global $DB;

    $data->timecreated = time();
    $data->timemodified = $data->timecreated;

    $result = $DB->insert_record('tapscompletion', $data);

    return $result;
}

function tapscompletion_update_instance($data, $mform) {
    global $DB;

    $data->timemodified = time();
    $data->id = $data->instance;

    $result = $DB->update_record('tapscompletion', $data);

    return $result;
}

function tapscompletion_delete_instance($id) {
    global $DB;

    if (!$tapscompletion = $DB->get_record('tapscompletion', array('id' => $id))) {
        return false;
    }

    $DB->delete_records('tapscompletion', array('id' => $tapscompletion->id));
    $DB->delete_records('tapscompletion_completion', array('tapscompletionid' => $tapscompletion->id));

    return true;
}

function tapscompletion_cm_info_dynamic(cm_info $cm) {
    global $CFG, $COURSE, $PAGE;

    if ($COURSE->id == $cm->course) {
        // We're on the actual course page.
        require_once($CFG->dirroot . '/course/modlib.php');
        $context = context_module::instance($cm->id);
        $cannotedit = !$PAGE->user_is_editing() || !can_update_moduleinfo($cm);
        $cannotcomplete = !has_capability('mod/tapscompletion:updatecompletion', $context);
        if ($cannotedit && $cannotcomplete) {
            $cm->set_no_view_link();
        }
    }
}

function tapscompletion_cm_info_view(cm_info $cm) {
    global $CFG, $DB, $PAGE, $USER;

    $renderer = $PAGE->get_renderer('mod_tapscompletion');

    $tapscompletion = new \mod_tapscompletion\tapscompletion();
    $tapscompletion->set_tapscompletion($cm->instance);
    $tapscompletion->set_course($tapscompletion->tapscompletion->course);
    $tapscompletion->set_cm('instance', $tapscompletion->tapscompletion->id, $tapscompletion->course->id);

    if (!$tapscompletion->check_installation()) {
            $cm->set_content($renderer->alert(html_writer::tag('p', get_string('installationissue', 'tapscompletion')), 'alert-danger', false));
    } else {
        $taps = new \local_taps\taps();

        list($in, $inparams) = $DB->get_in_or_equal(
            $taps->get_statuses('placed'),
            SQL_PARAMS_NAMED, 'status'
        );
        $compare = $DB->sql_compare_text('lte.bookingstatus');
        $sql = "SELECT lte.id
            FROM {local_taps_enrolment} lte
            WHERE
                lte.courseid = :tapscourse
                AND (lte.archived = 0 OR lte.archived IS NULL)
                AND {$compare} {$in}
                AND lte.staffid = :staffid
            ORDER BY
                lte.classid
            ";
        $params = array(
            'tapscourse' => $tapscompletion->tapscompletion->tapscourse,
            'staffid' => $USER->idnumber
        );

        $inprogress = $DB->get_records_sql($sql, array_merge($params, $inparams));

        if ($inprogress) {
            $cm->set_content($renderer->alert(get_string('inprogress', 'tapscompletion'), 'alert-warning'));
        } else {
            list($in, $inparams) = $DB->get_in_or_equal(
                $taps->get_statuses('attended'),
                SQL_PARAMS_NAMED, 'status'
            );
            $compare = $DB->sql_compare_text('lte.bookingstatus');
            $sql = "SELECT lte.id
                FROM {local_taps_enrolment} lte
                WHERE
                    lte.courseid = :tapscourse
                    AND (lte.archived = 0 OR lte.archived IS NULL)
                    AND lte.active = 1
                    AND {$compare} {$in}
                    AND lte.staffid = :staffid
                ORDER BY
                    lte.classid
                ";
            $params = array(
                'tapscourse' => $tapscompletion->tapscompletion->tapscourse,
                'staffid' => $USER->idnumber
            );
            $attended = $DB->get_records_sql($sql, array_merge($params, $inparams));

            if ($attended) {
                $cm->set_content($renderer->alert(get_string('attended', 'tapscompletion'), 'alert-success'));
            }
        }
    }
}

function tapscompletion_supports($feature) {
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

function tapscompletion_get_completion_state($course, $cm, $userid, $type) {
    global $DB;

    $tapscompletion = $DB->get_record('tapscompletion', array('id' => $cm->instance), '*', MUST_EXIST);

    $result = $type;

    if ($tapscompletion->completionattended) {
        $completed = $DB->get_field('tapscompletion_completion', 'completed', array('tapscompletionid' => $tapscompletion->id, 'userid' => $userid));

        $result = completion_info::aggregate_completion_states($type, $result, $completed);
    }

    return $result;
}

/**
 * Trigger the course_module_viewed event.
 *
 * @param  stdClass $tapscompletion tapscompletion object
 * @param  stdClass $course course object
 * @param  stdClass $cm course module object
 * @param  stdClass $context context object
 * @since Moodle 3.0
 */
function tapscompletion_view($tapscompletion, $course, $cm, $context) {

    // Trigger course_module_viewed event.
    $params = array(
        'context' => $context,
        'objectid' => $tapscompletion->id
    );

    $event = \mod_tapscompletion\event\course_module_viewed::create($params);
    $event->add_record_snapshot('course_modules', $cm);
    $event->add_record_snapshot('course', $course);
    $event->add_record_snapshot('tapscompletion', $tapscompletion);
    $event->trigger();
}
