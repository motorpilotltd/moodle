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
 * File description.
 *
 * @package   theme_arupboost
 * @copyright 2019 Bas Brands <bas@sonsbeekmedia.nl>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(

    'theme_arupboost_set_timezone' => array(
        'classname'   => 'theme_arupboost\external',
        'methodname'  => 'set_timezone',
        'description' => 'Set a user timezone.',
        'type'        => 'write',
        'ajax'        => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'theme_arupboost_get_footer_contents' => array(
        'classname'   => 'theme_arupboost\external',
        'methodname'  => 'get_footer_content',
        'description' => 'Get the page footer content.',
        'loginrequired' => false,
        'type'        => 'read',
        'ajax'        => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'theme_arupboost_saveimage' => array(
        'classname'     => 'theme_arupboost\external',
        'methodname'    => 'saveimage',
        'description'   => 'Image handler',
        'type'          => 'write',
        'ajax'          => true,
        'loginrequired' => true
    )
);

