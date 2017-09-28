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
 * Theme bootswatch lib.
 *
 * @package    theme_arup
 * @copyright  2016 Arup
 * @author     Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

function arup_grid($hassidepre, $hassidepost) {
    if ($hassidepre && $hassidepost) {
        $regions = array('content' => 'col-sm-6 col-sm-push-3');
        $regions['pre'] = 'col-sm-3 col-sm-pull-6';
        $regions['post'] = 'col-sm-3';
    } else if ($hassidepre && !$hassidepost) {
        $regions = array('content' => 'col-sm-9 col-sm-push-3 ');
        $regions['pre'] = 'col-sm-3 col-sm-pull-9';
        $regions['post'] = 'empty';
    } else if (!$hassidepre && $hassidepost) {
        $regions = array('content' => 'col-sm-9');
        $regions['pre'] = 'empty';
        $regions['post'] = 'col-sm-3';
    } else if (!$hassidepre && !$hassidepost) {
        $regions = array('content' => 'col-md-12');
        $regions['pre'] = 'empty';
        $regions['post'] = 'empty';
    }
    return $regions;
}

function theme_arup_process_css($css, $theme) {

    $imagename = 'loginbackground';
    $loginback = $theme->setting_file_url($imagename, $imagename);
    $css = theme_arup_set_loginbackground($css, $loginback);
    // Set custom CSS.
    if (!empty($theme->settings->customcss)) {
        $customcss = $theme->settings->customcss;
    } else {
        $customcss = null;
    }
    $css = theme_arup_set_customcss($css, $customcss);

    return $css;
}

function theme_arup_set_loginbackground($css, $logo) {
    $tag = '[[setting:loginbackground]]';
    $replacement = $logo;
    if (is_null($replacement)) {
        $replacement = '';
    }

    $css = str_replace($tag, $replacement, $css);
    return $css;
}

function theme_arup_pluginfile($course, $cm, $context, $filearea, $args, $forcedownload, array $options = array()) {
    if ($context->contextlevel == CONTEXT_SYSTEM) {
        $theme = theme_config::load('arup');
        if (!array_key_exists('cacheability', $options)) {
            $options['cacheability'] = 'public';
        }
        if (substr($filearea, 0, 4) === 'cate') {
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        } else if (substr($filearea, 0, 4) === 'logo') {
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        } else if (preg_match('/bannerimage\d+/', $filearea)) {
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        } else if ($filearea === 'loginbackground') {
            return $theme->setting_file_serve('loginbackground', $args, $forcedownload, $options);
        } else if ($filearea === 'dashboardimage') {
            return $theme->setting_file_serve('dashboardimage', $args, $forcedownload, $options);
        } else if (preg_match('/categorybackground\d+/', $filearea)) {
            return $theme->setting_file_serve($filearea, $args, $forcedownload, $options);
        } 

    } else {
        send_file_not_found();
    }
}


/**
 * Adds any custom CSS to the CSS before it is cached.
 *
 * @param string $css The original CSS.
 * @param string $customcss The custom CSS to add.
 * @return string The CSS which now contains our custom CSS.
 */
function theme_arup_set_customcss($css, $customcss) {
    $tag = '[[setting:customcss]]';
    $replacement = $customcss;
    if (is_null($replacement)) {
        $replacement = '';
    }

    $css = str_replace($tag, $replacement, $css);

    return $css;
}

function theme_arup_initialise_js(moodle_page $page) {
    user_preference_allow_ajax_update('theme_arup_sidebar', PARAM_TEXT);
    $page->requires->yui_module('moodle-theme_arup-arup', 'M.theme_arup.arup.init', array());
}
