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

namespace local_onlineappraisal\output\navlist;

defined('MOODLE_INTERNAL') || die();

class renderer extends \local_onlineappraisal\output\renderer {

    public function render_navlist(\local_onlineappraisal\output\navlist\navlist $navigation) {
        $bc = new \block_contents();
        $bc->attributes['id'] = 'local_onlineappraisal';
        $bc->title = 'Navigation';
        $bc->attributes['class'] = 'block block_local_onlineappraisal';

        // Call the export_for_template function from class navlist
        $nav = $navigation->export_for_template($this);

        $bc->content = parent::render_from_template('local_onlineappraisal/navlist', $nav);
        return $bc;
    }

    public function render_navlist_admin(\local_onlineappraisal\output\navlist\navlist_admin $navigation) {
        $bc = new \block_contents();
        $bc->attributes['id'] = 'local_onlineappraisal';
        $bc->title = 'Navigation';
        $bc->attributes['class'] = 'block block_local_onlineappraisal';

        // Call the export_for_template function from class navlist
        $nav = $navigation->export_for_template($this);

        $bc->content = parent::render_from_template('local_onlineappraisal/navlist', $nav);
        return $bc;
    }

    public function render_navlist_itadmin(\local_onlineappraisal\output\navlist\navlist_itadmin $navigation) {
        $bc = new \block_contents();
        $bc->attributes['id'] = 'local_onlineappraisal';
        $bc->title = 'Navigation';
        $bc->attributes['class'] = 'block block_local_onlineappraisal';

        // Call the export_for_template function from class navlist
        $nav = $navigation->export_for_template($this);

        $bc->content = parent::render_from_template('local_onlineappraisal/navlist', $nav);
        return $bc;
    }

    public function render_navlist_index(\local_onlineappraisal\output\navlist\navlist_index $navigation) {
        $bc = new \block_contents();
        $bc->attributes['id'] = 'local_onlineappraisal';
        $bc->title = 'Navigation';
        $bc->attributes['class'] = 'block block_local_onlineappraisal';

        // Call the export_for_template function from class navlist
        $nav = $navigation->export_for_template($this);

        $bc->content = parent::render_from_template('local_onlineappraisal/navlist', $nav);
        return $bc;
    }

    public function render_navlist_feedback(\local_onlineappraisal\output\navlist\navlist_feedback $navigation) {
        $bc = new \block_contents();
        $bc->attributes['id'] = 'local_onlineappraisal';
        $bc->title = 'Navigation';
        $bc->attributes['class'] = 'block block_local_onlineappraisal';

        // Call the export_for_template function from class navlist
        $nav = $navigation->export_for_template($this);

        $bc->content = parent::render_from_template('local_onlineappraisal/navlist', $nav);
        return $bc;
    }
}