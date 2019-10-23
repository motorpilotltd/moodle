<?php
defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->libdir.'/formslib.php');

if (!defined('REGIONS_INSTALLED')) {
    define('REGIONS_INSTALLED', get_config('local_regions', 'version'));
}
if (!defined('TAPS_INSTALLED')) {
    define('TAPS_INSTALLED', get_config('local_regions', 'version'));
}
if (!defined('COURSEMETADATA_INSTALLED')) {
    define('COURSEMETADATA_INSTALLED', get_config('local_coursemetadata', 'version'));
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

function local_search_get_course_search($value = '', $return = false) {
    global $CFG;
    static $count = 0;

    $count++;

    $id = 'localcoursesearch';

    if ($count > 1) {
        $id .= $count;
    }

    return array(
        'id' => $id,
        'action' => $CFG->wwwroot.'/local/search/index.php',
        'method' => 'get',
        'value' => $value,
        'placeholder' => get_string('search'));

}

/**
 * Utility function to tkae a search string, clean it, and return an array
 * of search terms and the original search.
 *
 * @param $search
 *
 * @return array
 */
function local_search_parse_search_string($search)
{
    $search = trim(strip_tags($search)); // trim & clean raw searched string
    $searchterms = array();
    if (!empty($search)) {
        $searchterms = explode(" ", $search);    // Search for words independently
        foreach ($searchterms as $key => $searchterm) {
            if (strlen($searchterm) < 2) {
                unset($searchterms[$key]);
            }
        }
        $search = trim(implode(" ", $searchterms));
    }
    return array($search, $searchterms);
}

function local_search_get_url($search, $page, $perpage, $showall, $allregions)
{
    $urlparams = array();
    foreach (array('search', 'page') as $param) {
        if (!empty($$param)) {
            $urlparams[$param] = $$param;
        }
    }
    if ($perpage != 10) {
        $urlparams['perpage'] = $perpage;
    }
    if ($showall) {
        $urlparams['search'] = ' ';
    }
    if ($allregions) {
        $urlparams['allregions'] = 1;
    }

    return new moodle_url('/local/search/index.php', $urlparams);
}


/**
 * A list of courses that match a search
 *
 * @global object
 * @global object
 * @param array $searchterms An array of search criteria
 * @param int $totalcount Passed in by reference.
 * @param string $region Passed in by reference.
 * @param array $filters An array of metadata filters
 * @param string $sort A field and direction to sort by
 * @param int $page The page number to get
 * @param int $recordsperpage The number of records per page
 * @param int $showall Show all courses
 * @param int $allregions Show all regions
 * @return object {@link $COURSE} records
 */
function local_search_get_courses_search($searchterms, &$totalcount, &$region, \local_search\local\filters $filters, $sort = 'fullname ASC', $page = 0, $recordsperpage = 50, $showall = false, $allregions = false) {
    global $DB, $SESSION, $OUTPUT;

    $arupadvertinstalled = $DB->count_records('modules', array('name' => 'arupadvert'));
    $arupadvertselect = '';
    $arupadvertjoin = '';
    if ($arupadvertinstalled) {
        $arupadvertselect = ", a.id as aid";
        $arupadvertjoin = <<<EOJ
LEFT JOIN
    {arupadvert} a
    ON a.course = c.id
EOJ;
    }

    $arupadverttapsinstalled = get_config('arupadvertdatatype_taps', 'version');
    $arupadvertcustominstalled = get_config('arupadvertdatatype_custom', 'version');
    $arupadverttapsselect = '';
    $arupadverttapsjoin = '';
    $arupadverttapswhere = '';
    $arupadvertcustomselect = '';
    $arupadvertcustomjoin = '';
    $arupadvertcustomwhere = '';
    if ($arupadvertinstalled) {
        if ($arupadverttapsinstalled) {
            $duration = $DB->sql_concat($DB->sql_cast_char2real('ltc.duration'), "' '", 'ltc.durationunits');
            $arupadverttapsselect = ", at.id as atid, {$duration} as duration";
            $arupadverttapsjoin = <<<EOJ
LEFT JOIN
    {arupadvertdatatype_taps} at
    ON at.arupadvertid = a.id
LEFT JOIN
    {local_taps_course} ltc
    ON ltc.courseid = at.tapscourseid
LEFT JOIN
    {local_taps_class} ltcc
    ON ltcc.courseid = ltc.courseid AND (ltcc.classhidden = 0 OR ltcc.classhidden IS NULL) AND (ltcc.archived = 0 OR ltcc.archived IS NULL)
EOJ;
        }
        if ($arupadvertcustominstalled) {
            $arupadvertcustomselect = ", ac.id as acid";
            $arupadvertcustomjoin = <<<EOJ
LEFT JOIN
    {arupadvertdatatype_custom} ac
    ON ac.arupadvertid = a.id
EOJ;
        }
    }

    $regionsinstalled = get_config('local_regions', 'version');
    $regionsjoin = '';
    $regionswhere = '';
    $regionsparams = array();
    if ($regionsinstalled) {
        if ($region != 0) {
                $regionsjoin = <<<EOJ
LEFT JOIN
    {local_regions_reg_cou} lrrc
    ON lrrc.courseid = c.id
LEFT JOIN
    {local_regions_reg} lrr
    ON lrr.id = lrrc.regionid
EOJ;

            if ($region < 0) {
                $regionswhere = <<<EOW
    AND (lrrc.regionid IS NULL)
EOW;
            } else {
                $regionsextrasql = (!empty($SESSION->showukmea) && (int) $region === REGIONS_REGION_EUROPE)
                    ? ' OR lrrc.regionid = :regionextra'
                    : '';

                $regionswhere = <<<EOW
    AND (lrrc.regionid = :regionid $regionsextrasql OR lrrc.regionid IS NULL)
EOW;
            }
            $regionsparams['regionid'] = $region;
            $regionsparams['regionextra'] = REGIONS_REGION_UKMEA;
        }
    }

    $filterjoin = '';
    $filterwhere = '';
    $filterparams = array();
    if (get_config('local_coursemetadata', 'version')) {
        $filtercount = 0;
        foreach ($filters->get_active_filters() as $filter) {
            $filtercount++;
            $filterjoin .= <<<EOJ

JOIN {coursemetadata_info_data} cid{$filtercount}
ON cid{$filtercount}.course = c.id
JOIN {coursemetadata_info_field} cif{$filtercount}
ON cif{$filtercount}.id = cid{$filtercount}.fieldid
EOJ;
            $filterwhere .= "    AND cif{$filtercount}.shortname = '{$filter->get_field_name()}' AND (";
            $filtervalues = new CachingIterator(new ArrayIterator($filter->getValue()));
            $internalfiltercount = 0;
            foreach ($filtervalues as $value) {
                $internalfiltercount++;
                $filterparams["filter{$filtercount}_{$internalfiltercount}"] = "%{$value}%";
                $filterwhere .= $DB->sql_like("cid{$filtercount}.data", ":filter{$filtercount}_{$internalfiltercount}");

                if ($filtervalues->hasNext()) {
                    $filterwhere .= ' OR ';
                }
            }
            $filterwhere .= ' ) ';

        }
    }

    $searchcond = '';
    $rankingjoin = '';
    $params     = array_merge($regionsparams, $filterparams);

    if (!empty($searchterms)) {
        $query = implode(' ', $searchterms);
        $rankingjoins = [];
        $rankingjoins['rank_course'] = "LEFT JOIN FREETEXTTABLE({course}, *, :keywords) AS rank_course ON rank_course.[KEY] = c.id";
        $params['keywords'] = $query;

        if ($arupadvertcustominstalled) {
            $rankingjoins['rank_arupadvertdatatype_custom'] = "LEFT JOIN FREETEXTTABLE({arupadvertdatatype_custom}, *, :keywords2) AS rank_arupadvertdatatype_custom ON rank_arupadvertdatatype_custom.[KEY] = ac.id";
            $params['keywords2'] = $query;
        }

        if ($arupadverttapsinstalled) {
            $rankingjoins['rank_local_taps_class'] =
                    "LEFT JOIN FREETEXTTABLE({local_taps_class}, *, :keywords3) AS rank_local_taps_class ON rank_local_taps_class.[KEY] = ltcc.id";
            $params['keywords3'] = $query;
            $rankingjoins['rank_local_taps_course'] =
                    "LEFT JOIN FREETEXTTABLE({local_taps_course}, *, :keywords4) AS rank_local_taps_course ON rank_local_taps_course.[KEY] = ltc.id";
            $params['keywords4'] = $query;
        }

        $rankingjoins['rank_course_fullname'] = "LEFT JOIN FREETEXTTABLE({course}, fullname, :keywords5) AS rank_course_fullname ON rank_course_fullname.[KEY] = c.id";
        $params['keywords5'] = $query;
        $rankingjoins['rank_arupadvertdatatype_custom_keywords'] = "LEFT JOIN FREETEXTTABLE({arupadvertdatatype_custom}, keywords, :keywords6) AS rank_arupadvertdatatype_custom_keywords ON rank_arupadvertdatatype_custom_keywords.[KEY] = ac.id";
        $params['keywords6'] = $query;
        $rankingjoins['rank_local_taps_course_keywords'] =
                "LEFT JOIN FREETEXTTABLE({local_taps_course}, keywords, :keywords7) AS rank_local_taps_course_keywords ON rank_local_taps_course_keywords.[KEY] = ltc.id";
        $params['keywords7'] = $query;

        $rankingjoin = implode("\n", array_values($rankingjoins));

        $rankingjoinscond = [];
        $sortelems = [];
        foreach ($rankingjoins as $rankingjoinname => $unused) {
            $rankingjoinscond[] =  "$rankingjoinname.RANK IS NOT NULL";

            if (in_array($rankingjoinname, ['rank_course_fullname', 'rank_arupadvertdatatype_custom_keywords', 'rank_local_taps_course_keywords'])) {
                $sortelems[] = "(ISNULL($rankingjoinname.RANK, 0) * ISNULL($rankingjoinname.RANK, 0))";
            } else {
                $sortelems[] = "ISNULL($rankingjoinname.RANK, 0)";
            }

        }
        $searchcond = " AND (" . implode(' OR ', $rankingjoinscond) . " ) ";
        $sort = implode(' + ', $sortelems) . ' DESC ';
    }

    $courses = array();
    $c = 0; // counts how many visible courses we've seen

    // Tiki pagination
    $limitfrom = $page * $recordsperpage;
    $limitto   = $limitfrom + $recordsperpage;

    $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
    $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = c.id AND ctx.contextlevel = :contextlevel)";
    $params['contextlevel'] = CONTEXT_COURSE;

    $siteid = SITEID;
    $sql = <<<EOS
SELECT
    c.*{$ccselect}{$arupadvertselect}{$arupadverttapsselect}{$arupadvertcustomselect}
FROM {course} c
   $arupadvertjoin
   $arupadverttapsjoin
   $arupadvertcustomjoin
   $regionsjoin
   $filterjoin
   $ccjoin
   $rankingjoin
WHERE
    c.id <> {$siteid}
    $searchcond
    $arupadverttapswhere
    $arupadvertcustomwhere
    $regionswhere
    $filterwhere
ORDER BY
    $sort
EOS;

    $rs = $DB->get_recordset_sql($sql, $params);

    // Hidden categories won't be included dependent on capabilities
    $categories = local_search_get_categories();
    // But want to show courses user is enrolled on
    $enrolledcourses = enrol_get_my_courses();
    // Get regions
    $allcourseregions = false;
    if ($regionsinstalled) {
        $sql = <<<EOS
SELECT
    lrrc.id,
    lrrc.courseid,
    lrr.name
FROM
    {local_regions_reg_cou} lrrc
JOIN
    {local_regions_reg} lrr
    ON lrr.id = lrrc.regionid
EOS;
        $allcourseregions = $DB->get_records_sql($sql);
    }
    $prevcourseid = 0;
    foreach($rs as $course) {
        if ($course->id === $prevcourseid) {
            continue;
        }
        $prevcourseid = $course->id;

        $visible = true;
        $skip = array_key_exists($course->id, $enrolledcourses);

        if (!$skip) {
            $coursecontext = context_course::instance($course->id);
            if (!$course->visible) {
                // No need to check all categories
                $visible = false;
            } elseif ($course->category != 0) {
                // Check for any hidden category in the tree for this course
                // If category not in array then is hidden
                if (!isset($categories[$course->category])) {
                    $visible = false;
                } else {
                    $parentcategories = explode('/', $categories[$course->category]->path);
                    unset($parentcategories[0]);
                    foreach ($parentcategories as $parentcategory) {
                        if (!isset($categories[$parentcategory]) || !$categories[$parentcategory]->visible) {
                            $visible = false;
                            break;
                        }
                    }
                }
            }
        }

        if ($visible || has_capability('moodle/course:viewhiddencourses', $coursecontext)) {
            // Don't exit this loop till the end
            // we need to count all the visible courses
            // to update $totalcount
            if ($c >= $limitfrom && $c < $limitto) {
                $course->regions = '';
                if ($allcourseregions) {
                    $courseregions = array_filter(
                        $allcourseregions,
                        local_search_course_match($course->id)
                    );
                    if ($courseregions) {
                        $tmparr = array();
                        foreach ($courseregions as $courseregion) {
                            $tmparr[] = $courseregion->name;
                        }
                        $course->regions = implode(', ', $tmparr);
                    }
                }
                if (empty($course->regions) && $regionsinstalled) {
                    $course->regions = get_string('global', 'local_regions');
                }
                $courses[$course->id] = $course;
            }
            $c++;
        }
    }

    $rs->close();

    // our caller expects 2 bits of data - our return
    // array, and an updated $totalcount
    $totalcount = $c;

    return $courses;
}

