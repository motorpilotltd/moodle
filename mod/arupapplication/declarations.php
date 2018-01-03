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

/**
 * prints the form to edit the declarations such moving, deleting and so on
 *
 * @author Jackson D'souza
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package arupapplication
 */

require_once("../../config.php");
require_once("lib.php");
require_once('locallib.php');
require_once('declarations_form.php');

arupapplication_init_arupapplication_session();

$id = required_param('id', PARAM_INT);
$dowhat = optional_param('dowhat', 'add', PARAM_RAW);
$declarationid = optional_param('declarationid', 0, PARAM_INT);
$courseid = optional_param('courseid', false, PARAM_INT);

if (($formdata = data_submitted()) AND !confirm_sesskey()) {
    print_error('invalidsesskey');
}

if (isset($formdata->submitbutton)){
    switch ($dowhat) {
        case 'edit':
            $record = new stdClass();
            $record->id = $formdata->declarationid;
            $record->declaration = $formdata->declaration;
            $record->ismandatory = $formdata->ismandatory;
            $record->timemodified = time();
            $DB->update_record('arupdeclarations', $record);
            $success = true;
            $message = get_string('updatesuccess', 'arupapplication');
            break;
        default:
            $formdata->timecreated = time();
            $DB->insert_record('arupdeclarations', $formdata);
            $success = true;
            $message = get_string('addedsuccess', 'arupapplication');
            break;
    }
} else {
    switch ($dowhat) {
        case 'edit':
            $formdata = $DB->get_record('arupdeclarations', array('id'=>$declarationid));
            break;
    }
}

$moveupitem = optional_param('moveupitem', false, PARAM_INT);
$movedownitem = optional_param('movedownitem', false, PARAM_INT);
$moveitem = optional_param('moveitem', false, PARAM_INT);
$movehere = optional_param('movehere', false, PARAM_INT);

$current_tab = 'declarations';
$do_show = 'declarations';

$url = new moodle_url('/mod/arupapplication/declarations.php', array('id'=>$id));

$PAGE->set_url($url);
if ($courseid !== false) {
    $url->param('courseid', $courseid);
}

if (! $cm = get_coursemodule_from_id('arupapplication', $id)) {
    print_error('invalidcoursemodule');
}

if (! $course = $DB->get_record("course", array("id"=>$cm->course))) {
    print_error('coursemisconf');
}

if (! $arupapplication = $DB->get_record("arupapplication", array("id"=>$cm->instance))) {
    print_error('invalidcoursemodule');
}

if (!$context = context_module::instance($cm->id)) {
    print_error('badcontext');
}

require_login($course, true, $cm);

$PAGE->requires->jquery();
$PAGE->requires->js('/mod/arupapplication/js/arupapplication.js', false);

require_capability('mod/arupapplication:edititems', $context);

$coursecontext = context_course::instance($course->id);

$submissionsinprogress = arupapplication_submissionsinprogress($arupapplication->id);

//move up/down items
if ($moveupitem) {
    $record = $DB->get_record('arupdeclarations', array('id'=>$moveupitem));
    arupapplication_moveup_item($record, 'arupdeclarations');
}
if ($movedownitem) {
    $record = $DB->get_record('arupdeclarations', array('id'=>$movedownitem));
    arupapplication_movedown_item($record, 'arupdeclarations');
}

//moving of items
if ($movehere && isset($SESSION->arupapplication->moving->movingitem)) {
    $record = $DB->get_record('arupdeclarations', array('id'=>$SESSION->arupapplication->moving->movingitem));
    arupapplication_move_record($record, intval($movehere), 'arupdeclarations');
    $moveitem = false;
}
if ($moveitem) {
    $record = $DB->get_record('arupdeclarations', array('id'=>$moveitem));
    $SESSION->arupapplication->moving->shouldmoving = 1;
    $SESSION->arupapplication->moving->movingitem = $moveitem;
} else {
    unset($SESSION->arupapplication->moving);
}

