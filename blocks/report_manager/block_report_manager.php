<?php //$Id$
/*
 * This file is part of T0tara LMS
 *
 * Copyright (C) 2010 onwards T0tara Learning Solutions LTD
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
 * @author Eugene Venter <eugene@catalyst.net.nz>
 * @package t0tara
 * @subpackage blocks_report_manager
 */

class block_report_manager extends block_base {

    function init() {
        $this->title = get_string('title', 'block_report_manager');
    }

    function specialization() {
        $this->title = get_string('displaytitle', 'block_report_manager');
    }

    function get_content() {

        if ($this->content !== NULL) {
            return $this->content;
        }

        $totaracorerenderer = $this->page->get_renderer('local_reportbuilder');

        $this->content = new stdClass;
        $this->content->text = totara_print_report_manager();
        $this->content->footer = '';

        return $this->content;
    }

}