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

class block_arup_mylearning_content {

    protected $_block;
    protected $_hascontent = array();
    protected $_workbook;
    protected $_worksheets;

    protected $_tapsinstalled;
    protected $_methodologyfield;

    public $renderer;

    public function __construct($instance) {
        global $DB, $PAGE;

        $this->renderer = $PAGE->get_renderer('block_arup_mylearning');

        $this->_block = new stdClass();
        $this->_block->context = context_block::instance($instance);
        $this->_block->instance = $DB->get_record('block_instances', array('id' => $instance));
        $this->_block->config = new stdClass();
        if ($this->_block->instance) {
            $this->_block->config = unserialize(base64_decode($this->_block->instance->configdata));
        }
    }

    public function has_content($tab) {
        if (!isset($this->_hascontent[$tab])) {
            $function = '_has_content_' . $tab;
            if (method_exists($this, $function)) {
                $this->_hascontent[$tab] = call_user_func(array($this, $function));
            } else {
                $this->_hascontent[$tab] = false;
            }
        }
        return $this->_hascontent[$tab];
    }

    public function get_content($tab) {
        $function = '_get_' . $tab . '_html';
        if (method_exists($this, $function)) {
            return call_user_func(array($this, $function));
        } else {
            return '';
        }
    }

    public function get_export($tab) {
        $function = '_get_' . $tab . '_export';
        if (method_exists($this, $function)) {
            call_user_func(array($this, $function));
        } else {
            return false;
        }
    }

    /* MAIN TAB FUNCTIONS - START */
    protected function _has_content_me() {
        return true;
    }

    protected function _get_me_html() {
        global $OUTPUT;
        $content = '';
        $content .= $OUTPUT->heading(get_string('me', 'block_arup_mylearning'));
        $content .= html_writer::tag('p', 'Coming soon, a new and improved profile page.');
        return $content;
    }

    protected function _has_content_overview() {
        return true;
    }

    protected function _get_overview_html() {
        global $CFG, $DB, $USER;

        require_once($CFG->libdir . '/completionlib.php');

        $content = '';

        $content .= html_writer::tag('p', get_string('overview:intro', 'block_arup_mylearning'));
        $content .= html_writer::tag('p',
                get_string('overview:history', 'block_arup_mylearning', get_string('myhistory', 'block_arup_mylearning')));

        $courses = array();
        $moodleenrolments = enrol_get_my_courses(null, 'visible DESC, fullname ASC');
        $tapsenrolments = $this->_get_taps_enrolments();

        if ($moodleenrolments || $tapsenrolments) {
            $courses = get_courses('all', 'visible DESC, fullname ASC');
            // Remove frontpage course.
            $site = get_site();
            if (array_key_exists($site->id, $courses)) {
                unset($courses[$site->id]);
            }

            foreach ($courses as $index => $course) {
                $ismoodle = array_key_exists($index, $moodleenrolments);
                $istaps = array_key_exists($index, $tapsenrolments);
                if (!$ismoodle && !$istaps) {
                    unset($courses[$course->id]);
                    continue;
                }

                if ($ismoodle && !$istaps) {
                    // Remove (pure) Moodle courses not a student on.
                    $context = context_course::instance($course->id);
                    $hasrole = self::user_has_role_assignments($USER->id, array('student'), $context->id);
                    if (!$hasrole) {
                        unset($courses[$course->id]);
                        continue;
                    }
                }

                $course->status = '';
                if ($istaps) {
                    foreach ($tapsenrolments[$course->id] as $statuses) {
                        foreach ($statuses as $status => $completiontime) {
                            if (in_array($status, array('placed', 'requested', 'waitlisted'))) {
                                // Should only ever be one of these active...
                                $course->status = get_string('status:' . $status, 'block_arup_mylearning');
                            }
                        }
                    }
                    if (!$course->status) {
                        // No status set, remove course.
                        unset($courses[$index]);
                        continue;
                    }
                } else {
                    // Remove completed Moodle courses.
                    $ccompletion = new completion_completion(array('userid' => $USER->id, 'course' => $course->id));
                    if ($ccompletion->is_complete()) {
                        unset($courses[$course->id]);
                        continue;
                    }
                }

                // Set last access.
                if (isset($USER->lastcourseaccess[$course->id])) {
                    $courses[$course->id]->lastaccess = $USER->lastcourseaccess[$course->id];
                } else {
                    $courses[$course->id]->lastaccess = 0;
                }
            }
        }

        $table = new html_table();

        $table->head = array();
        $headers = array(
                array('text' => get_string('course'), 'class' => 'text-left'),
                array('text' => get_string('category'), 'class' => 'text-left'),
                array('text' => get_string('status', 'block_arup_mylearning'), 'class' => 'text-left'),
        );
        if ($this->_has_methodologies()) {
            array_unshift($headers, array('text' => get_string('methodology', 'block_arup_mylearning'), 'class' => 'text-center'));
        }
        foreach ($headers as $col => $header) {
            $table->head[$col] = new html_table_cell(str_ireplace(' ', '&nbsp;', $header['text']));
            $table->head[$col]->attributes['class'] = $header['class'];
        }

        $columncount = count($table->head);

        // Base cell for reuse.
        $cell = new html_table_cell();
        if (!$courses) {
            $cells = array();

            $cell->text = get_string('nocourses', 'my');
            $cell->colspan = $columncount;
            $cells[] = clone($cell);

            $table->data[] = new html_table_row($cells);
        } else {
            // Load categories for use (only doing here as may not be needed).
            $categories = $DB->get_records_menu('course_categories', array(), '', 'id, name');

            foreach ($courses as $course) {
                $cells = array();

                if ($this->_has_methodologies()) {
                    $cell->text = $this->_get_methodology($course->id);
                    $cell->attributes['class'] = 'text-center';
                    $cells[] = clone($cell);
                }

                $cell->attributes['class'] = 'text-left';

                $cell->text = html_writer::link(
                        new moodle_url('/course/view.php', array('id' => $course->id)),
                        format_string($course->fullname)
                );
                $cells[] = clone($cell);

                $cell->text = isset($categories[$course->category]) ? format_string($categories[$course->category]) : '';
                $cells[] = clone($cell);

                $cell->text = $course->status ? $course->status : get_string('status:unknown', 'block_arup_mylearning');
                $cells[] = clone($cell);

                $table->data[] = new html_table_row($cells);
            }
        }

        $content .= html_writer::table($table);

        return $content;
    }

