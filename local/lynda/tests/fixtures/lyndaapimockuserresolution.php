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
 * @package    local_lynda
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_lynda;

require_once('lyndaapimock.php');

class lyndaapimockuserresolution extends lyndaapimock {
    const MATCHONLYONEMAIL = 10;
    const MATCHONNOTHING = 20;
    const MATCHUSERIDANDFIRSTNAME = 30;
    const MATCHALL = 40;
    public $mode = self::MATCHALL;

    private $users = [];
    private $runcount = 0;

    public function individualusagedetail($startdate, $enddate, $start) {
        if ($this->runcount > 0) {
            return [];
        }
        $retval = clone($this->individualusagedetailresponse);
        $retval->ReportData = array_slice($retval->ReportData, $start, 2);

        $i = 0;
        foreach ($retval->ReportData as $data) {
            $this->dousermatching($data, $i);
            $i++;
        }

        $this->runcount += 1;

        return $retval->ReportData;
    }

    public function certficateofcompletion($startdate, $enddate, $start) {
        if ($this->runcount > 0) {
            return [];
        }
        $retval = clone($this->certficateofcompletionresponse);
        $retval->ReportData = array_slice($retval->ReportData, $start, 2);

        $i = 0;
        foreach ($retval->ReportData as $data) {
            $this->dousermatching($data, $i);
            $i++;
        }

        $this->runcount += 1;

        return $retval->ReportData;
    }

    private $getcoursesresponse;
    private $individualusagedetailresponse;
    private $certficateofcompletionresponse;

    public function __construct() {
        global $CFG;

        parent::__construct();

        $this->region = -1;
        $this->certficateofcompletionresponse =
                json_decode(file_get_contents("$CFG->dirroot/local/lynda/tests/fixtures/single_record_mockcertficateofcompletionresponse.json"));
        $this->individualusagedetailresponse =
                json_decode(file_get_contents("$CFG->dirroot/local/lynda/tests/fixtures/single_record_mockindividualusagedetailresponse.json"));
    }

    /**
     * @param $data
     * @param $i
     */
    private function dousermatching($data, $i) {
        if (isset($this->users[$data->Username])) {
            $data->Username = $this->users[$data->Username];
        } else {
            if ($this->mode == self::MATCHALL) {
                $user = self::getDataGenerator()->create_user([
                        'idnumber'  => "padded$i",
                        'firstname' => $data->FirstName,
                        'lastname'  => $data->LastName,
                        'email'     => $data->Email
                ]);
            } else if ($this->mode == self::MATCHONLYONEMAIL) {
                $user = self::getDataGenerator()->create_user([
                        'idnumber'  => "padded$i",
                        'firstname' => 'nonmatching',
                        'lastname'  => 'nonmatching',
                        'email'     => $data->Email
                ]);
                $user->id = '666';
            } else if ($this->mode == self::MATCHONNOTHING) {
                $user = self::getDataGenerator()->create_user([
                        'idnumber'  => "padded$i",
                        'firstname' => 'nonmatching',
                        'lastname'  => 'nonmatching',
                        'email'     => 'nonmatching'
                ]);
                $user->id = '666';
            } else if ($this->mode == self::MATCHUSERIDANDFIRSTNAME) {
                $user = self::getDataGenerator()->create_user([
                        'idnumber'  => "padded$i",
                        'firstname' => $data->FirstName,
                        'lastname'  => 'nonmatching',
                        'email'     => 'nonmatching'
                ]);
            }
            $this->users[$data->Username] = $user->id;
            $data->Username = $user->id;
        }
    }
}