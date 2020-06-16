<?php
/*
 * This file is part of T0tara LMS
 *
 * Copyright (C) 2014 onwards T0tara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Petr Skoda <petr.skoda@t0taralms.com>
 * @package local_reportbuilder
 */

namespace local_reportbuilder\local;

/**
 * Class describing report graphs.
 */
class graph {
    /** @var \stdClass record from report_builder_graph table */
    protected $graphrecord;
    /** @var \reportbuilder the relevant reportbuilder instance */
    protected $report;
    /** @var array category and data series */

    protected $legend;
    /** @var int count of records processed - count() in PHP may be very slow */
    protected $processedcount;
    /** @var int index of category, -1 means simple counter */
    protected $category;
    /** @var array indexes of series columns */
    protected $series;
    /** @var int legend column index when headings used as category */
    protected $legendcolumn;

    protected $rotatelabels;

    public function __construct(\reportbuilder $report) {

        $this->load($report);

        if (!empty($this->graphrecord->type)) {
            $this->report = $report;
            $this->init();
        }
    }

    /**
     * Object initialisation.
     */
    private function init() {

        $this->processedcount = 0;
        $this->values = array();
        $this->series = array();

        $columns = array();
        $columnsmap = array();
        $i = 0;
        foreach ($this->report->columns as $colkey => $column) {
            if (!$column->display_column(true)) {
                continue;
            }
            $columns[$colkey] = $column;
            $columnsmap[$colkey] = $i++;
        }
        $rawseries = json_decode($this->graphrecord->series, true);
        $series = array();
        foreach ($rawseries as $colkey) {
            $series[$colkey] = $colkey;
        }

        if ($this->graphrecord->category === 'columnheadings') {
            $this->category = -2;

            $legendcolumn = $this->graphrecord->legend;
            if ($legendcolumn and isset($columns[$legendcolumn])) {
                $this->legendcolumn = $columnsmap[$legendcolumn];
            }

            foreach ($columns as $colkey => $column) {
                if (!isset($series[$colkey])) {
                    continue;
                }
                $i = $columnsmap[$colkey];
                $this->values[$i][-2] = $this->report->format_column_heading($this->report->columns[$colkey], true);
            }

        } else {
            if (isset($columns[$this->graphrecord->category])) {
                $this->category = $columnsmap[$this->graphrecord->category];
                unset($series[$this->graphrecord->category]);

            } else { // Category value 'none' or problem detected.
                $this->category = -1;
            }

            foreach ($series as $colkey) {
                if (!isset($columns[$colkey])) {
                    continue;
                }
                $i = $columnsmap[$colkey];
                $this->series[$i] = $colkey;
            }

            $legend = array();
            foreach ($this->series as $i => $colkey) {
                $legend[$i] = $this->report->format_column_heading($this->report->columns[$colkey], true);
            }
            if (count($legend) > 1) {
                $this->legend = $legend;
            } else {
                $this->legend = '';
            }
        }
    }

    /**
     * Load graph record.

     * @param \reportbuilder $report eportbuilder the relevant reportbuilder instance
     */
    private function load($report) {
        global $DB;

        $this->graphrecord = $DB->get_record('report_builder_graph', array('reportid' => $report->_id));
        if (!$this->graphrecord) {
            $this->graphrecord = new \stdClass();
            $this->graphrecord->type = '';
        }
    }

    public function reset_records() {
        $this->processedcount = 0;
        $this->series = [];
        $this->legend = [];
    }

    public function add_record($record) {
        $recorddata = $this->report->src->process_data_row($record, 'graph', $this->report);

        if ($this->category == -2) {
            $this->series[] = $this->processedcount;
            foreach ($recorddata as $k => $val) {
                if (isset($this->legendcolumn) and $k === $this->legendcolumn) {
                    $this->svggraphsettings['legend_entries'][] = (string)$val;
                    continue;
                }
                if (!isset($this->values[$k])) {
                    continue;
                }
                $this->values[$k][$this->processedcount] = self::normalize_numeric_value($val);
            }
            $this->processedcount++;
            return;
        }

        $value = array();
        if ($this->category == -1) {
            $value[-1] = $this->processedcount + 1;
        } else {
            $value[$this->category] = $recorddata[$this->category];
        }

        foreach ($this->series as $i => $key) {
            $val = $recorddata[$i];
            $value[$i] = self::normalize_numeric_value($val);
        }

        $this->values[] = $value;
        $this->processedcount++;
    }

