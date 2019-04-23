<?php

namespace block_certification_report;
use local_custom_certification\certification;
use local_custom_certification\completion;
use local_costcentre\costcentre;

/**
 * @package block_certification_report
 */

class certification_report {

    const CRITERIA_TYPE_CONTAIN = 0;
    const CRITERIA_TYPE_DOES_NOT_CONTAIN = 1;
    const CRITERIA_TYPE_IS_EQUAL_TO = 2;
    const CRITERIA_TYPE_STARTS_WITH = 3;
    const CRITERIA_TYPE_ENDS_WITH = 4;
    const CRITERIA_TYPE_IS_EMPTY = 5;
    const CRITERIA_TYPE_IS_NOT_EQUAL_TO = 6;

    /**
     * Prepare sql for given criteria
     *
     * @param $criteria
     * @param $paramname
     * @param $field
     * @param $value
     * @return array
     */
    public static function get_sql_for_criteria($criteria, $paramname, $field, $value){
        global $DB;
        $params = [];
        switch($criteria) {
            case self::CRITERIA_TYPE_CONTAIN: // Contains.
                $res = $DB->sql_like($field, ":$paramname", false, false);
                $params[$paramname] = "%$value%";
                break;
            case self::CRITERIA_TYPE_DOES_NOT_CONTAIN: // Does not contain.
                $res = $DB->sql_like($field, ":$paramname", false, false, true);
                $params[$paramname] = "%$value%";
                break;
            case self::CRITERIA_TYPE_IS_EQUAL_TO: // Equal to.
                $res = $DB->sql_like($field, ":$paramname", false, false);
                $params[$paramname] = "$value";
                break;
            case self::CRITERIA_TYPE_STARTS_WITH: // Starts with.
                $res = $DB->sql_like($field, ":$paramname", false, false);
                $params[$paramname] = "$value%";
                break;
            case self::CRITERIA_TYPE_ENDS_WITH: // Ends with.
                $res = $DB->sql_like($field, ":$paramname", false, false);
                $params[$paramname] = "%$value";
                break;
            case self::CRITERIA_TYPE_IS_EMPTY: // Empty.
                $res = $field.' = :'.$paramname.')';
                $params[$paramname] = '';
                break;
            case self::CRITERIA_TYPE_IS_NOT_EQUAL_TO: // Not equal to.
                $res = $DB->sql_like($field, ":$paramname", false, false, true);
                $params[$paramname] = "$value";
                break;
            default:
                $res ='';
                break;
        }
        return ['sql' => $res, 'params' => $params];
    }

    public static function get_filter_options() {
        global $DB, $USER;

        $options = [];

        $options['actualregions'] = self::get_hub_options('REGION_NAME');
        $options['georegions'] = self::get_hub_options('GEO_REGION');

        if (has_capability('block/certification_report:view_all_costcentres', \context_system::instance())
                || has_capability('block/certification_report:view_all_regions', \context_system::instance())){
            $options['costcentres'] = [-1 => 'NOT SET']
                + $DB->get_records_select_menu('user', "icq != ''", [], 'icq ASC', 'DISTINCT icq as id, icq as value');
        } else {
            $costcentres = array_keys(costcentre::get_user_cost_centres($USER->id, [
                costcentre::GROUP_LEADER,
                costcentre::HR_LEADER,
                costcentre::HR_ADMIN,
                costcentre::LEARNING_REPORTER,
            ]));
            $options['costcentres'] = array_combine($costcentres,  $costcentres);
        }

        $options['cohorts'] = $DB->get_records_menu('cohort', [], 'name ASC', 'id, name');

        $rootcategory = (int) get_config('block_certification_report', 'root_category');
        $options['categories'] = certification::get_categories($rootcategory);

        $certificationfilter = ['visible' => true, 'reportvisible' => true];
        if ($rootcategory) {
            // All child categories of root.
            $certificationfilter['category'] = array_merge([$rootcategory], array_keys(certification::get_categories($rootcategory)));
        }
        $certifications = certification::get_all($certificationfilter, 'ORDER by c.fullname');
        $certificationselect = [];
        foreach ($certifications as $certifid => $certification) {
            $certificationselect[$certifid] = $certification->fullname;
        }
        $options['certificationsdata'] = $certifications;
        $options['certifications'] = $certificationselect;

        $options['groupnames'] = self::get_hub_options('GROUP_NAME');
        $options['locationnames'] = self::get_hub_options('LOCATION_NAME');
        $options['employmentcategories'] = self::get_hub_options('EMPLOYMENT_CATEGORY');
        $options['grades'] = self::get_hub_options('GRADE');

        return $options;
    }

    public static function get_filter_data($filteroptions, $data = null) {
        global $DB, $USER;

        $filters = new \stdClass();

        if (has_capability('block/certification_report:view_all_costcentres', \context_system::instance())
                || has_capability('block/certification_report:view_all_regions', \context_system::instance())) {
            // Empty filter (may get populated later).
            $filters->costcentres = [];
        } else {
            // Pre-apply a filter if cannot view all cost centres.
            $filters->costcentres = array_keys(costcentre::get_user_cost_centres($USER->id, [
                costcentre::GROUP_LEADER,
                costcentre::HR_LEADER,
                costcentre::HR_ADMIN,
                costcentre::LEARNING_REPORTER,
            ]));

            // Force no region filtering as driven by costcentre.
            $filters->actualregions = [];
            $filters->georegions = [];
        }

        if ($data) {
            // From form.
            $filters->fullname = $data->fullname;
            $filters->cohorts = !empty($data->cohorts) ? $data->cohorts : [];

            $filters->certifications = !empty($data->certifications) ? $data->certifications : [];
            $filters->categories = !empty($data->categories) ? $data->categories : [];

            if (!isset($filters->actualregions)) {
                $filters->actualregions = !empty($data->actualregions) ? $data->actualregions : [];
            }

            if (!isset($filters->georegions)) {
                $filters->georegions = !empty($data->georegions) ? $data->georegions : [];
            }

            if (!empty($data->costcentres)) {
                // Only allows cost centres user can view (from form select).
                $filters->costcentres = $data->costcentres;
            }

            $filters->groupnames = !empty($data->groupnames) ? $data->groupnames : [];
            $filters->locationnames = !empty($data->locationnames) ? $data->locationnames : [];
            $filters->employmentcategories = !empty($data->employmentcategories) ? $data->employmentcategories : [];
            $filters->grades = !empty($data->grades) ? $data->grades : [];

            return $filters;
        }

        // Otherwise get from URL.
        $filters->fullname = optional_param('fullname', '', PARAM_TEXT);
        $filters->cohorts = optional_param_array('cohorts', [], PARAM_INT);
        $filters->certifications = optional_param_array('certifications', [], PARAM_INT);
        $filters->categories = optional_param_array('categories', [], PARAM_INT);

        if (!isset($filters->actualregions)) {
            $filters->actualregions = optional_param_array('actualregions', [], PARAM_TEXT);
        }

        if (!isset($filters->georegions)) {
            $filters->georegions = optional_param_array('georegions', [], PARAM_TEXT);
        }

        $filtercostcentres = array_intersect(optional_param_array('costcentres', [], PARAM_ALPHANUMEXT), array_keys($filteroptions['costcentres']));
        if (!empty($filtercostcentres)) {
            $filters->costcentres = $filtercostcentres;
        }

        $filters->groupnames = optional_param_array('groupnames', [], PARAM_TEXT);
        $filters->locationnames = optional_param_array('locationnames', [], PARAM_TEXT);
        $filters->employmentcategories = optional_param_array('employmentcategories', [], PARAM_TEXT);
        $filters->grades = optional_param_array('grades', [], PARAM_TEXT);

        return $filters;
    }

    public static function get_base_url($filters, $view, $page = 'report') {
        if (!in_array($page, ['report'])) {
            $page = 'report';
        }
        $params = [];
        $allowedparams = [
            'actualregions',
            'georegions',
            'costcentres',
            'fullname',
            'cohorts',
            'certifications',
            'categories',
            'groupnames',
            'locationnames',
            'employmentcategories',
            'grades',
        ];
        foreach ($allowedparams as $param) {
            if (!empty($filters->{$param})) {
                if (is_array($filters->{$param})) {
                    foreach ($filters->{$param} as $index => $value) {
                        $params["{$param}[{$index}]"] = $value;
                    }
                } else {
                    $params[$param] = $filters->{$param};
                }
            }
        }
        if ($view == 'regions') {
            $params['regionview'] = optional_param('regionview', 'actual', PARAM_ALPHA) == 'geo' ? 'geo' : 'actual';
        }

        return new \moodle_url("/blocks/certification_report/{$page}.php", $params);
    }

