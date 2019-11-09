<?php
/*
 * This file is part of T0tara LMS
 *
 * Copyright (C) 2017 onwards T0tara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Rob Tyler <rob.tyler@t0taralearning.com>
 * @package local_reportbuilder
 */

namespace local_reportbuilder\rb\display;

/**
 * Class describing column display formatting.
 *
 * @author Rob Tyler <rob.tyler@t0taralearning.com>
 * @package local_reportbuilder
 */
class user_actions extends base {

    public static function display($userid, $format, \stdClass $row, \rb_column $column, \reportbuilder $report) {
        global $CFG, $OUTPUT, $USER;

        if ($format !== 'html') {
            // Only applicable to the HTML format.
            return '';
        }

        // Note: performance here is not an issue because this column is not exported.
        require_once("$CFG->dirroot/lib/authlib.php");

        if (isguestuser($userid)) {
            // No actions for the guest user.
            return '';
        }

        $user = self::get_extrafields_row($row, $column);
        $user->id = $userid;

        if ($user->mnethostid != $CFG->mnet_localhost_id) {
            // We will not support Mnet in this report!
            return \html_writer::span(
                get_string('mnetuser', 'local_reportbuilder'),
                'label label-info',
                array('title' => get_string('mnetnotsupported', 'local_reportbuilder'))
            );
        }

        $returnurl = new \moodle_url($report->get_current_url());
        $spage = optional_param('spage', '', PARAM_INT);
        if ($spage) {
            $returnurl->param('spage', $spage);
        }
        $perpage = optional_param('perpage', '', PARAM_INT);
        if ($perpage) {
            $returnurl->param('perpage', $perpage);
        }
        $returnurl = $returnurl->out_as_local_url(false);
        $actionurl = new \moodle_url('/user/action.php', array('id' => $user->id, 'returnurl' => $returnurl));

        $sitecontext = \context_system::instance();
        if ($user->deleted) {
            $usercontext = $sitecontext;
        } else {
            $usercontext = \context_user::instance($user->id, IGNORE_MISSING);
            if (!$usercontext) {
                // This should never happen.
                return '';
            }
        }

        $buttons = array();

        if ($user->deleted) {
            if (has_capability('totara/core:undeleteuser', $sitecontext)) {
                // If the record has been marked as deleted, don't show any edit, suspend etc icons
                $preg_emailhash = '/^[0-9a-f]{32}$/i';

                $title = get_string('undeleterecord', 'local_reportbuilder', $user->fullname);
                $buttons[] = \html_writer::link(
                    new \moodle_url($actionurl, array('action' => 'undelete')),
                    $OUTPUT->flex_icon('recycle', array('alt' => $title)),
                    array('title' => $title)
                );

                if ($CFG->authdeleteusers !== 'partial' && !preg_match($preg_emailhash, $user->email)) {
                    $title = get_string('deleterecord', 'local_reportbuilder', $user->fullname);
                    $buttons[] = \html_writer::link(
                        new \moodle_url($actionurl, array('action' => 'delete')),
                        $OUTPUT->pix_icon('t/delete', array('alt' => $title)),
                        array('title' => $title)
                    );
                }
            }

        } else {
            // Here we want the icons to appear in a logical, useful order, and for their
            // positions to be as consistent as possible to improve usability. We want
            // the order to be: edit, suspend, delete; as these three are the three main
            // action icons, followed by: unlock and confirm.

            $issiteadmin = is_siteadmin($userid);
            $iscurrentuser = ($userid == $USER->id);
            $canupdate = has_capability('moodle/user:update', $sitecontext);
            $candelete = has_capability('moodle/user:delete', $sitecontext);

            // Add edit action icon but prevent editing of admins by non-admin users.
            if ((is_siteadmin($USER) || !$issiteadmin)) {
                if ($canupdate) {
                    $title = get_string('editrecord', 'local_reportbuilder', $user->fullname);
                    $buttons[] = \html_writer::link(
                        new \moodle_url('/user/editadvanced.php', array('id'=>$userid, 'course'=>SITEID, 'returnurl' => $returnurl)),
                        $OUTPUT->pix_icon('i/settings', array('alt' => $title)),
                        array('title' => $title)
                    );
                } else if (has_capability('moodle/user:editprofile', $usercontext)) {
                    $title = get_string('editrecord', 'local_reportbuilder', $user->fullname);
                    $buttons[] = \html_writer::link(
                        new \moodle_url('/user/edit.php', array('id'=>$userid, 'course'=>SITEID, 'returnurl' => $returnurl)),
                        $OUTPUT->pix_icon('i/settings', array('alt' => $title)),
                        array('title' => $title)
                    );
                }
            }

            // Add suspend and unsuspend icons.
            if ($canupdate && !$iscurrentuser && !$issiteadmin) {
                if ($user->suspended) {
                    $title = get_string('unsuspendrecord', 'local_reportbuilder', $user->fullname);
                    $buttons[] = \html_writer::link(
                        new \moodle_url($actionurl, array('action' => 'unsuspend', 'sesskey' => sesskey())),
                        $OUTPUT->pix_icon('i/show', array('alt' => $title)),
                        array('title' => $title)
                    );
                } else {
                    $title = get_string('suspendrecord', 'local_reportbuilder', $user->fullname);
                    $buttons[] = \html_writer::link(
                        new \moodle_url($actionurl, array('action' => 'suspend', 'sesskey' => sesskey())),
                        $OUTPUT->pix_icon('i/hide', array('alt' => $title)),
                        array('title' => $title)
                    );
                }
            }

            // Add delete action icon.
            if ($candelete && !$iscurrentuser && !$issiteadmin) {
                $title = get_string('deleterecord', 'local_reportbuilder', $user->fullname);
                $buttons[] = \html_writer::link(
                    new \moodle_url($actionurl, array('action' => 'delete')),
                    $OUTPUT->pix_icon('t/delete', array('alt' => $title)),
                    array('title' => $title)
                );
            }

            // Add an unlock icon for when the user has locked their account.
            if ($canupdate && login_is_lockedout($user)) {
                $title = get_string('unlockrecord', 'local_reportbuilder', $user->fullname);
                $buttons[] = \html_writer::link(
                    new \moodle_url($actionurl, array('action' => 'unlock', 'sesskey' => sesskey())),
                    $OUTPUT->flex_icon('unlock', array('alt' => $title)),
                    array('title' => $title)
                );
            }

            // If a user is self-registered allow the user to confirm the user.
            if ($canupdate && empty($user->confirmed)) {
                $title = get_string('confirmrecord', 'local_reportbuilder', $user->fullname);
                $buttons[] = \html_writer::link(
                    new \moodle_url($actionurl, array('action' => 'confirm', 'sesskey' => sesskey())),
                    $OUTPUT->flex_icon('check', array('alt' => $title)),
                    array('title' => $title)
                );
            }
        }

        if ($buttons) {
            return implode ('', $buttons);
        } else {
            return '';
        }
    }

    public static function is_graphable(\rb_column $column, \rb_column_option $option, \reportbuilder $report) {
        return false;
    }
}
