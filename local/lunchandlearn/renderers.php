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

require_once("{$CFG->dirroot}/calendar/renderer.php");

class theme_arup_core_calendar_renderer extends core_calendar_renderer {
    /**
     * Creates a button to add a new event
     *
     * @param int $courseid
     * @param int $day
     * @param int $month
     * @param int $year
     * @return string
     */
    protected function add_event_button($courseid, $day = null, $month = null, $year = null, $time = null) {
        return ''; // Do not show any buttons.
    }

    /**
     * Adds a pretent calendar block
     *
     * @param block_contents $bc
     * @param mixed $pos BLOCK_POS_RIGHT | BLOCK_POS_LEFT
     */
    public function add_pretend_calendar_block(block_contents $bc, $pos = BLOCK_POS_LEFT) {
        $bc->attributes['id'] = 'instek';
        $this->page->blocks->add_fake_block($bc, BLOCK_POS_LEFT); // FORCED to left.
    }

    public function show_event(lunchandlearn $lal) {
        $content = html_writer::start_tag('article', array('class' => 'event-full'));
        $content .= html_writer::tag('header', html_writer::tag('h1', get_string('lunchandlearnevent', 'local_lunchandlearn') .': ' . $lal->get_name()));
        if ($lal->scheduler->is_cancelled()) {
            $content .= html_writer::div('This session has been cancelled', 'alert alert-danger');
            // maybe add a full delete or revert action?
        }
        else {
            $content .= $this->add_event_actions($lal);
        }
        $content .= html_writer::start_div('event-full-content');
        $content .= $this->add_summary($lal);
        $content .= $this->add_agenda($lal);
        if ($lal->scheduler->has_past()) {
            $content .= $this->add_recording($lal);
        } else {
            $content .= $this->add_joinmeeting($lal);
        }
        $content .= $this->add_related($lal);

        $content .= html_writer::end_div();
        $content .= html_writer::end_tag('article');

        // no status for cancelled, just return
        if ($lal->scheduler->is_cancelled()) {
            return $content;
        }

        if ($lal->scheduler->has_past()) {
            if ($lal->attendeemanager->did_attend()) {
                $attendstring = 'attended';
                $btnclass = 'btn-success';
            } else {
                $attendstring = 'notattended';
                $btnclass = 'btn-warning';
            }
            $content .= html_writer::start_div('btn-group');
            $content .= html_writer::tag(
                    'button',
                    get_string($attendstring, 'local_lunchandlearn'),
                    array(
                        'class' => 'btn '.$btnclass.' dropdown-toggle',
                        'data-toggle' => 'dropdown'));
            $content .= html_writer::end_div();
        }
        else {
            global $USER;
            if ($lal->attendeemanager->is_user_signedup($USER->id)) {
                $content .= $this->cancel_button($lal, 'full');
            } else if (false === $lal->attendeemanager->has_capacity()) {
                $content .= html_writer::span(
                                get_string('button:nocapacity', 'local_lunchandlearn'),
                                'lalsignup btn btn-warning btn-fullup');
            } else {
                $content .= html_writer::link(
                            new moodle_url('/local/lunchandlearn/signup.php', array('backto' => 'full','id' => $lal->get_id())),
                            get_string('signup', 'local_lunchandlearn'),
                            array('class' => 'lalsignup btn btn-primary btn-signup')
                     );
            }
        }

        return $content;
    }

    protected function cancel_button(lunchandlearn $lal, $backto) {
        $cancelurl  = new moodle_url('/local/lunchandlearn/signup.php', array(
                            'backto' => $backto,
                            'action' => 'cancel',
                            'id' => $lal->get_id()));
        $cancellink = html_writer::link($cancelurl, get_string('cancelsignup', 'local_lunchandlearn'));

        $button = html_writer::start_div('btn-group');
        $button .= html_writer::tag( 'button',
                        get_string('attending', 'local_lunchandlearn')
                        . html_writer::span('', 'caret'),
                        array(
                            'class' => 'btn btn-success dropdown-toggle',
                            'data-toggle' => 'dropdown')
                );
        $button .= html_writer::alist(array($cancellink), array('class' => 'dropdown-menu'));
        $button .= html_writer::end_div();
        return $button;
    }

