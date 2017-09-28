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
use moodle_url;
use renderer_base;
use local_onlineappraisal\permissions as permissions;

class overview extends base {
    /**
     * Instance of comments class.
     * @var \local_onlineappraisal\comments $comments
     */
    private $comments;
    
    /**
     * Instance of stages class.
     * @var \local_onlineappraisal\stages $stages 
     */
    private $stages;

    /**
     * Holds session key.
     * @var string $sesskey
     */
    private $sesskey;

    /**
     * Constructor
     * 
     * @param \local_onlineappraisal\appraisal $appraisal
     */
    public function __construct(\local_onlineappraisal\appraisal $appraisal) {
        parent::__construct($appraisal);
        $this->comments = new \local_onlineappraisal\comments($appraisal->appraisal->id);
        $this->stages = new \local_onlineappraisal\stages($appraisal);
        $this->sesskey = sesskey();
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     * 
     * @global stdClass $SESSION
     * @param renderer_base $output
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        global $DB, $SESSION;

        $appraisal = $this->appraisal->appraisal;
        $data = new stdClass();
        $statusstring = 'status:' . $appraisal->statusid;
        $data->currentstatus = get_string($statusstring, 'local_onlineappraisal');
        $data->users = $this->get_user_data();
        $data->tagline = get_string(
                'tagline',
                'local_onlineappraisal',
                \core_text::strtoupper(fullname($appraisal->appraisee))
                );
        $data->progress = $this->progress();
        $data->overview = $this->overview();
        $data->comments = $this->comments($output);
        $lastsavedsql = <<<EOS
SELECT
    MAX(modified.lastsaved)
FROM (
  SELECT timemodified as lastsaved FROM {local_appraisal_forms} WHERE appraisalid = ?
  UNION
  SELECT modified_date as lastsaved FROM {local_appraisal_appraisal} WHERE id = ?
  ) as modified
EOS;
        $lastsaved = $DB->get_field_sql($lastsavedsql, [$appraisal->id, $appraisal->id]);
        $data->lastsaved = $lastsaved ? userdate($lastsaved, get_string('strftimedate')) : get_string('overview:lastsaved:never', 'local_onlineappraisal');

        if ($appraisal->archived) {
            // Even if a legacy appraisal archived message takes priority.
            $data->isarchived = true;
        } elseif ($appraisal->legacy) {
            $data->islegacy = true;
        }
        
        if (!empty($SESSION->local_onlineappraisal->overviewmessage)) {
            $data->overviewmessage = new stdClass();
            $data->overviewmessage->result = $SESSION->local_onlineappraisal->overviewmessage->result;
            $data->overviewmessage->text = $SESSION->local_onlineappraisal->overviewmessage->text;
            // Will be available if not redirected (which means there was an early error).
            $data->overviewcomment = trim(optional_param('comment', '', PARAM_RAW));
            // Clear message.
            unset($SESSION->local_onlineappraisal->overviewmessage);
        }
        return $data;
    }

    /**
     * Gets the data for users.
     *
     * @global stdClass $PAGE
     * @return stdClass
     */
    private function get_user_data() {
        global $PAGE;

        $appraisal = $this->appraisal->appraisal;

        $types = array('appraisee', 'appraiser', 'signoff');
        if (!empty($appraisal->groupleader)) {
            $types[] = 'groupleader';
        }

        $users = array();
        
        foreach ($types as $type) {
            $user = new stdClass();
            $user->type = $type;
            $userpicture = new \user_picture($appraisal->{$type});
            $user->imageurl = $userpicture->get_url($PAGE)->out(false);
            $user->fullname = fullname($appraisal->{$type});
            $user->role = get_string($type, 'local_onlineappraisal');
            $users[] = $user;
        }

        return $users;
    }

    /**
     * Returns the variables for the progess panel given the user type, status and face to face status.
     *
     * @global stdClass $CFG
     * @return stdClass
     */
    private function progress() {
        global $CFG;

        $appraisal = $this->appraisal->appraisal;

        // Necessary due to numbering being different on graphic compared to actual status.
        $status = $appraisal->statusid - 1;

        $progressvars = new stdClass();

        $svgclasses = array(
            'progress-svg',
            "progress-svg-{$appraisal->viewingas}"
        );
        if ($appraisal->face_to_face_held) {
            $svgclasses[] = 'progress-svg-f2f';
        }
        if (!empty($appraisal->groupleader)) {
            // Signifies groupleader active.
            $svgclasses[] = 'progress-svg-gla';
            $progressvars->svg = file_get_contents($CFG->dirroot.'/local/onlineappraisal/pix/progress_extra.svg');
        } else {
            $progressvars->svg = file_get_contents($CFG->dirroot.'/local/onlineappraisal/pix/progress.svg');
        }
        $svgclasses[] = "progress-svg-{$status}";
        $progressvars->class = implode(' ', $svgclasses);
        return $progressvars;
    }

