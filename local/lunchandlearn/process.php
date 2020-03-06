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

require_once(dirname(dirname(dirname(__FILE__))).'/config.php');
require_once(dirname(__FILE__).'/lib.php');
require_once(dirname(__FILE__).'/lunchandlearn_form.php');

require_once($CFG->dirroot.'/course/lib.php');
require_login();

$action = optional_param('action', 'new', PARAM_ALPHA);
$eventid = optional_param('id', 0, PARAM_INT);
$courseid = optional_param('courseid', SITEID, PARAM_INT);
$cal_y = optional_param('cal_y', 0, PARAM_INT);
$cal_m = optional_param('cal_m', 0, PARAM_INT);
$cal_d = optional_param('cal_d', 0, PARAM_INT);

if ($courseid != SITEID && !empty($courseid)) {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $courses = array($course->id => $course);
    $issite = false;
} else {
    $course = get_site();
    $courses = calendar_get_default_courses();
    $issite = true;
}
require_login($course, false);

$url = new moodle_url('/local/lunchandlearn/process.php', array('action' => $action));
if ($eventid != 0) {
    $url->param('id', $eventid);
}
if ($courseid != SITEID) {
    $url->param('course', $courseid);
}
if ($cal_y !== 0) {
    $url->param('cal_y', $cal_y);
}
if ($cal_m !== 0) {
    $url->param('cal_m', $cal_m);
}
if ($cal_d !== 0) {
    $url->param('cal_d', $cal_d);
}
$PAGE->set_url($url);
$PAGE->set_pagelayout('standard');

require_capability('local/lunchandlearn:edit', $PAGE->context);

lunchandlearn_add_page_navigation($PAGE, $PAGE->url);
lunchandlearn_add_admin_navigation($PAGE, $PAGE->url);
$PAGE->navbar->ignore_active();

$lunchlearn = new lunchandlearn(optional_param('id', 0, PARAM_INT));

$title = $lunchlearn->get_id()===0 ? get_string('newlunchlearntitle', 'local_lunchandlearn') : get_string('editlunchlearntitle', 'local_lunchandlearn');

$viewcalendarurl = new moodle_url(CALENDAR_URL.'view.php', $PAGE->url->params());
$viewcalendarurl->remove_params(array('id', 'action'));
$viewcalendarurl->param('view', 'upcoming');
$strcalendar = get_string('calendar', 'calendar');

$mform = new lunchandlearn_form($url, $lunchlearn, 'post', '', array('id' => 'lunchandlearnform'));
if ($mform->is_cancelled()) {
    redirect($lunchlearn->get_cal_url('full'));
}

$lunchlearn->form($mform);
if ($data = $mform->get_data()) {
    $lunchlearn->bind($data);
    $lunchlearn->save($data);
    if (($data->resendinvites)) {
        lunchandlearn_resend_invites($lunchlearn);
    }
    redirect($viewcalendarurl);
}

$PAGE->requires->jquery();
$PAGE->requires->js(new moodle_url('/local/lunchandlearn/js/resendinvites.js'));

$PAGE->requires->js(new moodle_url('/local/lunchandlearn/js/jquery.maxlen.js'));
$PAGE->requires->js_init_call("$('input[maxlength],textarea[maxlength]').maxlen", array(), true);

$PAGE->navbar->add(get_string('sitepages'));
$PAGE->navbar->add($strcalendar, $viewcalendarurl);
$PAGE->navbar->add($title);
$PAGE->set_title($course->shortname.': '.$strcalendar.': '.$title);
$PAGE->set_heading($course->fullname);

echo $OUTPUT->header();
echo $OUTPUT->heading($title);
$mform->display();
echo '
<div  id="myModal" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <button type="button" class="close" data-dismiss="modal"><span aria-hidden="true">&times;</span></button>
        <h4 class="modal-title">'.get_string('resendinvitestitle', 'local_lunchandlearn').'</h4>
      </div>
      <div class="modal-body">
        <p>'.get_string('resendinvitesbody', 'local_lunchandlearn').'</p>
      </div>
      <div class="modal-footer">
        <button id="savebutton" type="button" class="btn btn-default" data-dismiss="modal">Save</button>
        <button id="saveandsendbutton" type="button" class="btn btn-primary" data-dismiss="modal">Save and Send invites</button>
      </div>
    </div><!-- /.modal-content -->
  </div><!-- /.modal-dialog -->
</div><!-- /.modal -->';
echo $OUTPUT->footer();