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

namespace local_onlineappraisal\output\dashboard;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use renderer_base;
use moodle_url;

class checkin extends base {
    /**
     * Export this data so it can be used as the context for a mustache template.
     * @return stdClass
     */
    /**
     * Instance of checkins class.
     * @var \local_onlineappraisal\checkins $checkins
     */
    private $checkins;
    
    /**
     * Holds session key.
     * @var string $sesskey
     */
    private $sesskey;

    public function __construct(\local_onlineappraisal\appraisal $appraisal) {
        parent::__construct($appraisal);
        $this->checkins = new \local_onlineappraisal\checkins($this->appraisal->appraisal->id);
        $this->sesskey = sesskey();
    }

    public function export_for_template(renderer_base $output) {
        $data = new stdClass();
        $data->checkins = $this->checkins($output);
        $data->canadd = $this->appraisal->check_permission('checkin:add');
        $data->view = $this->appraisal->appraisal->viewingas;
        $data->appraisalid = $this->appraisal->appraisal->id;
        $data->sesskey = $this->sesskey;
        $checkinrecord = $this->editingcheckin();
        $data->editcheckinid = -1;
        $data->buttontext = get_string('form:add', 'local_onlineappraisal');
        if ($checkinrecord) {
            $data->editcheckinid = $checkinrecord->id;
            $data->editcheckin = $checkinrecord->unformattedcheckin;
            $data->buttontext = get_string('checkin:update', 'local_onlineappraisal');
        }
        $data->appraiseename = fullname($this->appraisal->appraisal->appraisee);
        $data->tagline = get_string('tagline', 'local_onlineappraisal', strtoupper($data->appraiseename));
        return $data;
    }

    /**
     * Returns the variables for the checkins stream.
     *
     * @param renderer_base $ouput
     * @return stdClass
     */
    private function checkins(renderer_base $output) {
        $checkins = $this->checkins->get_checkins();
        $params = array('page' => 'checkin',
            'appraisalid' => $this->appraisal->appraisal->id,
            'view' => $this->appraisal->appraisal->viewingas
            );
        foreach ($checkins as &$checkin) {
            $params['checkin'] = $checkin->id;
            $params['action'] = 'edit';
            $checkin->editurl = new moodle_url('/local/onlineappraisal/view.php', $params);
            $params['action'] = 'delete';
            $checkin->delurl = new moodle_url('/local/onlineappraisal/view.php', $params);
        }

        $template = new stdClass();
        $template->title = get_string('appraisee_checkin_title', 'local_onlineappraisal');
        $template->canadd = $this->appraisal->check_permission('checkin:add');
        $template->checkins = array_values($checkins);

        if ($template->canadd) {
            $appraisal = $this->appraisal->appraisal;
            $template->sesskey = $this->sesskey;
            $template->appraisalid = $appraisal->id;
            $template->view = $appraisal->viewingas;
        }

        return $template;
    }

    /**
     * Check if we are editing a checkin and get the original checkin text
     */
    private function editingcheckin() {
        global $SESSION;
        if (isset($SESSION->local_onlineappraisal->editcheckin)) {
            $checkinid = $SESSION->local_onlineappraisal->editcheckin;
            $checkin = $this->checkins->get_checkins($checkinid);
            unset($SESSION->local_onlineappraisal->editcheckin);
            return $checkin;
        }
    }
}
