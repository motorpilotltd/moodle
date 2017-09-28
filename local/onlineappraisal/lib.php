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

use local_onlineappraisal\appraisal as appraisal;
use local_onlineappraisal\permissions as permissions;
use local_costcentre\costcentre as costcentre;

function local_onlineappraisal_pluginfile($course, $cm, $context, $filearea, array $args, $forcedownload,
array $options=array()) {
    global $DB, $CFG, $PAGE, $USER;

    if ($context->contextlevel != CONTEXT_SYSTEM) {
        return false;
    }

    $validfileareas = array('logo', 'appraisalfile');

    if (in_array($filearea, $validfileareas)) {
        $itemid = $args[0];

        // This filearea is used in form Last Year Review
        // If a file from this form is requested we need to check if this user
        // has access to the appraisal and has to correct permissions to the appraisal.
        if ($filearea == 'appraisalfile') {
            $appraisalid = $itemid;
            $appraisalrecord = $DB->get_record('local_appraisal_appraisal', array('id' => $appraisalid));
            $costcentre = $DB->get_field('user', 'icq', array('id' => $appraisalrecord->appraisee_userid));
            $viewingas = array();
            foreach (array('appraisee', 'appraiser', 'signoff', 'groupleader', 'hrleader') as $type) {
                switch ($type) {
                    case 'appraisee':
                    case 'appraiser':
                    case 'signoff':
                    case 'groupleader':
                        $typeuid = "{$type}_userid";
                        if ($appraisalrecord->{$typeuid} == $USER->id) {
                            $viewingas[] = $type;
                        } else if ($type === 'groupleader' && array_key_exists($USER->id, costcentre::get_cost_centre_users($costcentre, costcentre::GROUP_LEADER))) {
                            $viewingas[] = $type;
                        }
                        break;
                    case 'hrleader' :
                        if (array_key_exists($USER->id, costcentre::get_cost_centre_users($costcentre, array(costcentre::HR_LEADER, costcentre::HR_ADMIN)))) {
                            $viewingas[] = $type;
                        }
                        break;
                }
            }
            $canview = false;
            // Required when checking appraisal access.
            $PAGE->set_context($context);
            foreach ($viewingas as $type) {
                $appraisal = new appraisal($USER, $appraisalid, $type, 'overview', 0);
                $canview = $appraisal->check_permission('lastyear:view');
                if ($canview) {
                    break;
                }
            }
            if (!$canview) {
                return send_file_not_found();;
            }

        }

        $fullpath = "/$context->id/local_onlineappraisal/$filearea/$itemid/".$args[1];
        $fs = get_file_storage();
        if (!$file = $fs->get_file_by_hash(sha1($fullpath)) or $file->is_directory()) {
            send_file_not_found();
        }
        $lifetime = isset($CFG->filelifetime) ? $CFG->filelifetime : 86400;
        send_stored_file($file, $lifetime, 0, $forcedownload, $options);
    }
}