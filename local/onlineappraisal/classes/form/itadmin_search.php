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

class itadmin_search extends moodleform {

    public function definition() {
        $mform =& $this->_form;
        $page = $this->_customdata['page'];
        $search = $this->_customdata['search'];

        $mform->addElement('text', 'search', get_string('form:userinfo:staffid', 'local_onlineappraisal'));
        $mform->setType('search', PARAM_RAW);
        if (!empty($search)) {
            $mform->setDefault('search', $search);
        }

        $mform->addElement('hidden', 'page', $page);
        $mform->setType('page', PARAM_ALPHA);

        $this->add_action_buttons(false, get_string('search'));

        $mform->disable_form_change_checker();
    }
    
}
