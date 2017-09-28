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

class classtable extends coursetable {

    /**
     * Export this data so it can be used as the context for a mustache template.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $OUTPUT;
        $data = new stdClass();
        
        $form = $this->coursemanager->form;
        $page = $this->coursemanager->get_current_pageobject();
        $this->cmcourse = $this->coursemanager->get_current_courseobject($this->coursemanager->cmcourse->id);
        $fields = array(
            'tab1' => 'header',
            'classname' => 'text',
            'classtype' => 'text',
            'classstatus' => 'text',
            'classdurationunits' => 'text',
            'classduration' => 'text',
            'usedtimezone' => 'text',
            'classstarttime' => 'time',
            'classendtime' => 'time',
            'enrolmentstartdate' => 'date',
            'enrolmentenddate' => 'date',
            'location' => 'text',
            'trainingcenter' => 'text',
            'maximumattendees' => 'text',
            'currencycode' => 'text',
            'price' => 'text',
            'jobnumber' => 'text',
            'classsuppliername' => 'text');

        if ($page->add) {
            $data->canedit = true;
            $data->icon = $OUTPUT->pix_icon('i/edit', get_string('form:course:edit', 'local_coursemanager'));
        }
        $this->myparams = array('page' => 'class',
                'cmcourse' => $this->cmcourse->id, 'cmclass' => $this->coursemanager->cmclass->id, 'edit' => '1');

        $data->rows = array();
        try {
            $timezone = new \DateTimeZone($form->usedtimezone);
        } catch (Exception $e) {
            $timezone = new \DateTimeZone(date_default_timezone_get());
        }
        foreach ($fields as $key => $type) {
            if (isset($form->$key)) {
                $data->rows[] = $this->tablerow($key, $type, $form->$key, 'form:class:', $timezone);
            }
            if ($type == 'header') {
                $data->rows[] = $this->tablerow($key, $type, null, 'form:class:');
            }
        }
        return $data;
    }
}