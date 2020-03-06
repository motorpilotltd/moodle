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

namespace rbsource_coursecompletionall;
use rb_base_source;
use coding_exception;
use rb_column_option;
use rb_filter_option;
use rb_param_option;
use rb_content_option;

defined('MOODLE_INTERNAL') || die();

class source extends rb_base_source {

    public $base, $joinlist, $columnoptions, $filteroptions;
    public $contentoptions, $paramoptions, $defaultcolumns;
    public $defaultfilters, $requiredcolumns, $sourcetitle;

    public function __construct() {
        $this->base = $this->define_base();
        $this->joinlist = $this->define_joinlist();
        $this->columnoptions = $this->define_columnoptions();
        $this->filteroptions = $this->define_filteroptions();
        $this->contentoptions = $this->define_contentoptions();
        $this->paramoptions = $this->define_paramoptions();
        $this->defaultcolumns = $this->define_defaultcolumns();
        $this->defaultfilters = $this->define_defaultfilters();
        $this->requiredcolumns = array();
        $this->sourcetitle = $this->define_sourcetitle();
        parent::__construct();
    }

    protected function define_sourcetitle() {
        return get_string('sourcetitle', 'rbsource_coursecompletionall');
    }

    protected function define_base() {
        global $DB;
        $ccuniqueid = $DB->sql_concat_join("'CC'", array(\local_reportbuilder\dblib\base::getbdlib()->sql_cast_2char('cc.id')));
        $cchuniqueid = $DB->sql_concat_join("'CCH'", array(\local_reportbuilder\dblib\base::getbdlib()->sql_cast_2char('cch.id')));

        $base = "(
              SELECT {$ccuniqueid} AS id, cc.userid, cc.course AS courseid, cc.timecompleted, gg.finalgrade AS grade, gi.grademax, gi.grademin, 1 AS iscurrent
                FROM {course_completions} cc
           LEFT JOIN {grade_items} gi ON cc.course = gi.courseid AND gi.itemtype = 'course'
           LEFT JOIN {grade_grades} gg ON gi.id = gg.itemid AND gg.userid = cc.userid
               WHERE cc.timecompleted >= 0
           UNION ALL
              SELECT {$cchuniqueid} AS id,cch.userid, cch.course as courseid, cch.timecompleted, NULL, gi.grademax, gi.grademin, 0 AS iscurrent
                FROM {certif_course_compl_archive} cch
           LEFT JOIN {grade_items} gi ON cch.course = gi.courseid AND gi.itemtype = 'course'
           LEFT JOIN {grade_grades} gg ON gi.id = gg.itemid AND gg.userid = cch.userid
              )";
        return $base;
    }

    /**
     * Creates the array of rb_join objects required for this->joinlist.
     *
     * @return array
     */
    protected function define_joinlist() {
        $joinlist = array();

        $this->add_user_table_to_joinlist($joinlist, 'base', 'userid');
        $this->add_course_table_to_joinlist($joinlist, 'base', 'courseid', 'INNER');

        return $joinlist;
    }

    /**
     * Creates the array of rb_column_option objects required for $this->columnoptions.
     *
     * @return array
     */
    protected function define_columnoptions() {
        $columnoptions = array(
                new rb_column_option(
                        'base',
                        'timecompleted',
                        get_string('timecompleted', 'rbsource_coursecompletionall'),
                        'base.timecompleted',
                        array(
                                'displayfunc' => 'nice_date',
                                'dbdatatype' => 'timestamp'
                        )
                ),
                new rb_column_option(
                        'base',
                        'grade',
                        get_string('grade', 'rbsource_coursecompletionall'),
                        'base.grade',
                        array(
                                'displayfunc' => 'grade_string',
                                'extrafields' => array(
                                        'grademax' => 'base.grademax',
                                        'grademin' => 'base.grademin',
                                )
                        )
                ),
        );
        if (get_class($this) === 'rbsource_coursecompletionall') {
            // Only add this to the base class.
            $columnoptions[] = new rb_column_option(
                    'base',
                    'iscurrent',
                    get_string('iscurrent', 'rbsource_coursecompletionall'),
                    'base.iscurrent',
                    array(
                            'displayfunc' => 'yes_or_no'
                    )
            );
        }

        $this->add_user_fields_to_columns($columnoptions);
        $this->add_staff_details_to_columns($columnoptions);
        $this->add_course_fields_to_columns($columnoptions);

        return $columnoptions;
    }

    /**
     * Creates the array of rb_filter_option objects required for $this->filteroptions.
     *
     * @return array
     */
    protected function define_filteroptions() {
        $filteroptions = array(
                new rb_filter_option(
                        'base',
                        'timecompleted',
                        get_string('timecompleted', 'rbsource_coursecompletionall'),
                        'date'
                ),
                new rb_filter_option(
                        'base',
                        'grade',
                        get_string('grade', 'rbsource_coursecompletionall'),
                        'number'
                ),
        );
        if (get_class($this) === 'rbsource_coursecompletionall') {
            // Only add this to the base class.
            $filteroptions[] = new rb_filter_option(
                    'base',
                    'iscurrent',
                    get_string('iscurrent', 'rbsource_coursecompletionall'),
                    'select',
                    array(
                            'selectfunc' => 'yesno_list',
                            'attributes' => rb_filter_option::select_width_limiter(),
                    )
            );
        }

        $this->add_user_fields_to_filters($filteroptions);
        $this->add_staff_fields_to_filters($filteroptions);
        $this->add_course_fields_to_filters($filteroptions);

        return $filteroptions;
    }

    /**
     * Creates the array of rb_content_option objects required for $this->contentoptions.
     *
     * @return array
     */
    protected function define_contentoptions() {
        $contentoptions = array();

        $contentoptions[] = new rb_content_option(
                'costcentre',
                get_string('costcentre', 'local_reportbuilder'),
                ['costcentre' => "auser.icq"],
                'auser'
        );

        return $contentoptions;
    }

    /**
     * Creates the array of rb_param_option objects required for $this->paramoptions.
     *
     * @return array
     */
    protected function define_paramoptions() {
        $paramoptions = array();

        $paramoptions[] = new rb_param_option(
                'userid',
                'base.userid',
                'base'
        );
        $paramoptions[] = new rb_param_option(
                'courseid',
                'base.courseid',
                'base'
        );
        return $paramoptions;
    }

    protected function define_defaultcolumns() {
        $defaultcolumns = array(
                array(
                        'type' => 'user',
                        'value' => 'namelink',
                ),
                array(
                        'type' => 'course',
                        'value' => 'courselink',
                ),
                array(
                        'type' => 'base',
                        'value' => 'timecompleted',
                ),
                array(
                        'type' => 'base',
                        'value' => 'grade',
                ),
        );
        if (get_class($this) === 'rbsource_coursecompletionall') {
            // Only add this to the base class.
            $defaultcolumns[] = array(
                    'type' => 'base',
                    'value' => 'iscurrent',
            );
        }
        return $defaultcolumns;
    }

    protected function define_defaultfilters() {
        $defaultfilters = array(
                array(
                        'type' => 'user',
                        'value' => 'fullname',
                ),
                array(
                        'type' => 'course',
                        'value' => 'fullname',
                ),
                array(
                        'type' => 'base',
                        'value' => 'timecompleted',
                ),
                array(
                        'type' => 'base',
                        'value' => 'grade',
                ),
        );
        if (get_class($this) === 'rbsource_coursecompletionall') {
            // Only add this to the base class.
            $defaultcolumns[] = array(
                    'type' => 'base',
                    'value' => 'iscurrent',
            );
        }

        return $defaultfilters;
    }
}
