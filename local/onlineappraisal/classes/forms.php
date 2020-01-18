<?php
// This file is part of the Arup online appraisal system
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
 * Version details
 *
 * @package     local_onlineappraisal
 * @copyright   2016 Motorpilot Ltd, Sonsbeekmedia
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_onlineappraisal;

use stdClass;
use moodle_url;

defined('MOODLE_INTERNAL') || die();

class forms {
    public $appraisal;
    private $filefields;
    public $form;

    /**
     * Constructor.
     *
     * The Constructor takes 4 arguments. The moduleid or the dbid of the current
     * appraisal instance, the user object, the name of the current page and the db id
     * of the form loaded on this page.
     *
     * param object $appraisal the full user object.
     * param string $page the name of the current page to show.
     * param int $forminstanceid the db id of the current form.
     */
    public function __construct(\local_onlineappraisal\appraisal $appraisal) {
        $this->appraisal = $appraisal;
        $this->load_custom_formfields();

        // Know file fields. These have to be added here for all know file fields in forms.
        // appraisalfile found in /forms/lastyear.php
        $this->filefields = array('lastyear' => 'appraisalfile');
    }

    /**
     * Load custom form fields.
     *
     * This function loads the added form fields to the HTML_QUICKFORM_ELEMENT_TYPES array
     * After adding thes form they can be access in each of the forms in /form
     */
    private function load_custom_formfields() {
        global $CFG, $GLOBALS;

        // Needs to be loaded before custom formfields are added.
        require_once($CFG->libdir . '/formslib.php');

        //Adding the textarearup. A textarea formfield that supports added help texts and lists of comments.
        $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES']['textarearup'] = array();
        $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES']['textarearup'][] = $CFG->dirroot . '/local/onlineappraisal/forms/textarea.php';
        $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES']['textarearup'][] = 'MoodleQuickForm_textarearup';

        $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES']['dateselect'] = array();
        $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES']['dateselect'][] = $CFG->dirroot . '/local/onlineappraisal/forms/dateselect.php';
        $GLOBALS['HTML_QUICKFORM_ELEMENT_TYPES']['dateselect'][] = 'MoodleQuickForm_dateselect';
    }


    /**
     * Get the form for the current page.
     *
     * @return object $form The form for the current page.
     */
    public function get_form() {
        global $CFG;

        require_once($CFG->dirroot . '/local/onlineappraisal/forms/'.$this->appraisal->page.'.php');
        $formclass = 'apform_' . $this->appraisal->page;
        if (method_exists($formclass, 'stored_form')) {
            $sform = call_user_func([$formclass, 'stored_form'], $this);
        } else {
            $sform = $this->stored_form();
        }
        $this->form_permissions($sform);
        $this->form = new $formclass(null, $sform);
        $this->process_data($sform);
        $this->form->set_data($sform);
    }

    /**
     * Get the stored form instance with data.
     *
     * return object $sform a new empty for or the full form data for an existing record.
     */
    private function stored_form() {
        global $DB;
        // Todo. Add in form instance logic in case there are repeated instances of the same form.
        $params = array(
            'appraisalid' => $this->appraisal->appraisal->id,
            'user_id' => $this->appraisal->appraisal->appraisee->id,
            'form_name' => $this->appraisal->page,
        );
        if ($stored = $DB->get_record('local_appraisal_forms', $params)) {
            $sform = new stdClass();
            $sform->formid = $stored->id;
            $this->get_formdata($sform);
        } else {
            $sform = new stdClass();
            $sform->formid = '-1';
        }
        $this->get_stored_files($sform, $this->appraisal->page);
        $sform->userid = $this->appraisal->user->id;
        $sform->appraisalid = $this->appraisal->appraisal->id;
        $sform->appraisal = $this->appraisal->appraisal;
        $sform->viewingas = $this->appraisal->appraisal->viewingas;
        $sform->nexturl = $this->appraisal->get_nextpage();
        return $sform;
    }

