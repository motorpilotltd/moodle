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
 * This hack is intended for clustered sites that do not want
 * to use shared cachedir for component cache.
 *
 * This file needs to be called after any change in PHP files in dataroot,
 * that is before upgrade and install.
 *
 * @package   core
 * @copyright 2013 Petr Skoda (skodak)  {@link http://skodak.org}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


define('CLI_SCRIPT', true);

require(__DIR__.'/../../../config.php');
require_once($CFG->libdir.'/clilib.php');

// Now get cli options.
list($options, $unrecognized) = cli_get_params(
    array(
        'file'    => false,
        'region' => false,
        'help'    => false
    ),
    array(
        'h' => 'help',
        'f' => 'file',
        'r' => 'region'
    )
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized), 2);
}

if (!$options['region'] || !$options['file']) {
    $help =
"
Options:
-h, --help            Print out this help
--region              A region name - e.g. Americas
--file=filepath       Path to a list of course names to add

Example:
\$ php local/linkedinlearning/cli/bulkcreatecourses.php --region=\"Americas\" --file=/Users/andrewhancox/Desktop/courses.csv
";

    echo $help;
    exit(0);
}
$region = $DB->get_record('local_regions_reg', ['name' => $options['region']]);

if (!isset($region)) {
    mtrace('Invalid region');
    exit(0);
}

try {
    $handle = fopen($options['file'], "r");
} catch (\Exception $ex) {
}

if (!$handle) {
    mtrace('Error loading file');
    exit(0);
}

cron_setup_user();

while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
    $num = count($data);
    if ($num > 1) {
        mtrace('Too many columns');
        exit(0);
    }
    $title = $data[0];

    $course = \local_linkedinlearning\course::fetch(['title' => $title]);
    if (!$course) {
        mtrace('Unable to find course: ' . $course);
    }

    $course = \local_linkedinlearning\course::fetch(['title' => $title]);
    $course->setregionstate($region->id, true);

    mtrace('Processed course: ' . $title);
}
fclose($handle);
