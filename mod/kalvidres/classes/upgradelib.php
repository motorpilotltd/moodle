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

namespace mod_kalvidres;
defined('MOODLE_INTERNAL') || die();

/**
 * Upgrade library class.
 *
 * @since     Moodle 3.3
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 **/
class upgradelib {
    public static function add_web_services() {
        global $DB;

        $mobileservices = $DB->get_records_select('external_services', 'shortname = :official OR shortname = :local',
                ['official' => MOODLE_OFFICIAL_MOBILE_SERVICE, 'local' => 'local_mobile']);

        $functions = ['mod_kalvidres_get_kalvidres_by_courses', 'mod_kalvidres_get_ks', 'mod_kalvidres_view_kalvidres'];

        foreach ($mobileservices as $mobileservice) {
            foreach ($functions as $function) {
                $getks = ['externalserviceid' => $mobileservice->id, 'functionname' => $function];
                if (!$DB->record_exists('external_services_functions', $getks)) {
                    $DB->insert_record('external_services_functions', (object) $getks);
                }
            }
        }
    }

    /**
     * Update kalvidres records, from duplicates, that have empty metadata.
     *
     * @throws \dml_exception
     */
    public static function update_metadata_field() {
        global $DB;
        $kalvidresdata = $DB->get_records_select('kalvidres', "metadata = NULL OR metadata = ''");
        foreach ($kalvidresdata as $item) {
            $params = ['entry_id' => $item->entry_id];
            $where = "entry_id = :entry_id AND (metadata != NULL OR metadata != '')";
            $kalvidres = $DB->get_record_select('kalvidres', $where, $params,"*", IGNORE_MULTIPLE);
            if (empty($kalvidres)) {
                continue;
            }
            $item->metadata = $kalvidres->metadata;
            $DB->update_record('kalvidres', $item);
        }
    }

    /**
     * Update kalvidres records, from API, that have empty metadata.
     *
     * @throws \dml_exception
     */
    public static function update_metadata_field_api() {
        global $DB;

        require_once($CFG->dirroot.'/local/kaltura/locallib.php');
        $client = arup_local_kaltura_get_kaltura_client();

        $kalvidresdata = $DB->get_records_select('kalvidres', "metadata = NULL OR metadata = ''");
        foreach ($kalvidresdata as $item) {
            try {
				$entry = $client->baseEntry->get($item->entry_id);
			} catch (\Exception $e) {
                debugging($e->getMessage());
				continue;
            }

            $metadata = local_kaltura_convert_kaltura_base_entry_object($entry);
            if (!$metadata) {
                continue;
            }

            // Serialize and base 64 encode the metadata.
            $item->metadata = local_kaltura_encode_object_for_storage($metadata);
            $DB->update_record('kalvidres', $item);
        }
    }
}