    protected function add_event_actions(lunchandlearn $lal, $showinvite=true) {
        if ($lal->editable()) {
            $editlink = new moodle_url('/local/lunchandlearn/process.php', array('action' => 'edit', 'id' => $lal->get_id()));
            $deletelink = new moodle_url('/local/lunchandlearn/delete.php', array('id' => $lal->get_id()));
        }
        if ($lal->markable()) {
            $attendeeslink = new moodle_url('/local/lunchandlearn/attendees.php', array('id' => $lal->get_id()));
        }
        $invitelink = new moodle_url('mailto:', array(
                            'subject' => get_string('invitesubject', 'local_lunchandlearn', $lal->get_name()),
                            'body'    => get_string('invitesignoff', 'local_lunchandlearn', (string)$lal->get_cal_url('full')->out(false))));
        $content =  html_writer::start_div('eventfullactions');
        if (isset($editlink)) {
            $content .= html_writer::start_tag('a', array('href' => $editlink));
            $content .= html_writer::empty_tag('img', array('src' => $this->output->pix_url('t/edit'), 'alt' => get_string('tt_editevent', 'calendar'), 'title' => get_string('tt_editevent', 'calendar')));
            $content .= html_writer::end_tag('a');
        }
        if (isset($deletelink)) {
            $content .= html_writer::start_tag('a', array('href' => $deletelink));
            $content .= html_writer::empty_tag('img', array('src' => $this->output->pix_url('t/delete'), 'alt' => get_string('tt_deleteevent', 'calendar'), 'title' => get_string('tt_deleteevent', 'calendar')));
            $content .= html_writer::end_tag('a');
        }
        if (isset($attendeeslink)) {
            $content .= html_writer::tag('a',
                        html_writer::empty_tag('img', array(
                            'src'  => $this->output->pix_url('t/groups'),
                            'alt'  => get_string('attendees', 'local_lunchandlearn'),
                            'title' => get_string('attendees', 'local_lunchandlearn')
                        )),
                        array(
                            'href' => $attendeeslink
                        )
                );
        }
        if ($showinvite) {
            $content .= html_writer::start_tag('a', array('href' => $invitelink));
            $content .= html_writer::empty_tag('img', array('width' => '12', 'src' => $this->output->pix_url('t/email'), 'alt' => get_string('invite', 'local_lunchandlearn'), 'title' => get_string('invite', 'local_lunchandlearn')));
            $content .= html_writer::end_tag('a');
        }
        $content .= html_writer::end_div();


        return $content;
    }

    protected function add_attendance(lunchandlearn $lal, $detailed=false) {
        $cell = html_writer::start_span('attendance');
        if (true == $lal->attendeemanager->availableinperson) {
            $cell .= $lal->get_fa_icon(lunchandlearn::ICON_INPERSON, get_string('popover:inperson', 'local_lunchandlearn'), get_string('popover:inpersondata', 'local_lunchandlearn'));
            if ($detailed) {
                $cell .= $lal->attendeemanager->get_inperson_attendance_string();
            } else {
                $cell .= $lal->attendeemanager->get_remaining_inperson(20);
            }
        }
        if (true == $lal->attendeemanager->availableonline) {
            if ($detailed) {
                $cell .= '<br />';
            }
            $cell .= $lal->get_fa_icon(lunchandlearn::ICON_ONLINE, get_string('popover:online', 'local_lunchandlearn'), get_string('popover:onlinedata', 'local_lunchandlearn'));
            if ($detailed) {
                $cell .= $lal->attendeemanager->get_online_attendance_string();
            } else {
                $cell .= $lal->attendeemanager->get_remaining_online(20);
            }
        }
        return $cell . html_writer::end_span();
    }

    protected function add_summary(lunchandlearn $lal) {
        $content = '';
        $summary = $lal->get_summary();
        if (!empty($summary)) {
            // summary
            $content .= html_writer::start_tag('section', array('class' => 'collapse in clearfix'));

            $content .= html_writer::tag('h1', get_string('heading:summary', 'local_lunchandlearn'), array('data-toggle' => "collapse", 'data-target' => '#summarysection'));
            $content .= html_writer::start_div('section-content', array('id' => 'summarysection'));
            $content .= html_writer::div($summary);
        }
        $duration = '';
        if ($lal->scheduler->duration > 0) {
            $duration = ' for ' . $lal->scheduler->duration . ' minutes';
        }
        $dayurl = $lal->get_cal_url();
        $content .= html_writer::div(html_writer::label(get_string('date', 'local_lunchandlearn'), '') . ': '
                  . html_writer::span(html_writer::link($dayurl, $lal->scheduler->get_date_string()) . $duration));
        $content .= html_writer::div(html_writer::label(get_string('eventregion', 'local_lunchandlearn'), '') . ': '
                  . html_writer::span($lal->scheduler->region_name));
        $content .= html_writer::div(html_writer::label(get_string('office', 'local_lunchandlearn'), '') . ': '
                  . html_writer::span($lal->scheduler->get_office()));
        $content .= html_writer::div(html_writer::label(get_string('meetingroom', 'local_lunchandlearn'), '') . ': '
                  . html_writer::span($lal->scheduler->get_room()));
        $content .= html_writer::div(html_writer::label(get_string('label:attending', 'local_lunchandlearn'), '')
                  . html_writer::div($this->add_attendance($lal, true)), 'attending-row');

        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('section');

        return $content;
    }

