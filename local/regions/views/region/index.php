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

<?php
if ($this->parentregion) :
?>
<h3>Current Sub Regions for <?php echo $this->parentregion->name ?></h3>
<?php
    if ($this->countsubregions == 0) :
?>
<p>No sub regions set</p>
<?php
    else :
        foreach ($this->subregionlist as $subregion) :
?>
<p>
    <?php echo $subregion->name; ?>
    |
    <a href="<?php echo $this->base_url('index.php?action=region/editsubregion&id='.$subregion->id); ?>">Edit</a>
    |
    <a href="<?php echo $this->base_url('index.php?action=region/deletesubregion&id='.$subregion->id); ?>">Delete</a>
</p>
<?php
        endforeach;
    endif;
?>
<p>
    <a href="<?php echo $this->base_url('index.php?action=index/mapcourses&id='.$this->parentregion->id); ?>">Map to Courses</a>
    <br />
    <a href="<?php echo $this->base_url('index.php?action=region/editsubregion&regionid='.$this->parentregion->id); ?>">Add Sub Region</a>
    <br />
    <a href="<?php echo $this->base_url('index.php?action=region/bulkaddsubregions&regionid='.$this->parentregion->id); ?>">Bulk Add Sub Regions</a>
</p>
<?php
endif;
?>
<p>
    <a href="<?php echo $this->base_url('index.php?action=index/index'); ?>">Return to Manage Regions</a>
</p>