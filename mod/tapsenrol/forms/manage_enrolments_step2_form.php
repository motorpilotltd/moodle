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

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir.'/formslib.php');
require_once($CFG->dirroot.'/mod/tapsenrol/classes/tables.php');
require_once($CFG->dirroot.'/mod/tapsenrol/classes/user_selectors.php');

abstract class mod_tapsenrol_manage_enrolments_step2_form extends moodleform {
    protected $_class;
    protected $_context;
    protected $_renderer;
    protected $_taps;
    protected $_url;

    public function definition() {
        global $PAGE;

        $this->_taps = new \local_taps\taps();

        $this->_context = context_module::instance($this->_customdata['id']);

        $this->_class = $this->_customdata['class'];

        $this->_renderer = $PAGE->get_renderer('mod_tapsenrol');

        $this->_url = new moodle_url(
            '/mod/tapsenrol/manage_enrolments_process.php',
            array(
                'id' => $this->_customdata['id'],
                'classid' => $this->_class->classid,
                'type' => $this->_customdata['type'],
            )
        );
    }

    protected function _get_class_html() {
        global $DB;

        if (!$this->_class) {
            return '';
        }

        $html = html_writer::start_tag('div', array('class' => 'tapsenrol_manage_enrolments_class mform', 'style' => 'margin-top: -10px;'));

        $classname = html_writer::tag('div', get_string('classname', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $classname .= html_writer::tag('div', $this->_class->classname, array('class' => 'felement'));
        $html .= html_writer::tag('div', $classname, array('class' => 'classname fitem'));

        $location = html_writer::tag('div', get_string('location', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $this->_class->location = $this->_class->location ? $this->_class->location : get_string('tbc', 'tapsenrol');
        $location .= html_writer::tag('div', $this->_class->location, array('class' => 'felement'));
        $html .= html_writer::tag('div', $location, array('class' => 'location fitem'));

        if (!is_null($this->_class->trainingcenter)) {
            $trainingcenter = html_writer::tag('div', get_string('trainingcenter', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
            $trainingcenter .= html_writer::tag('div', $this->_class->trainingcenter, array('class' => 'felement'));
            $html .= html_writer::tag('div', $trainingcenter, array('class' => 'trainingcenter fitem'));
        }

        $date = html_writer::tag('div', get_string('date', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        if (!$this->_class->classstarttime) {
            $this->_class->date = get_string('waitinglist:classroom', 'tapsenrol');
        } else {
            try {
                $timezone = new DateTimeZone($this->_class->usedtimezone);
            } catch (Exception $e) {
                $timezone = new DateTimeZone(date_default_timezone_get());
            }
            $startdatetime = new DateTime(null, $timezone);
            $startdatetime->setTimestamp($this->_class->classstarttime);
            $this->_class->date = $startdatetime->format('d M Y');
            $this->_class->date .= ($this->_class->classstarttime != $this->_class->classstartdate) ? $startdatetime->format(' H:i T') : '';
            // Show UTC as GMT for clarity.
            $this->_class->date = str_replace('UTC', 'GMT', $this->_class->date);
        }
        $date .= html_writer::tag('div', $this->_class->date, array('class' => 'felement'));
        $html .= html_writer::tag('div', $date, array('class' => 'date fitem'));

        $duration = html_writer::tag('div', get_string('duration', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $this->_class->duration = ($this->_class->classduration) ? (float) $this->_class->classduration . ' ' . $this->_class->classdurationunits : get_string('tbc', 'tapsenrol');
        $duration .= html_writer::tag('div', $this->_class->duration, array('class' => 'felement'));
        $html .= html_writer::tag('div', $duration, array('class' => 'duration fitem'));

        $cost = html_writer::tag('div', get_string('cost', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $this->_class->cost = $this->_class->price ? $this->_class->price.' '.$this->_class->currencycode : '-';
        $cost .= html_writer::tag('div', $this->_class->cost, array('class' => 'felement'));
        $html .= html_writer::tag('div', $cost, array('class' => 'cost fitem'));

        $tempseatsremaining = $this->_taps->get_seats_remaining($this->_class->classid);
        $seatsremainingtext = ($tempseatsremaining === -1 ? get_string('unlimited', 'tapsenrol') : $tempseatsremaining);
        $seatsremaining = html_writer::tag('div', get_string('seatsremaining', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $seatsremaining .= html_writer::tag('div', $seatsremainingtext, array('class' => 'felement'));
        $html .= html_writer::tag('div', $seatsremaining, array('class' => 'seatsremaining fitem'));

        $enrolments = html_writer::tag('div', get_string('enrolments', 'tapsenrol'). ':', array('class' => 'fitemtitle'));

        list($in, $inparams) = $DB->get_in_or_equal(
            $this->_taps->get_statuses('cancelled'),
            SQL_PARAMS_NAMED, 'status', false
        );
        $compare = $DB->sql_compare_text('bookingstatus');
        $params = array_merge(
            array('classid' => $this->_class->classid),
            $inparams
        );
        $enrolmentstext =
            $DB->count_records_select('local_taps_enrolment', "classid = :classid AND (archived = 0 OR archived IS NULL) AND {$compare} {$in}", $params);
        $enrolments .= html_writer::tag('div', $enrolmentstext, array('class' => 'felement'));
        $html .= html_writer::tag('div', $enrolments, array('class' => 'enrolments fitem'));

        $html .= html_writer::end_tag('div'); // End div tapsenrol_manage_enrolments_class.

        return $html;
    }

    protected function _get_current_user_html($type = 'enrol') {
        global $DB;

        $table = new tapsenrol_enrolments_table_sql('tapsenrol-current-enrolments');

        $usernamefields = get_all_user_name_fields(true, 'u');
        $fields = "lte.*, u.id as userid, {$usernamefields}, u.phone1, u.icq, u.department, u.address, tit.timeenrolled";

        $from = <<<EOF
    {local_taps_enrolment} lte
JOIN
    {user} u
    ON u.idnumber = lte.staffid
LEFT JOIN
    {tapsenrol_iw_tracking} tit
    ON tit.enrolmentid = lte.enrolmentid
EOF;
        $where = '(lte.archived = 0 OR lte.archived IS NULL) AND lte.classid = :classid';
        $params = array('classid' => $this->_class->classid);

        switch ($type) {
            case 'cancel':
            case 'move':
                list($in, $inparams) = $DB->get_in_or_equal(
                    array_merge($this->_taps->get_statuses('cancelled'), $this->_taps->get_statuses('attended')),
                    SQL_PARAMS_NAMED, 'status', false
                );
                $compare = $DB->sql_compare_text('lte.bookingstatus');

                $where .= " AND {$compare} {$in}";

                $params = array_merge(
                    $params,
                    $inparams
                );
                break;
            case 'waitlist':
                $fields .= ', tit.sponsorfirstname, tit.sponsorlastname';

                list($in, $inparams) = $DB->get_in_or_equal(
                    $this->_taps->get_statuses('waitlisted'),
                    SQL_PARAMS_NAMED, 'status'
                );
                $compare = $DB->sql_compare_text('lte.bookingstatus');

                $where .= " AND {$compare} {$in}";

                $params = array_merge(
                    $params,
                    $inparams
                );
                break;
            case 'delete':
                if ($this->_class->classstatus === 'Planned') {
                    // Classroom/Elearning, Planned, All - requested/waitlisted/cancelled.
                    list($in, $inparams) = $DB->get_in_or_equal(
                        array_merge($this->_taps->get_statuses('requested'), $this->_taps->get_statuses('waitlisted'), $this->_taps->get_statuses('cancelled')),
                        SQL_PARAMS_NAMED, 'status', true
                    );
                    $compare = $DB->sql_compare_text('lte.bookingstatus');
                } else if ($this->_class->classstarttime > time() || !has_capability('mod/tapsenrol:deleteattendedenrolments', $this->_context)) {
                    // Classroom/Elearning, Normal, Future - not attended.
                    // Doesn't have mod/tapsenrol:deleteattendedenrolments - not attended.
                    list($in, $inparams) = $DB->get_in_or_equal(
                        $this->_taps->get_statuses('attended'),
                        SQL_PARAMS_NAMED, 'status', false
                    );
                    $compare = $DB->sql_compare_text('lte.bookingstatus');
                }
                if (!empty($compare) && !empty($in) && !empty($inparams)) {
                    $where .= " AND {$compare} {$in}";

                    $params = array_merge(
                        $params,
                        $inparams
                    );
                }
                break;
        }

        $table->set_sql($fields, $from, $where, $params);

        $columns = array('fullname', 'staffid', 'phone1', 'costcentre', 'address', 'timeenrolled', 'bookingstatus');
        $headers = array(
            '',
            get_string('manageenrolments:table:staffid', 'tapsenrol'),
            get_string('manageenrolments:table:phone1', 'tapsenrol'),
            get_string('manageenrolments:table:costcentre', 'tapsenrol'),
            get_string('manageenrolments:table:address', 'tapsenrol'),
            get_string('manageenrolments:table:timeenrolled', 'tapsenrol'),
            get_string('manageenrolments:table:bookingstatus', 'tapsenrol'),
        );
        if ($type == 'waitlist') {
            array_splice($columns, 6, 0, 'sponsor');
            array_splice($headers, 6, 0, get_string('manageenrolments:table:sponsor', 'tapsenrol'));
            array_splice($columns, 2, 1);
            array_splice($headers, 2, 1);
        }
        switch ($type) {
            case 'cancel':
            case 'waitlist':
            case 'move':
            case 'delete':
                $columns[] = $type;
                $attributes = array(
                    'id' => 'tapsenrol-checkbox-selectall',
                    'type' => 'checkbox'
                );
                $checkbox = html_writer::empty_tag('input', $attributes);
                $headers[] = get_string("manageenrolments:table:{$type}", 'tapsenrol') . '<br>' . $checkbox;
                break;
        }
        $table->define_columns($columns);
        $table->column_style_all('text-align', 'left');
        $table->text_sorting('bookingstatus');
        $table->define_headers($headers);
        $table->define_baseurl($this->_url);

        $table->sortable(true, 'lastname', SORT_ASC);
        $table->is_collapsible = false;

        $table->useridfield = 'userid';

        $grandtotal = 10;
        switch ($type) {
            case 'cancel':
                $table->no_sorting('cancel');
                $table->column_style('cancel', 'text-align', 'center');

                if ($table->countsql === null) {
                    $table->countsql = 'SELECT COUNT(1) FROM '.$table->sql->from.' WHERE '.$table->sql->where;
                    $table->countparams = $table->sql->params;
                }
                $grandtotal = $DB->count_records_sql($table->countsql, $table->countparams);
                break;
            case 'waitlist':
                $table->no_sorting('waitlist');
                $table->column_style('waitlist', 'text-align', 'center');

                if ($table->countsql === null) {
                    $table->countsql = 'SELECT COUNT(1) FROM '.$table->sql->from.' WHERE '.$table->sql->where;
                    $table->countparams = $table->sql->params;
                }
                $grandtotal = $DB->count_records_sql($table->countsql, $table->countparams);
                break;
            case 'move':
                $table->no_sorting('move');
                $table->column_style('move', 'text-align', 'center');

                if ($table->countsql === null) {
                    $table->countsql = 'SELECT COUNT(1) FROM '.$table->sql->from.' WHERE '.$table->sql->where;
                    $table->countparams = $table->sql->params;
                }
                $grandtotal = $DB->count_records_sql($table->countsql, $table->countparams);
                break;
            case 'delete':
                $table->no_sorting('delete');
                $table->column_style('delete', 'text-align', 'center');

                if ($table->countsql === null) {
                    $table->countsql = 'SELECT COUNT(1) FROM '.$table->sql->from.' WHERE '.$table->sql->where;
                    $table->countparams = $table->sql->params;
                }
                $grandtotal = $DB->count_records_sql($table->countsql, $table->countparams);
                break;
        }

        ob_start();
        echo html_writer::start_div('tapsenrol-current-enrolments');
        $table->out($grandtotal, false);
        echo html_writer::end_div();
        return ob_get_clean();
    }

    protected function _get_user_selector_html($excludeusersql, $excludeuserparams) {
        $options = [
            'excludeusersql' => $excludeusersql,
            'excludeuserparams' => $excludeuserparams,
        ];
        $potentialuserselector = new tapsenrol_enrol_user_selector('users', $options);

        $html = $potentialuserselector->display(true);

        return $html;
    }
}

class mod_tapsenrol_manage_enrolments_enrol_form extends mod_tapsenrol_manage_enrolments_step2_form {

    public function definition() {
        global $DB;

        parent::definition();

        $mform =& $this->_form;

        $mform->addElement('hidden', 'id', $this->_customdata['id']);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'classid', $this->_class->classid);
        $mform->setType('classid', PARAM_INT);

        $mform->addElement('hidden', 'type', $this->_customdata['type']);
        $mform->setType('type', PARAM_ALPHA);

        $mform->addElement('header', 'header-class-details', get_string('manageenrolments:classdetails', 'tapsenrol'));
        $mform->setExpanded('header-class-details', false);

        $mform->addElement('html', $this->_get_class_html());

        $mform->addElement('header', 'header-current-enrolments', get_string('manageenrolments:currentenrolments', 'tapsenrol'));
        if (optional_param('tsort', '', PARAM_RAW) || !is_null(optional_param('page', null, PARAM_RAW))) {
            $mform->setExpanded('header-current-enrolments', true);
        } else {
            $mform->setExpanded('header-current-enrolments', false);
        }

        $mform->addElement('html', $this->_get_current_user_html());

        $mform->addElement('header', 'header-enrol', get_string('manageenrolments:enrol:header', 'tapsenrol'));
        $mform->setExpanded('header-enrol', true);

        // Select users.
        // Exclude not cancelled users.
        list($in, $inparams) = $DB->get_in_or_equal(
            $this->_taps->get_statuses('cancelled'),
            SQL_PARAMS_NAMED, 'status', false
        );
        $compare = $DB->sql_compare_text('bookingstatus');
        $excludeuserparams = array_merge(
            array('classid' => $this->_class->classid),
            $inparams
        );
        $excludeusersql = "SELECT DISTINCT u.id
                             FROM {user} u
                             JOIN {local_taps_enrolment} lte ON lte.staffid = u.idnumber
                            WHERE classid = :classid AND (archived = 0 OR archived IS NULL) AND {$compare} {$in}";
        $mform->addElement('html', $this->_get_user_selector_html($excludeusersql, $excludeuserparams));

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('manageenrolments:enrol:button', 'tapsenrol'));
        $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('manageenrolments:generic:button:cancel', 'tapsenrol'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }
}

class mod_tapsenrol_manage_enrolments_cancel_form extends mod_tapsenrol_manage_enrolments_step2_form {

    public function definition() {
        parent::definition();

        $mform =& $this->_form;

        $mform->addElement('hidden', 'id', $this->_customdata['id']);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'classid', $this->_class->classid);
        $mform->setType('classid', PARAM_INT);

        $mform->addElement('hidden', 'type', $this->_customdata['type']);
        $mform->setType('type', PARAM_ALPHA);

        $mform->addElement('header', 'header-class-details', get_string('manageenrolments:classdetails', 'tapsenrol'));
        $mform->setExpanded('header-class-details', false);

        $mform->addElement('html', $this->_get_class_html());

        $mform->addElement('header', 'header-cancel', get_string('manageenrolments:cancel:header', 'tapsenrol'));
        $mform->setExpanded('header-cancel', true);

        $mform->addElement('html', $this->_get_current_user_html('cancel'));

        if ($this->_class->classstarttime < time()) {
            $statuses = ['' => get_string('choosedots'), 'Cancelled' => 'Cancelled', 'No Show' => 'No Show'];
        } else {
            $statuses = ['Cancelled' => 'Cancelled'];
        }
        $mform->addElement('select', 'status', get_string('manageenrolments:cancel:status', 'tapsenrol'), $statuses, array('class' => 'class-select'));
        $mform->addRule('status', null, 'required', null, 'client');

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('manageenrolments:cancel:button', 'tapsenrol'));
        $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('manageenrolments:generic:button:cancel', 'tapsenrol'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }
}

class mod_tapsenrol_manage_enrolments_waitlist_form extends mod_tapsenrol_manage_enrolments_step2_form {

    public function definition() {
        parent::definition();

        $mform =& $this->_form;

        $mform->addElement('hidden', 'id', $this->_customdata['id']);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'classid', $this->_class->classid);
        $mform->setType('classid', PARAM_INT);

        $mform->addElement('hidden', 'type', $this->_customdata['type']);
        $mform->setType('type', PARAM_ALPHA);

        $mform->addElement('header', 'header-class-details', get_string('manageenrolments:classdetails', 'tapsenrol'));
        $mform->setExpanded('header-class-details', false);

        $mform->addElement('html', $this->_get_class_html());

        $mform->addElement('header', 'header-waitlist', get_string('manageenrolments:waitlist:header', 'tapsenrol'));
        $mform->setExpanded('header-waitlist', true);

        $seatsremaining = $this->_taps->get_seats_remaining($this->_class->classid);
        $a = new stdClass();
        $a->value = $seatsremaining;
        $a->text = ($seatsremaining === -1 ? get_string('unlimited', 'tapsenrol') : $seatsremaining);
        $alerttype = ($seatsremaining === 0) ? 'alert-danger' : (($seatsremaining > 0 && $seatsremaining < 3) ? 'alert-warning' : 'alert-info');
        $mform->addElement(
                'html',
                $this->_renderer->alert(
                        get_string('manageenrolments:waitlist:seatsremaining', 'tapsenrol', $a),
                        $alerttype,
                        false
                        )
                );

        $mform->addElement('html', $this->_get_current_user_html('waitlist'));

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('manageenrolments:waitlist:button', 'tapsenrol'));
        $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('manageenrolments:generic:button:cancel', 'tapsenrol'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }
}

class mod_tapsenrol_manage_enrolments_move_form extends mod_tapsenrol_manage_enrolments_step2_form {
    protected $_classes;

    public function definition() {
        parent::definition();

        $this->_classes = $this->_customdata['classes'];

        $mform =& $this->_form;

        $mform->addElement('hidden', 'id', $this->_customdata['id']);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'classid', $this->_class->classid);
        $mform->setType('classid', PARAM_INT);

        $mform->addElement('hidden', 'type', $this->_customdata['type']);
        $mform->setType('type', PARAM_ALPHA);

        $mform->addElement('header', 'header-class-details', get_string('manageenrolments:classdetails', 'tapsenrol'));
        $mform->setExpanded('header-class-details', false);

        $mform->addElement('html', $this->_get_class_html());

        $mform->addElement('header', 'header-move', get_string('manageenrolments:move:header', 'tapsenrol'));
        $mform->setExpanded('header-move', true);

        $mform->addElement('html', $this->_get_current_user_html('move'));

        $classes = array('' => get_string('choosedots')) + $this->_classes;
        $mform->addElement('select', 'moveto', get_string('manageenrolments:move:toclass', 'tapsenrol'), $classes, array('class' => 'class-select'));
        $mform->addRule('moveto', null, 'required', null, 'client');
        $mform->addElement('html', $this->_get_class_placeholder());

        $mform->addElement('checkbox', 'resendemails', get_string('manageenrolments:move:resendemails', 'tapsenrol'));
        $mform->setDefault('resendemails', 1);

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('manageenrolments:waitlist:button', 'tapsenrol'));
        $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('manageenrolments:generic:button:cancel', 'tapsenrol'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

    protected function _get_class_placeholder() {
        $html = html_writer::start_tag('div', array('class' => 'tapsenrol_manage_enrolments_class mform hide'));

        $classname = html_writer::tag('div', get_string('classname', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $classname .= html_writer::tag('div', '', array('class' => 'felement'));
        $html .= html_writer::tag('div', $classname, array('class' => 'classname fitem'));

        $location = html_writer::tag('div', get_string('location', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $location .= html_writer::tag('div', '', array('class' => 'felement'));
        $html .= html_writer::tag('div', $location, array('class' => 'location fitem'));

        $trainingcenter = html_writer::tag('div', get_string('trainingcenter', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $trainingcenter .= html_writer::tag('div', '', array('class' => 'felement'));
        $html .= html_writer::tag('div', $trainingcenter, array('class' => 'trainingcenter fitem'));

        $date = html_writer::tag('div', get_string('date', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $date .= html_writer::tag('div', '', array('class' => 'felement'));
        $html .= html_writer::tag('div', $date, array('class' => 'date fitem'));

        $duration = html_writer::tag('div', get_string('duration', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $duration .= html_writer::tag('div', '', array('class' => 'felement'));
        $html .= html_writer::tag('div', $duration, array('class' => 'duration fitem'));

        $cost = html_writer::tag('div', get_string('cost', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $cost .= html_writer::tag('div', '', array('class' => 'felement'));
        $html .= html_writer::tag('div', $cost, array('class' => 'cost fitem'));

        $seatsremaining = html_writer::tag('div', get_string('seatsremaining', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $seatsremaining .= html_writer::tag('div', '', array('class' => 'felement'));
        $html .= html_writer::tag('div', $seatsremaining, array('class' => 'seatsremaining fitem'));

        $enrolments = html_writer::tag('div', get_string('enrolments', 'tapsenrol'). ':', array('class' => 'fitemtitle'));
        $enrolments .= html_writer::tag('div', '', array('class' => 'felement'));
        $html .= html_writer::tag('div', $enrolments, array('class' => 'enrolments fitem'));

        $html .= html_writer::end_tag('div'); // End div tapsenrol_manage_enrolments_class.

        return $html;
    }
}

class mod_tapsenrol_manage_enrolments_update_form extends mod_tapsenrol_manage_enrolments_step2_form {
    protected $_classes;

    public function definition() {
        parent::definition();

        $mform =& $this->_form;

        $mform->addElement('html', 'Coming soon...');
    }
}

class mod_tapsenrol_manage_enrolments_delete_form extends mod_tapsenrol_manage_enrolments_step2_form {

    public function definition() {
        parent::definition();

        $mform =& $this->_form;

        $mform->addElement('hidden', 'id', $this->_customdata['id']);
        $mform->setType('id', PARAM_INT);

        $mform->addElement('hidden', 'classid', $this->_class->classid);
        $mform->setType('classid', PARAM_INT);

        $mform->addElement('hidden', 'type', $this->_customdata['type']);
        $mform->setType('type', PARAM_ALPHA);

        $mform->addElement('header', 'header-class-details', get_string('manageenrolments:classdetails', 'tapsenrol'));
        $mform->setExpanded('header-class-details', false);

        $mform->addElement('html', $this->_get_class_html());

        $mform->addElement('header', 'header-delete', get_string('manageenrolments:delete:header', 'tapsenrol'));
        $mform->setExpanded('header-delete', true);

        $mform->addElement('html', $this->_get_current_user_html('delete'));

        $mform->addElement('checkbox', 'sendemails', get_string('manageenrolments:delete:sendemails', 'tapsenrol'));
        $mform->setDefault('sendemails', 1);

        $buttonarray = array();
        $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('manageenrolments:delete:button', 'tapsenrol'));
        $buttonarray[] = &$mform->createElement('cancel', 'cancel', get_string('manageenrolments:generic:button:cancel', 'tapsenrol'));
        $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);
        $mform->closeHeaderBefore('buttonar');
    }

}