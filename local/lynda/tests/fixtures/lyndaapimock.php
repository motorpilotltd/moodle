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


class lyndaapimock extends lyndaapi {
    public $mockregions = false;
    public $updatedresponse = false;

    public static function getDataGenerator() {
        global $CFG;

        require_once("$CFG->dirroot/lib/phpunit/classes/util.php");
        return \phpunit_util::get_data_generator();
    }

    public function getcourses($start) {
        return array_slice($this->getcoursesresponse, $start, 2);
    }

    private $users = [];
    public function individualusagedetail($startdate, $enddate, $start) {
        $retval = clone($this->individualusagedetailresponse);
        $retval->ReportData = array_slice($retval->ReportData, $start, 2);

        $i = 0;
        foreach($retval->ReportData as $data) {
            if (in_array($data->Username, $this->users)) {
                continue; // Already been mapped.
            }

            if (isset($this->users[$data->Username])) {
                $data->Username = $this->users[$data->Username];
            } else {
                $user = self::getDataGenerator()->create_user(['idnumber' => "padded$i"]);
                $this->users[$data->Username] = $user->id;
                $data->Username = $user->id;
            }
            $i++;
        }

        return $retval->ReportData;
    }

    public function certficateofcompletion($startdate, $enddate, $start) {
        $retval = clone($this->certficateofcompletionresponse);
        $retval->ReportData = array_slice($retval->ReportData, $start, 2);

        $i = 0;
        foreach($retval->ReportData as $data) {
            $user = self::getDataGenerator()->create_user(['idnumber' => "padded$i"]);
            $data->Username = $user->id;
            $i++;
        }

        return $retval->ReportData;
    }

    private $getcoursesresponse;
    private $individualusagedetailresponse;
    private $certficateofcompletionresponse;

    public function __construct() {
        global $CFG;

        parent::__construct();

        $this->region = -1;
        $this->getcoursesresponse = json_decode(file_get_contents("$CFG->dirroot/local/lynda/tests/fixtures/mockcoursesresponse.json"));
        $this->individualusagedetailresponse = json_decode(file_get_contents("$CFG->dirroot/local/lynda/tests/fixtures/mockindividualusagedetailresponse.json"));
        $this->certficateofcompletionresponse = json_decode(file_get_contents("$CFG->dirroot/local/lynda/tests/fixtures/mockcertficateofcompletionresponse.json"));
    }

    public function reset() {
        global $CFG;
        
        $this->getcoursesresponse = json_decode(file_get_contents("$CFG->dirroot/local/lynda/tests/fixtures/mockcoursesresponse.json"));
        $this->individualusagedetailresponse = json_decode(file_get_contents("$CFG->dirroot/local/lynda/tests/fixtures/mockindividualusagedetailresponse.json"));
        $this->certficateofcompletionresponse = json_decode(file_get_contents("$CFG->dirroot/local/lynda/tests/fixtures/mockcertficateofcompletionresponse.json"));
    }

    public function useupdatedcourses() {
        global $CFG;
        $this->getcoursesresponse = json_decode(file_get_contents("$CFG->dirroot/local/lynda/tests/fixtures/mockcoursesresponse_withupdate.json"));
    }

    public function useupdatedindividualusagedetailresponse() {
        global $CFG;
        $this->updatedresponse = true;
        $this->individualusagedetailresponse = json_decode(file_get_contents("$CFG->dirroot/local/lynda/tests/fixtures/mockindividualusagedetailresponse_withupdate.json"));
    }

    public function dropcoursefromresponse() {
        array_shift($this->getcoursesresponse);
    }

    protected function setregion($regionid) {
        global $CFG;

        $this->region = $regionid;
        if ($this->updatedresponse) {
            $this->getcoursesresponse = json_decode(file_get_contents("$CFG->dirroot/local/lynda/tests/fixtures/mockcoursesresponse_withupdate.json"));
        } else if ($this->mockregions && ($regionid == 3 || $regionid ==4)) {
            $this->individualusagedetailresponse = json_decode(file_get_contents("$CFG->dirroot/local/lynda/tests/fixtures/mockindividualusagedetailresponse_region$regionid.json"));
        } else if ($this->mockregions) {
            $this->individualusagedetailresponse = json_decode('{"Total": 0,"ReportData":[]}');
        } else if (!$this->mockregions) {
            $this->individualusagedetailresponse = json_decode(file_get_contents("$CFG->dirroot/local/lynda/tests/fixtures/mockindividualusagedetailresponse.json"));
        }
        return true;
    }

    protected function setanyregion() {
        return true;
    }
}