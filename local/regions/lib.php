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
 * Regions
 * General purpose functions
 *
 * @package local_regions
 */

/***** General purpose functions *****/
function local_regions_user_check($user, $setwantsurl = true) {
    global $CFG, $DB, $SESSION, $USER;

    $update = false;
    $regionchanged = false;
    $statusflag = null;

    $userregion = $DB->get_record('local_regions_use', array('userid' => $user->id));
    // Get regions from hub.
    $query = "
        SELECT
            GEO_REGION as georegion, REGION_NAME as actregion
        FROM
            SQLHUB.ARUP_ALL_STAFF_V
        WHERE
            EMPLOYEE_NUMBER = :idnumber
    ";
    $params= ['idnumber' => (int) $user->idnumber];

    $hubregions = $DB->get_record_sql($query, $params);

    if ($hubregions) {
        $acttapsregion = local_regions_map_taps_region($hubregions->actregion);
        $geotapsregion = local_regions_map_taps_region($hubregions->georegion);
        $geotapsregionid = $geotapsregion ? $geotapsregion->id : 0;
        $acttapsregionid = $acttapsregion ? $acttapsregion->id : $geotapsregionid;
        if (!$geotapsregionid && $acttapsregionid && $acttapsregion->userselectable) {
            $regionid = $acttapsregion->id;
        } else {
            $regionid = $geotapsregionid;
        }

        if (!$userregion) {
            $regionchanged = true;
            $userregion = new stdClass();
            $userregion->userid = $user->id;
            $userregion->regionid = $regionid;
            $userregion->subregionid = 0;
            $userregion->acttapsregionid = $acttapsregionid;
            $userregion->geotapsregionid = $geotapsregionid;
            $userregion->statusflag = $statusflag;
        }

        if ($userregion->acttapsregionid != $acttapsregionid ||
                $userregion->geotapsregionid != $geotapsregionid) {
            $regionchanged = true;
            $userregion->regionid = $geotapsregionid;
            $userregion->subregionid = 0;
            $userregion->acttapsregionid = $acttapsregionid;
            $userregion->geotapsregionid = $geotapsregionid;
            $userregion->statusflag = $statusflag;
            $update = true;
        }

        if (!isset($userregion->id)) {
            $DB->insert_record('local_regions_use', $userregion);
        } else if ($update) {
            $DB->update_record('local_regions_use', $userregion);
        }
    }

    // Only actually set wantsurl if user being checked is current user.
    if ((!$userregion || $userregion->regionid == 0) && $setwantsurl && $USER->id == $user->id) {
        $SESSION->regionswantsurl = !empty($SESSION->wantsurl) ? $SESSION->wantsurl : $CFG->wwwroot;
        $SESSION->wantsurl = new moodle_url('/user/edit.php', array('id' => $user->id, 'updatelocation' => 1));
        return;
    }

    if ($regionchanged && $setwantsurl && $USER->id == $user->id) {
        $SESSION->regionswantsurl = !empty($SESSION->wantsurl) ? $SESSION->wantsurl : $CFG->wwwroot;
        $SESSION->wantsurl = new moodle_url('/user/edit.php', array('id' => $user->id, 'updatelocation' => 2));
        return;
    }

    return;
}

function local_regions_load_data_course(&$course) {
    global $DB;

    $course->regions_field_region = $DB->get_records_menu('local_regions_reg_cou', array('courseid' => $course->id), '', 'regionid as id, regionid as id2');
    if (!$course->regions_field_region) {
        $course->regions_field_region = array(0);
    }
    $course->regions_field_subregion = $DB->get_records_menu('local_regions_sub_cou', array('courseid' => $course->id), '', 'subregionid as id, subregionid as id2');
}

/**
 * @param  object   instance of the moodleform class
 */
