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
 * Class for exporting the page footer from an stdClass.
 *
 * @package    theme_arupboost
 * @copyright  2019 Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace theme_arupboost\external;

defined('MOODLE_INTERNAL') || die();

use renderer_base;
use moodle_url;
use context;
use core\external\exporter;
use core_user\external\user_summary_exporter;

/**
 * Class for exporting the page footer data.
 *
 * @copyright  2019 Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class page_footer_exporter extends exporter  {

    /**
     * Related objects definition.
     *
     * @return array
     */
    protected static function define_related() {
        return [
            'context' => 'context',
        ];
    }

    /**
     * Other properties.
     *
     * @return array
     */
    protected static function define_properties() {
        return [
            'content' => [
                'type' => PARAM_TEXT
            ],
            'context' => [
                'type' => PARAM_INT
            ],
            'contacts' => [
                'type' => user_summary_exporter::read_properties_definition(),
                'multiple' => true,
                'optional' => true
            ]
        ];
    }

    /**
     * Get the additional values to inject while exporting.
     *
     * @param renderer_base $output The renderer.
     * @return array Keys are the property names, values are their values.
     */
    protected function get_other_values(renderer_base $output) {
        $context = $this->related['context'];

        return [
            'context' => $context->id
        ];
    }
}
