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

class appraisal extends base {
    /**
     * Get extra context data.
     */
    protected function get_data() {
        // Get legacy summaries.
        $this->get_summaries();
        // Get forms.
        $this->get_forms();
        // Get check-ins.
        $this->get_checkins();
        // Get learning history.
        $this->get_learning_history();
        // Get activity logs
        $this->get_activity_logs();
    }

    /**
     * Inject summary info into data object.
     *
     * @global \moodle_database $DB
     */
    private function get_summaries() {
        global $DB;

        $this->data->summaries = array();

        $summaries = array(
            'appraiser',
            'recommendations',
            'appraisee',
            'signoff',
        );

        $params = array(
            'appraisalid' => $this->appraisal->id,
            'user_id' => $this->appraisal->appraisee->id,
            'form_name' => 'summaries',
        );

        $id = $DB->get_field('local_appraisal_forms', 'id', $params);
        $data = empty($id) ? array() : $DB->get_records('local_appraisal_data', array('form_id' => $id), '', 'name, type, data');

        // Esnure data is shown if exists, even if groupleader is no longer active (for whatever reason).
        if ($this->appraisal->groupleader || !empty($data['grpleader']->data)) {
            $summaries[] = 'grpleader';
        }

        $this->data->summaries = $this->get_fields($summaries, 'summaries', $data);

        $this->get_summaries_extra_rows($data);
    }

    /**
     * Inject extra rows (if applicable) into summary objects.
     *
     * @param array $data
     */
    private function get_summaries_extra_rows($data) {
        global $DB;

        foreach ($this->data->summaries as $summary) {
            switch ($summary->name) {
                case 'signoff' :
                    $summary->extrarow = true;
                    $summary->extrarowleft = fullname($this->appraisal->signoff);
                    $summary->extrarowright = get_string('pdf:completed','local_onlineappraisal') . ' ' . $this->data->appraisal->completed_date;
                    break;
                case 'grpleader' :
                    if (!empty($data['groupleaderid']->data)) {
                        $summary->extrarowleft = fullname($DB->get_record('user', array('id' => $data['groupleaderid']->data)));
                        $summary->extrarowright = empty($data['grpleadertimestamp']->data) ? '' : userdate($data['grpleadertimestamp']->data, get_string('strftimedate'));
                        $summary->extrarow = !empty($summary->extrarowleft);
                    }
                    break;
            }
        }
    }

    /**
     * Inject form info and data into data object.
     *
     * @global \moodle_database $DB
     */
    private function get_forms() {
        global $DB;

        $this->data->forms = array();

        // Forms and fields to print (in order).
        $forms = array(
            'lastyear' => array('appraiseereview', 'appraiserreview', 'appraiseedevelopment', 'appraiseefeedback'),
            'careerdirection' => array('mobility', 'progress', 'comments'),
            'impactplan' => array('impact', 'support', 'comments'),
            'development' => array('leadership', 'leadershiproles', 'leadershipattributes', 'seventy', 'twenty', 'ten', 'comments'),
        );

        $params = array(
            'appraisalid' => $this->appraisal->id,
            'user_id' => $this->appraisal->appraisee->id,
        );

        $count = 0;
        foreach ($forms as $name => $fields) {
            $count++;
            $form = new stdClass();
            $form->first = ($count == 1);
            $form->last = ($count == count($forms));
            $form->title = get_string("form:{$name}:title", 'local_onlineappraisal');

            $params['form_name'] = $name;
            $id = $DB->get_field('local_appraisal_forms', 'id', $params);
            $data = empty($id) ? array() : $DB->get_records('local_appraisal_data', array('form_id' => $id), '', 'name, type, data');

            $form->fields = $this->get_fields($fields, $name, $data);

            $this->data->forms[] = clone($form);
        }
    }

