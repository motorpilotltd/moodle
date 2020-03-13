<?php
/**
 * Created by PhpStorm.
 * User: andrewhancox
 * Date: 28/02/2019
 * Time: 13:05
 */

namespace local_panels;

require_once($CFG->libdir . '/formslib.php');

class panelsetform extends \moodleform {
    function definition() {
        $mform = $this->_form;

        /* @var $panels panelset */
        $panelset = $this->_customdata['panelset'];
        $newpanelid = $this->_customdata['newpanelid'];

        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'contextid');
        $mform->setType('contextid', PARAM_INT);
        $mform->addElement('hidden', 'panelorder');
        $mform->setType('panelorder', PARAM_SEQUENCE);

        foreach ($panelset->panels as $panel) {
            if ($panel instanceof \local_panels\panel) {
                $panel->addtoform($mform);
            }
        }

        $submitdivwrapperclass = 'd-none';
        if ($newpanelid > 1) {
            $submitdivwrapperclass = 'd-flex';
            $mform->setExpanded('panel-' . $newpanelid, true);
        }

        $buttonarray = [];
        $buttonarray[] = &$mform->createElement('html', '<div class="' . $submitdivwrapperclass . '" data-region="submitcancel">');
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('savechanges'));
        $buttonarray[] = &$mform->createElement('cancel');
        $buttonarray[] = &$mform->createElement('html', '</div>');
        $mform->addGroup($buttonarray, 'buttonar', '', array(''), false, 'haaaa');

        $addpanelarray = [];
        $addpanelarray[] = &$mform->createElement('submit', 'addpanel', get_string('addpanel', 'local_panels'));
        $mform->addGroup($addpanelarray, 'addpanelarray', '', array(' '), false);

        $mform->closeHeaderBefore('buttonar');
    }
}