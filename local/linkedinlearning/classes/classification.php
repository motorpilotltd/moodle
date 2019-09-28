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
 * @package    local_linkedinlearning
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_linkedinlearning;

defined('MOODLE_INTERNAL') || die();
global $CFG;
require_once($CFG->dirroot . '/completion/data_object.php');

class classification extends \data_object {
    public $table = 'linkedinlearning_class';
    public $required_fields = ['id', 'urn', 'name', 'type'];
    public $optional_fields = [];

    public $urn;
    public $name;
    public $type;

    /**
     * @param array $params
     * @return self
     */
    public static function fetch($params) {
        return self::fetch_helper('linkedinlearning_class', __CLASS__, $params);
    }

    public static function fetch_by_course($courseid) {
        global $DB;

        $sql = "SELECT lc.*
                FROM {linkedinlearning_class} lc 
                INNER JOIN {linkedinlearning_crs_class} lcc on lcc.classificationid = lc.id
                WHERE lcc.linkedinlearningcourseid = :linkedinlearningcourseid";
        $datas = $DB->get_records_sql($sql, ['linkedinlearningcourseid' => $courseid]);

        $result = array();
        foreach($datas as $data) {
            $instance = new classification();
            self::set_properties($instance, $data);
            $result[$instance->id] = $instance;
        }
        return $result;
    }

    /**
     * @param array $params
     * @return self[]
     */
    public static function fetch_all($params) {
        $ret = self::fetch_all_helper('linkedinlearning_class', __CLASS__, $params);
        if (!$ret) {
            return [];
        }
        return $ret;
    }

    /**
     * @param array $params
     * @return self[]
     */
    public static function fetch_all_key_on_urn($params) {
        $ret = self::fetch_all_helper('linkedinlearning_class', __CLASS__, $params);
        if (!$ret) {
            return [];
        }
        $keyed = [];
        foreach ($ret as $val) {
            $keyed[$val->urn] = $val;
        }
        return $keyed;
    }

    public static function get_types() {
        $tags = self::fetch_all([]);

        $raw = [];
        foreach ($tags as $tag) {
            if (!isset($raw[$tag->type])) {
                $raw[$tag->type] = ['name' => $tag->type, 'tags' => []];
            }
            $raw[$tag->type]['tags'][] = $tag;
        }

        $retval = [];
        foreach ($raw as $key => $value) {
            $retval[preg_replace("/[^A-Za-z0-9 ]/", '', $key)] = $value;
        }

        return $retval;
    }
}