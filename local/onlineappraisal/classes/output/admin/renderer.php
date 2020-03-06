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

namespace local_onlineappraisal\output\admin;

defined('MOODLE_INTERNAL') || die();

class renderer extends \local_onlineappraisal\output\renderer {

    public function render_allstaff(\local_onlineappraisal\output\admin\allstaff $allstaff) {
        // Call the export_for_template function.
        $templatevars = $allstaff->export_for_template($this);

        $template = "local_onlineappraisal/admin_allstaff_{$templatevars->template}";

        return $this->render_from_template($template, $templatevars);
    }

    public function render_initialise(\local_onlineappraisal\output\admin\initialise $initialise) {
        // Call the export_for_template function.
        $templatevars = $initialise->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/admin_initialise', $templatevars);
    }

    public function render_inprogress(\local_onlineappraisal\output\admin\inprogress $inprogress) {
        // Call the export_for_template function.
        $templatevars = $inprogress->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/admin_inprogress', $templatevars);
    }

    public function render_complete(\local_onlineappraisal\output\admin\complete $complete) {
        // Call the export_for_template function.
        $templatevars = $complete->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/admin_complete', $templatevars);
    }

    public function render_archived(\local_onlineappraisal\output\admin\archived $archived) {
        // Call the export_for_template function.
        $templatevars = $archived->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/admin_archived', $templatevars);
    }

    public function render_itadmin(\local_onlineappraisal\output\admin\itadmin $itadmin) {
        // Call the export_for_template function.
        $templatevars = $itadmin->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/it_admin', $templatevars);
    }

    public function render_deleted(\local_onlineappraisal\output\admin\deleted $deleted) {
        // Call the export_for_template function.
        $templatevars = $deleted->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/admin_deleted', $templatevars);
    }

    public function render_cycle(\local_onlineappraisal\output\admin\cycle $cycle) {
        // Call the export_for_template function.
        $templatevars = $cycle->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/admin_cycle', $templatevars);
    }
}