    /**
     * Load the permissions for this form. Passes additional data by reference
     * @param object $sform. Store Form Data
     */
    private function form_permissions(&$sform) {
        $sform->appraiseeedit = $sform->appraiseredit = $sform->signoffedit = $sform->groupleaderedit = APPRAISAL_FIELD_LOCKED;
        // Current appraisal page
        $page = $this->appraisal->page;
        // Current appraisal page object
        $pageobj = $this->appraisal->pages[$page];
        if ($pageobj->add) {
            $usertype = $this->appraisal->appraisal->viewingas;
            $useredit = $usertype . 'edit';
            $sform->$useredit = APPRAISAL_FIELD_EDIT;
        }
    }

    /**
     * Get the form data.
     *
     * Loop through the stored data and add it to the pass $dform by reference
     *
     * @param object $dbform stored form instance.
     */
    private function get_formdata(&$dform) {
        global $DB;
        $formrecords = $DB->get_records('local_appraisal_data', array('form_id' => $dform->formid));

        foreach ($formrecords as $record) {
            $fieldname = $record->name;
            $type = $record->type;
            if ($type == 'array') {
                $data = unserialize($record->data);
            } else {
                $data = $record->data;
            }
            $dform->$fieldname = $data;
        }
    }

    /**
     * Print our form.
     */
    public function display() {
        $this->form->display();
    }

    /**
     * Check if the form is submitted and pass it on to the store_data function.
     * Set any alerts and redirect as necessary.
     */
    private function process_data($sform) {
        $strings = $this->get_process_strings();

        $permission = $this->appraisal->appraisal->viewingas . 'edit';
        if (method_exists($this->form, 'has_permission')) {
            $haspermission = $this->form->has_permission();
        } else {
            $haspermission = (!empty($sform->{$permission}) && $sform->{$permission} === APPRAISAL_FIELD_EDIT);
        }

        if ($this->form->is_cancelled()) {
            appraisal::set_alert($strings->cancelled, 'warning');

            redirect($this->get_redirect_url(true));
        }

        $stored = false;
        if ($this->form->is_submitted() && $haspermission && ($data = $this->form->get_data())) {
            // Grab this now as it's unset prior to storing data in DB.
            $continue = isset($data->submitcontinue);
            // Allows forms to process data in a different way.
            if (method_exists($this->form, 'store_data')) {
                $stored = $this->form->store_data($this, $data);
            } else {
                $stored = $this->store_data($data);
            }

            if ($stored) {
                // Alert may already be set.
                if (empty($this->appraisal->called->done) ) {
                    appraisal::set_alert($strings->saved, 'success');
                }

                if ($continue) {
                    $this->appraisal->redirect_to_nextpage();
                }

                redirect($this->get_redirect_url());
            }
        }

        if ($this->form->is_submitted()) {
            // Form was submitted but there was an error, probably in validation or may not have permission).
            // Alert may already be set.
            if (empty($this->appraisal->called->done) ) {
                appraisal::set_alert($strings->error, 'danger');
            }
        }
    }

    /**
     * Get strings for alerts (either custom for form, if they exist, or defaults).
     *
     * @return stdClass
     */
    private function get_process_strings() {
        $strings = new stdClass();

        $strman = get_string_manager();
        $component = 'local_onlineappraisal';

        $base = "form:{$this->appraisal->page}:alert:";

        foreach (array('cancelled', 'saved', 'error') as $id) {
            if ($strman->string_exists($base.$id, $component)) {
                $strings->{$id}  = get_string($base.$id, $component);
            } else {
                $strings->{$id}  = get_string("form:alert:{$id}", $component);
            }
        }

        return $strings;
    }

    /**
     * Get the redirect URL (either custom for form, if method exists, or default).
     *
     * @param bool $cancel Is cancel url required.
     * @return moodle_url
     */
    private function get_redirect_url($cancel = false) {
        $method = ($cancel ? 'cancel_' : '') . 'redirect_url';
        if (method_exists($this->form, $method)) {
            // Set by form if required.
            return $this->form->{$method}();
        } else {
            // Default is to redirect back to self.
            return new moodle_url(
                '/local/onlineappraisal/view.php',
                array(
                    'page' => $this->appraisal->page,
                    'appraisalid' => $this->appraisal->appraisal->id,
                    'view' => $this->appraisal->appraisal->viewingas
                ));
        }
    }

