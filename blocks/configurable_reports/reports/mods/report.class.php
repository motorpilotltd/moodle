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

/**
 * Configurable Reports
 * A Moodle block for creating customizable reports
 * @package blocks
 * @author: Juan leyva <http://www.twitter.com/jleyvadelgado>
 * @date: 2009
 */

class report_mods extends report_base {

    public function init() {
        $this->components = array('columns', 'filters', 'permissions');
    }

    public function create_report() {
        global $DB, $CFG, $COURSE, $OUTPUT;

        $components = cr_unserialize($this->config->components);

        $columns = (isset($components['columns']['elements']))? $components['columns']['elements'] : array();
        $filters = (isset($components['filters']['elements']))? $components['filters']['elements'] : array();

        $dbtables = array(
            'cm' => 'course_modules',
            'c' => 'course',
            'cc' => 'course_categories',
            'm' => 'modules');
        $select = '';
        foreach ($dbtables as $dbprefix => $dbtable) {
            $dbtablecolumns = $DB->get_columns($dbtable);
            foreach ($dbtablecolumns as $dbtablecolumn => $dbtablecolumninfo) {
                $select .= $dbprefix.'.'.$dbtablecolumn.' as '.$dbprefix.$dbtablecolumn.',';
            }
        }
        $select = substr($select, 0, -1);
        $sql = "
            SELECT
                {$select}
            FROM
                {course_modules} cm
            JOIN
                {course} c
                ON (cm.course = c.id)
            JOIN
                {course_categories} cc
                ON (cc.id = c.category)
            JOIN
                {modules} m
                ON (cm.module = m.id)
            ORDER BY
                cc.name ASC,
                c.fullname ASC,
                m.name ASC
        ";

        $rs = $DB->get_recordset_sql($sql);
        $rows = array();
        if($rs) {
            foreach ($rs as $rsrow) {
                if (!isset($rows[$rsrow->cid])) {
                    // Clone to avoid a reference.
                    $rows[$rsrow->cid] = clone($rsrow);
                    $rows[$rsrow->cid]->activities = array();
                    $rows[$rsrow->cid]->cid = $this->_wrapInLink($rows[$rsrow->cid]->cid, $rsrow->cid, 'course');
                    $rows[$rsrow->cid]->cshortname = $this->_wrapInLink($rows[$rsrow->cid]->cshortname, $rsrow->cid, 'course');
                    $rows[$rsrow->cid]->cfullname = $this->_wrapInLink($rows[$rsrow->cid]->cfullname, $rsrow->cid, 'course');
                    $rows[$rsrow->cid]->ccid = $this->_wrapInLink($rows[$rsrow->cid]->ccid, $rsrow->ccid, 'category');
                    $rows[$rsrow->cid]->ccname = $this->_wrapInLink($rows[$rsrow->cid]->ccname, $rsrow->ccid, 'category');
                }
                if (!isset($rows[$rsrow->cid]->activities[$rsrow->mname])) {
                    $rows[$rsrow->cid]->activities[$rsrow->mname] = get_string('modulename', $rsrow->mname);
                }
            }

            // Filters.
            $postprocessing = false;
            if(!empty($filters)) {
                foreach($filters as $f) {
                    require_once($CFG->dirroot.'/blocks/configurable_reports/components/filters/'.$f['pluginname'].'/plugin.class.php');
                    $classname = 'plugin_'.$f['pluginname'];
                    $class = new $classname($this->config);
                    $rows = $class->execute($rows, $f['formdata']);
                }
            }

            foreach ($rows as $rowindex => $row) {
                $activities = '';
                asort($row->activities);
                foreach ($row->activities as $mod => $name) {
                    $activities .= '<img title="'
                        . $name
                        . '" alt="'
                        . $name
                        . '" class="activityicon" src="'
                        . $OUTPUT->pix_url('icon', $mod)
                        . '"> ';
                }
                $row->activities = substr($activities, 0, -1);
            }
        }

        $reporttable = array();
        $finaltable = array();
        $finalcalcs = array();
        $tablehead = array();
        $tablealign =array();
        $tablesize = array();
        $tablewrap = array();
        $firstrow = true;

        $pluginscache = array();

        if($rows) {
            foreach($rows as $r) {
                $tempcols = array();
                foreach($columns as $c) {
                    require_once($CFG->dirroot.'/blocks/configurable_reports/components/columns/'.$c['pluginname'].'/plugin.class.php');
                    $classname = 'plugin_'.$c['pluginname'];
                    if(!isset($pluginscache[$classname])) {
                        $class = new $classname($this->config, $c);
                        $pluginscache[$classname] = $class;
                    }
                    else{
                        $class = $pluginscache[$classname];
                    }

                    $tempcols[] = $class->execute($c['formdata'], $r, $this->currentuser, $COURSE->id, $this->starttime, $this->endtime);
                    if($firstrow) {
                        $tablehead[] = $class->summary($c['formdata']);
                        list($align, $size, $wrap) = $class->colformat($c['formdata']);
                        $tablealign[] = $align;
                        $tablesize[] = $size;
                        $tablewrap[] = $wrap;
                    }

                }
                $firstrow = false;
                $reporttable[] = $tempcols;
            }
        }

        $finaltable = $reporttable;

        $table = new stdclass;
        $table->id = 'reporttable';
        $table->data = $finaltable;
        $table->head = $tablehead;
        $table->size = $tablesize;
        $table->align = $tablealign;
        $table->wrap = $tablewrap;
        $table->width = (isset($components['columns']['config'])) ? $components['columns']['config']->tablewidth : '';
        $table->summary = $this->config->summary;
        $table->tablealign = (isset($components['columns']['config'])) ? $components['columns']['config']->tablealign : 'center';
        $table->cellpadding = (isset($components['columns']['config'])) ? $components['columns']['config']->cellpadding : '5';
        $table->cellspacing = (isset($components['columns']['config'])) ? $components['columns']['config']->cellspacing : '1';
        $table->class = (isset($components['columns']['config'])) ? $components['columns']['config']->class : 'generaltable';

        $calcs = new html_table();
        $calcs->data = array($finalcalcs);
        $calcs->head = $tablehead;
        $calcs->size = $tablesize;
        $calcs->align = $tablealign;
        $calcs->wrap = $tablewrap;
        $calcs->width = (isset($components['columns']['config'])) ? $components['columns']['config']->tablewidth : '';
        $calcs->summary = $this->config->summary;
        $calcs->tablealign = (isset($components['columns']['config'])) ? $components['columns']['config']->tablealign : 'center';
        $calcs->cellpadding = (isset($components['columns']['config'])) ? $components['columns']['config']->cellpadding : '5';
        $calcs->cellspacing = (isset($components['columns']['config'])) ? $components['columns']['config']->cellspacing : '1';
        $calcs->class = (isset($components['columns']['config'])) ? $components['columns']['config']->class : 'generaltable';

        $this->finalreport->table = $table;
        $this->finalreport->calcs = $calcs;

        return true;
    }

    protected function _wrapInLink($content, $id, $type = 'course') {
        switch ($type) {
            case 'course' :
                $url = new moodle_url('/course/view.php', array('id' => $id));
                break;
            case 'category' :
                $url = new moodle_url('/course/category.php', array('id' => $id));
                break;
            default :
                return $content;
                break;
        }
        return html_writer::link($url, $content);
    }
}
