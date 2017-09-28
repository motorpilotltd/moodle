<?php

function xmldb_block_carousel_upgrade($oldversion) {
    global $CFG, $DB;

    $dbman = $DB->get_manager();

    if ($oldversion < 2016031800) {

        // Define field layout to be added to block_carousel.
        $table = new xmldb_table('block_carousel');
        $field = new xmldb_field('layout', XMLDB_TYPE_CHAR, '254', null, null, null, null, 'name');

        // Conditionally launch add field layout.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Carousel savepoint reached.
        upgrade_block_savepoint(true, 2016031800, 'carousel');
    }

    if ($oldversion < 2016031801) {

        // Define field opacity to be added to block_carousel_item.
        $table = new xmldb_table('block_carousel_item');
        $field = new xmldb_field('opacity', XMLDB_TYPE_CHAR, '254', null, null, null, null, 'display');

        // Conditionally launch add field opacity.
        if (!$dbman->field_exists($table, $field)) {
            $dbman->add_field($table, $field);
        }

        // Carousel savepoint reached.
        upgrade_block_savepoint(true, 2016031801, 'carousel');
    }

    if ($oldversion < 2016031802) {
        // Update old image field entries from img tags to straight URLs.
        $items = $DB->get_records('block_carousel_item');
        foreach ($items as $item) {
            $item->image = str_replace('<img src="', '', $item->image);
            $item->image = str_replace('" />', '', $item->image);
            $item->image = trim($item->image);
            $DB->update_record('block_carousel_item', $item);
        }

        // Carousel savepoint reached.
        upgrade_block_savepoint(true, 2016031802, 'carousel');
    }
    return true;
}
