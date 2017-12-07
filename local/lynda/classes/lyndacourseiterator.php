<?php
/**
 * @package    local_lynda
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_lynda;

class lyndacourseiterator implements \Iterator {
    private $position;
    private $currentpage;
    private $positionoffset;

    private $api;

    public function __construct($api = null) {
        if ($api == null) {
            $this->api = new lyndaapi();
        } else {
            $this->api = $api;
        }
    }

    public function rewind() {
        $this->position = 0;
        $this->positionoffset = 0;
        $this->currentpage = $this->api->getcourses(0);
    }

    public function current() {
        return $this->currentpage[$this->position - $this->positionoffset];
    }

    public function key() {
        return $this->position;
    }

    public function next() {
        ++$this->position;
    }

    public function valid() {
        if(!isset($this->currentpage[$this->position - $this->positionoffset])) {
            $this->positionoffset = $this->position;
            $this->currentpage = $this->api->getcourses($this->position);
        }

        return isset($this->currentpage[$this->position - $this->positionoffset]);
    }
}