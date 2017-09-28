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

class header extends base {
    /**
     * Get extra context data.
     */
    protected function get_data() {
        global $CFG, $USER;
        $statusstring = 'status:' . $this->appraisal->statusid;
        $this->data->currentstatus = get_string($statusstring, 'local_onlineappraisal');
        if ($this->appraisal->archived) {
            $this->data->archived = get_string('archived', 'local_onlineappraisal');
        }
        $this->data->logopath = $CFG->dirroot.'/local/onlineappraisal/pix/pdf_header_logo.png';

        $a = new \stdClass();
        $a->who = fullname($USER);
        $a->when = userdate(time(), get_string('strftimedatetime'));
        $this->data->warning = get_string('pdf:header:warning', 'local_onlineappraisal', $a);
    }
}
