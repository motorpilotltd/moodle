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

namespace rbsource_walearningpath\embedded;

class subs_walearningpath extends \rb_base_embedded {
    public $defaultsortcolumn, $defaultsortorder;

    public function __construct() {
        $this->url = '/local/wa_learning_path/index.php';
        $this->source = 'walearningpath';
        $this->shortname = 'rbsource_walearningpath\embedded\subs_walearningpath';
        $this->fullname = get_string('walearningpath_subscriptions', 'rbsource_walearningpath');
        $this->columns = [
            [
                'type' => 'user',
                'value' => 'namelinkicon',
                'heading' => get_string('usernamelinkicon', 'local_reportbuilder'),
            ],
            [
                'type' => 'user',
                'value' => 'username',
                'heading' => get_string('username', 'local_reportbuilder'),
            ],
        ];

        $this->filters = array(
            array(
                'type' => 'user',
                'value' => 'username',
            ),
        );

        // Only show subscriptions for current path.
        $walearningpathid = optional_param('id', '0', PARAM_INT);

        $this->embeddedparams = [
            'walearningpath_id' => $walearningpathid
        ];

        $this->url = '/local/wa_learning_path/?c=admin&a=edit_subscriptions&id=' . $walearningpathid;
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
        global $CFG;
        require_once($CFG->dirroot . '/local/wa_learning_path/lib/lib.php');
        return \wa_learning_path\lib\is_contenteditor($reportfor);
    }
}