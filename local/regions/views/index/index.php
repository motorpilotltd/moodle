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

<h3>Current Regions</h3>
<?php
if ($this->countregions == 0) :
?>
<p>No regions set</p>
<?php
else :
    foreach ($this->regionlist as $region) :
?>
<p>
    <?php echo $region->name; ?>
    |
    <a href="<?php echo $this->base_url('index.php?action=index/editregion&id='.$region->id); ?>">Edit</a>
    |
    <a href="<?php echo $this->base_url('index.php?action=index/deleteregion&id='.$region->id); ?>">Delete</a>
    |
    <a href="<?php echo $this->base_url('index.php?action=region/index&regionid='.$region->id); ?>">Sub Regions</a>
    |
    <a href="<?php echo $this->base_url('index.php?action=index/mapcourses&id='.$region->id); ?>">Map to Courses</a>
</p>
<?php
    endforeach;
endif;
?>
<p>
    <a href="<?php echo $this->base_url('index.php?action=index/editregion'); ?>">Add Region</a>
    <br />
    <a href="<?php echo $this->base_url('index.php?action=index/bulkaddregions'); ?>">Bulk Add Regions</a>
</p>