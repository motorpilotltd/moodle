<?php
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
 * @author Simon Coggins <simonc@catalyst.net.nz>
 * @package local_reportbuilder
 *
 * Unit tests to check source column definitions
 *
 * vendor/bin/phpunit local_reportbuilder_column_testcase
 *
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

global $CFG;
require_once($CFG->dirroot . '/local/reportbuilder/lib.php');
require_once($CFG->dirroot . '/local/reportbuilder/tests/reportcache_advanced_testcase.php');

class local_reportbuilder_column_testcase extends reportcache_advanced_testcase {
    // Warning: Massive amount of test data ahead.
    protected $user_info_field_data = array(
       'id' => 1, 'shortname' => 'datejoined', 'name' => 'Date Joined', 'datatype' => 'text', 'description' => '', 'categoryid' => 1,
       'sortorder' => 1, 'required' => 0, 'locked' => 0, 'visible' => 1, 'forceunique' => 0, 'signup' => 0, 'defaultdata' => '',
       'param1' => 30, 'param2' => 2048, 'param3' => 0, 'param4' => '', 'param5' => '',
    );

    protected $user_info_data_data = array(
         'id' => 1, 'userid' => 2, 'fieldid' => 1, 'data' => 'test',
    );

    protected $course_completions_data = array(
        'id' => 1, 'userid' => 2, 'course' => 1, 'deleted' => 0, 'timenotified' => 0,
        'timestarted' => 1140606000, 'timeenrolled' => 1140606000, 'timecompleted' => 1140606000, 'reaggregate' => 0,
        'status' => 0,
    );

    protected $course_completion_criteria_data = array(
        'id' => 1, 'course' => 2, 'criteriatype' => 6, 'gradepass' => 2,
    );

    protected $course_completion_crit_compl_data = array(
        'id' => 1, 'userid' => 2, 'course' => 2, 'criteriaid' => 1, 'gradefinal' => 2, 'deleted' => 0,
    );

    protected $log_data = array(
        'id' => 1, 'time' => 1140606000, 'userid' => 2, 'ip' => '192.168.2.133', 'course' => 1,
        'module' => 'user', 'cmid' => 0, 'action' => 'update', 'url' => 'view.php', 'info' => 1,
    );

    protected $course_data = array(
        'id' => 2, 'fullname' => 'Test Course 1', 'shortname' => 'TC1', 'category' => 1, 'idnumber' => 'ID1', 'startdate' => 1140606000, 'icon' => '',
        'visible' => 1, 'summary' => 'Course Summary', 'coursetype' => 0, 'lang' => 'en',
    );

    protected $feedback_data = array(
        'id' => 1, 'course' => 1, 'name' => 'Feedback', 'intro' => 'introduction', 'page_after_submit' => 'final_page',
    );

    protected $feedback_item_data = array(
        'id' => 1, 'feedback' => 1, 'template' => 0, 'name' => 'Question',
        'presentation' => 'A\r|B\r|C\r', 'type' => 'radio', 'hasvalue' => 1, 'position' => 1, 'required' => 0,
    );

    protected $feedback_completed_data = array(
        'id' => 1, 'feedback' => 1, 'userid' => 2, 'timemodified' => 1140606000,
    );

    protected $feedback_value_data = array(
        'id' => 1, 'course_id' => 0, 'item' => 1, 'completed' => 1, 'value' => 2,
    );

    protected $tag_coll_data = array(
        'id' => 2, 'sortorder' => 1
    );

    protected $tag_instance_data = array(
        'id' => 1, 'tagid' => 1, 'itemtype' => 'feedback', 'itemid' => 1,
    );

    protected $tag_data = array(
        'id' => 1, 'userid' => 2, 'name' => 'Tag', 'isstandard' => '1', 'tagcollid' => 2
    );

    protected $grade_items_data = array(
        array('id' => 1, 'courseid' => 2, 'itemtype' => 'course', 'gradepass' => 2, 'itemmodule' => 'assignment', 'iteminstance' => 1, 'scaleid' => 3),
        array('id' => 2, 'courseid' => 2, 'itemtype' => 'mod', 'gradepass' => 0, 'itemmodule' => 'assign', 'iteminstance' => 1, 'scaleid' => 3),
    );

    protected $grade_grades_data = array(
        'id' => 1, 'itemid' => 1, 'userid' => 2, 'finalgrade' => 2, 'rawgrademin' => 2, 'rawgrademax' => 2,
    );

