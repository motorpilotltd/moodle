<?php
/*
 * This file is part of T0tara LMS
 *
 * Copyright (C) 2010 onwards T0tara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Oleg Demeshev <oleg.demeshev@t0taralms.com>
 * @package t0tara
 * @subpackage reportbuilder
 */

defined('MOODLE_INTERNAL') || die();

class local_reportbuilder_observer {

    /**
     * Event that is triggered when a user is deleted.
     *
     * Removes an user from any scheduled reports they are associated with, tables to clear are
     * report_builder_schd_eml_aud
     * report_builder_schd_eml_user
     * report_builder_schd_eml_ext
     * report_builder_schedule
     *
     * @param \core\event\user_deleted $event
     *
     */
    public static function user_deleted(\core\event\user_deleted $event) {
        global $DB;

        $userid = $event->objectid;

        $transaction = $DB->start_delegated_transaction();

        // If user is an owner of scheduled reports, delete all scheduled reports.
        $reports = $DB->get_records('report_builder_schedule', array('userid' => $userid), 'id', 'id, reportid');
        foreach ($reports as $report) {
            $DB->delete_records('report_builder_schd_eml_aud',   array('scheduleid' => $report->id));
            $DB->delete_records('report_builder_schd_eml_user', array('scheduleid' => $report->id));
            $DB->delete_records('report_builder_schd_eml_ext',   array('scheduleid' => $report->id));
            $DB->delete_records('report_builder_schedule', array('id' => $report->id));
        }
        // Remove the system user from scheduled reports.
        $DB->delete_records('report_builder_schd_eml_user', array('userid' => $userid));

        $transaction->allow_commit();
    }
}
