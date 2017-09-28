<?php

function xmldb_local_custom_certification_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2017011702) {
        $table = new xmldb_table('certif_completions');
        $field = new xmldb_field('progress', XMLDB_TYPE_INTEGER, 3, null, null, null, 0);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }
    
    if ($oldversion < 2017022202) {
        $table = new xmldb_table('certif_completions');
        $field = new xmldb_field('cronchecked', XMLDB_TYPE_INTEGER, 1, null, null, null, 0);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2017032900) {
        /**
         * Create pivot table to keep data about assignments for single user
         */
        $table = new xmldb_table('certif_assignments_users');
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null, null);
        $table->add_field('certifid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null);
        $table->add_field('assignmentid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null);
        $table->add_field('userid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, null, null);

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }

        $index = new xmldb_index('certifid', XMLDB_INDEX_NOTUNIQUE, array('certifid'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        $index = new xmldb_index('assignmentid', XMLDB_INDEX_NOTUNIQUE, array('assignmentid'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }

        $index = new xmldb_index('userid', XMLDB_INDEX_NOTUNIQUE, array('userid'));
        if (!$dbman->index_exists($table, $index)) {
            $dbman->add_index($table, $index);
        }
    }

    if ($oldversion < 2017032901) {
        $table = new xmldb_table('certif_user_assignments');
        $field = new xmldb_field('optional', XMLDB_TYPE_INTEGER, 1, null, null, null, 0);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2017032902) {
        // Add TAPS enrolment/CPD tracking fields to completions tables.
        $tables = [
            new xmldb_table('certif_completions'),
            new xmldb_table('certif_completions_archive'),
        ];
        $fields = [
            new xmldb_field('tapsenrolmentid', XMLDB_TYPE_INTEGER, 10),
            new xmldb_field('tapscpdid', XMLDB_TYPE_INTEGER, 10),
        ];
        foreach ($tables as $table) {
            foreach ($fields as $field) {
                if (!$dbman->field_exists($table, $field)) {
                    $dbman->add_field($table, $field);
                }
            }
        }

        // Add field to link certificate to TAPS course.
        $table = new xmldb_table('certif');
        $field = new xmldb_field('linkedtapscourseid', XMLDB_TYPE_INTEGER, 10);
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2017032902, 'local', 'custom_certification');
    }

    if ($oldversion < 2017032904) {
        // Update field to link certificate to multiple TAPS courses.
        $table = new xmldb_table('certif');
        $field = new xmldb_field('linkedtapscourseid', XMLDB_TYPE_TEXT);
        if ($dbman->field_exists($table, $field)) {
            $dbman->change_field_type($table, $field);
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2017032904, 'local', 'custom_certification');
    }

    if ($oldversion < 2017032908) {
        // Update field to link certificate to multiple TAPS courses.
        $table = new xmldb_table('certif');
        $fields = [
            new xmldb_field('uservisible', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, 1),
            new xmldb_field('reportvisible', XMLDB_TYPE_INTEGER, 1, null, XMLDB_NOTNULL, null, 1)
        ];
        foreach ($fields as $field) {
            if (!$dbman->field_exists($table, $field)) {
                $dbman->add_field($table, $field);
            }
        }

        // Savepoint reached.
        upgrade_plugin_savepoint(true, 2017032908, 'local', 'custom_certification');
    }

    return true;
}