    protected $scorm_data = array(
        'id' => 1, 'course' => 1, 'name' => 'Scorm', 'intro' => 'Hi there, this is a scorm.',
    );

    protected $scorm_scoes_data = array(
        'id' => 1, 'scorm' => 1, 'title' => 'SCO', 'launch' => 'launch',
    );

    protected $scorm_scoes_track_data = array(
        array(
            'id' => 1, 'userid' => 2, 'scormid' => 1, 'scoid' => 1, 'attempt' => 1, 'element' => 'cmi.core.lesson_status',
            'value' => 'done', 'timemodified' => 1205445539,
        ),
        array(
            'id' => 2, 'userid' => 2, 'scormid' => 1, 'scoid' => 1, 'attempt' => 1, 'element' => 'cmi.core.score.raw',
            'value' => '100', 'timemodified' => 1205445539,
        ),
        array(
            'id' => 3, 'userid' => 2, 'scormid' => 1, 'scoid' => 1, 'attempt' => 1, 'element' => 'cmi.core.score.min',
            'value' => '10', 'timemodified' => 1205445539,
        ),
        array(
            'id' => 4, 'userid' => 2, 'scormid' => 1, 'scoid' => 1, 'attempt' => 1, 'element' => 'cmi.core.score.max',
            'value' => '90', 'timemodified' => 1205445539,
        ),
    );

    protected $course_info_field_data = array(
        'id' => 1, 'fullname' => 'Field Name', 'shortname' => 'Field', 'datatype' => 'text', 'description' => 'Description',
        'sortorder' => 1, 'categoryid' => 1, 'hidden' => 0, 'locked' => 0, 'required' => 0, 'forceunique' => 0, 'defaultdata' => 'default',
        'param1' => 'text', 'param2' => 'text', 'param3' => 'text', 'param4' => 'text', 'param5' => 'text',
    );

    protected $course_info_data_data = array(
        'id' => 1, 'fieldid' => 1, 'courseid' => 1, 'data' => 'test',
    );

    protected $course_modules_data = array(
        'id' => 1, 'course' => 1, 'module' => 8, 'instance' => 1,
    );

    protected $block_totara_stats_data = array(
        'id' => 1, 'userid' => 2, 'timestamp' => 0, 'eventtype' => 1, 'data' => 1, 'data2' => 1,
    );

    protected $message_working_data = array(
        'id' => 1, 'unreadmessageid' => 1, 'processorid' => 1,
    );

    protected $message_data = array(
        'id' => 1, 'useridfrom' => 1, 'useridto' => 2, 'subject' => 'subject', 'fullmessage' => 'message', 'fullmessageformat' => 1,
        'fullmessagehtml' => 'message', 'smallmessage' => 'msg', 'notification' => 1, 'contexturl' => '', 'contexturlname' => '', 'timecreated' => 0,
    );

    protected $message_metadata_data = array(
        'id' => 1, 'messageid' => 1, 'msgtype' => 1, 'msgstatus' => 1, 'processorid' => 1, 'urgency' => 1,
        'roleid' => 1, 'onaccept' => '', 'onreject' => '', 'icon' => 'competency-regular',
    );

    protected $cohort_data = array(
        'id' => 1, 'name' => 'cohort', 'contextid' => 0, 'descriptionformat' => 0, 'timecreated' => 0, 'timemodified' => 0, 'cohorttype' => 1,
    );

    protected $cohort_members_data = array(
        'id' => 1, 'cohortid' => 1, 'userid' => 1,
    );

    protected $prog_data = array(
        array(
            'id' => 1, 'certifid' => null, 'category' => 1, 'fullname' => 'program', 'shortname' => 'prog', 'idnumber' => '123',
            'icon' => 'default.png', 'summary' => 'summary', 'availablefrom' => 123456789, 'availableuntil' => 123456789,
        ),
        array(
            'id' => 2, 'certifid' => 1, 'category' => 1, 'fullname' => 'Cf program fullname 101', 'shortname' => 'CP101', 'idnumber' => 'CP101',
            'summary' => 'CP101', 'availablefrom' => 123456789, 'availableuntil' => 123456789, 'icon' => 'people-and-communities',
        ),
    );

