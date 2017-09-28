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
 * Version details
 *
 * @package    block_yammer
 * @copyright  2014 Catalyst EU
 * @author     Chris Wharton <chris.wharton@catalyst-eu.net>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    // The remote location of the Yammer embed javascript.
    $settings->add(new admin_setting_configtext(
        'yammer/scriptsource',
        get_string('scriptsource', 'block_yammer'),
        get_string('scriptsource_desc', 'block_yammer'),
        'https://assets.yammer.com/assets/platform_embed.js',
        PARAM_URL
    ));
    // The default yammer network for new block instances.
    $settings->add(new admin_setting_configtext(
        'yammer/defaultnetwork',
        get_string('defaultnetwork', 'block_yammer'),
        get_string('defaultnetwork_desc', 'block_yammer'),
        '',
        PARAM_TEXT
    ));
}
