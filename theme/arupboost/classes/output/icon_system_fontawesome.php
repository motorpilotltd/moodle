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
 * Overridden fontawesome icons.
 *
 * @package     theme_arupboost
 * @copyright   2019 Moodle
 * @author      Bas Brands <bas@moodle.com>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace theme_arupboost\output;

defined('MOODLE_INTERNAL') || die();

/**
 * Class overriding some of the Moodle default FontAwesome icons.
 *
 *
 * @package    theme_arupboost
 * @copyright  2019 Moodle
 * @author     Bas Brands <bas@moodle.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class icon_system_fontawesome extends \core\output\icon_system_fontawesome {

    /**
     * @var array $map Cached map of moodle icon names to font awesome icon names.
     */
    private $map = [];


    /**
     * Change the core icon map
     * @return Array replaced icons.
     */
    public function get_core_icon_map() {
        $iconmap = parent::get_core_icon_map();

        $iconmap['theme:a/film'] = 'fa-film';
        $iconmap['theme:a/appraisal'] = 'fa-comment-alt';
        $iconmap['theme:a/catalogue'] = 'fa-school';
        $iconmap['theme:a/envelope'] = 'fa-envelope';
        $iconmap['theme:a/phone'] = 'fa-phone';
        $iconmap['theme:a/clock'] = 'fa-clock';
        $iconmap['theme:t/sort_by'] = 'fa-sort-amount-asc';
        $iconmap['theme:a/help'] = 'fa-question-circle';

        $corereplacements = [
            'core:a/add_file' => 'fa-file',
            'core:a/create_folder' => 'fa-folder',
            'core:b/document-new' => 'fa-file',
            'core:b/edit-copy' => 'fa-file',
            'core:e/emoticons' => 'fa-smile',
            'core:e/insert_edit_image' => 'fa-images',
            'core:e/insert_edit_video' => 'fa-file-video',
            'atto_recordrtc:i/videortc' => 'fa-video',
            'core:e/insert_nonbreaking_space' => 'fa-square',
            'core:e/insert_time' => 'fa-clock',
            'core:e/manage_files' => 'fa-file',
            'core:e/new_document' => 'fa-file',
            'core:e/save' => 'fa-save',
            'core:e/special_character' => 'fa-keyboard',
            'core:e/text_highlight_picker' => 'fa-lightbulb',
            'core:e/text_highlight' => 'fa-lightbulb',
            'theme:fp/add_file' => 'fa-file',
            'theme:fp/create_folder' => 'fa-folder',
            'core:i/backup' => 'fa-archive',
            'core:i/calendareventtime' => 'fa-clock',
            'core:i/competencies' => 'fa-check-square',
            'core:i/completion_self' => 'fa-user',
            'core:i/duration' => 'fa-clock',
            'core:i/groupv' => 'fa-user-circle',
            'core:i/manual_item' => 'fa-square',
            'core:i/marker' => 'fa-circle',
            'core:i/news' => 'fa-newspaper',
            'core:i/nosubcat' => 'fa-plus-square',
            'core:i/permissions' => 'fa-pencil-square',
            'core:i/privatefiles' => 'fa-file',
            'core:i/repository' => 'fa-hdd',
            'core:i/scheduled' => 'fa-calendar-check',
            'core:i/section' => 'fa-folder',
            'core:i/unchecked' => 'fa-square',
            'core:i/unflagged' => 'fa-flag',
            'core:t/collapsed_empty_rtl' => 'fa-plus-square',
            'core:t/collapsed_empty' => 'fa-plus-square',
            'core:t/email' => 'fa-envelope',
            'core:t/groupv' => 'fa-user-circle',
            'core:t/switch_whole' => 'fa-square',
            'core:e/remove_link' => 'fa-unlink',
            'core:i/next' => 'fa-chevron-right',
            'core:i/previous' => 'fa-chevron-left',
        ];

        return array_merge($iconmap, $corereplacements);
    }
}