    /**
     * Returns the variable for the overview and associated action buttons.
     *
     * @return stdClass
     */
    private function overview() {
        global $USER;

        $appraisal = $this->appraisal->appraisal;

        // Extra variables for language strings.
        $appraisal->formattedcreateddate = userdate($appraisal->created_date, get_string('strftimedate'));
        $printurl = new moodle_url('/local/onlineappraisal/print.php', array('appraisalid' => $appraisal->id, 'view' => $appraisal->viewingas, 'print' => 'appraisal'));
        $appraisal->printappraisalurl = $printurl->out(false);
        $printurl->param('print', 'feedback');
        $appraisal->printfeedbackurl = $printurl->out(false);
        foreach (array('appraisee', 'appraiser', 'signoff') as $type) {
            $styled = "styled{$type}name";
            $plain = "plain{$type}name";
            $appraisal->{$styled} = \html_writer::span(fullname($appraisal->{$type}), "oa-{$type}");
            $appraisal->{$plain} = fullname($appraisal->{$type});
        }

        $strman = get_string_manager();

        $overview = new stdClass();
        $contentidentifier = "overview:content:{$appraisal->viewingas}:{$appraisal->statusid}";
        if ($appraisal->permissionsid != $appraisal->statusid) {
            $altcontentidentifier = "overview:content:{$appraisal->viewingas}:{$appraisal->statusid}:{$appraisal->permissionsid}";
            if ($strman->string_exists($altcontentidentifier, 'local_onlineappraisal')) {
                $contentidentifier = $altcontentidentifier;
            }
        }
        if ($appraisal->archived || $appraisal->legacy) {
            // If both then archived message takes priority.
            $identifier = ($appraisal->archived ? 'archived' : 'legacy');
            $contentidentifier = "overview:content:special:{$identifier}";
            // Check for special case language strings based on user type.
            $altcontentidentifier = $contentidentifier . ":{$appraisal->viewingas}";
            if ($strman->string_exists($altcontentidentifier, 'local_onlineappraisal')) {
                $contentidentifier = $altcontentidentifier;
            }
            // Check for special case language strings based on user type _and_ *permissions* status.
            $altcontentidentifier = $contentidentifier . ":{$appraisal->viewingas}:{$appraisal->permissionsid}";
            if ($strman->string_exists($altcontentidentifier, 'local_onlineappraisal')) {
                $contentidentifier = $altcontentidentifier;
            }
        }

        // Special messages when the groupleader can add a summary.
        if ($appraisal->groupleader && $appraisal->statusid == 7) {
            $contentidentifier = "overview:content:{$appraisal->viewingas}:{$appraisal->statusid}:groupleadersummary";
            $contentidentifier .= ($appraisal->viewingas == 'groupleader' && $appraisal->groupleader->id != $USER->id) ? ':generic' : '';
        }
        
        $overview->buttons = $this->overview_buttons();

        $overview->content = get_string($contentidentifier, 'local_onlineappraisal', $appraisal);
        $overview->sesskey = $this->sesskey;
        $overview->appraisalid = $appraisal->id;
        $overview->view = $appraisal->viewingas;

        $overview->hasbuttons = !empty($overview->buttons);
        return $overview;
    }

