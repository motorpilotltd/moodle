<?php

defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;
require_once($CFG->libdir.'/formslib.php');
require_once($CFG->libdir.'/coursecatlib.php');

if (!defined('REGIONS_INSTALLED')) {
    define('REGIONS_INSTALLED', get_config('local_regions', 'version'));
}
if (!defined('COURSEMETADATA_INSTALLED')) {
    define('COURSEMETADATA_INSTALLED', get_config('local_coursemetadata', 'version'));
}

if (REGIONS_INSTALLED) {
    if (!defined('REGIONS_REGION_EUROPE')) {
        $like = $DB->sql_like('name', ':name', false);
        $region = $DB->get_field_select('local_regions_reg', 'id', $like, array('name' => '%europe%'));
        define('REGIONS_REGION_EUROPE', $region === false ? false : (int) $region);
    }
    if (!defined('REGIONS_REGION_UKMEA')) {
        $like = $DB->sql_like('name', ':name', false);
        $region = $DB->get_field_select('local_regions_reg', 'id', $like, array('name' => '%ukmea%'));
        define('REGIONS_REGION_UKMEA', $region === false ? false : (int) $region);
    }
    unset($like, $region);
}

function learningpath_get_categories_list(coursecat $category = null, $choose = false) {
    global $DB;
    
    $rootcategory = (int) get_config('local_learningpath', 'category');
    if ($rootcategory !== 0 && !$DB->get_record('course_categories', array('id' => $rootcategory))) {
        $rootcategory = 0;
    }

    $rtn = $choose ? array('' => get_string('choosedots')) : array();

    $topdepth = coursecat::get($rootcategory, MUST_EXIST, true)->depth;

    if (empty($category)) {
        $category = coursecat::get($rootcategory, MUST_EXIST, true);
    } else {
        $padding = str_repeat('&nbsp;',  2*($category->depth - (1+$topdepth)));

        if ($category->depth - $topdepth > 1) {
            $padding .= '-&nbsp;';
        }

        $rtn[$category->id] = $padding . $category->name;
    }
    if ($category->has_children()) {
        foreach ($category->get_children() as $child) {
            $rtn += learningpath_get_categories_list($child);
        }
    }

    return $rtn;
}

