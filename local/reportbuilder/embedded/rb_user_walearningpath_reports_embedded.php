<?php
/*
 * This file is part of T0tara LMS
 *
 * Copyright (C) 2010 onwards T0tara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Simon Coggins <simon.coggins@t0taralms.com>
 * @package t0tara
 * @subpackage reportbuilder
 */

class rb_user_walearningpath_reports_embedded extends rb_base_embedded {
    public $defaultsortcolumn, $defaultsortorder;

    public function __construct($data) {
        $this->url = '/my/index.php';
        $this->source = 'walearningpath';
        $this->shortname = 'user_walearningpath_reports';
        $this->fullname = get_string('sourcetitle', 'rbsource_walearningpath');
        $this->columns = [
            [
                'type' => 'learningpath',
                'value' => 'title',
                'heading' => get_string('title', 'rbsource_walearningpath'),
            ],
            [
                'type' => 'learningpath',
                'value' => 'summary',
                'heading' => get_string('summary', 'rbsource_walearningpath'),
            ],
        ];

        // only show learning path of the current user
        if (!empty($data['walearningpath_userid'])) {
            $this->embeddedparams = [
                'walearningpath_userid' => $data['walearningpath_userid']
            ];
        }
    }

        /**
     * Check if the user is capable of accessing this report.
     * We use $reportfor instead of $USER->id and $report->get_param_value() instead of getting report params
     * some other way so that the embedded report will be compatible with the scheduler (in the future).
     *
     * @param int $reportfor userid of the user that this report is being generated for
     * @param reportbuilder $report the report object - can use get_param_value to get params
     * @return boolean true if the user can access this report
     */
    public function is_capable($reportfor, $report) {
        return true;
    }

    /**
     * Returns true if require_login should be executed when the report is access through a page other than
     * report.php or an embedded report's webpage, e.g. through ajax calls.
     *
     * @return boolean True if require_login should be executed
     */
    public function needs_require_login() {
        global $CFG;
        return $CFG->forcelogin;
    }
}