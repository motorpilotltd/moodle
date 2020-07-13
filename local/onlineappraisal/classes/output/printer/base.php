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

use local_learningrecordstore\lrsentry;
use renderable;
use templatable;
use renderer_base;
use stdClass;

abstract class base implements renderable, templatable {
    /**
     * Printer class.
     * @var \local_onlineappraisal\printer $printer
     */
    protected $printer;
    /**
     * Appraisal record.
     * @var stdClass $appraisal
     */
    protected $appraisal;
    /**
     * Context for template.
     * @var stdClass $data
     */
    protected $data;

    /**
     * Constructor.
     *
     * @param \local_onlineappraisal\printer $printer
     */
    public function __construct(\local_onlineappraisal\printer $printer) {
        $this->printer = $printer;
        $this->appraisal = $printer->appraisal->appraisal;

        $this->data = new stdClass();
    }

    /**
     * Provides data for pre-processing and export.
     */
    abstract protected function get_data();

    /**
     * Pre-process and export this data so it can be used as the context for a mustache template.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        // Get deep clone of generic appraisal data.
        $this->data->appraisal = unserialize(serialize($this->appraisal));

        // Format dates.
        $this->data->appraisal->due_date = userdate($this->data->appraisal->due_date, get_string('strftimedate'), new \DateTimeZone('UTC')); // Always UTC (from datepicker).
        $this->data->appraisal->held_date = empty($this->data->appraisal->held_date) ? get_string('pdf:notset', 'local_onlineappraisal') : userdate($this->data->appraisal->held_date, get_string('strftimedate'), new \DateTimeZone('UTC')); // Always UTC (from datepicker).
        $this->data->appraisal->completed_date = empty($this->data->appraisal->completed_date) ? get_string('pdf:notcomplete', 'local_onlineappraisal') : userdate($this->data->appraisal->completed_date, get_string('strftimedate'));

        // Add data specific to renderer.
        $this->get_data();

        return $this->data;
    }

    /**
     * Inject learning history into data object.
     *
     * @global \moodle_database $DB
     * @return void
     */
    protected function get_learning_history() {
        global $OUTPUT;


        $staffid = $this->appraisal->appraisee->idnumber;

        $mylearninginstalled = get_config('block_arup_mylearning', 'version');

        if (empty($staffid) || !$mylearninginstalled) {
            return;
        }

        $records = lrsentry::fetchbystaffid($staffid, time() - 3 * 365 * DAYSECS);

        $this->data->learninghistory = [];
        foreach ($records as $record) {
            $this->data->learninghistory[] = $record->export_for_template($OUTPUT);
        }

        $this->data->haslearninghistory = (bool) count($this->data->learninghistory);
    }
}
