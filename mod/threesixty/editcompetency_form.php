<?php

define('DEFAULT_NUMBER_SKILLS', 5);
define('EXTRA_SKILLS', 2);

require_once $CFG->dirroot.'/lib/formslib.php';

class mod_threesixty_editcompetency_form extends moodleform {

    function definition() {
        global $OUTPUT, $PAGE;

        $mform =& $this->_form;

        $threesixty = $this->_customdata['threesixty'];

        $mform->addElement('header', 'general', threesixty_get_alternative_word($threesixty, 'competency'));

        $mform->addElement('text', 'name', get_string('name'), array('size'=>'50'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 255), 'maxlength', 255, 'client');

        $mform->addElement('textarea', 'description', get_string('description'), array('cols'=>'56', 'rows'=>'8'));
        $mform->setType('description', PARAM_TEXT);
        $mform->addRule('description', null, 'required', null, 'client');

        $PAGE->requires->js_init_call('M.util.init_colour_picker', array('id_colour', null));
        $colourpicker = array();
        $colourpickerhtml = html_writer::tag('div', $OUTPUT->pix_icon('i/loading', get_string('loading', 'admin'), 'moodle', array('class'=>'loadingicon')), array('class'=>'admin_colourpicker clearfix'));
        $colourpicker[] = &$mform->createElement('static', 'colourpicker', null, $colourpickerhtml);
        $colourpicker[] = &$mform->createElement('text', 'colour', get_string('competencycolour', 'threesixty', core_text::strtolower(threesixty_get_alternative_word($threesixty, 'competency'))));
        $mform->setType('colour', 'text');
        $mform->addGroup($colourpicker, 'colourpickerar', get_string('competencycolour', 'threesixty'), '', false);
        $mform->addHelpButton('colourpickerar', 'competencycolour', 'threesixty');

        $mform->addElement('checkbox', 'showfeedback', get_string('showfeedback', 'threesixty'));
        $mform->addHelpButton('showfeedback', 'showfeedback', 'threesixty');

        $mform->addElement('header', 'skills', threesixty_get_alternative_word($threesixty, 'skill', 'skills'));

        $repeatarray = array();
        $repeatarray[] = &$mform->createElement('hidden', 'skillid', 0);
        $repeatarray[] = &$mform->createElement('text', 'skillname', get_string('skillname', 'threesixty'), array('size'=>'50'));
        $repeatarray[] = &$mform->createElement('textarea', 'skilldescription', get_string('skilldescription', 'threesixty'), array('cols'=>'56', 'rows'=>'6'));
        $repeatarray[] = &$mform->createElement('text', 'skillaltname', get_string('skillaltname', 'threesixty'), array('size'=>'50'));
        $repeatarray[] = &$mform->createElement('textarea', 'skillaltdescription', get_string('skillaltdescription', 'threesixty'), array('cols'=>'56', 'rows'=>'6'));
        $checkboxelement = &$mform->createElement('checkbox', 'skilldelete', '', get_string('deleteskill', 'threesixty', core_text::strtolower(threesixty_get_alternative_word($threesixty, 'skill'))));
        unset($checkboxelement->_attributes['id']); // necessary until MDL-20441 is fixed
        $repeatarray[] = $checkboxelement;
        $repeatarray[] = &$mform->createElement('html', '<br/><br/>'); // spacer

        $repeatcount = DEFAULT_NUMBER_SKILLS;
        if ($this->_customdata['skills']) {
            $repeatcount = count($this->_customdata['skills']);
            $repeatcount += EXTRA_SKILLS;
        }

        $repeatoptions = array();
        $repeatoptions['skillname']['disabledif'] = array('skilldelete', 'checked');
        $repeatoptions['skilldescription']['disabledif'] = array('skilldelete', 'checked');
        $repeatoptions['skillaltname']['disabledif'] = array('skilldelete', 'checked');
        $repeatoptions['skillaltname']['helpbutton'] = array('skillaltname', 'threesixty');
        $repeatoptions['skillaltdescription']['disabledif'] = array('skilldelete', 'checked');
        $repeatoptions['skillaltdescription']['helpbutton'] = array('skillaltdescription', 'threesixty');
        $mform->setType('skillid', PARAM_INT);
        $mform->setType('skillname', PARAM_TEXT);
        $mform->setType('skilldescription', PARAM_TEXT);
        $mform->setType('skillaltname', PARAM_TEXT);
        $mform->setType('skillaltdescription', PARAM_TEXT);

        $a = new stdClass();
        $a->number = EXTRA_SKILLS;
        $a->skills = core_text::strtolower(threesixty_get_alternative_word($threesixty, 'skill', 'skills'));
        $this->repeat_elements($repeatarray, $repeatcount, $repeatoptions, 'skill_repeats', 'skill_add_fields',
                               EXTRA_SKILLS, get_string('addnewskills', 'threesixty', $a), true);

        $mform->addElement('hidden', 'a', $this->_customdata['a']);
        $mform->setType('a', PARAM_INT);
        $mform->addElement('hidden', 'c', $this->_customdata['c']);
        $mform->setType('c', PARAM_INT);

        $this->add_action_buttons();
    }

    public function get_data() {
        $data = parent::get_data();
        if (!$data) {
            return false;
        }

        $data->colour = core_text::strtolower($data->colour);

        if (preg_match('/^#?([[:xdigit:]]{3}){1,2}$/', $data->colour)) {
            if (strpos($data->colour, '#') !== 0) {
                $data->colour = '#'.$data->colour;
            }
        }

        return $data;
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        $colourok =
            empty($data['colour'])
            || preg_match('/^#[[:xdigit:]]{6}$/', $data['colour']);

        if (!$colourok) {
            $errors['colourpickerar'] = 'Invalid hexadecimal colour entered (e.g. #000000)';
        }

        return $errors;
    }
}
