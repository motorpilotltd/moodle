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
    public $required_fields = ['id', 'remotecourseid', 'title', 'description', 'lyndadatahash', 'thumbnail', 'durationinseconds'];
    public $optional_fields = ['deletedbylynda' => false];

    public $remotecourseid;
    public $title;
    public $description;
    public $lyndadatahash;
    public $lyndatags;
    public $deletedbylynda;
    public $thumbnail;
    public $durationinseconds;

    /*
     * @return self
     */
    public static function fetchbyremotecourseid($remotecourseid, $skipcache = false) {
        if ($skipcache) {
            return self::fetch(['remotecourseid' => $remotecourseid]);
        }

        $cache = \cache::make('local_lynda', 'lyndacourses');
        $course = $cache->get($remotecourseid);
        if ($course === false) {
            $course = self::fetch(['remotecourseid' => $remotecourseid]);
            $cache->set($remotecourseid, $course);
        }

        return $course;
    }

    /*
     * @return self[]
     */
    public static function fetchbyremotecourseids($remotecourseids) {
        global $DB;

        list($sql, $params) = $DB->get_in_or_equal($remotecourseids);
        $datas = $DB->get_records_sql("SELECT * FROM {local_lynda_course} WHERE remotecourseid $sql", $params);

        $results = [];
        foreach($datas as $data) {
            $instance = new lyndacourse();
            self::set_properties($instance, $data);
            $results[$instance->remotecourseid] = $instance;
        }

        return $results;
    }

    /*
     * @return self[]
     */
    public static function fetchbyids($ids) {
        global $DB;

        list($sql, $params) = $DB->get_in_or_equal($ids);
        $datas = $DB->get_records_sql("SELECT * FROM {local_lynda_course} WHERE id $sql", $params);

        $results = [];
        foreach($datas as $data) {
            $instance = new lyndacourse();
            self::set_properties($instance, $data);
            $results[$instance->remotecourseid] = $instance;
        }

        return $results;
    }

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
            if ($countonly) {
                return 0;
            } else {
                return [];
            }
        }

        global $DB;
        $wheresql = 'WHERE lti.id IS NULL';
        $params = [];

        if ($keyword != ' ') {
            $wheresql .= " AND " . $DB->sql_like('title', ':keyword', false, false);
            $params['keyword'] = '%' . $DB->sql_like_escape($keyword) . '%';
        }

        $url = $DB->sql_concat_join("''",["'https://www.lynda.com/portal/lti/course/'", 'remotecourseid']);
        $sql = "FROM {local_lynda_course} llc
            INNER JOIN {local_lynda_courseregions} llcr ON llcr.regionid = :regionid AND llcr.lyndacourseid = llc.id
            LEFT JOIN {lti} lti ON lti.toolurl = $url
            $wheresql";

        $params['regionid'] = $regionid;

        $obj = new self();
        $allfields = $obj->required_fields + array_keys($obj->optional_fields);
        $fieldlist = [];
        foreach ($allfields as $field) {
            $fieldlist[] = "llc.$field";
        }
        $fieldlist = implode(',', $fieldlist);

        if ($countonly) {
            $fields = 'SELECT COUNT(1) ';
            return $DB->count_records_sql($fields . $sql . 'GROUP BY ' . $fieldlist, $params);
        } else {
            $fields = 'SELECT ' . $fieldlist . ' ';
            if ($datas = $DB->get_records_sql($fields . $sql . 'GROUP BY ' . $fieldlist . ' ORDER BY title ASC', $params, $page * $perpage, $perpage)) {

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