function learningpath_get_courses($cellid, $coursetype = null, $filters = null) {
    global $DB, $SESSION, $SITE;

    $params = array('cellid' => $cellid);

    $fields = 'c.id, c.fullname, c.summary, c.visible';

    $subwherestatement = '';
    if ($coursetype) {
        $subwherestatement .= 'AND coursetype = :coursetype';
        $params['coursetype'] = $coursetype;
    }
    $wherestatement = "WHERE c.id IN (SELECT DISTINCT courseid FROM {local_learningpath_courses} WHERE cellid = :cellid {$subwherestatement})";
    
    $orderby = $DB->sql_order_by_text('c.fullname');
    $sortstatement = "ORDER BY {$orderby} ASC";

    $visiblecourses = array();

    $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
    $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
    $params['contextlevel'] = CONTEXT_COURSE;

    $filterjoin = '';
    if ($filters) {
        foreach ($filters as $filter) {
            if ($filter->value !== 0 && $filter->value !== '') {
                switch ($filter->type) {
                    case 'region':
                        if ($filter->value == -1) {
                            // Global (no region mappings)
                            $wherestatement .= ' AND c.id NOT IN (SELECT DISTINCT courseid FROM {local_regions_reg_cou}) ';
                        } elseif (!empty($SESSION->showukmea) && REGIONS_REGION_UKMEA !== false) {
                            $regionukmea = REGIONS_REGION_UKMEA;
                            $wherestatement .= ' AND (';
                            // In Europe (filtered region) AND UKMEA
                            $wherestatement .= "c.id IN (SELECT DISTINCT courseid FROM {local_regions_reg_cou} WHERE regionid = {$filter->value} OR regionid = {$regionukmea})";
                            $wherestatement .= ' OR ';
                            // Or global (no region mappings)
                            $wherestatement .= 'c.id NOT IN (SELECT DISTINCT courseid FROM {local_regions_reg_cou})';
                            $wherestatement .= ') ';
                        } else {
                            $wherestatement .= ' AND (';
                            // In chosen region
                            $wherestatement .= "c.id IN (SELECT DISTINCT courseid FROM {local_regions_reg_cou} WHERE regionid = {$filter->value})";
                            $wherestatement .= ' OR ';
                            // Or global (no region mappings)
                            $wherestatement .= 'c.id NOT IN (SELECT DISTINCT courseid FROM {local_regions_reg_cou})';
                            $wherestatement .= ') ';
                        }
                        break;
                    case 'subregion':
                        // In chosen sub-region
                        $wherestatement .= ' AND (';
                        // In chosen region
                        $wherestatement .= "c.id IN (SELECT DISTINCT courseid FROM {local_regions_sub_cou} WHERE subregionid = {$filter->value})";
                        $wherestatement .= ' OR ';
                        // Or no sub-region mappings)
                        $wherestatement .= 'c.id NOT IN (SELECT DISTINCT courseid FROM {local_regions_sub_cou})';
                        $wherestatement .= ') ';
                        break;
                }
            }
        }
    }

    $sql = <<<EOS
SELECT
    {$fields}{$ccselect}
FROM
    {course} c
{$ccjoin}
{$filterjoin}
{$wherestatement}
{$sortstatement}
EOS;

    $courses = $DB->get_records_sql($sql, $params);
    if (isset($SITE->id) && array_key_exists($SITE->id, $courses)) {
        unset($courses[$SITE->id]);
    }
    foreach ($courses as $course) {
        context_helper::preload_from_record($course);
        if ($course->visible > 0
            || (has_capability('moodle/course:viewhiddencourses', context_course::instance($course->id)))) {
            if (REGIONS_INSTALLED) {
                $regionssql = <<<EOS
SELECT
    lrrc.regionid, lrr.name
FROM
    {local_regions_reg_cou} lrrc
JOIN
    {local_regions_reg} lrr
    ON lrr.id = lrrc.regionid
WHERE
    lrrc.courseid = {$course->id}
EOS;
                $regions = $DB->get_records_sql_menu($regionssql);
                $course->regions = implode(', ', $regions);
            } else {
                $course->regions = '';
            }
            if (COURSEMETADATA_INSTALLED) {
                $metadatafields = get_config('local_learningpath', 'coursemetadata_info');
                if ($metadatafields) {
                    $metadatasql = <<<EOS
SELECT
    cif.id, cif.name, cid.data
FROM
    {coursemetadata_info_field} cif
JOIN
    {coursemetadata_info_category} cic
    ON cic.id = cif.categoryid
LEFT JOIN
    {coursemetadata_info_data} cid
    ON cid.fieldid = cif.id
    AND cid.course = {$course->id}
WHERE
    cif.id IN ({$metadatafields})
ORDER BY
    cic.sortorder ASC, cif.sortorder ASC
EOS;
                    $course->metadata = $DB->get_records_sql($metadatasql);
                } else {
                    $course->metadata = array();
                }
            }
            $visiblecourses[$course->id] = $course;
        }
    }

    return $visiblecourses;
}

class local_learningpath_filter_block {
    public $contents;

    protected $_content = '';
    protected $_footer = '';
    protected $_title = '';

    protected $_locationfilters = array();
    protected $_locationfilterform;

    protected $_showlocationfilter = false;

    protected $_userlocation;

    public function __construct() {
        global $PAGE;

        if (REGIONS_INSTALLED && get_config('local_learningpath', 'regions_filter')) {
            $this->_showlocationfilter = true;
            $this->_set_user_location();
            $this->_set_location_filters();
            // $PAGE->url MUST be properly set in calling page
            $this->_locationfilterform = new local_learningpath_location_filter_form($PAGE->url, $this->_locationfilters);
            $this->_set_selected_location_values();
        }

        $this->_set_title();
        $this->_set_content();
        $this->_footer = '';

        $this->_set_contents();
    }

    protected function _set_title() {
        $this->_title = get_string('filter', 'local_learningpath');
    }

    public function get_filter_region() {
        global $SESSION;
        if (isset($this->_locationfilters['region'])) {
            switch ($this->_locationfilters['region']->value) {
                case -1 :
                    $region = get_string('global', 'local_regions');
                    break;
                case 0 :
                    $region = get_string('allregions', 'local_learningpath');
                    break;
                default :
                    $region = $this->_locationfilters['region']->options[$this->_locationfilters['region']->value];
                    if (!empty($SESSION->showukmea)) {
                        $region .= get_string('andukmea', 'local_learningpath');
                    }
                    break;
            }
        } else {
            $region = '';
        }
        return $region;
    }

