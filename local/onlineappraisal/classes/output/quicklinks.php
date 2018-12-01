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

namespace local_onlineappraisal\output;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use renderer_base;
use stdClass;
use moodle_url;

class quicklinks implements renderable, templatable {
    private $data;

    /**
     * Quick links constructor.
     * @param string $text The caption text.
     */
    public function __construct($language, $page) {
        $this->data = new stdClass();
        $this->data->items = [];
        $quicklinks = get_config('local_onlineappraisal', 'quicklinks');
        $lines = explode("\n", $quicklinks);
        foreach ($lines as $line) {
            $line = trim($line);
            if (strlen($line) == 0) {
                continue;
            }
            // Parse item settings.
            $item = new stdClass();
            $item->text = null;
            $item->title = null;
            $item->url = null;
            $item->visible = true;
            $item->popup = true;
            $settings = explode('|', $line);
            foreach ($settings as $i => $setting) {
                $setting = trim($setting);
                if (!empty($setting)) {
                    switch ($i) {
                        case 0:
                            $item->text = trim($setting);
                            $item->title = $item->text;
                            break;
                        case 1:
                            try {
                                $item->url = new moodle_url($setting);
                            } catch (moodle_exception $e) {
                                $item->url = null;
                            }
                            break;
                        case 2:
                            if (!empty($language)) {
                                $itemlanguages = array_map('trim', explode(',', ltrim($setting, '!')));
                                if (stripos($setting, '!') === 0) {
                                    $item->visible &= !in_array($language, $itemlanguages);
                                } else {
                                    $item->visible &= in_array($language, $itemlanguages);
                                }
                            }
                            break;
                        case 3:
                            if (!empty($page)) {
                                $pages = array_map('trim', explode(',', ltrim($setting, '!')));
                                if (stripos($setting, '!') === 0) {
                                    $item->visible &= !in_array($page, $pages);
                                } else {
                                    $item->visible &= in_array($page, $pages);
                                }
                            }
                            break;
                    }
                }
            }
            if ($item->visible) {
                $this->data->items[] = $item;
            }
        }
    }

    /**
     * Is there any content?
     * @return bool
     */
    public function has_content() {
        return !empty($this->data->items);
    }

    /**
     * Export this data so it can be used as the context for a mustache template.
     * @return stdClass
     */
    public function export_for_template(renderer_base $output) {
        return $this->data;
    }
}
