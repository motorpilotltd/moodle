<?php
// This file is part of the Arup Reports system
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
 * @package     local_reports
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_reports\output\report;

defined('MOODLE_INTERNAL') || die();

class renderer extends \local_reports\output\renderer {

    public function render_learninghistory(\local_reports\output\report\learninghistory $learninghistory) {
        // Call the export_for_template function from class learninghistory.
        $templatevars = $learninghistory->export_for_template($this);

        return $this->render_from_template('local_reports/learninghistory', $templatevars);
    }

    public function render_elearningstatus(\local_reports\output\report\elearningstatus $elearningstatus) {
        // Call the export_for_template function from class elearningstatus.
        $templatevars = $elearningstatus->export_for_template($this);

        return $this->render_from_template('local_reports/elearningstatus', $templatevars);
    }

    public function render_daterangelearning(\local_reports\output\report\daterangelearning $daterangelearning) {
        // Call the export_for_template function from class daterangelearning.
        $templatevars = $daterangelearning->export_for_template($this);

        return $this->render_from_template('local_reports/daterangelearning', $templatevars);
    }

}