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
 * Description of lunchandlearn
 *
 * @author paulstanyer
 */
class lunchandlearn extends lal_base {
    const TABLE = 'local_lunchandlearn';

    const ICON_INPERSON = 'user';
    const ICON_ONLINE = 'headphones';

    protected $id = 0;

    protected $eventid;
    public $event = null;
    protected $categoryid;
    protected $category;
    protected $name;
    protected $description = '';
    protected $sessioninfo = '';
    protected $supplier;
    protected $joindetail = '';
    protected $summary;
    protected $recording;

    protected $locked = 0;

    protected $scheduler;
    protected $attendeemanager;

    public function __construct($id = 0, $event=null) {
        // Set default date -> today.
        $d = new DateTime();
        $this->date = $d->getTimestamp();
        $this->event = $event;
        if (!empty($event)) {
            $this->set_eventid($this->event->id);
        }
        if (!empty($id)) {
            $this->set_id($id);
            $this->load_by_id($id);
        } else {
            $this->scheduler = new lunchandlearn_schedule($this);
            $this->attendeemanager = new lunchandlearn_attendance_manager($this);
        }
    }

    public function get_fa_icon($name, $tooltip='', $tooltipdata='') {
        $extra = empty($tooltip) ? '' : ' rel="popover" title="'.$tooltip.'" data-content="'.$tooltipdata.'" ';
        return "<i $extra class=\"fa fa-fw fa-$name\"></i>";
    }

    protected function load_by_id($id) {
        global $DB;
        $me = $DB->get_record(self::TABLE, array('id' => $id));
        if (empty($me)) {
            throw new Exception(get_string('notfound', 'local_lunchandlearn'));
        }
        // Load the event data, unless it was passed into constructor.
        if (empty($this->event)) {
            $this->set_eventid($me->eventid);
            $this->event = $DB->get_record('event', array('id' => $me->eventid));
        }
        $this->set_name($this->event->name);
        $this->load_record($me);

        $this->scheduler = new lunchandlearn_schedule($this, $me);
        $this->attendeemanager = new lunchandlearn_attendance_manager($this, $me);
    }

    public function get_supplier() {
        if (empty($this->supplier)) {
            $this->supplier = 'Moodle'; // Never return a blank supplier.
        }
        return $this->supplier;
    }

    public static function get_instance_by_event($event) {
        global $DB;
        $result = $DB->get_record('local_lunchandlearn', array('eventid' => $event->id));
        if (!empty($result)) {
            return new lunchandlearn($result->id, $event);
        }
    }

    public function set_summary($summary) {
        if (is_array($summary)) {
            if (!empty($summary['text'])) {
                $this->summary = $summary['text'];
            }
        } else {
            $this->summary = $summary;
        }
        $this->summary = strip_tags($this->summary);
    }

    public function get_event() {
        return calendar_event::load($this->eventid);
    }

    public function set_description($description) {
        if (is_array($description)) {
            if (!empty($description['text'])) {
                $this->description = $description['text'];
            }
        } else {
            $this->description = $description;
        }
    }

    public function set_joindetail($details) {
        if (is_array($details)) {
            if (!empty($details['text'])) {
                $this->joindetail = $details['text'];
            }
        } else {
            $this->joindetail = $details;
        }
    }

    public function set_sessioninfo($info) {
        if (is_array($info)) {
            if (!empty($info['text'])) {
                $this->sessioninfo = $info['text'];
            }
        } else {
            $this->sessioninfo = $info;
        }
    }

    public function bind($data) {
        if (!empty($data)) {
            $this->set_categoryid($data->categoryid);
            $this->set_name($data->name);
            $this->set_description($data->description);
            $this->set_summary($data->summary);
            $this->set_recording($data->recorded_editor);
            $this->set_supplier($data->supplier);
            $this->set_joindetail($data->joindetail);
            $this->set_sessioninfo($data->sessioninfo);

            $this->scheduler->set_date($data->timestart, $data->timestart_timezone);
            $this->scheduler->set_duration($data->timedurationminutes * MINSECS);
            $this->scheduler->regionid = $data->regionid;
            $this->scheduler->set_office($data->office);
            $this->scheduler->set_room($data->room);

            $this->attendeemanager->availableinperson = $data->availableinperson;
            $this->attendeemanager->capacity = isset($data->capacity) ? $data->capacity : 0;
            $this->attendeemanager->overbookinperson = isset($data->overbookinperson) ? $data->overbookinperson : 0;
            $this->attendeemanager->availableonline = $data->availableonline;
            $this->attendeemanager->onlinecapacity = isset($data->onlinecapacity) ? $data->onlinecapacity : 0;
            $this->attendeemanager->overbookonline = isset($data->overbookonline) ? $data->overbookonline : 0;
        }
    }

    public function get_status() {
        if ($this->scheduler->is_cancelled()) {
            return get_string('cancelled', 'local_lunchandlearn');
        }
        if ($this->scheduler->has_past()) {
            return get_string('closed', 'local_lunchandlearn');
        }
        return get_string('open', 'local_lunchandlearn');
    }

    public function delete() {
        global $DB;

        if (false === $this->scheduler->is_cancelled()) {
            throw new Exception('Cannot delete a session until it has been cancelled');
        }

        $this->get_event()->delete();
        $DB->delete_records('local_lunchandlearn', array('id' => $this->id));
    }

    public function lock() {
        global $DB;
        $data = $DB->get_record(self::TABLE, array('id' => $this->get_id()));
        $data->locked = 1;
        return $DB->update_record(self::TABLE, $data);
    }

    public function is_locked() {
        return $this->locked == 1;
    }

