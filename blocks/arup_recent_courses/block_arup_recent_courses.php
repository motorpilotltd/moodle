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

require_once($CFG->dirroot . '/course/lib.php');

class block_arup_recent_courses extends block_base {
    protected $_tapsinstalled;
    protected $_arupadvertinstalled;
    protected $_methodologyfield;
    protected $_taps;

    public function init() {
        $this->title = get_string('pluginname', 'block_arup_recent_courses');

        if ($this->_is_taps_installed()) {
            $this->_taps = new \local_taps\taps();
        }
    }

    public function applicable_formats() {
        return array('site' => true);
    }

    public function specialization() {
        $this->title = get_string('title', 'block_arup_recent_courses');
    }

    public function get_content() {
        global $CFG, $DB, $OUTPUT, $USER;

        require_once($CFG->libdir.'/completionlib.php');

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        if (!isloggedin() or isguestuser()) {
            return $this->content;
        }

        $table = new html_table();
        $table->attributes['class'] = 'arup-recent-courses-table';

        if ($this->_has_methodologies()) {
            $table->attributes['class'] .= ' arup-has-methodologies';
        }

        $courses = enrol_get_my_courses(null, 'visible DESC, fullname ASC');
        $tapsenrolments = $this->_get_taps_enrolments();

        $rowcount = 0;

        // Variable $tapsenrolments is empty if local_taps not installed.
        foreach ($tapsenrolments as $moodlecourse => $tapsenrolment) {
            if ($rowcount >= 5) {
                break;
            }
            // Has TAPS enrolment but not matching Moodle enrolment.
            if (!array_key_exists($moodlecourse, $courses)) {
                $tapsstatus = '';
                $tapsstatustype = $this->_taps->get_status_type($tapsenrolment->bookingstatus);
                if (in_array($tapsstatustype, array('requested', 'waitlisted'))) {
                    $tapsstatus = get_string('status:'.$tapsstatustype, 'block_arup_recent_courses');
                }

                $cells = array();

                $cell = new html_table_cell();
                $cell->attributes['class'] = 'outer-spacer';
                $cell->text = '';
                $cells[] = clone($cell);

                if ($this->_has_methodologies()) {
                    $cell = new html_table_cell();
                    $cell->attributes['class'] = 'text-center';
                    if ($tapsenrolment->classtype) {
                        $classtypegroup = $this->_taps->get_classtype_type($tapsenrolment->classtype);
                        if (get_string_manager()->string_exists($classtypegroup, 'block_arup_recent_courses')) {
                            $alttitle = get_string($classtypegroup, 'block_arup_recent_courses');
                        } else {
                            $alttitle = $tapsenrolment->classtype;
                        }
                        $cell->text = html_writer::empty_tag(
                            'img',
                            array(
                                'src' => $OUTPUT->pix_url($classtypegroup, 'local_taps'),
                                'alt' => $alttitle,
                                'title' => $alttitle
                            )
                        );
                    } else {
                        $cell->text = '';
                    }
                    $cells[] = clone($cell);
                }
                $linkclass = $tapsenrolment->visible ? '' : 'dimmed';
                $linktext = format_string($tapsenrolment->fullname) . $tapsstatus;
                $linkurl = new moodle_url('/course/view.php', array('id' => $tapsenrolment->course));

                $cells[] = html_writer::link($linkurl, $linktext, array('class' => $linkclass, 'title' => format_string($tapsenrolment->shortname)));

                $cell = new html_table_cell();
                $cell->attributes['class'] = 'outer-spacer';
                $cell->text = '';
                $cells[] = clone($cell);

                $table->data[] = new html_table_row($cells);
                $rowcount++;
            }
        }

        // Remove frontpage course.
        $site = get_site();
        if (array_key_exists($site->id, $courses)) {
            unset($courses[$site->id]);
        }

        $enrolleddates = $this->_get_enrolled_dates();
        foreach ($enrolleddates as $enrolleddate) {
            if ($rowcount >= 5) {
                break;
            }
            $iscourse = isset($courses[$enrolleddate->courseid]);
            $istapsenrolment = key_exists($enrolleddate->courseid, $tapsenrolments);
            if ($iscourse) {
                $context = context_course::instance($enrolleddate->courseid);
                $hasrole = self::user_has_role_assignments($USER->id, array('student'), $context->id);
                if (!$hasrole) {
                    continue;
                }

                $tapsstatus = '';
                if ($istapsenrolment) {
                    $tapsstatustype = $this->_taps->get_status_type($tapsenrolments[$enrolleddate->courseid]->bookingstatus);
                    if (in_array($tapsstatustype, array('requested', 'waitlisted'))) {
                        $tapsstatus = get_string('status:'.$tapsstatustype, 'block_arup_recent_courses');
                    }
                } else {
                    // Remove completed Moodle courses.
                    $ccompletion = new completion_completion(array('userid' => $USER->id, 'course' => $enrolleddate->courseid));
                    if ($ccompletion->is_complete()) {
                        continue;
                    }
                }
                $course = $courses[$enrolleddate->courseid];
                $cells = array();

                $cell = new html_table_cell();
                $cell->attributes['class'] = 'outer-spacer';
                $cell->text = '';
                $cells[] = clone($cell);

                if ($this->_has_methodologies()) {
                    $cell = new html_table_cell();
                    $cell->attributes['class'] = 'text-center';
                    $cell->text = $this->_get_methodology($course->id);
                    $cells[] = clone($cell);
                }
                $linkclass = $course->visible ? '' : 'dimmed';
                $linktext = format_string($course->fullname) . $tapsstatus;
                $linkurl = new moodle_url('/course/view.php', array('id' => $course->id));

                $cells[] = html_writer::link($linkurl, $linktext, array('class' => $linkclass, 'title' => format_string($course->shortname)));

                $cell = new html_table_cell();
                $cell->attributes['class'] = 'outer-spacer';
                $cell->text = '';
                $cells[] = clone($cell);

                $table->data[] = new html_table_row($cells);
                $rowcount++;
            }
        }

        if (!empty($table->data)) {
            $this->content->text = html_writer::table($table);
        } else {
            $this->content->text = html_writer::tag('div', get_string('norecentcourses', 'block_arup_recent_courses'), array('class' => 'no-recent'));
        }

        $footerleft = '';
        $dbman = $DB->get_manager();
        if ($dbman->table_exists('tapsenrol_iw_tracking') && $this->_tapsinstalled) {
            list($in, $inparams) = $DB->get_in_or_equal(
                $this->_taps->get_statuses('requested'),
                SQL_PARAMS_NAMED, 'status'
            );
            $compare = $DB->sql_compare_text('lte.bookingstatus');

            $approvalssql = <<<EOS
SELECT
    COUNT(tit.id)
FROM
    {tapsenrol_iw_tracking} tit
JOIN
    {local_taps_enrolment} lte
    ON lte.enrolmentid = tit.enrolmentid
JOIN
    {tapsenrol} t
    ON t.tapscourse = lte.courseid
JOIN
    {tapsenrol_iw} ti
    ON ti.id = t.internalworkflowid
JOIN
    {user} u
    ON u.idnumber = lte.staffid
WHERE
    tit.approved IS NULL
    AND timecancelled IS NULL
    AND tit.sponsoremail = :sponsoremail
    AND lte.active = 1
    AND (lte.archived = 0 OR lte.archived IS NULL)
    AND {$compare} {$in}
EOS;
            $approvalsparams = array(
                'sponsoremail' => strtolower($USER->email),
            );
            $approvals = $DB->count_records_sql($approvalssql, array_merge($approvalsparams, $inparams));
            $allapprovals = $DB->get_records('tapsenrol_iw_tracking', array('sponsoremail' => $USER->email));
            if ($allapprovals) {
                $approvalurl = new moodle_url('/mod/tapsenrol/approve.php');
                $footerleft = html_writer::tag(
                    'div',
                    html_writer::link($approvalurl, get_string('approvals', 'block_arup_recent_courses', $approvals)),
                    array('class' => 'pull-left')
                );
            }
        }
        $footerrighturl = new moodle_url('/my/index.php');
        $footerright = html_writer::tag(
            'div',
            html_writer::link($footerrighturl, get_string('viewall', 'block_arup_recent_courses')),
            array('class' => 'pull-right')
        );
        $this->content->footer = html_writer::tag('div', $footerleft . $footerright, array('class' => 'clearfix'));

        return $this->content;
    }

