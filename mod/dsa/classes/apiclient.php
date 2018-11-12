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

class apiclient {
    public function __construct() {
        $this->config = get_config('mod_dsa');
    }

    public function staffprogress($staffid) {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');

        $basic_hash = base64_encode($this->config->username . ':' . $this->config->key);
        $authheader = "Basic {$basic_hash}";

        $url = "/api/progress/{$staffid}";

        $curl = new \curl();
        $curl->setHeader('Authorization: ' . $authheader);
        $curlresponse = $curl->get($this->config->apihost . $url, ['format' => 'json']);

        $response = json_decode($curlresponse);

        if ($response === null) {
            throw new \Exception('Unable to parse response from webservice');
        }

        return $response->assessmentDetails;
    }

    public function checkforupdate($time, $lastrun) {
        global $CFG;

        require_once($CFG->libdir . '/filelib.php');

        $basic_hash = base64_encode($this->config->username . ':' . $this->config->key);
        $authheader = "Basic {$basic_hash}";

        $minutes = ($time - $lastrun) / MINSECS;

        $url = "/api/updates/$minutes";

        $curl = new \curl();
        $curl->setHeader('Authorization: ' . $authheader);
        $curlresponse = $curl->get($this->config->apihost . $url, ['format' => 'json']);

        $response = json_decode($curlresponse);

        if ($response === null) {
            throw new \Exception('Unable to parse response from webservice');
        }

        return $response->staffNumbers;
    }
}