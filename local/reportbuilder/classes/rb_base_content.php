<?php // $Id$
/*
 * This file is part of T0tara LMS
 *
 * Copyright (C) 2010 onwards T0tara Learning Solutions LTD
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
 * @author Simon Coggins <simon.coggins@t0taralms.com>
 * @package t0tara
 * @subpackage reportbuilder
 */

/**
 * Abstract base content class to be extended to create report builder
 * content restrictions. This file also contains some core content restrictions
 * that can be used by any report builder source
 *
 * Defines the properties and methods required by content restrictions
 */
abstract class rb_base_content {

    public $reportfor;

    /*
     * @param integer $reportfor User ID to determine who the report is for
     *                           Typically this will be $USER->id, except
     *                           in the case of scheduled reports run by cron
     */
    public function __construct($reportfor=null) {
        $this->reportfor = $reportfor;
    }

    /*
     * All sub classes must define the following functions
     */
    abstract public function sql_restriction($fields, $reportid);
    abstract public function text_restriction($title, $reportid);
    abstract public function form_template(&$mform, $reportid, $title);
    abstract public function form_process($reportid, $fromform);

}

///////////////////////////////////////////////////////////////////////////


/*
 * Restrict content by a particular user or group of users
 */
class rb_user_content extends rb_base_content {

    const USER_OWN = 1;
    const USER_COHORT_MEMBERS = 2;

    /**
     * Generate the SQL to apply this content restriction.
     *
     * @param array $field      SQL field to apply the restriction against
     * @param integer $reportid ID of the report
     *
     * @return array containing SQL snippet to be used in a WHERE clause, as well as array of SQL params
     */
    public function sql_restriction($field, $reportid) {
        global $CFG, $DB;

        $userfield = $field;
        $userid = $this->reportfor;

        // remove rb_ from start of classname.
        $type = substr(get_class($this), 3);
        $settings = reportbuilder::get_all_settings($reportid, $type);
        $restriction = isset($settings['who']) ? $settings['who'] : null;
        $userid = $this->reportfor;


        if (empty($restriction)) {
            return array(' (1 = 1) ', array());
        }

        $conditions = array();
        $params = array();

        $viewownrecord = ($restriction & self::USER_OWN) == self::USER_OWN;
        if ($viewownrecord) {
            $conditions[] = "{$userfield} = :self";
            $params['self'] = $userid;
        }

        $viewcohortrecords = ($restriction & self::USER_COHORT_MEMBERS) == self::USER_COHORT_MEMBERS;
        if ($viewcohortrecords) {
            $cohorts = array_keys(cohort_get_user_cohorts($this->reportfor));

            if (!empty($cohorts)) {
                list($cohortsql, $cohortparams) = $DB->get_in_or_equal($cohorts, SQL_PARAMS_NAMED);

                $conditions[] = "{$userfield} IN (SELECT userid FROM {cohort_members} WHERE cohortid $cohortsql)";
                $params += $cohortparams;
            }
        }

        $sql = implode(' OR ', $conditions);

        if (empty($sql)) {
            $sql = "1=1";
        }

        return array(" ($sql) ", $params);
    }

    /**
     * Generate a human-readable text string describing the restriction
     *
     * @param string $title Name of the field being restricted
     * @param integer $reportid ID of the report
     *
     * @return string Human readable description of the restriction
     */
    public function text_restriction($title, $reportid) {
        global $DB;

        // remove rb_ from start of classname
        $type = substr(get_class($this), 3);
        $settings = reportbuilder::get_all_settings($reportid, $type);
        $who = isset($settings['who']) ? $settings['who'] : 0;
        $userid = $this->reportfor;

        $user = $DB->get_record('user', array('id' => $userid));

        $strings = array();
        $strparams = array('field' => $title, 'user' => fullname($user));

        if (($who & self::USER_OWN) == self::USER_OWN) {
            $strings[] = get_string('contentdesc_userown', 'local_reportbuilder', $strparams);
        }

        if (($who & self::USER_COHORT_MEMBERS) == self::USER_COHORT_MEMBERS) {
            $strings[] = get_string('contentdesc_usercohortmembers', 'local_reportbuilder', $strparams);
        }

        if (empty($strings)) {
            return $title . ' ' . get_string('isnotfound', 'local_reportbuilder');
        }

        return implode(get_string('or', 'local_reportbuilder'), $strings);
    }


    /**
     * Adds form elements required for this content restriction's settings page
     *
     * @param object &$mform Moodle form object to modify (passed by reference)
     * @param integer $reportid ID of the report being adjusted
     * @param string $title Name of the field the restriction is acting on
     */
    public function form_template(&$mform, $reportid, $title) {

        // get current settings
        // remove rb_ from start of classname
        $type = substr(get_class($this), 3);
        $enable = reportbuilder::get_setting($reportid, $type, 'enable');
        $who = reportbuilder::get_setting($reportid, $type, 'who');

        $mform->addElement('header', 'user_header', get_string('showbyx',
            'local_reportbuilder', lcfirst($title)));
        $mform->setExpanded('user_header');
        $mform->addElement('checkbox', 'user_enable', '',
            get_string('showbasedonx', 'local_reportbuilder', lcfirst($title)));
        $mform->disabledIf('user_enable', 'contentenabled', 'eq', 0);
        $mform->setDefault('user_enable', $enable);
        $checkgroup = array();
        $checkgroup[] =& $mform->createElement('advcheckbox', 'user_who['.self::USER_OWN.']', '',
            get_string('userownrecords', 'local_reportbuilder'), null, array(0, 1));
        $mform->setType('user_who['.self::USER_OWN.']', PARAM_INT);
        $checkgroup[] =& $mform->createElement('advcheckbox', 'user_who['.self::USER_COHORT_MEMBERS.']', '',
            get_string('cohortmembers', 'local_reportbuilder'), null, array(0, 1));
        $mform->setType('user_who['.self::USER_COHORT_MEMBERS.']', PARAM_INT);

        $mform->addGroup($checkgroup, 'user_who_group',
            get_string('includeuserrecords', 'local_reportbuilder'), html_writer::empty_tag('br'), false);
        $usergroups = array(self::USER_OWN, self::USER_COHORT_MEMBERS);
        foreach ($usergroups as $usergroup) {
            // Bitwise comparison.
            if (($who & $usergroup) == $usergroup) {
                $mform->setDefault('user_who['.$usergroup.']', 1);
            }
        }
        $mform->disabledIf('user_who_group', 'contentenabled', 'eq', 0);
        $mform->disabledIf('user_who_group', 'user_enable', 'notchecked');
        $mform->addHelpButton('user_header', 'reportbuilderuser', 'local_reportbuilder');
    }


