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

namespace local_onlineappraisal;

defined('MOODLE_INTERNAL') || die();

define('APPRAISAL_NOT_STARTED', '1');
define('APPRAISEE_DRAFT', '2');
define('APPRAISER_REVIEW', '3');
define('APPRAISEE_FINAL_REVIEW', '4');
define('APPRAISER_FINAL_REVIEW', '5');
define('APPRAISAL_SIGN_OFF', '6');
define('APPRAISAL_COMPLETE', '7');
define('APPRAISAL_GROUPLEADERSUMMARY', '9');

// Ints for locking form fields.
define('APPRAISAL_FIELD_EDIT', '1');
define('APPRAISAL_FIELD_LOCKED', '2');

// Pre-load strings for JS on every page.
$PAGE->requires->strings_for_js(
        array(
            'form:confirm:cancel:title',
            'form:confirm:cancel:question',
            'form:confirm:cancel:yes',
            'form:confirm:cancel:no'),
        'local_onlineappraisal');
// Loads AMD module used on every page.
$PAGE->requires->js_call_amd('local_onlineappraisal/general', 'init');

// Want this to run on every appraisal page to change logo.
if (!isset($CFG->additionalhtmlhead)) {
    $CFG->additionalhtmlhead = '';
}
$CFG->additionalhtmlhead .= get_logo_css();

/**
 * Setup language and inject language menu.
 *
 * @global string $FULLME
 * @global \moodle_page $PAGE
 * @param bool $menu Inject menu into page.
 */
function lang_setup($menu = true) {
    global $DB, $FULLME, $PAGE, $SESSION, $USER;

    if (!isset($SESSION->local_onlineappraisal)) {
        $SESSION->local_onlineappraisal = new \stdClass();
    }

    // In case we get here before previous teardown has had a chance to run (redirects).
    if (!empty($SESSION->local_onlineappraisal->awaitingteardown)) {
        \local_onlineappraisal\lang_teardown();
    }

    // Register shutdown function to reset language to default.
    \core_shutdown_manager::register_function('\local_onlineappraisal\lang_teardown');

    // Flag that we're awaiting teardown.
    $SESSION->local_onlineappraisal->awaitingteardown = true;

    if (!empty($SESSION->lang)) {
        // Store current SESSION language.
        $SESSION->local_onlineappraisal->prevlang = $SESSION->lang;
    }

    $lang = optional_param('appraisallang', null, PARAM_SAFEDIR);
    if ($lang && get_string_manager()->translation_exists($lang, false)) {
        if (!empty($USER->id)) {
            $langsetting = $DB->get_record('local_appraisal_users', array('userid' => $USER->id, 'setting' => 'lang'));
            if ($langsetting) {
                $langsetting->value = $lang;
                $DB->update_record('local_appraisal_users', $langsetting);
            } else {
                $langsetting = new \stdClass();
                $langsetting->userid = $USER->id;
                $langsetting->setting = 'lang';
                $langsetting->value = $lang;
                $DB->insert_record('local_appraisal_users', $langsetting);
            }
        }
    } else if (!empty($SESSION->local_onlineappraisal->lang)) {
        $lang = $SESSION->local_onlineappraisal->lang;
    } else if (!empty($USER->id)) {
        $langsetting = $DB->get_field('local_appraisal_users', 'value', array('userid' => $USER->id, 'setting' => 'lang'));
        if ($langsetting) {
            $lang = $langsetting;
        }
    } else {
        unset($lang);
    }

    if (!empty($lang)) {
        // Main SESSION language setting (reset on shutdown).
        $SESSION->lang = $lang;
        // Our SESSION language setting.
        $SESSION->local_onlineappraisal->lang = $lang;
        moodle_setlocale();
    }

    if ($menu) {
        // Use $FULLME as $PAGE->url may not have all parameters fully set yet.
        $url = new \moodle_url($FULLME);
        $s = new \single_select($url, 'appraisallang', get_string_manager()->get_list_of_translations(), current_language(), null);
        $s->label = get_accesshide(get_string('form:language', 'local_onlineappraisal'));
        $s->class = 'langmenu pull-right';

        // Reverse order as they pull-right!
        $button = $PAGE->get_renderer('local_onlineappraisal')->render($s) . $PAGE->button;
        $PAGE->set_button($button);
    }
}

function lang_teardown() {
    global $SESSION;
    if (session_status() !== PHP_SESSION_ACTIVE) {
        // No session, will teardown before next setup.
        return;
    }
    if (!empty($SESSION->local_onlineappraisal->prevlang)) {
        $SESSION->lang = $SESSION->local_onlineappraisal->prevlang;
        unset($SESSION->local_onlineappraisal->prevlang);
    } else {
        unset($SESSION->lang);
    }
    moodle_setlocale();
    // Mark that we're done.
    unset($SESSION->local_onlineappraisal->awaitingteardown);
}

/**
 * Get CSS, to inject in head, for custom log.
 *
 * @global \stdClass $CFG
 * @return string
 */
function get_logo_css() {
    global $CFG;

    $filepath = get_config('local_onlineappraisal', 'logo');

    if (!$filepath) {
        return '';
    }

    $component = 'local_onlineappraisal';
    $filearea = 'logo';
    $itemid = 0;
    $syscontext = \context_system::instance();

    $url = \moodle_url::make_file_url("$CFG->wwwroot/pluginfile.php", "/$syscontext->id/$component/$filearea/$itemid".$filepath);

    return <<<EOS
<style>
    .path-local-onlineappraisal nav#header a.navbar-brand {
        background-image: url("{$url->out(false)}");
        background-repeat: no-repeat;
        disaply: block;
        height: 59px;
        width: 199px;
    }
    .path-local-onlineappraisal nav#header a.navbar-brand img {
        display: none;
    }
</style>
EOS;
}
