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

require_once(dirname(dirname(dirname(__FILE__))) . '/calendar/lib.php');

require_once('classes/lal_base.php');
require_once('classes/lunchandlearn_attendance_manager.php');
require_once('classes/lunchandlearn_schedule.php');
require_once('classes/lunchandlearn_attendance.php');
require_once('classes/lunchandlearn.php');
require_once('classes/lunchandlearn_manager.php');

function lunchandlearn_get_categories_list(coursecat $category = null) {
    global $CFG;

    $rtn = array();

    $topdepth = coursecat::get($CFG->lunchandlearnrootcategory)->depth;

    if (empty($category)) {
        $category = coursecat::get($CFG->lunchandlearnrootcategory);
        $rtn = array('' => get_string('nocategory', 'local_lunchandlearn'));
    } else {
        $padding = str_repeat('&nbsp;', 2 * ($category->depth - (1 + $topdepth)));

        if ($category->depth - $topdepth > 1) {
            $padding .= '-&nbsp;';
        }

        $rtn[$category->id] = $padding . $category->name;
    }
    if ($category->has_children()) {
        foreach ($category->get_children() as $child) {
            $rtn += lunchandlearn_get_categories_list($child);
        }
    }

    return $rtn;
}

function lunchandlearn_get_moodle_user_timezone($user) {
    $timezone = empty($user->timezone) ? date_default_timezone_get() : $user->timezone;
    try {
        $usertimezone = new DateTimeZone($timezone);
    } catch (Exception $e) {
        if ($timezone == 99) {
            $tzstr = date_default_timezone_get();
        } else if (preg_match('/\d+\.\d+/', $timezone)) {
            $tzstr = lunchandlearn_convert_hours_to_timezonestring($timezone);
        } else {
            $tzstr = timezone_name_from_abbr($timezone);
        }
        try {
            $usertimezone = new DateTimeZone($tzstr);
        } catch (Exception $ex) {
            $usertimezone = new DateTimeZone(date_default_timezone_get());
        }
    }
    return $usertimezone;
}

function lunchandlearn_convert_hours_to_timezonestring($hours) {
    $seconds = $hours*(3600);

    $tzstr = timezone_name_from_abbr("", $seconds, date('I'));
    if ($tzstr === false) {
        foreach (timezone_abbreviations_list() as $list) {
            foreach ($list as $city) {
                if ($city['offset'] == $seconds) {
                    return $city['timezone_id'];
                }
            }
        }
    }
    return $tzstr;
}

function local_lunchandlearn_cron() {
    $now = time();
    $lastcron = get_config('local_lunchandlearn', 'lastcron');
    // Run early morning.
    if (date('G') == '6' || !$lastcron) {
        if (!$lastcron || $lastcron < strtotime('today', $now)) {
            // Send reminders here.
        }
    }
}

function lunchandlearn_get_region() {
    global $DB, $USER, $SESSION;

    $regionurl = optional_param('regionid', null, PARAM_ALPHANUMEXT);
    if (isset($regionurl)) {
        if (isset($SESSION->filter_regionid) && $regionurl != $SESSION->filter_regionid) {
            purge_all_caches();
        }
        $SESSION->filter_regionid = $regionurl;
        if (is_numeric($regionurl)) {
            return $regionurl;
        }
    }
    if (isset($SESSION->filter_regionid) && is_numeric($SESSION->filter_regionid)) {
        return $SESSION->filter_regionid;
    }
    $userregions = $DB->get_records('local_regions_use', array('userid' => $USER->id));
    if (!empty($userregions)) {
        $userregionrow = array_shift($userregions);
        return $userregionrow->regionid;
    }
    return -1; // All regions.
}

