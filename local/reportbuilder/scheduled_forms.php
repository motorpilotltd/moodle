<?php
/*
 * This file is part of T0tara LMS
 *
 * Copyright (C) 2010 onwards T0tara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Alastair Munro <alastair.munro@t0taralms.com>
 * @package t0tara
 * @subpackage reportbuilder
 */

/**
 * Moodle Formslib templates for scheduled reports settings forms
 */

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once($CFG->dirroot . '/lib/formslib.php');
require_once($CFG->dirroot . '/calendar/lib.php');
require_once($CFG->dirroot . '/lib/externallib.php');

/**
 * Formslib template for the new report form
 */
class scheduled_reports_new_form extends moodleform {
    function definition() {
        global $DB;

        $mform =& $this->_form;
        $id = $this->_customdata['id'];
        $frequency = $this->_customdata['frequency'];
        $schedule = $this->_customdata['schedule'];
        $format = $this->_customdata['format'];
        $ownerid = $this->_customdata['ownerid'];
        $report = $this->_customdata['report'];
        $savedsearches = $this->_customdata['savedsearches'];
        $exporttofilesystem = $this->_customdata['exporttofilesystem'];
        $context = context_system::instance();

        $mform->addElement('hidden', 'id', $id);
        $mform->setType('id', PARAM_INT);
        $mform->addElement('hidden', 'reportid', $report->_id);
        $mform->setType('reportid', PARAM_INT);

        // Export type options.
        $exportformatselect = reportbuilder_get_export_options($format, false);

        $exporttofilesystemenabled = false;
        if (get_config('reportbuilder', 'exporttofilesystem') == 1) {
            $exporttofilesystemenabled = true;
        }

        $mform->addElement('header', 'general', get_string('scheduledreportsettings', 'local_reportbuilder'));
        $mform->addElement('static', 'report', get_string('report', 'local_reportbuilder'), format_string($report->fullname));
        if (empty($savedsearches)) {
            $mform->addElement('static', '', get_string('data', 'local_reportbuilder'),
                    html_writer::div(get_string('scheduleneedssavedfilters', 'local_reportbuilder', $report->report_url()),
                            'notifyproblem'));
        } else {
            $mform->addElement('select', 'savedsearchid', get_string('data', 'local_reportbuilder'), $savedsearches);
        }
        $mform->addElement('select', 'format', get_string('export', 'local_reportbuilder'), $exportformatselect);

        if ($exporttofilesystemenabled) {
            $exporttosystemarray = array();
            $exporttosystemarray[] =& $mform->createElement('radio', 'emailsaveorboth', '',
                    get_string('exporttoemail', 'local_reportbuilder'), REPORT_BUILDER_EXPORT_EMAIL);
            $exporttosystemarray[] =& $mform->createElement('radio', 'emailsaveorboth', '',
                    get_string('exporttoemailandsave', 'local_reportbuilder'), REPORT_BUILDER_EXPORT_EMAIL_AND_SAVE);
            $exporttosystemarray[] =& $mform->createElement('radio', 'emailsaveorboth', '',
                    get_string('exporttosave', 'local_reportbuilder'), REPORT_BUILDER_EXPORT_SAVE);
            $mform->setDefault('emailsaveorboth', $exporttofilesystem);
            $mform->addGroup($exporttosystemarray, 'exporttosystemarray',
                    get_string('exportfilesystemoptions', 'local_reportbuilder'), array('<br />'), false);
        } else {
            $mform->addElement('hidden', 'emailsaveorboth', REPORT_BUILDER_EXPORT_EMAIL);
            $mform->setType('emailsaveorboth', PARAM_TEXT);
        }

        $schedulestr = get_string('schedule', 'local_reportbuilder');

        // A little trick to help with scheduling of reports belonging to other users.
        $owner = $DB->get_record('user', array('id' => $ownerid, 'deleted' => 0));
        if ($owner) {
            $ownertz = core_date::get_user_timezone($owner);
            if (core_date::get_user_timezone() !== $ownertz) {
                $schedulestr .= '<br />(' . core_date::get_localised_timezone($ownertz) . ')';
            }
        }

        // Schedule options.
        $options = ['frequency' => $frequency, 'schedule' => $schedule];
        if (!has_capability('local/reportbuilder:overridescheduledfrequency', $context)) {
            $currentoption = [];
            $defaultoptions = \local_reportbuilder\scheduler::get_options();
            if (!is_null($frequency)) {
                $currentoption = [array_flip($defaultoptions)[$frequency] => (int) $frequency];
            }
            $schedulerfrequency = get_config('local_reportbuilder', 'schedulerfrequency');
            switch ($schedulerfrequency) {
                case scheduler::DAILY:
                    unset($defaultoptions['hourly'], $defaultoptions['minutely']);
                    break;
                case scheduler::WEEKLY:
                    unset($defaultoptions['daily'], $defaultoptions['hourly'], $defaultoptions['minutely']);
                    break;
                case scheduler::MONTHLY:
                    unset($defaultoptions['weekly'], $defaultoptions['daily'], $defaultoptions['hourly'], $defaultoptions['minutely']);
                    break;
                case scheduler::HOURLY:
                    unset($defaultoptions['minutely']);
                    break;
                case scheduler::MINUTELY:
                    // Nothing to remove, keep all options.
                    break;
                default:
                    // Default, keep all options.
                    break;
            }
            $options['scheduleroptions'] = array_merge($defaultoptions, $currentoption);
        }
        $mform->addElement('scheduler', 'schedulegroup', $schedulestr, $options);

        // Email to, setting for the schedule reports.
        $mform->addElement('header', 'emailto', get_string('scheduledemailtosettings', 'local_reportbuilder'));
        $mform->addElement('html', html_writer::tag('p', get_string('warngrrvisibility', 'local_reportbuilder')));
        $mform->addElement('static', 'emailrequired', '', '');

        if (has_capability('moodle/user:viewdetails', $context)) {
            $options = array(
                    'ajax'              => 'local_reportbuilder/form-user-selector',
                    'multiple'          => true,
                    'valuehtmlcallback' => function($value) {
                        global $DB, $OUTPUT;
                        $user = $DB->get_record('user', ['id' => (int) $value], '*', IGNORE_MISSING);
                        if (!$user || !user_can_view_profile($user)) {
                            return false;
                        }
                        $details = user_get_user_details($user);
                        return $OUTPUT->render_from_template(
                                'core_search/form-user-selector-suggestion', $details);
                    }
            );
            $mform->addElement('autocomplete', 'systemusers', get_string('systemusers', 'local_reportbuilder'), array(), $options);
        }

        $mform->addElement('text', 'externalemails', get_string('emailexternalusers', 'local_reportbuilder'));
        $mform->setType('externalemails', PARAM_RAW_TRIMMED);

        if (!empty($savedsearches)) {
            $this->add_action_buttons();
        }
    }