    protected function _has_content_myteaching() {
        global $USER;
        return self::user_has_role_assignments($USER->id, array('editingteacher', 'teacher'));
    }

    protected function _get_myteaching_html() {
        global $DB, $OUTPUT, $USER;

        $content = '';

        $content .= html_writer::tag('p', get_string('myteaching:intro', 'block_arup_mylearning'));

        $courses = enrol_get_my_courses(null, 'visible DESC, fullname ASC');

        $site = get_site();
        if (array_key_exists($site->id, $courses)) {
            unset($courses[$site->id]);
        }

        foreach ($courses as $course) {
            $context = context_course::instance($course->id);
            $hasrole = self::user_has_role_assignments($USER->id, array('editingteacher', 'teacher'), $context->id);
            if (!$hasrole) {
                unset($courses[$course->id]);
                continue;
            }

            if (isset($USER->lastcourseaccess[$course->id])) {
                $courses[$course->id]->lastaccess = $USER->lastcourseaccess[$course->id];
            } else {
                $courses[$course->id]->lastaccess = 0;
            }
        }

        $table = new html_table();

        $table->head = array();
        $headers = array(
                array('text' => get_string('course'), 'class' => 'text-left'),
                array('text' => get_string('category'), 'class' => 'text-left'),
                array('text' => get_string('enrolments', 'block_arup_mylearning'), 'class' => 'text-center'),
                array('text' => get_string('open', 'block_arup_mylearning'), 'class' => 'text-center'),
        );
        if ($this->_has_methodologies()) {
            array_unshift($headers, array('text' => get_string('methodology', 'block_arup_mylearning'), 'class' => 'text-center'));
        }
        foreach ($headers as $col => $header) {
            $table->head[$col] = new html_table_cell(str_ireplace(' ', '&nbsp;', $header['text']));
            $table->head[$col]->attributes['class'] = $header['class'];
        }

        $columncount = count($table->head);

        // Base cell for reuse.
        $cell = new html_table_cell();
        if (!$courses) {
            $cells = array();

            $cell->text = get_string('nocourses', 'my');
            $cell->colspan = $columncount;
            $cells[] = clone($cell);

            $table->data[] = new html_table_row($cells);
        } else {
            // Load categories for use (only doing here as may not be needed).
            $categories = $DB->get_records_menu('course_categories', array(), '', 'id, name');

            foreach ($courses as $course) {
                $cells = array();

                if ($this->_has_methodologies()) {
                    $cell->text = $this->_get_methodology($course->id);
                    $cell->attributes['class'] = 'text-center';
                    $cells[] = clone($cell);
                }

                $cell->attributes['class'] = 'text-left';

                $cell->text = html_writer::link(
                        new moodle_url('/course/view.php', array('id' => $course->id)),
                        format_string($course->fullname)
                );
                $cells[] = clone($cell);

                $cell->text = isset($categories[$course->category]) ? format_string($categories[$course->category]) : '';
                $cells[] = clone($cell);

                $cell->attributes['class'] = 'text-center';

                $cell->text = count(get_enrolled_users(context_course::instance($course->id)));
                $cells[] = clone($cell);

                if ($course->visible) {
                    $cell->text = $OUTPUT->pix_icon('i/valid', get_string('yes'));
                } else {
                    $cell->text = $OUTPUT->pix_icon('i/invalid', get_string('no'));
                }
                $cells[] = clone($cell);

                $table->data[] = new html_table_row($cells);
            }
        }

        $content .= html_writer::table($table);

        return $content;
    }

