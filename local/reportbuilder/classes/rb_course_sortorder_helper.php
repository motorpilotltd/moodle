<?php
/*
 * This file is part of Totara Learn
 *
 * Copyright (C) 2018 onwards Totara Learning Solutions LTD
 *
 * This certification is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This certification is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this certification.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Sam Hemelryk <sam.hemelryk@totaralearning.com>
 * @package totara_certification
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Report Builder certification course sortorder helper
 *
 * This class is designed to aid report builder report sources that are displaying concatenated
 * course information and need to ensure that the courses are correctly ordered across all columns.
 *
 * This class also acts as a cache data source so that it can seamlessly load
 *
 * @internal
 */
final class rb_course_sortorder_helper implements \cache_data_source {

    /**
     * Returns a string to use as the field argument for an rb_column_option instance.
     *
     * @param string $field
     * @return string
     */
    public static function get_column_field_definition($field) {
        global $DB;
        return 'COALESCE(' . $DB->sql_concat('certif.id', "'|'", 'course.id', "'|'", 'COALESCE(' . $field . ', \'-\')') . ', \'-\')';
    }

    /**
     * Invalidates the required cache when certification content is updated.
     *
     * @param event\certification_contentupdated $event
     */
    public static function handle_certification_contentupdated(event\certification_contentupdated $event) {
        $certificationid = $event->objectid;
        $cache = self::get_cache();
        $cache->delete($certificationid);
    }

    /**
     * Invalidates the required cache when a certification is deleted.
     *
     * @param event\certification_contentupdated $event
     */
    public static function handle_certification_deleted(event\certification_deleted $event) {
        $certificationid = $event->objectid;
        $cache = self::get_cache();
        $cache->delete($certificationid);
    }

    /**
     * Returns the course sortorder for the certification with the given id.
     *
     * The cache uses a data source, as such the request to get data will never fail.
     * If the cache does not contain the required data then {@see self::load_for_cache()} will be
     * called to load it.
     *
     * @param int $certificationid
     * @return int[]
     */
    public static function get_sortorder($certificationid) {
        $cache = self::get_cache();
        return $cache->get($certificationid);
    }

    /**
     * Returns an instance of the course order cache.
     *
     * @return \cache_loader
     */
    private static function get_cache() {
        return \cache::make('totara_certification', 'course_order');
    }

    /**
     * Loads the data for the given certification so that it can be cached and returned.
     *
     * This is part of cache data source interface.
     *
     * @param int|string $certificationid
     * @return int[]
     */
    public function load_for_cache($certificationid) {
        global $DB;
        $sql = 'SELECT pcc.id, pcc.courseid
                  FROM {certif_courseset_courses} pcc
                  JOIN {certif_coursesets} pc ON pcc.coursesetid = pc.id
                WHERE pc.certifid = :certificationid
                ORDER BY pc.sortorder ASC, pcc.id ASC';
        $params = ['certificationid' => $certificationid];
        $order = $DB->get_records_sql_menu($sql, $params);
        return $order;
    }

    /**
     * Loads the data for all of the given certifications so that it can be cached and returned.
     *
     * This is part of cache data source interface.
     *
     * @param array $keys
     * @return int[]
     */
    public function load_many_for_cache(array $keys) {
        global $DB;

        $return = [];
        // Ensure all keys are present, even if we don't get a result from the database we have a result that we want to store.
        foreach ($keys as $key) {
            $return[$key] = [];
        }

        list ($certificationidin, $params) = $DB->get_in_or_equal($keys, SQL_PARAMS_NAMED);
        $sql = "SELECT pc.certifid, pcc.id, pcc.courseid
                  FROM {certif_courseset_courses} pcc
                  JOIN {certif_coursesets} pc ON pcc.coursesetid = pc.id
                WHERE pc.certificationid {$certificationidin}
                ORDER BY pc.certificationid, pc.sortorder ASC, pcc.id ASC";
        $result = $DB->get_records_sql($sql, $params);

        foreach ($result as $row) {
            $certificationid = $row->certifid;
            $coursesetid = $row->courseid;
            $courseid = $row->id;

            $return[$certificationid][$coursesetid] = $courseid;
        }

        return $return;
    }

    /**
     * Returns an instance of self for the cache system.
     *
     * @param \cache_definition $definition
     * @return rb_course_sortorder_helper
     */
    public static function get_instance_for_cache(\cache_definition $definition) {
        return new rb_course_sortorder_helper;
    }
}
