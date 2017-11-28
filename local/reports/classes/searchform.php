<?php
// This file is part of the Arup Reports system
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
 *
 * @package     local_reports
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_reports;

require_once("$CFG->libdir/formslib.php");

use moodleform;
use stdClass;

class searchform extends moodleform {
    
    public function definition() {
        global $OUTPUT;
        $data = $this->_customdata;
        $mform = $this->_form;
        $mform->addElement('hidden', 'page', $data->reportname);
        $mform->setType('page', PARAM_RAW);
        $filters = $data->filteroptions;

        $count = -1;
        foreach ($filters as $filter) {
            $count++;
            if ($count == $data->visiblesearchfields) {
                $mform->addelement('html', '<div id="extrafields" class="collapse">');
            }
            if ($filter->type == 'yn') {
                $mform->addElement('advcheckbox', $filter->field, $filter->name, get_string('showall', 'local_reports'), array('group' => 1), array(0, 1));
                if ($data->showall) {
                    $mform->setDefault($filter->field, 0);
                } else {
                    $mform->setDefault($filter->field, 1);
                }
            } else if ($filter->type == 'dropdown') {
                $options = $data->get_dropdown($filter->field);
                $mform->addElement('select', $filter->field, $filter->name, $options);
            } else if ($filter->type == 'dropdownmulti') {
                $options = $data->get_dropdown($filter->field);
                $mform->addElement('select', $filter->field, $filter->name, $options);
                $mform->getElement($filter->field)->setMultiple(true);
            } else if ($filter->type == 'autocomplete') {
                $options = $data->get_dropdown($filter->field);
                $params = array(                                                      
                    'placeholder' => get_string('learninghistory:' . $filter->field, 'local_reports'),
                    'noselectstring' => 'select'                                                      
                );   
                $mform->addElement('autocomplete', $filter->field, $filter->name, $options, $params);
                $mform->setDefault($filter->field, '');
            } else if ($filter->type == 'date') {
                $mform->addElement('date_selector', $filter->field, $filter->name);
                $mform->setDefault($filter->field, '');
            } else {
                $mform->addElement('text', $filter->field, $filter->name);
                if ($filter->type == 'int') {
                    $mform->setType($filter->field, PARAM_INT);
                } else {
                    $mform->setType($filter->field, PARAM_RAW);
                }
            }
            $mform->addHelpButton($filter->field, 'learninghistory:' . $filter->field, 'local_reports');
        }
        $mform->addelement('html', '</div>');
        $moreless = $OUTPUT->render_from_template('local_reports/moreless', new stdClass());
        $mform->addelement('html', $moreless);
        $mform->addElement('submit', 'submitbutton', get_string('search'));
        $mform->addelement('html', '</div></div>');
    }

    /**
     * Validate search form
     */
    function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (!empty($data['costcentre'])) {
            if (!preg_match('/^\d*\-\d*$/', $data['costcentre'])) {
                if (!preg_match('/^\d{2,3}$/', $data['costcentre'])) {
                    $errors['costcentre'] = get_string('incorrectcostcentreformat', 'local_reports');
                }
            }
        }

        return $errors;
    }


}