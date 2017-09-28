<?php
// This file is part of the coursemanager plugin for Moodle
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
 * @package     local_coursemanager
 * @copyright   2017 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author     Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_coursemanager;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->libdir . '/csvlib.class.php');
require_once($CFG->dirroot .'/local/coursemanager/forms/step1.php');
require_once($CFG->dirroot .'/local/coursemanager/forms/step2.php');

use moodle_url;
use stdClass;
use cmform_step1_form;
use cmform_step2_form;

class csvimportforms {

    private $context;
    private $showassign;
    private $showcrud;
    private $baseurl;
    public $importid;
    public $cir;
    public $step1data;
    public $step2data;
    public $filter;


    /**
     * Constructor
     * @param int $importid, used for handling CSV files
     * @param int $showcrud, this is question editing mode.
     */
    public function __construct($importid = null, $showcrud = 0) {
        $this->context = \context_system::instance();
        $this->baseurl = '/local/coursemanager/cpd.php';
        $this->showcrud = $showcrud;
        if ($importid) {
            $this->importid = $importid;
        }
    }
    
    /**
     * Setup the CSV upload form, step 1
     *
     * @return moodle_form | void
     */
    public function admin_form1() {

        $returnurl = new moodle_url($this->baseurl);

        $mform1 = new cmform_step1_form(null);

        // The importid is used to link to the uploaded CSV.
        if (empty($this->importid)) {
            if ($form1data = $mform1->is_cancelled()) {
                if (!empty($cir)) {
                    $this->cir->cleanup(true);
                }
                redirect($returnurl);
            } else if ($this->step1data = $mform1->get_data()) {
                $this->importid = \csv_import_reader::get_new_iid('uploaddata');
                $this->cir = new \csv_import_reader($this->importid, 'uploaddata');

                // Check for CSV errors after submit.
                $content = $mform1->get_file_content('csv');
                $readcount = $this->cir->load_csv_content($content, $this->step1data->encoding, $this->step1data->delimiter_name);
                unset($content);
                if ($readcount === false) {
                    print_error('csvemptyfile', 'error', $returnurl, $this->cir->get_error());
                } else if ($readcount == 0) {
                    print_error('csvemptyfile', 'error', $returnurl, $this->cir->get_error());
                }
                $this->importstep = 1;
            } else {
                return $mform1;
            }
        } else {
            $this->cir = new \csv_import_reader($this->importid, 'uploaddata');
            $this->importstep = 1;
        }
    }

    /**
     * Confirm the CSV upload, step 2
     *
     * @return moodle_form | void
     */
    public function admin_form2() {
        $returnurl = new moodle_url($this->baseurl);

        $mform2 = new cmform_step2_form(null, array('importid' => $this->importid));

        if ($mform2->is_cancelled()) {
            $this->cir->cleanup(true);
            redirect($returnurl);

        } else if ($this->step2data = $mform2->get_data()) {
            $this->importstep = 2;
            return false;
        } else {
            return $mform2;
        }
    }


    /**
     * Call the csv processor preview
     *
     * @return string
     */
    public function csv_preview($rows) {
        if ($this->importstep == 1) {
            $processor = new \local_coursemanager\csvimport($this->cir);
            $processor->preview($rows);
        }
    }

    /**
     * Process the csv data
     *
     * @return string info on result
     */
    public function csv_process() {
        if ($this->importstep == 2) {
            $processor = new \local_coursemanager\csvimport($this->cir);
            $processor->execute();
        }
    }
}