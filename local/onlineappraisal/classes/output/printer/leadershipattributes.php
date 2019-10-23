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

require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/local/onlineappraisal/forms/development.php');

use stdClass;

class leadershipattributes extends base {
    /**
     * Get extra context data.
     */
    protected function get_data() {
        // Get relevant role data.
        // Match attributes across roles
        // Template will be table...

        $roles = optional_param_array('role', [], PARAM_RAW);
        if (empty($roles) || count($roles) > 2) {
            // @TODO: Error message?
            return;
        }

        $basedata = (array) json_decode(get_string('form:development:leadershipattributes:role', 'local_onlineappraisal'), true);
        $data = [];

        foreach ($roles as $index => $role) {
            if (array_key_exists($role, $basedata)) {
                $data[$role] = $basedata[$role];
            } else {
                unset($roles[$index]);
            }
        }

        if (empty($data)) {
            // @TODO: Error message?
            return;
        }

        $this->shared_sort($data, $roles);

        $table = new stdClass();
        $table->headings = [];
        $table->rows = [];

        $rolecount = 0;
        foreach ($data as $rolename => $roledata) {
            $table->headings[] = $rolename;

            $rowcount = 0;
            foreach ($roledata as $attrname => $attrinfo) {
                if (!isset($table->rows[$rowcount])) {
                    $table->rows[$rowcount] = new stdClass();
                    $table->rows[$rowcount]->cells = [];
                }

                // Pad out row where necessary.
                $cellcount = count($table->rows[$rowcount]->cells);
                $expectedcells = (2 * $rolecount);
                if ($expectedcells > $cellcount) {
                    for ($i = $cellcount; $i < $expectedcells; $i++) {
                        $table->rows[$rowcount]->cells[$i] = new stdClass();
                        $table->rows[$rowcount]->cells[$i]->width = ($i % 2 === 0) ? 15 : 35;
                        $table->rows[$rowcount]->cells[$i]->content = '&nbsp;';
                    }
                }

                // Update cellcount (in case more added).
                $cellcount = count($table->rows[$rowcount]->cells);

                // Add cells with pre-rendering...
                $table->rows[$rowcount]->cells[$cellcount] = new stdClass();
                $table->rows[$rowcount]->cells[$cellcount]->width = 15;
                $table->rows[$rowcount]->cells[$cellcount]->content = \html_writer::tag('strong', $attrname);

                $table->rows[$rowcount]->cells[$cellcount + 1] = new stdClass();
                $table->rows[$rowcount]->cells[$cellcount + 1]->width = 35;
                $table->rows[$rowcount]->cells[$cellcount + 1]->content = \apform_development::process_leadership_attribute_info($attrinfo);

                $rowcount++;
            }
            $rolecount++;
        }

        // Pad out any rows
        foreach ($table->rows as $row) {
            $cellcount = count($row->cells);
            if ($cellcount < $rolecount * 2) {
                for ($i = $cellcount; $i < (2 * $rolecount); $i++) {
                    $row->cells[$i] = new stdClass();
                    $row->cells[$i]->width = ($i % 2 === 0) ? 15 : 35;
                    $row->cells[$i]->content = '&nbsp;';
                }
            }
        }

        $this->data->table = $table;
    }

    /**
     * Sorts attributes to bring shared ones to top.
     *
     * @param array $data [Reference to] data array.
     * @param array $roles The roles contained in the data array.
     */
    private function shared_sort(&$data, $roles) {
        if (count($roles) < 2) {
            $sharedattributes = [];
        } else {
            $sharedattributes = array_keys(array_intersect_key($data[$roles[0]], $data[$roles[1]]));
        }

        foreach ($data as $role => $attributes) {
            $tempshared = [];
            $tempind = [];
            foreach($attributes as $attribute => $info) {
                if (in_array($attribute, $sharedattributes)) {
                    $tempshared[$attribute] = $info;
                } else {
                    $tempind[$attribute] = $info;
                }
            }
            ksort($tempshared);
            ksort($tempind);
            $data[$role] = array_merge($tempshared, $tempind);
        }
    }
}
