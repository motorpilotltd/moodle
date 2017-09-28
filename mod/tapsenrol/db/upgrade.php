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
 * Upgrade code for mod_tapsenrol
 *
 * @package     mod_tapsenrol
 * @copyright   2016 Motorpilot Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade mod_tapsenrol plugin
 *
 * @param   int $oldversion The old version of the mod_tapsenrol plugin
 * @return  bool
 */
function xmldb_tapsenrol_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2014022851) {
        $plugin = new stdClass();
        $plugin->version = null;
        require($CFG->dirroot.'/mod/tapsenrol/version.php');

        $a = new stdClass();
        $a->name = 'mod_tapsenrol';
        $a->version = $plugin->version;
        $a->requiredversion = '2014022851';
        $a->currentversion = $DB->get_field('config_plugins', 'value', array('name' => 'version', 'plugin' => 'mod_tapsenrol'));

        throw new moodle_exception('pluginversiontoolow', 'mod_tapsenrol', "$CFG->wwwroot/$CFG->admin/index.php", $a);
    }

    if ($oldversion < 2015111600) {
        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111600, 'mod', 'tapsenrol');
    }

    if ($oldversion < 2015111601) {
        // Define table tapsenrol_tracking to be dropped.
        $table = new xmldb_table('tapsenrol_tracking');

        // Conditionally launch drop table for tapsenrol_tracking.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111601, 'mod', 'tapsenrol');
    }

    if ($oldversion < 2015111603) {
        // Create an 'Off' workflow for migration from Oracle.
        $iw = new stdClass();
        $iw->name = 'Off (Ex-Oracle)';
        $iw->regionid = 0;
        $iw->enroltype = 'enrol';
        $iw->approvalrequired = 0;
        $iw->approvalreminder = 0;
        $iw->cancelafter = 0;
        $iw->cancelbefore = 0;
        $iw->closeenrolment = 0;
        $iw->firstreminder = 0;
        $iw->secondreminder = 0;
        $iw->noreminder = 0;
        $iw->fromfirstname = null;
        $iw->fromlastname = null;
        $iw->fromemail = null;
        $iw->sponsors = null;
        $iw->rejectioncomments = 0;
        $iw->cancelcomments = 0;
        $iw->enrolinfo = null;
        $iw->approveinfo = null;
        $iw->rejectinfo = null;
        $iw->eitherinfo = null;
        $iw->cancelinfo = null;
        $iw->locked = 1;
        $iw->timecreated = $iw->timemodified = time();

        $iw->id = $DB->insert_record('tapsenrol_iw', $iw);

        if ($iw->id) {
            // Set all existing 'Off' workflows to this.
            // Where internalworkflowid == 0
            $DB->set_field_select('tapsenrol', 'internalworkflowid', $iw->id, 'internalworkflowid = :internalworkflowid', array('internalworkflowid' => 0));
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2015111603, 'mod', 'tapsenrol');
    }

    if ($oldversion < 2015111609) {
        // Update default emails
        require_once($CFG->dirroot.'/mod/tapsenrol/db/default_emails.php');
        foreach ($defaultemails as $defaultemail) {
            $existingemail = $DB->get_record('tapsenrol_iw_email', array('email' => $defaultemail->email));
            if (!$existingemail) {
                $DB->insert_record('tapsenrol_iw_email', $defaultemail);
            } else {
                $ids[] = $defaultemail->id = $existingemail->id;
                $DB->update_record('tapsenrol_iw_email', $defaultemail);
            }
        }

        upgrade_mod_savepoint(true, 2015111609, 'tapsenrol');
    }

    if ($oldversion < 2015111610) {
        // Define field moved to be added to tapsenrol_iw_tracking.
        $table = new xmldb_table('tapsenrol_iw_tracking');
        $field = new xmldb_field('moved', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, 0);

        // Conditionally launch add field completiontype.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Savepoint reached.
        upgrade_mod_savepoint(true, 2015111610, 'tapsenrol');
    }

    if ($oldversion < 2015111611) {
        // Define field moved to be added to tapsenrol_iw_tracking.
        $table = new xmldb_table('tapsenrol_iw');
        $field = new xmldb_field('emailsoff', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, 0);

        // Conditionally launch add field completiontype.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Try and find previously set Ex-Oracle worflow and turn emails off.
        $sql = "UPDATE {tapsenrol_iw} SET emailsoff = 1 WHERE name = :name";
        $DB->execute($sql, ['name' => 'Off (Ex-Oracle)']);

        // Savepoint reached.
        upgrade_mod_savepoint(true, 2015111611, 'tapsenrol');
    }

    return true;
}
