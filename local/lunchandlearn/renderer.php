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

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once('lib.php');
require_once($CFG->libdir . '/tablelib.php');

class local_lunchandlearn_renderer extends plugin_renderer_base {
    public function block($sessions, $controls = true) {
        $content = '';
        if (empty($sessions)) {
            $context = context_system::instance();
            if (has_capability('local/lunchandlearn:edit', $context)) {
                $content .= html_writer::div(get_string('blocknosessions', 'local_lunchandlearn'), 'alert alert-warning');
                $content .= $this->block_actions(true);
            }
        } else {
            $items = array();
            foreach ($sessions as $session) {
                $items[] = $this->block_item(new lunchandlearn($session->id));
            }
            $content .= html_writer::alist($items, array('class' => 'upcomingsessions'));
            $content .= $this->block_actions(true, $controls);
        }
        return $content;
    }

    public function block_item(lunchandlearn $lal) {
        $date = $lal->scheduler->get_date();
        $params = array('view' => 'day', 'cal_d' => date('d', $date),
            'cal_m' => date('m', $date), 'cal_y' => date('Y', $date));
        return html_writer::link(new moodle_url('/calendar/view.php', $params), $lal->get_name())
               . html_writer::div($lal->get_summary(), 'summary')
               . html_writer::div($lal->scheduler->get_date_string('j M Y |', ' H:i T'), 'datestart');
    }

    public function block_actions($viewmore=true, $addinstance=true) {
        $more = true == $viewmore
                ? html_writer::span(html_writer::link(new moodle_url('/calendar/view.php', array(
                        'view' => 'month')), get_string('more', 'local_lunchandlearn')), 'viewmore')
                :'';
        $icon = new pix_icon('icon_blueplus', '', 'theme');
        $addlink = '';
        $context = context_system::instance();
        if ($addinstance && has_capability('local/lunchandlearn:edit', $context)) {
            $addurl = new moodle_url('/local/lunchandlearn/process.php');
            $addlink = html_writer::span(
                       html_writer::link($addurl,
                               $this->render($icon)
                               . html_writer::span(get_string('newlunchlearn', 'local_lunchandlearn'), 'section-modchooser-text')),
                   'section-modchooser-link');
        }
        return $more . $addlink;
    }

    public function list_global_actions() {
        $content = html_writer::start_div('global_actions');
        $content .= $this->filter_by_status();
        $content .= $this->create_lunchandlearn_btn();
        $content .= html_writer::end_div();

        return $content;
    }

    public function filter_by_status() {
        global $OUTPUT;
        return $OUTPUT->single_select(new moodle_url('/local/lunchandlearn/index.php'),
                'status', array(
                    'all' => 'Show All',
                    'open' => get_string('open', 'local_lunchandlearn'),
                    'closed' => get_string('closed', 'local_lunchandlearn')),
                optional_param('status', 'closed', PARAM_ALPHA),
                null
        );
    }

    public function create_lunchandlearn_btn() {
        return html_writer::link(
                new moodle_url('process.php'),
                get_string('newlunchlearn', 'local_lunchandlearn'),
                array('class' => 'create btn btn-primary'));
    }

    public function list_sessions($sessions) {
        $content = $this->list_global_actions();
        $session_table = new html_table();
        $session_table->baseurl = new moodle_url('/local/lunchandlearn/index.php');
        $session_table->attributes = array('class' => 'generaltable sessionlist');

        $actionth = new html_table_cell('');
        $actionth->attributes = array('class' => '', 'data-sorter' => 'false');
        $actionth->header = true;

        $session_table->head = array(
            'Session', 'Status', 'Attending', 'Capacity',
            get_string('eventregion', 'local_lunchandlearn'),
            get_string('eventcategory', 'local_lunchandlearn'),
            'Date', $actionth
        );

        foreach ($sessions as $session) {
            $session_table->data[] = $this->list_session_row($session);
        }
        return $content . html_writer::table($session_table);
    }

    /* Creates a table row for displaying a session */
    public function list_session_row($session) {
        $lal = new lunchandlearn($session->id);
        $actions = $this->list_row_actions($session);
        $cells = array();
        $cells['name'] = html_writer::link($lal->get_cal_url('full'), $session->name);
        $cells['status'] = $lal->get_status();
        $cells['attending'] = $lal->attendeemanager->get_attendee_count();
        $capacity = $lal->attendeemanager->get_capacity();
        if (empty($capacity)) {
            $capacity = '-';
        }
        $cells['capacity'] = $capacity;
        $cells['region'] = $lal->scheduler->region_name;
        $cells['category'] = $lal->category_name;
        $cells['e.timestart'] = $lal->scheduler->get_date_string('l jS F Y', ' H:i T', true);
        $cells[''] = $actions;
        return $cells;
    }

