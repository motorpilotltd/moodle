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

namespace rbsource_facetofacesummary;
use coding_exception;
use rb_join;
use rb_column_option;
use rb_filter_option;
use html_writer;
use moodle_url;
use rb_content_option;
use context_module;
use rb_column;
use context_system;
use reportbuilder;

defined('MOODLE_INTERNAL') || die();

class source extends base {
    public $base, $joinlist, $columnoptions, $filteroptions;
    public $contentoptions, $paramoptions, $defaultcolumns;
    public $defaultfilters, $sourcetitle;

    public function __construct() {
        $this->base = '{facetoface_sessions_dates}';
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->contentoptions = $this->define_contentoptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->requiredcolumns = $this->define_requiredcolumns();
        $this->paramoptions = $this->define_paramoptions();
        $this->sourcetitle = get_string('sourcetitle', 'rbsource_facetofacesummary');

        parent::__construct();
    }

    public function define_joinlist() {
        $joinlist = array();

        $this->add_session_common_to_joinlist($joinlist);

        $joinlist[] = new rb_join(
                'allattendees',
                'LEFT',
                "(SELECT su.sessionid, su.userid, ss.id AS ssid, ss.statuscode
                FROM {facetoface_signups} su
                JOIN {facetoface_signups_status} ss
                    ON su.id = ss.signupid
                WHERE ss.superceded = 0)",
                'allattendees.sessionid = sessions.id',
                REPORT_BUILDER_RELATION_ONE_TO_ONE,
                'sessions'
        );

        $this->add_session_status_to_joinlist($joinlist);
        $this->add_course_table_to_joinlist($joinlist, 'facetoface', 'course');
        $this->add_course_category_table_to_joinlist($joinlist, 'course', 'category');
        $this->add_user_table_to_joinlist($joinlist, 'allattendees', 'userid');
        $this->add_user_table_to_joinlist($joinlist, 'sessions', 'usermodified', 'modifiedby');
        $this->add_facetoface_session_roles_to_joinlist($joinlist);
        $this->add_facetoface_currentuserstatus_to_joinlist($joinlist);
        $this->add_context_table_to_joinlist($joinlist, 'course', 'id', CONTEXT_COURSE, 'INNER');