    protected $context_data = array(
        array('instanceid' => 1, 'contextlevel' => CONTEXT_PROGRAM),
        array('instanceid' => 2, 'contextlevel' => CONTEXT_PROGRAM),
        array('instanceid' => 2, 'contextlevel' => CONTEXT_COURSE),
    );

    protected $assignment_data = array(
        'id' => 1, 'course' => 2, 'name' => 'Assignment 001', 'description' => 'Assignment description 001', 'format' => 0, 'assignmenttype' => 'uploadsingle',
        'resubmit' => 0, 'preventlate' => 0, 'emailteachers' => 0, 'var1' => 0, 'var2' => 0, 'var3' => 0, 'var4' => 0, 'var5' => 0, 'maxbytes' => 1048576,
        'timedue' => 1332758400, 'timeavailable' => 1332153600, 'grade' => 2, 'timemodified' => 1332153673, 'intro' => 'introduction',
    );

    protected $assignment_submissions_data = array(
        'id' => 1, 'assignment' => 1, 'userid' => 2, 'timecreated' => 0, 'timemodified' => 1332166933, 'numfiles' => 1, 'data1' => '', 'data2' => '',
        'grade' => 2, 'submissioncomment' => 'well done', 'format' => 0, 'teacher' => 0, 'timemarked' => 0, 'mailed' => 1,
    );

    protected $assign_data = array(
        'id' => 1, 'course' => 2, 'name' => 'Assign 001', 'intro' => 'Assign description 001', 'introformat' => 1, 'alwaysshowdescription' => 0,
        'completionsubmit' => 1, 'sendnotifications' => 0, 'sendlatenotifications' => 0, 'allowsubmissionsfromdate' => 1332153600, 'duedate' => 1332758400,
        'maxattempts' => -1,
    );

    protected $assign_submission_data = array(
        'id' => 1, 'assignment' => 1, 'userid' => 2, 'timecreated' => 0, 'timemodified' => 1332166933, 'status' => 'submitted',
        'groupid' => 0, 'attemptnumber' => 0,
    );

    protected $assign_onlinetext_data = array(
        'id' => 1, 'assignment' => 1, 'submission' => 1, 'onlinetext' => '<p>qweqwe</p>', 'onlineformat' => 1,
    );

    protected $assign_grades_data = array(
        'id' => 1, 'assignment' => 1, 'userid' => 2, 'timecreated' => 0, 'timemodified' => 1332166933, 'grader' => 2,
        'grade' => 100.00000, 'attemptnumber' => 0,
    );

    protected $assign_feedback_data = array(
        'id' => 1, 'assignment' => 1, 'grade' => 1, 'commenttext' => 'qweqwe', 'commentformat' => 1,
    );

    protected $scale_data = array(
        array(
            'id' => 3, 'courseid' => 0, 'userid' => 2, 'name' => 'Scale 001', 'scale' => 'Bad,Average,Good', 'description' => '', 'timemodified' => 1332243112,
        ),
        array(
            'id' => 4, 'courseid' => 0, 'userid' => 2, 'name' => 'Scale 002', 'scale' => 'Awful,Satisfactory,Good,Excellent', 'description' => '', 'timemodified' => 1332243112,
        ),
    );

    protected $files_data = array(
        'contextid' => 1, 'itemid' => 1, 'filepath' => '/totara/', 'filename' => 'icon.gif', 'filesize' => 8,
        'filearea' => 'course', 'status' => 1, 'timecreated' => 0, 'timemodified' => 0, 'sortorder' => 1,
    );

    protected $sync_log_data = array(
        'id' => 1, 'runid' => 1, 'time' => 1, 'element' => 'user', 'logtype' => 'info', 'action' => 'user sync', 'info' => 'sync started',
    );

    protected $visible_cohort_data = array(
        'id' => 1, 'cohortid' => 1, 'instanceid' => 1, 'instancetype' => 50, 'timemodified' => 1, 'timecreated' => 1, 'usermodified' => 2,
    );

    protected $certif_course_compl_archive_data = array(
        'id' => 1, 'courseid' => 1, 'userid' => 1, 'timecompleted' => 1332153671, 'grade' => 1,
    );

    protected $comp_record_history_data = array(
        'id' => 1, 'userid' => 1, 'competencyid' => 1, 'proficiency' => 1, 'timemodified' => 1332153671, 'usermodified' => 2,
    );

