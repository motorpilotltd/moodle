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
 * Renderer for mod_arupadvert.
 *
 * @package     mod_arupadvert
 * @copyright   2016 Motorpilot Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Renderer class for mod_arupadvert.
 *
 * @package   mod_arupadvert
 * @copyright 2016 Motorpilot Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_arupadvert_renderer extends plugin_renderer_base {
    /**
     * Renderers info to course page.
     *
     * @param stdClass $arupadvert
     * @param stdClass $info
     * @return string
     */
    public function cm_info_view($arupadvert, $info) {
        global $CFG, $OUTPUT;

        $purifier = false;
        if ($info->purify) {
            require_once($CFG->libdir.'/htmlpurifier/HTMLPurifier.safe-includes.php');
            $cachedir = $CFG->cachedir.'/htmlpurifier';
            check_dir_exists($cachedir);

            $config = HTMLPurifier_Config::createDefault();
            $config->set('Cache.SerializerPath', $cachedir);
            $config->set('Cache.SerializerPermissions', $CFG->directorypermissions);
            $config->set('HTML.AllowedElements', 'p,a,table,thead,tbody,tfoot,tr,th,td,ul,ol,li,strong,b,em,i');
            $config->set('Attr.AllowedFrameTargets', array('_blank', '_self', '_parent', '_top'));
            $config->set('AutoFormat.RemoveEmpty', true);
            $purifier = new HTMLPurifier($config);
        }

        $template = new stdClass();

        $template->showheadings = $arupadvert->showheadings;

        if ($info->accredited) {
            $logourl = $OUTPUT->pix_url('logo', 'arupadvertdatatype_'.$info->type);
        } else {
            $logourl = '';
        }
        $template->name = $info->name;
        $template->logo = $logourl;

        $sections = array('description', 'objectives', 'audience');
        $template->hassections = false;
        foreach ($sections as $section) {
            $sec = new stdClass();
            if ($purifier) {
                $content = $purifier->purify($info->{$section});
            } else {
                $content = $info->{$section};
            }
            if ($content) {
                $altword = empty($arupadvert->altword) ? core_text::strtolower(get_string('course')) : $arupadvert->altword;
                $sec->heading = get_string($section, 'arupadvert', $altword);
                $sec->text = $content;
                $template->sections[] = $sec;
                $template->hassections = true;
            } 
        }

        $elements = array('by', 'level', 'code', 'region', 'keywords');
        foreach ($elements as $element) {
            $be = new stdClass();
            $be->heading = get_string("block:{$element}", 'arupadvert');
            $be->text = $info->$element;
            if ($be->text) {
                $template->blockelements[] = $be;
            }
        }

        $template->courseimage = $info->imgurl;

        return $this->render_from_template('mod_arupadvert/cminfoview', $template);
    }

    /**
     * Renderers the arupadvert index page content.
     *
     * @param stdClass $course
     * @param array $arupadverts
     * @param course_modinfo $modinfo
     * @param bool $usesections
     * @return string
     */
    public function index($course, $arupadverts, $modinfo, $usesections) {
        global $OUTPUT;

        $strsectionname  = get_string('sectionname', 'format_'.$course->format);
        $strname         = get_string('name');
        $strlastmodified = get_string('lastmodified');

        $table = new html_table();
        $table->attributes['class'] = 'generaltable mod_index';

        if ($usesections) {
            $table->head  = array ($strsectionname, $strname);
            $table->align = array ('center', 'left');
        } else {
            $table->head  = array ($strlastmodified, $strname);
            $table->align = array ('left', 'left');
        }

        $currentsection = '';
        foreach ($arupadverts as $arupadvert) {
            $cm = $modinfo->cms[$arupadvert->coursemodule];
            if ($usesections) {
                $printsection = '';
                if ($arupadvert->section !== $currentsection) {
                    if ($arupadvert->section) {
                        $printsection = get_section_name($course, $arupadvert->section);
                    }
                    if ($currentsection !== '') {
                        $table->data[] = 'hr';
                    }
                    $currentsection = $arupadvert->section;
                }
            } else {
                $printsection = '<span class="smallinfo">'.userdate($arupadvert->timemodified)."</span>";
            }

            $extra = empty($cm->extra) ? '' : $cm->extra;
            $icon = '';
            if (!empty($cm->icon)) {
                $icon = '<img src="'.$OUTPUT->pix_url($cm->icon).'" class="activityicon" alt="'.get_string('modulename', $cm->modname).'" /> ';
            }

            $class = $arupadvert->visible ? '' : 'class="dimmed"'; // Hidden modules are dimmed.
            $table->data[] = array (
                $printsection,
                "<a {$class} {$extra} href=\"view.php?id={$cm->id}\">".$icon.format_string($arupadvert->name)."</a>");
        }

        return html_writer::table($table);
    }
}