    protected function _set_content() {
        global $PAGE, $SESSION, $USER;

        if ($this->_showlocationfilter) {
            if ($USER->auth == 'saml') {
                $this->_content .= html_writer::start_tag('div', array('class' => 'current_info'));
                $currentinfo = get_string('currentregion', 'local_learningpath', $this->_get_user_location()->region->name);
                if ($this->_get_user_location()->subregion->id) {
                    $currentinfo .= html_writer::empty_tag('br')
                        . get_string('currentsubregion', 'local_learningpath', $this->_get_user_location()->subregion->name);
                }
                $this->_content .= html_writer::tag('p', $currentinfo);
                if ($this->_get_user_location()->region->id === REGIONS_REGION_EUROPE && $this->_locationfilters['region']->value === REGIONS_REGION_EUROPE) {
                    $defaultshowukmea = isset($SESSION->showukmea) ? $SESSION->showukmea : 1;
                    $SESSION->showukmea = optional_param('showukmea', $defaultshowukmea, PARAM_INT);
                    $showukmeaform = new local_learningpath_showukmea_form($PAGE->url);
                    $this->_content .= html_writer::tag('div', $showukmeaform->form_to_html(), array('class' => 'form_wrapper'));
                } else {
                    unset($SESSION->showukmea);
                }
                $this->_content .= html_writer::end_tag('div');
            }

            $this->_content .= html_writer::tag('div', $this->_locationfilterform->form_to_html(), array('class' => 'form_wrapper'));
        }
    }

    protected function _set_user_location() {
        global $DB, $USER;

        $fields = array('region', 'subregion');
        $this->_userlocation = new stdClass();

        $sql = <<<EOS
SELECT
    lru.userid,
    lru.regionid,
    lru.subregionid,
    lrr.name as region,
    lrs.name as subregion
FROM
    {local_regions_use} lru
LEFT JOIN
    {local_regions_reg} lrr
    ON lru.regionid = lrr.id
LEFT JOIN
    {local_regions_sub} lrs
    ON lru.subregionid = lrs.id
WHERE
    lru.userid = :userid
EOS;
        $userlocation = $DB->get_record_sql($sql, array('userid' => $USER->id));

        foreach ($fields as $field) {
            $this->_userlocation->{$field} = new stdClass();
            if (!$userlocation || $userlocation->$field == NULL) {
                $this->_userlocation->{$field}->id = 0;
                $this->_userlocation->{$field}->name = get_string('notspecified', 'local_learningpath');
            } else {
                $this->_userlocation->{$field}->id = (int) $userlocation->{$field.'id'};
                $this->_userlocation->{$field}->name = $userlocation->{$field};
            }
        }
    }

    protected function _get_user_location() {
        if (empty($this->_userlocation)) {
            $this->_set_user_location();
        }
        return $this->_userlocation;
    }

    protected function _set_contents() {
        $this->contents = new block_contents(array('id' => 'arup_filter_block', 'class' => 'block'));
        $this->contents->collapsible = block_contents::VISIBLE;
        $this->contents->title = $this->_title;
        $this->contents->content = $this->_content;
        $this->contents->footer = $this->_footer;
    }

    protected function _set_selected_location_values() {
        global $SESSION;

        $locationfields = array('region', 'subregion');
        $locationdata = $this->_locationfilterform->get_data();

        if (isset($locationdata->filter)) {
            unset($SESSION->locationfilters);
            foreach ($locationfields as $locationfield) {
                $this->_locationfilters[$locationfield]->value
                    = $SESSION->locationfilters[$locationfield]
                    = $locationdata->{$locationfield}
                    = (int) $locationdata->filter[$locationfield];
                $userlocation[$locationfield] = 'user-'.$locationfield;
                $locationdata->{$userlocation[$locationfield]} = $this->_userlocation->{$locationfield}->id;
            }
        } else {
            foreach ($locationfields as $locationfield) {
                if (isset($SESSION->locationfilters[$locationfield]) && $SESSION->locationfilters[$locationfield] != $this->_userlocation->{$locationfield}->id) {
                    $fieldValue = $SESSION->locationfilters[$locationfield];
                } else {
                    $fieldValue = $this->_userlocation->{$locationfield}->id;
                }
                $this->_locationfilters[$locationfield]->value
                    = $locationdata->{$locationfield}
                    = $locationdata->filter[$locationfield]
                    = (int) $fieldValue;
                $userlocation[$locationfield] = 'user-'.$locationfield;
                $locationdata->{$userlocation[$locationfield]} = $this->_userlocation->{$locationfield}->id;
            }
        }
        $this->_locationfilterform->set_data($locationdata);
    }

