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

namespace local_onlineappraisal\output\printer;

defined('MOODLE_INTERNAL') || die();

use stdClass;

class appraisal_legacy extends base {
    /**
     * Get extra context data.
     */
    protected function get_data() {
        // Format six month review date.
        $this->data->appraisal->six_month_review_date = empty($this->data->appraisal->six_month_review_date) ? get_string('pdf:notcomplete', 'local_onlineappraisal') : userdate($this->data->appraisal->six_month_review_date, get_string('strftimedate'));
        // Get legacy summaries.
        $this->get_summaries();
        // Get legacy objectives
        $this->get_objectives();
        // Get leraning history.
        $this->get_learning_history();
    }

    /**
     * Inject legacy summary info into data object.
     *
     * @global \moodle_database $DB
     */
    private function get_summaries() {
        global $DB;

        $summaries = array(
            'contribution_summary' => 'summary',
            'appraiser_summary' => 'summary',
            'appraisee_summary' => 'summary',
            'teamperformance_comments' => 'appraisal',
            'groupleader_comments' => 'summary',
            'six_month_review' => 'appraisal'
        );


        $params = array(
            'appraisalid' => $this->appraisal->id
        );
        $summaryrecord = $DB->get_record('local_appraisal_summary', $params);
        $this->data->summaries = array();
        foreach ($summaries as $field => $table) {
            $summary = new stdClass();
            $summary->title = get_string("z:legacy:pdf:summary:{$field}", 'local_onlineappraisal');
            switch ($table) {
                case 'appraisal' :
                    $summary->data = format_text($this->appraisal->{$field}, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
                    break;
                case 'summary' :
                    $summary->data = empty($summaryrecord->{$field}) ? null : format_text($summaryrecord->{$field}, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
                    break;
            }
            $this->get_summaries_extra_row($field, $summary);
            $this->data->summaries[] = clone($summary);
        }
    }

    /**
     * Inject extra row (if applicable) into summary object.
     * 
     * @param string $field
     * @param stdClass $summary
     */
    private function get_summaries_extra_row($field, $summary) {
        switch ($field) {
            case 'groupleader_comments' :
                $summary->extrarow = true;
                $summary->extrarowleft = fullname($this->appraisal->signoff);
                $summary->extrarowright = get_string('pdf:completed','local_onlineappraisal') . ' ' . $this->data->appraisal->completed_date;
                break;
            case 'six_month_review';
                $summary->extrarow = true;
                $summary->extrarowright = get_string('z:legacy:pdf:completed:sixmonthreview','local_onlineappraisal') . ' ' . $this->data->appraisal->six_month_review_date;
                break;
        }
    }

    /**
     * Inject legacy objectives into data object.
     *
     * @global \moodle_database $DB
     */
    private function get_objectives() {
        global $DB;

        $this->data->objectives = new stdClass();
        $this->data->objectives->lyper = array();
        $this->data->objectives->lydev = array();
        $this->data->objectives->nyper = array();
        $this->data->objectives->nydev = array();
        
        $statuses = $DB->get_records_menu('local_appraisal_obj_status', array(), '', 'id, status');
        
        $devobjectives = $DB->get_records('local_appraisal_dev_objectiv', array('appraisalid' => $this->appraisal->id));
        $perobjectives = $DB->get_records('local_appraisal_per_objectiv', array('appraisalid' => $this->appraisal->id));

        foreach ($devobjectives as $devobjective) {
            $destination = ($devobjective->previous_appraisal ? 'lydev' : 'nydev');
            $objective = new stdClass();
            $objective->title = format_text($devobjective->title, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
            $objective->competency = format_text($devobjective->competency, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
            $objective->description = format_text($devobjective->description, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
            $objective->action = format_text($devobjective->action_required, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
            $objective->duedate = $this->get_duedate($devobjective);
            $objective->status = empty($statuses[$devobjective->status]) ? $devobjective->status : $statuses[$devobjective->status];
            $objective->progress = format_text($devobjective->progress, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
            $objective->further = format_text($devobjective->further_development, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
            $objective->appraisercomment = format_text($devobjective->appraiser_comments, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
            $this->data->objectives->{$destination}[] = clone($objective);
        }

        foreach ($perobjectives as $perobjective) {
            $destination = ($perobjective->previous_appraisal ? 'lyper' : 'nyper');
            $objective = new stdClass();
            $objective->title = format_text($perobjective->title, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
            $objective->description = format_text($perobjective->description, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
            $objective->duedate = $this->get_duedate($perobjective);
            $objective->status = empty($statuses[$perobjective->status]) ? $perobjective->status : $statuses[$perobjective->status];
            $objective->appraiseecomments = format_text($perobjective->appraisee_comments, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
            $objective->appraisercomments = format_text($perobjective->appraiser_comments, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
            $this->data->objectives->{$destination}[] = clone($objective);
        }

        $this->data->objectives->haslyper = (bool) count($this->data->objectives->lyper);
        $this->data->objectives->haslydev = (bool) count($this->data->objectives->lydev);
        $this->data->objectives->hasnyper = (bool) count($this->data->objectives->nyper);
        $this->data->objectives->hasnydev = (bool) count($this->data->objectives->nydev);

        foreach (array('lyper', 'lydev', 'nyper', 'nydev') as $destination) {
            end($this->data->objectives->{$destination});
            $key = key($this->data->objectives->{$destination});
            if ($key) {
                $this->data->objectives->{$destination}[$key]->last = true;
            }
            reset($this->data->objectives->{$destination});
        }
    }

    /**
     * Get due date information for legacy objectives.
     * 
     * @param stdClass $objective
     * @return stdClass
     */
    private function get_duedate($objective) {
        $duedate = new stdClass();
        if ($objective->previous_appraisal && !$objective->previous_app_new_objective) {
            $duedate->text = get_string('z:legacy:pdf:objective:previous', 'local_onlineappraisal');
        } else {
            $duedate->text = get_string('z:legacy:pdf:objective:new', 'local_onlineappraisal');
        }
        $duedate->created = userdate($objective->created_date, get_string('strftimedate'));
        $duedate->due = empty($objective->due_date) ? get_string('pdf:notcomplete','local_onlineappraisal') : userdate($objective->due_date, get_string('strftimedate'), new \DateTimeZone('UTC')); // Always UTC (Set by old appraisal system).
        return $duedate;
    }
}