    public function list_row_actions($session) {
        $actions = html_writer::start_tag('ul', array('class' => 'actions'));
        $actions .= $this->edit_action($session);
        $actions .= $this->delete_action($session);
        $actions .= $this->attendee_action($session);
        $actions .= html_writer::end_tag('ul');
        return $actions;
    }

    public function edit_action($session) {
        global $OUTPUT;

        $url = new moodle_url('process.php', array(
            'id' => $session->id,
            'action' => 'edit'));

        return html_writer::tag('li',
                $OUTPUT->action_icon(
                        $url,
                        new pix_icon('t/edit', get_string('edit', 'local_lunchandlearn'))
                ),
                array('class' => 'edit'));
    }
    public function delete_action($session) {
        global $OUTPUT;

        $url = new moodle_url('delete.php', array(
            'id' => $session->id));

        return html_writer::tag('li',
                $OUTPUT->action_icon(
                        $url,
                        new pix_icon('t/delete', get_string('delete', 'local_lunchandlearn'))
                ),
                array('class' => 'delete'));
    }
    public function attendee_action($session) {
        global $OUTPUT;

        $url = new moodle_url('attendees.php', array(
            'id' => $session->id,
            'action' => 'list'));

        return html_writer::tag('li',
                $OUTPUT->action_icon(
                        $url,
                        new pix_icon('t/groups', get_string('attendees', 'local_lunchandlearn'))
                ),
                array('class' => 'attendees'));
    }

    public function summary_session(lunchandlearn $session) {
        global $OUTPUT;

        $summary_table = new html_table();
        $summary_table->attributes['class'] = 'generaltable sessionsummary';
        $row = new html_table_row();
        $row->cells[] = new html_table_cell(get_string('eventname', 'local_lunchandlearn'));
        $row->cells[] = new html_table_cell(html_writer::link($session->get_cal_url('full'), $session->get_name())
                . html_writer::tag('ul', $this->edit_action($session), array('class' => 'settings screen-version')));

        $summary_table->data[] = $row;
        $row = new html_table_row();
        $row->cells[] = new html_table_cell(get_string('date'));
        $row->cells[] = new html_table_cell($session->scheduler->get_date_string());
        $summary_table->data[] = $row;

        $row = new html_table_row();
        $row->cells[] = new html_table_cell(get_string('label:attendance', 'local_lunchandlearn'));
        $cell = '';
        if (true == $session->attendeemanager->availableinperson) {
            $cell .= $session->get_fa_icon(lunchandlearn::ICON_INPERSON, get_string('popover:inperson', 'local_lunchandlearn'), get_string('popover:inpersondata', 'local_lunchandlearn'))
                     . $session->attendeemanager->get_inperson_attendance_string();
        }
        if (true == $session->attendeemanager->availableonline) {
            if (!empty($cell)) {
                $cell .= '<br />';
            }
            $cell .= $session->get_fa_icon(lunchandlearn::ICON_ONLINE, get_string('popover:online', 'local_lunchandlearn'), get_string('popover:onlinedata', 'local_lunchandlearn'))
                     . $session->attendeemanager->get_online_attendance_string();
        }
        $row->cells[] = new html_table_cell($cell);
        $summary_table->data[] = $row;

        $row = new html_table_row();
        $row->cells[] = new html_table_cell(get_string('label:location', 'local_lunchandlearn'));
        $row->cells[] = new html_table_cell($session->scheduler->get_room().', '.$session->scheduler->get_office());
        $summary_table->data[] = $row;
        $row = new html_table_row();
        $row->cells[] = new html_table_cell(get_string('eventsummary', 'local_lunchandlearn'));
        $row->cells[] = new html_table_cell($session->get_summary());
        $summary_table->data[] = $row;
        $mailtolink = $session->attendeemanager->get_mailto();
        $mailto = $mailtolink ?
            html_writer::link($session->attendeemanager->get_mailto(), html_writer::tag('i', '', array('class' => "fa fa-envelope")) . '&nbsp;' . get_string('emailattendees', 'local_lunchandlearn'), array('class' => 'btn')) . '&nbsp;' : '';
        $content = html_writer::div(
            $mailto . html_writer::link('javascript:window.print()', html_writer::tag('i', '', array('class' => "icon-white icon-print")) . '&nbsp;Print', array('class' => 'btn btn-info'))
            , 'print-button-container screen-version'
        );
        return $content . html_writer::table($summary_table);
    }

