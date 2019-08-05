<?php
/**
 * Responsible for getting paginated course search results
 */

require_once("../../config.php");
require_once("lib.php");
require_once($CFG->dirroot.'/lib/coursecatlib.php');

require_login(SITEID, false);
require_capability('local/search:view', context_system::instance());

$search = optional_param('search', '', PARAM_TEXT);
$page      = optional_param('page', 1, PARAM_INT) - 1; // courses run form zero-index
$perpage   = optional_param('perpage', 10, PARAM_INT);
$region    = optional_param('region', 0, PARAM_INT); // region
$allregions = optional_param('allregions', 0, PARAM_BOOL);

list($search, $searchterms) = local_search_parse_search_string($search);

// Flag if single space entered to show all entries
$showall = ($search === ' ');

$searchurl = local_search_get_url($search, $page, $perpage, $showall, $allregions);

$PAGE->set_url('/local/search/search.php');
$PAGE->set_context(context_system::instance());

$renderer = $PAGE->get_renderer('local_search');

$filters = new \local_search\local\filters();

$options = array();
foreach($DB->get_records('local_regions_reg', array('userselectable'=>'1'), 'name', 'id, name') as $row) {
    $options[] = array(
        'name'  => $row->name,
        'value' => $row->id,
        'selected' => $row->id == $region);
}
$options[] = array('name' => get_string('global', 'local_search'), 'value' => '-1', 'selected' => -1 == $region);
array_unshift($options, array('name' => get_string('allregions', 'local_search'), 'value' => '0',  'selected' => 0 == $region));

$totalcount = 0;
$courses = array();

if (!empty($searchterms) || $filters->active() || $showall) {
    $totalcount = 0;
    $courses = local_search_get_courses_search($searchterms, $totalcount, $region, $filters, 'fullname ASC', $page, $perpage, $showall, $allregions);
}

echo $renderer->courses(array_merge(
    local_search_get_results_data($courses, $search, $totalcount),
    [
        'regions' => $options,
        'appliedcount'  => count($filters->get_applied_values())
    ]));