<?php
// This file is part of the Arup cost centre local plugin
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
 * @package     local_costcentre
 * @copyright   2016 Motorpilot Ltd
 * @author      Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('local_admin_userreport');

// Required CSS and JS.
$PAGE->requires->css(new moodle_url('/local/admin/css/select2.min.css'));
$PAGE->requires->css(new moodle_url('/local/admin/css/select2-bootstrap.min.css'));
$PAGE->requires->js_call_amd('local_admin/enhance', 'initialise');

$title = get_string('userreport', 'local_admin');
$PAGE->set_title(get_site()->shortname . ': ' . $title);

// Render page.
echo $OUTPUT->header();
echo $OUTPUT->heading($title);

// Content.
$form = new \local_admin\form\user_report_filter();

$formdata = $form->get_data();

$table = new \local_admin\user_report_table_sql('user_report');

$fields = 'log.*, hub.FULL_NAME as name';
$staffid = $DB->sql_cast_char2real('log.staffid');
$from = "{local_admin_user_update_log} log LEFT JOIN SQLHUB.ARUP_ALL_STAFF_V hub ON {$staffid} = hub.EMPLOYEE_NUMBER";
$where = '1=1';
$params = [];

if ($formdata) {
    if (!empty($formdata->actions)) {
        list($actionsql, $actionparams) = $DB->get_in_or_equal($formdata->actions, SQL_PARAMS_NAMED, 'action');
        $where .= " AND log.action {$actionsql}";
        $params = $params + $actionparams;
    }
    if (!empty($formdata->statuses)) {
        list($statussql, $statusparams) = $DB->get_in_or_equal($formdata->statuses, SQL_PARAMS_NAMED, 'status');
        $where .= " AND log.status {$statussql}";
        $params = $params + $statusparams;
    }
    if (!empty($formdata->from)) {
        $where .= " AND timecreated >= {$formdata->from}";
    }
    if (!empty($formdata->to)) {
        $to = $formdata->to + (60 * 60 * 24) - 1; // End of day!
        $where .= " AND timecreated <= {$formdata->to}";
    }
}

$table->set_sql($fields, $from, $where, $params);

$columns = ['staffid', 'userid', 'name', 'action', 'status', 'extrainfo', 'timecreated'];
$headers = [];
foreach ($columns as $column) {
    $headers[] = get_string("userreport:{$column}", 'local_admin');
}

$table->define_columns($columns);
$table->column_style_all('text-align', 'left');
$table->define_headers($headers);
$table->define_baseurl($PAGE->url);

$table->sortable(true, 'timecreated', SORT_DESC);
$table->is_collapsible = false;

$table->useridfield = 'userid';

$table->no_sorting('extrainfo');

$form->display();

$table->out(50, false);

echo $OUTPUT->footer();
