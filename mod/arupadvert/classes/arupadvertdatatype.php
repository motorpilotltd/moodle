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
 * The arupadvertdatatype base.
 *
 * @package    mod_arupadvert
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_arupadvert;

defined('MOODLE_INTERNAL') || die();

/**
 * Base class for arupadvertdatatype_*.
 *
 * @since      Moodle 3.0
 * @package    mod_arupadvert
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
abstract class arupadvertdatatype {

    /** @var string $type */
    public $type;

    /** @var string $accredited */
    public $accredited;
    /** @var string $audience */
    public $audience;
    /** @var string $description */
    public $description;
    /** @var string $name */
    public $name;
    /** @var string $objectives */
    public $objectives;

    // Advert block data.
    /** @var string $by */
    public $by;
    /** @var string $code */
    public $code;
    /** @var string $imgurl */
    public $imgurl;
    /** @var string $keywords */
    public $keywords;
    /** @var string $level */
    public $level;
    /** @var string $region */
    public $region;

    /** @var stdClass $_arupadvert */
    protected $_arupadvert;
    /** @var stdClass $_cm */
    protected $_cm;
    /** @var array $_elements */
    protected $_elements = array();

    /**
     * Constructor.
     *
     * @param stdCLass|bool $arupadvert
     */
    public function __construct($arupadvert = false) {
        $this->_arupadvert = $arupadvert;
        if ($this->_arupadvert) {
            $this->_cm = get_coursemodule_from_instance('arupadvert', $this->_arupadvert->id);
            $this->_load_data();
        }
    }

    /**
     * Factory method for spawning child classes based on incoming datatype.
     *
     * @param string $datatype
     * @param stdCLass|bool $arupadvert
     * @return arupadvertdatatype
     */
    public static function factory($datatype, $arupadvert = false) {
        $classname = "\arupadvertdatatype_{$datatype}\arupadvertdatatype_{$datatype}";
        if (class_exists($classname)) {
            return new $classname($arupadvert);
        }
        return false;
    }

    /**
     * Load data.
     *
     * Override in child classes.
     */
    abstract protected function _load_data();

    /**
     * Form definition.
     *
     * Override in child classes.
     *
     * @param moodleform $mform
     * @param stdClass $current
     * @param context $context
     */
    abstract public function mod_form_definition($mform, $current, $context);

    /**
     * Form element removal.
     *
     * Override in child classes.
     *
     * @param moodleform $mform
     */
    abstract public function mod_form_remove_elements($mform);

    /**
     * Set form data.
     *
     * Override in child classes.
     *
     * @param array $defaultvalues
     */
    abstract public function mod_form_set_data(&$defaultvalues);

    /**
     * Form validation.
     *
     * Override in child classes.
     *
     * @param array $data
     * @param array $files
     */
    abstract public function mod_form_validation($data, $files);

    /**
     * Edit instance.
     *
     * Override in child classes.
     *
     * @param stdClass $data
     * @param moodleform $mform
     */
    abstract public function edit_instance($data, $mform);

    /**
     * Delete instance.
     *
     * Override in child classes.
     *
     * @param int $arupadvertid
     */
    abstract public function delete_instance($arupadvertid);

    /**
     * Get data for block.
     */
    public function get_advert_block() {
        global $CFG, $COURSE, $DB;

        $context = \context_module::instance($this->_cm->id);
        if ($context) {
            $fs = get_file_storage();
            $files = $fs->get_area_files($context->id, 'mod_arupadvert', 'blockimage');
            if ($files) {
                $file = array_pop($files);
                $this->imgurl = \moodle_url::make_pluginfile_url(
                        $file->get_contextid(),
                        $file->get_component(),
                        $file->get_filearea(),
                        null,
                        $file->get_filepath(),
                        $file->get_filename(),
                        false);
            }
        }

        if (isset($COURSE->category) && $COURSE->category) {
            $this->by = $DB->get_field('course_categories', 'name', array('id' => $COURSE->category));
        }

        if (isset($COURSE->id)) {
            require_once($CFG->dirroot.'/local/coursemetadata/lib.php');
            $coursemetadata = coursemetadata_course_record($COURSE->id);
            if (is_object($coursemetadata) && isset($coursemetadata->level) && $coursemetadata->level) {
                $this->level = preg_replace('/,(\S)/', ', $1', $coursemetadata->level);
            }

            $sql = <<<EOS
SELECT
    lrru.id, lrru.name
FROM
    {local_regions_reg} lrru
JOIN
    {local_regions_reg_cou} lrrc
    ON lrrc.regionid = lrru.id
WHERE
    lrrc.courseid = :courseid
EOS;
            $courseregions = $DB->get_records_sql_menu($sql, array('courseid' => $COURSE->id));
            if ($courseregions) {
                $this->region = implode(', ', $courseregions);
            } else {
                $this->region = get_string('global', 'local_regions');
            }
        }

        if ($this->keywords) {
            require_once($CFG->libdir.'/htmlpurifier/HTMLPurifier.safe-includes.php');

            $cachedir = $CFG->cachedir.'/htmlpurifier';
            check_dir_exists($cachedir);

            $config = \HTMLPurifier_Config::createDefault();
            $config->set('Cache.SerializerPath', $cachedir);
            $config->set('Cache.SerializerPermissions', $CFG->directorypermissions);
            $config->set('HTML.Allowed', 'p,a[href],table,thead,tbody,tfoot,tr,th,td,ul,ol,li,strong,b,em,i');
            $config->set('AutoFormat.RemoveEmpty', true);
            $purifier = new \HTMLPurifier($config);

            $this->keywords = $purifier->purify($this->keywords);
        }
    }

}
