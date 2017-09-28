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

require_once(dirname(__FILE__) . '/timezone.php');
require_once(dirname(__FILE__) . '/timezone_manager.php');

function print_timezone_selector() {
    global $PAGE, $DB, $USER;

    if (empty($USER->id)) {
        return '';
    }

    // Only do this if the module has been installed.
    $dbman = $DB->get_manager();
    if ($dbman->table_exists('local_timezones') === false) {
        return '';
    }
    $usertimezone = '';
    if (!empty($USER->timezone)) {
        $usertimezone = $USER->timezone;
    }

    // Load JS.
    $PAGE->requires->js_call_amd('local_timezones/selector', 'initialise');

    $highlighted = (empty($USER->timezone) || $usertimezone == '99') ? 'notset' : '';

    // Write icon.
    $link = html_writer::link('#settimezone', 'clock', array('class' => 'clock '.$highlighted));
    $time = new DateTime();
    $time->setTimezone(timezone_get_moodle_user_timezone($usertimezone));

    $clock = html_writer::span($time->format('G:i (T)'), 'clock '. $highlighted, array('data-tz' => $usertimezone));

    // Make list (not shown until icon clicked).
    $links = array();
    foreach (timezone_manager::get_timezones() as $timezone) {
        $current = ($timezone->get_timezone() == $usertimezone) ? ' current' : '';
        $links[] = html_writer::link(
                new moodle_url(
                        '/local/timezones/set.php',
                        array('id' => $timezone->get_id())
                        ),
                $timezone->get_display(),
                array('class' => 'timezone '.$current)
                );
    }

    echo html_writer::div("$clock $link" . html_writer::alist($links, array('id' => 'timezonelist')), 'timezoneselect', array('id' => 'timezoneselect'));
}

function timezone_get_moodle_user_timezone($timezone) {
    try {
        $usertimezone = new DateTimeZone($timezone);
    } catch (Exception $e) {
        if ($timezone == 99) {
            $tzstr = date_default_timezone_get();
        } else if (preg_match('/\d+\.\d+/', $timezone)) {
            $tzstr = lunchandlearn_convert_hours_to_timezonestring($timezone);
        } else {
            $tzstr = timezone_name_from_abbr($timezone);
        }
        try {
            $usertimezone = new DateTimeZone($tzstr);
        } catch (Exception $ex) {
            $usertimezone = new DateTimeZone(date_default_timezone_get());
        }
    }
    return $usertimezone;
}
