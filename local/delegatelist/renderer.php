<?php

if (!defined('MOODLE_INTERNAL')) {
    die('Direct access to this script is forbidden.');    ///  It must be included from a Moodle page
}

require_once 'lib.php';

class local_delegatelist_renderer extends plugin_renderer_base {
    
    public function navigation_buttons(delegate_list $dl) {
        if (!$dl->has_capability('manager', 'teacher')) {
            return '';
        }
        $items = array();
        $items[] = $this->activitycompletion_button($dl->get_completion_url());
        $tapscompletion = $dl->get_taps_completion_mod();
        if ($tapscompletion) {
            $items[] = $this->markattendance_button($dl->get_attendance_url($tapscompletion));
        }
        $items[] = $this->printregister_button($dl->get_print_url(), $dl->get_active_class());
        return html_writer::alist($items, array('class'=>'navigationbuttons'));
    }
    
    public function navbutton(moodle_url $url, $text, $icon, $data = array()) {
        $params = array('class'=>$icon);
        foreach($data as $key=>$value) {
            $params['data-'.$key] = $value;
        }
        return html_writer::link($url, html_writer::tag('i', '', array('class' => 'fa fa-lg fa-'.$icon)) . $text, $params);
    }
    
    public function activitycompletion_button(moodle_url $url) {
        return $this->navbutton($url, get_string('button:activitycompletion', 'local_delegatelist'), 'line-chart');
    }
    
    public function markattendance_button(moodle_url $url) {
        return $this->navbutton($url, get_string('button:markattendance', 'local_delegatelist'), 'check-square-o');
    }
    
    public function printregister_button(moodle_url $url, $currentclass) {
        $data = array();
        if (!empty($currentclass)) {
            $data['classid'] = $currentclass->classid;
        }
        $button = $this->navbutton($url, get_string('button:printregister', 'local_delegatelist'), 'print', $data);
        return str_replace('/index.php', '/print.php', $button);
    }
    
    public function delegate_list(delegate_list $delegatelist) {
        $activeclass = $delegatelist->get_active_class();
        $activeclassid = empty($activeclass)? 0 : $activeclass->classid;
        if (!empty($activeclass) && $delegatelist->get_function() == 'display' && $delegatelist->has_capability('manager', 'teacher')) {
            echo $this->manage_enrolments($delegatelist);
            echo $this->filters($delegatelist);
        }
        $dl = $delegatelist->get_list_table();
        if (!empty($dl)) {
            echo html_writer::start_div('', array(
                'id' => 'delegate-list-class-list',
                'class' => 'clear',
                'data-dates' => $delegatelist->get_class_dates(),
                'data-duration' => $delegatelist->get_class_duration(),
                'data-location' => $delegatelist->get_class_location(),
                'data-trainingcenter' => $delegatelist->get_class_trainingcenter(),
                'data-classid' => $activeclassid,
            ));

            $dl->out($dl->custom_page_size(), false);
            echo html_writer::end_div();
        }
    }
    
    public function summary(delegate_list $delegatelist) {
        $rows  = $this->render_course_row($delegatelist->get_course_name());
        if ($delegatelist->get_function() == 'print') {
            $rows .= $this->render_groups_row_print($delegatelist->get_active_class());
        } else {
            $rows .= $this->render_groups_row($delegatelist->get_class_menu(), $delegatelist->get_active_class(), $delegatelist->get_url('classid'));
        }
        $rows .= $this->render_dates_row($delegatelist->get_class_dates());
        $rows .= $this->render_duration_row($delegatelist->get_class_duration());
        $rows .= $this->render_location_row($delegatelist->get_class_location());
        $rows .= $this->render_trainingcenter_row($delegatelist->get_class_trainingcenter());
        return html_writer::div($rows, 'container-fluid delegate-summary');
    }

    public function manage_enrolments(delegate_list $delegatelist) {
        $url = $delegatelist->get_manage_enrolments_url();
        if (!$url) {
            return '';
        }
        return html_writer::link(
                $url,
                get_string('manageenrolments', 'tapsenrol'),
                ['class' => 'btn btn-primary']);
    }
    
    public function filters(delegate_list $delegatelist) {
        $filters = $delegatelist->get_filters();
        $filterselected = empty($filters['bookingstatus']) ? '' : $filters['bookingstatus'];
        $filter = new single_select($delegatelist->get_url('bookingstatus'), "filters[bookingstatus]", $delegatelist->get_statuses_list(),
                $filterselected,
                array('' => get_string('all', 'local_delegatelist')), 'formchangefilter');
        $filter->set_label(get_string('label:filterbookingstatus', 'local_delegatelist'));
        return $this->render_single_select($filter);
    }
    
    public function render_course_row($coursename) {
        $cols  = html_writer::div(get_string('course'), 'col-xs-3 mdl-right');
        $cols .= html_writer::div($coursename, 'col-xs-9 mdl-left');
        return html_writer::div($cols, 'row-fluid');
    }
    
