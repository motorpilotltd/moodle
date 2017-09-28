<?php
// This file is part of the Arup Course Management system
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
 * Version details
 *
 * @package     local_coursemanager
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Gets the form file
 *
 * @param object $course
 * @param object $cm
 * @param object $context
 * @param object $filearea
 * @param object $args
 * @param object $forcedownload
 * @param object $options
 */

function local_coursemanager_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload,
array $options=array()) {
    global $DB, $CFG, $USER;

    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }

    $validfileareas = array('coursedescription', 'courseobjectives', 'courseaudience',
            'onelinedescription', 'businessneed', 'keywords');

    if (in_array($filearea, $validfileareas)) {
        $itemid = $args[0];
        $fullpath = "/$context->id/local_coursemanager/$filearea/$itemid/".$args[1];
        $fs = get_file_storage();
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            send_file_not_found();
        }
        $lifetime = isset($CFG->filelifetime) ? $CFG->filelifetime : 86400;
        send_stored_file($file, $lifetime, 0, $forcedownload, $options);
    }
}