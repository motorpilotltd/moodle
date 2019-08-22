<?php
// This file is part of the Arup online appraisal system
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

use Exception;
use moodle_exception;

class printer {

    /**
     * Appraisal object.
     * @var \local_onlineappraisal\appraisal $appraisal
     */
    private $appraisal;

    /**
     * PDF class.
     * @var \local_onlineappraisal\pdf $pdf
     */
    private $pdf;

    /**
     * What to print.
     * @var string $print
     */
    private $print;

    /**
     * What can be printed.
     * @var array $canprint
     */
    private $canprint = [
        'appraisal' => 'P',
        'feedback' => 'P',
        'successionplan' => 'P',
        'leaderplan' => 'P',
        'leadershipattributes' => 'L'
    ];

    /**
     * Error message.
     * @var string $error
     */
    private $error;

    /**
     * Constructor.
     *
     * Make sure no renderer functions are being called in the constructor
     *
     * @param \local_onlineappraisal\appraisal $appraisal Appraisal object.
     * @param string $print What to print.
     */
    public function __construct(\local_onlineappraisal\appraisal $appraisal, $print) {
        $this->appraisal = $appraisal;
        $this->print = array_key_exists($print, $this->canprint) ? $print : 'appraisal';

        // Check if this user is allowed to print.
        if (!$this->can_print()) {
            throw new moodle_exception('error:noaccess', 'local_onlineappraisal');
        }

        $this->pdf = new \local_onlineappraisal\pdf($this->canprint[$this->print]);
        $this->pdf->SetProtection(['modify', 'annot-forms', 'fill-forms', 'extract', 'assemble']);
    }

    /**
     *  Magic getter.
     *
     * @param string $name
     * @return mixed property
     * @throws Exception
     */
    public function __get($name) {
        if (method_exists($this, "get_{$name}")) {
            return $this->{"get_{$name}"}();
        }
        if (!isset($this->{$name})) {
            throw new Exception('Undefined property ' .$name. ' requested');
        }
        return $this->{$name};
    }

    /**
     * Check permissions for printing.
     *
     * @return bool true if user can print.
     */
    private function can_print() {
        $canprint = false;
        switch ($this->print) {
            case 'leadershipattributes':
                $canprint = true;
                break;
            case 'feedback':
                $canprint = $this->appraisal->check_permission("feedback:print") || $this->appraisal->check_permission("feedbackown:print");
                break;
            default:
                $canprint = $this->appraisal->check_permission("{$this->print}:print");
                break;
        }
        return $canprint;
    }

    /**
     * Generate and output PDF.
     *
     * @global \moodle_page $PAGE
     */
    public function pdf() {
        global $CFG, $PAGE, $SESSION;

        $appraisal = $this->appraisal->appraisal;

        $renderer = $PAGE->get_renderer('local_onlineappraisal', 'printer');

        // Always in English.
        force_current_language('en');
        $header = new \local_onlineappraisal\output\printer\header($this);
        $headerhtml = $renderer->render($header);
        // Restore language.
        unset($SESSION->forcelang);
        moodle_setlocale();

        // Do we need a legacy renderer?
        $print = ($this->print == 'appraisal' && $appraisal->legacy) ? 'appraisal_legacy' : $this->print;
        $class = "\\local_onlineappraisal\\output\\printer\\{$print}";
        $content = new $class($this);
        $contenthtml = $renderer->render($content);

        switch ($this->print) {
            case 'leadershipattributes':
                $this->pdf->set_customheaderhtml('');
                $this->pdf->SetMargins(10, 10, 10);
                break;
            default:
                $this->pdf->set_customheaderhtml($headerhtml);
                $this->pdf->SetMargins(10, 45, 10);
                break;
        }

        $this->pdf->AddPage();
        if (preg_match('/[\x{4e00}-\x{9fa5}]+/u', $contenthtml)) {
            // Chinese.
            $this->pdf->SetFont('cid0cs', '', 12, $CFG->dirroot . '/local/onlineappraisal/fonts/cid0cs.php');
        } else if (preg_match('/[\x{4E00}-\x{9FBF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}]/u', $contenthtml)) {
            // Japanese.
            $this->pdf->SetFont('cid0jp', '', 12, $CFG->dirroot . '/local/onlineappraisal/fonts/cid0jp.php');
        } else {
            $this->pdf->SetFont('freeserif', '', 12);
        }

        $this->pdf->SetTextColor(25, 25, 75);

        $this->pdf->writeHTML($contenthtml, true, false, true, false, '');

        $filenamepieces = array(
            $appraisal->id,
            fullname($appraisal->appraisee),
            get_string('appraisal', 'local_onlineappraisal'),
        );
        if ($this->print != 'appraisal') {
            $filenamepieces[] = $this->print;
        }
        $filename = str_replace(' ', '_', strtolower(trim(clean_param(implode('_', $filenamepieces), PARAM_FILE))));

        $inline = optional_param('inline', false, PARAM_BOOL);
        if ($this->pdf->Output("{$filename}.pdf", ($inline ? 'I' : 'D'))) {
            // If output OK trigger event and exit.
            $class = "\\local_onlineappraisal\\event\\appraisal_{$appraisal->viewingas}_printed";
            if (!class_exists($class)) {
                $class = "\\local_onlineappraisal\\event\\appraisal_printed";
            }
            $eventdata = array(
                'objectid' => $appraisal->id,
                'relateduserid' => $appraisal->appraisee->id,
                'other' => array(
                    'type' => core_text::strtoupper($appraisal->viewingas),
                ),
            );
            $event = call_user_func(array($class, 'create'), $eventdata);
            if ($event) {
                $event->trigger();
            }
            exit;
        }

    }
}