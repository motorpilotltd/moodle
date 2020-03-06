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

require_once($CFG->libdir . '/pdflib.php');

/**
 * Wrapper class that extends Moodle wrapper of TCPDF (lib/tcpdf/tcpdf.php).
 */
class pdf extends \pdf {
    /**
     * Custom HTML for PDF header.
     * @var string $customheaderhtml
     */
    private $customheaderhtml = '';

    /**
     * Constructor.
     */
    public function __construct($orientation = 'P') {
        $orientation = in_array($orientation, ['P', 'L']) ? $orientation : 'P';
        parent::__construct($orientation, 'mm', 'A4', true, 'UTF-8');
    }

    /**
     * Set custom header HTML.
     *
     * @param string $html
     */
    public function set_customheaderhtml($html) {
        $this->customheaderhtml = $html;
    }

    /**
     * Write header.
     */
    public function Header() {
        global $CFG;

        if (preg_match('/[\x{4e00}-\x{9fa5}]+/u', $this->customheaderhtml)) {
            // Chinese.
            $this->SetFont('cid0cs', '', 10, $CFG->dirroot . '/local/onlineappraisal/fonts/cid0cs.php');
        } else if (preg_match('/[\x{4E00}-\x{9FBF}\x{3040}-\x{309F}\x{30A0}-\x{30FF}]/u', $this->customheaderhtml)) {
            // Japanese.
            $this->SetFont('cid0jp', '', 10, $CFG->dirroot . '/local/onlineappraisal/fonts/cid0jp.php');
        } else {
            $this->SetFont('freeserif', '', 10);
        }
        $this->writeHTML($this->customheaderhtml, true, false, true, false, '');
    }

    /**
     * Write footer.
     */
    public function Footer() {
        // Position at 15 mm from bottom.
        $this->SetY(-15);
        // Set font.
        $this->SetFont('freeserif', '', 8);
        // Page number.
        $this->Cell(0, 10, 'Page '.$this->getAliasNumPage().'/'.$this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M');
    }
}