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

class leaderplan extends base {
    /**
     * Get extra context data.
     */
    protected function get_data() {
        // Get fields.
        $this->get_fields();

        // Get extra user data.
        $this->get_extra_user_data();
    }

    /**
     * Inject field info and data into data object.
     *
     * @global \moodle_database $DB
     */
    private function get_fields() {
        global $DB;

        // Set up to match generic print template but to present as single form.
        $this->data->forms = [];
        $this->data->forms[0] = new stdClass();
        $this->data->forms[0]->first = true;
        $this->data->forms[0]->last = true;
        $this->data->forms[0]->title = get_string('form:leaderplan:title', 'local_onlineappraisal');
        $this->data->forms[0]->fields = [];

        // Forms and fields to print (in order).
        // Asterisks used to enable form repetition - stripped out before loading/processing!
        $formfields = [
            'leaderplan' => ['ldppotential'],
            'careerdirection' => ['mobility'],
            'leaderplan*' => ['ldpstrengths', 'ldpdevelopmentareas'],
            'summaries' => ['appraiser'],
            'development' => ['seventy', 'twenty', 'ten'],
            'leaderplan**' => ['ldpdevelopmentplan'],
            'careerdirection*' => ['progress', 'comments'],
            'leaderplan***' => ['ldplocked'],
        ];

        $params = [
            'appraisalid' => $this->appraisal->id,
            'user_id' => $this->appraisal->appraisee->id,
        ];

        $count = 0;
        $totalcount = count($formfields, COUNT_RECURSIVE) - count($formfields); // All elements minus top level (form names).
        foreach ($formfields as $form => $fields) {
            $params['form_name'] = rtrim($form, '*');
            $id = $DB->get_field('local_appraisal_forms', 'id', $params);
            $data = empty($id) ? array() : $DB->get_records('local_appraisal_data', array('form_id' => $id), '', 'name, type, data');

            foreach ($fields as $field) {
                $count++;
                $this->data->forms[0]->fields[] = $this->get_field($field, $params['form_name'], $data, ($count === 1), ($count === $totalcount));
            }
        }
    }

    /**
     * Get field information from loaded form data.
     *
     * @param string $fieldname
     * @param string $formname
     * @param array $formdata
     * @return array
     */
    private function get_field($fieldname, $formname, $formdata, $first, $last) {
        $field = new stdClass();
        $field->name = $fieldname;
        $field->first = $first;
        $field->last = $last;

        // Use PDF specific string if exists.
        $component = 'local_onlineappraisal';
        $str = "form:{$formname}:{$fieldname}";
        // As pulls from other forms and we don't want their names edited!
        $pdfstr = "pdf:leaderplan:{$fieldname}";
        if (get_string_manager()->string_exists($pdfstr, $component)) {
            $field->title = get_string($pdfstr, $component);
        } else {
            $field->title = get_string($str, $component);
        }

        if (isset($formdata[$fieldname])) {
            if ($fieldname === 'ldplocked') {
                $field->data = ($formdata[$fieldname]->data ? get_string('form:confirm:cancel:yes', $component) : get_string('form:confirm:cancel:no', $component));
            } else if ($formdata[$fieldname]->type == 'array') {
                $field->isarray = true;
                $field->data = unserialize($formdata[$fieldname]->data);
                $count = 0;
                foreach ($field->data as $index => $data) {
                    $count++;
                    $field->data[$index] = new stdClass();
                    $field->data[$index]->content = "{$count}. " . format_text($data, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
                    $field->data[$index]->last = ($count === count($field->data));
                }
            } else {
                $field->data = format_text($formdata[$fieldname]->data, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
            }
        }

        return $field;
    }

    private function get_extra_user_data() {
        global $DB;

        $sql = "SELECT name, type, data
                  FROM {local_appraisal_data} lad
                  JOIN {local_appraisal_forms} laf ON laf.id = lad.form_id
                 WHERE laf.form_name = :form_name
                       AND laf.appraisalid = :appraisalid
                       AND laf.user_id = :user_id";
        $params = [
            'form_name' => 'leaderplan',
            'appraisalid' => $this->appraisal->id,
            'user_id' => $this->appraisal->appraisee->id,
        ];
        $userdata = $DB->get_records_sql($sql, $params);
        $this->data->location = (!empty($userdata['location']) ? $userdata['location']->data : '');
        $this->data->group = (!empty($userdata['group']) ? $userdata['group']->data : '');
    }
}
