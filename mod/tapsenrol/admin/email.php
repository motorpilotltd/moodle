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

require_once(dirname(__FILE__).'/../../../config.php');

try {
    require_login();

    $type = required_param('type', PARAM_ALPHA);
    $id = required_param('id', PARAM_INT);
    $format = required_param('format', PARAM_ALPHA);

    $PAGE->set_context(context_system::instance());
    $PAGE->set_url('/mod/tapsenrol/admin/email.php', array('type' => $type, 'id' => $id));

    switch ($type) {
        case 'cm' :
        case 'iw' :
        case 'global' :
            $email = $DB->get_record('tapsenrol_iw_email_custom', array('id' => $id));
            break;
        default :
            $email = $DB->get_record('tapsenrol_iw_email', array('id' => $id));
            break;
    }

    if (!$email) {
        throw new moodle_exception('iw:emails:error:emailnotfound', 'tapsenrol');
    }

    switch ($format) {
        case 'html' :
            echo $email->body;
            break;
        default :
            if ($email->html) {
                $emailbody = html_to_text($email->body);
            } else {
                $emailbody = $email->body;
            }
            echo html_writer::tag('pre', $emailbody, array('style' => 'white-space:pre-wrap;'));
            break;
    }

} catch (Exception $e) {
    echo $e->getMessage();
}