    /**
     * Remove array params from URL.
     * Example:
     * remove_url_params($url, ['costcentre']);
     * Will remove all costcentre, costcentre[], and costcentre[0] type params.
     *
     * return \moodle_url
     */
    public static function remove_url_params(\moodle_url $url, $params) {
        if (!is_array($params)) {
            $params = [$params];
        }
        // Passed param of 'regions' will remove actual _and_ geo filter params.
        if (in_array('regions', $params)) {
            $params[] = 'actualregions';
            $params[] = 'georegions';
            $params = array_diff($params, ['regions']);
        }
        $allowedparams = [
            'actualregions',
            'georegions',
            'costcentres',
            'fullname',
            'cohorts',
            'certifications',
            'categories',
            'groupnames',
            'locationnames',
            'employmentcategories',
            'grades',
        ];
        // Validate passed params.
        foreach ($params as $index => $param) {
            if (!in_array($param, $allowedparams)) {
                unset($params[$index]);
            }
        }
        // Grab existing params.
        $urlparams = $url->params();
        // Clear down existing params.
        $url->remove_all_params();
        // Regular expression to match existing params to remove.
        $pregmatch = '/^'.implode('|', $params).'/';
        // Loop through and remove unwanted params.
        foreach (array_keys($urlparams) as $urlparam) {
            if (preg_match($pregmatch, $urlparam)) {
                unset($urlparams[$urlparam]);
            }

        }
        // Add back in still required params.
        $url->params($urlparams);
        return $url;
    }

    /**
     * Get hub options from DB
     */
    public static function get_hub_options($field) {
        global $DB;
        $sql = "SELECT DISTINCT {$field} as id, {$field} as value FROM SQLHUB.ARUP_ALL_STAFF_V WHERE {$field} IS NOT NULL AND {$field} != '' ORDER BY {$field} ASC";
        $options = [-1 => 'NOT SET'] + $DB->get_records_sql_menu($sql);
        return $options;
    }

