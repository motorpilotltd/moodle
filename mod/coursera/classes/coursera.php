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
 * @package   mod_coursera
 * @category  backup
 * @copyright 2018 Andrew Hancox <andrewdchancox@googlemail.com>
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_coursera;
global $CFG;

require_once($CFG->libdir . '/filelib.php');
require_once("$CFG->dirroot/completion/data_object.php");

class coursera {
    const COMPLETION_COMPLETE = 10;
    const COMPLETION_PASSED = 20;

    public function __construct() {
        $this->config = get_config('mod_coursera');
    }

    public function getrefreshtoken() {
        $now = time();

        $url = 'https://accounts.coursera.org/oauth2/v1/token';
        $params = [
                'redirect_uri'  => "http://localhost:9876/callback",
                'client_id'     => $this->config->client_id,
                'client_secret' => $this->config->client_secret,
                'access_type'   => 'offline',
                'code'          => $this->config->onetimecode,
                'Content-Type'  => 'application/x-www-form-urlencoded',
                'grant_type'    => 'authorization_code',
        ];

        $curl = new \curl();
        $options = array(
                'RETURNTRANSFER' => true,
                'USERAGENT'      => 'Moodle',
                'HTTPHEADER'     => ['Content-Type: application/x-www-form-urlencoded; charset=UTF-8']
        );

        try {
            $result = $curl->post($url, http_build_query($params), $options);

            $response = json_decode($result);
            $this->config->accesstoken = $response->access_token;
            $this->config->refreshtoken = $response->refresh_token;
            $this->config->accesstokenexpiry = $now + 1800;
            $this->config->refreshtokenexpiry = $now + 14 * DAYSECS;
        } catch (\Exception $ex) {
            print_error('Error fetching refresh token');
        }

        set_config('accesstoken', $this->config->accesstoken, 'mod_coursera');
        set_config('accesstokenexpiry', $this->config->accesstokenexpiry, 'mod_coursera');
        set_config('refreshtoken', $this->config->refreshtoken, 'mod_coursera');
        set_config('refreshtokenexpiry', $this->config->refreshtokenexpiry, 'mod_coursera');
        set_config('onetimecode', '', 'mod_coursera');
    }

    private function getaccesstoken() {
        $now = time();

        if (empty($this->config->refreshtoken) || $now > $this->config->refreshtokenexpiry) {
            print_error('No refresh token available');
        }

        if ($now < $this->config->accesstokenexpiry) {
            return $this->config->accesstoken;
        }

        $url = 'https://accounts.coursera.org/oauth2/v1/token';
        $params = [
                'redirect_uri'  => "http://localhost:9876/callback",
                'client_id'     => $this->config->client_id,
                'client_secret' => $this->config->client_secret,
                'access_type'   => 'offline',
                'Content-Type'  => 'application/x-www-form-urlencoded',
                'grant_type'    => 'refresh_token',
                'refresh_token' => $this->config->refreshtoken
        ];

        $curl = new \curl();
        $options = array(
                'RETURNTRANSFER' => true,
                'USERAGENT'      => 'Moodle',
                'HTTPHEADER'     => ['Content-Type: application/x-www-form-urlencoded; charset=UTF-8']
        );

        $result = $curl->post($url, http_build_query($params), $options);

        $response = json_decode($result);
        $this->config->accesstoken = $response->access_token;
        $this->config->accesstokenexpiry = $now + 1800;
        $this->config->refreshtokenexpiry = $now + 14 * DAYSECS;
        set_config('accesstoken', $this->config->accesstoken, 'mod_coursera');
        set_config('accesstokenexpiry', $this->config->accesstokenexpiry, 'mod_coursera');
        set_config('refreshtokenexpiry', $this->config->refreshtokenexpiry, 'mod_coursera');

        return $this->config->accesstoken;
    }

    public function synccourses() {
        $ret = $this->getcourses(0);
        if (isset($ret->elements)) {
            foreach ($ret->elements as $element) {
                if ($element->contentType !== 'Course') {
                    continue;
                }

                $courseracourse = course::savecourse($element);
                courserahashedcourseobject::savecourserahashedcourseobject($courseracourse, $element->instructors, 'instructor');
                courserahashedcourseobject::savecourserahashedcourseobject($courseracourse, $element->partners, 'partner');
                courserahashedcourseobject::savecourserahashedcourseobject($courseracourse, $element->programs, 'programlink');
            }
        }
        while (isset($ret->paging->next)) {
            $ret = $this->getcourses($ret->paging->next);
            if (isset($ret->elements)) {
                foreach ($ret->elements as $element) {
                    if ($element->contentType !== 'Course') {
                        continue;
                    }

                    $courseracourse = course::savecourse($element);
                    courserahashedcourseobject::savecourserahashedcourseobject($courseracourse, $element->instructors,
                            'instructor');
                    courserahashedcourseobject::savecourserahashedcourseobject($courseracourse, $element->partners, 'partner');
                    courserahashedcourseobject::savecourserahashedcourseobject($courseracourse, $element->programs, 'programlink');
                }
            }
        }
    }