    protected $badges_issued = array(
        'id' => 1, 'dateexpire' => 1432153671, 'dateissued' => 1332153671, 'issuernotified' => 1332153671, 'uniquehash' => '1-2', 'userid' => 2,
        'idchar' => '', 'badgeimage' => '', 'issuername' => 'Issuer', 'issuercontact' => 'issuer@contac.com', 'name' => 'Badge1', 'type' => 1,
        'status' => 1,
    );

    protected $upgrade_log = array(
        'id' => 11111111, 'type' => 0, 'plugin' => 'local_reportbuilder', 'version' => 2014012345, 'targetversion' => 'targetversion',
        'info' => 'nothing', 'details' => null, 'backtrace' => null, 'userid' => 0, 'timemodified' => 10,
    );

    protected $logstore_standard_log_data = array('id' => 1, 'eventname' => '\core\event\user_loggedin', 'component' => 'core',
        'action' => 'loggedin', 'target' => 'user', 'objecttable' => 'user', 'objectid' => 2, 'crud' => 'r', 'edulevel' => 0,
        'contextid' => 1, 'contextlevel' => 10, 'contextinstanceid' => 0, 'userid' => 2, 'courseid' => 0, 'relateduserid' => 0,
        'anonymous' => 0, 'other' => 'a:1:{s:8:"username";s:5:"admin";}', 'timecreated' => 1416859984, 'origin' => 'web',
        'ip' => '127.0.0.1'
    );

    protected $tool_customlang_data = array('id' => 1, 'lang' => 'en', 'componentid' => 1, 'stringid' => 'totara', 'original' => 'totara',
        'master' => 'totara', 'local' => 'Totara', 'timecustomized' => 1416859984, 'timemodified' => 1416859984);

    protected $tool_customlang_components_data = array('id' => 1, 'name' => 'totara', 'version' => '1985031400');

    // NOTE: Do not add more data above - you can now avoid core changes by defining the
    // {@link phpunit_column_test_add_data()} method in your source instead.
    // See local/reportbuilder/rb_sources/rb_source_reports.php for an example.


    public static function setUpBeforeClass() {
        parent::setUpBeforeClass();
        global $DB;
        if ($DB->get_dbfamily() === 'mysql') {
            // MySQL default size is too small for some of our reports when all columns and filters are included.
            $sbs = $DB->get_field_sql("SELECT @@sort_buffer_size");
            $required = 2097152;
            if (strpos($DB->get_dbcollation(), 'utf8mb4') !== false) {
                $required = 6291456;
            }
            if ($sbs < $required) {
                $DB->execute("SET sort_buffer_size=$required");
            }
        }
    }

    protected function tearDown() {
        $this->user_info_field_data = null;
        $this->user_info_data_data = null;
        $this->course_completions_data = null;
        $this->course_completion_criteria_data = null;
        $this->course_completion_crit_compl_data = null;
        $this->log_data = null;
        $this->course_data = null;
        $this->feedback_data = null;
        $this->feedback_item_data = null;
        $this->feedback_completed_data = null;
        $this->feedback_value_data = null;
        $this->tag_coll_data = null;
        $this->tag_instance_data = null;
        $this->tag_data = null;
        $this->grade_items_data = null;
        $this->grade_grades_data = null;
        $this->type_data = null;
        $this->type_field_data = null;
        $this->type_data_data = null;
        $this->scorm_data = null;
        $this->scorm_scoes_data = null;
        $this->scorm_scoes_track_data = null;
        $this->course_info_field_data = null;
        $this->course_info_data_data = null;
        $this->course_modules_data = null;
        $this->message_working_data = null;
        $this->message_data = null;
        $this->message_metadata_data = null;
        $this->cohort_data = null;
        $this->cohort_members_data = null;
        $this->prog_data = null;
        $this->context_data = null;
        $this->assignment_data = null;
        $this->assignment_submissions_data = null;
        $this->assign_data = null;
        $this->assign_submission_data = null;
        $this->assign_onlinetext_data = null;
        $this->assign_grades_data = null;
        $this->assign_feedback_data = null;
        $this->scale_data = null;
        $this->files_data = null;
        $this->visible_cohort_data = null;
        $this->certif_course_compl_archive_data = null;
        $this->comp_record_history_data = null;
        $this->filler_data = null;
        $this->dummy_data = null;
        $this->badges_issued = null;
        $this->upgrade_log = null;
        $this->logstore_standard_log_data = null;
        $this->tool_customlang_data = null;
        $this->tool_customlang_components_data = null;
        parent::tearDown();
    }

