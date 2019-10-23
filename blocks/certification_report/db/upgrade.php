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

    if ($oldversion < 2017033105) {
        // Define table certif_links to be created.
        $table = new xmldb_table('certif_links');

        // Adding fields to table certif_links.
        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('name', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('linkurl', XMLDB_TYPE_CHAR, '255', null, null, null, null);
        $table->add_field('geographicregionid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('actualregionid', XMLDB_TYPE_INTEGER, '10', null, null, null, null);
        $table->add_field('timecreated', XMLDB_TYPE_INTEGER, '10', null, null, null, '0');

        // Adding keys to table certif_links.
        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for certif_links.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);
        }
    }

    if ($oldversion < 2017033106) {
        // Define table certif_links.
        $table = new xmldb_table('certif_links');

        $field = new xmldb_field('linkurl', XMLDB_TYPE_TEXT, null, null, null, null, null, 'name');

        // Launch change of precision for field linkurl.
        $dbman->change_field_precision($table, $field);

        // Launch change of type for field linkurl.
        $dbman->change_field_type($table, $field);
    }

    if ($oldversion < 2017033107) {
        // Clear deprecated config settings.
        set_config('report_category', null, 'block_certification_report');
        set_config('report_certifications', null, 'block_certification_report');
        set_config('report_title', null, 'block_certification_report');
    }

    return true;
}