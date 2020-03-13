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
 * Participants block
 *
 * @package    block_participants
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/course/lib.php');

/**
 * Participants block
 *
 * @package    block_participants
 * @copyright  1999 onwards Martin Dougiamas (http://dougiamas.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class block_panels extends block_base {

    /**
     * Initialises the block
     */
    function init() {
        $this->title = get_string('pluginname', 'block_panels');
    }

    /**
     * Returns the content object
     *
     * @return stdObject
     */
    function get_content() {
        global $PAGE, $OUTPUT;
        if ($this->content !== NULL) {
            return $this->content;
        }

        $this->content = new stdClass;
        $this->content->text = '';

        $panelset = \local_panels\panelset::fetchbycontextid($this->context->id);

        if ($PAGE->user_is_editing()) {
            $url = new moodle_url('/local/panels/editpanelset.php', ['contextid' => $this->context->id]);
            $this->content->text .= $OUTPUT->render_from_template('block_panels/edit', (object) ['url' => $url]);
        }

        if (!$panelset) {
            $this->content->text .= '';
        } else {
            $this->content->text .= $panelset->render();
        }

        $this->content->footer = '';

        return $this->content;
    }


    public function get_content_for_output($output) {
        $retval = parent::get_content_for_output($output);

        if ($retval == null || empty($retval->controls)) {
            return $retval;
        }

        $str = new lang_string('editpanels', 'block_panels');
        $url = new moodle_url('/local/panels/editpanelset.php', ['contextid' => $this->context->id]);
        $icon = new pix_icon('t/edit', $str, 'moodle', array('class' => 'iconsmall', 'title' => ''));
        $attributes = array('class' => 'editing_show');
        $retval->controls[] = new action_menu_link_secondary($url, $icon, $str, $attributes);

        return $retval;
    }

    function instance_allow_multiple() {
        // Are you going to allow multiple instances of each block?
        // If yes, then it is assumed that the block WILL USE per-instance configuration
        return true;
    }
}