<?php
/*
 * This file is part of Totara LMS
 *
 * Copyright (C) 2010 onwards Totara Learning Solutions LTD
 *
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * @author Valerii Kuznetsov <valerii.kuznetsov@totaralms.com>
 * @package totara
 * @subpackage form
 */

namespace local_reportbuilder\form;

use DateTime;
use HTML_QuickForm_Renderer_Default;

global $CFG;
require_once($CFG->libdir . '/formslib.php');
require_once($CFG->dirroot . '/calendar/lib.php');
require_once($CFG->dirroot . '/lib/form/group.php');

/**
 * Class for a group of elements used to input a schedule for events.
 *
 */
class scheduler extends \MoodleQuickForm_group {

    /** @var array These complement separators, they are appended to the resultant HTML */
    public $_wrap = array('', '');
    /**
     * @var null|bool Keeps track of whether the date selector was initialised using createElement
     *                or addElement. If true, createElement was used signifying the element has been
     *                added to a group - see MDL-39187.
     */
    protected $_usedcreateelement = true;

    private $scheduleroptions = [];
    /**
     * constructor
     *
     * @param string $elementName Element's name
     * @param mixed $elementLabel Label(s) for an element
     * @param array $options Options to control the element's display
     * @param mixed $attributes Either a typical HTML attribute string or an associative array
     */
    public function __construct($elementName = null, $elementLabel = null, $options = array(), $attributes = null) {
        parent::__construct($elementName, $elementLabel);
        $this->setAttributes($attributes);
        $this->_persistantFreeze = true;
        $this->_appendName = true;
        $this->_type = 'scheduler';
        // set the options, do not bother setting bogus ones
        if (is_array($options)) {
            foreach ($options as $name => $value) {
                if (isset($this->_options[$name])) {
                    if (is_array($value) && is_array($this->_options[$name])) {
                        $this->_options[$name] = @array_merge($this->_options[$name], $value);
                    } else {
                        $this->_options[$name] = $value;
                    }
                }
            }
        }
        if (isset($options['scheduleroptions'])) {
            $this->scheduleroptions = $options['scheduleroptions'];
        }
    }

    /**
     * Old syntax of class constructor for backward compatibility.
     */
    public function MoodleQuickForm_scheduler($elementName = null, $elementLabel = null, $options = array(), $attributes = null) {
        self::__construct($elementName, $elementLabel, $options, $attributes);
    }

    /**
     * This will create date group element constisting of frequency and scheduled date/time
     *
     * @access private
     */
    function _createElements() {
        // Use a fixed date to prevent problems on days with DST switch and months with < 31 days.
        $date = new DateTime('2000-01-01T00:00:00+00:00');

        $CALENDARDAYS = calendar_get_days();

        if (empty($this->scheduleroptions)) {
            $this->scheduleroptions = \local_reportbuilder\scheduler::get_options();
        }
        // Schedule type options.
        $frequencyselect = array();
        foreach ($this->scheduleroptions as $option => $code) {
            $frequencyselect[$code] = get_string('schedule' . $option, 'local_reportbuilder');
        }

        // Minutely selector.
        $minutelyselect = array();
        foreach (array(1, 2, 3, 4, 5, 10, 15, 20, 30) as $i) { // Divide evenly into an hour, skipped 6 and 12.
            $minutelyselect[$i] = $i;
        }

        // Hourly selector.
        $hourlyselect = array();
        foreach (array(1, 2, 3, 4, 6, 8, 12) as $i) { // Divide evenly into a day.
            $hourlyselect[$i] = $i;
        }

        // Daily selector.
        $date->setDate(2000, 1, 1);
        $dailyselect = array();
        for ($i = 0; $i < 24; $i++) {
            $date->setTime($i, 0, 0);
            $dailyselect[$i] = $date->format('H:i');
        }

        // Weekly selector.
        $weeklyselect = array();
        for ($i = 0; $i < 7; $i++) {
            $weeklyselect[$i] = $CALENDARDAYS[$i]['fullname'];
        }

        // Monthly selector.
        $date->setTime(12, 0, 0);
        $monthlyselect = array();
        $dateformat = current_language() === 'en' ? 'jS' : 'j';
        for ($i = 1; $i <= 31; $i++) {
            $date->setDate(2000, 1, $i);
            $monthlyselect[$i] = $date->format($dateformat);
        }

        $this->_elements = array();
        $this->_elements['frequency'] = $this->createFormElement('select', 'frequency', get_string('schedule', 'local_reportbuilder'), $frequencyselect, '', true);
        $this->_elements['daily'] = $this->createFormElement('select', 'daily', get_string('dailyat', 'local_reportbuilder'), $dailyselect);
        $this->_elements['weekly'] = $this->createFormElement('select', 'weekly', get_string('weeklyon', 'local_reportbuilder'), $weeklyselect);
        $this->_elements['monthly'] = $this->createFormElement('select', 'monthly', get_string('monthlyon', 'local_reportbuilder'), $monthlyselect);
        $this->_elements['hourly'] = $this->createFormElement('select', 'hourly', get_string('hourlyon', 'local_reportbuilder'), $hourlyselect);
        $this->_elements['minutely'] = $this->createFormElement('select', 'minutely', get_string('minutelyon', 'local_reportbuilder'), $minutelyselect);

        foreach ($this->_elements as $option => $element) {
            if (method_exists($element, 'setHiddenLabel')) {
                $element->setHiddenLabel(true);
            }
            if (!array_key_exists($option, $this->scheduleroptions) && $option != 'frequency') {
                unset($this->_elements[$option]);
            }
        }
    }

