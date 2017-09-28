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

namespace local_onlineappraisal\output\bulkupload;

defined('MOODLE_INTERNAL') || die();

class renderer extends \local_onlineappraisal\output\renderer {

    public function render_stepone(\local_onlineappraisal\output\bulkupload\stepone $stepone) {
        // Call the export_for_template function.
        $templatevars = $stepone->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/bulkupload_stepone', $templatevars);
    }

    public function render_steptwo(\local_onlineappraisal\output\bulkupload\steptwo $steptwo) {
        // Call the export_for_template function.
        $templatevars = $steptwo->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/bulkupload_steptwo', $templatevars);
    }

    public function render_stepthree(\local_onlineappraisal\output\bulkupload\stepthree $stepthree) {
        // Call the export_for_template function.
        $templatevars = $stepthree->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/bulkupload_stepthree', $templatevars);
    }

    public function render_stepfour(\local_onlineappraisal\output\bulkupload\stepfour $stepfour) {
        // Call the export_for_template function.
        $templatevars = $stepfour->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/bulkupload_stepfour', $templatevars);
    }
}