    /**
     * Processes the form elements created by {@link form_template()}
     *
     * @param integer $reportid ID of the report to process
     * @param object $fromform Moodle form data received via form submission
     *
     * @return boolean True if form was successfully processed
     */
    public function form_process($reportid, $fromform) {
        $status = true;
        // remove rb_ from start of classname
        $type = substr(get_class($this), 3);

        // enable checkbox option
        $enable = (isset($fromform->user_enable) &&
            $fromform->user_enable) ? 1 : 0;
        $status = $status && reportbuilder::update_setting($reportid, $type,
            'enable', $enable);

        // Who checkbox option.
        // Enabled options are stored as user_who[key] = 1 when enabled.
        // Key is a bitwise value to be summed and stored.
        $whovalue = 0;
        $who = isset($fromform->user_who) ?
            $fromform->user_who : array();
        foreach ($who as $key => $option) {
            if ($option) {
                $whovalue += $key;
            }
        }
        $status = $status && reportbuilder::update_setting($reportid, $type,
            'who', $whovalue);

        return $status;
    }
}

/*
 * Restrict content by a particular user or group of users
 */
class rb_costcentre_content extends rb_base_content {

    /**
     * Generate the SQL to apply this content restriction.
     *
     * @param array $field      SQL field to apply the restriction against
     * @param integer $reportid ID of the report
     *
     * @return array containing SQL snippet to be used in a WHERE clause, as well as array of SQL params
     */
    public function sql_restriction($field, $reportid) {
        global $CFG, $DB;

        $costcentrefield = $field;
        $userid = $this->reportfor;

        // remove rb_ from start of classname.
        $type = substr(get_class($this), 3);
        $settings = reportbuilder::get_all_settings($reportid, $type);
        $restriction = isset($settings['costcentre_permission']) ? $settings['costcentre_permission'] : null;


        if (empty($restriction)) {
            return array(' (1 = 1) ', array());
        }

        $permissions = explode(',', $restriction);

        $costcentres = \local_costcentre\costcentre::get_user_cost_centres($userid, $permissions);

        $conditions = [];
        $params = array();


        if (!empty($costcentres)) {
                list($costcentresql, $costcentreparams) = $DB->get_in_or_equal(array_keys($costcentres), SQL_PARAMS_NAMED);

                $conditions[] = "{$costcentrefield} {$costcentresql}";
                $params += $costcentreparams;
        }

        $sql = implode(' OR ', $conditions);

        if (empty($sql)) {
            $sql = "1=0";
        }

        return array(" $sql ", $params);
    }

    /**
     * Generate a human-readable text string describing the restriction
     *
     * @param string $title Name of the field being restricted
     * @param integer $reportid ID of the report
     *
     * @return string Human readable description of the restriction
     */
    public function text_restriction($title, $reportid) {
        global $DB;

        // remove rb_ from start of classname
        $type = substr(get_class($this), 3);
        $settings = reportbuilder::get_all_settings($reportid, $type);
        $who = isset($settings['who']) ? $settings['who'] : 0;
        $userid = $this->reportfor;

        $user = $DB->get_record('user', array('id' => $userid));

        $strings = array();
        $strparams = array('field' => $title, 'user' => fullname($user));

        if (($who & self::USER_OWN) == self::USER_OWN) {
            $strings[] = get_string('contentdesc_userown', 'local_reportbuilder', $strparams);
        }

        if (($who & self::USER_COHORT_MEMBERS) == self::USER_COHORT_MEMBERS) {
            $strings[] = get_string('contentdesc_usercohortmembers', 'local_reportbuilder', $strparams);
        }

        if (empty($strings)) {
            return $title . ' ' . get_string('isnotfound', 'local_reportbuilder');
        }

        return implode(get_string('or', 'local_reportbuilder'), $strings);
    }


    /**
     * Adds form elements required for this content restriction's settings page
     *
     * @param object &$mform Moodle form object to modify (passed by reference)
     * @param integer $reportid ID of the report being adjusted
     * @param string $title Name of the field the restriction is acting on
     */
    public function form_template(&$mform, $reportid, $title) {

        // get current settings
        // remove rb_ from start of classname
        $type = substr(get_class($this), 3);
        $enable = reportbuilder::get_setting($reportid, $type, 'enable');
        $costcentre_permissions = reportbuilder::get_setting($reportid, $type, 'costcentre_permission');
        $costcentre_permissions = explode(',', $costcentre_permissions);

        $mform->addElement('header', 'costcentreheader', get_string('showbyx',
            'local_reportbuilder', lcfirst($title)));
        $mform->setExpanded('costcentreheader');
        $mform->addElement('checkbox', 'costcentre_enable', '',
            get_string('showbasedonx', 'local_reportbuilder', lcfirst($title)));
        $mform->disabledIf('costcentre_enable', 'contentenabled', 'eq', 0);
        $mform->setDefault('costcentre_enable', $enable);
        $checkgroup = array();

        $mform->addElement('select', 'costcentre_permission', get_string('costcentreroles', 'local_reportbuilder'),
                \local_costcentre\costcentre::get_permission_list(), ['multiple' => true]);
        $mform->setType('costcentre_permission', PARAM_INT);
        $mform->setDefault('costcentre_permission', $costcentre_permissions);


        $mform->disabledIf('costcentre_permission', 'contentenabled', 'eq', 0);
        $mform->disabledIf('costcentre_permission', 'costcentre_enable', 'notchecked');
    }


