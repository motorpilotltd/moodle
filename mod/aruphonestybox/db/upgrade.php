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
 * Upgrade code for mod_aruphonestybox
 *
 * @package     mod_aruphonestybox
 * @copyright   2016 Motorpilot Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade mod_aruphonestybox plugin
 *
 * @param   int $oldversion The old version of the mod_aruphonestybox plugin
 * @return  bool
 */
function xmldb_aruphonestybox_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();


    if ($oldversion < 2017051503) {
        // Set bulk enrolment update SQL.
        $updatesql = "UPDATE {local_taps_enrolment}
                   SET classname = :classname, origin = :origin, originid = :originid, locked = :locked, timemodified = :now
                 WHERE id IN (
                           SELECT lte.id
                             FROM {local_taps_enrolment} lte
                             JOIN {aruphonestybox} ahb
                                  ON ahb.classname = lte.classname
                                 AND ahb.provider = lte.provider
                                 AND ahb.id = :ahbid
                            WHERE cpdid IS NOT NULL
                       )";

        $instances = $DB->get_records('aruphonestybox');
        foreach ($instances as $instance) {
            $course = get_course($instance->course);
            $instance->origin = 'mod_aruphonestybox';
            $instance->classname = $course->fullname;
            $params = [
                'ahbid' => $instance->id,
                'classname' => $instance->classname,
                'origin' => $instance->origin,
                'originid' => $instance->id,
                'locked' => 1,
                'now' => time(),
            ];
            // Update enrolments first (before we update activity instance details).
            $DB->execute($updatesql, $params);
            // Update activity instance.
            $DB->update_record('aruphonestybox', $instance);
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2017051503, 'mod', 'aruphonestybox');
    }


    if ($oldversion < 2017051504) {
        // Define table aruphonestybox_duration to be created
        $table = new xmldb_table('aruphonestybox_duration');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('ahbid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('duration', XMLDB_TYPE_FLOAT, '20, 2', null, null, null, 0);
        $table->add_field('durationunitscode', XMLDB_TYPE_TEXT, 10);

        // Adding keys to table aruphonestybox_duration.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        $table->add_key('ahbid', XMLDB_KEY_UNIQUE, array('ahbid'));

        // Conditionally launch create table for aruphonestybox_duration.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $aruphonestybox_duration =  $DB->count_records('aruphonestybox_duration');

        // Copy aruphonestybox to aruphonestybox_duration
        if ($aruphonestybox_duration < 1) {
            $sql = "INSERT INTO {aruphonestybox_duration} (ahbid, duration, durationunitscode)
            SELECT id, duration,  durationunitscode
            FROM {aruphonestybox}";

            $DB->execute($sql, []);
        }

        $params = [
            'daystohour' => '7',
            'weekstohour' => '35',
            'hour' => 60,
            'formatdecimal' => '0.0000',
            'hourscode' => 'H',
            'minscode' => 'MIN',
            'dayscode' => 'D',
            'weekscode' => 'W',
        ];

        /*
         * Updating table aruphonestybox
         * ---------------------------------------------------------------------------------------------------------------------
         */
        // Update Minute(s) to Hour(s) and duration conversion for table aruphonestybox
        $sql = "UPDATE {aruphonestybox}
            SET duration = FORMAT((duration / :hour), :formatdecimal),
                durationunitscode = :hourscode
            WHERE durationunitscode = :minscode";
        $DB->execute($sql, $params);

        // Update Day(s) to Hour(s), and duration conversion for table aruphonestybox
        $sql = "UPDATE {aruphonestybox}
            SET duration = FORMAT((duration * :daystohour), :formatdecimal),
                durationunitscode = :hourscode
            WHERE durationunitscode = :dayscode";
        $DB->execute($sql, $params);

        // Update Weeks(s) to Hour(s), and duration conversion for table aruphonestybox
        $sql = "UPDATE {aruphonestybox}
            SET duration = FORMAT((duration * :weekstohour), :formatdecimal),
                durationunitscode = :hourscode
            WHERE durationunitscode = :weekscode";
        $DB->execute($sql, $params);

           // Savepoint reached.
        upgrade_plugin_savepoint(true, 2017051504, 'mod', 'aruphonestybox');
    }

    return true;
}
