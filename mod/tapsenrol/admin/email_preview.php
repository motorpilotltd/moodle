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

    $PAGE->set_context(context_system::instance());
    $PAGE->set_url('/mod/tapsenrol/admin/email_preview.php', array('type' => $type, 'id' => $id));

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

    $textonly = '';
    if ($email->html) {
        echo html_writer::tag('h4', get_string('iw:emails:htmlversion', 'tapsenrol'));
        $src = new moodle_url('/mod/tapsenrol/admin/email.php', array('type' => $type, 'id' => $id, 'format' => 'html'));
        echo html_writer::tag('iframe' , '', array('src' => $src));
    } else {
        $textonly = get_string('iw:emails:textonly', 'tapsenrol');
    }
    echo html_writer::tag('h4', get_string('iw:emails:textversion', 'tapsenrol').$textonly);
    $src = new moodle_url('/mod/tapsenrol/admin/email.php', array('type' => $type, 'id' => $id, 'format' => 'text'));
    echo html_writer::tag('iframe' , '', array('src' => $src));

    $script = <<<EOS
$(document).ready(function() {
    $('#iw-email-modal iframe').iframeAutoHeight({heightOffset: 2});
});
EOS;
    echo html_writer::tag('script', $script, array('type' => 'text/javascript'));

} catch (Exception $e) {
    echo $e->getMessage();
}
