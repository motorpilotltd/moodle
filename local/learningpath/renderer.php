<?php

defined('MOODLE_INTERNAL') || die();

class local_learningpath_renderer extends plugin_renderer_base {

    public function alert($message, $type = 'alert-warning', $exitbutton = true) {
        $class = "alert {$type} fade in";
        $button = '';
        if ($exitbutton) {
            $button .= html_writer::tag('button', '&times;', array('class' => 'close', 'data-dismiss' => 'alert', 'type' => 'button'));
        }
        return html_writer::tag('div', $button.$message, array('class' => $class));
    }

    public function courses($coursetype, $courses) {
        $html = '';
        if (get_string_manager()->string_exists("heading:{$coursetype}", 'local_learningpath')) {
            $html .= html_writer::tag('h3', get_string("heading:{$coursetype}", 'local_learningpath'));
        }
        if (empty($courses)) {
            if (get_string_manager()->string_exists("nomodules:{$coursetype}", 'local_learningpath')) {
                $html .= html_writer::tag('p', get_string("nomodules:{$coursetype}", 'local_learningpath'));
            } else {
                $html .= html_writer::tag('p', get_string('nomodules', 'local_learningpath'));
            }
        }
        foreach ($courses as $course) {
            $courselinkclasses = $course->visible ? array() : array('class' => 'dimmed');
            $courselink = html_writer::link(new moodle_url('/local/learningpath/view_course.php', array('id'=>$course->id)), format_string($course->fullname), $courselinkclasses);
            $coursecontentinner = html_writer::tag('span', $courselink, array());
            if ($course->summary) {
                $coursecontentinner .= html_writer::tag('span', $course->summary, array('class'=>'arup_course_summary'));
            } else {
                $coursecontentinner .= html_writer::tag(
                    'span',
                    html_writer::tag('p', get_string('nosummary', 'local_learningpath')),
                    array('class'=>'arup_course_summary')
                );
            }

            $config = get_config('local_learningpath');
            $regionpos = empty($config->regions_position) ? 1 : $config->regions_position;

            $regiondata = '';
            if (REGIONS_INSTALLED && !empty($config->regions_info) && $course->regions) {
                $regiondata .= html_writer::tag('span', get_string('regions', 'local_regions').':');
                $regiondata .= $course->regions;
            }

            $metadata = '';
            if (COURSEMETADATA_INSTALLED && !empty($config->coursemetadata_info)) {
                $metadatacount = 1;
                foreach ($course->metadata as $data) {
                    if ($metadatacount == $regionpos) {
                        $metadata .= $regiondata;
                    }
                    if (!empty($data->data)) {
                        $metadata .= html_writer::tag('span', $data->name.':');
                        $metadata .= str_ireplace(',', ', ', $data->data);
                    }
                    $metadatacount++;
                }
                if ($regionpos >= $metadatacount) {
                    $metadata .= $regiondata;
                }
            } else {
                $metadata .= $regiondata;
            }

            if ($metadata) {
                $coursecontentinner .= html_writer::tag('span', $metadata, array('class' => 'arup_course_metadata'));
            }

            $coursecontent = html_writer::tag('li', $coursecontentinner, array('class' => "arup_course_name arup_course_{$coursetype}"));
            $extraclass = '';
            if (!$course->visible) {
                $extraclass = ' arup_course_hidden';
                $coursecontent .= html_writer::div(
                    get_string('hiddencourse', 'local_learningpath', core_text::strtoupper(get_string('course'))),
                    'arup_course_hidden_label'
                );
            }
            
            $html .= html_writer::tag('ul', $coursecontent,  array('class' => "arup_course{$extraclass}"));
        }
        
        return $html;
    }

    public function back_to_learningpath($id) {
        $url = new moodle_url('/local/learningpath/view.php', array('id' => $id));
        $link = html_writer::link($url, get_string('backtolearningpath', 'local_learningpath'));
        return html_writer::tag('p', $link, array('class' => 'learningpath-return'));
    }

}