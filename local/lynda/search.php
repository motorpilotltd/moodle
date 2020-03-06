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
 * @package    local_lynda
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');

require_login(SITEID, false);
require_capability('local/search:view', context_system::instance());

require_once($CFG->dirroot . '/local/regions/lib.php');

$PAGE->set_url(new moodle_url('/local/lynda/search.php'));

$PAGE->set_pagelayout('base');

$renderer = $PAGE->get_renderer('local_lynda');

$search = optional_param('search', '', PARAM_TEXT);
$page = optional_param('page', 1, PARAM_INT) - 1; // courses run form zero-index
$perpage = optional_param('perpage', 10, PARAM_INT);

$context = context_system::instance();
if (has_capability('local/lynda:manage', $context)) {
    echo $OUTPUT->single_button(
            new moodle_url('/local/lynda/manage.php'),
            get_string('managecourses', 'local_lynda')
    );
}

$userregion = local_regions_get_user_region($USER);
if (isset($userregion->regionid)) {
    $results = new \local_lynda\searchresults($search, $userregion->geotapsregionid, $page, $perpage);
    $results->dosearch();

    echo $renderer->search_results($results);
}
