<?php
require_once('form/assignments.php');
require_once('form/duedates.php');

use local_custom_certification\certification;
$PAGE->requires->js(new moodle_url('/local/custom_certification/js/assignments.js'));

$currenturl = qualified_me();

$detailsform = new \local_custom_certification\form\certification_assignments_form($currenturl, [
    'certif' => $certif
]);

if ($assignmentid != null) {
    $assignment = $certif->assignments[$assignmentid];

    $duedatesform = new local_custom_certification\form\certification_duedates_form($currenturl, [
        'assignment' => $assignment
    ]);


    if ($formdata = $duedatesform->get_data()) {
        if ($formdata->fixeddate > 0) {
            $datetype = certification::DUE_DATE_FIXED;
            $dateperiod = $formdata->fixeddate;
            $dateunit = 0;
        } elseif (isset($formdata->duedatefromfirstlogincheck)) {
            $datetype = certification::DUE_DATE_FROM_FIRST_LOGIN;
            $dateperiod = $formdata->duedatefromfirstlogin;
            $dateunit = $formdata->duedatefromfirstloginunit;
        } elseif (isset($formdata->duedatefromenrolmentcheck)) {
            $datetype = certification::DUE_DATE_FROM_ENROLMENT;
            $dateperiod = $formdata->duedatefromenrolment;
            $dateunit = $formdata->duedatefromenrolmentunit;
        }else{
            $datetype = certification::DUE_DATE_NOT_SET;
            $dateperiod = 0;
            $dateunit = 0;
        }

        certification::set_due_date_details($assignmentid, $datetype, $dateperiod, $dateunit);
        \local_custom_certification\notification::add(get_string('certificationsaved', 'local_custom_certification'), \local_custom_certification\notification::TYPE_SUCCESS);
        redirect($currenturl);
    }

}

