<?php

/**
 * Sets up the tabs used by the threesixty pages based on the users capabilites.
 *
 * @author Francois Marier <francois@catalyst.net.nz>
 * @package mod/threesixty
 */

if (!defined('MOODLE_INTERNAL')) die('You cannot call this script in that way');

if (!isset($currenttab)) {
    $currenttab = '';
}
if (!isset($cm)) {
    $cm = get_coursemodule_from_instance('threesixty', $threesixty->id);
}

$context = context_module::instance($cm->id);

$tabs = array();
$row  = array();
$activated = array();
$inactive = array();

$row[] = new tabobject('activity', "$CFG->wwwroot/mod/threesixty/profiles.php?a=$threesixty->id", get_string('tab:activity', 'threesixty'));
if (has_capability('mod/threesixty:viewrespondents', $context) || threesixty_self_completed($threesixty, $USER->id)) {
    $row[] = new tabobject('respondents', "$CFG->wwwroot/mod/threesixty/respondents.php?a=$threesixty->id", get_string('tab:respondents', 'threesixty'));
}
if (threesixty_has_requests($threesixty->id)){
    $row[] = new tabobject('requests', "$CFG->wwwroot/mod/threesixty/requests.php?a=$threesixty->id", get_string('tab:requests', 'threesixty'));
}
if (has_capability('mod/threesixty:viewreports', $context) || (time() > $threesixty->reportsavailable && has_capability('mod/threesixty:viewownreports', $context))) {
    $row[] = new tabobject('reports', "$CFG->wwwroot/mod/threesixty/report.php?a=$threesixty->id", get_string('tab:reports', 'threesixty'));
}
if (has_capability('mod/threesixty:manage', $context)) {
    $row[] = new tabobject('edit', "$CFG->wwwroot/mod/threesixty/edit.php?a=$threesixty->id", get_string('tab:edit', 'threesixty'));
}

if (count($row) == 1) {
    // If there's only one tab, don't show the tab bar
} else {
    $tabs[] = $row;
}

$useridparam = '';
if (isset($user)) {
    $useridparam = "&amp;userid=$user->id";
}

if ($currenttab == 'reports' and isset($type)) {
    $activated[] = 'reports';

    $row  = array();
    $currenttab = $type;
    $tableurl = new moodle_url('/mod/threesixty/report.php', array('a' => $threesixty->id, 'type' => 'table'));
    if ($type != 'table' && isset($user)) {
        $tableurl->param('userid', $user->id);
    }
    $row[] = new tabobject('table', $tableurl, get_string('report:table', 'threesixty'), '', true);

    $spiderurl = new moodle_url('/mod/threesixty/report.php', array('a' => $threesixty->id, 'type' => 'spiderweb'));
    if ($type != 'spiderweb' && isset($user)) {
        $spiderurl->param('userid', $user->id);
    }
    $row[] = new tabobject('spiderweb', $spiderurl, get_string('report:spiderweb', 'threesixty'), '', true);

    $tabs[] = $row;
}

if ($currenttab == 'edit' and isset($section)) {
    $activated[] = 'edit';

    $row  = array();
    $currenttab = $section;
    $row[] = new tabobject('competencies', "$CFG->wwwroot/mod/threesixty/edit.php?a=$threesixty->id",
                           get_string('edit:competencies', 'threesixty', core_text::strtolower(threesixty_get_alternative_word($threesixty, 'competency', 'competencies'))));
    $tabs[] = $row;
}

print_tabs($tabs, $currenttab, $inactive, $activated);
