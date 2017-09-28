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
 * English language strings for arupadvertdatattype_taps.
 *
 * @package   arupadvertdatatype_taps
 * @copyright 2016 Motorpilot
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$string['header'] = 'Linked course Information';

$string['missingcourses'] = 'Linked courses not appearing in this list will already be linked to by an activity in another {$a->course}.';

$string['pluginname'] = 'Linked course';

$string['overrideregion'] = 'Use (catalogue) region set in Moodle?';
$string['overrideregion_help'] = 'When checked the (catalogue) region will not be mapped to that set in teh linked course and will remain mapped to that which was selected manually within Moodle.';

$string['tapscourseid'] = 'Linked course';
$string['tapsenrolhascompletion'] = 'An {$a->activity} activity exists within this {$a->course} and has completion data.<br />
    Unless the completion data is deleted you can only link this activity to the same linked course.';