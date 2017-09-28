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

defined('MOODLE_INTERNAL') || die();

function local_mylearning_extend_navigation($navigation) {
    global $CFG, $USER;

    if (!isloggedin() || !get_config('local_mylearning', 'version')) {
        return;
    }

    $node = $navigation->get('home');
    $node->action = null;
    $node->type = navigation_node::NODETYPE_BRANCH;
    /*
    $node->add(get_string('me', 'block_arup_mylearning'),
            new moodle_url('/my/index.php', array('tab' => 'me')),
            navigation_node::NODETYPE_LEAF,
            get_string('me', 'block_arup_mylearning'),
            'arup_mylearning_me');
    */
    $node->add(get_string('overview', 'block_arup_mylearning'),
            new moodle_url('/my/index.php', array('tab' => 'overview')),
            navigation_node::NODETYPE_LEAF,
            get_string('overview', 'block_arup_mylearning'),
            'arup_mylearning_overview');

    require_once($CFG->dirroot.'/blocks/arup_mylearning/content.php');
    $hasrole = block_arup_mylearning_content::user_has_role_assignments($USER->id, array('editingteacher', 'teacher'));
    if ($hasrole) {
        $node->add(get_string('myteaching', 'block_arup_mylearning'),
                new moodle_url('/my/index.php', array('tab' => 'myteaching')),
                navigation_node::NODETYPE_LEAF,
                get_string('myteaching', 'block_arup_mylearning'),
                'arup_mylearning_myteaching');
    }

    $node->add(get_string('myhistory', 'block_arup_mylearning'),
            new moodle_url('/my/index.php', array('tab' => 'myhistory')),
            navigation_node::NODETYPE_LEAF,
            get_string('myhistory', 'block_arup_mylearning'),
            'arup_mylearning_myhistory');
    /*
    $node->add(get_string('bookmarked', 'block_arup_mylearning'),
            new moodle_url('/my/index.php', array('tab' => 'bookmarked')),
            navigation_node::NODETYPE_LEAF,
            get_string('bookmarked', 'block_arup_mylearning'),
            'arup_mylearning_bookmarked');
    */
}
