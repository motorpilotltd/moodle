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

class coursemetadata_field_arup extends \local_coursemetadata\field_base {
    private $arupmetadata = null;

    public function __construct($fieldid = 0, $courseid = 0) {
        parent::__construct($fieldid, $courseid);

        $this->arupmetadata = new \coursemetadatafield_arup\arupmetadata(['course' => $this->courseid]);
    }

    /**
     * Overwrite the base class to display the data for this field.
     */
    public function display_data() {
        // Default formatting.
        $data = parent::display_data();

        return $data;
    }

    /**
     * Add fields for editing a text coursemetadata field.
     *
     * @param moodleform $mform
     */
    public function edit_field_add($mform) {
        $altword =
                empty($this->arupmetadata->altword) ? \core_text::strtolower(get_string('course')) : $this->arupmetadata->altword;

        $mform->addElement('checkbox', 'arupmeta_display', get_string('display', 'coursemetadatafield_arup'));

        $mform->addElement('text', 'arupmeta_name', get_string('name', 'coursemetadatafield_arup'), ['maxlength' => 254]);
        $mform->setType('arupmeta_name', PARAM_TEXT);

        $filemanageroptions = array();
        $filemanageroptions['return_types'] = 3;
        $filemanageroptions['accepted_types'] = array('web_image');
        $filemanageroptions['maxbytes'] = 0;
        $filemanageroptions['maxfiles'] = 1;
        $filemanageroptions['mainfile'] = false;

        $mform->addElement('filemanager', 'advertblockimage', get_string('advertblockimage', 'coursemetadatafield_arup'), null,
                $filemanageroptions);
        $mform->addHelpButton('advertblockimage', 'advertblockimage', 'coursemetadatafield_arup');

        $mform->addElement('text', 'arupmeta_altword', get_string('altword', 'coursemetadatafield_arup', $altword),
                ['maxlength' => 254]);
        $mform->setType('arupmeta_altword', PARAM_TEXT);

        $mform->addElement('checkbox', 'arupmeta_showheadings', get_string('showheadings', 'coursemetadatafield_arup'));

        $mform->addElement('editor', 'arupmeta_description_editor',
                get_string('description', 'coursemetadatafield_arup', $altword));
        $mform->setType('arupmeta_description_editor', PARAM_RAW); // No XSS prevention here, users must be trusted.

        $mform->addElement('editor', 'arupmeta_objectives_editor', get_string('objectives', 'coursemetadatafield_arup', $altword));
        $mform->setType('arupmeta_objectives_editor', PARAM_RAW); // No XSS prevention here, users must be trusted.

        $mform->addElement('editor', 'arupmeta_audience_editor', get_string('audience', 'coursemetadatafield_arup', $altword));
        $mform->setType('arupmeta_audience_editor', PARAM_RAW); // No XSS prevention here, users must be trusted.

        $mform->addElement('editor', 'arupmeta_keywords_editor', get_string('keywords', 'coursemetadatafield_arup'));
        $mform->setType('arupmeta_keywords_editor', PARAM_RAW); // No XSS prevention here, users must be trusted.

        $mform->addElement('checkbox', 'arupmeta_accredited', get_string('accredited', 'coursemetadatafield_arup'));

        $mform->addElement('date_selector', 'arupmeta_accreditationdate',
                get_string('accreditationdate', 'coursemetadatafield_arup'));

        $mform->addElement('date_selector', 'arupmeta_timecreated', get_string('timecreated', 'coursemetadatafield_arup'));

        $mform->addElement('date_selector', 'arupmeta_timemodified', get_string('timemodified', 'coursemetadatafield_arup'));

        $mform->addElement('text', 'arupmeta_duration', get_string('duration', 'coursemetadatafield_arup'));
        $mform->setType('arupmeta_duration', PARAM_FLOAT);

        $durationunits =
                ['minutes' => get_string('minutes', 'coursemetadatafield_arup'),
                 'hours'   => get_string('hours', 'coursemetadatafield_arup'),
                 'days'    => get_string('days', 'coursemetadatafield_arup'),
                 'weeks'   => get_string('weeks', 'coursemetadatafield_arup'),
                 'months'  => get_string('months', 'coursemetadatafield_arup'),
                 'years'   => get_string('years', 'coursemetadatafield_arup')];
        $mform->addElement('select', 'arupmeta_durationunits', get_string('durationunits', 'coursemetadatafield_arup'),
                $durationunits);
    }

