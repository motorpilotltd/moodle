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
 *
 * @package    block_carousel
 * @copyright  2016 Arup
 * @author     Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

class carousel {
    public $slides;
    public $slidenum;
    public $firstslide;
    public $lastslide;
    public $layout;
    public $multislides;

    public function __construct($carousel) {
        $this->slidenum = 0;
        if (empty($carousel->layout)) {
            $this->layout = 'centered';
        } else {
            $this->layout = $carousel->layout;
        }
        $this->multislides = true;
        $this->load_items($carousel->id);
        $this->find_prev_next();
    }

    private function load_items($carouselid) {
        global $DB;
        if ($slides = $DB->get_records('block_carousel_item', array('carousel_id' => $carouselid), 'display ASC')) {
            $this->add_slides($slides);
        }
    }


    public function add_slides($slides) {
        foreach ($slides as $slide) {
            $slide->slidenum = $this->slidenum;
            $slide->thisslide = $slide->image;
            $this->slides[$this->slidenum] = $slide;
            if ($this->slidenum == 0) {
                $this->firstslide = $slide;
            }
            $this->slidenum++;
        }
        $this->lastslide = $slide;
    }

    public function find_prev_next() {

        foreach ($this->slides as &$slide) {
            if (count($this->slides) == 1) {
                $slide->active = 'active';
                $this->multislides = false;
            } else if ($slide->slidenum == 0) {
                $slide->prevslide = $this->lastslide->image;
                $slide->active = 'active';
                $next = $this->slides[$slide->slidenum + 1];
                $slide->nextslide = $next->image;
            } else if ($slide->slidenum == (count($this->slides) -1)) {
                $slide->nextslide = $this->firstslide->image;
                $prev = $this->slides[$slide->slidenum - 1];
                $slide->nextslide = $prev->image;
            } else {
                $next = $this->slides[$slide->slidenum + 1];
                $slide->nextslide = $next->image;
                $prev = $this->slides[$slide->slidenum - 1];
                $slide->nextslide = $prev->image;
            }
        }
    }
}