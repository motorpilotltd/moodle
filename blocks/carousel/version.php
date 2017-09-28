<?php
// This file is part of the Arup Carousel for Moodle
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
 *
 * @package    block_carousel
 * @copyright  2016 Arup
 * @author 	   Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2016031802;
$plugin->requires  = 2015051100; // Moodle 2.9
$plugin->component = 'block_carousel';
$plugin->cron      = 300;
$plugin->maturity  = MATURITY_STABLE;
$plugin->release   = '2.9';

$plugin->dependencies = array(
    'theme_bootstrap' => 2015062200
);