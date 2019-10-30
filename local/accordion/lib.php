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

defined('MOODLE_INTERNAL') || die();

global $CFG, $DB;
require_once($CFG->libdir.'/formslib.php');
require_once("{$CFG->libdir}/coursecatlib.php");

if (!defined('REGIONS_INSTALLED')) {
    define('REGIONS_INSTALLED', get_config('local_regions', 'version'));
}
if (!defined('COURSEMETADATA_INSTALLED')) {
    define('COURSEMETADATA_INSTALLED', get_config('local_coursemetadata', 'version'));
}
if (!defined('LOCAL_ACCORDION_ID')) {
    define('LOCAL_ACCORDION_ID', 0);
}

if (REGIONS_INSTALLED) {
    require_once($CFG->dirroot.'/local/regions/lib.php');
    if (!defined('REGIONS_REGION_EUROPE')) {
        $like = $DB->sql_like('name', ':name', false);
        $region = $DB->get_field_select('local_regions_reg', 'id', $like, array('name' => '%europe%'));
        define('REGIONS_REGION_EUROPE', $region === false ? false : (int) $region);
    }
    if (!defined('REGIONS_REGION_UKMEA')) {
        $like = $DB->sql_like('name', ':name', false);
        $region = $DB->get_field_select('local_regions_reg', 'id', $like, array('name' => '%ukimea%'));
        define('REGIONS_REGION_UKMEA', $region === false ? false : (int) $region);
    }
    unset($like, $region);
}
if (COURSEMETADATA_INSTALLED) {
    require_once($CFG->dirroot.'/local/coursemetadata/lib.php');
}

class category {
    public $id;
    public $name;
    public $root;
    public $visible;

    public $categories = array();
    public $courses = array();
    public $learningpaths = array();

    protected $_filters;
    protected $_parent;

    protected $_coursemetadatainstalled;
    protected $_regionsinstalled;

    public function __construct($category = null, $filters = array(), $root = false) {
        if ($category) {
            $this->id = $category->id;
            $this->name = $category->name;
            $this->visible = $category->visible;
        } else {
            $this->id = 0;
            $this->name = '';
            $this->visible = true;
        }
        $this->root = $root;
        $this->_filters = $filters;

        $this->_recursive_category();
        $this->courses = $this->_get_courses();
        $this->learningpaths = $this->_get_learning_paths();
    }

    protected function _recursive_category() {
        $categories = coursecat::get($this->id)->get_children();
        if ($categories) {
            foreach ($categories as $category) {
                if ($category->visible || has_capability('moodle/category:viewhiddencategories', context_coursecat::instance($category->id))) {
                    $child = new category($category, $this->_filters);
                    if (!empty($child->categories) || !empty($child->courses) || !empty($child->learningpaths)) {
                        $this->categories[] = $child;
                    }
                }
            }
        }
    }

