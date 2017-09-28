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

$a = new stdClass();
$a->success = 0;

try {
    require_once(dirname(__FILE__) . '/../../config.php');

    $classid = required_param('classid', PARAM_INT); // Course Module ID.

    require_sesskey();

    $class = $DB->get_record('local_taps_class', array('classid' => $classid));

    if ($class) {
        $taps = new \local_taps\taps();

        $a->success = 1;
        $a->classname = $class->classname;
        $a->location = $class->location ? $class->location : get_string('tbc', 'tapsenrol');
        $a->trainingcenter = is_null($class->trainingcenter) ? '' : $class->trainingcenter;
        if (!$class->classstarttime) {
            $a->date = get_string('waitinglist:classroom', 'tapsenrol');
        } else {
            try {
                $timezone = new DateTimeZone($class->usedtimezone);
            } catch (Exception $e) {
                $timezone = new DateTimeZone(date_default_timezone_get());
            }
            $startdatetime = new DateTime(null, $timezone);
            $startdatetime->setTimestamp($class->classstarttime);
            $a->date = $startdatetime->format('d M Y');
            $a->date .= ($class->classstarttime != $class->classstartdate) ? $startdatetime->format(' H:i T') : '';
            // Show UTC as GMT for clarity.
            $a->date = str_replace('UTC', 'GMT', $a->date);
        }
        $a->duration = ($class->classduration) ? (float) $class->classduration . ' ' . $class->classdurationunits : get_string('tbc', 'tapsenrol');
        $a->cost = $class->price ? $class->price.' '.$class->currencycode : '-';
        $seatsremaining = $taps->get_seats_remaining($classid);
        $a->seatsremaining = ($seatsremaining === -1 ? get_string('unlimited', 'tapsenrol') : $seatsremaining);

        list($in, $inparams) = $DB->get_in_or_equal(
            $taps->get_statuses('cancelled'),
            SQL_PARAMS_NAMED, 'status', false
        );
        $compare = $DB->sql_compare_text('bookingstatus');
        $params = array_merge(
            array('classid' => $class->classid),
            $inparams
        );
        $a->enrolments = $DB->count_records_select('local_taps_enrolment', "classid = :classid AND (archived = 0 OR archived IS NULL) AND {$compare} {$in}", $params);
    }
    echo json_encode($a);
    exit;
} catch (moodle_exception $e) {
    echo json_encode($a);
    exit;
}