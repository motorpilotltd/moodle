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
use rb_base_source;
use rb_join;
use rb_column_option;
use rb_filter_option;
use html_writer;
use moodle_url;
use core_text;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/mod/facetoface/lib.php');

abstract class base extends rb_base_source {
    public function __construct() {
        $this->usedcomponents[] = 'mod_facetoface';
        parent::__construct();
    }

    /**
     * Add common facetoface columns
     * Requires 'sessions' and 'facetoface' joins
     * @param array $columnoptions
     * @param string $joinsessions
     */
    public function add_facetoface_common_to_columns(&$columnoptions, $joinsessions = 'sessions') {
        $columnoptions[] = new rb_column_option(
            'facetoface',
            'facetofaceid',
            get_string('ftfid', 'rbsource_facetofacebase'),
            'facetoface.id',
            array(
                'joins' => array('facetoface'),
                'dbdatatype' => 'integer'
            )
        );

        $columnoptions[] = new rb_column_option(
            'facetoface',
            'name',
            get_string('ftfname', 'rbsource_facetofacesessions'),
            'facetoface.name',
            array(
                'joins' => array('facetoface')
            )
        );

        $columnoptions[] = new rb_column_option(
            'facetoface',
            'namelink',
            get_string('ftfnamelink', 'rbsource_facetofacesessions'),
            "facetoface.name",
            array(
                'joins' => array('facetoface', $joinsessions),
                'displayfunc' => 'seminar_name_link',
                'defaultheading' => get_string('ftfname', 'rbsource_facetofacesessions'),
                'extrafields' => array('activity_id' => $joinsessions . '.facetoface'),
            )
        );
    }

   /**
    * Add common facetoface session columns
    * Requires 'sessions' join and custom named join to {facetoface_sessions_dates} (by default 'base')
    * @param array $columnoptions
    * @param string $sessiondatejoin Join that provides {facetoface_sessions_dates}
    */
    public function add_session_common_to_columns(&$columnoptions, $sessiondatejoin = 'base') {
        $columnoptions[] = new rb_column_option(
            'facetoface',
            'sessionid',
            get_string('sessionid', 'rbsource_facetofacebase'),
            'sessions.id',
            array(
                'joins' => 'sessions',
                'dbdatatype' => 'integer'
            )
        );

        $columnoptions[] = new rb_column_option(
            'session',
            'capacity',
            get_string('sesscapacity', 'rbsource_facetofacesessions'),
            'sessions.capacity',
            array(
                'joins' => 'sessions',
                'dbdatatype' => 'integer'
            )
        );
        $columnoptions[] = new rb_column_option(
            'date',
            'sessionstartdate',
            get_string('sessstartdatetime', 'rbsource_facetofacebase'),
            "{$sessiondatejoin}.timestart",
            array(
                'joins' => array($sessiondatejoin),
                'extrafields' => array('timezone' => "{$sessiondatejoin}.sessiontimezone"),
                'displayfunc' => 'event_date',
                'dbdatatype' => 'timestamp'
            )
        );

        $columnoptions[] = new rb_column_option(
            'date',
            'sessionfinishdate',
            get_string('sessfinishdatetime', 'rbsource_facetofacebase'),
            "{$sessiondatejoin}.timefinish",
            array(
                'joins' => array($sessiondatejoin),
                'displayfunc' => 'event_date',
                'dbdatatype' => 'timestamp'
            )
        );

        $columnoptions[] = new rb_column_option(
            'date',
            'localsessionstartdate',
            get_string('localsessstartdate', 'rbsource_facetofacesessions'),
            "{$sessiondatejoin}.timestart",
            array(
                'joins' => array($sessiondatejoin),
                'displayfunc' => 'local_event_date',
                'dbdatatype' => 'timestamp',
                'defaultheading' => get_string('sessstartdatetime', 'rbsource_facetofacebase'),
            )
        );

        $columnoptions[] = new rb_column_option(
            'date',
            'localsessionfinishdate',
            get_string('localsessfinishdate', 'rbsource_facetofacesessions'),
            "{$sessiondatejoin}.timefinish",
            array(
                'joins' => array($sessiondatejoin),
                'displayfunc' => 'local_event_date',
                'dbdatatype' => 'timestamp',
                'defaultheading' => get_string('sessfinishdatetime', 'rbsource_facetofacebase'),
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
                'joins' => array('attendees', 'sessions'),
                'dbdatatype' => 'integer',
                'displayfunc' => 'numattendeeslink',
                'defaultheading' => get_string('numattendees', 'rbsource_facetofacesessions'),
                'extrafields' => array(
                    'session' => 'sessions.id'
                )
            )
        );
    }