    /**
     * Processes the form elements created by {@link form_template()}
     *
     * @param integer $reportid ID of the report to process
     * @param object $fromform Moodle form data received via form submission
     *
     * @return boolean True if form was successfully processed
     */
    public function form_process($reportid, $fromform) {
        $status = true;
        // remove rb_ from start of classname
        $type = substr(get_class($this), 3);

        // enable checkbox option
        $enable = (isset($fromform->costcentre_enable) &&
            $fromform->costcentre_enable) ? 1 : 0;
        $status = $status && reportbuilder::update_setting($reportid, $type,
            'enable', $enable);

        if (isset($fromform->costcentre_enable)) {
            $costcentres = implode(',', $fromform->costcentre_permission);
            $status = $status && reportbuilder::update_setting($reportid, $type,
                            'costcentre_permission', $costcentres);
        }


        return $status;
    }
}

class rb_courseregion_content extends rb_base_content {

    const USER_OWN = 1;
    const OTHER = 2;
    const ANY = 3;

    /**
     * Generate the SQL to apply this content restriction.
     *
     * @param array $field      SQL field to apply the restriction against
     * @param integer $reportid ID of the report
     *
     * @return array containing SQL snippet to be used in a WHERE clause, as well as array of SQL params
     */
    public function sql_restriction($field, $reportid) {
        global $DB;

        // remove rb_ from start of classname.
        $type = substr(get_class($this), 3);
        $settings = reportbuilder::get_all_settings($reportid, $type);

        $courses = [];

        if ($settings['regiontype'] == self::ANY) {
            return array(" $field IS NOT NULL ", []);
        }

        if ($settings['regiontype'] == self::USER_OWN) {
            $regionid = $this->getuserregion();
        } else if ($settings['regiontype'] == self::OTHER) {
            $regionid = $settings['region'];
        }

        if (isset($regionid) && $regionid !== 0) {
            $courses += $DB->get_records_sql('SELECT courseid FROM {local_regions_reg_cou} WHERE regionid = :regionid GROUP BY courseid', ['regionid' => $regionid]);
        }

        if ($settings['regiontype'] == self::OTHER || $settings['regiontype'] == self::USER_OWN || $regionid == 0) {
            $courses += $DB->get_records_sql('
                SELECT c.* 
                FROM {course} c
                LEFT JOIN {local_regions_reg_cou} rc ON c.id = rc.courseid
                WHERE rc.id IS NULL');
        }

        list($courseregionsql, $courseregionparams) = $DB->get_in_or_equal(array_keys($courses), SQL_PARAMS_NAMED);

        return [" {$field} {$courseregionsql} ", $courseregionparams];
    }

    /**
     * Generate a human-readable text string describing the restriction
     *
     * @param string $title Name of the field being restricted
     * @param integer $reportid ID of the report
     *
     * @return string Human readable description of the restriction
     */
    public function text_restriction($title, $reportid) {
        global $DB;

        // remove rb_ from start of classname
        $type = substr(get_class($this), 3);
        $settings = reportbuilder::get_all_settings($reportid, $type);
        $who = isset($settings['who']) ? $settings['who'] : 0;
        $userid = $this->reportfor;

        $user = $DB->get_record('user', array('id' => $userid));

        $strings = array();
        $strparams = array('field' => $title, 'user' => fullname($user));

        if (($who & self::USER_OWN) == self::USER_OWN) {
            $strings[] = get_string('contentdesc_userown', 'local_reportbuilder', $strparams);
        }

        if (($who & self::USER_COHORT_MEMBERS) == self::USER_COHORT_MEMBERS) {
            $strings[] = get_string('contentdesc_usercohortmembers', 'local_reportbuilder', $strparams);
        }

        if (empty($strings)) {
            return $title . ' ' . get_string('isnotfound', 'local_reportbuilder');
        }

        return implode(get_string('or', 'local_reportbuilder'), $strings);
    }


    /**
     * Adds form elements required for this content restriction's settings page
     *
     * @param object &$mform Moodle form object to modify (passed by reference)
     * @param integer $reportid ID of the report being adjusted
     * @param string $title Name of the field the restriction is acting on
     */
    public function form_template(&$mform, $reportid, $title) {
        global $DB;

        // get current settings
        // remove rb_ from start of classname
        $type = substr(get_class($this), 3);
        $enable = reportbuilder::get_setting($reportid, $type, 'enable');
        $courseregion_type = reportbuilder::get_setting($reportid, $type, 'regiontype');
        $regions = reportbuilder::get_setting($reportid, $type, 'region');

        $mform->addElement('header', 'courseregionheader', get_string('showbyx',
            'local_reportbuilder', lcfirst($title)));
        $mform->setExpanded('courseregionheader');
        $mform->addElement('checkbox', 'courseregion_enable', '',
            get_string('showbasedonx', 'local_reportbuilder', lcfirst($title)));
        $mform->disabledIf('courseregion_enable', 'contentenabled', 'eq', 0);
        $mform->setDefault('courseregion_enable', $enable);

        $mform->addElement('select', 'regiontype', '', [
                self::USER_OWN => get_string('regionown', 'local_reportbuilder'),
                self::ANY => get_string('regionany', 'local_reportbuilder'),
                self::OTHER => get_string('regionother', 'local_reportbuilder'),
        ]);
        $mform->setType('regiontype', PARAM_INT);
        $mform->disabledIf('regiontype', 'contentenabled', 'eq', 0);
        $mform->disabledIf('regiontype', 'courseregion_enable', 'notchecked');
        $mform->setDefault('regiontype', $courseregion_type);

        $regionoptions =
                array(0 => get_string('global', 'local_regions')) +
                $DB->get_records_select_menu('local_regions_reg', 'userselectable = 1', array(), 'name DESC', 'id, name');
        $mform->addElement('select', 'region', '', $regionoptions);
        $mform->setType('region', PARAM_INT);
        $mform->setDefault('region', $regions);

        $mform->disabledIf('region', 'regiontype', 'neq', self::OTHER);
        $mform->disabledIf('region', 'contentenabled', 'eq', 0);
        $mform->disabledIf('region', 'courseregion_enable', 'notchecked');
    }

    /**
     * Processes the form elements created by {@link form_template()}
     *
     * @param integer $reportid ID of the report to process
     * @param object $fromform Moodle form data received via form submission
     *
     * @return boolean True if form was successfully processed
     */
    public function form_process($reportid, $fromform) {
        $status = true;
        // remove rb_ from start of classname
        $type = substr(get_class($this), 3);

        // enable checkbox option
        $enable = (isset($fromform->courseregion_enable) &&
            $fromform->courseregion_enable) ? 1 : 0;
        $status = $status && reportbuilder::update_setting($reportid, $type,
            'enable', $enable);

        $status = $status && reportbuilder::update_setting($reportid, $type,
                        'regiontype', $fromform->regiontype);

        if (isset($fromform->courseregion_enable) && $fromform->regiontype == self::OTHER) {
            $status = $status && reportbuilder::update_setting($reportid, $type,'region', $fromform->region);
        }

        return $status;
    }

    private $userregionid;
    private function getuserregion() {
        global $DB;

        if (!isset($this->userregionid)) {
            $userregion = $DB->get_record('local_regions_use', array('userid' => $this->reportfor));
            if ($userregion) {
                return $this->userregionid = $userregion->regionid;
            } else {
                $this->userregionid = false;
            }
        }

        return $this->userregionid;
    }
}

/*
 * Restrict content by a particular user or group of users
 */
class rb_enrolledcourses_content extends rb_base_content {
    const ENROLLEDCOURSES_OWN = 1;

