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
 * @package    local_lynda
 * @copyright  2017 Andrew Hancox <andrewdchancox@googlemail.com> On Behalf of Arup
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');

$context = context_system::instance();
$strheading = get_string('managecourses', 'local_lynda');
if (has_capability('moodle/site:config', $context)) {
    admin_externalpage_setup('managecourses');
} else {
    require_capability('local/lynda:manage', $context);
    $PAGE->set_pagelayout('admin');
    $PAGE->set_context($context);
    $PAGE->set_url('/local/lynda/manage.php');
    $PAGE->set_title($strheading);
    $PAGE->set_heading($strheading);
}

$PAGE->navbar->add($strheading);

$showallpagesize = 5000;
$defaultpagesize = 50;

$downloadformat = optional_param('download', false, PARAM_ALPHA);
$treset         = optional_param('treset', false, PARAM_BOOL);
$showall        = !$treset && ($downloadformat || optional_param('showall', false, PARAM_BOOL));
$context        = \context_system::instance();

$form     = new \local_lynda\managecourses_filterform();
$formdata = $form->get_data();
$tagtypes = \local_lynda\lyndatagtype::fetch_full_taxonomy();

if ($formdata) {
    $urlparams = [
            'showall'    => $showall,
    ];
    foreach ($tagtypes as $tagtype) {
        $selectname = $tagtype->gettagtypeselectname();

        if (!isset($formdata->$selectname)) {
            continue;
        }

        $urlparams[$selectname] = $formdata->$selectname;
    }
    $url = $PAGE->url;
    $url = \local_lynda\lib::appendurlparamswitharray($url, $urlparams);

    redirect($url);
}

$data             = new \stdClass();
$data->tsort      = optional_param('tsort', 'title', PARAM_ALPHA);

$atleastonefilter = false;
foreach ($tagtypes as $tagtype) {
    $selectname = $tagtype->gettagtypeselectname();
    $data->$selectname = optional_param_array($selectname, [], PARAM_INT);
    if (!empty($data->$selectname)) {
        $atleastonefilter = true;
    }
}

$form->set_data($data);

$baseurl = $PAGE->url;
if ($showall) {
    $baseurl->param('showall', true);
}
$baseurl = \local_lynda\lib::appendurlparamswitharray($baseurl, (array)$data);

$table = new \local_lynda\managecourses_table($data, $data->tsort);
$baseurl->remove_params($table->request); // Remove table params to avoid them being changed every request!
$table->define_baseurl($baseurl);

if (!empty($downloadformat)) {
    $filename = get_string('pluginname', 'local_lynda');
    $table->is_downloading($downloadformat, $filename);
} else {
    echo $OUTPUT->header();
    echo $OUTPUT->heading(get_string('managecourses', 'local_lynda'));

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
    echo get_string('selectfilters', 'local_lynda');
}


echo $OUTPUT->footer();