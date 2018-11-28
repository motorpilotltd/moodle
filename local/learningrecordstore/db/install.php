<?php

function xmldb_local_learningrecordstore_install() {
    global $DB;

    $dbman = $DB->get_manager(); // Loads ddl manager and xmldb classes.

    $table = new xmldb_table('local_taps_enrolment');

    $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
    $table->add_field('staffid', XMLDB_TYPE_CHAR, '10', null, XMLDB_NOTNULL);
    $table->add_field('enrolmentid', XMLDB_TYPE_INTEGER, '10');
    $table->add_field('classid', XMLDB_TYPE_INTEGER, '10');
    $table->add_field('classname', XMLDB_TYPE_TEXT);
    $table->add_field('courseid', XMLDB_TYPE_INTEGER, '10');
    $table->add_field('coursename', XMLDB_TYPE_TEXT);
    $table->add_field('location', XMLDB_TYPE_TEXT);
    $table->add_field('classtype', XMLDB_TYPE_TEXT);
    $table->add_field('classcategory', XMLDB_TYPE_TEXT);
    $table->add_field('classstartdate', XMLDB_TYPE_INTEGER, '10');
    $table->add_field('classenddate', XMLDB_TYPE_INTEGER, '10');
    $table->add_field('duration', XMLDB_TYPE_FLOAT);
    $table->add_field('durationunits', XMLDB_TYPE_TEXT);
    $table->add_field('durationunitscode', XMLDB_TYPE_TEXT);
    $table->add_field('provider', XMLDB_TYPE_TEXT);
    $table->add_field('certificateno', XMLDB_TYPE_TEXT);
    $table->add_field('expirydate', XMLDB_TYPE_INTEGER, '10');
    $table->add_field('bookingstatus', XMLDB_TYPE_TEXT);
    $table->add_field('classstarttime', XMLDB_TYPE_INTEGER, '10');
    $table->add_field('classendtime', XMLDB_TYPE_INTEGER, '10');
    $table->add_field('completiontime', XMLDB_TYPE_INTEGER, '10');
    $table->add_field('healthandsafetycategory', XMLDB_TYPE_TEXT);
    $table->add_field('classcost', XMLDB_TYPE_NUMBER, 20);
    $table->add_field('classcostcurrency', XMLDB_TYPE_TEXT);
    $table->add_field('learningdesc', XMLDB_TYPE_TEXT);
    $table->add_field('timezone', XMLDB_TYPE_TEXT);
    $table->add_field('usedtimezone', XMLDB_TYPE_CHAR, '255', null, XMLDB_NOTNULL, false, 'UTC');
    $table->add_field('pricebasis', XMLDB_TYPE_TEXT);
    $table->add_field('currencycode', XMLDB_TYPE_TEXT);
    $table->add_field('price', XMLDB_TYPE_NUMBER, 20);
    $table->add_field('trainingcenter', XMLDB_TYPE_TEXT);
    $table->add_field('bookingplaceddate', XMLDB_TYPE_INTEGER, '10');
    $table->add_field('active', XMLDB_TYPE_INTEGER, '4', null, XMLDB_NOTNULL, false, 1);
    $table->add_field('archived', XMLDB_TYPE_INTEGER, '4');
    $table->add_field('timemodified', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL);

    $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));
    $table->add_key('enrolmentid_cpdid', XMLDB_KEY_UNIQUE, array('enrolmentid'));

    $table->add_index('staffid', XMLDB_INDEX_NOTUNIQUE, array('staffid'));

    if (!$dbman->table_exists($table)) {
        $dbman->create_table($table);
    }

}
