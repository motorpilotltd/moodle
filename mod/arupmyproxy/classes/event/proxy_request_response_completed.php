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
 * The mod_arupmyproxy proxy request response completed event.
 *
 * @package    mod_arupmyproxy
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_arupmyproxy\event;
defined('MOODLE_INTERNAL') || die();

/**
 * The mod_arupmyproxy proxy request response completed event class.
 *
 * @package    mod_arupmyproxy
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class proxy_request_response_completed extends \core\event\base {
    /**
     * Init method.
     *
     * @return void
     */
    protected function init() {
        $this->data['crud'] = 'u';
        $this->data['edulevel'] = self::LEVEL_OTHER;
        $this->data['objecttable'] = 'arupmyproxy_proxies';
    }

    /**
     * Return localised event name.
     *
     * @return string
     */
    public static function get_name() {
        return get_string('eventproxyrequestresponsecompleted', 'arupmyproxy');
    }

    /**
     * Returns non-localised event description.
     *
     * @return string
     */
    public function get_description() {
        if ($this->other['allow']) {
            $status = 'ALLOWED';
        } else {
            $status = 'NOT ALLOWED';
        }
        return "PROXY REQUEST RESPONSE {$status} | Proxy: {$this->other['proxyuserid']} | User: {$this->relateduserid}";
    }
}
