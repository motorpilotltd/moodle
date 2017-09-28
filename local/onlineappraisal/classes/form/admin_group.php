<?php
// This file is part of the appraisal plugin for Moodle
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
 * @package    local_onlineappraisal
 * @copyright  2016 Motorpilot Ltd, Sonsbeekmedia
 * @author     Bas Brands, Simon Lewis
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_onlineappraisal\form;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/lib/formslib.php');

use moodleform;

class admin_group extends moodleform {

    public function definition() {
        $mform =& $this->_form;
        $groups = $this->_customdata['groups'];
        $page = $this->_customdata['page'];
        $groupid = $this->_customdata['groupid'];

        $mform->addElement('select', 'groupid', get_string('group', 'local_onlineappraisal'), $groups);
        $mform->setDefault('groupid', $groupid);

        if (!empty($this->_customdata['cohorts'])) {
            $mform->addElement('select', 'cohortid', get_string('cohort', 'local_onlineappraisal'), $this->_customdata['cohorts']);
            $mform->setDefault('cohortid', $this->_customdata['cohortid']);

            $mform->addElement('hidden', 'currentcohortid', $this->_customdata['cohortid']);
            $mform->setType('currentcohortid', PARAM_INT);
        }

        $mform->addElement('hidden', 'page', $page);
        $mform->setType('page', PARAM_ALPHA);

        $mform->addElement('hidden', 'currentgroupid', $groupid);
        $mform->setType('currentgroupid', PARAM_ALPHANUMEXT);

        $this->add_action_buttons(false, get_string('form:go', 'local_onlineappraisal'));

        $mform->disable_form_change_checker();
    }
    
}
