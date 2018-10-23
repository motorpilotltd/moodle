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
$export = optional_param('export', '', PARAM_TEXT);

$exporturl = new moodle_url('/cohort/assign.php', array('id' => $id, 'export' => 'csv'));

/**
 * Show custom members list if this is dynamic cohort, else show standard assign view
 */
if($export == 'csv'){
    send_headers('text/csv; charset=utf-8;', false);
    @header('Content-Disposition: attachment; filename="cohort_members.csv"');
    $csv =  \local_dynamic_cohorts\dynamic_cohorts::export_members($id, $export);
    echo $csv;
    exit;
}


$cohort = $DB->get_record('cohort', array('id'=>$id), '*', MUST_EXIST);
$context = context::instance_by_id($cohort->contextid, MUST_EXIST);

if(($isdynamic = \local_dynamic_cohorts\dynamic_cohorts::check_if_dynamic($id)) || !has_capability('local/dynamic_cohorts:edit', $context)){

    require_once($CFG->dirroot.'/cohort/externallib.php');
    require_once($CFG->dirroot.'/local/dynamic_cohorts/form/members_filter.php');
    require_login();

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

    echo html_writer::tag('button', get_string('export', 'local_dynamic_cohorts'), ['class' => 'btn-primary', 'onclick' => 'window.open("'.$exporturl->out(false).'","export");']);
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
    if($isdynamic){
        echo html_writer::span(get_string('dynamiccohortsmembersinfo', 'local_dynamic_cohorts'));
    }
    echo $OUTPUT->footer();
    exit;
}else{

    require_once($CFG->dirroot.'/cohort/locallib.php');

    require_login();

    require_capability('moodle/cohort:assign', $context);

    $PAGE->set_context($context);
    $PAGE->set_url('/cohort/assign.php', array('id'=>$id));
    $PAGE->set_pagelayout('admin');

    if ($returnurl) {
        $returnurl = new moodle_url($returnurl);
    } else {
        $returnurl = new moodle_url('/cohort/index.php', array('contextid' => $cohort->contextid));
    }

    if (!empty($cohort->component)) {
        // We can not manually edit cohorts that were created by external systems, sorry.
        redirect($returnurl);
    }

    if (optional_param('cancel', false, PARAM_BOOL)) {
        redirect($returnurl);
    }

    if ($context->contextlevel == CONTEXT_COURSECAT) {
        $category = $DB->get_record('course_categories', array('id'=>$context->instanceid), '*', MUST_EXIST);
        navigation_node::override_active_url(new moodle_url('/cohort/index.php', array('contextid'=>$cohort->contextid)));
    } else {
        navigation_node::override_active_url(new moodle_url('/cohort/index.php', array()));
    }

    $PAGE->navbar->add(get_string('assign', 'cohort'));

    $PAGE->set_title(get_string('assigncohorts', 'cohort'));
    $PAGE->set_heading($COURSE->fullname);

    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('assignto', 'cohort', format_string($cohort->name)));
    echo html_writer::tag('button', get_string('export', 'local_dynamic_cohorts'), ['class' => 'btn-primary', 'onclick' => 'window.open("'.$exporturl->out(false).'","export");']);

    echo $OUTPUT->notification(get_string('removeuserwarning', 'core_cohort'));

// Get the user_selector we will need.
    $potentialuserselector = new cohort_candidate_selector('addselect', array('cohortid'=>$cohort->id, 'accesscontext'=>$context));
    $existinguserselector = new cohort_existing_selector('removeselect', array('cohortid'=>$cohort->id, 'accesscontext'=>$context));

// Process incoming user assignments to the cohort

    if (optional_param('add', false, PARAM_BOOL) && confirm_sesskey()) {
        $userstoassign = $potentialuserselector->get_selected_users();
        if (!empty($userstoassign)) {

            foreach ($userstoassign as $adduser) {
                cohort_add_member($cohort->id, $adduser->id);
            }

            $potentialuserselector->invalidate_selected_users();
            $existinguserselector->invalidate_selected_users();
        }
    }

// Process removing user assignments to the cohort
    if (optional_param('remove', false, PARAM_BOOL) && confirm_sesskey()) {
        $userstoremove = $existinguserselector->get_selected_users();
        if (!empty($userstoremove)) {
            foreach ($userstoremove as $removeuser) {
                cohort_remove_member($cohort->id, $removeuser->id);
            }
            $potentialuserselector->invalidate_selected_users();
            $existinguserselector->invalidate_selected_users();
        }
    }

// Print the form.
    ?>
    <form id="assignform" method="post" action="<?php echo $PAGE->url ?>"><div>
            <input type="hidden" name="sesskey" value="<?php echo sesskey() ?>" />
            <input type="hidden" name="returnurl" value="<?php echo $returnurl->out_as_local_url() ?>" />

            <table summary="" class="generaltable generalbox boxaligncenter" cellspacing="0">
                <tr>
                    <td id="existingcell">
                        <p><label for="removeselect"><?php print_string('currentusers', 'cohort'); ?></label></p>
                        <?php $existinguserselector->display() ?>
                    </td>
                    <td id="buttonscell">
                        <div id="addcontrols">
                            <input name="add" id="add" type="submit" value="<?php echo $OUTPUT->larrow().'&nbsp;'.s(get_string('add')); ?>" title="<?php p(get_string('add')); ?>" /><br />
                        </div>

                        <div id="removecontrols">
                            <input name="remove" id="remove" type="submit" value="<?php echo s(get_string('remove')).'&nbsp;'.$OUTPUT->rarrow(); ?>" title="<?php p(get_string('remove')); ?>" />
                        </div>
                    </td>
                    <td id="potentialcell">
                        <p><label for="addselect"><?php print_string('potusers', 'cohort'); ?></label></p>
                        <?php $potentialuserselector->display() ?>
                    </td>
                </tr>
                <tr><td colspan="3" id='backcell'>
                        <input type="submit" name="cancel" value="<?php p(get_string('backtocohorts', 'cohort')); ?>" />
                    </td></tr>
            </table>
        </div></form>

    <?php

    echo $OUTPUT->footer();
    exit;
}