<?php
// This file is part of the Arup online appraisal system
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
 * Version details
 *
 * @package     local_onlineappraisal
 * @copyright   2016 Motorpilot Ltd, Sonsbeekmedia
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_onlineappraisal\output\index;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;
use moodle_url;
use local_onlineappraisal\permissions as permissions;

abstract class base implements renderable, templatable {
    protected $index;
    protected $data;
    protected $type;
    protected $leavers = false;
    protected $cycle = null;
    protected $cycleselect = null;
    protected $searching = false;
    protected $searchid = null;

    protected static $state = ['current' => true, 'archived' => false];

    public function __construct(\local_onlineappraisal\index $index) {
        $this->index = $index;
        $this->data = new stdClass();

        // Grab needed parameters.
        $this->searching = optional_param('search', false, PARAM_BOOL);
        $this->searchid = $this->searching ? optional_param('appraisee', 0, PARAM_INT) : null;
        $this->leavers = optional_param('leavers', false, PARAM_BOOL);
        $this->cycle = optional_param('cycle', false, PARAM_INT);

        // Searching by appraisee.
        $this->handle_search();

        if (($this->type === 'hrleader' || $this->type === 'groupleader') && !$this->searching && !$this->index->groupid) {
            $this->data->showtables = false;
            return;
        }
        $this->data->showtables = true;

        // Show/hide leavers (in archived).
        $this->handle_leavers_toggle();

        // Filtering by cycle.
        $this->handle_cycle_filter();
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        if (!empty($this->cycleselect)) {
            $this->data->cycleselect = $output->render($this->cycleselect);
        }
        $istype = 'is' . $this->type;
        $this->data->{$istype} = true;
    }

    protected function set_type($type) {
        $this->type = $type;
    }

    protected function get_appraisals() {
        $istype = 'is' . $this->type;

        foreach (self::$state as $state => $leavers) {
            $appraisals = $this->index->get_appraisals($this->type, $state, $leavers || $this->leavers, $this->cycle, $this->searchid);
            if (!empty($appraisals)) {
                $isstate = 'is' . $state;

                $this->data->{$state} = new stdClass();
                $this->data->{$state}->{$istype} = true;
                $this->data->{$state}->{$isstate} = true;
                $this->data->{$state}->appraisals = array_values($appraisals);
            }
        }

        $this->pre_process_appraisals();
    }

    // This is duplicated from admin/base... Bring in a base, base class?
    protected function get_progress($statusid) {
        $progress = new stdClass();
        $progress->count = max(array(0, $statusid - 1));
        $progress->percentage = $progress->count ? round(100 * ($progress->count / 6)) : 0;
        $progress->text = get_string('status:' . $statusid, 'local_onlineappraisal');
        return $progress;
    }

    protected function pre_process_appraisals() {
        foreach (self::$state as $state => $leavers) {
            if (!empty($this->data->{$state})) {
                foreach ($this->data->{$state}->appraisals as $appraisal) {
                    $appraisal->progress = $this->get_progress($appraisal->statusid);
                    if (!empty($appraisal->held_date)) {
                        $appraisal->held_date_array = array(
                            'y' => (int) userdate($appraisal->held_date, '%Y', new \DateTimeZone('UTC')), // Always UTC (from datepicker).
                            'm' => (int) userdate($appraisal->held_date, '%m', new \DateTimeZone('UTC')), // Always UTC (from datepicker).
                            'd' => (int) userdate($appraisal->held_date, '%d', new \DateTimeZone('UTC')), // Always UTC (from datepicker).
                        );
                        $appraisal->held_date = userdate($appraisal->held_date, get_string('strftimedate'), new \DateTimeZone('UTC')); // Always UTC (from datepicker).
                    } else {
                        $appraisal->held_date = '-';
                    }
                    if (!empty($appraisal->completed_date)) {
                        $appraisal->completed_date = userdate($appraisal->completed_date, get_string('strftimedate'));
                    } else {
                        $appraisal->completed_date = '-';
                    }
                    if (!empty($appraisal->latestcheckin)) {
                        $appraisal->latestcheckin = userdate($appraisal->latestcheckin, get_string('strftimedate'));
                    } else {
                        $appraisal->latestcheckin = '-';
                    }
                    $appraisal->permissions = $this->get_permissions($state, $appraisal);
                    $appraisal->urls = $this->get_urls($appraisal->id);
                    $appraisal->requiresaction = ($state === 'current' && $this->index->requires_action($this->type, $appraisal));
                }
            }
        }
    }