    public function get_filters() {
        return $this->_locationfilters;
    }

    protected function _set_location_filters() {
        global $DB;

        $filter = new stdClass();
        $filter->type = 'region';
        $filter->name = get_string('region', 'local_learningpath');
        $filter->shortname = 'region';
        $filter->options = array();
        $filter->value = 0; // Initialise, populated separately

        $regions = $DB->get_records_menu('local_regions_reg', array('userselectable' => 1), '', 'id, name');

        if ($regions) {
            $filter->options = $regions;
        }

        $this->_locationfilters['region'] = $filter;

        $filter = new stdClass();
        $filter->type = 'subregion';
        $filter->name = get_string('subregion', 'local_learningpath');
        $filter->shortname = 'subregion';
        $filter->options = array();
        $filter->value = 0; // Initialise, populated separately

        $subregions = $DB->get_records_menu('local_regions_sub', null, '', 'id, name');

        if ($subregions) {
            $filter->options = $subregions;
        }

        $this->_locationfilters['subregion'] = $filter;
    }

    public static function filtered_sub_regions($regionid) {
        global $DB;

        $subregions = array();
        if (REGIONS_INSTALLED) {
            $subregions = $DB->get_records_menu('local_regions_sub', array('regionid' => $regionid), '', 'id, name');
        }

        return array(0 => get_string('all')) + $subregions;
    }
}

class local_learningpath_location_filter_form extends moodleform {

    /// Define the form
    function definition () {
        global $OUTPUT;

        $mform =& $this->_form;

        $mform->disable_form_change_checker();

        $globalcap = has_capability('local/regions:global', context_system::instance());

        if (is_array($this->_customdata)) {
            $locationfilters_string =
                get_string('locationfilters', 'local_learningpath') . ' ' . $OUTPUT->help_icon('locationfilters', 'local_learningpath');

            $mform->addElement('html', html_writer::start_tag('div', array('id' => 'locationfilters', 'class' => 'filter_wrapper')));
            $mform->addElement('html', html_writer::tag('span', $locationfilters_string, array('class' => 'filter_title')));
            $mform->addElement('html', html_writer::start_tag('div', array('class' => 'filter_filters')));

            foreach ($this->_customdata as $filter) {
                $mform->addElement('hidden', 'user-'.$filter->shortname, 0);
                $mform->setType('user-'.$filter->shortname, PARAM_INT);
                $mform->addElement('hidden', $filter->shortname, 0);
                $mform->setType($filter->shortname, PARAM_INT);
                $options = array(0 => get_string('all')) + $filter->options;
                if ($globalcap) {
                    $options = array(-1 => get_string('global', 'local_regions')) + $options;
                }
                $mform->addElement('select', 'filter['.$filter->shortname.']', $filter->name, $options);
            }

            $mform->addElement('submit', 'submitbutton', get_string('go'));

            $mform->addElement('html', html_writer::end_tag('div'));
            $mform->addElement('html', html_writer::end_tag('div'));
        }
    }

    public function form_to_html() {
        return $this->_form->toHtml();
    }
}

class local_learningpath_showukmea_form extends moodleform {

    /// Define the form
    function definition () {
        global $SESSION;

        $mform =& $this->_form;

        $mform->disable_form_change_checker();

        $mform->addElement('html', html_writer::start_tag('div', array('id' => 'showukmea', 'class' => 'filter_wrapper')));
        $mform->addElement('html', html_writer::start_tag('div', array('class' => 'filter_filters')));

        $mform->addElement('advcheckbox', 'showukmea', get_string('ukmea:show', 'local_learningpath'));
        $mform->setDefault('showukmea', !empty($SESSION->showukmea));

        $mform->addElement('submit', 'submitbutton', get_string('go'));

        $mform->addElement('html', html_writer::end_tag('div'));
        $mform->addElement('html', html_writer::end_tag('div'));
    }

    public function form_to_html() {
        return $this->_form->toHtml();
    }
}