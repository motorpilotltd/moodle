<?php
// This file is part of the Arup online appraisal system
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
 * Version details
 *
 * @package     local_onlineappraisal
 * @copyright   2016 Motorpilot Ltd, Sonsbeekmedia
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_onlineappraisal\output\printer;

defined('MOODLE_INTERNAL') || die();

use stdClass;

class successionplan extends base {
    /**
     * Get extra context data.
     */
    protected function get_data() {
        

        // Get forms.
        $this->get_forms();


    }

    /**
     * Inject form info and data into data object.
     *
     * @global \moodle_database $DB
     */
    private function get_forms() {
        global $DB;

        $this->data->forms = array();

        // Forms and fields to print (in order).
        $forms = array(
            'successionplan' => array('assessment', 'readiness', 'potential', 'strengths', 'developmentareas', 'appraiseecomments', 'appraisercomments' ,'locked'),
        );

        $params = array(
            'appraisalid' => $this->appraisal->id,
            'user_id' => $this->appraisal->appraisee->id,
        );

        $count = 0;
        foreach ($forms as $name => $fields) {
            $count++;
            $form = new stdClass();
            $form->first = ($count == 1);
            $form->last = ($count == count($forms));
            $form->title = get_string("form:{$name}:title", 'local_onlineappraisal');

            $params['form_name'] = $name;
            $id = $DB->get_field('local_appraisal_forms', 'id', $params);
            $data = empty($id) ? array() : $DB->get_records('local_appraisal_data', array('form_id' => $id), '', 'name, type, data');

            $form->fields = $this->get_fields($fields, $name, $data);

            $this->data->forms[] = clone($form);
        }
    }

    /**
     * Get field information from loaded form data.
     *
     * @param array $fields
     * @param string $formname
     * @param array $formdata
     * @return array
     */
    private function get_fields($fields, $formname, $formdata) {
        $return = array();

        $count = 0;
        foreach ($fields as $name) {
            $count++;
            $field = new stdClass();
            $field->name = $name;
            $field->first = ($count == 1);
            $field->last = ($count == count($fields));

            // Use PDF specific string if exists.
            $component = 'local_onlineappraisal';
            $str = "form:{$formname}:{$name}";
            $pdfstr = "pdf:{$str}";
            if (get_string_manager()->string_exists($pdfstr, $component)) {
                $field->title = get_string($pdfstr, $component);
            } else {
                $field->title = get_string($str, $component);
            }

            if (isset($formdata[$name])) {
                if ($formdata[$name]->type == 'array') {
                    $field->isarray = true;
                    $field->data = unserialize($formdata[$name]->data);
                    foreach ($field->data as $index => $data) {
                        $field->data[$index] = format_text($data, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
                    }
                } else {
                    $field->data = format_text($formdata[$name]->data, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
                }
            }
            $return[] = clone($field);
        }

        return $return;
    }
}
