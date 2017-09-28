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

class renderer extends \local_onlineappraisal\output\renderer {

    public function render_introduction(\local_onlineappraisal\output\dashboard\introduction $introduction) {
        // Call the export_for_template function from class introduction.
        $templatevars = $introduction->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/introduction', $templatevars);
    }

    public function render_overview(\local_onlineappraisal\output\dashboard\overview $overview) {
        // Call the export_for_template function from class overview.
        $templatevars = $overview->export_for_template($this);

        // String pre-loading.
        $this->page->requires->strings_for_js(array('error:request', 'comment:addingdots'), 'local_onlineappraisal');
        // Comments JS.
        $this->page->requires->js_call_amd('local_onlineappraisal/comment', 'init',
            array('local_onlineappraisal/comment'));
        // Stages JS
        $this->page->requires->js_call_amd('local_onlineappraisal/stages', 'init');

        return $this->render_from_template('local_onlineappraisal/overview', $templatevars);
    }

    public function render_feedback(\local_onlineappraisal\output\dashboard\feedback $feedback) {
        // Call the export_for_template function from class overview.
        $templatevars = $feedback->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/feedback', $templatevars);
    }

    public function render_addfeedback(\local_onlineappraisal\output\dashboard\addfeedback $feedback) {
        // Call the export_for_template function from class overview.
        $templatevars = $feedback->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/addfeedback', $templatevars);
    }

    public function render_checkin(\local_onlineappraisal\output\dashboard\checkin $checkin) {
        // Call the export_for_template function from class overview.
        $templatevars = $checkin->export_for_template($this);
        $this->page->requires->js_call_amd('local_onlineappraisal/checkin', 'init',
            array('local_onlineappraisal/checkin'));

        return $this->render_from_template('local_onlineappraisal/checkins', $templatevars);
    }

    public function render_feedbackrequests(\local_onlineappraisal\output\dashboard\feedbackrequests $feedbackrequests) {
        // Call the export_for_template function from class overview.
        $templatevars = $feedbackrequests->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/feedbackrequests', $templatevars);
    }
}