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
 * Cohort related management functions, this file needs to be included manually.
 *
 * @package    core_cohort
 * @copyright  2010 Petr Skoda  {@link http://skodak.org}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot . '/lib/formslib.php');

class cohort_edit_form extends moodleform
{

    /**
     * Define the cohort edit form
     */
    public function definition()
    {

        $mform = $this->_form;
        $editoroptions = $this->_customdata['editoroptions'];
        $cohort = $this->_customdata['data'];
        $cohorttypes = $this->_customdata['cohorttypes'];
        $roles = $this->_customdata['roles'];
        $renderer = $this->_customdata['renderer'];

        $mform->addElement('html', get_string('cohort:heading', 'local_dynamic_cohorts'));
        $mform->addElement('text', 'name', get_string('name', 'cohort'), 'maxlength="254" size="50"');
        $mform->addRule('name', get_string('required'), 'required', null, 'client');
        $mform->setType('name', PARAM_TEXT);

        $options = $this->get_category_options($cohort->contextid);
        $mform->addElement('select', 'contextid', get_string('context', 'role'), $options);

        $mform->addElement('text', 'idnumber', get_string('idnumber', 'cohort'), 'maxlength="254" size="50"');
        $mform->setType('idnumber', PARAM_RAW); // Idnumbers are plain text, must not be changed.

        $mform->addElement('select', 'type', get_string('cohorttypes', 'local_dynamic_cohorts'), $cohorttypes);
        $mform->setDefault('type', $cohort->type);

        $mform->addElement('advcheckbox', 'visible', get_string('visible', 'cohort'));
        $mform->setDefault('visible', 1);
        $mform->addHelpButton('visible', 'visible', 'cohort');

        $mform->addElement('editor', 'description_editor', get_string('description', 'cohort'), null, $editoroptions);
        $mform->setType('description_editor', PARAM_RAW);

        $attr = [];
        if($cohort->type == \local_dynamic_cohorts\dynamic_cohorts::TYPE_STANDARD){
            $attr['style'] = 'display:none;';
        }
        $mform->addElement('html', html_writer::start_div('dynamic-cohort-rules', $attr));
        $mform->addElement('html', get_string('cohortrules:heading', 'local_dynamic_cohorts'));

        $generalrules[] = $mform->createElement('advcheckbox', 'adduser', null, get_string('addusercheckboxlabel', 'local_dynamic_cohorts'), null, [0, 1]);
        $generalrules[] = $mform->createElement('advcheckbox', 'removeuser', null, get_string('removeusercheckboxlabel', 'local_dynamic_cohorts'), null, [0, 1]);
        $mform->addGroup($generalrules, null, get_string('membersupdatelabel', 'local_dynamic_cohorts'), '<br />');

        $mform->setDefault('adduser', $cohort->memberadd);
        $mform->setDefault('removeuser', $cohort->memberremove);

        $operatorsbetweenrulesets[] = $mform->createElement('radio', 'rulesetsoperator', '', get_string('rulesetandoperator', 'local_dynamic_cohorts'), 1);
        $operatorsbetweenrulesets[] = $mform->createElement('radio', 'rulesetsoperator', '', get_string('rulesetoroperator', 'local_dynamic_cohorts'), 0);
        $mform->addGroup($operatorsbetweenrulesets, null, get_string('rulesetsoperatorgroup', 'local_dynamic_cohorts'), '<br />');
        $mform->setDefault('rulesetsoperator', $cohort->operator);

        $mform->addElement('html', \html_writer::start_div('', ['id' => 'rulesetscontainer']));

        foreach($cohort->rulesets as $counter => $ruleset){
            $mform->addElement('html' , $renderer->display_ruleset($ruleset->id, ++$counter, $ruleset->operator, $ruleset->rules));
        }

        $mform->addElement('html', \html_writer::end_div());

        $mform->addElement('button', 'addruleset', get_string('addrulesetbtn', 'local_dynamic_cohorts'));

        $mform->addElement('html', html_writer::end_div());

        $mform->addElement('html', get_string('cohortroles:heading', 'local_dynamic_cohorts'));
        $mform->addElement('html', get_string('cohortroles:instructions', 'local_dynamic_cohorts'));
        foreach ($roles as $roleid => $rolename) {
            $systemroles[] = $mform->createElement('advcheckbox', $roleid, null, $rolename, null, array(0, 1));

        }

        $mform->addGroup($systemroles, 'systemrolesgroup', get_string('systemrolesgrouplabel', 'local_dynamic_cohorts'), '<br />');
        foreach($cohort->roles as $roleid => $tmp){
            $mform->setDefault('systemrolesgroup['.$roleid.']', 1);
        }
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        if (isset($this->_customdata['returnurl'])) {
            $mform->addElement('hidden', 'returnurl', $this->_customdata['returnurl']->out_as_local_url());
            $mform->setType('returnurl', PARAM_LOCALURL);
        }

        $this->add_action_buttons();

        $this->set_data($cohort);
    }

    public function validation($data, $files)
    {
        global $DB;

        $errors = parent::validation($data, $files);

        $idnumber = trim($data['idnumber']);
        if ($idnumber === '') {
            // Fine, empty is ok.

        } else if ($data['id']) {
            $current = $DB->get_record('cohort', array('id' => $data['id']), '*', MUST_EXIST);
            if ($current->idnumber !== $idnumber) {
                if ($DB->record_exists('cohort', array('idnumber' => $idnumber))) {
                    $errors['idnumber'] = get_string('duplicateidnumber', 'cohort');
                }
            }

        } else {
            if ($DB->record_exists('cohort', array('idnumber' => $idnumber))) {
                $errors['idnumber'] = get_string('duplicateidnumber', 'cohort');
            }
        }

        return $errors;
    }

    protected function get_category_options($currentcontextid)
    {
        global $CFG;
        require_once($CFG->libdir . '/coursecatlib.php');
        $displaylist = coursecat::make_categories_list('moodle/cohort:manage');
        $options = array();
        $syscontext = context_system::instance();
        if (has_capability('moodle/cohort:manage', $syscontext)) {
            $options[$syscontext->id] = $syscontext->get_context_name();
        }
        foreach ($displaylist as $cid => $name) {
            $context = context_coursecat::instance($cid);
            $options[$context->id] = $name;
        }
        // Always add current - this is not likely, but if the logic gets changed it might be a problem.
        if (!isset($options[$currentcontextid])) {
            $context = context::instance_by_id($currentcontextid, MUST_EXIST);
            $options[$context->id] = $syscontext->get_context_name();
        }
        return $options;
    }
}

