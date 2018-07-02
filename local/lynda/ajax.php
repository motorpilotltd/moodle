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

try {
    require_login();
    require_sesskey();

    $action = required_param('action', PARAM_ALPHA);

    $PAGE->set_context(context_system::instance());
    $PAGE->set_url('/local/lynda/ajax.php');

    switch ($action) {
        case 'setregion' :
            $regionid = required_param('regionid', PARAM_INT);
            $courseid = required_param('courseid', PARAM_INT);
            $state = required_param('state', PARAM_BOOL);

            $course = \local_lynda\lyndacourse::fetch(['id' => $courseid]);
            $course->setregionstate($regionid, $state);
            exit;
            break;
        case 'setregions' :
            $regionid = required_param('regionid', PARAM_INT);
            $courseids = required_param('courseids', PARAM_TEXT);
            $state = required_param('state', PARAM_BOOL);
            $courseids = explode(',', $courseids);
            $courses = \local_lynda\lyndacourse::fetchbyids($courseids);
            foreach ($courses as $course) {
                $course->setregionstate($regionid, $state);
            }
            exit;
            break;
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