    protected function _has_content_myhistory() {
        global $USER;
        return ($USER->idnumber);
    }

    protected function _get_myhistory_html() {
        global $OUTPUT, $USER;

        $hascapabilities = array('addcpd' => false, 'editcpd' => false, 'deletecpd' => false);
        foreach ($hascapabilities as $capability => &$hascapability) {
            $hascapability = has_capability('block/arup_mylearning:' . $capability, $this->_block->context);
        }

        $content = '';

        $content .= html_writer::tag('p', get_string('myhistory:intro', 'block_arup_mylearning'));

        if ($this->has_content('halogen')) {
            $content .= html_writer::tag('p', get_string('halogen:historyavailable', 'block_arup_mylearning'));
            $halogenurl = new moodle_url('/my/index.php', array('tab' => 'halogen'));
            $halogenlink = html_writer::link($halogenurl, get_string('halogen:viewhistory', 'block_arup_mylearning'),
                    array('class' => 'btn btn-info'));
            $content .= html_writer::tag('p', $halogenlink, array('class' => 'block_arup_mylearning_tabs'));
        }

        $tapshistory = \local_learningrecordstore\lrsentry::fetchbystaffid($USER->idnumber);

        $table = new html_table();

        $table->head = array();
        $headers = array(
                array('text' => get_string('methodology', 'block_arup_mylearning'), 'class' => 'text-center'),
                array('text' => get_string('course'), 'class' => 'text-left'),
                array('text' => get_string('date', 'block_arup_mylearning'), 'class' => 'text-left'),
                array('text' => get_string('duration', 'block_arup_mylearning'), 'class' => 'text-left'),
                array('text' => get_string('date:expiry', 'block_arup_mylearning'), 'class' => 'text-left'),
                array('text' => get_string('actions', 'block_arup_mylearning'), 'class' => 'text-left actions'),
        );
        foreach ($headers as $col => $header) {
            $table->head[$col] = new html_table_cell(str_ireplace(' ', '&nbsp;', $header['text']));
            $table->head[$col]->attributes['class'] = $header['class'];
        }

        $columncount = count($table->head);

        $table->data = array();

        // Base cell for reuse.
        $cell = new html_table_cell();
        foreach ($tapshistory as $th) {
            $cells = array();

            $cell->text = $th->classtype;
            $cell->attributes['class'] = 'text-center';
            $cells[] = clone($cell);

            $cell->attributes['class'] = 'text-left';

            $courseurl = $th->generateurl();
            if ($courseurl) {
                $cell->text = html_writer::link($courseurl, format_string($th->providername));
            } else {
                $cell->text = format_string($th->providername);
            }

            $cells[] = clone($cell);

            $cell->text = userdate($th->completiontime);
            $cells[] = clone($cell);

            $cell->text = $th->formatduration();
            $cells[] = clone($cell);

            $cell->text = $th->formatexpirydate();
            $cells[] = clone($cell);

            $actions = array();
            $modalurl = new moodle_url('/blocks/arup_mylearning/modal.php',
                    array('id' => $th->id, 'instance' => $this->_block->instance->id));
            $actions[] = $OUTPUT->action_icon(
                    '#',
                    new pix_icon(
                            'icon_plus',
                            get_string('more', 'block_arup_mylearning'),
                            'block_arup_mylearning'
                    ),
                    null,
                    array(
                            'data-toggle' => 'modal',
                            'data-target' => '#info-modal',
                            'data-label'  => $th->providername,
                            'data-url'    => $modalurl->out(false),
                    )
            );

            if ($hascapabilities['editcpd'] && !$th->locked) {
                $editcpdurl = new moodle_url(
                        '/blocks/arup_mylearning/editcpd.php',
                        array('id' => $th->id, 'tab' => 'myhistory', 'instance' => $this->_block->instance->id)
                );
                $actions[] = $OUTPUT->action_icon(
                        $editcpdurl,
                        new pix_icon(
                                't/editstring',
                                get_string('editcpd', 'block_arup_mylearning')
                        ),
                        null,
                        array('class' => 'action-icon extra-action')
                );
            }

            if ($hascapabilities['deletecpd'] && !$th->locked) {
                $deletecpdurl = new moodle_url(
                        '/blocks/arup_mylearning/deletecpd.php',
                        array('id' => $th->id, 'tab' => 'myhistory', 'instance' => $this->_block->instance->id)
                );
                $actions[] = $OUTPUT->action_icon(
                        $deletecpdurl,
                        new pix_icon(
                                'icon_delete',
                                get_string('deletecpd', 'block_arup_mylearning'),
                                'block_arup_mylearning'
                        ),
                        null,
                        array('class' => 'action-icon extra-action')
                );
            }

            $cell->attributes['class'] .= ' actions';
            $cell->text = implode($actions);
            $cells[] = clone($cell);

            $table->data[] = new html_table_row($cells);
        }

        if (empty($table->data)) {
            $cell->text = get_string('nohistory', 'block_arup_mylearning');
            $cell->colspan = $columncount;
            $cell->attributes['class'] = 'text-center';
            $table->data[] = new html_table_row(array(clone($cell)));
        }

        $topactions = $this->_get_export_button('myhistory');
        if ($hascapabilities['addcpd']) {
            $topactions .= $this->_get_add_cpd_button('myhistory');
        }
        $content .= html_writer::tag('p', $topactions, array('class' => 'actions'));

        $content .= html_writer::table($table);

        $bottomactions = $this->_get_export_button('myhistory');
        if ($hascapabilities['addcpd']) {
            $bottomactions .= $this->_get_add_cpd_button('myhistory');
        }
        $content .= html_writer::tag('p', $bottomactions, array('class' => 'actions'));

        $content .= $this->renderer->more_info_modal();

        return $content;
    }

