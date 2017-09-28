<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->dirroot . '/local/learningpath/lib.php');

require_login(SITEID, false);

$categoryid = optional_param('categoryid', 0, PARAM_INT);

if ($categoryid) {
    $context = context_coursecat::instance($categoryid);
} else {
    $context = context_system::instance();
}

$capabilities = array('local/learningpath:add', 'local/learningpath:edit', 'local/learningpath:delete');
if (!has_any_capability($capabilities, $context)) {
    throw new moodle_exception('nopermissions', '', '', get_string('nopermissions:info', 'local_learningpath'));
}

if (!isset($SESSION->local_learningpath)) {
    $SESSION->local_learningpath = new stdClass();
}

$PAGE->set_url(new moodle_url('/local/learningpath/index.php'));
if ($categoryid) {
    $PAGE->url->param('categoryid', $categoryid);
}

$action = optional_param('action', '', PARAM_ALPHA);
$allowedactions = array(
    'delete',
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
    case 'delete':
        $learningpathid = optional_param('id', 0, PARAM_INT);
        $learningpath = $DB->get_record('local_learningpath', array('id' => $learningpathid));
        if (!$learningpath) {
            $SESSION->local_learningpath->alert = new stdClass();
            $SESSION->local_learningpath->alert->message = get_string('error:couldnotdelete', 'local_learningpath', core_text::strtolower(get_string('learningpath', 'local_learningpath')));
            $SESSION->local_learningpath->alert->type = 'alert-danger';
        } elseif (optional_param('confirm', 0, PARAM_INT)) {
            // Delete cell<->course mappings
            $courseselect = <<<EOS
cellid IN (
	SELECT id FROM {local_learningpath_cells}
    WHERE x IN (
            SELECT id FROM {local_learningpath_axes}
            WHERE learningpathid = :learningpathid AND axis = :x
        ) OR y IN (
            SELECT id FROM {local_learningpath_axes}
            WHERE learningpathid = :learningpathid2 AND axis = :y
        )
)
EOS;
            $courseparams = array(
                'learningpathid' => $learningpath->id,
                'learningpathid2' => $learningpath->id,
                'x' => 'x',
                'y' => 'y',
            );
            $DB->delete_records_select('local_learningpath_courses', $courseselect, $courseparams);

            // Delete cells
            $cellselect = <<<EOS
x IN (
    SELECT id FROM {local_learningpath_axes}
    WHERE learningpathid = :learningpathid AND axis = :x
) OR y IN (
    SELECT id FROM {local_learningpath_axes}
    WHERE learningpathid = :learningpathid2 AND axis = :y
)
EOS;
            $cellparams = $courseparams; // Same
            $DB->delete_records_select('local_learningpath_cells', $cellselect, $cellparams);

            // Delete axes
            $DB->delete_records('local_learningpath_axes', array('learningpathid' => $learningpath->id));

            // Delete learningpath itself
            $DB->delete_records('local_learningpath', array('id' => $learningpath->id));
            
            redirect($PAGE->url);
        } else {
            $SESSION->local_learningpath->alert = new stdClass();
            $a = new stdClass();
            $yesurl = clone($PAGE->url);
            $yesurl->param('sesskey', sesskey());
            $yesurl->param('id', $learningpath->id);
            $yesurl->param('action', 'delete');
            $yesurl->param('confirm', 1);
            $a->yeslink = html_writer::link($yesurl, get_string('yes'), array('class' => 'btn btn-success'));
            $a->nolink = html_writer::link($PAGE->url, get_string('no'), array('class' => 'btn btn-danger'));
            $a->what = core_text::strtolower(get_string('learningpath', 'local_learningpath'));
            $a->name = $learningpath->name;
            $SESSION->local_learningpath->alert->message = get_string('delete:confirm', 'local_learningpath', $a);
            $SESSION->local_learningpath->alert->type = 'alert-warning';
            $SESSION->local_learningpath->alert->hidebutton = true;
            $SESSION->local_learningpath->alert->suppresspage = true;
        }
        break;
}