    public function list_session_attendees(lunchandlearn $session) {
        global $OUTPUT;
        $content = '';

        if ($session->is_locked()) {
            $content .= $OUTPUT->notification(get_string('sessionlocked', 'local_lunchandlearn'), 'notifyproblem screen-version');
        }
        $attendeetable = new html_table('lal_attendees');
        $attendeetable->attributes['class'] = 'generaltable attendeelist';

        $attendedcheckbox = html_writer::empty_tag('br').html_writer::empty_tag('input', array('type' => 'checkbox', 'id' => 'lal-attendee-selectall'));
        $attendedcell = new html_table_cell(get_string('thead:attended', 'local_lunchandlearn') . $attendedcheckbox);
        $attendedcell->header = true;
        $attendedcell->attributes = array('class' => '', 'data-sorter' => 'false');
        $userth = new html_table_cell(
                html_writer::span(get_string('thead:user:first', 'local_lunchandlearn'), '', array('data-sort' => 'firstname')) . ' ' .
                html_writer::span(get_string('thead:user:last', 'local_lunchandlearn'), '', array('data-sort' => 'lastname')));
        $userth->attributes['data-sorter']= 'false';

        $attendeetable->head = array(
            $userth,
            get_string('thead:office', 'local_lunchandlearn'),
            get_string('thead:inperson', 'local_lunchandlearn'),
            get_string('thead:requirements', 'local_lunchandlearn'),
            get_string('thead:signupcreated', 'local_lunchandlearn'),
            $attendedcell,
            get_string('thead:signature', 'local_lunchandlearn')
        );
        $attendeetable->align = $align = array(
            'left',
            'left',
            'center',
            'left',
            'left',
            'center',
            'left',
        );
        foreach ($session->attendeemanager->get_attendees() as $attendee) {
            $attendeetable->data[] = $this->list_session_attendees_row($attendee, $session->is_locked());
        }
        $attendingusers = $session->attendeemanager->get_attendees(lunchandlearn_attendance::STATUS_ATTENDING);
        $submitbutton = $session->is_locked() && empty($attendingusers) ? '' : get_string('takeattendance', 'local_lunchandlearn');
        $content .=  $this->wrap_in_form(
                html_writer::empty_tag('input', array('name' => 'id', 'type' => 'hidden', 'value' => $session->get_id())) .
                html_writer::table($attendeetable) .
                html_writer::div(get_string('submiterror', 'local_lunchandlearn'), 'alert alert-danger', array('id' => 'submiterror', 'style' => 'display: none;')), 'attendance', $submitbutton);
        if (!empty($this->errors)) {
            $button = '<button type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></button>';
            $content .= html_writer::div($button . implode('<br />', $this->errors), 'alert alert-danger alert-dismissible', array('role' => 'alert', 'style' => 'width: 100%; box-sizing: border-box; margin-top: 10px;'));
        }
        $content .= html_writer::tag('h2', get_string('addadditionalattendeesprint', 'local_lunchandlearn'), array('class' => 'print-version'));
        $content .= html_writer::div(get_string('addadditionalprintdescription', 'local_lunchandlearn'), 'print-version');
        if (false === $session->scheduler->is_cancelled() && $session->attendeemanager->has_capacity()) {
            $content .= html_writer::tag('h2', get_string('addadditionalattendees', 'local_lunchandlearn'), array('class' => 'screen-version'));
            $addtable = new html_table();
            $addtable->head = array(
                get_string('thead:searchusers', 'local_lunchandlearn') . $OUTPUT->help_icon('thead:searchusers', 'local_lunchandlearn'),
                get_string('thead:inperson', 'local_lunchandlearn'),
                get_string('thead:requirements', 'local_lunchandlearn') . $this->sessioninfo_help($session->id),
                ''
            );
            $addtable->align = array(
                'center', 'center', 'center', 'right',
            );
            $addtable->size = array('40%', '100px', '40%', '100px');
            $addtable->data[] = $this->add_attendee_row($session);
            $content .= $this->wrap_in_form(html_writer::table($addtable), 'signup', '', 'addattendee');
        }
        // cancellations
        $attendeetable->head[4] = get_string('thead:signupdate', 'local_lunchandlearn');
        $content .= html_writer::tag('h2', get_string('cancelledattendees', 'local_lunchandlearn'), array('class' => 'screen-version'));
        $canxtable = new html_table();
        $canxtable->attributes['class'] = 'generaltable canxlist';
        $canxtable->head = array_splice($attendeetable->head,0,5);
        $canxtable->align = array_splice($align,0,5);
        foreach ($session->attendeemanager->get_attendees(lunchandlearn_attendance::STATUS_CANCELLED) as $attendee) {
            $canxtable->data[] = $this->list_session_attendees_row($attendee, false, false);
        }
        if (!empty($canxtable->data)) {
            $content .= html_writer::table($canxtable);
        } else {
            $content .= html_writer::tag('p', get_string('nocancelledattendees', 'local_lunchandlearn'), array('class' => 'screen-version'));
        }
        return $content;
    }

