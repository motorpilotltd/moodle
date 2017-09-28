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
 * Index page.
 *
 * @package     local_diskusage
 * @copyright   2016 Motorpilot Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('local_diskusage');

echo $OUTPUT->header();
echo $OUTPUT->heading('Disk Usage');

echo $OUTPUT->skip_link_target();

$sql = <<<EOS
    SELECT
        f.*,
        c.id as cid,
        c.contextlevel,
        c.instanceid
    FROM
        {files} f
    JOIN
        {context} c
        ON f.contextid = c.id
    WHERE
        f.filesize > 0
EOS;

$files = $DB->get_records_sql($sql);

$usernamefields = get_all_user_name_fields(true);
$users = $DB->get_records('user', array(), 'lastname ASC', "id, {$usernamefields}, 0 AS filesize");

$courses = $DB->get_records('course', array(), 'shortname ASC', 'id, shortname, fullname, 0 AS filesize');

$coursemodules = $DB->get_records('course_modules', array(), '', 'id, course');

$unattached = array('course' => 0, 'user' => 0);

if ($files) {
    foreach ($files as $file) {
        switch ($file->contextlevel) {
            case CONTEXT_USER :
                if (isset($users[$file->instanceid])) {
                    $users[$file->instanceid]->filesize += $file->filesize;
                } else {
                    $unattached['user'] += $file->filesize;
                }
                break;
            case CONTEXT_COURSE :
                if (isset($courses[$file->instanceid])) {
                    $courses[$file->instanceid]->filesize += $file->filesize;
                } else {
                    $unattached['course'] += $file->filesize;
                }
                break;
            case CONTEXT_MODULE :
                if (isset($coursemodules[$file->instanceid])
                        && isset($courses[$coursemodules[$file->instanceid]->course])) {
                    $courses[$coursemodules[$file->instanceid]->course]->filesize += $file->filesize;
                } else {
                    $unattached['course'] += $file->filesize;
                }
                break;
        }
    }
}

echo html_writer::tag('h3', 'Modules');
echo html_writer::tag('p', 'Only modules using over 1MB of disk space are shown.');

usort($courses, 'disk_usage_sort');

$coursetable = new html_table();
$coursetable->head = array('ID', 'Shortname', 'Disk Usage (MB)');

foreach ($courses as $course) {
    if (isset($course->filesize) && $course->filesize > (1024 * 1024)) {
        $coursetable->data[] = array($course->id, $course->shortname, round($course->filesize / (1024 * 1024), 2));
    }
}

if (empty($coursetable->data)) {
    $cell1 = new html_table_cell();
    $cell1->text = 'No usage over 1MB';
    $cell1->colspan = 3;
    $row1 = new html_table_row();
    $row1->cells[] = $cell1;
    $coursetable->data[] = $row1;
}

echo html_writer::table($coursetable);

echo html_writer::tag('h3', 'Users');
echo html_writer::tag('p', 'Only users using over 1MB of disk space are shown.');

usort($users, 'disk_usage_sort');

$usertable = new html_table();
$usertable->head = array('ID', 'Name', 'Disk Usage (MB)');

foreach ($users as $user) {
    if (isset($user->filesize) && $user->filesize > (1024 * 1024)) {
        $usertable->data[] = array($user->id, fullname($user), round($user->filesize / (1024 * 1024), 2));
    }
}

if (empty($usertable->data)) {
    $cell1 = new html_table_cell();
    $cell1->text = 'No usage over 1MB';
    $cell1->colspan = 3;
    $row1 = new html_table_row();
    $row1->cells[] = $cell1;
    $usertable->data[] = $row1;
}

echo html_writer::table($usertable);

echo html_writer::tag('h3', 'Unattached Files');

$unattachedtable = new html_table();
$unattachedtable->head = array('Type', 'Disk Usage (MB)');

$unattachedtable->data[] = array('Module', round($unattached['course'] / (1024 * 1024), 2));
$unattachedtable->data[] = array('User', round($unattached['user'] / (1024 * 1024), 2));

echo html_writer::table($unattachedtable);

echo $OUTPUT->footer();

/**
 * Array sorting callback.
 *
 * @param stdClass $a
 * @param stdClass $b
 * @return int
 */
function disk_usage_sort( $a, $b ) {
    $a->filesize = isset($a->filesize) ? $a->filesize : 0;
    $b->filesize = isset($b->filesize) ? $b->filesize : 0;
    return $a->filesize == $b->filesize ? 0 : ( $a->filesize < $b->filesize ) ? 1 : -1;
}
