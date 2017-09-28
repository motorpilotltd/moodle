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



defined('MOODLE_INTERNAL') || die();

/**
 * Event observer for local_custom_certification.
 */
class local_custom_certification_observer {

    /**
     * Triggered via user_assignment_created event.
     *
     * @param \local_custom_certification\event\user_assignment_created $event
     */
    public static function user_assignment_created(\local_custom_certification\event\user_assignment_created $event) {
        $messages = local_custom_certification\message::get_message_templates($event->other['certifid'], local_custom_certification\message::TYPE_ENROLLMENT);
        foreach($messages as $message){
            local_custom_certification\message::add_message($message->id, $event->other['userid']);
        }
    }
    /**
     * Triggered via user_assignment_deleted event.
     *
     * @param \local_custom_certification\event\user_assignment_deleted $event
     */
    public static function user_assignment_deleted(\local_custom_certification\event\user_assignment_deleted $event) {
        $messages = local_custom_certification\message::get_message_templates($event->other['certifid'], local_custom_certification\message::TYPE_UNENROLLMENT);
        foreach($messages as $message){
            local_custom_certification\message::add_message($message->id, $event->other['userid']);
        }
    }

    /**
     * Triggered via certification_completed event.
     *
     * @param \local_custom_certification\event\certification_completed $event
     */
    public static function certification_completed(\local_custom_certification\event\certification_completed $event) {
        $messages = local_custom_certification\message::get_message_templates($event->other['certifid'], local_custom_certification\message::TYPE_CERTIFICATION_COMPLETED);
        foreach($messages as $message){
            local_custom_certification\message::add_message($message->id, $event->other['userid']);
        }
    }

    /**
     * Triggered via certification_expired event.
     *
     * @param \local_custom_certification\event\certification_expired $event
     */
    public static function certification_expired(\local_custom_certification\event\certification_expired $event) {
        $messages = local_custom_certification\message::get_message_templates($event->other['certifid'], local_custom_certification\message::TYPE_CERTIFICATION_EXPIRED);
        foreach($messages as $message){
            local_custom_certification\message::add_message($message->id, $event->other['userid']);
        }
    }

    /**
     * Triggered via course_completed event.
     *
     * @param \core\event\course_completed $event
     */
    public static function course_completed(\core\event\course_completed $event) {
        global $DB;

        $query = "
            SELECT
                DISTINCT 
                cua.userid,
                cua.certifid 
            FROM {certif_user_assignments} cua
            JOIN {certif_courseset_courses} ccc ON ccc.certifid = cua.certifid
            JOIN {certif} c ON c.id = cua.certifid
            WHERE cua.userid = :userid 
            AND ccc.courseid = :courseid
            AND c.visible = :visible
            AND c.deleted = :deleted
        ";
        $params = [];
        $params['userid'] = $event->relateduserid;
        $params['courseid'] = $event->courseid;
        $params['visible'] = 1;
        $params['deleted'] = 0;
        $certifications = $DB->get_records_sql($query, $params);
        foreach($certifications as $certification){
            local_custom_certification\completion::check_completion($certification->certifid, $certification->userid);
        }
    }

}
