<?php
/*
 * This file is part of T0tara LMS
 *
 * Copyright (C) 2016 onwards T0tara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Simon Coggins <simon.coggins@t0taralearning.com>
 * @package local_reportbuilder
 */

namespace local_reportbuilder\form;

require_once($CFG->libdir.'/formslib.php');

use moodleform;

class delete_report_form extends moodleform {
    protected function definition() {

        $mform          = $this->_form;
        $mform->addElement('hidden', 'id');
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'returnurl');
        $mform->setType('returnurl', PARAM_LOCALURL);

        $this->add_action_buttons(true, get_string($this->_customdata['type'], 'moodle'));
    }
}