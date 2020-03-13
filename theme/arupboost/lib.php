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
 * Callbacks
 *
 * @package     theme_arupboost
 * @copyright   2019 Bas Brands <bas@sonsbeekmedia.nl>
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

/**
 * Implementation of $THEME->scss
 *
 * @param theme_config $theme
 * @return string
 */
function theme_arupboost_get_main_scss_content($theme) {
    global $CFG;
    $scss = '';
    $scss .= '$loginbackground: "' . $theme->setting_file_url('loginbackground', 'loginbackground') . '";';
    $scss .= '$frontpageimage: "' . $theme->setting_file_url('frontpageimage', 'frontpageimage') . '";';
    $scss .= file_get_contents($CFG->dirroot . '/theme/arupboost/scss/default.scss');
    return $scss;
}

/**
 * Inject additional SCSS.
 *
 * @param theme_config $theme The theme config object.
 * @return string
 */
function theme_arupboost_get_extra_scss($theme) {
    return !empty($theme->settings->scss) ? $theme->settings->scss : '';
}

/**
 * Get SCSS to prepend.
 *
 * @param theme_config $theme The theme config object.
 * @return array
 */
function theme_arupboost_get_pre_scss($theme) {
    global $CFG;

    $scss = '';
    $configurable = [
        // Config key => [variableName, ...].
        'brandcolor' => ['primary'],
    ];

    // Prepend variables first.
    foreach ($configurable as $configkey => $targets) {
        $value = isset($theme->settings->{$configkey}) ? $theme->settings->{$configkey} : null;
        if (empty($value)) {
            continue;
        }
        array_map(function($target) use (&$scss, $value) {
            $scss .= '$' . $target . ': ' . $value . ";\n";
        }, (array) $targets);
    }

    // Prepend pre-scss.
    if (!empty($theme->settings->scsspre)) {
        $scss .= $theme->settings->scsspre;
    }

    if (!empty($theme->settings->fontsize)) {
        $scss .= '$font-size-base: ' . (1 / 100 * $theme->settings->fontsize) . "rem !default;\n";
    }

    return $scss;
}

function theme_arupboost_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($filearea === 'catalogue') {
        theme_arupboost_send_file($context, $filearea, $args, $forcedownload, $options);
    }

    if ($filearea === 'catalogue_cropped') {
        theme_arupboost_send_file($context, $filearea, $args, $forcedownload, $options);
    }

    if ($filearea === 'course') {
        theme_arupboost_send_file($context, $filearea, $args, $forcedownload, $options);
    }
    if ($filearea === 'course_cropped') {
        theme_arupboost_send_file($context, $filearea, $args, $forcedownload, $options);
    }
    if ($context->contextlevel == CONTEXT_SYSTEM) {
        $theme = theme_config::load('arupboost');
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        if ($filearea === 'loginbackground') {
            return $theme->setting_file_serve('loginbackground', $args, $forcedownload, $options);
        } else if ($filearea === 'frontpageimage') {
            return $theme->setting_file_serve('frontpageimage', $args, $forcedownload, $options);
        }
    } else {
        send_file_not_found();
    }
}

/**
 * Based on theme function setting_file_serve.
 * Always sends item 0
 *
 * @param $context
 * @param $filearea
 * @param $args
 * @param $forcedownload
 * @param $options
 * @return bool
 */
function theme_arupboost_send_file($context, $filearea, $args, $forcedownload, $options) {
    $revision = array_shift($args);
    if ($revision < 0) {
        $lifetime = 0;
    } else {
        $lifetime = DAYSECS * 60;
    }

    $filename = end($args);
    $contextid = $context->id;
    $fullpath = "/$contextid/theme_arupboost/$filearea/0/$filename";
    $fs = get_file_storage();
    $file = $fs->get_file_by_hash(sha1($fullpath));

    if ($file) {
        send_stored_file($file, $lifetime, 0, $forcedownload, $options);
        return true;
    } else {
        send_file_not_found();
    }
}

/**
 * Get compiled css.
 *
 * @return string compiled css
 */
function theme_arupboost_get_precompiled_css() {
    global $CFG;
    return file_get_contents($CFG->dirroot . '/theme/arupboost/style/moodle.css');
}