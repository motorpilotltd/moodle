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

require(dirname(__FILE__).'/../../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$action = optional_param('action', 'view', PARAM_TEXT);
$id = optional_param('id', 0, PARAM_INT);

$urlparams = array('action' => $action);
if (in_array($action, array('edit', 'delete', 'lock', 'duplicate'))) {
    $urlparams['id'] = $id;
}

admin_externalpage_setup('mod_tapsenrol_iw_configure', '', $urlparams);

$PAGE->set_title(get_string('internalworkflow_heading', 'tapsenrol'));
$PAGE->add_body_class('path-mod-tapsenrol-iw');

$output = $PAGE->get_renderer('mod_tapsenrol');

$viewurl = new moodle_url('/mod/tapsenrol/admin/internalworkflow.php', array('action' => 'view'));

$hascap = array(
    'global' => has_capability('mod/tapsenrol:internalworkflow', $PAGE->context),
    'edit' => false,
    'lock' => false,
);
if (!$hascap['global']) {
    $hascap['edit'] = has_capability('mod/tapsenrol:internalworkflow_edit', $PAGE->context);
    $hascap['lock'] = has_capability('mod/tapsenrol:internalworkflow_lock', $PAGE->context);
}

$editaction = in_array($action, array('edit', 'delete', 'duplicate'));
if ($editaction && !$hascap['global'] && !$hascap['edit']) {
    redirect($viewurl, get_string('nopermissions', 'tapsenrol'));
}
if ($action == 'lock' && !$hascap['global'] && !$hascap['lock']) {
    redirect($viewurl, get_string('nopermissions', 'tapsenrol'));
}

// Build View.
$echo = '';
switch ($action) {
    case 'edit' :
        require_once($CFG->dirroot.'/mod/tapsenrol/admin/forms/internalworkflow_form.php');

        $iw = $DB->get_record('tapsenrol_iw', array('id' => $id));
        if ($id && !$iw) {
            redirect($viewurl, get_string('invalidworkflow', 'tapsenrol'));
        }
        if ($iw) {
            // Load declarations.
            $iw->declaration = $DB->get_records_menu('tapsenrol_iw_declaration', array('internalworkflowid' => $iw->id), '', 'declarationid, declaration');
        }

        $customdata = array();
        $customdata['locked'] = !empty($iw->locked);
        $customdata['declarationcount'] = ($iw) ? count($iw->declaration) : 1;
        $form = new internalworkflow_form(null, $customdata);

        if ($form->is_cancelled()) {
            if (!empty($iw->locked)) {
                redirect($viewurl);
            } else {
                redirect($viewurl, get_string('iw:editing:redirect:cancelled', 'tapsenrol'));
            }
            exit;
        }

        $fromform = $form->get_data();
        if ($fromform) {
            if (!empty($iw->locked)) {
                redirect($viewurl, get_string('iw:editing:redirect:locked', 'tapsenrol'));
            }
            $fromform->timemodified = time();
            // Tidy up sponsors emails.
            $fromform->sponsors = trim(
                preg_replace(
                    "/\n+/",
                    "\n",
                    preg_replace("/\r\n|\r/", "\n", $fromform->sponsors)
                )
            );
            if ($iw) {
                // Editing.
                $fromform->id = $iw->id;
                $DB->update_record('tapsenrol_iw', $fromform);
            } else {
                $fromform->timecreated = $fromform->timemodified;
                $iw = new stdClass();
                $iw->id = $DB->insert_record('tapsenrol_iw', $fromform);
            }

            // Clear existing declarations.
            $DB->delete_records('tapsenrol_iw_declaration', array('internalworkflowid' => $iw->id));
            $declarationrecord = new stdClass();
            $declarationrecord->internalworkflowid = $iw->id;
            $declarationcount = 0;
            if (!empty($fromform->declaration)) {
                foreach ($fromform->declaration as $declaration) {
                    if (!empty($declaration)) {
                        $declarationrecord->declarationid = $declarationcount;
                        $declarationrecord->declaration = $declaration;
                        if ($DB->insert_record('tapsenrol_iw_declaration', $declarationrecord)) {
                            $declarationcount++;
                        }
                    }
                }
            }

            redirect($viewurl, get_string('iw:editing:redirect:saved', 'tapsenrol'));
            exit;
        }

        $form->set_data($iw);
        break;
    case 'delete' :
        $iw = $DB->get_record('tapsenrol_iw', array('id' => $id));
        if (!$iw) {
            redirect($viewurl, get_string('invalidworkflow', 'tapsenrol'));
        }

        $iwused = $DB->get_records('tapsenrol', array('internalworkflowid' => $iw->id));
        if ($iwused) {
            redirect($viewurl, get_string('iw:delete:redirect:inuse', 'tapsenrol'));
        }

        if (optional_param('confirm', 0, PARAM_INT)) {
            require_sesskey();
            $DB->delete_records('tapsenrol_iw_email_custom', array('internalworkflowid' => $iw->id, 'coursemoduleid' => null));
            $DB->delete_records('tapsenrol_iw_declaration', array('internalworkflowid' => $iw->id));
            $DB->delete_records('tapsenrol_iw', array('id' => $iw->id));
            redirect($viewurl, get_string('iw:delete:redirect:saved', 'tapsenrol'));
        }
        break;
    case 'lock' :
        $iw = $DB->get_record('tapsenrol_iw', array('id' => $id));
        if (!$iw) {
            redirect($viewurl, get_string('invalidworkflow', 'tapsenrol'));
        }
        $iw->locked = optional_param('lock', 1, PARAM_INT);
        $iw->timemodified = time();
        $DB->update_record('tapsenrol_iw', $iw);
        $message = $iw->locked ? get_string('iw:lock:redirect:locked', 'tapsenrol') : get_string('iw:lock:redirect:unlocked', 'tapsenrol');
        redirect($viewurl, $message);
        break;
    case 'duplicate' :
        $iw = $DB->get_record('tapsenrol_iw', array('id' => $id));
        if (!$iw) {
            redirect($viewurl, get_string('invalidworkflow', 'tapsenrol'));
        }

        if (optional_param('confirm', 0, PARAM_INT)) {
            require_sesskey();
            $oldid = $iw->id;
            unset($iw->id);
            $iw->name = '[COPY] '.$iw->name;
            $iw->timecreated = $iw->timemodified = time();
            $iw->id = $DB->insert_record('tapsenrol_iw', $iw);
            $emails = $DB->get_records('tapsenrol_iw_email_custom', array('internalworkflowid' => $oldid));
            foreach ($emails as $email) {
                unset($email->id);
                $email->internalworkflowid = $iw->id;
                $email->id = $DB->insert_record('tapsenrol_iw_email_custom', $email);
            }
            $declarations = $DB->get_records('tapsenrol_iw_declaration', array('internalworkflowid' => $oldid));
            foreach ($declarations as $declaration) {
                unset($declaration->id);
                $declaration->internalworkflowid = $iw->id;
                $declaration->id = $DB->insert_record('tapsenrol_iw_declaration', $declaration);
            }
            redirect($viewurl, get_string('iw:duplicate:redirect:saved', 'tapsenrol'));
        }
        break;
    case 'view' :
    default:
        $iws = $DB->get_records('tapsenrol_iw');
        break;
}

// Output View.
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('internalworkflow_heading', 'tapsenrol'));

