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

namespace local_admin;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/tablelib.php');

class user_report_table_sql extends \table_sql {
    public function other_cols($column, $row) {
        switch ($column) {
            case 'extrainfo':
                $return = '';
                $extrainfo = json_decode($row->extrainfo);
                if (is_string($extrainfo)) {
                    return $extrainfo;
                }
                if (!empty($extrainfo->exception)) {
                    $return .= "{$extrainfo->exception}";
                }
                if (!empty($extrainfo->debuginfo)) {
                    $return .= (!empty($return) ? '<br><br>' : '');
                    $return .= "<strong>Debug info</strong><br>{$extrainfo->debuginfo}";
                }
                return $return;
            case 'timecreated':
                return gmdate('H:i:s d M Y', $row->timecreated);
            default:
                return null;
        }
    }
}