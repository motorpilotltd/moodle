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
 * @author Jonathan Newman <jonathan.newman@catalyst.net.nz>
 * @package totara
 * @subpackage local_reportbuilder
 */

defined('MOODLE_INTERNAL') || die();
$systemcontext = context_system::instance();


global $TEXTAREA_OPTIONS;
$TEXTAREA_OPTIONS = [
        'subdirs' => 0,
        'maxfiles' => -1,
        'maxbytes' => get_max_upload_file_size(),
        'trusttext' => false,
        'context' => $systemcontext,
        'collapsed' => true
];




/**
 * Get the where clause sql fragment and parameters needed to restrict an sql query to only those courses or
 * programs available to a user.
 *
 * sqlparams in return are SQL_PARAMS_NAMED, so queries built using this function must also use named params.
 *
 * !!! Your query must join to the context table, with alias "ctx" !!!
 *
 * Note that currently, if using normal visibility, hidden items will not show in the RoL for a learner, but
 * it will show in their Required Learning, is accessible, and they are processed for completion. All other
 * places which display learning items are limited to those that are not hidden. We may want to change this.
 * For example, f2f calendar items, appraisal questions, recent learning, user course completion report,
 * enrol_get_my_courses, ... Basically we should check every call to this function.
 *
 * @param int $userid The user that the results should be restricted for. Defaults to current user.
 * @param string $fieldbaseid The field in the base sql query which this query can link to.
 * @param string $fieldvisible The field in the base sql query which contains the visible property.
 * @param string $fieldaudvis The field in the base sql query which contains the audiencevisibile property.
 * @param string $tablealias The alias for the base table (This is used mainly for programs and cert which has available field)
 * @param string $type course, program or certification.
 * @param bool $iscached True if the fields passed comes from a report which data has been cached.
 * @param bool $showhidden If using normal visibility, show items even if they are hidden.
 * @return array(sqlstring, array(sqlparams))
 */
function totara_visibility_where($userid = null, $fieldvisible = 'course.visible',
        $type = 'course', $iscached = false,
        $showhidden = false) {
    global $CFG, $USER;

    if ($userid === null) {
        $userid = $USER->id;
    }

    // Initialize availability variables, needed for programs and certifications.
    $availabilitysql = '1=1';
    $availabilityparams = array();
    $separator = ($iscached) ? '_' : '.'; // When the report is caches its fields comes in type_value form.
    $systemcontext = context_system::instance();

    // Evaluate capabilities.
    switch($type) {
        case 'course':
            $capability = 'moodle/course:viewhiddencourses';
            break;
        case 'certification':
            $capability = 'local/custom_certification:viewhiddencertifications';
            break;
    }

    if (is_siteadmin($userid)) {
        // Admins can see all records no matter what the visibility.
        return array('1=1', array());

    } else {
        if ($showhidden || has_capability($capability, $systemcontext, $userid)) {
            return array('1=1', array());
        } else {
            // Normal visibility unless they have the capability to see hidden learning components.
            $sqlnormalvisible = "
            (({$fieldvisible} = :tcvwnormalvisible) OR
             ({$fieldvisible} = :tcvwnormalvisiblenone AND
                 EXISTS (
                     SELECT 1
                     FROM {role_assignments} ra
                     INNER JOIN {role_capabilities} rc on rc.roleid = ra.roleid
                     WHERE ra.contextid = ctx{$separator}id
                       AND ra.userid = :tcvuseridnormalvisibility
                       AND rc.capability = :hiddencapability
                       AND rc.permission = 1
                   )
             ))";
            $params = array(
                    'tcvwnormalvisible' => 1,
                    'tcvwnormalvisiblenone' => 0,
                    'hiddencapability' => $capability,
                    'tcvuseridnormalvisibility' => $userid,
                    'hiddencapability' => $capability,
            );

            // Add availability sql.
            if ($availabilitysql != '1=1') {
                $sqlnormalvisible .= " AND {$availabilitysql} ";
                $params = array_merge($params, $availabilityparams);
            }

            return array($sqlnormalvisible, $params);
        }
    }
}


/**
 * Returns the style css name for the component's visibility.
 *
 * @param stdClass $component Component (Course, Program, Certification) object
 * @param string $oldvisfield Old visibility field
 * @param string $audvisfield Audience visibility field
 * @return string $dimmed Css class name
 */
function totara_get_style_visibility($component, $oldvisfield = 'visible') {
    $dimmed = '';

    if (!is_object($component)) {
        return $dimmed;
    }
    if (isset($component->{$oldvisfield}) && !$component->{$oldvisfield}) {
        $dimmed = 'dimmed';
    }

    return $dimmed;
}