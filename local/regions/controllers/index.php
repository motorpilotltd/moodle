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
 *
 * @package local_regions
 */

defined('MOODLE_INTERNAL') || die();

global $CFG;
require_once($CFG->dirroot.'/local/regions/lib.php');

class local_regions_controller_index extends local_regions_controller {
    protected $pagename = 'regionsmanage';

    public function index_action() {
        $this->view->pageheading = $this->get_string('regionsmanage');

        $regions = array();

        $countregions = $this->model('region')->count_regions();
        if ($countregions > 0) {
            $regions = $this->model('region')->fetch_all_regions();
        }

        $this->view->regionlist = $regions;
        $this->view->countregions = $countregions;
    }

    public function editregion_action() {
        $id = optional_param('id', '', PARAM_INT);
        if ($id) {
            $existingregion = $this->model('region')->fetch_region_byid($id);
        }

        $this->view->pageheading = $this->get_string('regionsedit');

        $this->view->form = $this->form('editregion', array(
            'action' => 'index.php?action=index/editregion'
        ));

        if ($this->view->form->is_cancelled()) {
            redirect($this->view->base_url('index.php?action=index/index'));
            exit;
        }

        if ($this->view->form->is_submitted()) {
            if (!confirm_sesskey()) {
                $this->view->error = 'Session error';
                return; // Need error reporting.
            } else if (!$this->view->form->is_validated()) {
                $this->view->error = 'Form not valid';
                return; // Need error reporting.
            }

            $formdata = $this->view->form->get_data();

            $region = $this->model('region')->fetch_region_byname($formdata->name);
            if ($region && (!isset($existingregion) || $region->id != $existingregion->id)) {
                $this->view->error = 'Region with same name already exists';
                return; // Need error reporting.
            }

            $region = $this->model('region')->fetch_region_bytapsname($formdata->tapsname);
            if ($region && (!isset($existingregion) || $region->id != $existingregion->id)) {
                $this->view->error = 'Region with same TAPS name already exists';
                return; // Need error reporting.
            }

            $id = $this->model('region')->save_region($formdata);
            $region = $this->model('region')->fetch_region_byid($id);
            if (!$region) {
                $this->view->error = 'Region save failed';
                return; // Need error reporting.
            }

            // All saved, show them a nice view (need info partial, not error!).
            // Also tidy up any invalid mappings.
            local_regions_tidy_course_mappings();
            local_regions_tidy_user_mappings();
            $this->view->error = 'Region saved';
            $this->request = 'index/index';
            $this->index_action();
            return;
        }

        if (isset($existingregion) && $existingregion) {
            $this->view->form->set_data($existingregion);
        }
    }

    public function bulkaddregions_action() {
        $this->view->pageheading = $this->get_string('regionsaddbulk');

        $this->view->form = $this->form('bulkaddregions', array(
            'action' => 'index.php?action=index/bulkaddregions'
        ));

        if ($this->view->form->is_cancelled()) {
            redirect($this->view->base_url('index.php?action=index/index'));
            exit;
        }

        if ($this->view->form->is_submitted()) {
            if (!confirm_sesskey()) {
                $this->view->error = 'Session error';
                return; // Need error reporting.
            } else if (!$this->view->form->is_validated()) {
                $this->view->error = 'Form not valid';
                return; // Need error reporting.
            }

            $formdata = $this->view->form->get_data();

            $regions = explode("\n", $formdata->details);

            $info = '';
            $count = 0;
            foreach ($regions as $region) {
                $count++;

                $regiondata = array_filter(array_map('trim', explode(',', $region)), 'strlen');

                if (count($regiondata) != 3) {
                    $info = $info ? $info . '<br />Incorrect number of fields in line ' . $count : $info . 'Incorrect number of fields in line ' . $count;
                    continue;
                }

                if ($this->model('region')->fetch_region_byname($regiondata[0])) {
                    $info = $info ? $info . '<br />Region ' . $regiondata[0] . ' already exists' : $info . 'Region ' . $regiondata[0] . ' already exists';
                } else if ($this->model('region')->fetch_region_bytapsname($regiondata[1])) {
                    $info = $info ? $info . '<br />TAPS region ' . $regiondata[1] . ' already exists' : $info . 'TAPS region ' . $regiondata[1] . ' already exists';
                } else {
                    $data = new stdClass();
                    $data->name = $regiondata[0];
                    $data->tapsname = $regiondata[1];
                    $data->userselectable = (bool) $regiondata[2];
                    $id = $this->model('region')->save_region($data);
                    if (!$this->model('region')->fetch_region_byid($id)) {
                        $info = $info ? $info . '<br /> Failed to add ' . $region : $info . 'Failed to add ' . $region;
                    } else {
                        $info = $info ? $info . '<br />' . $region . ' added' : $info . $region . ' added';
                    }
                }
            }

            // All (hopefully) saved, show them a nice view (need info partial, not error!).
            local_regions_tidy_course_mappings();
            local_regions_tidy_user_mappings();
            $this->view->error = $info;
            $this->request = 'index/index';
            $this->index_action();
            return;
        }
    }

