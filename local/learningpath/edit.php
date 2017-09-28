<?php

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/local/learningpath/lib.php');
require_once($CFG->dirroot . '/local/learningpath/forms/edit_form.php');

require_login(SITEID, false);

$learningpathid = required_param('id', PARAM_INT);

// Load learning path and check capability against category
$learningpath = $DB->get_record('local_learningpath', array('id' => $learningpathid), '*', MUST_EXIST);
$context = context_coursecat::instance($learningpath->categoryid, IGNORE_MISSING);
require_capability('local/learningpath:edit', $context ? $context : context_system::instance());

if (!isset($SESSION->local_learningpath)) {
    $SESSION->local_learningpath = new stdClass();
}

$PAGE->set_url(new moodle_url('/local/learningpath/edit.php', array('id' => $learningpath->id)));

$action = optional_param('action', '', PARAM_ALPHA);
$allowedactions = array(
    'hideaxis', 'showaxis',
    'addcolumn', 'addrow',
    'moveup', 'movedown',
    'deletecolumn', 'deleterow',
);
if (in_array($action, $allowedactions)) {
    try {
        require_sesskey();
    } catch (Exception $e) {
        $SESSION->local_learningpath->alert = new stdClass();
        $SESSION->local_learningpath->alert->message = $e->getMessage();
        $SESSION->local_learningpath->alert->type = 'alert-danger';
        redirect($PAGE->url);
    }
}
switch ($action) {
    case 'hideaxis':
        $axisid = optional_param('axisid', 0, PARAM_INT);
        $axisrecord = $DB->get_record('local_learningpath_axes', array('id' => $axisid));
        if ($axisrecord && $axisrecord->visible != 0) {
            $axisrecord->visible = 0;
            $DB->update_record('local_learningpath_axes', $axisrecord);
        }
        break;
    case 'showaxis':
        $axisid = optional_param('axisid', 0, PARAM_INT);
        $axisrecord = $DB->get_record('local_learningpath_axes', array('id' => $axisid));
        if ($axisrecord && $axisrecord->visible != 1) {
            $axisrecord->visible = 1;
            $DB->update_record('local_learningpath_axes', $axisrecord);
        }
        break;
    case 'addcolumn':
        if ($DB->count_records('local_learningpath_axes', array('learningpathid' => $learningpath->id, 'axis' => 'x')) < 15) {
            $newcol = new stdClass();
            $newcol->learningpathid = $learningpath->id;
            $newcol->sortorder = 1 + $DB->get_field('local_learningpath_axes', 'MAX(sortorder)', array('learningpathid' => $learningpath->id, 'axis' => 'x'));
            $newcol->axis = 'x';
            $newcol->name = get_string('newcolumn', 'local_learningpath');
            $newcol->visible = 0;
            $newcol->id = $DB->insert_record('local_learningpath_axes', $newcol);
            $newcol->name .= ' ['.$newcol->id.']';
            $DB->update_record('local_learningpath_axes', $newcol);
        } else {
            $SESSION->local_learningpath->alert = new stdClass();
            $SESSION->local_learningpath->alert->message = get_string('error:maxreached:xaxis', 'local_learningpath');
            $SESSION->local_learningpath->alert->type = 'alert-danger';
        }
        redirect($PAGE->url);
        break;
    case 'addrow':
        if ($DB->count_records('local_learningpath_axes', array('learningpathid' => $learningpath->id, 'axis' => 'y')) < 15) {
            $newrow = new stdClass();
            $newrow->learningpathid = $learningpath->id;
            $newrow->sortorder = 1 + $DB->get_field('local_learningpath_axes', 'MAX(sortorder)', array('learningpathid' => $learningpath->id, 'axis' => 'y'));
            $newrow->axis = 'y';
            $newrow->name = get_string('newrow', 'local_learningpath');
            $newrow->visible = 0;
            $newrow->id = $DB->insert_record('local_learningpath_axes', $newrow);
            $newrow->name .= ' ['.$newrow->id.']';
            $DB->update_record('local_learningpath_axes', $newrow);
        } else {
            $SESSION->local_learningpath->alert = new stdClass();
            $SESSION->local_learningpath->alert->message = get_string('error:maxreached:yaxis', 'local_learningpath');
            $SESSION->local_learningpath->alert->type = 'alert-danger';
        }
        redirect($PAGE->url);
        break;
    case 'moveup':
        $axisid = optional_param('axisid', 0, PARAM_INT);
        $axisrecord = $DB->get_record('local_learningpath_axes', array('id' => $axisid));
        if ($axisrecord && $axisrecord->sortorder > 1) {
            // Update axis above
            $updatesql = <<<EOS
UPDATE
    {local_learningpath_axes}
SET
    sortorder = :newsortorder
WHERE
    learningpathid = :learningpathid
    AND axis = :axis
    AND sortorder = :oldsortorder
EOS;
            $updateparams = array(
                'newsortorder' => $axisrecord->sortorder,
                'learningpathid' => $learningpath->id,
                'axis' => $axisrecord->axis,
                'oldsortorder' => $axisrecord->sortorder - 1
            );
            $DB->execute($updatesql, $updateparams);
            $axisrecord->sortorder--;
            $DB->update_record('local_learningpath_axes', $axisrecord);
        }
        redirect($PAGE->url);
        break;
    case 'movedown':
        $axisid = optional_param('axisid', 0, PARAM_INT);
        $axisrecord = $DB->get_record('local_learningpath_axes', array('id' => $axisid));
        if ($axisrecord) {
            $maxsortorder = $DB->get_field('local_learningpath_axes', 'MAX(sortorder)', array('learningpathid' => $learningpath->id, 'axis' => $axisrecord->axis));
            if ($axisrecord->sortorder < $maxsortorder) {
                // Update axis above
                $updatesql = <<<EOS
UPDATE
    {local_learningpath_axes}
SET
    sortorder = :newsortorder
WHERE
    learningpathid = :learningpathid
    AND axis = :axis
    AND sortorder = :oldsortorder
EOS;
                $updateparams = array(
                    'newsortorder' => $axisrecord->sortorder,
                    'learningpathid' => $learningpath->id,
                    'axis' => $axisrecord->axis,
                    'oldsortorder' => $axisrecord->sortorder + 1
                );
                $DB->execute($updatesql, $updateparams);
                $axisrecord->sortorder++;
                $DB->update_record('local_learningpath_axes', $axisrecord);
            }
        }
        redirect($PAGE->url);
        break;
    case 'deletecolumn' :
        $axisid = optional_param('axisid', 0, PARAM_INT);
        $column = $DB->get_record('local_learningpath_axes', array('id' => $axisid, 'axis' => 'x'));
        if (!$column) {
            $SESSION->local_learningpath->alert = new stdClass();
            $SESSION->local_learningpath->alert->message = get_string('error:couldnotdelete', 'local_learningpath', core_text::strtolower(get_string('error:axis:xaxis', 'local_learningpath')));
            $SESSION->local_learningpath->alert->type = 'alert-danger';
        } elseif (optional_param('confirm', 0, PARAM_INT)) {
            // Delete cell<->course mappings
            $courseselect = <<<EOS
cellid IN (
	SELECT id FROM {local_learningpath_cells}
    WHERE x = :axisid
)
EOS;
            $courseparams = array(
                'axisid' => $column->id,
            );
            $DB->delete_records_select('local_learningpath_courses', $courseselect, $courseparams);

            // Delete cells
            $DB->delete_records('local_learningpath_cells', array('x' => $column->id));

            // Delete axis
            $DB->delete_records('local_learningpath_axes', array('id' => $column->id));
            redirect($PAGE->url);
        } else {
            $SESSION->local_learningpath->alert = new stdClass();
            $a = new stdClass();
            $yesurl = clone($PAGE->url);
            $yesurl->param('sesskey', sesskey());
            $yesurl->param('axisid', $column->id);
            $yesurl->param('action', 'deletecolumn');
            $yesurl->param('confirm', 1);
            $a->yeslink = html_writer::link($yesurl, get_string('yes'), array('class' => 'btn btn-success'));
            $a->nolink = html_writer::link($PAGE->url, get_string('no'), array('class' => 'btn btn-danger'));
            $a->what = core_text::strtolower(get_string('error:axis:xaxis', 'local_learningpath'));
            $a->name = $column->name;
            $SESSION->local_learningpath->alert->message = get_string('delete:confirm', 'local_learningpath', $a);
            $SESSION->local_learningpath->alert->type = 'alert-warning';
            $SESSION->local_learningpath->alert->hidebutton = true;
            $SESSION->local_learningpath->alert->suppresspage = true;
        }
        break;
    case 'deleterow' :
        $axisid = optional_param('axisid', 0, PARAM_INT);
        $row = $DB->get_record('local_learningpath_axes', array('id' => $axisid, 'axis' => 'y'));
        if (!$row) {
            $SESSION->local_learningpath->alert = new stdClass();
            $SESSION->local_learningpath->alert->message = get_string('error:couldnotdelete', 'local_learningpath', core_text::strtolower(get_string('error:axis:yaxis', 'local_learningpath')));
            $SESSION->local_learningpath->alert->type = 'alert-danger';
        } elseif (optional_param('confirm', 0, PARAM_INT)) {
            // Delete cell<->course mappings
            $courseselect = <<<EOS
cellid IN (
	SELECT id FROM {local_learningpath_cells}
    WHERE y = :axisid
)
EOS;
            $courseparams = array(
                'axisid' => $row->id,
            );
            $DB->delete_records_select('local_learningpath_courses', $courseselect, $courseparams);

            // Delete cells
            $DB->delete_records('local_learningpath_cells', array('y' => $row->id));

            // Delete axis
            $DB->delete_records('local_learningpath_axes', array('id' => $row->id));
            redirect($PAGE->url);
        } else {
            $SESSION->local_learningpath->alert = new stdClass();
            $a = new stdClass();
            $yesurl = clone($PAGE->url);
            $yesurl->param('sesskey', sesskey());
            $yesurl->param('axisid', $row->id);
            $yesurl->param('action', 'deleterow');
            $yesurl->param('confirm', 1);
            $a->yeslink = html_writer::link($yesurl, get_string('yes'), array('class' => 'btn btn-success'));
            $a->nolink = html_writer::link($PAGE->url, get_string('no'), array('class' => 'btn btn-danger'));
            $a->what = core_text::strtolower(get_string('error:axis:yaxis', 'local_learningpath'));
            $a->name = $row->name;
            $SESSION->local_learningpath->alert->message = get_string('delete:confirm', 'local_learningpath', $a);
            $SESSION->local_learningpath->alert->type = 'alert-warning';
            $SESSION->local_learningpath->alert->hidebutton = true;
            $SESSION->local_learningpath->alert->suppresspage = true;
        }
        break;
}