//get the statement declarations
$lastposition = 0;
$declarations = $DB->get_records('arupdeclarations', array('applicationid'=>$arupapplication->id), 'sortorder');
if (is_array($declarations)) {
    $declarations = array_values($declarations);
    if (count($declarations) > 0) {
        $lastrecord = $declarations[count($declarations)-1];
        $lastposition = $lastrecord->sortorder;
    } else {
        $lastposition = 0;
    }
}
$lastposition++;

//the create_template-form
$create_declaration_form = new declarations_form($CFG->wwwroot . '/mod/arupapplication/declarations.php?id=' . $id . '&do_show=declarations&dowhat=' . $dowhat, array('applicationid'=>$arupapplication->id, 'sortorder'=>$lastposition, 'dowhat' => $dowhat, 'declarationid' => $declarationid));
$create_declaration_form->set_data($formdata);

if ($create_declaration_form->is_cancelled()) {
    redirect($CFG->wwwroot . '/mod/arupapplication/declarations.php?id=' . $id . '&do_show=declarations&dowhat=add');
}

$PAGE->set_url('/mod/arupapplication/declarations.php', array('id'=>$cm->id, 'do_show'=>'declarations'));
$PAGE->set_heading(format_string($course->fullname));
$PAGE->set_title(format_string($arupapplication->name));
echo $OUTPUT->header();

/// print the tabs
require('tabs.php');

echo $OUTPUT->heading(get_string('heading:declarations', 'arupapplication'));

if (isset($success)) {
    $continue = new moodle_url('/mod/arupapplication/declarations.php?id=' . $id . 'do_show=declarations');
    echo $message;
    echo $OUTPUT->continue_button($continue);
    echo $OUTPUT->footer();
    exit;
}