    /**
     * Called by HTML_QuickForm whenever form event is made on this element
     *
     * @param string $event Name of event
     * @param mixed $arg event arguments
     * @param object $caller calling object
     * @return bool
     */
    function onQuickFormEvent($event, $arg, &$caller) {
        global $CFG;
        $scheduler_options = \local_reportbuilder\scheduler::get_options();
        switch ($event) {
            case 'updateValue':
                // constant values override both default and submitted ones
                // default values are overriden by submitted
                $value = $this->_findValue($caller->_constantValues);
                if (null === $value) {
                    // if no boxes were checked, then there is no value in the array
                    // yet we don't want to display default value in this case
                    if ($caller->isSubmitted()) {
                        $value = $this->_findValue($caller->_submitValues);
                    } else {
                        $value = $this->_findValue($caller->_defaultValues);
                    }
                }
                if (null !== $value) {
                    $this->setValue($value);
                }
                break;
            case 'createElement':
                $caller->disabledIf($arg[0] . '[minutely]', $arg[0] . '[frequency]', 'neq', $scheduler_options['minutely']);
                $caller->disabledIf($arg[0] . '[hourly]', $arg[0] . '[frequency]', 'neq', $scheduler_options['hourly']);
                $caller->disabledIf($arg[0] . '[daily]', $arg[0] . '[frequency]', 'neq', $scheduler_options['daily']);
                $caller->disabledIf($arg[0] . '[weekly]', $arg[0] . '[frequency]', 'neq', $scheduler_options['weekly']);
                $caller->disabledIf($arg[0] . '[monthly]', $arg[0] . '[frequency]', 'neq', $scheduler_options['monthly']);
                // Optional is an optional param, if its set we need to add a disabledIf rule.
                // If its empty or not specified then its not an optional dateselector.
                if (!empty($arg[2]['optional']) && !empty($arg[0])) {
                    $caller->disabledIf($arg[0], $arg[0] . '[enabled]');
                }
                return parent::onQuickFormEvent($event, $arg, $caller);
                break;
            case 'addElement':
                $this->_usedcreateelement = false;
                return parent::onQuickFormEvent($event, $arg, $caller);
                break;
            default:
                return parent::onQuickFormEvent($event, $arg, $caller);
        }
    }

    /**
     * Search for required value in array
     * @param array $values
     */
    public function _findValue(&$values) {
        if (empty($values)) {
            return null;
        }
        $elementname = $this->getName();
        $fields = array('frequency' => null, 'schedule' => null, 'daily' => null, 'weekly' => null,
            'monthly' => null, 'hourly' => null, 'minutely' => null);
        foreach ($fields as $key => $field) {
            if (isset($values[$elementname][$key])) {
                $fields[$key] = $values[$elementname][$key];
            } elseif (isset($values[$key])) {
                $fields[$key] = $values[$key];
            } elseif (isset($values[$elementname . "[$key]"])) {
                $fields[$key] = $values[$elementname . "[$key]"];
            }
        }
        if (!isset($fields['frequency'])) {
            return null;
        }
        switch ($fields['frequency']) {
            case \local_reportbuilder\scheduler::MINUTELY:
                $name = 'minutely';
                $schedule = (isset($fields['schedule'])) ? $fields['schedule'] : $fields['minutely'];
                break;
            case \local_reportbuilder\scheduler::HOURLY:
                $name = 'hourly';
                $schedule = (isset($fields['schedule'])) ? $fields['schedule'] : $fields['hourly'];
                break;
            case \local_reportbuilder\scheduler::DAILY:
                $name = 'daily';
                $schedule = (isset($fields['schedule'])) ? $fields['schedule'] : $fields['daily'];
                break;
            case \local_reportbuilder\scheduler::WEEKLY:
                $name = 'weekly';
                $schedule = (isset($fields['schedule'])) ? $fields['schedule'] : $fields['weekly'];
                break;
            case \local_reportbuilder\scheduler::MONTHLY:
                $name = 'monthly';
                $schedule = (isset($fields['schedule'])) ? $fields['schedule'] : $fields['monthly'];
                break;
            default:
                $name = $schedule = '';
                mtrace("Wrong scheduler frequency code: {$fields['frequency']} in element {$this->getName()}");
                break;
        }
        return array('frequency' => $fields['frequency'], $name => $schedule);
    }

