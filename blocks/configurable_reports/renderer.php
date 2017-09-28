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

class block_configurable_reports_renderer extends plugin_renderer_base {
    public function render_content($contents) {
        $output = '';
        $groupcount = 0;
        foreach ($contents as $groupheading => $group) {
            if (empty($group)) {
                continue;
            } elseif ($groupcount > 0) {
                $output .= html_writer::empty_tag('hr', array('class' => 'bcr-accordion-hr'));
            }

            $groupheading = get_string("menu:{$groupheading}", 'block_configurable_reports');
            $output .= html_writer::div($groupheading, 'accordion-group-heading');

            $groupcount++;
            $subgroupcount = 0;
            $groupoutput = '';
            ksort($group);
            foreach ($group as $heading => $reports) {
                $subgroupcount++;
                if ($heading === 'ZZZZZZ') {
                    $heading = get_string('menu:misc', 'block_configurable_reports');
                }
                $groupoutput .= $this->_render_accordion_group($heading, $reports, $groupcount, $subgroupcount);
            }
            $output .= html_writer::div($groupoutput, 'accordion', array('id' => "bcr-accordion-{$groupcount}"));
        }
        return html_writer::div($output, 'accordion', array('id' => 'bcr-accordion'));
    }

    private function _render_accordion_group($heading, $items, $groupcount, $subgroupcount) {
        $identifier = "bcr-collapse-{$groupcount}-{$subgroupcount}";
        $parent = "#bcr-accordion-{$groupcount}";
        $options = array(
            'class' => 'accordion-toggle collapsed',
            'data-toggle' => 'collapse',
            'data-parent' => $parent
        );
        $output = html_writer::start_div('accordion-group');
        $headerlink = html_writer::link("#{$identifier}", $heading, $options);
        $output .= html_writer::div($headerlink, 'accordion-heading');
        $output .= html_writer::start_div('accordion-body collapse', array('id' => $identifier));
        $output .= implode("\n", $items);
        $output .= html_writer::end_div();
        $output .= html_writer::end_div();
        return $output;
    }
}