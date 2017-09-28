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
 * Instance add/edit form
 *
 * @package    mod_arupmyproxy
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot.'/course/moodleform_mod.php');

class mod_arupmyproxy_mod_form extends moodleform_mod {

    public function definition() {
        global $CFG, $COURSE;

        $mform =& $this->_form;

        if (!is_null($this->_cm)) {
            $logouturl = new moodle_url('/mod/arupmyproxy/logout.php', array('id' => $this->_cm->id));
            $mform->addElement('static', 'logouturl', get_string('logouturl', 'arupmyproxy'), html_writer::tag('em', $logouturl->out(true)));
            $mform->addHelpButton('logouturl', 'logouturl', 'arupmyproxy');
        }

        $mform->addElement('header', 'general', get_string('general', 'form'));

        $mform->addElement('text', 'name', get_string('name', 'arupmyproxy'), array('size' => '64'));
        if (!empty($CFG->formatstringstriptags)) {
            $mform->setType('name', PARAM_TEXT);
        } else {
            $mform->setType('name', PARAM_CLEAN);
        }
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $this->standard_intro_elements(get_string('introeditor', 'arupmyproxy'));

        // Choose role of participants.
        $roles = array('' => get_string('choosedots')) + role_get_names(context_course::instance($COURSE->id), ROLENAME_ALIAS, true);
        $mform->addElement('select', 'roleid', get_string('roleid', 'arupmyproxy'), $roles);
        $mform->addRule('roleid', null, 'required', null, 'client');
        $mform->addHelpButton('roleid', 'roleid', 'arupmyproxy');

        $this->standard_coursemodule_elements();

        $this->add_action_buttons();
    }

    public function definition_after_data() {
        global $DB;

        parent::definition_after_data();

        $mform =& $this->_form;

        if ($this->_instance && $DB->get_records('arupmyproxy_proxies', array('arupmyproxyid' => $this->_instance))) {
            $mform->hardFreeze('roleid');
        }
    }
}