    protected function _get_taps_enrolments() {
        global $DB, $USER;

        if (empty($USER->idnumber)
            || !$this->_is_taps_installed()
            || !$this->_is_arupadvert_installed()
        ) {
            return array();
        }

        $statuses = array_merge(
            $this->_taps->get_statuses('requested'),
            $this->_taps->get_statuses('waitlisted'),
            $this->_taps->get_statuses('placed')
        );
        list($usql, $params) = $DB->get_in_or_equal($statuses, SQL_PARAMS_NAMED, 'status');
        $sql = <<<EOS
SELECT
    lte.id, lte.coursename, lte.classtype, lte.bookingstatus,
    a.course,
    c.fullname, c.shortname, c.visible
FROM
    {local_taps_enrolment} lte
JOIN
    {arupadvertdatatype_taps} at
    ON at.tapscourseid = lte.courseid
JOIN
    {arupadvert} a
    ON a.id = at.arupadvertid
JOIN
    {course} c ON c.id = a.course
WHERE
    lte.staffid = :staffid
    AND lte.active = 1
    AND (lte.archived = 0 OR lte.archived IS NULL)
    AND {$DB->sql_compare_text('lte.bookingstatus')} {$usql}
EOS;
        $params['staffid'] = $USER->idnumber;
        $tapsenrolments = $DB->get_records_sql($sql, $params);
        $return = array();
        foreach ($tapsenrolments as $tapsenrolment) {
            $return[$tapsenrolment->course] = $tapsenrolment; // Shouldn't be more than one enrolment with one of these statuses.
        }
        return $return;
    }

