<?php

function xmldb_block_certification_report_upgrade($oldversion) {
    global $DB;
    $dbman = $DB->get_manager();

    if ($oldversion < 2017032000) {
        $table = new xmldb_table('certif_exemptions');
        $field = new xmldb_field('archived', XMLDB_TYPE_INTEGER, 1, null, null, null, 0);

        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }
    }

    return true;
}