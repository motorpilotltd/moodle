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

namespace rbsource_facetofaceevents;
use coding_exception;
use rb_join;
use rb_column_option;
use rb_filter_option;
use moodle_url;
use rb_content_option;
use rb_column;
use context_module;
use html_writer;
use context_system;
use reportbuilder;
use rbsource_facetofacesummary\base;

defined('MOODLE_INTERNAL') || die();

class source extends base {
    public $base, $joinlist, $columnoptions, $filteroptions;
    public $contentoptions, $paramoptions, $defaultcolumns;
    public $defaultfilters, $sourcetitle;

    public function __construct() {
        $this->base = '{facetoface_sessions}';
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->contentoptions = $this->define_contentoptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->requiredcolumns = $this->define_requiredcolumns();
        $this->paramoptions = $this->define_paramoptions();
        $this->sourcetitle = get_string('sourcetitle', 'rbsource_facetofaceevents');

        parent::__construct();
    }

    public function define_joinlist() {
        $joinlist = array();

        $joinlist[] = new rb_join(
                'attendees',
                'LEFT',
                "(SELECT su.sessionid, count(ss.id) AS number
                FROM {facetoface_signups} su
                JOIN {facetoface_signups_status} ss
                    ON su.id = ss.signupid
                WHERE ss.superceded=0 AND ss.statuscode >= " . MDL_F2F_STATUS_APPROVED ."
                GROUP BY su.sessionid)",
                "attendees.sessionid = base.id",
                REPORT_BUILDER_RELATION_ONE_TO_ONE
        );

        $joinlist[] = new rb_join(
                'facetoface',
                'LEFT',
                '{facetoface}',
                '(facetoface.id = base.facetoface)',
                REPORT_BUILDER_RELATION_ONE_TO_MANY
        );

