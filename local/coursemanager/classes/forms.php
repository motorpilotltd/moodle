<?php
// This file is part of the Arup Course Management system
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
 * @package     local_coursemanager
 * @copyright   2016 Motorpilot Ltd, Sonsbeekmedia
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursemanager;

use stdClass;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

class forms {
    public $coursemanager;
    private $filefields;
    private $context;
    private $courseeditorfield;
    public $form;

    /**
     * Constructor.
     *
     * The Constructor takes 4 arguments. The moduleid or the dbid of the current
     * coursemanager instance, the user object, the name of the current page and the db id
     * of the form loaded on this page.
     *
     * param object $coursemanager the full user object.
     * param string $page the name of the current page to show.
     * param int $forminstanceid the db id of the current form.
     */
    public function __construct(\local_coursemanager\coursemanager $coursemanager) {
        global $CFG;
        require_once($CFG->libdir . '/formslib.php');
        $this->coursemanager = $coursemanager;
        $this->context = \context_system::instance();
    }

    /**
     * Get the form for the current page.
     *
     * @return object $form The form for the current page.
     */
    public function get_form() {
        global $CFG, $USER;
        
        if ($this->coursemanager->editing == 1) {
            $this->courseeditorfield = array('coursedescription', 'courseobjectives', 'courseaudience', 'businessneed',);
            require_once($CFG->dirroot . '/local/coursemanager/forms/'.$this->coursemanager->page.'.php');
            $formclass = 'cmform_' . $this->coursemanager->page;
            $sform = $this->stored_form();
            $this->form = new $formclass(null, $sform);
            $this->process_data($sform);
            $this->form->set_data($sform);
        } else {
            $this->courseeditorfield = array();
            $this->form = $this->stored_form();
        }
    }

    /**
     * Get the stored form instance with data.
     * 
     * return object $sform a new empty for or the full form data for an existing record.
     */
    private function stored_form() {
        global $DB, $USER, $SESSION;

        $sform = new stdClass();
        $sform->cmcourse = '-1';
        $sform->cmclass = '-1';
        $sform->id = '-1';
        if ($this->coursemanager->page == 'course' && $this->coursemanager->cmcourse->id == -1) {
            //return $sform;
        }
        $sform->start = $this->coursemanager->start;
        $definitionoptions = array('trusttext' => false, 'subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 99,
        'context' => $this->context);
        if (strpos($this->coursemanager->page, 'class') === 0) {
            $params = array('id' => $this->coursemanager->cmclass->id);
            if ($classdata = $DB->get_record('local_taps_class', $params)) {
                $classdata->cmclass = $classdata->id;
                $classdata->cmcourse = $DB->get_field('local_taps_course', 'id', array('courseid' => $classdata->courseid));
                $classdata->start = $this->coursemanager->start;
                $classdata->coursename = $classdata->coursenamedisplay = $this->coursemanager->cmcourse->coursename;
                if ($this->coursemanager->editing == 1 && $classdata->classtype == 'Scheduled') {
                    // Unset for Scheduled classes as hidden fields in secondary form.
                    unset($classdata->classtype);
                    unset($classdata->classstatus);
                }
                // Need to do some date/time offsetting.
                try {
                    $timezone = new \DateTimeZone($classdata->usedtimezone);
                } catch (Exception $e) {
                    $timezone = new \DateTimeZone(date_default_timezone_get());
                }
                $utctimezone = new \DateTimeZone('UTC');
                if ($classdata->classstarttime > 0) {
                    $classdata->classstarttimeenabled = 1;
                    // Need to adjust timestamps back to align against UTC.
                    $classstarttime = new \DateTime(null, $timezone);
                    $classstarttime->setTimestamp($classdata->classstarttime);
                    $newclassstarttime = new \DateTime($classstarttime->format('Y-m-d H:i'), $utctimezone);
                    $classdata->classstarttime = $newclassstarttime->getTimestamp();
                }
                if ($classdata->classendtime > 0) {
                    $classdata->classendtimeenabled = 1;
                    // Need to adjust timestamps back to align against UTC.
                    $classendtime = new \DateTime(null, $timezone);
                    $classendtime->setTimestamp($classdata->classendtime);
                    $newclassendtime = new \DateTime($classendtime->format('Y-m-d H:i'), $utctimezone);
                    $classdata->classendtime = $newclassendtime->getTimestamp();
                }
                if ($classdata->enrolmentenddate > 0) {
                    $classdata->enrolmentenddateenabled = 1;
                }
                // Unset some fields when duplicating classes.
                $duplicate = optional_param('duplicate', false, PARAM_BOOL);
                if ($duplicate) {
                    //
                    unset($classdata->classname);
                    $classdata->id = -1;
                }
                if ($classdata->maximumattendees == -1) {
                    $classdata->unlimitedattendees = 1;
                    $classdata->maximumattendees = 0;
                } else {
                    $classdata->unlimitedattendees = 0;
                }

                return $classdata;
            }
            $sform->jobnumber = $this->coursemanager->cmcourse->jobnumber;
            $sform->cmcourse = $this->coursemanager->cmcourse->id;
            $sform->courseid = $sform->courseiddisplay = $this->coursemanager->cmcourse->courseid;
            $sform->coursename = $sform->coursenamedisplay = $this->coursemanager->cmcourse->coursename;

        } else if ($this->coursemanager->page == 'course') {
            $params = array('id' => $this->coursemanager->cmcourse->id);

            if ($coursedata = $DB->get_record('local_taps_course', $params)) {
                $coursedata->cmcourse = $coursedata->id;
                $coursedata->start = $this->coursemanager->start;
                $coursedata->durationunits = $coursedata->durationunitscode;

                if ($coursedata->enddate > 0) {
                    $coursedata->enddateenabled = 1;
                }
                foreach ($coursedata as $key => $field) {
                    if (in_array($key, $this->courseeditorfield)) {
                        $format = $key . 'format';
                        $coursedata->$format = 1;
                        $coursedata = file_prepare_standard_editor($coursedata, $key, $definitionoptions,
                            $this->context, 'local_coursemanager', $key, $coursedata->id);
                    }
                }
                if (!empty($coursedata->globallearningstandards)) {
                    $coursedata->globallearningstandards = 1;
                }
                return $coursedata;
            } else {
                if ($sessiondata = $this->get_session_data()) {
                    return $sessiondata;
                }
            }
        }
        return $sform;
    }

