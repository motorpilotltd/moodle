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

require_once($CFG->dirroot . '/course/renderer.php');

class theme_arup_core_course_renderer extends theme_bootstrap_core_course_renderer {

    /**
     * Renders HTML to display one course module in a course section
     *
     * This includes link, content, availability, completion info and additional information
     * that module type wants to display (i.e. number of unread forum posts)
     *
     * This function calls:
     * {@link core_course_renderer::course_section_cm_name()}
     * {@link core_course_renderer::course_section_cm_text()}
     * {@link core_course_renderer::course_section_cm_availability()}
     * {@link core_course_renderer::course_section_cm_completion()}
     * {@link course_get_cm_edit_actions()}
     * {@link core_course_renderer::course_section_cm_edit_actions()}
     *
     * @param stdClass $course
     * @param completion_info $completioninfo
     * @param cm_info $mod
     * @param int|null $sectionreturn
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm($course, &$completioninfo, cm_info $mod, $sectionreturn, $displayoptions = array()) {

        $template = new stdClass();

        // We return empty string (because course module will not be displayed at all)
        // if:
        // 1) The activity is not visible to users
        // and
        // 2) The 'availableinfo' is empty, i.e. the activity was
        //     hidden in a way that leaves no info, such as using the
        //     eye icon.
        if (!$mod->uservisible && empty($mod->availableinfo)) {
            return '';
        }

        $indentclasses = 'mod-indent';
        if (!empty($mod->indent)) {
            $indentclasses .= ' mod-indent-'.$mod->indent;
            if ($mod->indent > 15) {
                $indentclasses .= ' mod-indent-huge';
            }
        }

        $template->indentclasses = $indentclasses;

        $template->moving = false;
        if ($this->page->user_is_editing()) {
            $template->moving = course_get_cm_move($mod, $sectionreturn);
        }

        // Display the link to the module (or do nothing if module has no url)
        $cmname = $this->course_section_cm_name($mod, $displayoptions);

        $template->cmname = $cmname;

        if (!empty($cmname)) {

            if ($this->page->user_is_editing()) {
                $template->rename = course_get_cm_rename_action($mod, $sectionreturn);
            }

            // Module can put text after the link (e.g. forum unread)
            $template->afterlink = $mod->afterlink;
        }

        // If there is content but NO link (eg label), then display the
        // content here (BEFORE any icons). In this case cons must be
        // displayed after the content so that it makes more sense visually
        // and for accessibility reasons, e.g. if you have a one-line label
        // it should work similarly (at least in terms of ordering) to an
        // activity.
        $contentpart = $this->course_section_cm_text($mod, $displayoptions);
        $url = $mod->url;
        $template->modurl = $url;
        $template->contentpart = $contentpart;

        $modicons = '';
        if ($this->page->user_is_editing()) {
            $editactions = course_get_cm_edit_actions($mod, $mod->indent, $sectionreturn);
            $modicons .= ' '. $this->course_section_cm_edit_actions($editactions, $mod, $displayoptions);
            $modicons .= $mod->afterediticons;
        }

        $modicons .= $this->course_section_cm_completion($course, $completioninfo, $mod, $displayoptions);

        $template->actions = false;
        if (!empty($modicons)) {
            $template->actions = $modicons;
        }
        
        // show availability info (if module is not available)
        $template->availability = $this->course_section_cm_availability($mod, $displayoptions);

        $template->specialformat = in_array(course_get_format($course)->get_format(), array('topics', 'aruponepage'));

        //return $output;
        return $this->render_from_template('theme_arup/coursesectioncm', $template);
    }

    /**
     * Renders html to display a name with the link to the course module on a course page
     *
     * If module is unavailable for user but still needs to be displayed
     * in the list, just the name is returned without a link
     *
     * Note, that for course modules that never have separate pages (i.e. labels)
     * this function return an empty string
     *
     * @param cm_info $mod
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm_name(cm_info $mod, $displayoptions = array()) {
        global $CFG;
        $output = '';
        $template = new stdClass();
        if (!$mod->uservisible && empty($mod->availableinfo)) {
            // nothing to be displayed to the user
            return '';
        }
        $url = $mod->url;
        if (!$url) {
            return '';
        }

        $template->url = $url;

        //Accessibility: for files get description via icon, this is very ugly hack!
        $instancename = $mod->get_formatted_name();
        $altname = $mod->modfullname;
        // Avoid unnecessary duplication: if e.g. a forum name already
        // includes the word forum (or Forum, etc) then it is unhelpful
        // to include that in the accessible description that is added.
        if (false !== strpos(core_text::strtolower($instancename),
                core_text::strtolower($altname))) {
            $altname = '';
        }
        // File type after name, for alphabetic lists (screen reader).
        if ($altname) {
            $altname = get_accesshide(' '.$altname);
        }

        $template->altname = $altname;
        $template->instancename = $instancename;

        // For items which are hidden but available to current user
        // ($mod->uservisible), we show those as dimmed only if the user has
        // viewhiddenactivities, so that teachers see 'items which might not
        // be available to some students' dimmed but students do not see 'item
        // which is actually available to current student' dimmed.
        $linkclasses = '';
        $accesstext = '';
        $textclasses = '';
        if ($mod->uservisible) {
            $conditionalhidden = $this->is_cm_conditionally_hidden($mod);
            $accessiblebutdim = (!$mod->visible || $conditionalhidden) &&
                has_capability('moodle/course:viewhiddenactivities', $mod->context);
            if ($accessiblebutdim) {
                $linkclasses .= ' dimmed';
                $textclasses .= ' dimmed_text';
                if ($conditionalhidden) {
                    $linkclasses .= ' conditionalhidden';
                    $textclasses .= ' conditionalhidden';
                }
                // Show accessibility note only if user can access the module himself.
                $accesstext = get_accesshide(get_string('hiddenfromstudents').':'. $mod->modfullname);
            }
        } else {
            $linkclasses .= ' dimmed';
            $textclasses .= ' dimmed_text';
        }

        $template->linkclasses = $linkclasses;
        $template->textclasses = $textclasses;

        // Get on-click attribute value if specified and decode the onclick - it
        // has already been encoded for display (puke).
        $onclick = htmlspecialchars_decode($mod->onclick, ENT_QUOTES);
        $template->onclick = $onclick;

        $groupinglabel = $mod->get_grouping_label($textclasses);
        $template->groupinglabel = $groupinglabel;
        $template->uservisible = $mod->uservisible;
        $template->userhidden = (!$mod->uservisible);
        $template->iconurl = $mod->get_icon_url();

        return $this->render_from_template('theme_arup/coursesectioncmname', $template);
    }

    /**
     * Renders HTML to show course module availability information (for someone who isn't allowed
     * to see the activity itself, or for staff)
     *
     * @param cm_info $mod
     * @param array $displayoptions
     * @return string
     */
    public function course_section_cm_availability(cm_info $mod, $displayoptions = array()) {
        // Only show padlock style availability info for specific formats.
        if (!in_array(course_get_format($mod->course)->get_format(), array('topics', 'aruponepage'))) {
            return parent::course_section_cm_availability($mod, $displayoptions);
        }
        
        if (!$mod->uservisible) {
            // This is a student who is not allowed to see the activity but might be allowed to see availability info.
            if (!empty($mod->availableinfo)) {
                $formattedinfo = \core_availability\info::format_info(
                        $mod->availableinfo, $mod->get_course());
                return html_writer::tag('i', '', array(
                    'class' => 'availabilityinfo fa fa-lock',
                    'data-toggle' => 'tooltip',
                    'data-html' => true,
                    'title' => str_replace('"', "'", strip_links($formattedinfo))
                ));
            } else {
                // Nothing to show them.
                return '';
            }
        }

        $ci = new \core_availability\info_module($mod);
        $fullinfo = $ci->get_full_information();

        if (!empty($fullinfo)) {
            // This activity has availability info.
            $modcontext = context_module::instance($mod->id);
            $canviewhidden = has_capability('moodle/course:viewhiddenactivities', $modcontext);
            if ($canviewhidden) {
                // This is a teacher who is allowed to see module but should still see availability info.
                $formattedinfo = \core_availability\info::format_info(
                        $fullinfo, $mod->get_course());
                return html_writer::tag('i', '', array(
                    'class' => 'availabilityinfo fa fa-lock',
                    'data-toggle' => 'tooltip',
                    'data-html' => true,
                    'title' => str_replace('"', "'", strip_links($formattedinfo))
                ));
            } else {
                return html_writer::tag('i', '', array(
                    'class' => 'availabilityinfo fa fa-unlock'
                ));
            }
        }

        return '';
    }

    /**
     * Redirects to card/accordion menu page.
     *
     * Invoked from /course/index.php
     *
     * @param int|stdClass|coursecat $category
     */
    public function course_category($category) {
        $catid = is_object($category) ? $category->id : $category;
        /*
         * Temporarily changed below from:
         * redirect(new moodle_url('/local/accordion/index.php', array('id' => $catid, 'catalogue' => 'card')));
         */
        redirect(new moodle_url('/local/accordion/index.php', array('id' => $catid)));
    }
}
