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

use stdClass;
use moodle_url;
use local_onlineappraisal\stages as stages;

class overview {

    private $appraisal;

    /**
     * Constructor.
     *
     * @param object $appraisal the full appraisal object
     */
    public function __construct(\local_onlineappraisal\appraisal $appraisal) {
        $this->appraisal = $appraisal;
    }

    /**
     * Hook
     *
     * This function is called from the main appraisal controller when the page
     * is loaded. This function can be added to all the other page types as long as this
     * class is being declared in \local_onlineappraisal\appraisal->add_page();
     *
     * @global \stdClass $SESSION
     * @return void
     */
    public function hook() {
        global $SESSION;

        // Only act if on overview page.
        if ($this->appraisal->page != 'overview') {
            return;
        }

        $action = optional_param('overviewaction', '', PARAM_ALPHANUMEXT);
        $button = optional_param('button', '', PARAM_ALPHA);
        $comment = trim(optional_param('comment', '', PARAM_RAW));

        if ($action == 'update_stage' && in_array($button, array('submit', 'return'))) {
            require_sesskey();

            $stages = new stages($this->appraisal);
            $result = $stages->update_direction($button, $comment);

            if (!isset($SESSION->local_onlineappraisal)) {
                $SESSION->local_onlineappraisal = new stdClass();
            }
            $SESSION->local_onlineappraisal->overviewmessage = new stdClass();
            $SESSION->local_onlineappraisal->overviewmessage->redirected = $stages->redirect;

            if (!$result) {
                $SESSION->local_onlineappraisal->overviewmessage->result = 'danger';
                $SESSION->local_onlineappraisal->overviewmessage->text = $stages->get_errors();
            } else {
                $SESSION->local_onlineappraisal->overviewmessage->result = 'success';
                // To match error return for template.
                $text = new stdClass();
                $text->message = get_string('success:appraisal:update', 'local_onlineappraisal');
                $text->first = $text->last = true;
                $SESSION->local_onlineappraisal->overviewmessage->text = array($text);
            }

            if ($stages->redirect === true) {
                $redirecturl = new moodle_url(
                        '/local/onlineappraisal/view.php',
                        array(
                            'appraisalid' => $this->appraisal->appraisal->id,
                            'view' => $this->appraisal->appraisal->viewingas,
                            'page' => $this->appraisal->page,
                        ));
                redirect($redirecturl);
            }
        }
    }
}