    /**
     * Get field information from loaded form data.
     *
     * @param array $fields
     * @param string $formname
     * @param array $formdata
     * @return array
     */
    private function get_fields($fields, $formname, $formdata) {
        $return = array();

        $count = 0;
        foreach ($fields as $name) {
            if (!$this->show_field($name, $formname, $formdata)) {
                continue;
            }
            $count++;
            $field = new stdClass();
            $field->name = $name;
            $field->first = ($count == 1);
            $field->last = ($count == count($fields));

            // Use PDF specific string if exists.
            $component = 'local_onlineappraisal';
            $str = "form:{$formname}:{$name}";
            $pdfstr = "pdf:{$str}";
            if (get_string_manager()->string_exists($pdfstr, $component)) {
                $field->title = get_string($pdfstr, $component);
            } else {
                $field->title = get_string($str, $component);
            }

            if (isset($formdata[$name])) {
                if ($formdata[$name]->type == 'array') {
                    $field->isarray = true;
                    $field->data = unserialize($formdata[$name]->data);
                    $count = 0;
                    foreach ($field->data as $index => $data) {
                        $count++;
                        $field->data[$index] = new stdClass();
                        $field->data[$index]->content = format_text($data, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
                        $field->data[$index]->last = ($count === count($field->data));
                    }
                } else {
                    $field->data = format_text($formdata[$name]->data, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
                }
            }
            $return[] = clone($field);
        }

        return $return;
    }

    private function show_field($fieldname, $formname, $formdata) {
        switch ($formname) {
            case 'development':
                switch ($fieldname) {
                    case 'leadershiproles':
                    case 'leadershipattributes':
                        if ($formdata['leadership']->data === get_string('form:development:leadership:answer:1', 'local_onlineappraisal')) {
                            return false;
                        }
                    break;
                }
            break;
        }
        return true;
    }

    /**
     * Inject comments/activity logs into data object.
     *
     * @global \moodle_database $DB
     */
    private function get_activity_logs() {
        global $DB;

        // Owner caching.
        $owners = array();

        $this->data->activitylogs = array();

        $records = $DB->get_records('local_appraisal_comment', array('appraisalid' => $this->appraisal->id), 'created_date ASC');

        $count = 0;
        foreach ($records as $record) {
            $count++;
            if (!isset($owners[$record->ownerid])) {
                $owners[$record->ownerid] = $DB->get_record('user', array('id' => $record->ownerid));
            }

            $comment = new stdClass();
            $comment->first = ($count == 0);
            $comment->last = ($count == count($records));
            $comment->name = ($owners[$record->ownerid])? fullname($owners[$record->ownerid]) : get_string('comment:system', 'local_onlineappraisal');
            $comment->role = !empty($record->user_type)? get_string($record->user_type, 'local_onlineappraisal'): '';
            $comment->comment = format_text($record->comment, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
            $comment->date = userdate($record->created_date, get_string('strftimedate'));

            $this->data->activitylogs[] = clone($comment);
        }

        $this->data->hasactivitylog = (bool) count($this->data->activitylogs);
    }

    /**
     * Inject check-in info into data object.
     *
     * @global \moodle_database $DB
     */
    private function get_checkins() {
        global $DB;

        // Owner caching.
        $owners = array();

        $this->data->checkins = array();

        $records = $DB->get_records('local_appraisal_checkins', array('appraisalid' => $this->appraisal->id), 'created_date ASC');

        $count = 0;
        foreach ($records as $record) {
            if (!isset($owners[$record->ownerid])) {
                $owners[$record->ownerid] = $DB->get_record('user', array('id' => $record->ownerid));
            }
            $count++;
            $checkin = new stdClass();
            $checkin->first = ($count == 0);
            $checkin->last = ($count == count($records));
            $checkin->name = fullname($owners[$record->ownerid]);
            $checkin->role = get_string($record->user_type, 'local_onlineappraisal');
            $checkin->checkin = format_text($record->checkin, FORMAT_PLAIN, array('filter' => 'false', 'nocache' => true));
            $checkin->date = userdate($record->created_date, get_string('strftimedate'));

            $this->data->checkins[] = clone($checkin);
        }
        $this->data->hascheckins = (bool) count($this->data->checkins);
    }
}
