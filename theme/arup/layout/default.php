<?php
// This file is part of The Arup Moodle theme
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

if ($PAGE->bodyid == 'page-mod-scorm-player') {
    include('scorm.php');
    return;
}

if ($PAGE->bodyid == 'page-report-completion-index') {
    // Include JS for completion marking confirmation
    $PAGE->requires->strings_for_js(
        array(
            'popconfirm:togglecompletion:title',
            'popconfirm:togglecompletion:content',
            'popconfirm:togglecompletion:yes',
            'popconfirm:togglecompletion:no'
        ),
        'theme_arup'
    );
    $PAGE->requires->js_call_amd('theme_arup/popconfirm_completion', 'init');
}

$hassidepre = $PAGE->blocks->region_has_content('side-pre', $OUTPUT);
$hassidepost = $PAGE->blocks->region_has_content('side-post', $OUTPUT);
$hasaffix = empty($PAGE->layout_options['noaffix']);


$knownregionpre = $PAGE->blocks->is_known_region('side-pre');
$knownregionpost = $PAGE->blocks->is_known_region('side-post');
$knownregioncentre = $PAGE->blocks->is_known_region('centre');
$knownregiontop = $PAGE->blocks->is_known_region('top');

$regions = arup_grid($hassidepre, $hassidepost);
$PAGE->set_popup_notification_allowed(false);
$PAGE->requires->jquery();
$PAGE->requires->jquery_plugin('arup', 'theme_arup');
$PAGE->requires->js_call_amd('theme_arup/bsoptions', 'init');
$PAGE->requires->js_call_amd('theme_arup/selecttimezone', 'init');
$sectionnum = !empty($PAGE->cm->sectionnum) ? $PAGE->cm->sectionnum : null;
$PAGE->requires->js_call_amd('theme_arup/backtocourse', 'init', array($sectionnum, get_config('core', 'moodlecourse_linktosection')));

$html = $PAGE->get_renderer('theme_arup', 'html');

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>" />
    <?php echo $OUTPUT->standard_head_html(); ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimal-ui">
</head>

<body <?php echo $OUTPUT->body_attributes(); ?>>

<?php echo $OUTPUT->standard_top_of_body_html() ?>

<?php echo $OUTPUT->local_sitemessaging(); ?>

<?php echo $html->notifications(); ?>

<div class="container-fluid">
    <nav id="header" role="navigation"  class="navbar navbar-fixed-top navbar-default">
        <div class="navbar-inner">
            <?php echo $OUTPUT->navbar_button(); ?>
            <a class="navbar-brand" href="<?php echo $CFG->wwwroot; ?>/?redirect=0">
                <?php echo $html->page_logo(); ?>
            </a>
            
            <div class="navbar-header pull-right">
                <?php echo $OUTPUT->user_menu(); ?>
            </div>
            <?php echo $html->searchbox('', 'navbar'); ?>
            <div id="moodle-navbar" class="navbar-collapse collapse">
                <?php echo $OUTPUT->custom_menu(); ?>
                <ul class="nav pull-right">
                    <li><?php echo $OUTPUT->page_heading_menu(); ?></li>
                </ul>
            </div>
        </div>
    </nav>
</div>
<?php echo $OUTPUT->usertime_modal() ?>
<div id="page" class="container-fluid">
<?php echo $OUTPUT->full_header(); ?>
    <div id="topblocks" class="row">
        <?php
        if ($knownregiontop) {
            echo $OUTPUT->blocks('top', 'col-md-12');
        }?>
    </div>
    <div id="page-content" class="row">
        <div id="region-main" class="<?php echo $regions['content']; ?>">
            <?php
            echo $OUTPUT->course_content_header();
            echo $html->pre_content();
            echo $OUTPUT->main_content();
            echo $html->post_content();
            echo $OUTPUT->course_content_footer();
            ?>
        </div>

        <?php
        if ($knownregionpre) {
            echo $OUTPUT->blocks('side-pre', $regions['pre']);
        }?>
        <?php
        if ($knownregionpost) {
            echo $OUTPUT->blocks('side-post', $regions['post']);
        }?>
    </div>
    <?php if ($knownregioncentre) { ?>
    <div class="row">
        <div class="col-md-12">
            <?php
                $class = $this->page->user_is_editing() ? 'fixedregion' : 'flexregion';
                echo $OUTPUT->blocks('centre', $class);
            ?>
        </div>
    </div>
    <?php } ?>
    
    <footer id="page-footer">
        
        <?php echo $html->page_footer(); ?>
    </footer>

    <?php echo $OUTPUT->standard_end_of_body_html() ?>
</div>
</body>
</html>