    protected function add_agenda(lunchandlearn $lal) {
        $agenda = $lal->get_description();
        if (!empty($agenda)) {
            // agenda
            return html_writer::tag('section',
                            html_writer::tag('h1', get_string('heading:agenda', 'local_lunchandlearn'), array('data-toggle' => "collapse", 'data-target' => '#agendasection'))
                          . html_writer::div($agenda, 'section-content collapse in clearfix', array('id' => 'agendasection')));
        }
        return '';
    }

    protected function add_joinmeeting(lunchandlearn $lal) {
        global $USER;
        $join = $lal->get_joindetail();
        if ($lal->attendeemanager->availableonline && !empty($join)) {
            if (false === $lal->attendeemanager->is_user_signedup($USER->id)) {
                // hide joining instructions until signed up
                $join = get_string('signuptogetinstructions', 'local_lunchandlearn');
            }
            // agenda
            return html_writer::tag('section',
                            html_writer::tag('h1', get_string('heading:joindetail', 'local_lunchandlearn'), array('data-toggle' => "collapse", 'data-target' => '#joindetailsection', 'class' => 'collapsed'))
                            . html_writer::div($join, 'section-content collapse', array('id' => 'joindetailsection')));
        }
        return '';
    }

    protected function add_recording(lunchandlearn $lal) {
        $recording = $lal->get_recording();
        if (!empty($recording)) {
            // agenda
            return html_writer::tag('section',
                            html_writer::tag('h1', get_string('heading:recording', 'local_lunchandlearn'), array('data-toggle' => "collapse", 'data-target' => '#recordingsection', 'class' => 'collapsed'))
                            . html_writer::div($recording, 'section-content collapse', array('id' => 'recordingsection')));
        }
        return '';
    }

    protected function add_related(lunchandlearn $lal) {
        // Add related reading
        $fs = get_file_storage();
        $files = $fs->get_area_files(context_system::instance()->id, 'local_lunchandlearn', 'attachment', $lal->id);
        if (empty($files)) {
            return '';
        }
        $content =  html_writer::start_tag('section');
        $content .= html_writer::tag('h1', get_string('heading:related', 'local_lunchandlearn'), array('data-toggle' => "collapse", 'data-target' => '#relatedsection', 'class' => 'collapsed'));

        $content .= html_writer::start_div('collapse', array('id' => 'relatedsection'));

        foreach ($files as $f) {
            if ($f->is_directory()) {
                continue;
            }
            $furl = new moodle_url('/local/lunchandlearn/pluginfile.php/attachment/'
                    . $f->get_itemid() . '/'
                    . $f->get_filepath() . '/'
                    . $f->get_filename());

            $content .= html_writer::div(html_writer::link(
                             new moodle_url(
                                $furl->out()
                             ),
                                html_writer::empty_tag('img', array(
                                    'src' => $furl->out(false,
                                            array(
                                                'preview' => 'thumb',
                                                'oid' => $f->get_timemodified()
                                            )))),
                                array(
                                    'target' => '_blank'
                                )
                            )
                            . html_writer::empty_tag('br')
                            . html_writer::span($f->get_filename())
                       , 'relateditem');

        }
        $content .= html_writer::end_tag('div');
        $content .= html_writer::end_tag('section');
        return $content;
    }

     /**
     * Displays upcoming events
     *
     * @param calendar_information $calendar
     * @param int $futuredays
     * @param int $maxevents
     * @return string
     */
    public function show_upcoming_events(calendar_information $calendar, $futuredays, $maxevents, moodle_url $returnurl = null) {

        if ($returnurl === null) {
            $returnurl = $this->page->url;
        }

        $events = calendar_get_upcoming($calendar->courses, $calendar->groups, $calendar->users, $futuredays, $maxevents);

        $output  = html_writer::start_tag('div', array('class' => 'header'));
        $output .= html_writer::end_tag('div');

        $output .= html_writer::tag('h2', get_string('upcomingevents', 'calendar'), array('class' => 'main'));
        $output .= $this->add_region_filtering();

        if ($events) {
            $this->output->heading(get_string('upcomingevents', 'calendar'));
            $output .= html_writer::start_tag('div', array('class' => 'eventlist'));
            foreach ($events as $event) {
                // Convert to calendar_event object so that we transform description
                // accordingly
                $event = new calendar_event($event);
                $event->calendarcourseid = $calendar->courseid;
                $output .= $this->event($event);
            }
            $output .= html_writer::end_tag('div');
        } else {
            $output .= $this->output->heading(get_string('noupcomingevents', 'calendar'));
        }

        return $output;
    }

