<?php
/*
 * This file is part of Totara LMS
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
 * @author Simon Coggins <simon.coggins@totaralms.com>
 * @package totara
 * @subpackage local_reportbuilder
 */

/*
 * This file contains general purpose utility functions
 */

// Constants defined to be used in totara_search_for_value function.
define('TOTARA_SEARCH_OP_EQUAL', 0);
define('TOTARA_SEARCH_OP_NOT_EQUAL', 1);
define('TOTARA_SEARCH_OP_GREATER_THAN', 2);
define('TOTARA_SEARCH_OP_GREATER_THAN_OR_EQUAL', 3);
define('TOTARA_SEARCH_OP_LESS_THAN', 4);
define('TOTARA_SEARCH_OP_LESS_THAN_OR_EQUAL', 5);

// Type of icon.
define ('TOTARA_ICON_TYPE_COURSE', 'course');
define ('TOTARA_ICON_TYPE_PROGRAM', 'program');

/**
 * Using a timestamp return a natural language string describing the
 * timestamp relative to the current time provided by the web server.
 *
 * @param integer $timestamp Describes the time in a timestamp.
 * @param integer $compare_to Describes what time the comparison should be made against.
 * @param boolean $return_date Return a date rather that a years relative time.
 * @return string Natural language string describing the time difference.
 */
function local_reportbuilder_get_relative_time_text ($timestamp, $compare_to = null, $return_date = false) {

    $relative_time = '';

    if (!$timestamp) {
        return '';
    }

    if (!$compare_to) {
        $compare_to = time();
    }

    // Get a nice natural language string that says when the course was last accessed.
    if ($timestamp >= strtotime('-5 minutes', $compare_to)) {
        $relative_time = get_string('relative_time_five_minutes', 'local_reportbuilder');
    } else if ($timestamp >= strtotime('-30 minutes', $compare_to)) {
        $relative_time = get_string('relative_time_half_hour', 'local_reportbuilder');
    } else if ($timestamp >= strtotime('-1 hour', $compare_to)) {
        $relative_time = get_string('relative_time_hour', 'local_reportbuilder');
    } else if ($timestamp >= strtotime('today', $compare_to)) {
        $relative_time = date_format_string($timestamp, get_string('strftimetodayattime', 'core_langconfig'));
    } else if ($timestamp >= strtotime('yesterday', $compare_to)) {
        $relative_time = date_format_string($timestamp, get_string('strftimeyesterdayattime', 'core_langconfig'));
    } else if ($timestamp >= strtotime('-6 days', $compare_to)) {
        $relative_time = date_format_string($timestamp, get_string('strftimedayattime', 'core_langconfig'));
    } else if ($timestamp >= strtotime('-1 month', $compare_to)) {
        $days = floor(($compare_to - $timestamp) / DAYSECS);
        $relative_time = get_string('relative_time_days', 'local_reportbuilder', $days);
    } else if ($timestamp >= strtotime('-2 years', $compare_to)) {
        $months = floor(($compare_to - $timestamp) / (DAYSECS * 30.5));

        if ($months == 1) {
            $relative_time = get_string('relative_time_month', 'local_reportbuilder');
        } else {
            $relative_time = get_string('relative_time_months', 'local_reportbuilder', $months);
        }
    } else if (!$return_date) {
        $years = floor(($compare_to - $timestamp) / YEARSECS);

        $relative_time = get_string('relative_time_years', 'local_reportbuilder', $years);
    } else {
        $relative_time = date_format_string($timestamp, get_string('strftimedaydateattime', 'core_langconfig'));
    }

    return $relative_time;
}

