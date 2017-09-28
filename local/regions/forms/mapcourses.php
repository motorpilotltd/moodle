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
 *
 * @package local_regions
 */

defined('MOODLE_INTERNAL') || die();

?>

<form id="regions-mapcourses" accept-charset="utf-8" method="post" action="index.php?action=index/mapcourses" autocomplete="off">
    <div style="display: none;">
        <input id="id_region" type="hidden" value="<?php echo $this->currentregion->id; ?>" name="id">
        <input type="hidden" value="<?php echo sesskey(); ?>" name="sesskey">
    </div>
    <table>
        <tr>
            <th class="regions-right regions-middle" rowspan="2"><?php echo get_string('course'); ?></th>
            <th class="regions-spacer">&nbsp;</th>
            <th><?php echo get_string('region', 'local_regions'); ?></th>
            <th class="regions-spacer">&nbsp;</th>
<?php
if (!empty($this->subregions)) :
?>
            <th colspan="<?php echo count($this->subregions); ?>"><?php echo get_string('subregion', 'local_regions'); ?></th>
            <th class="regions-spacer">&nbsp;</th>
            <th>&nbsp;</th>
<?php
endif;
?>
        </tr>
        <tr>
            <th class="regions-spacer">&nbsp;</th>
            <th><?php echo $this->currentregion->name; ?></th>
            <th class="regions-spacer">&nbsp;</th>
<?php
if (!empty($this->subregions)) :
    foreach ($this->subregions as $subregionname) :
        echo '<th>'.$subregionname.'</th>';
    endforeach;
    echo '<td class="regions-spacer">&nbsp;</td>';
    echo '<td>&nbsp;</td>';
endif;
?>
        </tr>
<?php
foreach ($this->allcourses as $id => $shortname) :
    $regionchecked = '';
    if (array_key_exists($id, $this->regioncourses)) :
        $regionchecked = ' checked="checked"';
    endif;
    echo '<tr>';
    echo '<td class="regions-right">';
    echo '<label for="course['.$id.']">'.$shortname.'</label>';
    echo '</td>';
    echo '<td class="regions-spacer">&nbsp;</td>';
    echo '<td class="regions-center">';
    echo '<input class="region-checkbox" type="checkbox" name="course['.$id.']" value="1"'.$regionchecked.' />';
    echo '</td>';
    echo '<td class="regions-spacer">&nbsp;</td>';
    if (!empty($this->subregions)) :
        $count = 1;
        foreach ($this->subregions as $subregionid => $subregionname) :
            $subregionchecked = '';
            if (isset($this->subregioncourses[$id]) && array_key_exists($subregionid, $this->subregioncourses[$id])) :
                $subregionchecked = ' checked="checked"';
            endif;
            echo '<td class="regions-center subregion-column-'.$count.'">';
            echo '<input class="subregion-checkbox" type="checkbox" name="subregion['.$id.']['.$subregionid.']" value="1"'.$subregionchecked.' />';
            echo '</td>';
            $count++;
        endforeach;
        echo '<td class="regions-spacer">&nbsp;</td>';
        echo '<td><a href="#" class="regions-selectall-row">'.get_string('selectall').'</a></td>';
    endif;
    echo '</tr>';
endforeach;
?>
        <tr>
            <td>&nbsp;</td>
            <td class="regions-spacer">&nbsp;</td>
            <td class="regions-center"><a href="#" class="region-selectall-column"><?php echo get_string('selectall'); ?></a></td>
            <td class="regions-spacer">&nbsp;</td>
<?php
if (!empty($this->subregions)) :
    $count = 1;
    foreach ($this->subregions as $subregion) :
        echo '<td class="regions-center"><a href="#" class="subregion-selectall-column subregion-column-'.$count.'">'.get_string('selectall').'</a></td>';
        $count++;
    endforeach;
    echo '<td class="regions-spacer">&nbsp;</td>';
endif;
?>
        </tr>
    </table>
    <input type="submit" value="Save" name="submit" class="btn-primary" /> <input type="submit" value="Cancel" name="cancel" class="btn-default" />
</form>