$PAGE->set_pagelayout('standard');

$PAGE->requires->jquery();

$PAGE->requires->js(new moodle_url('/local/learningpath/js/select2.min.js'));
$PAGE->requires->css(new moodle_url('/local/learningpath/css/select2.css'));
$PAGE->requires->js_init_code(js_writer::function_call("jQuery('.select2').select2", array(array('width'=>'75%'))), true);

//$PAGE->requires->js(new moodle_url('/local/learningpath/js/bootstrap.min.js'));
$PAGE->requires->js(new moodle_url('/local/learningpath/js/bootstrap-editable.min.js'));
$PAGE->requires->css(new moodle_url('/local/learningpath/css/bootstrap-editable.css'));
$PAGE->requires->js_init_code(js_writer::function_call("jQuery('.editable').editable", array(array('mode' => 'inline'))), true);

$title = get_string('title:edit', 'local_learningpath', $learningpath->name);

$PAGE->set_title($title);

$output = $PAGE->get_renderer('local_learningpath');

$mform = new local_learningpath_edit_form();
$mform->set_data($learningpath);

if ($mform->is_cancelled()) {
    // Message and redirect to index
    $SESSION->local_learningpath->alert = new stdClass();
    $SESSION->local_learningpath->alert->message = get_string('edit:cancelled', 'local_learningpath');
    $SESSION->local_learningpath->alert->type = 'alert-warning';
    redirect(new moodle_url('/local/learningpath/index.php'));
} else if ($data = $mform->get_data()) {
    // Update it
    $DB->update_record('local_learningpath', $data);
    // Message and redirect
    $SESSION->local_learningpath->alert = new stdClass();
    $SESSION->local_learningpath->alert->message = get_string('edit:saved', 'local_learningpath');
    $SESSION->local_learningpath->alert->type = 'alert-success';
    // Choose redirect depending on save option
    if (isset($data->submitbutton2)) {
        redirect(new moodle_url('/local/learningpath/index.php'));
    } else {
        redirect($PAGE->url);
    }
}

