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
 * Renderers to align Moodle's HTML with that expected by Bootstrap
 *
 * @package    theme_arupboost
 * @copyright  2019 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_arupboost\output;

defined('MOODLE_INTERNAL') || die;

use html_writer;
/**
 * Theme renderer
 *
 * @package    theme_arupboost
 * @copyright  2019 Bas Brands <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class core_renderer extends \theme_boost\output\core_renderer {

    private $blockcount;

    private $theme;

    /**
     * Wrapper for header elements.
     *
     * @return string HTML to display the main header.
     */
    public function full_header() {
        global $PAGE, $COURSE;

        $header = new \stdClass();
        $header->settingsmenu = $this->context_header_settings_menu();
        $header->contextheader = $this->context_header();
        $header->hasnavbar = empty($PAGE->layout_options['nonavbar']);
        $header->hasheader = empty($PAGE->layout_options['nocontextheader']);
        $header->navbar = $this->navbar();
        $header->pageheadingbutton = $this->page_heading_button();
        $header->courseheader = $this->course_header();
        $header->notifications = $this->notifications();

        $context = $PAGE->context;
        return $this->render_from_template('theme_arupboost/header', $header);
    }

    /**
     * Shows the user time.
     */
    public function display_usertime() {
        $usertimezone = '';
        if (!empty($USER->timezone)) {
            $usertimezone = $USER->timezone;
            $tzclass = '';
        } else {
            $tzclass = ' notset';
        }
        if ($usertimezone == 99) {
            $tzclass = ' notset';
        }

        $usertimezone = \core_date::get_user_timezone_object();
        $time = new \DateTime();
        $time->setTimezone($usertimezone);
        $usertime = '<div class="mr-1">' . $time->format('G:i') . '</div><div class="tz">' . $time->format('(T)') . '</div>';
        return $usertime;
    }

    /**
     * Returns an array of timezones for this user.
     */
    public function timezone_countries() {
        global $USER, $DB, $CFG;

        if (!isloggedin() || isguestuser()) {
            return '';
        }

        if (!file_exists($CFG->dirroot . '/local/timezones/version.php')) {
            return '';
        }
        $countries = array();

        $aruptimezones = $DB->get_records('local_timezones');

        if (count($aruptimezones) > 0) {
            foreach ($aruptimezones as $country) {
                $thiscountry = new \stdClass();
                $thiscountry->name = $country->display;
                $thiscountry->timezone = $country->timezone;
                $countries[] = $thiscountry;
            }
        } else {
            $countries = \core_date::get_list_of_timezones($USER->timezone, true);
            foreach ($countries as $country) {
                $thiscountry = new \stdClass();
                $thiscountry->name = $country;
                $countries[] = $thiscountry;
            }
        }
        return $countries;
    }

    /**
     * Create the content for the arup mega menu
     * @param  Object $user      The user object
     * @return String Html containing the arup megamenu.
     */
    public function mega_menu($user = null) {

        $template = new \stdClass();

        // If during initial install, return the empty return string.
        if (during_initial_install()) {
            return "";
        }

        $titles = ['Study', 'About', 'Courses', 'Faculties', 'Skills & Expertise', 'Research'];

        $template->sections = [];

        // Create Section.
        for ($a = 0; $a < 6; $a++) {
            $section = new \stdClass();
            $section->title = $titles[$a];
            $section->content = [];

            // Create Action.
            for ($i = 0; $i < rand(3, 10); $i++) {
                $action = new \stdClass();
                $action->title = 'My Learning' . $i;
                $action->url = "#";
                $section->content[] = $action;
            }

            $template->sections[] = $section;
        }

        return $this->render_from_template('theme_arupboost/megamenu', $template);
    }

    /**
     * Render the navbar with clear hidden elements.
     */
    public function navbar() {
        $template = new \stdClass();
        $template->breadcrumbs = [];
        $template->wwwroot = new \moodle_url("/");

        $items = $this->page->navbar->get_items();
        if (!empty($items) && preg_match('/page-local-onlineappraisal/', $this->page->bodyid)) {
            array_shift($items);
        }
        if (empty($items)) { // MDL-46107.
            return '';
        }
        $breadcrumbs = '';
        foreach ($items as $item) {
            $item->hideicon = true;
            if ($item->hidden) {
                $item->text .= get_string('hiddennavitem', 'theme_arupboost');
            }
            if ($item->text == get_string('home')) {
                $template->hashome = true;
                continue;
            }
            $template->items[] = $this->render($item);
        }

        return $this->render_from_template('theme_arupboost/breadcrumb', $template);
    }

    /**
     * Whether a user is logged in.
     *
     * @return bool
     */
    public function is_logged_in() {
        return isloggedin();
    }

    /**
     * Override parent block renderer to output blocks with class flexblock for front page
     */
    public function block(\block_contents $bc, $region) {
        $bc = clone($bc); // Avoid messing up the object passed in.
        if (empty($bc->blockinstanceid) || !strip_tags($bc->title)) {
            $bc->collapsible = \block_contents::NOT_HIDEABLE;
        }

        $id = !empty($bc->attributes['id']) ? $bc->attributes['id'] : uniqid('block-');
        $context = new \stdClass();
        $context->skipid = $bc->skipid;
        $context->blockinstanceid = $bc->blockinstanceid;
        $context->dockable = $bc->dockable;
        $context->id = $id;
        $context->hidden = $bc->collapsible == \block_contents::HIDDEN;
        $context->skiptitle = strip_tags($bc->title);
        $context->showskiplink = !empty($context->skiptitle);
        $context->arialabel = $bc->arialabel;
        $context->ariarole = !empty($bc->attributes['role']) ? $bc->attributes['role'] : 'complementary';
        $context->type = $bc->attributes['data-block'];
        $context->classes = $bc->attributes['class'];
        $context->title = $bc->title;
        $context->content = $bc->content;
        $context->annotation = $bc->annotation;
        $context->footer = $bc->footer;
        $context->hascontrols = !empty($bc->controls);
        if ($context->hascontrols) {
            $context->controls = $this->block_controls($bc->controls, $id);
        }

        $output = $this->render_from_template('core/block', $context);

        if ($region == 'centre'  && !$this->page->user_is_editing()) {
            return \html_writer::tag('div', $output, array('class' => 'flexblock'));
        } else {
            return $output;
        }
    }

    public function arup_logo() {
        if (preg_match('/page-local-onlineappraisal/', $this->page->bodyid)) {
            return false;
        } else {
            return $this->image_url('Arup-Moodle', 'theme_arupboost');
        }
    }

    public function appraisal_logo() {
        if (preg_match('/page-local-onlineappraisal/', $this->page->bodyid)) {
            return $this->image_url('appraisal', 'theme_arupboost');
        }
    }

    public function arup_pagecontent() {
        global $CFG;
        $layout = $this->page->pagelayout;
        if ($layout == 'frontpage') {
            $theme = \theme_config::load('arupboost');
            $template = new \stdClass();
            $template->output = $this;
            $template->wwwroot = $CFG->wwwroot;
            $template->frontpageimage = $theme->setting_file_url('frontpageimage', 'frontpageimage');
            $template->settingsmenu = $this->context_header_settings_menu();
            return $this->render_from_template('theme_arupboost/'.$layout.'_content', $template);
        }
    }

    public function pagecontextid() {
        return $this->page->context->id;
    }

    public function theme_image_loginbackground() {
        $theme = \theme_config::load('arupboost');
        return $theme->setting_file_url('loginbackground', 'loginbackground');
    }

    public function theme_header_image() {
        $theme = \theme_config::load('arupboost');
        if ($image = $theme->setting_file_url('frontpageimage', 'frontpageimage')) {
            return $image;
        } else {
            return $this->image_url('header/bw-ove', 'theme_arupboost');
        }
    }

    /**
     * Does this theme have a megamenu?
     *
     * If enabled ensure you set the scss variable $hasmegamenu in variables.scss to true.
     * @return {Bool} false
     */
    public function hasmegamenu() {
        return false;
    }


    /**
     * Renders a custom menu object (located in outputcomponents.php)
     *
     * The custom menu this method produces makes use of the YUI3 menunav widget
     * and requires very specific html elements and classes.
     *
     * @staticvar int $menucount
     * @param custom_menu $menu
     * @return string
     */
    protected function render_custom_menu(\custom_menu $menu) {
        global $CFG;

        $content = '';
        $langs = get_string_manager()->get_list_of_translations();
        $haslangmenu = $this->lang_menu() != '';

        if (!$menu->has_children() && !$haslangmenu) {
            return '';
        }

        $custommenucontent = '';
        foreach ($menu->get_children() as $item) {
            $context = $item->export_for_template($this);
            $custommenucontent .= $this->render_from_template('theme_arupboost/custom_menu_item', $context);
        }

        $inappraisal = preg_match('/page-local-onlineappraisal/', $this->page->bodyid);
        $appraisalmenucontent = $this->appraisal_dropdown_menu($inappraisal);
        if ($inappraisal) {
            $content .= $appraisalmenucontent;
        } else {
            $content .= $custommenucontent;
        }

        return $content;
    }


    /**
     * Renders the appraisal custom menu for the navbar.
     * @param  Bool $inappraisal Are we in a appraisal?
     * @return string Dropdown menu HTML.
     */
    protected function appraisal_dropdown_menu($inappraisal) {
        global $CFG;
        $classfile = $CFG->dirroot . '/local/onlineappraisal/classes/navbarmenu.php';
        if (file_exists($classfile)) {
            $renderer = $this->page->get_renderer('local_onlineappraisal', 'navbarmenu');
            $navbarmenu = new \local_onlineappraisal\navbarmenu();
            $template = new \local_onlineappraisal\output\navbarmenu\navbarmenu($navbarmenu);
            $template->inappraisal = $inappraisal;
            return $renderer->render($template);
        }
    }

    /**
     * Render notification boxes for the user.
     *
     */
    public function notifications() {
        global $USER;
        // Should not be shown on the frontpage.
        if ($this->page->bodyid == 'page-site-index') {
            return '';
        }
        if (empty($this->theme)) {
            $this->theme = \theme_config::load('arupboost');
        }

        $alerts = array();
        if (!empty($this->theme->settings->notice)) {
            $type = 'info';
            if (!empty($this->theme->settings->noticecolor)) {
                $type = $this->theme->settings->noticecolor;
            }
            $text = $this->theme->settings->notice;
            $alerts[] = $this->notification($text, $type);
        }
        if (isloggedin() && !isguestuser()) {
            if (empty($USER->timezone) || ($USER->timezone == 99)) {
                $type = 'warning';
                $settingurl = new \moodle_url('/user/edit.php#fitem_id_city', array('userid' => $USER->id));
                $text = get_string('pleasesettimezone', 'theme_arupboost', $settingurl->out());
                 $alerts[] = $this->notification($text, $type);
            }
        }

        if (count($alerts)) {
            $templateinfo = new \stdClass();
            $templateinfo->alerts = $alerts;
            return $this->render_from_template('theme_arupboost/notifications', $templateinfo);
        }
    }

    /**
     * Print out the arup copyright
     * @return string copyright HTML.
     */
    public function copyright() {
        return get_string('copyright', 'theme_arupboost', date('Y'));
    }

    /**
     * Render the page footer links.
     *
     * @return string Footer links HTML.
     */
    public function footerlinks() {
        $templateinfo = new \stdClass();

        if (empty($this->theme)) {
            $this->theme = \theme_config::load('arupboost');
        }

        if (isset($this->theme->settings->footerlinks) &&
            !empty($this->theme->settings->footerlinks) &&
            isloggedin()) {

            $footerlinks = str_ireplace("\r\n", "\n", $this->theme->settings->footerlinks);
            $links = explode("\n", $footerlinks);

            foreach ($links as $link) {
                $footerlink = new \stdClass();
                $linkinfo = explode('|', $link);

                if (count($linkinfo) == 2) {
                    $url = new \moodle_url($linkinfo[1]);
                    $footerlink->link = \html_writer::link($url, $linkinfo[0]);
                    $templateinfo->footerlinks[] = $footerlink;
                }
            }
        }
        return $this->render_from_template('theme_arupboost/footer_links', $templateinfo);
    }

    public function edit_button(\moodle_url $url) {
        // If setting editbuttonincourseheader ist checked give out the edit on / off button in course header.
        if (get_config('theme_arupboost', 'courseeditbutton') == '1') {
            return \core_renderer::edit_button($url);
        }
    }
}
