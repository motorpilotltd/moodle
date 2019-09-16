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

class renderer extends \local_onlineappraisal\output\renderer {

    public function render_error(\local_onlineappraisal\output\printer\error $error) {
        // Call the export_for_template function.
        $templatevars = $error->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/printer_error', $templatevars);
    }

    public function render_header(\local_onlineappraisal\output\printer\header $header) {
        // Call the export_for_template function.
        $templatevars = $header->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/printer_header', $templatevars);
    }

    public function render_appraisal(\local_onlineappraisal\output\printer\appraisal $appraisal) {
        // Call the export_for_template function.
        $templatevars = $appraisal->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/printer_appraisal', $templatevars);
    }

    public function render_appraisal_legacy(\local_onlineappraisal\output\printer\appraisal_legacy $appraisal) {
        // Call the export_for_template function.
        $templatevars = $appraisal->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/printer_appraisal_legacy', $templatevars);
    }

    public function render_feedback(\local_onlineappraisal\output\printer\feedback $feedback) {
        // Call the export_for_template function.
        $templatevars = $feedback->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/printer_feedback', $templatevars);
    }

    public function render_successionplan(\local_onlineappraisal\output\printer\successionplan $successionplan) {
        // Call the export_for_template function.
        $templatevars = $successionplan->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/printer_successionplan', $templatevars);
    }

    public function render_leaderplan(\local_onlineappraisal\output\printer\leaderplan $leaderplan) {
        // Call the export_for_template function.
        $templatevars = $leaderplan->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/printer_leaderplan', $templatevars);
    }

    public function render_leadershipattributes(\local_onlineappraisal\output\printer\leadershipattributes $leadershipattributes) {
        // Call the export_for_template function.
        $templatevars = $leadershipattributes->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/printer_leadershipattributes', $templatevars);
    }
}