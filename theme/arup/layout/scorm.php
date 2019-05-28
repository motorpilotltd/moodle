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

$appletouchicon = $OUTPUT->image_url('apple-touch-icon', 'theme');
$favicon32x32 = $OUTPUT->image_url('favicon-32x32', 'theme');
$favicon16x16 = $OUTPUT->image_url('favicon-16x16', 'theme');
$webmanifest = "$CFG->wwwroot/theme/arup/android.webmanifest.php";
$safaritab = $OUTPUT->image_url('safari-pinned-tab', 'theme');

echo $OUTPUT->doctype() ?>
<html <?php echo $OUTPUT->htmlattributes(); ?>>
<head>
    <title><?php echo $OUTPUT->page_title(); ?></title>
    <link rel="shortcut icon" href="<?php echo $OUTPUT->favicon(); ?>" />

    <!-- iPhone(first generation or 2G), iPhone 3G, iPhone 3GS -->
    <link rel="apple-touch-icon" sizes="57x57" href="<?php echo $appletouchicon->out(); ?>">
    <!-- iPad and iPad mini @1x -->
    <link rel="apple-touch-icon" sizes="76x76" href="<?php echo $appletouchicon->out(); ?>">
    <!-- iPhone 4, iPhone 4s, iPhone 5, iPhone 5c, iPhone 5s, iPhone 6, iPhone 6s, iPhone 7, iPhone 7s, iPhone8 -->
    <link rel="apple-touch-icon" sizes="120x120" href="<?php echo $appletouchicon->out(); ?>">
    <!-- iPad and iPad mini @2x -->
    <link rel="apple-touch-icon" sizes="152x152" href="<?php echo $appletouchicon->out(); ?>">
    <!-- iPad Pro -->
    <link rel="apple-touch-icon" sizes="167x167" href="<?php echo $appletouchicon->out(); ?>">
    <!-- iPhone X, iPhone 8 Plus, iPhone 7 Plus, iPhone 6s Plus, iPhone 6 Plus -->
    <link rel="apple-touch-icon" sizes="180x180" href="<?php echo $appletouchicon->out(); ?>">

    <link rel="icon" type="image/png" sizes="32x32" href="<?php echo $favicon32x32->out(); ?>">
    <link rel="icon" type="image/png" sizes="16x16" href="<?php echo $favicon16x16->out(); ?>">
    <link rel="manifest" href="<?php echo $webmanifest;?>">
    <link rel="mask-icon" href="<?php echo $safaritab->out(); ?>" color="#5bbad5">

    <?php echo $OUTPUT->standard_head_html() ?>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, minimal-ui">
</head>

<body <?php echo $OUTPUT->body_attributes(); ?>>

<?php echo $OUTPUT->standard_top_of_body_html() ?>

<?php echo $OUTPUT->local_sitemessaging(); ?>

<div class="container-fluid">
    <nav id="header" role="navigation"  class="navbar navbar-fixed-top navbar-default">
        <div class="navbutton" id="button-scorm-exit">
                <?php echo $PAGE->button; ?>
        </div>
        <div id="scorm-fullscreen-message"><?php print_string('scormfullscreenmessage', 'theme_arup'); ?></div>
        <script type="text/javascript">
            function getFirstChild(el){
                var firstChild = el.firstChild;
                while(firstChild != null && firstChild.nodeType != 1){
                    firstChild = firstChild.nextSibling;
                }
                return firstChild;
            }
            function updateScormButton() {
                var scormLink = getFirstChild(document.getElementById('button-scorm-exit'));
                scormLink.innerHTML = 'Your progress is being recorded...';
            }
            var scormButton = getFirstChild(document.getElementById('button-scorm-exit'));
            if (!scormButton.addEventListener) {
                scormButton.attachEvent('onclick', updateScormButton);
            } else {
                scormButton.addEventListener('click', updateScormButton, false);
            }
            scormButton.className = 'btn btn-warning';
        </script>
    </nav>
</div>

<div id="page" class="container-fluid">
    <div id="page-content" class="row">
        <div id="region-main">
            <?php
            echo $OUTPUT->main_content();
            ?>
        </div>
    </div>
    <?php echo $OUTPUT->standard_end_of_body_html() ?>
</div>
</body>
</html>