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

require_once("../../config.php");

require_once($CFG->dirroot.'/blocks/arup_mylearning/tabs.php');
require_once($CFG->dirroot.'/blocks/arup_mylearning/content.php');

try {
    require_login();
    require_sesskey();

    $action = required_param('action', PARAM_ALPHA);
    $currenttab = required_param('currenttab', PARAM_ALPHA);
    $instance = required_param('instance', PARAM_INT);

    $PAGE->set_context(context_block::instance($instance));
    $PAGE->set_url('/blocks/arup_mylearning/ajax.php');

    switch ($action) {
        case 'tabs' :
            $tabs = new block_arup_mylearning_tabs();
            echo $tabs->get_tab_html($currenttab);
            exit;
            break;
        case 'content' :
            $content = new block_arup_mylearning_content($instance);
            echo $content->get_content($currenttab);
            exit;
            break;
    }
} catch (Exception $e) {
    echo $e->getMessage();
}
