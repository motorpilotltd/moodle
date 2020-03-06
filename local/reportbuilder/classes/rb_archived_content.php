<?php
/*
 * This file is part of T0tara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
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
 * @author Oleg Demeshev <oleg.demeshev@totaralms.com>
 * @package totara
 * @subpackage reportbuilder
 */

/**
 * Restrict content by the session roles
 * Pass in an integer list that represents the session role ids
 */
class rb_archived_content extends rb_base_content {

    /**
     * Generate the SQL to apply this content restriction
     *
     * @param string $field SQL field to apply the restriction against
     * @param integer $reportid ID of the report
     *
     * @return array containing SQL snippet to be used in a WHERE clause, as well as array of SQL params
     */
    public function sql_restriction($field, $reportid) {
        $params = array();
        $norestriction = array(" 1=1 ", $params); // No restrictions.
        $restriction = array("($field = '' OR $field IS NULL )", $params);

        $type = substr(get_class($this), 3);
        $excludearchivedcontent = reportbuilder::get_setting($reportid, $type, 'excludearchivedcontent');
        if (!$excludearchivedcontent) {
            return $norestriction;
        }
        $enable = reportbuilder::get_setting($reportid, $type, 'enable');
        if (!$enable) {
            return $norestriction;
        }

        return $restriction;
    }

    /**
     * Generate a human-readable text string describing the restriction
     *
     * @param string $title Name of the field being restricted
     * @param integer $reportid ID of the report
     *
     * @return string Human readable description of the restriction
     */
    public function text_restriction($title, $reportid) {
        return get_string('excludearchivedcontent', 'local_reportbuilder');
    }

    /**
     * Adds form elements required for this content restriction's settings page
     *
     * @param object &$mform Moodle form object to modify (passed by reference)
     * @param integer $reportid ID of the report being adjusted
     * @param string $title Name of the field the restriction is acting on
     */
    public function form_template(&$mform, $reportid, $title) {
        $type = substr(get_class($this), 3);
        $enable = reportbuilder::get_setting($reportid, $type, 'enable');
        $excludearchivedcontent = reportbuilder::get_setting($reportid, $type, 'excludearchivedcontent');

        $mform->addElement('header', 'excludearchivedcontenthdr', get_string('excludearchivedcontent', 'local_reportbuilder'));

        $mform->addElement('checkbox', 'enable', get_string('enable'));
        $mform->addElement('checkbox', 'excludearchivedcontent', get_string('excludearchivedcontent', 'local_reportbuilder'));

        $mform->disabledIf('excludearchivedcontent', 'enable', 'notchecked');

        $mform->setDefault('excludearchivedcontent', $excludearchivedcontent);
        $mform->setDefault('enable', $enable);
    }

    /**
     * Processes the form elements created by {@link form_template()}
     *
     * @param integer $reportid ID of the report to process
     * @param object $fromform Moodle form data received via form submission
     *
     * @return boolean True if form was successfully processed
     */
    public function form_process($reportid, $fromform) {
        $type = substr(get_class($this), 3);
        $status = true;
        $status = $status && reportbuilder::update_setting($reportid, $type, 'excludearchivedcontent', !empty($fromform->excludearchivedcontent));
        $status = $status && reportbuilder::update_setting($reportid, $type, 'enable', !empty($fromform->enable));
        return $status;
    }
}