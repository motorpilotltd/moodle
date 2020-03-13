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

defined('MOODLE_INTERNAL') || die();

require_once("{$CFG->libdir}/coursecatlib.php");

if ($hassiteconfig) {

    $settings = new admin_settingpage('local_catalogue_settings', get_string('configuration', 'local_catalogue'));
    $ADMIN->add('localplugins', $settings);

    $categoryoptions = array(0 => get_string('default', 'local_catalogue')) + coursecat::make_categories_list();
    $settings->add(
        new admin_setting_configselect(
            'local_catalogue/root_category',
            get_string('catalogue_choose_root_category', 'local_catalogue'),
            get_string('catalogue_choose_root_category_desc', 'local_catalogue'),
            0,
            $categoryoptions
        )
    );
}
