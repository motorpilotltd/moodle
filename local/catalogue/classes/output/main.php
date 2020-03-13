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

/**
 * Class containing data for the catalogue.
 *
 * @copyright  2019 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class main implements renderable, templatable {

    /**
     * Store the view preference
     *
     * @var string String matching the view/display constants defined in myoverview/lib.php
     */
    private $view;

    /**
     * Store the paging preference
     *
     * @var string String matching the paging constants defined in myoverview/lib.php
     */
    private $paging;

    /**
     * Regions or selection
     *
     * @var array List of regions
     */
    private $regions;

    /**
     * Category to show
     * @var int category id
     */
    private $categoryid;

    /**
     * subcategories
     * @var array cat
     */
    private $subcategories;



    /**
     * main constructor.
     * Initialize the user preferences
     *
     * @param string $view Display user preference
     */
    public function __construct($view, $paging, $cat) {
        global $DB;
        $this->view = $view ? $view : LOCAL_CATALOGUE_VIEW_CARD;
        $this->paging = $paging ? $paging : 12;
        $this->regions = array_values($DB->get_records('local_regions_reg', ['userselectable' => 1]));
        $this->categoryid = $cat;
    }


    /**
     * Export this data so it can be used as the context for a mustache template.
     *
     * @param \renderer_base $output
     * @return array Context variables for the template
     */
    public function export_for_template(renderer_base $output) {
        global $CFG, $DB, $USER;

        $nocoursesurl = $output->image_url('courses', 'local_catalogue')->out();

        $selectedcat = (int) get_config('local_catalogue', 'root_category');

        $selectcategorystring = get_string('selectcategory', 'local_catalogue');

        $prefcategoryid = get_user_preferences('local_catalogue_cat', $selectedcat);

        $showcat = optional_param('showcatid', $prefcategoryid, PARAM_INT);

        $page = optional_param('page', '', PARAM_RAW);

        $category = new \local_catalogue\output\categories($selectedcat, $showcat);

        $searchfor = optional_param('search', '', PARAM_RAW);

        $baseurl = new \moodle_url('/local/catalogue/index.php');

        if ($selectedcat) {
            foreach ($category->subcategories as &$cat) {
                if ($cat->id == $showcat) {
                    $cat->active = 'active';
                    $selectcategorystring = $cat->name;
                }
            }
        }

        // Reset the region preference when the user re-visits the page.
        $pref = '[{}]';
        if ($page == 'new') {
            if ($regionuse = $DB->get_record('local_regions_use', ['userid' => $USER->id])) {
                $region = [];
                $region[] = (object)[
                    'type' => 'region',
                    'value' => $regionuse->regionid
                ];
                $pref = json_encode($region);
            }
        }

        $defaultvariables = [
            'showtoplevel' => ($prefcategoryid == $selectedcat),
            'nocoursesimg' => $nocoursesurl,
            'view' => $this->view,
            'paging' => $this->paging,
            'regions' => $this->regions,
            'categoryid' => $showcat,
            'myurl' => new \moodle_url('/local/catalogue/index.php'),
            'selectcategorystring' => $selectcategorystring,
            'filter' => $pref,
            'searchword' => $searchfor,
            'baseurl' => $baseurl,
            'maxbites' => get_max_upload_file_size($CFG->maxbytes)
        ];

        $defaultvariables[$this->view] = true;

        $categories = $category->export_for_template($output);

        return array_merge($defaultvariables, $categories);
    }
}