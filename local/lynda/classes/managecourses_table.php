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
 * @package    local_lynda
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_lynda;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/tablelib.php');

class managecourses_table extends \table_sql {
    public function __construct($filterparams, $sortcolumn) {
        global $DB;
        global $PAGE;

        parent::__construct('managecourses_table');

        $PAGE->requires->js_call_amd('local_lynda/manage', 'initialise');

        $this->filterparams = $filterparams;

        $this->define_columns(['courseid', 'title', 'description', 'tags', 'regions']);
        $this->define_headers([
                get_string('courseid', 'local_lynda'),
                get_string('title', 'local_lynda'),
                get_string('description', 'local_lynda'),
                get_string('tags', 'local_lynda'),
                get_string('regions', 'local_lynda')
        ]);
        $this->collapsible(false);
        $this->sortable(true);
        $this->pageable(true);
        $this->is_downloadable(true);
        $this->sort_default_column = $sortcolumn;

        $this->regions = $DB->get_records('local_regions_reg', ['userselectable' => true]);
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
        $tagtypes = \local_lynda\lyndatagtype::fetch_full_taxonomy();
        foreach ($tagtypes as $tagtype) {
            $selectname = $tagtype->gettagtypeselectname();
            if (!isset($this->filterparams->$selectname)) {
                continue;
            }

            foreach ($this->filterparams->$selectname as $tag) {
                $tagfilterjoins[] =
                        "INNER JOIN {local_lynda_coursetags} lct_$tag ON lct_$tag.remotetagid = $tag AND lct_$tag.remotecourseid = lc.remotecourseid"; // Add tag filter
            }
        }
        $tagfilterjoins = implode("\n", $tagfilterjoins);

        $sql = "FROM {local_lynda_course} lc
                $tagfilterjoins
                LEFT JOIN {local_lynda_coursetags} lct ON lct.remotecourseid = lc.remotecourseid
                LEFT JOIN {local_lynda_courseregions} lcr ON lcr.lyndacourseid = lc.id";

        $sort = $this->get_sql_sort();

        $orderby = "ORDER BY $sort";

        $total = $DB->count_records_sql("SELECT COUNT(DISTINCT lc.id) $sql", $params);
        $this->pagesize($pagesize, $total);

        $remotetagidconcat = $this->sql_group_concat('lct.remotetagid', ',', true);
        $regionsconcat = $this->sql_group_concat('regionid', ',', true);
        $columns =
                "lc.id, lc.remotecourseid as courseid, lc.title, lc.description, $remotetagidconcat as tags, $regionsconcat as regions";
        $groupby = "GROUP BY lc.id, lc.remotecourseid, lc.title, lc.description";

        $this->rawdata = $DB->get_records_sql("SELECT $columns $sql $groupby $orderby", $params, $this->get_page_start(),
                $this->get_page_size());

        // Set initial bars.
        if ($useinitialsbar) {
            $this->initialbars($total > $pagesize);
        }
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

    public function col_tags($event) {
        $tagsoncourse = explode(',', $event->tags);

        $tagstrings = '';
        foreach (lyndatagtype::fetch_full_taxonomy() as $type) {
            $tagstoshow = [];

            foreach ($type->tags as $key => $tag) {
                if (!in_array($key, $tagsoncourse)) {
                    continue;
                }
                $tagstoshow[] = $tag->name;

            }

            if (empty($tagstoshow)) {
                continue;
            }

            $tagstrings .= \html_writer::div($type->name . ': ' . implode(', ', $tagstoshow));
        }

        return $tagstrings;
    }

    public function col_regions($event) {
        $checkedregions = explode(',', $event->regions);

        $checks = '';
        foreach ($this->regions as $region) {
            $chkname = "chk_region_$region->id";
            $checked = !empty($checkedregions) && in_array($region->id, $checkedregions);
            $checks .= \html_writer::span(\html_writer::checkbox($chkname, $chkname, $checked, $region->name,
                    ['data-regionid' => $region->id, 'data-courseid' => $event->id, 'class' => 'regioncheck']));
        }
        return $checks;
    }
}