        $joinlist[] = new rb_join(
                'allattendees',
                'LEFT',
                "(SELECT su.sessionid, su.userid, ss.id AS ssid, ss.statuscode
                FROM {facetoface_signups} su
                JOIN {facetoface_signups_status} ss
                    ON su.id = ss.signupid
                WHERE ss.superceded = 0)",
                'allattendees.sessionid = base.id',
                REPORT_BUILDER_RELATION_ONE_TO_ONE
        );
        $joinlist[] = new rb_join(
                'sessiondate',
                'LEFT',
                '{facetoface_sessions_dates}',
                'sessiondate.sessionid = base.id',
                REPORT_BUILDER_RELATION_ONE_TO_MANY
        );

        $this->add_grouped_session_status_to_joinlist($joinlist, 'base', 'id');
        $this->add_course_table_to_joinlist($joinlist, 'facetoface', 'course');
        $this->add_course_category_table_to_joinlist($joinlist, 'course', 'category');
        $this->add_user_table_to_joinlist($joinlist, 'allattendees', 'userid');
        $this->add_user_table_to_joinlist($joinlist, 'base', 'usermodified', 'modifiedby');
        $this->add_facetoface_session_roles_to_joinlist($joinlist, 'base.id');
        $this->add_facetoface_currentuserstatus_to_joinlist($joinlist);
        $this->add_context_table_to_joinlist($joinlist, 'course', 'id', CONTEXT_COURSE, 'INNER');
        $this->add_cohort_course_tables_to_joinlist($joinlist, 'facetoface', 'course');

        return $joinlist;
    }

    public function define_columnoptions() {
        global $DB;
        $usernamefieldscreator = totara_get_all_user_name_fields_join('modifiedby');

        $columnoptions = array(
                new rb_column_option(
                        'session',
                        'totalnumattendees',
                        get_string('totalnumattendees', 'rbsource_facetofacesummary'),
                        '(CASE WHEN allattendees.statuscode >= ' . MDL_F2F_STATUS_REQUESTED . ' THEN 1 ELSE NULL END)',
                        array(
                                'joins' => array('allattendees'),
                                'grouping' => 'count',
                                'dbdatatype' => 'integer'
                        )
                ),
                new rb_column_option(
                        'session',
                        'waitlistattendees',
                        get_string('waitlistattendees', 'rbsource_facetofacesummary'),
                        '(CASE WHEN allattendees.statuscode = ' . MDL_F2F_STATUS_WAITLISTED . ' THEN 1 ELSE NULL END)',
                        array(
                                'joins' => array('allattendees'),
                                'grouping' => 'count',
                                'dbdatatype' => 'integer'
                        )
                ),
                new rb_column_option(
                        'session',
                        'numspaces',
                        get_string('numspaces', 'rbsource_facetofacesummary'),
                        '(CASE WHEN allattendees.statuscode >= ' . MDL_F2F_STATUS_APPROVED . ' THEN 1 ELSE NULL END)',
                        array('joins' => array('allattendees'),
                              'grouping' => 'count',
                              'displayfunc' => 'session_spaces',
                              'extrafields' => array('overall_capacity' => 'base.capacity'),
                              'dbdatatype' => 'integer'
                        )
                ),
                new rb_column_option(
                        'session',
                        'cancelledattendees',
                        get_string('cancelledattendees', 'rbsource_facetofacesummary'),
                        '(CASE WHEN allattendees.statuscode IN (' . MDL_F2F_STATUS_USER_CANCELLED . ', ' . MDL_F2F_STATUS_SESSION_CANCELLED . ') THEN 1 ELSE NULL END)',
                        array(
                                'joins' => array('allattendees'),
                                'grouping' => 'count',
                                'dbdatatype' => 'integer'
                        )
                ),
                new rb_column_option(
                        'session',
                        'fullyattended',
                        get_string('fullyattended', 'rbsource_facetofacesummary'),
                        '(CASE WHEN allattendees.statuscode = ' . MDL_F2F_STATUS_FULLY_ATTENDED . ' THEN 1 ELSE NULL END)',
                        array(
                                'joins' => array('allattendees'),
                                'grouping' => 'count',
                                'dbdatatype' => 'integer'
                        )
                ),
                new rb_column_option(
                        'session',
                        'partiallyattended',
                        get_string('partiallyattended', 'rbsource_facetofacesummary'),
                        '(CASE WHEN allattendees.statuscode = ' . MDL_F2F_STATUS_PARTIALLY_ATTENDED . ' THEN 1 ELSE NULL END)',
                        array(
                                'joins' => array('allattendees'),
                                'grouping' => 'count',
                                'dbdatatype' => 'integer'
                        )
                ),
                new rb_column_option(
                        'session',
                        'noshowattendees',
                        get_string('noshowattendees', 'rbsource_facetofacesummary'),
                        '(CASE WHEN allattendees.statuscode = ' . MDL_F2F_STATUS_NO_SHOW . ' THEN 1 ELSE NULL END)',
                        array(
                                'joins' => array('allattendees'),
                                'grouping' => 'count',
                                'dbdatatype' => 'integer'
                        )
                ),
                new rb_column_option(
                        'session',
                        'declinedattendees',
                        get_string('declinedattendees', 'rbsource_facetofacesummary'),
                        '(CASE WHEN allattendees.statuscode = ' . MDL_F2F_STATUS_DECLINED . ' THEN 1 ELSE NULL END)',
                        array(
                                'joins' => array('allattendees'),
                                'grouping' => 'count',
                                'dbdatatype' => 'integer'
                        )
                ),
                new rb_column_option(
                        'session',
                        'details',
                        get_string('sessdetails', 'rbsource_facetofacesessions'),
                        'base.details'
                ),
                new rb_column_option(
                        'session',
                        'overbookingallowed',
                        get_string('overbookingallowed', 'rbsource_facetofacesummary'),
                        'base.allowoverbook',
                        array(
                                'displayfunc' => 'yes_or_no'
                        )
                ),
        );

        if (!get_config(null, 'facetoface_hidecost')) {
            $columnoptions[] = new rb_column_option(
                    'facetoface',
                    'normalcost',
                    get_string('normalcost', 'rbsource_facetofacesummary'),
                    'base.normalcost',
                    array(
                            'dbdatatype' => 'decimal'
                    )
            );
            if (!get_config(null, 'facetoface_hidediscount')) {
                $columnoptions[] = new rb_column_option(
                        'facetoface',
                        'discountcost',
                        get_string('discountcost', 'rbsource_facetofacesummary'),
                        'base.discountcost',
                        array(
                                'dbdatatype' => 'decimal'
                        )
                );
            }
        }

        $columnoptions[] = new rb_column_option(
                'facetoface',
                'sessionid',
                get_string('sessionid', 'rbsource_facetofacesummary'),
                'base.id',
                array(
                        'dbdatatype' => 'integer'
                )
        );

        $columnoptions[] = new rb_column_option(
                'session',
                'capacity',
                get_string('sesscapacity', 'rbsource_facetofacesessions'),
                'base.capacity',
                array(
                        'dbdatatype' => 'integer'
                )
        );
        $columnoptions[] = new rb_column_option(
                'session',
                'numattendees',
                get_string('numattendees', 'rbsource_facetofacesessions'),
                'attendees.number',
                array(
                        'joins' => 'attendees',
                        'dbdatatype' => 'integer'
                )
        );

        $columnoptions[] = new rb_column_option(
                'session',
                'numattendeeslink',
                get_string('numattendeeslink', 'rbsource_facetofacesummary'),
                'attendees.number',
                array(
                        'joins' => array('attendees'),
                        'dbdatatype' => 'integer',
                        'displayfunc' => 'numattendeeslink',
                        'defaultheading' => get_string('numattendees', 'rbsource_facetofacesessions'),
                        'extrafields' => array(
                                'session' => 'base.id'
                        )
                )
        );

        $columnoptions[] = new rb_column_option(
                'session',
                'eventtimecreated',
                get_string('eventtimecreated', 'rbsource_facetofaceevents'),
                "base.timecreated",
                array(
                        'displayfunc' => 'nice_datetime',
                        'dbdatatype' => 'timestamp',
                )
        );

        $columnoptions[] = new rb_column_option(
                'session',
                'eventtimemodified',
                get_string('lastupdated', 'rbsource_facetofaceevents'),
                "base.timemodified",
                array(
                        'displayfunc' => 'nice_datetime',
                        'dbdatatype' => 'timestamp',
                )
        );

        $columnoptions[] = new rb_column_option(
                'session',
                'eventmodifiedby',
                get_string('lastupdatedby', 'rbsource_facetofaceevents'),
                "CASE WHEN base.usermodified = 0 THEN null
                  ELSE " . $DB->sql_concat_join("' '", $usernamefieldscreator) . " END",
                array(
                        'joins' => 'modifiedby',
                        'displayfunc' => 'link_user',
                        'extrafields' => array_merge(array('id' => 'modifiedby.id'), $usernamefieldscreator),
                )
        );

        $this->add_grouped_session_status_to_columns($columnoptions, 'base');
        $this->add_facetoface_common_to_columns($columnoptions, 'base');
        $this->add_facetoface_session_roles_to_columns($columnoptions);
        $this->add_facetoface_currentuserstatus_to_columns($columnoptions);

        // Include some standard columns.
        $this->add_course_category_fields_to_columns($columnoptions);
        $this->add_course_fields_to_columns($columnoptions);

        return $columnoptions;
    }


    /**
     * Add joins required by @see rb_source_facetoface_events::add_grouped_session_status_to_columns()
     * @param array $joinlist
     * @param string $join 'sessions' table to join to
     * @param string $field 'id' field (from sessions table) to join to
     */
    protected function add_grouped_session_status_to_joinlist(&$joinlist, $join, $field) {
        $joinlist[] =  new rb_join(
                'cntbookings',
                'LEFT',
                "(SELECT s.id sessionid, COUNT(ss.id) cntsignups
                FROM {facetoface_sessions} s
                LEFT JOIN {facetoface_signups} su ON (su.sessionid = s.id)
                LEFT JOIN {facetoface_signups_status} ss
                    ON (su.id = ss.signupid AND ss.superceded = 0 AND ss.statuscode >= " . MDL_F2F_STATUS_BOOKED . ")
                WHERE 1=1
                GROUP BY s.id)",

                "cntbookings.sessionid = {$join}.{$field}",
                REPORT_BUILDER_RELATION_ONE_TO_ONE,
                $join
        );

        $joinlist[] = new rb_join(
                'eventdateinfo',
                'LEFT',
                '(  SELECT  sd.sessionid,
                        sd.eventstart,
                        sd.eventfinish
                FROM (
                        SELECT   sessionid,
                                 MIN(timestart) AS eventstart,
                                 MAX(timefinish) AS eventfinish
                        FROM     {facetoface_sessions_dates}
                        GROUP BY sessionid
                     ) sd
                INNER JOIN {facetoface_sessions_dates} tzstart
                    ON sd.eventstart = tzstart.timestart AND sd.sessionid = tzstart.sessionid
                INNER JOIN {facetoface_sessions_dates} tzfinish
                    ON sd.eventfinish = tzfinish.timefinish AND sd.sessionid = tzfinish.sessionid )',
                "eventdateinfo.sessionid = {$join}.{$field}",
                REPORT_BUILDER_RELATION_ONE_TO_MANY
        );
    }

    /**
     * Add session booking and overall status columns for sessions (so it also groups all sessions (dates) in an event)
     * Requires 'eventdateinfo' join, and 'cntbookings' join provided by
     * @see rb_source_facetoface_events::add_grouped_session_status_to_joinlist()
     *
     * If you call this function in order to get the correct highlighting you will need to extend the CSS rules in
     * mod/facetoface/styles.css and add a line like the following:
     *     .reportbuilder-table[data-source="rbsource_facetofacesummary"] tr
     *
     * Search for that and you'll see what you need to do.
     *
     * @param array $columnoptions
     * @param string $joinsessions Join name that provide {facetoface_sessions}
     */
    protected function add_grouped_session_status_to_columns(&$columnoptions, $joinsessions = 'sessions') {
        $now = time();

        $columnoptions[] = new rb_column_option(
                'session',
                'overallstatus',
                get_string('overallstatus', 'rbsource_facetofacesummary'),
                "( CASE WHEN cancelledstatus <> 0 THEN 'cancelled'
                    WHEN eventdateinfo.eventstart IS NULL OR eventdateinfo.eventstart = 0 OR eventdateinfo.eventstart > {$now} THEN 'upcoming'
                    WHEN {$now} > eventdateinfo.eventstart AND {$now} < eventdateinfo.eventfinish THEN 'started'
                    WHEN {$now} > eventdateinfo.eventfinish THEN 'ended'
                    ELSE NULL END
             )",
                array(
                        'joins' => array('eventdateinfo'),
                        'displayfunc' => 'overall_status',
                        'extrafields' => array(
                                'timestart' => "eventdateinfo.eventstart",
                                'timefinish' => "eventdateinfo.eventfinish",
                        )
                )
        );

        $columnoptions[] = new rb_column_option(
                'session',
                'bookingstatus',
                get_string('bookingstatus', 'rbsource_facetofacesummary'),
                "(CASE WHEN {$now} > eventdateinfo.eventfinish AND cntsignups < {$joinsessions}.capacity THEN 'ended'
                   WHEN cancelledstatus <> 0 THEN 'cancelled'
                   WHEN cntsignups < {$joinsessions}.capacity THEN 'available'
                   WHEN cntsignups = {$joinsessions}.capacity THEN 'fullybooked'
                   WHEN cntsignups > {$joinsessions}.capacity THEN 'overbooked'
                   ELSE NULL END)",
                array(
                        'joins' => array('eventdateinfo', 'cntbookings', $joinsessions),
                        'displayfunc' => 'booking_status',
                        'dbdatatype' => 'char',
                        'extrafields' => array(
                                'capacity' => "{$joinsessions}.capacity",
                                'timestart' => "eventdateinfo.eventstart",
                                'timefinish' => "eventdateinfo.eventfinish"
                        )
                )
        );

        $columnoptions[] = new rb_column_option(
                'session',
                'eventstartdate',
                get_string('eventstartdatetime', 'rbsource_facetofaceevents'),
                "eventdateinfo.eventstart",
                array(
                        'joins' => array('eventdateinfo'),
                        'displayfunc' => 'event_date',
                        'extrafields' => array('timezone' => 'eventdateinfo.tzstart'),
                        'dbdatatype' => 'timestamp',
                )
        );

        $columnoptions[] = new rb_column_option(
                'session',
                'eventfinishdate',
                get_string('eventfinishdatetime', 'rbsource_facetofaceevents'),
                "eventdateinfo.eventfinish",
                array(
                        'joins' => array('eventdateinfo'),
                        'displayfunc' => 'event_date',
                        'extrafields' => array('timezone' => 'eventdateinfo.tzfinish'),
                        'dbdatatype' => 'timestamp',
                )
        );
    }

    protected function define_filteroptions() {
        $filteroptions = array(
                new rb_filter_option(
                        'facetoface',
                        'name',
                        get_string('ftfname', 'rbsource_facetofacesessions'),
                        'text'
                ),
                new rb_filter_option(
                        'session',
                        'bookingstatus',
                        get_string('bookingstatus', 'rbsource_facetofacesummary'),
                        'select',
                        array(
                                'selectchoices' => self::get_bookingstatus_options(),
                        )
                ),
                new rb_filter_option(
                        'session',
                        'eventtimecreated',
                        get_string('eventtimecreated', 'rbsource_facetofaceevents'),
                        'date'
                ),
                new rb_filter_option(
                        'session',
                        'eventtimemodified',
                        get_string('lastupdated', 'rbsource_facetofaceevents'),
                        'date'
                ),
                new rb_filter_option(
                        'session',
                        'eventmodifiedby',
                        get_string('lastupdatedby', 'rbsource_facetofaceevents'),
                        'text'
                ),
        );

        $this->add_facetoface_session_role_fields_to_filters($filteroptions);
        $this->add_facetoface_currentuserstatus_to_filters($filteroptions);

        // Add session custom fields to filters.
        $this->add_course_category_fields_to_filters($filteroptions);
        $this->add_course_fields_to_filters($filteroptions);

        return $filteroptions;
    }

    protected function define_contentoptions() {
        $contentoptions = array();

        $contentoptions[] = new rb_content_option(
                'session_roles',
                get_string('sessionroles', 'rbsource_facetofaceevents'),
                'base.id'
        );

        $contentoptions[] = new rb_content_option(
                'user',
                get_string('user', 'local_reportbuilder'),
                ['userid' => 'allattendees.userid'],
                'allattendees'
        );

        $contentoptions[] = new rb_content_option(
                'enrolledcourses',
                get_string('enrolledcourses', 'local_reportbuilder'),
                'facetoface.course',
                'facetoface'
        );

        $contentoptions[] = new rb_content_option(
                'costcentre',
                get_string('costcentre', 'local_reportbuilder'),
                ['costcentre' => "auser.icq"],
                'auser'
        );

        return $contentoptions;
    }

    protected function define_defaultcolumns() {
        $defaultcolumns = array(
                array(
                        'type' => 'course',
                        'value' => 'fullname',
                ),
                array(
                        'type' => 'facetoface',
                        'value' => 'namelink',
                ),
        );

        return $defaultcolumns;
    }

    public function rb_display_actions($session, $row, $isexport = false) {
        global $OUTPUT;

        if ($isexport) {
            return null;
        }

        $cm = get_coursemodule_from_instance('facetoface', $row->facetofaceid);
        $context = context_module::instance($cm->id);
        if (!has_capability('mod/facetoface:viewattendees', $context)) {
            return null;
        }

        return html_writer::link(
                new moodle_url('/mod/facetoface/attendees.php', array('s' => $session)),
                $OUTPUT->pix_icon('t/cohort', get_string("attendees", "facetoface"))
        );
    }

    /**
     * Spaces left on session.
     *
     * @param string $count Number of signups
     * @param object $row Report row
     * @return string Display html
     */
    public function rb_display_session_spaces($count, $row) {
        $spaces = $row->overall_capacity - $count;
        return ($spaces > 0 ? $spaces : 0);
    }

    /**
     * Show if manager's approval required
     * @param bool $required True when approval required
     * @param stdClass $row
     */
    public function rb_display_approver($required, $row) {
        if ($required) {
            return get_string('manager', 'core_role');
        } else {
            return get_string('noone', 'rbsource_facetofacesummary');
        }
    }

    /**
     * Required columns.
     */
    protected function define_requiredcolumns() {
        $requiredcolumns = array();

        $requiredcolumns[] = new rb_column(
                'visibility',
                'id',
                '',
                "course.id",
                array(
                        'joins' => 'course',
                        'required' => 'true',
                        'hidden' => 'true'
                )
        );

        $requiredcolumns[] = new rb_column(
                'visibility',
                'visible',
                '',
                "course.visible",
                array(
                        'joins' => 'course',
                        'required' => 'true',
                        'hidden' => 'true'
                )
        );

        $requiredcolumns[] = new rb_column(
                'ctx',
                'id',
                '',
                "ctx.id",
                array(
                        'joins' => 'ctx',
                        'required' => 'true',
                        'hidden' => 'true'
                )
        );

        $context = context_system::instance();
        if (has_any_capability(['mod/facetoface:viewattendees'], $context)) {
            $requiredcolumns[] = new rb_column(
                    'admin',
                    'actions',
                    get_string('actions', 'rbsource_facetofacesummary'),
                    'base.id',
                    array(
                            'noexport' => true,
                            'nosort' => true,
                            'extrafields' => array('facetofaceid' => 'base.facetoface'),
                            'displayfunc' => 'actions',
                    )
            );
        }

        return $requiredcolumns;
    }

    protected function define_paramoptions() {
        $paramoptions = array(
        );

        return $paramoptions;
    }

    /**
     * Report post config operations.
     *
     * @param reportbuilder $report
     */
    public function post_config(reportbuilder $report) {
        $userid = $report->reportfor;
        if (isset($report->embedobj->embeddedparams['userid'])) {
            $userid = $report->embedobj->embeddedparams['userid'];
        }
        $report->set_post_config_restrictions($report->post_config_visibility_where('course', 'course', $userid));
    }
}
