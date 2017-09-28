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
use Exception;
use moodle_exception;
use local_onlineappraisal\output\alert as alert;
use local_costcentre\costcentre as costcentre;

class bulkupload {

    public $pagetitle;
    public $pageheading;

    private $step;
    private $steps = array(
        'one' => false,
        'two' => false,
        'three' => false,
        'four' => false,
    );

    private $alerts = array(
        'success' => array(),
        'warning' => array(),
        'danger' => array(),
    );

    private $renderer;

    private $path = '';

    private $headers = array(
        'appraisee' => 'appraisee',
        'appraiser' => 'appraiser',
        'signoff' => 'signoff',
        'groupleader' => 'groupleader'
    );

    private $users = array();

    private $rowcount = 0;
    private $usercount = 0;
    private $okusercount = 0;

    private $samlauth;

    private $duedate = null;

    private $processing = array();


    /**
     * Constructor.
     */
    public function __construct($step) {
        global $CFG, $PAGE;

        $this->path = $CFG->dataroot . '/local_onlineappraisal/';

        $this->step = array_key_exists($step, $this->steps) ? $step : 'one';

        // Set up renderer.
        $this->renderer = $PAGE->get_renderer('local_onlineappraisal', 'bulkupload');

        // If in the midst of processing (or it's completed) then need to redirect from here...
        if ($this->step !== 'four' && file_exists($this->path . 'bulk_upload_results.json')) {
            $this->alerts['warning'][] = '<i class="fa fa-exclamation-triangle"></i> Processing in progress you cannot go to step '.$this->step.'.';
            $this->step = 'four';
        }

        // Run this step...
        $this->{$this->step}();
    }

    /**
     *  Magic getter.
     * 
     * @param string $name
     * @return mixed property
     * @throws Exception
     */
    public function __get($name) {
        if (method_exists($this, "get_{$name}")) {
            return $this->{"get_{$name}"}();
        }
        if (!isset($this->{$name})) {
            throw new Exception('Undefined property ' .$name. ' requested');
        }
        return $this->{$name};
    }

    private function set_samlauth() {
        if (empty($this->samlauth)) {
            $this->samlauth = get_auth_plugin('saml');
        }
    }

    /**
     * Setup the page variables.
     */
    public function setup_page() {
        $this->pagetitle = 'Bulk Upload';
        $this->pageheading = 'Bulk Upload';
    }

    /**
     * Generate the main content for the page
     *
     * @return string html
     */
    public function main_content() {
        $preamblevars = new stdClass();
        $preamblevars->steptitle = 'Step '.ucfirst($this->step);
        $preamble = $this->renderer->render_from_template('local_onlineappraisal/bulkupload_preamble', $preamblevars);

        // Are there alerts?
        $alerthtml = '';
        foreach ($this->alerts as $type => $alerts) {
            if (!empty($alerts)) {
                $alert = new alert(implode('<br />', $alerts), $type, false);
                $alerthtml .= $this->renderer->render($alert);
            }
        }

        $class = "\\local_onlineappraisal\\output\bulkupload\\step{$this->step}";
        $view = new $class($this);

        $viewhtml = $this->renderer->render($view);

        return $preamble . $alerthtml . $viewhtml;
    }

    private function one() {
        // Clear existing json file.
        @unlink($this->path . 'bulk_upload.json');
        if (file_exists($this->path . 'bulk_upload.csv')) {
            $this->alerts['success'][] = '<i class="fa fa-check"></i> CSV found';

            $csv = array_map('str_getcsv', file($this->path . 'bulk_upload.csv'));
            foreach ($csv as $i => &$row) {
                $row = (count($csv[0]) === count($row) ? array_combine($csv[0], $row) : null);
                if (empty($row)) {
                    $this->alerts['danger'][] = '<i class="fa fa-exclamation-triangle"></i> Missing data, row '.($i+1).' of CSV.';
                } else {
                    foreach ($row as $type => $staffid) {
                        if ($type !== 'groupleader' && empty($staffid)) {
                            $this->alerts['danger'][] = '<i class="fa fa-exclamation-triangle"></i> Missing '.$type.' staff id, row '.($i+1).' of CSV.';
                        }
                    }
                }
            }
            $headers = array_shift($csv); // Removes headers.
            if (empty($this->alerts['danger']) && $headers === $this->headers) {
                $rows = count($csv);
                $rowplural = ($rows === 1 ? '' : 's');
                $this->alerts['success'][] = '<i class="fa fa-check"></i> CSV correctly formatted ('.$rows." row{$rowplural} found)";
                if (!file_put_contents($this->path . 'bulk_upload.json', json_encode($csv))) {
                    $this->alerts['danger'][] = '<i class="fa fa-exclamation-triangle"></i> Failed to save data for processing to disk.';
                } else {
                    $this->steps['one'] = true;
                }
            } else if ($headers !== $this->headers) {
                $this->alerts['danger'][] = '<i class="fa fa-exclamation-triangle"></i> CSV incorrectly formatted';
            }
        } else {
            $this->alerts['danger'][] = '<i class="fa fa-exclamation-triangle"></i> CSV not found';
        }

        $pdfs = count(glob($this->path . '*.pdf'));
        $pdfplural = ($pdfs === 1 ? '' : 's');
        $this->alerts['warning'][] = '<i class="fa fa-question-circle"></i> '.$pdfs." PDF{$pdfplural} found";
    }