    public static function get_data($infilters = null){
        global $DB;

        // Clone if an object as might be manipulated in this method.
        $filters = is_object($infilters) ? clone $infilters : $infilters;

        $regionview = optional_param('regionview', 'actual', PARAM_ALPHA);
        $regionfield = $regionview == 'geo' ? 'georegion' : 'actualregion';
        $regionhubfield = $regionview == 'geo' ? 'GEO_REGION' : 'REGION_NAME';

        /**
         * Filter certifications
         */
        $certificationfilter = ['visible' => true, 'reportvisible' => true];

        if(!empty($filters->certifications)){
            $certificationfilter += ['id' => $filters->certifications];
        }

        if(!empty($filters->categories)){
            $certificationfilter += ['category' => $filters->categories];
        } else if (get_config('block_certification_report', 'root_category')) {
            // All child categories of root.
            $root = (int) get_config('block_certification_report', 'root_category');
            $categories = array_merge([$root], array_keys(\local_custom_certification\certification::get_categories($root)));
            $certificationfilter += ['category' => $categories];
        }

        $certifications = certification::get_all($certificationfilter, 'ORDER by c.fullname');

        list($insql, $params) = $DB->get_in_or_equal(array_keys($certifications), SQL_PARAMS_NAMED, 'param', true, null);

        $now = time();
        $where = "";
        $join = "";
        $view = 'regions';

        /**
         * Filter for region.
         */
        if (!empty($filters->actualregions)){
            // Check for not set region selection.
            $notsetwhere = '';
            if (in_array(-1, $filters->actualregions)) {
                $notsetwhere = "(REGION_NAME IS NULL OR REGION_NAME = '')";
                $filters->actualregions = array_diff($filters->actualregions, [-1]);
            }
            // Any more to filter?
            $generalwhere = '';
            if (!empty($filters->actualregions)) {
                list($sqldata, $sqlparams) = $DB->get_in_or_equal($filters->actualregions, SQL_PARAMS_NAMED, 'actualregion');
                $generalwhere .= "REGION_NAME {$sqldata}";
                $params += $sqlparams;
            }
            if ($notsetwhere && $generalwhere) {
                $where .= " AND ({$notsetwhere} OR {$generalwhere})";
            } else {
                // Only one of two will be set...
                $where .= " AND {$notsetwhere}{$generalwhere}";
            }
            $view = 'costcentre';
        }
        if (!empty($filters->georegions)){
            // Check for not set region selection.
            $notsetwhere = '';
            if (in_array(-1, $filters->georegions)) {
                $notsetwhere = "(GEO_REGION IS NULL OR GEO_REGION = '')";
                $filters->georegions = array_diff($filters->georegions, [-1]);
            }
            // Any more to filter?
            $generalwhere = '';
            if (!empty($filters->georegions)) {
                list($sqldata, $sqlparams) = $DB->get_in_or_equal($filters->georegions, SQL_PARAMS_NAMED, 'georegion');
                $generalwhere .= "GEO_REGION {$sqldata}";
                $params += $sqlparams;
            }
            if ($notsetwhere && $generalwhere) {
                $where .= " AND ({$notsetwhere} OR {$generalwhere})";
            } else {
                // Only one of two will be set...
                $where .= " AND {$notsetwhere}{$generalwhere}";
            }
            $view = 'costcentre';
        }

        /**
         * Filter for cost centres.
         */
        if(!empty($filters->costcentres)){
            $view = (count($filters->costcentres) === 1 ? 'users' : 'costcentre');
            // Check for not set cost centre selection.
            $notsetwhere = '';
            if (in_array(-1, $filters->costcentres)) {
                $notsetwhere = "u.icq = ''";
                $filters->costcentres = array_diff($filters->costcentres, [-1]);
            }
            // Any more to filter?
            $generalwhere = '';
            if (!empty($filters->costcentres)) {
                list($ccinsql, $ccparams) = $DB->get_in_or_equal($filters->costcentres, SQL_PARAMS_NAMED, 'paramcc', true, null);
                $generalwhere = "u.icq {$ccinsql}";
                $params += $ccparams;
            }
            if ($notsetwhere && $generalwhere) {
                $where .= " AND ({$notsetwhere} OR {$generalwhere})";
            } else {
                // Only one of two will be set...
                $where .= " AND {$notsetwhere}{$generalwhere}";
            }
        }

        /**
         * User full name filter
         */
        if(!empty($filters->fullname)){
            $sqldata = self::get_sql_for_criteria(self::CRITERIA_TYPE_CONTAIN, 'fullname' ,$DB->sql_concat('u.firstname', "' '", 'u.lastname'), $filters->fullname);
            $where .= " AND ".$sqldata['sql'];
            $params += $sqldata['params'];
            $view = 'users';
        }

        /**
         * Filter for Cohort
         */
        if(!empty($filters->cohorts)){
            list($sqldata, $sqlparams) = $DB->get_in_or_equal($filters->cohorts, SQL_PARAMS_NAMED, 'cohort');
            $params += $sqlparams;
            $join = "
                JOIN (
                    SELECT
                        DISTINCT
                        cau.userid,
                        cau.certifid
                    FROM {cohort} c
                    JOIN {certif_assignments} ca ON  ca.assignmenttypeid = c.id AND ca.assignmenttype = :assignmenttype
                    JOIN {certif_assignments_users} cau ON cau.assignmentid = ca.id
                    WHERE c.id {$sqldata}
                ) ca ON ca.userid = cua.userid AND ca.certifid = cua.certifid
            ";
            $params['assignmenttype'] = certification::ASSIGNMENT_TYPE_AUDIENCE;
        }

        // Other hub field filters.
        // Group name.
        if (!empty($filters->groupnames)){
            // Check for not set groupname selection.
            $notsetwhere = '';
            if (in_array(-1, $filters->groupnames)) {
                $notsetwhere = "(GROUP_NAME IS NULL OR GROUP_NAME = '')";
                $filters->groupnames = array_diff($filters->groupnames, [-1]);
            }
            // Any more to filter?
            $generalwhere = '';
            if (!empty($filters->groupnames)) {
                list($sqldata, $sqlparams) = $DB->get_in_or_equal($filters->groupnames, SQL_PARAMS_NAMED, 'groupname');
                $generalwhere .= "GROUP_NAME {$sqldata}";
                $params += $sqlparams;
            }
            if ($notsetwhere && $generalwhere) {
                $where .= " AND ({$notsetwhere} OR {$generalwhere})";
            } else {
                // Only one of two will be set...
                $where .= " AND {$notsetwhere}{$generalwhere}";
            }
        }
        // Location name.
        if (!empty($filters->locationnames)){
            // Check for not set locationname selection.
            $notsetwhere = '';
            if (in_array(-1, $filters->locationnames)) {
                $notsetwhere = "(LOCATION_NAME IS NULL OR LOCATION_NAME = '')";
                $filters->locationnames = array_diff($filters->locationnames, [-1]);
            }
            // Any more to filter?
            $generalwhere = '';
            if (!empty($filters->locationnames)) {
                list($sqldata, $sqlparams) = $DB->get_in_or_equal($filters->locationnames, SQL_PARAMS_NAMED, 'locationname');
                $generalwhere .= "LOCATION_NAME {$sqldata}";
                $params += $sqlparams;
            }
            if ($notsetwhere && $generalwhere) {
                $where .= " AND ({$notsetwhere} OR {$generalwhere})";
            } else {
                // Only one of two will be set...
                $where .= " AND {$notsetwhere}{$generalwhere}";
            }
        }
        // Employment category.
        if (!empty($filters->employmentcategories)){
            // Check for not set employment category selection.
            $notsetwhere = '';
            if (in_array(-1, $filters->employmentcategories)) {
                $notsetwhere = "(EMPLOYMENT_CATEGORY IS NULL OR EMPLOYMENT_CATEGORY = '')";
                $filters->employmentcategories = array_diff($filters->employmentcategories, [-1]);
            }
            // Any more to filter?
            $generalwhere = '';
            if (!empty($filters->employmentcategories)) {
                list($sqldata, $sqlparams) = $DB->get_in_or_equal($filters->employmentcategories, SQL_PARAMS_NAMED, 'employmentcategory');
                $generalwhere .= "EMPLOYMENT_CATEGORY {$sqldata}";
                $params += $sqlparams;
            }
            if ($notsetwhere && $generalwhere) {
                $where .= " AND ({$notsetwhere} OR {$generalwhere})";
            } else {
                // Only one of two will be set...
                $where .= " AND {$notsetwhere}{$generalwhere}";
            }
        }
        // Grade.
        if (!empty($filters->grades)){
            // Check for not set employment category selection.
            $notsetwhere = '';
            if (in_array(-1, $filters->grades)) {
                $notsetwhere = "(GRADE IS NULL OR GRADE = '')";
                $filters->grades = array_diff($filters->grades, [-1]);
            }
            // Any more to filter?
            $generalwhere = '';
            if (!empty($filters->grades)) {
                list($sqldata, $sqlparams) = $DB->get_in_or_equal($filters->grades, SQL_PARAMS_NAMED, 'employmentcategory');
                $generalwhere .= "GRADE {$sqldata}";
                $params += $sqlparams;
            }
            if ($notsetwhere && $generalwhere) {
                $where .= " AND ({$notsetwhere} OR {$generalwhere})";
            } else {
                // Only one of two will be set...
                $where .= " AND {$notsetwhere}{$generalwhere}";
            }
        }

        // Do we need to flip view?
        if (defined('BLOCK_CERTIFICATION_REPORT_EXPORT') && BLOCK_CERTIFICATION_REPORT_EXPORT && optional_param('exportview', '', PARAM_ALPHA) === 'users') {
            $view = 'users';
            // Raise limits as could be big...
            \core_php_time_limit::raise();
            raise_memory_limit(MEMORY_HUGE);
        }

        /**
         * Prepare query basing on view type
         */
        $groupby = '';
        $orderby = "ORDER by {$regionfield}, costcentre";
        $fields = "
              cua.id,
              cua.userid,
              cua.certifid,
              u.firstname,
              u.lastname,
              u.email,
              CASE WHEN (h.REGION_NAME IS NULL OR h.REGION_NAME = '') THEN '-1' ELSE h.REGION_NAME END as actualregion,
              CASE WHEN (h.GEO_NAME IS NULL OR h.GEO_NAME = '') THEN '-1' ELSE h.GEO_NAME END as georegion,
              CASE WHEN u.icq = '' THEN '-1' ELSE u.icq END as costcentre,
              ce.id as exemptionid,
              cc.duedate,
              cc.progress,
              cc.timeexpires,
              cc.timecompleted,
              (CASE WHEN cc.timecompleted > 0 THEN cc.timecompleted WHEN cca.timecompleted IS NOT NULL THEN cca.timecompleted ELSE 0 END) as lasttimecompleted
              ";

        if ($view == 'regions') {
            $groupby = "GROUP BY h.{$regionhubfield}, cua.certifid ";
            $groupbycompliant = "GROUP BY u.{$regionfield} ";
            $fieldscompliantid = "CASE WHEN (u.{$regionfield} IS NULL OR u.{$regionfield} = '') THEN '-1' ELSE u.{$regionfield} END ";
            $fieldscompliantname =  "CASE WHEN (u.{$regionfield} IS NULL OR u.{$regionfield} = '') THEN 'NOT SET' ELSE u.{$regionfield} END ";
            $orderby = "ORDER BY h.{$regionhubfield} ";
            $fields = "
                cua.certifid,
                CASE WHEN (h.{$regionhubfield} IS NULL OR h.{$regionhubfield} = '') THEN '-1' ELSE h.{$regionhubfield} END as itemid,
                CASE WHEN (h.{$regionhubfield} IS NULL OR h.{$regionhubfield} = '') THEN 'NOT SET' ELSE h.{$regionhubfield} END as itemname,
                SUM(1) as alluserscounter,
                SUM(
                    CASE WHEN
                        (
                            cc.timecompleted > 0
                            AND
                            (
                                cc.timeexpires = 0
                                OR cc.timeexpires > :timeexpires1
                            )
                        )
                        OR
                        (
                            cca.timecompleted > 0
                            AND
                            (
                                cca.timeexpires = 0
                                OR cca.timeexpires > :timeexpires2
                            )
                        )
                    THEN 100
                    ELSE 0
                    END
                ) as allprogresssum,
                SUM(
                    CASE WHEN
                        cua.optional = 0
                        AND ce.id IS NULL
                    THEN 1
                    ELSE 0
                    END
                ) as userscounter,
                SUM(
                    CASE WHEN
                        cua.optional = 1
                        OR ce.id IS NOT NULL
                    THEN 0
                    WHEN
                        (
                            cc.timecompleted > 0
                            AND
                            (
                                cc.timeexpires = 0
                                OR cc.timeexpires > :timeexpires3
                            )
                        )
                        OR
                        (
                            cca.timecompleted > 0
                            AND
                            (
                                cca.timeexpires = 0
                                OR cca.timeexpires > :timeexpires4
                            )
                        )
                    THEN 100
                    ELSE 0
                    END
                ) as progresssum,
                SUM(
                    CASE WHEN
                        ce.id IS NOT NULL
                    THEN 1
                    ELSE 0
                    END
                ) as exemptuserscounter,
                SUM(
                    CASE WHEN
                        ce.id IS NULL
                        THEN 0
                        WHEN
                            (
                            cc.timecompleted > 0
                            AND
                            (
                                cc.timeexpires = 0
                                OR cc.timeexpires > :timeexpires5
                            )
                        )
                        OR
                        (
                            cca.timecompleted > 0
                            AND
                            (
                                cca.timeexpires = 0
                                OR cca.timeexpires > :timeexpires6
                            )
                        )
                    THEN 100
                    ELSE 0
                    END
                ) as exemptprogresssum,
                SUM(
                    CASE WHEN
                        cua.optional = 1
                        AND ce.id IS NULL
                    THEN 1
                    ELSE 0
                    END
                ) as optionaluserscounter,
                SUM(
                    CASE WHEN
                        cua.optional = 0
                        OR ce.id IS NOT NULL
                        THEN 0
                        WHEN
                            (
                            cc.timecompleted > 0
                            AND
                            (
                                cc.timeexpires = 0
                                OR cc.timeexpires > :timeexpires7
                            )
                        )
                        OR
                        (
                            cca.timecompleted > 0
                            AND
                            (
                                cca.timeexpires = 0
                                OR cca.timeexpires > :timeexpires8
                            )
                        )
                    THEN 100
                    ELSE 0
                    END
                ) as optionalprogresssum
            ";
            $params['timeexpires1'] = $now;
            $params['timeexpires2'] = $now;
            $params['timeexpires3'] = $now;
            $params['timeexpires4'] = $now;
            $params['timeexpires5'] = $now;
            $params['timeexpires6'] = $now;
            $params['timeexpires7'] = $now;
            $params['timeexpires8'] = $now;
        } else if ($view == 'costcentre') {
            $groupby = 'GROUP BY u.icq, cua.certifid ';
            $groupbycompliant = 'GROUP BY u.icq ';
            $fieldscompliantid = "CASE WHEN u.icq = '' THEN '-1' ELSE u.icq END ";
            $fieldscompliantname = "CASE WHEN u.icq = '' THEN 'NOT SET' ELSE u.icq END ";
            $orderby = 'ORDER by u.icq ';
            $fields = "
                cua.certifid,
                CASE WHEN u.icq = '' THEN '-1' ELSE u.icq END as itemid,
                CASE WHEN u.icq = '' THEN 'NOT SET' ELSE u.icq END as itemname,
                SUM(1) as alluserscounter,
                SUM(
                    CASE WHEN
                        (
                            cc.timecompleted > 0
                            AND
                            (
                                cc.timeexpires = 0
                                OR cc.timeexpires > :timeexpires1
                            )
                        )
                        OR
                        (
                            cca.timecompleted > 0
                            AND
                            (
                                cca.timeexpires = 0
                                OR cca.timeexpires > :timeexpires2
                            )
                        )
                    THEN 100
                    ELSE 0
                    END
                ) as allprogresssum,
                SUM(
                    CASE WHEN
                        cua.optional = 0
                        AND ce.id IS NULL
                    THEN 1
                    ELSE 0
                    END
                ) as userscounter,
                SUM(
                    CASE WHEN
                        cua.optional = 1
                        OR ce.id IS NOT NULL
                    THEN 0
                    WHEN
                        (
                            cc.timecompleted > 0
                            AND
                            (
                                cc.timeexpires = 0
                                OR cc.timeexpires > :timeexpires3
                            )
                        )
                        OR
                        (
                            cca.timecompleted > 0
                            AND
                            (
                                cca.timeexpires = 0
                                OR cca.timeexpires > :timeexpires4
                            )
                        )
                    THEN 100
                    ELSE 0
                    END
                ) as progresssum,
                SUM(
                    CASE WHEN
                        ce.id IS NOT NULL
                    THEN 1
                    ELSE 0
                    END
                ) as exemptuserscounter,
                SUM(
                    CASE WHEN
                        ce.id IS NULL
                        THEN 0
                        WHEN
                            (
                            cc.timecompleted > 0
                            AND
                            (
                                cc.timeexpires = 0
                                OR cc.timeexpires > :timeexpires5
                            )
                        )
                        OR
                        (
                            cca.timecompleted > 0
                            AND
                            (
                                cca.timeexpires = 0
                                OR cca.timeexpires > :timeexpires6
                            )
                        )
                    THEN 100
                    ELSE 0
                    END
                ) as exemptprogresssum,
                SUM(
                    CASE WHEN
                        cua.optional = 1
                        AND ce.id IS NULL
                    THEN 1
                    ELSE 0
                    END
                ) as optionaluserscounter,
                SUM(
                    CASE WHEN
                        cua.optional = 0
                        OR ce.id IS NOT NULL
                        THEN 0
                        WHEN
                            (
                            cc.timecompleted > 0
                            AND
                            (
                                cc.timeexpires = 0
                                OR cc.timeexpires > :timeexpires7
                            )
                        )
                        OR
                        (
                            cca.timecompleted > 0
                            AND
                            (
                                cca.timeexpires = 0
                                OR cca.timeexpires > :timeexpires8
                            )
                        )
                    THEN 100
                    ELSE 0
                    END
                ) as optionalprogresssum
            ";
            $params['timeexpires1'] = $now;
            $params['timeexpires2'] = $now;
            $params['timeexpires3'] = $now;
            $params['timeexpires4'] = $now;
            $params['timeexpires5'] = $now;
            $params['timeexpires6'] = $now;
            $params['timeexpires7'] = $now;
            $params['timeexpires8'] = $now;
        } else if ($view == 'users') {
            $groupby = '';
            $orderby = 'ORDER by u.icq, u.lastname, u.firstname';
            $fields = "
                cua.userid,
                cua.certifid,
                u.firstname,
                u.lastname,
                u.idnumber,
                u.email,
                h.REGION_NAME as actualregion,
                h.GEO_REGION as georegion,
                CASE WHEN u.icq = '' THEN '-1' ELSE u.icq END as costcentre,
                ce.id as exemptionid,
                cc.duedate,
                cc.progress,
                cc.timecompleted,
                cua.optional,
                (CASE WHEN cc.timecompleted > 0 THEN cc.timecompleted WHEN cca.timecompleted IS NOT NULL THEN cca.timecompleted ELSE 0 END) as lasttimecompleted,
                (CASE WHEN cc.timeexpires > 0 THEN cc.timeexpires WHEN cca.timeexpires IS NOT NULL THEN cca.timeexpires ELSE 0 END) as timeexpires,
                cca.timewindowsopens as lasttimewindowsopens,
                h.GRADE as grade,
                h.GROUP_NAME as groupname,
                h.DISCIPLINE_NAME as 'disciplinename',
                h.LOCATION_NAME as locationname,
                h.EMPLOYMENT_CATEGORY as employmentcategory
            ";
        }
        // Join to hub table.
        $castidnumber = $DB->sql_cast_char2int('u.idnumber');
        $join .= "
            LEFT JOIN SQLHUB.ARUP_ALL_STAFF_V h ON h.EMPLOYEE_NUMBER = {$castidnumber}
        ";

        $query = "
            SELECT
              ".$fields."
            FROM {certif_user_assignments} cua
            JOIN {user} u ON u.id = cua.userid
            LEFT JOIN {certif_completions} cc ON cc.userid = cua.userid AND cc.certifid = cua.certifid
            LEFT JOIN {certif_exemptions} ce ON ce.userid = cua.userid AND ce.certifid = cua.certifid AND ce.archived = :archived AND (ce.timeexpires = 0 OR ce.timeexpires > :now)
            LEFT JOIN (
                SELECT
                  cca.userid,
                  cca.certifid,
                  MAX(cca.timecompleted) as timecompleted,
                  MAX(cca.timeexpires) as timeexpires,
                  MAX(cca.timewindowsopens) as timewindowsopens
                FROM {certif_completions_archive} cca
                GROUP BY cca.userid, cca.certifid
            ) cca ON cca.userid = cua.userid AND cca.certifid = cua.certifid
            ".$join."
            WHERE u.suspended = 0 AND u.deleted = 0 AND cua.certifid ".$insql."
            ".$where."
            ".$groupby."
            ".$orderby."

        ";

        $params['now'] = $now;
        $params['archived'] = 0;

        $completiondata = $DB->get_recordset_sql($query, $params);

        /**
         * Get information about compliant users
         */
        if($view != 'users'){
            $query = "
                SELECT
                  ".$fieldscompliantid." as itemid,
                  ".$fieldscompliantname." as itemname,
                  SUM(u.users) as users,
                  SUM(u.compliant) as compliant,
                  SUM(u.optionalusers) as optionalusers,
                  SUM(u.optionalcompliant) as optionalcompliant,
                  SUM(u.exemptusers) as exemptusers,
                  SUM(u.exemptcompliant) as exemptcompliant,
                  SUM(u.users) + SUM(u.optionalusers) + SUM(u.exemptusers) as allusers,
                  SUM(u.compliant) + SUM(u.optionalcompliant) + SUM(u.exemptcompliant) as allcompliant
                FROM
                (
                    SELECT
                        u.id,
                        u.icq,
                        h.{$regionhubfield} as {$regionfield},
                        SUM(
                            CASE WHEN ce.id IS NULL AND cua.optional = 0
                            THEN 1
                            ELSE 0
                            END
                        ) as users,
                        SUM(
                            CASE WHEN ce.id IS NULL AND cua.optional = 1
                            THEN 1
                            ELSE 0
                            END
                        ) as optionalusers,
                        SUM(
                            CASE WHEN ce.id IS NOT NULL
                            THEN 1
                            ELSE 0
                            END
                        ) as exemptusers,
                        SUM(
                            CASE WHEN
                                ce.id IS NULL
                                AND cua.optional = 0
                                AND
                                (
                                    (
                                        cc.timecompleted > 0
                                        AND
                                        (
                                            cc.timeexpires = 0 OR cc.timeexpires > :timeexpires1
                                        )
                                    )
                                    OR
                                    (
                                        cca.timecompleted > 0
                                        AND
                                        (
                                            cca.timeexpires = 0 OR cca.timeexpires > :timeexpires2
                                        )
                                    )
                                )
                            THEN 1
                            ELSE 0
                            END
                        ) as compliant,
                        SUM(
                            CASE WHEN
                                ce.id IS NULL
                                AND cua.optional = 1
                                AND
                                (
                                    (
                                        cc.timecompleted > 0
                                        AND
                                        (
                                            cc.timeexpires = 0 OR cc.timeexpires > :timeexpires3
                                        )
                                    )
                                    OR
                                    (
                                        cca.timecompleted > 0
                                        AND
                                        (
                                            cca.timeexpires = 0 OR cca.timeexpires > :timeexpires4
                                        )
                                    )
                                )
                            THEN 1
                            ELSE 0
                            END
                        ) as optionalcompliant,
                        SUM(
                            CASE WHEN
                                ce.id IS NOT NULL
                                AND
                                (
                                    (
                                        cc.timecompleted > 0
                                        AND
                                        (
                                            cc.timeexpires = 0 OR cc.timeexpires > :timeexpires5
                                        )
                                    )
                                    OR
                                    (
                                        cca.timecompleted > 0
                                        AND
                                        (
                                            cca.timeexpires = 0 OR cca.timeexpires > :timeexpires6
                                        )
                                    )
                                )
                            THEN 1
                            ELSE 0
                            END
                        ) as exemptcompliant
                    FROM {certif_user_assignments} cua
                    JOIN {user} u ON u.id = cua.userid
                    LEFT JOIN {certif_exemptions} ce ON ce.userid = cua.userid AND ce.certifid = cua.certifid AND ce.archived = :archived AND (ce.timeexpires = 0 OR ce.timeexpires > :now)
                    LEFT JOIN {certif_completions} cc ON cc.userid = cua.userid AND cc.certifid = cua.certifid
                    LEFT JOIN (
                        SELECT
                          cca.userid,
                          cca.certifid,
                          MAX(cca.timecompleted) as timecompleted,
                          MAX(cca.timeexpires) as timeexpires
                        FROM {certif_completions_archive} cca
                        GROUP BY cca.userid, cca.certifid
                    ) cca ON cca.userid = cua.userid AND cca.certifid = cua.certifid
                    " . $join . "
                    WHERE u.suspended = 0 AND u.deleted = 0 AND cua.certifid " . $insql . "
                    " . $where . "
                    GROUP BY u.id, h.{$regionhubfield}, u.icq
                ) u
                ".$groupbycompliant."
            ";
            $compliantdata = $DB->get_records_sql($query, $params);
        }

        $data = [];
        $data['viewtotal'] = [
            'certifications' => [],
            'name' => 'Total'
        ];
        foreach($certifications as $certification){
            $data['viewtotal']['certifications'][$certification->id] = [
                /**
                 * Progress for required (non-optional, not exempt) assignments.
                 */
                'progress' => null,
                'progresssum' => 0,
                'userscounter' => 0,
                /**
                 * Progress for all (including optional and exempt) assignments.
                 */
                'allprogress' => 0,
                'allprogresssum' => 0,
                'alluserscounter' => 0,
                /**
                 * Progress for optional assignments.
                 */
                'optionalprogress' => 0,
                'optionalprogresssum' => 0,
                'optionaluserscounter' => 0,
                /**
                 * Progress for exempt assignments.
                 */
                'exemptprogress' => 0,
                'exemptprogresssum' => 0,
                'exemptuserscounter' => 0,
                /**
                 * Is this overall (everyone) optional?
                 */
                'optional' => 0,
                /**
                 * Is this overall (everyone) exempt?
                 */
                'exempt' => 0,
            ];
        }

        if ($view == 'costcentre') {
            // Get costcentre names.
            $ccs = self::get_costcentre_names();
        }

        foreach($completiondata as $compldata){
            if($view == 'regions' || $view == 'costcentre'){
                if(!isset($data[$compldata->itemid])){
                    $data[$compldata->itemid] = [
                        'certifications' => [],
                        'name' => $compldata->itemname,
                        'fullname' => $compldata->itemname,
                        'users' => 0,
                        'progress' => null,
                        'allusers' => 0,
                        'allprogress' => 0,
                        'optionalusers' => 0,
                        'optionalprogress' => 0,
                        'exemptusers' => 0,
                        'exemptprogress' => 0,
                    ];
                    if ($view == 'costcentre' && !empty($ccs[$compldata->itemid])) {
                        $data[$compldata->itemid]['fullname'] = $ccs[$compldata->itemid];
                    }
                    foreach($certifications as $certification){
                        $data[$compldata->itemid]['certifications'][$certification->id] = [
                            /**
                             * Progress for required (non-optional, not exempt) assignments.
                             */
                            'progress' => null,
                            'userscounter' => 0,
                            /**
                             * Progress for all (including optional and exempt) assignments.
                             */
                            'allprogress' => 0,
                            'alluserscounter' => 0,
                            /**
                             * Progress for optional assignments.
                             */
                            'optionalprogress' => 0,
                            'optionaluserscounter' => 0,
                            /**
                             * Progress for exempt assignments.
                             */
                            'exemptprogress' => 0,
                            'exemptuserscounter' => 0,
                            /**
                             * Is this overall (everyone) optional?
                             */
                            'optional' => 0,
                            /**
                             * Is this overall (everyone) exempt?
                             */
                            'exempt' => 0,
                        ];
                    }
                }

                $data[$compldata->itemid]['certifications'][$compldata->certifid]['progress'] = $compldata->userscounter ? round($compldata->progresssum / $compldata->userscounter) : null;
                $data[$compldata->itemid]['certifications'][$compldata->certifid]['userscounter'] = $compldata->userscounter;
                $data[$compldata->itemid]['certifications'][$compldata->certifid]['allprogress'] = round($compldata->allprogresssum / MAX(1, $compldata->alluserscounter));
                $data[$compldata->itemid]['certifications'][$compldata->certifid]['alluserscounter'] = $compldata->alluserscounter;
                $data[$compldata->itemid]['certifications'][$compldata->certifid]['optionalprogress'] = round($compldata->optionalprogresssum / MAX(1, $compldata->optionaluserscounter));
                $data[$compldata->itemid]['certifications'][$compldata->certifid]['optionaluserscounter'] = $compldata->optionaluserscounter;
                $data[$compldata->itemid]['certifications'][$compldata->certifid]['exemptprogress'] = round($compldata->exemptprogresssum / MAX(1, $compldata->exemptuserscounter));
                $data[$compldata->itemid]['certifications'][$compldata->certifid]['exemptuserscounter'] = $compldata->exemptuserscounter;

                $data['viewtotal']['certifications'][$compldata->certifid]['progresssum'] += $compldata->progresssum;
                $data['viewtotal']['certifications'][$compldata->certifid]['userscounter'] += $compldata->userscounter;
                $data['viewtotal']['certifications'][$compldata->certifid]['progress'] = round($data['viewtotal']['certifications'][$compldata->certifid]['progresssum'] / MAX(1, $data['viewtotal']['certifications'][$compldata->certifid]['userscounter']));

                $data['viewtotal']['certifications'][$compldata->certifid]['allprogresssum'] += $compldata->allprogresssum;
                $data['viewtotal']['certifications'][$compldata->certifid]['alluserscounter'] += $compldata->alluserscounter;
                $data['viewtotal']['certifications'][$compldata->certifid]['allprogress'] = round($data['viewtotal']['certifications'][$compldata->certifid]['allprogresssum'] / MAX(1, $data['viewtotal']['certifications'][$compldata->certifid]['alluserscounter']));

                $data['viewtotal']['certifications'][$compldata->certifid]['optionalprogresssum'] += $compldata->optionalprogresssum;
                $data['viewtotal']['certifications'][$compldata->certifid]['optionaluserscounter'] += $compldata->optionaluserscounter;
                $data['viewtotal']['certifications'][$compldata->certifid]['optionalprogress'] = round($data['viewtotal']['certifications'][$compldata->certifid]['optionalprogresssum'] / MAX(1, $data['viewtotal']['certifications'][$compldata->certifid]['optionaluserscounter']));

                $data['viewtotal']['certifications'][$compldata->certifid]['exemptprogresssum'] += $compldata->exemptprogresssum;
                $data['viewtotal']['certifications'][$compldata->certifid]['exemptuserscounter'] += $compldata->exemptuserscounter;
                $data['viewtotal']['certifications'][$compldata->certifid]['exemptprogress'] = round($data['viewtotal']['certifications'][$compldata->certifid]['exemptprogresssum'] / MAX(1, $data['viewtotal']['certifications'][$compldata->certifid]['exemptuserscounter']));

                if ($compldata->optionaluserscounter > 0 && $compldata->userscounter == 0 && $compldata->exemptuserscounter == 0) {
                    $data[$compldata->itemid]['certifications'][$compldata->certifid]['optional'] = 1;
                } else {
                    $data[$compldata->itemid]['certifications'][$compldata->certifid]['optional'] = 0;
                }
                if ($compldata->exemptuserscounter > 0 && $compldata->userscounter == 0 && $compldata->optionaluserscounter == 0) {
                    $data[$compldata->itemid]['certifications'][$compldata->certifid]['exempt'] = 1;
                } else {
                    $data[$compldata->itemid]['certifications'][$compldata->certifid]['exempt'] = 0;
                }

                if ($data['viewtotal']['certifications'][$compldata->certifid]['optionaluserscounter'] > 0
                        && $data['viewtotal']['certifications'][$compldata->certifid]['userscounter'] === 0
                        && $data['viewtotal']['certifications'][$compldata->certifid]['exemptuserscounter'] === 0) {
                    $data['viewtotal']['certifications'][$compldata->certifid]['optional'] = 1;
                } else {
                    $data['viewtotal']['certifications'][$compldata->certifid]['optional'] = 0;
                }
                if ($data['viewtotal']['certifications'][$compldata->certifid]['exemptuserscounter'] > 0
                        && $data['viewtotal']['certifications'][$compldata->certifid]['userscounter'] === 0
                        && $data['viewtotal']['certifications'][$compldata->certifid]['optionaluserscounter'] === 0) {
                    $data['viewtotal']['certifications'][$compldata->certifid]['exempt'] = 1;
                } else {
                    $data['viewtotal']['certifications'][$compldata->certifid]['exempt'] = 0;
                }
            } else if ($view == 'users') {
                /**
                 * Prepare empty users
                 */
                if(!isset($data[$compldata->userid])){
                    $data[$compldata->userid] = [
                        'certifications' => [],
                        'userdata' => $compldata,
                        'count' => 0,
                        'progress' => null,
                        'progresssum' => 0,
                        'allcount' => 0,
                        'allprogress' => 0,
                        'allprogresssum' => 0,
                        'optionalcount' => 0,
                        'optionalprogress' => 0,
                        'optionalprogresssum' => 0,
                        'exemptcount' => 0,
                        'exemptprogress' => 0,
                        'exemptprogresssum' => 0,
                    ];
                    foreach($certifications as $certification){
                        $data[$compldata->userid]['certifications'][$certification->id] = [
                            'completiondate'=> null,
                            'duedate'=> null,
                            'optional' => 0,
                            'exemptionid' => 0,
                            'progress' => null,
                            'ragstatus' => completion::RAG_STATUS_RED,
                            'currentcompletiondate' => null
                        ];
                    }
                }
                /**
                 * Add user progress data
                 */
                // Complete if not expired (or doesn't expire).
                if ($compldata->lasttimecompleted > 0 && ($compldata->timeexpires > time() || $compldata->timeexpires == 0)) {
                    $progress = 100;
                } else {
                    $progress = $compldata->progress;
                }
                // Binary progress used to calculate overall totals (either complete or not).
                $binaryprogress = ($progress == 100 ? 100 : 0);
                $data[$compldata->userid]['certifications'][$compldata->certifid] = [
                    'duedate' => $compldata->duedate,
                    'completiondate'=> $compldata->lasttimecompleted,
                    'progress' => $progress,
                    'optional' => $compldata->optional,
                    'exemptionid' => $compldata->exemptionid,
                    'ragstatus' => completion::get_rag_status($compldata->timecompleted, $compldata->lasttimecompleted, $compldata->duedate, $compldata->lasttimewindowsopens, $compldata->progress, $compldata->optional, 1),
                    'currentcompletiondate' => $compldata->timecompleted
                ];

                $data[$compldata->userid]['allprogresssum'] += $binaryprogress;
                $data[$compldata->userid]['allcount']++;
                $data[$compldata->userid]['allprogress'] = round($data[$compldata->userid]['allprogresssum'] / MAX(1,$data[$compldata->userid]['allcount']));
                $data['viewtotal']['certifications'][$compldata->certifid]['allprogresssum'] += $binaryprogress;
                $data['viewtotal']['certifications'][$compldata->certifid]['alluserscounter'] ++;
                $data['viewtotal']['certifications'][$compldata->certifid]['allprogress'] = round($data['viewtotal']['certifications'][$compldata->certifid]['allprogresssum'] / MAX(1, $data['viewtotal']['certifications'][$compldata->certifid]['alluserscounter']));

                if (!$compldata->optional && !$compldata->exemptionid) {
                    $data[$compldata->userid]['progresssum'] += $binaryprogress;
                    $data[$compldata->userid]['count']++;
                    $data[$compldata->userid]['progress'] = round($data[$compldata->userid]['progresssum'] / MAX(1,$data[$compldata->userid]['count']));
                    $data['viewtotal']['certifications'][$compldata->certifid]['progresssum'] += $binaryprogress;
                    $data['viewtotal']['certifications'][$compldata->certifid]['userscounter'] ++;
                    $data['viewtotal']['certifications'][$compldata->certifid]['progress'] = round($data['viewtotal']['certifications'][$compldata->certifid]['progresssum'] / MAX(1, $data['viewtotal']['certifications'][$compldata->certifid]['userscounter']));
                }

                if ($compldata->optional && !$compldata->exemptionid) {
                    $data[$compldata->userid]['optionalprogresssum'] += $binaryprogress;
                    $data[$compldata->userid]['optionalcount']++;
                    $data[$compldata->userid]['optionalprogress'] = round($data[$compldata->userid]['optionalprogresssum'] / MAX(1,$data[$compldata->userid]['optionalcount']));
                    $data['viewtotal']['certifications'][$compldata->certifid]['optionalprogresssum'] += $binaryprogress;
                    $data['viewtotal']['certifications'][$compldata->certifid]['optionaluserscounter'] ++;
                    $data['viewtotal']['certifications'][$compldata->certifid]['optionalprogress'] = round($data['viewtotal']['certifications'][$compldata->certifid]['optionalprogresssum'] / MAX(1, $data['viewtotal']['certifications'][$compldata->certifid]['optionaluserscounter']));
                }

                if ($compldata->exemptionid) {
                    $data[$compldata->userid]['exemptprogresssum'] += $binaryprogress;
                    $data[$compldata->userid]['exemptcount']++;
                    $data[$compldata->userid]['exemptprogress'] = round($data[$compldata->userid]['exemptprogresssum'] / MAX(1,$data[$compldata->userid]['exemptcount']));
                    $data['viewtotal']['certifications'][$compldata->certifid]['exemptprogresssum'] += $binaryprogress;
                    $data['viewtotal']['certifications'][$compldata->certifid]['exemptuserscounter'] ++;
                    $data['viewtotal']['certifications'][$compldata->certifid]['exemptprogress'] = round($data['viewtotal']['certifications'][$compldata->certifid]['exemptprogresssum'] / MAX(1, $data['viewtotal']['certifications'][$compldata->certifid]['exemptuserscounter']));
                }

                if ($data['viewtotal']['certifications'][$compldata->certifid]['optionaluserscounter'] > 0
                        && $data['viewtotal']['certifications'][$compldata->certifid]['userscounter'] === 0
                        && $data['viewtotal']['certifications'][$compldata->certifid]['exemptuserscounter'] === 0) {
                    $data['viewtotal']['certifications'][$compldata->certifid]['optional'] = 1;
                } else {
                    $data['viewtotal']['certifications'][$compldata->certifid]['optional'] = 0;
                }
                if ($data['viewtotal']['certifications'][$compldata->certifid]['exemptuserscounter'] > 0
                        && $data['viewtotal']['certifications'][$compldata->certifid]['userscounter'] === 0
                        && $data['viewtotal']['certifications'][$compldata->certifid]['optionaluserscounter'] === 0) {
                    $data['viewtotal']['certifications'][$compldata->certifid]['exempt'] = 1;
                } else {
                    $data['viewtotal']['certifications'][$compldata->certifid]['exempt'] = 0;
                }
            }
        }

        if ($view != 'users'){
            foreach($data as $itemid => $itemdata){
                foreach($itemdata['certifications'] as $certificationid => $certification){
                    /**
                     * If certification is marked as optional or exempt (no 'required' assignments) force to 100%.
                     */
                    if ($data[$itemid]['certifications'][$certificationid]['optional'] == 1 || $data[$itemid]['certifications'][$certificationid]['exempt'] == 1){
                        $data[$itemid]['certifications'][$certificationid]['progress'] = 100;
                    }
                }
                if (isset($compliantdata[$itemid])){
                    $data[$itemid]['users'] = $compliantdata[$itemid]->users;
                    $data[$itemid]['allusers'] = $compliantdata[$itemid]->allusers;
                    $data[$itemid]['allprogress'] = round($compliantdata[$itemid]->allcompliant / MAX(1, $compliantdata[$itemid]->allusers) * 100);
                    $data[$itemid]['optionalusers'] = $compliantdata[$itemid]->optionalusers;
                    $data[$itemid]['optionalprogress'] = round($compliantdata[$itemid]->optionalcompliant / MAX(1, $compliantdata[$itemid]->optionalusers) * 100);
                    $data[$itemid]['exemptusers'] = $compliantdata[$itemid]->exemptusers;
                    $data[$itemid]['exemptprogress'] = round($compliantdata[$itemid]->exemptcompliant / MAX(1, $compliantdata[$itemid]->exemptusers) * 100);
                    // Force to 100% if no users need to be compliant.
                    if ($compliantdata[$itemid]->users) {
                        $data[$itemid]['progress'] = round($compliantdata[$itemid]->compliant / $compliantdata[$itemid]->users * 100);
                    } else if (!$compliantdata[$itemid]->allusers) {
                        $data[$itemid]['progress'] = null;
                    } else {
                        $data[$itemid]['progress'] = 100;
                    }
                }
            }
        } else {
            foreach($data as $itemid => $itemdata){
                if ($itemid === 'viewtotal') {
                    foreach ($itemdata['certifications'] as $certificationid => $certification) {
                        /**
                        * If certification is marked as optional or exempt (no 'required' assignments) force to 100%.
                        */
                       if ($data['viewtotal']['certifications'][$certificationid]['optional'] == 1 || $data['viewtotal']['certifications'][$certificationid]['exempt'] == 1){
                           $data['viewtotal']['certifications'][$certificationid]['progress'] = 100;
                        } else if (!$data['viewtotal']['certifications'][$certificationid]['alluserscounter']) {
                            $data['viewtotal']['certifications'][$certificationid]['progress'] = null;
                        }
                    }
                }
                // Only process for user data entries that are entirely optional or exempt.
                if ($itemid === (int) $itemid && $itemdata['count'] === 0) {
                    $data[$itemid]['progress'] = 100;
                }
            }
        }

        return ['view' => $view, 'data' => $data];
    }

