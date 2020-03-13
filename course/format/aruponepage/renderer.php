<?php
// This file is part of the aruponepage course format
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
 * Main renderer
 *
 * @package    format_aruponepage
 * @author     2019 <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */


defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/course/format/renderer.php');

/**
 * Basic renderer for aruponepage format.
 *
 * @package    format_aruponepage
 * @author     2019 <bas@sonsbeekmedia.nl>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @license    Moodle Workplace License, distribution is restricted, contact support@moodle.com
 */
class format_aruponepage_renderer extends format_section_renderer_base {

    /**
     * Constructor method, calls the parent constructor
     *
     * @param moodle_page $page
     * @param string $target one of rendering target constants
     */
    public function __construct(moodle_page $page, $target) {
        parent::__construct($page, $target);
        $page->set_other_editing_capability('moodle/course:setcurrentsection');
    }

    /**
     * Generate the starting container html for a aruponepage of sections
     * @return string HTML to output.
     */
    protected function start_section_list() {
        return '';
    }

    /**
     * Generate the closing container html for a aruponepage of sections
     * @return string HTML to output.
     */
    protected function end_section_list() {
        return '';
    }

    /**
     * Generate the title for this section page
     * @return string the page title
     */
    protected function page_title() {
        return get_string('topicoutline');
    }

    /**
     * Generate the content to displayed on the right part of a section
     * before course modules are included
     *
     * @param stdClass|section_info $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @param bool $onsectionpage true if being printed on a section page
     * @return string HTML to output.
     */
    protected function edit_section($section, $course, $onsectionpage) {
        $template = new stdClass();

        $template->addcm = $this->course_section_add_cm_control($course, $section->section, 0);

        $controls = $this->section_edit_control_items($course, $section, $onsectionpage);
        $template->sectionmenu = $this->section_edit_control_menu($controls, $course, $section);

        return $this->render_from_template('format_aruponepage/editsection', $template);
    }

    /**
     * Generate the edit control action menu
     *
     * @param array $controls The edit control items from section_edit_control_items
     * @param stdClass $course The course entry from DB
     * @param stdClass $section The course_section entry from DB
     * @return string HTML to output.
     */
    protected function section_edit_control_menu($controls, $course, $section) {
        $o = "";
        if (!empty($controls)) {
            $menu = new action_menu();
            $menu->set_menu_trigger($this->output->pix_icon('i/settings', '', 'core'));
            $menu->attributes['class'] .= ' section-actions';
            foreach ($controls as $value) {
                $url = empty($value['url']) ? '' : $value['url'];
                $icon = empty($value['icon']) ? '' : $value['icon'];
                $name = empty($value['name']) ? '' : $value['name'];
                $attr = empty($value['attr']) ? array() : $value['attr'];
                $class = empty($value['pixattr']['class']) ? '' : $value['pixattr']['class'];
                $alt = empty($value['pixattr']['alt']) ? '' : $value['pixattr']['alt'];
                $al = new action_menu_link_secondary(
                    new moodle_url($url),
                    new pix_icon($icon, $alt, null, array('class' => "smallicon " . $class)),
                    $name,
                    $attr
                );
                $menu->add($al);
            }

            $o .= html_writer::div($this->render($menu), 'section_action_menu',
                array('data-sectionid' => $section->id, 'title' => get_string('edit', 'moodle')));
        }

        return $o;
    }

