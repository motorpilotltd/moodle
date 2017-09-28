<?php

defined('MOODLE_INTERNAL') || die();

require_once ($CFG->libdir.'/formslib.php');

class local_learningpath_add_form extends moodleform {

    public function definition() {
        $mform =& $this->_form;

        $mform->addElement('html', html_writer::tag('div', get_string('header:add', 'local_learningpath'), array('class' => 'learningpath-header')));

        $mform->addElement('select', 'categoryid', get_string('label:categoryid', 'local_learningpath'), learningpath_get_categories_list(null, true), array('class'=>'select2'));
        $mform->addRule('categoryid', null, 'required', null, 'client');
        $mform->addHelpButton('categoryid', 'label:categoryid','local_learningpath');

        $mform->addElement('text', 'name', get_string('label:name', 'local_learningpath'));
        $mform->setType('name', PARAM_TEXT);
        $mform->addRule('name', null, 'required', null, 'client');
        $mform->addRule('name', get_string('maximumchars', '', 150), 'maxlength', 150, 'client');
        $mform->addHelpButton('name', 'label:name','local_learningpath');

        $mform->addElement('textarea', 'description', get_string('label:description', 'local_learningpath'));
        $mform->setType('description', PARAM_RAW_TRIMMED);

        $mform->addElement('textarea', 'xaxis', get_string('label:xaxis', 'local_learningpath'));
        $mform->setType('xaxis', PARAM_TEXT);
        $mform->addRule('xaxis', null, 'required', null, 'client');
        $mform->addHelpButton('xaxis', 'label:xaxis','local_learningpath');

        $mform->addElement('textarea', 'yaxis', get_string('label:yaxis', 'local_learningpath'));
        $mform->setType('yaxis', PARAM_TEXT);
        $mform->addRule('yaxis', null, 'required', null, 'client');
        $mform->addHelpButton('yaxis', 'label:yaxis','local_learningpath');

        $mform->addElement('html', html_writer::tag('div', get_string('footer:add', 'local_learningpath'), array('class' => 'learningpath-header')));

        $this->add_action_buttons(true, get_string('button:create', 'local_learningpath'));
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);

        // Validate x and y axis headings
        $linelimits = array(
            'xaxis' => 35,
            'yaxis' => 35,
        );
        $totallimit = 15;
        $fielderrors = array();
        foreach ($linelimits as $field => $limit) {
            $fielderrors[$field] = '';
            if (!empty($data[$field])) {
                $axisdata = trim(str_ireplace(array("\r\n", "\r"), "\n", $data[$field]));
                $count = 0;
                $axisnames = array();
                foreach (explode("\n", $axisdata) as $axisname) {
                    $count++;
                    if (strlen($axisname) > $limit) {
                        $a = new stdClass();
                        $a->name = $axisname;
                        $a->length = $limit;
                        $a->line = $count;
                        $a->axis = get_string('error:axis:'.$field, 'local_learningpath');
                        $fielderrors[$field] .= empty($fielderrors[$field]) ? '' : html_writer::empty_tag('br');
                        $fielderrors[$field] .= get_string('error:axisname:length', 'local_learningpath', $a);
                    } elseif (strlen($axisname) == 0) {
                        $a = new stdClass();
                        $a->line = $count;
                        $a->axis = get_string('error:axis:'.$field, 'local_learningpath');
                        $fielderrors[$field] .= empty($fielderrors[$field]) ? '' : html_writer::empty_tag('br');
                        $fielderrors[$field] .= get_string('error:axisname:empty', 'local_learningpath', $a);
                    } elseif (in_array($axisname, $axisnames)) {
                        $a = new stdClass();
                        $a->name = $axisname;
                        $a->line = $count;
                        $a->axis = get_string('error:axis:'.$field, 'local_learningpath');
                        $a->axis2 = core_text::strtolower(get_string('error:axis:'.$field, 'local_learningpath'));
                        $fielderrors[$field] .= empty($fielderrors[$field]) ? '' : html_writer::empty_tag('br');
                        $fielderrors[$field] .= get_string('error:axisname:inuse', 'local_learningpath', $a);
                    }
                    $axisnames[] = $axisname;
                }
                if ($count > $totallimit) {
                    $a = new stdClass();
                    $a->count = $count;
                    $a->totallimit = $totallimit;
                    $a->axis = core_text::strtolower(get_string('error:axis:'.$field, 'local_learningpath'));
                    $totallimiterror = get_string('error:axis:totallimit', 'local_learningpath', $a);
                    $totallimiterror .= empty($fielderrors[$field]) ? '' : html_writer::empty_tag('br');
                    $fielderrors[$field] = $totallimiterror . $fielderrors[$field];
                }
            }
            if (!empty($fielderrors[$field])) {
                $errors[$field] = $fielderrors[$field];
            }
        }

        return $errors;
    }

}