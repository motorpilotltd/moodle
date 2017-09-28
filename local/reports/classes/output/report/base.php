<?php
// This file is part of the Arup online reports system
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
 * @package     local_reports
 * @copyright   2016 Motorpilot Ltd, Sonsbeekmedia
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_reports\output\report;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;

abstract class base implements renderable, templatable {
    protected $report;

    public function __construct(\local_reports\report $report) {
        global $USER;
        $this->report = $report;
    }

    public function table_to_excel($filename, $table) {
        global $DB, $CFG;

        require_once($CFG->dirroot.'/lib/excellib.class.php');

        if (!empty($table->head)) {
            $countcols = count($table->head);
            $keys = array_keys($table->head);
            $lastkey = end($keys);
            foreach ($table->head as $key => $heading) {
                    $matrix[0][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($heading))));
            }
        }

        if (!empty($table->data)) {
            foreach ($table->data as $rkey => $row) {
                foreach ($row as $key => $item) {
                    $matrix[$rkey + 1][$key] = str_replace("\n", ' ', htmlspecialchars_decode(strip_tags(nl2br($item))));
                }
            }
        }

        $downloadfilename = clean_filename($filename);
        // Creating a workbook.
        $workbook = new MoodleExcelWorkbook("-");
        // Sending HTTP headers.
        $workbook->send($downloadfilename);
        // Adding the worksheet.
        $myxls =& $workbook->add_worksheet($filename);

        foreach ($matrix as $ri => $col) {
            foreach ($col as $ci => $cv) {
                $myxls->write_string($ri, $ci, $cv);
            }
        }

        $workbook->close();
        exit;
    }
}
