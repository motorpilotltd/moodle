<?php
// This file is part of the appraisal plugin for Moodle
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
 * @package    mod_appraisal
 * @copyright  2015 Sonsbeekmedia
 * @author     Bas Brands
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

defined('MOODLE_INTERNAL') || die();

class apform_development extends moodleform {
    private $renderer;

    public function definition() {
        global $PAGE;

        $this->renderer = $PAGE->get_renderer('local_onlineappraisal');

        $data = $this->_customdata;
        $mform = $this->_form;

        $mform->updateAttributes(array('class' => $mform->getAttribute('class').' oa-save-session-check'));

        $mform->addElement('hidden', 'formid', $data->formid);
        $mform->setType('formid', PARAM_INT);

        $mform->addElement('hidden', 'page', 'development');
        $mform->setType('page', PARAM_TEXT);

        $mform->addElement('hidden', 'userid', $data->userid);
        $mform->setType('userid', PARAM_INT);

        $mform->addElement('hidden', 'appraiseeedit', $data->appraiseeedit);
        $mform->setType('appraiseeedit', PARAM_INT);

        $mform->addElement('hidden', 'appraiseredit', $data->appraiseredit);
        $mform->setType('appraiseredit', PARAM_INT);

        $mform->addElement('hidden', 'appraisalid', $data->appraisalid);
        $mform->setType('appraisalid', PARAM_INT);

        $mform->addElement('hidden', 'view', $data->appraisal->viewingas);
        $mform->setType('view', PARAM_TEXT);

        $appraiseelocked = '';
        if ($data->appraiseeedit == APPRAISAL_FIELD_LOCKED) {
            $appraiseelocked = ' locked="yes"';
        }

        $appraiserlocked = '';
        if ($data->appraiseredit == APPRAISAL_FIELD_LOCKED) {
            $appraiserlocked = ' locked="yes"';
        }

        $mform->addElement('html', '<hr class="tophr">');
        $appraiseename = fullname($data->appraisal->appraisee);
        $mform->addElement('html', html_writer::tag('div',
            get_string('tagline', 'local_onlineappraisal', strtoupper($appraiseename)),
            array('class' => 'oa-tagline')));
        $mform->addElement('html', html_writer::tag('h2', $this->str('title')));

        $mform->addElement('html', html_writer::tag('div', $this->str('intro'), array('class' => 'm-b-20')));

        // START : Leadership Attributes.
        $roleattributes = $this->get_leadership_attributes('role');
        $genericattributes = $this->get_leadership_attributes('generic', 'Other | ');

        $answers = [];
        for ($i = 1; $i <= 2; $i++) {
            $answers[$this->str("leadership:answer:{$i}")] = $this->str("leadership:answer:{$i}");
        }
        $leadership = $mform->addElement('select', 'leadership', $this->str('leadership'), $answers);
        if ($data->appraisal->viewingas !== 'appraisee' || $data->appraiseeedit == APPRAISAL_FIELD_LOCKED) {
            $leadership->updateAttributes(['disabled' => 'disabled']);
        }

        $answers = array_combine($roleattributes->details->headings, $roleattributes->details->headings);
        // Add 'Other'
        $answers[$this->str("leadershiproles:answer:generic")] = $this->str("leadershiproles:answer:generic");
        $question = $this->str('leadershiproles:1')
                . html_writer::tag('i', '', [
                    'class' => 'fa fa-info-circle fa-lg fa-fw',
                    'role' => 'button',
                    'data-toggle' => 'popover',
                    'data-content' => $this->str('leadershiproles:popover'),
                    'data-html' => 'true'])
                . html_writer::empty_tag('br')
                . $this->str('leadershiproles:2');
        $label = html_writer::div(
            html_writer::span($question, 'pull-left') . html_writer::span($this->str('leadershiproles:links'), 'pull-right'),
            'clearfix');
        $leadershiproles = $mform->addElement('select', 'leadershiproles', $label, $answers, ['class' => 'hidden']);
        $leadershiproles->setMultiple(true);
        if ($data->appraisal->viewingas !== 'appraisee' || $data->appraiseeedit == APPRAISAL_FIELD_LOCKED) {
            $leadershiproles->updateAttributes(['disabled' => 'disabled']);
        }

        $popover = html_writer::tag('i', '', [
            'class' => 'fa fa-info-circle fa-lg fa-fw',
            'role' => 'button',
            'data-toggle' => 'popover',
            'data-content' => $this->str('leadershipattributes:popover'),
            'data-html' => 'true']);
        $mform->addElement('html', '<div id="oa-development-leadershipattributes" class="hidden">');
        $mform->addElement('html', "<p>Select 2-3 attributes {$popover} to concentrate on from the following</p>");
        $mform->addElement('html', $this->renderer->render_from_template('local_onlineappraisal/development-leadership-attributes', $genericattributes->details));
        $mform->addElement('html', $this->renderer->render_from_template('local_onlineappraisal/development-leadership-attributes', $roleattributes->details));
        $mform->addElement('html', '</div>');

        $options = array_merge($roleattributes->options, $genericattributes->options);

        $leadershipattributes = $mform->addElement('selectgroups', 'leadershipattributes', 'Your selected attributes for this year:', $options, ['class' => 'hidden']);
        $leadershipattributes->setMultiple(true);
        if ($data->appraisal->viewingas !== 'appraisee' || $data->appraiseeedit == APPRAISAL_FIELD_LOCKED) {
            $leadershipattributes->updateAttributes(['disabled' => 'disabled']);
        }
        // END : Leadership Attributes.

        $mform->addElement('textarearup', 'seventy', $this->str('seventy'), 'rows="10" cols="70"' . $appraiseelocked, $this->str('seventyhelp'), 'appraisee');
        $mform->setType('seventy', PARAM_RAW);
        $mform->disabledIf('seventy', 'appraiseeedit', 'eq', APPRAISAL_FIELD_LOCKED);

        $mform->addElement('textarearup', 'twenty', $this->str('twenty'), 'rows="5" cols="70"' . $appraiseelocked, $this->str('twentyhelp'), 'appraisee');
        $mform->setType('twenty', PARAM_RAW);
        $mform->disabledIf('twenty', 'appraiseeedit', 'eq', APPRAISAL_FIELD_LOCKED);

        $mform->addElement('textarearup', 'ten', $this->str('ten'), 'rows="3" cols="70"' . $appraiseelocked, $this->str('tenhelp'), 'appraisee');
        $mform->setType('ten', PARAM_RAW);
        $mform->disabledIf('ten', 'appraiseeedit', 'eq', APPRAISAL_FIELD_LOCKED);

        $mform->addElement('textarearup', 'comments', $this->str('comments'), 'rows="3" cols="70"' . $appraiserlocked, $this->str('commentshelp'), 'appraiser');
        $mform->setType('comments', PARAM_RAW);
        $mform->disabledIf('comments', 'appraiseredit', 'eq', APPRAISAL_FIELD_LOCKED);

        if ($data->appraiseeedit == APPRAISAL_FIELD_EDIT || $data->appraiseredit == APPRAISAL_FIELD_EDIT) {
            $buttonarray=array();
            $buttonarray[] = &$mform->createElement('submit', 'submitbutton', get_string('form:save', 'local_onlineappraisal'));
            $buttonarray[] = &$mform->createElement('submit', 'submitcontinue', get_string('form:submitcontinue', 'local_onlineappraisal'));
            $buttonarray[] = &$mform->createElement('cancel', 'cancelbutton', get_string('form:cancel', 'local_onlineappraisal'), array('class' => 'm-l-5'));
            $mform->addGroup($buttonarray, 'buttonar', '', array(' '), false);

            // Saving nag modal.
            $renderer = $PAGE->get_renderer('local_onlineappraisal');
            $mform->addElement('html', $renderer->render_from_template('local_onlineappraisal/modal_save_nag', new stdClass()));
        } else {
            $mform->addElement('html', html_writer::link($data->nexturl,
                get_string('form:nextpage', 'local_onlineappraisal'), array('class' => 'btn btn-success')));
        }
    }