    public function validation($data, $files) {

        $errors = parent::validation($data, $files);

        $sysusers = $data['systemusers'];
        $extusers = $data['externalemails'];
        $emailsaveorboth = $data['emailsaveorboth'];

        if (empty($sysusers) && empty($extusers) && $emailsaveorboth != REPORT_BUILDER_EXPORT_SAVE) {
            $errors['emailrequired'] = get_string('error:emailrequired', 'local_reportbuilder');
        }

        if (!empty($extusers)) {
            $emails = explode(',', $extusers);
            foreach ($emails as $email) {
                $email = strtolower($email);
                $email = trim($email);

                if (empty($email)) {
                    continue;
                }

                if (!validate_email($email)) {
                    $errors['externalemails'] = get_string('error:invalidemail', 'local_reportbuilder');
                }
            }
        }

        return $errors;
    }
}

class scheduled_reports_add_form extends moodleform {
    function definition() {

        $mform =& $this->_form;

        $sources = array();

        //Report type options
        $reports = reportbuilder::get_user_permitted_reports();
        $reportselect = array();
        foreach ($reports as $report) {
            if (!isset($sources[$report->source])) {
                $sources[$report->source] = reportbuilder::get_source_object($report->source);
            }

            if ($sources[$report->source]->scheduleable) {
                try {
                    if ($report->embedded) {
                        $reportobject = new reportbuilder($report->id);
                    }
                    $reportselect[$report->id] = format_string($report->fullname);
                } catch (moodle_exception $e) {
                    if ($e->errorcode != "nopermission") {
                        // The embedded report creation failed, almost certainly due to a failed is_capable check.
                        // In this case, we just don't add it to $reportselect.
                    } else {
                        throw ($e);
                    }
                }
            }
        }

        if (!empty($reportselect)) {
            $elements = array();
            $elements[] = &$mform->createElement('select', 'reportid', get_string('addnewscheduled', 'local_reportbuilder'),
                    $reportselect);
            $elements[] = &$mform->createElement('submit', 'submitbutton', get_string('addscheduledreport', 'local_reportbuilder'));
            $mform->addGroup($elements, 'addanewscheduledreport', get_string('addanewscheduledreport', 'local_reportbuilder'), '');
        }
    }
}
