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
 * Class for exporting a course summary from an stdClass.
 *
 * @package    local_catalogue
 * @copyright  2019 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_catalogue\external;
defined('MOODLE_INTERNAL') || die();

use renderer_base;
use moodle_url;
require_once("$CFG->libdir/coursecatlib.php");

/**
 * Class for exporting a course summary from an stdClass.
 *
 * @copyright  2019 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class catalogue_course_exporter extends \core\external\exporter {

    const METHODOLOGY_CLASSROOM = 10;
    const METHODOLOGY_ELEARNING = 20;
    const METHODOLOGY_LEARNINGBURST = 40;
    const METHODOLOGY_PROGRAMMES = 50;
    const METHODOLOGY_OTHER = 60;

    protected static function define_related() {
        // We cache the context so it does not need to be retrieved from the course.
        return array('context' => 'context');
    }

    protected function get_other_values(renderer_base $output) {
        global $CFG, $PAGE;

        $coursecategory = \coursecat::get($this->data->category, MUST_EXIST, true);

        $arupboost = new \theme_arupboost\arupboost();

        $courseimage = $arupboost->arupboostimage_url($this->data->contextid, 'theme_arupboost', 'course_cropped');
        if (! $courseimage) {
            $courseimage = $arupboost->arupboostimage_url($this->data->contextid, 'theme_arupboost', 'course');
        };

        if ($courseimage) {
            $courseimage = $courseimage->out();
        }

        return array(
            'fullnamedisplay' => get_course_display_name_for_list($this->data),
            'viewurl' => (new moodle_url('/course/view.php', array('id' => $this->data->id)))->out(false),
            'courseimage' => $courseimage,
            'icon' => $this->getmethodologyadverticon($this->data->methodology),
            'advertclass' => $this->getmethodologyname($this->data->methodology, false),
            'methodologyname' => $this->getmethodologyname($this->data->methodology, true),
            'coursecategory' => $coursecategory->name
        );
    }

    private function getmethodologyadverticon($methodology) {
        global $OUTPUT;
        $icons = [
                self::METHODOLOGY_CLASSROOM     => $OUTPUT->image_url('method/method-classroom', 'local_coursemetadata'),
                self::METHODOLOGY_ELEARNING     => $OUTPUT->image_url('method/method-elearning', 'local_coursemetadata'),
                self::METHODOLOGY_LEARNINGBURST => $OUTPUT->image_url('method/method-learningburst', 'local_coursemetadata'),
                self::METHODOLOGY_PROGRAMMES    => $OUTPUT->image_url('method/method-masters', 'local_coursemetadata'),
                self::METHODOLOGY_OTHER         => $OUTPUT->image_url('method/method-other', 'local_coursemetadata')
        ];
        if (array_key_exists($methodology, $icons)) {
            return $icons[$methodology]->out();
        }
        return '';
    }

    private function getmethodologyname($methodology, $tostring = true) {
        switch ($methodology) {
            case self::METHODOLOGY_CLASSROOM:
                $identifier = 'methodology_classroom';
                break;
            case self::METHODOLOGY_PROGRAMMES:
                $identifier = 'methodology_programmes';
                break;
            case self::METHODOLOGY_ELEARNING:
                $identifier = 'methodology_elearning';
                break;
            case self::METHODOLOGY_LEARNINGBURST:
                $identifier = 'methodology_learningburst';
                break;
            default:
                return false;
                break;
        }
        if ($tostring) {
            return get_string($identifier, 'coursemetadatafield_arup');
        } else {
            return $identifier;
        }
        return $identifier;
    }

    public static function define_properties() {
        return array(
            'id' => array(
                'type' => PARAM_INT,
            ),
            'fullname' => array(
                'type' => PARAM_TEXT,
            ),
            'shortname' => array(
                'type' => PARAM_TEXT,
            ),
            'idnumber' => array(
                'type' => PARAM_RAW,
            ),
            'summary' => array(
                'type' => PARAM_RAW,
                'null' => NULL_ALLOWED
            ),
            'summaryformat' => array(
                'type' => PARAM_INT,
            ),
            'startdate' => array(
                'type' => PARAM_INT,
            ),
            'enddate' => array(
                'type' => PARAM_INT,
            )
        );
    }

    /**
     * Get the formatting parameters for the summary.
     *
     * @return array
     */
    protected function get_format_parameters_for_summary() {
        return [
            'component' => 'course',
            'filearea' => 'summary',
        ];
    }

    public static function define_other_properties() {
        return array(
            'fullnamedisplay' => array(
                'type' => PARAM_TEXT,
            ),
            'viewurl' => array(
                'type' => PARAM_URL,
            ),
            'courseimage' => array(
                'type' => PARAM_RAW,
            ),
            'icon' => array(
                'type' => PARAM_RAW,
            ),
            'methodologyname' => array(
                'type' => PARAM_RAW,
            ),
            'advertclass' => array(
                'type' => PARAM_RAW,
            ),
            'timeaccess' => array(
                'type' => PARAM_INT,
                'optional' => true
            ),
            'coursecategory' => array(
                'type' => PARAM_TEXT
            )
        );
    }
}
