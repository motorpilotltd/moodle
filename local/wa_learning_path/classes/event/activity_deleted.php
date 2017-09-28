<?php

/**
 * The Activity deleted event.
 *
 * @package     local_wa_learning_path
 * @author      Łukasz Juchnik <lukasz.juchnik@webanywhere.co.uk>
 * @author      Bartosz Hornik <bartosz.hornik@webanywhere.co.uk>
 * @copyright   2016 Webanywhere (http://www.webanywhere.co.uk)
 */
namespace local_wa_learning_path\event;
defined('MOODLE_INTERNAL') || die();
/**
 * The Activity deleted event class.
 *
 * @package     local_wa_learning_path
 * @author      Łukasz Juchnik <lukasz.juchnik@webanywhere.co.uk>
 * @author      Bartosz Hornik <bartosz.hornik@webanywhere.co.uk>
 * @copyright   2016 Webanywhere (http://www.webanywhere.co.uk)
 **/

class activity_deleted extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'd'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'wa_learning_path_activity';
    }
 
    public static function get_name() {
        return get_string('event_activity_deleted', 'local_wa_learning_path');
    }
 
    public function get_description() {
        return "The user with id {$this->userid} deleted Activity with id {$this->objectid}.";
    }
 
    public function get_url() {
        return new \moodle_url('/local/wa_learning_path/', array('c' => 'activity', 'a' => 'delete', 'id' => $this->objectid));
    }
 
    protected function get_legacy_eventdata() {
        // Override if you migrating events_trigger() call.
        $data = new \stdClass();
        $data->id = $this->objectid;
        $data->userid = $this->relateduserid;
        return $data;
    }
}
