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
 * @package    theme_arupboost
 * @copyright  2019 Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die;

require_once(__DIR__ . "/simple_theme_settings.class.php");

global $PAGE;

$ss = new arupboost_simple_theme_settings($settings, 'theme_arupboost');

$ss->add_heading('notes');

$ss->add_textarea('footerlinks');

$ss->add_textarea('notice');

$options = array('success' => 'success',
                 'info' => 'info',
                 'warning' => 'warning',
                 'danger' => 'danger');

$ss->add_select('noticecolor' , 'info', $options);

$ss->add_file('loginbackground');

$ss->add_checkbox('courseeditbutton');

$ss->add_file('frontpageimage');