    /**
     * Normalise the value before sending to SVGGraph for display.
     *
     * Note: There is a lot of guessing in here.
     *
     * @param mixed $val
     * @return int|float|string
     */
    public static function normalize_numeric_value($val) {
        // Strip the percentage sign, the SVGGraph is not compatible with it.
        if (substr($val, -1) === '%') {
            $val = substr($val, 0, -1);
        }

        // Trim spaces, they might be before the % for example, keep newlines though.
        if (is_string($val)) {
            $val = trim($val, ' ');
        }

        // Normalise decimal values to PHP format, SVGGraph needs to localise the numbers itself.
        if (substr_count($val, ',') === 1 and substr_count($val, '.') === 0) {
            $val = str_replace(',', '.', $val);
        }

        if ($val === null or $val === '' or !is_numeric($val)) {
            // There is no way to plot non-numeric data, sorry,
            // we need to use '0' because SVGGraph does not support nulls.
            $val = 0;
        } else if (is_string($val)) {
            if ($val === (string)(int)$val) {
                $val = (int)$val;
            } else {
                $val = (float)$val;
            }
        }

        return $val;
    }

    public function count_records() {
        return $this->processedcount;
    }

    public function get_max_records() {
        return $this->graphrecord->maxrecords;
    }

    protected function init_svggraph() {
        if ($this->count_records() == 0) {
            return;
        }


        // Rotate data labels if necessary.
        if ($this->count_records() > 5) {
            if (get_string('thisdirectionvertical', 'core_langconfig') === 'btt') {
                $this->rotatelabels = 90;
            } else {
                $this->rotatelabels = -90;
            }
        }
    }

    /**
     * Get SVG image markup suitable for embedding in report page.
     *
     * @return string SVG markup
     */
    public function fetch_svg() {
        global $OUTPUT;

        $this->init_svggraph();

        if ($this->graphrecord->type === 'line') {
            $chart = new \core\chart_line(); // Create a bar chart instance.
        } else if ($this->graphrecord->type === 'smoothedline') {
            $chart = new \core\chart_line(); // Create a bar chart instance.
            $chart->set_smooth(true);
        } else if ($this->graphrecord->type === 'pie') {
            $chart = new \core\chart_pie(); // Create a bar chart instance.
            $oblyeserieskey = array_keys($this->series)[0];
            $this->series = [$oblyeserieskey =>  $this->series[$oblyeserieskey]];
        } else if ($this->graphrecord->type === 'doughnut') {
            $chart = new \core\chart_pie(); // Create a bar chart instance.
            $chart->set_doughnut(true);
            $oblyeserieskey = array_keys($this->series)[0];
            $this->series = [$oblyeserieskey =>  $this->series[$oblyeserieskey]];
        } else if ($this->graphrecord->type === 'bar') {
            $chart = new \core\chart_bar(); // Create a bar chart instance.
            $chart->set_horizontal(true);
            $chart->set_stacked($this->graphrecord->stacked);
        } else if ($this->graphrecord->type === 'column') {
            $chart = new \core\chart_bar(); // Create a bar chart instance.
            $chart->set_stacked($this->graphrecord->stacked);
        }

        foreach($this->series as $index => $series) {
            $label = isset($this->legend[$index]) ? $this->legend[$index]:$series;

            $seriesdata = [];
            foreach ($this->values as $val) {
                $seriesdata[] = $val[$index];
            }

            $slice = array_slice($seriesdata, 0, $this->graphrecord->maxrecords, true);

            $chart->add_series(new \core\chart_series($label, $slice));
        }

        $lables = [];
        foreach ($this->values as $val) {
            $lables[] = $val[0];
        }

        $lables = array_slice($lables, 0, $this->graphrecord->maxrecords, true);
        $chart->set_labels($lables);

        return $OUTPUT->render_chart($chart, false);
    }

    /**
     * Get SVG image markup intended for graph block.
     *
     * NOTE: the RTL fixes are not applied because we need to cache the results.
     *
     * @return string SVG markup without RTL hacks
     */
    public function fetch_block_svg() {
        return $this->fetch_export_png(400,400);
    }

    /**
     * Get SVG image markup suitable for general export.
     *
     * Note: the result is NOT intended for displaying in MS browsers!
     *
     * @param int $w width of the SVG
     * @param int $h height of SVG
     * @return string SVG markup
     */
    public function fetch_export_png($w) {
        global $CFG;

        $sesskey = sesskey();
        set_user_preferences(['graphhandlerkey' => $sesskey], null, $this->report->reportfor);

        $tempfile = make_request_directory() . "/oput.png";
        $reportid = $this->graphrecord->reportid;
        $userid = $this->report->reportfor;

        $pathtochrome = get_config('reportbuilder', 'pathtochrome');
        $h = $w/2 + 2;
        $cmd = "\"$pathtochrome\" --headless --window-size=$w,$h --disable-gpu --screenshot=$tempfile $CFG->wwwroot/local/reportbuilder/graphhandler/graph.php?id=$reportid&userid=$userid&graphhandlerkey=$sesskey&";
        $cmd = escapeshellcmd($cmd);

        raise_memory_limit(MEMORY_HUGE);
        exec($cmd, $output, $return);

        return file_get_contents($tempfile);
    }

    public function is_valid() {

        if (empty($this->graphrecord->type)) {
            return false;
        }

        return (bool)$this->series;
    }

}
