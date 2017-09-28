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
// List of observers.
$observers = [
    [
        'eventname'   => '\local_custom_certification\event\user_assignment_created',
        'callback'    => 'local_custom_certification_observer::user_assignment_created',
    ],
    [
        'eventname'   => '\local_custom_certification\event\user_assignment_deleted',
        'callback'    => 'local_custom_certification_observer::user_assignment_deleted',
    ],
    [
        'eventname'   => '\local_custom_certification\event\certification_completed',
        'callback'    => 'local_custom_certification_observer::certification_completed',
    ],
    [
        'eventname'   => '\local_custom_certification\event\certification_expired',
        'callback'    => 'local_custom_certification_observer::certification_expired',
    ],
    [
        'eventname'   => '\core\event\course_completed',
        'callback'    => 'local_custom_certification_observer::course_completed',
    ]
];
