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
 * Date reset script.
 * Updates start/end dates based on times.
 *
 * @package     local_coursemanager
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once("$CFG->libdir/clilib.php");

cli_logo();
cli_writeln('');
cli_heading('Arup Linked Course date resetting');
cli_writeln('');

if (moodle_needs_upgrading()) {
    cli_writeln('Moodle upgrade pending, cannot execute script.');
    exit(1);
}

list($options, $unrecognized) = cli_get_params(
    ['help' => false, 'reset' => false, 'since' => false, 'dryrun' => false],
    ['h' => 'help', 'r' => 'reset']
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help'] || !$options['reset']) {
    $help =
"Resets start/end dates for classes (class/enrolment) based on times and chosen timezones.

Options:

-h, --help            Print out this help
-r, --reset           Reset dates

--since               Check entries added/edited since (timestamp). 0 will process _all_.
--dryrun              Must be 'no' to update data.


Example:
\$sudo -u www-data /usr/bin/php local/coursemanager/cli/date_reset.php --reset --since=0

";
    echo $help;
    die;
}

// Increase memory limit.
raise_memory_limit(MEMORY_EXTRA);

$predbqueries = $DB->perf_get_queries();
$pretime = microtime(true);

if ($options['reset']) {
    cli_heading('Resetting dates');
    $since = $options['since'] !== false ? $options['since'] : cli_input('Classes added/edited since (timestamp)');
    $update = ($options['dryrun'] === 'no');

    // Reset time to allow for variable time taken to input parameters.
    $pretime = microtime(true);

    try {
        // Find and update records/trigger event(s).
        cli_writeln('');
        cli_writeln('UPDATING COURSES');
        // Course: enddate (Should be 23:59:59).
        $courses = $DB->get_records_select('local_taps_course', "enddate > 0 AND timemodified > :since", ['since' => (int)$since], '', 'id, enddate');
        $totalcourses = count($courses);
        $coursesupdated = 0;
        cli_writeln('Courses found: ' . $totalcourses);
        foreach ($courses as $course) {
            if (($course->enddate % (24*60*60)) === 0) {
                // Isn't 23:59:59, need to shift on!
                cli_write('Course end date: '.$course->enddate);
                $course->enddate = $course->enddate + (23*60*60) + (59*60) + 59;
                cli_writeln(' => '.$course->enddate);
                if ($update) {
                    $DB->update_record('local_taps_course', $course);
                    $coursesupdated ++;
                }
            }
        }
        cli_writeln('Courses updated (enddate): ' . $coursesupdated);
        cli_writeln('');

        cli_writeln('UPDATING CLASSES');
        // Class: class[start|end]date should be midnight/23:59:59 offset by chosen timezone.
        // Class: enrolment[start|end]date should be midnight/23:59:59 offset by chosen timezone.
        $fields = 'id, classid, classstarttime, classendtime, classstartdate, classenddate, enrolmentstartdate, enrolmentenddate, usedtimezone';
        $classes = $DB->get_records_select('local_taps_class', "timemodified > :since", ['since' => (int)$since], '', $fields);
        $totalclasses = count($classes);
        $classesupdated = 0;
        $enrolmentdatesupdated = 0;
        cli_writeln('Classes found: ' . $totalclasses);
        foreach ($classes as $class) {
            try {
                $timezone = new DateTimeZone($class->usedtimezone);
            } catch (Exception $e) {
                $timezone = new DateTimeZone(date_default_timezone_get());
            }

            $classdateupdated = false;

            if (is_null($class->classstartdate) || !($class->classstartdate == 0 && $class->classstarttime == 0)) {
                $classstarttime = new DateTime(null, $timezone);
                $classstarttime->setTimestamp($class->classstarttime);
                $classstartdatestring = $classstarttime->format('d M Y 00:00:00');
                $classstartdate = new DateTime($classstartdatestring, $timezone);
                if ($class->classstarttime == 0) {
                    cli_write($class->id.' : Class start date : ' . $class->classstartdate);
                    $class->classstartdate = 0;
                    $classdateupdated = true;
                    cli_writeln(' => ' . $class->classstartdate);
                } else if ($classstartdate->getTimestamp() != $class->classstartdate) {
                    cli_write($class->id.' : Class start date : ' . $class->classstartdate);
                    $class->classstartdate = $classstartdate->getTimestamp();
                    $classdateupdated = true;
                    cli_writeln(' => ' . $class->classstartdate);
                }
            }

            if (is_null($class->classenddate) || !($class->classenddate == 0 && $class->classendtime == 0)) {
                $classendtime = new DateTime(null, $timezone);
                $classendtime->setTimestamp($class->classendtime);
                $classenddatestring = $classendtime->format('d M Y 23:59:59');
                $classenddate = new DateTime($classenddatestring, $timezone);
                if ($class->classendtime == 0) {
                    cli_write($class->id.' : Class end date : ' . $class->classenddate);
                    $class->classenddate = 0;
                    $classdateupdated = true;
                    cli_writeln(' => ' . $class->classenddate);
                } else if ($classenddate->getTimestamp() != $class->classenddate) {
                    cli_write($class->id.' : Class end date : ' . $class->classenddate);
                    $class->classenddate = $classenddate->getTimestamp();
                    $classdateupdated = true;
                    cli_writeln(' => ' . $class->classenddate);
                }
            }

            // Enrolments...
            $enrolmentdateupdated = false;
            if ($class->enrolmentstartdate > 0) {
                $enrolmentstartdate = new DateTime(null, $timezone);
                $enrolmentstartdate->setTimestamp($class->enrolmentstartdate);
                // Is it midnight in timezone?
                if ($enrolmentstartdate->format('His') !== '000000') {
                    cli_write($class->id.' : Enrolment start date : '.$enrolmentstartdate->format('d M Y H:is T'));
                    $enrolmentstartdate->setTimestamp($enrolmentstartdate->getTimestamp() - $enrolmentstartdate->getOffset());
                    $class->enrolmentstartdate = $enrolmentstartdate->getTimestamp();
                    cli_writeln(' => ' . $enrolmentstartdate->format('d M Y H:i:s T'));
                    $enrolmentdateupdated = true;
                }
            }
            if ($class->enrolmentenddate > 0) {
                $enrolmentenddate = new DateTime(null, $timezone);
                $enrolmentenddate->setTimestamp($class->enrolmentenddate);
                // Is it 23:59:59 in timezone?
                if ($enrolmentenddate->format('His') !== '235959') {
                    cli_write($class->id.' : Enrolment end date : ' . $enrolmentenddate->format('d M Y H:i:s T'));
                    $addoffset = 0;
                    if ($enrolmentenddate->format('His') !== '000000') {
                        $addoffset -= $enrolmentenddate->getOffset();
                    }
                    $enrolmentenddate->setTimestamp($enrolmentenddate->getTimestamp() + $addoffset + (23*60*60) + (59*60) + 59);
                    $class->enrolmentenddate = $enrolmentenddate->getTimestamp();
                    cli_writeln(' => ' . $enrolmentenddate->format('d M Y H:i:s T'));
                    $enrolmentdateupdated = true;
                }
            }

            if ($update && ($classdateupdated || $enrolmentdateupdated)) {
                $DB->update_record('local_taps_class', $class);

                if ($classdateupdated) {
                    // Trigger event only needed if class dates updated.
                    $event = \local_coursemanager\event\class_updated::create(array(
                        'objectid' => $class->id,
                        'other' => array('classid' => $class->classid)
                    ));
                    $event->trigger();
                }

                $classesupdated += (int) $classdateupdated;
                $enrolmentdatesupdated += (int) $enrolmentdateupdated;
            }
        }

        cli_writeln('Classes updated (class[start|end]date): ' . $classesupdated); // Always does all!
        cli_writeln('Classes updated (enrolment[start|end]date): ' . $enrolmentdatesupdated);
        cli_writeln('');

        cli_writeln('');
        cli_writeln('DB queries: ' . ($DB->perf_get_queries() - $predbqueries));
        cli_writeln('Time: ' . (microtime(true) - $pretime) . ' seconds');

        exit(0);
    } catch (Exception $e) {
        if ($DB->is_transaction_started()) {
            $DB->force_transaction_rollback();
        }

        cli_writeln('');
        cli_writeln('Oracle data resetting failed: ' . $e->getMessage());
        cli_writeln('');

        cli_writeln('DB queries: ' . ($DB->perf_get_queries() - $predbqueries));
        cli_writeln('Time: ' . (microtime(true) - $pretime) . ' seconds');

        if ($CFG->debugdeveloper) {
            cli_writeln('');
            if (!empty($e->debuginfo)) {
                cli_writeln('Debug info:');
                cli_writeln($e->debuginfo);
            }
            cli_writeln('Backtrace:');
            cli_writeln(format_backtrace($e->getTrace(), true));
        }

        exit(1);
    }
}