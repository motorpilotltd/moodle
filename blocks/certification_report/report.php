<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir . '/adminlib.php');
require_once(dirname(__FILE__) . '/form/filter_form.php');
use block_certification_report\certification_report;
use local_costcentre\costcentre;
$systemcontext = context_system::instance();
require_login();

$PAGE->requires->css(new moodle_url('/blocks/certification_report/styles/certification_report.css'));
$PAGE->requires->js(new moodle_url('/blocks/certification_report/js/certification_report.js'));

$stringmanager = get_string_manager();
$strings = $stringmanager->load_component_strings('block_certification_report', 'en');
$PAGE->requires->strings_for_js(array_keys($strings), 'block_certification_report');


$actualurl = new moodle_url('/blocks/certification_report/report.php');

$PAGE->set_url($actualurl);
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->set_title(get_string('header', 'block_certification_report'));
$renderer = $PAGE->get_renderer('block_certification_report');

$filters = new stdClass();
$certificationfilter = ['visible' => true, 'reportvisible' => true];

/**
 * Verify capabilities
 */
if(!has_capability('block/certification_report:view', context_system::instance())){
    echo get_string('nopermissions', 'block_certification_report');
    exit;
}

if (!has_capability('block/certification_report:view_all_regions', context_system::instance())){
    $userregion = $DB->get_field('local_regions_use', 'geotapsregionid', ['userid' => $USER->id]);
    if (!$userregion) {
        echo get_string('noregion', 'block_certification_report');
        exit;
        
    }
    $filters->regions = [$userregion => $userregion];
}

if (has_capability('block/certification_report:view_all_costcentres', context_system::instance())){
    // Empty filter (may get populated later).
    $filters->costcentres = [];
    // All cost centres.
    $costcentres = [-1 => 'NOT SET']
        + $DB->get_records_select_menu('user', "icq != ''", [], 'icq ASC', 'DISTINCT icq as id, icq as value');
} else {
    // Pre-apply a filter if cannot view all cost centres.
    $filters->costcentres = array_keys(costcentre::get_user_cost_centres($USER->id, [
        costcentre::GROUP_LEADER,
        costcentre::HR_LEADER,
        costcentre::HR_ADMIN,
        costcentre::LEARNING_REPORTER,
    ]));
    $costcentres = array_combine($filters->costcentres,  $filters->costcentres);
}

// Get filter options (as needed for active filter display, even if filter not visible).
$regions = [-1 => 'NOT SET']
        + $DB->get_records_menu('local_regions_reg', ['userselectable' => true], 'name ASC', 'id, name');
$cohorts = $DB->get_records_menu('cohort', [], 'name ASC', 'id, name');
$rootcategory = (int) get_config('block_certification_report', 'root_category');
$categories = local_custom_certification\certification::get_categories($rootcategory);
if ($rootcategory) {
    // All child categories of root.
    $certificationfilter['category'] = array_merge([$rootcategory], array_keys(\local_custom_certification\certification::get_categories($rootcategory)));
}
$certifications = \local_custom_certification\certification::get_all($certificationfilter, 'ORDER by c.fullname');
$certificationselect = [];
foreach ($certifications as $certifid => $certification) {
    $certificationselect[$certifid] = $certification->fullname;
}

$useurlparams = true;
if (has_capability('block/certification_report:filter', context_system::instance())) {
    $reporturl = new moodle_url('/blocks/certification_report/report.php');
    $form = new certification_report_filter_form(
        $reporturl,
        [
            'regions' => $regions,
            'costcentres' => $costcentres,
            'cohorts' => $cohorts,
            'certifications' => $certificationselect,
            'categories' => $categories
        ],
        'post',
        '',
        ['id' => 'certification_report_form']
    );
    
    // Has data been submitted/form been cancelled?
    if ($form->is_cancelled()) {
        $useurlparams = false;
    } else {
        $data = $form->get_data();
        if ($data) {
            $filters->fullname = $data->fullname;
            $filters->cohorts = !empty($data->cohorts) ? $data->cohorts : [];

            $filters->certifications = !empty($data->certifications) ? $data->certifications : [];
            $filters->categories = !empty($data->categories) ? $data->categories : [];

            if (!isset($filters->regions)) {
                $filters->regions = !empty($data->regions) ? $data->regions : [];
            }
            if (!empty($data->costcentres)) {
                // Only allows cost centres user can view (from form select).
                $filters->costcentres = $data->costcentres;
            }
            $useurlparams = false;
        }
    }
}

if ($useurlparams) {
    // No form data, try URL.
    $filters->fullname = optional_param('fullname', '', PARAM_TEXT);
    $filters->cohorts = optional_param_array('cohorts', [], PARAM_INT);
    $filters->certifications = optional_param_array('certifications', [], PARAM_INT);
    $filters->categories = optional_param_array('categories', [], PARAM_INT);

    if (!isset($filters->regions)) {
        $filters->regions = optional_param_array('regions', [], PARAM_INT);
    }
    if (isset($costcentres[-1])) {
        $costcentres[-1] = -1;
    }
    $filtercostcentres = array_intersect(optional_param_array('costcentres', [], PARAM_ALPHANUMEXT), $costcentres);
    if (!empty($filtercostcentres)) {
        $filters->costcentres = $filtercostcentres;
    }

    if (!empty($form)) {
        $form->set_data($filters);
    }
}

$params = [];
foreach (['regions', 'costcentres', 'fullname', 'cohorts', 'certifications', 'categories'] as $param) {
    if (!empty($filters->{$param})) {
        $params[$param] = $filters->{$param};
    }
}

$urlbase = new moodle_url('/blocks/certification_report/report.php?'.http_build_query($params));

if(isset($filters->certifications)){
    $certificationfilter += ['id' => $filters->certifications];
}
if(isset($filters->categories)){
    $certificationfilter += ['category' => $filters->categories];
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('header', 'block_certification_report'));

if (!empty($form)) {
    $form->display();
}

echo $renderer->show_active_filters($params, $certificationselect, $categories, $regions, $cohorts);

/**
 * Get report data
 */
$certificationsreport = certification_report::get_data($filters);
if(count($certificationsreport['data']) == 0){
    echo html_writer::div(get_string('nodatafound', 'block_certification_report'));
}else{
    echo $renderer->show_table($certifications, $certificationsreport['data'], $certificationsreport['view'], $urlbase);
}

echo $OUTPUT->footer();