    /**
     * Returns the relevant button HTML for the current overview
     *
     * @return array
     */
    private function overview_buttons() {
        global $USER;
        
        $appraisal = $this->appraisal->appraisal;
        $viewingas = $appraisal->viewingas;
        $currentstatus = $appraisal->statusid;

        // Can the current user update from the current status?
        // NB. This is based on status id not permissions id as is related to who 'owns' the appraisal at this stage.
        $canupdate = permissions::is_allowed('appraisal:update', $currentstatus, $viewingas, $appraisal->archived, $appraisal->legacy);
        if ($canupdate && $currentstatus == APPRAISAL_COMPLETE) {
            // Only groupleader can possibly update here.
            $canupdate = $appraisal->groupleader && $appraisal->groupleader->id === $USER->id;
        }
        $appraisersignoff = (
                $viewingas === 'appraiser'
                && $appraisal->appraiser->id === $appraisal->signoff->id
                && permissions::is_allowed('appraisal:update', $currentstatus, 'signoff', $appraisal->archived, $appraisal->legacy)
                );
        if (!$canupdate && !$appraisersignoff) {
            return array();
        }

        $buttons = array();

        $basebutton = new stdClass();
        $basebutton->type = 'submit';
        $basebutton->classes = array('btn');
        $basebutton->text = '';
        $basebutton->title = array();
        $basebutton->data = array();

        if ($canupdate) {
            $returnstatus = $this->stages->get_update_path('return');
            if ($returnstatus) {
                $returnbutton = clone($basebutton);
                $returnbutton->class[] = 'btn-default';
                $returnstr = "overview:button:{$viewingas}:{$currentstatus}:return";
                $returnbutton->text = get_string($returnstr, 'local_onlineappraisal', $appraisal);
                $returnbutton->data[] = array('name' => 'return', 'value' => $returnstatus);
                $returnlabelstr = "overview:label:comment:return";
                $returnbutton->data[] = array('name' => 'label', 'value' => get_string($returnlabelstr, 'local_onlineappraisal'));
                $buttons[] = clone($returnbutton);
            }

            // Clear errors from return button validation.
            $this->stages->clear_errors();
            $submitstatus = $this->stages->get_update_path('submit');
            if ($submitstatus) {
                $submitbutton = clone($basebutton);
                $submitbutton->class[] = 'btn-primary';
                $submitstr = "overview:button:{$viewingas}:{$currentstatus}:submit";
                $submitbutton->text = get_string($submitstr, 'local_onlineappraisal', $appraisal);

                $validated = $this->stages->validate($submitstatus);

                $submitbutton->data[] = array('name' => 'validated', 'value' => $validated);
                $submitbutton->data[] = array('name' => 'submit', 'value' => $submitstatus);
                $submitlabelstr = "overview:label:comment:submit";
                $submitbutton->data[] = array('name' => 'label', 'value' => get_string($submitlabelstr, 'local_onlineappraisal'));
                $submitbutton->title = $this->stages->errors;
                $buttons[] = clone($submitbutton);
            }
        }

        // Extra buttons
        if ($currentstatus == APPRAISEE_DRAFT && !$validated) {
            // When beginning and no data yet entered.
            $extrabutton = clone($basebutton);
            $extrabutton->class[] = 'btn-success';
            $extrastr = "overview:button:{$viewingas}:{$currentstatus}:extra";
            $extrabutton->text = get_string($extrastr, 'local_onlineappraisal', $appraisal);
            $extrabutton->islink = true;
            $extrabutton->href = (new \moodle_url('/local/onlineappraisal/view.php', array('appraisalid' => $appraisal->id, 'page' => 'userinfo', 'view' => 'appraisee')))->out(false);
            // Stick it first.
            array_unshift($buttons, clone($extrabutton));
        }
        if ($appraisersignoff) {
            // When beginning and no data yet entered.
            $extrabutton = clone($basebutton);
            $extrabutton->class[] = 'btn-primary';
            $extrastr = "overview:button:{$viewingas}:{$currentstatus}:extra";
            $extrabutton->text = get_string($extrastr, 'local_onlineappraisal', $appraisal);
            $extrabutton->islink = true;
            $extrabutton->href = (new \moodle_url('/local/onlineappraisal/view.php', array('appraisalid' => $appraisal->id, 'page' => 'summaries', 'view' => 'signoff')))->out(false);
            $buttons[] = clone($extrabutton);
        }

        foreach ($buttons as &$button) {
            // Can't have double quotes inside double quotes!
            $button->title = str_ireplace('"', "'", $button->title);
            // Check for title and set tooltip and disable.
            if (!empty($button->title)) {
                $button->hastitle = true;
                $button->data[] = array('name' => 'toggle', 'value' => 'tooltip');
                $button->disabled = true;
            }
        }

        return $buttons;
    }

    /**
     * Returns the variables for the comments stream.
     *
     * @param renderer_base $output
     * @return stdClass
     */
    private function comments(renderer_base $output) {
        $comments = $this->comments->get_comments();
        $commentsvars = new stdClass();
        $commentsvars->canadd = $this->appraisal->check_permission('comments:add'); // Need to enable $appraisal->can_edit functionality.
        // Reset array indices as necessary for templating.
        $commentsvars->comments = array_values($comments);

        if ($commentsvars->canadd) {
            $appraisal = $this->appraisal->appraisal;
            $commentsvars->sesskey = $this->sesskey;
            $commentsvars->appraisalid = $appraisal->id;
            $commentsvars->view = $appraisal->viewingas;
        }

        return $commentsvars;
    }
}