    /**
     * Get regions actual/geographic
     *
     * @return array regions
     */
    public static function get_local_regions() {
        global $DB;

        $regions = [
            'actual' => [],
            'geographic' => []
        ];

        if ($records = $DB->get_records('local_regions_reg')) {
            foreach ($records as $record) {
                if ($record->userselectable == 1) {
                    $regions['geographic'][$record->id] = $record->name;
                }
                $regions['actual'][$record->id] = $record->name;
            }
        }

        return $regions;

    }

    public static function get_report_links() {
        global $DB;

        $query = "
            SELECT
                cl.id,
                cl.name,
                cl.linkurl,
                cl.geographicregionid,
                cl.actualregionid,
                lrr1.name as geographicregion,
                lrr2.name as actualregion,
                cl.timecreated
            FROM mdl_certif_links cl
            LEFT JOIN mdl_local_regions_reg lrr1 ON cl.geographicregionid = lrr1.id
            LEFT JOIN mdl_local_regions_reg lrr2 ON cl.actualregionid = lrr2.id
            ORDER BY cl.timecreated DESC";

        return $DB->get_records_sql($query);
    }

    public static function get_costcentre_names() {
        global $DB;
        $like = $DB->sql_like('icq', ':icq', false, false);
        $sql = "
            SELECT
                DISTINCT(icq)
            FROM {user}
            WHERE
                {$like}
            ORDER BY
                icq ASC";

        $ccs = $DB->get_records_sql($sql, ['icq' => '__-___']);

        $return = [];
        foreach($ccs as $cc) {
            // Find the group name.
            $sql = "
                SELECT
                    u.department
                FROM
                    {user} u
                INNER JOIN
                    (SELECT
                        MAX(id) maxid
                    FROM
                        {user} inneru
                    INNER JOIN
                        (SELECT
                            MAX(timemodified) as maxtimemodified
                        FROM
                            {user}
                        WHERE
                            icq = :icq1
                        ) groupedicq
                        ON inneru.timemodified = groupedicq.maxtimemodified
                    WHERE
                        icq = :icq2
                    ) groupedid
                    ON u.id = groupedid.maxid
                WHERE
                    u.icq = :icq3";
            $params = array_fill_keys(array('icq1', 'icq2', 'icq3'), $cc->icq);
            $ccname = $DB->get_field_sql($sql, $params);
            $return[$cc->icq] = $cc->icq.' - ' . $ccname;
        }
        return $return;
    }