    protected function get_permissions($state, $appraisal) {
        global $DB;

        $permissions = array();
        $permissions['start'] = $appraisal->statusid == APPRAISAL_NOT_STARTED && permissions::is_allowed('introduction:view', $appraisal->permissionsid, $this->type, $appraisal->archived, $appraisal->legacy);
        $hrnoview = $this->type === 'hrleader' && empty($this->index->canviewvip[$appraisal->costcentre]);
        $hrnotoggle = $this->type === 'hrleader' && empty($this->index->cantogglesdp[$appraisal->costcentre]);
        $glnoview = $this->type === 'groupleader' && $appraisal->groupleader_userid !== $this->index->user->id;
        $vipnoview = $appraisal->isvip && ($hrnoview || $glnoview);
        if ($vipnoview) {
            // Cannot view/print VIP.
            $permissions['view'] = $permissions['printappraisal'] = $permissions['printfeedback'] = false;
        } else {
            $permissions['view'] = $state != 'archived' && permissions::is_allowed('appraisal:view', $appraisal->permissionsid, $this->type, $appraisal->archived, $appraisal->legacy);
            $permissions['printappraisal'] = permissions::is_allowed('appraisal:print', $appraisal->permissionsid, $this->type, $appraisal->archived, $appraisal->legacy);
            $permissions['printfeedback'] = permissions::is_allowed('feedback:print', $appraisal->permissionsid, $this->type, $appraisal->archived, $appraisal->legacy)
                    || permissions::is_allowed('feedbackown:print', $appraisal->permissionsid, $this->type, $appraisal->archived, $appraisal->legacy);
            $permissions['printsuccessionplan'] = permissions::is_allowed('successionplan:print', $appraisal->permissionsid, $this->type, $appraisal->archived, $appraisal->legacy);
            $permissions['printleaderplan'] = permissions::is_allowed('leaderplan:print', $appraisal->permissionsid, $this->type, $appraisal->archived, $appraisal->legacy);
        }
        $permissions['editf2f'] = permissions::is_allowed('f2f:add', $appraisal->permissionsid, $this->type, $appraisal->archived, $appraisal->legacy);
        $permissions['togglef2f'] = permissions::is_allowed('f2f:complete', $appraisal->permissionsid, $this->type, $appraisal->archived, $appraisal->legacy);

        // Modifications.
        if (!empty($permissions['printsuccessionplan'])) {
            // Only display SDP download link if has been saved.
            $sql = "SELECT COUNT(lad.id)
                  FROM {local_appraisal_data} lad
                  JOIN {local_appraisal_forms} laf ON laf.id = lad.form_id
                 WHERE laf.form_name = :form_name
                       AND laf.appraisalid = :appraisalid
                       AND laf.user_id = :user_id";
            $params = [
                'form_name' => 'successionplan',
                'appraisalid' => $appraisal->id,
                'user_id' => $appraisal->appraisee_userid,
            ];
            if ($DB->count_records_sql($sql, $params) === 0) {
                // Not yet saved, remove download link.
                $permissions['printsuccessionplan'] = false;
            }
        }
        if (!empty($permissions['printleaderplan'])) {
            // Only display SDP download link if has been saved.
            $sql = "SELECT COUNT(lad.id)
                  FROM {local_appraisal_data} lad
                  JOIN {local_appraisal_forms} laf ON laf.id = lad.form_id
                 WHERE laf.form_name = :form_name
                       AND laf.appraisalid = :appraisalid
                       AND laf.user_id = :user_id";
            $params = [
                'form_name' => 'leaderplan',
                'appraisalid' => $appraisal->id,
                'user_id' => $appraisal->appraisee_userid,
            ];
            if ($DB->count_records_sql($sql, $params) === 0) {
                // Not yet saved, remove download link.
                $permissions['printleaderplan'] = false;
            }
        }

        $permissions['haspermissions'] = in_array(true, $permissions);

        // Keep these separate to appear outside of dropdown.
        $permissions['notstarted'] = $appraisal->statusid == APPRAISAL_NOT_STARTED && !$permissions['start'] && !$vipnoview;
        $permissions['vipnoview'] = $vipnoview;
        $permissions['togglesuccessionplan'] = !$hrnotoggle && permissions::is_allowed('successionplan:toggle', $appraisal->permissionsid, $this->type, $appraisal->archived, $appraisal->legacy);
        $permissions['toggleleaderplan'] = !$hrnotoggle && permissions::is_allowed('leaderplan:toggle', $appraisal->permissionsid, $this->type, $appraisal->archived, $appraisal->legacy);

        return $permissions;
    }

