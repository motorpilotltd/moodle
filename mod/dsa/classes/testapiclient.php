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
 * @author Andrew Hancox <andrewdchancox@googlemail.com>
 * @package mod
 * @subpackage dsa
 */

namespace mod_dsa;

class testapiclient extends apiclient {
    public static $idnumbertoreturn = '';

    public function checkforupdate($time, $lastrun) {
        if (empty(self::$idnumbertoreturn)) {
            return [];
        }

        $curlresponse =
                '{
  "timePeriodMins": "180",
  "afterUtcTimestamp": "2018-09-24 14:06:54",
  "userCount": 8,
  "staffNumbers": [
    {
      "StaffNumber": "' . self::$idnumbertoreturn . '"
    }
  ],
  "message": "StaffNumbers having assessments with updates in the last 180 minutes"
}';

        $response = json_decode($curlresponse);
        return $response->staffNumbers;
    }

    const DSA_TESTSTATE_ALLDONE = 0;
    const DSA_TESTSTATE_ONEINPROGRESS = 10;
    const DSA_TESTSTATE_NOASSESSMENTS = 20;

    public static $teststate;

    public function staffprogress($staffid) {
        if (self::$teststate == self::DSA_TESTSTATE_ALLDONE) {
            $curlresponse = '{
  "assessmentCount": 2,
  "assessmentDetails": [
    {
      "AssessmentID": "2936",
      "State": "closed",
      "Started": "2014-05-30 10:56:33",
      "Completed": "2014-05-30 10:57:00",
      "Closed": "2014-05-30 10:57:00",
      "LocationCode": "4.13",
      "OfficeName": "London Office",
      "FirstName": "Martin",
      "LastName": "Cramp",
      "EmailAddress": "martin.cramp@arup.com",
      "MachineIdentification": "172.26.3.224",
      "Reason": "Hot desk user",
      "AssessorFirstName": null,
      "AssessorLastName": null,
      "AssessorEmailAddress": null,
      "AssessorPhoneNumber": null,
      "Status": "Compliant"
    },
    {
      "AssessmentID": "2937",
      "State": "abandoned",
      "Started": "2014-05-30 10:56:33",
      "Completed": "2014-05-30 10:57:00",
      "Closed": "2014-05-30 10:57:00",
      "LocationCode": "4.13",
      "OfficeName": "London Office",
      "FirstName": "Martin",
      "LastName": "Cramp",
      "EmailAddress": "martin.cramp@arup.com",
      "MachineIdentification": "172.26.3.224",
      "Reason": "Hot desk user",
      "AssessorFirstName": null,
      "AssessorLastName": null,
      "AssessorEmailAddress": null,
      "AssessorPhoneNumber": null,
      "Status": "Compliant"
    }
  ],
  "message": "Assessment history for user with staff number 13899"
}';
        } else if (self::$teststate == self::DSA_TESTSTATE_ONEINPROGRESS) {
            $curlresponse = '{
  "assessmentCount": 3,
  "assessmentDetails": [
    {
      "AssessmentID": "2936",
      "State": "closed",
      "Started": "2014-05-30 10:56:33",
      "Completed": "2014-05-30 10:57:00",
      "Closed": "2014-05-30 10:57:00",
      "LocationCode": "4.13",
      "OfficeName": "London Office",
      "FirstName": "Martin",
      "LastName": "Cramp",
      "EmailAddress": "martin.cramp@arup.com",
      "MachineIdentification": "172.26.3.224",
      "Reason": "Hot desk user",
      "AssessorFirstName": null,
      "AssessorLastName": null,
      "AssessorEmailAddress": null,
      "AssessorPhoneNumber": null,
      "Status": "Compliant"
    },
    {
      "AssessmentID": "2937",
      "State": "open",
      "Started": "2014-05-30 10:56:33",
      "Completed": "2014-05-30 10:57:00",
      "Closed": "2014-05-30 10:57:00",
      "LocationCode": "4.13",
      "OfficeName": "London Office",
      "FirstName": "Martin",
      "LastName": "Cramp",
      "EmailAddress": "martin.cramp@arup.com",
      "MachineIdentification": "172.26.3.224",
      "Reason": "Hot desk user",
      "AssessorFirstName": null,
      "AssessorLastName": null,
      "AssessorEmailAddress": null,
      "AssessorPhoneNumber": null,
      "Status": "Compliant"
    },
    {
      "AssessmentID": "2938",
      "State": "abandoned",
      "Started": "2014-05-30 10:56:33",
      "Completed": "2014-05-30 10:57:00",
      "Closed": "2014-05-30 10:57:00",
      "LocationCode": "4.13",
      "OfficeName": "London Office",
      "FirstName": "Martin",
      "LastName": "Cramp",
      "EmailAddress": "martin.cramp@arup.com",
      "MachineIdentification": "172.26.3.224",
      "Reason": "Hot desk user",
      "AssessorFirstName": null,
      "AssessorLastName": null,
      "AssessorEmailAddress": null,
      "AssessorPhoneNumber": null,
      "Status": "Compliant"
    }
  ],
  "message": "Assessment history for user with staff number 13899"
}';
        } else if (self::$teststate == self::DSA_TESTSTATE_NOASSESSMENTS) {
            $curlresponse = '{
  "assessmentCount": 0,
  "assessmentDetails": [
  ],
  "message": "Assessment history for user with staff number 13899"
}';
        }

        $response = json_decode($curlresponse);

        return $response->assessmentDetails;
    }
}