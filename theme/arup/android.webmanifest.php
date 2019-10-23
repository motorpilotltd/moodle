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
 * Web manifest for including a native app banner.
 *
 * @package    theme_arup
 * @copyright  2019 Xantico Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('NO_DEBUG_DISPLAY', true);
define('NO_MOODLE_COOKIES', true);
require_once(__DIR__ . '/../../config.php');
require_once($CFG->dirroot . '/theme/bootstrap/renderers/core_renderer.php');
require_once($CFG->dirroot . '/theme/bootstrap/renderers/course_renderer.php');

header('Content-Type: application/json; charset: utf-8');

$android192 = $OUTPUT->image_url('android-chrome-192x192', 'theme');
$android128 = $OUTPUT->image_url('android-chrome-128x128', 'theme');
$android512 = $OUTPUT->image_url('android-chrome-512x512', 'theme');

$manifest = new StdClass;
$manifest->prefer_related_applications = true;
$manifest->icons = [(object)
            [
                'sizes' => '192x192',
                'type' => 'image/png',
                'src' => $android192->out()
            ],
            (object)
            [
                'sizes' => '512x512',
                'type' => 'image/png',
                'src' => $android512->out()
            ],
            (object)
            [
                'sizes' => '128x128',
                'type' => 'image/png',
                'src' => $android128->out()
            ]
];
$manifest->theme_color = "#ffffff";
$manifest->background_color = "#ffffff";

echo json_encode($manifest);
die;