    private function editors() {
        return ['description', 'objectives', 'audience', 'keywords'];
    }

    public function edit_save_data($data) {
        foreach ($this->editors() as $editor) {
            if (isset($data->{'arupmeta_' . $editor . '_editor'})) {
                $data->{'arupmeta_' . $editor} = clean_text($data->{'arupmeta_' . $editor . '_editor'}['text'],
                        $data->{'arupmeta_' . $editor . '_editor'}['format']);
                $data->{'arupmeta_' . $editor . 'format'} = $data->{'arupmeta_' . $editor . '_editor'}['format'];
            }
        }

        if (!isset($data->arupmeta_showheadings)) {
            $data->arupmeta_showheadings = false;
        }

        if (!isset($data->arupmeta_accredited)) {
            $data->arupmeta_accredited = false;
        }

        if (!isset($data->arupmeta_display)) {
            $data->arupmeta_display = false;
        }

        $data->{$this->inputname} = true;
        $arupmetadata = new \coursemetadatafield_arup\arupmetadata(['course' => $this->courseid]);

        foreach ($arupmetadata->getallfields() as $field) {
            if (isset($data->{"arupmeta_" . $field})) {
                $arupmetadata->{$field} = $data->{"arupmeta_" . $field};
            }
        }

        $arupmetadata->save();

        $context = context_course::instance($this->courseid);
        $draftitemid = $data->advertblockimage;
        file_save_draft_area_files($draftitemid, $context->id, 'coursemetadatafield_arup', 'originalblockimage', 0);
        $fs = get_file_storage();

        $newimages = $fs->get_area_files($context->id, 'coursemetadatafield_arup', 'originalblockimage', false,
                'itemid, filepath, filename', false);
        $newimage = array_pop($newimages);
        if (!is_null($newimage) && $newimage->get_imageinfo()) {
            $this->arupadvert_process_blockimage($context->id, $newimage);
        } else {
            $fs->delete_area_files($context->id, 'coursemetadatafield_arup');
        }

        parent::edit_save_data($data);
    }