function lunchandlearn_add_event_key() {
    global $PAGE, $USER;

    $properties = array(
        'key' => 'eventkeymenu',
        'type' => navigation_node::TYPE_SYSTEM,
        'text' => 'Menu'
    );
    $eventkey = new navigation_node($properties);

    $ek = $eventkey->add(get_string('eventskey', 'calendar'), null, navigation_node::TYPE_ROOTNODE, null, 'eventskey');
    $ek->force_open();

    $returnurl = $PAGE->url;

    $roles = get_archetype_roles('manager');
    $userismanager = false;
    foreach ($roles as $role) {
        if (user_has_role_assignment($USER->id, $role->id)) {
            $userismanager = true;
            break;
        }
    }

    $hasmarkablecategories = count(lunchandlearn_manager::get_markable_categories()) > 0;
    $canlaledit = has_capability('local/lunchandlearn:edit', $PAGE->context);
    $canmanageentries = has_capability('moodle/calendar:manageentries', $PAGE->context);

    if (
            $hasmarkablecategories
        ||  $canlaledit
        ||  ($userismanager && $canmanageentries)
    ) {

        $le = $eventkey->add(get_string('lunchandlearnadmin', 'local_lunchandlearn'), null, navigation_node::TYPE_ROOTNODE, null, 'lunchandlearn');

        if ($hasmarkablecategories) {
            $le->add(get_string('lunchandlearnviewsessions', 'local_lunchandlearn'), new moodle_url('/local/lunchandlearn/index.php'));
            $le->add(get_string('settings', 'local_lunchandlearn'), new moodle_url('/admin/settings.php', array('section' => 'lunchandlearnglobalsettings')));
        }
        if ($canlaledit) {
            $addlalurl = new moodle_url('/local/lunchandlearn/process.php', $returnurl->params());
            $addlalurl->param('id', 0);
            $le->add(get_string('newlunchlearn', 'local_lunchandlearn'), $addlalurl);
        }

        if ($userismanager && $canmanageentries) {
            $addurl = new moodle_url(CALENDAR_URL . 'event.php', $returnurl->params());
            $addurl->param('id', 0);
            $le->add(get_string('addevent', 'calendar'), $addurl);
        }
    }

    $glink = new moodle_url(CALENDAR_URL.'set.php', array('var' => 'showglobal', 'return' => base64_encode($returnurl->out_as_local_url(false)), 'sesskey' => sesskey()));
    $globalicon = new pix_icon('i/show', 'off');
    if (calendar_show_event_type(CALENDAR_EVENT_GLOBAL)) {
        $globalicon = new pix_icon('i/hide', 'on');
    }
    $ek->add(get_string('globalevent', 'calendar'), $glink, navigation_node::TYPE_SETTING, null, 'globalevent', $globalicon);

    $clink = clone $glink;
    $courseicon = new pix_icon('i/show', 'off');
    if (calendar_show_event_type(CALENDAR_EVENT_COURSE)) {
        $courseicon = new pix_icon('i/hide', 'on');
    }
    $clink->param('var', 'showcourses');
    $ek->add(get_string('courseevent', 'calendar'), $clink, navigation_node::TYPE_SETTING, null, 'courseevent', $courseicon);

    if (has_capability('local/lunchandlearn:view', context_system::instance())) {
        $llink = clone $glink;
        $lalicon = new pix_icon('i/show', 'off');
        if (calendar_show_event_type(CALENDAR_EVENT_LUNCHANDLEARN)) {
            $lalicon = new pix_icon('i/hide', 'on');
        }
        $llink->param('var', 'showlunchandlearn');
        $ek->add(get_string('lunchandlearnevent', 'local_lunchandlearn'), $llink, navigation_node::TYPE_SETTING, null, 'lunchandlearnevent', $lalicon);
    }

    $arguments = array(
        'instanceid' => 'ek'
    );
    $PAGE->requires->js_call_amd('block_navigation/navblock', 'init', $arguments);

    return $eventkey;
}

