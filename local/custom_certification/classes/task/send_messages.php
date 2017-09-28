<?php
namespace local_custom_certification\task;

use local_custom_certification\message;

class send_messages extends \core\task\scheduled_task
{
    public function get_name()
    {
        // Shown in admin screens
        return get_string('tasksendmessages', 'local_custom_certification');
    }

    /**
     * Prepare messages to send and
     * send pending messages
     */
    public function execute()
    {
        $this->prepare_window_open_messages();
        $this->prepare_due_messages();
        $this->prepare_overdue_messages();
        $this->prepare_enrolment_messages();

        $messages = message::get_messages_to_send();
        foreach($messages as $message){
            message::send_message($message);
        }
    }

    /**
     * Prepare message for window open action
     */
    public function prepare_window_open_messages(){
        global $DB;
        /**
         * Add messages related with recertification window open
         */

        $query = "
            SELECT
              cm.*,
              cc.userid
            FROM {certif_messages} cm
            JOIN {certif_completions} cc ON (cc.timewindowsopens - cm.triggertime) < :now AND cc.certifid = cm.certifid
            JOIN {certif} c ON c.id = cm.certifid
            JOIN {certif_user_assignments} cua ON cua.userid = cc.userid AND cua.certifid = cc.certifid
            JOIN {certif_assignments} ca ON ca.id = cua.assignmentid
            LEFT JOIN {certif_messages_log} cml ON cml.messageid = cm.id AND cml.userid = cc.userid
            WHERE cm.messagetype = :messagetype
            AND c.visible = :visible
            AND c.deleted = :deleted
            AND cm.triggertime > 0
            AND cml.id IS NULL
            AND cc.timeexpires > 0
        ";
        $params = [];
        $params['messagetype'] = message::TYPE_RECERTIFICATION_WINDOW_OPEN;
        $params['now'] = time();
        $params['visible'] = 1;
        $params['deleted'] = 0;

        $messages = $DB->get_records_sql($query, $params, 0, 100);

        foreach($messages as $message){
            message::add_message($message->id, $message->userid);
        }
    }

    /**
     * Prepare messages X days before due date
     */
    public function prepare_due_messages(){
        global $DB;

        $query = "SELECT
              cm.*,
              cua.userid
            FROM {certif_user_assignments} cua 
            JOIN {certif_assignments} ca ON ca.id = cua.assignmentid
            JOIN {certif_completions} comp ON comp.certifid = cua.certifid AND comp.userid = cua.userid
            JOIN {certif_messages} cm ON cm.certifid = cua.certifid AND (comp.duedate - cm.triggertime) < :now
            JOIN {certif} c ON c.id = cm.certifid
            LEFT JOIN {certif_messages_log} cml ON cml.messageid = cm.id AND cml.userid = cua.userid
            WHERE cm.messagetype = :messagetype
            AND comp.duedate > 0
            AND c.visible = :visible
            AND c.deleted = :deleted
            AND cml.id IS NULL
        ";
        $params = [];
        $params['messagetype'] = message::TYPE_CERTIFICATION_BEFORE_EXPIRATION;
        $params['now'] = time();
        $params['visible'] = 1;
        $params['deleted'] = 0;

        $messages = $DB->get_records_sql($query, $params, 0, 100);
        foreach($messages as $message){
            message::add_message($message->id, $message->userid);
        }
    }

    /**
     * Prepare overdue messages
     */
    public function prepare_overdue_messages(){
        global $DB;

        $query = "SELECT
              cm.*,
              cua.userid
            FROM {certif_user_assignments} cua 
            JOIN {certif_assignments} ca ON ca.id = cua.assignmentid
            JOIN {certif_completions} comp ON comp.certifid = cua.certifid AND comp.userid = cua.userid
            JOIN {certif_messages} cm ON cm.certifid = cua.certifid
            JOIN {certif} c ON c.id = cm.certifid
            LEFT JOIN {certif_messages_log} cml ON cml.messageid = cm.id AND cml.userid = cua.userid
            WHERE cm.messagetype = :messagetype
            AND comp.duedate < :now
            AND comp.duedate > 0
            AND c.visible = :visible
            AND c.deleted = :deleted
            AND cml.id IS NULL
        ";
        $params = [];
        $params['messagetype'] = message::TYPE_CERTIFICATION_EXPIRED;
        $date = new \DateTime('now');
        $date->sub(new \DateInterval('P1D'));
        $params['now'] = $date->getTimestamp();
        $params['visible'] = 1;
        $params['deleted'] = 0;

        $messages = $DB->get_records_sql($query, $params, 0, 100);
        foreach($messages as $message){
            message::add_message($message->id, $message->userid);
        }
    }

    /**
     * Prepare enrolment messages
     */
    public function prepare_enrolment_messages(){
        global $DB;
        $query = "
            SELECT
              CONCAT(cua.id, '#', cm.id) as uniqueid,
              cm.*,
              cua.userid
            FROM {certif_user_assignments} cua
            JOIN {certif_assignments} ca ON ca.id = cua.assignmentid
            JOIN {certif} c ON c.id = cua.certifid
            JOIN {certif_messages} cm ON cm.messagetype = :messagetype AND cm.certifid = cua.certifid
            LEFT JOIN {certif_messages_log} cml ON cml.messagetype = cm.messagetype AND cml.userid = cua.userid
            WHERE c.visible = :visible
            AND c.deleted = :deleted
            AND cml.id IS NULL
        ";
        $params = [];
        $params['messagetype'] = message::TYPE_ENROLLMENT;
        $params['visible'] = 1;
        $params['deleted'] = 0;

        $messages = $DB->get_records_sql($query, $params, 0, 100);

        foreach($messages as $message){
            message::add_message($message->id, $message->userid);
        }
    }
}