    protected function _get_myhistory_export() {
        global $CFG, $USER;

        require_once("$CFG->libdir/excellib.class.php");

        $filename = clean_filename('learning-history.xls');

        $this->_workbook = new MoodleExcelWorkbook('-');
        $this->_workbook->send($filename);

        $this->_worksheets = array();

        $this->_worksheets[0] = $this->_workbook->add_worksheet(get_string('worksheet:history', 'block_arup_mylearning'));

        $fields = array(
                'coursename'              => get_string('course'),
                'classcategory'           => get_string('category'),
                'healthandsafetycategory' => get_string('cpd:healthandsafetycategory', 'block_arup_mylearning'),
                'provider'                => get_string('provider', 'block_arup_mylearning'),
                'location'                => get_string('location', 'block_arup_mylearning'),
                'duration'                => get_string('duration', 'block_arup_mylearning'),
                'durationunits'           => get_string('durationunits', 'block_arup_mylearning'),
                'starttime'          => get_string('date:start', 'block_arup_mylearning'),
                'completiontime'          => get_string('date:completion', 'block_arup_mylearning'),
                'certificateno'           => get_string('certificateno', 'block_arup_mylearning'),
                'expirydate'              => get_string('date:expiry', 'block_arup_mylearning'),
                'description'     => get_string('descriptionription', 'block_arup_mylearning'),
        );

        $col = 0;
        foreach ($fields as $fieldname) {
            $this->_worksheets[0]->write(0, $col, $fieldname);
            $col++;
        }

        $tapshistory = \local_learningrecordstore\lrsentry::fetchbystaffid($USER->idnumber);

        $row = 1;
        foreach ($tapshistory as $th) {
            $col = 0;
            foreach ($fields as $field => $fieldname) {
                switch ($field) {
                    case 'coursename' :
                        $data = $th->providername;
                        break;
                    case 'classcategory' :
                        $data = format_string($th->classcategory);
                        break;
                    case 'starttime' :
                    case 'completiontime' :
                    case 'expirydate' :
                        $data = userdate($th->{$field});
                        break;
                    case 'duration' :
                        $data = $th->formatduration();
                        break;
                    case 'descriptionription' :
                        $data = $th->description;
                        break;
                    default :
                        $data = $th->{$field};
                        break;
                }
                if (in_array($field, ['duration'])) {
                    $this->_worksheets[0]->write_number($row, $col, $data);
                } else {
                    $this->_worksheets[0]->write_string($row, $col, $data);
                }
                $col++;
            }
            $row++;
        }

        $this->_workbook->close();
        exit;
    }

