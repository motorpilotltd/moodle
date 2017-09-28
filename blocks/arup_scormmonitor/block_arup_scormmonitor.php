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
 * @package    block_arup_scormmonitor
 * @copyright  2017 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_arup_scormmonitor extends block_base {
    const ADMIN_SECTION = 'blocksettingarup_scormmonitor';
    const BLOCK_NAME = 'block_arup_scormmonitor';

    private $canaccess;
    private $active;
    private $isadmin;
    private $limit;
    private $scos;
    private $timeout;
    private $warn;

    public function init() {
        $this->title = get_string('pluginname', self::BLOCK_NAME);
    }

    public function specialization() {
        global $CFG, $DB, $PAGE;

        // Set variables.
        $this->canaccess = has_capability('block/arup_scormmonitor:canaccessscorm', $this->context);
        $this->active = isset($CFG->block_arup_scormmonitor_active) ? $CFG->block_arup_scormmonitor_active : 0;
        $this->limit = isset($CFG->block_arup_scormmonitor_limit) ? $CFG->block_arup_scormmonitor_limit : 100;
        $this->timeout = isset($CFG->block_arup_scormmonitor_timeout) ? ($CFG->block_arup_scormmonitor_timeout * 60) : 3600;
        $this->warn = isset($CFG->block_arup_scormmonitor_warn) ? ($CFG->block_arup_scormmonitor_warn / 100) : 0.75;

        $this->isadmin = is_siteadmin();

        $this->scoes = 0;

        // @TODO: Refactor.
        if (($PAGE->pagetype == 'mod-scorm-view' && $this->active) || $this->canaccess) {
            // Launches since timeout ago which don't have an exit.
            // Exit is tracked by new event.
            // NB. This is _NOT_ log store agnostic!
            $sql = <<<EOS
SELECT
    COUNT(DISTINCT lsl.id)
FROM
    {logstore_standard_log} lsl
LEFT JOIN
    {logstore_standard_log} lsl2
    ON lsl2.objectid = lsl.objectid
		AND lsl2.userid = lsl.userid
        AND lsl2.eventname = :exitevent
        AND lsl2.timecreated > lsl.timecreated
WHERE
    lsl.eventname = :launchevent
    AND lsl.timecreated > :timeout
    AND lsl2.id IS NULL
EOS;
            $params = array(
                'launchevent' => '\\mod_scorm\\event\\sco_launched',
                'exitevent' => '\\block_arup_scormmonitor\\event\\sco_exited',
                'timeout' => time() - ($this->timeout),
            );

            $this->scoes = $DB->count_records_sql($sql, $params);
            
            if (!$this->canaccess && $this->scoes >= ($this->limit * $this->warn)) {
                // JS.
                // String pre-loading.
                $PAGE->requires->strings_for_js(
                    array(
                        'launch:hide',
                        'launch:warn',
                    ), 'block_arup_scormmonitor');
                // Add JS.
                $arguments = array(
                    'hide' => $this->scoes >= $this->limit,
                    'warn' => $this->scoes >= ($this->limit * $this->warn),
                );
                $PAGE->requires->js_call_amd('block_arup_scormmonitor/scormmonitor', 'init', array($arguments));
            }
        }
    }

    public function has_config() {
        return true;
    }

    public function applicable_formats() {
        return array(
            'site-index' => true,
            'course-view' => true,
            'mod' => true);
    }

    public function instance_allow_multiple() {
        return false;
    }

    public function instance_allow_config() {
        return true;
    }

    public function instance_can_be_docked() {
        return false;
    }

    public function get_content() {
        // @TODO: Refactor to templates.
        
        // Empty if can display CSS.
        $this->content = new stdClass;
        $this->content->text = '';
        $this->content->footer = '';

        if ($this->isadmin) {
            $url = new moodle_url('/admin/settings.php', array('section' => self::ADMIN_SECTION));
            $this->content->text .= html_writer::tag('p', html_writer::link($url, get_string('link:globalconfig', self::BLOCK_NAME)));
            $active = $this->active ? 'YES' : 'NO';
            $warn = floor($this->limit * $this->warn);
            $timeout = $this->timeout / 60;
            $stats = array(
                "Active: {$active}",
                "Limit: {$this->limit} active SCOs",
                "Warn above: {$warn} active SCOs",
                "Timeout: {$timeout} minutes",
                // @TODO: Won't have a count if off so change to say 'N/A' or something.
                "Currently: {$this->scoes} active SCOs",
            );
            $this->content->text .= html_writer::tag('p', implode(html_writer::empty_tag('br'), $stats));
        }

        return $this->content;
    }
}
