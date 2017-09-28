<?php

/**
 * The Learning Path created event.
 *
 * @package     local_wa_learning_path
 * @author      Łukasz Juchnik <lukasz.juchnik@webanywhere.co.uk>
 * @author      Bartosz Hornik <bartosz.hornik@webanywhere.co.uk>
 * @copyright   2016 Webanywhere (http://www.webanywhere.co.uk)
 */
namespace local_wa_learning_path\event;
defined('MOODLE_INTERNAL') || die();
/**
 * The Learning Path created event class.
 *
 * @package     local_wa_learning_path
 * @author      Łukasz Juchnik <lukasz.juchnik@webanywhere.co.uk>
 * @author      Bartosz Hornik <bartosz.hornik@webanywhere.co.uk>
 * @copyright   2016 Webanywhere (http://www.webanywhere.co.uk)
 **/

class learning_path_created extends \core\event\base {
    protected function init() {
        $this->data['crud'] = 'c'; // c(reate), r(ead), u(pdate), d(elete)
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'wa_learning_path';
    }
 
    public static function get_name() {
        return get_string('event_learning_path_created', 'local_wa_learning_path');
    }
 
    public function get_description() {
        return "The user with id {$this->userid} created Learning Path with id {$this->objectid}.";
    }
 
    public function get_url() {
        return new \moodle_url('/local/wa_learning_path/', array('c' => 'admin', 'a' => 'edit', 'id' => $this->objectid));
    }
 
    protected function get_legacy_eventdata() {
        // Override if you migrating events_trigger() call.
        $data = new \stdClass();
        $data->id = $this->objectid;
        $data->userid = $this->relateduserid;
        return $data;
    }
}
