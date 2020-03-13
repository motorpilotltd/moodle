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
 * External services.
 *
 * @package    format_aruponepage
 * @author     2019 <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$functions = array(

    'format_aruponepage_move_section' => array(
        'classpath' => 'course/format/aruponepage/classes/external.php',
        'classname'   => 'format_aruponepage_external',
        'methodname'  => 'move_section',
        'description' => 'Move Sections.',
        'type'        => 'write',
        'ajax'        => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'format_aruponepage_move_module' => array(
        'classpath' => 'course/format/aruponepage/classes/external.php',
        'classname'   => 'format_aruponepage_external',
        'methodname'  => 'move_module',
        'description' => 'Move Modules.',
        'type'        => 'write',
        'ajax'        => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    ),
    'format_aruponepage_module_completion' => array(
        'classpath' => 'course/format/aruponepage/classes/external.php',
        'classname'   => 'format_aruponepage_external',
        'methodname'  => 'module_completion',
        'description' => 'Change module completion.',
        'type'        => 'write',
        'ajax'        => true,
        'services'    => array(MOODLE_OFFICIAL_MOBILE_SERVICE),
    )
);

