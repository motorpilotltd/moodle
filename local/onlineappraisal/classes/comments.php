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

class comments {

    /**
     * @var string $table Associated table name.
     */
    public static $table = 'local_appraisal_comment';

    /**
     * @var int $appraisalid The appraisal ID.
     */
    private $appraisalid;
    /**
     * @var array $comments Comments records.
     */
    private $comments;

    /**
     * Stores who to send emails to when a user (of a certain type) adds a comment.
     * 
     * @var array $emails
     */
    private static $emails = array(
        'appraisee' => array('appraiser'),
        'appraiser' => array('appraisee'),
        'signoff' => array('appraisee', 'appraiser'),
        'groupleader' => array('appraisee', 'appraiser', 'signoff'),
    );
    
    /**
     * Constructor.
     * 
     * @param int $appraisalid
     */
    public function __construct($appraisalid) {
        $this->appraisalid = $appraisalid;
        $this->load_comments();
    }

    /**
     * Get all comments or an individual comment.
     *
     * @param int $id
     * @return mixed
     */
    public function get_comments($id = 0) {
        if ($id) {
            return isset($this->comments[$id]) ? $this->comments[$id] : null;
        }
        return $this->comments;
    }

    /**
     * Load all comments for current appraisal.
     *
     * @global moodle_database $DB
     */
    public function load_comments() {
        global $DB;

        $params = array('appraisalid' => $this->appraisalid);
        $sort = 'created_date DESC';
        $comments =  $DB->get_records(self::$table, $params, $sort);

        foreach($comments as $comment) {
            self::format($comment);
        }

        $this->comments = $comments;
    }

    /**
     * Process comment form submission via ajax.
     *
     * @global stdClass $USER
     * @return stdClass
     * @throws Exception
     */
    public static function submit_comment() {
        global $USER;

        $appraisalid = required_param('appraisalid', PARAM_INT);
        $comment = trim(required_param('comment', PARAM_RAW));
        $view = optional_param('view', '', PARAM_ALPHA);

        $appraisal = new \local_onlineappraisal\appraisal($USER, $appraisalid, $view, 'overview', 0);

        if (!$appraisal->check_permission('comments:add')) {
            throw new moodle_exception('error:permission:comment:add', 'local_onlineappraisal');
        }

        if (empty($comment)) {
            throw new moodle_exception('error:comment:validation', 'local_onlineappraisal');
        }

        $record = self::save_comment($appraisalid, $comment, $USER->id, $appraisal->appraisal->viewingas);
        $return = new stdClass();
        $return->success = $record->id;

        if ($return->success) {
            $return->message = get_string('success:comment:add', 'local_onlineappraisal');
            self::format($record);
            $return->data = $record;
            self::send_email($appraisal, $comment);
        } else {
            $return->message = get_string('error:comment:add', 'local_onlineappraisal');
            $return->data = '';
        }

        return $return;
    }

    /**
     * Save the comment to the DB.
     *
     * @global \moodle_database $DB
     * @param int $appraisalid
     * @param string $comment
     * @param int|null $ownerid
     * @param string $usertype
     * @return stdClass
     */
    public static function save_comment($appraisalid, $comment, $ownerid = null, $usertype = null) {
        global $DB;

        $record = new stdClass();
        $record->appraisalid = $appraisalid;
        $record->comment = $comment;
        $record->ownerid = $ownerid;
        $record->user_type = $usertype;
        $record->created_date = time();

        $record->id = $DB->insert_record(self::$table, $record);

        return $record;
    }

