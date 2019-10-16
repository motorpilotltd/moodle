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

namespace rbsource_user;
use rb_base_source;
use coding_exception;
use rb_join;
use rb_column_option;
use rb_filter_option;
use html_writer;
use moodle_url;
use rb_param_option;
use context_system;
use rb_content_option;
use context_user;

defined('MOODLE_INTERNAL') || die();

require_once("{$CFG->dirroot}/completion/completion_completion.php");

/**
 * A report builder source for the "user" table.
 */
class source extends rb_base_source {

    public $base, $joinlist, $columnoptions, $filteroptions;
    public $contentoptions, $paramoptions, $defaultcolumns;
    public $defaultfilters, $requiredcolumns, $sourcetitle;

    /**
     * Conditional SQL to filter the data source available to the report.
     *
     * @var string
     */
    public $sourcewhere;

    /**
     * Parameters to support the conditional SQL that filters the data source available to the report.
     *
     * @var array
     */
    public $sourceparams;

    /*
     * Indicate if the actions column is permitted on the source.
     * NOTE: you need to extend this source and override this if you want to enable user actions to your reports.
     * @var boolean.
     */
    protected $allow_actions_column = null;

    /**
     * Constructor
     *
     */
    public function __construct() {
        $this->base = "{user}";
        list($this->sourcewhere, $this->sourceparams) = $this->define_sourcewhere();
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->contentoptions = $this->define_contentoptions();
        $this->paramoptions = $this->define_paramoptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->requiredcolumns = array();
        $this->sourcetitle = get_string('sourcetitle', 'rbsource_user');

        parent::__construct();
    }

    //
    //
    // Methods for defining contents of source
    //
    //

    /**
     * Define some extra SQL for the base to limit the data set.
     *
     * @return array The SQL and parmeters that defines the WHERE for the source.
     */
    protected function define_sourcewhere() {
        $params = array ();
        $sql = 'deleted = 0';

        // Ensure SQL is wrapped in brackets, otherwise our where statements will bleed into each other.
        return array("({$sql})", $params);
    }


