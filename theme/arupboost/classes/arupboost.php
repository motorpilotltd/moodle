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
 * Global theme elements.
 *
 * @package   theme_arupboost
 * @copyright 2018 Moodle
 * @author    Bas Brands
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace theme_arupboost;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use moodle_url;
use Exception;
use moodle_exception;
use navigation_node;
use flat_navigation_node;

class arupboost {

    /**
     * Remove items from the flat navigation menu.
     */
    public function removenav() {
        global $PAGE, $CFG;
        $flatnav = $PAGE->flatnav;

        // Check if the appraisal system is installed.
        $hasappraisal = $inappraisal = $hascatalogue = $incatalogue = $appraisal = $catalogue = false;

        // Create the Appraisal nav item.
        $appraisalfile = $CFG->dirroot . '/local/onlineappraisal/version.php';
        if (file_exists($appraisalfile)) {
            $hasappraisal = true;
        }
        if ($hasappraisal) {
            if (preg_match('/page-local-onlineappraisal/', $PAGE->bodyid)) {
                $inappraisal = true;
            }
            $aurl = new moodle_url('/local/onlineappraisal/index.php');
            $anav = navigation_node::create(get_string('pluginname', 'local_onlineappraisal'), $aurl);
            $appraisal = new flat_navigation_node($anav, 0);
            $appraisal->key = 'appraisal';
            $appraisal->icon->pix = 'a/appraisal';
            $appraisal->icon->component = 'theme';
        }

        // Create the Catalogue nav item.
        $cataloguefile = $CFG->dirroot . '/local/catalogue/version.php';
        if (file_exists($cataloguefile)) {
            $hascatalogue = true;
        }
        if ($hascatalogue) {
            if (preg_match('/page-local-catalogue/', $PAGE->bodyid)) {
                $incatalogue = true;
            }
            $curl = new moodle_url('/local/catalogue/index.php');
            $cnav = navigation_node::create(get_string('pluginname', 'local_catalogue'), $curl);
            $catalogue = new flat_navigation_node($cnav, 0);
            $catalogue->key = 'catalogue';
            $catalogue->icon->pix = 'a/catalogue';
            $catalogue->icon->component = 'theme';
        }

        // Create the Help nav item.
        $firstaction = false;
            $hurl = new moodle_url('/mod/book/view.php', ['id' => 539]);
            $hnav = navigation_node::create(get_string('help', 'core'), $hurl);
            $help = new flat_navigation_node($hnav, 0);
            $help->key = 'help';
            $help->icon->pix = 'a/help';
            $help->icon->component = 'theme';

        // Fetch the navitems from the current navigation object.
        $home = $myhome = $course = $settings = $calendar = $participants = false;
        foreach ($flatnav as $action) {
            if ($action->key == 'home') {
                $home = $action;
            }
            if ($action->key == 'myhome') {
                $myhome = $action;
            }
            if ($action->key == 'coursehome') {
                $course = $action;
            }
            if ($action->key == 'sitesettings') {
                $settings = $action;
            }
            if ($action->key == 'calendar') {
                $calendar = $action;
            }
            if ($action->key == 'participants') {
                $participants = $action;
            }
        }

        // Always add home as the first link.
        $keys = $flatnav->get_key_list();

        $newnavtop = [$home, $myhome, $catalogue, $calendar, $appraisal, $help];
        $newnavbottom = [$course, $participants, $settings];

        // Empty the nav object.
        foreach ($keys as $key) {
             $flatnav->remove($key);
        }
        foreach ($newnavtop as $navitem) {
            if ($navitem) {
                $navitem->set_showdivider(false);
                $flatnav->add($navitem);
            }
        }

        $first = true;
        foreach ($newnavbottom as $navitem) {
            if ($navitem) {
                $navitem->set_showdivider(false);
                if ($first) {
                    $navitem->set_showdivider(true);
                }
                $first = false;
                $flatnav->add($navitem);
            }
        }

        if ($PAGE->pagelayout == 'admin' && $action->key == 'sitesettings') {
            $settings->make_active();
        }
        if ($inappraisal) {
            $appraisal->make_active();
        }
    }

    /**
     * Theme arupboost image renderer
     *
     * @param string $type the image type
     * @param int $imageid the image imageid
     * @param string $originalimage the original image url
     * @param string $fallbackimage the default image url
     */
    public function arupboostimage($type, $imageid, $originalimage, $fallbackimage = null) {
        global $PAGE, $OUTPUT, $USER;

        if ($type !== 'catalogue' && $type !== 'course') {
            return false;
        }
        $image = new stdClass();
        $image->imageid = $imageid;
        if ($type == 'course') {
            $image->contextid = \context_course::instance($imageid)->id;
        }
        if ($type == 'catalogue') {
            $image->contextid = \context_coursecat::instance($imageid)->id;
        }
        $image->type = $type;

        $croppedimage = $this->arupboostimage_url($image->contextid, 'theme_arupboost', $type . '_cropped');
        $fullimage = $this->arupboostimage_url($image->contextid, 'theme_arupboost', $type);

        $image->defaultimage = $fallbackimage;
        $image->originalimage = $originalimage;

        if ($croppedimage) {
            $image->image = $croppedimage;
            $image->originalimage = $fullimage;
        } else if ($fullimage) {
            $image->image = $fullimage;
            $image->originalimage = $fullimage;
        } else {
            $image->image = $originalimage;
        }

        if ($PAGE->user_allowed_editing() && isset($USER->editing) && $USER->editing == 1) {
            $image->allowcrop = true;
            $image->allowupload = true;
        }
        return $OUTPUT->render_from_template('theme_arupboost/imagehandler', $image);
    }

    /**
     * Get the image url for the arupboostimage
     *
     * @param int $contextid The image contextid
     * @param string $component The image component
     * @param string $filearea The image filearea
     */
    public function arupboostimage_url($contextid, $component, $filearea) {
        $fs = get_file_storage();
        $files = $fs->get_area_files($contextid, $component, $filearea, 0, "itemid, filepath, filename", false);
        if (!$files) {
            return false;
        }
        if (count($files) > 1) {
            // Note this is a coding exception and not a moodle exception because there should never be more than one
            // file in this area, where as the course summary files area can in some circumstances have more than on file.
            throw new \coding_exception('Multiple files found in filearea (context '.$contextid.')');
        }
        $file = (end($files));

        return \moodle_url::make_pluginfile_url(
            $file->get_contextid(),
            $file->get_component(),
            $file->get_filearea(),
            $file->get_timemodified(), // Used as a cache buster.
            $file->get_filepath(),
            $file->get_filename()
        );
    }

}