    public function sessioninfo_help($id) {
        $title = get_string('sessioninfo', 'local_lunchandlearn');

        $img = html_writer::empty_tag('img', array(
            'src' => $this->pix_url('help'),
            'alt' => get_string('helpwiththis'),
            'class' => 'iconhelp'));

        // now create the link around it - we need https on loginhttps pages
        $url = new moodle_url('/local/lunchandlearn/help.php', array('id' => $id));

        $link = html_writer::tag('a', $img, array('href' => $url, 'title' => $title, 'aria-haspopup' => 'true', 'target' => '_blank'));

        // and finally span
        return html_writer::tag('span', $link, array('class' => 'helptooltip'));
    }

    public function wrap_in_form($content, $action, $submit = '', $class = 'attendeelist') {
        $btn = empty($submit) ? '' : html_writer::empty_tag('input', array(
            'type'  => 'submit',
            'class' => 'submit',
            'value' => $submit
        ));
        $form = html_writer::tag('form',
                    $content .
                    html_writer::empty_tag('input', array(
                        'name'  => 'action',
                        'type'  => 'hidden',
                        'value' => $action
                    )) . $btn,
                    array (
                        'method' => 'POST',
                        'action' => '',
                        'class'  => $class . ' clearfix'
                    )
                );
        return $form;
    }

