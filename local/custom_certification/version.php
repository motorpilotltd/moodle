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
 * @author Artur Rietz <artur.rietz@webanywhere.co.uk>
 */
defined('MOODLE_INTERNAL') || die();

defined('MOODLE_INTERNAL') || die();

$plugin->version   = 2018022804; // Continuing incrementing WA versioning as already jumped past base Moodle version.
$plugin->requires  = 2017051500; // Moodle 3.3.
$plugin->component = 'local_custom_certification';
$plugin->maturity  = MATURITY_STABLE;
// Previous version 1.8.5.1.
$plugin->release   = "3.3.4 (Build: {$plugin->version})"; // Restart release versioning to follow underlying Moodle version.
$plugin->dependencies = array();