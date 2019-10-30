<?php
// This file is part of Moodle - http://moodle.org/
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
 * Observer class containing methods monitoring various events.
 *
 * @package    local_accordion
 * @copyright  2019 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_accordion;

defined('MOODLE_INTERNAL') || die();

/**
 * Observer class containing methods monitoring various events.
 *
 * @since      Moodle 3.3
 * @package    local_accordion
 * @copyright  2019 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class eventobservers {
    /**
     * Triggered via:
     * \core\event\course_updated event.
     *
     * @param stdClass $event
     * @return void
     */
    public static function course_updated(\core\event\course_updated $event) {
        global $DB;

        $cache = cache::make('local_accordion', 'course_info');

        $cachedata = ['regions' => '', 'metadata' => []];

        if (REGIONS_INSTALLED) {
            $regionssql = "
                SELECT lrrc.regionid, lrr.name
                  FROM {local_regions_reg_cou} lrrc
                  JOIN {local_regions_reg} lrr
                       ON lrr.id = lrrc.regionid
                 WHERE lrrc.courseid = {$event->objectid}
                ";
            $regions = $DB->get_records_sql_menu($regionssql);
            $cachedata['regions'] = implode(', ', $regions);
        }
        if (COURSEMETADATA_INSTALLED) {
            $metadatafields = get_config('local_accordion', 'coursemetadata_info');
            if ($metadatafields) {
                $metadatasql = "
                    SELECT cif.id, cif.name, cid.data
                      FROM {coursemetadata_info_field} cif
                      JOIN {coursemetadata_info_category} cic
                           ON cic.id = cif.categoryid
                 LEFT JOIN {coursemetadata_info_data} cid
                           ON cid.fieldid = cif.id
                               AND cid.course = {$event->objectid}
                     WHERE cif.id IN ({$metadatafields})
                  ORDER BY cic.sortorder ASC, cif.sortorder ASC
                    ";
                    $cachedata['metadata'] = $DB->get_records_sql($metadatasql);
            }
        }
        $cache->set($event->objectid, $cachedata);
    }
}