    /**
     * Get exemption data for single user and certification
     * @param $userid
     * @param $certifid
     */
    public static function get_exemption($userid, $certifid){
        global $DB;

        $query = "
            SELECT
              ce.*,
              ".$DB->sql_fullname('u.firstname', 'u.lastname')." as modifier
            FROM {certif_exemptions} ce
            JOIN {user} u ON u.id = ce.modifierid
            WHERE ce.userid = :userid
            AND ce.certifid = :certifid
            AND ce.archived = :archived
            AND (ce.timeexpires > :now OR ce.timeexpires = 0)
        ";

        return $DB->get_record_sql($query, ['userid' => $userid, 'certifid' => $certifid, 'archived' => 0, 'now' => time()], IGNORE_MULTIPLE);
    }

    /**
     * Remove certification exemption
     * @param $userid
     * @param $certifid
     */
    public static function delete_exemption($userid, $certifid){
        global $DB;
        return $DB->delete_records('certif_exemptions', ['userid' => $userid, 'certifid' => $certifid]);
    }

    /**
     * Save exemption
     * @param $userid
     * @param $certifid
     * @param $reason
     * @param $timeexpires
     */
    public static function save_exemption($userid, $certifid, $reason, $timeexpires, $archived = 0){
        global $DB, $USER;

        if($record = $DB->get_record('certif_exemptions', ['userid' => $userid, 'certifid' => $certifid, 'archived' => 0])){
            $record->archived = 1;
            $record->timemodified = time();
            $DB->update_record('certif_exemptions', $record);
        }

        $record = new \stdClass();
        $record->userid = $userid;
        $record->modifierid = $USER->id;
        $record->certifid = $certifid;
        $record->reason = $reason;
        $record->timeexpires = ($timeexpires != '' ? strtotime($timeexpires) : 0);
        $record->timecreated = time();
        $record->timemodified = time();
        $record->archived = $archived;
        $DB->insert_record('certif_exemptions', $record);
    }

