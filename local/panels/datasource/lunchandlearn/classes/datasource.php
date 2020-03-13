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

namespace datasource_lunchandlearn;

require_once("$CFG->dirroot/local/lunchandlearn/lib.php");

use local_panels\layout;

class datasource extends \local_panels\datasource {
    private $lunchandlearnids = [];

    private $counter = 0;
    public function rendernextitem($zonesize) {
        global $OUTPUT;
        $template = $this->gettemplate($zonesize);

        if (isset($this->lunchandlearnids[$this->counter])) {
            $model = $this->getdatafortemplate($this->lunchandlearnids[$this->counter]);
            $this->counter++;

            if (!$model) {
                return '';
            }

            return $OUTPUT->render_from_template("datasource_lunchandlearn/$template", $model);
        } else {
            return '';
        }
    }

    public function renderallitems($zonesize) {
        global $OUTPUT;

        $template = $this->gettemplate($zonesize);

        $retval = [];
        foreach ($this->lunchandlearnids as $courseid) {
            $model = $this->getdatafortemplate($courseid);

            if (!$model) {
                continue;
            }

            $retval[] = $OUTPUT->render_from_template("datasource_lunchandlearn/$template", $model);
        }
        return $retval;
    }

    public function renderitem($zonesize) {
        global $OUTPUT;
        $template = $this->gettemplate($zonesize);

        if (!isset($this->lunchandlearnids[0])) {
            return '';
        }

        $model = $this->getdatafortemplate($this->lunchandlearnids[0]);

        if (!$model) {
            return '';
        }
        return $OUTPUT->render_from_template("datasource_lunchandlearn/$template", $model);
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

    /**
     * @return \stdClass
     * @throws \coding_exception
     */
    protected function getdatafortemplate($lunchandlearnid) {
        try {
            $lal = new \lunchandlearn($lunchandlearnid);
        } catch (\Exception $ex) {
            if ($ex->getCode() == 99) { // Not found.
                return null;
            } else {
                throw $ex;
            }
        }

        $event = $lal->get_event();

        $data = new \stdClass();
        //echo '<pre>' . print_r($lal, true) . '</pre>';
        $data->date = $event->timestart;
        $data->office = $lal->scheduler->office;
        $data->room = $lal->scheduler->room;
        $data->timezone = $lal->scheduler->timezone;
        $data->name = $lal->name;
        $data->summary = $lal->summary;
        $data->description = $lal->description;

        $data->attendanceinfo = '';
        if (true == $lal->attendeemanager->availableinperson) {
            $data->attendanceinfo .= $lal->get_fa_icon(\lunchandlearn::ICON_INPERSON,
                    get_string('popover:inperson', 'local_lunchandlearn'),
                    get_string('popover:inpersondata', 'local_lunchandlearn'));
            $data->attendanceinfo .= $lal->attendeemanager->get_remaining_inperson(20);
        }
        if (true == $lal->attendeemanager->availableonline) {
            $data->attendanceinfo .= $lal->get_fa_icon(\lunchandlearn::ICON_ONLINE,
                    get_string('popover:online', 'local_lunchandlearn'), get_string('popover:onlinedata', 'local_lunchandlearn'));
            $data->attendanceinfo .= $lal->attendeemanager->get_remaining_online(20);
        }
        $data->actionclass = $lal->actionclass;
        $data->actionurl = new \moodle_url('/local/lunchandlearn/signup.php', array('id' => $lal->get_id()));
        return $data;
    }

    public function unpickle($zonedata) {
        if (is_array($zonedata['lunchandlearnids'])) {
            $this->lunchandlearnids = $zonedata['lunchandlearnids'];
        } else {
            $this->lunchandlearnids[] = $zonedata['lunchandlearnids'];
        }
    }

    public function pickle() {
        return ["$this->zoneprefix-lunchandlearnids" => $this->lunchandlearnids];
    }

    public static function addtoform($mform, $fieldprefix, $multiple) {
        $options = array(
                'ajax' => 'datasource_lunchandlearn/form-lunchandlearn-selector',
                'multiple' => $multiple,
        );
        $mform->addElement('autocomplete', "$fieldprefix-lunchandlearnids", get_string('pluginname', 'datasource_lunchandlearn'), array(), $options);
    }
}