    protected function _get_enrolled_dates() {
        global $USER, $DB;

        $params = array();
        $params['userid'] = $USER->id;
        $params['timeend'] = $params['timestart'] = time();

        $sql = "
            SELECT
                e.courseid, MAX(ue.timestart) as timestart
            FROM
                {user_enrolments} ue
            JOIN
                {enrol} e
                ON e.id = ue.enrolid
            WHERE
                ue.userid = :userid
                AND timestart < :timestart
                AND (timeend > :timeend OR timeend = 0)
            GROUP BY
                e.courseid
            ORDER BY
                timestart DESC
            ";

        return $DB->get_records_sql($sql, $params);
    }

    public static function user_has_role_assignments($userid, array $rolearchetypes, $contextid = 0) {
        global $DB;
        $archetyperoles = array();
        foreach ($rolearchetypes as $rolearchetype) {
            $archetyperoles = $archetyperoles + get_archetype_roles($rolearchetype);
        }
        $roles = array();
        foreach ($archetyperoles as $archetyperole) {
            $roles[] = $archetyperole->id;
        }

        if (empty($roles)) {
            return false;
        }

        list($roles, $params) = $DB->get_in_or_equal($roles, SQL_PARAMS_NAMED, 'r');
        $params['userid'] = $userid;
        if ($contextid) {
            $contextwhere = '  AND ra.contextid = :contextid ';
            $params['contextid'] = $contextid;
        } else {
            $contextwhere = '';
        }

        $sql = "SELECT COUNT(ra.id)
                FROM {role_assignments} ra
                WHERE ra.roleid $roles AND ra.userid = :userid {$contextwhere}";

        $count = $DB->get_field_sql($sql, $params);
        return ($count > 0);
    }

    protected function _has_methodologies() {
        global $DB;

        if (!isset($this->_methodologyfield)) {
            $this->_methodologyfield = false;

            $fieldid = isset($this->config->methodologyfield) ? $this->config->methodologyfield : 0;
            if ($fieldid && get_config('local_coursemetadata', 'version')) {
                $this->_methodologyfield = $DB->get_record('coursemetadata_info_field', array('id' => $fieldid));
            }
        }
        return $this->_methodologyfield;
    }

    protected function _get_methodology($courseid) {
        global $CFG;

        if (!$this->_has_methodologies()) {
            return '';
        }

        require_once("{$CFG->dirroot}/local/coursemetadata/lib.php");
        require_once("{$CFG->dirroot}/local/coursemetadata/field/{$this->_methodologyfield->datatype}/field.class.php");
        $fieldclassname = 'coursemetadata_field_'.$this->_methodologyfield->datatype;
        $fieldclass = new $fieldclassname($this->_methodologyfield->id, $courseid);

        return $fieldclass->display_data();
    }

    protected function _is_taps_installed() {
        if (!isset($this->_tapsinstalled)) {
            $this->_tapsinstalled = get_config('local_taps', 'version');
        }
        return $this->_tapsinstalled;
    }

    protected function _is_arupadvert_installed() {
        if (!isset($this->_arupadvertinstalled)) {
            $this->_arupadvertinstalled = get_config('arupadvertdatatype_taps', 'version');
        }
        return $this->_arupadvertinstalled;
    }
}