    public function list_session_attendees_row(lunchandlearn_attendance $attendee, $locked = false, $addactions = true) {
        global $OUTPUT;

        $row = new html_table_row();
        $user = $attendee->get_user();
        $idnumber = empty($user->idnumber)
                        ? ''
                        : html_writer::empty_tag('br') . html_writer::span($user->idnumber);

        $useractions = '';
        if ($addactions) {
            $actions =  $OUTPUT->action_icon(new moodle_url('attendees.php', array('id' => $attendee->lunchandlearnid, 'userid' => $attendee->userid, 'action' => 'edit')), new pix_icon('t/edit', get_string('edit', 'local_lunchandlearn')), null, array('class' => 'screen-version')) .
                        $OUTPUT->action_icon(new moodle_url('attendees.php', array('id' => $attendee->lunchandlearnid, 'userid' => $attendee->userid, 'action' => 'cancel')), new pix_icon('t/delete', get_string('cancel')), null, array('class' => 'screen-version'));
            $useractions = html_writer::div($actions, 'lal-usercell-useractions');
        }

        $userpicture = $OUTPUT->user_picture($user);
        $userinfo = html_writer::div(
                        html_writer::link(
                            new moodle_url('/user/profile.php', array('id' => $user->id)),
                            fullname($user))
                        . $idnumber
                    , 'lal-usercell-userinfo');
        $usercell = new html_table_cell(html_writer::div($userpicture.$userinfo, 'lal-usercell').$useractions);
        $usercell->attributes['data-firstname'] = $user->firstname;
        $usercell->attributes['data-lastname'] = $user->lastname;

        $row->cells[] = $usercell;
        $row->cells[] = new html_table_cell($user->address);
        $row->cells[] = new html_table_cell($attendee->get_icon());
        $row->cells[] = new html_table_cell($attendee->requirements);
        $signupcellcontent = $attendee->timesignup ? $attendee->signup_userdate : '-';
        if (false === $attendee->did_self_enrol()) {
            $signupcellcontent .= '<br />(by '.$attendee->signup_userfull.')';
        }
        $signupcell = new html_table_cell($signupcellcontent);
        $signupcell->attributes['data-value'] = $attendee->timesignup;
        $row->cells[] = $signupcell;
        if ($attendee->status != lunchandlearn_attendance::STATUS_CANCELLED) {
            if ($locked && $attendee->status == lunchandlearn_attendance::STATUS_ATTENDED) {
                $row->cells[] = new html_table_cell(html_writer::checkbox('attended['.$user->id.']', true, $attendee->status == lunchandlearn_attendance::STATUS_ATTENDED, '', array('disabled' => 'disabled')));
            } else {
                $row->cells[] = new html_table_cell(html_writer::checkbox('attendance['.$user->id.']', true, $attendee->status == lunchandlearn_attendance::STATUS_ATTENDED));
            }

            $row->cells[] = new html_table_cell('&nbsp;');
        }

        return $row;
    }
    public function add_attendee_row(lunchandlearn $lal) {
        $attendlocationselector = '';

        if ($lal->attendeemanager->availableinperson) {
            $nocapacity = '';
            $radattr = array('id' => 'inpersonyes', 'type' => 'radio', 'name' => 'inperson', 'value' => 1, 'checked' => 'checked');
            $icon = $lal->get_fa_icon(lunchandlearn::ICON_INPERSON, get_string('popover:inperson', 'local_lunchandlearn'), get_string('popover:inpersondata', 'local_lunchandlearn'));
            if (false === $lal->attendeemanager->has_inperson_capacity()) {
                $radattr['disabled'] = 'disabled';
                $nocapacity = get_string('nocapacity', 'local_lunchandlearn');
                unset($radattr['checked']);
            }
            $attendlocationselector = html_writer::empty_tag('input', $radattr);
            $attendlocationselector .= html_writer::tag('label', $icon.$nocapacity, array('for' => 'inpersonyes'));
        }
        if ($lal->attendeemanager->availableonline) {
            $nocapacity = '';
            $radattr = array('id' => 'inpersonno', 'type' => 'radio', 'name' => 'inperson', 'value' => 0);
            if (!$lal->attendeemanager->has_inperson_capacity()) {
                $radattr['checked'] = 'checked';
            }
            $icon = $lal->get_fa_icon(lunchandlearn::ICON_ONLINE, get_string('popover:online', 'local_lunchandlearn'), get_string('popover:onlinedata', 'local_lunchandlearn'));
            if (false === $lal->attendeemanager->has_online_capacity()) {
                $radattr['disabled'] = 'disabled';
                $nocapacity = get_string('nocapacity', 'local_lunchandlearn');
            }
            $attendlocationselector .= html_writer::empty_tag('input', $radattr);
            $attendlocationselector .= html_writer::tag('label', $icon.$nocapacity, array('for' => 'inpersonno'));
        }

        $row = new html_table_row();
        $row->cells[] = new html_table_cell(html_writer::empty_tag('input', array('name' => 'userid', 'type' => 'hidden')));
        $row->cells[] = new html_table_cell($attendlocationselector);
        $row->cells[] = new html_table_cell(
                html_writer::tag('textarea', '', array('name' => 'requirements')));
        $submit = new html_table_cell(html_writer::empty_tag('input', array(
            'type' => 'submit',
            'value' => get_string('addattendee', 'local_lunchandlearn'),
            'class' => 'btn-primary'
        )));
        $submit->attributes = array('class' => 'lastcol');
        $row->cells[] = $submit;
        return $row;
    }

    public function show_invite_preview(lunchandlearn $lal, stdClass $invite) {
        global $DB, $OUTPUT;
        $tofield = html_writer::start_div('tofield');
        $params = array();
        foreach ($invite->to as $to) {
            $params['to['.$to.']'] = $to;
            $user = $DB->get_record('user', array('id' => $to));
            $tofield .= html_writer::span(fullname($user), 'email', array('title' => $user->email));
        }
        $tofield .= html_writer::end_div();
        $subjectfield = html_writer::div(get_string('invitesubject', 'local_lunchandlearn', $lal->get_name()),'subjectfield');
        $bodyfield = html_writer::start_div('bodyfield');
        $bodyfield .= $invite->body['text'];
        $bodyfield .= html_writer::div(get_string('invitesignoff', 'local_lunchandlearn', (string)$lal->get_cal_url('full')));
        $bodyfield .= html_writer::end_div();
        $content = $tofield;
        $content .= $subjectfield;
        $content .= $bodyfield;
        $params['id'] = $lal->get_id();
        $params['body'] = $bodyfield;
        $params['action'] = 'send';
        $content .= $OUTPUT->confirm('Do you want to send the email now?',
                            new single_button(
                                    new moodle_url('/local/lunchandlearn/invite.php', $params),
                                    get_string('send', 'local_lunchandlearn')),
                            '/local/lunchandlearn/invite.php?id='.$lal->get_id());
        return $content;
    }

