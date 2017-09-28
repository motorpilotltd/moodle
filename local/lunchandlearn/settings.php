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
 * Settings File
 *
 * @package    local
 * @subpackage regions
 */

defined('MOODLE_INTERNAL') || die;

if (!class_exists('local_lunchandlearn_admin_setting_confightmleditor')) {
    class local_lunchandlearn_admin_setting_confightmleditor extends admin_setting_configtext {
        private $rows;
        private $cols;

        private $tinymcecustomconfig = false;

        /**
         * @param string $name
         * @param string $visiblename
         * @param string $description
         * @param mixed $defaultsetting string or array
         * @param mixed $paramtype
         */
        public function __construct($name, $visiblename, $description, $defaultsetting, $paramtype=PARAM_RAW, $cols='60', $rows='8') {
            $this->rows = $rows;
            $this->cols = $cols;
            parent::__construct($name, $visiblename, $description, $defaultsetting, $paramtype);
            editors_head_setup();
        }

        /**
         * Returns an XHTML string for the editor
         *
         * @param string $data
         * @param string $query
         * @return string XHTML string for the editor
         */
        public function output_html($data, $query='') {
            $default = $this->get_defaultsetting();

            $defaultinfo = $default;
            if (!is_null($default) and $default !== '') {
                $defaultinfo = "\n".$default;
            }

            $editor = editors_get_preferred_editor(FORMAT_HTML);
            if (get_class($editor) === 'tinymce_texteditor') {
                $this->set_tinymce_customconfig();
            }
            $editor->set_text($data);
            $editor->use_editor($this->get_id(), array('noclean'=>true));

            if (get_class($editor) === 'tinymce_texteditor') {
                $this->reset_tinymce_customconfig();
            }

            return format_admin_setting($this, $this->visiblename,
            '<div class="form-textarea"><textarea rows="'. $this->rows .'" cols="'. $this->cols .'" id="'. $this->get_id() .'" name="'. $this->get_full_name() .'" spellcheck="true">'. s($data) .'</textarea></div>',
            $this->description, true, '', $defaultinfo, $query);
        }

        private function set_tinymce_customconfig() {
            // Override for TinyMCE URL conversion.
            $this->tinymcecustomconfig = get_config('editor_tinymce', 'customconfig');
            if (!empty($this->tinymcecustomconfig)) {
                $tempcustomconfig = json_decode($this->tinymcecustomconfig);
            } else {
                $tempcustomconfig = new stdClass();
            }
            $tempcustomconfig->convert_urls = false;
            set_config('customconfig', json_encode($tempcustomconfig), 'editor_tinymce');
        }

        private function reset_tinymce_customconfig() {
            // Reset customconfig.
            set_config('customconfig', $this->tinymcecustomconfig, 'editor_tinymce');
        }
    }
}

if (isset($ADMIN)) {
    require_once($CFG->dirroot . '/local/lunchandlearn/lib.php');
    $fakecap = 'moodle/site:config';
    if (count(lunchandlearn_manager::get_markable_categories()) > 0) {
        $fakecap = 'moodle/block:view';
    }
    $ADMIN->add('root', new admin_category('lunchandlearn', get_string('pluginname','local_lunchandlearn')));
    $ADMIN->add('lunchandlearn', new admin_externalpage('lunchandlearnlist', get_string('lunchandlearnviewsessions','local_lunchandlearn'), $CFG->wwwroot . '/local/lunchandlearn/index.php', $fakecap, false));
    $settingpage = new admin_settingpage('lunchandlearnglobalsettings', get_string('settings', 'local_lunchandlearn'));
    $ADMIN->add('lunchandlearn', $settingpage);

    // Root category for course category list
    $settingpage->add(new admin_setting_configselect('lunchandlearnrootcategory', get_string('setting:rootcategory', 'local_lunchandlearn'),get_string('setting:rootcategorylong', 'local_lunchandlearn'), 0, coursecat::make_categories_list()));

    // Timezones to use in dropdown
    $timezones = array();
    foreach (DateTimeZone::listIdentifiers() as $tz) {
        $now = new DateTime();
        $now->setTimezone(new DateTimeZone($tz));
        $tzread = str_replace('_', ' ', $tz);
        $timezones[$tz] = $now->format('P (T)') .' - '.$tzread;
    }
    $settingpage->add(new admin_setting_configmultiselect(
                'lunchandlearntimezones',
                get_string('setting:timezones', 'local_lunchandlearn'),
                get_string('setting:timezoneslong', 'local_lunchandlearn'),
                array('Europe/London'),
                $timezones
    ));
    $settingpage->add(new admin_setting_heading('notifications', get_string('setting:notifications', 'local_lunchandlearn'), get_string('setting:notificationslong', 'local_lunchandlearn')));

    $settingpage->add(new local_lunchandlearn_admin_setting_confightmleditor('lunchandlearnsignupemail',
            get_string('setting:signupemail', 'local_lunchandlearn'),
            get_string('setting:signupemaillong', 'local_lunchandlearn'),
            get_string('setting:signupemaildefault', 'local_lunchandlearn')
    ));

    $settingpage->add(new admin_setting_configtext('lunchandlearnreminderdays',
            get_string('setting:reminderdayslong', 'local_lunchandlearn'),
            get_string('setting:reminderdayslong', 'local_lunchandlearn'),
            1, PARAM_NUMBER));


    $settingpage->add(new local_lunchandlearn_admin_setting_confightmleditor('lunchandlearnreminderemail',
            get_string('setting:reminderemail', 'local_lunchandlearn'),
            get_string('setting:reminderemaillong', 'local_lunchandlearn'),
            get_string('setting:reminderemaildefault', 'local_lunchandlearn')
    ));

    $settingpage->add(new local_lunchandlearn_admin_setting_confightmleditor('lunchandlearncancelemail',
            get_string('setting:cancelemail', 'local_lunchandlearn'),
            get_string('setting:cancelemaillong', 'local_lunchandlearn'),
            get_string('setting:cancelemaildefault', 'local_lunchandlearn')
    ));

    $settingpage->add(new local_lunchandlearn_admin_setting_confightmleditor('lunchandlearnadmincancelemail',
            get_string('setting:admincancelemail', 'local_lunchandlearn'),
            get_string('setting:admincancelemaillong', 'local_lunchandlearn'),
            get_string('setting:admincancelemaildefault', 'local_lunchandlearn')
    ));

    $settingpage->add(new local_lunchandlearn_admin_setting_confightmleditor('lunchandlearnadminbulkcancelemail',
            get_string('setting:adminbulkcancelemail', 'local_lunchandlearn'),
            get_string('setting:adminbulkcancelemaillong', 'local_lunchandlearn'),
            get_string('setting:adminbulkcancelemaildefault', 'local_lunchandlearn')
    ));
}