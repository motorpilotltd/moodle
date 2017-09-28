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

require_once("{$CFG->libdir}/formslib.php");
 
class delete_carousel_form extends moodleform {
 
    function definition() {
 
        global $DB;
		
		$mform =& $this->_form;
        $mform->addElement('header','displayinfo', get_string('deletecarousel', 'block_carousel'));

        $mform->addElement('html', html_writer::tag('p', get_string('deletecarouselsure', 'block_carousel')));
		
		// add carousel name element.
		$mform->addElement('static', 'carouselname', get_string('carouselname', 'block_carousel'));
		$mform->setDefault('carouselname',$this->_customdata->name);
		
		//Arup specific select dropdown to capture the supported regions
		$arup_regions = $DB->get_records('local_regions_reg');
        $arup_regions = array(0 => 'Default') + $arup_regions;

		$mform->addElement('static', 'carouselregionid', get_string('carouselregion', 'block_carousel'));
		$mform->setDefault('carouselregionid', $arup_regions[$this->_customdata->regionid]->name);
		
		$mform->addElement('hidden', 'carouselid', $this->_customdata->id);
		
		$buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('yes'));
        $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('no'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
		
    }
}