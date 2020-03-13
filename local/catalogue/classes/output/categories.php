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
 * Class containing data for my overview block.
 *
 * @package    local_catalogue
 * @copyright  2019 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_catalogue\output;
defined('MOODLE_INTERNAL') || die();

use renderable;
use renderer_base;
use templatable;

require_once($CFG->dirroot . '/local/catalogue/lib.php');
require_once($CFG->dirroot. '/course/lib.php');
require_once($CFG->dirroot. '/course/renderer.php');
require_once($CFG->dirroot. '/lib/coursecatlib.php');

/**
 * Class containing data for my overview block.
 *
 * @copyright  2019 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class categories implements renderable, templatable {

    /**
     * Category to show
     * @var int category id
     */
    private $categoryid;

    /**
     * subcategories
     * @var array cat
     */
    public $subcategories;

    /**
     * hassubcategories
     * @var bool true/fals
     */
    public $hassubcategories;


    /**
     * Category record
     */
    private $record;

    /**
     * Selected category to view.
     * @var [type]
     */
    private $viewcat;


    /**
     * main constructor.
     * Initialize the user preferences
     *
     * @param int $cat Category id
     * @param int $viewcate Display this category only.
     * @param bool $isrootcategory Is this the root category?
     */
    public function __construct($cat, $viewcat = null) {
        global $DB;
        $this->record = $DB->get_record('course_categories', ['id' => $cat]);
        $this->subcategories = array_values($DB->get_records('course_categories', ['parent' => $cat, 'visible' => 1]));
        $this->load_subsub($viewcat);
        if ($viewcat) {
            $this->viewcat = $viewcat;
            $this->load_subcategory_info();
        } else {
            $this->viewcat = false;
        }
        $this->get_category_images();
        $this->categoryid = $cat;
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array Context variables for the template
     */
    public function export_for_template(renderer_base $output) {
        global $CFG, $USER;
        $defaultcategoryimage = $output->image_url('categoryimage', 'local_catalogue')->out();
        $baseurl = new \moodle_url('/local/catalogue/index.php');

        $canedit = false;
        if (isset($USER->editing) && is_siteadmin()) {
            if ($USER->editing == 1) {
                $canedit = true;
            }
        }
        $defaultvariables = [
            'record' => $this->record,
            'subcategories' => $this->subcategories,
            'baseurl' => $baseurl,
            'viewsingle' => $this->viewcat,
            'hassubcategories' => $this->hassubcategories,
            'editbutton' => $this->catalogue_edit_button($output)
        ];

        return $defaultvariables;

    }

    private function get_category_images() {
        foreach ($this->subcategories as &$cat) {
            $cat->image = $this->categoryimage($cat->id);
            $cat->catalogueimage = $this->catalogueimage($cat->id);
            if (!$cat->image) {
                $cat->defaultimage = true;
            }
        }
    }

    private function load_subsub($viewcat) {
        global $DB;
        foreach ($this->subcategories as &$cat) {
            if ($cat->id != $viewcat) {
                continue;
            }

            $subcategories = \coursecat::get($cat->id)->get_children();

            $cat->subsub = [];
            foreach ($subcategories as $subcat) {
                $catids = array_merge([$subcat->id], \coursecat::get($subcat->id)->get_all_children_ids());
                $cat->subsub[] = ['name' => $subcat->name, 'id' => implode('-', $catids)];
            }

            if (count($cat->subsub)) {
                $this->hassubcategories = true;
            }
            if (count($cat->subsub) > 5) {
                $cat->hasmoresubcat = true;
            }
            $cat->image = $this->categoryimage($cat->id);
            if (!$cat->image) {
                $cat->defaultimage = true;
            }
        }
    }

    private function load_subcategory_info() {
        foreach ($this->subcategories as &$cat) {
            $chelper = new \coursecat_helper();
            $cat->incatalogue = true;
            $cat->desc = $chelper->get_category_formatted_description($cat);
            $cat->editlink = new \moodle_url('/course/editcategory.php', ['id' => $cat->id]);
            if ($cat->id == $this->viewcat) {
                $cat->active = true;
            }
        }
    }

    private function catalogueimage($categoryid) {
        global $OUTPUT;
        // The theme renderer will add resize.
        $arupboost = new \theme_arupboost\arupboost();
        return $arupboost->arupboostimage('catalogue', $categoryid, $this->categoryimage($categoryid),
            $OUTPUT->image_url('categoryimage', 'local_catalogue')->out());
    }

    /**
     * Get cover image for context
     *
     * @param int $categoryid
     * @return bool|stored_file
     * @throws \coding_exception
     */
    private function categoryimage($categoryid) {
        $context = get_category_or_system_context($categoryid);
        $contextid = $context->id;
        $fs = get_file_storage();

        $files = $fs->get_area_files($contextid, 'local_catalogue', 'categoryimage', 0, "itemid, filepath, filename", false);
        if (!$files) {
            return false;
        }
        if (count($files) > 1) {
            // Note this is a coding exception and not a moodle exception because there should never be more than one
            // file in this area, where as the course summary files area can in some circumstances have more than on file.
            throw new \coding_exception('Multiple files found in course coverimage area (context '.$contextid.')');
        }
        $file = (end($files));

        return $imageurl = \moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_timemodified(), // Used as a cache buster.
            $file->get_filepath(),
            $file->get_filename()
        );
    }

    /**
     * Create the edit on / off button.
     */
    private function catalogue_edit_button($output) {
        global $PAGE;
        if ($PAGE->user_allowed_editing()) {
            $options = [];
            $options['sesskey'] = sesskey();
            if ($PAGE->user_is_editing()) {
                $options['edit'] = 0;
                $string = get_string('turneditingoff');
            } else {
                $options['edit'] = 1;
                $string = get_string('turneditingon');
            }
            $button = new \single_button(new \moodle_url('/local/catalogue', $options), $string, 'get');
            return $output->render($button);
        }
    }
}