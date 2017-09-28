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
 * Iconmulti local_coursemetadata field.
 *
 * @package    coursemetadatafield_iconmulti
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Class coursemetadata_define_iconmulti.
 *
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class coursemetadata_define_iconmulti extends \local_coursemetadata\define_base {

    /**
     * Define the settings for a multiselect custom field.
     *
     * @param moodleform $form the course form
     */
    public function define_form_specific($form) {
        // Param 1 for iconmulti type contains the options.
        $form->addElement('textarea', 'param1', get_string('coursemetadatamenuoptions', 'local_coursemetadata'), array('rows' => 6, 'cols' => 40));
        $form->setType('param1', PARAM_MULTILANG);

        // Default data.
        $form->addElement('text', 'defaultdata', get_string('coursemetadatadefaultdata', 'local_coursemetadata'), 'size="50"');
        $form->setType('defaultdata', PARAM_MULTILANG);

        $filemanageroptions = array();
        $filemanageroptions['return_types'] = 3;
        $filemanageroptions['accepted_types'] = array('.jpg', '.jpeg', '.gif', '.png', '.svg');
        $filemanageroptions['maxbytes'] = 0;
        $filemanageroptions['maxfiles'] = -1;
        $filemanageroptions['mainfile'] = false;

        $form->addElement('filemanager', 'icons', get_string('icons', 'coursemetadatafield_iconmulti'), null, $filemanageroptions);
        $form->addHelpButton('icons', 'icons', 'coursemetadatafield_iconmulti');
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
            $defaults = explode(',', $data->defaultdata);
            foreach ($defaults as $default) {
                if (!in_array($default, $options)) {
                    $err['defaultdata'] = get_string('coursemetadatamenudefaultnotinoptions', 'local_coursemetadata');
                }
            }
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

        $draftid = file_get_submitted_draft_itemid('icons');
        file_save_draft_area_files($draftid, context_system::instance()->id, 'local_coursemetadata', "icons_{$data->id}", 0, array('subdirs' => true));

        // Tidy up removed options from coursemetadata_info_data.
        $opts = explode("\n", $data->param1);
        if (!empty($opts)) {
            $cids = $DB->get_records('coursemetadata_info_data', array('fieldid' => $data->id));
            foreach ($cids as $cid) {
                $selopts = explode(',', $cid->data);
                if (!empty($selopts)) {
                    $origcount = count($selopts);
                    $newcount = 0;
                    foreach ($selopts as $index => $selopt) {
                        if (!in_array($selopt, $opts)) {
                            unset($selopts[$index]);
                            $newcount++;
                        }
                    }
                    if ($newcount != $origcount) {
                        $cid->data = implode(',', $selopts);
                        $DB->update_record('coursemetadata_info_data', $cid);
                    }
                } else {
                    // No options, tidy up.
                    $DB->delete_records('coursemetadata_info_data', array('id' => $cid->id));
                }
            }
        } else {
            // Clear all as we have no options!
            $DB->delete_records('coursemetadata_info_data', array('fieldid' => $data->id));
        }
    }

    /**
     * Set default data.
     *
     * @param array|stdClass $defaults
     */
    public function set_data($defaults) {
        if (isset($defaults->id)) {
            $draftitemid = file_get_submitted_draft_itemid('icons');
            file_prepare_draft_area($draftitemid, context_system::instance()->id, 'local_coursemetadata', "icons_{$defaults->id}", 0,
                                    array('subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 0));
            $defaults->icons = $draftitemid;
        }
    }
}