    public function render_groups_row($classes, $currentclass, $url) {
        $classid = empty($currentclass) ? 0 : $currentclass->classid;
        $select = new single_select($url, 'classid', $classes, $classid, array('' => 'choosedots'), 'formchangeclass');
        $select->attributes['autocomplete'] = 'off';
        $cols  = html_writer::div(get_string('class', 'local_delegatelist'), 'col-xs-3 mdl-right');
        $cols .= html_writer::div($this->render_single_select($select), 'col-xs-9 mdl-left');
        return html_writer::div($cols, 'row-fluid');
    }

    public function render_groups_row_print($currentclass) {
        $cols  = html_writer::div(get_string('class', 'local_delegatelist'), 'col-xs-3 mdl-right');
        $cols .= html_writer::div($currentclass->classname, 'col-xs-9 mdl-left');
        return html_writer::div($cols, 'row-fluid');
    }
    
    public function render_dates_row($dates) {
        $cols  = html_writer::div(get_string('dates', 'local_delegatelist'), 'col-xs-3 mdl-right');
        $cols .= html_writer::div($dates, 'col-xs-9 mdl-left', array('id' => 'delegate-list-class-dates'));
        return html_writer::div($cols, 'row-fluid');
    }
    
    public function render_duration_row($duration) {
        $cols  = html_writer::div(get_string('duration', 'local_delegatelist'), 'col-xs-3 mdl-right');
        $cols .= html_writer::div($duration, 'col-xs-9 mdl-left', array('id' => 'delegate-list-class-duration'));
        return html_writer::div($cols, 'row-fluid');
    }

    public function render_location_row($location) {
        $extraclass = empty($location) ? ' hide' : '';
        $cols  = html_writer::div(get_string('location', 'local_delegatelist'), 'col-xs-3 mdl-right');
        $cols .= html_writer::div($location, 'col-xs-9 mdl-left', array('id' => 'delegate-list-class-location'));
        return html_writer::div($cols, 'row-fluid'.$extraclass);
    }

    public function render_trainingcenter_row($trainingcenter) {
        $extraclass = empty($trainingcenter) ? ' hide' : '';
        $cols  = html_writer::div(get_string('trainingcenter', 'local_delegatelist'), 'col-xs-3 mdl-right');
        $cols .= html_writer::div($trainingcenter, 'col-xs-9 mdl-left', array('id' => 'delegate-list-class-trainingcenter'));
        return html_writer::div($cols, 'row-fluid'.$extraclass);
    }

    // Jacked from lib/outputrenderers.php to remove YUI formautosubmit
    protected function render_single_select(single_select $select) {
        $select = clone($select);
        if (empty($select->formid)) {
            $select->formid = html_writer::random_id('single_select_f');
        }

        $output = '';
        $params = $select->url->params();
        if ($select->method === 'post') {
            $params['sesskey'] = sesskey();
        }
        foreach ($params as $name=>$value) {
            $output .= html_writer::empty_tag('input', array('type'=>'hidden', 'name'=>$name, 'value'=>$value));
        }

        if (empty($select->attributes['id'])) {
            $select->attributes['id'] = html_writer::random_id('single_select');
        }

        if ($select->disabled) {
            $select->attributes['disabled'] = 'disabled';
        }

        if ($select->tooltip) {
            $select->attributes['title'] = $select->tooltip;
        }

        $select->attributes['class'] = '';
        if ($select->class) {
            $select->attributes['class'] .= ' ' . $select->class;
        }

        if ($select->label) {
            $output .= html_writer::label($select->label, $select->attributes['id'], false, $select->labelattributes);
        }

        if ($select->helpicon instanceof help_icon) {
            $output .= $this->render($select->helpicon);
        }
        $output .= html_writer::select($select->options, $select->name, $select->selected, $select->nothing, $select->attributes);

        $go = html_writer::empty_tag('input', array('type'=>'submit', 'value'=>get_string('go')));
        $output .= html_writer::tag('noscript', html_writer::tag('div', $go), array('class' => 'inline'));

        // then div wrapper for xhtml strictness
        $output = html_writer::tag('div', $output);

        // now the form itself around it
        if ($select->method === 'get') {
            $url = $select->url->out_omit_querystring(true); // url without params, the anchor part allowed
        } else {
            $url = $select->url->out_omit_querystring();     // url without params, the anchor part not allowed
        }
        $formattributes = array('method' => $select->method,
                                'action' => $url,
                                'id'     => $select->formid);
        $output = html_writer::tag('form', $output, $formattributes);

        // and finally one more wrapper with class
        return html_writer::tag('div', $output, array('class' => $select->class));
    }

    public function alert($message, $type = 'alert-warning', $exitbutton = true) {
        $class = "alert {$type} fade in";
        $button = '';
        if ($exitbutton) {
            $button .= html_writer::tag('button', '&times;', array('class' => 'close', 'data-dismiss' => 'alert', 'type' => 'button'));
        }
        return html_writer::tag('div', $button.$message, array('class' => $class));
    }

    public function close_window() {
        $html = html_writer::start_div('container-fluid delegate-close-window hidden-print');
        $html .= html_writer::tag('button', get_string('closewindow', 'local_delegatelist'), array('class' => 'close-window btn btn-warning'));
        $html .= html_writer::end_div();
        return $html;
    }
}
