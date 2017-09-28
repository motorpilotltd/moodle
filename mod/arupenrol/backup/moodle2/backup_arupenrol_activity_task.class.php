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
 * Defines the backup_arupenrol_activity_task class.
 *
 * @package    mod_arupenrol
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

require_once($CFG->dirroot.'/mod/arupenrol/backup/moodle2/backup_arupenrol_stepslib.php');    // Because it exists (must).

/**
 * The backup_arupenrol_activity_task class.
 *
 * @package    mod_arupenrol
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_arupenrol_activity_task extends backup_activity_task {

    /**
     * Define (add) particular settings this activity can have.
     *
     * @return void
     */
    protected function define_my_settings() {
        // No particular settings for this activity.
    }

    /**
     * Define (add) particular steps this activity can have.
     *
     * @return void
     */
    protected function define_my_steps() {
        // Only has one structure step for arupenrol.
        $this->add_step(new backup_arupenrol_activity_structure_step('arupenrol_structure', 'arupenrol.xml'));
    }

    /**
     * Code the transformations to perform in the activity in order to get transportable (encoded) links.
     *
     * @param string $content
     * @return string encoded content
     */
    static public function encode_content_links($content) {
        global $CFG;

        $base = preg_quote($CFG->wwwroot, "/");

        // Link to the list of arupenrols.
        $search = "/(".$base."\/mod\/arupenrol\/index.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@ARUPENROLINDEX*$2@$', $content);

        // Link to arupenrol view by cm->id.
        $search = "/(".$base."\/mod\/arupenrol\/view.php\?id\=)([0-9]+)/";
        $content = preg_replace($search, '$@ARUPENROLVIEWBYID*$2@$', $content);

        return $content;
    }
}
