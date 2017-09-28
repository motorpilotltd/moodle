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


require_once(dirname(__FILE__) . '/../../../../config.php');

$id = required_param('id', PARAM_INT);
$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);

/**
 * Show custom members list if this is dynamic cohort, else show standard assign view
 */
if(\local_dynamic_cohorts\dynamic_cohorts::check_if_dynamic($id)){

    require_once($CFG->dirroot.'/cohort/externallib.php');
    require_once($CFG->dirroot.'/local/dynamic_cohorts/form/members_filter.php');
    require_login();

    $cohort = $DB->get_record('cohort', array('id'=>$id), '*', MUST_EXIST);
    $context = context::instance_by_id($cohort->contextid, MUST_EXIST);
    $currenturl = qualified_me();

    require_capability('moodle/cohort:assign', $context);

    $PAGE->set_context($context);
    $PAGE->set_url('/cohort/assign.php', array('id'=>$id));
    $PAGE->set_pagelayout('admin');
    
    if ($returnurl) {
        $returnurl = new moodle_url($returnurl);
    } else {
        $returnurl = new moodle_url('/cohort/index.php', array('contextid' => $cohort->contextid));
    }

    navigation_node::override_active_url(new moodle_url('/cohort/index.php', array()));
    
    $PAGE->navbar->add(get_string('members', 'local_dynamic_cohorts'));

    $PAGE->set_title(get_string('members', 'local_dynamic_cohorts'));
    $PAGE->set_heading($COURSE->fullname);

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('assignto', 'cohort', format_string($cohort->name)));

    $filterform = new \local_dynamic_cohorts\form\dynamic_cohorts_members_filter_form($currenturl);
    $filters = [];
    if ($formdata = $filterform->get_data()) {
        $filters['fullname'] = $formdata->fullname;
    }

    $filterform->display();

    $members = \local_dynamic_cohorts\dynamic_cohorts::get_members($id, $filters, 200);
    $data = [];
    foreach($members as $member){
        $line = [];
        $line[] = $member->fullname;
        $line[] = $member->email;
        $data[] = $row = new html_table_row($line);
    }

    $table = new html_table();
    $table->head  = array(get_string('name'), get_string('email'));
    $table->colclasses = array('leftalign name', 'leftalign email');

    $table->id = 'cohorts';
    $table->attributes['class'] = 'admintable generaltable';
    $table->data  = $data;
    echo html_writer::table($table);
    if(count($members) == 200){
        echo html_writer::span(get_string('memberslimited', 'local_dynamic_cohorts'));
        echo html_writer::empty_tag('br');
    }
    echo html_writer::span(get_string('dynamiccohortsmembersinfo', 'local_dynamic_cohorts'));
    echo $OUTPUT->footer();
    exit;
}