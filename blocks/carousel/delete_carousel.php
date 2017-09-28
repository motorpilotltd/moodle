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
 * @author     Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

 
require_once('../../config.php');
require_once('delete_carousel_form.php');
 
global $DB;
 
// Check for all required variables.
$carousel_id = optional_param('carouselid', 0, PARAM_INT);
if (!$carousel_id) {
	$carousel_id = optional_param('id', 0, PARAM_INT);
}

$context = get_context_instance(CONTEXT_SYSTEM);

$PAGE->set_url('/blocks/carousel/delete_carousel.php');
$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');
 
$title = get_string('deletecarousel', 'block_carousel');
$PAGE->set_title($title);
$PAGE->set_heading( $title);
$PAGE->set_cacheable( true);

$carouseldata = $DB->get_record('block_carousel', array('id' => $carousel_id));

if (!$carouseldata) {
    print_error('carouseldoesnotexist', 'block_carousel');
}

$carousel = new delete_carousel_form(null, $carouseldata);

if($carousel->is_cancelled()) {
    $cancelurl = new moodle_url('/blocks/carousel/manage_instances.php');
    redirect($cancelurl);
} else if ($fromform = $carousel->get_data()) {
	//delete the carousel and all its items from the DB
    if (!$DB->delete_records('block_carousel_item', array('carousel_id' => $fromform->carouselid))) {
        print_error('deleteerror', 'block_carousel');
    }
	if (!$DB->delete_records('block_carousel', array('id' => $fromform->carouselid))) {
		print_error('deleteerror', 'block_carousel');
	}
    $fs = get_file_storage();
    for ($i=1;$i<=5;$i++) {
        $itemid = "image{$i}_{$fromform->carouselid}";
        $fs->delete_area_files(context_system::instance()->id, 'block_carousel', $itemid);
    }
    $deletedurl = new moodle_url('/blocks/carousel/manage_instances.php');
    redirect($deletedurl);
} else {
	echo $OUTPUT->header();
	$carousel->display();
	echo $OUTPUT->footer();
}