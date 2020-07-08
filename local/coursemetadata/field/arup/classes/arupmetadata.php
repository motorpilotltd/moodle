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

use core_user\external\user_summary_exporter;
use moodle_url;

class arupmetadata extends \data_object implements \renderable, \templatable {
    public $table = 'coursemetadata_arup';
    public $required_fields = [
            'id', 'course'
    ];

    public $optional_fields = ['name'              => '',
                               'altword' => '',
                               'showheadings' => true,
                               'description' => '',
                               'descriptionformat' => FORMAT_HTML, 'objectives' => '', 'objectivesformat' => FORMAT_HTML,
                               'audience'          => '', 'audienceformat' => FORMAT_HTML, 'keywords' => '',
                               'keywordsformat'    => FORMAT_HTML,
                               'accredited'        => false, 'accreditationdate' => null, 'timecreated' => 0, 'timemodified' => 0,
                               'duration'          => null, 'durationunits' => null,
                               'display'           => true,
                               'methodology'       => self::METHODOLOGY_CLASSROOM,
                                'thirdpartyreference' => ''
    ];

    const METHODOLOGY_CLASSROOM = 10; //Enrolments only accept Classroom Classes/
    const METHODOLOGY_ELEARNING = 20; // - Enrolments only accept Elearning Classes
    const METHODOLOGY_LINKEDINLEARNING = 25; // - Enrolments only accept Elearning Classes
    const METHODOLOGY_LEARNINGBURST = 40; // same as Elearning
    const METHODOLOGY_PROGRAMMES = 50; //Enrolments only accept Classroom Classes
    const METHODOLOGY_OTHER = 60; //No enrolment plugin required on setup. But user can add and choose class or elearn or a blended option of both.

    public $course;
    public $display;
    public $name; // NOT ACTUALLY USED BUT RETAINED IN CASE WE NEED IT.
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
    public $methodology;
    public $thirdpartyreference;

    private function get_role_id() {
        $cache = \cache::make('coursemetadatafield_arup', 'roleid');
        $roleid = $cache->get('roleid');
        if (!$roleid) {
            global $DB;

            $roleid = $DB->get_field('coursemetadata_info_field', 'param1', ['datatype' => 'arup']);
            $cache->set('roleid', $roleid);
        }
        return $roleid;
    }

    public function export_for_template(\renderer_base $output) {
        global $CFG, $OUTPUT, $DB, $PAGE;

        if (! $PAGE->user_is_editing()) {
            $cache = \cache::make('coursemetadatafield_arup', 'renderable');
            if ($export = $cache->get($this->get_course()->id)) {
                return $export;
            }
        }

        $data = new \stdClass();

        $data->showheadings = $this->showheadings;

        if ($this->accredited) {
            $logourl = $OUTPUT->image_url('logo', 'coursemetadatafield_arup');
        } else {
            $logourl = '';
        }
        $data->name = $this->get_course()->fullname;
        $data->logo = $logourl;

        $sections = array('description', 'objectives', 'audience');
        $data->hassections = false;
        foreach ($sections as $section) {
            $sec = new \stdClass();
            $content = $this->{$section};
            if ($content) {
                $altword = empty($this->altword) ? \core_text::strtolower(get_string('course')) : $this->altword;
                $sec->heading = get_string($section, 'coursemetadatafield_arup', $altword);
                $sec->text = format_text($content, $this->{$section . 'format'});
                $data->sections[] = $sec;
                $data->hassections = true;
            }
        }

        $elements = [
                'by'       => $this->get_categoryname(),
                'level'    => $this->get_level(),
                'code'     => $this->get_course()->shortname,
                'region'   => $this->get_region(),
                'keywords' => format_text($this->keywords, $this->keywordsformat)
        ];
        foreach ($elements as $element => $value) {
            $be = new \stdClass();
            $be->heading = get_string("block:{$element}", 'coursemetadatafield_arup');
            $be->text = $value;
            if ($be->text) {
                $data->blockelements[] = $be;
            }
        }

        $data->courseimage = $this->get_image_url();

        $data->editableimage = $this->get_editable_image();

        $data->canviewshare =
                has_capability('coursemetadatafield/arup:viewsharelink', \context_course::instance($this->get_course()->id));
        $data->description = json_encode(format_string($this->get_course()->summary));
        $data->description_text = format_string($this->get_course()->summary);
        $data->ogsharelink =
                urlencode(new \moodle_url("/mod/arupadvert/redirect.php", ['shortname' => $this->get_course()->shortname]));

        $map = $this->getmethodologymap();

        if (!isset($this->methodology) || !isset($map[$this->methodology])) {
            $data->methodology = '';
        } else {
            $data->methodology = $map[$this->methodology];
            $data->icon = self::getmethodologyadverticon()[$this->methodology];
            $data->advertclass = $this->getmethodologyname($this->methodology, false);
        }
        $data->duration = $this->formatduration();

        $roleid = $this->get_role_id();
        $data->trainers = [];
        if (!empty($roleid)) {
            $users = get_role_users($roleid, \context_course::instance($this->course), false, \user_picture::fields('u'));
            foreach ($users as $user) {
                $profileurl = new \moodle_url('/user/profile.php', ['id' => $user->id]);
                $trainer = new \stdClass();
                $trainer->id = $user->id;
                $trainer->fullname = fullname($user);
                $userpicture = new \user_picture($user);
                $trainer->imageurl = $userpicture->get_url($PAGE);
                $trainer->profileurl = $profileurl->out();
                $data->trainers[] = $trainer;
            }
        }
        $data->url = new moodle_url('/course/view.php', ['id' => $this->get_course()->id]);

        if (! $PAGE->user_is_editing()) {
            $cache->set($this->get_course()->id, $data);
        }
        return $data;
    }

