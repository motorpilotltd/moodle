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

namespace datasource_course;

use coursemetadatafield_arup\arupmetadata;
use local_panels\layout;

class datasource extends \local_panels\datasource {
    private $courseids = [];

    private $counter = 0;
    public function rendernextitem($zonesize) {
        global $OUTPUT;
        $template = $this->gettemplate($zonesize);

        if (isset($this->courseids[$this->counter])) {
            $model = $this->getdatafortemplate($this->courseids[$this->counter]);

            if (!$model) {
                return '';
            }

            return $OUTPUT->render_from_template("datasource_course/$template", $model);
        } else {
            return '';
        }
    }

    public function renderallitems($zonesize) {
        global $OUTPUT;

        $template = $this->gettemplate($zonesize);

        $retval = [];
        foreach ($this->courseids as $courseid) {
            $model = $this->getdatafortemplate($courseid);

            if (!$model) {
                continue;
            }

            $retval[] = $OUTPUT->render_from_template("datasource_course/$template", $model);
        }
        return $retval;
    }

    public function renderitem($zonesize) {
        global $OUTPUT;
        $template = $this->gettemplate($zonesize);

        if (!isset($this->courseids[0])) {
            return '';
        }

        $model = $this->getdatafortemplate($this->courseids[0]);

        if (!$model) {
            return '';
        }
        return $OUTPUT->render_from_template("datasource_course/$template", $model);
    }

    protected function getdatafortemplate($courseid) {
        global $OUTPUT;

        $arupmetadata = arupmetadata::fetch(['course' => $courseid]);

        if (empty($arupmetadata)) {
            return false;
        }

        return $arupmetadata->export_for_template($OUTPUT);
    }

    public function unpickle($zonedata) {
        if (empty($zonedata['courseids'])) {
            return;
        }

        if (is_array($zonedata['courseids'])) {
            $this->courseids = $zonedata['courseids'];
        } else {
            $this->courseids = [$zonedata['courseids']];
        }
    }

    public static function addtoform($mform, $fieldprefix, $multiple) {
        $options = array(
                'ajax' => 'datasource_course/form-course-selector',
                'multiple' => $multiple,
                'valuehtmlcallback' => function($value) {
                    global $OUTPUT;

                    $course = get_course($value);
                    $useroptiondata = [
                            'name' => $course->fullname,
                            'id' => $course->id
                    ];
                    return $OUTPUT->render_from_template('datasource_course/form-course-selector-suggestion', $useroptiondata);
                }
        );
        $mform->addElement('autocomplete', "$fieldprefix-courseids", get_string('courses', 'core'), array(), $options);
    }

    public function pickle() {
        return ["$this->zoneprefix-courseids" => $this->courseids];
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