    public function syncprogress() {
        $ret = $this->getprogress(0);
        foreach ($ret->elements as $element) {
            if ($element->contentType !== 'Course') {
                continue;
            }
            progress::saveprogress($element);
        }
        while (isset($ret->paging->next)) {
            $ret = $this->getprogress($ret->paging->next);
            foreach ($ret->elements as $element) {
                if ($element->contentType !== 'Course') {
                    continue;
                }
                progress::saveprogress($element);
            }
        }
        progress::updateusercourselinkages();
        progress::coursecompletions();
    }

    public function syncprogrammembers() {
        global $DB;

        $DB->execute('UPDATE {courseraprogrammember} set dateleft = :now where dateleft = 0', ['now' => time()]); // Mark them all as dateleft, they'll be zeroed out in the next step/

        $ret = $this->getprogrammembership(0);
        foreach ($ret->elements as $element) {
            programmember::saveprogrammember($element);
        }
        while (isset($ret->paging->next)) {
            $ret = $this->getprogrammembership($ret->paging->next);
            foreach ($ret->elements as $element) {
                programmember::saveprogrammember($element);
            }
        }
        programmember::updateuserprogrammemberlinkages();
        self::disableunusedmemberships();
    }

    public function disableunusedmemberships() {
        $activelearners = programmember::getmoodleactivelearners();
        $members = programmember::getallprogrammembershipidnumbers();

        $toremove = array_diff($members, $activelearners);

        foreach ($toremove as $useridnumber) {
            $this->unenrolfromprogram($useridnumber);
        }
    }

    private function getcourses($start = 0) {
        $url = "https://api.coursera.org/api/businesses.v1/{$this->config->orgid}/contents"; //GET
        if (!empty($start)) {
            $url .= '?start=' . $start;
        }
        return $this->callapi($url, '', 'get');
    }

    public function enrolonprogram($userid) {
        if (programmember::iscurrentlymember($userid)) {
            return true;
        }

        $user = \core_user::get_user($userid);

        $body = [
                'externalId' => $user->idnumber,
                'fullName'   => fullname($user),
                'email'      => $user->email,
                'sendEmail'  => false
        ];

        $url = "https://api.coursera.org/api/businesses.v1/{$this->config->orgid}/programs/{$this->config->programid}/invitations";
        $ret = $this->callapi($url, $body, 'post');

        if (isset($ret->errorCode) && $ret->errorCode !== 'INVITATION_ALREADY_EXISTS' && $ret->errorCode !== 'MEMBERSHIP_ALREADY_EXISTS') {
            return $ret->errorCode;
        }
        $programmember = new programmember([
                'programid'  => $this->config->programid,
                'externalid' => $user->idnumber,
                'datejoined' => 0, // This gets set when we pull the data back from coursera on the next sync run.
                'userid'     => $user->id,
                'dateleft'   => 0
        ]);
        $programmember->insert();
    }

    private function getprogress($start = 0) {
        $url =
                "https://api.coursera.org/api/businesses.v1/{$this->config->orgid}/enrollments?q=byProgramId&programId={$this->config->programid}";
        if (!empty($start)) {
            $url .= '&start=' . $start;
        }
        $ret = $this->callapi($url, false, 'get');
        return $ret;
    }

    public function getcourselink($courseracourseid) {
        $link = new \mod_coursera\programlink(['courseracourseid' => $courseracourseid, 'programid' => $this->config->programid]);
        return $link->contenturl . '&attemptSSOLogin=true';
    }

    private function unenrolfromprogram($useridnumber) {
        //DELETE verb
        $url = "https://api.coursera.org/api/businesses.v1/{$this->config->orgid}/programs/{$this->config->programid}/memberships/{$this->config->programid}~{$useridnumber}";

        //$this->callapi($url, null, 'delete');
    }

    private function getprogrammembership($start = 0) {
        $url = "https://api.coursera.org/api/businesses.v1/{$this->config->orgid}/programs/{$this->config->programid}/memberships";
        if (!empty($start)) {
            $url .= '?start=' . $start;
        }
        return $this->callapi($url, '', 'get');
    }

    private function callapi($url, $body, $verb) {
        $curl = new \curl();
        $options = array(
                'RETURNTRANSFER' => true,
                'USERAGENT'      => 'Moodle',
                'HTTPHEADER'     => ["Authorization: Bearer {$this->getaccesstoken()}"],
                'CONNECTTIMEOUT' => 0,
                'TIMEOUT'        => 240, // Fail if data not returned within 10 seconds.
        );

        if (!empty($body)) {
            $body = json_encode($body);
        } else {
            $body = '';
        }

        $result = $curl->{$verb}($url, $body, $options);

        return json_decode($result);
    }
}