    /**
     * TODO: Capture error here and delete event if needed
     *
     * @global type $DB
     * @param type $data
     */
    public function save($data) {
        global $DB;
        $class = new stdClass;
        $class->id = $this->id;

        // Potentially create event here, or update.
        $eventdata = clone $data;
        $eventdata->timeduration = $this->scheduler->get_duration() * MINSECS;
        if (!empty($class->id)) {
            $eventdata->id = $data->eventid;
        }
        $event = new calendar_event();
        $event->update($eventdata);

        // Now add the lunch and learn.
        $class->eventid = $event->id;
        $class->categoryid = $this->get_categoryid();
        $class->name = $this->name;
        $class->summary = $this->get_summary();
        $class->supplier = $this->get_supplier();
        $class->joindetail = $this->get_joindetail();
        $class->description = $this->get_description();
        $class->sessioninfo = $this->sessioninfo;
        $class->recording = $this->get_recording();
        $class->locked = $this->is_locked();
        // From scheduler.
        $class->startdate = $this->scheduler->get_date();
        $class->timezone  = $this->scheduler->get_timezone();
        $class->regionid = $this->scheduler->regionid;
        $class->office = $this->scheduler->get_office();
        $class->room   = $this->scheduler->get_room();

        // From attendance manager.
        $class->availableinperson = $this->attendeemanager->availableinperson;
        $class->capacity = $this->attendeemanager->capacity;
        $class->overbookinperson = $this->attendeemanager->overbookinperson;
        $class->availableonline = $this->attendeemanager->availableonline;
        $class->onlinecapacity = $this->attendeemanager->onlinecapacity;
        $class->overbookonline = $this->attendeemanager->overbookonline;

        if (empty($class->id)) {
            $this->set_id($DB->insert_record('local_lunchandlearn', $class));
        } else {
            $DB->update_record('local_lunchandlearn', $class);
        }
        // Attachments (related docs).
        $context = context_system::instance();
        file_save_draft_area_files($data->attachments, $context->id, 'local_lunchandlearn', 'attachment',
                   $this->get_id(), array(/*'subdirs' => 0, 'maxbytes' => $maxbytes, 'maxfiles' => 50*/));
    }

    public function set_recording($recording) {
        if (is_array($recording)) {
            if (!empty($recording['text'])) {
                $this->recording = $recording['text'];
            }
        } else {
            $this->recording = $recording;
        }
    }

    public function get_recording_files() {
        // Actually grab the files from the filemanager...
    }

    public function get_icon() {
        $fs = get_file_storage();
        $files = $fs->get_area_files(1, 'local_lunchandlearn', 'attachment', $this->id);
        if ($files) {
            $file = array_pop($files);
            return moodle_url::make_pluginfile_url(
                    $file->get_contextid(),
                    $file->get_component(),
                    $file->get_filearea(),
                    $file->get_itemid(),
                    $file->get_filepath(),
                    $file->get_filename(),
                    false
                    );
        }
    }

    /* Used to set data on a form */
    public function form(moodleform $mform) {
        $mform->set_data(get_object_vars($this));

        $this->attendeemanager->form($mform);
        $this->scheduler->form($mform);

        // Load attachments...
        $draftitemid = file_get_submitted_draft_itemid('attachments');
        $context = context_system::instance();
        file_prepare_draft_area($draftitemid, $context->id, 'local_lunchandlearn', 'attachment', $this->get_id());
        $mform->set_data(array('attachments' => $draftitemid));
        // Prepare editor.
        $recorded = new stdClass;
        $recorded->recorded = $this->get_recording();
        $recorded->recordedformat = FORMAT_HTML;
        $data = file_prepare_standard_editor($recorded, 'recorded', array(
                    'trusttext' => true,
                    'maxfiles' => 5,
                    'maxbytes' => get_max_upload_file_size(),
                    'subdirs' => true,
                    'context' => context_system::instance()
                ), context_system::instance(),
                                     'local_lunchandlearn', 'entry', $this->get_id());
        $mform->set_data($data);
    }

    public function get_category_name() {
        if (empty($this->category)) {
            $this->category = coursecat::get($this->categoryid, MUST_EXIST, true);
        }
        return $this->category->name;
    }

    /**
     * Helper funciton to grab the URL to day for this calendar event
     */
    public function get_cal_url($mode='day') {
        global $USER;
        if ($mode == 'full') {
            return new moodle_url('/calendar/view.php', array('id' => $this->get_id(), 'view' => 'event'));
        }
        $datetime = $this->scheduler->get_DateTime();
        if (!empty($USER->timezone)) {
            $datetime->setTimezone(lunchandlearn_get_moodle_user_timezone($USER));
        }
        return new moodle_url('/calendar/view.php', array(
            'view' => 'day',
            'cal_d' => $datetime->format('d'),
            'cal_m' => $datetime->format('m'),
            'cal_y' => $datetime->format('Y')));
    }

    public function markable($userid = null) {
        if (has_capability('local/lunchandlearn:global', context_system::instance(), $userid)) {
            return true;
        }
        $coursecat = $this->get_categoryid();
        if (!empty($coursecat)) {
            if (has_capability('local/lunchandlearn:mark', context_coursecat::instance($coursecat), $userid)) {
                return true;
            }
        }
        return false;
    }

    public function editable($userid = null) {
        if (has_capability('local/lunchandlearn:global', context_system::instance(), $userid)) {
            return true;
        }
        $coursecat = $this->get_categoryid();
        if (!empty($coursecat)) {
            if (has_capability('local/lunchandlearn:edit', context_coursecat::instance($coursecat), $userid)) {
                return true;
            }
        }
        return false;
    }
}
