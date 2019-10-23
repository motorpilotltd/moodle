<?php
// This file is part of the arup theme for Moodle
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
 * Theme arup version file.
 *
 * @package    theme_arup
 * @copyright  2016 Arup
 * @author 	   Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die;

$plugin->version   = 2018020102;
$plugin->requires  = 2014051200;
$plugin->release  = 2014051300;
$plugin->maturity  = MATURITY_STABLE;
$plugin->component = 'theme_arup';
$plugin->dependencies = array(
    'theme_bootstrap'  => 2015062200
);
