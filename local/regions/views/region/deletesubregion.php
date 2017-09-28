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

<p>Are you sure you want to delete this sub region (<?php echo $this->subregion->name; ?>) from <?php echo $this->parentregion->name; ?>?</p>
<p>
    <a href="<?php echo $this->base_url('index.php?action=region/deletesubregion&confirm=1&id='.$this->subregion->id); ?>">Yes</a>
    |
    <a href="<?php echo $this->base_url('index.php?action=region/index&regionid='.$this->subregion->regionid); ?>">No</a>
</p>
