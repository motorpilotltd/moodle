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

class lyndatagtype extends \data_object {
    public $table = 'local_lynda_course';
    public $required_fields = ['id', 'remotetypeid', 'name'];
    public $optional_fields = [];

    public $remotetypeid;
    public $name;
    /**
     * @var lyndatag[]
     */
    public $tags = [];

    public function gettagtypeselectname() {
        return 'tagtype_' . $this->remotetypeid;
    }

    /**
     * @param array $params
     * @return self
     */
    public static function fetch($params) {
        return self::fetch_helper('local_lynda_tagtypes', __CLASS__, $params);
    }

    /**
     * @param array $params
     * @return self[]
     */
    public static function fetch_all($params) {
        $ret = self::fetch_all_helper('local_lynda_tagtypes', __CLASS__, $params);
        if (!$ret) {
            return [];
        }
        return $ret;
    }

    /**
     * @return lyndatagtype[]
     */
    public static function fetch_full_taxonomy() {
        $cache = \cache::make('local_lynda', 'full_taxonomy');

        $full_tag_graph = $cache->get(1);

        if ($full_tag_graph === false) {
            $tagtypes = self::fetch_all([]);
            $tags = lyndatag::fetch_all([]);

            $full_tag_graph = [];
            foreach ($tagtypes as $tagtype) {
                if ($tagtype->name == "Unknown") { // Ignore legacy tag categories.
                    continue;
                }

                $full_tag_graph[$tagtype->remotetypeid] = $tagtype;
            }

            foreach ($tags as $tag) {
                if (!isset($full_tag_graph[$tag->remotetypeid])) {
                    continue;
                }
                $full_tag_graph[$tag->remotetypeid]->tags[$tag->remotetagid] = $tag;
            }

            foreach ($full_tag_graph as $tag) {
                uasort($tag->tags, function($a, $b) use ($tag) {
                    return strcmp($a->name, $b->name);
                });
            }

            if (isset($tag)) {
                uasort($full_tag_graph, function($a, $b) use ($tag) {
                    return strcmp($tag->name, $tag->name);
                });
            }

            $cache->set(1, $full_tag_graph);
        }

        return $full_tag_graph;
    }
}