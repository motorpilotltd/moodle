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
 * Defines the backup_arupenrol_activity_structure_step class.
 *
 * @package    mod_arupenrol
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * The backup_arupenrol_activity_structure_step class.
 *
 * @package    mod_arupenrol
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class backup_arupenrol_activity_structure_step extends backup_activity_structure_step {

    /**
     * Define structure.
     *
     * @return void
     */
    protected function define_structure() {

        // To know if we are including userinfo.
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated.
        $arupenrol = new backup_nested_element(
            'arupenrol',
            array('id'),
            array(
                'name', 'intro', 'introformat',
                'shownamebefore', 'showdescriptionbefore', 'shownameafter', 'showdescriptionafter',
                'action', 'usegroupkeys', 'keyvalue', 'keylabel', 'keytransform', 'enroluser', 'buttontext', 'buttontype',
                'successmessage', 'outro', 'outroformat', 'unenroluser', 'unenrolbuttontext', 'unenrolbuttontype',
                'timecreated', 'timemodified'
            )
        );

        $completions = new backup_nested_element('completions');

        $completion = new backup_nested_element(
            'completion',
            array('id'),
            array(
                'userid',
                'completed'
            )
        );

        // Build the tree.
        $arupenrol->add_child($completions);
        $completions->add_child($completion);

        // Define sources.
        $arupenrol->set_source_table('arupenrol', array('id' => backup::VAR_ACTIVITYID));

        if ($userinfo) {
            $completion->set_source_table('arupenrol_completion', array('arupenrolid' => backup::VAR_PARENTID));
        }

        $completion->annotate_ids('user', 'userid');

        $arupenrol->annotate_files('mod_arupenrol', 'intro', null);
        $arupenrol->annotate_files('mod_arupenrol', 'outro', null);

        // Return the root element (arupenrol), wrapped into standard activity structure.
        return $this->prepare_activity_structure($arupenrol);
    }
}