    protected function add_region_filtering() {
        global $PAGE, $DB;

        $defaultregion = lunchandlearn_get_region();

        $regions = $DB->get_records_menu('local_regions_reg', array('userselectable' => 1));
        $regions[-1] = 'All Regions';
        $regions[0] = 'Global';
        $ss = new single_select(
                $PAGE->url,
                'regionid',
                $regions,
                $defaultregion
        );
        $ss->set_label(get_string('selectregion', 'local_lunchandlearn'));
        $ss->class = 'regionfilter';
        echo $this->render($ss);
    }

    /**
     * Displays the calendar for a single day
     *
     * @param calendar_information $calendar
     * @return string
     */
    public function show_day(calendar_information $calendar, moodle_url $returnurl = null) {

        if ($returnurl === null) {
            $returnurl = $this->page->url;
        }

        $events = calendar_get_upcoming($calendar->courses, $calendar->groups, $calendar->users, 1, 100, $calendar->timestamp_today());

        $output  = html_writer::start_tag('div', array('class' => 'header'));

        $output .= $this->add_region_filtering();
        $output .= html_writer::end_tag('div');
        // Controls
        $output .= html_writer::tag('div', calendar_top_controls('day', array('id' => $calendar->courseid, 'time' => $calendar->time)), array('class' => 'controls'));

        if (empty($events)) {
            // There is nothing to display today.
            $output .= html_writer::span(get_string('daywithnoevents', 'calendar'), 'calendar-information calendar-no-results');
        } else {
            $output .= html_writer::start_tag('div', array('class' => 'eventlist'));
            $underway = array();
            // First, print details about events that start today
            foreach ($events as $event) {
                $event = new calendar_event($event);
                $event->calendarcourseid = $calendar->courseid;
                if ($event->timestart >= $calendar->timestamp_today() && $event->timestart <= $calendar->timestamp_tomorrow()-1) {  // Print it now
                    $event->time = calendar_format_event_time($event, time(), null, false, $calendar->timestamp_today());
                    $output .= $this->event($event);
                } else {                                                                 // Save this for later
                    $underway[] = $event;
                }
            }

            // Then, show a list of all events that just span this day
            if (!empty($underway)) {
                $output .= html_writer::span(get_string('spanningevents', 'calendar'),
                    'calendar-information calendar-span-multiple-days');
                foreach ($underway as $event) {
                    $event->time = calendar_format_event_time($event, time(), null, false, $calendar->timestamp_today());
                    $output .= $this->event($event);
                }
            }

            $output .= html_writer::end_tag('div');
        }

        return $output;
    }

    public function fake_block_threemonths(calendar_information $calendar) {
        global $CFG;
        require_once($CFG->dirroot . '/local/lunchandlearn/lib.php');

        // Get the calendar type we are using.
        $calendartype = \core_calendar\type_factory::get_calendar_instance();

        $date = $calendartype->timestamp_to_date_array($calendar->time);

        $nextmonth = calendar_add_month($date['mon'], $date['year']);
        $nextmonthtime = $calendartype->convert_to_gregorian($nextmonth[1], $nextmonth[0], 1);
        $nextmonthtime = make_timestamp($nextmonthtime['year'], $nextmonthtime['month'], $nextmonthtime['day'],
            $nextmonthtime['hour'], $nextmonthtime['minute']);

        $content  = html_writer::start_tag('div', array('class' => 'minicalendarblock'));
        $content .= calendar_get_mini($calendar->courses, $calendar->groups, $calendar->users, false, false, 'display', $calendar->courseid, $calendar->time);
        $content .= html_writer::end_tag('div');
        $content .= html_writer::start_tag('div', array('class' => 'minicalendarblock'));
        $content .= calendar_get_mini($calendar->courses, $calendar->groups, $calendar->users, false, false, 'display', $calendar->courseid, $nextmonthtime);
        $content .= html_writer::end_tag('div');
        $content .= html_writer::empty_tag('hr');
        $content .= html_writer::div($this->navigation_node(lunchandlearn_add_event_key(), array('class' => 'block_tree list')), 'block_navigation  block');

        return $content;
    }


