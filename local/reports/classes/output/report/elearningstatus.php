<?php
// This file is part of the Arup Reports system
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

/**
 *
 * @package     local_reports
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_reports\output\report;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use moodle_url;
use renderer_base;
use html_writer;

class elearningstatus extends base {

    /**
     * Export this data so it can be used as the context for a mustache template.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        $this->data->pagination = $this->pagination();
        return $this->data;
    }

    public function set_report(\local_reports\reports\elearningstatus $report) {
        $report->get_table_data();
        $report->showxlsexport = false;
        if ($report->numrecords < $this->report->maxexportrows) {
            $report->showxlsexport = true;
        }
        $this->data = $report;
    }


    private function pagination() {
        global $OUTPUT;
        if (!$this->data->numrecords) {
            return '';
        }
        if ($this->data->numrecords == 1) {
            return '';
        }

        $pagination = new stdClass();
        $pagination->pages = $this->pagination_pages();
        if (count($pagination->pages) <= 1) {
            return '';
        }
        $params = array();
        $params['page'] = 'elearningstatus';
        $params['sort'] = $this->data->sort;
        $params['dir'] = $this->data->direction;
        $params['start'] = $this->data->start - 1;
        $pagination->prevurl = new moodle_url('/local/reports/index.php', $params);
        $params['start'] = $this->data->start + 1;
        $pagination->nexturl = new moodle_url('/local/reports/index.php', $params);
        
        $leftalt = get_string('previous', 'local_reports');
        $licon = new \pix_icon('t/left', $leftalt, '', array('title' => $leftalt));
        $pagination->leftarrow = $OUTPUT->render($licon);

        $rightalt = get_string('next', 'local_reports');
        $ricon = new \pix_icon('t/right', $rightalt, '', array('title' => $rightalt));
        $pagination->rightarrow = $OUTPUT->render($ricon);

        return $pagination;
    }

    public function pagination_pages() {
        $numpages = floor($this->data->numrecords / $this->data->limit);
        $pages = array();
        $maxpages = 10;
        $bigjump = 200;
        $maxjumps = 4;
        $numjumps = 0;
        if (isset($this->data->start) && $this->data->start > 4) {
            $start = $this->data->start - 4;
        } else {
            $start = 0;
        }
        $pagecount = 0;
        if ($this->data->start > 4) {
            $pages[] = $this->pagination_page(0);
        }
        for ($i = $start ; $i < $numpages ; $i++) {
            $pagecount++;
            if ($pagecount > $maxpages) { 
                if (($i + 1) % $bigjump != 0) {
                    continue;
                } else if ($numjumps >= $maxjumps) {
                    continue;
                } else {
                    $numjumps++;
                }
            }
            $pages[] = $this->pagination_page($i);
        }
        if ($numpages > $maxpages) {
            $pages[] = $this->pagination_page($numpages);
        }
        
        return $pages;
    }

    public function pagination_page($i) {
        $page = new stdClass();
        $params = array();
        $params['page'] = 'elearningstatus';
        $params['sort'] = $this->data->sort;
        $params['dir'] = $this->data->direction;
        $params['start'] = $i;
        $page->link = new moodle_url('/local/reports/index.php', $params);
        $page->active = '';
        if ($this->data->start == $i) {
            $page->active = 'active';
        }
        $page->number = $i + 1;
        return $page;
    }
}
