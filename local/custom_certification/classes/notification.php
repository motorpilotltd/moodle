<?php

namespace local_custom_certification;

class notification {

	const TYPE_SUCCESS = 'success';
	const TYPE_INFO = 'info';
	const TYPE_WARNIONG = 'warning';
	const TYPE_DANGER = 'danger';

	/**
	 * Create new HTML notification
	 *
	 * @param $message Message text
	 * @param $type Message typ, see class constants
	 */
	public static function add($message, $type)
	{
		global $SESSION;
		$SESSION->certificationmessage = new \stdClass();
		$SESSION->certificationmessage->message = $message;
		$SESSION->certificationmessage->type = $type;
	}

	/**
	 * Check if there is message to show
	 * @return bool
	 */
	public static function exists()
	{
		global $SESSION;
		if(isset($SESSION->certificationmessage->message) && !empty($SESSION->certificationmessage->message)){
			return true;
		}
		return false;
	}

	/**
	 * Get HTML for message
	 * @return string
	 */
	public static function get()
	{
		global $SESSION;
		$html = '';
		if(self::exists()){
			$html = \html_writer::div($SESSION->certificationmessage->message, 'alert alert-'.$SESSION->certificationmessage->type);
			self::clear();
		}
		return $html;
	}

	/**
	 * Remove message from session
	 */
	public static function clear()
	{
		global $SESSION;
		unset($SESSION->certificationmessage);
	}

}