    private function str($string) {
        return get_string('form:development:' . $string, 'local_onlineappraisal');
    }

    public function definition_after_data() {
        global $USER;
        $mform =& $this->_form;
        $data = $this->_customdata;
        if ($data->userid != $USER->id) {
            $mform->hardFreeze();
        }
    }

    /**
     * Validate the form.
     */
    public function validation($data, $files) {
        $errors = parent::validation($data, $files);
        if (isset($data['leadership']) && $data['leadership'] === $this->str("leadership:answer:2")) {
            if (empty($data['leadershiproles'])) {
                $errors['leadershiproles'] = get_string('required');
            } else if (count($data['leadershiproles']) > 2) {
                $errors['leadershiproles'] = $this->str('leadershiproles:error:toomany');
            }

            if (empty($data['leadershipattributes'])) {
                $errors['leadershipattributes'] = get_string('required');
            } else if (count($data['leadershipattributes']) < 2 || count($data['leadershipattributes']) > 3) {
                $errors['leadershipattributes'] = $this->str('leadershipattributes:error:wrongnumber');
            }
        }
        return $errors;
    }

    public function get_data() {
        $data = parent::get_data();

        if ($this->_customdata->appraiseeedit == APPRAISAL_FIELD_EDIT && $this->is_validated()) {
            // Handle empty multi select, but only if unlocked.
            if ($data->leadership === $this->str("leadership:answer:1")) {
                $data->leadershiproles = [];
            }
        }

        return $data;
    }