    protected function navigation_node(navigation_node $node, $attrs=array()) {
        $items = $node->children;

        // exit if empty, we don't want an empty ul element
        if ($items->count() == 0) {
            return '';
        }

        // array of nested li elements
        $lis = array();
        foreach ($items as $item) {
            if (!$item->display) {
                continue;
            }

            $isbranch = ($item->children->count()>0  || $item->nodetype == navigation_node::NODETYPE_BRANCH);
            $hasicon = (!$isbranch && $item->icon instanceof renderable);

            if ($isbranch) {
                $item->hideicon = true;
            }
            $content = $this->output->render($item);

            // this applies to the li item which contains all child lists too
            $liclasses = array($item->get_css_type());
            $liexpandable = array();
            if (!$item->forceopen || (!$item->forceopen && $item->collapse) || ($item->children->count() == 0  && $item->nodetype == navigation_node::NODETYPE_BRANCH)) {
                $liclasses[] = 'collapsed';
            }
            if ($isbranch) {
                $liclasses[] = 'contains_branch';
                $liexpandable = array('aria-expanded' => in_array('collapsed', $liclasses) ? "false" : "true");
            } else if ($hasicon) {
                $liclasses[] = 'item_with_icon';
            }
            if ($item->isactive === true) {
                $liclasses[] = 'current_branch';
            }
            $liattr = array('class' => join(' ',$liclasses)) + $liexpandable;
            // class attribute on the div item which only contains the item content
            $divclasses = array('tree_item');
            if ($isbranch) {
                $divclasses[] = 'branch';
            } else {
                $divclasses[] = 'leaf';
            }
            if (!empty($item->classes) && count($item->classes)>0) {
                $divclasses[] = join(' ', $item->classes);
            }
            $divattr = array('class' => join(' ', $divclasses));
            if (!empty($item->id)) {
                $divattr['id'] = $item->id;
            }
            $content = html_writer::tag('p', $content, $divattr) . $this->navigation_node($item);
            if (!empty($item->preceedwithhr) && $item->preceedwithhr===true) {
                $content = html_writer::empty_tag('hr') . $content;
            }
            $content = html_writer::tag('li', $content, $liattr);
            $lis[] = $content;
        }

        if (count($lis)) {
            return html_writer::tag('ul', implode("\n", $lis), $attrs);
        } else {
            return '';
        }
    }