    /**
     * Creates the array of rb_join objects required for this->joinlist
     *
     * @return array
     */
    protected function define_joinlist() {

        $joinlist = array(
                new rb_join(
                        'course_completions_courses_started',
                        'LEFT',
                        "(SELECT userid, COUNT(id) as number
                    FROM {course_completions}
                    WHERE timestarted > 0 OR timecompleted > 0
                    GROUP BY userid)",
                        'base.id = course_completions_courses_started.userid',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE
                ),
                new rb_join(
                        'totara_stats_courses_completed',
                        'LEFT',
                        "(SELECT userid, count(DISTINCT course) AS number
                    FROM {course_completions}
                    WHERE timecompleted > 0
                    GROUP BY userid)",
                        'base.id = totara_stats_courses_completed.userid',
                        REPORT_BUILDER_RELATION_ONE_TO_ONE
                ),
                new rb_join(
                        'basestaff',
                        'LEFT',
                        'SQLHUB.ARUP_ALL_STAFF_V',
                        "basestaff.EMPLOYEE_NUMBER = base.idnumber",
                        REPORT_BUILDER_RELATION_ONE_TO_ONE
                )
        );

        $this->add_cohort_user_tables_to_joinlist($joinlist, 'base', 'id', 'basecohort');

        return $joinlist;
    }

    /**
     * Creates the array of rb_column_option objects required for
     * $this->columnoptions
     *
     * @return array
     */
    protected function define_columnoptions() {
        global $DB;

        $columnoptions = array();
        $this->add_user_fields_to_columns($columnoptions, 'base');
        $this->add_staff_details_to_columns($columnoptions, 'basestaff');

        // A column to display a user's profile picture
        $columnoptions[] = new rb_column_option(
                'user',
                'userpicture',
                get_string('userspicture', 'rbsource_user'),
                'base.id',
                array(
                        'displayfunc' => 'user_picture',
                        'noexport' => true,
                        'defaultheading' => get_string('picture', 'rbsource_user'),
                        'extrafields' => array(
                                'userpic_picture' => 'base.picture',
                                'userpic_firstname' => 'base.firstname',
                                'userpic_firstnamephonetic' => 'base.firstnamephonetic',
                                'userpic_middlename' => 'base.middlename',
                                'userpic_lastname' => 'base.lastname',
                                'userpic_lastnamephonetic' => 'base.lastnamephonetic',
                                'userpic_alternatename' => 'base.alternatename',
                                'userpic_imagealt' => 'base.imagealt',
                                'userpic_email' => 'base.email'
                        )
                )
        );

        // A column to display the "My Learning" icons for a user
        $columnoptions[] = new rb_column_option(
                'user',
                'userlearningicons',
                get_string('mylearningicons', 'rbsource_user'),
                'base.id',
                array(
                        'displayfunc' => 'learning_icons',
                        'noexport' => true,
                        'defaultheading' => get_string('options', 'rbsource_user')
                )
        );

        // A column to display the number of started courses for a user
        // We need a COALESCE on the field for 0 to replace nulls, which ensures correct sorting order.
        $columnoptions[] = new rb_column_option(
                'statistics',
                'coursesstarted',
                get_string('userscoursestartedcount', 'rbsource_user'),
                'COALESCE(course_completions_courses_started.number,0)',
                array(
                        'displayfunc' => 'count',
                        'joins' => 'course_completions_courses_started',
                        'dbdatatype' => 'integer',
                )
        );

        // A column to display the number of completed courses for a user
        // We need a COALESCE on the field for 0 to replace nulls, which ensures correct sorting order.
        $columnoptions[] = new rb_column_option(
                'statistics',
                'coursescompleted',
                get_string('userscoursescompletedcount', 'rbsource_user'),
                'COALESCE(totara_stats_courses_completed.number,0)',
                array(
                        'displayfunc' => 'count',
                        'joins' => 'totara_stats_courses_completed',
                        'dbdatatype' => 'integer',
                )
        );

        $usednamefields = totara_get_all_user_name_fields_join('base');
        $allnamefields = totara_get_all_user_name_fields_join('base');

        $columnoptions[] = new rb_column_option(
                'user',
                'namewithlinks',
                get_string('usernamewithlearninglinks', 'rbsource_user'),
                $DB->sql_concat_join("' '", $usednamefields),
                array(
                        'displayfunc' => 'user_with_links',
                        'defaultheading' => get_string('user', 'rbsource_user'),
                        'extrafields' => array_merge(array('id' => 'base.id',
                                                           'picture' => 'base.picture',
                                                           'imagealt' => 'base.imagealt',
                                                           'email' => 'base.email',
                                                           'deleted' => 'base.deleted'),
                                $allnamefields),
                        'dbdatatype' => 'char',
                        'outputformat' => 'text'
                )
        );

        $usednamefields = totara_get_all_user_name_fields_join('base');

        if ($this->allow_actions_column) {
            $columnoptions[] = new rb_column_option(
                    'user',
                    'actions',
                    get_string('actions', 'local_reportbuilder'),
                    'base.id',
                    array(
                            'displayfunc' => 'user_actions',
                            'noexport' => true,
                            'nosort' => true,
                            'graphable' => false,
                            'extrafields' => array(
                                    'fullname' => $DB->sql_concat_join("' '", $usednamefields),
                                    'username' => 'base.username',
                                    'email' => 'base.email',
                                    'mnethostid' => 'base.mnethostid',
                                    'confirmed' => 'base.confirmed',
                                    'suspended' => 'base.suspended',
                                    'deleted' => 'base.deleted'
                            )
                    )
            );
        }

        return $columnoptions;
    }

    /**
     * Creates the array of rb_filter_option objects required for $this->filteroptions
     * @return array
     */
    protected function define_filteroptions() {
        $filteroptions = [];

        $this->add_user_fields_to_filters($filteroptions);
        $this->add_staff_fields_to_filters($filteroptions, 'user', false, 'basestaff');

        $roles = get_roles_used_in_context(context_system::instance());

        // We only want this filter to be available on reports that user the user source.
        $filteroptions[] = new rb_filter_option(
                'user',
                'roleid',
                get_string('usersystemrole', 'local_reportbuilder'),
                'system_role',
                [
                        'selectchoices' => [
                                        '' => get_string('chooserole', 'local_reportbuilder'),
                                        '0' => get_string('anyrole', 'local_reportbuilder')
                                ] + role_fix_names($roles, null, null, true),
                ],
                'base.id'
        );

        return $filteroptions;
    }


    protected function define_defaultcolumns() {
        $defaultcolumns = array(
                array(
                        'type' => 'user',
                        'value' => 'namelinkicon',
                ),
                array(
                        'type' => 'user',
                        'value' => 'username',
                ),
                array(
                        'type' => 'user',
                        'value' => 'lastlogin',
                ),
        );
        return $defaultcolumns;
    }

    protected function define_defaultfilters() {
        $defaultfilters = array(
                array(
                        'type' => 'user',
                        'value' => 'fullname',
                ),
        );

        return $defaultfilters;
    }
    /**
     * Creates the array of rb_content_option object required for $this->contentoptions
     * @return array
     */
    protected function define_contentoptions() {
        $contentoptions = array();

        // Add the time created content option.
        $contentoptions[] = new rb_content_option(
                'date',
                get_string('timecreated', 'rbsource_user'),
                'base.timecreated'
        );

        return $contentoptions;
    }


    /**
     * A rb_column_options->displayfunc helper function for showing a user's links column on the My Team page.
     * To pass the correct data, first:
     *      $usednamefields = totara_get_all_user_name_fields_join($base, null, true);
     *      $allnamefields = totara_get_all_user_name_fields_join($base);
     * then your "field" param should be:
     *      $DB->sql_concat_join("' '", $usednamefields)
     * to allow sorting and filtering, and finally your extrafields should be:
     *      array_merge(array('id' => $base . '.id',
     *                        'picture' => $base . '.picture',
     *                        'imagealt' => $base . '.imagealt',
     *                        'email' => $base . '.email'),
     *                  $allnamefields)
     *
     * @param string $user Users name
     * @param object $row All the data required to display a user's name, icon and link
     * @param boolean $isexport If the report is being exported or viewed
     * @return string
     */
    function rb_display_user_with_links($user, $row, $isexport = false) {
        global $CFG, $OUTPUT;

        require_once($CFG->dirroot . '/user/lib.php');

        // Process obsolete calls to this display function.
        if (isset($row->userpic_picture)) {
            $picuser = new \stdClass();
            $picuser->id = $row->user_id;
            $picuser->picture = $row->userpic_picture;
            $picuser->imagealt = $row->userpic_imagealt;
            $picuser->firstname = $row->userpic_firstname;
            $picuser->firstnamephonetic = $row->userpic_firstnamephonetic;
            $picuser->middlename = $row->userpic_middlename;
            $picuser->lastname = $row->userpic_lastname;
            $picuser->lastnamephonetic = $row->userpic_lastnamephonetic;
            $picuser->alternatename = $row->userpic_alternatename;
            $picuser->email = $row->userpic_email;
            $row = $picuser;
        }

        $userid = $row->id;

        if ($isexport) {
            return $this->rb_display_user($user, $row, true);
        }

        $usercontext = context_user::instance($userid, MUST_EXIST);
        $show_profile_link = user_can_view_profile($row, null, $usercontext);

        $user_pic = $OUTPUT->user_picture($row, array('courseid' => 1, 'link' => $show_profile_link));

        $profilestr = get_string('profile', 'rbsource_user');
        $profile_link = html_writer::link("{$CFG->wwwroot}/user/view.php?id={$userid}", $profilestr);

        $links = html_writer::start_tag('ul');
        $links .= $show_profile_link ? html_writer::tag('li', $profile_link) : '';

        $links .= html_writer::end_tag('ul');

        if ($show_profile_link) {
            $user_tag = html_writer::link(new moodle_url("/user/profile.php", array('id' => $userid)),
                    fullname($row), array('class' => 'name'));
        }
        else {
            $user_tag = html_writer::span(fullname($row), 'name');
        }

        $return = $user_pic . $user_tag . $links;

        return $return;
    }

    function rb_display_count($result) {
        return $result ? $result : 0;
    }

    protected function define_paramoptions() {
        $paramoptions = array(
                new rb_param_option(
                        'deleted',
                        'base.deleted'
                ),
        );

        return $paramoptions;
    }

    /**
     * Returns expected result for column_test.
     * @param rb_column_option $columnoption
     * @return int
     */
    public function phpunit_column_test_expected_count($columnoption) {
        if (!PHPUNIT_TEST) {
            throw new coding_exception('phpunit_column_test_expected_count() cannot be used outside of unit tests');
        }
        if (get_class($this) === 'rbsource_user') {
            return 2;
        }
        return parent::phpunit_column_test_expected_count($columnoption);
    }
}

// end of rb_source_user class

