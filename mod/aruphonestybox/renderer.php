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

class mod_aruphonestybox_renderer extends plugin_renderer_base {
    /**
     * Genate table of lists of ahb users' completions
     * @param $usercompletions
     * @param $context
     * @param $cmid
     * @return string
     */
    public function completion_approval_list($usercompletions, $context, $cmid) {
        global $USER;

        $ahb_users = null;

        $html = '';

        $table = new html_table();
        $table->attributes['class'] = 'generaltable mod_index';

        $table->head = array();
        $table->head[] = get_string('approve:name', 'mod_aruphonestybox');
        $table->head[] = get_string('approve:email', 'mod_aruphonestybox');
        $table->head[] = get_string('approve:datecompleted', 'mod_aruphonestybox');
        $table->head[] = get_string('approve:certificatelink', 'mod_aruphonestybox');
        $table->head[] = get_string('approve:dateapproved', 'mod_aruphonestybox');
        $table->head[] = get_string('approve:approvedby', 'mod_aruphonestybox');
        $table->head[] = get_string('approve:actions', 'mod_aruphonestybox');

        if(empty($usercompletions)) {
            $cells = array();
            $cell = new html_table_cell();
            $cell->colspan = count($table->head);
            $cell->attributes['class'] = 'text-center';
            $cell->text = get_string('approve:nocompletions', 'mod_aruphonestybox');
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
                $cell->text = $this->format_user_certificatelink($context, $usercompletion->userid);
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
                $approvelink = new moodle_url('/mod/aruphonestybox/approve.php', array('id' => $cmid, 'action' => 'approve', 'ahbuserid' => $usercompletion->id));
                $editlink = new moodle_url('/mod/aruphonestybox/view.php', array('id' => $cmid, 'action' => 'edit', 'ahbuserid' => $usercompletion->id));

                $actions['approve'] = (empty($usercompletion->approved))? html_writer::link($approvelink , get_string('approve:approve', 'mod_aruphonestybox')) : get_string('approve:approved', 'mod_aruphonestybox') ;

                if($usercompletion->userid == $USER->id) {
                    unset($actions['approve']);
                }

                if(empty($usercompletion->approved)) {
                    $actions['edit'] = html_writer::link($editlink , get_string('approve:edit', 'mod_aruphonestybox'));
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
     * @param $context
     * @param $userid
     * @return moodle_url|null
     */
    public function format_user_certificatelink($context, $userid) {
        // Show link to the uploaded certificate file
        $fs = get_file_storage();
        $files = $fs->get_area_files($context->id, 'mod_aruphonestybox', 'certificate');
        if ($files) {

            foreach ($files as $file) {
                if(($userid == $file->get_itemid() && $file->get_source() != null)) {
                    $url = moodle_url::make_pluginfile_url($file->get_contextid(), $file->get_component(), $file->get_filearea(), $file->get_itemid(), $file->get_filepath(), $file->get_filename(), false);
                    return html_writer::link($url, $file->get_filename());
                    break;
                }
            }
        }
        return null;
    }
}