    protected function setUp() {
        global $DB;
        parent::setup();
        set_config('enablecompletion', 1);

        $DB->delete_records('upgrade_log', array());

        $this->loadDataSet($this->createArrayDataset(array(
            'user_info_field' => array($this->user_info_field_data),
            'user_info_data' => array($this->user_info_data_data),
            'course_completion_crit_compl' => array($this->course_completion_crit_compl_data),
            'course_completion_criteria' => array($this->course_completion_criteria_data),
            'course_completions' => array($this->course_completions_data),
            'log' => array($this->log_data),
            'course' => array($this->course_data),
            'grade_items' => $this->grade_items_data,
            'grade_grades' => array($this->grade_grades_data),
            'comp_record_history' => array($this->comp_record_history_data),
            'scorm_scoes' => array($this->scorm_scoes_data),
            'scorm_scoes_track' => $this->scorm_scoes_track_data,
            'feedback' => array($this->feedback_data),
            'feedback_item' => array($this->feedback_item_data),
            'feedback_completed' => array($this->feedback_completed_data),
            'feedback_value' => array($this->feedback_value_data),
            'tag_coll' => array($this->tag_coll_data),
            'tag' => array($this->tag_data),
            'tag_instance' => array($this->tag_instance_data),
            'course_info_field' => array($this->course_info_field_data),
            'course_info_data' => array($this->course_info_data_data),
            'course_modules' => array($this->course_modules_data),
            'block_totara_stats' => array($this->block_totara_stats_data),
            'message' => array($this->message_data),
            'message_working' => array($this->message_working_data),
            'message_metadata' => array($this->message_metadata_data),
            'cohort' => array($this->cohort_data),
            'cohort_members' => array($this->cohort_members_data),
            'assignment' => array($this->assignment_data),
            'assignment_submissions' => array($this->assignment_submissions_data),
            'assign' => array($this->assign_data),
            'assign_submission' => array($this->assign_submission_data),
            'assignsubmission_onlinetext' => array($this->assign_onlinetext_data),
            'assign_grades' => array($this->assign_grades_data),
            'assignfeedback_comments' => array($this->assign_feedback_data),
            'scale' => $this->scale_data,
            'files' => array($this->files_data),
            'cohort_visibility' => array($this->visible_cohort_data),
            'certif_course_compl_archive' => array($this->certif_course_compl_archive_data),
            'badge_issued' => array($this->badges_issued),
            'context' => $this->context_data,
            'upgrade_log' => array($this->upgrade_log),
            'logstore_standard_log' => array($this->logstore_standard_log_data),
            'tool_customlang' => array($this->tool_customlang_data),
            'tool_customlang_components' => array($this->tool_customlang_components_data),
        )));
    }

    /**
     * Data provider for columns and filters test.
     *
     * Each source is tested separately, so that one failure won't prevent the other sources from being tested.
     */
    public function data_columns_and_filters() {
        $sources = array();

        // Loop through installed sources.
        $sourcelist = reportbuilder::get_source_list(true);
        foreach ($sourcelist as $sourcename => $title) {
            $sources[] = array($sourcename, $title);
        }

        return $sources;
    }