function local_regions_definition_course(&$mform) {
    global $DB, $PAGE;

    $regionoptions =
        array(0 => get_string('global', 'local_regions')) +
        $DB->get_records_select_menu('local_regions_reg', 'userselectable = 1', array(), 'name DESC', 'id, name');
    $size = min(array(count($regionoptions), 10));
    $mform->addElement('header', 'regions_field_region_mapping', get_string('form:name:region_mapping_course', 'local_regions'));
    $mform->setExpanded('regions_field_region_mapping', true, true);
    $region = &$mform->addElement('select', 'regions_field_region', get_string('regions', 'local_regions'), $regionoptions, array('size' => $size, 'style' => 'min-width:200px'));
    $region->setMultiple(true);
    $mform->setDefault('regions_field_region', 0);

    $subregionoptions = array();
    $subregionoptions[''][''] = get_string('choosedots');
    $count = 1;
    foreach ($regionoptions as $id => $name) {
        $regionsubregions = $DB->get_records_menu('local_regions_sub', array('regionid' => $id), '', 'id, name');
        if ($regionsubregions) {
            $subregionoptions[$name] = $regionsubregions;
            $count += count($regionsubregions) + 1;
        }
    }
    $size = min(array($count, 10));
    $subregion = &$mform->addElement(
            'selectgroups',
            'regions_field_subregion',
            get_string('subregions', 'local_regions'),
            $subregionoptions,
            array('size' => $size, 'style' => 'min-width:200px')
            );
    $subregion->setMultiple(true);

    // Add JS.
    $PAGE->requires->js_call_amd('local_regions/regions_course', 'initialise');
}

/**
 * @param  object   instance of the moodleform class
 */
function local_regions_definition_after_data_course(&$mform) {
    // Not required - Retained for future use as called in core mod.
}

function local_regions_validation_course($coursenew, $files) {
    // Not required - Retained for future use as called in core mod.
    return array();
}

function local_regions_save_data_course($coursenew) {
    global $DB;
    if (in_array(0, $coursenew->regions_field_region)) {
        $DB->delete_records('local_regions_reg_cou', array('courseid' => $coursenew->id));
        $DB->delete_records('local_regions_sub_cou', array('courseid' => $coursenew->id));
    } else {
        $allregions = $DB->get_records_menu('local_regions_reg', array('userselectable' => 1), '', 'id as id, id as id2');
        $regionsubregions = array();
        if (isset($coursenew->regions_field_region)
                && is_array($coursenew->regions_field_region)
                && is_array($allregions)) {
            foreach ($allregions as $regionid) {
                if (!in_array($regionid, $coursenew->regions_field_region)) {
                    $DB->delete_records('local_regions_reg_cou', array('regionid' => $regionid, 'courseid' => $coursenew->id));
                } else {
                    $regioncourse = $DB->get_record('local_regions_reg_cou', array('regionid' => $regionid, 'courseid' => $coursenew->id));
                    if (!$regioncourse) {
                        $regioncourse = new stdClass();
                        $regioncourse->regionid = $regionid;
                        $regioncourse->courseid = $coursenew->id;
                        $DB->insert_record('local_regions_reg_cou', $regioncourse);
                    }
                }
            }

            $allsubregions = $DB->get_records_menu('local_regions_sub', array(), '', 'id as id, id as id2');
            $regionsubregions = $DB->get_records_select_menu(
                'local_regions_sub',
                'regionid IN ('.implode(',', $coursenew->regions_field_region).')',
                array(),
                '',
                'id as id, id as id2'
            );
            if (isset($coursenew->regions_field_subregion)
                    && is_array($coursenew->regions_field_subregion)
                    && is_array($allsubregions)) {
                foreach ($allsubregions as $subregionid) {
                    if (!in_array($subregionid, $coursenew->regions_field_subregion) || !in_array($subregionid, $regionsubregions)) {
                        $DB->delete_records('local_regions_sub_cou', array('subregionid' => $subregionid, 'courseid' => $coursenew->id));
                    } else {
                        $subregioncourse = $DB->get_record('local_regions_sub_cou', array('subregionid' => $subregionid, 'courseid' => $coursenew->id));
                        if (!$subregioncourse) {
                            $subregioncourse = new stdClass();
                            $subregioncourse->subregionid = $subregionid;
                            $subregioncourse->courseid = $coursenew->id;
                            $DB->insert_record('local_regions_sub_cou', $subregioncourse);
                        }
                    }
                }
            }
        }
    }
}

function local_regions_load_data_user(&$user) {
    global $DB;

    if ($user->auth != 'saml') {
        // Only SAML authenticated users should have region data.
        $DB->delete_records('local_regions_use', array('userid' => $user->id));
        return;
    }

    local_regions_user_check($user, false);
    $userregion = $DB->get_record('local_regions_use', array('userid' => $user->id));
    if ($userregion) {
        $user->regions_field_region = $userregion->regionid;
        $user->regions_field_subregion = $userregion->subregionid;
        $user->regions_field_acttapsregionid = $userregion->acttapsregionid;
        $user->regions_field_geotapsregionid = $userregion->geotapsregionid;
    }
}

