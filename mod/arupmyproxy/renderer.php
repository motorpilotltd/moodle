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
 *
 * @package    mod_arupmyproxy
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class mod_arupmyproxy_renderer extends plugin_renderer_base {

    public function view_wrapper($input, $visible) {

        $show = html_writer::span(get_string('button:wrapper:show:pre', 'arupmyproxy'), 'arupmyproxy-wrapper-button-pre') .
            html_writer::span(get_string('button:wrapper:show', 'arupmyproxy'), 'arupmyproxy-wrapper-button-main') .
            html_writer::span(get_string('button:wrapper:show:post', 'arupmyproxy'), 'arupmyproxy-wrapper-button-post');
        $hide = html_writer::span(get_string('button:wrapper:hide:pre', 'arupmyproxy'), 'arupmyproxy-wrapper-button-pre') .
            html_writer::span(get_string('button:wrapper:hide', 'arupmyproxy'), 'arupmyproxy-wrapper-button-main') .
            html_writer::span(get_string('button:wrapper:hide:post', 'arupmyproxy'), 'arupmyproxy-wrapper-button-post');

        $output = html_writer::tag(
            'a',
            $visible ? $hide : $show,
            array(
                'class' => $visible ? 'arupmyproxy-wrapper-button' : 'arupmyproxy-wrapper-button arupmyproxy-wrapper-hidden',
                'data-hidden' => $show,
                'data-visible' => $hide
            )
        );
        $output .= html_writer::start_div('arupmyproxy-wrapper', array('style' => $visible ? '' : 'display: none;'));
        $output .= $input;
        $output .= html_writer::end_div(); // End div arupmyproxy-wrapper.

        return $output;
    }

    public function proxy_loginas($users, $cmid) {
        global $SESSION;

        $output = '';
        if (empty($users)) {
            return $output;
        }
        $error = '';
        if (isset($SESSION->arupmyproxy[$cmid]->errors->loginas)) {
            $error = $this->alert($SESSION->arupmyproxy[$cmid]->errors->loginas->message, $SESSION->arupmyproxy[$cmid]->errors->loginas->type);
            unset($SESSION->arupmyproxy[$cmid]->errors);
        }
        $actionurl = new moodle_url('/mod/arupmyproxy/loginas.php');
        $output .= html_writer::start_tag('form', array('id' => "form-proxy-loginas-{$cmid}", 'class' => 'arupmyproxy-form-proxy', 'action' => $actionurl, 'method' => 'POST'));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $cmid));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
        $output .= $error;
        $output .= html_writer::tag('label', get_string('label:loginas', 'arupmyproxy'), array('for' => 'p'));
        $output .= html_writer::start_tag('select', array('name' => 'p'));
        $output .= html_writer::tag('option', get_string('choosedots'), array('value' => ''));
        foreach ($users as $user) {
            $output .= html_writer::tag('option', fullname($user), array('value' => $user->id));
        }
        $output .= html_writer::end_tag('select');
        $output .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'submit', 'value' => get_string('button:loginas', 'arupmyproxy')));
        $output .= html_writer::end_tag('form');
        return $output;
    }

    public function proxy_request($users, $cmid) {
        global $SESSION;

        $output = '';
        if (empty($users)) {
            return $output;
        }
        $error = '';
        if (isset($SESSION->arupmyproxy[$cmid]->errors->request)) {
            $error = $this->alert($SESSION->arupmyproxy[$cmid]->errors->request->message, $SESSION->arupmyproxy[$cmid]->errors->request->type);
            unset($SESSION->arupmyproxy[$cmid]->errors);
        }
        $actionurl = new moodle_url('/mod/arupmyproxy/request.php');
        $output .= html_writer::start_tag('form', array('id' => "form-proxy-request-{$cmid}", 'class' => 'arupmyproxy-form-proxy', 'action' => $actionurl, 'method' => 'POST'));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $cmid));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
        $output .= $error;
        $output .= html_writer::tag('label', get_string('label:request', 'arupmyproxy'), array('for' => 'p'));
        $output .= html_writer::start_tag('select', array('name' => 'p'));
        $output .= html_writer::tag('option', get_string('choosedots'), array('value' => ''));
        foreach ($users as $user) {
            $output .= html_writer::tag('option', fullname($user), array('value' => $user->id));
        }
        $output .= html_writer::end_tag('select');
        $output .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'submit', 'value' => get_string('button:request', 'arupmyproxy')));
        $output .= html_writer::end_tag('form');
        return $output;
    }

    public function proxy_pending($users, $cmid) {
        global $SESSION;

        $output = '';
        if (empty($users)) {
            return $output;
        }
        $error = '';
        if (isset($SESSION->arupmyproxy[$cmid]->errors->pending)) {
            $error = $this->alert($SESSION->arupmyproxy[$cmid]->errors->pending->message, $SESSION->arupmyproxy[$cmid]->errors->pending->type);
            unset($SESSION->arupmyproxy[$cmid]->errors);
        }
        $actionurl = new moodle_url('/mod/arupmyproxy/pending.php');
        $output .= html_writer::start_tag('form', array('id' => "form-proxy-pending-{$cmid}", 'class' => 'arupmyproxy-form-proxy', 'action' => $actionurl, 'method' => 'POST'));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $cmid));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'sesskey', 'value' => sesskey()));
        $output .= $error;
        $output .= html_writer::tag('label', get_string('label:pending', 'arupmyproxy'), array('for' => 'p'));
        $output .= html_writer::start_tag('select', array('name' => 'p'));
        $output .= html_writer::tag('option', get_string('choosedots'), array('value' => ''));
        foreach ($users as $user) {
            $output .= html_writer::tag('option', fullname($user), array('value' => $user->id));
        }
        $output .= html_writer::end_tag('select');
        $output .= html_writer::tag(
                'button',
                get_string('button:remind', 'arupmyproxy'),
                array('class' => 'btn btn-default', 'type' => 'submit', 'name' => 'remind', 'value' => 1)
                );
        $output .= html_writer::tag(
                'button',
                get_string('button:delete', 'arupmyproxy'),
                array('class' => 'btn btn-danger', 'type' => 'submit', 'name' => 'delete', 'value' => 1)
                );
        $output .= html_writer::end_tag('form');
        return $output;
    }

    public function proxy_refused($users, $cmid) {
        $output = '';
        if (empty($users)) {
            return $output;
        }
        $output .= html_writer::start_tag('form', array('id' => "form-proxy-refused-{$cmid}", 'class' => 'arupmyproxy-form-proxy', 'method' => 'POST'));
        $output .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => 'id', 'value' => $cmid));
        $output .= html_writer::tag('label', get_string('label:refused', 'arupmyproxy'), array('for' => 'p'));
        $output .= html_writer::start_tag('select', array('name' => 'p'));
        foreach ($users as $user) {
            $output .= html_writer::tag('option', fullname($user), array('value' => $user->id));
        }
        $output .= html_writer::end_tag('select');
        $output .= html_writer::end_tag('form');
        return $output;
    }

    public function alert($message, $type = 'alert-warning', $exitbutton = true, $center = false) {
        $class = "alert {$type} fade in";
        if ($center) {
            $class .= ' text-center';
        }
        $button = '';
        if ($exitbutton) {
            $button .= html_writer::tag('button', '&times;', array('class' => 'close', 'data-dismiss' => 'alert', 'type' => 'button'));
        }
        return html_writer::tag('div', $button.$message, array('class' => $class));
    }

    public function back_to_module($courseid) {
        $url = new moodle_url('/course/view.php', array('id' => $courseid));
        $link = html_writer::link($url, get_string('backtomodule', 'arupmyproxy'));
        return html_writer::tag('p', $link);
    }
}