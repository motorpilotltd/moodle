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
 * Filter settings page
 *
 * @package    filter_regional
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

if ($ADMIN->fulltree) {

    $regions = $DB->get_records('local_regions_reg', array('userselectable' => 1), 'name ASC');
    $defaultoptions = array('default' => 'Default');
    foreach ($regions as $region) {
        $defaultoptions[strtolower(trim($region->name))] = trim($region->name);
    }
    $settings->add(new admin_setting_configselect('filter_regional/default',
                                                   get_string('default', 'filter_regional'),
                                                   get_string('default_help', 'filter_regional'),
                                                   'default',
                                                   $defaultoptions));

}
