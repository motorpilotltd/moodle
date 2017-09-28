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
require_once('edit_carousel_form.php');
 
global $DB;
 
// Check for all required variables.
$carousel_id = optional_param('carouselid', 0, PARAM_INT);
if (!$carousel_id) {
	$carousel_id = optional_param('id', 0, PARAM_INT);
}

$context = context_system::instance();

$PAGE->set_url('/blocks/carousel/edit_carousel.php');
$PAGE->set_context($context);
$PAGE->set_pagelayout('incourse');

$title = get_string('editcarousel', 'block_carousel');
$PAGE->set_title($title);
$PAGE->set_heading( $title);
$PAGE->set_cacheable( true);


/* Pass the existing carousel item data to the edit form */

$existingcarousel = $DB->get_record('block_carousel', array('id'=> $carousel_id));

if (!$existingcarousel) {
    print_error('carouseldoesnotexist', 'block_carousel');
}

$existing_carousel_data = array(
    'contextid' => $context->id,
	'id' => $existingcarousel->id,
	'regionid' => $existingcarousel->regionid,
	'name' => $existingcarousel->name,
    'carousellayout' => $existingcarousel->layout
);

$carousel_items = $DB->get_records('block_carousel_item', array('carousel_id'=> $carousel_id), 'display ASC');
if ($carousel_items) {
	foreach ($carousel_items as $carousel_item) {
        $existing_carousel_data["image{$carousel_item->display}"] = $carousel_item->image;
		$existing_carousel_data["caption{$carousel_item->display}"] = $carousel_item->caption;
		$existing_carousel_data["captioncolour{$carousel_item->display}"] = $carousel_item->captioncolour;
		$existing_carousel_data["captionbackground{$carousel_item->display}"] = $carousel_item->captionbackground;
		$existing_carousel_data["display{$carousel_item->display}"] = $carousel_item->display;
		$existing_carousel_data["buttontext{$carousel_item->display}"] = $carousel_item->buttontext;
		$existing_carousel_data["link{$carousel_item->display}"] = $carousel_item->link;
        $existing_carousel_data["slideopacity{$carousel_item->display}"] = $carousel_item->opacity;
	}
}

$carousel = new edit_carousel_form(null, $existing_carousel_data);

if($carousel->is_cancelled()) {
    $cancelurl = new moodle_url('/blocks/carousel/manage_instances.php');
    redirect($cancelurl);
} else if ($fromform = $carousel->get_data()) {
	$record = new stdClass();
	$record->id = $fromform->carouselid;
	$record->regionid = $fromform->carouselregionid;
	$record->name = $fromform->carouselname;
    $record->layout = $fromform->carousellayout;
	
	if (!$DB->update_record('block_carousel', $record)) {
		print_error('inserterror', 'block_carousel');
	}

    $fs = get_file_storage();
    for ($i = 1; $i <= 5; $i++) {
        $existingcarouselitem = $DB->get_record('block_carousel_item', array('carousel_id' => $record->id, 'display' => $i));

        $itemid = "image{$i}_{$record->id}";

        $draftid = file_get_submitted_draft_itemid("image{$i}");
        $draftareafiles = file_get_drafarea_files($draftid);
        if (empty($draftareafiles->list)) {
            if ($existingcarouselitem) {
                $DB->delete_records('block_carousel_item', array('id' => $existingcarouselitem->id));
            }
            $fs->delete_area_files(context_system::instance()->id, 'block_carousel', $itemid);
            continue;
        }
        $file = array_shift($draftareafiles->list);
        file_save_draft_area_files($draftid, $context->id, 'block_carousel', $itemid, 0, array('subdirs'=>true));
        $imageurl = new moodle_url("/pluginfile.php/{$context->id}/block_carousel/{$itemid}/{$file->filename}");

        $carousel_item = new stdClass();
        $carousel_item->carousel_id = $record->id;
        $carousel_item->image = $imageurl->out();
        $caption = "caption{$i}";
        $carousel_item->caption = $fromform->{$caption};
        $captioncolour = "captioncolour{$i}";
        $carousel_item->captioncolour = $fromform->{$captioncolour};
        $captionbackground = "captionbackground{$i}";
        $carousel_item->captionbackground = $fromform->{$captionbackground};
        $buttontext = "buttontext{$i}";
        $carousel_item->buttontext = $fromform->{$buttontext};
        $link = "link{$i}";
        $carousel_item->link = $fromform->{$link};
        $carousel_item->display = $i;
        $slideopacity = "slideopacity{$i}";
        $carousel_item->opacity = $fromform->{$slideopacity};

        if ($existingcarouselitem) {
            $carousel_item->id = $existingcarouselitem->id;
            if (!$DB->update_record('block_carousel_item', $carousel_item)) {
                print_error('inserterror', 'block_carousel');
            }
        } else if (!$DB->insert_record('block_carousel_item', $carousel_item)) {
            print_error('inserterror', 'block_carousel');
        }
    }
	
	if (isset($fromform->submitbutton)) {
        $addedurl = new moodle_url('/blocks/carousel/edit_carousel.php', array('id' => $fromform->carouselid));
    } else {
        $addedurl = new moodle_url('/blocks/carousel/manage_instances.php');
    }
    redirect($addedurl);
	
} else {
    // Only needed for form
    $PAGE->requires->css('/blocks/carousel/css/forms.css');
    $PAGE->requires->css('/blocks/carousel/css/bootstrap-colorpicker.css');
    $PAGE->requires->js('/blocks/carousel/js/bootstrap-colorpicker.js');
    $jscode = <<<EOJ
        $(document).ready(function(){
            $('.colorpicker').colorpicker();
            $('.showcarouselitem').show();
            $('.showcarouselitem').siblings().hide();
            $('.showcarouselitem').click(function(){
                $(this).hide();
                $(this).siblings().show();
                return false;
            });
        });
EOJ;
    $PAGE->requires->js_init_code($jscode);

	echo $OUTPUT->header();
	$carousel->display();
	echo $OUTPUT->footer();

}

?>