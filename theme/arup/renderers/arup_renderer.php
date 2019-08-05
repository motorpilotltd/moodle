<?php
// This file is part of the arup theme for Moodle
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
 * Theme arup config file.
 *
 * @package    theme_arup
 * @copyright  2016 Arup
 * @author     Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class theme_arup_html_renderer extends plugin_renderer_base {

    private $theme;

    /**
     * Render notification boxes for the user.
     * Should not be shown on the frontpage.
     */
    public function notifications() {
        if ($this->page->bodyid == 'page-site-index') {
            return '';
        }
        global $USER;
        if (empty($this->theme)) {
            $this->theme = theme_config::load('arup');
        }

        $alerts = array();
        if (!empty($this->theme->settings->notice)) {
            $alert = new stdClass();
            $alert->color = 'info';
            if (!empty($this->theme->settings->noticecolor)) {
                $alert->color = $this->theme->settings->noticecolor;
            }
            $alert->text = $this->theme->settings->notice;
            $alerts[] = $alert;
        }
        if (isloggedin() && !isguestuser()) {
            if (empty($USER->timezone) || ($USER->timezone == 99)) {
                $alert = new stdClass();
                $alert->color = 'warning';
                $settingurl = new moodle_url('/user/edit.php#fitem_id_city', array('userid' => $USER->id));
                $alert->text = get_string('pleasesettimezone', 'theme_arup', $settingurl->out());
                $alerts[] = $alert;
            }
        }

        if (count($alerts)) {
            $templateinfo = new stdClass();
            $templateinfo->alerts = $alerts;
            return $this->render_from_template('theme_arup/notifications', $templateinfo);
        }
    }

    /**
     * Loaded by default template. Use this to load any custom code that should go
     * before the main content.
     */
    public function pre_content() {
        // if ($this->page->bodyid == 'page-site-index') {
        //     $catid = optional_param('catid', 0, PARAM_INT);
        //     return $this->availablecategories($catid);
        // }
    }

    /**
     * Loaded by default template. Use this to load any custom code that should go
     * after the main content.
     */
    public function post_content() {
    }

    /**
     * Render the page logo.
     */
    public function page_logo() {
        return html_writer::empty_tag('img',
            array('src' => $this->image_url('Arup_logo', 'theme_arup'),
                'class' => 'img-responsive'));
    }

    /**
     * Render the page footer links.
     */
    public function page_footer() {
        $templateinfo = new stdClass();
        $templateinfo->date = date('Y');

        if (isset($this->page->theme->settings->footerlinks) && !empty($this->page->theme->settings->footerlinks) && isloggedin()) {
            $footerlinks = str_ireplace("\r\n", "\n", $this->page->theme->settings->footerlinks);
            $links = explode("\n", $footerlinks);
            foreach ($links as $link) {
                $footerlink = new stdClass();
                $linkinfo = explode('|', $link);
                if (count($linkinfo) == 2) {
                    $url = new moodle_url($linkinfo[1]);
                    $footerlink->link = ' / ' . html_writer::link($url, $linkinfo[0]);
                    $templateinfo->footerlinks[] = $footerlink;
                }
            }
        }

        return $this->render_from_template('theme_arup/footer', $templateinfo);
    }

    /**
     * Render the searchbox shown in the top navbar.
     */
    public function searchbox($value = '') {
        if (!has_capability('local/search:view', context_system::instance())) {
            return '';
        }
        $configsearchurl = get_config('theme_arup', 'searchlocation');
        $searchurl = empty($configsearchurl) ? '/course/search.php' : $configsearchurl;
        $templateinfo = new stdClass();
        $templateinfo->formaction = new moodle_url($searchurl);
        return $this->render_from_template('theme_arup/navbarsearch', $templateinfo);
    }

    /**
     * Render the course cards.
     */
    public function courseboxes($catid) {
        global $DB, $OUTPUT;

        $allcourses = $DB->get_records('course', array('visible' => 1, 'category' => $catid));

        $mycourses = enrol_get_my_courses();

        $courses = array();

        $timenow = time();

        foreach ($allcourses as $acourse) {
            if ($acourse->id == 1) {
                continue;
            }
            // JUST FOR DUMMY DATA
            $acourse->hasvideo = false;
            if ($acourse->id == 3) {
                $acourse->hasvideo = true;
            }
            $coursecontext = context_course::instance($acourse->id);

            $acourse->courseimage = $this->courseimage($acourse);

            if (empty($acourse->summary)) {
                $acourse->summary = get_string('summary', 'theme_arup');
            } else {
                $context = context_course::instance($acourse->id);
                $acourse->summary = file_rewrite_pluginfile_urls($acourse->summary,
                    'pluginfile.php', $context->id, 'course', 'summary', null);
            }

            if (strlen($acourse->summary) > 200) {
                //$acourse->summary = substr($acourse->summary, 0, 200) . '...';
            }

            $acourse->hasprogress = false;

            if (array_key_exists($acourse->id, $mycourses)) {
                $acourse->hasprogress = true;
            }
            $acourse->courselink = new moodle_url('/course/view.php', array('id' => $acourse->id));


            if (!isloggedin() || isguestuser()) {
                $acourse->hasprogress = false;
            }

            $acourse->hasprogress = true;

            $acourse->progress = $this->course_progress($acourse->id);

            $acourse->coursedate = userdate($acourse->startdate, get_string('strftimedayshort'));

            $courses[] = $acourse;
        }
        return $courses;
    }

    /**
     * Render the category cards.
     */
    public function categoryboxes($catid = 0) {
        global $DB, $OUTPUT, $PAGE;

        $categories = $DB->get_records('course_categories', array('visible' => 1));

        if ($catid > 0) {
            $categories[$catid] = $DB->get_record('course_categories', array('id' => $catid));
        }

        $timenow = time();
        $returncategories = array();

        $editing = $PAGE->user_is_editing();
        if (empty($this->theme)) {
            $this->theme = theme_config::load('arup');
        }
        //echo '<pre>' . print_r($categories,true) . '</pre>';
        ksort($categories);

        $checkcat = $categories;

        foreach ($categories as $category) {
            if (($category->parent != $catid) && ($category->id != $catid)) {
                continue;
            }
            $categorysetting = 'categorybackground' . $category->id;
            if (!empty($this->theme->settings->$categorysetting)) {
                $category->image = $this->theme->setting_file_url($categorysetting, $categorysetting);
            }

            if ($category->numcourses = $DB->count_records('course', array('category' => $category->id, 'visible' => 1))) {
                if ($category->numcourses > 1) {
                    $category->multicourses = true;
                    $category->singlecourse = false;
                } else {
                    $category->multicourses = false;
                    $category->singlecourse = true;
                }
            } else {
                $category->numcourses = 0;
                $category->multicourses = true;
                $category->singlecourse = false;
            }

            if ($category->id == 12) {
                $tag1 = new stdClass();
                $tag1->tag = 'automotive';
                $tag1->id = 1;
                $tag2 = new stdClass();
                $tag2->tag = 'math';
                $tag2->id = 2;
                $category->tags = array($tag1, $tag2);
            }

            if ($category->parent != 0) {
                $category->parenturl = new moodle_url('/local/accordion/index.php', array('catalogue' => 'card', 'id' => $category->parent));
                $category->parentname = $categories[$category->parent]->name;
            } else if ($catid) {
                $category->parenturl = new moodle_url('/local/accordion/index.php', array('catalogue' => 'card', 'id' => $catid));
                $category->parentname = get_string('toplevel', 'theme_arup');
            }

            $category->childcategories = 0;
            foreach ($categories as $subcat) {
                if ($subcat->parent == $category->id) {
                    $category->childcategories++;
                }
            }
            if ($category->childcategories > 1) {
                $category->multichild = true;
            } else {
                $category->singlechild = true;
            }
            $context = context_coursecat::instance($category->id);
            $category->description = file_rewrite_pluginfile_urls($category->description,
                    'pluginfile.php', $context->id, 'coursecat', 'description', null);
            $category->descriptiontext = format_text($category->description, $category->descriptionformat);
            $category->editing = $editing;
            $category->editlink = new moodle_url('/course/editcategory.php', array('id' => $category->id));
            $category->categorylink = new moodle_url('/local/accordion/index.php', array('catalogue' => 'card', 'id' => $category->id));
            $returncategories[] = $category;
        }
        return $returncategories;
    }

    /**
     * Calculate and show course progress in enrolled courses.
     */
    public function course_progress($courseid, $user = null, $method = 'count') {
        global $DB, $USER;

        if (!$user) {
            $user = $USER;
        }

        $coursemodules = $DB->get_records('course_modules', array('completion' => 1, 'course' => $courseid, 'visible' =>1));

        $total = count($coursemodules);

        $done = 0;
        if ($total > 0 ) {
            $completed = $DB->get_records('course_modules_completion', array('userid' => $user->id, 'completionstate' => 1));
            if (count($completed) > 0) {
                foreach ($completed as $complete) {
                    if (array_key_exists($complete->coursemoduleid, $coursemodules)) {
                        $done++;
                    }
                }
            } else {
                return 0;
            }
        } else {
            return 0;
        }

        if ($done) {
            return round(($done / $total) * 100);
        } else {
            return 0;
        }
    }

    /**
     * Get the course image from the course settings page.
     */
    public function courseimage($course) {
        global $DB, $CFG, $OUTPUT;

        if ($advert = $DB->get_record('arupadvert', array('course' => $course->id))) {
            $advertobj = \mod_arupadvert\arupadvertdatatype::factory($advert->datatype, $advert);
            $advertobj->get_advert_block();
            if (!empty($advertobj) && !empty($advertobj->imgurl)) {
                return $advertobj->imgurl;
            }
        }

        if ($course instanceof stdClass) {
            require_once($CFG->libdir. '/coursecatlib.php');
            $course = new course_in_list($course);
        }
        $content = '';

        foreach ($course->get_course_overviewfiles() as $file) {
            $isimage = $file->is_valid_image();
            $url = file_encode_url("$CFG->wwwroot/pluginfile.php",
                    '/'. $file->get_contextid(). '/'. $file->get_component(). '/'.
                    $file->get_filearea(). $file->get_filepath(). $file->get_filename(), !$isimage);
            if ($isimage) {
                return $url;
            }
        }

        return $OUTPUT->image_url('courseimage', 'theme_arup');
    }

    /**
     * Renders the courses seen on the frontpage
     */
    public function availablecourses($catid = null) {
        global $DB;

        $templateinfo = new Object();
        $templateinfo->boxes = $this->courseboxes($catid);
        $templateinfo->categorie = $this->render_from_template('theme_arup/categories', $templateinfo);
        $templateinfo->video = $this->render_from_template('theme_arup/videos', $templateinfo);
        $content = $this->render_from_template('theme_arup/availablecourses', $templateinfo);
        return $content;
    }

    /**
     * Renders the categories and courses listing
     */
    public function availablecategories($catid = null) {
        global $DB, $CFG;

        require_once($CFG->dirroot . '/local/kaltura/locallib.php');
        $templateinfo = new Object();

        $templateinfo->categories = $this->categoryboxes($catid);
        if ($catid > 0) {
            $category = $DB->get_record('course_categories', array('id' => $catid));
            $templateinfo->hasparent = true;
            $templateinfo->parentlink = new moodle_url('/local/accordion/index.php', array('catalogue' => 'card', 'id' => $category->parent));
        }
        $templateinfo->courses = $this->courseboxes($catid);
        $content = $this->render_from_template('theme_arup/availablecategories', $templateinfo);
        return $content;
    }
}