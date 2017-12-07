<?php
/**
 * @package    local_lynda
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_lynda;

class lyndaapimock extends lyndaapi {
    public function getcourses($start) {
        return array_slice($this->response, $start, 2);
    }

    private $response;
    public function __construct() {
        global $CFG;

        parent::__construct();

        $this->response = json_decode(file_get_contents("$CFG->dirroot/local/lynda/tests/fixtures/mockresponse.json"));
    }

    public function reset() {
        global $CFG;
        
        $this->response = json_decode(file_get_contents("$CFG->dirroot/local/lynda/tests/fixtures/mockresponse.json"));
    }

    public function dropcoursefromresponse() {
        array_shift($this->response);
    }
}