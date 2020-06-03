<?php
/*
 * This file is part of T0tara LMS
 *
 * Copyright (C) 2017 onwards T0tara Learning Solutions LTD
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
 * @author Rob Tyler <rob.tyler@t0taralearning.com>
 * @package local_reportbuilder
 */

namespace rbsource_courseralearners\embedded;

class manageextensions extends \rb_base_embedded {

    public function __construct($data = []) {

        $this->url = '/mod/coursera/manageextensions.php';
        $this->source = 'courseralearners';
        $this->shortname = 'manageextensions'; // This must be unique, lets try make it really unique.
        $this->fullname = get_string('manageextensions', 'rbsource_courseralearners');
        $this->columns = array(
                array(
                        'type'    => 'user',
                        'value'   => 'namelink',
                        'heading' => get_string('userfullname', 'local_reportbuilder'),
                ),
                array(
                        'type'    => 'coursera',
                        'value'   => 'iscompleted',
                        'heading' => get_string('iscompleted', 'rbsource_courseralearners'),
                ),
                array(
                        'type'    => 'coursera',
                        'value'   => 'durationofeligibility',
                        'heading' => get_string('durationofeligibility', 'rbsource_courseralearners'),
                ),
                array(
                        'type'    => 'coursera',
                        'value'   => 'timeend',
                        'heading' => get_string('timeend', 'rbsource_courseralearners'),
                ),
                array(
                        'type'    => 'coursera',
                        'value'   => 'extendeligibility',
                        'heading' => get_string('extendeligibility', 'rbsource_courseralearners'),
                ),
        );

        $cminstanceid = array_key_exists('cminstanceid', $data) ? $data['cminstanceid'] : null;
        $this->embeddedparams['cminstanceid'] = $cminstanceid;

        parent::__construct();
    }

    public function is_capable() {
        global $PAGE;

        return has_capability('mod/coursera:extendeligibility', $PAGE->context);
    }
}
