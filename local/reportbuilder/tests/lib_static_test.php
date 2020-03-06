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
 * @author Simon Coggins <simonc@catalyst.net.nz>
 * @package t0tara
 * @subpackage reportbuilder
 *
 * Unit tests for static functions in local/reportbuilder/lib.php and
 * any other tests that don't require the monster setup occurring within
 * local/reportbuilder/tests/lib_test.php
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/local/reportbuilder/lib.php');

class local_reportbuilder_lib_static_testcase extends advanced_testcase {

    /**
     * Test \reportbuilder::find_source_dirs
     */
    public function test_find_source_dirs() {
        $key = 'all';

        // Ensure its not in the cache.
        $cache = cache::make('local_reportbuilder', 'rbsource_directories');
        $cache->delete($key);
        $this->assertFalse($cache->get($key));

        // Generate the directories.
        $generateddirs = \reportbuilder::find_source_dirs(true);
        $this->assertInternalType('array', $generateddirs);

        // Get it from the cache.
        $cacheddirs = $cache->get($key);
        $this->assertInternalType('array', $cacheddirs);

        // Confirm that it is the exact same list from the method and the cache.
        $this->assertSame($generateddirs, $cacheddirs);

        // Now check that if we request it from find_source_dirs again its still the same,
        // this time however it will come internally from the cache.
        $this->assertSame($generateddirs, \reportbuilder::find_source_dirs());
    }
}