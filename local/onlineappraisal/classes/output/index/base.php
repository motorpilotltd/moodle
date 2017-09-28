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
use stdClass;
use moodle_url;
use local_onlineappraisal\permissions as permissions;

abstract class base implements renderable, templatable {
    protected $index;
    protected $data;
    protected $type;

    protected static $state = array('current', 'archived');

    public function __construct(\local_onlineappraisal\index $index) {
        $this->index = $index;
        $this->data = new stdClass();
    }

    protected function set_type($type) {
        $this->type = $type;
    }

    protected function get_appraisals() {
        $istype = 'is' . $this->type;

        foreach (self::$state as $state) {
            $appraisals = $this->index->get_appraisals($this->type, $state);
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
        foreach (self::$state as $state) {
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
        $permissions = array();
        $permissions['start'] = $appraisal->statusid == APPRAISAL_NOT_STARTED && permissions::is_allowed('introduction:view', $appraisal->permissionsid, $this->type, $appraisal->archived, $appraisal->legacy);
        $hrnoview = $this->type === 'hrleader' && empty($this->index->canviewvip[$appraisal->costcentre]);
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
        }
        $permissions['editf2f'] = permissions::is_allowed('f2f:add', $appraisal->permissionsid, $this->type, $appraisal->archived, $appraisal->legacy);
        $permissions['togglef2f'] = permissions::is_allowed('f2f:complete', $appraisal->permissionsid, $this->type, $appraisal->archived, $appraisal->legacy);
        
        $permissions['haspermissions'] = in_array(true, $permissions);

        // Keep these separate to appear outside of dropdown.
        $permissions['notstarted'] = $appraisal->statusid == APPRAISAL_NOT_STARTED && !$permissions['start'] && !$vipnoview;
        $permissions['vipnoview'] = $vipnoview;

        return $permissions;
    }

    protected function get_urls($appraisalid) {
        $urls = array(
            'start' => (new moodle_url('/local/onlineappraisal/view.php', array('appraisalid' => $appraisalid, 'page' => 'introduction', 'view' => $this->type)))->out(false),
            'view' => (new moodle_url('/local/onlineappraisal/view.php', array('appraisalid' => $appraisalid, 'page' => 'overview', 'view' => $this->type)))->out(false),
            'printappraisal' => (new moodle_url('/local/onlineappraisal/print.php', array('appraisalid' => $appraisalid, 'print' => 'appraisal', 'view' => $this->type)))->out(false),
            'printfeedback' => (new moodle_url('/local/onlineappraisal/print.php', array('appraisalid' => $appraisalid, 'print' => 'feedback', 'view' => $this->type)))->out(false),
            'togglef2f' => (new moodle_url('/local/onlineappraisal/index.php', array('appraisalid' => $appraisalid, 'action' => 'togglef2f')))->out(false),
        );
        return $urls;
    }
}
