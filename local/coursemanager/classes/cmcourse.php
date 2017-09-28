<?php
// This file is part of the Arup Course Management system
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
 *
 * @package     local_coursemanager
 * @copyright   2017 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursemanager;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use moodle_url;
use Exception;
use moodle_exception;
use html_writer;

class cmcourse {

    private $coursemanager;
    /**
     * Constructor.
     *
     * @param object $coursemanager the full coursemanager object
     */
    public function __construct(\local_coursemanager\coursemanager $coursemanager = null) {
        $this->coursemanager = $coursemanager;
    }
    /**
     * Hook
     *
     * This function is called from the main coursemanager controller when the page
     * is loaded. This function can be added to all the other page types as long as this
     * class is being declared in \local_coursemanager\coursemanager->add_page();
     */
    public function hook() {
        global $DB, $OUTPUT, $USER;
        if (!$this->coursemanager->editing) {
            return;
        }
        $template = new stdClass();
        $template->items = array();

        $myparams = array('edit' => 1, 'page' => 'course', 'start' => $this->coursemanager->start);
        $params = array_merge($this->coursemanager->searchparams, $myparams);
        $paramtab = optional_param('tab', 1, PARAM_INT);

        for ($i = 1; $i <= 3; $i++) {
            $tab = new stdClass();
            if ($this->coursemanager->cmcourse->id > 0) {
                $tab->visible = false;
            } else {
                $tab->visible = true;
            }
            if ($paramtab == $i) {
                $tab->active = 'active';
                $tab->visible = true;
            }
            if ($i > $paramtab) {
                $tab->active = 'disabled';
                $tab->url = '#';
            } else {
                $tab->url = new moodle_url($this->coursemanager->baseurl,
                array_merge($params, array('tab' => $i)));
            }

            $tab->name = get_string('form:course:tab'.$i, 'local_coursemanager');
            $template->items[] = $tab;
        }

        return $OUTPUT->render_from_template('local_coursemanager/navlist', $template);
    }
}