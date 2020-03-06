<?php

require_once("../../config.php");

require_login(SITEID, false);
require_capability('local/search:view', context_system::instance());

require_once("lib.php");

$search = optional_param('search', '', PARAM_TEXT);
$page = optional_param('page', 1, PARAM_INT);
$perpage = optional_param('perpage', 10, PARAM_INT);
$region = optional_param('region', 0, PARAM_INT);

$PAGE->set_url('/local/lunchandlearn/search.php');
$PAGE->set_context(context_system::instance());

$renderer = $PAGE->get_renderer('local_lunchandlearn');

$results = lunchandlearn_manager::search_sessions($search, $region, $page, $perpage);

$options = array();
foreach($DB->get_records('local_regions_reg', array('userselectable'=>'1'), 'tapsname', 'id, tapsname') as $row) {
    $options[] = array(
        'name'  => $row->tapsname,
        'value' => $row->id,
        'selected' => $row->id == $region);
}
$options[] = array('name'=>'Global', 'value'=> '-1', -1 == $region);
array_unshift($options, array('name'=>'All regions', 'value'=> '0', 0 == $region));

echo $renderer->search_events($results['sessions'], $search, $results['total'], $options);