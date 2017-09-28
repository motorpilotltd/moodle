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

defined('MOODLE_INTERNAL') || die();

require_once('lib.php');

class local_timezones_renderer extends plugin_renderer_base {

    public function header_actions() {
        $addurl = new moodle_url('/local/timezones/edit.php');
        $items = array(
            html_writer::link($addurl, get_string('addtimezone', 'local_timezones'))
        );
        return html_writer::alist($items);
    }

    public function list_layout($timezones) {
        if (empty($timezones)) {
            return '';
        }

        $items = array();

        foreach ($timezones as $timezone) {
            $items[] = html_writer::tag('span', $timezone) . $this->list_row_actions($timezone);
        }
        return html_writer::alist($items);
    }

    public function list_row_actions(timezone $timezone) {
        $editurl = new moodle_url('/local/timezones/edit.php', array('id' => $timezone->get_id()));
        $deleteurl = new moodle_url('/local/timezones/delete.php', array('id' => $timezone->get_id()));
        $items = array(
            html_writer::link($editurl, get_string('edittimezone', 'local_timezones')),
            html_writer::link($deleteurl, get_string('deletetimezone', 'local_timezones'))
        );
        return html_writer::alist($items);
    }

    public function get_edit_form() {
        require_once('edit_form.php');
        return new timezone_form(new moodle_url('/local/timezones/edit.php'));
    }
}
