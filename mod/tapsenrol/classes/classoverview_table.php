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
 * @author Andrew Hancox <andrewdchancox@googlemail.com> on behalf of Synergy Learning
 * @package local
 * @subpackage gioska
 */

namespace mod_tapsenrol;

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot . '/lib/tablelib.php');
require_once($CFG->dirroot . '/user/profile/lib.php');

class classoverview_table extends \table_sql {
    private $context;
    public function __construct($sortcolumn, $cm) {
        global $DB;
        parent::__construct('classoverview_table');

        $this->define_columns(['classname', 'classstatus', 'classstarttime', 'classendtime', 'maximumattendees', 'attending', 'actions']);
        $this->define_headers([
                get_string('classname', 'tapsenrol'),
                get_string('classstatus', 'tapsenrol'),
                get_string('classstarttime', 'tapsenrol'),
                get_string('classendtime', 'tapsenrol'),
                get_string('maximumattendees', 'tapsenrol'),
                get_string('attending', 'tapsenrol'),
                get_string('actions', 'tapsenrol')
        ]);

        $this->collapsible(false);
        $this->sortable(true);
        $this->pageable(true);
        $this->is_downloadable(true);
        $this->sort_default_column = $sortcolumn;
        $this->context = \context_module::instance($cm->id);

        $taps = new \mod_tapsenrol\taps();
        $statuses = array_merge($taps->get_statuses('placed'), $taps->get_statuses('attended'));
        list($insql, $params) = $DB->get_in_or_equal($statuses, SQL_PARAMS_NAMED, 'status');

        $fields = ['id',
                'archived',
                'classname',
                'classstatus',
                'usedtimezone', // Needed to format start/end time correctly.
                'classstarttime',
                'classendtime',
                'maximumattendees',
                'classid'];
        $fieldsstring = 'c.' . implode(',c.', $fields) . ",  count(e.classid) as attending";
        $from = "{local_taps_class} c
                      LEFT OUTER JOIN {local_taps_enrolment} e
                      ON c.classid = e.classid
                        AND (e.archived = 0 OR e.archived IS NULL)
                        AND {$DB->sql_compare_text('e.bookingstatus')} {$insql}";
        $where = "c.courseid = :courseid AND (c.archived is NULL OR c.archived = 0)
                      GROUP BY ". 'c.' . implode(',c.', $fields);
        $params['courseid'] = $cm->course;
        $this->set_sql($fieldsstring, $from, $where, $params);
        $this->set_count_sql('SELECT COUNT(id) FROM {local_taps_class} c WHERE c.courseid = :courseid', ['courseid' => $cm->course]);
    }

    public function col_classstarttime($class) {
        if (empty($class->classstarttime)) {
            return '';
        }
        return userdate($class->classstarttime);
    }

    public function col_classendtime($class) {
        if (empty($class->classendtime)) {
            return '';
        }
        return userdate($class->classendtime);
    }

    public function col_actions($class) {
        global $OUTPUT;

        // Edit.
        if (has_capability('mod/tapsenrol:editclass', $this->context)) {
            $urledit = new \moodle_url('/mod/tapsenrol/editclass.php', ['id' => $class->id]);
            $edit = $OUTPUT->action_icon($urledit, new \pix_icon('i/edit', get_string('edit')));
        }

        // Delegate list.
        // TODO Bring local_delegatelist into this plugin.
        $coursecontext = $this->context->get_parent_context();
        $params = array('contextid' => $coursecontext->id, 'classid' => $class->classid);
        if (has_any_capability(["local/delegatelist:managerview", "local/delegatelist:teacherview", "local/delegatelist:studentview"], $coursecontext)) {
            $urldelegate = new \moodle_url('/local/delegatelist/index.php', $params);
            $delegates = $OUTPUT->action_icon($urldelegate, new \pix_icon('i/users', get_string('users')));
        }

        // Duplicate
        if (has_capability('mod/tapsenrol:createclass', $this->context)) {
            $urledit = new \moodle_url('/mod/tapsenrol/editclass.php', ['id' => $class->id, 'duplicate' => true]);
            $duplicate = $OUTPUT->action_icon($urledit, new \pix_icon('e/copy', get_string('duplicate')));
        }

        // Delete.
        if (has_capability('mod/tapsenrol:deleteclass', $this->context)) {
            $urldelete = new \moodle_url('/mod/tapsenrol/deleteclass.php', ['id' => $class->id]);
            $delete = $OUTPUT->action_icon($urldelete, new \pix_icon('t/delete', get_string('delete')));
        }

        return $edit . $delegates . $duplicate . $delete;
    }
}
