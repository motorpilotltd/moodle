<?php
// This file is part of Moodle - http://moodle.org/
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
 * Menu local_coursemetadata field.
 *
 * @package    coursemetadatafield_menu
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class coursemetadata_define_menu.
 *
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class coursemetadata_define_menu extends \local_coursemetadata\define_base {

    /**
     * Define the settings for a menu custom field.
     *
     * @param moodleform $form the course form
     */
    public function define_form_specific($form) {
        // Param 1 for menu type contains the options.
        $form->addElement('textarea', 'param1', get_string('coursemetadatamenuoptions', 'local_coursemetadata'), array('rows' => 6, 'cols' => 40));
        $form->setType('param1', PARAM_TEXT);

        // Default data.
        $form->addElement('text', 'defaultdata', get_string('coursemetadatadefaultdata', 'local_coursemetadata'), 'size="50"');
        $form->setType('defaultdata', PARAM_TEXT);
    }

    /**
     * Validates data for the field.
     *
     * @param array $data
     * @param array $files
     * @return array
     */
    public function define_validate_specific($data, $files) {
        $err = array();

        $data->param1 = str_replace("\r", '', $data->param1);

        // Check that we have at least 2 options.
        if (($options = explode("\n", $data->param1)) === false) {
            $err['param1'] = get_string('coursemetadatamenuoptions', 'local_coursemetadata');
        } else if (count($options) < 2) {
            $err['param1'] = get_string('coursemetadatamenutoofewoptions', 'local_coursemetadata');

            // Check the default data exists in the options.
        } else if (!empty($data->defaultdata) and !in_array($data->defaultdata, $options)) {
            $err['defaultdata'] = get_string('coursemetadatamenudefaultnotinoptions', 'local_coursemetadata');
        }
        return $err;
    }

    /**
     * Processes data before it is saved.
     *
     * @param array|stdClass $data
     * @return array|stdClass
     */
    public function define_save_preprocess($data) {
        $data->param1 = str_replace("\r", '', $data->param1);

        return $data;
    }

    /**
     * Processes data after it is saved.
     *
     * @param array|stdClass $data
     * @return array|stdClass
     */
    public function define_save_postprocess($data) {
        global $DB;

        // Tidy up removed options from coursemetadata_info_data.
        $opts = explode("\n", $data->param1);
        if (!empty($opts)) {
            list($usql, $params) = $DB->get_in_or_equal($opts, SQL_PARAMS_NAMED, 'opt', false);
            $where = "fieldid = :fieldid AND {$DB->sql_compare_text('data', 255)} {$usql}";
            $params['fieldid'] = $data->id;
            $DB->delete_records_select('coursemetadata_info_data', $where, $params);
        } else {
            // Clear all as we have no options!
            $DB->delete_records('coursemetadata_info_data', array('fieldid' => $data->id));
        }
    }
}


