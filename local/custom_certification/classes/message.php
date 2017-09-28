<?php

namespace local_custom_certification;

class message {
	/**
	 * Enrolment message type
	 */
	const TYPE_ENROLLMENT = 1;

	/**
	 * Unenrolment message type
	 */
	const TYPE_UNENROLLMENT = 2;

	/**
	 * Certificatoin / recertification completed
	 */
	const TYPE_CERTIFICATION_COMPLETED = 3;

	/**
	 * Recertification window opens message type
	 */
	const TYPE_RECERTIFICATION_WINDOW_OPEN = 4;

	/**
	 * Certiftication / Recertification expired (overdue) message type
	 */
	const TYPE_CERTIFICATION_EXPIRED = 5;

	/**
	 * Certification due message type
	 */
    const TYPE_CERTIFICATION_BEFORE_EXPIRATION = 6;

	/**
	 * Get all available types of messages
	 * 
	 * @return array key = message type, value = message type name
	 */
	public static function get_types(){
		$types = [];
		$types[self::TYPE_ENROLLMENT] = get_string('messagetypeenrollment', 'local_custom_certification');
		$types[self::TYPE_UNENROLLMENT] = get_string('messagetypeunenrollment', 'local_custom_certification');
		$types[self::TYPE_CERTIFICATION_EXPIRED] = get_string('messagetypecertificationexpired', 'local_custom_certification');
		$types[self::TYPE_CERTIFICATION_COMPLETED] = get_string('messagetypecertificationcompleted', 'local_custom_certification');
		$types[self::TYPE_RECERTIFICATION_WINDOW_OPEN] = get_string('messagetyperecertificationwindowopen', 'local_custom_certification');
		$types[self::TYPE_CERTIFICATION_BEFORE_EXPIRATION] = get_string('messagetyperecertificationbeforeexpiration', 'local_custom_certification');

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
	 * Add message to queque
	 * If 3party email is configured for this message instance, add message for each 3rd party email
	 * 
	 * @param $messageid Message ID
	 * @param $userid User id
	 */
	public static function add_message($messageid, $userid){
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
	 * @return array
	 */
	public static function get_messages_to_send($limit = 20){
		global $DB;
		return $DB->get_records('certif_messages_log', ['timesent' => 0], '', '*', 0, $limit);
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

			$message->body = nl2br($message->body);
			email_to_user($user, $contact, $message->subject, strip_tags($message->body), $message->body);

			$message->timesent = time();
			$DB->update_record('certif_messages_log', $message);
		}
	}
}