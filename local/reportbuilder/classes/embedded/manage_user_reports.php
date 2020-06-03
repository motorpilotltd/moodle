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
 * @author Simon Coggins <simon.coggins@t0taralearning.com>
 * @package local_reportbuilder
 */

namespace local_reportbuilder\embedded;

class manage_user_reports extends \rb_base_embedded {

    public $url, $source, $fullname, $filters, $columns;
    public $contentmode, $contentsettings, $embeddedparams;
    public $hidden, $accessmode, $accesssettings, $shortname;
    public $defaultsortcolumn, $defaultsortorder;

    public function __construct() {
        $this->url = '/local/reportbuilder/index.php';
        $this->source = 'reports';
        $this->defaultsortcolumn = 'report_namelinkeditview';
        $this->shortname = 'manage_user_reports';
        $this->fullname = get_string('manageuserreports', 'local_reportbuilder');
        $this->columns = [
            [
                'type' => 'report',
                'value' => 'namelinkeditview',
                'heading' => get_string('reportname', 'local_reportbuilder'),
            ],
            [
                'type' => 'report',
                'value' => 'source',
                'heading' => get_string('reportsource', 'local_reportbuilder'),
            ],
            [
                'type' => 'report',
                'value' => 'actions',
                'heading' => get_string('reportactions', 'local_reportbuilder'),
            ],
        ];

        $this->filters = [
            [
                'type' => 'report',
                'value' => 'name',
            ],
        ];

        // only show user reports
        $this->embeddedparams = [
            'embedded' => '0',
        ];

        parent::__construct();
    }

    /**
     * Hide this source if feature disabled or hidden.
     * @return bool
     */
    public function is_ignored() {
        return false;
    }

    /**
     * Check if the user is capable of accessing this report.
     * We use $reportfor instead of $USER->id and $report->get_param_value() instead of getting params
     * some other way so that the embedded report will be compatible with the scheduler (in the future).
     *
     * @param int $reportfor userid of the user that this report is being generated for
     * @param reportbuilder $report the report object - can use get_param_value to get params
     * @return boolean true if the user can access this report
     */
    public function is_capable($reportfor, $report) {
        $syscontext = \context_system::instance();
        return has_capability('local/reportbuilder:managereports', $syscontext, $reportfor);
    }
}
