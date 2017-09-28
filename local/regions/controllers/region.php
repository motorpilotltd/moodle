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

class local_regions_controller_region extends local_regions_controller {

    protected $pagename = 'regionsmanage';
    protected $parentregion;
    protected $subregion;

    public function __construct($action, $request, local_regions $base) {
        parent::__construct($action, $request, $base);

        $this->subregion = $this->model('subregion')->fetch_subregion_byid(optional_param('id', '', PARAM_INT));
        if ($this->subregion) {
            $this->parentregion = $this->model('region')->fetch_region_byid($this->subregion->regionid);
        } else {
            $this->parentregion = $this->model('region')->fetch_region_byid(optional_param('regionid', '', PARAM_INT));
        }

        $this->view->parentregion = $this->parentregion;
        $this->view->subregion = $this->subregion;
    }

    public function index_action() {
        if (!$this->parentregion) {
            $this->view->error = 'Region not found';
            return;
        }

        $this->view->pageheading = $this->get_string('subregionsmanage');

        $subregions = array();

        $countsubregions = $this->model('subregion')->count_subregions(array('regionid' => $this->parentregion->id));
        if ($countsubregions > 0) {
            $subregions = $this->model('subregion')->fetch_all_subregions(array('regionid' => $this->parentregion->id));
        }

        $this->view->subregionlist = $subregions;
        $this->view->countsubregions = $countsubregions;
    }

    public function editsubregion_action() {
        if (!$this->parentregion) {
            $this->view->error = 'Region not found';
            $this->request = 'region/index';
            $this->index_action();
            return;
        }

        $this->view->pageheading = $this->get_string('subregionsedit');

        $this->view->form = $this->form('editsubregion', array(
            'action' => 'index.php?action=region/editsubregion'
        ));

        if ($this->view->form->is_cancelled()) {
            redirect($this->view->base_url('index.php?action=region/index&regionid='.$this->parentregion->id));
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

            $subregion = $this->model('subregion')->fetch_subregion_byname($formdata->name);
            if ($subregion && (!isset($this->subregion) || $this->subregion->id != $subregion->id)) {
                $this->view->error = 'Sub Region name exists';
                return; // Need error reporting.
            }

            $id = $this->model('subregion')->save_subregion($formdata);
            $subregion = $this->model('subregion')->fetch_subregion_byid($id);
            if (!$subregion) {
                $this->view->error = 'Sub Region save failed';
                return; // Need error reporting.
            }

            // All saved, show them a nice view (need info partial, not error!).
            $this->view->error = 'Sub Region saved';
            $this->request = 'region/index';
            $this->index_action();
            return;
        }

        $this->view->form->set_data($this->subregion);
    }

    public function bulkaddsubregions_action() {
        $this->view->pageheading = $this->get_string('subregionsaddbulk');

        $this->view->form = $this->form('bulkaddsubregions', array(
            'action' => 'index.php?action=region/bulkaddsubregions'
        ));

        if ($this->view->form->is_cancelled()) {
            redirect($this->view->base_url('index.php?action=region/index&regionid='.$this->parentregion->id));
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

            $subregions = explode("\n", $formdata->names);

            $info = '';
            foreach ($subregions as $subregion) {
                $subregion = trim($subregion);

                if (!$subregion) {
                    continue;
                }

                if ($this->model('subregion')->fetch_subregion_byname($subregion)) {
                    $info = $info ? $info . '<br />' . $subregion . ' already exists' : $info . $subregion . ' already exists';
                } else {
                    $data = new stdClass();
                    $data->name = $subregion;
                    $data->regionid = $formdata->regionid;
                    $id = $this->model('subregion')->save_subregion($data);
                    if (!$this->model('subregion')->fetch_subregion_byid($id)) {
                        $info = $info ? $info . '<br /> Failed to add ' . $subregion : $info . 'Failed to add ' . $subregion;
                    } else {
                        $info = $info ? $info . '<br />' . $subregion . ' added' : $info . $subregion . ' added';
                    }
                }
            }

            // All (hopefully) saved, show them a nice view (need info partial, not error!).
            $this->view->error = $info;
            $this->request = 'region/index';
            $this->index_action();
            return;
        }
    }

    public function deletesubregion_action() {
        if (!$this->subregion) {
            $this->view->error = 'Sub Region not found';
            $this->request = 'region/index';
            $this->index_action();
            return;
        }

        $confirm = optional_param('confirm', 0, PARAM_INT);

        if ($confirm) {
            $deleted = $this->model('subregion')->delete($this->subregion->id);
            if (!$deleted) {
                $this->view->error = 'Delete failed';
                $this->request = 'region/index';
                $this->index_action();
                return;
            } else {
                $this->view->error = 'Delete succeeded';
                $this->request = 'region/index';
                $this->index_action();
                return;
            }
        }

        $this->view->pageheading = $this->get_string('subregionsdelete');
    }
}