function local_search_course_match($courseid) {
    return function($courseregion) use ($courseid) { return $courseregion->courseid == $courseid; };
}

/**
 * Print a description of a course, suitable for browsing in a list.
 *
 * @param object $course the course object.
 * @param string $highlightterms (optional) some search terms that should be highlighted in the display.
 */
function local_search_print_course($course, $highlightterms = '') {
    global $CFG, $DB, $OUTPUT;

    $context = context_course::instance($course->id);

    // Rewrite file URLs so that they are correct
    $course->summary = file_rewrite_pluginfile_urls($course->summary, 'pluginfile.php', $context->id, 'course', 'summary', NULL);

    echo html_writer::start_tag('div', array('class' => 'coursebox clearfix'));

    $imgurl = $OUTPUT->image_url('no_image', 'local_search');
    if (!empty($course->aid)) {
        $sql = <<<EOS
SELECT
    cm.id
FROM
    {course_modules} cm
JOIN
    {modules} m
    ON m.id = cm.module
    AND m.name = :modulename
WHERE
    cm.course = :courseid
EOS;
        $arupadvert = $DB->get_field_sql($sql, array('courseid' => $course->id, 'modulename' => 'arupadvert'));
        if ($arupadvert) {
            $arupadvertcontext = context_module::instance($arupadvert);
            if ($arupadvertcontext) {
                $fs = get_file_storage();
                $files = $fs->get_area_files($arupadvertcontext->id, 'mod_arupadvert', 'blockimage');
                if ($files) {
                    $file = array_pop($files);
                    $imgurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), NULL, $file->get_filepath(), $file->get_filename(), false);
                }
            }
        }
    }

    echo html_writer::start_tag('div', array('class' => 'image'));
    echo html_writer::empty_tag('img', array('src' => $imgurl, 'style' => 'width: 150px;'));
    echo html_writer::end_tag('div'); // End of image div

    echo html_writer::start_tag('div', array('class' => 'info'));
    echo html_writer::start_tag('h3', array('class' => 'name'));

    $linkhref = new moodle_url('/course/view.php', array('id' => $course->id));

    $coursename = get_course_display_name_for_list($course);
    $linktext = highlight($highlightterms, format_string($coursename));
    $linkparams = array('title'=>get_string('entercourse'));
    if (empty($course->visible)) {
        $linkparams['class'] = 'dimmed';
    }
    echo html_writer::link($linkhref, $linktext, $linkparams);
    echo html_writer::end_tag('h3');

    if (isset($course->categorylink) && !empty($course->categorylink)) {
        echo html_writer::tag('p', get_string('by', 'local_search') . ' ' . $course->categorylink);
    }

    echo html_writer::start_tag('div', array('class' => 'summary'));
    $options = new stdClass();
    $options->noclean = true;
    $options->para = false;
    $options->overflowdiv = true;
    if (!isset($course->summaryformat)) {
        $course->summaryformat = FORMAT_MOODLE;
    }
    echo highlight($highlightterms, format_text($course->summary, $course->summaryformat, $options,  $course->id));

    echo html_writer::end_tag('div'); // End of summary div

    $config = get_config('local_search');
    $durationpos = empty($config->duration_position) ? 1 : $config->duration_position;
    $regionpos = empty($config->region_position) ? 1 : $config->region_position;

    $regiondata = '';
    if (REGIONS_INSTALLED && !empty($config->regions_info)) {
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
        if ($course->regions) {
            $regiondata .= html_writer::tag('span', get_string('regions', 'local_regions').':');
            $regiondata .= $course->regions;
        }
    }

    $durationdata = '';
    if (TAPS_INSTALLED && !empty($config->duration_info) && $course->duration) {
        $durationdata .= html_writer::tag('span', get_string('duration', 'local_search').':');
        $durationdata .= $course->duration;
    }

    $metadata = '';
    if (COURSEMETADATA_INSTALLED && !empty($config->coursemetadata_info)) {
        $metadatafields = get_config('local_search', 'coursemetadata_info');
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
        }
    } else {
        $course->metadata = array();
    }

    $metadatacount = 1;
    foreach ($course->metadata as $data) {
        if ($metadatacount == $durationpos) {
            $metadata .= $durationdata;
        }
        if ($metadatacount == $regionpos) {
            $metadata .= $regiondata;
        }
        if (!empty($data->data)) {
            $metadata .= html_writer::tag('span', $data->name.':');
            $metadata .= str_ireplace(',', ', ', $data->data);
        }
        $metadatacount++;
    }
    if ($durationpos >= $metadatacount) {
        $metadata .= $durationdata;
    }
    if ($regionpos >= $metadatacount) {
        $metadata .= $regiondata;
    }

    if ($metadata) {
        echo html_writer::tag('div', $metadata, array('class'=>'arup_course_metadata'));
    }

    echo html_writer::end_tag('div'); // End of info div
    echo html_writer::end_tag('div'); // End of coursebox div
}