    private function two() {
        // Clear existing json file.
        @unlink($this->path . 'bulk_upload_user_map.json');

        if (!file_exists($this->path . 'bulk_upload.json')) {
            $this->alerts['danger'][] = '<i class="fa fa-exclamation-triangle"></i> Step one has not been successfully completed.';
            return;
        }

        $data = json_decode(file_get_contents($this->path . 'bulk_upload.json'));
        foreach ($data as $users) {
            $this->rowcount++;
            foreach($users as $user) {
                $staffid = str_pad($user, 6, '0', STR_PAD_LEFT);
                if ($staffid !== '000000' && !isset($this->users[$staffid])) {
                    $this->usercount++;
                    try {
                        $this->process_staffid($staffid);
                    } catch (Exception $e) {
                        $this->alerts['danger'][] = '<i class="fa fa-exclamation-triangle"></i> '.$e->getMessage();
                        error_log("local/onlineappraisal : Bulk Upload : Step Two : ERROR\n{$e->getMessage()}\n{$e->getTraceAsString()}");
                    }
                }
            }
            if ($this->rowcount % 100 === 0) {
                error_log("local/onlineappraisal : Bulk Upload : Step Two : {$this->rowcount} rows processed.");
            }
        }

        if (empty($this->alerts['danger']) && !file_put_contents($this->path . 'bulk_upload_user_map.json', json_encode($this->users))) {
            $this->alerts['danger'][] = '<i class="fa fa-exclamation-triangle"></i> Failed to save data for processing to disk.';
        }

        if (empty($this->alerts['danger'])) {
            $this->steps['two'] = true;
        }

        $fp = fopen($this->path.'bulk_upload_user_details.csv', 'w');
        fputcsv($fp, array('Staff ID', 'Moodle ID', 'Cost Centre', 'Moodle OK'));
        foreach ($this->users as $staffid => $data) {
            fputcsv($fp, array($staffid, $data->userid, $data->costcentre, (int)$data->moodleok));
        }
        fclose($fp);
    }

