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
 * Renderer for mod_arupenrol.
 *
 * @package     mod_arupenrol
 * @copyright   2016 Motorpilot Ltd
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Renderer class for mod_arupenrol.
 *
 * @package   mod_arupenrol
 * @copyright 2016 Motorpilot Ltd
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class mod_arupenrol_renderer extends plugin_renderer_base {

    /**
     * Renderers the arupenrol index page content.
     *
     * @param stdClass $course
     * @param array $arupenrols
     * @param course_modinfo $modinfo
     * @param bool $usesections
     * @return string
     */
    public function index($course, $arupenrols, $modinfo, $usesections) {
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
        foreach ($arupenrols as $arupenrol) {
            $cm = $modinfo->cms[$arupenrol->coursemodule];
            if ($usesections) {
                $printsection = '';
                if ($arupenrol->section !== $currentsection) {
                    if ($arupenrol->section) {
                        $printsection = get_section_name($course, $arupenrol->section);
                    }
                    if ($currentsection !== '') {
                        $table->data[] = 'hr';
                    }
                    $currentsection = $arupenrol->section;
                }
            } else {
                $printsection = '<span class="smallinfo">'.userdate($arupenrol->timemodified)."</span>";
            }

            $extra = empty($cm->extra) ? '' : $cm->extra;
            $icon = '';
            if (!empty($cm->icon)) {
                $icon = '<img src="'.$OUTPUT->pix_url($cm->icon).'" class="activityicon" alt="'.get_string('modulename', $cm->modname).'" /> ';
            }

            $class = $arupenrol->visible ? '' : 'class="dimmed"'; // Hidden modules are dimmed.
            $table->data[] = array (
                $printsection,
                "<a {$class} {$extra} href=\"view.php?id={$cm->id}\">".$icon.format_string($arupenrol->name)."</a>");
        }

        return html_writer::table($table);
    }

    public function format_available_info($availableinfo) {
        if (is_string($availableinfo)) {
            return $availableinfo;
        }

        $items = array();
        foreach ($availableinfo->items as $item) {
            if (is_string($item)) {
                $items[] = $item;
            }
        }

        $out = '';
        $count = count($items);
        if ($count > 1) {
            $out .= get_string('list_root_' . ($availableinfo->andoperator ? 'and' : 'or'), 'availability');
            $out .= html_writer::alist($items);
        } else if ($count === 1) {
            $out .= get_string('list_root_and', 'availability');
            $out .= ' ';
            $out .= reset($items);
        }

        return $out;
    }

    public function intro($arupenrol, $cm, $showname, $showdescription) {
        $output = '';
        if ($showname) {
            $output .= html_writer::tag('h3', $cm->name);
        }
        if ($showdescription && !empty($arupenrol->intro)) {
            $output .= format_module_intro('arupenrol', $arupenrol, $cm->id);
        }
        return $output;
    }

    public function action_2($arupenrol) {
        $fields = '';
        $fields .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $arupenrol->id));
        $fields .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'action', 'value' => 2));
        $fields .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
        $fields .= html_writer::label($arupenrol->keylabel, 'keyvalue', false);
        $fields .= html_writer::empty_tag('input', array('type' => 'text', 'name' => 'keyvalue'));
        $fields .= $this->_get_button($arupenrol->buttontype, $arupenrol->buttontext);

        $fieldset = html_writer::tag('div', $fields);

        $url = new moodle_url('/mod/arupenrol/process.php');
        $formattributes = array('method' => 'post',
                                'action' => $url,
                                'id'     => 'arupenrol-action-2-form');
        $form = html_writer::tag('form', $fieldset, $formattributes);

        return html_writer::tag('div', $form, array('class' => 'arupenrol-action-2'));
    }

    public function action_3($arupenrol) {
        global $USER;

        $fields = '';
        $fields .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $arupenrol->id));
        $fields .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'action', 'value' => 3));
        $fields .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
        $fields .= $this->_get_button($arupenrol->buttontype, $arupenrol->buttontext);

        $fieldset = html_writer::tag('div', $fields);

        $url = new moodle_url('/mod/arupenrol/process.php');
        $formattributes = array('method' => 'post',
                                'action' => $url,
                                'id'     => 'arupenrol-action-3-form');
        $form = html_writer::tag('form', $fieldset, $formattributes);

        return html_writer::tag('div', $form, array('class' => 'arupenrol-action-3'));
    }

    public function unenrol_button($arupenrol, $unenrolurl) {
        $output = '';

        if (!$unenrolurl) {
            return $output;
        }

        $fields = '';
        $fields .= $this->_get_button($arupenrol->unenrolbuttontype, $arupenrol->unenrolbuttontext);

        $fieldset = html_writer::tag('div', $fields);

        $formattributes = array('method' => 'post',
                                'action' => $unenrolurl,
                                'id'     => 'arupenrol-unenrol-form');
        $form = html_writer::tag('form', $fieldset, $formattributes);

        return html_writer::tag('div', $form, array('class' => 'arupenrol-unenrol'));
    }

    public function alert($message, $type = 'alert-warning', $exitbutton = true) {
        $class = "alert {$type} fade in";
        $button = '';
        if ($exitbutton) {
            $button .= html_writer::tag('button', '&times;', array('class' => 'close', 'data-dismiss' => 'alert', 'type' => 'button'));
        }
        return html_writer::tag('div', $button.$message, array('class' => $class));
    }

    public function outro($arupenrol, $cm) {
        global $CFG;
        $output = '';
        if (!empty($arupenrol->outro)) {
            require_once("$CFG->libdir/filelib.php");
            $context = context_module::instance($cm->id);
            $options = array('noclean' => true, 'para' => false, 'filter' => true, 'context' => $context, 'overflowdiv' => true);
            $outro = file_rewrite_pluginfile_urls($arupenrol->outro, 'pluginfile.php', $context->id, 'mod_arupenrol', 'outro', null);
            $output .= trim(format_text($outro, $arupenrol->outroformat, $options, null));
        }
        return $output;
    }

    protected function _get_button($buttontype, $buttontext) {
        $output = '';
        $buttonoptions = array(
            'id' => 'arupenrol-button',
            'type' => 'submit',
            'class' => "btn btn-{$buttontype}",
        );
        $buttonoptions['onclick'] = <<<EOB
document.getElementById('arupenrol-button').style.display = 'none'; document.getElementById('arupenrol-loader').style.display = 'inline-block'
EOB;
        $output .= html_writer::tag('button', $buttontext, $buttonoptions);
        $imgurl = new moodle_url('/mod/arupenrol/pix/ajax-loader.gif');
        $output .= html_writer::empty_tag('img', array('id' => 'arupenrol-loader', 'style' => 'display: none;', 'src' => $imgurl, 'alt' => 'Processing...'));
        return $output;
    }
}