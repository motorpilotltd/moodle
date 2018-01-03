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
    public $required_fields = ['id', 'remotecourseid', 'title', 'description', 'lyndadatahash', 'thumbnail'];
    public $optional_fields = ['deletedbylynda' => false];

    public $remotecourseid;
    public $title;
    public $description;
    public $lyndadatahash;
    public $lyndatags;
    public $deletedbylynda;
    public $thumbnail;

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

    /**
     * @return self[]
     */
    public static function search($keyword, $regionid, $page, $perpage) {
        return self::handlesearch(false, $keyword, $regionid, $page, $perpage);
    }

    /**
     * @return int
     */
    public static function searchcount($keyword, $regionid) {
        return self::handlesearch(true, $keyword, $regionid, 0, 0);
    }

    /**
     * @return self[]|int
     */
    private static function handlesearch($countonly, $keyword, $regionid, $page, $perpage) {
        if (empty($keyword)) {
            return false;
        }

        global $DB;
        $wheresql = '';
        $params = [];

        if ($keyword != ' ') {
            $wheresql .= $DB->sql_like('title', ':keyword', false, false);
            $params['keyword'] = '%' . $DB->sql_like_escape($keyword) . '%';
        }

        if (!empty($wheresql)) {
            $wheresql = 'WHERE ' . $wheresql;
        }

        $sql = "FROM {local_lynda_course} llc
            INNER JOIN {local_lynda_courseregions} llcr ON llcr.regionid = :regionid AND llcr.lyndacourseid = llc.id
            $wheresql";

        $params['regionid'] = $regionid;

        if ($countonly) {
            $fields = 'SELECT COUNT(1) ';
            return $DB->count_records_sql($fields . $sql, $params);
        } else {
            $fields = 'SELECT * ';
            if ($datas = $DB->get_records_sql($fields . $sql . ' ORDER BY title ASC', $params, $page * $perpage, $perpage)) {

                $result = array();
                foreach ($datas as $data) {
                    $instance = new lyndacourse();
                    self::set_properties($instance, $data);
                    $result[$instance->id] = $instance;
                }
                return $result;

            } else {

                return [];
            }
        }
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

    public function setregionstate($region, $state) {
        global $DB;

        if (!$state) {
            $DB->delete_records('local_lynda_courseregions', ['lyndacourseid' => $this->id, 'regionid' => $region]);
        } else {
            $DB->insert_record('local_lynda_courseregions', (object) ['lyndacourseid' => $this->id, 'regionid' => $region]);
        }
    }

    private function updatetags() {
        global $DB;

        $tagrecords = [];
        foreach ($this->lyndatags as $tag) {
            $tagrecords[] = (object) ['remotetagid' => $tag, 'remotecourseid' => $this->remotecourseid];
        }
        $DB->delete_records_select('local_lynda_coursetags', 'remotecourseid = :remotecourseid',
                ['remotecourseid' => $this->remotecourseid]);
        $DB->insert_records('local_lynda_coursetags', $tagrecords);
    }
}