    private function three() {
        global $DB;

        if (!file_exists($this->path . 'bulk_upload_user_map.json')) {
            $this->alerts['danger'][] = '<i class="fa fa-exclamation-triangle"></i> Step two has not been successfully completed.';
            return;
        }

        $this->duedate = required_param('duedate', PARAM_INT);

        $data = json_decode(file_get_contents($this->path . 'bulk_upload.json'));
        $users = json_decode(file_get_contents($this->path . 'bulk_upload_user_map.json'));

        $output = array(
            'duedate' => $this->duedate,
            'appraisals' => array(),
        );

        foreach ($data as $appraisal) {
            $appraisee = $appraiser = $signoff = $groupleader = null;
            foreach ($appraisal as $type => $intstaffid) {
                // From original upload so needs padding.
                $staffid = str_pad($intstaffid, 6, '0', STR_PAD_LEFT);
                // Intentional variable variable.
                $$type = (empty($staffid) || empty($users->{$staffid})) ? null : $users->{$staffid};
            }
            if (!empty($groupleader) && $groupleader->userid == $signoff->userid) {
                $groupleader = null;
            }
            $costcentre = costcentre::get_setting($appraisee->costcentre);
            if (!$costcentre) {
                $costcentre = new stdClass();
                $costcentre->costcentre = $appraisee->costcentre;
                $costcentre->enableappraisal = 1;
                $costcentre->groupleaderactive = empty($groupleader) ? null : 1;
                $costcentre->id = $DB->insert_record('local_costcentre', $costcentre);
                $this->alerts['success'][] = $appraisee->costcentre.' appraisal settings added.';
            } else if (!$costcentre->enableappraisal || (!empty($groupleader) && !$costcentre->groupleaderactive)) {
                $costcentre->enableappraisal = 1;
                // Use current if no groupleader as may already be set for another appraisal.
                $costcentre->groupleaderactive = empty($groupleader) ? $costcentre->groupleaderactive : 1;
                $DB->update_record('local_costcentre', $costcentre);
                $this->alerts['success'][] = $appraisee->costcentre.' appraisal settings updated.';
            }
            // Check appraiser.
            if ($appraiser->costcentre !== $appraisee->costcentre) {
                $apermission = $DB->get_record('local_costcentre_user', array('userid' => $appraiser->userid, 'costcentre' => $appraisee->costcentre));
                if (!$apermission) {
                    $apermission = new stdClass();
                    $apermission->userid = $appraiser->userid;
                    $apermission->costcentre = $appraisee->costcentre;
                    $apermission->permissions = costcentre::APPRAISER;
                    $apermission->id = $DB->insert_record('local_costcentre_user', $apermission);
                    $this->alerts['success'][] = $appraiser->staffid.' given (added) appraiser permissions on cost centre '.$appraisee->costcentre.'.';
                } else if ((costcentre::APPRAISER & $apermission->permissions) !== costcentre::APPRAISER) {
                    $apermission->permissions = $apermission->permissions + costcentre::APPRAISER;
                    $DB->update_record('local_costcentre_user', $apermission);
                    $this->alerts['success'][] = $appraiser->staffid.' given (updated) appraiser permissions on cost centre '.$appraisee->costcentre.'.';
                } else {
                    $this->alerts['success'][] = $appraiser->staffid.' already has appraiser permissions on cost centre '.$appraisee->costcentre.'.';
                }
            } else {
                $this->alerts['success'][] = $appraiser->staffid.' (appraiser) is in cost centre '.$appraisee->costcentre.'.';
            }
            // Check signoff.
            $spermission = $DB->get_record('local_costcentre_user', array('userid' => $signoff->userid, 'costcentre' => $appraisee->costcentre));
            if (!$spermission) {
                $spermission = new stdClass();
                $spermission->userid = $signoff->userid;
                $spermission->costcentre = $appraisee->costcentre;
                $spermission->permissions = costcentre::SIGNATORY;
                $spermission->id = $DB->insert_record('local_costcentre_user', $spermission);
                $this->alerts['success'][] = $signoff->staffid.' given (added) signoff permissions on cost centre '.$appraisee->costcentre.'.';
            } else if (((costcentre::SIGNATORY & $spermission->permissions) !== costcentre::SIGNATORY)
                    && ((costcentre::GROUP_LEADER & $spermission->permissions) !== costcentre::GROUP_LEADER)) {
                $spermission->permissions = $spermission->permissions + costcentre::SIGNATORY;
                $DB->update_record('local_costcentre_user', $spermission);
                $this->alerts['success'][] = $signoff->staffid.' given (updated) signoff permissions on cost centre '.$appraisee->costcentre.'.';
            } else {
                $this->alerts['success'][] = $signoff->staffid.' already has signoff permissions on cost centre '.$appraisee->costcentre.'.';
            }
            // Groupleader handling.
            if (!empty($groupleader)) {
                // Check for groupleaderactive already carried out.
                $gpermission = $DB->get_record('local_costcentre_user', array('userid' => $groupleader->userid, 'costcentre' => $appraisee->costcentre));
                if (!$gpermission) {
                    $gpermission = new stdClass();
                    $gpermission->userid = $groupleader->userid;
                    $gpermission->costcentre = $appraisee->costcentre;
                    $gpermission->permissions = costcentre::GROUP_LEADER_APPRAISAL; // Only give them appraisal groupleader access.
                    $gpermission->id = $DB->insert_record('local_costcentre_user', $gpermission);
                    $this->alerts['success'][] = $groupleader->staffid.' given (added) groupleader appraisal permissions on cost centre '.$appraisee->costcentre.'.';
                } else if ((costcentre::GROUP_LEADER & $gpermission->permissions) !== costcentre::GROUP_LEADER
                        && (costcentre::GROUP_LEADER_APPRAISAL & $gpermission->permissions) !== costcentre::GROUP_LEADER_APPRAISAL) {
                    $gpermission->permissions = $gpermission->permissions + costcentre::GROUP_LEADER_APPRAISAL; // Only give them appraisal groupleader access.
                    $DB->update_record('local_costcentre_user', $gpermission);
                    $this->alerts['success'][] = $groupleader->staffid.' given (updated) groupleader appraisal permissions on cost centre '.$appraisee->costcentre.'.';
                } else {
                    $this->alerts['success'][] = $groupleader->staffid.' already has groupleader permissions on cost centre '.$appraisee->costcentre.'.';
                }
            }

            $output['appraisals'][] = array(
                'appraisee' => array('userid' => $appraisee->userid, 'staffid' => $appraisee->staffid),
                'appraiser' => array('userid' => $appraiser->userid, 'staffid' => $appraiser->staffid),
                'signoff' => array('userid' => $signoff->userid, 'staffid' => $signoff->staffid),
                'groupleader' => empty($groupleader) ? null : array('userid' => $groupleader->userid, 'staffid' => $groupleader->staffid),
            );
        }

        if (!file_put_contents($this->path . 'bulk_upload_process.json', json_encode($output))) {
            $this->alerts['danger'][] = '<i class="fa fa-exclamation-triangle"></i> Failed to save data for processing to disk.';
        }

        if (empty($this->alerts['danger'])) {
            $this->steps['three'] = true;
            if (empty($this->alerts['success'])) {
                $this->alerts['success'][] = '<i class="fa fa-check"></i> No updates were necessary, all users were found to have the correct permissions.';
            }
        }
    }

