<?php
// This file is part of the Arup online appraisal system
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
 * Version details
 *
 * @package     local_onlineappraisal
 * @copyright   2016 Motorpilot Ltd, Sonsbeekmedia
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Upgrade local_onlineappraisal plugin
 *
 * @global stdClass $CFG
 * @global \moodle_database $DB
 * @param int $oldversion The old version of the local_onlineappraisal plugin
 * @return bool
 */
function xmldb_local_onlineappraisal_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2015060259) {
        $plugin = new stdClass();
        $plugin->version = null;
        require($CFG->dirroot.'/local/onlineappraisal/version.php');

        $a = new stdClass();
        $a->name = 'local_onlineappraisal';
        $a->version = $plugin->version;
        $a->requiredversion = '2015060259';
        $a->currentversion = $DB->get_field('config_plugins', 'value', array('name' => 'version', 'plugin' => 'local_onlineappraisal'));

        throw new moodle_exception('pluginversiontoolow', 'local_onlineappraisal', "$CFG->wwwroot/$CFG->admin/index.php", $a);
    }

    if ($oldversion < 2016051003) {

        // Define table local_appraisal_forms to be created.
        $table = new xmldb_table('local_appraisal_forms');

        // Adding fields to table local_appraisal_forms.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('appraisalid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('form_name', XMLDB_TYPE_CHAR, '200', null, XMLDB_NOTNULL, null, null);
        $table->add_field('form_instance', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null);
        $table->add_field('user_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('data_id', XMLDB_TYPE_INTEGER, '20', null, XMLDB_NOTNULL, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '20', null, null, null, null);

        // Adding keys to table local_appraisal_forms.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for local_appraisal_forms.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Define table local_appraisal_data to be created.
        $table = new xmldb_table('local_appraisal_data');

        // Adding fields to table local_appraisal_data.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('form_id', XMLDB_TYPE_INTEGER, '20', null, null, null, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '254', null, XMLDB_NOTNULL, null, null);
        $table->add_field('type', XMLDB_TYPE_CHAR, '254', null, null, null, null);
        $table->add_field('data', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table local_appraisal_data.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for local_appraisal_data.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Onlineappraisal savepoint reached.
        upgrade_plugin_savepoint(true, 2016051003, 'local', 'onlineappraisal');
    }

    if ($oldversion < 2016051800) {

        // Define table local_appraisal_permissions to be created.
        $table = new xmldb_table('local_appraisal_permissions');

        // Adding fields to table local_appraisal_permissions.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('permission', XMLDB_TYPE_CHAR, '254', null, XMLDB_NOTNULL, null, null);
        $table->add_field('usertype', XMLDB_TYPE_CHAR, '254', null, XMLDB_NOTNULL, null, null);
        $table->add_field('data', XMLDB_TYPE_TEXT, null, null, null, null, null);

        // Adding keys to table local_appraisal_permissions.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for local_appraisal_permissions.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Onlineappraisal savepoint reached.
        upgrade_plugin_savepoint(true, 2016051800, 'local', 'onlineappraisal');
    }

    if ($oldversion < 2016061400) {

        // Define table local_appraisal_appraisal to be updated.
        $table = new xmldb_table('local_appraisal_appraisal');

        // Adding permissionsid field to table local_appraisal_appraisal.
        $field = new xmldb_field('permissionsid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'statusid');

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            $appraisals = $DB->get_records('local_appraisal_appraisal');
            foreach ($appraisals as $appraisal) {
                // Set all permissions ids to match current status id.
                $appraisal->permissionsid = min(array($appraisal->statusid, 7)); // Old status 8 doesn't exist for permissions.
                $DB->update_record('local_appraisal_appraisal', $appraisal);
            }
        }

        // Onlineappraisal savepoint reached.
        upgrade_plugin_savepoint(true, 2016061400, 'local', 'onlineappraisal');
    }

    if ($oldversion < 2016061401) {

        // Define table local_appraisal_appraisal to be updated.
        $table = new xmldb_table('local_appraisal_appraisal');

        // Adding legacy field to table local_appraisal_appraisal.
        $field = new xmldb_field('legacy', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', null);

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
            $appraisals = $DB->get_records('local_appraisal_appraisal');
            foreach ($appraisals as $appraisal) {
                // Only legacy appraisals will have an entry in the summary table (created on initialisation).
                $appraisal->legacy = (bool) $DB->get_records('local_appraisal_summary', array('appraisalid' => $appraisal->id));
                if ($appraisal->legacy && $appraisal->statusid == 1) {
                    // Need to bump status on to be able to view 'overview' page.
                    $appraisal->statusid = 2;
                    $appraisal->permissionsid = 2;
                    $appraisal->status_history = $appraisal->status_history . '|2';
                    $a = new stdClass();
                    $a->status = get_string("status:2", 'local_onlineappraisal');
                    $a->relateduser = 'System';
                    \local_onlineappraisal\comments::save_comment(
                            $appraisal->id,
                            get_string('comment:status:1_to_2', 'local_onlineappraisal', $a)
                            );
                }
                $DB->update_record('local_appraisal_appraisal', $appraisal);
            }
        }

        // Onlineappraisal savepoint reached.
        upgrade_plugin_savepoint(true, 2016061401, 'local', 'onlineappraisal');
    }

    if ($oldversion < 2016062302) {

        // Define table local_appraisal_checkins to be created.
        $table = new xmldb_table('local_appraisal_checkins');

        // Adding fields to table local_appraisal_checkins.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('appraisalid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('ownerid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('user_type', XMLDB_TYPE_CHAR, '50', null, null, null, null);
        $table->add_field('subjectid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('checkin', XMLDB_TYPE_TEXT, null, null, null, null, null);
        $table->add_field('created_date', XMLDB_TYPE_INTEGER, '10', null, null, null, null);

        // Adding keys to table local_appraisal_checkins.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for local_appraisal_checkins.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Onlineappraisal savepoint reached.
        upgrade_plugin_savepoint(true, 2016062302, 'local', 'onlineappraisal');
    }

    if ($oldversion < 2016062800) {

        // Define field user_id to be dropped from local_appraisal_forms.
        $table = new xmldb_table('local_appraisal_forms');
        $field = new xmldb_field('form_instance');

        // Conditionally launch drop field user_id.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Onlineappraisal savepoint reached.
        upgrade_plugin_savepoint(true, 2016062800, 'local', 'onlineappraisal');
    }

    if ($oldversion < 2016062801) {

        // Define field data_id to be dropped from local_appraisal_forms.
        $table = new xmldb_table('local_appraisal_forms');
        $field = new xmldb_field('data_id');

        // Conditionally launch drop field data_id.
        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Onlineappraisal savepoint reached.
        upgrade_plugin_savepoint(true, 2016062801, 'local', 'onlineappraisal');
    }

    if ($oldversion < 2016070800) {

        // Define table local_appraisal_permissions to be updated.
        $table = new xmldb_table('local_appraisal_permissions');

        // Adding archived and legacy fields to table local_appraisal_permissions.
        $fields = array(
            new xmldb_field('archived', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', null),
            new xmldb_field('legacy', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', null)
        );

        // Conditionally launch add field.
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        // Onlineappraisal savepoint reached.
        upgrade_plugin_savepoint(true, 2016070800, 'local', 'onlineappraisal');
    }

    if ($oldversion < 2016071402) {
        // Define table appraisal_userstatus to be dropped.
        $table = new xmldb_table('appraisal_userstatus');

        // Conditionally drop table.
        if ($dbman->table_exists($table)) {
            $dbman->drop_table($table);
        }

        // Onlineappraisal savepoint reached.
        upgrade_plugin_savepoint(true, 2016071402, 'local', 'onlineappraisal');
    }

    if ($oldversion < 2016072101) {
        // Clear down language strings to remove old ones from DB.
        $componentid = $DB->get_field('tool_customlang_components', 'id', array('name' => 'local_onlineappraisal'));
        if ($componentid) {
            $DB->delete_records('tool_customlang', array('componentid' => $componentid));
        }

        // Onlineappraisal savepoint reached.
        upgrade_plugin_savepoint(true, 2016072101, 'local', 'onlineappraisal');
    }

    if ($oldversion < 2016072104) {

        // Define field lang to be added to local_appraisal_feedback.
        $table = new xmldb_table('local_appraisal_feedback');
        $field = new xmldb_field('lang', XMLDB_TYPE_TEXT, null, null, null, null, null, 'password');

        // Conditionally launch add field lang.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Onlineappraisal savepoint reached.
        upgrade_plugin_savepoint(true, 2016072104, 'local', 'onlineappraisal');
    }

    if ($oldversion < 2016081200) {

        // Define table local_appraisal_users to be created.
        $table = new xmldb_table('local_appraisal_users');

        // Adding fields to table local_appraisal_users.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $table->add_field('appraisalnotrequired', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL);

        // Adding keys to table local_appraisal_users.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for local_appraisal_users.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        // Onlineappraisal savepoint reached.
        upgrade_plugin_savepoint(true, 2016081200, 'local', 'onlineappraisal');
    }

    if ($oldversion < 2016081802) {

        // Define table local_appraisal_users to be created.
        $table = new xmldb_table('local_appraisal_users');

        // New fields to add.
        $newfields = array(
            new xmldb_field('setting', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL),
            new xmldb_field('value', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL),
        );

        // Conditionally launch add new fields to table local_appraisal_permissions.
        foreach ($newfields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        // Migrate data.
        $records = $DB->get_records('local_appraisal_users');
        foreach ($records as $record) {
            if (!isset($record->appraisalnotrequired)) {
                continue;
            }
            $record->setting = 'appraisalnotrequired';
            $record->value = $record->appraisalnotrequired;
            $DB->update_record('local_appraisal_users', $record);
        }

        // Old field to remove.
        $oldfield = new xmldb_field('appraisalnotrequired');

        // Conditionally launch drop field appraisalnotrequired.
        if ($dbman->field_exists($table, $oldfield)) {
            $dbman->drop_field($table, $oldfield);
        }

        // Define index userid_setting (unique) to be added to local_appraisal_users.
        $index = new xmldb_index('userid_setting', XMLDB_INDEX_UNIQUE, array('userid', 'setting'));

        // Conditionally launch add index userid_setting.
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        // Onlineappraisal savepoint reached.
        upgrade_plugin_savepoint(true, 2016081802, 'local', 'onlineappraisal');
    }

    if ($oldversion < 2016091200) {

        // Define field lang to be added to local_appraisal_appraisal.
        $table = new xmldb_table('local_appraisal_appraisal');
        $field = new xmldb_field('groupleader_userid', XMLDB_TYPE_INTEGER, '10', null, null, null, null, 'signoff_userid');

        // Conditionally launch add field lang.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Onlineappraisal savepoint reached.
        upgrade_plugin_savepoint(true, 2016091200, 'local', 'onlineappraisal');
    }

    if ($oldversion < 2017070500) {

        // Define field feedback_2 to be added to local_appraisal_feedback.
        $table = new xmldb_table('local_appraisal_feedback');
        $field = new xmldb_field('feedback_2', XMLDB_TYPE_TEXT, null, null, null, null, null, 'feedback');

        // Conditionally launch add field feedback_2.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Onlineappraisal savepoint reached.
        upgrade_plugin_savepoint(true, 2017070500, 'local', 'onlineappraisal');
    }

    if ($oldversion < 2017070502) {
        // Rebuild permissions table and cache.
        \local_onlineappraisal\permissions::rebuild_permissions();

        // Onlineappraisal savepoint reached.
        upgrade_plugin_savepoint(true, 2017070502, 'local', 'onlineappraisal');
    }

    if ($oldversion < 2017090300) {

        // Define field customemail to be added to local_appraisal_feedback.
        $table = new xmldb_table('local_appraisal_feedback');
        $field = new xmldb_field('customemail', XMLDB_TYPE_TEXT, null, null, null, null, null, 'lang');

        // Conditionally launch add field customemail.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Onlineappraisal savepoint reached.
        upgrade_plugin_savepoint(true, 2017090300, 'local', 'onlineappraisal');
    }

    if ($oldversion < 2017090305) {

        // Define new tables to be created.
        $tables = [
            'cohorts' => new xmldb_table('local_appraisal_cohorts'),
            'appraisals' => new xmldb_table('local_appraisal_cohort_apps'),
            'users' => new xmldb_table('local_appraisal_cohort_users'),
            'ccs' => new xmldb_table('local_appraisal_cohort_ccs'),
            ];

        // Adding fields to tables.
        foreach ($tables as $table) {
            $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
            $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
        }

        $tables['cohorts']->add_field('name', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $tables['cohorts']->add_field('availablefrom', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $tables['cohorts']->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $tables['cohorts']->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);

        $tables['appraisals']->add_field('cohortid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $tables['appraisals']->add_field('appraisalid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);

        $tables['users']->add_field('cohortid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $tables['users']->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);

        $tables['ccs']->add_field('cohortid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);
        $tables['ccs']->add_field('costcentre', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL);
        $tables['ccs']->add_field('started', XMLDB_TYPE_INTEGER, '10', null, null);
        $tables['ccs']->add_field('locked', XMLDB_TYPE_INTEGER, '10', null, null);
        $tables['ccs']->add_field('closed', XMLDB_TYPE_INTEGER, '10', null, null);
        $tables['ccs']->add_field('duedate', XMLDB_TYPE_INTEGER, '10', null, null);

        // Conditionally launch create table.
        foreach ($tables as $table) {
            if (!$dbman->table_exists($table)) {
                $dbman->create_table($table);
            }
        }

        $existing = $DB->count_records('local_appraisal_cohorts');

        if ($existing) {
            throw new Exception('Cohorts already exist, cannot add base cohorts. Please check/rectify and re-run upgrade.');
        }

        // Add previous cohorts.
        $cohorts = [
            '2015' => new stdClass(),
            '2016' => new stdClass(),
        ];

        foreach ($cohorts as $name => $cohort) {
            $cohort->name = $name;
            $cohort->availablefrom = gmmktime(0, 0, 0, 9, 1, $name);
            $cohort->timemodified = $cohort->timecreated = time();
            $cohort->id = $DB->insert_record('local_appraisal_cohorts', $cohort);
        }

        // Attach legacy appraisals (and users) to 2015 cohort.
        $legacysql = "SELECT aa.id, aa.appraisee_userid, u.icq as costcentre FROM {local_appraisal_appraisal} aa JOIN {user} u ON u.id = aa.appraisee_userid WHERE aa.legacy = 1";
        $legacies = $DB->get_records_sql($legacysql);
        $legacyapps = $DB->get_records_menu('local_appraisal_cohort_apps', ['cohortid' => $cohorts['2015']->id], '', 'appraisalid, appraisalid');
        $legacyusers = $DB->get_records_menu('local_appraisal_cohort_users', ['cohortid' => $cohorts['2015']->id], '', 'userid, userid');
        $legacyccs = $DB->get_records_menu('local_appraisal_cohort_ccs', ['cohortid' => $cohorts['2015']->id], '', 'costcentre, costcentre');
        foreach ($legacies as $legacy) {
            if (!in_array($legacy->id, $legacyapps)) {
                $appmap = new stdClass();
                $appmap->cohortid = $cohorts['2015']->id;
                $appmap->appraisalid = $legacy->id;
                $DB->insert_record('local_appraisal_cohort_apps', $appmap);
                $legacyapps[$legacy->id] = $legacy->id;
            }
            if (!in_array($legacy->appraisee_userid, $legacyusers)) {
                $appmap = new stdClass();
                $appmap->cohortid = $cohorts['2015']->id;
                $appmap->userid = $legacy->appraisee_userid;
                $DB->insert_record('local_appraisal_cohort_users', $appmap);
                $legacyusers[$legacy->appraisee_userid] = $legacy->appraisee_userid;
            }
            if (!empty($legacy->costcentre) && !in_array($legacy->costcentre, $legacyccs)) {
                $ccinfo = new stdClass();
                $ccinfo->cohortid = $cohorts['2015']->id;
                $ccinfo->costcentre = $legacy->costcentre;
                $ccinfo->started = $cohorts['2015']->availablefrom; // Default to when was available from.
                $ccinfo->locked = $cohorts['2015']->availablefrom; // Default to when was available from.
                $ccinfo->closed = $cohorts['2016']->availablefrom - 1; // Default to when next was available from minus one second.
                $ccinfo->duedate = null; // No default due date.
                $DB->insert_record('local_appraisal_cohort_ccs', $ccinfo);
                $legacyccs[$legacy->costcentre] = $legacy->costcentre;
            }
        }

        // Attach rest to 2016 cohort.
        $currentsql = "SELECT aa.id, aa.appraisee_userid, u.icq as costcentre FROM {local_appraisal_appraisal} aa JOIN {user} u ON u.id = aa.appraisee_userid WHERE aa.legacy = 0";
        $currents = $DB->get_records_sql($currentsql);
        $currentapps = $DB->get_records_menu('local_appraisal_cohort_apps', ['cohortid' => $cohorts['2016']->id], '', 'appraisalid, appraisalid');
        $currentusers = $DB->get_records_menu('local_appraisal_cohort_users', ['cohortid' => $cohorts['2016']->id], '', 'userid, userid');
        $currentccs = $DB->get_records_menu('local_appraisal_cohort_ccs', ['cohortid' => $cohorts['2016']->id], '', 'costcentre, costcentre');
        foreach ($currents as $current) {
            if (!in_array($current->id, $currentapps)) {
                $appmap = new stdClass();
                $appmap->cohortid = $cohorts['2016']->id;
                $appmap->appraisalid = $current->id;
                $DB->insert_record('local_appraisal_cohort_apps', $appmap);
                $currentapps[$current->id] = $current->id;
            }
            if (!in_array($current->appraisee_userid, $currentusers)) {
                $appmap = new stdClass();
                $appmap->cohortid = $cohorts['2016']->id;
                $appmap->userid = $current->appraisee_userid;
                $DB->insert_record('local_appraisal_cohort_users', $appmap);
                $currentusers[$current->appraisee_userid] = $current->appraisee_userid;
            }
            if (!empty($current->costcentre) && !in_array($current->costcentre, $currentccs)) {
                $ccinfo = new stdClass();
                $ccinfo->cohortid = $cohorts['2016']->id;
                $ccinfo->costcentre = $current->costcentre;
                $ccinfo->started = $cohorts['2016']->availablefrom; // Default to when was available from.
                $ccinfo->locked = $cohorts['2016']->availablefrom; // Default to when was available from.
                $ccinfo->closed = null; // Not yet closed.
                $ccinfo->duedate = null; // No default due date.
                $DB->insert_record('local_appraisal_cohort_ccs', $ccinfo);
                $currentccs[$current->costcentre] = $current->costcentre;
            }
        }

        // Onlineappraisal savepoint reached.
        upgrade_plugin_savepoint(true, 2017090305, 'local', 'onlineappraisal');
    }

    if ($oldversion < 2017090306) {
        $table = new xmldb_table('local_appraisal_users');
        // Add description field to enhance setting.
        $field = new xmldb_field('description', XMLDB_TYPE_TEXT);

        // Conditionally add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Onlineappraisal savepoint reached.
        upgrade_plugin_savepoint(true, 2017090306, 'local', 'onlineappraisal');
    }

    if ($oldversion < 2017090308) {
        // Add local_appraisal_notrequired table.
        $newtable = new xmldb_table('local_appraisal_notrequired');
        $newtable->add_field('id', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, XMLDB_SEQUENCE);
        $newtable->add_field('userid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $newtable->add_field('reason', XMLDB_TYPE_TEXT, null, null, XMLDB_NOTNULL);
        $newtable->add_field('timecreated', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $newtable->add_field('createdby', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL);
        $newtable->add_field('superseded', XMLDB_TYPE_INTEGER, 10);
        $newtable->add_field('supersededby', XMLDB_TYPE_INTEGER, 10);
        $newtable->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($newtable)) {
            $dbman->create_table($newtable);
        }

        // Migrate data.
        $now = time();
        $notrequireds = $DB->get_records('local_appraisal_users', ['setting' => 'appraisalnotrequired', 'value' => 1]);
        foreach ($notrequireds as $notrequired) {
            $newentry = new stdClass();
            $newentry->userid = $notrequired->userid;
            $newentry->reason = !empty($notrequired->description) ? $notrequired->description : '';
            $newentry->timecreated = $now;
            $newentry->createdby = get_admin()->id;
            $DB->insert_record('local_appraisal_notrequired', $newentry);
        }

        // Clear old data.
        $DB->delete_records('local_appraisal_users', ['setting' => 'appraisalnotrequired']);

        // Remove deprecated description field on settings table.
        $oldtable = new xmldb_table('local_appraisal_users');
        // Add description field to enhance setting.
        $oldfield = new xmldb_field('description');

        // Conditionally drop field.
        if ($dbman->field_exists($oldtable, $oldfield)) {
            $dbman->drop_field($oldtable, $oldfield);
        }

        // Onlineappraisal savepoint reached.
        upgrade_plugin_savepoint(true, 2017090308, 'local', 'onlineappraisal');
    }

    if ($oldversion < 2018010102) {
        $rows = $DB->get_records_select(
                'local_appraisal_data',
                '(name = :name1 OR name = :name2) AND type = :type',
                ['name1' => 'strengths', 'name2' => 'developmentareas', 'type' => 'normal']
                );
        foreach ($rows as $row) {
            $row->type = 'array';
            $row->data = serialize(json_decode($row->data));
            $DB->update_record('local_appraisal_data', $row);
        }

        // Onlineappraisal savepoint reached.
        upgrade_plugin_savepoint(true, 2018010102, 'local', 'onlineappraisal');
    }

    if ($oldversion < 2018010105) {

        // Define table local_appraisal_appraisal to be updated.
        $table = new xmldb_table('local_appraisal_appraisal');

        // Adding successionplan field to table local_appraisal_appraisal.
        $field = new xmldb_field('successionplan', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', null);

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Onlineappraisal savepoint reached.
        upgrade_plugin_savepoint(true, 2018010105, 'local', 'onlineappraisal');
    }

    if ($oldversion < 2018010107) {

        // Define table local_appraisal_appraisal to be updated.
        $table = new xmldb_table('local_appraisal_appraisal');

        // Adding leaderplan field to table local_appraisal_appraisal.
        $field = new xmldb_field('leaderplan', XMLDB_TYPE_INTEGER, '1', null, XMLDB_NOTNULL, null, '0', null);

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Rebuild permissions table and cache.
        \local_onlineappraisal\permissions::rebuild_permissions();

        // Onlineappraisal savepoint reached.
        upgrade_plugin_savepoint(true, 2018010107, 'local', 'onlineappraisal');
    }

    if ($oldversion < 2018010111) {
        // Tidy up feeback contributor emails that are not trimmed.
        $spacebefore = $DB->sql_like('email', ':spacebefore');
        $spaceafter = $DB->sql_like('email', ':spaceafter');
        $select = "{$spacebefore} OR {$spaceafter}";
        $params = [
            'spacebefore' => ' %',
            'spaceafter' => '% ',
        ];
        $requests = $DB->get_records_select('local_appraisal_feedback', $select, $params);
        foreach ($requests as $request) {
            $request->email = trim($request->email);
            $DB->update_record('local_appraisal_feedback', $request);
        }

        // Onlineappraisal savepoint reached.
        upgrade_plugin_savepoint(true, 2018010111, 'local', 'onlineappraisal');
    }

    return true;
}