    /**
     * Process the uploaded image for display in the associated arupadvert block.
     *
     * @param int $contextid
     * @param stored_file $newimage
     * @return void
     */
    private function arupadvert_process_blockimage($contextid, $newimage) {
        global $CFG;
        require_once($CFG->libdir . '/gdlib.php');

        $fs = get_file_storage();
        // Clear existing files.
        $fs->delete_area_files($contextid, 'coursemetadatafield_arup', 'blockimage');

        $tempimage = $newimage->copy_content_to_temp();

        if (!is_file($tempimage)) {
            // Can't process, just save.
            $this->arupadvert_use_uploaded_blockimage($fs, $newimage, $tempimage, $contextid);
            return;
        }

        $imageinfo = getimagesize($tempimage);

        if (empty($imageinfo)) {
            // Can't process, just save.
            $this->arupadvert_use_uploaded_blockimage($fs, $newimage, $tempimage, $contextid);
            return;
        }

        $desiredaspectratio = 9 / 16; // Widescreen.

        $image = new stdClass();
        $image->width = $imageinfo[0];
        $image->height = $imageinfo[1];
        $image->aspectratio = $image->height / $image->width;
        $image->type = $imageinfo[2];

        $image->newwidth = 800;
        $image->heightstart = 0;
        $image->cropheight = $image->height;
        if ($image->aspectratio > $desiredaspectratio) { // Will need cropping.
            $image->newheight = $image->newwidth * $desiredaspectratio;
            $image->cropheight = round($image->width * $desiredaspectratio);
            $image->heightstart = round(($image->height - $image->cropheight) / 2);
        } else {
            $image->newheight = $image->newwidth * $image->aspectratio;
        }

        switch ($image->type) {
            case IMAGETYPE_GIF:
                if (function_exists('imagecreatefromgif')) {
                    $im = imagecreatefromgif($tempimage);
                } else {
                    $this->arupadvert_use_uploaded_blockimage($fs, $newimage, $tempimage, $contextid);
                    return;
                }
                break;
            case IMAGETYPE_JPEG:
                if (function_exists('imagecreatefromjpeg')) {
                    $im = imagecreatefromjpeg($tempimage);
                } else {
                    $this->arupadvert_use_uploaded_blockimage($fs, $newimage, $tempimage, $contextid);
                    return;
                }
                break;
            case IMAGETYPE_PNG:
                if (function_exists('imagecreatefrompng')) {
                    $im = imagecreatefrompng($tempimage);
                } else {
                    $this->arupadvert_use_uploaded_blockimage($fs, $newimage, $tempimage, $contextid);
                    return;
                }
                break;
            default:
                // Won't process, just save.
                $this->arupadvert_use_uploaded_blockimage($fs, $newimage, $tempimage, $contextid);
                return;
        }

        if (function_exists('imagejpeg')) {
            $imagefnc = 'imagejpeg';
            $imageext = '.jpg';
            $filters = null; // Not used.
            $quality = 60;
        } else {
            // Can't process, just save.
            $this->arupadvert_use_uploaded_blockimage($fs, $newimage, $tempimage, $contextid);
            return;
        }

        $im1 = imagecreatetruecolor($image->newwidth, $image->newheight);

        imagecopybicubic($im1, $im, 0, 0, 0, $image->heightstart, $image->newwidth, $image->newheight, $image->width,
                $image->cropheight);

        ob_start();
        if (!$imagefnc($im1, null, $quality, $filters)) {
            ob_end_clean();
            $this->arupadvert_use_uploaded_blockimage($fs, $newimage, $tempimage, $contextid);
            return;
        }
        $data = ob_get_clean();
        imagedestroy($im1);

        $filerecord = array(
                'filename'  => 'info_image' . $imageext,
                'contextid' => $contextid,
                'component' => 'coursemetadatafield_arup',
                'filearea'  => 'blockimage',
                'itemid'    => 0,
                'filepath'  => '/');
        $fs->create_file_from_string($filerecord, $data);

        @unlink($tempimage);
    }

    /**
     * Use originally uploaded image for display in the associated arupadvert block.
     *
     * @param file_storage $fs
     * @param stored_file $newimage
     * @param string|bool $tempimage
     * @param int $contextid
     * @return void
     */
    private function arupadvert_use_uploaded_blockimage($fs, $newimage, $tempimage, $contextid) {
        $filerecord = array('contextid' => $contextid, 'component' => 'coursemetadatafield_arup', 'filearea' => 'blockimage',
                            'itemid'    => 0, 'filepath' => '/');
        $fs->create_file_from_storedfile($filerecord, $newimage);
        if (is_file($tempimage)) {
            @unlink($tempimage);
        }
    }

    public function edit_field_set_default($mform) {
        if (!isset($this->arupmetadata->id)) {
            return;
        }

        $data = [];
        foreach ($this->arupmetadata->getallfields() as $field) {
            $data['arupmeta_' . $field] = $this->arupmetadata->$field;
        }

        foreach ($this->editors() as $editor) {
            $data['arupmeta_' . $editor . '_editor'] =
                    ['text' => $this->arupmetadata->$editor, 'format' => $this->arupmetadata->{$editor . 'format'}];
        }

        $context = context_course::instance($this->arupmetadata->course);
        $draftitemid = file_get_submitted_draft_itemid('advertblockimage');
        file_prepare_draft_area($draftitemid, $context->id, 'coursemetadatafield_arup', 'originalblockimage', 0);
        $data['advertblockimage'] = $draftitemid;

        $mform->setDefaults($data);
    }
}