    public function show_month_detailed(calendar_information $calendar, moodle_url $returnurl  = null) {
        global $CFG;

        if (empty($returnurl)) {
            $returnurl = $this->page->url;
        }

        // Get the calendar type we are using.
        $calendartype = \core_calendar\type_factory::get_calendar_instance();

        // Store the display settings.
        $display = new stdClass;
        $display->thismonth = false;

        // Get the specified date in the calendar type being used.
        $date = $calendartype->timestamp_to_date_array($calendar->time);
        $thisdate = $calendartype->timestamp_to_date_array(time());
        if ($date['mon'] == $thisdate['mon'] && $date['year'] == $thisdate['year']) {
            $display->thismonth = true;
            $date = $thisdate;
            $calendar->time = time();
        }

        // Get Gregorian date for the start of the month.
        $gregoriandate = $calendartype->convert_to_gregorian($date['year'], $date['mon'], 1);
        // Store the gregorian date values to be used later.
        list($gy, $gm, $gd, $gh, $gmin) = array($gregoriandate['year'], $gregoriandate['month'], $gregoriandate['day'],
            $gregoriandate['hour'], $gregoriandate['minute']);

        // Get the starting week day for this month.
        $startwday = dayofweek(1, $date['mon'], $date['year']);
        // Get the days in a week.
        $daynames = calendar_get_days();
        // Store the number of days in a week.
        $numberofdaysinweek = $calendartype->get_num_weekdays();

        $display->minwday = calendar_get_starting_weekday();
        $display->maxwday = $display->minwday + ($numberofdaysinweek - 1);
        $display->maxdays = calendar_days_in_month($date['mon'], $date['year']);

        // These are used for DB queries, so we want unixtime, so we need to use Gregorian dates.
        $display->tstart = make_timestamp($gy, $gm, $gd, $gh, $gmin, 0);
        $display->tend = $display->tstart + ($display->maxdays * DAYSECS) - 1;

        // give room each side for timezones... events by day will filter these out properly
        $display->tstart -= HOURSECS*13;
        $display->tend += HOURSECS*13;

        // Align the starting weekday to fall in our display range
        // This is simple, not foolproof.
        if ($startwday < $display->minwday) {
            $startwday += $numberofdaysinweek;
        }

        // Get events from database
        $events = calendar_get_events($display->tstart, $display->tend, $calendar->users, $calendar->groups, $calendar->courses);
        if (!empty($events)) {
            foreach($events as $eventid => $event) {
                $event = new calendar_event($event);
                if (!empty($event->modulename)) {
                    $cm = get_coursemodule_from_instance($event->modulename, $event->instance);
                    if (!\core_availability\info_module::is_user_visible($cm, 0, false)) {
                        unset($events[$eventid]);
                    }
                }
            }
        }

        // Extract information: events vs. time
        calendar_events_by_day($events, $date['mon'], $date['year'], $eventsbyday, $durationbyday, $typesbyday, $calendar->courses);

        $output  = html_writer::start_tag('div', array('class'=>'header'));
        $output .= $this->add_region_filtering();
        $output .= html_writer::end_tag('div', array('class'=>'header'));
        // Controls
        $output .= html_writer::tag('div', calendar_top_controls('month', array('id' => $calendar->courseid, 'time' => $calendar->time)), array('class' => 'controls'));

        $table = new html_table();
        $table->attributes = array('class'=>'calendarmonth calendartable');
        $table->summary = get_string('calendarheading', 'calendar', userdate($calendar->time, get_string('strftimemonthyear')));
        $table->data = array();

        // Get the day names as the header.
        $header = array();
        for($i = $display->minwday; $i <= $display->maxwday; ++$i) {
            $header[] = $daynames[$i % $numberofdaysinweek]['shortname'];
        }
        $table->head = $header;

        // For the table display. $week is the row; $dayweek is the column.
        $week = 1;
        $dayweek = $startwday;

        $row = new html_table_row(array());

        // Paddding (the first week may have blank days in the beginning)
        for($i = $display->minwday; $i < $startwday; ++$i) {
            $cell = new html_table_cell('&nbsp;');
            $cell->attributes = array('class'=>'nottoday dayblank');
            $row->cells[] = $cell;
        }

        // Now display all the calendar
        $weekend = CALENDAR_DEFAULT_WEEKEND;
        if (isset($CFG->calendar_weekend)) {
            $weekend = intval($CFG->calendar_weekend);
        }

        $daytime = strtotime('-1 day', $display->tstart);
        for ($day = 1; $day <= $display->maxdays; ++$day, ++$dayweek) {
            $daytime = strtotime('+1 day', $daytime);
            if($dayweek > $display->maxwday) {
                // We need to change week (table row)
                $table->data[] = $row;
                $row = new html_table_row(array());
                $dayweek = $display->minwday;
                ++$week;
            }

            // Reset vars
            $cell = new html_table_cell();
            $dayhref = calendar_get_link_href(new moodle_url(CALENDAR_URL.'view.php', array('view' => 'day', 'course' => $calendar->courseid)), 0, 0, 0, $daytime);

            $cellclasses = array();

            if ($weekend & (1 << ($dayweek % $numberofdaysinweek))) {
                // Weekend. This is true no matter what the exact range is.
                $cellclasses[] = 'weekend';
            }

            // Special visual fx if an event is defined
            if (isset($eventsbyday[$day])) {
                if(count($eventsbyday[$day]) == 1) {
                    $title = get_string('oneevent', 'calendar');
                } else {
                    $title = get_string('manyevents', 'calendar', count($eventsbyday[$day]));
                }
                $cell->text = html_writer::tag('div', html_writer::link($dayhref, $day, array('title'=>$title)), array('class'=>'day'));
            } else {
                $cell->text = html_writer::tag('div', $day, array('class'=>'day'));
            }

            // Special visual fx if an event spans many days
            $durationclass = false;
            if (isset($typesbyday[$day]['durationglobal'])) {
                $durationclass = 'duration_global';
            } else if (isset($typesbyday[$day]['durationcourse'])) {
                $durationclass = 'duration_course';
            } else if (isset($typesbyday[$day]['durationgroup'])) {
                $durationclass = 'duration_group';
            } else if (isset($typesbyday[$day]['durationuser'])) {
                $durationclass = 'duration_user';
            }
            if ($durationclass) {
                $cellclasses[] = 'duration';
                $cellclasses[] = $durationclass;
            }

            // Special visual fx for today
            if ($display->thismonth && $day == $date['mday']) {
                $cellclasses[] = 'day today';
            } else {
                $cellclasses[] = 'day nottoday';
            }
            $cell->attributes = array('class'=>join(' ',$cellclasses));

            if (isset($eventsbyday[$day])) {
                $cell->text .= html_writer::start_tag('ul', array('class'=>'events-new'));
                foreach($eventsbyday[$day] as $eventindex) {
                    // Rewrite the dayurl to be full event for lal, or else add anchor.
                    $lal = lunchandlearn::get_instance_by_event($events[$eventindex]);
                    // If event has a class set then add it to the event <li> tag
                    $attributes = array();
                    if (!empty($events[$eventindex]->class)) {
                        $attributes['class'] = $events[$eventindex]->class;
                    }
                    if (!empty($lal)) {
                        $dayhref = new moodle_url(CALENDAR_URL . 'view.php', array(
                            'view' => 'event',
                            'id'   => $lal->get_id()
                        ));
                        if ($lal->scheduler->is_cancelled()) {
                            $attributes['class'] .= ' cancelled';
                        }
                    } else {
                        $dayhref->set_anchor('event_'.$events[$eventindex]->id);
                    }
                    $link = html_writer::link($dayhref, format_string($events[$eventindex]->name, true));
                    $cell->text .= html_writer::tag('li', $link, $attributes);
                }
                $cell->text .= html_writer::end_tag('ul');
            }
            if (isset($durationbyday[$day])) {
                $cell->text .= html_writer::start_tag('ul', array('class'=>'events-underway'));
                foreach ($durationbyday[$day] as $eventindex) {
                    $lal = lunchandlearn::get_instance_by_event($events[$eventindex]);
                    $extra = (empty($lal) || false === $lal->scheduler->is_cancelled) ? '' : ' cancelled';
                    $cell->text .= html_writer::tag('li', '['.format_string($events[$eventindex]->name,true).']', array('class' => 'events-underway'.$extra));
                }
                $cell->text .= html_writer::end_tag('ul');
            }
            $row->cells[] = $cell;
        }

        // Paddding (the last week may have blank days at the end)
        for($i = $dayweek; $i <= $display->maxwday; ++$i) {
            $cell = new html_table_cell('&nbsp;');
            $cell->attributes = array('class'=>'nottoday dayblank');
            $row->cells[] = $cell;
        }
        $table->data[] = $row;
        $output .= html_writer::table($table);

        return $output;
    }