    /**
     * Get rag status basing on progress for region / cost centre
     * @param $progress
     * @return string
     */
    public static function get_rag_status($progress){
        $config = get_config('block_certification_report');
        if($progress === null){
            return 'na';
        }elseif($progress >= $config->greenthreshold){
            return completion::RAG_STATUS_GREEN;
        }elseif($progress >= $config->amberthreshold){
            return completion::RAG_STATUS_AMBER;
        }else{
            return completion::RAG_STATUS_RED;
        }
    }

    /**
     * Export data to CSV
     *
     * @param $certifications
     * @param $data
     * @return mixed
     */
    public static function export_to_csv($filters, $filteroptions, $data, $view = 'regions') {
        global $CFG, $PAGE;

        require_once($CFG->libdir . '/csvlib.class.php');

        $lines = [];

        $usersheader = [];
        $usersheader[] = get_string('staffid', 'block_certification_report');
        $usersheader[] = get_string('fullnameuser');
        $usersheader[] = get_string('email');
        $usersheader[] = get_string('grade', 'block_certification_report');
        $usersheader[] = get_string('employmentcategory', 'block_certification_report');
        $usersheader[] = get_string('actualregion', 'block_certification_report');
        $usersheader[] = get_string('georegion', 'block_certification_report');
        $usersheader[] = get_string('costcentre', 'block_certification_report');
        $usersheader[] = get_string('discipline', 'block_certification_report');
        $usersheader[] = get_string('groupname', 'block_certification_report');
        $usersheader[] = get_string('locationname', 'block_certification_report');

        $header = [];
        $regionview = optional_param('regionview', 'actual', PARAM_ALPHA) == 'geo' ? 'geo' : 'actual';
        if ($view == 'regions') {
            $header[] = get_string('header'.$view.$regionview, 'block_certification_report');
        } else {
            $header[] = get_string('header'.$view, 'block_certification_report');
        }
        foreach($filteroptions['certificationsdata'] as $certification){
            if (isset($data['viewtotal']['certifications'][$certification->id]) && $data['viewtotal']['certifications'][$certification->id]['progress'] !== null) {
                $header[] = $certification->shortname
                        . ($data['viewtotal']['certifications'][$certification->id]['exempt'] == 1 ? "\n" . get_string('exempt', 'block_certification_report') : '')
                        . ($data['viewtotal']['certifications'][$certification->id]['optional'] == 1 ? "\n" . get_string('optional', 'block_certification_report') : '')
                        . "\n" . get_string('headercomplete', 'block_certification_report');
                $header[] = $certification->shortname
                        . ($data['viewtotal']['certifications'][$certification->id]['exempt'] == 1 ? "\n" . get_string('exempt', 'block_certification_report') : '')
                        . ($data['viewtotal']['certifications'][$certification->id]['optional'] == 1 ? "\n" . get_string('optional', 'block_certification_report') : '')
                        . "\n" . get_string('headertotal', 'block_certification_report');
                $usersheader[] = $certification->shortname . ' ' . get_string('progress', 'block_certification_report');
                $usersheader[] = $certification->shortname . ' ' . get_string('completiondate', 'block_certification_report');
            }
        }

        if ($view == 'users') {
            $lines[] = $usersheader;
            $ccs = \block_certification_report\certification_report::get_costcentre_names();
        } else {
            // Show filters on first line.
            $renderer = $PAGE->get_renderer('block_certification_report');
            $filterline = $renderer->show_active_filters($filters, $filteroptions, false);
            if (!empty($filterline)) {
                $lines[] = [$filterline];
            }
            $lines[] = $header;
        }

        foreach ($data as $itemname => $item) {
            if ($view == 'users' && $itemname == 'viewtotal') {
                continue;
            }
            $line = [];
            if ($view == 'users') {
                $line[] = $item['userdata']->idnumber;
                $line[] = $item['userdata']->firstname.' '.$item['userdata']->lastname;
                $line[] = $item['userdata']->email;
                $line[] = $item['userdata']->grade;
                $line[] = $item['userdata']->employmentcategory;
                $line[] = $item['userdata']->actualregion;
                $line[] = $item['userdata']->georegion;
                $costcentre = isset($ccs[$item['userdata']->costcentre]) ? $ccs[$item['userdata']->costcentre] : $item['userdata']->costcentre;
                $line[] = $costcentre == -1 ? '' : $costcentre;
                $line[] = $item['userdata']->disciplinename;
                $line[] = $item['userdata']->groupname;
                $line[] = $item['userdata']->locationname;
            } else {
                $line[] = isset($item['fullname']) ? $item['fullname'] : $item['name'];
            }
            foreach ($item['certifications'] as $certificationid => $certification) {
                if (isset($data['viewtotal']['certifications'][$certificationid]) && $data['viewtotal']['certifications'][$certificationid]['progress'] !== null) {
                    if (isset($certification['exemptionid']) && $certification['exemptionid'] > 0) {
                        $line[] = get_string('notrequired', 'block_certification_report');
                        $line[] = get_string('notrequired', 'block_certification_report');
                    } elseif ($view == 'users') {

                        if ($certification['progress'] === null) {
                            $line[] = get_string('na', 'block_certification_report');
                            $line[] = get_string('na', 'block_certification_report');
                        } else {
                            // Progress cell.
                            $line[] = $certification['progress'] . '%';
                            // Completion date cell.
                            if (empty($certification['completiondate'])) {
                                $line[] = '';
                            } else {
                                $line[] = strtoupper(userdate($certification['completiondate'], get_string('strftimedatefull', 'block_certification_report')));
                            }
                        }
                    } else {
                        $line[] = round(($certification['progress']/100) * $certification['userscounter']);
                        $line[] = $certification['userscounter'];
                    }
                }
            }
            $lines[] = $line;
        }
        return \csv_export_writer::print_array($lines, 'comma', '"', true);
    }