/**
 * @param  object   instance of the moodleform class
 */
function local_regions_definition_user(&$mform, $userid) {
    global $DB, $PAGE, $USER;

    if ($userid != $USER->id) {
        $user = $DB->get_record('user', array('id' => $userid));
    } else {
        $user = $USER;
    }

    if (!$user || $user->auth != 'saml') {
        return;
    }

    $updatelocation = optional_param('updatelocation', 0, PARAM_INT);

    if ($updatelocation == 1) {
        $headertext = html_writer::tag(
            'span',
            get_string('form:name:region_mapping_user', 'local_regions'),
            array('class' => 'required')
        );
    } else {
        $headertext = get_string('form:name:region_mapping_user', 'local_regions');
    }

    $mform->addElement('header', 'regions_field_region_mapping', $headertext);
    $mform->setExpanded('regions_field_region_mapping', true, true);

    if ($updatelocation == 1) {
        $mform->addElement(
            'html',
             html_writer::tag('span', get_string('form:hint:region_update_location_1', 'local_regions'), array('class' => 'required'))
        );
    } else if ($updatelocation == 2) {
        $mform->addElement(
            'html',
             html_writer::tag('span', get_string('form:hint:region_update_location_2', 'local_regions'))
        );
    }

    $regionoptions = array();
    $regionoptions[0] = get_string('choosedots');
    $regionmenu = $DB->get_records_select_menu('local_regions_reg', 'userselectable = 1', array(), 'name ASC', 'id, name');
    if ($regionmenu) {
        $regionoptions = $regionoptions + $regionmenu;
    }

    $mform->addElement('select', 'regions_field_region', get_string('region', 'local_regions'), $regionoptions, array('style' => 'min-width:200px'));

    $subregionoptions = array();
    $subregionoptions[''][''] = get_string('choosedots');
    foreach ($regionoptions as $id => $name) {
        $regionsubregions = $DB->get_records_menu('local_regions_sub', array('regionid' => $id), 'name ASC', 'id, name');
        if ($regionsubregions) {
            $subregionoptions[$name] = $regionsubregions;
        }
    }
    $mform->addElement('selectgroups', 'regions_field_subregion', get_string('subregion', 'local_regions'), $subregionoptions, array('style' => 'min-width:200px'));

    if ($updatelocation) {
        $mform->addElement('submit', 'submitbutton', get_string('updatemyprofile'));
    }

    $mform->addElement('hidden', 'regions_field_acttapsregionid');
    $mform->setType('regions_field_acttapsregionid', PARAM_INT);
    $mform->addElement('hidden', 'regions_field_geotapsregionid');
    $mform->setType('regions_field_geotapsregionid', PARAM_INT);

    // Add JS.
    $PAGE->requires->js_call_amd('local_regions/regions_user', 'initialise');
}

function local_regions_definition_after_data_user(&$form, &$mform, &$user) {
    global $DB;

    if ($user->auth != 'saml') {
        return;
    }

    if (!$form->is_submitted()) {
        if ($mform->elementExists('regions_field_region')) {
            if (is_array($mform->getElementValue('regions_field_region'))) {
                $regionid = array_pop($mform->getElementValue('regions_field_region'));
            } else {
                $regionid = 0;
            }
            $geotapsregionid = $mform->getElementValue('regions_field_geotapsregionid');

            if ($regionid && $geotapsregionid && $geotapsregionid != $regionid) {
                $georegion = $DB->get_record('local_regions_reg', array('id' => $geotapsregionid));
                $georegionname = $georegion ? $georegion->name : 'unknown';
                $mform->insertElementBefore(
                    $mform->createElement('static', 'region_preferred', '', get_string('form:hint:region_preferred_set', 'local_regions', $georegionname)),
                    'regions_field_region'
                );
            } else {
                $mform->insertElementBefore(
                    $mform->createElement('static', 'region_preferred', '', get_string('form:hint:region_preferred', 'local_regions')),
                    'regions_field_region'
                );
            }
        }
    }
}

function local_regions_validation_user($usernew, $files) {
    global $DB;

    if ($DB->get_field('user', 'auth', array('id' => $usernew->id)) != 'saml') {
        return array();
    }

    $errors = array();
    if (isset($usernew->regions_field_subregion)) {
        $subregion = $DB->get_record(
            'local_regions_sub',
            array('id' => $usernew->regions_field_subregion)
        );
        if ($usernew->regions_field_region && $subregion && $subregion->regionid != $usernew->regions_field_region) {
            $errors['regions_field_subregion'] = get_string('form:error:subregionmustmatch', 'local_regions');
        }
    }

    return $errors;
}