    /**
     * Check all reports columns and filters
     *
     * Note for MySQL/MariaDB: Report modification can result in queries hanging in a 'statistics' state in this test. If
     * you have this problem, the MySQL config setting "optimizer_search_depth" is likely the cause.
     *
     * @group slowtest
     * @dataProvider data_columns_and_filters
     */
    public function test_columns_and_filters($sourcename, $title) {
        global $SESSION, $DB;

        $this->resetAfterTest();
        $this->setAdminUser();

        // We need to be able to calculate the total count.
        set_config('allowtotalcount', 1, 'local_reportbuilder');

        $i = 1;
        $reportname = 'Test Report';
        $filtername = 'filtering_testreport';

        $src = reportbuilder::get_source_object($sourcename, true); // Caching here is completely fine.
        $src->phpunit_column_test_add_data($this);

        // Create a report.
        $report = new stdClass();
        $report->fullname = 'Big test report';
        $report->shortname = 'bigtest';
        $report->source = $sourcename;
        $report->hidden = 0;
        $report->accessmode = 0;
        $report->contentmode = 0;
        $report->showtotalcount = 1;
        $bigreportid = $DB->insert_record('report_builder', $report);

        $sortorder = 1;
        foreach ($src->columnoptions as $column) {
            // Create a report.
            $report = new stdClass();
            $report->fullname = $reportname;
            $report->shortname = 'test' . $i++;
            $report->source = $sourcename;
            $report->hidden = 0;
            $report->accessmode = 0;
            $report->contentmode = 0;
            $report->showtotalcount = 1;
            $reportid = $DB->insert_record('report_builder', $report);
            $col = new stdClass();
            $col->reportid = $reportid;
            $col->type = $column->type;
            $col->value = $column->value;
            $col->heading = $column->defaultheading;
            $col->sortorder = $sortorder++;
            $colid = $DB->insert_record('report_builder_columns', $col);

            // Add column to the big report with everything.
            $col->reportid = $bigreportid;
            $DB->insert_record('report_builder_columns', $col);

            // Create the reportbuilder object.
            $rb = new reportbuilder($reportid);
            $sql = $rb->build_query();

            $message = "\nReport title : {$title}\n";
            $message .= "Report sourcename : {$sourcename}\n";
            $message .= "Column option : Test {$column->type}_{$column->value} column\n";
            $message .= "SQL : {$sql[0]}\n";
            $message .= "SQL Params : " . var_export($sql[1], true) . "\n";

            // Get the column option object.
            $columnoption = reportbuilder::get_single_item($rb->columnoptions, $column->type, $column->value);

            // The answer here depends on if the column we are testing.
            $expectedcount = $src->phpunit_column_test_expected_count($columnoption);
            $this->assertEquals($expectedcount, $rb->get_full_count(), $message);

            // Remove the report again so reports report source gets expected count.
            $DB->delete_records('report_builder_columns', ['reportid' => $reportid]);
            $DB->delete_records('report_builder', ['id' => $reportid]);

            // Make sure the type string exists.
            $langstr = 'type_' . $column->type;
            if (!get_string_manager()->string_exists($langstr, 'rbsource_' . $sourcename)
                and !get_string_manager()->string_exists($langstr, 'local_reportbuilder')
            ) {
                // Display in missing string format to make it obvious.
                $type = get_string($langstr, 'rbsource_' . $sourcename);
            }
        }

        $sortorder = 1;

        foreach ($src->filteroptions as $filter) {
            // Create a report.
            $report = new stdClass();
            $report->fullname = $reportname;
            $report->shortname = 'test' . $i++;
            $report->source = $sourcename;
            $report->hidden = 0;
            $report->accessmode = 0;
            $report->contentmode = 0;
            $reportid = $DB->insert_record('report_builder', $report);
            // If the filter is based on a column, include that column.
            if (empty($filter->field)) {
                // Add a single column.
                $col = new stdClass();
                $col->reportid = $reportid;
                $col->type = $filter->type;
                $col->value = $filter->value;
                $col->heading = 'Test' . $i++;
                $col->sortorder = 1;
                $colid = $DB->insert_record('report_builder_columns', $col);
            }
            // Add a single filter.
            $fil = new stdClass();
            $fil->reportid = $reportid;
            $fil->type = $filter->type;
            $fil->value = $filter->value;
            $fil->sortorder = $sortorder++;
            $filid = $DB->insert_record('report_builder_filters', $fil);

            // Add column to the big report with everything.
            $fil->reportid = $bigreportid;
            $DB->insert_record('report_builder_filters', $fil);

            // Set session to filter by this column.
            $fname = $filter->type . '-' . $filter->value;
            switch($filter->filtertype) {
                case 'date':
                    $search = array('before' => null, 'after' => 1);
                    break;
                case 'text':
                case 'number':
                case 'select':
                default:
                    $search = array('operator' => 1, 'value' => 2);
                    break;
            }
            $SESSION->{$filtername} = array();
            $SESSION->{$filtername}[$fname] = array($search);

            // Create the reportbuilder object.
            $rb = new reportbuilder($reportid);

            // Just try to get the count, we cannot guess the actual number here.
            $rb->get_filtered_count();

            // Make sure the type string exists.
            $langstr = 'type_' . $filter->type;
            if (!get_string_manager()->string_exists($langstr, 'rbsource_' . $sourcename)
                and !get_string_manager()->string_exists($langstr, 'local_reportbuilder')
            ) {
                // Display in missing string format to make it obvious.
                $type = get_string($langstr, 'rbsource_' . $sourcename);
            }
        }

        // Make sure that joins are not using reserved SQL keywords.
        $reserved = \sql_generator::getAllReservedWords();
        foreach ($src->joinlist as $join) {
            $message = "\nReport title : {$title}\n";
            $message .= "Report sourcename : {$sourcename}\n";
            $message .= "Join name {$join->name} is invalid, it cannot be any SQL reserved word!\n";
            $this->assertArrayNotHasKey($join->name, $reserved, $message);
        }

        // Test filters are compatible with caching.
        if ($src->cacheable) {
            foreach ($src->filteroptions as $filteroption) {
                if (isset($filteroption->filteroptions['cachingcompatible'])) {
                    // Developer says they know, no need to test!
                    continue;
                }
                if (empty($filteroption->field)) {
                    // The filter is using column info to get the field data.
                    continue;
                }
                if (reportbuilder::get_single_item($src->requiredcolumns, $filteroption->type, $filteroption->value)) {
                    $this->fail("Filter '{$filteroption->type}-{$filteroption->value}' in '{$sourcename}' has custom field and is colliding with required column, you need to add 'cachingcompatible' to filter options");
                }
                if (reportbuilder::get_single_item($src->columnoptions, $filteroption->type, $filteroption->value)) {
                    $this->fail("Filter '{$filteroption->type}-{$filteroption->value}' in '{$sourcename}' has custom field and is colliding with column option, you need to add 'cachingcompatible' to filter options");
                }
            }
        }

        // Test we can execute the query with all columns and filters.
        $rb = new reportbuilder($bigreportid);
        list($sql, $params, $cacheschedule) = $rb->build_query(false, true, false);
        $rs = \local_reportbuilder\dblib\base::getbdlib()->get_counted_recordset_sql($sql, $params);
        $rs->close();

        if (!$src->cacheable) {
            return;
        }

        if ($DB->get_dbvendor() === 'mysql') {
            $info = $DB->get_server_info();
            if (version_compare($info['version'], '5.7', '<')) {
                $this->markTestSkipped('MySQL versions lower than 5.7 have severe limits, skipping source caching test');
            }
        }
        if ($DB->get_dbvendor() === 'mariadb') {
            $info = $DB->get_server_info();
            if (version_compare($info['version'], '10.2', '<')) {
                $this->markTestSkipped('MariaDB versions lower than 10.2 have severe limits, skipping source caching test');
            }
        }

        // Remove all filters that are not compatible with caching.
        foreach ($rb->filters as $filter) {
            /** @var rb_filter_type $filter */
            if ($filter->is_caching_compatible()) {
                continue;
            }
            $DB->delete_records('report_builder_filters', array('reportid' => $rb->_id, 'type' => $filter->type, 'value' => $filter->value));
        }

        // Now generate the cache table and run the query.
        $this->enable_caching($bigreportid);
        $rb = new reportbuilder($bigreportid);
        if ($rb->cache) {
            list($sql, $params, $cacheschedule) = $rb->build_query(false, true, true);
            $rs = \local_reportbuilder\dblib\base::getbdlib()->get_counted_recordset_sql($sql, $params);
            $rs->close();
        }

        reportbuilder_purge_cache($bigreportid, false);
    }

    public function test_embedded_reports() {
        $this->resetAfterTest();

        $embeddedobjects = reportbuilder_get_all_embedded_reports();
        foreach ($embeddedobjects as $embeddedobject) {
            $source = reportbuilder::get_source_object($embeddedobject->source, false, true, null);

            foreach ($embeddedobject->columns as $column) {
                foreach ($source->columnoptions as $option) {
                    /** @var rb_column_option $option */
                    if ($column['type'] === $option->type and $column['value'] === $option->value) {
                        continue 2;
                    }
                }
                $columnname = $column['type'] . '-' . $column['value'];
                $this->fail("Invalid column {$columnname} detected in embedded report {$embeddedobject->shortname}");
            }

            foreach ($embeddedobject->filters as $filter) {
                foreach ($source->filteroptions as $option) {
                    /** @var rb_filter_option $option */
                    if ($filter['type'] === $option->type and $filter['value'] === $option->value) {
                        continue 2;
                    }
                }
                $filtername = $filter['type'] . '-' . $filter['value'];
                $this->fail("Invalid filter {$filtername} detected in embedded report {$embeddedobject->shortname}");
            }
        }
    }
}