    public function formatduration() {
        if (empty($this->duration)) {
            return '';
        }

        if ($this->durationunits === 'hours') {
            return \mod_tapsenrol\taps::duration_hours_display($this->duration, $this->durationunits);
        }

        return (float) $this->duration . ' ' . $this->durationunits;
    }

    private $courseobject = null;

    public static function getmethodologyadverticon() {
        global $OUTPUT;
        return [
                self::METHODOLOGY_CLASSROOM     => $OUTPUT->image_url('method/method-classroom', 'local_coursemetadata'),
                self::METHODOLOGY_ELEARNING     => $OUTPUT->image_url('method/method-elearning', 'local_coursemetadata'),
                self::METHODOLOGY_LEARNINGBURST => $OUTPUT->image_url('method/method-learningburst', 'local_coursemetadata'),
                self::METHODOLOGY_PROGRAMMES    => $OUTPUT->image_url('method/method-masters', 'local_coursemetadata'),
                self::METHODOLOGY_OTHER         => $OUTPUT->image_url('method/method-other', 'local_coursemetadata')
        ];
    }

    public static function getmethodologymap() {
        return [
                self::METHODOLOGY_CLASSROOM     => get_string('methodology_classroom', 'coursemetadatafield_arup'),
                self::METHODOLOGY_ELEARNING     => get_string('methodology_elearning', 'coursemetadatafield_arup'),
                self::METHODOLOGY_LEARNINGBURST => get_string('methodology_learningburst', 'coursemetadatafield_arup'),
                self::METHODOLOGY_PROGRAMMES    => get_string('methodology_programmes', 'coursemetadatafield_arup'),
                self::METHODOLOGY_LINKEDINLEARNING    => get_string('methodology_linkedinlearning', 'coursemetadatafield_arup'),
                self::METHODOLOGY_OTHER         => get_string('methodology_other', 'coursemetadatafield_arup')
        ];
    }

    public static function getmethodologyname($methodology, $tostring = true) {
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
            case self::METHODOLOGY_LINKEDINLEARNING:
                $identifier = 'methodology_linkedinlearning';
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
    }

    public static function getmethodologyicon($methodology) {
        global $OUTPUT;

        switch ($methodology) {
            case self::METHODOLOGY_CLASSROOM:
                $identifier = 'methodology_classroom';
                break;
            case self::METHODOLOGY_PROGRAMMES:
                $identifier = 'methodology_programmes';
                break;
            case self::METHODOLOGY_ELEARNING:
            case self::METHODOLOGY_LINKEDINLEARNING:
                $identifier = 'methodology_elearning';
                break;
            case self::METHODOLOGY_LEARNINGBURST:
                $identifier = 'methodology_learningburst';
                break;
            default:
                return false;
                break;
        }
        return $OUTPUT->pix_icon($identifier, get_string($identifier, 'coursemetadatafield_arup'), 'coursemetadatafield_arup');
    }

    public function classtypelocked() {
        return $this->methodology !== self::METHODOLOGY_OTHER;
    }

    public function get_default_class_type() {
        switch ($this->methodology) {
            case self::METHODOLOGY_CLASSROOM:
            case self::METHODOLOGY_PROGRAMMES:
                return \mod_tapsenrol\enrolclass::TYPE_CLASSROOM;
                break;
            case self::METHODOLOGY_ELEARNING:
            case self::METHODOLOGY_LINKEDINLEARNING:
            case self::METHODOLOGY_LEARNINGBURST:
                return \mod_tapsenrol\enrolclass::TYPE_ELEARNING;
                break;
            default:
                return \mod_tapsenrol\enrolclass::TYPE_CLASSROOM;
        }
    }

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

    /**
     * @param $params
     * @return \coursemetadatafield_arup\arupmetadata
     */
    public static function fetch($params) {
        return self::fetch_helper('coursemetadata_arup', __CLASS__, $params);
    }

    /**
     * @param array $params
     * @param bool $sort
     * @return \coursemetadatafield_arup\arupmetadata[]
     */
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

    public function get_editable_image() {
        global $OUTPUT;
        // the theme renderer will add resize
        $arupboost = new \theme_arupboost\arupboost();
        return $arupboost->arupboostimage('course', $this->get_course()->id,
            $this->get_image_url(), $OUTPUT->image_url('categoryimage', 'local_catalogue')->out());
    }

    public function get_image_url() {
        $arupboost = new \theme_arupboost\arupboost();
        $context = \context_course::instance($this->get_course()->id);

        $croppedimage = $arupboost->arupboostimage_url($context->id, 'theme_arupboost', 'course_cropped');
        if ($croppedimage) { return $croppedimage; };

        $originalimage = $arupboost->arupboostimage_url($context->id, 'theme_arupboost', 'course');
        if ($originalimage) { return $originalimage; };

        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'coursemetadatafield_arup', 'blockimage', 0);

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
        }
        return '';
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

        require_once($CFG->dirroot . '/local/coursemetadata/lib.php');
        $coursemetadata = coursemetadata_course_record($this->course);
        if (is_object($coursemetadata) && isset($coursemetadata->level) && $coursemetadata->level) {
            return preg_replace('/,(\S)/', ', $1', $coursemetadata->level);
        } else {
            return false;
        }
    }

    /**
     * Observer for \core\event\course_updated event. Clears metadata cache.
     *
     * @param \core\event\course_updated $event
     * @return void
     */
    public static function course_updated(\core\event\course_updated $event) {
        $course = $event->get_record_snapshot('course', $event->objectid);
        $cache = \cache::make('coursemetadatafield_arup', 'renderable');
        $cache->delete($course->id);
    }
}