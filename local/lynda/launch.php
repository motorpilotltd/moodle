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
         "name"                          => 'Dummy LTI',
         "launchcontainer"               => 1,
         "debuglaunch"                   => 1,
         "showtitlelaunch"               => 1,
         "showdescriptionlaunch"         => 0,
         "servicesalt"                   => "5a4ccaf9002253.88329128"
];

$config = get_config('local_lynda');
if (empty($config->{'ltikey_' . $userregion->regionid}) || empty($config->{'ltisecret_' . $userregion->regionid})) {
    print_error('LTI keys are not configured for your region');
}

$data['resourcekey'] = $config->{'ltikey_' . $userregion->regionid};
$data['password'] = $config->{'ltisecret_' . $userregion->regionid};

$instance = (object) $data;
list($endpoint, $newparms) = lti_get_launch_data($instance);

// The next chunk of code is taken from lti_post_launch_html in mod/lti/locallib.php
$r = "<form action=\"" . $endpoint .
        "\" name=\"ltiLaunchForm\" id=\"ltiLaunchForm\" method=\"post\" encType=\"application/x-www-form-urlencoded\">\n";

// Contruct html for the launch parameters.
foreach ($newparms as $key => $value) {
    $key = htmlspecialchars($key);
    $value = htmlspecialchars($value);
    if ($key == "ext_submit") {
        $r .= "<input type=\"submit\"";
    } else {
        $r .= "<input type=\"hidden\" name=\"{$key}\"";
    }
    $r .= " value=\"";
    $r .= $value;
    $r .= "\"/>\n";
}
$r .= "</form>\n";
$r .= " <script type=\"text/javascript\"> \n" .
        "  //<![CDATA[ \n" .
        "    setTimeout(function(){ document.ltiLaunchForm.submit(); }, 3000); \n" .
        "  //]]> \n" .
        " </script> \n";
// End chunk of copied code.

echo $r;

$PAGE->set_pagelayout('standard');
$PAGE->set_url('/local/lynda/launch.php', ['lyndacourseid' => $lyndacourseid]);
echo $OUTPUT->header();
echo html_writer::div(get_string('redirectmessage', 'local_lynda'), "alert alert-success");
echo $OUTPUT->footer();
