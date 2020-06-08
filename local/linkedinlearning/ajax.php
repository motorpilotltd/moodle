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

require_once("../../config.php");

require_login();
require_sesskey();

$context = context_system::instance();

$action = required_param('action', PARAM_ALPHA);

$PAGE->set_context($context);
$PAGE->set_url('/local/linkedinlearning/ajax.php');

$regionid = required_param('regionid', PARAM_INT);
$state = required_param('state', PARAM_BOOL);

$userregion = $DB->get_field('local_regions_use', 'regionid', array('userid' => $USER->id));
if (!$userregion) {
    print_error('No region assigned to current user');
}
$context = context_system::instance();

if ($userregion == $regionid) {
    if (!has_any_capability(['local/linkedinlearning:manageglobal', 'local/linkedinlearning:manage'], $context)) {
        throw new required_capability_exception($context, 'local/linkedinlearning:manageglobal or local/linkedinlearning:manage', 'nopermissions');
    }
} else {
    require_capability('local/linkedinlearning:manageglobal', $context);
}

switch ($action) {
    case 'setregion' :
        $courseid = required_param('courseid', PARAM_INT);
        $course = \local_linkedinlearning\course::fetch(['id' => $courseid]);
        $course->setregionstate($regionid, $state);
        exit;
        break;
    case 'setregions' :
        $courseids = required_param('courseids', PARAM_TEXT);
        $courseids = explode(',', $courseids);
        $courses = \local_linkedinlearning\course::fetchbyids($courseids);
        foreach ($courses as $course) {
            $course->setregionstate($regionid, $state);
        }
        exit;
        break;
}