    /**
     * Returns HTML for advchecbox form element.
     *
     * @return string
     */
    function toHtml() {
        //$html = parent::toHtml();
        include_once('HTML/QuickForm/Renderer/Default.php');
        $renderer = new HTML_QuickForm_Renderer_Default();
        $renderer->setElementTemplate('{element}');
        parent::accept($renderer);

        $html = $this->_wrap[0];
        if ($this->_usedcreateelement) {
            $html .= \html_writer::tag('span', $renderer->toHtml(), array('class' => 'scheduler'));
        } else {
            $html .= $renderer->toHtml();
        }
        $html .= $this->_wrap[1];

        return $html;

    }

    /**
     * Accepts a renderer
     *
     * @param \HTML_QuickForm_Renderer $renderer An HTML_QuickForm_Renderer object
     * @param bool $required Whether a group is required
     * @param string $error An error message associated with a group
     */
    function accept(&$renderer, $required = false, $error = null) {
        $this->renderElement($renderer, $this, $required, $error);
    }

    /**
     * Renders element
     *
     * @param \HTML_QuickForm_Renderer $renderer An HTML_QuickForm_Renderer object
     * @param bool $required if input is required field
     * @param string $error error message to display
     */
    function renderElement($renderer, $required, $error) {
        global $OUTPUT;

        // Make sure the element has an id.
        $this->_generateId();
        $advanced = isset($renderer->_advancedElements[$this->getName()]);

        $html = $this->mform_element($required, $advanced, $error, false);

        if ($renderer->_inGroup) {
            $renderer->_groupElementTemplate = $html;
        }

        if (($renderer->_inGroup) and !empty($renderer->_groupElementTemplate)) {
            $renderer->_groupElementTemplate = $html;
        } else if (!isset($renderer->_templates[$this->getName()])) {
            $renderer->_templates[$this->getName()] = $html;
        }


            if (in_array($this->getName(), $renderer->_stopFieldsetElements) && $renderer->_fieldsetsOpen > 0) {
                $renderer->_html .= $renderer->_closeFieldsetTemplate;
                $renderer->_fieldsetsOpen--;
            }
            $renderer->_html .= $html;

    }


    /**
     * Renders an mform element from a template.
     *
     * @param bool $required if input is required field
     * @param bool $advanced if input is an advanced field
     * @param string $error error message to display
     * @param bool $ingroup True if this element is rendered as part of a group
     * @return mixed string|bool
     */
    public function mform_element($required, $advanced, $error, $ingroup) {
        global $OUTPUT;

        $html = $OUTPUT->mform_element($this, $required, $advanced, $error, false);

        if ($html) {
            return $html;
        }

        $templatename = 'local_reportbuilder/element-scheduler';
        if ($ingroup) {
            $templatename .= "-inline";
        }

        $thiscontext = $this->export_for_template($OUTPUT);

        $helpbutton = '';
        if (method_exists($this, 'getHelpButton')) {
            $helpbutton = $this->getHelpButton();
        }
        $label = $this->getLabel();

        $context = array(
                'element' => $thiscontext,
                'label' => $label,
                'required' => $required,
                'advanced' => $advanced,
                'helpbutton' => $helpbutton,
                'error' => $error
        );
        return $OUTPUT->render_from_template($templatename, $context);
    }


    public function export_for_template(\renderer_base $output) {
        $this->_createElementsIfNotExist();
        return parent::export_for_template($output);
    }

    /**
     * Return array where array[0] - frequency, array[1] - schedule
     *
     * @param array $submitValues values submitted.
     * @param bool $assoc specifies if returned array is associative
     * @return array
     */
    function exportValue(&$submitValues, $assoc = false) {
        if (!isset($this->_elements['frequency'])) return array();
        $value = array();
        $value['frequency'] = $this->_elements['frequency']->exportValue($submitValues[$this->getName()], false);
        $value['schedule'] = 0;

        switch ($value['frequency']) {
            case \local_reportbuilder\scheduler::MINUTELY:
                $value['schedule'] = $this->_elements['minutely']->exportValue($submitValues[$this->getName()], false);
                break;
            case \local_reportbuilder\scheduler::HOURLY:
                $value['schedule'] = $this->_elements['hourly']->exportValue($submitValues[$this->getName()], false);
                break;
            case \local_reportbuilder\scheduler::DAILY:
                $value['schedule'] = $this->_elements['daily']->exportValue($submitValues[$this->getName()], false);
                break;
            case \local_reportbuilder\scheduler::WEEKLY:
                $value['schedule'] = $this->_elements['weekly']->exportValue($submitValues[$this->getName()], false);
                break;
            case \local_reportbuilder\scheduler::MONTHLY:
                $value['schedule'] = $this->_elements['monthly']->exportValue($submitValues[$this->getName()], false);
                break;
        }
        return ($assoc) ? $value : array_values($value);
    }
}
