<?php
// This file is part of the Arup Carousel for Moodle
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
 * The Arup carousel block class.
 *
 * @package    block_carousel
 * @copyright  2016 Arup
 * @author     Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class block_carousel extends block_base {

    function init() {
        $this->title = get_string('pluginname', 'block_carousel');
    }

    function get_content() {
        global $CFG, $DB, $COURSE, $PAGE, $USER;


        $PAGE->requires->js_call_amd('block_carousel/carousel', 'init');

        require_once($CFG->dirroot . '/blocks/carousel/locallib.php');

        $this->content = new stdClass();

        if ($PAGE->theme->name != 'arup') {
            $this->content->text = '';
            return;
        }
		
		//first, determine the user's region
        $userregion = $DB->get_record('local_regions_use', array('userid' => $USER->id));
		if (!$userregion) {
			//by default, we display the 'Default' carousel if it exists, otherwise nothing
			$region_id = 0;
		} else {
            $region_id = $userregion->regionid;
        }

        $forceregion = optional_param('carouselregion', 0, PARAM_INT);
        if ($forceregion) {
            $region_id = $forceregion;
        }
        
		//select the appropriate carousel based on the user's region
        $carousels = $DB->get_records('block_carousel', array('regionid' => $region_id));
        //try and get deafult if no resutls
        if (!$carousels) {
            $carousels = $DB->get_records('block_carousel', array('regionid' => 0));
        }

		if ($carousels) {
            $carousel = array_shift($carousels);
            $template = new carousel($carousel);
            $renderer = $this->page->get_renderer('block_carousel');

            $this->content->text = $renderer->carousel($template);
		} else {
			// Empty content so carousel isn't displayed
			$this->content->text   = '';
		}

        return $this->content;
    }

	//Hide the carousel block header
	public function hide_header() {
	    return true;
	}
	
    // my moodle can only have SITEID and it's redundant here, so take it away
    public function applicable_formats() {
        return array('all' => false,
                     'site' => true,
                     'site-index' => true,
					 'my-index' => true, 
                     'course-view' => true, 
                     'course-view-social' => false,
                     'mod' => true, 
                     'mod-quiz' => false);
    }

    public function instance_allow_multiple() {
        //return true;
		//we only want one carousel instance to be added to the homepage, so return false
		return false;
    }

    function has_config() {
		return true;
	}
	
	/**
     * Serialize and store config data
     */
    function instance_config_save($data, $nolongerused = false) {
        $config = clone($data);
        parent::instance_config_save($config, $nolongerused);
    }

    function instance_delete() {
        global $DB;
        $fs = get_file_storage();
        $fs->delete_area_files(context_system::instance()->id, 'block_carousel');
        return true;
    }

    function content_is_trusted() {
        global $SCRIPT;

        if (!$context = get_context_instance_by_id($this->instance->parentcontextid)) {
            return false;
        }

        return true;
    }

    function user_can_addto($page) {
        global $DB;
        
        // Does an instance already exist? If so, can't add another...
        $select = 'blockname = :blockname';
        $params = array('blockname' => $this->name());
        if (is_object($this->instance)) {
            $select .= ' AND id != :id';
            $params['id'] = $this->instance->id;
        }
        if ($DB->get_records_select('block_instances', $select, $params)) {
            return false;
        }

        return parent::user_can_addto($page);
    }
}