    public function search_events($sessions, $searchterm, $total, $regions)
    {
        $templateinfo = new stdClass;
        $templateinfo->sessions = [];
        $templateinfo->formatDate = function ($datetime) {

        };

        foreach ($sessions as $session) {
            $arrSession = (array)$session;
            $lal = new lunchandlearn($session->id);

            $arrSession['button'] = $this->get_lal_actions_data($lal);
            $arrSession['attendance'] = $this->get_lal_attendance($lal);

            $arrSession['url'] = new moodle_url('/calendar/view.php', array('view'=> 'event', 'id'=> $session->id));
            $arrSession['sessionDateTimeRange'] = date('F, d M, H:i T', $session->timestart);

            // Do highlighting
            $arrSession['name'] = highlight($searchterm, $session->name);
            $arrSession['summary'] = highlight($searchterm, $session->summary);

            $templateinfo->sessions[] = $arrSession;
        }
        $templateinfo->sessioncount = $total;
        $templateinfo->regions = $regions;

        return $this->render_from_template('local_lunchandlearn/search', $templateinfo);
    }

    public function get_lal_attendance(lunchandlearn $lal)
    {
        if ($lal->scheduler->is_cancelled()) {
            return '';
        }

        $cell = html_writer::start_span('attendance');
        if (true == $lal->attendeemanager->availableinperson) {
            $cell .= $lal->get_fa_icon(lunchandlearn::ICON_INPERSON, get_string('popover:inperson', 'local_lunchandlearn'), get_string('popover:inpersondata', 'local_lunchandlearn'));
            $cell .= $lal->attendeemanager->get_remaining_inperson(20);
        }
        if (true == $lal->attendeemanager->availableonline) {
            $cell .= $lal->get_fa_icon(lunchandlearn::ICON_ONLINE, get_string('popover:online', 'local_lunchandlearn'), get_string('popover:onlinedata', 'local_lunchandlearn'));
            $cell .= $lal->attendeemanager->get_remaining_online(20);
        }
        return get_string('label:availableplaces', 'local_lunchandlearn') . $cell . html_writer::end_span();
    }

    public function get_lal_actions_data(lunchandlearn $lal)
    {
        global $USER;

        if ($lal->scheduler->is_cancelled()) {

            return '';
        }

        $button = array();

        if ($lal->scheduler->has_past()) {

            if ($lal->did_attend()) {
                $button['title'] = get_string('attended', 'local_lunchandlearn');
                $button['buttonclass'] = 'btn btn-success dropdown-toggle';
            } else {
                $button['title'] = get_string('notattended', 'local_lunchandlearn');
                $button['buttonclass'] = 'btn btn-warning dropdown-toggle';
            }
            $button['toggle'] = 'dropdown';
        } else {

            if ($lal->attendeemanager->is_user_signedup($USER->id)) {

                $button['actionurl'] = new moodle_url('/local/lunchandlearn/signup.php', array(
                    'backto' => '',
                    'action' => 'cancel',
                    'id' => $lal->get_id()));

                $button['title'] = get_string('attending', 'local_lunchandlearn');
                $button['actiontitle'] = get_string('cancelsignup', 'local_lunchandlearn');
                $button['buttonclass'] = 'btn btn-success dropdown-toggle';
                $button['toggle'] = 'dropdown';

            } else if (false === $lal->attendeemanager->has_capacity()) {
                $button['title'] = get_string('button:nocapacity', 'local_lunchandlearn');
                $button['buttonclass'] = 'btn btn-warning btn-fullup';
            } else {
                $button['actionurl'] = new moodle_url('/local/lunchandlearn/signup.php', array('id' => $lal->get_id()));
                $button['actiontitle'] = get_string('signup', 'local_lunchandlearn');
                $button['actionclass'] = 'lalsignup btn btn-primary btn-signup';
            }
        }

        return $button;
    }
}
