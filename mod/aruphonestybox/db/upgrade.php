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

    if ($oldversion < 2014091501) {
        $plugin = new stdClass();
        $plugin->version = null;
        require($CFG->dirroot.'/mod/aruphonestybox/version.php');

        $a = new stdClass();
        $a->name = 'mod_aruphonestybox';
        $a->version = $plugin->version;
        $a->requiredversion = '2014091501';
        $a->currentversion = $DB->get_field('config_plugins', 'value', array('name' => 'version', 'plugin' => 'mod_aruphonestybox'));

        throw new moodle_exception('pluginversiontoolow', 'mod_aruphonestybox', "$CFG->wwwroot/$CFG->admin/index.php", $a);
    }

    if ($oldversion < 2015111600) {
        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111600, 'mod', 'aruphonestybox');
    }

    if ($oldversion < 2015111602) {
        $table = new xmldb_table('aruphonestybox');

        // Update fields to match local_taps_enrolment table.
        $updatefields = array(
            new xmldb_field('classname', XMLDB_TYPE_TEXT),
            new xmldb_field('provider', XMLDB_TYPE_TEXT),
            new xmldb_field('duration', XMLDB_TYPE_FLOAT),
            new xmldb_field('durationunitscode', XMLDB_TYPE_TEXT),
            new xmldb_field('learningdesc', XMLDB_TYPE_TEXT),
            new xmldb_field('location', XMLDB_TYPE_TEXT),
            new xmldb_field('classtype', XMLDB_TYPE_TEXT),
            new xmldb_field('classcategory', XMLDB_TYPE_TEXT),
            new xmldb_field('healthandsafetycategory', XMLDB_TYPE_TEXT),
            new xmldb_field('classname', XMLDB_TYPE_TEXT),
            new xmldb_field('classcost', XMLDB_TYPE_NUMBER, '20, 2'),
            new xmldb_field('certificateno', XMLDB_TYPE_TEXT),
        );
        // Launch changes for fields.
        foreach ($updatefields as $field) {
            if ($dbman->field_exists($table, $field)) {
                $dbman->change_field_type($table, $field);
            }
        }

        // Migrate data from learning description continuation fields.
        $instances = $DB->get_records('aruphonestybox');
        foreach ($instances as $instance) {
            $instance->learningdesc = $instance->learningdesc
                    . ' ' . $instance->learningdesccont1
                    . ' ' . $instance->learningdesccont2;
            $instance->learningdesccont1 = null;
            $instance->learningdesccont2 = null;
            $DB->update_record('aruphonestybox', $instance);
        }

        // Remove learning description continuation fields
        $removefields = array(
            new xmldb_field('learningdesccont1'),
            new xmldb_field('learningdesccont2'),
        );
        // Launch field removal.
        foreach ($removefields as $field) {
            if ($dbman->field_exists($table, $field)) {
                $dbman->drop_field($table, $field);
            }
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111602, 'mod', 'aruphonestybox');
    }

    if ($oldversion < 2015111614) {
        // Tidy up DB as code/requirements removed.
        $table = new xmldb_table('aruphonestybox');
        // Update fields to match local_taps_enrolment table.
        $fields = array(
            new xmldb_field('showcompletiondate'),
            new xmldb_field('showcertificateupload'),
            new xmldb_field('approvalrequired'),
            new xmldb_field('firstname'),
            new xmldb_field('lastname'),
            new xmldb_field('email'),
        );

        // Conditionally launch drop field
        foreach ($fields as $field) {
            if ($dbman->field_exists($table, $field)) {
                $dbman->drop_field($table, $field);
            }
        }

        $table2 = new xmldb_table('aruphonestybox_users');
        $fields2 = array(
            new xmldb_field('approved'),
            new xmldb_field('approverid'),
            new xmldb_field('timemodified'),
            new xmldb_field('completiondate'),
        );
        // Conditionally launch drop field
        foreach ($fields2 as $field2) {
            if ($dbman->field_exists($table2, $field2)) {
                $dbman->drop_field($table2, $field2);
            }
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111614, 'mod', 'aruphonestybox');
    }    }

    return true;
}
