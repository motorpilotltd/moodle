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
 * Contains class mod_questionnaire\output\renderer
 *
 * @package    mod_questionnaire
 * @copyright  2016 Mike Churchward (mike.churchward@poetgroup.org)
 * @author     Mike Churchward
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace mod_dsa\output;

defined('MOODLE_INTERNAL') || die();

class renderer extends \plugin_renderer_base {
    public function render_cm_info_view() {
        global $DB, $USER, $OUTPUT;

        $assesments = $DB->get_records('dsa_assessment', ['userid' => $USER->id], 'started DESC', '*');

        if (empty($assesments)) {
            return '';
        }

        $table = new \html_table();
        $table->attributes['class'] = 'generaltable dsa_assessment modinfotable';
        $table->head = [
                '',
                get_string('started', 'mod_dsa'),
                get_string('officename', 'mod_dsa'),
                get_string('assessorname', 'mod_dsa'),
                get_string('status', 'mod_dsa'),
                get_string('completed', 'mod_dsa'),

        ];

        $table->data = [];

        $rownumber = 0;

        foreach ($assesments as $assesment) {
            if (!empty($assesment->assessoremailaddress)) {
                $subject = get_string('emailassessorsubject', 'mod_dsa', $assesment);
                $body = get_string('emailassessorbody', 'mod_dsa', $assesment);
                $assessorlink = \html_writer::link("mailto:$assesment->assessoremailaddress?subject=$subject&body=$body",
                        "$assesment->assessorfirstname $assesment->assessorlastname");
            } else {
                $assessorlink = "$assesment->assessorfirstname $assesment->assessorlastname";
            }

            if (!empty($assesment->started)) {
                $started = userdate($assesment->started, get_string('strftimedatefullshort', 'core_langconfig'));
            } else {
                $started = '';
            }
            if (!empty($assesment->completed)) {
                $completed = userdate($assesment->completed, get_string('strftimedatefullshort', 'core_langconfig'));
            } else {
                $completed = '';
            }

            if (!in_array($assesment->state, ['closed', 'abandoned'])) {
                $table->rowclasses[$rownumber] = 'open';
            }

            $state = "$assesment->state ($assesment->status)";

            if ($assesment->state == 'abandoned') {
                $icon = $OUTPUT->pix_icon('abandoned', $state, 'mod_dsa');
            } else if ($assesment->state == 'unassigned') {
                $icon = $OUTPUT->pix_icon('unassigned', $state, 'mod_dsa');
            } else if ($assesment->status == 'Not finished') {
                $icon = $OUTPUT->pix_icon('inprogress', $state, 'mod_dsa');
            } else if ($assesment->state == 'closed' && strpos($assesment->status, 'Non-compliant') !== false) {
                $icon = $OUTPUT->pix_icon('complete', $state, 'mod_dsa') . $OUTPUT->pix_icon('noncompliant', '', 'mod_dsa');
            } else if ($assesment->state == 'closed' && $assesment->status == 'Compliant') {
                $icon = $OUTPUT->pix_icon('complete', $state, 'mod_dsa');
            } else {
                $icon = $OUTPUT->pix_icon('onhold', $state, 'mod_dsa');
                debugging('Unknown DSA status/state');
            }

            $table->data[] = [
                    $icon,
                    $started,
                    $assesment->officename,
                    $assessorlink,
                    $assesment->status,
                    $completed,
            ];

            $rownumber++;
        }

        // Print the summary table.
        $output = \html_writer::table($table);

        return $output;
    }
}