    protected function get_urls($appraisalid) {
        $urls = array(
            'start' => (new moodle_url('/local/onlineappraisal/view.php', array('appraisalid' => $appraisalid, 'page' => 'introduction', 'view' => $this->type)))->out(false),
            'view' => (new moodle_url('/local/onlineappraisal/view.php', array('appraisalid' => $appraisalid, 'page' => 'overview', 'view' => $this->type)))->out(false),
            'printappraisal' => (new moodle_url('/local/onlineappraisal/print.php', array('appraisalid' => $appraisalid, 'print' => 'appraisal', 'view' => $this->type)))->out(false),
            'printfeedback' => (new moodle_url('/local/onlineappraisal/print.php', array('appraisalid' => $appraisalid, 'print' => 'feedback', 'view' => $this->type)))->out(false),
            'printsuccessionplan' => (new moodle_url('/local/onlineappraisal/print.php', array('appraisalid' => $appraisalid, 'print' => 'successionplan', 'view' => $this->type)))->out(false),
            'printleaderplan' => (new moodle_url('/local/onlineappraisal/print.php', array('appraisalid' => $appraisalid, 'print' => 'leaderplan', 'view' => $this->type)))->out(false),
            'togglef2f' => (new moodle_url('/local/onlineappraisal/index.php', array('appraisalid' => $appraisalid, 'action' => 'togglef2f')))->out(false),
            'togglesuccessionplan' => (new moodle_url('/local/onlineappraisal/index.php', array('appraisalid' => $appraisalid, 'action' => 'togglesuccessionplan')))->out(false),
            'toggleleaderplan' => (new moodle_url('/local/onlineappraisal/index.php', array('appraisalid' => $appraisalid, 'action' => 'toggleleaderplan')))->out(false),
        );
        return $urls;
    }

    protected function handle_leavers_toggle() {
        // This needs to be set for all types.
        if ($this->type != 'appraisee' && !$this->searching) {
            $params = [
                'page' => $this->type,
                'leavers' => !$this->leavers,
                'cycle' => $this->cycle,
            ];
            if ($this->type === 'hrleader') {
                // Need to maintain groupid.
                $params['groupid'] = $this->index->groupid;
            }
            $this->data->toggleleaversurl = (new moodle_url('', $params))->out(false);
            $leaversstr = $this->leavers ? 'hide' : 'show';
            $this->data->toggleleaversstr = get_string("index:toggleleavers:{$leaversstr}", 'local_onlineappraisal');
        }
    }

    protected function handle_cycle_filter() {
        global $DB;

        if ($this->type != 'appraisee' && !$this->searching) {
            $cycles = $DB->get_records_select_menu(
                    'local_appraisal_cohorts',
                    'availablefrom < :now',
                    ['now' => time()],
                    'availablefrom DESC',
                    'id, name');

            $cyclecount = $this->index->get_cycle_appraisal_count($this->type, 'archived', $this->leavers);
            foreach ($cycles as $cycle => $cyclename) {
                if (isset($cyclecount[$cycle])) {
                    $cycles[$cycle] = $cyclename . " ({$cyclecount[$cycle]->count})";
                    if (!$this->cycle) {
                        $this->cycle = $cycle;
                    }
                } else {
                    $cycles[$cycle] = $cyclename . ' (0)';
                }
            }

            $params = [
                'page' => $this->type,
                'leavers' => $this->leavers // Maintain any leaver filtering.
            ];
            if ($this->type === 'hrleader') {
                // Need to maintain groupid.
                $params['groupid'] = $this->index->groupid;
            }
            $url = new \moodle_url('', $params);
            $this->cycleselect = new \single_select($url, 'cycle', $cycles, $this->cycle, null);
            $this->cycleselect->label = get_string('index:filter:label', 'local_onlineappraisal');
            $this->cycleselect->labelattributes = ['class' => 'm-t-5 m-r-5'];
            $this->cycleselect->class = 'pull-right';
        }
    }

    protected function handle_search() {
        global $DB;

        $this->data->page = $this->index->page;
        $this->data->searching = $this->searching;
        $this->data->searchoption = '<option></option>';

        if ($this->searching && $this->searchid) {
            $usertextconcat = $DB->sql_concat('firstname', "' '", 'lastname', "' ('", 'email', "')'");
            $usertext = $DB->get_field('user', $usertextconcat, ['id' => $this->searchid]);
            if ($usertext) {
                $this->data->searchoption = '<option value="'.$this->searchid.'">'.$usertext.'</option>';
            }
        }
    }
}
