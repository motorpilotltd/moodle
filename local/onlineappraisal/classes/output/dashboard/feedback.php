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

namespace local_onlineappraisal\output\dashboard;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use renderer_base;
use moodle_url;

class feedback extends base {
    /**
     * Export this data so it can be used as the context for a mustache template.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $data = new stdClass();

        $fo = new \local_onlineappraisal\feedback($this->appraisal);
        $addfeedback = optional_param('addfeedback', 0, PARAM_INT);

        $hasf2fdate = (!empty($this->appraisal->appraisal->held_date));
        // Show Form
        if ($hasf2fdate) {
            if (!$this->appraisal->form->is_submitted() && $addfeedback) {
                $data->link = new moodle_url('/local/onlineappraisal/view.php',
                array('page' => 'feedback', 'addfeedback' => 0,
                    'view' => $this->appraisal->appraisal->viewingas,
                    'appraisalid' => $this->appraisal->appraisal->id));
                $data->hasform = true;
                $data->form = $this->appraisal->form->render();
            }
            if ($this->appraisal->form->is_submitted() && !$this->appraisal->form->is_validated()) {
                $addfeedback = 1;
                $data->link = new moodle_url('/local/onlineappraisal/view.php',
                array('page' => 'feedback', 'addfeedback' => 0,
                    'view' => $this->appraisal->appraisal->viewingas,
                    'appraisalid' => $this->appraisal->appraisal->id));
                $data->hasform = true;
                $data->form = $this->appraisal->form->render();
            }
        }

        // Show Requests
        if (!$addfeedback) {
            $data->link = new moodle_url('/local/onlineappraisal/view.php',
            array('page' => 'feedback', 'addfeedback' => 1,
                'view' => $this->appraisal->appraisal->viewingas,
                'appraisalid' => $this->appraisal->appraisal->id));
            if ($this->appraisal->appraisal->viewingas == 'appraisee') {
                $data->linkreceivedfeedback = new moodle_url('/local/onlineappraisal/add_feedback.php',
                array('pw' => 'self',
                    'id' => $this->appraisal->appraisal->id));
            }
            if ($this->appraisal->appraisal->viewingas == 'appraiser') {
                $data->linkreceivedfeedback = new moodle_url('/local/onlineappraisal/add_feedback.php',
                array('pw' => 'self',
                    'id' => $this->appraisal->appraisal->id,
                    'appraiser' => 1));
            }
            $data->hasrequests = true;
            $data->requests = $fo->get_feedback_requests();
        }
        $data->canadd = \local_onlineappraisal\permissions::is_allowed('feedback:add',
            $this->appraisal->appraisal->permissionsid, $this->appraisal->appraisal->viewingas,
            $this->appraisal->appraisal->archived, $this->appraisal->appraisal->legacy);
        if (!$hasf2fdate) {
            $data->showconditions = true;
            $data->canadd = false;
        }
        $data->nextpage = $this->appraisal->get_nextpage();
        $data->appraiseename = fullname($this->appraisal->appraisal->appraisee);
        $data->tagline = get_string('tagline', 'local_onlineappraisal', strtoupper($data->appraiseename));
        return $data;
    }
}
