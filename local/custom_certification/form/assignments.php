<?php

namespace local_custom_certification\form;

use local_custom_certification\certification;

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

class certification_assignments_form extends \moodleform
{
    function definition()
    {
        global $OUTPUT;
        $mform =& $this->_form;
        $certif = $this->_customdata['certif'];

        $duration = certification::get_time_periods();

        $mform->addElement('header', 'invdividualassignment', get_string('individualassignments', 'local_custom_certification'));


        $mform->addElement('html', \html_writer::tag('p', get_string('instructions:programassignments', 'local_custom_certification'), ['class' => 'instructions']));
        $individualstable = \html_writer::start_tag('table', ['class' => 'generaltable']);
        $individualstable .= \html_writer::start_tag('tr');
        $individualstable .= \html_writer::tag('th', get_string('headerindividualname', 'local_custom_certification'));
        $individualstable .= \html_writer::tag('th', get_string('headeridividualassignmentduedate', 'local_custom_certification'));
        $individualstable .= \html_writer::tag('th', get_string('headeridividualactualduedate', 'local_custom_certification'));
        $individualstable .= \html_writer::tag('th', get_string('headeractions', 'local_custom_certification'), ['class' => 'actions']);
        $individualstable .= \html_writer::end_tag('tr');

        foreach ($certif->assignments as $assignmentid => $assignment) {
            if (($assignment->assignmenttype == certification::ASSIGNMENT_TYPE_USER) && isset($certif->assignedusers[$assignment->assignmenttypeid])) {
                $user = $certif->assignedusers[$assignment->assignmenttypeid];
                $individualstable .= \html_writer::start_tag('tr');
                $individualstable .= \html_writer::start_tag('td');
                $individualstable .= \html_writer::start_tag('a', ['href' => new \moodle_url('/user/profile.php', ['id' => $user->id])]);
                $individualstable .= \html_writer::div($OUTPUT->user_picture($user, ['link' => false]), 'user-photo');
                $individualstable .= \html_writer::end_tag('a');
                $individualstable .= \html_writer::start_tag('div', ['class' => 'user-info-container']);
                $individualstable .= \html_writer::tag('p', $user->fullname);
                $individualstable .= \html_writer::tag('p', $user->email);
                $individualstable .= \html_writer::end_tag('div');
                $individualstable .= \html_writer::end_tag('td');


                $actualduedate = date('d/m/Y', $user->duedate);
                if ($assignment->duedatetype == certification::DUE_DATE_NOT_SET) {

                    $duedatelabel = get_string('setduedate', 'local_custom_certification');
                    $actualduedate = get_string('duedatenotset', 'local_custom_certification');

                } elseif ($assignment->duedatetype == certification::DUE_DATE_FIXED) {

                    $duedatelabel = get_string('fixedduedate', 'local_custom_certification', date('jS F Y', $assignment->duedateperiod));

                } elseif ($assignment->duedatetype == certification::DUE_DATE_FROM_FIRST_LOGIN) {

                    $duedateperiod = $assignment->duedateperiod . ' ' . $duration[$assignment->duedateunit];
                    $duedatelabel = get_string('firstloginduedate', 'local_custom_certification', $duedateperiod);
                    if ($user->duedate == certification::DUE_DATE_NOT_SET) {
                        $actualduedate = get_string('duedatenotset', 'local_custom_certification');
                    }

                } elseif ($assignment->duedatetype == certification::DUE_DATE_FROM_ENROLMENT) {
                    $duedateperiod = $assignment->duedateperiod . ' ' . $duration[$assignment->duedateunit];
                    $duedatelabel = get_string('enrolmentduedate', 'local_custom_certification', $duedateperiod);

                }
                $url = \html_writer::link('#', $duedatelabel, ['onclick' => 'return;','class' => 'setduedate', 'data-assignmentid' => $assignmentid, 'data-certifid' => $certif->id]);
                $individualstable .= \html_writer::tag('td', $url);


                $individualstable .= \html_writer::tag('td', $actualduedate);
                $individualstable .= \html_writer::start_tag('td', ['class' => 'action-img']);

                $individualstable .= \html_writer::tag('img', '', ['src' => $OUTPUT->pix_url("/t/delete"), 'onclick' => "if(confirm('" . get_string('removeassignmentconfirmdialog', 'local_custom_certification') . "')) deleteAssignment(this);", 'class' => 'deletebtn', 'data-type' => 'individuals', 'data-id' => $certif->assignedusers[$assignment->assignmenttypeid]->id, 'data-certifid' => $certif->id]);
                $individualstable .= \html_writer::end_tag('td');
                $individualstable .= \html_writer::end_tag('tr');
            }
        }

        $individualstable .= \html_writer::end_tag('table');
        $mform->addElement('html', $individualstable);
        $individualbtn = [];
        $individualbtn[] = $mform->createElement('button', 'individualsbtn', get_string('individualsbtn', 'local_custom_certification'), ['class' => 'form-submit assignmentbtn', 'data-certifid' => $certif->id, 'data-type' => 'individuals']);
        $mform->addGroup($individualbtn, 'individualbtngroup');

        $mform->addElement('header', 'programdetails', get_string('cohortassignments', 'local_custom_certification'));
        $mform->addElement('html', \html_writer::tag('p', get_string('instructions:programassignmentscohort', 'local_custom_certification'), ['class' => 'instructions']));


        $cohortstable = \html_writer::start_tag('table', ['class' => 'generaltable individuals']);

        $cohortstable .= \html_writer::start_tag('tr');
        $cohortstable .= \html_writer::tag('th', get_string('cohortname', 'local_custom_certification'));
        $cohortstable .= \html_writer::tag('th', get_string('headercohortassignmentduedate', 'local_custom_certification'));
        $cohortstable .= \html_writer::tag('th', get_string('headercohortactualduedate', 'local_custom_certification'));
        $cohortstable .= \html_writer::tag('th', get_string('learnerscount', 'local_custom_certification'));
        $cohortstable .= \html_writer::tag('th', get_string('actions', 'local_custom_certification'), ['class' => 'actions']);
        $cohortstable .= \html_writer::end_tag('tr');


        foreach ($certif->assignedcohorts as $cohort) {
            $cohortstable .= \html_writer::start_tag('tr');
            $cohortstable .= \html_writer::tag('td', $cohort->name);
            if (isset($certif->assignments[$cohort->assignmentid])) {


                $assignment = $certif->assignments[$cohort->assignmentid];

                if ($assignment->duedatetype == certification::DUE_DATE_NOT_SET) {

                    $duedate = get_string('setduedate', 'local_custom_certification');

                } elseif ($assignment->duedatetype == certification::DUE_DATE_FIXED) {

                    $duedate = get_string('fixedduedate', 'local_custom_certification', date('jS F Y', $assignment->duedateperiod));

                } elseif ($assignment->duedatetype == certification::DUE_DATE_FROM_FIRST_LOGIN) {

                    $duedateperiod = $assignment->duedateperiod . ' ' . $duration[$assignment->duedateunit];
                    $duedate = get_string('firstloginduedate', 'local_custom_certification', $duedateperiod);

                } elseif ($assignment->duedatetype == certification::DUE_DATE_FROM_ENROLMENT) {

                    $duedateperiod = $assignment->duedateperiod . ' ' . $duration[$assignment->duedateunit];
                    $duedate = get_string('enrolmentduedate', 'local_custom_certification', $duedateperiod);

                }
                $url = \html_writer::link('#', $duedate, ['onclick' => 'return;', 'class' => 'setduedate', 'data-assignmentid' => $cohort->assignmentid, 'data-certifid' => $certif->id]);
                $cohortstable .= \html_writer::tag('td', $url);
                $url = \html_writer::link('#', get_string('cohortviewdates', 'local_custom_certification'), ['class' => 'view-dates', "onclick" => "showCohortDueDates('" . $assignment->id . "','" . $certif->id . "');return;"]);

                $cohortstable .= \html_writer::tag('td', $url);

                $cohortstable .= \html_writer::start_tag('td');
                $cohortstable .= \html_writer::tag('a', $cohort->memberscount, ['href' => new \moodle_url('/cohort/assign.php', ['id' => $cohort->id])]);
                $cohortstable .= \html_writer::end_tag('td');

                $cohortstable .= \html_writer::start_tag('td', ['class' => "action-img"]);
                $cohortstable .= \html_writer::tag('img', '', ['src' => $OUTPUT->pix_url("/t/delete"), 'onclick' => "if(confirm('" . get_string('removeassignmentconfirmdialog', 'local_custom_certification') . "')) deleteAssignment(this);", 'class' => 'deletebtn', 'data-type' => 'cohorts', 'data-id' => $cohort->id, 'data-certifid' => $certif->id]);
                $cohortstable .= \html_writer::end_tag('td');
                $cohortstable .= \html_writer::end_tag('tr');
            }
        }
        $cohortstable .= \html_writer::end_tag('table');
        $mform->addElement('html', $cohortstable);
        $cohortbtn[] = $mform->createElement('button', 'cohortbtn', get_string('cohortbtn', 'local_custom_certification'), ['class' => 'form-submit assignmentbtn', 'data-certifid' => $certif->id, 'data-type' => 'cohorts']);
        $mform->addGroup($cohortbtn, 'cohortbtngroup');
        $mform->disable_form_change_checker();
    }


}
