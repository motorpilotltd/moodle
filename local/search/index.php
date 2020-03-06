<?php
require_once("../../config.php");

require_login(SITEID, false);
require_capability('local/search:view', context_system::instance());

require_once($CFG->dirroot.'/lib/coursecatlib.php');
require_once($CFG->dirroot.'/local/search/lib.php');
require_once($CFG->dirroot . '/local/regions/lib.php');

$userregion = local_regions_get_user_region($USER);

$search    = optional_param('search', '', PARAM_RAW);  // search words
$page      = optional_param('page', 0, PARAM_INT);     // which page to show
$perpage   = optional_param('perpage', 10, PARAM_INT); // how many per page
$region    = optional_param('region', isset($userregion->regionid) ? $userregion->regionid : 0, PARAM_INT); // region
$allregions = optional_param('allregions', 0, PARAM_BOOL); // show all regions

// Flag if single space entered to show all entries
$showall = ($search === ' ');

list($search, $searchterms) = local_search_parse_search_string($search);

$site = get_site();

$searchurl = local_search_get_url($search, $page, $perpage, $showall, $allregions);

$PAGE->set_url($searchurl);
$PAGE->set_context(context_system::instance());

$isinternaluser = (isset($USER->auth) && 'manual' == $USER->auth) ? false : true;

$PAGE->requires->js_call_amd('local_search/search', 'init');

$renderer = $PAGE->get_renderer('local_search');

if ($CFG->forcelogin) {
    require_login();
}

$strcourses = new lang_string('courses');
$strsearch = new lang_string('search');
$strsearchresults = new lang_string('searchresults');
$strnovalidcourses = new lang_string('noresults', 'local_search');

$courses = array();
$totalcount = 0;

$filters = new \local_search\local\filters();

if (!empty($searchterms) || $filters->active() || $showall) {
    $totalcount = 0;
    $courses = local_search_get_courses_search($searchterms, $totalcount, $region, $filters, 'fullname ASC', $page, $perpage, $showall, $allregions);
}

$PAGE->navbar->add($strcourses, new moodle_url('/course/index.php'));
$PAGE->navbar->add($strsearch, new moodle_url('/local/search/index.php'));
if (!empty($search)) {
    $PAGE->navbar->add(s($search));
}
$PAGE->set_title("$site->fullname : $strsearchresults");
$PAGE->set_heading($site->fullname);

echo $OUTPUT->header();

echo html_writer::start_tag('div', array('class' => 'search-content'));

echo $OUTPUT->heading(get_string('search'), 2);

echo $renderer->search_form(local_search_get_course_search($search));

$options = array();
foreach($DB->get_records('local_regions_reg', array('userselectable'=>'1'), 'name', 'id, name') as $row) {
    $options[] = array(
        'name'  => $row->name,
        'value' => $row->id,
        'selected' => $row->id == $region);
}
$options[] = array('name' => get_string('global', 'local_search'), 'value' => '-1', 'selected' => -1 == $region);
array_unshift($options, array('name' => get_string('allregions', 'local_search'), 'value' => '0',  'selected' => 0 == $region));

$filteroutput = new \local_search\output\filter_output($filters);

$enablelynda = false;
if (isset($userregion->geotapsregionid)) {
    $enablelynda = \local_lynda\lib::enabledforregion($userregion->geotapsregionid);
}

$resultsdata = array_merge(local_search_get_results_data($courses, $search, $totalcount), array(
    'searchterm'    => $search,
    'regionid'      => $region,
    'appliedcount'  => count($filters->get_applied_values()),
    'courseloader'  => new moodle_url('/local/search/search.php'),
    'searchloader'  => new moodle_url('/local/lunchandlearn/search.php'),
    'kalturaloader' => new moodle_url('/local/kalturaview/search.php'),
    'lyndaloader' => new moodle_url('/local/lynda/search.php'),
    'regions' => $options,
    'filtersdata' => $filteroutput->export_for_template($OUTPUT),
    'kalturaenabled' => $isinternaluser,
    'lyndaenabled' => $enablelynda,
    'learningeventsenabled' => $isinternaluser
));

if($DB->get_dbfamily() != 'mssql') {
    echo $OUTPUT->notification('Search will not function correctly without MS SQL so results below may be inaccurate.');
}

echo $renderer->search_results($resultsdata, $search);

echo html_writer::end_tag('div');

echo $OUTPUT->footer();