    private function four() {
        if (!file_exists($this->path . 'bulk_upload_process.json')) {
            $this->alerts['danger'][] = '<i class="fa fa-exclamation-triangle"></i> Step three has not been successfully completed.';
            return;
        }

        // Create results json file if not present.
        touch($this->path . 'bulk_upload_results.json');

        $toprocess = json_decode(file_get_contents($this->path . 'bulk_upload_process.json'));
        $results = json_decode(file_get_contents($this->path . 'bulk_upload_results.json'));

        if (empty($results)) {
            $results = new stdClass();
            $results->nextruntime = 0;
            $results->total = 0;
            $results->error = 0;
            $results->success = 0;
            $results->appraisals = array();
        }

        if (optional_param('process', false, PARAM_BOOL) && !get_config('local_onlineappraisal', 'bulkuploadinprogress')) {
            set_config('bulkuploadinprogress', true, 'local_onlineappraisal');
            $count = 0;
            // Convert to array (for unsetting) and deep clone (to avoid object references).
            $appraisals = unserialize(serialize((array) $toprocess->appraisals));
            foreach ($appraisals as $index => $appraisal) {
                $count++;
                $results->total++;
                try {
                    $result = $this->initialise_appraisal($appraisal, $toprocess->duedate);
                    $result->success ? $results->success++ : $results->error++;
                    $appraisal->message = $result->message;
                    $pdfresult = $this->find_upload_pdf($result->success, $appraisal->appraisee);
                    $appraisal->pdfresult = $pdfresult->success;
                    $appraisal->pdfmessage = $pdfresult->message;
                } catch (Exception $e) {
                    $appraisal->message = $e->getMessage();
                    $results->error++;
                }
                $results->appraisals[] = $appraisal;
                unset($appraisals[$index]);
                if ($count >= 100) {
                    break;
                }
            }
            $toprocess->appraisals = $appraisals;

            // Save
            file_put_contents($this->path . 'bulk_upload_process.json', json_encode($toprocess));
            file_put_contents($this->path . 'bulk_upload_results.json', json_encode($results));
            
            set_config('bulkuploadinprogress', null, 'local_onlineappraisal');
        } else if (get_config('local_onlineappraisal', 'bulkuploadinprogress')) {
            $this->alerts['warning'][] = '<i class="fa fa-exclamation-triangle"></i> Processing is currently in progress and a new batch cannot yet be triggered.';
        }

        $this->processing = array(
            'total' => $results->total,
            'error' => $results->error,
            'success' => $results->success,
            'toprocess' => count($toprocess->appraisals),
            'inprogress' => get_config('local_onlineappraisal', 'bulkuploadinprogress'),
        );

        $this->steps['four'] = true;
    }

