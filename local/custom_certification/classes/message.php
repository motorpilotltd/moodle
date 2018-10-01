<?php

namespace local_custom_certification;

defined('MOODLE_INTERNAL') || die();

class message {
	// Enrolment - EVENT triggered.
	const TYPE_ENROLMENT = 1;
	// Unenrolment - EVENT triggered.
	const TYPE_UNENROLMENT = 2;
	// Certification/Recertification completed - EVENT triggered.
	const TYPE_COMPLETED = 3;
	// DEPRECATED #4 - DO NOT REUSE.
	// Certification/Recertification expired.
	const TYPE_EXPIRED = 5;
	// Certification soon to expire (Expiry window notification) - TASK triggered.
    const TYPE_BEFORE_EXPIRY = 6;
    // Certification overdue - TASK triggered.
    const TYPE_OVERDUE = 7;
    // Certification overdue reminders (recurring, regularly, after TYPE_CERTIFICATION_OVERDUE sent) - TASK triggered.
    const TYPE_OVERDUE_REMINDER = 8;

	/**
	 * Get all available types of messages
	 *
	 * @return array key = message type, value = message type name
	 */
	public static function get_types(){
		$types = [];
		$types[self::TYPE_ENROLMENT] = get_string('messagetype:enrolment', 'local_custom_certification');
		$types[self::TYPE_UNENROLMENT] = get_string('messagetype:unenrolment', 'local_custom_certification');
		$types[self::TYPE_COMPLETED] = get_string('messagetype:completed', 'local_custom_certification');
		$types[self::TYPE_EXPIRED] = get_string('messagetype:expired', 'local_custom_certification');
		$types[self::TYPE_BEFORE_EXPIRY] = get_string('messagetype:beforeexpiry', 'local_custom_certification');
		$types[self::TYPE_OVERDUE] = get_string('messagetype:overdue', 'local_custom_certification');
		$types[self::TYPE_OVERDUE_REMINDER] = get_string('messagetype:overduereminder', 'local_custom_certification');

		return $types;
	}

	/**
	 * Get all messages configured for given certification and type
	 *
	 * @param $certifid Certification ID
	 * @param $type Message type (see class constants)
	 * @return array
	 */
	public static function get_message_templates($certifid, $type){
		global $DB;

		$query = "
			SELECT
				cm.*
			FROM {certif_messages} cm
			JOIN {certif} c ON c.id = cm.certifid
			WHERE c.visible = :visible
			AND c.deleted = :deleted
			AND cm.certifid = :certifid
			AND cm.messagetype = :messagetype
		";

		$params = [];
		$params['visible'] = 1;
		$params['deleted'] = 0;
		$params['certifid'] = $certifid;
		$params['messagetype'] = $type;

		$messages = $DB->get_records_sql($query, $params);

		return $messages;
	}


	/**
	 * Add message to queue
	 * If 3party email is configured for this message instance, add message for each 3rd party email
	 *
	 * @param $messageid Message ID
	 * @param $userid User id
     * @param $messagetypeid ID to lock task triggered message to a particular instance of 'something'.
	 */
	public static function add_message($messageid, $userid, $messagetypeid = 0){
		global $DB;

		$user = $DB->get_record('user', ['id' => $userid]);
		$query = "
			SELECT
				c.*
			FROM {certif_messages} cm
			JOIN {certif} c ON c.id = cm.certifid
			WHERE cm.id = :messageid
			AND c.visible = :visible
			AND c.deleted = :deleted
		";

		$param['messageid'] = $messageid;
		$param['visible'] = 1;
		$param['deleted'] = 0;
		$message = $DB->get_record('certif_messages', ['id' => $messageid]);
		$certification = $DB->get_record_sql($query, $param);

		$replace['%userfirstname%'] = $user->firstname;
		$replace['%userlastname%'] = $user->lastname;
		$replace['%userfullname%'] = fullname($user);
		$replace['%username%'] = $user->username;
		$replace['%certificationfullname%'] = $certification->fullname;

		$subject = str_replace(array_flip($replace), $replace, $message->subject);
		$body = str_replace(array_flip($replace), $replace, $message->body);

		$record = new \stdClass();
		$record->messageid = $messageid;
		$record->messagetype = $message->messagetype;
        $record->messagetypeid = $messagetypeid;
		$record->userid = $userid;
		$record->subject = $subject;
		$record->body = $body;
		$record->email = $user->email;
		$record->timesent = 0;
		$DB->insert_record('certif_messages_log', $record);

		if($message->recipient == 1){
			$thirdpartyemails = explode(';', $message->recipientemail);
			foreach($thirdpartyemails as $thirdpartyemail){
				if(!empty($thirdpartyemail)){
					$record->userid = 0;
					$record->email = trim($thirdpartyemail);
					$DB->insert_record('certif_messages_log', $record);
				}
			}
		}
	}

	/**
	 * Get messages that are not send yet
	 *
	 * @param int $limit Message limit
     * @global \moodle_database $DB
	 * @return array
	 */
	public static function get_messages_to_send($limit = 20){
		global $DB;
		return $DB->get_records('certif_messages_log', ['timesent' => 0], 'id ASC', '*', 0, $limit);
	}


	/**
	 * Sends single message using standard moodle function email_to_user
	 *
	 * @param $message Message log ID or record object that will be send
	 */
	public static function send_message($message){
		global $DB;
		if(!is_object($message)){
			$message = $DB->get_record('certif_messages_log', ['id' => $message]);
		}
		if($message){
			$user = $DB->get_record('user', ['id' => $message->userid]);
			$contact = \core_user::get_support_user();
			if(!$user){
				$user = clone $contact;
				$user->email = $message->email;
				$user->firstname = '';
				$user->lastname = '';
			}

			email_to_user($user, $contact, $message->subject, html_to_text($message->body), $message->body);

			$message->timesent = time();
			$DB->update_record('certif_messages_log', $message);
		}
	}
}