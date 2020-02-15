<?php
/*
 * This file is part of T0tara LMS
 *
 * Copyright (C) 2017 onwards T0tara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @package local_reportbuilder
 */

$definitions = array(
    // Caches for ignored embedded reports.
    'rb_ignored_embedded' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'simpledata' => true,
        'staticacceleration' => true,
        'staticaccelerationsize' => 2,
    ),

    // Caches for ignored report sources.
    'rb_ignored_sources' => array(
        'mode' => cache_store::MODE_APPLICATION,
        'simplekeys' => true,
        'simpledata' => true,
        'staticacceleration' => true,
        'staticaccelerationsize' => 2,
    ),

    'rb_staff_filter_options' => array(
        'mode' => cache_store::MODE_REQUEST,
        'simplekeys' => true,
        'simpledata' => false,
        'staticacceleration' => true,
    ),
);