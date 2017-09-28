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
use local_onlineappraisal\permissions as permissions;

class feedback extends base {

    /**
     * Array of feedback requests.
     * @var array $requests
     */
    private $requests;
    /**
     * Number of requests.
     * @var int $requestcount
     */
    private $requestcount;
    /**
     * Can only view own requests.
     * @var bool $viewownonly
     */
    private $viewownonly;
    /**
     * Can view confidential requests.
     * @var bool $viewconfidential
     */
    private $viewconfidential;

    /**
     * Constructor, sets properties and loads requests.
     * @global \moodle_database $DB
     * @param \local_onlineappraisal\printer $printer
     */
    public function __construct(\local_onlineappraisal\printer $printer) {
        global $DB;

        parent::__construct($printer);

        $params = array(
            'appraisalid' => $this->appraisal->id
        );
        $this->requests = $DB->get_records('local_appraisal_feedback', $params);
        $this->requestcount = count($this->requests);
        $this->viewownonly = $this->printer->appraisal->check_permission("feedbackown:print") && !$this->printer->appraisal->check_permission("feedback:print");
        $this->viewconfidential = ($this->appraisal->viewingas != 'appraisee');
    }

    /**
     * Get extra context data.
     */
    protected function get_data() {
        $this->data->requests = $this->get_feedback_requests();
        $this->data->hasrequests = (bool) count($this->data->requests);
        $this->data->responses = $this->get_feedback_responses();
        $this->data->hasresponses = (bool) count($this->data->responses);
        $this->data->viewownonly = $this->viewownonly;
        $this->data->viewconfidential = $this->viewconfidential;
    }

    /**
     * Return an array of feedback request objects for template.
     * @return array
     */
    private function get_feedback_requests() {
        $vars = array();

        $count = 0;
        foreach ($this->requests as $request) {
            $count++;
            $var = new stdClass();
            $var->first = ($count == 1);
            $var->last = ($count == $this->requestcount);
            $var->firstname = $request->firstname;
            $var->lastname = $request->lastname;
            $var->appraiserrequest = ($request->feedback_user_type == 'appraiser');
            $vars[] = clone($var);
        }

        return $vars;
    }

    /**
     * Return an array of feedback response objects for template.
     * @return array
     */
    private function get_feedback_responses() {
        $vars = array();

        $count = 0;
        foreach ($this->requests as $request) {
            if (empty($request->received_date)) {
                continue;
            }
            $count++;
            $var = new stdClass();
            $var->first = ($count == 1);
            $var->last = false; // Set later as not all requests may be responses;
            $var->firstname = $request->firstname;
            $var->lastname = $request->lastname;
            $var->confidential = ($request->confidential ? get_string('pdf:feedback:confidentialflag', 'local_onlineappraisal') : '');
            if ($var->confidential && !$this->viewconfidential) {
                $var->feedback = get_string('confidential_label', 'local_onlineappraisal');
            } else if ($request->feedback_user_type == 'appraiser' && $this->viewownonly) {
                $var->appraiserrequest = get_string('pdf:feedback:appraiserflag', 'local_onlineappraisal');
                $var->feedback = get_string('pdf:feedback:notyetavailable', 'local_onlineappraisal');
            } else {
                $var->feedback = format_text($request->feedback, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
                $var->feedback .= '<br><br>' . format_text($request->feedback_2, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
            }
            $vars[] = clone($var);
        }

        // Pop last value to update 'last' flag.
        $var = array_pop($vars);
        if ($var) {
            $var->last = true;
            // Push it back on.
            array_push($vars, $var);
        }

        return $vars;
    }
}
