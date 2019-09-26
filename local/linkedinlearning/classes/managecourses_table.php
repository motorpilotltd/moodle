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
 * @package    local_linkedinlearning
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_linkedinlearning;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/tablelib.php');

class managecourses_table extends \table_sql {
    private $regions = null;

    public function __construct($filterparams, $sortcolumn) {
        global $DB;
        global $PAGE;

        parent::__construct('managecourses_table');
        $this->regions = array(0 => get_string('global', 'local_regions')) + $DB->get_records_menu('local_regions_reg', ['userselectable' => true]);

        $PAGE->requires->js_call_amd('local_linkedinlearning/manage', 'initialise');

        $this->filterparams = $filterparams;

        $this->collapsible(false);
        $this->sortable(true);
        $this->pageable(true);
        $this->is_downloadable(true);
        $this->sort_default_column = $sortcolumn;

    }

    /**
     * Query the reader. Store results in the object for use by build_table.
     *
     * @param int $pagesize size of page for paginated displayed table.
     * @param bool $useinitialsbar do you want to use the initials bar.
     */
    public function query_db($pagesize, $useinitialsbar = true) {
        global $DB;

        $params = array();

        // Set up filtering.
        $tagfilterjoins = [];
        $classifications = \local_linkedinlearning\classification::get_types();
        $where = 'WHERE 1=1 ';
        foreach ($classifications as $key => $value) {
            if (empty($this->filterparams->$key)) {
                continue;
            }

            list($sqlfrag, $paramsfrag) = $DB->get_in_or_equal($this->filterparams->$key, SQL_PARAMS_NAMED);
            $params += $paramsfrag;
            $tagfilterjoins[] =
                    "INNER JOIN {linkedinlearning_crs_class} lct_$key ON lct_$key.classificationid $sqlfrag AND lct_$key.linkedinlearningcourseid = lc.id"; // Add tag filter
        }

        if (isset($this->filterparams->title)) {
            $params['titlewhere'] = '%'.$DB->sql_like_escape($this->filterparams->title).'%';

            $where .= 'AND ' . $DB->sql_like('lc.title', ':titlewhere', false, false);
        }

        $tagfilterjoins = implode("\n", $tagfilterjoins);

        $sql = "FROM {linkedinlearning_course} lc
                $tagfilterjoins
                LEFT JOIN {linkedinlearning_crs_class} lct ON lct.linkedinlearningcourseid = lc.id
                LEFT JOIN {local_taps_course} ltc on ltc.coursecode = lc.urn
                LEFT JOIN {course} c on c.idnumber = ltc.courseid
                LEFT JOIN {local_regions_reg_cou} lrrc on lrrc.courseid = c.id
                $where";

        $sort = $this->get_sql_sort();

        $orderby = "ORDER BY $sort";

        $total = $DB->count_records_sql("SELECT COUNT(DISTINCT lc.id) $sql", $params);
        $this->pagesize($pagesize, $total);

        $remotetagidconcat = $this->sql_group_concat('lct.classificationid', ',', true);
        $regionsconcat = $this->sql_group_concat('lrrc.regionid', ',', true);
        //434462
        $columns =
                "lc.id, lc.urn as courseid, c.id as moodlecourseid, c.visible as moodlecoursevisible, lc.title, lc.shortdescription, lc.timetocomplete, $remotetagidconcat as classifications, $regionsconcat as regions";
        $groupby = "GROUP BY lc.id, lc.urn, lc.title, lc.shortdescription, lc.timetocomplete, c.id, c.visible";

        $this->rawdata = $DB->get_records_sql("SELECT $columns $sql $groupby $orderby", $params, $this->get_page_start(),
                $this->get_page_size());

        // Set initial bars.
        if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }
    }

    public function col_timetocomplete($event) {
        return format_time($event->timetocomplete);
    }

    public function col_classifications($event) {
        $classificationsoncourse = explode(',', $event->classifications);

        $classificationstrings = [];
        foreach (\local_linkedinlearning\classification::get_types() as $type) {
            $classificationstoshow = [];

            foreach ($type['tags'] as $tag) {
                if (!in_array($tag->id, $classificationsoncourse)) {
                    continue;
                }
                $classificationstoshow[] = $tag->name;

            }

            if (empty($classificationstoshow)) {
                continue;
            }

            $classificationstrings[] = \html_writer::div($type['name'] . ': ' . implode(', ', $classificationstoshow));
        }

        return implode($classificationstrings);
    }

    public function other_cols($column, $event) {
        $matches = [];

        preg_match('/^region([0-9]+)/', $column, $matches);
        if (isset($matches[1])) {
            $regionid = $matches[1];

            if (!empty($event->regions)) {
                $checkedregions = explode(',', $event->regions);
            } else if (!empty($event->moodlecourseid) && !empty($event->moodlecourseid)) {
                $checkedregions = [0];
            } else {
                $checkedregions = [];
            }

            $checked = !empty($checkedregions) && in_array($regionid, $checkedregions);

            if (!empty($this->download)) {
                if ($checked) {
                    return 'X';
                } else {
                    return '';
                }
            } else {
                $chkname = "chk_region_$regionid";
                return \html_writer::span(\html_writer::checkbox($chkname, $chkname, $checked, '',
                        ['data-regionid' => $regionid, 'data-courseid' => $event->id, 'class' => 'regioncheck']));
            }
        }
    }

    public function configurecolumns() {
        $columns = ['courseid', 'title', 'shortdescription', 'timetocomplete'];
        $headers = [
                get_string('urn', 'local_linkedinlearning'),
                get_string('title', 'local_linkedinlearning'),
                get_string('shortdescription', 'local_linkedinlearning'),
                get_string('timetocomplete', 'local_linkedinlearning')
        ];

        if (empty($this->download)) {
            $headers[] = get_string('classifications', 'local_linkedinlearning');
            $columns[] = 'classifications';
        }
        foreach ($this->regions as $id => $name) {
            $headers[] = $name .
                    \html_writer::div(
                            \html_writer::checkbox("selectall$id", "selectall$id", false, get_string('selectall')
                                    , ['data-regionid' => $id, 'class' => 'regionselectall'])
                    );
            $columns[] = 'region' . $id;
            $this->no_sorting('region' . $id);
        }
        $this->define_columns($columns);
        $this->define_headers($headers);
    }

    private function sql_group_concat($field, $delimiter = ', ', $unique = false) {
        global $DB;

        // if not supported, just return single value - use min()
        $sql = " MIN($field) ";

        switch ($DB->get_dbfamily()) {
            case 'mysql':
                // use native function
                $distinct = $unique ? 'DISTINCT' : '';
                $sql = " GROUP_CONCAT($distinct $field SEPARATOR '$delimiter') ";
                break;
            case 'postgres':
                // use custom aggregate function - must have been defined
                // in db/upgrade.php
                $distinct = $unique ? 'TRUE' : 'FALSE';
                $sql = " GROUP_CONCAT($field, '$delimiter', $distinct) ";
                break;
            case 'mssql':
                $distinct = $unique ? 'DISTINCT' : '';
                $sql = " dbo.GROUP_CONCAT_D($distinct $field, '$delimiter') ";
                break;
        }

        return $sql;
    }
}
