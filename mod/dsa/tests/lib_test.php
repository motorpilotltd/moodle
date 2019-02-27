<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * @author Andrew Hancox <andrewdchancox@googlemail.com>
 * @package mod
 * @subpackage dsa
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/mod/assign/lib.php');
require_once($CFG->dirroot . '/mod/assign/locallib.php');
require_once($CFG->dirroot . '/mod/assign/tests/base_test.php');

class mod_dsa_lib_testcase extends advanced_testcase {
    public function setUp(){
        $this->resetAfterTest();
    }

    protected function create_instance($params=array()) {
        $generator = $this->getDataGenerator()->get_plugin_generator('mod_dsa');
        $instance = $generator->create_instance($params);
        $cm = get_coursemodule_from_instance('dsa', $instance->id);
        $context = context_module::instance($cm->id);
        return [$instance, $cm, $context];
    }

    public function test_dsa_get_completion_state() {
        $course = $this->getDataGenerator()->create_course();

        list($instance, $cm, $context) = $this->create_instance(['course' => $course->id]);
        $learner = $this->getDataGenerator()->create_user(['idnumber' => 'testidnumber']);

        $this->assertFalse(dsa_get_completion_state($course, $cm, $learner->id, null));


        $events = $this->redirectEvents();

        $task = new \mod_dsa\task\sync();
        \mod_dsa\testapiclient::$idnumbertoreturn = $learner->idnumber;
        \mod_dsa\testapiclient::$teststate = \mod_dsa\testapiclient::DSA_TESTSTATE_ONEINPROGRESS;
        $task->execute();

        $this->assertEquals(1, $events->count());
        $event = $events->get_events()[0];
        $this->assertEquals($learner->id, $event->relateduserid);
        $this->assertEquals('mod_dsa', $event->component);

        $this->assertFalse(dsa_get_completion_state($course, $cm, $learner->id, null));

        \mod_dsa\testapiclient::$teststate = \mod_dsa\testapiclient::DSA_TESTSTATE_ALLDONE;
        $task->execute();

        $this->assertTrue(dsa_get_completion_state($course, $cm, $learner->id, null));
    }

    public function test_dsa_get_completion_state_on_enrolment() {
        global $DB;

        $course = $this->getDataGenerator()->create_course();

        list($instance, $cm, $context) = $this->create_instance(['course' => $course->id]);
        $learner = $this->getDataGenerator()->create_user(['idnumber' => 'testidnumber']);

        \mod_dsa\testapiclient::$idnumbertoreturn = $learner->idnumber;
        \mod_dsa\testapiclient::$teststate = \mod_dsa\testapiclient::DSA_TESTSTATE_ALLDONE;

        $task = new \mod_dsa\task\sync();
        $task->execute();

        $studentroleid = $DB->get_field('role', 'id', array('shortname' => 'student'));
        $this->getDataGenerator()->enrol_user($learner->id, $course->id, $studentroleid);

        $this->assertTrue(dsa_get_completion_state($course, $cm, $learner->id, null));
    }

    public function test_dsa_get_completion_state_on_add_module() {
        global $DB;

        $course = $this->getDataGenerator()->create_course();
        $learner = $this->getDataGenerator()->create_user(['idnumber' => 'testidnumber']);
        $studentroleid = $DB->get_field('role', 'id', array('shortname' => 'student'));
        $this->getDataGenerator()->enrol_user($learner->id, $course->id, $studentroleid);

        \mod_dsa\testapiclient::$idnumbertoreturn = $learner->idnumber;
        \mod_dsa\testapiclient::$teststate = \mod_dsa\testapiclient::DSA_TESTSTATE_ALLDONE;

        $task = new \mod_dsa\task\sync();
        $task->execute();

        list($instance, $cm, $context) = $this->create_instance(['course' => $course->id]);

        $task = \core\task\manager::get_next_adhoc_task(time());
        $this->assertInstanceOf('\\mod_dsa\\task\\resynccourse', $task);
        $task->execute();
        \core\task\manager::adhoc_task_complete($task);

        $this->assertTrue(dsa_get_completion_state($course, $cm, $learner->id, null));
    }

    public function test_dsa_no_assessments() {
        $course = $this->getDataGenerator()->create_course();

        list($instance, $cm, $context) = $this->create_instance(['course' => $course->id]);
        $learner = $this->getDataGenerator()->create_user(['idnumber' => 'testidnumber']);

        $this->assertFalse(dsa_get_completion_state($course, $cm, $learner->id, null));

        $task = new \mod_dsa\task\sync();
        \mod_dsa\testapiclient::$idnumbertoreturn = $learner->idnumber;
        \mod_dsa\testapiclient::$teststate = \mod_dsa\testapiclient::DSA_TESTSTATE_ALLDONE;
        $task->execute();

        $this->assertTrue(dsa_get_completion_state($course, $cm, $learner->id, null));

        \mod_dsa\testapiclient::$teststate = \mod_dsa\testapiclient::DSA_TESTSTATE_NOASSESSMENTS;

        $task->execute();

        $this->assertFalse(dsa_get_completion_state($course, $cm, $learner->id, null));
    }

