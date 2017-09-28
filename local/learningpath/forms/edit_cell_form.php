<?php

defined('MOODLE_INTERNAL') || die();

require_once ($CFG->libdir.'/formslib.php');

class local_learningpath_edit_cell_form extends moodleform {

    protected $_courses;

    public function definition() {
        $mform =& $this->_form;

        $mform->addElement('html', html_writer::tag('div', get_string('header:edit_cell', 'local_learningpath'), array('class' => 'learningpath-header')));

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'x');
        $mform->setType('x', PARAM_INT);

        $mform->addElement('hidden', 'y');
        $mform->setType('y', PARAM_INT);

        $mform->addElement('textarea', 'description', get_string('label:celldescription', 'local_learningpath'));
        $mform->setType('description', PARAM_RAW_TRIMMED);
        $mform->addHelpButton('description', 'label:celldescription','local_learningpath');

        // Three selects for different course types
        
        $essential =& $mform->addElement('select', 'essential', get_string('label:essential', 'local_learningpath', core_text::strtolower(get_string('courses'))), $this->_get_courses(), array('class'=>'select2'));
        $essential->setMultiple(true);
        $mform->addHelpButton('essential', 'label:essential','local_learningpath');

        $recommended =& $mform->addElement('select', 'recommended', get_string('label:recommended', 'local_learningpath', core_text::strtolower(get_string('courses'))), $this->_get_courses(), array('class'=>'select2'));
        $recommended->setMultiple(true);
        $mform->addHelpButton('recommended', 'label:recommended','local_learningpath');

        $elective = & $mform->addElement('select', 'elective', get_string('label:elective', 'local_learningpath', core_text::strtolower(get_string('courses'))), $this->_get_courses(), array('class'=>'select2'));
        $elective->setMultiple(true);
        $mform->addHelpButton('elective', 'label:elective','local_learningpath');

        $mform->addElement('html', html_writer::tag('div', get_string('footer:edit_cell', 'local_learningpath'), array('class' => 'learningpath-header')));

        $buttonarray=array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('button:save', 'local_learningpath'));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        // if any courses have been selected ensure description added
        $hascourses = !empty($data['essential']) || !empty($data['recommended']) || !empty($data['elective']);
        $hasdescription = !empty($data['description']);
        if ($hascourses && !$hasdescription) {
            $errors['description'] = get_string('error:cell:nodescription', 'local_learningpath');
        }
        return $errors;
    }

    protected function _get_courses() {
        global $DB;

        if (!isset($this->_courses)) {
            $select = 'id != :id';
            $params = array('id' => SITEID);

            $rootcategory = (int) get_config('local_learningpath', 'category');
            if ($rootcategory !== 0 && $DB->get_record('course_categories', array('id' => $rootcategory))) {
                $like1 = $DB->sql_like('path', ':pathsnippet1');
                $like2 = $DB->sql_like('path', ':pathsnippet2');
                $select .= " AND category IN (SELECT id FROM {course_categories} WHERE {$like1} OR {$like2})";
                $params['pathsnippet1'] =  "%/{$rootcategory}/%";
                $params['pathsnippet2'] =  "%/{$rootcategory}";
            }
            $concat = $DB->sql_concat('fullname', "' [ID: '", 'id', "']'");
            $this->_courses = $DB->get_records_select_menu('course', $select, $params, 'fullname ASC', "id, {$concat} as name");
        }
        
        return $this->_courses;
    }
}