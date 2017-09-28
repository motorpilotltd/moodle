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

require_once($CFG->dirroot.'/blocks/arup_mylearning/content.php');

class block_arup_mylearning_tabs {

    protected $_showtabs;
    protected $_maskedtabs;

    public function __construct(array $allowedtabs = array(), array $maskedtabs = array()) {
        $this->_showtabs = $allowedtabs;
        $this->_maskedtabs = $maskedtabs;
        $this->_showtabs = array_diff($allowedtabs, array_keys($this->_maskedtabs));
    }

    public function get_tab_html($actualtab, $hide = false) {
        global $CFG;

        $tabs = array();
        $row = array();

        $currenttab = $actualtab;
        $linkedwhenselected = false;
        if (array_key_exists($actualtab, $this->_maskedtabs)) {
            $currenttab = $this->_maskedtabs[$actualtab];
            $linkedwhenselected = true;
        }

        foreach ($this->_showtabs as $showtab) {
            $row[] = new tabobject($showtab, "$CFG->wwwroot/my/index.php?tab={$showtab}", get_string($showtab, 'block_arup_mylearning'), '', $linkedwhenselected);
        }
        $tabs[] = $row;

        $hide = $hide ? 'hidden' : '';
        return html_writer::tag('div', print_tabs($tabs, $currenttab, null, null, true), array('id' => 'block_arup_mylearning_tabs_'.$actualtab, 'class' => $hide));
    }
}

