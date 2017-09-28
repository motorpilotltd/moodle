<?php
namespace local_custom_certification\form;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

class certification_content_form extends \moodleform
{
    function definition()
    {
        global $PAGE;
        $mform =& $this->_form;
        $certif = $this->_customdata['certif'];
        $certifications = $this->_customdata['certifications'];
        $recertifications = $this->_customdata['recertifications'];
        $renderer = $PAGE->get_renderer('local_custom_certification');

        $mform->addElement('hidden', 'certifid', $certif->id);
        $mform->setType('certifid', PARAM_INT);


        $mform->addElement('header', 'certificationpath', get_string('certificationoriginalpath', 'local_custom_certification'));

        $html = \html_writer::tag('p', get_string('instructions:programcontentoriginal', 'local_custom_certification'), ['class' => 'instructions']);

        $html .= \html_writer::start_div('content-wrapper');
        $html .= \html_writer::tag('span', get_string('certificationcontent', 'local_custom_certification'), ['class' => 'programcontent']);
        $html .= \html_writer::tag('p', '', ['class' => 'certificationpath']);

        $html .= \html_writer::tag('p', get_string('instructions:certificationcourseset', 'local_custom_certification'), ['class' => 'instructions']);
        $html .= \html_writer::tag('p', get_string('error:certifpathcontent', 'local_custom_certification'), ['class' => 'certifpathcontenterror custom-error']);
        $html .= \html_writer::start_tag('div', ['class' => 'coursesets-container certification-coursesets']);
        $html .= $renderer->display_coursesetbox($certifications, $certif->id, 'certification');
        $html .= \html_writer::tag('input', '', ['data-certiftype' => 'certification', 'data-id' => $certif->id, 'value' => get_string('coursesetbtn', 'local_custom_certification'), 'type' => 'button', 'class' => 'form-submit coursesetbtn']);

        $html .= \html_writer::end_tag('div');
        $mform->addElement('html', $html);

        if(count($certifications) > 0){

            $html = \html_writer::start_div('summary-wrapper');
            $html .= \html_writer::tag('span', get_string('summarylabel', 'local_custom_certification'), ['class' => 'summarylabel']);

            $html .= \html_writer::tag('p', '', ['class' => 'certificationpath']);
            $html .= \html_writer::tag('em', get_string('instructions:updatesonsave', 'local_custom_certification'), ['class' => 'instructions']);
            $html .= $renderer->display_coursesetbox_summary_editform($certifications);
            $html .= \html_writer::end_div();
            $html .= \html_writer::empty_tag('br');
            $html .= \html_writer::empty_tag('br');
            $mform->addElement('html', $html);
        }


        $html = \html_writer::end_tag('div');
        $mform->addElement('html', $html);
        $mform->addElement('header', 'recertificationpath', get_string('recertificationoriginalpath', 'local_custom_certification'));

        $html = \html_writer::tag('p', get_string('instructions:programcontentoriginal', 'local_custom_certification'), ['class' => 'instructions']);

        $html .= \html_writer::start_div('content-wrapper');
        $mform->addElement('html', $html);

        $mform->addElement('checkbox', 'userecertif', get_string('userecertif', 'local_custom_certification'));
        $mform->setType('userecertif', PARAM_INT);
        if (empty($recertifications)) {
            $class = 'disable-recertification recertification';
            $mform->setDefault('userecertif', false);
        } else {
            $class = 'recertification';
            $mform->setDefault('userecertif', true);
        }
        $html = \html_writer::start_tag('div', ['class' => $class]);




        $mform->addElement('html', $html);
        if (empty($recertifications)) {
            $mform->addElement('checkbox', 'usecertif', get_string('usecertif', 'local_custom_certification'));
            $mform->setType('usecertif', PARAM_INT);
        }


        $html = \html_writer::tag('span', get_string('recertificationcontent', 'local_custom_certification'), ['class' => 'programcontent']);
        $html .= \html_writer::tag('p', '', ['class' => 'certificationpath']);

        $html .= \html_writer::tag('p', get_string('instructions:recertificationcourseset', 'local_custom_certification'), ['class' => 'instructions']);

//        $mform->addElement('html', $html);
        $html .= \html_writer::start_tag('div', ['class' => 'coursesets-container recertification-coursesets']);
        $html .= $renderer->display_coursesetbox($recertifications, $certif->id, 'recertification');
        $html .= \html_writer::tag('input', '', ['data-certiftype' => 'recertification', 'data-id' => $certif->id, 'value' => get_string('coursesetbtn', 'local_custom_certification'), 'type' => 'button', 'class' => 'form-submit coursesetbtn']);


        $html .= \html_writer::end_tag('div');
        $mform->addElement('html', $html);


        if(count($recertifications) > 0){

            $html = \html_writer::start_div('summary-wrapper');
            $html .= \html_writer::tag('span', get_string('summarylabel', 'local_custom_certification'), ['class' => 'summarylabel']);

            $html .= \html_writer::tag('p', '', ['class' => 'certificationpath']);
            $html .= \html_writer::tag('em', get_string('instructions:updatesonsave', 'local_custom_certification'), ['class' => 'instructions']);
            $html .= $renderer->display_coursesetbox_summary_editform($recertifications);
            $html .= \html_writer::end_div();
            $mform->addElement('html', $html);
        }



        $html = \html_writer::end_tag('div');
        $html .= \html_writer::end_tag('div');
        $mform->addElement('html', $html);

        $generalbtngroup[] = $mform->createElement('button', 'savedatabtn', get_string('savedatabtn', 'local_custom_certification'), ['data-id' => $certif->id, 'class' => 'form-submit savedatabtn']);
        $generalbtngroup[] = $mform->createElement('button', 'cancelbtn', get_string('cancelbtn', 'local_custom_certification'), ['data-id' => $certif->id, 'class' => 'cancelbtn']);
        $mform->addGroup($generalbtngroup, 'generalbtngroup', '', [' '], false);
        $mform->closeHeaderBefore('generalbtngroup');
    }
}
