<?php
// This file is part of the Arup Reports system
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
 *
 * @package     local_reports
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_reports\task;

defined('MOODLE_INTERNAL') || die();

/**
 * The mod_tapsenrol activity cleanup task class.
 *
 * @package    mod_tapsenrol
 * @since      Moodle 3.0
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class file_cleanup extends \core\task\scheduled_task {
    /**
     * Get a descriptive name for this task (shown to admins).
     *
     * @return string
     */
    public function get_name() {
        return get_string('cleanuptempreports', 'local_reports');
    }

    /**
     * Run activity cleanup task.
     */
    public function execute() {
        global $DB;
        $params = array('component' => 'local_reports');
        $files = $DB->get_records('files', $params);
        $fs = get_file_storage();
        foreach ($files as $file) {
           
            // Prepare file record object
            $fileinfo = array(
                'component' => 'local_reports',
                'filearea' => $file->filearea,
                'itemid' => $file->itemid,
                'contextid' => $file->contextid,
                'filepath' => $file->filepath,
                'filename' => $file->filename);
             
            // Get file
            $file = $fs->get_file($fileinfo['contextid'], $fileinfo['component'], $fileinfo['filearea'], 
                    $fileinfo['itemid'], $fileinfo['filepath'], $fileinfo['filename']);
             
            // Delete it if it exists
            if ($file) {
                $file->delete();
            }
            $file = false;

        }
    }
}