    protected function _get_courses() {
        global $DB, $SESSION, $SITE;

        $params = array();

        $fields = 'c.id, c.fullname, c.summary, c.visible';

        $wherestatement = 'WHERE c.category = :catid';
        $params['catid'] = $this->id;
        $params['contextlevel'] = CONTEXT_COURSE;

        $sortstatement = 'ORDER BY c.sortorder ASC';

        $visiblecourses = array();

        $ccselect = ", " . context_helper::get_preload_record_columns_sql('ctx');
        $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
        $filterjoin = '';
        if ($this->_filters) {
            $filtercount = 0;
            foreach ($this->_filters as $filter) {
                if ($filter->value !== 0 && $filter->value !== '') {
                    switch ($filter->type) {
                        case 'region':
                            if ($filter->value == -1) {
                                // Global (no region mappings).
                                $wherestatement .= ' AND c.id NOT IN (SELECT DISTINCT courseid FROM {local_regions_reg_cou}) ';
                            } else if (!empty($SESSION->showukmea) && REGIONS_REGION_UKMEA !== false) {
                                $regionukmea = REGIONS_REGION_UKMEA;
                                $wherestatement .= ' AND (';
                                // In Europe (filtered region) AND UKMEA.
                                $wherestatement .= "c.id IN (SELECT DISTINCT courseid FROM {local_regions_reg_cou} WHERE regionid = {$filter->value} OR regionid = {$regionukmea})";
                                $wherestatement .= ' OR ';
                                // Or global (no region mappings).
                                $wherestatement .= 'c.id NOT IN (SELECT DISTINCT courseid FROM {local_regions_reg_cou})';
                                $wherestatement .= ') ';
                            } else {
                                // In chosen region.
                                $wherestatement .= ' AND (';
                                // In chosen region.
                                $wherestatement .= "c.id IN (SELECT DISTINCT courseid FROM {local_regions_reg_cou} WHERE regionid = {$filter->value})";
                                $wherestatement .= ' OR ';
                                // Or global (no region mappings).
                                $wherestatement .= 'c.id NOT IN (SELECT DISTINCT courseid FROM {local_regions_reg_cou})';
                                $wherestatement .= ') ';
                            }
                            break;
                        case 'subregion':
                            // In chosen sub-region.
                            $wherestatement .= ' AND (';
                            // In chosen region.
                            $wherestatement .= "c.id IN (SELECT DISTINCT courseid FROM {local_regions_sub_cou} WHERE subregionid = {$filter->value})";
                            $wherestatement .= ' OR ';
                            // Or no sub-region mappings.
                            $wherestatement .= 'c.id NOT IN (SELECT DISTINCT courseid FROM {local_regions_sub_cou})';
                            $wherestatement .= ') ';
                            break;
                        case 'metadata':
                            $filterjoin .= <<<EOF
JOIN {coursemetadata_info_field} as cif{$filtercount}
    ON (cif{$filtercount}.shortname = '{$filter->shortname}')
JOIN {coursemetadata_info_data} as cid{$filtercount}
    ON (cif{$filtercount}.id = cid{$filtercount}.fieldid
    AND c.id = cid{$filtercount}.course
    AND cid{$filtercount}.data LIKE '%{$filter->value}%')

EOF;
                            $filtercount++;
                            break;
                    }
                }
            }
        }

        $sql = <<<EOS
SELECT
    {$fields} {$ccselect}
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
        $cache = cache::make('local_accordion', 'course_info');
        $coursedata = $cache->get_many(array_keys($courses));
        foreach ($courses as $course) {
            context_helper::preload_from_record($course);
            if ($course->visible > 0
                || has_capability('moodle/course:viewhiddencourses', context_course::instance($course->id))) {

                $course->regions = '';
                $course->metadata = [];
                if (!$coursedata[$course->id]) {
                    if (REGIONS_INSTALLED) {
                        $regionssql = "
                            SELECT lrrc.regionid, lrr.name
                              FROM {local_regions_reg_cou} lrrc
                              JOIN {local_regions_reg} lrr
                                   ON lrr.id = lrrc.regionid
                             WHERE lrrc.courseid = {$course->id}
                            ";
                        $regions = $DB->get_records_sql_menu($regionssql);
                        $course->regions = implode(', ', $regions);
                    }
                    if (COURSEMETADATA_INSTALLED) {
                        $metadatafields = get_config('local_accordion', 'coursemetadata_info');
                        if ($metadatafields) {
                            $metadatasql = "
                                SELECT cif.id, cif.name, cid.data
                                  FROM {coursemetadata_info_field} cif
                                  JOIN {coursemetadata_info_category} cic
                                       ON cic.id = cif.categoryid
                             LEFT JOIN {coursemetadata_info_data} cid
                                       ON cid.fieldid = cif.id
                                           AND cid.course = {$course->id}
                                 WHERE cif.id IN ({$metadatafields})
                              ORDER BY cic.sortorder ASC, cif.sortorder ASC
                                ";
                            $course->metadata = $DB->get_records_sql($metadatasql);
                        }
                    }
                    $cache->set($course->id, [
                        'regions' => $course->regions,
                        'metadata' => $course->metadata,
                    ]);
                } else {
                    $course->regions = $coursedata[$course->id]['regions'];
                    $course->metadata = $coursedata[$course->id]['metadata'];
                }
                $visiblecourses[$course->id] = $course;
            }
        }

        return $visiblecourses;
    }