/**
 * Print a list navigation bar
 * Display page numbers, and a link for displaying all entries
 * @param int $totalcount number of entry to display
 * @param int $page page number
 * @param int $perpage number of entry per page
 * @param string $encodedsearch
 * @param bool $allregions module name
 */
function local_search_print_navigation_bar($totalcount, $page, $perpage, $searchurl) {
    global $OUTPUT;

    // Clone to avoid changing referenced object
    $url = clone($searchurl);
    $url->remove_params('page');

    echo $OUTPUT->paging_bar($totalcount, $page, $perpage, $url);

    if ($perpage != 99999 && $totalcount > $perpage) {
        $url->params(array('perpage' => 99999));
        $link = html_writer::link($url, get_string('showall', '', $totalcount));
        echo html_writer::tag('p', $link, array('style' => 'text-align: center;'));
    } else if ($perpage === 99999) {
        $url->params(array('perpage' => 10));
        $link = html_writer::link($url, get_string('showperpage', '', '10'));
        echo html_writer::tag('p', $link, array('style' => 'text-align: center;'));
    }
}

/**
 * Returns a sorted list of categories.
 *
 * When asking for $parent='none' it will return all the categories, regardless
 * of depth. Wheen asking for a specific parent, the default is to return
 * a "shallow" resultset. Pass false to $shallow and it will return all
 * the child categories as well.
 *
 * @param string $parent The parent category if any
 * @param string $sort the sortorder
 * @param bool   $shallow - set to false to get the children too
 * @return array of categories
 */
