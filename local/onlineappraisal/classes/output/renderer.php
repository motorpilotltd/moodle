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

namespace local_onlineappraisal\output;

defined('MOODLE_INTERNAL') || die();

use plugin_renderer_base;

// Base renderer class.
class renderer extends plugin_renderer_base {

    public function render_alert(\local_onlineappraisal\output\alert $alert) {
        // Call the export_for_template function.
        $templatevars = $alert->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/alert', $templatevars);
    }

    public function render_caption(\local_onlineappraisal\output\caption $caption) {
        // Call the export_for_template function.
        $templatevars = $caption->export_for_template($this);

        return $this->render_from_template('local_onlineappraisal/caption', $templatevars);
    }

}