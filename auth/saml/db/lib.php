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
 * The auth_saml install file.
 *
 * @package    auth_saml
 * @copyright  2016 Motorpilot Ltd.
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

/**
 * Place the config file in the data directory if it's not already there.
 *
 * @return void
 */
function auth_saml_create_config_file() {
    global $CFG;

    $source = json_encode(array(
        'samllib' => 'C:\inetpub\simplesaml\lib',
        'sp_source' => 'arup-sp',
        'dosinglelogout' => false,
    ));
    $destination = $CFG->dataroot.'/saml_config.json';

    if (!file_exists($destination)) {
        file_put_contents($destination, $source);
    }
}

/**
 * Copies the old config file to the new location.
 *
 * @return void
 */
function auth_saml_copy_config_file() {
    global $CFG;

    $source = $CFG->dataroot.'/saml_config.php';
    $destination = $CFG->dataroot.'/saml_config.json';

    if (file_exists($source)) {
        if (!file_exists($destination)) {
            rename($source, $destination);
        } else {
            unlink($source);
        }
    } else if (!file_exists($destination)) {
        auth_saml_create_config_file();
    }
}

/**
 * Removes the config file.
 *
 * @return void
 */
function auth_saml_remove_config_file() {
    global $CFG;

    $file = $CFG->dataroot.'/saml_config.json';

    if (file_exists($file)) {
        unlink($file);
    }
}