    public function deleteregion_action() {
        $id = required_param('id', PARAM_INT);
        $this->view->currentregion = $this->model('region')->fetch_region_byid($id);

        if (!$this->view->currentregion) {
            $this->view->error = 'Region not found';
            $this->request = 'index/index';
            $this->index_action();
            return;
        }

        $confirm = optional_param('confirm', 0, PARAM_INT);

        if ($confirm) {
            $deleted = $this->model('region')->delete($id);
            if (!$deleted) {
                $this->view->error = 'Delete failed';
                $this->request = 'index/index';
                $this->index_action();
                return;
            } else {
                local_regions_tidy_course_mappings();
                local_regions_tidy_user_mappings();
                $this->view->error = 'Delete succeeded';
                $this->request = 'index/index';
                $this->index_action();
                return;
            }
        }

        $this->view->pageheading = $this->get_string('regionsdelete');
    }

    public function mapcourses_action() {
        if (isset($_POST['cancel'])) {
            redirect($this->view->base_url('index.php?action=index/index'));
            exit;
        }

        $id = required_param('id', PARAM_INT);
        $this->view->currentregion = $this->model('region')->fetch_region_byid($id);

        if (!$this->view->currentregion) {
            $this->view->error = 'Region not found';
            $this->request = 'index/index';
            $this->index_action();
            return;
        }

        $this->view->allcourses = $this->model('regioncourse')->fetch_all_courses_menu();
        if (!$this->view->allcourses) {
            $this->view->error = 'No courses found';
            $this->request = 'index/index';
            $this->index_action();
            return;
        }

        $this->view->subregions = $this->model('subregion')->fetch_all_subregions_menu(array('regionid' => $this->view->currentregion->id));

        $this->view->regioncourses = $this->model('regioncourse')->fetch_regioncourses_formapping($this->view->currentregion->id);
        if (!$this->view->regioncourses) {
            $this->view->regioncourses = array();
        }

        $this->view->subregioncourses = $this->model('subregioncourse')->fetch_subregioncourses_formapping($this->view->currentregion->id);
        if (!$this->view->subregioncourses) {
            $this->view->subregioncourses = array();
        }

        if (isset($_POST['submit'])) {
            if (!confirm_sesskey()) {
                $this->view->error = 'Session error';
                return; // Need error reporting.
            }
            $data = array(
                'regioncourse' => new stdClass(),
                'subregioncourse' => new stdClass()
            );
            $data['regioncourse']->regionid = $this->view->currentregion->id;
            foreach (array_keys($this->view->allcourses) as $courseid) {
                $data['regioncourse']->courseid = $courseid;
                $data['subregioncourse']->courseid = $courseid;
                if (isset($_POST['course'][$courseid])) {
                    if (!array_key_exists($courseid, $this->view->regioncourses)) {
                        $this->model('regioncourse')->save_regioncourse($data['regioncourse']);
                    }
                    if ($this->view->subregions) {
                        foreach (array_keys($this->view->subregions) as $subregionid) {
                            $data['subregioncourse']->subregionid = $subregionid;
                            $subregioncourse = $this->model('subregioncourse')->fetch_subregioncourse_bysubregionandcourse($subregionid, $courseid);
                            if (isset($_POST['subregion'][$courseid][$subregionid])) {
                                if (!$subregioncourse) {
                                    $this->model('subregioncourse')->save_subregioncourse($data['subregioncourse']);
                                }
                            } else if ($subregioncourse) {
                                $this->model('subregioncourse')->delete($subregioncourse->id);
                            }
                        }
                    }
                } else {
                    if (array_key_exists($courseid, $this->view->regioncourses)) {
                        $this->model('regioncourse')->delete_byregionandcourse($this->view->currentregion->id, $courseid);
                    }
                }
            }

            $this->view->error = 'All saved...';
            $this->request = 'index/index';
            $this->index_action();
        }

        $this->page->requires->js_call_amd('local_regions/mapcourses', 'initialise');
        $this->view->pageheading = $this->get_string('regionsmapcourses', core_text::strtolower(get_string('courses')));
    }
}
