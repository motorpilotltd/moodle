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

require_once("{$CFG->dirroot}/local/accordion/lib.php");

class local_accordion_renderer extends plugin_renderer_base {

    public function editing_block($category) {
        $params = array('sesskey' => sesskey());
        if ($category) {
            $params['categoryid'] = $category->id;
        }
        $url = new moodle_url('/course/management.php', $params);

        $block = new block_contents(array('id' => 'arup_editing_block', 'class' => 'block'));
        $block->title = '';
        $block->content = html_writer::link($url, get_string('gotoeditpage', 'local_accordion'));
        $block->footer = '';

        return $block;
    }

    public function buildarea_block($categoryid) {
        $params = array('catalogue' => 'accordion');
        if ($categoryid) {
            $params['id'] = $categoryid;
        }
        $url = new moodle_url('/local/accordion/index.php', $params);

        $block = new block_contents(array('id' => 'arup_buildarea_block', 'class' => 'block'));
        $block->title = '';
        $block->content = html_writer::link($url, get_string('gotobuildarea', 'local_accordion', get_string('buildarea', 'local_accordion')));
        $block->footer = '';

        return $block;
    }

    public function learningpath_block($regionid) {
        // Plugin local_wa_learning_path has all/global the other way around.
        switch ($regionid) {
            case -1:
                $regionid = 0;
                break;
            case 0:
                $regionid = -1;
                break;
        }
        $params = array(
            'c' => 'learning_path',
            'mode' => 'tiles',
            'region' => $regionid,
            );
        $url = new moodle_url('/local/wa_learning_path/index.php', $params);

        $block = new block_contents(array('id' => 'arup_learningpath_block', 'class' => 'block'));
        $block->title = '';
        $imgurl = new moodle_url('/local/accordion/pix/learningpath_block.svg');
        $img = html_writer::img(
                $imgurl,
                get_string('viewalllearningpaths', 'local_accordion'),
                array('title' => get_string('viewalllearningpaths', 'local_accordion')));
        $block->content .= html_writer::link($url, $img);
        $block->footer = '';

        return $block;
    }

    public function render_category(category $category) {
        $lpcount = 0;
        $totallpcount = count($category->learningpaths);
        $coursecount = 0;
        $totalcoursecount = count($category->courses);
        $categorycount = 0;
        $totalcategorycount = count($category->categories);

        if ($category->root) {
            if (!$totalcategorycount && !$totalcoursecount) {
                $message = html_writer::tag('p', get_string('nocoursesmatch', 'local_accordion', core_text::strtolower(get_string('courses'))));
                echo html_writer::tag('div', $message, array('class' => 'box generalbox'));
            }
            echo html_writer::start_tag('ul', array('class' => 'arup_category_master'));
        }

        foreach ($category->learningpaths as $lp) {
            $lpcount++;

            $lpclass = '';
            $lpclass .= ($lpcount == 1 ? ' arup_lp_first' : '');
            $lpclass .= ($lpcount == $totallpcount ? ' arup_lp_last' : '');

            $lpurlparams = array(
                'c' => 'learning_path',
                'a' => 'view',
                'id' => $lp->id,
                );
            $lplink = html_writer::link(new moodle_url('/local/wa_learning_path/?c=learning_path&a=view&id=8', $lpurlparams), $lp->title);
            $lpspan= '';
            if ($lp->subscribed) {
                $lpspan = html_writer::tag('span', get_string('learningpath:subscribed', 'local_accordion'), array('class' => 'arup_lp_indicator'));
            }
            $lpcontentinner = html_writer::tag('span', $lplink . $lpspan);
            if ($lp->summary) {
                $lpcontentinner .= html_writer::tag('span', $lp->summary, array('class' => 'arup_lp_summary'));
            }

            $metadata = '';
            if ($lp->regions) {
                $metadata .= html_writer::tag('span', get_string('regions', 'local_regions').':');
                $metadata .= $lp->regions;
            }
            $metadata .= html_writer::tag('span', get_string('learningpath:methodology', 'local_accordion').':');
            $metadata .= get_string('learningpath', 'local_accordion');
            $lpcontentinner .= html_writer::tag('span', $metadata, array('class' => 'arup_lp_metadata'));

            $lpcontent = html_writer::tag('li', $lpcontentinner, array('class' => 'arup_lp_name'));
            echo html_writer::tag('ul', $lpcontent,  array('class' => 'arup_lp'.$lpclass));
        }

        foreach ($category->courses as $course) {
            $coursecount++;

            $courseclass = '';
            $courseclass .= ($coursecount == 1 ? ' arup_course_first' : '');
            $courseclass .= ($coursecount == $totalcoursecount ? ' arup_course_last' : '');

            $courselinkclasses = $course->visible ? array() : array('class' => 'dimmed');
            $courselink = html_writer::link(new moodle_url('/course/view.php', array('id' => $course->id)), format_string($course->fullname), $courselinkclasses);
            $coursecontentinner = html_writer::tag('span', $courselink);
            if ($course->summary) {
                $coursecontentinner .= html_writer::tag('span', $course->summary, array('class' => 'arup_course_summary'));
            } else {
                $coursecontentinner .= html_writer::tag(
                    'span',
                    html_writer::tag('p', get_string('nosummary', 'local_accordion')),
                    array('class' => 'arup_course_summary')
                );
            }

            $config = get_config('local_accordion');
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

            $coursecontent = html_writer::tag('li', $coursecontentinner, array('class' => 'arup_course_name'));
            echo html_writer::tag('ul', $coursecontent,  array('class' => 'arup_course'.$courseclass));
        }
        foreach ($category->categories as $subcategory) {
            $categorycount++;

            if ($category->root) {
                $extraclass = '';
                if ($categorycount == 1) {
                    $extraclass .= ' arup_category_first';
                }
                if ($categorycount == $totalcategorycount) {
                    $extraclass .= ' arup_category_last';
                }
                echo html_writer::start_tag('ul', array('class' => 'arup_category arup_category_top'.$extraclass));
            } else {
                $categoryclass = '';
                $categoryclass .= (($categorycount == 1 && ($totalcoursecount || $totallpcount)) ? ' arup_category_sub_first' : '');
                $categoryclass .= ($categorycount == $totalcategorycount ? ' arup_category_sub_last' : '');

                echo html_writer::start_tag('ul', array('class' => 'arup_category arup_category_sub'.$categoryclass));
            }

            $categorylinkclasses = $subcategory->visible ? array('class' => 'accordion-link') : array('class' => 'accordion-link dimmed');
            $categorylink = html_writer::link(
                    new moodle_url('/local/accordion/index.php', array('id' => $subcategory->id, 'catalogue' => 'accordion')),
                    html_writer::tag('span', '&ndash;', array('class' => 'accordion-plusminus')) . format_string($subcategory->name),
                    $categorylinkclasses + array('title' => get_string('expandcategory', 'local_accordion'))
            );
            $categorylink .= html_writer::tag(
                'span',
                html_writer::link(
                    new moodle_url('/local/accordion/index.php', array('id' => $subcategory->id, 'catalogue' => 'accordion')), get_string('details', 'local_accordion')),
                    array('class' => 'accordion-category-link', 'title' => get_string('viewcategory', 'local_accordion')
                )
            );
            echo html_writer::start_tag('li');
            echo html_writer::tag('span', $categorylink, array('class' => 'accordion-toggle accordion-open'));
            $this->render_category($subcategory);
            echo html_writer::end_tag('li');
            echo html_writer::end_tag('ul');
        }
        if ($category->root) {
            echo html_writer::end_tag('ul');
        }
    }
}