    /**
     * Renders HTML for the menus to add activities and resources to the current course
     *
     * @param stdClass $course
     * @param int $section relative section number (field course_sections.section)
     * @param int $sectionreturn The section to link back to
     * @param array $displayoptions additional display options, for example blocks add
     *     option 'inblock' => true, suggesting to display controls vertically
     * @return string
     */
    private function course_section_add_cm_control($course, $section, $sectionreturn = null, $displayoptions = array()) {
        global $CFG;

        $vertical = !empty($displayoptions['inblock']);

        // Check to see if user can add menus and there are modules to add.
        if (!has_capability('moodle/course:manageactivities', context_course::instance($course->id))
                || !$this->page->user_is_editing()
                || !($modnames = get_module_types_names()) || empty($modnames)) {
            return '';
        }

        $modules = get_module_metadata($course, $modnames, $sectionreturn);
        $urlparams = array('section' => $section);

        $activities = array(MOD_CLASS_ACTIVITY => array(), MOD_CLASS_RESOURCE => array());

        foreach ($modules as $module) {
            $activityclass = MOD_CLASS_ACTIVITY;
            if ($module->archetype == MOD_ARCHETYPE_RESOURCE) {
                $activityclass = MOD_CLASS_RESOURCE;
            } else if ($module->archetype === MOD_ARCHETYPE_SYSTEM) {
                continue;
            }
            $link = $module->link->out(true, $urlparams);
            $activities[$activityclass][$link] = $module->title;
        }

        $output = $this->courserenderer->course_modchooser($modules, $course);

        return $output;
    }

