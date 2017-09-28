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
 * Theme arup settings file.
 *
 * @package    theme_arup
 * @copyright  2016 Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once(__DIR__ . "/simple_theme_settings.class.php");

global $PAGE;

$ss = new arup_simple_theme_settings($settings, 'theme_arup');

$ss->add_heading('notes');

$ss->add_text('logo_url');

$ss->add_text('customcss');

$ss->add_text('gakey');

$ss->add_text('searchlocation');

$ss->add_textarea('footerlinks');

$ss->add_text('altsitename');

$ss->add_textarea('notice');

$ss->add_text('noticeid');

$options = array('success' => 'success',
                 'info' => 'info',
                 'warning' => 'warning',
                 'danger' => 'danger');

$ss->add_select('noticecolor' , 'info', $options);

$categories = $DB->get_records('course_categories');
foreach ($categories as $category) {
    $name = 'categorybackground';
    $title = get_string('categorybackground', 'theme_arup', $category->name);        
    $ss->add_files($name , $category->id);
}