    protected function _get_learning_paths() {
        global $DB, $SESSION, $USER;

        // Only if local_wa_learning_path and local_regions are installed.
        if (!get_config('local_wa_learning_path', 'version') || !REGIONS_INSTALLED) {
            return array();
        }

        $region = null;
        if ($this->_filters) {
            foreach ($this->_filters as $filter) {
                if ($filter->type == 'region') {
                    switch ($filter->value) {
                        case -1:
                            $region = 0;
                            break;
                        case 0:
                            $region = null;
                            break;
                        default:
                            $region = (int) $filter->value;
                            break;
                    }
                }
            }
        }

        // Rolling own version as \wa_learning_path\model\learningpath::get_published_list() doesn't quite do what is needed.
        $params = array(
                'catid' => $this->id,
                'userid' => $USER->id
                );

        $regionwhere = '';
        $regionwhereukmea = '';
        if (!empty($SESSION->showukmea) && REGIONS_REGION_UKMEA !== false) {
            $regionwhereukmea .= ' OR lpr.regionid = ' . REGIONS_REGION_UKMEA;
        }
        if($region > 0) {
            $params['region'] = $region;
            $regionwhere .= " AND (lpr.regionid = :region OR lpr.regionid = 0{$regionwhereukmea}) ";
        } else if ($region === 0) {
            $regionwhere .= ' AND lpr.regionid = 0 ';
        }

        // SQL query.
        $sql = <<<EOS
SELECT
    DISTINCT lp.id, lp.title, lp.summary, lps.status as subscribed
FROM
    {wa_learning_path} lp
LEFT JOIN
    {wa_learning_path_region} lpr
    ON lpr.learningpathid = lp.id
LEFT JOIN
    {wa_learning_path_subscribe} lps
    ON lps.learningpathid = lp.id
    AND lps.userid = :userid AND lps.status = 1
WHERE
    lp.status = 2
    AND lp.preview = 0
    AND lp.category = :catid
    {$regionwhere}
ORDER BY
    subscribed DESC, lp.title ASC
EOS;

        $paths = $DB->get_records_sql($sql, $params);

        foreach ($paths as $path) {
            $regionssql = <<<EOS
SELECT
    lpr.regionid, lrr.name
FROM
    {wa_learning_path_region} lpr
JOIN
    {local_regions_reg} lrr
    ON lrr.id = lpr.regionid
WHERE
    lpr.learningpathid = :path
EOS;
            $regions = $DB->get_records_sql_menu($regionssql, array('path' => $path->id));
            $path->regions = implode(', ', $regions);
            if (empty($path->regions)) {
                $path->regions = get_string('global', 'local_regions');
            }
        }

        return $paths;
    }
}

class filter_block {
    public $contents;

    protected $_content = '';
    protected $_footer = '';
    protected $_title = '';

    protected $_advancedfilters = array();
    protected $_locationfilters = array();
    protected $_advancedfilterform;
    protected $_locationfilterform;

    protected $_showlocationfilter = false;
    protected $_showadvancedfilter = false;

    protected $_userlocation;

    public function __construct() {
        if (REGIONS_INSTALLED && get_config('local_accordion', 'regions_filter')) {
            $this->_showlocationfilter = true;
            $this->_set_user_location();
            $this->_set_location_filters();
            $this->_locationfilterform = new location_filter_form(null, $this->_locationfilters);
            $this->_set_selected_location_values();
        }
        if (COURSEMETADATA_INSTALLED && get_config('local_accordion', 'coursemetadata_filter')) {
            $this->_showadvancedfilter = true;
            $this->_set_advanced_filters();
            $this->_advancedfilterform = new advanced_filter_form(null, $this->_advancedfilters);
            $this->_set_selected_advanced_values();
        }

        $this->_set_title();
        $this->_set_content();
        $this->_footer = '';

        $this->_set_contents();
    }

    protected function _set_title() {
        $this->_title = get_string('catalogue', 'local_accordion');
    }

    public function get_filter_region() {
        global $SESSION;
        if (isset($this->_locationfilters['region'])) {
            switch ($this->_locationfilters['region']->value) {
                case -1 :
                    $region = get_string('global', 'local_regions');
                    break;
                case 0 :
                    $region = get_string('allregions', 'local_accordion');
                    break;
                default :
                    $region = $this->_locationfilters['region']->options[$this->_locationfilters['region']->value];
                    if (!empty($SESSION->showukmea)) {
                        $region .= get_string('andukmea', 'local_accordion');
                    }
                    break;
            }
        } else {
            $region = '';
        }
        return $region;
    }