    /**
     * Store the date passed from our current form
     *
     * @param object $data formdata
     */
    public function store_data($data) {
        global $DB;

        $params = array(
            'appraisalid' => $this->appraisal->appraisal->id,
            'user_id' => $this->appraisal->appraisal->appraisee->id,
            'form_name' => $this->appraisal->page,
        );
        if ($form = $DB->get_record('local_appraisal_forms', $params)) {
            $form->timemodified = time();
            $DB->update_record('local_appraisal_forms', $form);
        } else {
            $form = new stdClass();
            $form->form_name = $this->appraisal->page;
            $form->appraisalid = $this->appraisal->appraisal->id;
            $form->user_id = $this->appraisal->appraisal->appraisee->id;
            $form->timecreated = time();
            $form->timemodified = 0;
            $form->id = $DB->insert_record('local_appraisal_forms', $form);
        }

        // Default data being send from forms that can be unset
        // to prevent them using unneeded space in the DB table.
        unset(
            $data->formid,
            $data->eformname,
            $data->userid,
            $data->id,
            $data->save,
            $data->page,
            $data->submitbutton,
            $data->submitcontinue,
            $data->cancelbutton, // Just in case.
            $data->appraiseredit,
            $data->appraiseeedit,
            $data->signoffedit,
            $data->groupleaderedit,
            $data->view,
            $data->appraisalid
        );

        foreach ($data as $fieldname => $fielddata) {

            foreach ($this->filefields as $filefield) {
                if ($fieldname == $filefield) {
                    $this->store_form_files($fielddata);
                }
            }

            $datarecord = new stdClass();
            $datarecord->form_id = $form->id;
            $datarecord->name = $fieldname;
            if (is_array($fielddata)) {
                $datarecord->type = 'array';
                $datarecord->data = serialize($fielddata);
            } else {
                $datarecord->type = 'normal';
                $datarecord->data = $fielddata;
            }

            if ($storeddata = $DB->get_record('local_appraisal_data', array('form_id' => $form->id,
                    'name' => $fieldname))) {
                $datarecord->id = $storeddata->id;
                $DB->update_record('local_appraisal_data', $datarecord);
            } else {
                $datarecord->id = $DB->insert_record('local_appraisal_data', $datarecord);
            }
        }

        return true;
    }

    /**
     * Store files submitted from a form
     */
    private function store_form_files($fielddata) {
        // Defaults for the data storage
        $definitionoptions = array('trusttext' => true, 'subdirs' => 0, 'maxbytes' => 0, 'maxfiles' => 99);
        $attachmentoptions = array('subdirs' => false, 'maxfiles' => 1, 'maxbytes'=> 0);
        $context = \context_system::instance();

        // Store the file
        file_save_draft_area_files($fielddata, $context->id,
        'local_onlineappraisal', 'appraisalfile', $this->appraisal->appraisal->id, $attachmentoptions);
    }
    /**
     * Get the stored files for this form
     */
    private function get_stored_files(&$sform,  $page) {

        $attachmentoptions = array('subdirs' => false, 'maxfiles' => 1, 'maxbytes'=> 0);
        $context = \context_system::instance();

        foreach ($this->filefields as $form => $field) {
            if ($form == $page) {
                $draftitemid = file_get_submitted_draft_itemid($field);

                file_prepare_draft_area($draftitemid, $context->id, 'local_onlineappraisal', $field, $this->appraisal->appraisal->id, $attachmentoptions);

                $sform->$field = $draftitemid;
            }
        }
    }

    /**
     * Get all stored forms for this user in this appraisal
     * @param int $appraisalid
     * @param int $userid
     *
     * @return array $userforms (empty array if no forms stored);
     */
    public static function get_user_forms($appraisalid, $userid) {
        global $DB;
        $params = array(
            'appraisalid' => $appraisalid,
            'user_id' => $userid);
        if ($forms = $DB->get_records('local_appraisal_forms', $params)) {
            $userforms = array();
            foreach ($forms as $form) {
                $userforms[] = $form->form_name;
            }
            return $userforms;
        } else {
            return array();
        }
    }
}
