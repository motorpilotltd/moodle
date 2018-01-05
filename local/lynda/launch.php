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
 * @package    local_lynda
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once($CFG->dirroot . '/mod/lti/lib.php');
require_once($CFG->dirroot . '/mod/lti/locallib.php');
require_once($CFG->dirroot . '/local/regions/lib.php');

$userregion = local_regions_get_user_region($USER);

$lyndacourseid = required_param('lyndacourseid', PARAM_INT);

require_login();
if (!$userregion || !\local_lynda\lib::enabledforregion($userregion->regionid)) {
    print_error('Lynda.com is not available in your region');
}

$PAGE->set_context(context_system::instance());
$PAGE->set_course($SITE);

$data = ["id"                            => -1,
         "course"                        => $PAGE->course->id,
         "toolurl"                       => "https://www.lynda.com/portal/lti/course/$lyndacourseid",
         "securetoolurl"                 => "",
         "instructorchoicesendname"      => 1,
         "instructorchoicesendemailaddr" => 1,
         "instructorchoiceallowroster"   => null,
         "instructorchoiceallowsetting"  => null,
         "instructorcustomparameters"    => "",
         "instructorchoiceacceptgrades"  => 1,
         "grade"                         => 100,
         "launchcontainer"               => 1,
         "debuglaunch"                   => 0,
         "showtitlelaunch"               => 1,
         "showdescriptionlaunch"         => 0,
         "servicesalt"                   => "5a4ccaf9002253.88329128"
];
$instance = (object) $data;
list($endpoint, $parms) = lti_get_launch_data($instance);
$content = lti_post_launch_html($parms, $endpoint, $instance->debuglaunch);

echo $content;