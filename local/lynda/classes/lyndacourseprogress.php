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

class lyndacourseprogress extends \data_object {
    public $table = 'local_lynda_progress';
    public $required_fields = ['id', 'userid', 'remotecourseid', 'lastviewed', 'percentcomplete', 'thumbnail', 'regionid'];
    public $optional_fields = ['deletedbylynda' => false];

    public $userid;
    public $remotecourseid;
    public $lastviewed;
    public $percentcomplete;
    public $regionid;

    /**
     * @var lyndacourse
     */
    public $lyndacourse;

    /**
     * @param array $params
     * @return self
     */
    public static function fetch($params) {
        return self::fetch_helper('local_lynda_progress', __CLASS__, $params);
    }

    /**
     * @param array $params
     * @return self[]
     */
    public static function fetch_all($params) {
        $ret = self::fetch_all_helper('local_lynda_progress', __CLASS__, $params);
        if (!$ret) {
            return [];
        }
        self::load_courses($ret);
        return $ret;
    }

    /**
     * @param lyndacourseprogress[] $courseprogresses
     */
    private static function load_courses($courseprogresses) {
        $remotecourseids = [];
        foreach ($courseprogresses as $courseprogress) {
            $remotecourseids[] = $courseprogress->remotecourseid;
        }

        $courses = lyndacourse::fetchbyremotecourseids($remotecourseids);

        foreach ($courseprogresses as $courseprogress) {
            if (!isset($courses[$courseprogress->remotecourseid])) {
                continue;
            }
            $courseprogress->lyndacourse = $courses[$courseprogress->remotecourseid];
        }
    }
}