<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Simon Coggins <simon.coggins@totaralms.com>
 * @author Alastair Munro <alastair.munro@totaralms.com>
 * @package mod_assign
 */

namespace rbsource_badgeissued;
use rb_base_source;
use rb_content_option;
use rb_join;
use rb_column_option;
use rb_filter_option;
use badge;

defined('MOODLE_INTERNAL') || die();

class source extends rb_base_source {

    public $base, $joinlist, $columnoptions, $filteroptions;
    public $contentoptions, $paramoptions, $defaultcolumns;
    public $defaultfilters, $requiredcolumns, $sourcetitle;

    public function __construct() {
        $this->base = '{badge_issued}';
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->contentoptions = $this->define_contentoptions();
        $this->paramoptions = $this->define_paramoptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->requiredcolumns = $this->define_requiredcolumns();
        $this->sourcetitle = get_string('sourcetitle', 'rbsource_badgeissued');

        parent::__construct();
    }

    /**
     * Hide this source if feature disabled or hidden.
     * @return bool
     */
    public function is_ignored() {
        global $CFG;
        return empty($CFG->enablebadges);
    }

    /**
     * Global report restrictions are implemented in this source.
     * @return boolean
     */
    public function global_restrictions_supported() {
        return true;
    }

    //
    //
    // Methods for defining contents of source.
    //
    //

    protected function define_joinlist() {
        $joinlist = array(
                new rb_join(
                        'badge',
                        'LEFT',
                        '{badge}',
                        'base.badgeid = badge.id',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE
                ),
        );

        // Include some standard joins.
        $this->add_user_table_to_joinlist($joinlist, 'base', 'userid');
        $this->add_course_table_to_joinlist($joinlist, 'badge', 'courseid');
        // Requires the course join.
        $this->add_course_category_table_to_joinlist($joinlist,
                'course', 'category');
        $this->add_core_tag_tables_to_joinlist('core', 'course', $joinlist, 'badge', 'courseid');
        $this->add_cohort_course_tables_to_joinlist($joinlist, 'badge', 'courseid');

        return $joinlist;
    }

    protected function define_columnoptions() {
        global $DB;

        $columnoptions = array(
                new rb_column_option(
                        'base',
                        'dateexpire',
                        get_string('dateexpire', 'rbsource_badgeissued'),
                        'base.dateexpire',
                        array('displayfunc' => 'nice_date')
                ),
                new rb_column_option(
                        'base',
                        'dateissued',
                        get_string('dateissued', 'rbsource_badgeissued'),
                        'base.dateissued',
                        array('displayfunc' => 'nice_date')
                ),
                new rb_column_option(
                        'base',
                        'issuernotified',
                        get_string('issuernotified', 'rbsource_badgeissued'),
                        'base.issuernotified',
                        array('displayfunc' => 'nice_date')
                ),
                new rb_column_option(
                        'badge',
                        'idchar',
                        'badgeid',
                        \local_reportbuilder\dblib\base::getbdlib()->sql_cast_2char('badge.id'),
                        array('joins' => 'badge', 'selectable' => false)
                ),
                new rb_column_option(
                        'badge',
                        'badgeimage',
                        get_string('badgeimage', 'rbsource_badgeissued'),
                        'badge.id',
                        array('displayfunc' => 'badgeimage',
                              'extrafields' => array('userid' => 'base.userid',
                                                     'uniquehash' => 'base.uniquehash',
                                                     'badgename' => 'badge.name'),
                              'joins' => 'badge')
                ),
                new rb_column_option(
                        'badge',
                        'issuername',
                        get_string('issuername', 'rbsource_badgeissued'),
                        'badge.issuername',
                        array('displayfunc' => 'issuernamelink',
                              'extrafields' => array('issuerurl' => 'badge.issuerurl'),
                              'joins' => 'badge')
                ),
                new rb_column_option(
                        'badge',
                        'issuercontact',
                        get_string('issuercontact', 'rbsource_badgeissued'),
                        'badge.issuercontact',
                        array('joins' => 'badge')
                ),
                new rb_column_option(
                        'badge',
                        'name',
                        get_string('badgename', 'rbsource_badgeissued'),
                        'badge.name',
                        array('joins' => 'badge')
                ),
                new rb_column_option(
                        'badge',
                        'type',
                        get_string('badgetype', 'rbsource_badgeissued'),
                        'badge.type',
                        array('displayfunc' => 'badgetype', 'joins' => 'badge')
                ),
                new rb_column_option(
                        'badge',
                        'status',
                        get_string('badgestatus', 'rbsource_badgeissued'),
                        'badge.status',
                        array('displayfunc' => 'badgestatus', 'joins' => 'badge')
                ),
                new rb_column_option(
                        'badge',
                        'description',
                        get_string('badgedescription', 'rbsource_badgeissued'),
                        'badge.description',
                        array(
                                'displayfunc' => 'text',
                                'joins' => 'badge',
                        )
                )
        );

        // Include some standard columns.
        $this->add_user_fields_to_columns($columnoptions);
        $this->add_course_fields_to_columns($columnoptions);
        $this->add_course_category_fields_to_columns($columnoptions);
        $this->add_core_tag_fields_to_columns('core', 'course', $columnoptions);
        $this->add_cohort_course_fields_to_columns($columnoptions);

        return $columnoptions;
    }