$PAGE->set_pagelayout('standard');

$title = get_string('pluginname', 'local_learningpath');

$PAGE->set_title($title);

$output = $PAGE->get_renderer('local_learningpath');

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

if (has_capability('local/learningpath:add', $context)) {
    $addurl = new moodle_url('/local/learningpath/add.php');
    $link = html_writer::link($addurl, get_string('learningpath:add', 'local_learningpath'), array('class' => 'btn btn-primary'));
    echo html_writer::tag('p', $link);
}

$params = array();
$where = '';
if ($categoryid) {
    $like = $DB->sql_like('path', ':pathsnippet');
    $where = "WHERE lp.categoryid IN (SELECT id FROM {course_categories} WHERE id = :categoryid OR {$like})";
    $params['catgeoryid'] =  $categoryid;
    $params['pathsnippet'] =  "%/{$categoryid}/%";
}

$sql = <<<EOS
SELECT
    lp.*, cc.name as categoryname
FROM
    {local_learningpath} lp
LEFT JOIN
    {course_categories} cc
    ON cc.id = lp.categoryid
{$where}
EOS;
$learningpaths = $DB->get_records_sql($sql, $params);

$table = new html_table();
$table->head = array(
    get_string('learningpath', 'local_learningpath'),
    get_string('category'),
    get_string('visible', 'local_learningpath'),
    get_string('actions', 'local_learningpath'),
);
$table->align = array('left', 'left');
$table->width = 'auto';


if (empty($learningpaths)) {
    $cell = new html_table_cell();
    $cell->colspan = count($table->head);
    $cell->text = get_string('nolearningpaths', 'local_learningpath');
    $table->data[] = new html_table_row(array($cell));
}
// Reusable base cell
$cell = new html_table_cell();
foreach ($learningpaths as $learningpath) {
    // Reset styles
    $cell->style = '';

    // New row of cells
    $cells = array();

    // Name/view link
    $viewurl = new moodle_url('/local/learningpath/view.php', array('id' => $learningpath->id));
    $cell->text = html_writer::link($viewurl, $learningpath->name);
    $cells[] = clone($cell);

    // Category info
    $cell->text = !empty($learningpath->categoryname) ? $learningpath->categoryname : get_string('notsetcategory', 'local_learningpath');
    $cells[] = clone($cell);

    // Visiblity info
    $visibleclass = 'fa fa-lg';
    $visibleclass .= !empty($learningpath->visible) ? ' fa-check-circle text-success' : ' fa-times-circle text-error';
    $cell->text = html_writer::tag('i', '', array('class' => $visibleclass));
    $cell->style = 'text-align:center;';
    $cells[] = clone($cell);

    // Actions
    $actions = array();
    $catcontext = context_coursecat::instance($learningpath->categoryid, IGNORE_MISSING);
    if (has_capability('local/learningpath:edit', $catcontext ? $catcontext : $context)) {
        $editurl = new moodle_url('/local/learningpath/edit.php', array('id' => $learningpath->id));
        $actions[] = html_writer::link($editurl, get_string('learningpath:edit', 'local_learningpath'), array('class' => 'btn btn-small'));
    }
    if (has_capability('local/learningpath:delete', $context)) {
        $deleteurl = clone($PAGE->url);
        $deleteurl->param('id', $learningpath->id);
        $deleteurl->param('action', 'delete');
        $deleteurl->param('sesskey', sesskey());
        $actions[] = html_writer::link($deleteurl, get_string('learningpath:delete', 'local_learningpath'), array('class' => 'btn btn-danger btn-small'));
    }
    $cell->text = implode('&nbsp;', $actions);
    $cells[] = clone($cell);
    $table->data[] = new html_table_row($cells);
}

echo html_writer::table($table);

echo $OUTPUT->footer();