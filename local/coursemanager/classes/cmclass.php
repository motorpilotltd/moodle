<?php
// This file is part of the Arup Course Management system
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
 *
 * @package     local_coursemanager
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursemanager;

defined('MOODLE_INTERNAL') || die();

use mod_tapsenrol\taps;
use stdClass;
use moodle_url;
use html_writer;

class cmclass {

    private $coursemanager;
    /**
     * Constructor.
     *
     * @param object $coursemanager the full coursemanager object
     */
    public function __construct(\local_coursemanager\coursemanager $coursemanager = null) {
        $this->coursemanager = $coursemanager;
    }

    /**
     * Hook
     *
     * This function is called from the main coursemanager controller when the page
     * is loaded. This function can be added to all the other page types as long as this
     * class is being declared in \local_coursemanager\coursemanager->add_page();
     */
    public function hook() {
        global $DB, $OUTPUT, $USER;

        // Class List
        $output = '';

        $duplicate = optional_param('duplicate', false, PARAM_BOOL);
        $classinfo = $this->get_current_classinfo($this->coursemanager->cmclass->id, $duplicate);
        if ($this->coursemanager->editing) {
            if ($this->coursemanager->cmclass->id !== 0) {
                if ($classinfo->hasattendedenrolments) {
                    $output = html_writer::tag('div', get_string('form:alert:attendedenrolments', 'local_coursemanager'), array('class' => 'alert alert-warning')) . $output;
                }
                // Class Types
                $params = array(
                    'courseid' => $this->coursemanager->course->id,
                    'start' => $this->coursemanager->start,
                    'cmclass' => $this->coursemanager->cmclass->id,
                    'edit' => 1,
                    'duplicate' => $duplicate);
                $url = new moodle_url('/local/coursemanager/index.php', $params);

                $classtypes = array(
                    "class_scheduled" => $this->str('class_scheduled'),
                    "class_self_paced" => $this->str('class_self_paced'));

                $selectstr = array('class' => get_string('form:class:selectclasstype', 'local_coursemanager'));
                $select = new \single_select($url, 'page', $classtypes, $classinfo->type, $selectstr);
                if ($classinfo->hasattendedenrolments) {
                    $select->disabled = true;
                }
                $output .= html_writer::start_tag('div', array('class' => 'cmselect'));
                $output .= html_writer::tag('div', get_string('form:class:classtype', 'local_coursemanager'), array('class' => 'cmselecttitle'));
                $output .= $OUTPUT->render($select);
                $output .= html_writer::end_tag('div');

                if ($classinfo->type == 'class_scheduled') {
                    $classsatus = array(
                        "class_scheduled_normal" => $this->str('class_scheduled_normal'),
                        "class_scheduled_planned" => $this->str('class_scheduled_planned'));
                    $selectstr = array('' => get_string('form:class:selectstatustype', 'local_coursemanager'));
                    $select = new \single_select($url, 'page', $classsatus, $classinfo->status, $selectstr);
                    if ($classinfo->hasattendedenrolments) {
                        $select->disabled = true;
                    }
                    $output .= html_writer::start_tag('div', array('class' => 'cmselect'));
                    $output .= html_writer::tag('div', get_string('form:class:classstatus', 'local_coursemanager'), array('class' => 'cmselecttitle'));
                    $output .= $OUTPUT->render($select);
                    $output .= html_writer::end_tag('div');
                }
            }
        }
        return $output;
    }

    public function get_current_classinfo($cmclass, $duplicate) {
        global $DB;

        $classinfo = new stdClass();
        
        $classinfo->type = '';
        $classinfo->status = 'none';
        $classinfo->hasattendedenrolments = false;
        if ($cmclass === 0) {
            return $classinfo;
        }

        if ($cmclass === -1) {
            if ($this->coursemanager->page == 'class') {
                return $classinfo;
            } else if ($this->coursemanager->page == 'class_scheduled') {
                $classinfo->type = 'class_scheduled';
                $classinfo->status = 'none';
                return $classinfo;
            } else if ($this->coursemanager->page == 'class_scheduled_planned') {
                $classinfo->type = 'class_scheduled';
                $classinfo->status = 'class_scheduled_planned';
                return $classinfo;
            } else if ($this->coursemanager->page == 'class_scheduled_normal') {
                $classinfo->type = 'class_scheduled';
                $classinfo->status = 'class_scheduled_normal';
                return $classinfo;
            } else if ($this->coursemanager->page == 'class_self_paced') {
                $classinfo->type = 'class_self_paced';
                $classinfo->status = '';
                return $classinfo;
            }
        }
        if ($cmclass >= 1) {
            $classrecord = $DB->get_record('local_taps_class', ['id' => $cmclass]);

            // Ignore attended enrolments if duplicating.
            if ($classrecord && !$duplicate) {
                $taps = new \local_taps\taps();
                list($insql, $params) = $DB->get_in_or_equal($taps->get_statuses('attended'), SQL_PARAMS_NAMED, 'status');
                $sql = "SELECT COUNT(id)
                      FROM {local_taps_enrolment}
                      WHERE
                        classid = :classid
                        AND (archived = 0 OR archived IS NULL)
                        AND {$DB->sql_compare_text('bookingstatus')} {$insql}";

                $params['classid'] = $classrecord->classid;
                $classinfo->hasattendedenrolments = (bool) $DB->count_records_sql($sql, $params);
            }

            if ($this->coursemanager->page == 'class') {
                $classinfo->type = 'class_scheduled';
                $classinfo->status = 'class_scheduled_normal';

                $params = array('id' => $cmclass);

                $classtypes = array('Scheduled', 'Self Paced');
                $classstatus = array('Planned', 'Normal');
                if ($classrecord = $DB->get_record('local_taps_class', $params)) {
                    if (in_array($classrecord->classtype, $classtypes) &&
                        in_array($classrecord->classstatus, $classstatus)) {
                        $classinfo->type = strtolower('class_' . $classrecord->classtype);
                        $classinfo->status = strtolower('class_' . $classrecord->classtype . '_' . $classrecord->classstatus);
                        if ($classrecord->classtype == 'Self Paced') {
                            $this->coursemanager->set_page('class_self_paced');
                        } else {
                            $this->coursemanager->set_page($classinfo->status);
                        }
                    } 
                }
            } else if ($this->coursemanager->page == 'class_scheduled') {
                $classinfo->type = 'class_scheduled';
                $classinfo->status = '';
            }  else if ($this->coursemanager->page == 'class_self_paced') {
                $classinfo->type = 'class_self_paced';
                $classinfo->status = '';
            } else if ($this->coursemanager->page == 'class_scheduled_normal') {
                $classinfo->type = 'class_scheduled';
                $classinfo->status = 'class_scheduled_normal';
                $this->coursemanager->set_page($classinfo->status);
            }   else if ($this->coursemanager->page == 'class_scheduled_planned') {
                $classinfo->type = 'class_scheduled';
                $classinfo->status = 'class_scheduled_planned';
                $this->coursemanager->set_page($classinfo->status);
            }
            return $classinfo;
        }

        return $classinfo;
        
    }

    public function str($string) {
        return get_string('form:class:' . $string, 'local_coursemanager');
    }
}