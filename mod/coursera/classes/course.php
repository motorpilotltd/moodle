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

namespace mod_coursera;

use renderer_base;
use stdClass;

defined('MOODLE_INTERNAL') || die();
global $CFG;

require_once("$CFG->dirroot/completion/data_object.php");
class course extends \data_object implements \templatable {
    public $table = 'courseracourse';
    public $required_fields = ['id', 'title', 'contentid', 'description', 'languagecode', 'estimatedlearningtime',
            'promophoto'];

    public $title;
    public $contentid;
    public $description;
    public $languagecode;
    public $estimatedlearningtime;
    public $promophoto;

    /**
     * @param array $params
     * @return self
     */
    public static function fetch($params) {
        return self::fetch_helper('courseracourse', __CLASS__, $params);
    }

    /**
     * @param array $params
     * @return self[]
     */
    public static function fetch_all($params) {
        $ret = self::fetch_all_helper('courseracourse', __CLASS__, $params);
        if (!$ret) {
            return [];
        }
        return $ret;
    }

    public static function getcoursesselectoptions() {
        global $DB;

        $progid = get_config('mod_coursera', 'programid');

        return $DB->get_records_sql_menu('select cc.id, cc.title 
                                                from {courseracourse} cc 
                                                inner join {courseraprogramlink} cpl on cc.id = cpl.courseracourseid 
                                                where cpl.programid = :programid
                                                order by title', ['programid' => $progid]);
    }

    public static function savecourse($element) {
        $courseracourse = course::fetch(['contentid' => $element->contentId]);

        if (empty($courseracourse)) {
            $courseracourse = new course();
        }
        $courseracourse->title = $element->name;
        $courseracourse->contentid = $element->contentId;
        $courseracourse->description = $element->description;
        $courseracourse->languagecode = $element->languageCode;
        $courseracourse->estimatedlearningtime = isset($element->extraMetadata->definition->estimatedLearningTime) ?
                $element->extraMetadata->definition->estimatedLearningTime : 0;
        $courseracourse->promophoto =
                isset($element->extraMetadata->definition->promoPhoto) ? $element->extraMetadata->definition->promoPhoto : '';

        if (!isset($courseracourse->id)) {
            $courseracourse->insert();
        } else {
            $courseracourse->update();
        }
        return $courseracourse;
    }

    /**
     * Function to export the renderer data in a format that is suitable for a
     * mustache template. This means:
     * 1. No complex types - only stdClass, array, int, string, float, bool
     * 2. Any additional info that is required for the template is pre-calculated (e.g. capability checks).
     *
     * @param renderer_base $output Used to do a final render of any components that need to be rendered for export.
     * @return stdClass|array
     */
    public function export_for_template(\renderer_base $output) {
        global $DB;

        $retval = [];

        $instructors = instructor::fetch_all(['courseracourseid' => $this->id]);

        $retval['instructors'] = [];
        foreach ($instructors as $instructor) {
            $retval['instructors'][] = $instructor->export_for_template($output);
        }

        if (!empty($retval['instructors'])) {
            $retval['hasinstructors'] = true;
        }

        $partners = partner::fetch_all(['courseracourseid' => $this->id]);

        $retval['partners'] = [];
        foreach ($partners as $partner) {
            $retval['partners'][] = $partner->export_for_template($output);
        }

        if (!empty($retval['partners'])) {
            $retval['haspartners'] = true;
        }

        foreach ($this->required_fields as $fieldname) {
            $retval[$fieldname] = $this->$fieldname;
        }

        $retval['description'] = text_to_html($this->description);

        $retval['estimatedlearningtime'] = round($retval['estimatedlearningtime'] / HOURSECS);

        return $retval;
    }

    public static function get_languages() {
        global $DB;

        $options = $DB->get_records_sql_menu("SELECT languagecode as valone, languagecode as valtwo FROM {courseracourse} GROUP BY languagecode");
        foreach ($options as $language) {
            if (get_string_manager()->string_exists('lang_' . $language, 'local_reportbuilder')) {
                $options[$language] = get_string('lang_' . $language, 'local_reportbuilder');
            }
        }

        return $options;
    }
}