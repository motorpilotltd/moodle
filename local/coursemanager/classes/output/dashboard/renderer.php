<?php
// This file is part of the Arup Course Management system
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
 *
 * @package     local_coursemanager
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursemanager\output\dashboard;

defined('MOODLE_INTERNAL') || die();

class renderer extends \local_coursemanager\output\renderer {


    public function render_overview(\local_coursemanager\output\dashboard\overview $overview) {
        // Call the export_for_template function from class overview.
        $templatevars = $overview->export_for_template($this);

        return $this->render_from_template('local_coursemanager/overview', $templatevars);
    }

    public function render_classoverview(\local_coursemanager\output\dashboard\classoverview $classoverview) {
        // Call the export_for_template function from class overview.
        $templatevars = $classoverview->export_for_template($this);

        return $this->render_from_template('local_coursemanager/classoverview', $templatevars);
    }

    public function render_coursetable(\local_coursemanager\output\dashboard\coursetable $coursetable) {
        // Call the export_for_template function from class actions.
        $templatevars = $coursetable->export_for_template($this);

        return $this->render_from_template('local_coursemanager/coursetable', $templatevars);
    }

    public function render_classtable(\local_coursemanager\output\dashboard\classtable $classtable) {
        // Call the export_for_template function from class actions.
        $templatevars = $classtable->export_for_template($this);

        return $this->render_from_template('local_coursemanager/classtable', $templatevars);
    }

    public function render_actions(\local_coursemanager\output\dashboard\actions $actions) {
        // Call the export_for_template function from class actions.
        $templatevars = $actions->export_for_template($this);

        return $this->render_from_template('local_coursemanager/actions', $templatevars);
    }

}