function local_search_get_categories($parent='none', $sort=NULL, $shallow=true) {
    global $DB;

    if ($sort === NULL) {
        $sort = 'ORDER BY cc.sortorder ASC';
    } elseif ($sort ==='') {
        // leave it as empty
    } else {
        $sort = "ORDER BY $sort";
    }

    $params = array();

    $ccselect = ', ' . context_helper::get_preload_record_columns_sql('ctx');
    $ccjoin = "LEFT JOIN {context} ctx ON (ctx.instanceid = cc.id AND ctx.contextlevel = :contextlevel)";
    $params['contextlevel'] = CONTEXT_COURSECAT;

    if ($parent === 'none') {
        $sql = "SELECT cc.* $ccselect
                  FROM {course_categories} cc
               $ccjoin
                $sort";
    } elseif ($shallow) {
        $sql = "SELECT cc.* $ccselect
                  FROM {course_categories} cc
               $ccjoin
                 WHERE cc.parent = :parent
                $sort";
        $params['parent'] = $parent;

    } else {
        $sql = "SELECT cc.* $ccselect
                  FROM {course_categories} cc
               $ccjoin
                  JOIN {course_categories} ccp
                       ON ((cc.parent = ccp.id) OR (cc.path LIKE ".$DB->sql_concat('ccp.path',"'/%'")."))
                 WHERE ccp.id = :parent
                $sort";
        $params['parent'] = $parent;
    }
    $categories = array();

    $rs = $DB->get_recordset_sql($sql, $params);
    foreach($rs as $cat) {
        $catcontext = context_coursecat::instance($cat->id);
        if ($cat->visible || has_capability('moodle/category:viewhiddencategories', $catcontext)) {
            $categories[$cat->id] = $cat;
        }
    }
    $rs->close();
    return $categories;
}

