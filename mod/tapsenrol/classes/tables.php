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

require_once($CFG->libdir.'/tablelib.php');

class tapsenrol_table_sql extends table_sql {
    public function other_cols($column, $row) {
        switch ($column) {
            case 'approved' :
                switch ($row->approved) {
                    case 0 :
                        return ucfirst(get_string('approve:rejected', 'tapsenrol'));
                    case 1 :
                        return ucfirst(get_string('approve:approved', 'tapsenrol'));
                    default:
                        return '-';
                }
            case 'timeapproved':
                return gmdate('d M Y', $row->timeapproved);
            case 'classstarttime' :
                if ($row->classstarttime) {
                    try {
                        $timezone = new DateTimeZone($row->usedtimezone);
                    } catch (Exception $e) {
                        $timezone = new DateTimeZone(date_default_timezone_get());
                    }
                    $startdatetime = new DateTime(null, $timezone);
                    $startdatetime->setTimestamp($row->classstarttime);
                    return $startdatetime->format('d M Y');
                } else {
                    return get_string('waitinglist:classroom', 'tapsenrol');
                }
            default:
                return null;
        }
    }

    public function print_nothing_to_display() {
        echo html_writer::tag('p', get_string('approve:nohistory', 'tapsenrol'));
    }
}

class tapsenrol_enrolments_table_sql extends table_sql {
    public function other_cols($column, $row) {
        switch ($column) {
            case 'timeenrolled':
                if (!is_null($row->timeenrolled)) {
                    return gmdate('d M Y', $row->timeenrolled);
                } else {
                    return get_string('manageenrolments:unavailable', 'tapsenrol');
                }
            case 'cancel':
            case 'waitlist':
            case 'move':
            case 'delete':
                $attributes = array(
                    'class' => 'tapsenrol-checkbox',
                    'type' => 'checkbox',
                    'name' => 'enrolmentid[]',
                    'value' => $row->enrolmentid
                );
                if ($column == 'waitlist') {
                    $attributes['disabled'] = 'disabled';
                }
                return html_writer::empty_tag('input', $attributes);
            case 'costcentre':
                $return = $row->icq;
                $return .= (!empty($return) && !empty($row->department)) ? ' - ' : '';
                $return .= !empty($row->department) ? $row->department : '';
                return $return;
            case 'sponsor':
                return $row->sponsorfirstname . ' ' . $row->sponsorlastname;
        }
        return null;
    }

    public function print_nothing_to_display() {
        global $PAGE;
        $renderer = $PAGE->get_renderer('mod_tapsenrol');
        echo $renderer->alert(get_string('manageenrolments:noenrolments', 'tapsenrol'), 'alert-warning', false);
    }
}