    /**
     * Generate the SQL to apply this content restriction.
     *
     * @param array $field      SQL field to apply the restriction against
     * @param integer $reportid ID of the report
     *
     * @return array containing SQL snippet to be used in a WHERE clause, as well as array of SQL params
     */
    public function sql_restriction($field, $reportid) {
        global $DB;

        $enrolledcoursesfield = $field;

        // remove rb_ from start of classname.
        $type = substr(get_class($this), 3);
        $settings = reportbuilder::get_all_settings($reportid, $type);
        $restriction = isset($settings['who']) ? $settings['who'] : null;


        if (empty($restriction)) {
            return array(' (1 = 1) ', array());
        }

        $conditions = array();
        $params = array();

        $viewownrecord = ($restriction & self::ENROLLEDCOURSES_OWN) == self::ENROLLEDCOURSES_OWN;
        if ($viewownrecord) {
            $courses = array_keys(enrol_get_users_courses($this->reportfor));

            if ($courses) {
                list($cohortsql, $cohortparams) = $DB->get_in_or_equal($courses, SQL_PARAMS_NAMED);

                $conditions[] = "{$enrolledcoursesfield} $cohortsql";
                $params += $cohortparams;
            }
        }

        $sql = implode(' OR ', $conditions);

        if (empty($sql)) {
            $sql = "1=1";
        }

        return array(" ($sql) ", $params);
    }

    /**
     * Generate a human-readable text string describing the restriction
     *
     * @param string $title Name of the field being restricted
     * @param integer $reportid ID of the report
     *
     * @return string Human readable description of the restriction
     */
    public function text_restriction($title, $reportid) {
        global $DB;

        // remove rb_ from start of classname
        $type = substr(get_class($this), 3);
        $settings = reportbuilder::get_all_settings($reportid, $type);
        $who = isset($settings['who']) ? $settings['who'] : 0;
        $enrolledcoursesid = $this->reportfor;

        $enrolledcourses = $DB->get_record('enrolledcourses', array('id' => $enrolledcoursesid));

        $strings = array();
        $strparams = array('field' => $title, 'enrolledcourses' => fullname($enrolledcourses));

        if (($who & self::ENROLLEDCOURSES_OWN) == self::ENROLLEDCOURSES_OWN) {
            $strings[] = get_string('contentdesc_enrolledcoursesown', 'local_reportbuilder', $strparams);
        }

        if (empty($strings)) {
            return $title . ' ' . get_string('isnotfound', 'local_reportbuilder');
        }

        return implode(get_string('or', 'local_reportbuilder'), $strings);
    }


    /**
     * Adds form elements required for this content restriction's settings page
     *
     * @param object &$mform Moodle form object to modify (passed by reference)
     * @param integer $reportid ID of the report being adjusted
     * @param string $title Name of the field the restriction is acting on
     */
    public function form_template(&$mform, $reportid, $title) {

        // get current settings
        // remove rb_ from start of classname
        $type = substr(get_class($this), 3);
        $enable = reportbuilder::get_setting($reportid, $type, 'enable');
        $who = reportbuilder::get_setting($reportid, $type, 'who');

        $mform->addElement('header', 'enrolledcourses_header', get_string('showbyx',
            'local_reportbuilder', lcfirst($title)));
        $mform->setExpanded('enrolledcourses_header');
        $mform->addElement('checkbox', 'enrolledcourses_enable', '',
            get_string('showbasedonx', 'local_reportbuilder', lcfirst($title)));
        $mform->disabledIf('enrolledcourses_enable', 'contentenabled', 'eq', 0);
        $mform->setDefault('enrolledcourses_enable', $enable);
        $checkgroup = array();
        $checkgroup[] =& $mform->createElement('advcheckbox', 'enrolledcourses_who['.self::ENROLLEDCOURSES_OWN.']', '',
            get_string('enrolledcoursesownrecords', 'local_reportbuilder'), null, array(0, 1));
        $mform->setType('enrolledcourses_who['.self::ENROLLEDCOURSES_OWN.']', PARAM_INT);

        $mform->addGroup($checkgroup, 'enrolledcourses_who_group',
            get_string('includeenrolledcoursesrecords', 'local_reportbuilder'), html_writer::empty_tag('br'), false);
        $enrolledcoursesgroups = array(self::ENROLLEDCOURSES_OWN);
        foreach ($enrolledcoursesgroups as $enrolledcoursesgroup) {
            // Bitwise comparison.
            if (($who & $enrolledcoursesgroup) == $enrolledcoursesgroup) {
                $mform->setDefault('enrolledcourses_who['.$enrolledcoursesgroup.']', 1);
            }
        }
        $mform->disabledIf('enrolledcourses_who_group', 'contentenabled', 'eq', 0);
        $mform->disabledIf('enrolledcourses_who_group', 'enrolledcourses_enable', 'notchecked');
        $mform->addHelpButton('enrolledcourses_header', 'reportbuilderenrolledcourses', 'local_reportbuilder');
    }


