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

class block_arup_mylearning_renderer extends plugin_renderer_base {
    public function more_info_modal() {
        $output = '';

        $output .= html_writer::start_tag(
            'div',
            array(
                'id' => 'info-modal',
                'class' => 'modal fade',
                'tabindex' => '-1',
                'role' => 'dialog',
                'aria-labelledby' => 'info-modal-label',
                'aria-hidden' => true,
            )
        );
        $output .= html_writer::start_div('modal-dialog', array('role' => 'document'));
        $output .= html_writer::start_div('modal-content', array('role' => 'document'));
        $output .= html_writer::start_tag('div', array('class' => 'modal-header'));
        $output .= html_writer::tag(
            'button',
            '&times;',
            array(
                'type' => 'button',
                'class' => 'close',
                'data-dismiss' => 'modal',
                'aria-hidden' => 'true',
            )
        );
        $output .= html_writer::tag('h3', '', array('id' => 'info-modal-label'));
        $output .= html_writer::end_tag('div'); // End div modal-header.

        $imgsrc = $this->output->pix_url('loader', 'block_arup_mylearning');
        $img = html_writer::empty_tag('img', array('src' => $imgsrc));
        $output .= html_writer::tag('div', $img, array('class' => 'modal-body'));

        $output .= html_writer::start_tag('div', array('class' => 'modal-footer'));
        $output .= html_writer::tag(
            'button',
            get_string('close', 'block_arup_mylearning'),
            array(
                'type' => 'button',
                'class' => 'btn',
                'data-dismiss' => 'modal',
                'aria-hidden' => 'true',
            )
        );
        $output .= html_writer::end_tag('div'); // End div modal-footer.
        $output .= html_writer::end_tag('div'); // End div modal-content.
        $output .= html_writer::end_tag('div'); // End div modal-dialog.
        $output .= html_writer::end_tag('div'); // End div modal.

        return $output;
    }

    public function alert($message, $type = 'alert-warning') {
        $class = "alert {$type} fade in";
        $button = html_writer::tag('button', '&times;', array('class' => 'close', 'data-dismiss' => 'alert', 'type' => 'button'));
        return html_writer::tag('div', $button.$message, array('class' => $class));
    }
}
