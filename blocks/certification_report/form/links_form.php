<?php
global $CFG;
require_once($CFG->dirroot . '/lib/formslib.php');

use block_certification_report\certification_report;

class certification_report_links_form extends \moodleform
{

    public function definition()
    {
        $mform =& $this->_form;

        $regions = $this->_customdata['regions'];
        $mform->addElement('text', 'linkname', get_string('linkname', 'block_certification_report'));
        $mform->setType('linkname', PARAM_TEXT);
        $mform->addRule('linkname', null, 'required');


        $mform->addElement('text', 'linkurl', get_string('linkurl', 'block_certification_report'), array('size' => 60));
        $mform->setType('linkurl', PARAM_RAW_TRIMMED);
        $mform->addRule('linkurl', null, 'required');

        $selectregion = get_string('selectregion', 'block_certification_report');

        $mform->addElement(
            'select',
            'actualregion',
            get_string('actualregion', 'block_certification_report'),
            ['0' => $selectregion] + $regions['actual']
        );
        $mform->setType('actualregion', PARAM_TEXT);

        $mform->addElement(
            'select',
            'geographicregion',
            get_string('georegion', 'block_certification_report'),
            ['0' => $selectregion] + $regions['geographic']
        );

        // Hidden fields
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->setType('geographicregion', PARAM_TEXT);
        $this->add_action_buttons(true, get_string('submit'));

    }

    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (!empty($data['linkurl'])) {

            $url = $data['linkurl'];
            if (!certification_report::url_appears_valid_url($url)) {
                $errors['linkurl'] = get_string('invalidurl', 'block_certification_report');
            }

        } else {
            $errors['linkurl'] = get_string('erroremptyurl', 'block_certification_report');
        }
        return $errors;
    }

}