    /**
     * Processes the form elements created by {@link form_template()}
     *
     * @param integer $reportid ID of the report to process
     * @param object $fromform Moodle form data received via form submission
     *
     * @return boolean True if form was successfully processed
     */
    public function form_process($reportid, $fromform) {
        $status = true;
        // remove rb_ from start of classname
        $type = substr(get_class($this), 3);

        // enable checkbox option
        $enable = (isset($fromform->enrolledcourses_enable) &&
            $fromform->enrolledcourses_enable) ? 1 : 0;
        $status = $status && reportbuilder::update_setting($reportid, $type,
            'enable', $enable);

        // Who checkbox option.
        // Enabled options are stored as enrolledcourses_who[key] = 1 when enabled.
        // Key is a bitwise value to be summed and stored.
        $whovalue = 0;
        $who = isset($fromform->enrolledcourses_who) ?
            $fromform->enrolledcourses_who : array();
        foreach ($who as $key => $option) {
            if ($option) {
                $whovalue += $key;
            }
        }
        $status = $status && reportbuilder::update_setting($reportid, $type,
            'who', $whovalue);

        return $status;
    }
}


/*
 * Restrict content by a particular date
 *
 * Pass in an integer that contains a unix timestamp
 */
class rb_date_content extends rb_base_content {
    /**
     * Generate the SQL to apply this content restriction
     *
     * @param string $field SQL field to apply the restriction against
     * @param integer $reportid ID of the report
     *
     * @return array containing SQL snippet to be used in a WHERE clause, as well as array of SQL params
     */
    public function sql_restriction($field, $reportid) {
        global $DB;
        $now = time();
        $financialyear = get_config('reportbuilder', 'financialyear');
        $month = substr($financialyear, 2, 2);
        $day = substr($financialyear, 0, 2);

        // remove rb_ from start of classname
        $type = substr(get_class($this), 3);
        $settings = reportbuilder::get_all_settings($reportid, $type);

        // option to include empty date fields
        $includenulls = (isset($settings['incnulls']) &&
            $settings['incnulls']) ?
            " OR {$field} IS NULL OR {$field} = 0 " : " AND {$field} != 0 ";

        switch ($settings['when']) {
        case 'past':
            return array("({$field} < {$now} {$includenulls})", array());
        case 'future':
            return array("({$field} > {$now} {$includenulls})", array());
        case 'last30days':
            $sql = "( ({$field} < {$now}  AND {$field}  >
                ({$now} - 60*60*24*30)) {$includenulls})";
            return array($sql, array());
        case 'next30days':
            $sql = "( ({$field} > {$now} AND {$field} <
                ({$now} + 60*60*24*30)) {$includenulls})";
            return array($sql, array());
        case 'currentfinancial':
            $required_year = date('Y', $now);
            $year_before = $required_year - 1;
            $year_after = $required_year + 1;
            if (date('z', $now) >= date('z', mktime(0, 0, 0, $month, $day, $required_year))) {
                $start = mktime(0, 0, 0, $month, $day, $required_year);
                $end = mktime(0, 0, 0, $month, $day, $year_after);
            } else {
                $start = mktime(0, 0, 0, $month, $day, $year_before);
                $end = mktime(0, 0, 0, $month, $day, $required_year);
            }
            $sql = "( ({$field} >= {$start} AND {$field} <
                {$end}) {$includenulls})";
            return array($sql, array());
        case 'lastfinancial':
            $required_year = date('Y', $now) - 1;
            $year_before = $required_year - 1;
            $year_after = $required_year + 1;
            if (date('z', $now) >= date('z', mktime(0, 0, 0, $month, $day, $required_year))) {
                $start = mktime(0, 0, 0, $month, $day, $required_year);
                $end = mktime(0, 0, 0, $month, $day, $year_after);
            } else {
                $start = mktime(0, 0, 0, $month, $day, $year_before);
                $end = mktime(0, 0, 0, $month, $day, $required_year);
            }
            $sql = "( ({$field} >= {$start} AND {$field} <
                {$end}) {$includenulls})";
            return array($sql, array());
        default:
            // no match
            // using 1=0 instead of FALSE for MSSQL support
            return array("(1=0 {$includenulls})", array());
        }

    }

    /**
     * Generate a human-readable text string describing the restriction
     *
     * @param string $title Name of the field being restricted
     * @param integer $reportid ID of the report
     *
     * @return string Human readable description of the restriction
     */
    public function text_restriction($title, $reportid) {

        // remove rb_ from start of classname
        $type = substr(get_class($this), 3);
        $settings = reportbuilder::get_all_settings($reportid, $type);

        // option to include empty date fields
        $includenulls = (isset($settings['incnulls']) &&
                         $settings['incnulls']) ? " (or $title is empty)" : '';

        switch ($settings['when']) {
        case 'past':
            return $title . ' ' . get_string('occurredbefore', 'local_reportbuilder') . ' ' .
                userdate(time(), '%c'). $includenulls;
        case 'future':
            return $title . ' ' . get_string('occurredafter', 'local_reportbuilder') . ' ' .
                userdate(time(), '%c'). $includenulls;
        case 'last30days':
            return $title . ' ' . get_string('occurredafter', 'local_reportbuilder') . ' ' .
                userdate(time() - 60*60*24*30, '%c') . get_string('and', 'local_reportbuilder') .
                get_string('occurredbefore', 'local_reportbuilder') . userdate(time(), '%c') .
                $includenulls;

        case 'next30days':
            return $title . ' ' . get_string('occurredafter', 'local_reportbuilder') . ' ' .
                userdate(time(), '%c') . get_string('and', 'local_reportbuilder') .
                get_string('occurredbefore', 'local_reportbuilder') .
                userdate(time() + 60*60*24*30, '%c') . $includenulls;
        case 'currentfinancial':
            return $title . ' ' . get_string('occurredthisfinancialyear', 'local_reportbuilder') .
                $includenulls;
        case 'lastfinancial':
            return $title . ' ' . get_string('occurredprevfinancialyear', 'local_reportbuilder') .
                $includenulls;
        default:
            return 'Error with date content restriction';
        }
    }


    /**
     * Adds form elements required for this content restriction's settings page
     *
     * @param object &$mform Moodle form object to modify (passed by reference)
     * @param integer $reportid ID of the report being adjusted
     * @param string $title Name of the field the restriction is acting on
     */
    public function form_template(&$mform, $reportid, $title) {
        // get current settings
        // remove rb_ from start of classname
        $type = substr(get_class($this), 3);
        $enable = reportbuilder::get_setting($reportid, $type, 'enable');
        $when = reportbuilder::get_setting($reportid, $type, 'when');
        $incnulls = reportbuilder::get_setting($reportid, $type, 'incnulls');

        $mform->addElement('header', 'date_header', get_string('showbyx',
            'local_reportbuilder', lcfirst($title)));
        $mform->setExpanded('date_header');
        $mform->addElement('checkbox', 'date_enable', '',
            get_string('showbasedonx', 'local_reportbuilder',
            lcfirst($title)));
        $mform->setDefault('date_enable', $enable);
        $mform->disabledIf('date_enable', 'contentenabled', 'eq', 0);
        $radiogroup = array();
        $radiogroup[] =& $mform->createElement('radio', 'date_when', '',
            get_string('thepast', 'local_reportbuilder'), 'past');
        $radiogroup[] =& $mform->createElement('radio', 'date_when', '',
            get_string('thefuture', 'local_reportbuilder'), 'future');
        $radiogroup[] =& $mform->createElement('radio', 'date_when', '',
            get_string('last30days', 'local_reportbuilder'), 'last30days');
        $radiogroup[] =& $mform->createElement('radio', 'date_when', '',
            get_string('next30days', 'local_reportbuilder'), 'next30days');
        $radiogroup[] =& $mform->createElement('radio', 'date_when', '',
            get_string('currentfinancial', 'local_reportbuilder'), 'currentfinancial');
        $radiogroup[] =& $mform->createElement('radio', 'date_when', '',
            get_string('lastfinancial', 'local_reportbuilder'), 'lastfinancial');
        $mform->addGroup($radiogroup, 'date_when_group',
            get_string('includerecordsfrom', 'local_reportbuilder'), html_writer::empty_tag('br'), false);
        $mform->setDefault('date_when', $when);
        $mform->disabledIf('date_when_group', 'contentenabled', 'eq', 0);
        $mform->disabledIf('date_when_group', 'date_enable', 'notchecked');
        $mform->addHelpButton('date_header', 'reportbuilderdate', 'local_reportbuilder');

        $mform->addElement('checkbox', 'date_incnulls',
            get_string('includeemptydates', 'local_reportbuilder'));
        $mform->setDefault('date_incnulls', $incnulls);
        $mform->disabledIf('date_incnulls', 'date_enable', 'notchecked');
        $mform->disabledIf('date_incnulls', 'contentenabled', 'eq', 0);
    }


    /**
     * Processes the form elements created by {@link form_template()}
     *
     * @param integer $reportid ID of the report to process
     * @param object $fromform Moodle form data received via form submission
     *
     * @return boolean True if form was successfully processed
     */
    public function form_process($reportid, $fromform) {
        $status = true;
        // remove rb_ from start of classname
        $type = substr(get_class($this), 3);

        // enable checkbox option
        $enable = (isset($fromform->date_enable) &&
            $fromform->date_enable) ? 1 : 0;
        $status = $status && reportbuilder::update_setting($reportid, $type,
            'enable', $enable);

        // when radio option
        $when = isset($fromform->date_when) ?
            $fromform->date_when : 0;
        $status = $status && reportbuilder::update_setting($reportid, $type,
            'when', $when);

        // include nulls checkbox option
        $incnulls = (isset($fromform->date_incnulls) &&
            $fromform->date_incnulls) ? 1 : 0;
        $status = $status && reportbuilder::update_setting($reportid, $type,
            'incnulls', $incnulls);

        return $status;
    }
}