    public function get_filter_region_id() {
        // Return region if set or null if not.
        return isset($this->_locationfilters['region']->value) ? $this->_locationfilters['region']->value : null;
    }

    protected function _set_content() {
        global $SESSION, $USER;

        if ($this->_showlocationfilter) {
            if (!empty($USER->auth) && $USER->auth == 'saml') {
                $this->_content .= html_writer::start_tag('div', array('class' => 'current_info'));
                $currentinfo = get_string('currentregion', 'local_accordion', $this->_get_user_location()->region->name);
                if ($this->_get_user_location()->subregion->id) {
                    $currentinfo .= html_writer::empty_tag('br')
                        . get_string('currentsubregion', 'local_accordion', $this->_get_user_location()->subregion->name);
                }
                $this->_content .= html_writer::tag('p', $currentinfo);
                if ($this->_get_user_location()->region->id === REGIONS_REGION_EUROPE && $this->_locationfilters['region']->value === REGIONS_REGION_EUROPE) {
                    $defaultshowukmea = isset($SESSION->showukmea) ? $SESSION->showukmea : 1;
                    $SESSION->showukmea = optional_param('showukmea', $defaultshowukmea, PARAM_INT);
                    $showukmeaform = new showukmea_form();
                    $this->_content .= html_writer::tag('div', $showukmeaform->form_to_html(), array('class' => 'form_wrapper'));
                } else {
                    unset($SESSION->showukmea);
                }
                $this->_content .= html_writer::end_tag('div');
            }

            $this->_content .= html_writer::tag('div', $this->_locationfilterform->form_to_html(), array('class' => 'form_wrapper'));
        }

        if ($this->_showadvancedfilter) {
            $this->_content .= html_writer::tag('div', $this->_advancedfilterform->form_to_html(), array('class' => 'form_wrapper'));
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
            if (!$userlocation || $userlocation->$field == null) {
                $this->_userlocation->{$field}->id = 0;
                $this->_userlocation->{$field}->name = get_string('notspecified', 'local_accordion');
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
                    $fieldvalue = $SESSION->locationfilters[$locationfield];
                } else {
                    $fieldvalue = $this->_userlocation->{$locationfield}->id;
                }
                $this->_locationfilters[$locationfield]->value
                    = $locationdata->{$locationfield}
                    = $locationdata->filter[$locationfield]
                    = (int) $fieldvalue;
                $userlocation[$locationfield] = 'user-'.$locationfield;
                $locationdata->{$userlocation[$locationfield]} = $this->_userlocation->{$locationfield}->id;
            }
        }
        $this->_locationfilterform->set_data($locationdata);
    }

    protected function _set_selected_advanced_values() {
        global $SESSION;

        $advanceddata = $this->_advancedfilterform->get_data();
        if (isset($advanceddata->filter)) {
            unset($SESSION->advancedfilters);
            foreach ($advanceddata->filter as $name => $value) {
                if (isset($this->_advancedfilters[$name])) {
                    $SESSION->advancedfilters[$name] = $this->_advancedfilters[$name]->value = $value;
                }
            }
        } else if (isset($SESSION->advancedfilters) && is_array($SESSION->advancedfilters)) {
            foreach ($SESSION->advancedfilters as $name => $value) {
                $this->_advancedfilters[$name]->value = $advanceddata->filter[$name] = $value;
            }
        }
        $this->_advancedfilterform->set_data($advanceddata);
    }

    public function get_filters() {
        return array_merge($this->_locationfilters, $this->_advancedfilters);
    }

    protected function _set_advanced_filters() {
        global $DB;

        $filtersql = <<<EOS
SELECT
    cif.shortname, cif.name, cif.param1
FROM
    {coursemetadata_info_field} cif
JOIN
    {coursemetadata_info_category} cic
    ON cic.id = cif.categoryid
WHERE
    cif.datatype IN ('menu', 'multiselect', 'iconsingle', 'iconmulti')
ORDER BY
    cic.sortorder ASC, cif.sortorder ASC
EOS;
        $filters = $DB->get_records_sql($filtersql);

        if (!$filters) {
            $this->_advancedfilters = array();
            return;
        }

        foreach ($filters as $filter) {
            $filter->type = 'metadata';
            $filter->value = ''; // Initialise, populated separately.
            $data = explode("\n", $filter->param1);
            $filter->options = array_combine($data, $data);
            unset($filter->param1);
            $this->_advancedfilters[$filter->shortname] = $filter;
        }
    }

    protected function _set_location_filters() {
        global $DB;

        $filter = new stdClass();
        $filter->type = 'region';
        $filter->name = get_string('region', 'local_accordion');
        $filter->shortname = 'region';
        $filter->options = array();
        $filter->value = 0; // Initialise, populated separately.

        $regions = $DB->get_records_menu('local_regions_reg', array('userselectable' => 1), '', 'id, name');

        if ($regions) {
            $filter->options = $regions;
        }

        $this->_locationfilters['region'] = $filter;

        $filter = new stdClass();
        $filter->type = 'subregion';
        $filter->name = get_string('subregion', 'local_accordion');
        $filter->shortname = 'subregion';
        $filter->options = array();
        $filter->value = 0; // Initialise, populated separately.

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

class location_filter_form extends moodleform {

    public function definition () {
        global $OUTPUT;

        $mform =& $this->_form;

        $mform->disable_form_change_checker();

        $globalcap = has_capability('local/regions:global', context_system::instance());

        if (is_array($this->_customdata)) {
            $locationfiltersstring =
                get_string('locationfilters', 'local_accordion') . ' ' . $OUTPUT->help_icon('locationfilters', 'local_accordion');

            $mform->addElement('html', html_writer::start_tag('div', array('id' => 'locationfilters', 'class' => 'filter_wrapper')));
            $mform->addElement('html', html_writer::tag('span', $locationfiltersstring, array('class' => 'filter_title')));
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

            $mform->addElement('hidden', 'id', LOCAL_ACCORDION_ID);
            $mform->setType('id', PARAM_INT);
            $mform->addElement('hidden', 'catalogue', 'accordion');
            $mform->setType('catalogue', PARAM_ALPHA);
            $mform->addElement('submit', 'submitbutton', get_string('go'));

            $mform->addElement('html', html_writer::end_tag('div'));
            $mform->addElement('html', html_writer::end_tag('div'));
        }
    }

    public function form_to_html() {
        return $this->_form->toHtml();
    }
}

class advanced_filter_form extends moodleform {

    public function definition () {
        $mform =& $this->_form;

        $mform->disable_form_change_checker();

        if (is_array($this->_customdata)) {
            $mform->addElement('html', html_writer::start_tag('div', array('id' => 'advancedfilters', 'class' => 'filter_wrapper')));
            $mform->addElement('html', html_writer::tag('span', get_string('advancedfilters', 'local_accordion'), array('class' => 'filter_title')));
            $mform->addElement('html', html_writer::start_tag('div', array('class' => 'filter_filters')));

            foreach ($this->_customdata as $filter) {
                $options = array('' => get_string('all')) + $filter->options;
                $mform->addElement('select', 'filter['.$filter->shortname.']', $filter->name, $options);
            }

            $mform->addElement('hidden', 'id', LOCAL_ACCORDION_ID);
            $mform->setType('id', PARAM_INT);
            $mform->addElement('hidden', 'catalogue', 'accordion');
            $mform->setType('catalogue', PARAM_ALPHA);
            $mform->addElement('submit', 'submitbutton', get_string('go'));

            $mform->addElement('html', html_writer::end_tag('div'));
            $mform->addElement('html', html_writer::end_tag('div'));
        }
    }

    public function form_to_html() {
        return $this->_form->toHtml();
    }
}

class showukmea_form extends moodleform {

    public function definition () {
        global $SESSION;

        $mform =& $this->_form;

        $mform->disable_form_change_checker();

        $mform->addElement('html', html_writer::start_tag('div', array('id' => 'showukmea', 'class' => 'filter_wrapper')));
        $mform->addElement('html', html_writer::start_tag('div', array('class' => 'filter_filters')));

        $mform->addElement('advcheckbox', 'showukmea', get_string('ukmea:show', 'local_accordion'));
        $mform->setDefault('showukmea', !empty($SESSION->showukmea));

        $mform->addElement('hidden', 'id', LOCAL_ACCORDION_ID);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('submit', 'submitbutton', get_string('go'));

        $mform->addElement('html', html_writer::end_tag('div'));
        $mform->addElement('html', html_writer::end_tag('div'));
    }

    public function form_to_html() {
        return $this->_form->toHtml();
    }
}