    private function process_staffid($staffid) {
        global $DB;

        $this->users[$staffid] = new stdClass();
        $this->users[$staffid]->staffid = $staffid;
        $this->users[$staffid]->userid = 0;
        $this->users[$staffid]->costcentre = '';
        $this->users[$staffid]->moodleok = false;

        $moodleuser = $DB->get_record('user', array('idnumber' => $staffid, 'deleted' => 0));

        if (!$moodleuser) {
            $this->alerts['danger'][] = $staffid.' NOT MOODLE USER.';
            return false;
        }

        if ($moodleuser->auth != 'saml') {
            $this->alerts['danger'][] = $staffid.' NOT SAML AUTH.';
            return false;
        }

        if (!preg_match('/^[0-9]{2}-[0-9]{3}$/', $moodleuser->icq)) {
            $this->alerts['danger'][] = $staffid.' Cost centre is not in correct format (XX-XXX).';
            return false;
        }

        // Update user details.
        $this->users[$staffid]->moodleok = true;
        $this->users[$staffid]->userid = $moodleuser->id;
        $this->users[$staffid]->costcentre = $moodleuser->icq;
        $this->okusercount++;
        return true;
    }

    private function initialise_appraisal($details, $duedate) {
        global $DB;

        $existingparams = array(
            'appraisee_userid' => $details->appraisee->userid,
            'archived' => 0,
            'deleted' => 0,
        );
        $existing = $DB->get_records('local_appraisal_appraisal', $existingparams);
        if (!empty($existing)) {
            // Active appraisal already exists.
            throw new moodle_exception('error:appraisalexists', 'local_onlineappraisal');
        }
        // New appraisal record.
        $record = new stdClass();
        $record->appraisee_userid = $details->appraisee->userid;
        $record->appraiser_userid = $details->appraiser->userid;
        $record->signoff_userid = $details->signoff->userid;
        $record->groupleader_userid = empty($details->groupleader) ? null : $details->groupleader->userid; // Use $groupleaderid as always set.
        $record->statusid = 1;
        $record->permissionsid = 1;
        $record->modified_date = $record->created_date = time();
        $record->due_date = $duedate;
        $record->status_history = '1';

        $query = "
            SELECT
                CORE_JOB_TITLE as jobtitle, GRADE as grade
            FROM
                SQLHUB.ARUP_ALL_STAFF_V
            WHERE
                EMPLOYEE_NUMBER = :idnumber
        ";
        $params= ['idnumber' => (int) $details->appraisee->staffid];

        $hubdata = $DB->get_record_sql($query, $params);

        $record->job_title = !empty($hubdata->jobtitle) ? $hubdata->jobtitle : '';
        $record->grade = !empty($hubdata->grade) ? $hubdata->grade : '';

        $record->id = $DB->insert_record('local_appraisal_appraisal', $record);

        $return = new stdClass();
        $return->success = $record->id;
        $return->data = '';

        if ($return->success) {
            // Load users!
            $appraisee = $DB->get_record('user', array('id' => $details->appraisee->userid));
            $appraiser = $DB->get_record('user', array('id' => $details->appraiser->userid));
            $signoff = $DB->get_record('user', array('id' => $details->signoff->userid));
            $groupleader = empty($details->groupleader) ? null : $DB->get_record('user', array('id' => $details->groupleader->userid));
            $admin = get_admin(); // For sending emails.

            $emailvars = new stdClass();
            $emailvars->appraiseefirstname = $appraisee->firstname;
            $emailvars->appraiseelastname = $appraisee->lastname;
            $emailvars->appraiseeemail = $appraisee->email;
            $emailvars->appraiserfirstname = $appraiser->firstname;
            $emailvars->appraiserlastname = $appraiser->lastname;
            $emailvars->appraiseremail = $appraiser->email;
            $emailvars->signofffirstname = $signoff->firstname;
            $emailvars->signofflastname = $signoff->lastname;
            $emailvars->signoffemail = $signoff->email;
            $emailvars->groupleaderfirstname = $groupleader ? $groupleader->firstname : '-';
            $emailvars->groupleaderlastname = $groupleader ? $groupleader->lastname : '-';
            $emailvars->groupleaderemail = $groupleader ? $groupleader->email : '-';
            $url = new \moodle_url(
                    '/local/onlineappraisal/view.php',
                    array('appraisalid' => $record->id, 'view' => 'appraisee', 'page' => 'overview')
                    );
            $urldashboard = new \moodle_url(
                    '/local/onlineappraisal/index.php',
                    array('page' => 'appraisee')
                    );
            $emailvars->linkappraisee = $url->out();
            $emailvars->linkappraiseedashboard = $urldashboard->out();
            $url->param('view', 'appraiser');
            $urldashboard->param('page', 'appraiser');
            $emailvars->linkappraiser = $url->out();
            $emailvars->linkappraiserdashboard = $urldashboard->out();
            $url->param('view', 'signoff');
            $urldashboard->param('page', 'signoff');
            $emailvars->linksignoff = $url->out();
            $emailvars->linksignoffdashboard = $urldashboard->out();
            $url->param('view', 'groupleader');
            $urldashboard->param('page', 'groupleader');
            $emailvars->linkgroupleader = $url->out();
            $emailvars->linkgroupleaderdashboard = $urldashboard->out();
            $emailvars->duedate = userdate($record->due_date, get_string('strftimedate'));
            $emailvars->status = get_string("status:{$record->statusid}", 'local_onlineappraisal');
            $emailvars->bafirstname = $admin->firstname;
            $emailvars->balastname = $admin->lastname;
            $emailvars->baemail = $admin->email;

            $appraiseeemail = new email('status:0_to_1:appraisee', $emailvars, $appraisee, $admin);
            if ($appraiseeemail->used_language() != current_language()) {
                $appraiseeemail->set_emailvar('duedate', self::userdate($appraiseeemail->used_language(), $record->due_date, 'strftimedate', '', new \DateTimeZone('UTC')));
            }
            $appraiseeemail->prepare();
            $appraiseeemailsent = $appraiseeemail->send();
            $appraiseremail = new email('status:0_to_1:appraiser', $emailvars, $appraiser, $admin);
            if ($appraiseremail->used_language() != current_language()) {
                $appraiseremail->set_emailvar('duedate', self::userdate($appraiseremail->used_language(), $record->due_date, 'strftimedate', '', new \DateTimeZone('UTC')));
            }
            $appraiseremail->prepare();
            $appraiseremailsent = $appraiseremail->send();

            // Add comment
            $a = new stdClass();
            $a->status = get_string('status:1', 'local_onlineappraisal');
            $a->relateduser = fullname($admin);
            $comment = comments::save_comment(
                    $record->id,
                    get_string(
                            'comment:status:0_to_1',
                            'local_onlineappraisal',
                            $a
                            )
                    );
            $return->message = get_string('success:appraisal:create', 'local_onlineappraisal');
            if (!$appraiseeemailsent) {
                $return->message .= get_string('error:appraisal:create:appraiseeemail', 'local_onlineappraisal');
            }
            if (!$appraiseremailsent) {
                $return->message .= get_string('error:appraisal:create:appraiseremail', 'local_onlineappraisal');
            }
            if (!$comment->id) {
                $return->message .= get_string('error:appraisal:create:comment', 'local_onlineappraisal');
            }
        } else {
            $return->message = get_string('error:appraisal:create', 'local_onlineappraisal');
        }

        return $return;
    }

