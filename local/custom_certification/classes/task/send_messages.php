<?php
namespace local_custom_certification\task;

defined('MOODLE_INTERNAL') || die();

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
        $this->prepare_before_expiry_messages();
        $this->prepare_expired_messages();
        $this->prepare_overdue_messages();
        $this->prepare_overdue_reminder_messages();

        $messages = message::get_messages_to_send(100);
        foreach($messages as $message){
            message::send_message($message);
        }
    }

    /**
     * Prepare messages to be sent on expiry.
     * Sent when expired.
     *  - Has an archived record (otherwise overdue).
     * Sent if not exempt.
     * Sent if progress is 0.
     * Will not send if within chosen number of days of asssignment to certification (donotsendtime).
     * Will only send once but may not be immediately.
     *  - Depends on donotsendtime window (will send as soon as clear of this).
     *  - Depends on exemption (will send as soon as not exempt).
     *  - May also send as soon as user returns to 0 progress (if not already sent).
     */
    public function prepare_expired_messages() {
        global $DB;

        $now = time();

        $concat = $DB->sql_concat_join("'-'", ['cm.id', 'cua.userid', 'cc.id']);
        $query = "
            SELECT {$concat} as uniqueid,
                   cm.id,
                   cua.userid,
                   cc.id as messagetypeid
              FROM {certif_user_assignments} cua
              JOIN {certif_assignments} ca ON ca.id = cua.assignmentid
              JOIN {certif_messages} cm ON cm.certifid = cua.certifid
              JOIN {certif} c ON c.id = cm.certifid
              JOIN {certif_completions} cc ON cc.certifid = cua.certifid AND cc.userid = cua.userid
         LEFT JOIN {certif_completions_archive} cca ON cca.certifid = cc.certifid AND cca.userid = cc.userid
         LEFT JOIN {certif_exemptions} ce ON ce.certifid = c.id AND ce.userid = cua.userid AND ce.archived = 0 AND (ce.timeexpires = 0 OR ce.timeexpires > {$now})
         LEFT JOIN {certif_messages_log} cml ON cml.messageid = cm.id AND cml.userid = cua.userid AND cml.messagetypeid = cc.id
             WHERE cm.messagetype = :messagetype
                   AND (cua.timecreated + cm.donotsendtime) < {$now}
                   AND cc.duedate > 0
                   AND cc.progress = 0
                   AND (cc.duedate) < {$now}
                   AND cca.id IS NOT NULL
                   AND ce.id IS NULL
                   AND c.visible = 1
                   AND c.deleted = 0
                   AND cml.id IS NULL
        ";
        $params = [];
        $params['messagetype'] = message::TYPE_EXPIRED;

        $messages = $DB->get_records_sql($query, $params, 0, 250);
        foreach($messages as $message){
            message::add_message($message->id, $message->userid, $message->messagetypeid);
        }
    }

    /**
     * Prepare messages X days before expiry.
     * Sent (once only) within 24 hours of the hitting the triggertime before expiry (if not sent then, never sent).
     * Sent if progress is 0.
     * Only sent if user clear of donotsendtime window.
     * Only sent if not exempt.
     */
    public function prepare_before_expiry_messages(){
        global $DB;

        $now = time();
        $hours24 = 24 * 60 * 60;
        if ($this->get_last_run_time() < ($now - $hours24)) {
            // Adjust 24 hour window to account for task not running.
            $hours24 = $now - $this->get_last_run_time() + (6 * 60 * 60);
        }

        $concat = $DB->sql_concat_join("'-'", ['cm.id', 'cua.userid', 'cc.id']);
        $query = "
            SELECT {$concat} as uniqueid,
                   cm.id,
                   cua.userid,
                   cc.id as messagetypeid
              FROM {certif_user_assignments} cua
              JOIN {certif_assignments} ca ON ca.id = cua.assignmentid
              JOIN {certif_messages} cm ON cm.certifid = cua.certifid
              JOIN {certif} c ON c.id = cm.certifid
              JOIN {certif_completions} cc ON cc.certifid = cua.certifid AND cc.userid = cua.userid
         LEFT JOIN {certif_exemptions} ce ON ce.certifid = c.id AND ce.userid = cua.userid AND ce.archived = 0 AND (ce.timeexpires = 0 OR ce.timeexpires > {$now})
         LEFT JOIN {certif_messages_log} cml ON cml.messageid = cm.id AND cml.userid = cua.userid AND cml.messagetypeid = cc.id
             WHERE cm.messagetype = :messagetype
                   AND (cua.timecreated + cm.donotsendtime) < {$now}
                   AND cc.duedate > 0
                   AND cc.progress = 0
                   AND (cc.duedate - cm.triggertime) < {$now} AND (cc.duedate - cm.triggertime) > ({$now} - {$hours24})
                   AND ce.id IS NULL
                   AND c.visible = 1
                   AND c.deleted = 0
                   AND cml.id IS NULL
        ";
        $params = [];
        $params['messagetype'] = message::TYPE_BEFORE_EXPIRY;

        $messages = $DB->get_records_sql($query, $params, 0, 250);
        foreach($messages as $message){
            message::add_message($message->id, $message->userid, $message->messagetypeid);
        }
    }

    /**
     * Prepare overdue messages.
     * Sent when overdue.
     * Sent if no previous completion/expiration (Otherwise message::TYPE_EXPIRED will be sent).
     *  - No archived record.
     * Sent if not exempt.
     * Sent if progress is 0.
     * Will not send if within chosen number of days of asssignment to certification (donotsendtime).
     * Will only send once but may not be immediately.
     *  - Depends on donotsendtime window (will send as soon as clear of this).
     *  - Depends on exemption (will send as soon as not exempt).
     *  - May also send as soon as user returns to 0 progress (if not already sent).
     */
    public function prepare_overdue_messages(){
        global $DB;

        $now = time();

        $concat = $DB->sql_concat_join("'-'", ['cm.id', 'cua.userid', 'cc.id']);
        $query = "
            SELECT {$concat} as uniqueid,
                   cm.id,
                   cua.userid,
                   cc.id as messagetypeid
              FROM {certif_user_assignments} cua
              JOIN {certif_assignments} ca ON ca.id = cua.assignmentid
              JOIN {certif_messages} cm ON cm.certifid = cua.certifid
              JOIN {certif} c ON c.id = cm.certifid
              JOIN {certif_completions} cc ON cc.certifid = cua.certifid AND cc.userid = cua.userid
         LEFT JOIN {certif_completions_archive} cca ON cca.certifid = cc.certifid AND cca.userid = cc.userid
         LEFT JOIN {certif_exemptions} ce ON ce.certifid = c.id AND ce.userid = cua.userid AND ce.archived = 0 AND (ce.timeexpires = 0 OR ce.timeexpires > {$now})
         LEFT JOIN {certif_messages_log} cml ON cml.messageid = cm.id AND cml.userid = cua.userid AND cml.messagetypeid = cc.id
             WHERE cm.messagetype = :messagetype
                   AND (cua.timecreated + cm.donotsendtime) < {$now}
                   AND cc.duedate > 0
                   AND cc.progress = 0
                   AND (cc.duedate) < {$now}
                   AND cca.id IS NULL
                   AND ce.id IS NULL
                   AND c.visible = 1
                   AND c.deleted = 0
                   AND cml.id IS NULL
        ";
        $params = [];
        $params['messagetype'] = message::TYPE_OVERDUE;

        $messages = $DB->get_records_sql($query, $params, 0, 250);
        foreach($messages as $message){
            message::add_message($message->id, $message->userid, $message->messagetypeid);
        }
    }

    /**
     * Prepare overdue reminder messages.
     * Sent after either message::TYPE_EXPIRED OR message::TYPE_OVERDUE.
     * Subsequently sent after message::TYPE_OVERDUE_REMINDER.
     * Sent if not exempt.
     * Sent if progress is 0.
     * May not be immediately.
     *  - Depends on exemption (will send as soon as not exempt).
     *  - May also send as soon as user returns to 0 progress.
     */
    public function prepare_overdue_reminder_messages() {
        global $DB;

        $now = time();
        $hours24 = 24 * 60 * 60;
        if ($this->get_last_run_time() < ($now - $hours24)) {
            // Adjust 24 hour window to account for task not running.
            $hours24 = $now - $this->get_last_run_time() + (6 * 60 * 60);
        }

        $concat = $DB->sql_concat_join("'-'", ['cm.id', 'cua.userid', 'cc.id']);
        $query = "
            SELECT {$concat} as uniqueid,
                   cm.id,
                   cua.userid,
                   cc.id as messagetypeid
              FROM {certif_user_assignments} cua
              JOIN {certif_assignments} ca ON ca.id = cua.assignmentid
              JOIN {certif_messages} cm ON cm.certifid = cua.certifid
              JOIN {certif} c ON c.id = cm.certifid
              JOIN {certif_completions} cc ON cc.certifid = cua.certifid AND cc.userid = cua.userid
              JOIN {certif_messages_log} cml ON cml.messagetype IN (:messagetype1, :messagetype2, :messagetype3) AND cml.userid = cua.userid AND cml.messagetypeid = cc.id
         LEFT JOIN {certif_exemptions} ce ON ce.certifid = c.id AND ce.userid = cua.userid AND ce.archived = 0 AND (ce.timeexpires = 0 OR ce.timeexpires > {$now})
             WHERE cm.messagetype = :messagetype
                   AND cc.timecompleted IS NULL
                   AND cc.progress = 0
                   AND ce.id IS NULL
                   AND c.visible = 1
                   AND c.deleted = 0
          GROUP BY cua.userid, cc.id, cm.id, cm.triggertime
            HAVING (MAX(cml.timesent) + cm.triggertime) < {$now} AND (MAX(cml.timesent) + cm.triggertime) > ({$now} - {$hours24})
        ";
        $params = [];
        $params['messagetype1'] = $params['messagetype'] = message::TYPE_OVERDUE_REMINDER;
        $params['messagetype2'] = message::TYPE_OVERDUE;
        $params['messagetype3'] = message::TYPE_EXPIRED;

        $messages = $DB->get_records_sql($query, $params, 0, 250);
        foreach($messages as $message){
            message::add_message($message->id, $message->userid, $message->messagetypeid);
        }
    }
}
