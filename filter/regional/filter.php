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

class filter_regional extends moodle_text_filter {

    private static $region;
    private static $css;
    private static $js;

    /**
     * We add some Javascript to sweep up any non-filtered out bits.
     *
     * @param moodle_page $page Moodle page object
     * @param context $context Page context object
     * @return void
     */
    public function setup($page, $context) {
        if (empty(self::$js)) {
            $page->requires->js_call_amd('filter_regional/filter', 'initialise', array('css' => $this->_get_css()));
            self::$js = true;
        }
    }

    protected function _get_region() {
        global $DB, $USER;

        if (empty(self::$region)) {
            $sql = <<<EOS
SELECT
    lrr.name
FROM
    {local_regions_use} lru
JOIN
    {local_regions_reg} lrr
    ON lrr.id = lru.regionid
WHERE
    lru.userid = :userid
EOS;

            $configdefault = get_config('filter_regional', 'default');
            $default = empty($configdefault) ? 'default' : $configdefault;
            $userregion = $DB->get_field_sql($sql, array('userid' => $USER->id));
            self::$region = $userregion ? $userregion : $default;
        }
        return self::$region;
    }

    protected function _get_css() {
        if (empty(self::$css)) {
            $region = $this->_get_region();
            self::$css = '[data-visible-regions] { display: none; } '.
                    '[data-visible-regions*="'
                    .strtolower($region)
                    .'"], [data-visible-regions*="'
                    .ucfirst(strtolower($region))
                    .'"], [data-visible-regions*="'
                    .ucwords(strtolower($region))
                    .'"], [data-visible-regions*="'
                    .strtoupper($region)
                    .'"] { display: block; } '.
                    'span[data-visible-regions*="'
                    .strtolower($region)
                    .'"], span[data-visible-regions*="'
                    .ucfirst(strtolower($region))
                    .'"], span[data-visible-regions*="'
                    .ucwords(strtolower($region))
                    .'"], span[data-visible-regions*="'
                    .strtoupper($region)
                    .'"] { display: inline; }';
        }
        return self::$css;
    }

    /**
     * Apply the filter.
     *
     * @param string $text Text that is to be displayed on the page
     * @param array $options An array of additional options
     * @return string The same text or modified text is returned
     */
    public function filter($text, array $options = array()) {
        global $CFG;

        if (!is_string($text) or empty($text)) {
            // Non string data can not be filtered anyway.
            return $text;
        }

        if (stripos($text, 'data-visible-regions') === false) {
            return $text;
        }

        $region = $this->_get_region();

        $dom = new DOMDocument();
        $dom->loadHTML($text);
        $xpath = new DOMXpath($dom);
        $elements = $xpath->query("//*[@data-visible-regions]");

        if (!is_null($elements)) {
            foreach ($elements as $element) {
                $visibleregions = array_map('strtolower',
                                    array_map('trim',
                                        explode(',', $element->getAttribute('data-visible-regions'))));

                if (false === in_array(strtolower($region), $visibleregions)) {
                    $element->parentNode->removeChild($element);
                } else {
                    // Remove the data attribute or else it will be picked up by JS too.
                    $element->removeAttribute('data-visible-regions');
                }
            }
        }
        return $dom->saveHTML();
    }
}
