<?php
// This file is part of the arup theme for Moodle
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
 * Renderer override for mod_hvp.
 *
 * @package    theme_arup
 * @copyright  2016 Arup
 * @author     Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("{$CFG->dirroot}/mod/hvp/renderer.php");

class theme_arup_mod_hvp_renderer extends mod_hvp_renderer {
    /**
     * Add styles when an H5P activity is displayed.
     *
     * @param array $styles Styles that will be applied.
     * @param array $libraries Libraries that will be shown.
     * @param string $embedType How the H5P activity is displayed.
     */
    public function hvp_alter_styles(&$styles, $libraries, $embedType) {
        global $CFG;
        $styles[] = (object) array(
            'path'    => $CFG->httpswwwroot . '/theme/arup/style/mod_hvp.css',
            'version' => '?ver=0.0.1',
        );

    }

    /**
     * Alter semantics before they are processed. This is useful for changing
     * how the editor looks and how content parameters are filtered.
     *
     * @param object $semantics Semantics as object
     * @param string $name Machine name of library
     * @param int $majorversion Major version of library
     * @param int $minorversion Minor version of library
     */
    public function hvp_alter_semantics(&$semantics, $name, $majorversion, $minorversion) {
        if ($name === 'H5P.ImageHotspots') {
            foreach ($semantics as $value) {
                if (isset($value->name) && $value->name === 'color') {
                    $value->default = '#0f95ff';
                }
            }
        }
        error_log(print_r($semantics, true));
    }
}