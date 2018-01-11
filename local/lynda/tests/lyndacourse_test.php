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
 * @package    local_lynda
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

use local_gioska\local\soaptypes\zher_st_ws_mdl_context_info;
use local_gioska\soap\incomingservices;

defined('MOODLE_INTERNAL') || die();

class lyndacourse_test extends advanced_testcase {
    public function setUp() {
        $this->resetAfterTest();
    }

    public function test_synccourses() {
        global $DB;

        require_once('fixtures/lyndaapimock.php');
        $api = new \local_lynda\lyndaapimock();

        $api->synccourses();
        $this->assertEquals(5, $DB->count_records('local_lynda_course'));

        // Delete a course from the API response.
        $api->dropcoursefromresponse();
        $api->synccourses();
        $this->assertEquals(5, $DB->count_records('local_lynda_course'));
        $this->assertEquals(1, $DB->count_records('local_lynda_course', ['deletedbylynda' => 1]));

        // Reset back to all 5.
        $api->reset();
        $api->synccourses();
        $this->assertEquals(5, $DB->count_records('local_lynda_course'));
        $this->assertEquals(0, $DB->count_records('local_lynda_course', ['deletedbylynda' => 1]));

        // Check we have the expected number of tags.
        $this->assertEquals(7, $DB->count_records('local_lynda_tags'));
        $this->assertEquals(3, $DB->count_records('local_lynda_tagtypes'));
        $this->assertEquals(15, $DB->count_records('local_lynda_coursetags'));

        // Check a course has been correctly populated.
        $course = new \local_lynda\lyndacourse(['remotecourseid' => 180212]);
        $tags = $DB->get_records_menu('local_lynda_coursetags', ['remotecourseid' => 180212], 'remotetagid', 'id, remotetagid');
        $this->assertEquals("100 Courses and Counting: David Rivers on Elearning", $course->title);
        $this->assertEquals("David Rivers has been recording elearning courses for lynda.com for over a decade. He's one of our star authors! In this interview (conducted from his home recording studio in Canada), he offers lessons from his 100-course journey with lynda.com. David shares his process for creating and recording course content, and provides inspiration for other authors who want to turn their knowledge into tutorials for the emerging elearning market.",
                $course->description);
        $this->assertEquals(['33', '100', '330'], array_values($tags));
        // Check logo

        // Check a tag has been correctly populated.
        $tag = $DB->get_record('local_lynda_tags', ['remotetagid' => 33]);
        $this->assertEquals('In Person', $tag->name);
        $this->assertEquals('2', $tag->remotetypeid);

        // Check a tag type has been correctly populated.
        $tag = $DB->get_record('local_lynda_tagtypes', ['remotetypeid' => 2]);
        $this->assertEquals('Topic', $tag->name);
    }

    public function test_synccourses_update() {
        global $DB;

        require_once('fixtures/lyndaapimock.php');
        $api = new \local_lynda\lyndaapimock();

        $api->synccourses();

        // Check a course has been correctly populated.
        $course = new \local_lynda\lyndacourse(['remotecourseid' => 180212]);
        $tags = $DB->get_records_menu('local_lynda_coursetags', ['remotecourseid' => 180212], 'remotetagid', 'id, remotetagid');
        $this->assertEquals("100 Courses and Counting: David Rivers on Elearning", $course->title);
        $this->assertEquals("David Rivers has been recording elearning courses for lynda.com for over a decade. He's one of our star authors! In this interview (conducted from his home recording studio in Canada), he offers lessons from his 100-course journey with lynda.com. David shares his process for creating and recording course content, and provides inspiration for other authors who want to turn their knowledge into tutorials for the emerging elearning market.",
                $course->description);
        $this->assertEquals(['33', '100', '330'], array_values($tags));

        $api->useupdatedcourses();
        $api->synccourses();

        $this->assertEquals(5, $DB->count_records('local_lynda_course'));
        // Check a course has been correctly populated.
        $course = new \local_lynda\lyndacourse(['remotecourseid' => 180212]);
        $tags = $DB->get_records_menu('local_lynda_coursetags', ['remotecourseid' => 180212], 'remotetagid', 'id, remotetagid');
        $this->assertEquals("101 Courses and Counting: David Rivers on Elearning", $course->title);
        $this->assertEquals("David Rivers has been recording elearning courses for lynda.com for over eleven years. He's one of our star authors! In this interview (conducted from his home recording studio in Canada), he offers lessons from his 100-course journey with lynda.com. David shares his process for creating and recording course content, and provides inspiration for other authors who want to turn their knowledge into tutorials for the emerging elearning market.",
                $course->description);
        $this->assertEquals(['33', '100'], array_values($tags));
    }

    public function test_synccoursecompletion() {
        global $DB;

        require_once('fixtures/lyndaapimock.php');
        $api = new \local_lynda\lyndaapimock();
        $api->synccourses();

        $api->synccoursecompletion(time(), time());

        $cpdrecords = $DB->get_records('local_taps_enrolment');
        $this->assertEquals(6, count($cpdrecords));

        $record1 = reset($cpdrecords);
        $this->assertEquals('Navisworks Essential Training', $record1->classname);
        $this->assertEquals('Lynda.com', $record1->provider);
        $this->assertEquals('1466575404', $record1->classcompletiontime);
        $this->assertEquals('172.62', $record1->duration);
        $this->assertEquals('Minute(s)', $record1->durationunits);
        $this->assertEquals('External Course Online', $record1->classtype);
        $this->assertEquals('Professional Development', $record1->classcategory);
        $this->assertEquals("David Rivers has been recording elearning courses for lynda.com for over a decade. He's one of our star authors! In this interview (conducted from his home recording studio in Canada), he offers lessons from his 100-course journey with lynda.com. David shares his process for creating and recording course content, and provides inspiration for other authors who want to turn their knowledge into tutorials for the emerging elearning market.", $record1->learningdesc);
        $this->assertEquals('padded0', $record1->staffid);

        $api->synccoursecompletion(time(), time());
        $cpdrecords = $DB->get_records('local_taps_enrolment');
        $this->assertEquals(6, count($cpdrecords));
    }

    public function test_synccourseprogress() {
        require_once('fixtures/lyndaapimock.php');
        $api = new \local_lynda\lyndaapimock();
        $api->synccourses();

        $api->synccourseprogress(time(), time());

        $progressrecords = \local_lynda\lyndacourseprogress::fetch_all([]);
        $this->assertEquals(6, count($progressrecords));

        $record1 = reset($progressrecords);
        $this->assertEquals('100 Courses and Counting: David Rivers on Elearning', $record1->lyndacourse->title);
        $this->assertEquals(25, $record1->percentcomplete);
        $this->assertEquals('1515409077', $record1->lastviewed);
        $user = core_user::get_user($record1->userid);
        $this->assertEquals('padded0', $user->idnumber);

        $api->useupdatedindividualusagedetailresponse();
        $api->synccourseprogress(time(), time());

        $progressrecords = \local_lynda\lyndacourseprogress::fetch_all([]);
        $this->assertEquals(6, count($progressrecords));

        $record1 = reset($progressrecords);
        $this->assertEquals('100 Courses and Counting: David Rivers on Elearning', $record1->lyndacourse->title);
        $this->assertEquals(75, $record1->percentcomplete);
        $this->assertEquals('1515409077', $record1->lastviewed);
        $user = core_user::get_user($record1->userid);
        $this->assertEquals('padded0', $user->idnumber);
    }
}