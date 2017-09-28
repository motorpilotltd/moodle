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
 * Version details
 *
 * @package     local_onlineappraisal
 * @copyright   2016 Motorpilot Ltd, Sonsbeekmedia
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_onlineappraisal;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use moodle_exception;
use moodle_url;

class checkins extends comments {

    /**
     * @var string $table Associated table name.
     */
    public static $table = 'local_appraisal_checkins';

    /**
     * @var int $appraisalid The appraisal ID.
     */
    private $appraisalid;

    /**
     * @var object $appraisal instance.
     */
    private $appraisal;

    /**
     * @var array $checkins checkins records.
     */
    private $checkins;
    
    /**
     * Constructor.
     * 
     * @param int $appraisalid
     */
    public function __construct($appraisal) {
        if (is_object($appraisal)) {
            $this->appraisal = $appraisal;
            $this->appraisalid = $appraisal->appraisal->id;
        } else {
            $this->appraisalid = $appraisal;
        }
    }

    public function hook() {
        global $SESSION;
        $checkin = optional_param('checkin', 0, PARAM_INT);
        $action = optional_param('action', '', PARAM_TEXT);
        if ($checkin && $action) {
            if ($action == 'delete') {
                $this->delete_checkin($checkin);
            }
            if ($action == 'edit') {
                if (empty($SESSION->local_onlineappraisal)) {
                    $SESSION->local_onlineappraisal = new stdClass();
                }
                $SESSION->local_onlineappraisal->editcheckin = $checkin;
            }
        }
    }

    /**
     * Process comment form submission via ajax.
     * @global stdClass $USER
     * @return stdClass
     * @throws Exception
     */
    public static function submit_checkin() {
        global $USER;

        $appraisalid = required_param('appraisalid', PARAM_INT);
        $checkinid = required_param('checkinid', PARAM_INT);
        $checkin = trim(required_param('checkin', PARAM_RAW));
        $view = optional_param('view', '', PARAM_ALPHA);

        $onlineappraisal = new \local_onlineappraisal\appraisal($USER, $appraisalid, $view, 'overview', 0);

        if (!$onlineappraisal->check_permission('checkin:add')) {
            throw new moodle_exception('error:permission:checkin:add', 'local_onlineappraisal');
        }

        if (empty($checkin)) {
            throw new moodle_exception('error:checkin:validation', 'local_onlineappraisal');
        }

        $record = self::save_checkin($appraisalid, $checkin, $checkinid, $USER->id, $onlineappraisal->appraisal->viewingas);

        $return = new stdClass();
        $return->success = $record->id;

        if ($return->success) {
            $return->message = get_string('success:checkin:add', 'local_onlineappraisal');
            // Method comments::format() expects comment property.
            $record->comment = $record->checkin;
            self::format($record);
            $record->checkin = $record->comment;
            $return->data = $record;
        } else {
            $return->message = get_string('error:checkin:add', 'local_onlineappraisal');
            $return->data = new stdClass();
        }

        $params = array('page' => 'checkin',
            'appraisalid' => $appraisalid,
            'view' => $view,
            'checkin' => $record->id
            );

        $params['action'] = 'edit';
        $editurl = new moodle_url('/local/onlineappraisal/view.php', $params);
        $return->data->editurl = $editurl->out();
        $params['action'] = 'delete';
        $delurl = new moodle_url('/local/onlineappraisal/view.php', $params);
        $return->data->delurl = $delurl->out();
        $return->data->visible = true;
        $return->data->isowner = true;
        return $return;
    }

    /**
     * Save the checkin to the DB.
     * Duplicate found in checkins class
     * @global \moodle_database $DB
     * @param int $appraisalid
     * @param string $checkin
     * @param int|null $ownerid
     * @param string $usertype
     * @return stdClass
     */
    public static function save_checkin($appraisalid, $checkin, $checkinid, $ownerid = null, $usertype = null) {
        global $DB, $USER;

        if ($checkinid == -1) {
            $record = new stdClass();
            $record->appraisalid = $appraisalid;
            $record->checkin = $checkin;
            $record->ownerid = $ownerid;
            $record->user_type = $usertype;
            $record->created_date = time();
            $record->id = $DB->insert_record(self::$table, $record);
        } else {
            $params = array('id' => $checkinid);
            if ($record = $DB->get_record(self::$table, $params)) {
                $record->checkin = $checkin;
                $record->created_date = time();
                $DB->update_record(self::$table, $record);
            }
        }

        return $record;
    }

    /**
     * Get all checkins or an individual checkin.
     * @param int $id
     * @return mixed
     */
    public function get_checkins($id = 0) {
        $this->load_checkins();
        if (!$id) {
            return $this->checkins;
        } else if (isset($this->checkins[$id])) {
            $checkin = $this->checkins[$id];
            // Remove the line breaks.
            $checkin->checkin = preg_replace('/\<br(\s*)?\/?\>/i', "", $checkin->checkin);
            return $checkin;
        } else {
            return null;
        }
    }

    /**
     * Load all checkins for current appraisal.
     * Duplicate found in the checkins class
     *
     * @global moodle_database $DB
     */
    public function load_checkins() {
        global $DB, $USER, $SESSION;

        $skip = false;
        if (isset($SESSION->local_onlineappraisal->editcheckin)) {
            $skip = $SESSION->local_onlineappraisal->editcheckin;
        } 

        $params = array('appraisalid' => $this->appraisalid);
        $sort = 'created_date DESC';

        $checkins = $DB->get_records(self::$table, $params, $sort);

        // Only load full class if necessary.
        if ($checkins && !is_object($this->appraisal)) {
            $view = optional_param('view', '', PARAM_ALPHA);
            $this->appraisal = new \local_onlineappraisal\appraisal($USER, $this->appraisalid, $view, 'overview', 0);
        }

        foreach($checkins as $checkin) {
            $checkin->visible = true;
            if ($checkin->id ==  $skip) {
                $checkin->visible = false;
            }
            // The parent function expects the checkin to be called comment.
            // So for now we just swap them.
            // Also store an unformatted one for the edit form.
            $checkin->comment = $checkin->unformattedcheckin = $checkin->checkin;
            parent::format($checkin);
            $checkin->checkin = $checkin->comment;
            if ($USER->id == $checkin->ownerid && $checkin->user_type == $this->appraisal->appraisal->viewingas) {
                $checkin->isowner = true;
            } else {
                $checkin->isowner = false;
            }
        }

        $this->checkins = $checkins;
    }

    private function delete_checkin($checkin) {
        global $DB, $USER;
        $params = array('id' => $checkin);
        if ($ci = $DB->get_record(self::$table, $params)) {
            if ($USER->id == $ci->ownerid) {
                if ($DB->delete_records(self::$table, $params)) {
                    $message = get_string('checkin:deleted', 'local_onlineappraisal');
                    $type = 'success';
                    $button = true;
                    appraisal::set_alert($message, $type, $button);
                    return true;
                }
            }
        }

        $message = get_string('checkin:delete:failed', 'local_onlineappraisal');
        $type = 'danger';
        $button = true;
        appraisal::set_alert($message, $type, $button);
    }
}
