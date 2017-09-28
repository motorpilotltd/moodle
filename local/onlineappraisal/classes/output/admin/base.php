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

namespace local_onlineappraisal\output\admin;

defined('MOODLE_INTERNAL') || die();

use renderable;
use templatable;
use stdClass;

abstract class base implements renderable, templatable {
    protected $admin;

    public function __construct(\local_onlineappraisal\admin $admin) {
        $this->admin = $admin;
    }

    protected function get_users_select($type, $altname = '') {
        $users = $this->admin->get_selectable_users($type);

        $data = new stdClass();
        $data->selectid = "oa-{$type}-select";
        $data->label = new stdClass();
        $data->label->class = 'sr-only';
        $data->label->label = get_string('form:select', 'local_onlineappraisal') . ' ' . get_string($type, 'local_onlineappraisal');
        $data->options = array();
        $option = new stdClass();
        $option->value = 0;
        $option->name = $altname ? get_string("form:{$altname}", 'local_onlineappraisal') : get_string('form:choosedots', 'local_onlineappraisal');
        $option->selected = true;
        $data->options[0] = clone($option);
        foreach ($users as $user) {
            $option->value = $user->id;
            $option->name = fullname($user) . " ({$user->email})";
            $option->selected = false;
            $data->options[$user->id] = clone($option);
        }
        return $data;
    }

    protected function get_progress($statusid) {
        $progress = new stdClass();
        $progress->count = max(array(0, $statusid - 1));
        $progress->percentage = $progress->count ? round(100 * ($progress->count / 6)) : 0;
        $progress->text = get_string('status:' . $statusid, 'local_onlineappraisal');
        return $progress;
    }
}
