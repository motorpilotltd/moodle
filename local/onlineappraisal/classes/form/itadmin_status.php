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

class itadmin_status extends moodleform {

    public function definition() {
        $mform =& $this->_form;
        $appraisalid = $this->_customdata['appraisalid'];
        $search = $this->_customdata['search'];
        $statusconfirm = $this->_customdata['statusconfirm'];
        
        if ($statusconfirm) {
            $newstatusid = $this->_customdata['newstatusid'];

            $mform->addElement('hidden', 'newstatusid', $newstatusid);
            $mform->setType('newstatusid', PARAM_INT);

            $mform->addElement('hidden', 'itadminaction', 'changestatus');
            $mform->setType('itadminaction', PARAM_RAW);

            $mform->addElement('html', \html_writer::tag('h3', get_string('itadmin:reasonstatus', 'local_onlineappraisal')));
            $mform->addElement('textarea', 'reason', 
                '' , 'rows="8" cols="70"');
            $mform->setType('reason', PARAM_RAW);
        } else {
            $statusoptions = $this->_customdata['statusoptions'];
            $mform->addElement('select', 'newstatusid', get_string('itadmin:changestatus', 'local_onlineappraisal'), $statusoptions);
            $mform->setType('search', PARAM_RAW);
        }

        $mform->addElement('hidden', 'appraisalid', $appraisalid);
        $mform->setType('appraisalid', PARAM_INT);

        $mform->addElement('hidden', 'search', $search);
        $mform->setType('search', PARAM_RAW);

        $this->add_action_buttons(false, get_string('itadmin:change', 'local_onlineappraisal'));

        $mform->disable_form_change_checker();
    }
    
}