    /**
     * Partially filled out forms are stored in the user session.
     * this function returns this data.
     */
    private function get_session_data($data = null) {
        global $SESSION;

        $sessiondata = new stdClass();
        $hassessiondata = false;

        for ($i = 1; $i <= 3; $i++) {
            $tab = 'tab' . $i;
            if (isset($SESSION->$tab)) {
                $hassessiondata = true;
                $tabdata = unserialize($SESSION->$tab);
                foreach ($tabdata as $key => $value) {
                    if ($key == 'tab') {
                        continue;
                    }
                    $sessiondata->$key = $value;
                }
            }
        }
        // this is data coming in from the last tab. So when this is added
        // we can destroy the session data;
        if ($data) {
            $hassessiondata = true;
            foreach ($data as $key => $value) {
                if ($key == 'tab') {
                    continue;
                }
                $sessiondata->$key = $value;
            }
            unset ($SESSION->tab1, $SESSION->tab2, $SESSION->tab3);
        }
        if ($hassessiondata) {
            return $sessiondata;
        } else {
            return false;
        }
    }

    /**
     * Check if the form is submitted and pass it on to the store_data function.
     * Set any alerts and redirect as necessary.
     */
    private function process_data($sform) {
        $strings = $this->get_process_strings();
        if ($this->form->is_cancelled()) {
            if (strpos($this->coursemanager->page, 'class') === 0) {
                $params = array('page' => 'classoverview', 'cmcourse' => $this->coursemanager->cmcourse->id,
                    'start' => $this->coursemanager->start);
                redirect(new moodle_url($this->coursemanager->baseurl, $params));
            } else if ($this->coursemanager->page == 'course') {
                $params = array('page' => 'overview', 'start' => $this->coursemanager->start);
                redirect(new moodle_url($this->coursemanager->baseurl, $params));
            }
        }

        if ($this->form->is_submitted() && ($data = $this->form->get_data())) {

            // Grab this now as it's unset prior to storing data in DB.
            $continue = isset($data->submitcontinue);
            // Allows forms to process data in a different way.
            if (method_exists($this->form, 'store_data')) {
                $this->form->store_data($this, $data);
            } else {
                $this->store_data($data);
            }
        }
    }

    /**
     * Get strings for alerts (either custom for form, if they exist, or defaults).
     *
     * @return stdClass
     */
    private function get_process_strings() {
        $strings = new stdClass();

        $strman = get_string_manager();
        $component = 'local_coursemanager';

        $base = "form:{$this->coursemanager->page}:alert:";

        foreach (array('cancelled', 'saved', 'error') as $id) {
            if ($strman->string_exists($base.$id, $component)) {
                $strings->{$id}  = get_string($base.$id, $component);
            } else {
                $strings->{$id}  = get_string("form:alert:{$id}", $component);
            }
        }

        return $strings;
    }

