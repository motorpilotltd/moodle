<?php

define('AJAX_SCRIPT', true);

defined('MOODLE_INTERNAL') || die();

require_once(dirname(dirname(dirname(dirname(__FILE__)))) . '/config.php');

require_login();

// Send the correct headers.
send_headers('text/html; charset=utf-8', false);

$syscontext = context_system::instance();

$PAGE->set_context($syscontext);

if (!has_capability('local/dynamic_cohorts:edit', $syscontext)) {
    throw new moodle_exception('nopermissions');
}

$rulesetrenderer = $PAGE->get_renderer('local_dynamic_cohorts', 'ruleset');
$rolerenderer = $PAGE->get_renderer('local_dynamic_cohorts', 'role');

$action = optional_param('action', null, PARAM_RAW);
$rulesetscount = optional_param('rulesetscount', null, PARAM_INT);
$rulesetid = optional_param('rulesetid', null, PARAM_TEXT);
$field = optional_param('field', null, PARAM_TEXT);
$context = optional_param('context', null, PARAM_TEXT);
$criteriatype = optional_param('criteriatype', null, PARAM_INT);
$value = optional_param('value', null, PARAM_TEXT);
$ruleid = optional_param('ruleid', null, PARAM_INT);
$edit = optional_param('edit', null, PARAM_INT);
$roleid = optional_param('roleid', null, PARAM_INT);
$contextid = optional_param('contextid', null, PARAM_INT);

switch ($action) {
    case 'addruleset':
        $newrulesetid = rand(100000000,9999999999);
        echo $rulesetrenderer->display_ruleset($newrulesetid, $rulesetscount);
        break;
    case 'addrule':
        echo $rulesetrenderer->display_rule($rulesetid, $ruleid, $field, $criteriatype, $value);
        break;
    case 'editrule':
        echo $rulesetrenderer->display_rule_form($rulesetid, 'saverule', $ruleid, $field, $criteriatype, $value);
        break;
    case 'getcriteriatypes':
        echo json_encode(\local_dynamic_cohorts\dynamic_cohorts::get_criteria_types_by_field_type($field));
        break;
    case 'getvaluefield':
        echo $rulesetrenderer->value_field($rulesetid, $field, $value, $edit == 1 ? true : false);
        break;
    case 'getroles':
        echo json_encode(\local_dynamic_cohorts\dynamic_cohorts::get_roles($context));
        break;
    case 'addrole':
        echo $rolerenderer->display_role($roleid, $contextid);
        break;
}