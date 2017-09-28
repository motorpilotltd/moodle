<?php
// This file is part of Moodle - http://moodle.org/
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
 * @package moodlecore
 * @subpackage backup-moodle2
 * @copyright 2010 onwards Eloy Lafuente (stronk7) {@link http://stronk7.com}
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * Define all the backup steps that will be used by the backup_arupapplication_activity_task
 */

/**
 * Define the complete arupapplication structure for backup, with file and id annotations
 */
class backup_arupapplication_activity_structure_step extends backup_activity_structure_step {

    protected function define_structure() {

        // To know if we are including userinfo
        $userinfo = $this->get_setting_value('userinfo');

        // Define each element separated
        $arupapplication = new backup_nested_element('arupapplication', array('id'), array(
                                                'name',
                                                'intro',
                                                'introformat',
                                                'technicalreferencereq',
                                                'sponsorstatementreq',
                                                'sponsordeclarationlabel',
                                                'refereemessage_hint',
                                                'email_referee_footer',
                                                'sponsormessage_hint',
                                                'email_sponsor_footer',
                                                'submission_hint',
                                                'reference_hint',
                                                'sponsorstatement_hint',
                                                'footer',
                                                'email_startnotification',
                                                'email_submissionnotification',
                                                'email_completenotification',
                                                'timecreated',
                                                'timemodified',
                                                'completionsubmit'));


        $arupstatementquestions = new backup_nested_element('arupstatementquestionss');

        $arupstatementquestion = new backup_nested_element('arupstatementquestions', array('id'), array(
                                                'question',
                                                'ismandatory',
                                                'sortorder',
                                                'timecreated',
                                                'timemodified'));

        $arupdeclarations = new backup_nested_element('arupdeclarationss');

        $arupdeclaration = new backup_nested_element('arupdeclarations', array('id'), array(
                                                'declaration',
                                                'sortorder',
                                                'timecreated',
                                                'timemodified'));

        $arupsubmissions = new backup_nested_element('arupsubmissionss');

        $arupsubmission = new backup_nested_element('arupsubmissions', array('id'), array(
                                                'userid',
                                                'title',
                                                'passportname',
                                                'knownas',
                                                'dateofbirth',
                                                'countryofresidence',
                                                'requirevisa',
                                                'grade',
                                                'jobtitle',
                                                'discipline',
                                                'joiningdate',
                                                'arupgroup',
                                                'businessarea',
                                                'officelocation',
                                                'otherofficelocation',
                                                'degree',
                                                'cv',
                                                'referee_email',
                                                'referee_message',
                                                'referee_audit',
                                                'reference_phone',
                                                'referenceposition',
                                                'referenceknown',
                                                'referenceperformance',
                                                'referencetalent',
                                                'referencemotivation',
                                                'referenceknowledge',
                                                'referencecomments',
                                                'sponsor_email',
                                                'sponsor_message',
                                                'sponsor_audit',
                                                'sponsorstatement',
                                                'sponsordeclaration',
                                                'status',
                                                'timecreated',
                                                'timemodified'));


        $arupstatementanswers = new backup_nested_element('arupstatementanswerss');

        $arupstatementanswer = new backup_nested_element('arupstatementanswers', array('id'), array(
                                                'questionid',
                                                'answer',
                                                'userid',
                                                'timecreated',
                                                'timemodified'));

        $arupdeclarationanswers = new backup_nested_element('arupdeclarationanswerss');

        $arupdeclarationanswer = new backup_nested_element('arupdeclarationanswers', array('id'), array(
                                                'declarationid',
                                                'answer',
                                                'userid',
                                                'timecreated',
                                                'timemodified'));

        $arupapplication_trackings = new backup_nested_element('arupapplication_trackingss');

        $arupapplication_tracking = new backup_nested_element('arupapplication_tracking', array('id'), array(
                                                'userid',
                                                'completed',
                                                'timemodified'));

        $arupapplication->add_child($arupstatementquestions);
        $arupstatementquestions->add_child($arupstatementquestion);

        $arupapplication->add_child($arupdeclarations);
        $arupdeclarations->add_child($arupdeclaration);

        $arupapplication->add_child($arupsubmissions);
        $arupsubmissions->add_child($arupsubmission);

        $arupsubmissions->add_child($arupstatementanswers);
        $arupstatementanswers->add_child($arupstatementanswer);

        $arupsubmissions->add_child($arupdeclarationanswers);
        $arupdeclarationanswers->add_child($arupdeclarationanswer);

        $arupapplication->add_child($arupapplication_trackings);
        $arupapplication_trackings->add_child($arupapplication_tracking);

        // Define sources.
        $arupapplication->set_source_table('arupapplication', array('id' => backup::VAR_ACTIVITYID));
        $arupstatementquestion->set_source_table('arupstatementquestions', array('applicationid' => backup::VAR_PARENTID));
        $arupdeclaration->set_source_table('arupdeclarations', array('applicationid' => backup::VAR_PARENTID));

         if ($userinfo) {
            $arupsubmission->set_source_table('arupsubmissions',
                                     array('applicationid' => backup::VAR_PARENTID));
            $arupstatementanswer->set_source_table('arupstatementanswers',
                                     array('applicationid' => backup::VAR_PARENTID));
            $arupdeclarationanswer->set_source_table('arupdeclarationanswers',
                                     array('applicationid' => backup::VAR_PARENTID));
            $arupapplication_tracking->set_source_table('arupapplication_tracking',
                                     array('applicationid' => backup::VAR_PARENTID));
         }

         // Define id annotations
        $arupsubmission->annotate_ids('user', 'userid');
        $arupstatementanswer->annotate_ids('user', 'userid');
        $arupdeclarationanswer->annotate_ids('user', 'userid');
        $arupapplication_tracking->annotate_ids('user', 'userid');

        // Define file annotations
        $arupapplication->annotate_files('mod_arupapplication', 'intro', null); // This file area hasn't itemid
        $arupsubmission->annotate_files('mod_arupapplication', 'submission', 'id');

        // Return the root element (arupapplication), wrapped into standard activity structure
        return $this->prepare_activity_structure($arupapplication);
    }

}
