<?php

define('AJAX_SCRIPT', true);

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');
require_once($CFG->dirroot . '/lib/formslib.php');
require_once(dirname(dirname(__FILE__)) . '/form/duedates.php');
require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/user/lib.php');
use local_custom_certification\certification;

// Send the correct headers.
send_headers('text/html; charset=utf-8', false);

$PAGE->set_context(context_system::instance());

$renderer = $PAGE->get_renderer('local_custom_certification');

$param = optional_param('param', null, PARAM_RAW);
$action = optional_param('action', null, PARAM_RAW);
$certifid = optional_param('certifid', null, PARAM_INT);
$searchword = optional_param('word', null, PARAM_RAW);
$itemid = optional_param('itemid', null, PARAM_INT);
$type = optional_param('type', null, PARAM_RAW);
$page = optional_param('page', 1, PARAM_INT);
$assignmentid = optional_param('assignmentid', 0, PARAM_INT);


if (!empty($certifid)) {
    $certif = new \local_custom_certification\certification($certifid);
}

switch ($action) {
    case 'modal':
        if ($param == 'individuals') {
            $where = ' WHERE u.id <> 1 AND u.deleted = 0 AND u.suspended = 0 '; //without quest user
            $params = [];
            $assignedusersids = array_keys($certif->assignedusers);

            if (!empty($assignedusersids)) {
                list($insql, $params) = $DB->get_in_or_equal($assignedusersids);

                if (count($params) > 1) {
                    $where .= ' AND u.id NOT ' . $insql;
                } else {
                    $where .= ' AND u.id !' . $insql;
                }
            }
            if (!empty($searchword)) {
                if ($searchword != 'reset') {
                    $searchword = strtolower($searchword);
                    $where .= " AND LOWER(concat(u.firstname,u.lastname)) LIKE '%$searchword%'";
                }

                $users = $DB->get_records_sql("SELECT * FROM {user} u $where", $params);
                $html = $renderer->display_searched_rows($users, $certifid, $param);
                $response = ['count' => count($users), 'content' => $html];
                echo json_encode($response);
                die();
            }

            $users = $DB->get_records_sql("SELECT * FROM {user} u $where", $params, 0, 200);

            echo $renderer->display_assignment_modal($users, $certifid, $param);

        } elseif ($param == 'cohorts') {
            $where = '';
            $params = [];
            $assignedcohortsids = array_keys($certif->assignedcohorts);

            if (!empty($assignedcohortsids)) {
                list($insql, $params) = $DB->get_in_or_equal($assignedcohortsids);
                if (count($params) > 1) {
                    $where = 'WHERE c.id NOT ' . $insql;
                } else {
                    $where = 'WHERE c.id !' . $insql;
                }
            }

            if (!empty($searchword)) {
                if ($searchword != 'reset') {
                    $searchword = strtolower($searchword);
                    if (count($params) > 0) {
                        $where .= " AND LOWER(c.name) LIKE '%$searchword%'";
                    } else {
                        $where .= "WHERE LOWER(c.name) LIKE '%$searchword%'";
                    }
                }

                $cohorts = $DB->get_records_sql("SELECT * FROM {cohort} c $where", $params);

                $html = $renderer->display_searched_rows($cohorts, $certifid, $param);

                $response = ['count' => count($cohorts), 'content' => $html];
                echo json_encode($response);
                die();
            }
            $cohorts = $DB->get_records_sql("SELECT * FROM {cohort} c $where", $params);
            echo $renderer->display_assignment_modal($cohorts, $certifid, $param);
        }
        break;
    case 'addtotable':

        if ($type == 'individuals') {
            $assignmenttype = certification::ASSIGNMENT_TYPE_USER;
            local_custom_certification\certification::add_assignment($certifid, $assignmenttype, $itemid);
            echo json_encode('success');
        } elseif ($type == 'cohorts') {
            $assignmenttype = certification::ASSIGNMENT_TYPE_AUDIENCE;
            local_custom_certification\certification::add_assignment($certifid, $assignmenttype, $itemid);
            echo json_encode('success');
        }
        \local_custom_certification\notification::add(get_string('certificationsaved', 'local_custom_certification'), \local_custom_certification\notification::TYPE_SUCCESS);
        break;
    case 'delete':

        if ($type == 'individuals') {
            local_custom_certification\certification::delete_assignment($certifid, certification::ASSIGNMENT_TYPE_USER, $itemid);
            echo json_encode('success');
        } elseif ($type == 'cohorts') {
            local_custom_certification\certification::delete_assignment($certifid, certification::ASSIGNMENT_TYPE_AUDIENCE, $itemid);
            echo json_encode('success');
        }
        break;
    case 'duedate':

        $formurl = new moodle_url('/local/custom_certification/edit.php', ['action' => 'assignments', 'id' => $certifid, 'assignmentid' => $assignmentid]);

        $assignment = $certif->assignments[$assignmentid];
        $duedatesform = new local_custom_certification\form\certification_duedates_form($formurl, [
            'assignment' => $assignment
        ]);

        $duedateshtml = $duedatesform->render();

        echo $renderer->get_duedate_modal($duedateshtml);
        break;
    case 'viewcohortuserduedates':

        $cohortuserassignments=certification::get_assigned_cohort_members_details($certifid,$assignmentid);

        echo $renderer->get_cohort_user_duedates($cohortuserassignments);

        break;
}