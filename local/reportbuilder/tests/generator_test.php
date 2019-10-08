<?php
/*
 * This file is part of T0tara LMS
 *
 * Copyright (C) 2015 onwards T0tara Learning Solutions LTD
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
 * @author Petr Skoda <petr.skoda@t0taralms.com>
 * @package local_reportbuilder
 */

class local_reportbuilder_generator_testcase extends advanced_testcase {
    public function test_create_global_restriction() {
        global $DB;
        $this->resetAfterTest();

        /** @var local_reportbuilder_generator $reportgenerator */
        $reportgenerator = $this->getDataGenerator()->get_plugin_generator('local_reportbuilder');

        $record = new stdClass();
        $record->name = 'Some restriction name';
        $record->description = 'Some restriction description';
        $record->active = '1';
        $record->allrecords = '0';
        $record->allusers = '0';
        $record->sortorder = '0';
        $record->timecreated = time();
        $record->timemodified = $record->timecreated;

        $id = $DB->insert_record('report_builder_global_restr', $record);
        $restriction1 = $DB->get_record('report_builder_global_restr', array('id' => $id), '*', MUST_EXIST);

        $this->assertObjectHasAttribute('name', $restriction1);
        $this->assertObjectHasAttribute('description', $restriction1);
        $this->assertObjectHasAttribute('active', $restriction1);
        $this->assertObjectHasAttribute('allrecords', $restriction1);
        $this->assertObjectHasAttribute('allusers', $restriction1);
        $this->assertObjectHasAttribute('sortorder', $restriction1);
        $this->assertObjectHasAttribute('timecreated', $restriction1);
        $this->assertObjectHasAttribute('timemodified', $restriction1);

        unset($record->sortorder);
        $this->setCurrentTimeStart();
        $restriction2 = $reportgenerator->create_global_restriction($record);
        $this->assertInstanceOf('rb_global_restriction', $restriction2);
        $this->assertEquals($id + 1, $restriction2->id);
        $this->assertSame($record->name, $restriction2->name);
        $this->assertSame($record->description, $restriction2->description);
        $this->assertSame($record->active, $restriction2->active);
        $this->assertSame($record->allrecords, $restriction2->allrecords);
        $this->assertSame($record->allusers, $restriction2->allusers);
        $this->assertEquals($restriction1->sortorder + 1, $restriction2->sortorder);
        $this->assertTimeCurrent($restriction2->timecreated);
        $this->assertTimeCurrent($restriction2->timemodified);

        unset($record->name);
        $restriction3 = $reportgenerator->create_global_restriction($record);
        $this->assertInstanceOf('rb_global_restriction', $restriction2);
        $this->assertEquals($id + 2, $restriction3->id);
        $this->assertSame('Global report restriction 2', $restriction3->name);
        $this->assertEquals($restriction2->sortorder + 1, $restriction3->sortorder);
    }

    public function test_assign_global_restriction_record() {
        global $DB;

        $this->resetAfterTest();

        /** @var local_reportbuilder_generator $reportgenerator */
        $reportgenerator = $this->getDataGenerator()->get_plugin_generator('local_reportbuilder');

        $cohort = $this->getDataGenerator()->create_cohort();
        $user = $this->getDataGenerator()->create_cohort();

        $restriction = $reportgenerator->create_global_restriction();

        // Test cohort.

        $item = new stdClass();
        $item->restrictionid = $restriction->id;
        $item->prefix = 'cohort';
        $item->itemid = $cohort->id;

        $record = $reportgenerator->assign_global_restriction_record($item);
        $this->assertTrue($DB->record_exists('reportbuilder_grp_cht_rec', array('id' => $record->id)));

        // Test user.

        $item = new stdClass();
        $item->restrictionid = $restriction->id;
        $item->prefix = 'user';
        $item->itemid = $user->id;

        $record = $reportgenerator->assign_global_restriction_record($item);
        $this->assertTrue($DB->record_exists('reportbuilder_grp_usr_rec', array('id' => $record->id)));
    }

    public function test_assign_global_restriction_user() {
        global $DB;

        $this->resetAfterTest();

        /** @var local_reportbuilder_generator $reportgenerator */
        $reportgenerator = $this->getDataGenerator()->get_plugin_generator('local_reportbuilder');

        $restriction = $reportgenerator->create_global_restriction();

        // Test user.

        $item = new stdClass();
        $item->restrictionid = $restriction->id;
        $item->prefix = 'user';
        $item->itemid = $user->id;

        $record = $reportgenerator->assign_global_restriction_user($item);
        $this->assertTrue($DB->record_exists('reportbuilder_grp_usr_usr', array('id' => $record->id)));
    }
}