    private function find_upload_pdf($appraisalid, $appraisee) {
        global $DB;

        $return = new stdClass();
        $return->success = false;
        $return->message = '';

        if (!$appraisalid) {
            $return->message = 'Appraisal not created.';
            return $return;
        }

        $pdfs = glob($this->path . "{$appraisee->staffid}*.pdf");

        if (count($pdfs) < 1) {
            $return->message = 'No PDF found.';
            return $return;
        }

        if (count($pdfs) > 1) {
            $return->message = 'Multiple PDFs found.';
            return $return;
        }

        $pdf = array_pop($pdfs);

        $fs = get_file_storage();
        $context = \context_system::instance();
        $user = $DB->get_record('user', array('id' => $appraisee->userid));
        $filerecord = array(
            'contextid' => $context->id,
            'component' => 'local_onlineappraisal',
            'filearea' => 'appraisalfile',
            'itemid' => $appraisalid,
            'filepath' => '/',
            'filename' => pathinfo($pdf, PATHINFO_BASENAME),
            'userid' => $user->id,
            'author' => fullname($user),
            'license' => 'allrightsreserved',
            'timecreated' => time(),
            'timemodified' => time()
        );
        $fs->create_file_from_pathname($filerecord, $pdf);

        $return->success = true;
        return $return;
    }
}