if (is_array($declarations)) {
    $itemnr = 0;

    $align = right_to_left() ? 'right' : 'left';

    if (isset($SESSION->arupapplication->moving) AND $SESSION->arupapplication->moving->shouldmoving == 1) {
        $anker = '<a href="declarations.php?id='.$id.'">';
        $anker .= get_string('action:cancel_moving', 'arupapplication');
        $anker .= '</a>';
        echo $OUTPUT->heading($anker);
    }

    //use list instead a table
    echo $OUTPUT->box_start('declarations');
    if (isset($SESSION->arupapplication->moving) AND $SESSION->arupapplication->moving->shouldmoving == 1) {
        $moveposition = 1;
        $movehereurl = new moodle_url($url, array('movehere'=>$moveposition));
        //only shown if shouldmoving = 1
        echo $OUTPUT->box_start('declaration_box_'.$align.' clipboard');
        $buttonlink = $movehereurl->out();
        $strbutton = get_string('action:move_here', 'arupapplication');
        $src = $OUTPUT->image_url('movehere');
        echo '<a title="'.$strbutton.'" href="'.$buttonlink.'">
                <img class="movetarget" alt="'.$strbutton.'" src="'.$src.'" />
              </a>';
        echo $OUTPUT->box_end();
    }
    //print the inserted items
    $itempos = 0;
    foreach ($declarations as $declaration) {
        $itempos++;
        //hiding the item to move
        if (isset($SESSION->arupapplication->moving)) {
            if ($SESSION->arupapplication->moving->movingitem == $declaration->id) {
                continue;
            }
        }

        //echo $OUTPUT->box_start('declaration_box_'.$align);
        echo $OUTPUT->box_start('box generalbox boxalign_'.$align);

        echo '<div class="no-overflow">'.$declaration->declaration."</div>";

        echo $OUTPUT->box_start('declaration_commands_'.$align);
        echo '<span class="declaration_commands">';
        echo '('.get_string('sortorder', 'arupapplication').':'.$itempos .')';
        echo '</span>';
        //print the moveup-button
        if ($declaration->sortorder > 1) {
            echo '<span class="declaration_command_moveup">';
            $moveupurl = new moodle_url($url, array('moveupitem'=>$declaration->id));
            $buttonlink = $moveupurl->out();
            $strbutton = get_string('action:up', 'arupapplication');
            echo '<a class="icon up" title="'.$strbutton.'" href="'.$buttonlink.'">
                    <img alt="'.$strbutton.'" src="'.$OUTPUT->image_url('t/up') . '" />
                  </a>';
            echo '</span>';
        }
        //print the movedown-button
        if ($declaration->sortorder < $lastposition - 1) {
            echo '<span class="declaration_command_movedown">';
            $urlparams = array('movedownitem'=>$declaration->id);
            $movedownurl = new moodle_url($url, $urlparams);
            $buttonlink = $movedownurl->out();
            $strbutton = get_string('action:down', 'arupapplication');
            echo '<a class="icon down" title="'.$strbutton.'" href="'.$buttonlink.'">
                    <img alt="'.$strbutton.'" src="'.$OUTPUT->image_url('t/down') . '" />
                  </a>';
            echo '</span>';
        }
        //print the move-button
        echo '<span class="declaration_command_move">';
        $moveurl = new moodle_url($url, array('moveitem'=>$declaration->id));
        $buttonlink = $moveurl->out();
        $strbutton = get_string('action:move', 'arupapplication');
        echo '<a class="editing_move" title="'.$strbutton.'" href="'.$buttonlink.'">
                <img alt="'.$strbutton.'" src="'.$OUTPUT->image_url('t/move') . '" />
              </a>';
        echo '</span>';

        //print the button to edit the item
        echo '<span class="declaration_command_edit">';
        $editurl = new moodle_url('/mod/arupapplication/declarations.php');
        $editurl->params(array('do_show'=>$do_show,
                                'dowhat'=>'edit',
                                'id'=>$id,
                                'declarationid'=>$declaration->id));

        // in edit_item.php the param id is used for the itemid
        // and the cmid is the id to get the module
        $buttonlink = $editurl->out();
        $strbutton = get_string('action:edit', 'arupapplication');
        echo '<a class="editing_update" title="'.$strbutton.'" href="'.$buttonlink.'">
                <img alt="'.$strbutton.'" src="'.$OUTPUT->image_url('t/edit') . '" />
              </a>';
        echo '</span>';

        //Hide delete option if application submission has already started
        if (!$submissionsinprogress) {
            //print the delete-button
            echo '<span class="declaration_command_toggle">';
            $deleteitemurl = new moodle_url('/mod/arupapplication/delete_record.php');
            $deleteitemurl->params(array('id'=>$id,
                                         'do_show'=>$do_show,
                                         'deleterecord'=>$declaration->id));

            $buttonlink = $deleteitemurl->out();
            $strbutton = get_string('action:delete', 'arupapplication');
            $src = $OUTPUT->image_url('t/delete');
            echo '<a class="icon delete" title="'.$strbutton.'" href="'.$buttonlink.'">
                    <img alt="'.$strbutton.'" src="'.$src.'" />
                  </a>';
            echo '</span>';
        }
        echo $OUTPUT->box_end();
        if (isset($SESSION->arupapplication->moving) AND $SESSION->arupapplication->moving->shouldmoving == 1) {
            $moveposition++;
            $movehereurl->param('movehere', $moveposition);
            echo $OUTPUT->box_start('clipboard'); //only shown if shouldmoving = 1
            $buttonlink = $movehereurl->out();
            $strbutton = get_string('action:move_here', 'arupapplication');
            $src = $OUTPUT->image_url('movehere');
            echo '<a title="'.$strbutton.'" href="'.$buttonlink.'">
                    <img class="movetarget" alt="'.$strbutton.'" src="'.$src.'" />
                  </a>';
            echo $OUTPUT->box_end();
        }
        echo '<div class="clearer">&nbsp;</div>';
        echo $OUTPUT->box_end();
    }

    echo $OUTPUT->box_end();
} else {
    echo $OUTPUT->box(get_string('no_items_available_yet', 'feedback'),
                     'generalbox boxaligncenter');
}

//Hide edit/delete options if application submission has already started
if ((!$submissionsinprogress && $itempos < ARUPAPPLICATION_MAX_DECLARATIONS) || $dowhat == 'edit') {
    $create_declaration_form->display();
}

// Finish the page
echo $OUTPUT->footer();