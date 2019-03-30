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
 * @author Andrew Hancox <andrewdchancox@googlemail.com>
 * @package mod
 * @subpackage dsa
 */

defined('MOODLE_INTERNAL') || die();

if ($ADMIN->fulltree) {
    $settings = new admin_settingpage('mod_dsa', get_string('pluginname', 'mod_dsa'));

    $settings->add(new admin_setting_configtext(
            'mod_dsa/apihost',
            new lang_string('apihost', 'mod_dsa'),
            new lang_string('apihost', 'mod_dsa'),
            'https://assessment.intranet.arup.com',
            PARAM_URL
    ));
    $settings->add(new admin_setting_configtext(
            'mod_dsa/username',
            new lang_string('username', 'mod_dsa'),
            new lang_string('username', 'mod_dsa'),
            'Moodle',
            PARAM_TEXT
    ));
    $settings->add(new admin_setting_configtext(
            'mod_dsa/key',
            new lang_string('key', 'mod_dsa'),
            new lang_string('key', 'mod_dsa'),
            '3e4fba1426d86692f6ebacf43d55ca9c',
            PARAM_TEXT
    ));
    $settings->add(new admin_setting_configtext(
            'mod_dsa/redirecturl',
            new lang_string('redirecturl', 'mod_dsa'),
            new lang_string('redirecturl', 'mod_dsa'),
            'http://assessment.intranet.arup.com/',
            PARAM_TEXT
    ));
}