    /**
     * Loads leadership attributes JSON data and formats for rendering.
     *
     * @param string $type The type of data to load/render.
     * @param string $prefix Any extra prefix for the returned options.
     * @return stdClass Data ready for rendering
     */
    private function get_leadership_attributes($type, $prefix = '') {
        $data = json_decode($this->str("leadershipattributes:{$type}"), true);

        $return = new stdClass();
        $return->options = [];
        $return->details = new stdClass();
        $return->details->disabled = '';
        if ($this->_customdata->appraiseeedit == APPRAISAL_FIELD_LOCKED) {
            $return->details->disabled = 'disabled="disabled"';
        }
        $return->details->type = $type;
        $return->details->headings = [];
        $return->details->rows = [];

        $colcount = 0;
        foreach ($data as $colname => $coldata) {
            $return->details->headings[] = $colname;
            $optgroup = $prefix . $colname;
            $optionprefix = $optgroup . ' | ';

            // Sort column data alphabetically by key.
            ksort($coldata);

            $rowcount = 0;
            foreach ($coldata as $attrname => $attrinfo) {
                if (!isset($return->details->rows[$rowcount])) {
                    $return->details->rows[$rowcount] = new stdClass();
                    $return->details->rows[$rowcount]->cells = [];
                    if ($colcount > 0) {
                        for ($i = 0; $i <= $colcount; $i++) {
                            $return->details->rows[$rowcount]->cells[$i] = new stdClass();
                        }
                    }
                }
                $option = $optionprefix . $attrname;
                $return->options[$optgroup][$option] = $option;

                $cell = new stdClass();
                $cell->name = $attrname;
                $cell->option = $option;
                $cell->info = $this->process_leadership_attribute_info($attrinfo);
                $return->details->rows[$rowcount]->cells[] = clone $cell;

                $rowcount++;
            }
            $colcount++;
        }

        return $return;
    }

    /**
     * Process the attribute information for info popover.
     *
     * @param array $data Array of content
     * @return string Rendered data ready for popover
     */
    private function process_leadership_attribute_info($data) {
        $torender = new stdClass();
        $torender->sections = [];
        $section = -1;
        foreach ($data as $line) {
            $isheading = substr($line, 0, 1) === '#';
            if ($section === -1 || $isheading) {
                $section++;
                $torender->sections[$section] = new stdClass();
                $torender->sections[$section]->hasheading = $isheading;
                $torender->sections[$section]->heading = $isheading ? substr($line, 1) : '';
                $torender->sections[$section]->items = [];
            }
            if (!$isheading) {
                $torender->sections[$section]->items[] = $line;
            }
        }
        return $this->renderer->render_from_template('local_onlineappraisal/development-leadership-attributes-info', $torender);
    }
}
