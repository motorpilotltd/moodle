<?php

require_once $CFG->dirroot.'/lib/formslib.php';
require_once $CFG->dirroot.'/group/lib.php';

class mod_threesixty_group_form extends moodleform {

    public $groups = array();

    public function definition() {
        global $COURSE;

        $mform =& $this->_form;

        $mform->addElement('hidden', 'a', $this->_customdata['a']);
        $mform->setType('a', PARAM_INT);

        if (isset($this->_customdata['type'])) {
            $mform->addElement('hidden', 'type', $this->_customdata['type']);
            $mform->setType('type', PARAM_ALPHA);
        }

        $groups = groups_get_all_groups($COURSE->id);
        foreach ($groups as $group) {
            $this->groups[$group->id] = $group->name;
        }

        $select =& $mform->addElement(
            'select',
            'groupids',
            get_string('groupid', 'threesixty'),
            $this->groups,
            array(
                'class' => 'chosen-select',
                'data-placeholder' => get_string('choosedots'),
                'style' => 'width:50%;'
            )
        );
        $select->setMultiple(true);

        $this->add_action_buttons(false, get_string('go'));
    }
}
