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


require_once("{$CFG->libdir}/formslib.php");
 
class add_carousel_form extends moodleform {
 
    function definition() {
 
		global $DB;
 
        $mform =& $this->_form;
        $mform->addElement('header','displayinfo', get_string('addnewcarousel', 'block_carousel'));
 
		// add carousel name element.
		$mform->addElement('text', 'carouselname', get_string('carouselname', 'block_carousel'));
		$mform->addRule('carouselname', null, 'required', null, 'client');
        $mform->setType('carouselname', PARAM_RAW);

		//Arup specific select dropdown to capture the supported regions
        $sql = "
            SELECT
                lrr.id, lrr.name
            FROM
                {local_regions_reg} lrr
            LEFT JOIN
                {block_carousel} bc
                ON bc.regionid = lrr.id
            WHERE
                lrr.userselectable = 1
                AND bc.id IS NULL
            ";
		$arup_regions = $DB->get_records_sql_menu($sql);
        if (!$DB->get_record('block_carousel', array('regionid' => 0))) {
            $arup_regions = array(0 => 'Default') + $arup_regions;
        }

		$mform->addElement('select', 'carouselregionid', get_string('carouselregion', 'block_carousel'), $arup_regions);
		$mform->addRule('carouselregionid', null, 'required', null, 'client');
		
        $layouts = array('centered' => get_string('centered', 'block_carousel'),
            'leftaligned' => get_string('leftaligned', 'block_carousel'));
        $mform->addElement('select', 'carousellayout', get_string('carousellayout', 'block_carousel'), $layouts);
        if (isset($this->_customdata['carousellayout'])) {
            $defaultlayout = $this->_customdata['carousellayout'];
        } else {
            $defaultlayout = 'centered';
        }
        $mform->setDefault('carousellayout', $defaultlayout);

		$editoroptions = array('maxfiles' => 0);
        
        $filemanager_options = array();
        $filemanager_options['return_types'] = 3;
        $filemanager_options['accepted_types'] = array('.jpg','.jpeg','.gif','.png');
        $filemanager_options['maxbytes'] = 0;
        $filemanager_options['maxfiles'] = 1;
        $filemanager_options['mainfile'] = false;
		
		/**********/
		
		$mform->addElement('html', '<p>' . get_string('numberofcarouselelements', 'block_carousel') . '</p>');
		
		/**********/

        for ($i = 1; $i <= 5; $i++) {
            $mform->addElement('header', "carouselitem{$i}", get_string('carouselitem', 'block_carousel', $i));
            
            if ($i > 1) {
                $mform->addElement('html', html_writer::tag('button', get_string('showcarouselitem', 'block_carousel', $i), array('class' => 'btn showcarouselitem')));
            }
            
            $mform->addElement('filemanager', "image{$i}", get_string('image', 'block_carousel'), null, $filemanager_options);
            if ($i == 1) {
                $mform->addRule("image{$i}", null, 'required', null, 'client');
            }
            $mform->setType("image{$i}", PARAM_RAW);

            $opacities = array(
                'overlay-00' => '0',
                'overlay-01' => '0.1',
                'overlay-02' => '0.2',
                'overlay-03' => '0.3',
                'overlay-04' => '0.4',
                'overlay-05' => '0.5',
                'overlay-06' => '0.6',
                'overlay-07' => '0.7',
                'overlay-08' => '0.8',
                'overlay-09' => '0.9',
                'overlay-10' => '1');

            $mform->addElement('select', "slideopacity{$i}", get_string('slideopacity', 'block_carousel'), $opacities);
            $mform->setDefault("slideopacity{$i}", 'overlay-05');

            $mform->addElement('textarea', "caption{$i}", get_string('caption', 'block_carousel'));
            $mform->setType("caption{$i}", PARAM_RAW);

            $mform->addElement('text', "captioncolour{$i}", get_string('captioncolour', 'block_carousel'), 'class="colorpicker" data-color-format="hex"');
            $mform->setType("captioncolour{$i}", PARAM_RAW);
            $mform->addHelpButton("captioncolour{$i}", 'captioncolour', 'block_carousel');

            $mform->addElement('text', "captionbackground{$i}", get_string('captionbackground', 'block_carousel'), 'class="colorpicker" data-color-format="rgba"');
            $mform->setType("captionbackground{$i}", PARAM_RAW);
            $mform->addHelpButton("captionbackground{$i}", 'captionbackground', 'block_carousel');

            $mform->addElement('text', "buttontext{$i}", get_string('buttontext', 'block_carousel'));
            $mform->setType("buttontext{$i}", PARAM_RAW);

            $mform->addElement('text', "link{$i}", get_string('link', 'block_carousel'));
            $mform->setType("link{$i}", PARAM_RAW);
        }
		
		$buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('saveandcontinue', 'block_carousel'));
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton2', get_string('saveandexit', 'block_carousel'));
        $buttonarray[] = &$mform->createElement('cancel');
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }
}