    protected function define_filteroptions() {
        $filteroptions = array(
                new rb_filter_option(
                        'base',
                        'dateissued',
                        get_string('dateissued', 'rbsource_badgeissued'),
                        'date'
                ),
                new rb_filter_option(
                        'base',
                        'dateexpire',
                        get_string('dateexpire', 'rbsource_badgeissued'),
                        'date'
                ),
                new rb_filter_option(
                        'badge',
                        'name',
                        get_string('badgename', 'rbsource_badgeissued'),
                        'select',
                        array(
                                'selectfunc' => 'badgename_list',
                                'attributes' => rb_filter_option::select_width_limiter(),
                        )
                ),
                new rb_filter_option(
                        'badge',
                        'idchar',
                        get_string('badges', 'rbsource_badgeissued'),
                        'badge',
                        array('selectfunc' => 'badges_list')
                ),
                new rb_filter_option(
                        'badge',
                        'issuername',
                        get_string('issuername', 'rbsource_badgeissued'),
                        'select',
                        array(
                                'selectfunc' => 'badgeissuer_list',
                                'attributes' => rb_filter_option::select_width_limiter(),
                        )
                ),
                new rb_filter_option(
                        'badge',
                        'type',
                        get_string('badgetype', 'rbsource_badgeissued'),
                        'select',
                        array(
                                'selectfunc' => 'badgetype_list',
                                'attributes' => rb_filter_option::select_width_limiter(),
                        )
                ),
                new rb_filter_option(
                        'badge',
                        'status',
                        get_string('badgestatus', 'rbsource_badgeissued'),
                        'multicheck',
                        array(
                                'selectfunc' => 'badgestatus_list',
                                'attributes' => rb_filter_option::select_width_limiter(),
                        )
                ),
                new rb_filter_option(
                        'badge',
                        'description',
                        get_string('badgedescription', 'rbsource_badgeissued'),
                        'text'
                )
        );

        // Include some standard filters.
        $this->add_user_fields_to_filters($filteroptions);
        $this->add_course_fields_to_filters($filteroptions);
        $this->add_course_category_fields_to_filters($filteroptions);
        $this->add_core_tag_fields_to_filters('core', 'course', $filteroptions);
        $this->add_cohort_course_fields_to_filters($filteroptions);

        return $filteroptions;
    }

    protected function define_contentoptions() {
        $contentoptions = array();

        $contentoptions[] = new rb_content_option(
                'date',
                get_string('dateissued', 'rbsource_badgeissued'),
                'base.dateissued'
        );

        // Add the time created content option.
        $contentoptions[] = new rb_content_option(
                'user',
                get_string('user', 'local_reportbuilder'),
                ['userid' => 'base.userid']
        );

        $contentoptions[] = new rb_content_option(
                'enrolledcourses',
                get_string('enrolledcourses', 'local_reportbuilder'),
                'badge.courseid',
                'badge'
        );

        $contentoptions[] = new rb_content_option(
                'costcentre',
                get_string('costcentre', 'local_reportbuilder'),
                ['costcentre' => "auser.icq"],
                'auser'
        );
        return $contentoptions;
    }

    protected function define_paramoptions() {
        $paramoptions = array();

        return $paramoptions;
    }

