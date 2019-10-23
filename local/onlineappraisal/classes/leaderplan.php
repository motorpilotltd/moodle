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

namespace local_onlineappraisal;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use moodle_url;

class leaderplan {

    private $appraisal;

    /**
     * Constructor.
     *
     * @param object $appraisal the full appraisal object
     */
    public function __construct(\local_onlineappraisal\appraisal $appraisal) {
        $this->appraisal = $appraisal;
    }

    /**
     * Hook
     *
     * This function is called from the main appraisal controller when the page
     * is loaded. This function can be added to all the other page types as long as this
     * class is being declared in \local_onlineappraisal\appraisal->add_page();
     *
     * @global \stdClass $SESSION
     * @return void
     */
    public function hook() {
        global $DB;

        $appraisal = $this->appraisal->appraisal;

        $action = optional_param('leaderplanaction', '', PARAM_ALPHANUMEXT);

        if ($action == 'import' && $appraisal->viewingas === 'appraisee') {
            require_sesskey();

            // Set up redirect URL.
            $redirecturl = new moodle_url(
                '/local/onlineappraisal/view.php',
                array(
                    'appraisalid' => $this->appraisal->appraisal->id,
                    'view' => $this->appraisal->appraisal->viewingas,
                    'page' => $this->appraisal->page,
                ));

            if (!$this->appraisal->pages['leaderplan']->add) {
                // Locked for user.
                \local_onlineappraisal\appraisal::set_alert(get_string('form:leaderplan:import:error:lockeduser', 'local_onlineappraisal'), 'danger');
                redirect($redirecturl);
            }

            $formrec = $DB->get_record(
                'local_appraisal_forms',
                [
                    'appraisalid' => $appraisal->id,
                    'user_id' => $appraisal->appraisee->id,
                    'form_name' => 'leaderplan'
                ]);

            if ($formrec) {
                $fields = ['ldplocked', 'ldppotential', 'ldpstrengths', 'ldpdevelopmentareas', 'ldpdevelopmentplan'];
                list($in, $params) = $DB->get_in_or_equal($fields, SQL_PARAMS_NAMED);
                $params['formid'] = $formrec->id;
                $fieldrecs = $DB->get_records_select('local_appraisal_data', "form_id = :formid AND name {$in}", $params);
                foreach ($fieldrecs as $fieldrec) {
                    if ($fieldrec->type === 'array' && empty(unserialize($fieldrec->data))) {
                        continue;
                    } else if (empty($fieldrec->data)) {
                        continue;
                    }
                    if ($fieldrec->name === 'ldplocked') {
                        \local_onlineappraisal\appraisal::set_alert(get_string('form:leaderplan:import:error:locked', 'local_onlineappraisal'), 'danger');
                    } else {
                        \local_onlineappraisal\appraisal::set_alert(get_string('form:leaderplan:import:error:hasdata', 'local_onlineappraisal'), 'danger');
                    }
                    // Redirect as not importing.
                    redirect($redirecturl);
                }
            }

            // Import data here!
            if (!$this->import_previous_ldp($appraisal->id, $appraisal->appraisee->id)) {
                \local_onlineappraisal\appraisal::set_alert(get_string('form:leaderplan:import:error:import', 'local_onlineappraisal'), 'danger');
            } else {
                \local_onlineappraisal\appraisal::set_alert(get_string('form:leaderplan:import:success', 'local_onlineappraisal'), 'success');
            }
            redirect($redirecturl);
        }
    }

    /**
     * Import previous appraisal LDP for this user.
     * @param int $appraisalid
     * @param int $userid
     */
    private function import_previous_ldp($appraisalid, $userid) {
        global $DB;

        $select = "appraisee_userid = :userid AND deleted = 0 AND id != :appraisalid";
        $params = array('userid' => $userid, 'appraisalid' => $appraisalid);
        $appraisal = $DB->get_records_select('local_appraisal_appraisal', $select, $params, 'created_date DESC', '*', 0, 1);
        if (!$appraisal) {
            return false;
        }
        $oldappraisal = array_pop($appraisal);

        $oldformrec = $DB->get_record(
            'local_appraisal_forms',
            [
                'appraisalid' => $oldappraisal->id,
                'user_id' => $userid,
                'form_name' => 'leaderplan'
            ]);
        if (!$oldformrec) {
            return false;
        }

        // Does current form exist?
        // If not create.
        $newformrec = $DB->get_record(
            'local_appraisal_forms',
            [
                'appraisalid' => $appraisalid,
                'user_id' => $userid,
                'form_name' => 'leaderplan'
            ]);
        if (!$newformrec) {
            $newformrec = new stdClass();
            $newformrec->appraisalid = $appraisalid;
            $newformrec->form_name = 'leaderplan';
            $newformrec->user_id = $userid;
            $newformrec->timemodified = $newformrec->timecreated = time();
            $newformrec->id = $DB->insert_record('local_appraisal_forms', $newformrec);
            if (!$newformrec->id) {
                return false;
            }
        }

        $fields = ['ldppotential', 'ldpstrengths', 'ldpdevelopmentareas', 'ldpdevelopmentplan'];
        list($in, $params) = $DB->get_in_or_equal($fields, SQL_PARAMS_NAMED);
        $params['formid'] = $oldformrec->id;
        $fieldrecs = $DB->get_records_select('local_appraisal_data', "form_id = :formid AND name {$in}", $params);
        if (!$fieldrecs) {
            return false;
        }
        $dataimported = false;
        foreach ($fieldrecs as $fieldrec) {
            if ($fieldrec->type === 'array' && empty(unserialize($fieldrec->data))) {
                continue;
            } else if (empty($fieldrec->data)) {
                continue;
            }
            // Copy to current form.
            unset($fieldrec->id);
            $fieldrec->form_id = $newformrec->id;
            $existing = $DB->get_record('local_appraisal_data', ['form_id' => $fieldrec->form_id, 'name' => $fieldrec->name]);
            if ($existing) {
                $fieldrec->id = $existing->id;
                $DB->update_record('local_appraisal_data', $fieldrec);
            } else {
                $fieldrec->id = $DB->insert_record('local_appraisal_data', $fieldrec);
            }
            $dataimported = true;
        }

        if ($dataimported) {
            return true;
        }

        return false;
    }
}
