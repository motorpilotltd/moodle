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
 * @package   mod_coursera
 * @category  backup
 * @copyright 2018 Andrew Hancox <andrewdchancox@googlemail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once("$CFG->dirroot/mod/coursera/lib.php");

if ($ADMIN->fulltree) {
    $settings->add(new admin_setting_configcheckbox ('mod_coursera/devmode', new lang_string('devmode', 'mod_coursera'), new lang_string('devmode_desc', 'mod_coursera'), true));

    $settings->add(new admin_setting_configtext('mod_coursera/client_id', new lang_string('client_id', 'mod_coursera'), '',
            '', PARAM_RAW));
    $settings->add(new admin_setting_configtext('mod_coursera/client_secret', new lang_string('client_secret', 'mod_coursera'), '',
            '', PARAM_RAW));
    $settings->add(new admin_setting_configtext('mod_coursera/orgid', new lang_string('orgid', 'mod_coursera'), '',
            '', PARAM_RAW));
    $settings->add(new admin_setting_configtext('mod_coursera/programid', new lang_string('programid', 'mod_coursera'), '',
            '', PARAM_RAW));

    $onetimecode = new admin_setting_configtext(
            'mod_coursera/onetimecode',
            new lang_string('onetimecode', 'mod_coursera'),
            new lang_string('onetimecodedesc', 'mod_coursera'),
            '',
            PARAM_RAW
    );
    $onetimecode->set_updatedcallback('coursera_get_refresh_token');
    $settings->add($onetimecode);

}