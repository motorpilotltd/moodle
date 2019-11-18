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

class rb_findcourses_embedded extends rb_base_embedded {
    public $defaultsortcolumn, $defaultsortorder;

    public function __construct($data) {
        $this->url = '/course/find.php';
        $this->source = 'courses';
        $this->defaultsortcolumn = 'course_courselinkicon';
        $this->shortname = 'findcourses';
        $this->fullname = get_string('findcourses', 'local_reportbuilder');
        $this->columns = array(
            array(
                'type' => 'course',
                'value' => 'courselinkicon',
                'heading' => get_string('coursename', 'local_reportbuilder'),
            ),
            array(
                'type' => 'course_category',
                'value' => 'namelink',
                'heading' => get_string('category', 'local_reportbuilder'),
            ),
            array(
                'type' => 'course',
                'value' => 'startdate',
                'heading' => get_string('report:startdate', 'local_reportbuilder'),
            ),
            array(
                'type' => 'course',
                'value' => 'mods',
                'heading' => get_string('content', 'local_reportbuilder'),
            ),
        );

        $this->filters = array(
            array(
                'type' => 'course',
                'value' => 'name_and_summary',
                'advanced' => 0,
            ),
            array(
                'type' => 'course',
                'value' => 'mods',
                'advanced' => 0,
            ),
            array(
                'type' => 'course_category',
                'value' => 'path',
                'advanced' => 1,
            ),
            array(
                'type' => 'course',
                'value' => 'startdate',
                'advanced' => 1,
                'region' => rb_filter_type::RB_FILTER_REGION_SIDEBAR,
            ),
        );

        $this->toolbarsearchcolumns = array(
            array(
                'type' => 'course',
                'value' => 'fullname',
            ),
            array(
                'type' => 'course',
                'value' => 'summary',
            ),
        );

        $this->contentmode = REPORT_BUILDER_CONTENT_MODE_NONE;

        parent::__construct();
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
}
