<?php
// This file is part of the register plugin for Moodle
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
 * @package     local_coursemanager
 * @copyright   2017 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author     Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursemanager;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/csvlib.class.php');
use moodle_exception;
use html_writer;
use stdClass;

defined('MOODLE_INTERNAL') || die();
require_once($CFG->libdir . '/weblib.php');

class csvimporttracker {

    /**
     * @var array columns to display.
     */
    protected $columns = array(
        "staffid",
        "classname",
        "classcompletiondate",
        "location",
        "provider",
        "duration",
        "durationunits",
        "classtype",
        "classcategory",
        "healthandsafetycategory",
        "classcost",
        "classcostcurrency",
        "classstarttime",
        "certificateno",
        "expirydate",
        "learningdesc"
        );

    /**
     * @var int row number.
     */
    protected $rownb = 0;

    /**
     * @var int chosen output mode.
     */
    protected $outputmode;

    /**
     * @var object output buffer.
     */
    protected $buffer;

    /**
     * Constructor.
     *
     * @param int $outputmode desired output mode.
     */
    public function __construct() {
    }

    /**
     * Finish the output.
     *
     * @return void
     */
    public function finish() {
        global $OUTPUT;
        $template = new stdClass();
        echo $OUTPUT->render_from_template('local_coursemanager/csvupload_finish', $template);
    }

    /**
     * Output one more line.
     *
     * @param int $line line number.
     * @param bool $outcome success or not?
     * @param array $status array of statuses.
     * @param array $data extra data to display.
     * @return void
     */
    public function output($line, $outcome, $status, $data) {
        global $OUTPUT;
        $template = new stdClass();

        $ci = 0;
        $this->rownb++;
        $highlight = [];
        if (is_array($status)) {
            $highlight = array_keys($status);
            $status = '<br>' . implode(html_writer::empty_tag('br'), $status);
        }
        if ($outcome) {
            $outcome = $OUTPUT->pix_icon('i/valid', '');
        } else {
            $outcome = $OUTPUT->pix_icon('i/invalid', '');
        }

        $template->values = array();
        $template->rowclass = 'r' . $this->rownb % 2;

        $value = new stdClass();
        $value->value = $line;
        $value->class = 'c' . $ci++;
        $template->values[] = $value;

        $value = new stdClass();
        $value->value = $outcome . $status;
        $value->class = 'c' . $ci++;
        $template->values[] = $value;
        foreach ($this->columns as $col) {
            $value = new stdClass();
            if (isset($data[$col])) {
                $value->value = $data[$col];
            } else {
                $value->value = '-';
            }

            if (in_array($col, $highlight)) {
                $value->value = html_writer::tag('span', $value->value, array('class' => 'alert-warning'));
            }
            $value->class = 'c' . $ci++;
            $template->values[] = $value;
        }

        echo $OUTPUT->render_from_template('local_coursemanager/csvupload', $template);
    }


    /**
     * Start the output.
     *
     * @return void
     */
    public function start() {
        global $OUTPUT;
        $template = new stdClass();
        $template->heading = array();

        $ci = 0;

        $head = new stdClass();
        $head->name = get_string('form:csv:csvline', 'local_coursemanager');
        $head->class = 'c' . $ci++;
        $template->heading[] = $head;


        $head = new stdClass();
        $head->name = get_string('form:csv:result', 'local_coursemanager');
        $head->class = 'c' . $ci++;
        $template->heading[] = $head;

        foreach ($this->columns as $col) { 
            $head = new stdClass();
            $head->name = get_string('form:csv:'. $col, 'local_coursemanager');
            $head->class = 'c' . $ci++;
            $template->heading[] = $head;
        }
        
        echo $OUTPUT->render_from_template('local_coursemanager/csvupload_start', $template);
    }
}
