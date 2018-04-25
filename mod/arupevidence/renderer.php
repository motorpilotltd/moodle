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

defined('MOODLE_INTERNAL') || die();
require_once($CFG->dirroot.'/mod/arupevidence/lib.php');

class mod_arupevidence_renderer extends plugin_renderer_base {
    /**
     * Genate table of lists of ahb users' completions
     * @param $usercompletions
     * @param $context
     * @return string
     */
    protected $arupevidence;
    protected $context;

    public function completion_approval_list($usercompletions, $context) {
        global $USER, $DB;

        $ahb_users = null;
        $cm = get_coursemodule_from_id('arupevidence',  $context->instanceid);
        $html = '';

        if (empty($this->context)) {
            $this->context = $context;
        }

        if (empty($this->arupevidence)) {
            $this->arupevidence = $DB->get_record('arupevidence',  array('id' => $cm->instance));
        }

        $table = new html_table();
        $table->attributes['class'] = 'generaltable mod_index';

        $table->head = array();
        $table->head[] = get_string('approve:name', 'mod_arupevidence');
        $table->head[] = get_string('approve:email', 'mod_arupevidence');
        $table->head[] = get_string('approve:datecompleted', 'mod_arupevidence');
        $table->head[] = get_string('approve:certificatelink', 'mod_arupevidence');
        $table->head[] = get_string('approve:dateapproved', 'mod_arupevidence');
        $table->head[] = get_string('approve:approvedby', 'mod_arupevidence');
        $table->head[] = get_string('approve:actions', 'mod_arupevidence');

        if(empty($usercompletions)) {
            $cells = array();
            $cell = new html_table_cell();
            $cell->colspan = count($table->head);
            $cell->attributes['class'] = 'text-center';
            $cell->text = get_string('approve:nocompletions', 'mod_arupevidence');
            $cells[] = clone($cell);

            $table->data[] = new html_table_row($cells);
        } else {
            foreach ($usercompletions as $usercompletion) {
                $cells = array();
                $cell = new html_table_cell();

                // Name
                $cell->text = fullname($usercompletion);
                $cell->attributes['class'] = 'text-left nowrap';
                $cells[] = clone($cell);

                // Email
                $cell->text = $usercompletion->email;
                $cell->attributes['class'] = 'text-left';
                $cells[] = clone($cell);

                // Date Completed
                $cell->text = (!empty($usercompletion->completiondate))? userdate($usercompletion->completiondate,'%A, %d %B %Y') : '';
                $cell->attributes['class'] = 'text-left';
                $cells[] = clone($cell);

                // Certificate File Link
                $cell->text = $this->format_user_certificatelink($usercompletion);
                $cell->attributes['class'] = 'text-left';
                $cells[] = clone($cell);

                // Date Approved
                $cell->text = (!empty($usercompletion->approved))? userdate($usercompletion->approved,'%A, %d %B %Y') : '';
                $cell->attributes['class'] = 'text-left';
                $cells[] = clone($cell);

                // Approved By
                $cell->text = $usercompletion->approverid;
                $cell->attributes['class'] = 'text-left';
                $cells[] = clone($cell);

                // Actions Link
                $actions = array();
                $editlink = new moodle_url('/mod/arupevidence/view.php', array('id' =>  $context->instanceid, 'action' => 'edit', 'ahbuserid' => $usercompletion->id));

                if($usercompletion->userid != $USER->id) {
                    $attrs = array(
                        'data-ahbuserid' => $usercompletion->id,
                        'data-cmid' =>  $context->instanceid,
                    );

                    if (empty($usercompletion->approved)) {
                        $attrs['class'] = 'approve-evidence action-evidence';
                        $attrs['data-actiontype'] = 'approve';
                        $actions['approve'] = html_writer::link('#' , get_string('approve:approve', 'mod_arupevidence'), $attrs);
                    }

                    if (!$usercompletion->rejected && !$usercompletion->approved) {
                        $attrs['class'] = 'reject-evidence action-evidence';
                        $attrs['data-actiontype'] = 'reject';
                        $actions['reject']  = html_writer::link('#' , get_string('approve:reject', 'mod_arupevidence'), $attrs);
                    }
                }

                if(empty($usercompletion->approved)) {
                    $actions['edit'] = html_writer::link($editlink , get_string('approve:edit', 'mod_arupevidence'));
                }  else {
                    unset($actions['edit']);
                }
                $cell->text = implode(' | ', $actions);
                $cell->attributes['class'] = 'text-left nowrap';
                $cells[] = clone($cell);

                $table->data[] = new html_table_row($cells);
            }
        }
        $html .= html_writer::table($table);

        return $html;
    }

    /**
     * Generate alertbox
     * @param string $message
     * @param string $type
     * @param bool $exitbutton
     * @return string
     */
    public function alert($message = '', $type = 'alert-warning', $exitbutton = true) {
        $class = "alert fade in {$type}";
        $button = '';
        if ($exitbutton) {
            $button .= html_writer::tag('button', '&times;', array('class' => 'close', 'data-dismiss' => 'alert', 'type' => 'button'));
        }
        return html_writer::tag('div', $button.$message, array('class' => $class));
    }

    /**
     * Format user certificate file link
     *
     * @param $arupevidenceuser
     * @return moodle_url|null
     */
    public function format_user_certificatelink($arupevidenceuser) {

        // Show link to the uploaded certificate file
        $filearea = arupevidence_fileareaname($this->arupevidence->cpdlms);
        $file = '';

        if (!empty($arupevidenceuser->itemid) && $arupevidenceuser->completion) {
            $file = arupevidence_fileinfo($this->context, null, $filearea, $arupevidenceuser->itemid);
        } else {
            $file = arupevidence_fileinfo($this->context, $arupevidenceuser->userid);
        }
        if (!empty($file) && !empty($file->fileevidencelink)) {
            return html_writer::link($file->fileevidencelink, $file->get_filename());
        }
        return null;
    }

    public function return_to_course($id) {
        $url = new moodle_url('/course/view.php', array('id' => $id));
        $strcourse = core_text::strtolower(get_string('course'));
        $link = html_writer::link($url, get_string('returntocourse', 'mod_arupevidence', $strcourse));
        return html_writer::tag('p', $link, array('class' => 'course-return'));
    }
}