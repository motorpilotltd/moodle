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
 * coursemetadatafield_arup field.
 *
 * @package    coursemetadatafield_arup
 * @copyright  Andrew Hancox <andrewdchancox@googlemail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace coursemetadatafield_arup;

class arupmetadata extends \data_object implements \renderable, \templatable {
    public $table = 'coursemetadata_arup';
    public $required_fields = [
            'id', 'course'
    ];

    public $optional_fields = ['name'              => '', 'altword' => '', 'showheadings' => true, 'description' => '',
                               'descriptionformat' => FORMAT_HTML, 'objectives' => '', 'objectivesformat' => FORMAT_HTML,
                               'audience'          => '', 'audienceformat' => FORMAT_HTML, 'keywords' => '',
                               'keywordsformat'    => FORMAT_HTML,
                               'accredited'        => false, 'accreditationdate' => null, 'timecreated' => 0, 'timemodified' => 0,
                               'duration'          => null, 'durationunits' => null,
                               'display'           => true
    ];

    public $course;
    public $display;
    public $name;
    public $altword;
    public $showheadings;
    public $description;
    public $descriptionformat;
    public $objectives;
    public $objectivesformat;
    public $audience;
    public $audienceformat;
    public $keywords;
    public $keywordsformat;
    public $accredited;
    public $accreditationdate;
    public $timecreated;
    public $timemodified;
    public $duration;
    public $durationunits;

    public function export_for_template(\renderer_base $output) {
        global $DB;

        $data = (array) $this;
        $data = (object) $data;

        $data->courseimage = $this->get_image_url();

        $data->description = format_text($this->description, $this->descriptionformat);
        $data->objectives = format_text($this->objectives, $this->objectivesformat);
        $data->audience = format_text($this->audience, $this->audienceformat);
        $data->keywords = format_text($this->keywords, $this->keywordsformat);
        $data->accreditationdate = userdate($this->accreditationdate);
        $data->timecreated = userdate($this->timecreated);
        $data->timemodified = userdate($this->timemodified);

        if ($this->accredited) {
            $data->logo = $output->image_url('icon', 'coursemetadatafield_arup');
        } else {
            $data->logo = '';
        }

        $sections = array('description', 'objectives', 'audience');
        $data->hassections = false;
        $data->sections = [];

        foreach ($sections as $section) {
            $sec = new \stdClass();
            $content = $data->{$section};

            if ($content) {
                $altword = empty($this->altword) ? \core_text::strtolower(get_string('course')) : $this->altword;
                $sec->heading = get_string($section, 'coursemetadatafield_arup', $altword);
                $sec->text = $content;
                $data->sections[] = $sec;
                $data->hassections = true;
            }
        }

        $elements = [
                'category' => $this->get_categoryname(),
                'level' => $this->get_level(),
                'code'     => format_text($this->get_course()->summary, $this->get_course()->summaryformat),
                'region' => $this->get_region(),
                'keywords' => $data->keywords
        ];


        foreach ($elements as $element => $value) {
            $be = new \stdClass();
            $be->heading = get_string("block:{$element}", 'coursemetadatafield_arup');
            $be->text = $value;
            if ($be->text) {
                $data->blockelements[] = $be;
            }
        }

        return $data;
    }

    private $courseobject = null;
    public function get_course() {
        if (!isset($this->courseobject)) {
            $this->courseobject = get_course($this->course);
        }
        return $this->courseobject;
    }

    public function get_categoryname() {
        global $DB;

        return $DB->get_field('course_categories', 'name', array('id' => $this->get_course()->category));
    }

    public function getallfields() {
        return array_merge($this->required_fields, array_keys($this->optional_fields));
    }

    public static function fetch($params) {
        return self::fetch_helper('coursemetadata_arup', __CLASS__, $params);
    }

    public static function fetch_all($params, $sort = false) {
        $ret = self::fetch_all_helper('coursemetadata_arup', __CLASS__, $params);
        if (!$ret) {
            $ret = [];
        }
        return $ret;
    }

    public function save() {
        if ($this->id) {
            $this->update();
        } else {
            $this->insert();
        }
    }

    public function get_image_url() {
        $context = \context_course::instance($this->course);
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'coursemetadatafield_arup', 'blockimage');

        if ($files) {
            $file = array_pop($files);
            return \moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    null,
                    $file->get_filepath(),
                    $file->get_filename(),
                    false);
        } else {
            return false;
        }
    }

    public function get_region() {
        global $DB;

        $sql = "SELECT
                    lrru.id, lrru.name
                FROM
                    {local_regions_reg} lrru
                INNER JOIN
                    {local_regions_reg_cou} lrrc
                    ON lrrc.regionid = lrru.id
                WHERE
                    lrrc.courseid = :courseid";

        $courseregions = $DB->get_records_sql_menu($sql, array('courseid' => $this->course));
        if ($courseregions) {
            return implode(', ', $courseregions);
        } else {
            return get_string('global', 'local_regions');
        }
    }

    public function get_level() {
        global $CFG;

        require_once($CFG->dirroot.'/local/coursemetadata/lib.php');
        $coursemetadata = coursemetadata_course_record($this->course);
        if (is_object($coursemetadata) && isset($coursemetadata->level) && $coursemetadata->level) {
            return preg_replace('/,(\S)/', ', $1', $coursemetadata->level);
        } else {
            return false;
        }
    }
}