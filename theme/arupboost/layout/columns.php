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
 * A two column layout for the arup boost theme.
 *
 * @package   theme_arupboost
 * @copyright 2019 Bas Brands <bas@sonsbeekmedia.nl>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

user_preference_allow_ajax_update('drawer-open-nav', PARAM_ALPHA);

if (isloggedin()) {
    $navdraweropen = (get_user_preferences('drawer-open-nav', 'true') == 'true');
} else {
    $navdraweropen = false;
}

$arupboost = new \theme_arupboost\arupboost();
$arupboost->removenav();

$extraclasses = [];
if ($navdraweropen) {
    $extraclasses[] = 'drawer-open-left';
}
$bodyattributes = $OUTPUT->body_attributes($extraclasses);
$blockshtml = $OUTPUT->blocks('side-pre');
$centreclass = $this->page->user_is_editing() ?
    'fixedregion flexregion d-flex w-100 flex-wrap' : 'flexregion d-flex w-100 flex-wrap';
$blockscentre = $OUTPUT->blocks('centre', $centreclass);
$blockstop = $OUTPUT->blocks('top', 'w-100');
$blocksbottom = $OUTPUT->blocks('bottom', 'w-100');
$hasblocks = strpos($blockshtml, 'data-block=') !== false;
$hasblockscentre = strpos($blockscentre, 'data-block=') !== false;
$hasblockstop = strpos($blockstop, 'data-block=') !== false;
$hasblocksbottom = strpos($blocksbottom, 'data-block=') !== false;
$regionmainsettingsmenu = $OUTPUT->region_main_settings_menu();
$templatecontext = [
    'sitename' => format_string($SITE->shortname, true, ['context' => context_course::instance(SITEID), "escape" => false]),
    'courseid' => $COURSE->id,
    'output' => $OUTPUT,
    'sidepreblocks' => $blockshtml,
    'blockscentre' => $blockscentre,
    'blockstop' => $blockstop,
    'blocksbottom' => $blocksbottom,
    'hasblocks' => $hasblocks,
    'hasblockscentre' => $hasblockscentre,
    'hasblockstop' => $hasblockstop,
    'hasblocksbottom' => $hasblocksbottom,
    'bodyattributes' => $bodyattributes,
    'navdraweropen' => $navdraweropen,
    'regionmainsettingsmenu' => $regionmainsettingsmenu,
    'hasregionmainsettingsmenu' => !empty($regionmainsettingsmenu),
    'wwwroot' => $CFG->wwwroot
];

$templatecontext['flatnavigation'] = $PAGE->flatnav;
echo $OUTPUT->render_from_template('theme_arupboost/columns', $templatecontext);

