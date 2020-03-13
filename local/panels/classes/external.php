<?php
// This file is part of Moodle - http://moodle.org/
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
 * External API.
 *
 * @package    core_competency
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_panels;
defined('MOODLE_INTERNAL') || die();

require_once("$CFG->libdir/externallib.php");
require_once("$CFG->libdir/grade/grade_scale.php");

use context;
use context_system;
use context_course;
use context_helper;
use context_user;
use coding_exception;
use external_api;
use external_function_parameters;
use external_value;
use external_format_value;
use external_single_structure;
use external_multiple_structure;
use invalid_parameter_exception;
use local_panels\panel;
use required_capability_exception;
use grade_scale;

use core_competency\external\competency_exporter;
use core_competency\external\competency_framework_exporter;
use core_competency\external\course_competency_exporter;
use core_competency\external\course_competency_settings_exporter;
use core_competency\external\evidence_exporter;
use core_competency\external\performance_helper;
use core_competency\external\plan_exporter;
use core_competency\external\template_exporter;
use core_competency\external\user_competency_exporter;
use core_competency\external\user_competency_plan_exporter;
use core_competency\external\user_evidence_competency_exporter;
use core_competency\external\user_evidence_exporter;

/**
 * External API class.
 *
 * @package    core_competency
 * @copyright  2016 Frédéric Massart - FMCorz.net
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class external extends external_api {
    /**
     * Returns description of reorder_panels() parameters.
     *
     * @return \external_function_parameters
     */
    public static function reorder_panels_parameters() {
        $panelsetid = new external_value(
            PARAM_INT,
            'The course id',
            VALUE_REQUIRED
        );
        $competencyidfrom = new external_value(
            PARAM_INT,
            'The competency id we are moving',
            VALUE_REQUIRED
        );
        $competencyidto = new external_value(
            PARAM_INT,
            'The competency id we are moving to',
            VALUE_REQUIRED
        );
        $params = array(
            'panelsetid' => $panelsetid,
            'competencyidfrom' => $competencyidfrom,
            'competencyidto' => $competencyidto,
        );
        return new external_function_parameters($params);
    }

    /**
     * Change the order of course competencies.
     *
     * @param int $panelsetid The course id
     * @param int $competencyidfrom The competency to move.
     * @param int $competencyidto The competency to move to.
     * @return bool
     */
    public static function reorder_panels($panelsetid, $competencyidfrom, $competencyidto) {
        $params = self::validate_parameters(self::reorder_panels_parameters(), array(
            'panelsetid' => $panelsetid,
            'competencyidfrom' => $competencyidfrom,
            'competencyidto' => $competencyidto,
        ));
        self::validate_context(context_course::instance($params['panelsetid']));
        return api::reorder_panels($params['panelsetid'], $params['competencyidfrom'], $params['competencyidto']);
    }

    /**
     * Returns description of reorder_panels() result value.
     *
     * @return \external_description
     */
    public static function reorder_panels_returns() {
        return new external_value(PARAM_BOOL, 'True if successful.');
    }

}
