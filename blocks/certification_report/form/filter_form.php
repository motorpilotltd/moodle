<?php
global $CFG;
require_once($CFG->dirroot . '/lib/formslib.php');

class certification_report_filter_form extends \moodleform
{

    public function definition()
    {
        $mform =& $this->_form;

        $mform->addElement('header', 'header-filter', get_string('header:filter', 'block_certification_report'));
        $mform->setExpanded('header-filter', false);

        $mform->addElement('text', 'fullname', get_string('fullname', 'block_certification_report'), ['class' => 'select2-match']);
        $mform->setType('fullname', PARAM_TEXT);

        if (has_capability('block/certification_report:view_all_regions', context_system::instance())) {
            $actualregionselect = $mform->addElement('select', 'actualregions', get_string('actualregions', 'block_certification_report'), $this->_customdata['actualregions'], ['class' => 'select2']);
            $actualregionselect->setMultiple(true);
            $mform->setType('actualregions', PARAM_TEXT);

            $georegionselect = $mform->addElement('select', 'georegions', get_string('georegions', 'block_certification_report'), $this->_customdata['georegions'], ['class' => 'select2']);
            $georegionselect->setMultiple(true);
            $mform->setType('georegions', PARAM_TEXT);
        }

        $costcentreselect = $mform->addElement('select', 'costcentres', get_string('costcentres', 'block_certification_report'), $this->_customdata['costcentres'], ['class' => 'select2']);
        $costcentreselect->setMultiple(true);
        $mform->setType('costcentres', PARAM_ALPHANUMEXT);

        $certificationselect = $mform->addElement('select', 'certifications', get_string('certifications', 'block_certification_report'), $this->_customdata['certifications'], ['class' => 'select2']);
        $certificationselect->setMultiple(true);
        $mform->setType('certifications', PARAM_INT);

        $cohortselect = $mform->addElement('select', 'cohorts', get_string('cohorts', 'block_certification_report'), $this->_customdata['cohorts'], ['class' => 'select2']);
        $cohortselect->setMultiple(true);
        $mform->setType('cohorts', PARAM_INT);
        $mform->setAdvanced('cohorts');

        $categoryselect = $mform->addElement('select', 'categories', get_string('categories', 'block_certification_report'), $this->_customdata['categories'], ['class' => 'select2']);
        $categoryselect->setMultiple(true);
        $mform->setType('categories', PARAM_INT);
        $mform->setAdvanced('categories');

        $groupnameselect = $mform->addElement('select', 'groupnames', get_string('groupnames', 'block_certification_report'), $this->_customdata['groupnames'], ['class' => 'select2']);
        $groupnameselect->setMultiple(true);
        $mform->setType('groupnames', PARAM_TEXT);
        $mform->setAdvanced('groupnames');

        $locationnameselect = $mform->addElement('select', 'locationnames', get_string('locationnames', 'block_certification_report'), $this->_customdata['locationnames'], ['class' => 'select2']);
        $locationnameselect->setMultiple(true);
        $mform->setType('locationnames', PARAM_TEXT);
        $mform->setAdvanced('locationnames');

        $employmentcategoryselect = $mform->addElement('select', 'employmentcategories', get_string('employmentcategories', 'block_certification_report'), $this->_customdata['employmentcategories'], ['class' => 'select2']);
        $employmentcategoryselect->setMultiple(true);
        $mform->setType('employmentcategories', PARAM_TEXT);
        $mform->setAdvanced('employmentcategories');

        $gradeselect = $mform->addElement('select', 'grades', get_string('grades', 'block_certification_report'), $this->_customdata['grades'], ['class' => 'select2']);
        $gradeselect->setMultiple(true);
        $mform->setType('grades', PARAM_TEXT);
        $mform->setAdvanced('grades');

        $generalbtngroup = [];
        $generalbtngroup[] = &$mform->createElement('submit', 'submitbutton', get_string('searchbutton', 'block_certification_report'));
        $generalbtngroup[] = &$mform->createElement('cancel', 'cancel', get_string('clearbutton', 'block_certification_report'));

        $mform->addGroup($generalbtngroup, 'generalbtngroup', '', [' '], false);


        $mform->disable_form_change_checker();
    }
}