echo $OUTPUT->header();

if (isset($SESSION->local_learningpath->alert)) {
    $suppresspage = !empty($SESSION->local_learningpath->alert->suppresspage);
    echo $output->alert($SESSION->local_learningpath->alert->message, $SESSION->local_learningpath->alert->type, empty($SESSION->local_learningpath->alert->hidebutton));
    unset($SESSION->local_learningpath->alert);
    if ($suppresspage) {
        $OUTPUT->footer();
        exit;
    }
}

echo $OUTPUT->heading($title);

$mform->display();

// Axes table
$table = new html_table();
$headings = array('');
$alignment = array('right');
$xaxisdetails = $DB->get_records('local_learningpath_axes', array('learningpathid' => $learningpath->id, 'axis' => 'x'), 'sortorder ASC');
$xmaxsortorder = $DB->get_field('local_learningpath_axes', 'MAX(sortorder)', array('learningpathid' => $learningpath->id, 'axis' => 'x'));
$url = clone($PAGE->url);
$url->param('sesskey', sesskey());
foreach ($xaxisdetails as $xaxis) {
    $edit = html_writer::link(
        '#',
        $xaxis->name,
        array(
            'class' => 'editable',
            'data-type' => 'text',
            'data-url' => 'post.php',
            'data-pk' => $xaxis->id,
            'data-name' => 'axis'
        )
    );
    $url->param('axisid', $xaxis->id);
    if (count($xaxisdetails) > 1) {
        $url->param('action', 'deletecolumn');
        $delete = html_writer::link($url, html_writer::tag('i', '', array('class' => 'fa fa-trash')), array('class' => 'lp-action-icon'));
    } else {
        $delete  = '';
    }
    $showhide = '';
    if ($xaxis->visible) {
        $url->param('action', 'hideaxis');
        $showhide .= html_writer::link($url, html_writer::tag('i', '', array('class' => 'fa fa-eye')), array('class' => 'lp-action-icon'));
    } else {
        $url->param('action', 'showaxis');
        $showhide .= html_writer::link($url, html_writer::tag('i', '', array('class' => 'fa fa-eye-slash')), array('class' => 'lp-action-icon'));
    }
    $moveup = $movedown = '';
    if ($xaxis->sortorder > 1) {
        $url->param('action', 'moveup');
        $moveup = html_writer::link($url, html_writer::tag('i', '', array('class' => 'fa fa-arrow-left')), array('class' => 'lp-action-icon'));
    }
    if ($xaxis->sortorder < $xmaxsortorder) {
        $url->param('action', 'movedown');
        $movedown = html_writer::link($url, html_writer::tag('i', '', array('class' => 'fa fa-arrow-right')), array('class' => 'lp-action-icon'));
    }
    $heading = $moveup.$edit.$delete.$showhide.$movedown;
    $headings[] = $heading;
    $alignment[] = null;
}
$headings[] = '';
$alignment[] = null;