    protected function define_defaultcolumns() {
        $defaultcolumns = array(
                array(
                        'type' => 'user',
                        'value' => 'namelink',
                ),
                array(
                        'type' => 'badge',
                        'value' => 'badgeimage',
                ),
                array(
                        'type' => 'base',
                        'value' => 'dateissued',
                ),
                array(
                        'type' => 'badge',
                        'value' => 'type',
                ),
                array(
                        'type' => 'badge',
                        'value' => 'status',
                ),
        );
        return $defaultcolumns;
    }

    protected function define_defaultfilters() {
        $defaultfilters = array(
                array(
                        'type' => 'badge',
                        'value' => 'idchar',
                ),
                array(
                        'type' => 'base',
                        'value' => 'dateissued',
                ),
                array(
                        'type' => 'badge',
                        'value' => 'type',
                ),
                array(
                        'type' => 'badge',
                        'value' => 'status',
                ),

        );

        return $defaultfilters;
    }

    protected function define_requiredcolumns() {
        return array();
    }

    //
    //
    // Source specific column display methods.
    //
    //
    public function rb_display_issuernamelink($name, $row, $isexport) {
        global $CFG;
        if (empty($name)) {
            return '';
        }
        $url = parse_url($CFG->wwwroot);
        if (empty($row->issuerurl) || $row->issuerurl == ($url['scheme'] . '://' . $url['host']) || substr($row->issuerurl, 0, 4) != 'http') {
            return $name;
        }

        return \html_writer::tag('a', $name, array('href' => $row->issuerurl));
    }

    public function rb_display_badgetype($type, $row, $isexport) {
        global $CFG;
        require_once($CFG->libdir.'/badgeslib.php');
        return get_string("badgetype_{$type}", 'rbsource_badgeissued');
    }

    public function rb_display_badgestatus($status, $row, $isexport) {
        global $CFG;
        require_once($CFG->libdir.'/badgeslib.php');
        return get_string("badgestatus_{$status}", 'badges');
    }


    public function rb_display_badgeimage($badgeid, $row, $isexport) {
        global $CFG;

        if ($isexport) {
            return $row->badgename;
        }

        require_once($CFG->libdir.'/badgeslib.php');
        $badge = new badge($badgeid);

        return print_badge_image($badge, $badge->get_context());
    }

    //
    //
    // Source specific filter display methods.
    //
    //

    public function rb_filter_badgename_list() {
        global $DB;

        $sql = "SELECT DISTINCT b.name AS idx, b.name AS val
                FROM {badge_issued} bi
                JOIN {badge} b ON bi.badgeid = b.id";
        return $DB->get_records_sql_menu($sql);
    }

    public function rb_filter_badgeissuer_list() {
        global $DB;

        $sql = "SELECT DISTINCT b.issuername AS idx, b.issuername AS val
                FROM {badge_issued} bi
                JOIN {badge} b ON bi.badgeid = b.id";
        return $DB->get_records_sql_menu($sql);
    }

    public function rb_filter_badges_list() {
        global $DB;

        $sql = "SELECT DISTINCT b.id, b.name
            FROM {badge_issued} bi
            JOIN {badge} b ON bi.badgeid = b.id
            ORDER BY b.name";

        return $DB->get_records_sql_menu($sql);
    }

    public function rb_filter_badgetype_list() {
        global $CFG;
        require_once($CFG->libdir.'/badgeslib.php');
        return array(
                BADGE_TYPE_SITE => get_string('badgetype_'.BADGE_TYPE_SITE, 'rbsource_badgeissued'),
                BADGE_TYPE_COURSE => get_string('badgetype_'.BADGE_TYPE_COURSE, 'rbsource_badgeissued')
        );
    }

    public function rb_filter_badgestatus_list() {
        global $CFG;
        require_once($CFG->libdir.'/badgeslib.php');
        return array(
                BADGE_STATUS_INACTIVE => get_string('badgestatus_'.BADGE_STATUS_INACTIVE, 'badges'),
                BADGE_STATUS_ACTIVE => get_string('badgestatus_'.BADGE_STATUS_ACTIVE, 'badges'),
                BADGE_STATUS_INACTIVE_LOCKED => get_string('badgestatus_'.BADGE_STATUS_INACTIVE_LOCKED, 'badges'),
                BADGE_STATUS_ACTIVE_LOCKED => get_string('badgestatus_'.BADGE_STATUS_ACTIVE_LOCKED, 'badges'),
                BADGE_STATUS_ARCHIVED => get_string('badgestatus_'.BADGE_STATUS_ARCHIVED, 'badges'),
        );
    }
}