    /**
     * Output the html for a single section page .
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections (argument not used)
     * @param array $mods (argument not used)
     * @param array $modnames (argument not used)
     * @param array $modnamesused (argument not used)
     * @param int $displaysection The section number in the course which is being displayed
     */
    public function print_single_section_page($course, $sections, $mods, $modnames, $modnamesused, $displaysection) {
        global $PAGE;
        if (!$PAGE->user_is_editing()) {
            $course = course_get_format($course)->get_course();
            $modinfo = get_fast_modinfo($course);
            $allsections = $modinfo->get_section_info_all();
            $arupmetadata = \coursemetadatafield_arup\arupmetadata::fetch(['course' => $course->id]);
            $course->methodology = \coursemetadatafield_arup\arupmetadata::getmethodologyname($arupmetadata->methodology, false);

            $sectionnavlinkicons =  $this->get_nav_link_icons($course, $modinfo->get_section_info_all(), $displaysection);
            $sectionnavlinks = $this->get_nav_links($course, $modinfo->get_section_info_all(), $displaysection);

            echo html_writer::start_tag('div', array('class' => 'row mt-5'));
            echo html_writer::start_tag('div', array('class' => 'col-10 col-lg-4 ml-lg-auto mr-lg-auto order-lg-2'));
            echo $this->print_section_chapters($course, $allsections);
            echo html_writer::end_tag('div');
            echo html_writer::start_tag('div', array('class' => 'col-lg-8 pr-lg-5 order-lg-1 d-flex flex-column'));
            echo $this->print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused);
                    // Title with section navigation links.

            echo $sectionnavlinks;
            echo html_writer::end_tag('div');
            echo html_writer::end_tag('div');
        } else {
            echo $this->print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused);
        }

    }

    private function print_section_chapters($course, $sections) {
        global $DB, $PAGE, $OUTPUT;

        $template = new stdClass();
        $template->methodology = $course->methodology;
        $template->sections = [];

        $selected = optional_param('section', null, PARAM_INT);

        $context = context_course::instance($course->id);
        $completioninfo = new completion_info($course);

        foreach ($sections as $section) {
            $i = $section->section;
            if (!$section->uservisible) {
                continue;
            }

            if (!empty($section->name)) {
                $title = format_string($section->name, true, array('context' => $context));
            } else {
                $title = get_section_name($course, $section);
            }

            $thissection = new stdClass();
            $thissection->number = $i;
            $thissection->title = $title;
            $thissection->url = course_get_format($course)->get_view_url($section);
            $thissection->selected = false;
            $thissection->availability = $this->section_availability_message($section,
                has_capability('moodle/course:viewhiddensections', $context));

            if ($i == $selected) {
                $thissection->selected = true;
            }
            $template->sections[] = $thissection;
        }

        $template->coursename = $course->fullname;

        return $this->render_from_template('format_aruponepage/coursenav', $template);
    }

    /**
     * Generate next/previous section ICONS for naviation
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections The course_sections entries from the DB
     * @param int $sectionno The section number in the coruse which is being dsiplayed
     * @return array associative array with previous and next section link
     */
    protected function get_nav_link_icons($course, $sections, $sectionno) {
        // FIXME: This is really evil and should by using the navigation API.
        $course = course_get_format($course)->get_course();
        $canviewhidden = has_capability('moodle/course:viewhiddensections', context_course::instance($course->id))
            or !$course->hiddensections;

        $links = array('previous' => '', 'next' => '', 'home' => '');

        $links['home'] = html_writer::link(course_get_url($course), html_writer::tag('i', '', array('class' => 'fa fa-home')) . get_string('home', 'format_aruponepage'), array('class'=>'home-link'));

        $back = $sectionno - 1;
        while ($back > 0 and empty($links['previous'])) {
            if ($canviewhidden || $sections[$back]->uservisible) {
                $class = 'back-link';
                if (!$sections[$back]->visible) {
                    $class .= ' dimmed_text';
                }
                $params = array('class' => $class);
                $previouslink = html_writer::tag('i', '', array('class' => 'fa fa-chevron-left'));
                $previouslink .= 'previous';
                //$previouslink .= get_section_name($course, $sections[$back]);
                $links['previous'] = html_writer::link(course_get_url($course, $back), $previouslink, $params);
            }
            $back--;
        }

        $forward = $sectionno + 1;
        while ($forward <= course_get_format($course)->get_last_section_number() and empty($links['next'])) {
            if ($canviewhidden || $sections[$forward]->uservisible) {
                $class = 'next-link';
                if (!$sections[$forward]->visible) {
                    $class .= ' dimmed_text';
                }
                $params = array('class' => $class);
                $nextlink = html_writer::tag('i', '', array('class' => 'fa fa-chevron-right'));
                $nextlink .= 'next';
                $links['next'] = html_writer::link(course_get_url($course, $forward), $nextlink, $params);
            }
            $forward++;
        }

        return $links;
    }

    /**
     * Generate next/previous section links for naviation
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections The course_sections entries from the DB
     * @param int $sectionno The section number in the coruse which is being dsiplayed
     * @return array associative array with previous and next section link
     */
    protected function get_nav_links($course, $sections, $sectionno) {
        global $OUTPUT;
        // FIXME: This is really evil and should by using the navigation API.

        $template = new \stdClass();
        $canviewhidden = has_capability('moodle/course:viewhiddensections', context_course::instance($course->id))
            or !$course->hiddensections;

        $links = array('previous' => '', 'next' => '');
        $back = $sectionno - 1;
        $hasprevious = $hasnext = false;
        while ($back > 0 and !$hasprevious) {
            if ($canviewhidden || $sections[$back]->uservisible) {
                $template->previous = new \stdClass();
                $template->previous->url = course_get_url($course, $back);
                $template->previous->visible = true;
                $hasprevious = true;
                if (!$sections[$back]->visible) {
                    $template->previous->visible = false;
                }
            }
            $back--;
        }

        $forward = $sectionno + 1;
        while ($forward <= course_get_format($course)->get_last_section_number() and !$hasnext) {
            if ($canviewhidden || $sections[$forward]->uservisible) {
                $template->next = new \stdClass();
                $template->next->url = course_get_url($course, $forward);
                $template->next->visible = true;
                $hasnext = true;
                if (!$sections[$forward]->visible) {
                    $template->next->visible = false;
                }
            }
            $forward++;
        }

        return $OUTPUT->render_from_template('format_aruponepage/sectionnavigation', $template);
    }

    /**
     * Output the html for a multiple section page
     *
     * @param stdClass $course The course entry from DB
     * @param array $sections (argument not used)
     * @param array $mods (argument not used)
     * @param array $modnames (argument not used)
     * @param array $modnamesused (argument not used)
     */
    public function print_multiple_section_page($course, $sections, $mods, $modnames, $modnamesused) {
        global $PAGE;

        $template = new stdClass();

        $arupmetadata = \coursemetadatafield_arup\arupmetadata::fetch(['course' => $course->id]);
        $template->methodology = \coursemetadatafield_arup\arupmetadata::getmethodologyname($arupmetadata->methodology, false);

        $template->courseid = $course->id;
        $template->editing = $this->page->user_is_editing();
        $template->editsettingsurl = new moodle_url('/course/edit.php', ['id' => $course->id]);
        $template->enrolusersurl = new moodle_url('/user/index.php', ['id' => $course->id]);
        $template->incourse = true;

        if ($PAGE->user_is_editing()) {
            $template->editoff = new moodle_url($PAGE->url, ['sesskey' => sesskey(), 'edit' => 'off']);
        } else {
            $template->editon = new moodle_url($PAGE->url, ['sesskey' => sesskey(), 'edit' => 'on']);
        }
        /** @var format_aruponepage $courseformat */
        $courseformat = course_get_format($course);
        $course = $courseformat->get_course();
        $options = $courseformat->get_format_options();

        $template->sections = [];

        $modinfo = get_fast_modinfo($course);

        $template->accordion = $options['accordioneffect'];

        $context = context_course::instance($course->id);

        $template->contextid = $context->id;

        $completioninfo = new completion_info($course);

        $template->completioninfo = $completioninfo->display_help_icon();
        $template->courseactivityclipboard = $this->course_activity_clipboard($course, 0);

        $numsections = $courseformat->get_last_section_number();

        $allsections = $modinfo->get_section_info_all();

        // figure out if we are showing toc
        $showtoc = false;
        foreach ($allsections as $section => $thissection) {
            if ($section == 0) {
                continue;
            }
            if ($section > course_get_format($course)->get_last_section_number()) {
                break;
            }
            if ($thissection->uservisible ||
                    ($thissection->visible && !$thissection->available
                    && !empty($thissection->availableinfo))) {
                $showtoc = true;
                break;
            }
        }

        $showsection = optional_param('section', 0, PARAM_INT);
        $template->showsection = $showsection;
        $template->startbuttonurl = new moodle_url('/course/view.php', ['id' => $course->id, 'section' => 1]);

        foreach ($allsections as $section => $thissection) {

            if (!$PAGE->user_is_editing()) {
                if ($showsection !== $section) {
                    continue;
                }
            }
            $sectiontemp = (object)$thissection;
            $sectiontemp->sectionnumber = $section;
            if ($section == 0) {
                $sectiontemp->sectionzero = true;
            }
            if ($PAGE->user_is_editing()) {
                if ($section > 0) {
                    $sectiontemp->move = true;
                    $sectiontemp->movetitle = get_string('movesection', 'moodle', $section);
                } else {
                    $sectiontemp->moveplaceholder = true;
                }
                $sectiontemp->editsection = $this->edit_section($thissection, $course, false);
            } else {
                if (!$thissection->uservisible || !$thissection->visible) {
                    continue;
                }
            }
            if ($section > $numsections) {
                $sectiontemp->mutedsection = true;
                if (!$PAGE->user_is_editing()) {
                    continue;
                }
            }
            $sectiontemp->availabilitymsg = $this->section_availability($thissection);
            $sectiontemp->sectionname = $courseformat->get_section_name($thissection);
            $sectiontemp->name = $this->section_title($thissection, $course);
            $sectiontemp->summary = $this->format_summary_text($thissection);
            $sectiontemp->coursemodules = $this->course_section_cm_aruponepage($course, $thissection, 0);

            $template->sections[] = $sectiontemp;
        }

        if ($PAGE->user_is_editing() and has_capability('moodle/course:update', $context)) {
            $template->editing = true;
            $template->addsection = $this->aruponepage_change_number_sections($course, 0);
        }

        echo $this->render_from_template('format_aruponepage/multisectionpage', $template);
    }

    /**
     * Displays availability information for the section (hidden, not available unles, etc.)
     *
     * @param section_info $section
     * @return string
     */
    public function section_availability($section) {
        $context = context_course::instance($section->course);
        $canviewhidden = has_capability('moodle/course:viewhiddensections', $context);
        return $this->section_availability_message($section, $canviewhidden);
    }

    /**
     * Generate the section title, wraps it in a link to the section page if page is to be displayed on a separate page
     *
     * @param stdClass|section_info $section The course_section entry from DB
     * @param stdClass $course The course entry from DB
     * @return string HTML to output.
     */
    public function section_title($section, $course) {
        return $this->render(course_get_format($course)->inplace_editable_render_section_name($section, false));
    }

    /**
     * Renders HTML to display a aruponepage of course modules in a course section
     *
     * This function calls {@link core_course_renderer::course_section_cm_aruponepage_item()}
     *
     * @param stdClass $course course object
     * @param int|stdClass|section_info $section relative section number or section object
     * @param int $sectionreturn section number to return to
     * @param int $displayoptions
     * @return string
     */
    public function course_section_cm_aruponepage($course, $section, $sectionreturn = null, $displayoptions = array()) {
        global $PAGE;

        $template = new stdClass();

        $template->section = $section->section;

        $modinfo = get_fast_modinfo($course);
        if (is_object($section)) {
            $section = $modinfo->get_section_info($section->section);
        } else {
            $section = $modinfo->get_section_info($section);
        }
        $completioninfo = new completion_info($course);

        // Check if we are currently in the process of moving a module with JavaScript disabled.
        $template->ismoving = $PAGE->user_is_editing() && ismoving($course->id);

        $template->editing = $PAGE->user_is_editing();

        $template->modules = [];
        if (!empty($modinfo->sections[$section->section])) {
            foreach ($modinfo->sections[$section->section] as $modnumber) {
                $mod = $modinfo->cms[$modnumber];
                if (!$mod->is_visible_on_course_page()) {
                    continue;
                }
                $template->modules[] = $this->course_section_cm_aruponepage_item($course,
                    $completioninfo, $mod, $sectionreturn, $displayoptions);
            }
        } else {
            $template->nomodules = true;
        }
        return $this->render_from_template('format_aruponepage/coursemodules', $template);
    }

    /**
     * Renders HTML to display one course module for display within a section.
     *
     * This function calls:
     * {@link core_course_renderer::course_section_cm()}
     *
     * @param stdClass $course
     * @param completion_info $completioninfo
     * @param cm_info $mod
     * @param int|null $sectionreturn
     * @param array $displayoptions
     * @return String
     */
    public function course_section_cm_aruponepage_item($course, &$completioninfo, cm_info $mod, $sectionreturn,
        $displayoptions = array()) {
        global $OUTPUT, $PAGE;
        $template = new stdClass();
        $template->mod = $mod;

        $template->text = $mod->get_formatted_content(array('overflowdiv' => false, 'noclean' => true));
        $template->completion = $this->course_section_cm_completion($course, $completioninfo, $mod, $displayoptions);
        $template->cmname = $this->courserenderer->course_section_cm_name($mod, $displayoptions);
        $template->editing = $PAGE->user_is_editing();
        $template->availability = $this->courserenderer->course_section_cm_availability($mod, $displayoptions);

        if ($PAGE->user_is_editing()) {
            $editactions = course_get_cm_edit_actions($mod, $mod->indent, $sectionreturn);
            $template->editoptions = $this->course_section_cm_edit_actions($editactions, $mod, $displayoptions);
            $template->editoptions .= $mod->afterediticons;
            $template->moveicons = $this->course_get_cm_move($mod, $sectionreturn);
        }

        return $this->render_from_template('format_aruponepage/coursemodule', $template);
    }

    /**
     * Render the course module move icon.
     *
     * @param  cm_info $mod
     * @param  int $sr Section return ID
     * @return String HTML to be returned
     */
    public function course_get_cm_move(cm_info $mod, $sr = null) {
        $template = new stdClass();

        $modcontext = context_module::instance($mod->id);
        $hasmanageactivities = has_capability('moodle/course:manageactivities', $modcontext);

        $template->movetitle = get_string('movecoursemodule', 'moodle');

        if ($hasmanageactivities) {
            return $this->render_from_template('format_aruponepage/movecoursemodule', $template);
        }
        return '';
    }

    /**
     * Checks if course module has any conditions that may make it unavailable for
     * all or some of the students
     *
     * This function is internal and is only used to create CSS classes for the module name/text
     *
     * @param cm_info $mod
     * @return bool
     */
    protected function is_cm_conditionally_hidden(cm_info $mod) {
        global $CFG;
        $conditionalhidden = false;
        if (!empty($CFG->enableavailability)) {
            $info = new \core_availability\info_module($mod);
            $conditionalhidden = !$info->is_available_for_all();
        }
        return $conditionalhidden;
    }

    /**
     * Renders html for completion box on course page
     *
     * If completion is disabled, returns empty string
     * If completion is automatic, returns an icon of the current completion state
     * If completion is manual, returns a form (with an icon inside) that allows user to
     * toggle completion
     *
     * @param stdClass $course course object
     * @param completion_info $completioninfo completion info for the course, it is recommended
     *     to fetch once for all modules in course/section for performance
     * @param cm_info $mod module to show completion for
     * @param array $displayoptions display options, not used in core
     * @return string
     */
    public function course_section_cm_completion($course, &$completioninfo, cm_info $mod, $displayoptions = array()) {
        global $CFG, $USER;

        if (!empty($displayoptions['hidecompletion']) || !isloggedin() || isguestuser() || !$mod->uservisible) {
            return "";
        }
        if ($completioninfo === null) {
            $completioninfo = new completion_info($course);
        }
        $completion = $completioninfo->is_enabled($mod);

        if ($completion == COMPLETION_TRACKING_NONE) {
            return "";
        }

        $isediting = $this->page->user_is_editing();
        $istrackeduser = $completioninfo->is_tracked_user($USER->id);

        $completionicon = '';

        $completiondata = $completioninfo->get_data($mod, true);
        if ($isediting) {
            switch ($completion) {
                case COMPLETION_TRACKING_MANUAL :
                    $completionicon = 'manual-enabled';
                    break;
                case COMPLETION_TRACKING_AUTOMATIC :
                    $completionicon = 'auto-enabled';
                    break;
            }
        } else {
            if ($completion == COMPLETION_TRACKING_MANUAL) {
                switch($completiondata->completionstate) {
                    case COMPLETION_INCOMPLETE:
                        $completionicon = 'manual-n' . ($completiondata->overrideby ? '-override' : '');
                        break;
                    case COMPLETION_COMPLETE:
                        $completionicon = 'manual-y' . ($completiondata->overrideby ? '-override' : '');
                        break;
                }
            } else {
                switch($completiondata->completionstate) {
                    case COMPLETION_INCOMPLETE:
                        $completionicon = 'auto-n' . ($completiondata->overrideby ? '-override' : '');
                        break;
                    case COMPLETION_COMPLETE:
                        $completionicon = 'auto-y' . ($completiondata->overrideby ? '-override' : '');
                        break;
                    case COMPLETION_COMPLETE_PASS:
                        $completionicon = 'auto-pass';
                        break;
                    case COMPLETION_COMPLETE_FAIL:
                        $completionicon = 'auto-fail';
                        break;
                }
            }
        }
        $template = new stdClass();
        $template->sectionnumber = $mod->get_section_info()->section;
        $template->mod = $mod;
        $template->completionicon = $completionicon;
        $template->courseid = $course->id;

        if ($completionicon) {
            $formattedname = $mod->get_formatted_name(['escape' => false]);
            $template->hascompletion = true;

            if ($isediting) {
                $template->editing = true;
            }

            if (\core_availability\info::completion_value_used($course, $mod->id)) {
                $template->reloadonchange = true;
            }

            if (!$isediting && $istrackeduser && $completiondata->overrideby) {
                $args = new stdClass();
                $args->modname = $formattedname;
                $overridebyuser = \core_user::get_user($completiondata->overrideby, '*', MUST_EXIST);
                $args->overrideuser = fullname($overridebyuser);
                $imgalt = get_string('completion-alt-' . $completionicon, 'completion', $args);
            } else {
                $imgalt = get_string('completion-alt-' . $completionicon, 'completion', $formattedname);
            }
            $template->imgalt = $imgalt;

            if ($completion == COMPLETION_TRACKING_MANUAL) {
                $template->self = true;
                $template->newstate =
                    $completiondata->completionstate == COMPLETION_COMPLETE
                    ? COMPLETION_INCOMPLETE
                    : COMPLETION_COMPLETE;

                if ($completiondata->completionstate == COMPLETION_COMPLETE) {
                    $template->checked = true;
                }
            } else {
                $template->auto = true;
                if ($completionicon == 'auto-y' || $completionicon == 'auto-pass') {
                    $template->checked = true;
                }
            }
        }
        return $this->render_from_template('format_aruponepage/completionicon', $template);
    }

    /**
     * Renders HTML for displaying the sequence of course module editing buttons
     *
     * @see course_get_cm_edit_actions()
     *
     * @param action_link[] $actions Array of action_link objects
     * @param cm_info $mod The module we are displaying actions for.
     * @param array $displayoptions additional display options:
     *     ownerselector => A JS/CSS selector that can be used to find an cm node.
     *         If specified the owning node will be given the class 'action-menu-shown' when the action
     *         menu is being displayed.
     *     constraintselector => A JS/CSS selector that can be used to find the parent node for which to constrain
     *         the action menu to when it is being displayed.
     *     donotenhance => If set to true the action menu that gets displayed won't be enhanced by JS.
     * @return string
     */
    public function course_section_cm_edit_actions($actions, cm_info $mod = null, $displayoptions = array()) {
        global $CFG;

        if (empty($actions)) {
            return '';
        }

        $template = new stdClass();
        $template->controls = [];

        foreach ($actions as $action) {
            if ($action instanceof action_menu_link) {
                $action->add_class('cm-edit-action');
            }
        }

        foreach ($actions as $key => $action) {
            if ($key === 'moveright' || $key === 'moveleft' ) {
                continue;
            }
            if (empty($action->url)) {
                continue;
            }

            $control = new stdClass();
            $control->icon = $action->icon;
            $control->attributes = '';
            if (is_array($action->attributes)) {
                foreach ($action->attributes as $name => $value) {
                    $control->attributes .= s($name) . '="' . s($value) . '"';
                }
            }
            $control->url = $action->url->out(false);
            $control->string = $action->text;
            $template->controls[] = $control;
        }

        return $this->render_from_template('format_aruponepage/editactivity', $template);
    }

    /**
     * Returns controls in the bottom of the page to increase/decrease number of sections
     *
     * @param stdClass $course
     * @param int|null $sectionreturn
     * @return string
     */
    private function aruponepage_change_number_sections($course, $sectionreturn = null) {
        $coursecontext = context_course::instance($course->id);
        if (!has_capability('moodle/course:update', $coursecontext)) {
            return '';
        }

        $format = course_get_format($course);
        $maxsections = $format->get_max_sections();
        $lastsection = $format->get_last_section_number();

        if ($lastsection >= $maxsections) {
            return '';
        }

        $template = new stdClass();
        $url = new moodle_url('/course/changenumsections.php',
            ['courseid' => $course->id, 'insertsection' => 0, 'sesskey' => sesskey()]);

        if ($sectionreturn !== null) {
            $url->param('sectionreturn', $sectionreturn);
        }
        $template->url = $url->out(false);
        $template->attributes = [['name' => 'new-sections', 'value' => $maxsections - $lastsection]];

        return $this->render_from_template('format_aruponepage/change_number_sections', $template);
    }
}
