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

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once("{$CFG->dirroot}/local/lunchandlearn/lib.php");

admin_externalpage_setup('lunchandlearnlist');

$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('/local/lunchandlearn/js/jquery.tablesorter.min.js'));
$PAGE->requires->js_init_code('$("table.sessionlist").tablesorter({widgets: ["zebra"], widgetOptions : { zebra : [ "normal-row", "alt-row" ] }});');

$renderer = $PAGE->get_renderer('local_lunchandlearn');
$PAGE->navbar->ignore_active();
$PAGE->navbar->add(get_string('calendar', 'calendar'), new moodle_url('/calendar/', array('view' => 'month')));
$PAGE->navbar->add(get_string('pluginnameplural', 'local_lunchandlearn'));
$PAGE->navbar->add(get_string('lunchandlearnviewsessions', 'local_lunchandlearn'));
lunchandlearn_add_page_navigation($PAGE, $PAGE->url);

$sessions = lunchandlearn_manager::get_marking_list(optional_param('status', 'closed', PARAM_ALPHA), '');

print $OUTPUT->header();
print $OUTPUT->heading(get_string('lunchandlearnlist', 'local_lunchandlearn'));
print $renderer->list_sessions($sessions);
print $OUTPUT->footer();