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
 * imagemetadatafield_arup field.
 *
 * @package    imagemetadatafield_arup
 * @copyright  Andrew Hancox <andrewdchancox@googlemail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace datasource_image;

use imagemetadatafield_arup\arupmetadata;
use local_panels\layout;

class datasource extends \local_panels\datasource {

    private $counter = 0;

    public function rendernextitem($zonesize) {
        global $OUTPUT;
        $template = $this->gettemplate($zonesize);

        $urls = $this->getfileurls();

        if (isset($urls[$this->counter])) {
            $model = (object)['imageurl' => $urls[$this->counter]];
            return $OUTPUT->render_from_template("datasource_image/$template", $model);
        } else {
            return '';
        }
    }

    public function renderallitems($zonesize) {
        global $OUTPUT;

        $template = $this->gettemplate($zonesize);

        $retval = [];
        foreach ($this->getfileurls() as $imgurl) {
            $model = (object)['imageurl' => $imgurl];
            $retval[] = $OUTPUT->render_from_template("datasource_image/$template", $model);
        }
        return $retval;
    }

    public function renderitem($zonesize) {
        global $OUTPUT;
        $template = $this->gettemplate($zonesize);
        $urls = $this->getfileurls();

        if (!isset($urls[0])) {
            return '';
        }

        $model = (object) ['imageurl' => $urls[0]];

        if (!$model) {
            return '';
        }
        return $OUTPUT->render_from_template("datasource_image/$template", $model);
    }

    private function getfileurls() {
        $fieldname = $this->zoneprefix . '-image';
        $filearea = str_replace('-', '_', $fieldname);

        $fs = new \file_storage();

        $files = $fs->get_area_files($this->context->id, 'datasource_image', $filearea, 0, '', false);

        $urls = [];

        foreach ($files as $file) {
            $urls[] = \moodle_url::make_pluginfile_url($this->context->id, 'datasource_image', $filearea, 0, $file->get_filepath(),
                    $file->get_filename());
        }

        return $urls;
    }

    public function unpickle($zonedata) {
        return;
    }

    public static function addtoform($mform, $zoneprefix, $multiple) {
        $filemanageroptions = array();
        $filemanageroptions['return_types'] = 3;
        $filemanageroptions['accepted_types'] = array('web_image');
        $filemanageroptions['maxbytes'] = 0;

        if (!$multiple) {
            $filemanageroptions['maxfiles'] = 1;
        }

        $filemanageroptions['mainfile'] = false;

        $mform->addElement('filemanager', $zoneprefix . '-image', '', null, $filemanageroptions);
    }

    public function pickle() {
        return [];
    }

    public function preprocessform($zonedata) {
        $fieldname = $this->zoneprefix . '-image';
        $draftitemid = file_get_submitted_draft_itemid($fieldname);
        $filearea = str_replace('-', '_', $fieldname);

        file_prepare_draft_area($draftitemid, $this->context->id, 'datasource_image', $filearea, 0);
        $zonedata[$fieldname] = $draftitemid;
        return $zonedata;
    }

    public function postprocessform() {
        $fieldname = $this->zoneprefix . '-image';
        $draftitemid = file_get_submitted_draft_itemid($fieldname);
        $filearea = str_replace('-', '_', $fieldname);
        file_save_draft_area_files($draftitemid, $this->context->id, 'datasource_image', $filearea, 0);
    }

    /**
     * @param $zonesize
     * @return string
     */
    protected function gettemplate($zonesize) {
        switch ($zonesize) {
            case layout::ZONESIZE_SMALL:
                $template = 'small';
                break;
            case layout::ZONESIZE_LARGE:
                $template = 'large';
                break;

        }
        return $template;
    }
}