switch ($action) {
    case 'edit' :
        if ($iw) {
            // Link to allow editing of emails.
            $url = new moodle_url('/mod/tapsenrol/admin/emails.php', array('iw' => $iw->id));
            $linktext = empty($iw->locked) ? get_string('iw:emails:title:iw:edit', 'tapsenrol') : get_string('iw:emails:title:iw:view', 'tapsenrol');
            $link = html_writer::link($url, $linktext, array('class' => 'btn btn-primary'));
            echo html_writer::tag('p', $link);
        }
        $form->display();
        break;
    case 'delete' :
        $confirmurl = new moodle_url('/mod/tapsenrol/admin/internalworkflow.php', array('action' => 'delete', 'id' => $iw->id, 'confirm' => 1, 'sesskey' => sesskey()));
        
        $continue = html_writer::link($confirmurl, get_string('iw:delete', 'tapsenrol'), array('class' => 'btn btn-primary'));
        $cancel = html_writer::link($viewurl, get_string('cancel'), array('class' => 'btn btn-default m-l-10'));

        echo html_writer::tag('p', get_string('iw:delete:redirect:confirm', 'tapsenrol', $iw->name));
        echo html_writer::tag('div', $continue . $cancel, array('class' => 'buttons'));
        break;
    case 'duplicate' :
        $confirmurl = new moodle_url('/mod/tapsenrol/admin/internalworkflow.php', array('action' => 'duplicate', 'id' => $iw->id, 'confirm' => 1, 'sesskey' => sesskey()));
        
        $continue = html_writer::link($confirmurl, get_string('iw:duplicate', 'tapsenrol'), array('class' => 'btn btn-primary'));
        $cancel = html_writer::link($viewurl, get_string('cancel'), array('class' => 'btn btn-default m-l-10'));

        echo html_writer::tag('p', get_string('iw:duplicate:redirect:confirm', 'tapsenrol', $iw->name));
        echo html_writer::tag('div', $continue . $cancel, array('class' => 'buttons'));
        break;
    case 'view' :
    default :
        echo $output->iw_emails($hascap);
        echo $output->iw_current($iws, $hascap);
        echo $output->iw_new($hascap);
        break;
}

echo $OUTPUT->footer();
