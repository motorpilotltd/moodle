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


namespace local_certification;

use core\event\course_completed;
use core\event\course_deleted;
use coursemetadatafield_arup\arupmetadata;
use local_learningrecordstore\lrsentry;
use mod_tapsenrol\taps;

class eventhandlers {
    /**
     * @param course_completed $event
     */
    public static function course_completed(course_completed $event) {
        global $DB;
        $lrsentry = new lrsentry();
        $metadata = arupmetadata::fetch(['courseid' => $event->courseid]);
        $course = get_course($event->courseid);
        $category = $DB->get_record('course_categories', ['id' => $course->category]);
        $user = \core_user::get_user($event->relateduserid);

        $lrsentry->provider = 'moodle';
        $lrsentry->providerid = $event->courseid;
        $lrsentry->completiontime = $event->timecreated;
        $lrsentry->classcategory = $category->name;

        $lrsentry->staffid = $user->idnumber;

        $lrsentry->description = $metadata->description;
        $lrsentry->providername = $course->fullname;

        $lrsentry->timemodified = time();

        $taps = new taps();
        $classes = \mod_tapsenrol\enrolclass::fetch_all_visible_by_course($event->courseid);

        if (!empty($classes)) {
            $class = reset($classes);
            $lrsentry->starttime = $class->classstarttime;
            $lrsentry->endtime = $class->classendtime;
            $lrsentry->location = $class->location;
            $lrsentry->classcost = $class->classcost;
            $lrsentry->classcostcurrency = $class->classcostcurrency;
            $lrsentry->classtype = $class->classtype;
            $lrsentry->duration = $class->classduration;
            $lrsentry->durationunits = $class->classdurationunits; //needs mapping
        } else {
            $lrsentry->duration = $metadata->duration;
            $lrsentry->durationunits = $metadata->durationunits; //needs mapping
        }

        $lrsentry->locked = true;

        $lrsentry->insert();
    }
}