    public function test_dsa_deleteorphannedrecords() {
        global $DB;

        $learner = $this->getDataGenerator()->create_user(['idnumber' => 'testidnumber']);

        $task = new \mod_dsa\task\sync();
        \mod_dsa\testapiclient::$idnumbertoreturn = $learner->idnumber;
        \mod_dsa\testapiclient::$teststate = \mod_dsa\testapiclient::DSA_TESTSTATE_ONEINPROGRESS;
        $task->execute();

        $this->assertEquals(3, $DB->count_records('dsa_assessment', ['userid' => $learner->id]));

        \mod_dsa\testapiclient::$teststate = \mod_dsa\testapiclient::DSA_TESTSTATE_ALLDONE;

        $task->execute();

        $this->assertEquals(2, $DB->count_records('dsa_assessment', ['userid' => $learner->id]));
    }

    public function test_dsa_get_completion_state_unsynced() {
        $course = $this->getDataGenerator()->create_course();

        list($instance, $cm, $context) = $this->create_instance(['course' => $course->id]);
        $learner = $this->getDataGenerator()->create_user(['idnumber' => 'testidnumber']);

        $this->assertFalse(dsa_get_completion_state($course, $cm, $learner->id, null));

        $task = new \mod_dsa\task\sync();
        \mod_dsa\testapiclient::$idnumbertoreturn = '';
        \mod_dsa\testapiclient::$teststate = \mod_dsa\testapiclient::DSA_TESTSTATE_ALLDONE;

        $task->execute();

        $this->assertTrue(dsa_get_completion_state($course, $cm, $learner->id, null));
    }

    /**
     * Test get_course_completion_status
     */
    public function test_get_course_completion_status() {
        global $DB, $CFG;
        require_once($CFG->dirroot.'/completion/criteria/completion_criteria_activity.php');

        $this->resetAfterTest(true);

        $CFG->enablecompletion = true;
        $student = $this->getDataGenerator()->create_user(['idnumber' => 'testidnumber']);

        $course = $this->getDataGenerator()->create_course(array('enablecompletion' => 1));
        list($instance, $cmdata, $context) = $this->create_instance(['course' => $course->id, 'completion' => 1, 'completion' => COMPLETION_TRACKING_AUTOMATIC]);

        $studentrole = $DB->get_record('role', array('shortname' => 'student'));
        $this->getDataGenerator()->enrol_user($student->id, $course->id, $studentrole->id);

        $criteriadata = new stdClass();
        $criteriadata->id = $course->id;
        $criteriadata->criteria_activity = [$cmdata->id => 1];
        $criterion = new completion_criteria_activity();
        $criterion->update_config($criteriadata);

        // Handle overall aggregation.
        $aggdata = array(
                'course'        => $course->id,
                'criteriatype'  => null
        );
        $aggregation = new completion_aggregation($aggdata);
        $aggregation->setMethod(COMPLETION_AGGREGATION_ALL);
        $aggregation->save();

        $task = new \mod_dsa\task\sync();
        \mod_dsa\testapiclient::$idnumbertoreturn = $student->idnumber;
        \mod_dsa\testapiclient::$teststate = \mod_dsa\testapiclient::DSA_TESTSTATE_ALLDONE;

        $task->execute();
        $this->assertTrue(dsa_get_completion_state($course, $cmdata, $student->id, null));

        $params = array(
                'userid'    => $student->id,
                'course'  => $course->id
        );

        require_once($CFG->dirroot.'/completion/cron.php');
        completion_cron_criteria();
        $this->waitForSecond();
        completion_cron_completions();

        $ccompletion = new completion_completion($params);
        $this->assertTrue($ccompletion->is_complete());

        // Add a new, incomplete assessment and activity and course should be incomplete.

        \mod_dsa\testapiclient::$teststate = \mod_dsa\testapiclient::DSA_TESTSTATE_ONEINPROGRESS;
        $task->execute();

        $this->assertFalse(dsa_get_completion_state($course, $cmdata, $student->id, null));

        $ccompletion = new completion_completion($params);
        $this->assertFalse($ccompletion->is_complete());

        require_once($CFG->dirroot.'/completion/cron.php');
        completion_cron_criteria();
        $this->waitForSecond();
        completion_cron_completions();

        $ccompletion = new completion_completion($params);
        $this->assertFalse($ccompletion->is_complete());
    }
}
