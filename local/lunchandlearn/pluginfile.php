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

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir . '/filelib.php');

$fs = get_file_storage();

$preview = optional_param('preview', '', PARAM_ALPHA);

$relativepath = get_file_argument();
$args = explode('/', ltrim($relativepath, '/'));

$area = array_shift($args);

$filename = array_pop($args);
$itemid = array_pop($args);
$filepath = $args ? '/'.implode('/', $args).'/' : '/';

if (!$file = $fs->get_file(context_system::instance()->id, 'local_lunchandlearn',
        $area, $itemid, '/', $filename) or $file->is_directory()) {
    send_file_not_found();
}
\core\session\manager::write_close(); // Unlock session during fileserving.
send_stored_file($file, 60*60, 0, false, array('preview' => $preview));