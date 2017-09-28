<?php
require_once(dirname(dirname(dirname(__FILE__))) . '/config.php');
require_once($CFG->libdir . '/adminlib.php');

$systemcontext = context_system::instance();
require_login();
$PAGE->requires->js(new moodle_url('/local/custom_certification/js/collapsed.js'));
$PAGE->requires->css(new moodle_url('/local/custom_certification/styles/custom_certification.css'));
$PAGE->requires->css(new moodle_url('/local/custom_certification/styles/overview.css'));

$certifid = required_param('id', PARAM_INT);
$userid = optional_param('userid', null, PARAM_INT);

$capability = has_capability('local/custom_certification:view', $systemcontext, $USER, true);

$actualurl = new moodle_url('/local/custom_certification/overview.php', ['id' => $certifid]);

$PAGE->set_url($actualurl);
$PAGE->set_pagelayout('admin');
$PAGE->set_context($systemcontext);
$PAGE->navbar->add(get_string('overviewheading', 'local_custom_certification'));
$renderer = $PAGE->get_renderer('local_custom_certification');


$certif = new \local_custom_certification\certification($certifid, false);
$PAGE->set_title(get_string('overviewheading', 'local_custom_certification'));

echo $OUTPUT->header();

$viewinguser = false;
$user = $USER;
$showoverview = true;
if ($userid != null) {
    if(!$capability){
        echo get_string('missingpermission', 'local_custom_certification');
        $showoverview = false;
    }else{
        $user = $DB->get_record('user', ['id' => $userid ]);
        if(!$user){
            echo get_string('nouser', 'local_custom_certification', $userid);
            $showoverview = false;
        }
        $viewinguser = true;
    }
}

if ($showoverview) {
    $userfullname = fullname($user);
    $enrolleduser = false;
    $isrecertif = null;
    $coursesprogress = [];
    $ragstatus = '';
    $usercertificationdetails = null;
    if ($assignmentdata = $certif->get_user_assignments($user->id)) {
        
        $enrolleduser = true;
        $coursesprogress = \local_custom_certification\completion::get_user_progress($certif, $user->id);
        $usercertificationdetails = \local_custom_certification\completion::get_user_certification_details($certif->id, $user->id);
        $ragstatus = \local_custom_certification\completion::get_rag_status($usercertificationdetails->timecompleted, $usercertificationdetails->duedate, $usercertificationdetails->assignmentduedate);
        
        $isrecertif = local_custom_certification\completion::is_recertification($certif, $user->id);
    }

    echo $renderer->display_overview($certif, $certifid, $viewinguser, $userfullname, $assignmentdata, 
                                     $capability, $enrolleduser, $isrecertif, $coursesprogress, $usercertificationdetails, $ragstatus);

}


echo $OUTPUT->footer();