    public static function reset_certification($userid, $certifid) {
        if (!has_capability('block/certification_report:reset_certification', \context_system::instance())) {
            return;
        }
        $completionrecord = \local_custom_certification\completion::get_completion_info($certifid, $userid);
        if ($completionrecord && $completionrecord->timecompleted > 0) {
            $resetcourses = \local_custom_certification\completion::open_window($completionrecord);
            $activitycompletioncache = \cache::make('core', 'completion');
            $coursecompletioncache = \cache::make('core', 'coursecompletion');
            foreach ($resetcourses as $courseid) {
                $activitycompletioncache->delete("{$userid}_{$courseid}");
                $coursecompletioncache->delete("{$userid}_{$courseid}");
            }
        }
        return;
    }

    public static function can_view_report() {
        global $USER;
        // Check capabilities.
        if (has_any_capability(['block/certification_report:view_all_costcentres', 'block/certification_report:view_all_regions'], \context_system::instance())) {
            return true;
        }
        // Check cost centre permissions.
        if (costcentre::is_user($USER->id, [costcentre::GROUP_LEADER, costcentre::HR_LEADER, costcentre::HR_ADMIN, costcentre::LEARNING_REPORTER])) {
            return true;
        }
        // No permissions.
        return false;
    }

    public static function is_admin() {
        global $USER;
        // Check capabilities.
        if (has_capability('block/certification_report:administer', \context_system::instance())) {
            return true;
        }

        // No permissions.
        return false;
    }

    /**
     * This methods does weak url validation, we are looking for major problems only,
     * no strict RFE validation.
     *
     * @param $url
     * @return bool true is seems valid, false if definitely not valid URL
     */
    public static function url_appears_valid_url($url) {
        if (preg_match('/^(\/|https?:|ftp:)/i', $url)) {
            // note: this is not exact validation, we look for severely malformed URLs only
            return (bool)preg_match('/^[a-z]+:\/\/([^:@\s]+:[^@\s]+@)?[a-z0-9_\.\-]+(:[0-9]+)?(\/[^#]*)?(#.*)?$/i', $url);
        } else {
            return (bool)preg_match('/^[a-z]+:\/\/...*$/i', $url);
        }
    }

}
