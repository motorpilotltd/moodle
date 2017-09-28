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
 * The local_taps course listing page.
 *
 * @package    local_taps
 * @copyright  2016 Motorpilot Ltd
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once("../../config.php");

require_once($CFG->libdir.'/adminlib.php');

admin_externalpage_setup('local_taps_listcourses');

$issuesparam = optional_param('issues', 0, PARAM_INT);
$regionparam = optional_param('region', -1, PARAM_INT);

$regionsinstalled = get_config('local_regions', 'version');
$coursemetadatainstalled = get_config('local_coursemetadata', 'version');
$arupadvertinstalled = get_config('arupadvertdatatype_taps', 'version');
$tapsenrolinstalled = get_config('mod_tapsenrol', 'version');
$tapscompletioninstalled = get_config('mod_tapscompletion', 'version');

$output = '';

if ($regionsinstalled && $coursemetadatainstalled && $arupadvertinstalled && $tapsenrolinstalled && $tapscompletioninstalled) {

    $PAGE->requires->js_call_amd('local_taps/enhance', 'initialise');

    $sql = <<<EOS
SELECT
    c.id,
    c.fullname,
    c.shortname,
    at.id as atid,
    te.id as teid,
    teiw.id as teiwid,
    teiw.name as teiwname,
    tc.id as tcid,
    ltc.courseid as attapscourseid,
    ltc.coursecode as attapscoursecode,
    ltc.coursename as attapscoursename,
    ltc2.courseid as tetapscourseid,
    ltc2.coursecode as tetapscoursecode,
    ltc2.coursename as tetapscoursename,
    ltc3.courseid as tctapscourseid,
    ltc3.coursecode as tctapscoursecode,
    ltc3.coursename as tctapscoursename
FROM
    {course} c
LEFT JOIN
    {arupadvert} a
    ON c.id = a.course
LEFT JOIN
    {arupadvertdatatype_taps} at
    ON a.id = at.arupadvertid
LEFT JOIN
    {tapsenrol} te
    ON c.id = te.course
LEFT JOIN
    {tapsenrol_iw} teiw
    ON teiw.id = te.internalworkflowid
LEFT JOIN
    {tapscompletion} tc
    ON c.id = tc.course
LEFT JOIN
    {local_taps_course} ltc
    ON at.tapscourseid = ltc.courseid
LEFT JOIN
    {local_taps_course} ltc2
    ON te.tapscourse = ltc2.courseid
LEFT JOIN
    {local_taps_course} ltc3
    ON tc.tapscourse = ltc3.courseid
WHERE
    at.id IS NOT NULL
    OR tc.id IS NOT NULL
    OR te.id IS NOT NULL
ORDER BY
    c.id ASC
EOS;

    $records = $DB->get_records_sql($sql);

    $regions = $DB->get_records_menu('local_regions_reg', array('userselectable' => 1), '', 'id, name');
    $regionsshort = array();
    foreach ($regions as $rid => $rname) {
        $wordcount = str_word_count($rname, 2);
        if (count($wordcount) > 1) {
            $regionsshort[$rid] = '';
            foreach ($wordcount as $windex => $word) {
                $regionsshort[$rid] .= strtoupper(substr($rname, $windex, 1));
            }
        } else {
            $regionsshort[$rid] = strtoupper(substr($rname, 0, 2));
        }
    }

    if (!$records) {
        $output .= html_writer::tag('p', get_string('norecords', 'local_taps'));
    } else {
        $table = new html_table();
        $table->classes = array('logtable', 'generalbox');
        $table->align = array('left', 'left', 'left', 'left', 'center', 'center', 'left', 'center', 'center', 'left', 'left');
        $table->head = array(
            get_string('id', 'local_taps'),
            get_string('fullname'),
            get_string('shortname'),
            get_string('regions:catalogue', 'local_regions'),
            get_string('arupadvert', 'local_taps'),
            get_string('tapsenrol', 'local_taps'),
            get_string('regions:enrolment', 'tapsenrol'),
            get_string('tapsenrol:workflow', 'local_taps'),
            get_string('tapscompletion', 'local_taps'),
            get_string('tapscoursedetails', 'local_taps'),
            get_string('issues', 'local_taps'),
        );
        $table->data = array();

        foreach ($records as $record) {
            $catalogueregions = $DB->get_records('local_regions_reg_cou', array('courseid' => $record->id));
            $enrolmentregions = is_null($record->teid) ? null : $DB->get_records('tapsenrol_region', array('tapsenrolid' => $record->teid));

            $row = array();
            $row[] = local_taps_wrap($record->id, $record->id);
            $row[] = local_taps_wrap($record->id, $record->fullname);
            $row[] = local_taps_wrap($record->id, $record->shortname);

            $cellcatalogueregions = array();
            if (empty($catalogueregions)) {
                $cellcatalogueregions[0] = get_string('global', 'local_regions');
            } else {
                foreach ($catalogueregions as $catalogueregion) {
                    if (isset($regionsshort[$catalogueregion->regionid])) {
                        $cellcatalogueregions[$catalogueregion->regionid] = $regionsshort[$catalogueregion->regionid];
                    }
                }
            }
            $row[] = implode(', ', $cellcatalogueregions);

            $row[] = local_taps_yesno(!is_null($record->atid));
            $row[] = local_taps_yesno(!is_null($record->teid));

            if (is_null($record->teid)) {
                $row[] = local_taps_yesno(false);
            } else {
                $cellenrolmentregions = array();
                if (empty($enrolmentregions)) {
                    $cellenrolmentregions[0] = get_string('global', 'local_regions');
                } else {
                    foreach ($enrolmentregions as $enrolmentregion) {
                        if (isset($regionsshort[$enrolmentregion->regionid])) {
                            $cellenrolmentregions[$enrolmentregion->regionid] = $regionsshort[$enrolmentregion->regionid];
                        }
                    }
                }
                $row[] = implode(', ', $cellenrolmentregions);
            }

            $row[] = local_taps_yesno(!is_null($record->teiwid), $record->teiwname);
            $row[] = local_taps_yesno(!is_null($record->tcid));

            $issues = array();
            $detailscell = new html_table_cell();

            $tapscoursedetails = array(
                'at' => '',
                'te' => '',
                'tc' => '',
            );

            $tapscourseids = array();
            if (!is_null($record->atid)) {
                $tapscourseids['at'] = $record->attapscourseid;
            } else {
                $atstring = get_string('at', 'local_taps') . ' ' . get_string('missing', 'local_taps');
                $issues[] = html_writer::tag('strong', $atstring);
            }
            if (!is_null($record->teid)) {
                $tapscourseids['te'] = $record->tetapscourseid;
            }
            if (!is_null($record->tcid)) {
                $tapscourseids['tc'] = $record->tctapscourseid;
            }

            if (empty($tapscourseids)) {
                $row[] = get_string('notlinked', 'local_taps');
            } else {
                $allsame = count(array_unique($tapscourseids)) == 1;
                foreach ($tapscourseids as $index => $id) {
                    $tapscourseid = "{$index}tapscourseid";
                    $tapscoursecode = "{$index}tapscoursecode";
                    $tapscoursename = "{$index}tapscoursename";
                    if (is_null($record->{$tapscourseid})) {
                        $tapscoursedetails[$index] .= get_string('notfound', 'local_taps');
                    } else {
                        $tapscoursedetails[$index] .= $record->{$tapscourseid};
                        $tapscoursedetails[$index] .= ' | ';
                        $tapscoursedetails[$index] .= is_null($record->{$tapscoursecode}) ? '-' : $record->{$tapscoursecode};
                        $tapscoursedetails[$index] .= ' | ';
                        $tapscoursedetails[$index] .= $record->{$tapscoursename};
                    }
                    if ($allsame) {
                        break;
                    } else {
                        $tapscoursedetails[$index] .= ' ['.get_string($index, 'local_taps').']';
                    }
                }
                $detailscell->text = implode(html_writer::empty_tag('br'), array_filter($tapscoursedetails));
                if (!$allsame) {
                    $notallsamestring = html_writer::tag('strong', get_string('linkedcoursemismatch', 'local_taps')) .
                        html_writer::empty_tag('br');
                    $issues[] = $notallsamestring;
                    $detailscell->style = 'color: #FFFFFF; background-color: #FF0000;';
                }
                $row[] = $detailscell;
                $issuescell = new html_table_cell(implode(html_writer::empty_tag('br'), $issues));
                if (!empty($issues)) {
                    $issuescell->style = 'color: #FFFFFF; background-color: #FF0000;';
                }
                $row[] = $issuescell;
            }
            $showissues = !$issuesparam || ($issuesparam && !empty($issues));
            $showregion = $regionparam == -1 || array_key_exists($regionparam, $cellcatalogueregions);
            if ($showissues && $showregion) {
                $table->data[] = $row;
            }
        }

        if (empty($table->data)) {
            $cell = new html_table_cell(get_string('norecords:filter', 'local_taps'));
            $cell->colspan = count($table->head);
            $table->data[] = new html_table_row(array($cell));
        }

        // Filters.
        $output .= html_writer::start_tag('p');
        $url = clone($PAGE->url);
        if ($issuesparam) {
            $url->param('issues', true);
        }
        if ($regionparam > -1) {
            $url->param('region', $regionparam);
        }
        $issuesurl = clone($url);
        if ($issuesparam) {
            $issuesurl->remove_params('issues');
            $output .= html_writer::link($issuesurl, get_string('showissues:reset', 'local_taps'));
        } else {
            $issuesurl->param('issues', true);
            $output .= html_writer::link($issuesurl, get_string('showissues', 'local_taps'));
        }
        $output .= html_writer::empty_tag('br');
        $output .= get_string('chooseregion', 'local_taps');
        $regionurl = clone($url);
        $regionurl->param('region', -1);
        $regionlinks = array(html_writer::link($regionurl, get_string('all', 'local_taps')));
        $regionurl->param('region', 0);
        $regionlinks[] = html_writer::link($regionurl, get_string('global', 'local_regions'));
        foreach ($regions as $rid => $rname) {
            $regionurl->param('region', $rid);
            $regionlinks[] = html_writer::link($regionurl, $rname);
        }
        $output .= implode(' | ', $regionlinks);
        $output .= html_writer::end_tag('p');

        $output .= html_writer::table($table);
    }
} else {
    $output .= html_writer::tag('p', get_string('modsnotinstalled', 'local_taps'));
}

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('listcourses', 'local_taps'));

echo $output;

echo $OUTPUT->footer();

/**
 * Wraps text with link to course with specified ID.
 *
 * @param int $id
 * @param string $text
 * @return string
 */
function local_taps_wrap($id, $text) {
    $url = new moodle_url('/course/view.php', array('id' => $id));
    return html_writer::link($url, $text);
}

/**
 * Outputs tick/cross for yes/no.
 *
 * @param bool $yes
 * @param mixed $tooltip
 * @return string
 */
function local_taps_yesno($yes, $tooltip = null) {
    global $OUTPUT;
    if ($yes && !empty($tooltip)) {
        return html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/valid'), 'class' => 'taps-tooltip', 'title' => $tooltip));
    } else if ($yes) {
        return html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/valid')));
    } else {
        return html_writer::empty_tag('img', array('src' => $OUTPUT->pix_url('i/invalid')));
    }
}