$table->head = $headings;
$table->align = $alignment;
$table->width = 'auto';
$table->attributes['class'] = 'generaltable lp-table lp-edit-table';

$yaxisdetails = $DB->get_records('local_learningpath_axes', array('learningpathid' => $learningpath->id, 'axis' => 'y'), 'sortorder ASC');
$ymaxsortorder = $DB->get_field('local_learningpath_axes', 'MAX(sortorder)', array('learningpathid' => $learningpath->id, 'axis' => 'y'));
$count = 0;
foreach ($yaxisdetails as $yaxis) {
    $count++;

    $cells = array();
    $cell = new html_table_cell();
    $cell->header = true;
    $edit = html_writer::link(
        '#',
        $yaxis->name,
        array(
            'class' => 'editable',
            'data-type' => 'text',
            'data-url' => 'post.php',
            'data-pk' => $yaxis->id,
            'data-name' => 'axis'
        )
    );
    $url->param('axisid', $yaxis->id);
    if (count($yaxisdetails) > 1) {
        $url->param('action', 'deleterow');
        $delete = html_writer::link($url, html_writer::tag('i', '', array('class' => 'fa fa-trash')), array('class' => 'lp-action-icon'));
    } else {
        $delete  = '';
    }
    $showhide = '';
    if ($yaxis->visible) {
        $url->param('action', 'hideaxis');
        $showhide .= html_writer::link($url, html_writer::tag('i', '', array('class' => 'fa fa-eye')), array('class' => 'lp-action-icon'));
    } else {
        $url->param('action', 'showaxis');
        $showhide .= html_writer::link($url, html_writer::tag('i', '', array('class' => 'fa fa-eye-slash')), array('class' => 'lp-action-icon'));
    }
    $moveup = $movedown = '';
    if ($yaxis->sortorder > 1) {
        $url->param('action', 'moveup');
        $moveup = html_writer::link($url, html_writer::tag('i', '', array('class' => 'fa fa-arrow-up')), array('class' => 'lp-action-icon'));
    }
    if ($yaxis->sortorder < $ymaxsortorder) {
        $url->param('action', 'movedown');
        $movedown = html_writer::link($url, html_writer::tag('i', '', array('class' => 'fa fa-arrow-down')), array('class' => 'lp-action-icon'));
    }
    $cell->text = $moveup.$edit.$delete.$showhide.$movedown;
    $cells[] = clone($cell);

    $cell->header = false;
    foreach ($xaxisdetails as $xaxis) {
        $cell->attributes['class'] = 'lp-edit-courses';
        $editcellurl = new moodle_url('/local/learningpath/edit_cell.php', array('id' => $learningpathid, 'x' => $xaxis->id, 'y' => $yaxis->id));
        $countselect = 'cellid = (SELECT id FROM {local_learningpath_cells} WHERE x = :x AND y = :y)';
        $countparams = array('x' => $xaxis->id, 'y' => $yaxis->id);
        $hasdescription = $DB->get_record_select('local_learningpath_cells', "x = :x AND y = :y AND description NOT LIKE ''", $countparams);
        $hascourses = $DB->count_records_select('local_learningpath_courses', $countselect, $countparams);
        $linktext = html_writer::tag('i', '', array('class' => 'fa fa-plus-square-o'));
        if ($hasdescription && $hascourses) {
            $linktext = html_writer::tag('i', '', array('class' => 'fa fa-plus-square'));
            $cell->attributes['class'] .= ' lp-has-courses';
        } elseif ($hasdescription) {
            $cell->attributes['class'] .= ' lp-has-description';
        }
        $cell->text = html_writer::link($editcellurl, $linktext);
        $cells[] = clone($cell);
    }

    if ($count == 1) {
        $cell->attributes ['class'] = '';
        $cell->rowspan = count($yaxisdetails);
        if (count($xaxisdetails) < 15) {
            $url->remove_params('axisid');
            $url->param('action', 'addcolumn');
            $celltext = html_writer::start_span('rotated-text');
            $celltext .= html_writer::start_span('rotated-text__inner');
            $celltext .= html_writer::link($url, get_string('addcolumn', 'local_learningpath'), array('class' => 'btn btn-small'));
            $celltext .= html_writer::end_span();
            $celltext .= html_writer::end_span();
        } else {
            $celltext = '';
        }
        $cell->text = $celltext;
        $cells[] = clone($cell);
        $cell->rowspan = null;
    }

    $table->data[] = new html_table_row($cells);
}

// Final row (add row)
$cells = array();
$cell->attributes['class'] = '';

$cell->header = true;
$cell->text = '';
$cells[] = clone($cell);

$cell->header = false;
if (count($yaxisdetails) < 15) {
    $url->remove_params('axisid');
    $url->param('action', 'addrow');
    $cell->text = html_writer::link($url, get_string('addrow', 'local_learningpath'), array('class' => 'btn btn-small'));
} else {
    $cell->text = '';
}

$cell->colspan = count($xaxisdetails);
$cells[] = clone($cell);

$cell->text = '';
$cell->colspan = null;
$cells[] = clone($cell);

$table->data[] = new html_table_row($cells);

echo html_writer::start_div('lp-edit-table-wrapper');
echo html_writer::table($table);
echo html_writer::end_div();

echo $OUTPUT->footer();