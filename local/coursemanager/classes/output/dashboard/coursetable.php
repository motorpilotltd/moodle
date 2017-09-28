<?php
// This file is part of the Arup Course Management system
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
 *
 * @package     local_coursemanager
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursemanager\output\dashboard;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use moodle_url;
use renderer_base;
use html_writer;

class coursetable extends base {

    private $form;
    public $cmcourse;
    public $myparams;
      /**
     * Export this data so it can be used as the context for a mustache template.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $OUTPUT;
        $data = new stdClass();

        $this->form = $this->coursemanager->form;
        $page = $this->coursemanager->get_current_pageobject();
        $this->cmcourse = $this->coursemanager->get_current_courseobject($this->coursemanager->cmcourse->id);
        $this->myparams = array('page' => 'course',
                'cmcourse' => $this->cmcourse->id, 'edit' => '1');
        $tab1 = array(
            'tab1' => 'header',
            'moodlecourse' => 'moodlecourse',
            'coursecode' => 'text',
            'coursename' => 'text',
            'startdate' => 'date',
            'enddate' => 'date',
            'courseregion' => 'text',
            'duration' => 'text',
            'durationunits' => 'text',
            'onelinedescription' => 'text');

        $tab2 = array(
            'tab2' => 'header',
            'coursedescription' => 'html',
            'courseobjectives' => 'html',
            'courseaudience' => 'html',
            'keywords' => 'text');

        $tab3 = array(
            'tab3' => 'header',
            'globallearningstandards', 'checkbox',
            'accreditationgivendate' => 'date',
            'futurereviewdate' => 'date',
            'jobnumber' => 'text');

        $data->tables[] = $this->tabtable($tab1);
        $data->tables[] = $this->tabtable($tab2);
        $data->tables[] = $this->tabtable($tab3);


        if ($page->add) {
            $data->canedit = true;
            $data->icon = $OUTPUT->pix_icon('i/edit', get_string('form:course:edit', 'local_coursemanager'));
        }
        return $data;
    }

    public function tabtable($fields) {
        try {
            $timezone = new \DateTimeZone($this->form->usedtimezone);
        } catch (Exception $e) {
            $timezone = new \DateTimeZone(date_default_timezone_get());
        }
        $data = new stdClass();
        $data->rows = array();
        foreach ($fields as $key => $type) {
            if (isset($this->form->$key)) {
                $data->rows[] = $this->tablerow($key, $type, $this->form->$key, 'form:course:', $timezone);
            }
            if ($type == 'header' || $type == 'moodlecourse') {
                $data->rows[] = $this->tablerow($key, $type, null, 'form:course:');
            }
        }
        return $data;
    }

    public function tablerow($key, $type, $value, $langprefix, \DateTimeZone $timezone = null) {
        if (is_null($timezone)) {
            $timezone = new \DateTimeZone('UTC');
        }
        
        $row = new stdClass();
        $row->name = get_string($langprefix.$key, 'local_coursemanager');

        if ($type == 'header') {
            $row->header = true;
            $tab = str_replace('tab', '', $key);
            if ($this->myparams['page'] == 'course') {
                $this->myparams['tab'] = $tab;
            }
            $params = array_merge($this->coursemanager->searchparams, $this->myparams);
            $row->actionurl = new moodle_url($this->coursemanager->baseurl, $params);
        }
        if ($type == 'moodlecourse') {
            if ($this->cmcourse->moodlecourse) {
                $moodlecourseurl = new moodle_url('/course/view.php',
                    array('id' =>  $this->cmcourse->moodlecourse->id));
                $row->value = html_writer::link($moodlecourseurl, $this->cmcourse->moodlecourse->fullname);
            } else {
                $addmoodlecourse = new moodle_url('/local/taps/addcourse.php', array('courseid' => $this->cmcourse->courseid));
                $row->value = html_writer::link($addmoodlecourse, get_string('addcourse', 'local_coursemanager'));
            }
        } else if ($type == 'text') {
            $row->value = $value;
        } else if ($type == 'date') {
            if ($value == 0) {
                $row->value = get_string('notset', 'local_coursemanager');
            } else {
                $row->value = userdate($value, get_string('strftimedate'), $timezone);
            }
        } else if ($type == 'time') {
            if ($value == 0) {
                $row->value = get_string('notset', 'local_coursemanager');
            } else {
                $date = new \DateTime(null, $timezone);
                $date->setTimestamp($value);
                $row->value = str_ireplace([' ', 'UTC'], ['&nbsp;', 'GMT'], $date->format('d M Y H:i T'));
            }
        } else if ($type == 'html') {
            $row->value = file_rewrite_pluginfile_urls($value,
                'pluginfile.php', 1, 'local_coursemanager', $key, $this->cmcourse->id);
        } else if ($type == 'checkbox') {
            if ($value == 1) {
                $row->value = get_string('yes');
            } else {
                $row->value = get_string('no');
            }
        } else {
            $row->value = get_string('no');
        }
        return $row;
    }
}