function local_regions_save_data_user($usernew) {
    global $DB;

    if ($DB->get_field('user', 'auth', array('id' => $usernew->id)) != 'saml') {
        return array();
    }

    $userregion = $DB->get_record('local_regions_use', array('userid' => $usernew->id));

    if ($userregion) {
        $userregion->regionid = $usernew->regions_field_region;
        $userregion->subregionid = isset($usernew->regions_field_subregion) ? $usernew->regions_field_subregion : 0;
        $DB->update_record('local_regions_use', $userregion);
    } else {
        $userregion = new stdClass();
        $userregion->userid = $usernew->id;
        $userregion->regionid = $usernew->regions_field_region;
        $userregion->subregionid = isset($usernew->regions_field_subregion) ? $usernew->regions_field_subregion : 0;
        $userregion->acttapsregionid = 0;
        $userregion->geotapsregionid = 0;
        $DB->insert_record('local_regions_use', $userregion);
    }
}

function local_regions_map_taps_region($tapsregion) {
    global $DB;
    $region = $DB->get_record_select(
            'local_regions_reg',
            'name = :name OR tapsname = :tapsname',
            ['name' => $tapsregion, 'tapsname' => $tapsregion]
            );
    return $region;
}

function local_regions_user_profile($user) {
    global $DB;
    $sql = "
        SELECT
            lru.id, lru.regionid as rid, lru.acttapsregionid as arid, lru.geotapsregionid as grid,
            lrrr.name as rname, lrra.name as aname, lrrg.name as gname
        FROM {local_regions_use} lru
        JOIN {local_regions_reg} lrrr
            ON lrrr.id = lru.regionid
        LEFT JOIN {local_regions_reg} lrra
            ON lrra.id = lru.acttapsregionid
        LEFT JOIN {local_regions_reg} lrrg
            ON lrrg.id = lru.geotapsregionid
        WHERE
            lru.userid = :userid
        ";
    $params = array('userid' => $user->id);
    $userregion = $DB->get_record_sql($sql, $params);
    if (!$userregion) {
        return false;
    }
    $return = new stdClass();
    $return->left = get_string('region', 'local_regions');
    if ($userregion->arid != $userregion->grid) {
        $return->right = $userregion->aname . ' | ' . $userregion->gname;
    } else {
        $return->right = $userregion->aname;
    }
    if ($userregion->rid != $userregion->grid) {
        $return->right .= get_string('viewingas', 'local_regions', $userregion->rname);
    }
    return $return;
}

function local_regions_tidy_course_mappings() {
    global $DB;

    $tables = array('local_regions_reg_cou', 'local_regions_sub_cou');
    foreach ($tables as $table) {
        $sql = "
            DELETE FROM {{$table}}
            WHERE courseid IN (
                SELECT
                    lr.courseid
                FROM
                    {{$table}} lr
                LEFT JOIN
                    {course} c
                    ON c.id = lr.courseid
                WHERE
                    c.id IS NULL
                GROUP BY
                    lr.courseid
            )
            ";
        $DB->execute($sql);
    }
}

function local_regions_tidy_user_mappings() {
    global $DB;

    // Remove non-userselectable mappings.
    $DB->execute("
        DELETE FROM {local_regions_reg_cou}
        WHERE regionid NOT IN (
            SELECT
                id
            FROM
                {local_regions_reg}
            WHERE
                userselectable = 1
        )
        ");
    $DB->execute("
        DELETE FROM {local_regions_sub_cou}
        WHERE subregionid NOT IN (
            SELECT
                lrs.id
            FROM
                {local_regions_sub} lrs
            JOIN
                {local_regions_reg} lrr
                ON lrr.id = lrs.regionid
            WHERE
                lrr.userselectable = 1
        )
        ");
    $DB->execute("
        DELETE FROM {local_regions_use}
        WHERE regionid <> 0 AND regionid NOT IN (
            SELECT
                id
            FROM
                {local_regions_reg}
            WHERE
                userselectable = 1
        )
        ");

    // Remove mappings from non-saml authenticated users.
    $DB->execute("
        DELETE FROM {local_regions_use}
        WHERE userid IN (
            SELECT
                id
            FROM
                {user}
            WHERE
                auth <> 'saml'
        )
        ");
}

