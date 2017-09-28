<?php
// This file is part of the Arup Carousel for Moodle
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
 * @package    block_carousel
 * @copyright  2016 Arup
 * @author 	   Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
 
require_once('../../config.php');
 
global $DB;
 
// Check for all required variables.

$context = context_system::instance();

$PAGE->set_url('/blocks/carousel/manage_instances.php');
$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');

$title = "Manage Carousels";
$navlinks = array();
$navlinks[] = array('name' => $title, 'link' => null, 'type' => 'title');
// $navigation = build_navigation($navlinks);

$PAGE->set_title($title);
$PAGE->set_heading( $title);
$PAGE->set_cacheable( true);
echo $OUTPUT->header();

echo '<h2>Manage Carousels</h2>';

if ($carousels = $DB->get_records('block_carousel', array(), $sort=' regionid ASC ')) {

	echo html_writer::start_tag('table', array('class'=>'generaltable', 'id'=>'mytable'));

	echo html_writer::start_tag('tr');
		
		echo html_writer::start_tag('th');
			echo 'Carousel Name';
		echo html_writer::end_tag('th');
		
		echo html_writer::start_tag('th');
			echo 'Region';
		echo html_writer::end_tag('th');
		
		echo html_writer::start_tag('th');
			echo 'Edit';
		echo html_writer::end_tag('th');
		
		echo html_writer::start_tag('th');
			echo 'Delete';
		echo html_writer::end_tag('th');
		
	echo html_writer::end_tag('tr');
	
	$carousel_count = 0;
	
	foreach ($carousels as $carousel) {
	
		$carousel_count++;
	
		$id = $carousel->id;
		$region_id = $carousel->regionid;
		
		$region_name = '';
		if ($regions = $DB->get_records('local_regions_reg', array('id'=> $region_id))) {
			foreach ($regions as $region) {
				$region_name = $region->name;
				break;
			}
		}
		
		$name = $carousel->name;
		
		if ($carousel_count % 2 == 0) {
			echo html_writer::start_tag('tr', array('class'=>'alt'));
		}
		else {
			echo html_writer::start_tag('tr');
		}
		
			echo html_writer::start_tag('td');
				echo $name;
			echo html_writer::end_tag('td');
			
			echo html_writer::start_tag('td');
				echo $region_name;
			echo html_writer::end_tag('td');
			
			echo html_writer::start_tag('td');
				echo '<input type="button" onclick="location.href=\'' . $CFG->wwwroot . '/blocks/carousel/edit_carousel.php?id=' . $id . '\'" value="Edit Carousel" >';
			echo html_writer::end_tag('td');
			
			echo html_writer::start_tag('td');
				echo '<input type="button" onclick="location.href=\'' . $CFG->wwwroot . '/blocks/carousel/delete_carousel.php?id=' . $id . '\'" value="Delete Carousel" >';
			echo html_writer::end_tag('td');
		
		echo html_writer::end_tag('tr');
	}
	
	echo html_writer::end_tag('table');
}

echo '<input type="button" onclick="location.href=\'' . $CFG->wwwroot . '/blocks/carousel/add_carousel.php\'" value="Add Carousel" >';

//$add_url = new moodle_url('/blocks/carousel/add_carousel.php');
//echo html_writer::link($add_url, "Add Carousel");

echo $OUTPUT->footer();

?>