function lunchandlearn_add_page_navigation(moodle_page $page, moodle_url $url) {
    global $USER;

    $roles = get_archetype_roles('manager');
    $userismanager = false;
    foreach ($roles as $role) {
        if (user_has_role_assignment($USER->id, $role->id)) {
            $userismanager = true;
            break;
        }
    }

    $canlaledit = has_capability('local/lunchandlearn:edit', $page->context);
    $canmanageentries = has_capability('moodle/calendar:manageentries', $page->context);
    $hasmarkablecategories = count(lunchandlearn_manager::get_markable_categories()) > 0;
    $sessions = lunchandlearn_manager::get_user_sessions();

    if (
            $hasmarkablecategories
        ||  $canlaledit
        ||  ($userismanager && $canmanageentries)
        ||  $sessions
    ) {

        $addurl = new moodle_url(CALENDAR_URL . 'event.php', $url->params());
        $cal = $page->navigation->find('calendar', navigation_node::TYPE_UNKNOWN);
        if (empty($cal)) {
            $cal = $page->navigation->add('Calendar');
        }

        if ($userismanager && $canmanageentries) {
            $cal->add(get_string('addevent', 'calendar'), $addurl);
        }

        if ($canlaledit) {
            $addlalurl = new moodle_url('/local/lunchandlearn/process.php', $url->params());
            $le = $cal->add(get_string('lunchandlearnadmin', 'local_lunchandlearn'));
            $le->add(get_string('newlunchlearn', 'local_lunchandlearn'), $addlalurl);
        }

        if ($hasmarkablecategories) {
            if (!isset($le)) {
                $le = $cal->add(get_string('lunchandlearnadmin', 'local_lunchandlearn'));
            }
            $le->add(get_string('lunchandlearnviewsessions', 'local_lunchandlearn'), new moodle_url('/local/lunchandlearn/index.php'));
            $le->add(get_string('settings', 'local_lunchandlearn'), new moodle_url('/admin/settings.php', array('section' => 'lunchandlearnglobalsettings')));
        }

        if ($sessions) {
            $mle = $cal->add('My Learning Events');
            foreach ($sessions as $session) {
                $lal = new lunchandlearn($session->id);
                $mle->add($lal->name, $lal->get_cal_url('full'));
            }
        }

        $cal->force_open();
    }
}

function lunchandlearn_add_admin_navigation(moodle_page $page, moodle_url $url) {
    global $USER;

    $caneditpage = $page->user_allowed_editing();
    $canmanagecalendar = false;
    $roles = get_archetype_roles('manager');
    foreach ($roles as $role) {
        if (user_has_role_assignment($USER->id, $role->id)) {
            $canmanagecalendar = has_capability('moodle/calendar:manageentries', $page->context);
            break;
        }
    }
    $caneditlunchandlearn = has_capability('local/lunchandlearn:edit', $page->context);

    if ($caneditpage || $canmanagecalendar || $caneditlunchandlearn) {
        $settingnode = $page->settingsnav->prepend(get_string('calendar', 'calendar'), null, navigation_node::TYPE_CONTAINER);
    }

    if ($caneditpage) {
        if ((optional_param('edit', -1, PARAM_BOOL) == 1) and confirm_sesskey()) {
            $USER->editing = 1;
        } else if ((optional_param('edit', -1, PARAM_BOOL) == 0) and confirm_sesskey()) {
            $USER->editing = 0;
        }

        $editname = $page->user_is_editing() ? get_string('turneditingoff') : get_string('turneditingon');
        $editval  = $page->user_is_editing() ? 'off' : 'on';
        $editonoff = $settingnode->add($editname, new moodle_url($url, array('edit' => $editval, 'sesskey' => sesskey())));
        $editonoff->force_open();
    }
    if ($canmanagecalendar) {
        $addurl = new moodle_url(CALENDAR_URL . 'event.php', $url->params());
        $settingnode->add(get_string('addevent', 'calendar'), $addurl);
    }
    if ($caneditlunchandlearn) {
        $addlalurl = new moodle_url('/local/lunchandlearn/process.php', $url->params());
        $settingnode->add(get_string('newlunchlearn', 'local_lunchandlearn'), $addlalurl);
    }
}

function lunchandlearn_resend_invites(lunchandlearn $lal) {
    global $DB;

    $attendees = $lal->attendeemanager->get_attendees();

    if (count($attendees) === 0) {
        return;
    }

    foreach ($attendees as $attendee) {
        $user = $DB->get_record('user', array('id' => $attendee->userid));
        lunchandlearn_manager::send_meeting_request($lal, $user);
    }
}

function local_lunchandlearn_pluginfile($course, $birecord_or_cm, $context, $filearea, $args, $forcedownload, array $options=array()) {
    $fs = get_file_storage();

    $filename = array_pop($args);
    $itemid = array_pop($args);

    if (!$file = $fs->get_file($context->id, 'local_lunchandlearn', $filearea, $itemid, '/', $filename) or $file->is_directory()) {
        send_file_not_found();
    }
    \core\session\manager::write_close();
    send_stored_file($file, 60*60, 0, $forcedownload, $options);
    exit;
}