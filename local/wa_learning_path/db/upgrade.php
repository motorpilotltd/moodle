<?php

/**
 * Upgrade code for install.
 *
 * @package     local_wa_learning_path
 * @copyright   Bartosz Hornik <bartosz.hornik@webanywhere.co.uk>
 * @copyright   2016 Webanywhere (http://www.webanywhere.co.uk)
 */

/**
 * Upgrade wa_learning_path plugin
 * @param int $oldversion The old version of the assign module
 * @return bool
 */
function xmldb_local_wa_learning_path_upgrade($oldversion) {
    global $CFG, $DB, $OUTPUT;

    if ($oldversion < 2016050400) {
        $dbman = $DB->get_manager();

        // Define field to be added to assign.
        $table = new xmldb_table('wa_learning_path');
        $field = new xmldb_field('matrix', XMLDB_TYPE_TEXT, 'medium', null, null, null, null);

        // Conditionally launch add field.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Assign savepoint reached.
        upgrade_plugin_savepoint(true, 2016050400, 'local', 'wa_learning_path');
    }

    if ($oldversion < 2016061403) {
        $dbman = $DB->get_manager();

        $table = new xmldb_table('wa_learning_path_region');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('learningpathid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('regionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for assign_user_mapping.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);

            $index = new xmldb_index('wa_lp_inde_learningpathid', XMLDB_INDEX_NOTUNIQUE, array('learningpathid'));
            $dbman->add_index($table, $index);

            $index = new xmldb_index('wa_lp_inde_regionid', XMLDB_INDEX_NOTUNIQUE, array('regionid'));
            $dbman->add_index($table, $index);
        }

        // Assign savepoint reached.
        upgrade_plugin_savepoint(true, 2016061403, 'local', 'wa_learning_path');
    }

    if ($oldversion < 2016061404) {
        $dbman = $DB->get_manager();

        $table = new xmldb_table('wa_learning_path');
        $field = new xmldb_field('region', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'nosubmissions');

        if ($dbman->field_exists($table, $field)) {
            $dbman->drop_field($table, $field);
        }

        // Assign savepoint reached.
        upgrade_plugin_savepoint(true, 2016061404, 'local', 'wa_learning_path');
    }
    
    if ($oldversion < 2016061507) {
//        die('aa');
        $dbman = $DB->get_manager();

        $table = new xmldb_table('wa_learning_path_act_region');

        $table->add_field('id', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, XMLDB_SEQUENCE, null);
        $table->add_field('activityid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');
        $table->add_field('regionid', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0');

        $table->add_key('primary', XMLDB_KEY_PRIMARY, array('id'));

        // Conditionally launch create table for wa_learning_path_act_region.
        if (!$dbman->table_exists($table)) {
            $dbman->create_table($table);

            $index = new xmldb_index('wa_lpar_inde_activityid', XMLDB_INDEX_NOTUNIQUE, array('activityid'));
            $dbman->add_index($table, $index);

            $index = new xmldb_index('wa_lpar_inde_regionid', XMLDB_INDEX_NOTUNIQUE, array('regionid'));
            $dbman->add_index($table, $index);
        }
        //=============
        // And delete the old columns.
        
        $table2 = new xmldb_table('wa_learning_path_activity');
		if ($dbman->table_exists($table2)) {
			$index = new xmldb_index('wa_lpa_index_region', XMLDB_INDEX_NOTUNIQUE, array('region'));

			// Conditionally launch drop index mailed.
			if ($dbman->index_exists($table2, $index)) {
				$dbman->drop_index($table2, $index);
			}

			// Drop column.
			$field = new xmldb_field('region', XMLDB_TYPE_INTEGER, '10', null, XMLDB_NOTNULL, null, '0', 'nosubmissions');

			if ($dbman->field_exists($table2, $field)) {
				$dbman->drop_field($table2, $field);
			}
		}
        // Assign savepoint reached.
        upgrade_plugin_savepoint(true, 2016061507, 'local', 'wa_learning_path');
    }


    return true;
}