    /**
     * Formats comments for display purposes.
     *
     * @global \moodle_database $DB
     * @staticvar array $owners
     * @param stdClass $comment
     */
    public static function format(&$comment) {
        global $DB, $OUTPUT, $PAGE;

        // Timezone caching.
        static $timezone = null;
        // Owner caching.
        static $owners = array();
        // Keep time constant across calculations.
        static $now = null;

        if (empty($timezone)) {
            $timezone = \core_date::get_user_timezone_object();
        }

        if (empty($now)) {
            $now = new \DateTime('now', $timezone);
        }

        // Save on loading records every time for repeated owners.
        if (empty($comment->ownerid) && !isset($owners[0])) {
            $owners[0] = new stdClass();
            $owners[0]->fullname = get_string('comment:system', 'local_onlineappraisal');
            $owners[0]->imageurl = $OUTPUT->pix_url('u/f2')->out(false);
        } else if (!empty($comment->ownerid) && !isset($owners[$comment->ownerid])) {
            $owner = $DB->get_record('user', array('id' => $comment->ownerid));
            $ownerpicture = new \user_picture($owner);
            $owners[$comment->ownerid] = new stdClass();
            $owners[$comment->ownerid]->fullname = fullname($owner);
            $owners[$comment->ownerid]->imageurl = $ownerpicture->get_url($PAGE)->out(false);
        }

        $comment->role = empty($comment->ownerid) ? get_string('comment:system', 'local_onlineappraisal') : (!empty($comment->user_type) ? get_string($comment->user_type, 'local_onlineappraisal') : null);
        $comment->owner = empty($comment->ownerid) ? $owners[0] : $owners[$comment->ownerid];

        $createdtime = new \DateTime('@'.$comment->created_date);
        $createdtime->setTimezone($timezone);

        $datediff = $now->diff($createdtime, true);

        if ($datediff->y > 0) {
            $string = ($datediff->y === 1) ? 'timediff:year' : 'timediff:years';
            $a = $datediff->y;
        } else if ($datediff->m > 0) {
            $string = ($datediff->m === 1) ? 'timediff:month' : 'timediff:months';
            $a = $datediff->m;
        } else if ($datediff->d > 0) {
            $string = ($datediff->d === 1) ? 'timediff:day' : 'timediff:days';
            $a = $datediff->d;
        } else if ($datediff->h > 0) {
            $string = ($datediff->h === 1) ? 'timediff:hour' : 'timediff:hours';
            $a = $datediff->h;
        } else if ($datediff->i > 0) {
            $string = ($datediff->i === 1) ? 'timediff:minute' : 'timediff:minutes';
            $a = $datediff->i;
        } else if ($datediff->s > 0) {
            $string = ($datediff->s === 1) ? 'timediff:second' : 'timediff:seconds';
            $a = $datediff->s;
        } else {
            $string = 'timediff:now';
            $a = '';
        }
        $comment->createdtext = get_string($string, 'local_onlineappraisal', $a);
        $comment->createdtitle = userdate($comment->created_date);

        // Inject html line breaks.
        $comment->comment = format_text($comment->comment, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
    }

    /**
     * Send emails to users.
     * Will silently continue if no email template present for a particular user.
     *
     * @param \local_onlineappraisal\appraisal $oa
     * @param string $comment
     * @return boolean
     * @throws moodle_exception
     */
    private static function send_email($oa, $comment) {
        global $USER;

        $result = true;

        $appraisal = $oa->appraisal;

        $emailvars = new stdClass();
        $emailvars->appraiseefirstname = $appraisal->appraisee->firstname;
        $emailvars->appraiseelastname = $appraisal->appraisee->lastname;
        $emailvars->appraiseeemail = $appraisal->appraisee->email;
        $emailvars->appraiserfirstname = $appraisal->appraiser->firstname;
        $emailvars->appraiserlastname = $appraisal->appraiser->lastname;
        $emailvars->appraiseremail = $appraisal->appraiser->email;
        $emailvars->signofffirstname = $appraisal->signoff->firstname;
        $emailvars->signofflastname = $appraisal->signoff->lastname;
        $emailvars->signoffemail = $appraisal->signoff->email;
        $emailvars->groupleaderfirstname = $appraisal->groupleader ? $appraisal->groupleader->firstname : '-';
        $emailvars->groupleaderlastname = $appraisal->groupleader ? $appraisal->groupleader->lastname : '-';
        $emailvars->groupleaderemail = $appraisal->groupleader ? $appraisal->groupleader->email : '-';
        // Who added the comment (and is therefore sending the email).
        $emailvars->fromfirstname = $USER->firstname;
        $emailvars->fromlastname = $USER->lastname;
        $emailvars->fromemail = $USER->email;
        $emailvars->fromtype = get_string($appraisal->viewingas, 'local_onlineappraisal');

        $url = new \moodle_url(
                '/local/onlineappraisal/view.php',
                array('appraisalid' => $appraisal->id, 'view' => 'appraisee', 'page' => 'overview')
                );
        $urldashboard = new \moodle_url(
                '/local/onlineappraisal/index.php',
                array('page' => 'appraisee')
                );
        $emailvars->linkappraisee = $url->out();
        $emailvars->linkappraiseedashboard = $urldashboard->out();
        $url->param('view', 'appraiser');
        $urldashboard->param('page', 'appraiser');
        $emailvars->linkappraiser = $url->out();
        $emailvars->linkappraiserdashboard = $urldashboard->out();
        $url->param('view', 'signoff');
        $urldashboard->param('page', 'signoff');
        $emailvars->linksignoff = $url->out();
        $emailvars->linksignoffdashboard = $urldashboard->out();
        $url->param('view', 'groupleader');
        $urldashboard->param('page', 'groupleader');
        $emailvars->linkgroupleader = $url->out();
        $emailvars->linkgroupleaderdashboard = $urldashboard->out();

        $emailvars->status = get_string("status:{$appraisal->statusid}", 'local_onlineappraisal');
        $emailvars->comment = $comment;

        foreach (self::$emails[$appraisal->viewingas] as $type) {
            if (!$appraisal->{$type}) {
                continue;
            }
            $stremail = "comment:{$type}";
            $to = $appraisal->{$type};
            try {
                $email = new email($stremail, $emailvars, $to, $USER);
                $email->prepare();
                $email->send();
            } catch (moodle_exception $e) {
                // If error indicates language string doesn't exist there is no email for this user (i.e. a known error).
                if (!($e->errorcode == 'error:invalidemail' && $e->module == 'local_onlineappraisal')) {
                    // Don't know what the error is, so mark it.
                    $result = false;
                }
            }
        }

        return $result;
    }
}
