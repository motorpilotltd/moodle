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
 * @author Brendan Cox <brendan.cox@t0taralms.com>
 * @package local_reportbuilder
 */

define('AJAX_SCRIPT', true);

require_once(__DIR__ . '/../../../../../config.php');
require_once($CFG->dirroot.'/local/reportbuilder/filters/lib.php');
require_once($CFG->dirroot.'/local/reportbuilder/filters/course_multi.php');

$ids = required_param('ids', PARAM_SEQUENCE);
// array_filter() is necessary here has explode() may return array elements with empty strings.
$ids = array_filter(explode(',', $ids));
$filtername = required_param('filtername', PARAM_ALPHANUMEXT);

// Permissions checks.
require_login();
require_sesskey();

// Send headers.
send_headers('text/html; charset=utf-8', false);

$PAGE->set_context(context_system::instance());

echo $OUTPUT->container_start('rb-filter-content-list list-' . $filtername);
if (!empty($ids)) {
    list($insql, $inparams) = $DB->get_in_or_equal($ids);
    $courses = $DB->get_records_select('course', "id ".$insql, $inparams);
    foreach ($courses as $course) {
        echo rb_filter_course_multi::display_selected_course_item($course, $filtername);
    }
}
echo $OUTPUT->container_end();