    protected function _has_content_bookmarked() {
        return true;
    }

    protected function _get_bookmarked_html() {
        global $OUTPUT;
        $content = '';
        $content .= $OUTPUT->heading(get_string('bookmarked', 'block_arup_mylearning'));
        $content .= html_writer::tag('p',
                'Coming soon, the ability to add modules you are interested in, but not ready to enrol on, to your wishlist for ease of access later.');
        return $content;
    }

    protected function _has_content_halogen() {
        global $DB, $USER;
        $dbman = $DB->get_manager();
        if ($dbman->table_exists('block_arup_mylearning_halogen')) {
            return $DB->count_records('block_arup_mylearning_halogen', array('staffid' => $USER->idnumber));
        } else {
            return false;
        }
    }

    protected function _has_content_lynda() {
        global $DB, $USER, $CFG;

        require_once($CFG->dirroot . '/local/regions/lib.php');

        $userregion = local_regions_get_user_region($USER);
        if (!isset($userregion) || !isset($userregion->geotapsregionid)) {
            return false;
        }

        if (!\local_lynda\lib::enabledforregion($userregion->geotapsregionid)) {
            return false;
        }

        return $DB->count_records('local_lynda_progress', array('userid' => $USER->id));
    }

    protected function _get_lynda_html() {
        global $USER;

        $table = new html_table();

        $table->head = array(
                get_string('course'),
                get_string('percentcomplete', 'local_lynda'),
                get_string('lastviewed', 'local_lynda')
        );

        $progresses = \local_lynda\lyndacourseprogress::fetch_all(['userid' => $USER->id]);
        foreach ($progresses as $progress) {
            if (!isset($progress->lyndacourse)) {
                continue;
            }
            $url = new \moodle_url('/local/lynda/launch.php', ['lyndacourseid' => $progress->lyndacourse->remotecourseid]);
            $linkedtitle = \html_writer::link($url, $progress->lyndacourse->title);
            $cells = [];
            $cells[] = new html_table_cell($linkedtitle);
            $cells[] = new html_table_cell($progress->percentcomplete . '%');
            $cells[] = new html_table_cell(userdate($progress->lastviewed));

            $table->data[] = new html_table_row($cells);
        }

        return html_writer::table($table);
    }

