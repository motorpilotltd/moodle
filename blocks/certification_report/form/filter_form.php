<?php
global $CFG;
require_once($CFG->dirroot . '/lib/formslib.php');

class certification_report_filter_form extends \moodleform
{

    public function definition()
    {
        $mform =& $this->_form;
        $certifications = $this->_customdata['certifications'];
        $categories = $this->_customdata['categories'];
        $regions = $this->_customdata['regions'];
        $costcentres = $this->_customdata['costcentres'];
        $cohorts = $this->_customdata['cohorts'];

        $mform->addElement('header', 'header-filter', get_string('header:filter', 'block_certification_report'));
        $mform->setExpanded('header-filter', false);

        $mform->addElement('text', 'fullname', get_string('fullname', 'block_certification_report'), 'maxlength="254" size="50"');
        $mform->setType('fullname', PARAM_TEXT);

        if (has_capability('block/certification_report:view_all_regions', context_system::instance())) {
            $regionselect = $mform->addElement('select', 'regions', get_string('regions', 'block_certification_report'), $regions);
            $regionselect->setMultiple(true);
            $mform->setType('regions', PARAM_INT);
        }

        $costcentreselect = $mform->addElement('select', 'costcentres', get_string('costcentres', 'block_certification_report'), $costcentres);
        $costcentreselect->setMultiple(true);
        $mform->setType('costcentres', PARAM_ALPHANUMEXT);

        $cohortselect = $mform->addElement('select', 'cohorts', get_string('cohorts', 'block_certification_report'), $cohorts);
        $cohortselect->setMultiple(true);
        $mform->setType('cohorts', PARAM_INT);

        $certificationselect = $mform->addElement('select', 'certifications', get_string('certifications', 'block_certification_report'), $certifications);
        $certificationselect->setMultiple(true);
        $mform->setType('certifications', PARAM_INT);

        $categoryselect = $mform->addElement('select', 'categories', get_string('categories', 'block_certification_report'), $categories);
        $categoryselect->setMultiple(true);
        $mform->setType('categories', PARAM_INT);

        $generalbtngroup = [];
        $generalbtngroup[] = &$mform->createElement('submit', 'submitbutton', get_string('searchbutton', 'block_certification_report'));
        $generalbtngroup[] = &$mform->createElement('cancel', 'cancel', get_string('clearbutton', 'block_certification_report'));

        $mform->addGroup($generalbtngroup, 'generalbtngroup', '', [' '], false);


        $mform->disable_form_change_checker();
    }
}