function local_search_get_results_data($courses, $highlightterms = '', $total = 0) {
    global $CFG, $DB, $OUTPUT;

    $displaylist = local_search_get_categories();

    $resultdata = array(
        'coursecount' => $total,
        'courses' => array());

    foreach ($courses as $course) {

        $resultrow = array();

        // Don't show category if hidden (i.e. not in list)
        if ($course->category > 0 && isset($displaylist[$course->category])) {
            $category = $displaylist[$course->category];
            $categoryurl = new moodle_url('/course/index.php', array('categoryid' => $course->category));
            $course->categorylink = html_writer::link($categoryurl, $category->name);
        }

        $context = context_course::instance($course->id);

        // Rewrite file URLs so that they are correct
        $course->summary = file_rewrite_pluginfile_urls($course->summary, 'pluginfile.php', $context->id, 'course', 'summary', NULL);

        $imgurl = (string)$OUTPUT->image_url('no_image', 'local_search');

        if (!empty($course->aid)) {
            $sql = <<<EOS
SELECT
    cm.id
FROM
    {course_modules} cm
JOIN
    {modules} m
    ON m.id = cm.module
    AND m.name = :modulename
WHERE
    cm.course = :courseid
EOS;
            $arupadvert = $DB->get_field_sql($sql, array('courseid' => $course->id, 'modulename' => 'arupadvert'));
            if ($arupadvert) {
                $arupadvertcontext = context_module::instance($arupadvert);
                if ($arupadvertcontext) {
                    $fs = get_file_storage();
                    $files = $fs->get_area_files($arupadvertcontext->id, 'mod_arupadvert', 'blockimage');
                    if ($files) {
                        $file = array_pop($files);
                        $imgurl = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), NULL, $file->get_filepath(), $file->get_filename(), false);
                    }
                }
            }
        }
        $resultrow['imgurl'] = $imgurl;

        $resultrow['titlehref'] = new moodle_url('/course/view.php', array('id' => $course->id));
        $resultrow['title'] = highlight($highlightterms, format_string(get_course_display_name_for_list($course)));
        $resultrow['titletext'] = get_string('entercourse');
        $resultrow['titleclass'] = empty($course->visible) ? 'dimmed' : '';

        if (isset($course->categorylink) && !empty($course->categorylink)) {
            $category = $displaylist[$course->category];
            $resultrow['category'] = array (
                'stringby' => get_string('by', 'local_search'),
                'link' => $course->categorylink,
                'title' => $category->name,
                'url'  => new moodle_url('/course/index.php', ['categoryid' => $course->category]),
                'categoryclass' => $category->visible ? '' : 'category-hidden'
            );
        }

        $options = new stdClass();
        $options->noclean = true;
        $options->para = false;
        $options->overflowdiv = true;
        if (!isset($course->summaryformat)) {
            $course->summaryformat = FORMAT_MOODLE;
        }
        $resultrow['summary'] = highlight($highlightterms, format_text($course->summary, $course->summaryformat, $options, $course->id));


        $config = get_config('local_search');
        $durationpos = empty($config->duration_position) ? 1 : $config->duration_position;
        $regionpos = empty($config->region_position) ? 1 : $config->region_position;

        $regiondata = '';
        if (REGIONS_INSTALLED && !empty($config->regions_info)) {
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
            if ($course->regions) {
                $regiondata .= html_writer::tag('span', get_string('regions', 'local_regions') . ':');
                $regiondata .= $course->regions;
            }
        }

        $durationdata = '';
        if (TAPS_INSTALLED && !empty($config->duration_info) && $course->duration) {
            $durationdata .= html_writer::tag('span', get_string('duration', 'local_search') . ':');
            $durationdata .= $course->duration;
        }

        $metadata = '';
        if (COURSEMETADATA_INSTALLED && !empty($config->coursemetadata_info)) {
            $metadatafields = get_config('local_search', 'coursemetadata_info');
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
            }
        } else {
            $course->metadata = array();
        }

        $metadatacount = 1;
        foreach ($course->metadata as $data) {
            if ($metadatacount == $durationpos) {
                $metadata .= $durationdata;
            }
            if ($metadatacount == $regionpos) {
                $metadata .= $regiondata;
            }
            if (!empty($data->data)) {
                $metadata .= html_writer::tag('span', $data->name . ':');
                $metadata .= str_ireplace(',', ', ', $data->data);
            }
            $metadatacount++;
        }
        if ($durationpos >= $metadatacount) {
            $metadata .= $durationdata;
        }
        if ($regionpos >= $metadatacount) {
            $metadata .= $regiondata;
        }

        if ($metadata) {
            $resultrow['meta'] = $metadata;
        }
        $resultdata['courses'][] = $resultrow;
    }
    return $resultdata;
}