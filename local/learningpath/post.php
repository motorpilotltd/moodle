<?php
try {
    require_once(dirname(__FILE__) . '/../../config.php');
    require_login(SITEID, false);

    $pk = required_param('pk', PARAM_INT);
    $name = required_param('name', PARAM_ALPHA);
    $value = required_param('value', PARAM_TEXT);
    $axisname = trim($value);
    if (strlen($axisname) > 35) {
        throw new Exception('Cannot exceed 35 characters');
    } elseif (empty($axisname)) {
        throw new Exception('Cannot be empty');
    }
    switch ($name) {
        case 'axis':
            $axisrecord = $DB->get_record('local_learningpath_axes', array('id' => $pk));
            if (!$axisrecord) {
                throw new Exception('Could not retrieve axis record');
            }
            $learningpath = $DB->get_record('local_learningpath', array('id' => $axisrecord->learningpathid));
            if (!$learningpath) {
                throw new Exception('Could not retrieve learning path');
            }
            $catcontext = context_coursecat::instance($learningpath->categoryid, IGNORE_MISSING);
            if (!has_capability('local/learningpath:edit', $catcontext ? $catcontext : context_system::instance())) {
                throw new Exception('You do not have update permissions');
            }
            $select = 'name = :name AND learningpathid = :learningpathid AND axis = :axis AND id != :id';
            $params = array(
                'name' => $axisname,
                'learningpathid' => $axisrecord->learningpathid,
                'axis' => $axisrecord->axis,
                'id' => $axisrecord->id,
            );
            if ($DB->get_record_select('local_learningpath_axes', $select, $params)) {
                throw new Exception('Name already in use');
            }
            $axisrecord->name = $axisname;
            $DB->update_record('local_learningpath_axes', $axisrecord);
            break;
        default:
            throw new Exception('Cannot update this value');
    }
} catch (Exception $ex) {
    http_response_code(400);
    echo $ex->getMessage();
    exit;
}

