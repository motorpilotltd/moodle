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

defined('MOODLE_INTERNAL') || die;

echo $this->render_partial('partial/messages');
?>

<p><?php
$a = new stdClass();
$a->courses = core_text::strtolower(get_string('courses'));
$a->currentregion = $this->currentregion->name;
$a->region = core_text::strtolower(get_string('region', 'local_regions'));
$a->subregions = core_text::strtolower(get_string('subregions', 'local_regions'));
print_string('mapcourses', 'local_regions', $a);
?></p>

<?php
require('forms/mapcourses.php');