    /**
     * Provides 'currentuserstatus' join required for the current signed in users status
     * @param array $joinlist
     */
    public function add_facetoface_currentuserstatus_to_joinlist(&$joinlist) {
        global $USER;

        $joinlist[] = new rb_join(
            'currentuserstatus',
            'LEFT',
            "(SELECT su.sessionid, su.userid, ss.id AS ssid, ss.statuscode AS statuscode
                FROM {facetoface_signups} su
                JOIN {facetoface_signups_status} ss
                    ON su.id = ss.signupid
                WHERE ss.superceded = 0
                AND su.userid = {$USER->id})",
            'currentuserstatus.sessionid = base.id',
            REPORT_BUILDER_RELATION_ONE_TO_ONE
        );
    }

    /**
     * Add the current signed in users status column
     *
     * @param array $columnoptions
     */
    public function add_facetoface_currentuserstatus_to_columns(&$columnoptions) {
        $columnoptions[] =
            new rb_column_option(
                'session',
                'currentuserstatus',
                get_string('userstatus', 'rbsource_facetofaceevents'),
                "CASE WHEN currentuserstatus.statuscode > 0 THEN currentuserstatus.statuscode ELSE NULL END",
                array(
                    'joins' => array('currentuserstatus'),
                    'displayfunc' => 'signup_status',
                    'defaultheading' => get_string('userstatusdefault', 'rbsource_facetofaceevents')
                )
            );
    }

    /**
     * Add the current signed-in users status filter options
     * @param array $filteroptions
     */
    protected function add_facetoface_currentuserstatus_to_filters(array &$filteroptions) {
        $filteroptions[] =
            new rb_filter_option(
                'session',
                'currentuserstatus',
                get_string('userstatus', 'rbsource_facetofaceevents'),
                'select',
                array(
                    'selectchoices' => self::get_currentuserstatus_options(),
                )
            );
    }

    /**
     * Provides 'sessions', 'attendess', 'facetoface', 'room' joins to join list
     * Requires join that provides relevant "sessionid" field (by default used 'base')
     * @param array $joinlist
     * @param string $sessiondatejoin join to {facetoface_sessions_dates}
     */
    public function add_session_common_to_joinlist(&$joinlist, $sessiondatejoin = 'base') {
        $global_restriction_join_su = $this->get_global_report_restriction_join('su', 'userid');

        $joinlist[] = new rb_join(
            'sessions',
            'INNER',
            '{facetoface_sessions}',
            "(sessions.id = {$sessiondatejoin}.sessionid)",
            REPORT_BUILDER_RELATION_ONE_TO_MANY,
            $sessiondatejoin
        );

        $joinlist[] = new rb_join(
            'attendees',
            'LEFT',
            "(SELECT su.sessionid, count(ss.id) AS number
                FROM {facetoface_signups} su
                {$global_restriction_join_su}
                JOIN {facetoface_signups_status} ss
                    ON su.id = ss.signupid
                WHERE ss.superceded=0 AND ss.statuscode >= " . MDL_F2F_STATUS_APPROVED ."
                GROUP BY su.sessionid)",
            "attendees.sessionid = {$sessiondatejoin}.sessionid",
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            $sessiondatejoin
        );

        $joinlist[] = new rb_join(
            'facetoface',
            'LEFT',
            '{facetoface}',
            '(facetoface.id = sessions.facetoface)',
            REPORT_BUILDER_RELATION_ONE_TO_MANY,
            'sessions'
        );
    }

    /*
     * Adds any facetoface session roles to the $joinlist array
     *
     * @param array &$joinlist Array of current join options
     *                         Passed by reference and updated if
     *                         any session roles exist
     */
    public function add_facetoface_session_roles_to_joinlist(&$joinlist, $sessionidfield = 'base.sessionid') {
        global $DB;
        // add joins for the following roles as "session_role_X" and
        // "session_role_user_X"
        $sessionroles = self::get_session_roles();
        if (empty($sessionroles)) {
            return;
        }

        // Fields.
        $usernamefields = totara_get_all_user_name_fields_join('role_user', null);
        $userlistcolumn = $this->rb_group_comma_list($DB->sql_concat_join("' '", $usernamefields));
        // Add id to fields.
        $usernamefieldsid = array_merge(array('role_user.id' => 'userid'), $usernamefields);
        // Length of resulted concatenated fields.
        $lengthfield = array('lengths' => $DB->sql_length($DB->sql_concat_join("' '", $usernamefieldsid)));
        // Final column: concat(strlen(concat(fields)),concat(fields)) so we know length of each username with id.
        $usernamefieldslink = array_merge($lengthfield, $usernamefieldsid);
        $userlistcolumnlink = $this->rb_group_comma_list($DB->sql_concat_join("' '", $usernamefieldslink));

        foreach ($sessionroles as $role) {
            $field = $role->shortname;
            $roleid = $role->id;

            $sql = "(SELECT session_role.sessionid AS sessionid, session_role.roleid AS roleid, %s AS userlist
                    FROM {user} role_user
                      INNER JOIN {facetoface_session_roles} session_role ON (role_user.id = session_role.userid)
                    GROUP BY session_role.sessionid, session_role.roleid)";

            $userkey = "session_role_user_$field";
            $joinlist[] = new rb_join(
                $userkey,
                'LEFT',
                sprintf($sql, $userlistcolumn),
                "($userkey.sessionid = $sessionidfield AND $userkey.roleid = $roleid)",
                REPORT_BUILDER_RELATION_ONE_TO_MANY
            );

            $userkeylink = $userkey . 'link';
            $joinlist[] = new rb_join(
                $userkeylink,
                'LEFT',
                sprintf($sql, $userlistcolumnlink),
                "($userkeylink.sessionid = $sessionidfield AND $userkeylink.roleid = $roleid)",
                REPORT_BUILDER_RELATION_ONE_TO_MANY
            );
        }
    }

    /*
     * Adds any session role fields to the $columnoptions array
     *
     * @param array &$columnoptions Array of current column options
     *                              Passed by reference and updated if
     *                              any session roles exist
     * @return boolean True if session roles exist
     */
    function add_facetoface_session_roles_to_columns(&$columnoptions) {
        $sessionroles = self::get_session_roles();
        if (empty($sessionroles)) {
            return;
        }

        foreach ($sessionroles as $sessionrole) {
            $field = $sessionrole->shortname;
            $name = $sessionrole->name;
            if (empty($name)) {
                $name = role_get_name($sessionrole);
            }

            $userkey = "session_role_user_$field";

            // User name.
            $columnoptions[] = new rb_column_option(
                'role',
                $field . '_name',
                get_string('sessionrole', 'rbsource_facetofacesessions', $name),
                "$userkey.userlist",
                array(
                    'joins' => $userkey,
                    'dbdatatype' => 'char',
                    'outputformat' => 'text'
                )
            );

            // User name with link to profile.
            $userkeylink = $userkey . 'link';
            $columnoptions[] = new rb_column_option(
                'role',
                $field . '_namelink',
                get_string('sessionrolelink', 'rbsource_facetofacesessions', $name),
                "$userkeylink.userlist",
                array(
                    'joins' => $userkeylink,
                    'dbdatatype' => 'char',
                    'outputformat' => 'text',
                    'defaultheading' => get_string('sessionrole', 'rbsource_facetofacesessions', $name),
                    'displayfunc' => 'coded_link_user',
                )
            );
        }
        return true;
    }

    /**
     * Add session booking and overall status columns
     * Requires 'sessions' join, and 'cntbookings' join provided by @see rb_facetoface_base_source::add_session_status_to_joinlist()
     *
     * If you call this function in order to get the correct highlighting you will need to extend the CSS rules in
     * mod/facetoface/styles.css and add a line like the following:
     *     .reportbuilder-table[data-source="rbsource_facetofacesummary"] tr
     *
     * Search for that and you'll see what you need to do.
     *
     * @param array $columnoptions
     * @param string $joindates Join name that provide {facetoface_sessions_dates}
     * @param string $joinsessions Join name that provide {facetoface_sessions}
     */
    public function add_session_status_to_columns(&$columnoptions, $joindates = 'base', $joinsessions = 'sessions') {
        $now = time();
            // TODO: TL-8187 Cancellation status ("Face-to-face cancellations" specification).
        $columnoptions[] = new rb_column_option(
            'session',
            'overallstatus',
            get_string('overallstatus', 'rbsource_facetofacesummary'),

            "( CASE WHEN cancelledstatus <> 0 THEN 'cancelled'
                    WHEN timestart IS NULL OR timestart = 0 OR timestart > {$now} THEN 'upcoming'
                    WHEN {$now} > timestart AND {$now} < timefinish THEN 'started'
                    WHEN {$now} > timefinish THEN 'ended'
                    ELSE NULL END
             )",
            array(
                'joins' => array($joindates, $joinsessions),
                'displayfunc' => 'overall_status',
                'extrafields' => array(
                    'timestart' => "{$joindates}.timestart",
                    'timefinish' => "{$joindates}.timefinish",
                    'timezone' => "{$joindates}.sessiontimezone",
                )
            )
        );

        $columnoptions[] = new rb_column_option(
            'session',
            'bookingstatus',
            get_string('bookingstatus', 'rbsource_facetofacesummary'),
            "(CASE WHEN {$now} > {$joindates}.timefinish AND cntsignups < {$joinsessions}.capacity THEN 'ended'
                   WHEN cancelledstatus <> 0 THEN 'cancelled'
                   WHEN cntsignups < {$joinsessions}.capacity THEN 'available'
                   WHEN cntsignups = {$joinsessions}.capacity THEN 'fullybooked'
                   WHEN cntsignups > {$joinsessions}.capacity THEN 'overbooked'
                   ELSE NULL END)",
            array(
                'joins' => array($joindates, 'cntbookings', $joinsessions),
                'displayfunc' => 'booking_status',
                'dbdatatype' => 'char',
                'extrafields' => array(
                    'capacity' => "{$joinsessions}.capacity"
                )
            )
        );
    }

    /**
     * Add joins required by @see rb_facetoface_base_source::add_session_status_to_columns()
     * @param array $joinlist
     * @param string $join 'sessions' table to join to
     * @param string $field 'id' field (from sessions table) to join to
     */
    public function add_session_status_to_joinlist(&$joinlist, $join = 'sessions', $field = 'id') {
        // No global restrictions here because status is absolute (e.g if it is overbooked then it is overbooked, even if user
        // cannot see all participants.
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
    }

    /**
     * Return list of user names linked to their profiles from string of concatenated user names, their ids,
     * and length of every name with id
     * @param string $name Concatenated list of names, ids, and lengths
     * @param \stdClass $row
     * @param bool $isexport
     * @return string
     */
    public function rb_display_coded_link_user($name, $row, $isexport = false) {
        // Concatenated names are provided as (kind of) pascal string beginning with id in the following format:
        // length_of_following_string.' '.id.' '.name.', '
        if (empty($name)) {
            return '';
        }
        $leftname = $name;
        $result = array();
        while(true) {
            $len = (int)$leftname; // Take string length.
            if (!$len) {
                break;
            }
            $idname = core_text::substr($leftname, core_text::strlen((string)$len)+1, $len, 'UTF-8');
            if (empty($idname)) {
                break;
            }
            $idendpos = core_text::strpos($idname, ' ');
            $id = (int)core_text::substr($idname, 0, $idendpos);
            if (!$id) {
                break;
            }
            $name = trim(core_text::substr($idname, $idendpos));
            $result[] = ($isexport) ? $name : html_writer::link(new moodle_url('/user/view.php', array('id' => $id)), $name);

            // length(length(idname)) + length(' ') + length(idname) + length(', ').
            $leftname = core_text::substr($leftname, core_text::strlen((string)$len)+1+$len+2);
        }
        return implode(', ', $result);
    }

    /*
     * Adds some common user field to the $filteroptions array
     *
     * @param array &$filteroptions Array of current filter options
     *                              Passed by reference and updated by
     *                              this method
     */
    protected function add_facetoface_session_role_fields_to_filters(&$filteroptions) {
        // auto-generate filters for session roles fields
        $sessionroles = self::get_session_roles();
        if (empty($sessionroles)) {
            return;
        }

        foreach ($sessionroles as $sessionrole) {
            $field = $sessionrole->shortname;
            $name = $sessionrole->name;
            if (empty($name)) {
                $name = role_get_name($sessionrole);
            }

            $filteroptions[] = new rb_filter_option(
                'role',
                $field . '_name',
                get_string('sessionrole', 'rbsource_facetofacesessions', $name),
                'text'
            );
        }
    }

    /**
     * Get session roles from list of allowed roles
     * @return array
     */
    protected static function get_session_roles() {
        global $DB;

        $allowedroles = get_config(null, 'facetoface_session_roles');
        if (!isset($allowedroles) || $allowedroles == '') {
            return array();
        }
        $allowedroles = explode(',', $allowedroles);

        list($allowedrolessql, $params) = $DB->get_in_or_equal($allowedroles);

        $sessionroles = $DB->get_records_sql("SELECT id, name, shortname FROM {role} WHERE id $allowedrolessql", $params);
        if (!$sessionroles) {
            return array();
        }
        return $sessionroles;
    }

    /**
     * Display opposite to rb_display_yes_no. E.g. zero value will be 'yes', and non-zero 'no'
     *
     * @param scalar $no
     * @param stdClass $row
     * @param bool $isexport
     */
    public function rb_display_no_yes($no, $row, $isexport = false) {
        return ($no) ? get_string('no') : get_string('yes');
    }

    /**
     * Display count of attendees and link to session attendees report page.
     *
     * @param int $cntattendees
     * @param stdClass $row
     * @param bool $isexport
     */
    public function rb_display_numattendeeslink($cntattendees, $row, $isexport = false) {
        if ($isexport) {
            return $cntattendees;
        }
        if (!$cntattendees) {
            $cntattendees = '0';
        }

        $viewattendees = get_string('viewattendees', 'mod_facetoface');

        $description = html_writer::span($viewattendees, 'sr-only');
        return html_writer::link(new moodle_url('/mod/facetoface/attendees.php', array('s' => $row->session)), $cntattendees . $description, array('title' => $viewattendees));

    }

    /**
     * Get currently supported booking status filter options
     * @return array
     */
    protected static function get_bookingstatus_options() {
        $statusopts = array(
            'underbooked' => get_string('status:underbooked', 'rbsource_facetofacesummary'),
            'available' => get_string('status:available', 'rbsource_facetofacesummary'),
            'fullybooked' => get_string('status:fullybooked', 'rbsource_facetofacesummary'),
            'overbooked' => get_string('status:overbooked', 'rbsource_facetofacesummary'),
        );
        return $statusopts;
    }

    /**
     * Get currently supported user booking status filter options
     * @return array
     */
    protected static function get_currentuserstatus_options() {
        $statusopts = array();
        foreach(facetoface_statuses() as $status => $name) {
            $statusopts[$status] =  get_string('userstatus:' . $name, 'rbsource_facetofaceevents');
        }

        return $statusopts;
    }

    /**
     * Filter by session overall status
     * @return array of options
     */
    public function rb_filter_overallstatus() {
        $statusopts = array(
            'upcoming' => get_string('status:upcoming', 'rbsource_facetofacesummary'),
            'cancelled' => get_string('status:cancelled', 'rbsource_facetofacesummary'),
            'started' => get_string('status:started', 'rbsource_facetofacesummary'),
            'ended' => get_string('status:ended', 'rbsource_facetofacesummary'),
        );
        return $statusopts;
    }
}
