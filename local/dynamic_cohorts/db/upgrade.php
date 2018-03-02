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


    return true;
}