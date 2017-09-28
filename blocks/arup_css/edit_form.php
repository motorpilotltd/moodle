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
 * Form for editing Arup CSS block instances.
 *
 * @package   block_arup_css
 */

class block_arup_css_edit_form extends block_edit_form {

    protected function specific_definition($mform) {
        $html = html_writer::tag('div', get_string('warning:refresh', 'block_arup_css'), array('class' => 'alert alert-warning fade in', 'style' => 'margin-top: 10px;'));
        $mform->addElement('html', $html);

        $selectdisabled = $this->block->canselect ? '' : 'disabled="disabled"';
        $editdisabled = $this->block->canedit ? '' : ' disabled="disabled"';

        $mform->addElement('header', 'selectcss', get_string('selectcss', 'block_arup_css'));
        $mform->setExpanded('selectcss', true);

        // Options.
        $options = array(
            '' => get_string('default'),
            '#DD4628' => 'AU 1 (#DD4628)',
            '#BD462B' => 'AU 2 (#BD462B)',
            '#3C3C3B' => 'Text (#3C3C3B)',
            '#FFFFFF' => 'White (#FFFFFF)',

            '#EC87C0' => 'Pink 1 (#EC87C0)',
            '#D770AD' => 'Pink 2 (#D770AD)',

            '#ED5564' => 'Red 1 (#ED5564)',
            '#DB4453' => 'Red 2 (#DB4453)',

            '#FFB41D' => 'Yellow 1 (#FFB41D)',
            '#FC9C10' => 'Yellow 2 (#FC9C10)',

            '#38C19A' => 'Green 1 (#38C19A)',
            '#30AA8C' => 'Green 2 (#30AA8C)',

            '#0F95FF' => 'Blue 1 (#0F95FF)',
            '#007FFF' => 'Blue 2 (#007FFF)',

            '#AC92ED' => 'Purple 1 (#AC92ED)',
            '#967BDC' => 'Purple 2 (#967BDC)',

            '#000000' => 'Black (#000000)',
            '#777777' => 'Grey 5 (#777777)',
            '#939598' => 'Grey 4 (#939598)',
            '#BCBEC0' => 'Grey 3 (#BCBEC0)',
            '#E6E7E8' => 'Grey 2 (#E6E7E8)',
            '#F1F2F2' => 'Grey 1 (#F1F2F2)',
            
            '#69143C' => 'Pompadour (#69143C)',
        );

        $mform->addElement('select', 'config_h2colour', get_string('colour:h2', 'block_arup_css'), $options, $selectdisabled);
        $mform->addElement('select', 'config_h3colour', get_string('colour:h3', 'block_arup_css'), $options, $selectdisabled);
        $mform->addElement('select', 'config_h4colour', get_string('colour:h4', 'block_arup_css'), $options, $selectdisabled);
        $mform->addElement('select', 'config_h5colour', get_string('colour:h5', 'block_arup_css'), $options, $selectdisabled);
        $mform->addElement('select', 'config_sectionlinecolour', get_string('colour:sectionline', 'block_arup_css'), $options, $selectdisabled);
        $mform->addElement('select', 'config_toplinecolour', get_string('colour:topline', 'block_arup_css'), $options, $selectdisabled);

        if (!empty($this->block->config->css)) {
            $html = html_writer::tag('div', get_string('warning:advanced', 'block_arup_css'), array('class' => 'alert alert-warning fade in', 'style' => 'margin-top: 10px;'));
            $mform->addElement('html', $html);
        }

        $mform->addElement('header', 'editcss', get_string('editcss', 'block_arup_css'));
        $mform->setExpanded('editcss', true);

        $mform->addElement('textarea', 'config_css', get_string('configcss', 'block_arup_css'), 'cols="60" rows="10"'.$editdisabled);
        $mform->setType('config_css', PARAM_RAW);
    }
}
