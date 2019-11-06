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

namespace rbsource_facetofacesessions;
use coding_exception;
use rb_join;
use rb_column_option;
use rb_filter_option;
use moodle_url;
use rb_content_option;
use rb_column;
use reportbuilder;
use rb_param_option;
use core_user;

use rbsource_facetofacesummary\base;

defined('MOODLE_INTERNAL') || die();

class source extends base {
    public $base, $joinlist, $columnoptions, $filteroptions;
    public $contentoptions, $paramoptions, $defaultcolumns;
    public $defaultfilters, $sourcetitle, $requiredcolumns;

    public function __construct() {
        $this->base = '{facetoface_signups}';
        $this->usedcomponents[] = 'mod_facetoface';
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->contentoptions = $this->define_contentoptions();
        $this->paramoptions = $this->define_paramoptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->requiredcolumns = $this->define_requiredcolumns();
        $this->sourcetitle = get_string('sourcetitle', 'rbsource_facetofacesessions');
        parent::__construct();
    }

    //
    //
    // Methods for defining contents of source
    //
    //

    protected function define_joinlist() {
        global $CFG;
        require_once($CFG->dirroot . '/mod/facetoface/lib.php');

        // joinlist for this source
        $joinlist = array(
                new rb_join(
                        'sessions',
                        'LEFT',
                        '{facetoface_sessions}',
                        'sessions.id = base.sessionid',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE
                ),
                new rb_join(
                        'facetoface',
                        'LEFT',
                        '{facetoface}',
                        'facetoface.id = sessions.facetoface',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE,
                        'sessions'
                ),
                new rb_join(
                        'sessiondate',
                        'LEFT',
                        '{facetoface_sessions_dates}',
                        '(sessiondate.sessionid = base.sessionid)',
                        REPORT_BUILDER_RELATION_ONE_TO_MANY,
                        'sessions'
                ),
                new rb_join(
                        'status',
                        'LEFT',
                        '{facetoface_signups_status}',
                        '(status.signupid = base.id AND status.superceded = 0)',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE
                ),
                new rb_join(
                        'attendees',
                        'LEFT',
                        // subquery as table
                        "(SELECT su.sessionid, count(ss.id) AS number
                    FROM {facetoface_signups} su
                    JOIN {facetoface_signups_status} ss
                        ON su.id = ss.signupid
                    WHERE ss.superceded=0 AND ss.statuscode >= 50
                    GROUP BY su.sessionid)",
                        'attendees.sessionid = base.sessionid',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE
                ),
                new rb_join(
                        'cancellationstatus',
                        'LEFT',
                        '{facetoface_signups_status}',
                        '(cancellationstatus.signupid = base.id AND
                    cancellationstatus.superceded = 0 AND
                    cancellationstatus.statuscode = ' . MDL_F2F_STATUS_USER_CANCELLED . ')',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE
                ),
                new rb_join(
                        'creator',
                        'LEFT',
                        '{user}',
                        'status.createdby = creator.id',
                        REPORT_BUILDER_RELATION_MANY_TO_ONE,
                        'status'
                ),
                new rb_join(
                        'approver',
                        'LEFT',
                        // Subquery as table - statuscode 50 = approved.
                        // Only want the last approval record
                        "(SELECT status.signupid, status.createdby as approverid, status.timecreated as approvaltime
                    FROM {facetoface_signups_status} status
                    JOIN (SELECT signupid, max(timecreated) as approvaltime
                            FROM {facetoface_signups_status}
                           WHERE statuscode = " . MDL_F2F_STATUS_APPROVED . "
                        GROUP BY signupid) lastapproval
                      ON status.signupid = lastapproval.signupid
                     AND status.timecreated = lastapproval.approvaltime
                  WHERE status.statuscode = " . MDL_F2F_STATUS_APPROVED .
                        ")",
                        'base.id = approver.signupid',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE
                ),
        );

        // include some standard joins
        $this->add_user_table_to_joinlist($joinlist, 'base', 'userid');
        $this->add_course_table_to_joinlist($joinlist, 'facetoface', 'course', 'INNER');
        $this->add_context_table_to_joinlist($joinlist, 'course', 'id', CONTEXT_COURSE, 'INNER');
        // requires the course join
        $this->add_course_category_table_to_joinlist($joinlist,
                'course', 'category');
        $this->add_core_tag_tables_to_joinlist('core', 'course', $joinlist, 'facetoface', 'course');

        $this->add_facetoface_session_roles_to_joinlist($joinlist);

        $this->add_cohort_course_tables_to_joinlist($joinlist, 'facetoface', 'course');

        return $joinlist;
    }

    protected function define_columnoptions() {
        global $DB, $CFG;
        $intimezone = '';
        if (!empty($CFG->facetoface_displaysessiontimezones)) {
            $intimezone = '_in_timezone';
        }

        $usernamefieldscreator = totara_get_all_user_name_fields_join('creator');
        $usernamefieldsbooked = totara_get_all_user_name_fields_join('bookedby');
        $columnoptions = array(
                new rb_column_option(
                        'session',                  // Type.
                        'capacity',                 // Value.
                        get_string('sesscapacity', 'rbsource_facetofacesessions'),    // Name.
                        'sessions.capacity',        // Field.
                        array('joins' => 'sessions', 'dbdatatype' => 'integer')         // Options array.
                ),
                new rb_column_option(
                        'session',
                        'numattendees',
                        get_string('numattendees', 'rbsource_facetofacesessions'),
                        'attendees.number',
                        array('joins' => 'attendees', 'dbdatatype' => 'integer')
                ),
                new rb_column_option(
                        'session',
                        'details',
                        get_string('sessdetails', 'rbsource_facetofacesessions'),
                        'sessions.details',
                        array(
                                'joins'        => 'sessions',
                                'displayfunc'  => 'editor_textarea',
                                'extrafields'  => array(
                                        'filearea'  => '\'session\'',
                                        'component' => '\'mod_facetoface\'',
                                        'fileid'    => 'sessions.id',
                                        'context'   => '\'context_module\'',
                                        'recordid'  => 'sessions.facetoface'
                                ),
                                'dbdatatype'   => 'text',
                                'outputformat' => 'text'
                        )
                ),
                new rb_column_option(
                        'session',
                        'signupperiod',
                        get_string('signupperiod', 'rbsource_facetofacesessions'),
                        'sessions.registrationtimestart',
                        array(
                                'joins'        => array('sessions', 'sessiondate'),
                                'dbdatatype'   => 'timestamp',
                                'displayfunc'  => 'nice_two_datetime_in_timezone',
                                'extrafields'  => array('finishdate' => 'sessions.registrationtimefinish',
                                                        'timezone'   => 'sessiondate.sessiontimezone'),
                                'outputformat' => 'text'
                        )
                ),
                new rb_column_option(
                        'status',
                        'statuscode',
                        get_string('status', 'rbsource_facetofacesessions'),
                        'status.statuscode',
                        array(
                                'joins'       => 'status',
                                'displayfunc' => 'signup_status',
                        )
                ),
                new rb_column_option(
                        'facetoface',
                        'name',
                        get_string('ftfname', 'rbsource_facetofacesessions'),
                        'facetoface.name',
                        array('joins'        => 'facetoface',
                              'dbdatatype'   => 'char',
                              'outputformat' => 'text')
                ),
                new rb_column_option(
                        'facetoface',
                        'namelink',
                        get_string('ftfnamelink', 'rbsource_facetofacesessions'),
                        "facetoface.name",
                        array(
                                'joins'          => array('facetoface', 'sessions'),
                                'displayfunc'    => 'seminar_name_link',
                                'defaultheading' => get_string('ftfname', 'rbsource_facetofacesessions'),
                                'extrafields'    => array('activity_id' => 'sessions.facetoface'),
                        )
                ),
                new rb_column_option(
                        'status',
                        'createdby',
                        get_string('createdby', 'rbsource_facetofacesessions'),
                        $DB->sql_concat_join("' '", $usernamefieldscreator),
                        array(
                                'joins'       => 'creator',
                                'displayfunc' => 'link_user',
                                'extrafields' => array_merge(array('id' => 'creator.id'), $usernamefieldscreator),
                        )
                ),
                new rb_column_option(
                        'date',
                        'sessiondate',
                        get_string('sessdate', 'rbsource_facetofacesessions'),
                        'sessiondate.timestart',
                        array(
                                'extrafields' => array(
                                        'timezone' => 'sessiondate.sessiontimezone'),
                                'joins'       => 'sessiondate',
                                'displayfunc' => 'event_date',
                                'dbdatatype'  => 'timestamp'
                        )
                ),
                new rb_column_option(
                        'date',
                        'sessiondate_link',
                        get_string('sessdatelink', 'rbsource_facetofacesessions'),
                        'sessiondate.timestart',
                        array(
                                'joins'          => 'sessiondate',
                                'displayfunc'    => 'event_date_link',
                                'defaultheading' => get_string('sessdate', 'rbsource_facetofacesessions'),
                                'extrafields'    => array(
                                        'session_id' => 'base.sessionid',
                                        'timezone'   => 'sessiondate.sessiontimezone'),
                                'dbdatatype'     => 'timestamp'
                        )
                ),
                new rb_column_option(
                        'date',
                        'datefinish',
                        get_string('sessdatefinish', 'rbsource_facetofacesessions'),
                        'sessiondate.timefinish',
                        array(
                                'extrafields' => array(
                                        'timezone' => 'sessiondate.sessiontimezone'),
                                'joins'       => 'sessiondate',
                                'displayfunc' => 'event_date',
                                'dbdatatype'  => 'timestamp')
                ),
                new rb_column_option(
                        'date',
                        'timestart',
                        get_string('sessstart', 'rbsource_facetofacesessions'),
                        'sessiondate.timestart',
                        array(
                                'extrafields' => array(
                                        'timezone' => 'sessiondate.sessiontimezone'),
                                'joins'       => 'sessiondate',
                                'displayfunc' => 'nice_time' . $intimezone,
                                'dbdatatype'  => 'timestamp'
                        )
                ),
                new rb_column_option(
                        'date',
                        'timefinish',
                        get_string('sessfinish', 'rbsource_facetofacesessions'),
                        'sessiondate.timefinish',
                        array(
                                'extrafields' => array(
                                        'timezone' => 'sessiondate.sessiontimezone'),
                                'joins'       => 'sessiondate',
                                'displayfunc' => 'nice_time' . $intimezone,
                                'dbdatatype'  => 'timestamp'
                        )
                ),
                new rb_column_option(
                        'date',
                        'localsessionstartdate',
                        get_string('localsessstartdate', 'rbsource_facetofacesessions'),
                        'sessiondate.timestart',
                        array(
                                'joins'          => 'sessiondate',
                                'displayfunc'    => 'local_event_date',
                                'defaultheading' => get_string('sessdate', 'rbsource_facetofacesessions'),
                                'dbdatatype'     => 'timestamp'
                        )
                ),
                new rb_column_option(
                        'date',
                        'localsessionfinishdate',
                        get_string('localsessfinishdate', 'rbsource_facetofacesessions'),
                        'sessiondate.timefinish',
                        array(
                                'joins'          => 'sessiondate',
                                'displayfunc'    => 'local_event_date',
                                'defaultheading' => get_string('sessdatefinish', 'rbsource_facetofacesessions'),
                                'dbdatatype'     => 'timestamp'
                        )
                ),
                new rb_column_option(
                        'session',
                        'cancellationdate',
                        get_string('cancellationdate', 'rbsource_facetofacesessions'),
                        'cancellationstatus.timecreated',
                        array('joins' => 'cancellationstatus', 'displayfunc' => 'nice_datetime', 'dbdatatype' => 'timestamp')
                ),
                new rb_column_option(
                        'session',
                        'bookedby',
                        get_string('bookedby', 'rbsource_facetofacesessions'),
                        $DB->sql_concat_join("' '", $usernamefieldsbooked),
                        array(
                                'joins'       => 'bookedby',
                                'displayfunc' => 'link_user',
                                'extrafields' => array_merge(array('id' => 'bookedby.id'), $usernamefieldsbooked),
                        )
                ),
                new rb_column_option(
                        'status',
                        'timecreated',
                        get_string('timeofsignup', 'rbsource_facetofacesessions'),
                        '(SELECT MAX(timecreated)
                    FROM {facetoface_signups_status}
                    WHERE signupid = base.id AND statuscode IN (' . MDL_F2F_STATUS_BOOKED . ', ' . MDL_F2F_STATUS_WAITLISTED . '))',
                        array(
                                'displayfunc' => 'nice_datetime',
                                'dbdatatype'  => 'timestamp'
                        )
                ),
                new rb_column_option(
                        'approver',
                        'approvername',
                        get_string('approvername', 'rbsource_facetofacesessions'),
                        'approver.approverid',
                        array('joins'       => 'approver',
                              'displayfunc' => 'approvername')
                ),
                new rb_column_option(
                        'approver',
                        'approveremail',
                        get_string('approveremail', 'rbsource_facetofacesessions'),
                        'approver.approverid',
                        array('joins'       => 'approver',
                              'displayfunc' => 'approveremail')
                ),
                new rb_column_option(
                        'approver',
                        'approvaltime',
                        get_string('approvertime', 'rbsource_facetofacesessions'),
                        'approver.approvaltime',
                        array('joins'       => 'approver',
                              'displayfunc' => 'nice_datetime')
                ),
                new rb_column_option(
                        'session',
                        'cancelledstatus',
                        get_string('cancelledstatus', 'rbsource_facetofacesessions'),
                        'sessions.cancelledstatus',
                        array(
                                'displayfunc' => 'show_cancelled_status',
                                'joins'       => 'sessions',
                                'dbdatatype'  => 'integer'
                        )
                ),
        );

        if (!get_config(null, 'facetoface_hidecost')) {
            $columnoptions[] = new rb_column_option(
                    'session',
                    'normalcost',
                    get_string('normalcost', 'rbsource_facetofacesessions'),
                    'sessions.normalcost',
                    array(
                            'joins'        => 'sessions',
                            'dbdatatype'   => 'char',
                            'outputformat' => 'text'
                    )
            );
            if (!get_config(null, 'facetoface_hidediscount')) {
                $columnoptions[] = new rb_column_option(
                        'session',
                        'discountcost',
                        get_string('discountcost', 'rbsource_facetofacesessions'),
                        'sessions.discountcost',
                        array(
                                'joins'        => 'sessions',
                                'dbdatatype'   => 'char',
                                'outputformat' => 'text'
                        )
                );
                $columnoptions[] = new rb_column_option(
                        'session',
                        'discountcode',
                        get_string('discountcode', 'rbsource_facetofacesessions'),
                        'base.discountcode',
                        array('dbdatatype'   => 'text',
                              'outputformat' => 'text')
                );
            }
        }

        // include some standard columns
        $this->add_user_fields_to_columns($columnoptions);
        $this->add_staff_details_to_columns($columnoptions);
        $this->add_course_fields_to_columns($columnoptions);
        $this->add_course_category_fields_to_columns($columnoptions);
        $this->add_core_tag_fields_to_columns('core', 'course', $columnoptions);

        $this->add_facetoface_session_roles_to_columns($columnoptions);

        $this->add_cohort_course_fields_to_columns($columnoptions);

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
                        'status',
                        'statuscode',
                        get_string('status', 'rbsource_facetofacesessions'),
                        'multicheck',
                        array(
                                'selectfunc' => 'session_status_list',
                                'attributes' => rb_filter_option::select_width_limiter(),
                        )
                ),
                new rb_filter_option(
                        'date',
                        'sessiondate',
                        get_string('sessdate', 'rbsource_facetofacesessions'),
                        'date'
                ),
                new rb_filter_option(
                        'date',
                        'timestart',
                        get_string('sessstart', 'rbsource_facetofacesessions'),
                        'date',
                        array('includetime' => true)
                ),
                new rb_filter_option(
                        'date',
                        'timefinish',
                        get_string('sessfinish', 'rbsource_facetofacesessions'),
                        'date',
                        array('includetime' => true)
                ),
                new rb_filter_option(
                        'session',
                        'capacity',
                        get_string('sesscapacity', 'rbsource_facetofacesessions'),
                        'number'
                ),
                new rb_filter_option(
                        'session',
                        'details',
                        get_string('sessdetails', 'rbsource_facetofacesessions'),
                        'text'
                ),
                new rb_filter_option(
                        'session',
                        'bookedby',
                        get_string('bookedby', 'rbsource_facetofacesessions'),
                        'text'
                ),
                new rb_filter_option(
                        'status',
                        'createdby',
                        get_string('createdby', 'rbsource_facetofacesessions'),
                        'text'
                ),
                new rb_filter_option(
                        'session',
                        'cancelledstatus',
                        get_string('cancelledstatus', 'rbsource_facetofacesessions'),
                        'select',
                        array(
                                'selectfunc' => 'cancel_status',
                                'attributes' => rb_filter_option::select_width_limiter(),
                        )
                ),
        );

        if (!get_config(null, 'facetoface_hidecost')) {
            $filteroptions[] = new rb_filter_option(
                    'session',
                    'normalcost',
                    get_string('normalcost', 'rbsource_facetofacesessions'),
                    'text'
            );
            if (!get_config(null, 'facetoface_hidediscount')) {
                $filteroptions[] = new rb_filter_option(
                        'session',
                        'discountcost',
                        get_string('discountcost', 'rbsource_facetofacesessions'),
                        'text'
                );
                $filteroptions[] = new rb_filter_option(
                        'session',
                        'discountcode',
                        get_string('discountcode', 'rbsource_facetofacesessions'),
                        'text'
                );
            }
        }

        // include some standard filters
        $this->add_user_fields_to_filters($filteroptions);
        $this->add_staff_fields_to_filters($filteroptions);
        $this->add_course_fields_to_filters($filteroptions);
        $this->add_course_category_fields_to_filters($filteroptions);
        $this->add_core_tag_fields_to_filters('core', 'course', $filteroptions);

        // add session role fields to filters
        $this->add_facetoface_session_role_fields_to_filters($filteroptions);

        $this->add_cohort_course_fields_to_filters($filteroptions);

        return $filteroptions;
    }

    public function rb_filter_cancel_status() {
        $selectchoices = array(
                '1' => get_string('cancelled', 'rbsource_facetofacesessions')
        );

        return $selectchoices;
    }

    protected function define_contentoptions() {
        $contentoptions = array();

        $contentoptions[] = new rb_content_option(
                'date',
                get_string('thedate', 'rbsource_facetofacesessions'),
                'sessiondate.timefinish',
                'sessiondate'
        );
        $contentoptions[] = new rb_content_option(
                'session_roles',
                get_string('sessionroles', 'rbsource_facetofacesessions'),
                'base.sessionid'
        );

        $contentoptions[] = new rb_content_option(
                'user',
                get_string('user', 'local_reportbuilder'),
                ['userid' => 'base.userid']
        );

        $contentoptions[] = new rb_content_option(
                'enrolledcourses',
                get_string('enrolledcourses', 'local_reportbuilder'),
                'facetoface.course',
                'facetoface'
        );

        return $contentoptions;
    }

    protected function define_paramoptions() {
        $paramoptions = array(
                new rb_param_option(
                        'userid',         // parameter name
                        'base.userid'     // field
                ),
                new rb_param_option(
                        'courseid',
                        'course.id',
                        'course'
                ),
                new rb_param_option(
                        'status',
                        'status.statuscode',
                        'status'
                ),
                new rb_param_option(
                        'sessionid',
                        'base.sessionid'
                ),
        );

        return $paramoptions;
    }

    protected function define_defaultcolumns() {
        $defaultcolumns = array(
                array(
                        'type'  => 'user',
                        'value' => 'namelink',
                ),
                array(
                        'type'  => 'course',
                        'value' => 'courselink',
                ),
                array(
                        'type'  => 'date',
                        'value' => 'sessiondate',
                ),
        );

        return $defaultcolumns;
    }

    protected function define_requiredcolumns() {
        $requiredcolumns = array();

        $requiredcolumns[] = new rb_column(
                'visibility',
                'id',
                '',
                "course.id",
                array(
                        'joins'    => 'course',
                        'required' => 'true',
                        'hidden'   => 'true'
                )
        );

        $requiredcolumns[] = new rb_column(
                'visibility',
                'visible',
                '',
                "course.visible",
                array(
                        'joins'    => 'course',
                        'required' => 'true',
                        'hidden'   => 'true'
                )
        );

        $requiredcolumns[] = new rb_column(
                'ctx',
                'id',
                '',
                "ctx.id",
                array(
                        'joins'    => 'ctx',
                        'required' => 'true',
                        'hidden'   => 'true'
                )
        );

        return $requiredcolumns;
    }

    protected function define_defaultfilters() {
        $defaultfilters = array(
                array(
                        'type'  => 'user',
                        'value' => 'fullname',
                ),
                array(
                        'type'     => 'course',
                        'value'    => 'fullname',
                        'advanced' => 1,
                ),
                array(
                        'type'     => 'status',
                        'value'    => 'statuscode',
                        'advanced' => 1,
                ),
                array(
                        'type'     => 'date',
                        'value'    => 'sessiondate',
                        'advanced' => 1,
                ),
        );

        return $defaultfilters;
    }

    //
    //
    // Face-to-face specific display functions
    //
    //
    /**
     * Convert a f2f activity name into a link to that activity.
     *
     * @param $name Seminar name
     * @param $row Extra data from the report row.
     * @return string The content to display.
     * @deprecated Since Totara 9.2
     *
     */
    function rb_display_link_f2f($name, $row) {
        global $OUTPUT;

        debugging('The rb_display_link_f2f function has been deprecated. Please use \'seminar_name_link\' for the display function instead.',
                DEBUG_DEVELOPER);
        if (empty($name)) {
            return '';
        }
        $activityid = $row->activity_id;
        return $OUTPUT->action_link(new moodle_url('/mod/facetoface/view.php', array('f' => $activityid)), $name);
    }

    // Override user display function to show 'Reserved' for reserved spaces.
    function rb_display_link_user($user, $row, $isexport = false) {
        return parent::rb_display_link_user($user, $row, $isexport);
    }

    // Override user display function to show 'Reserved' for reserved spaces.
    function rb_display_link_user_icon($user, $row, $isexport = false) {
        return parent::rb_display_link_user_icon($user, $row, $isexport);
    }

    /**
     * Display the email address of the approver
     *
     * @param int $approverid
     * @param object $row
     * @return string
     */
    function rb_display_approveremail($approverid, $row) {
        if (empty($approverid)) {
            return '';
        } else {
            $approver = core_user::get_user($approverid);
            return $approver->email;
        }
    }

    /**
     * Display the full name of the approver
     *
     * @param int $approverid
     * @param object $row
     * @return string
     */
    function rb_display_approvername($approverid, $row) {
        if (empty($approverid)) {
            return '';
        } else {
            $approver = core_user::get_user($approverid);
            return fullname($approver);
        }
    }

    // Override user display function to show 'Reserved' for reserved spaces.
    function rb_display_user($user, $row, $isexport = false) {
        return parent::rb_display_user($user, $row, $isexport);
    }


    //
    //
    // Source specific filter display methods
    //
    //

    function rb_filter_session_status_list() {
        global $CFG;

        include_once($CFG->dirroot . '/mod/facetoface/lib.php');

        $output = array();
        foreach (facetoface_statuses() as $code => $statusitem) {
            $output[$code] = get_string('status_' . $statusitem, 'facetoface');
        }
        // show most completed option first in pulldown
        return array_reverse($output, true);

    }

    function rb_filter_coursedelivery_list() {
        $coursedelivery = array();
        $coursedelivery[0] = get_string('no');
        $coursedelivery[1] = get_string('yes');
        return $coursedelivery;
    }

    /**
     * Reformat a timestamp and timezone into a date, showing nothing if invalid or null
     *
     * @param integer $date Unix timestamp
     * @param object $row Object containing all other fields for this row (which should include a timezone field)
     *
     * @return string Date in a nice format
     */
    function rb_display_show_cancelled_status($status) {
        if ($status == 1) {
            return get_string('cancelled', 'rbsource_facetofacesessions');
        }
        return "";
    }

    public function post_config(reportbuilder $report) {
        $userid = $report->reportfor;
        if (isset($report->embedobj->embeddedparams['userid'])) {
            $userid = $report->embedobj->embeddedparams['userid'];
        }
        $report->set_post_config_restrictions($report->post_config_visibility_where('course', 'course', $userid));
    }

} // end of rb_source_facetoface_sessions class