    protected function _get_halogen_html() {
        global $DB, $USER;

        $dbman = $DB->get_manager();
        $isrecordset = false;
        $halogenhistory = array();
        if ($dbman->table_exists('block_arup_mylearning_halogen')) {
            $isrecordset = true;
            $halogenhistory =
                    $DB->get_recordset('block_arup_mylearning_halogen', array('staffid' => $USER->idnumber), 'status_date DESC');
        }

        $content = '';

        $content .= html_writer::tag('p', get_string('halogen:intro', 'block_arup_mylearning'));

        $myhistoryurl = new moodle_url('/my/index.php', array('tab' => 'myhistory'));
        $myhistorylink = html_writer::link($myhistoryurl, get_string('halogen:back', 'block_arup_mylearning'),
                array('class' => 'btn btn-primary'));
        $content .= html_writer::tag('p', $myhistorylink, array('class' => 'block_arup_mylearning_tabs actions'));

        $table = new html_table();

        $table->head = array();
        $headers = array(
                array('text' => get_string('halogen:title', 'block_arup_mylearning'), 'class' => 'text-left'),
                array('text' => get_string('halogen:completed', 'block_arup_mylearning'), 'class' => 'text-center'),
                array('text' => get_string('halogen:score', 'block_arup_mylearning'), 'class' => 'text-center'),
                array('text' => get_string('halogen:passfail', 'block_arup_mylearning'), 'class' => 'text-center'),
        );
        foreach ($headers as $col => $header) {
            $table->head[$col] = new html_table_cell(str_ireplace(' ', '&nbsp;', $header['text']));
            $table->head[$col]->attributes['class'] = $header['class'];
        }

        $columncount = count($table->head);

        $table->data = array();

        // Base cell for reuse.
        $cell = new html_table_cell();
        foreach ($halogenhistory as $hh) {
            $cells = array();

            // Title.
            $cell->attributes['class'] = 'text-left';
            $cell->text = $hh->title;
            $cells[] = clone($cell);

            // Completed.
            $cell->attributes['class'] = 'text-center';
            if ($hh->status == 'Completed') {
                $cell->text = date('d M Y', strtotime($hh->status_date));
            } else {
                $cell->text = '';
            }
            $cells[] = clone($cell);

            // Score.
            if (is_numeric($hh->score)) {
                if ($hh->score <= 1) {
                    // Convert to percentage.
                    $hh->score = $hh->score * 100;
                }
                $cell->text = $hh->score . '%';
            } else {
                $cell->text = '';
            }
            $cells[] = clone($cell);

            // Pass/Fail.
            $cell->text = $hh->pass_fail;
            $cells[] = clone($cell);

            $table->data[] = new html_table_row($cells);
        }
        if ($isrecordset) {
            $halogenhistory->close();
        }

        if (empty($table->data)) {
            $cell->text = get_string('halogen:nohistory', 'block_arup_mylearning');
            $cell->colspan = $columncount;
            $cell->attributes['class'] = 'text-center';
            $table->data[] = new html_table_row(array(clone($cell)));
        }

        $content .= html_writer::table($table);

        $content .= html_writer::tag('p', $myhistorylink, array('class' => 'block_arup_mylearning_tabs actions'));

        return $content;
    }
    /* MAIN TAB FUNCTIONS - END */

    /* OVERVIEW/MYTEACHING FUNCTIONS - START */
    protected function _has_methodologies() {
        global $DB;

        if (!isset($this->_methodologyfield)) {
            $this->_methodologyfield = false;

            $fieldid = isset($this->_block->config->methodologyfield) ? $this->_block->config->methodologyfield : 0;
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
        $fieldprovidername = 'coursemetadata_field_' . $this->_methodologyfield->datatype;
        $fieldclass = new $fieldprovidername($this->_methodologyfield->id, $courseid);

        return $fieldclass->display_data();
    }

    protected function _get_taps_enrolments() {
        global $CFG, $DB, $USER;

        $return = array();

        if (!$USER->idnumber) {
            return $return;
        }

        $taps = new \mod_tapsenrol\taps();

        list($usql, $params) = $DB->get_in_or_equal($taps->get_statuses('cancelled'), SQL_PARAMS_NAMED, 'status', false);
        $sql = <<<EOS
SELECT
    lte.id, lte.bookingstatus, lte.completiontime, ltc.courseid as course
FROM
    {tapsenrol_class_enrolments} lte
INNER JOIN {local_taps_class} ltc on lte.classid = ltc.classid
WHERE
    lte.userid = :userid
    AND lte.active = 1
    AND (lte.archived = 0 OR lte.archived IS NULL)
    AND {$DB->sql_compare_text('lte.bookingstatus')} {$usql}
EOS;
        $params['userid'] = $USER->id;
        $tapsenrolments = $DB->get_records_sql($sql, $params);
        foreach ($tapsenrolments as $tapsenrolment) {
            $return[$tapsenrolment->course][$tapsenrolment->id] =
                    array($taps->get_status_type($tapsenrolment->bookingstatus) => $tapsenrolment->completiontime);
        }
        return $return;
    }
    /* OVERVIEW/MYTEACHING FUNCTIONS - END */
    /* HISTORY FUNCTIONS - END */

    /* UTILITY FUNCTIONS - START */

    protected function _get_export_button($tab) {
        $url = new moodle_url('/blocks/arup_mylearning/export.php',
                array('tab' => $tab, 'instance' => $this->_block->instance->id));
        return html_writer::link($url, get_string('export:excel', 'block_arup_mylearning'), array('class' => 'btn'));
    }

    protected function _get_add_cpd_button($tab) {
        $url = new moodle_url('/blocks/arup_mylearning/editcpd.php',
                array('tab' => $tab, 'instance' => $this->_block->instance->id));
        $link = html_writer::tag('span', get_string('addcpd', 'block_arup_mylearning'));
        return html_writer::link($url, $link, array('class' => 'btn btn-primary'));
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
    /* UTILITY FUNCTIONS - END */
}