    public function event_summary(calendar_event $event) {
        $lal = lunchandlearn::get_instance_by_event($event);

        $content = html_writer::start_div('event_summary');

        $content .= html_writer::div(html_writer::tag('label', get_string('eventname', 'local_lunchandlearn') . ':')
                . $lal->get_name());
        $content .= html_writer::div(html_writer::tag('label', get_string('date') . ':')
                . $lal->scheduler->get_date_string('l jS F Y,', ' g:iA T', true));

        $content .= html_writer::div(html_writer::tag('label', get_string('eventsummary', 'local_lunchandlearn') . ':')
                . $lal->get_summary());

        $content .= html_writer::div(html_writer::tag('label', get_string('attending', 'local_lunchandlearn') . ':')
                . $lal->attendeemanager->get_attendee_count());

        return $content . html_writer::end_div();
    }

    /**
     * Displays an event
     *
     * @param calendar_event $event
     * @param bool $showactions
     * @return string
     */
    public function event(calendar_event $event, $showactions=true) {
        global $CFG, $USER, $PAGE;

        require_once($CFG->dirroot . '/local/lunchandlearn/lib.php');

        // Check here for lunchandlearns that match event (eventid in lunch and learn)
        $lal = lunchandlearn::get_instance_by_event($event);

        // Not a lunch and learn event - use parent renderer.
        if (empty($lal)) {
            return parent::event($event, $showactions);
        }

        $PAGE->requires->js('/local/lunchandlearn/js/lal.js', false);

        $cancelledclass = '';
        if ($lal->scheduler->is_cancelled()) {
            $cancelledclass = ' cancelled';
        }

        $event = calendar_add_event_metadata($event);
        $context = $event->context;
        $output = '';

        if (!empty($event->icon)) {
            $output .= $event->icon;
        } else {
            $output .= $this->output->spacer(array('height' => 16, 'width' => 16));
        }

        if (!empty($event->referer)) {
            $output .= html_writer::tag('div', $event->referer, array('class' => 'referer'));
        } else {
            $lalurl = new moodle_url(CALENDAR_URL . 'view.php', array(
                            'view' => 'event', 'id' => $lal->get_id()));
            $output.= html_writer::link(
                    $lalurl,
                    format_string($event->name, false, array('context' => $context), array('class' => 'name'))
                    );
            // Add in location.
            if ($lal->scheduler->get_office() != '') {
                $output .= html_writer::span($lal->scheduler->get_office(), 'location');
            }
        }
        if (!empty($event->courselink)) {
            $output .= html_writer::tag('div', $event->courselink, array('class' => 'course'));
        }
        // Show subscription source if needed.
        if (!empty($event->subscription) && $CFG->calendar_showicalsource) {
            if (!empty($event->subscription->url)) {
                $source = html_writer::link($event->subscription->url, get_string('subsource', 'calendar', $event->subscription));
            } else {
                // File based ical.
                $source = get_string('subsource', 'calendar', $event->subscription);
            }
            $output .= html_writer::tag('div', $source, array('class' => 'subscription'));
        }
        if (!empty($event->time)) {
            $output .= html_writer::tag('span', $event->time, array('class' => 'date'));
        } else {
            $output .= html_writer::tag('span', calendar_time_representation($event->timestart), array('class' => 'date'));
        }
        if ($lal->scheduler->is_cancelled()) {
            $output .= html_writer::div('This session has been cancelled', 'alert alert-danger');
            // Maybe add a full delete or revert action?
        }

        $tabs = '';
        $tabbedcontent = '';

        $lalrecording = $lal->get_recording();
        $lalsummary = $lal->get_summary();
        $laldesc = $lal->get_description();
        $laljoindetail = $lal->get_joindetail();
        $lis = array();

        if (!empty($lalsummary)) {
            $lis[] = html_writer::link(
                        new moodle_url('#summary'),
                        get_string('tab:summary', 'local_lunchandlearn'));
            $tabbedcontent .= html_writer::div($lalsummary, 'tab tab-summary tab-active');
        }
        if (!empty($laldesc)) {
            $lis[] = html_writer::link(
                        new moodle_url('#description'),
                        get_string('tab:description', 'local_lunchandlearn'));
            $tabbedcontent .= html_writer::div($laldesc, 'tab tab-description');
        }

        if ($lal->attendeemanager->availableonline && !empty($laljoindetail)) {
            $lis[] = html_writer::link(
                        new moodle_url('#joindetail'),
                        get_string('tab:joindetail', 'local_lunchandlearn'));

            if (false === $lal->attendeemanager->is_user_signedup($USER->id)) {
                // Hide joining instructions until signed up.
                $laljoindetail = get_string('signuptogetinstructions', 'local_lunchandlearn');
            }
            $tabbedcontent .= html_writer::div($laljoindetail, 'tab tab-joindetail');
        }
        if ($lal->scheduler->has_past() && !empty($lalrecording)) {
            $lis[] = html_writer::link(
                        new moodle_url('#recording'),
                        get_string('tab:recording', 'local_lunchandlearn'));
            $tabbedcontent .= html_writer::div($lalrecording, 'tab tab-recording');
        }
        // Add related reading.
        $fs = get_file_storage();
        $files = $fs->get_area_files(context_system::instance()->id, 'local_lunchandlearn', 'attachment', $lal->id);
        if (!empty($files)) {
            $lis[] = html_writer::link(
                        new moodle_url('#related'),
                        get_string('tab:related', 'local_lunchandlearn'));
            $lalrelated = '';
            foreach ($files as $f) {
                if ($f->is_directory()) {
                    continue;
                }
                $furl = new moodle_url('/local/lunchandlearn/pluginfile.php/attachment/'
                        . $f->get_itemid() . '/'
                        . $f->get_filepath() . '/'
                        . $f->get_filename()
                );

                $lalrelated .=
                    html_writer::div(
                        html_writer::link(
                            new moodle_url(
                                $furl->out()
                            ),
                            html_writer::empty_tag('img', array(
                                'src' => $furl->out(
                                        false,
                                        array(
                                            'preview' => 'thumb',
                                            'oid' => $f->get_timemodified()
                                        ))
                                )
                            ),
                            array(
                                'target' => '_blank'
                            )
                        )
                        . html_writer::empty_tag('br')
                        . html_writer::span($f->get_filename())
                    );

            }
            $tabbedcontent .= html_writer::div($lalrelated, 'tab tab-related');
        }
        if (count($lis) > 0) {
            $tabs = html_writer::alist($lis, array('class' => 'tabs'));
        }

        $eventdetailshtml = $tabs . html_writer::start_div('tabbedcontent') . format_text($tabbedcontent, FORMAT_MOODLE, array('context' => $context));

        // Lunch and learn bottom bar
        $button = '';
        if (false === $lal->scheduler->is_cancelled()) {
            if ($lal->scheduler->has_past()) {
                if ($lal->did_attend()) {
                    $attendstring = 'attended';
                    $btnclass = 'btn-success';
                } else {
                    $attendstring = 'notattended';
                    $btnclass = 'btn-warning';
                }
                $button = html_writer::start_div('btn-group');
                $button .= html_writer::tag(
                    'button',
                    get_string($attendstring, 'local_lunchandlearn'),
                    array(
                        'class' => 'btn '.$btnclass.' dropdown-toggle',
                        'data-toggle' => 'dropdown'));
                $button .= html_writer::end_div();
            } else {
                if ($lal->attendeemanager->is_user_signedup($USER->id)) {
                    $button = $this->cancel_button($lal, '');
                } else if (false === $lal->attendeemanager->has_capacity()) {
                    $button = html_writer::span(
                            get_string('button:nocapacity', 'local_lunchandlearn'),
                            'btn btn-warning btn-fullup'
                     );
                } else {
                    $button = html_writer::link(
                            new moodle_url('/local/lunchandlearn/signup.php', array('id' => $lal->get_id())),
                            get_string('signup', 'local_lunchandlearn'),
                            array('class' => 'lalsignup btn btn-primary btn-signup')
                     );
                }
            }
        }

        $eventdetailshtml .= html_writer::div( ($lal->scheduler->is_cancelled() ? '' : get_string('label:availableplaces', 'local_lunchandlearn') . $this->add_attendance($lal)) . $button, 'bottom-bar');
        $eventdetailshtml .= html_writer::end_div();

        $eventdetailsclasses = 'description';
        if (isset($event->cssclass)) {
            $eventdetailsclasses .= ' '.$event->cssclass;
        }

        $commands  = html_writer::start_tag('div', array('class'=>'commands'));
        $commands .= $this->add_event_actions($lal, false);
        $commands .= html_writer::end_tag('div');

        $output .= html_writer::tag('div', $eventdetailshtml.$commands, array('class' => $eventdetailsclasses));

        return html_writer::tag('div', $output , array('class' => "event {$event->cssclass}", 'id' => 'event_' . $event->id));
    }
}