        return $joinlist;
    }

    public function define_columnoptions() {

        global $CFG, $DB;

        $usernamefieldscreator = totara_get_all_user_name_fields_join('modifiedby');

        $columnoptions = array(
                new rb_column_option(
                        'session',
                        'totalnumattendees',
                        get_string('totalnumattendees', 'rbsource_facetofacesummary'),
                        '(CASE WHEN allattendees.statuscode >= ' . MDL_F2F_STATUS_REQUESTED . ' THEN 1 ELSE NULL END)',
                        array(
                                'joins' => array('allattendees', 'sessions'),
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
                                'joins' => array('allattendees', 'sessions'),
                                'grouping' => 'count',
                                'dbdatatype' => 'integer'
                        )
                ),
                new rb_column_option(
                        'session',
                        'numspaces',
                        get_string('numspaces', 'rbsource_facetofacesummary'),
                        '(CASE WHEN allattendees.statuscode >= ' . MDL_F2F_STATUS_APPROVED . ' THEN 1 ELSE NULL END)',
                        array('joins' => array('allattendees', 'sessions'),
                              'grouping' => 'count',
                              'displayfunc' => 'session_spaces',
                              'extrafields' => array('overall_capacity' => 'sessions.capacity'),
                              'dbdatatype' => 'integer'
                        )
                ),
                new rb_column_option(
                        'session',
                        'cancelledattendees',
                        get_string('cancelledattendees', 'rbsource_facetofacesummary'),
                        '(CASE WHEN allattendees.statuscode IN (' . MDL_F2F_STATUS_USER_CANCELLED . ', ' . MDL_F2F_STATUS_SESSION_CANCELLED . ') THEN 1 ELSE NULL END)',
                        array(
                                'joins' => array('allattendees', 'sessions'),
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
                                'joins' => array('allattendees', 'sessions'),
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
                                'joins' => array('allattendees', 'sessions'),
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
                                'joins' => array('allattendees', 'sessions'),
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
                                'joins' => array('allattendees', 'sessions'),
                                'grouping' => 'count',
                                'dbdatatype' => 'integer'
                        )
                ),
                new rb_column_option(
                        'session',
                        'details',
                        get_string('sessdetails', 'rbsource_facetofacesessions'),
                        'sessions.details',
                        array('joins' => 'sessions')
                ),
                new rb_column_option(
                        'session',
                        'overbookingallowed',
                        get_string('overbookingallowed', 'rbsource_facetofacesummary'),
                        'sessions.allowoverbook',
                        array(
                                'joins' => 'sessions',
                                'displayfunc' => 'yes_or_no'
                        )
                ),
                new rb_column_option(
                        'session',
                        'signupperiod',
                        get_string('signupperiod', 'rbsource_facetofacesummary'),
                        'sessions.registrationtimestart',
                        array(
                                'joins' => array('sessions'),
                                'dbdatatype' => 'timestamp',
                                'displayfunc' => 'nice_two_datetime_in_timezone',
                                'extrafields' => array('finishdate' => 'sessions.registrationtimefinish', 'timezone' => 'base.sessiontimezone'),
                                'outputformat' => 'text'
                        )
                ),
                new rb_column_option(
                        'date',
                        'sessiondate_link',
                        get_string('sessdatetimelink', 'rbsource_facetofacesummary'),
                        'base.timestart',
                        array(
                                'joins' => 'sessions',
                                'extrafields' => array(
                                        'session_id' => 'sessions.id',
                                        'timezone' => 'base.sessiontimezone',
                                ),
                                'defaultheading' => get_string('sessdatetime', 'rbsource_facetofacesummary'),
                                'displayfunc' => 'event_date_link',
                                'dbdatatype' => 'timestamp'
                        )
                )
        );

        if (!get_config(null, 'facetoface_hidecost')) {
            $columnoptions[] = new rb_column_option(
                    'facetoface',
                    'normalcost',
                    get_string('normalcost', 'rbsource_facetofacesummary'),
                    'sessions.normalcost',
                    array(
                            'joins' => 'sessions',
                            'dbdatatype' => 'decimal'
                    )
            );
            if (!get_config(null, 'facetoface_hidediscount')) {
                $columnoptions[] = new rb_column_option(
                        'facetoface',
                        'discountcost',
                        get_string('discountcost', 'rbsource_facetofacesummary'),
                        'sessions.discountcost',
                        array(
                                'joins' => 'sessions',
                                'dbdatatype' => 'decimal'
                        )
                );
            }
            $columnoptions[] = new rb_column_option(
                    'session',
                    'eventtimecreated',
                    get_string('eventtimecreated', 'rbsource_facetofaceevents'),
                    "sessions.timecreated",
                    array(
                            'joins' => 'sessions',
                            'displayfunc' => 'nice_datetime',
                            'dbdatatype' => 'timestamp',
                    )
            );
            $columnoptions[] = new rb_column_option(
                    'session',
                    'eventtimemodified',
                    get_string('lastupdated', 'rbsource_facetofacesummary'),
                    "sessions.timemodified",
                    array(
                            'joins' => 'sessions',
                            'displayfunc' => 'nice_datetime',
                            'dbdatatype' => 'timestamp',
                    )
            );
            $columnoptions[] = new rb_column_option(
                    'session',
                    'eventmodifiedby',
                    get_string('lastupdatedby', 'rbsource_facetofacesummary'),
                    "CASE WHEN sessions.usermodified = 0 THEN null
                  ELSE " . $DB->sql_concat_join("' '", $usernamefieldscreator) . " END",
                    array(
                            'joins' => 'modifiedby',
                            'displayfunc' => 'link_user',
                            'extrafields' => array_merge(array('id' => 'modifiedby.id'), $usernamefieldscreator),
                    )
            );
        }

        $this->add_session_status_to_columns($columnoptions);
        $this->add_session_common_to_columns($columnoptions);
        $this->add_facetoface_common_to_columns($columnoptions);
        $this->add_facetoface_session_roles_to_columns($columnoptions);
        $this->add_facetoface_currentuserstatus_to_columns($columnoptions);

        // Include some standard columns.
        $this->add_course_category_fields_to_columns($columnoptions);
        $this->add_course_fields_to_columns($columnoptions);

        return $columnoptions;
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
                        'date',
                        'sessionstartdate',
                        get_string('sessdate', 'rbsource_facetofacesessions'),
                        'date'
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
                        'overallstatus',
                        get_string('overallstatus', 'rbsource_facetofacesummary'),
                        'select',
                        array(
                                'selectfunc' => 'overallstatus',
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
                        get_string('lastupdated', 'rbsource_facetofacesummary'),
                        'date'
                ),
                new rb_filter_option(
                        'session',
                        'eventmodifiedby',
                        get_string('lastupdatedby', 'rbsource_facetofacesummary'),
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
                'date',
                get_string('thedate', 'rbsource_facetofacesessions'),
                'base.timestart'
        );
        $contentoptions[] = new rb_content_option(
                'session_roles',
                get_string('sessionroles', 'rbsource_facetofacesummary'),
                'base.sessionid'
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
                array(
                        'type' => 'session',
                        'value' => 'capacity',
                ),
                array(
                        'type' => 'session',
                        'value' => 'totalnumattendees',
                ),
                array(
                        'type' => 'session',
                        'value' => 'numspaces',
                ),
        );

        return $defaultcolumns;
    }

    /**
     * Convert a f2f date into a link to that session with timezone.
     * @deprecated since Totara 10. Please user event_date_time instead
     *
     * @param string $date Date of session
     * @param object $row Report row
     * @param bool $isexport
     * @return string Display html
     */
    function rb_display_link_f2f_session_in_timezone($date, $row, $isexport = false) {
        global $OUTPUT;
        debugging('Function rb_display_link_f2f_session_in_timezone is deprecated. Use event_date_link instead ', DEBUG_DEVELOPER);
        $sessionid = $row->session_id;
        if ($date && is_numeric($date)) {
            $date = $this->rb_display_nice_datetime_in_timezone($date, $row);
            if ($isexport) {
                return $date;
            }
            return $OUTPUT->action_link(new moodle_url('/mod/facetoface/attendees.php', array('s' => $sessionid)), $date);
        } else {
            $unknownstr = get_string('unknowndate', 'rbsource_facetofacesummary');
            if ($isexport) {
                return $unknownstr;
            }
            return $OUTPUT->action_link(new moodle_url('/mod/facetoface/attendees.php', array('s' => $sessionid)), $unknownstr);
        }
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
        // Session_id is needed so when grouping we can keep the information grouped by sessions.
        // This is done to cover the case when we have several sessions which are identical.
        $requiredcolumns = array(
                new rb_column(
                        'sessions',
                        'id',
                        '',
                        "sessions.id",
                        array(
                                'joins' => 'sessions'
                        )
                ),
                new rb_column(
                        'visibility',
                        'id',
                        '',
                        "course.id",
                        array(
                                'joins' => 'course',
                                'required' => 'true',
                                'hidden' => 'true'
                        )
                ),
                new rb_column(
                        'visibility',
                        'visible',
                        '',
                        "course.visible",
                        array(
                                'joins' => 'course',
                                'required' => 'true',
                                'hidden' => 'true'
                        )
                ),
                new rb_column(
                        'ctx',
                        'id',
                        '',
                        "ctx.id",
                        array(
                                'joins' => 'ctx',
                                'required' => 'true',
                                'hidden' => 'true'
                        )
                )
        );

        $context = context_system::instance();
        if (has_any_capability(['mod/facetoface:viewattendees'], $context)) {
            $requiredcolumns[] = new rb_column(
                    'admin',
                    'actions',
                    get_string('actions', 'rbsource_facetofacesummary'),
                    'sessions.id',
                    [
                            'noexport' => true,
                            'nosort' => true,
                            'extrafields' => ['facetofaceid' => 'sessions.facetoface'],
                            'displayfunc' => 'actions'
                    ]
            );
        }

        return $requiredcolumns;
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
