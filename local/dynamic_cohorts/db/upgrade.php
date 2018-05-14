<?php

function xmldb_local_dynamic_cohorts_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2018020101) {
        $table = new xmldb_table('wa_cohort_roles');
        $field = new xmldb_field('contextid', XMLDB_TYPE_INTEGER, 1, null, null, null, 1);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    if ($oldversion < 2018040604) {
        // Update existing records.
        $sql = 'UPDATE {wa_cohort_roles} SET contextid = :syscontextid WHERE contextid IS NULL';
        $DB->execute($sql, ['syscontextid' => SYSCONTEXTID]);

        // Increase size of field and make not null to match install file. Also update default.
        $table = new xmldb_table('wa_cohort_roles');
        $field = new xmldb_field('contextid', XMLDB_TYPE_INTEGER, 10, null, XMLDB_NOTNULL, null, SYSCONTEXTID);

        if ($dbman->field_exists($table, $field)) {
            // Will do the size and NOT NULL settings, other calls are just wrappers for this.
            $dbman->change_field_type($table, $field);
            $dbman->change_field_default($table, $field);
        }
    }

    return true;
}