    /**
     * Store the date passed from our current form
     *
     * @param object $data formdata
     */
    public function store_data($data) {
        global $DB, $SESSION;

        $taps = new \local_taps\taps();

        $classpages = array('class_scheduled', 'class_scheduled_normal', 'class_scheduled_planned', 'class_self_paced');

        if (in_array($data->page, $classpages)) {
            $datatype = 'class';
        } else if ($data->page == 'course') {
            $datatype = 'course';
        } else {
            echo $data->page;
            return false;
            $datatype = 'other';
        }

        if ($datatype == 'course') {
            $database = 'local_taps_course';
            $definitionoptions = array('trusttext' => true, 'subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 99);
            foreach ($data as $key => $value) {
                $orgname = str_replace('_editor', '', $key);
                if (in_array($orgname, $this->courseeditorfield)) {
                    $data = file_postupdate_standard_editor($data, $orgname, $definitionoptions,
                                    $this->context, 'local_coursemanager', $orgname, $data->id);
                }
            }
            if ($data->courseid == 0) {
                $sqlmax = "SELECT max(courseid) as maxid from {local_taps_course}";
                $max = $DB->get_record_sql($sqlmax);
                $data->courseid = $max->maxid + 1;
            }
            if ($data->tab == 1) {
                if (!isset($data->enddateenabled)) {
                    $data->enddate = 0;
                }
                if ($data->durationunits !== "0") {
                    
                    $tapsdurationunits = $taps->get_durationunitscode();
                    $data->durationunitscode = $data->durationunits;
                    $data->durationunits = $tapsdurationunits[$data->durationunits];
                }
            }
            if ($data->tab == 3) {
                if ($data->globallearningstandards == 1) {
                    $data->futurereviewdata = 0;
                    $data->accreditationgivendate = 0;
                }
            }
        } else if ($datatype == 'class') {
            $database = 'local_taps_class';
            $data->coursename = $this->coursemanager->cmcourse->coursename;
            if (!isset($data->enrolmentenddateenabled)) {
                $data->enrolmentenddate = 0;
            }
            if (!isset($data->classhidden)) {
                $data->classhidden = 0;
            }
            if ($data->classdurationunitscode !== "0") {
                $tapsdurationunits = $taps->get_durationunitscode();
                $data->classdurationunits = $tapsdurationunits[$data->classdurationunitscode];
            }
            if ($data->classtype == 'Scheduled' && $data->classstatus == 'Planned') {
                if (!isset($data->classstarttimeenabled)) {
                    $data->classstarttime = 0;
                }
                if (!isset($data->classendtimeenabled)) {
                    $data->classendtime = 0;
                }
            }
            if ($data->classtype == 'Self Paced') {
                if (!isset($data->classendtimeenabled)) {
                    $data->classendtime = 0;
                }
            }
            if (isset($data->unlimitedattendees) && $data->unlimitedattendees == 1) {
                $data->maximumattendees = -1;
            }
        }

        // Set some event details before page info is unset.
        $eventclass = "\\local_coursemanager\\event\\{$datatype}_";
        $eventid = "{$datatype}id";
        $oldfields = [];

        unset(
            $data->submitbutton,
            $data->submitcontinue,
            $data->cancelbutton
        );

        if ($record = $DB->get_record($database, array('id' => $data->id))) {
            foreach ($data as $key => $value) {
                if (isset($record->$key) && $record->$key != $value) {
                    $oldfields[$key] = $record->$key;
                }
                $record->$key = $value;
            }
            $record->timemodified = time();
            $DB->update_record($database, $record);
            $params = array('page' => 'course', 'cmcourse' => $record->id, 'start' => 0, 'edit' => 0);
            $eventtype = 'updated';
        } else {
            $data->timemodified = time();
            if ($datatype == 'course') {
                $params = array('page' => 'course', 'cmcourse' => 0, 'start' => 0, 'edit' => 1);
                // Temporary store data in the session. Only save on final tab.
                if ($data->tab == 1 || $data->tab == 2) {
                    $tab = 'tab' . $data->tab;
                    $SESSION->$tab = serialize($data);
                    $params['tab'] = $data->tab + 1;
                    $redirect = new moodle_url($this->coursemanager->baseurl, $params);
                    redirect($redirect);
                }
                if ($data->tab == 3) {
                    $data = $this->get_session_data($data);
                }
            }

            if ($datatype == 'class') {
                $sqlmax = "SELECT max(classid) as maxid from {local_taps_class}";
                $max = $DB->get_record_sql($sqlmax);
                $data->classid = $max->maxid + 1;
            }

            $params['cmcourse'] = $data->id = $DB->insert_record($database, $data);
            $params['edit'] = 0;
            $eventtype = 'created';
        }

        if ($datatype == 'class') {
            $params = array('page' => 'classoverview', 'cmcourse' => $data->cmcourse, 'start' => 0);
            if ($eventtype === 'updated' && $data->classtype == 'Scheduled' && $data->classstatus == 'Normal' && $data->classstarttime > time()) {
                // Any 'placed' enrolments?
                $statuses = array_merge($taps->get_statuses('placed'));
                list($insql, $inparams) = $DB->get_in_or_equal($statuses, SQL_PARAMS_NAMED, 'status');
                $sql = "SELECT COUNT(*)
                          FROM {local_taps_enrolment}
                          WHERE classid = :classid AND {$DB->sql_compare_text('bookingstatus')} {$insql}";
                $inparams['classid'] = $data->classid;
                if ($DB->count_records_sql($sql, $inparams)) {
                    $params['resendinvites'] = $data->{$eventid};
                }
            }
        }

        $redirect = new moodle_url($this->coursemanager->baseurl, $params);

        //Complete event class.
        $eventclass .= $eventtype;
        $event = $eventclass::create(array(
            'objectid' => $data->id,
            'other' => array(
                $eventid => $data->{$eventid},
                'oldfields' => $oldfields)
        ));
        $event->trigger();

        redirect($redirect);
    }
}