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
 * Theme arup config file.
 *
 * @package    theme_arup
 * @copyright  2016 Arup
 * @author     Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

$THEME->name = 'arup';
$THEME->parents = array('bootstrap');
$THEME->yuicssmodules = array();
$THEME->doctype = 'html5';
$THEME->parents_exclude_sheets = array('bootstrap' => array('moodle'));
$THEME->plugins_exclude_sheets = array(
    'mod' => array(
        'feedback'
    )
);
// $THEME->sheets = array('custom', 'arup');

// Enable auto-build
$THEME->sheets = array('custom', 'arup');
//$THEME->lessfile = 'arup';

$THEME->rendererfactory = 'theme_overridden_renderer_factory';
$THEME->csspostprocess = 'theme_arup_process_css';

//$standardareas = array('side-pre', 'side-post');
$standardareas = array('side-pre');
$preonly = array('side-pre');
$defaultregion = 'side-pre';

$THEME->layouts = array(
    // Most backwards compatible layout without the blocks - this is the layout used by default.
    'base' => array(
        'file' => 'default.php',
        'regions' => array(),
    ),
    // Standard layout with blocks, this is recommended for most pages with general information.
    'standard' => array(
        'file' => 'default.php',
        'regions' => $standardareas,
        'defaultregion' => $defaultregion,
    ),
    // Main course page.
    'course' => array(
        'file' => 'default.php',
        'regions' => $standardareas,
        'defaultregion' => $defaultregion,
        'options' => array('langmenu' => true),
    ),
    'coursecategory' => array(
        'file' => 'default.php',
        'regions' => array('side-pre'),
        'defaultregion' => $defaultregion,
        'options' => array('categoryimage' => true),
    ),
    // Part of course, typical for modules - default page layout if $cm specified in require_login().
    'incourse' => array(
        'file' => 'default.php',
        'regions' => $standardareas,
        'defaultregion' => $defaultregion,
    ),
    // The site home page.
    'frontpage' => array(
        'file' => 'default.php',
        'regions' => array('centre', 'top'),
        'defaultregion' => 'centre',
        'options' => array('categoryboxes' => true, 'notitle' => true),
    ),
    // Server administration scripts.
    'admin' => array(
        'file' => 'default.php',
        'regions' => $standardareas,
        'defaultregion' => $defaultregion,
        'options' => array('fluid' => true),
    ),
    // My dashboard page.
    'mydashboard' => array(
        'file' => 'default.php',
        'regions' => array('side-pre'),
        'defaultregion' => $defaultregion,
        'options' => array('langmenu' => true),
    ),
    // My public page.
    'mypublic' => array(
        'file' => 'default.php',
        'regions' => $standardareas,
        'defaultregion' => $defaultregion,
    ),
    'login' => array(
        'file' => 'default.php',
        'regions' => array(),
        'options' => array('notitle' => true),
    ),

    // Pages that appear in pop-up windows - no navigation, no blocks, no header.
    'popup' => array(
        'file' => 'popup.php',
        'regions' => array(),
        'options' => array('nofooter' => true, 'nonavbar' => true),
    ),
    // No blocks and minimal footer - used for legacy frame layouts only!
    'frametop' => array(
        'file' => 'default.php',
        'regions' => array(),
        'options' => array('nofooter' => true, 'nocoursefooter' => true),
    ),
    // Embeded pages, like iframe/object embeded in moodleform - it needs as much space as possible.
    'embedded' => array(
        'file' => 'embedded.php',
        'regions' => array()
    ),
    // Used during upgrade and install, and for the 'This site is undergoing maintenance' message.
    // This must not have any blocks, links, or API calls that would lead to database or cache interaction.
    // Please be extremely careful if you are modifying this layout.
    'maintenance' => array(
        'file' => 'maintenance.php',
        'regions' => array(),
    ),
    // Should display the content and basic headers only.
    'print' => array(
        'file' => 'default.php',
        'regions' => array(),
        'options' => array('nofooter' => true, 'nonavbar' => false),
    ),
    // The pagelayout used when a redirection is occuring.
    'redirect' => array(
        'file' => 'default.php',
        'regions' => array(),
    ),
    // The pagelayout used for reports.
    'report' => array(
        'file' => 'default.php',
        'regions' => array('side-pre'),
        'defaultregion' => $defaultregion,
    ),
    // The pagelayout used for safebrowser and securewindow.
    'secure' => array(
        'file' => 'default.php',
        'regions' => array('side-pre'),
        'defaultregion' => $defaultregion
    ),
);
