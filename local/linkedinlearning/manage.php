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
 * @package    local_linkedinlearning
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$strheading = get_string('managecourses', 'local_linkedinlearning');

admin_externalpage_setup('local_linkedinlearning/managecourses');

$PAGE->navbar->add($strheading);

$showallpagesize = 5000;
$defaultpagesize = 50;

$downloadformat = optional_param('download', false, PARAM_ALPHA);
$treset         = optional_param('treset', false, PARAM_BOOL);
$showall        = !$treset && ($downloadformat || optional_param('showall', false, PARAM_BOOL));

$form     = new \local_linkedinlearning\managecourses_filterform();
$formdata = $form->get_data();
$tagtypes = \local_linkedinlearning\classification::get_types();

if ($formdata) {
    $urlparams = [
            'showall'    => $showall,
    ];
    foreach ($tagtypes as $key => $name) {

        if (!isset($formdata->$key)) {
            continue;
        }

        $urlparams[$key] = $formdata->$key;
    }
    if (isset($formdata->title)) {
        $urlparams['title'] = $formdata->title;
    }
    $url = $PAGE->url;
    $url = \local_linkedinlearning\lib::appendurlparamswitharray($url, $urlparams);

    redirect($url);
}

$data             = new \stdClass();
$data->tsort      = optional_param('tsort', 'title', PARAM_ALPHA);

$atleastonefilter = false;
foreach ($tagtypes as $key => $name) {
    $data->$key = optional_param_array($key, [], PARAM_INT);
    if (!empty($data->$key)) {
        $atleastonefilter = true;
    }
}

$title = optional_param('title', null, PARAM_TEXT);
if (!empty($title)) {
    $data->title = $title;
    $atleastonefilter = true;
}
$form->set_data($data);

$baseurl = $PAGE->url;
if ($showall) {
    $baseurl->param('showall', true);
}
$baseurl = \local_linkedinlearning\lib::appendurlparamswitharray($baseurl, (array)$data);

$table = new \local_linkedinlearning\managecourses_table($data, $data->tsort);
$baseurl->remove_params($table->request); // Remove table params to avoid them being changed every request!
$table->define_baseurl($baseurl);

if (!empty($downloadformat)) {
    $filename = get_string('pluginname', 'local_linkedinlearning');
    $table->is_downloading($downloadformat, $filename);
} else {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('managecourses', 'local_linkedinlearning'));

    echo $form->render();
}

if ($showall) {
    $pagesize = $showallpagesize;
} else {
    $pagesize = $defaultpagesize;
}
$table->configurecolumns();

if ($atleastonefilter) {
    $table->out($pagesize, true);
    if (!$showall) {
        echo \html_writer::div(\html_writer::link(new \moodle_url($table->baseurl, array('showall' => true)), 'Show all'), 'paging');
    }
} else {
    echo get_string('selectfilters', 'local_linkedinlearning');
}


echo $OUTPUT->footer();