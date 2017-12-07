<?php
/**
 * @package    local_lynda
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_lynda;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/completion/data_object.php');

class lyndacourse extends \data_object {
    public $table = 'local_lynda_course';
    public $required_fields = ['id', 'remotecourseid', 'title', 'description', 'lyndadatahash'];
    public $optional_fields = ['deletedbylynda' => false];

    public $remotecourseid;
    public $title;
    public $description;
    public $lyndadatahash;
    public $lyndatags;
    public $deletedbylynda;

    /**
     * @param array $params
     * @return self
     */
    public static function fetch($params) {
        return self::fetch_helper('local_lynda_course', __CLASS__, $params);
    }

    /**
     * @param array $params
     * @return self[]
     */
    public static function fetch_all($params) {
        $ret = self::fetch_all_helper('local_lynda_course', __CLASS__, $params);
        if (!$ret) {
            return [];
        }
        return $ret;
    }

    public function update() {
        global $DB;

        if (empty($this->id)) {
            $this->id = $DB->get_field('local_lynda_course', 'id',
                    ['remotecourseid' => $this->remotecourseid]);
        }

        parent::update();
        $this->updatetags();
    }

    public function insert() {
        parent::insert();
        $this->updatetags();
        return $this->id;
    }

    private function updatetags() {
        global $DB;

        $tagrecords = [];
        foreach ($this->lyndatags as $tag) {
            $tagrecords[] = (object)['remotetagid' => $tag, 'remotecourseid' => $this->remotecourseid];
        }
        $DB->delete_records_select('local_lynda_coursetags', 'remotecourseid = :remotecourseid', ['remotecourseid' => $this->remotecourseid]);
        $DB->insert_records('local_lynda_coursetags', $tagrecords);
    }
}