/*
 * Restrict content by offical tags
 *
 * Pass in a column that contains a pipe '|' separated list of official tag ids
 */
class rb_tag_content extends rb_base_content {
    /**
     * Generate the SQL to apply this content restriction
     *
     * @param string $field SQL field to apply the restriction against
     * @param integer $reportid ID of the report
     *
     * @return array containing SQL snippet to be used in a WHERE clause, as well as array of SQL params
     */
    public function sql_restriction($field, $reportid) {
        global $DB;

        // remove rb_ from start of classname
        $type = substr(get_class($this), 3);

        $include_sql = array();
        $exclude_sql = array();

        // get arrays of included and excluded tags
        $settings = reportbuilder::get_all_settings($reportid, $type);
        $itags = ($settings['included']) ?
            explode('|', $settings['included']) : array();
        $etags = ($settings['excluded']) ?
            explode('|', $settings['excluded']) : array();
        $include_logic = (isset($settings['include_logic']) &&
            $settings['include_logic'] == 0) ? ' AND ' : ' OR ';
        $exclude_logic = (isset($settings['exclude_logic']) &&
            $settings['exclude_logic'] == 0) ? ' OR ' : ' AND ';

        // loop through current official tags
        $tags = $DB->get_records('tag', array('isstandard' => 1), 'name');
        $params = array();
        $count = 1;
        foreach ($tags as $tag) {
            // if found, add the SQL
            // we can't just use LIKE '%tag%' because we might get
            // partial number matches
            if (in_array($tag->id, $itags)) {
                $uniqueparam = rb_unique_param("cctre_{$count}_");
                $elike = $DB->sql_like($field, ":{$uniqueparam}");
                $params[$uniqueparam] = $DB->sql_like_escape($tag->id);

                $uniqueparam = rb_unique_param("cctrew_{$count}_");
                $ewlike = $DB->sql_like($field, ":{$uniqueparam}");
                $params[$uniqueparam] = $DB->sql_like_escape($tag->id).'|%';

                $uniqueparam = rb_unique_param("cctrsw_{$count}_");
                $swlike = $DB->sql_like($field, ":{$uniqueparam}");
                $params[$uniqueparam] = '%|'.$DB->sql_like_escape($tag->id);

                $uniqueparam = rb_unique_param("cctrsc_{$count}_");
                $clike = $DB->sql_like($field, ":{$uniqueparam}");
                $params[$uniqueparam] = '%|'.$DB->sql_like_escape($tag->id).'|%';

                $include_sql[] = "({$elike} OR
                {$ewlike} OR
                {$swlike} OR
                {$clike})\n";

                $count++;
            }
            if (in_array($tag->id, $etags)) {
                $uniqueparam = rb_unique_param("cctre_{$count}_");
                $enotlike = $DB->sql_like($field, ":{$uniqueparam}", true, true, true);
                $params[$uniqueparam] = $DB->sql_like_escape($tag->id);

                $uniqueparam = rb_unique_param("cctrew_{$count}_");
                $ewnotlike = $DB->sql_like($field, ":{$uniqueparam}", true, true, true);
                $params[$uniqueparam] = $DB->sql_like_escape($tag->id).'|%';

                $uniqueparam = rb_unique_param("cctrsw_{$count}_");
                $swnotlike = $DB->sql_like($field, ":{$uniqueparam}", true, true, true);
                $params[$uniqueparam] = '%|'.$DB->sql_like_escape($tag->id);

                $uniqueparam = rb_unique_param("cctrsc_{$count}_");
                $cnotlike = $DB->sql_like($field, ":{$uniqueparam}", true, true, true);
                $params[$uniqueparam] = '%|'.$DB->sql_like_escape($tag->id).'|%';

                $include_sql[] = "({$enotlike} AND
                {$ewnotlike} AND
                {$swnotlike} AND
                {$cnotlike})\n";

                $count++;
            }
        }

        // merge the include and exclude strings separately
        $includestr = implode($include_logic, $include_sql);
        $excludestr = implode($exclude_logic, $exclude_sql);

        // now merge together
        if ($includestr && $excludestr) {
            return array(" ($includestr AND $excludestr) ", $params);
        } else if ($includestr) {
            return array(" $includestr ", $params);
        } else if ($excludestr) {
            return array(" $excludestr ", $params);
        } else {
            // using 1=0 instead of FALSE for MSSQL support
            return array('1=0', $params);
        }
    }

    /**
     * Generate a human-readable text string describing the restriction
     *
     * @param string $title Name of the field being restricted
     * @param integer $reportid ID of the report
     *
     * @return string Human readable description of the restriction
     */
    public function text_restriction($title, $reportid) {
        global $DB;

        // remove rb_ from start of classname
        $type = substr(get_class($this), 3);
        $settings = reportbuilder::get_all_settings($reportid, $type);

        $include_text = array();
        $exclude_text = array();

        $itags = ($settings['included']) ?
            explode('|', $settings['included']) : array();
        $etags = ($settings['excluded']) ?
            explode('|', $settings['excluded']) : array();
        $include_logic = (isset($settings['include_logic']) &&
            $settings['include_logic'] == 0) ? 'and' : 'or';
        $exclude_logic = (isset($settings['exclude_logic']) &&
            $settings['exclude_logic'] == 0) ? 'and' : 'or';

        $tags = $DB->get_records('tag', array('isstandard' => 1), 'name');
        foreach ($tags as $tag) {
            if (in_array($tag->id, $itags)) {
                $include_text[] = '"' . $tag->name . '"';
            }
            if (in_array($tag->id, $etags)) {
                $exclude_text[] = '"' . $tag->name . '"';
            }
        }

        if (count($include_text) > 0) {
            $includestr = $title . ' ' . get_string('istaggedwith', 'local_reportbuilder') .
                ' ' . implode(get_string($include_logic, 'local_reportbuilder'), $include_text);
        } else {
            $includestr = '';
        }
        if (count($exclude_text) > 0) {
            $excludestr = $title . ' ' . get_string('isnttaggedwith', 'local_reportbuilder') .
                ' ' . implode(get_string($exclude_logic, 'local_reportbuilder'), $exclude_text);
        } else {
            $excludestr = '';
        }

        if ($includestr && $excludestr) {
            return $includestr . get_string('and', 'local_reportbuilder') . $excludestr;
        } else if ($includestr) {
            return $includestr;
        } else if ($excludestr) {
            return $excludestr;
        } else {
            return '';
        }

    }


    /**
     * Adds form elements required for this content restriction's settings page
     *
     * @param object &$mform Moodle form object to modify (passed by reference)
     * @param integer $reportid ID of the report being adjusted
     * @param string $title Name of the field the restriction is acting on
     */
    public function form_template(&$mform, $reportid, $title) {
        global $DB;

        // remove rb_ from start of classname
        $type = substr(get_class($this), 3);
        $enable = reportbuilder::get_setting($reportid, $type, 'enable');
        $include_logic = reportbuilder::get_setting($reportid, $type, 'include_logic');
        $exclude_logic = reportbuilder::get_setting($reportid, $type, 'exclude_logic');
        $activeincludes = explode('|',
            reportbuilder::get_setting($reportid, $type, 'included'));
        $activeexcludes = explode('|',
            reportbuilder::get_setting($reportid, $type, 'excluded'));

        $mform->addElement('header', 'tag_header',
            get_string('showbytag', 'local_reportbuilder'));
        $mform->setExpanded('tag_header');
        $mform->addHelpButton('tag_header', 'reportbuildertag', 'local_reportbuilder');

        $mform->addElement('checkbox', 'tag_enable', '',
            get_string('tagenable', 'local_reportbuilder'));
        $mform->setDefault('tag_enable', $enable);
        $mform->disabledIf('tag_enable', 'contentenabled', 'eq', 0);

        $mform->addElement('html', html_writer::empty_tag('br'));

        // include the following tags
        $tags = $DB->get_records('tag', array('isstandard' => 1), 'name');
        if (!empty($tags)) {
            $checkgroup = array();
            $opts = array(1 => get_string('anyofthefollowing', 'local_reportbuilder'),
                          0 => get_string('allofthefollowing', 'local_reportbuilder'));
            $mform->addElement('select', 'tag_include_logic', get_string('includetags', 'local_reportbuilder'), $opts);
            $mform->setDefault('tag_include_logic', $include_logic);
            $mform->disabledIf('tag_enable', 'contentenabled', 'eq', 0);
            foreach ($tags as $tag) {
                $checkgroup[] =& $mform->createElement('checkbox',
                    'tag_include_option_' . $tag->id, '', $tag->name, 1);
                $mform->disabledIf('tag_include_option_' . $tag->id,
                    'tag_exclude_option_' . $tag->id, 'checked');
                if (in_array($tag->id, $activeincludes)) {
                    $mform->setDefault('tag_include_option_' . $tag->id, 1);
                }
            }
            $mform->addGroup($checkgroup, 'tag_include_group', '', html_writer::empty_tag('br'), false);
        }
        $mform->disabledIf('tag_include_group', 'contentenabled', 'eq', 0);
        $mform->disabledIf('tag_include_group', 'tag_enable',
            'notchecked');

        $mform->addElement('html', str_repeat(html_writer::empty_tag('br'), 2));

        // exclude the following tags
        if (!empty($tags)) {
            $checkgroup = array();
            $opts = array(1 => get_string('anyofthefollowing', 'local_reportbuilder'),
                          0 => get_string('allofthefollowing', 'local_reportbuilder'));
            $mform->addElement('select', 'tag_exclude_logic', get_string('excludetags', 'local_reportbuilder'), $opts);
            $mform->setDefault('tag_exclude_logic', $exclude_logic);
            $mform->disabledIf('tag_enable', 'contentenabled', 'eq', 0);
            foreach ($tags as $tag) {
                $checkgroup[] =& $mform->createElement('checkbox',
                    'tag_exclude_option_' . $tag->id, '', $tag->name, 1);
                $mform->disabledIf('tag_exclude_option_' . $tag->id,
                    'tag_include_option_' . $tag->id, 'checked');
                if (in_array($tag->id, $activeexcludes)) {
                    $mform->setDefault('tag_exclude_option_' . $tag->id, 1);
                }
            }
            $mform->addGroup($checkgroup, 'tag_exclude_group', '', html_writer::empty_tag('br'), false);
        }
        $mform->disabledIf('tag_exclude_group', 'contentenabled', 'eq', 0);
        $mform->disabledIf('tag_exclude_group', 'tag_enable',
            'notchecked');

    }


    /**
     * Processes the form elements created by {@link form_template()}
     *
     * @param integer $reportid ID of the report to process
     * @param object $fromform Moodle form data received via form submission
     *
     * @return boolean True if form was successfully processed
     */
    public function form_process($reportid, $fromform) {
        global $DB;

        $status = true;
        // remove the rb_ from class
        $type = substr(get_class($this), 3);

        // enable checkbox option
        $enable = (isset($fromform->tag_enable) &&
            $fromform->tag_enable) ? 1 : 0;
        $status = $status && reportbuilder::update_setting($reportid, $type,
            'enable', $enable);

        // include with any or all
        $includelogic = (isset($fromform->tag_include_logic) &&
            $fromform->tag_include_logic) ? 1 : 0;
        $status = $status && reportbuilder::update_setting($reportid, $type,
            'include_logic', $includelogic);

        // exclude with any or all
        $excludelogic = (isset($fromform->tag_exclude_logic) &&
            $fromform->tag_exclude_logic) ? 1 : 0;
        $status = $status && reportbuilder::update_setting($reportid, $type,
            'exclude_logic', $excludelogic);

        // tag settings
        $tags = $DB->get_records('tag', array('isstandard' => 1));
        if (!empty($tags)) {
            $activeincludes = array();
            $activeexcludes = array();
            foreach ($tags as $tag) {
                $includename = 'tag_include_option_' . $tag->id;
                $excludename = 'tag_exclude_option_' . $tag->id;

                // included tags
                if (isset($fromform->$includename)) {
                    if ($fromform->$includename == 1) {
                        $activeincludes[] = $tag->id;
                    }
                }

                // excluded tags
                if (isset($fromform->$excludename)) {
                    if ($fromform->$excludename == 1) {
                        $activeexcludes[] = $tag->id;
                    }
                }

            }

            // implode into string and update setting
            $status = $status && reportbuilder::update_setting($reportid,
                $type, 'included', implode('|', $activeincludes));

            // implode into string and update setting
            $status = $status && reportbuilder::update_setting($reportid,
                $type, 'excluded', implode('|', $activeexcludes));
        }
        return $status;
    }
}

// Include report access content restriction.
include_once($CFG->dirroot . '/local/reportbuilder/classes/rb_report_access_content.php');
include_once($CFG->dirroot . '/local/reportbuilder/classes/rb_archived_content.php');
