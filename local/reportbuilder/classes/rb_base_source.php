<?php
/*
 * This file is part of T0tara LMS
 *
 * Copyright (C) 2010 onwards T0tara Learning Solutions LTD
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
 * @author Simon Coggins <simon.coggins@t0taralms.com>
 * @package t0tara
 * @subpackage reportbuilder
 */

global $CFG;
require_once($CFG->dirroot . '/user/profile/lib.php');

/**
 * Abstract base class to be extended to create report builder sources
 *
 * @property string $base
 * @property rb_join[] $joinlist
 * @property rb_column_option[] $columnoptions
 * @property rb_filter_option[] $filteroptions
 */
abstract class rb_base_source {

    /*
     * Used in default pre_display_actions function.
     */
    public $needsredirect, $redirecturl, $redirectmessage;

    /** @var array of component used for lookup of classes */
    protected $usedcomponents = array();

    /** @var rb_column[] */
    public $requiredcolumns;

    /** @var rb_global_restriction_set with active restrictions, ignore if null */
    protected $globalrestrictionset = null;

    /** @var rb_join[] list of global report restriction joins  */
    public $globalrestrictionjoins = array();

    /** @var array named query params used in global restriction joins */
    public $globalrestrictionparams = array();

    /**
     * TODO - it would be nice to make this definable in the config or something.
     * @var string $uniqueseperator - A string unique enough to use as a seperator for textareas
     */
    protected static $uniquedelimiter = '^|:';

    /** @var array $addeduserjoins internal tracking of user columns */
    private $addeduserjoins = array();

    /**
     * Class constructor
     *
     * Call from the constructor of all child classes with:
     *
     *  parent::__construct()
     *
     * to ensure child class has implemented everything necessary to work.
     */
    public function __construct() {
        // Extending classes should add own component to this array before calling parent constructor,
        // this allows us to lookup display classes at more locations.
        $this->usedcomponents[] = 'local_reportbuilder';

        // check that child classes implement required properties
        $properties = array(
            'base',
            'joinlist',
            'columnoptions',
            'filteroptions',
        );
        foreach ($properties as $property) {
            if (!property_exists($this, $property)) {
                $a = new stdClass();
                $a->property = $property;
                $a->class = get_class($this);
                throw new ReportBuilderException(get_string('error:propertyxmustbesetiny', 'local_reportbuilder', $a));
            }
        }

        // set sensible defaults for optional properties
        $defaults = array(
            'paramoptions' => array(),
            'requiredcolumns' => array(),
            'contentoptions' => array(),
            'preproc' => null,
            'grouptype' => 'none',
            'groupid' => null,
            'selectable' => true,
            'scheduleable' => true,
            'cacheable' => true,
        );
        foreach ($defaults as $property => $default) {
            if (!property_exists($this, $property)) {
                $this->$property = $default;
            } else if ($this->$property === null) {
                $this->$property = $default;
            }
        }

        // Make sure that there are no column options using subqueries if report is grouped.
        if ($this->get_grouped_column_options()) {
            foreach ($this->columnoptions as $k => $option) {
                if ($option->issubquery) {
                    unset($this->columnoptions[$k]);
                }
            }
        }

        // basic sanity checking of joinlist
        $this->validate_joinlist();

        // Add custom user fields for columns added via add_user_fields_to_columns() and friends.
        foreach ($this->addeduserjoins as $join => $info) {
            if (empty($info['groupname'])) {
                // Most likely somebody did not add any user columns, in that case do not add custom fields and rely on the BC fallback later.
                continue;
            }
            $this->add_custom_user_fields($this->joinlist, $this->columnoptions, $this->filteroptions, $join, $info['groupname'], $info['addtypetoheading'], empty($info['filters']));
            $this->addeduserjoins[$join]['processed'] = true;
        }

        //create array to store the join functions and join table
        $joindata = array();
        $base = $this->base;
        //if any of the join tables are customfield-related, ensure the customfields are added
        foreach ($this->joinlist as $join) {
            //tables can be joined multiple times so we set elements of an associative array as joinfunction => jointable
            $table = $join->table;
            switch ($table) {
                case '{user}':
                    if ($join->name !== 'auser') {
                        break;
                    }
                    $joindata['add_custom_user_fields'] = 'auser'; // This is a fallback only for sources that does not add user fields properly!
                    break;
                case '{course}':
                    $joindata['add_custom_course_fields'] = 'course';
                    break;
            }
        }
        //now ensure customfields fields are added if there are no joins but the base table is customfield-related
        switch ($base) {
            case '{user}':
                $joindata['add_custom_user_fields'] = 'base'; // This is a fallback only for sources that does not add user fields properly!
                break;
            case '{course}':
                $joindata['add_custom_course_fields'] = 'base';
                break;
        }
        //and then use the flags to call the appropriate add functions
        foreach ($joindata as $joinfunction => $jointable) {
            $this->$joinfunction($this->joinlist,
                                 $this->columnoptions,
                                 $this->filteroptions,
                                 $jointable
                                );

        }
    }

    /**
     * Is this report source usable?
     *
     * Override and return true if the source should be hidden
     * in all user interfaces. For example when the source
     * requires some subsystem to be enabled.
     *
     * @return bool
     */
    public function is_ignored() {
        return false;
    }

    /**
     * Are the global report restrictions implemented in the source?
     *
     * Return values mean:
     *   - true: this report source supports global report restrictions.
     *   - false: this report source does NOT support global report restrictions.
     *   - null: this report source has not been converted to use global report restrictions yet.
     *
     * @return null|bool
     */
    public function global_restrictions_supported() {
        // Null means not converted yet, override in sources with true or false.
        return null;
    }

    /**
     * Set redirect url and (optionally) message for use in default pre_display_actions function.
     *
     * When pre_display_actions is call it will redirect to the specified url (unless pre_display_actions
     * is overridden, in which case it performs those actions instead).
     *
     * @param mixed $url moodle_url or url string
     * @param string $message
     */
    protected function set_redirect($url, $message = null) {
        $this->redirecturl = $url;
        $this->redirectmessage = $message;
    }


    /**
     * Set whether redirect needs to happen in pre_display_actions.
     *
     * @param bool $truth true if redirect is needed
     */
    protected function needs_redirect($truth = true) {
        $this->needsredirect = $truth;
    }


    /**
     * Default pre_display_actions - if needsredirect is true then redirect to the specified
     * page, otherwise do nothing.
     *
     * This function is called after post_config and before report data is generated. This function is
     * not called when report data is not generated, such as on report setup pages.
     * If you want to perform a different action after post_config then override this function and
     * set your own private variables (e.g. to signal a result from post_config) in your report source.
     */
    public function pre_display_actions() {
        if ($this->needsredirect && isset($this->redirecturl)) {
            if (isset($this->redirectmessage)) {
                notice($this->redirectmessage, $this->redirecturl, array('class' => 'notifymessage'));
            } else {
                redirect($this->redirecturl);
            }
        }
    }


    /**
     * Create a link that when clicked will display additional information inserted in a box below the clicked row.
     *
     * @param string|stringable $columnvalue the value to display in the column
     * @param string $expandname the name of the function (prepended with 'rb_expand_') that will generate the contents
     * @param array $params any parameters that the content generator needs
     * @param string|moodle_url $alternateurl url to link to in case js is not available
     * @param array $attributes
     * @return type
     */
    protected function create_expand_link($columnvalue, $expandname, $params, $alternateurl = '', $attributes = array()) {
        global $OUTPUT;

        // Serialize the data so that it can be passed as a single value.
        $paramstring = http_build_query($params, '', '&');

        $class_link = 'rb-display-expand-link ';
        if (array_key_exists('class', $attributes)) {
            $class_link .=  $attributes['class'];
        }

        $attributes['class'] = 'rb-display-expand';
        $attributes['data-name'] = $expandname;
        $attributes['data-param'] = $paramstring;
        $infoicon = html_writer::tag('i', '', array('class' => 'fa fa-info-circle ft-state-info'));

        // Create the result.
        $link = html_writer::link($alternateurl, format_string($columnvalue), array('class' => $class_link));
        return html_writer::div($infoicon . $link, 'rb-display-expand', $attributes);
    }


    /**
     * Check the joinlist for invalid dependencies and duplicate names
     *
     * @return True or throws exception if problem found
     */
    private function validate_joinlist() {
        $joinlist = $this->joinlist;
        $joins_used = array();

        // don't let source define join with same name as an SQL
        // reserved word
        $reserved_words = sql_generator::getAllReservedWords();
        $reserved_words = array_keys($reserved_words);

        foreach ($joinlist as $item) {
            // check join list for duplicate names
            if (in_array($item->name, $joins_used)) {
                $a = new stdClass();
                $a->join = $item->name;
                $a->source = get_class($this);
                throw new ReportBuilderException(get_string('error:joinxusedmorethanonceiny', 'local_reportbuilder', $a));
            } else {
                $joins_used[] = $item->name;
            }

            if (in_array($item->name, $reserved_words)) {
                $a = new stdClass();
                $a->join = $item->name;
                $a->source = get_class($this);
                throw new ReportBuilderException(get_string('error:joinxisreservediny', 'local_reportbuilder', $a));
            }
        }

        foreach ($joinlist as $item) {
            // check that dependencies exist
            if (isset($item->dependencies) &&
                is_array($item->dependencies)) {

                foreach ($item->dependencies as $dep) {
                    if ($dep == 'base') {
                        continue;
                    }
                    if (!in_array($dep, $joins_used)) {
                        $a = new stdClass();
                        $a->join = $item->name;
                        $a->source = get_class($this);
                        $a->dependency = $dep;
                        throw new ReportBuilderException(get_string('error:joinxhasdependencyyinz', 'local_reportbuilder', $a));
                    }
                }
            } else if (isset($item->dependencies) &&
                $item->dependencies != 'base') {

                if (!in_array($item->dependencies, $joins_used)) {
                    $a = new stdClass();
                    $a->join = $item->name;
                    $a->source = get_class($this);
                    $a->dependency = $item->dependencies;
                    throw new ReportBuilderException(get_string('error:joinxhasdependencyyinz', 'local_reportbuilder', $a));
                }
            }
        }
        return true;
    }


    //
    //
    // General purpose source specific methods
    //
    //

    /**
     * Returns a new rb_column object based on a column option from this source
     *
     * If $heading is given use it for the heading property, otherwise use
     * the default heading property from the column option
     *
     * @param string $type The type of the column option to use
     * @param string $value The value of the column option to use
     * @param int $transform
     * @param int $aggregate
     * @param string $heading Heading for the new column
     * @param boolean $customheading True if the heading has been customised
     * @return rb_column A new rb_column object with details copied from this rb_column_option
     */
    public function new_column_from_option($type, $value, $transform, $aggregate, $heading=null, $customheading = true, $hidden=0) {
        $columnoptions = $this->columnoptions;
        $joinlist = $this->joinlist;
        if ($coloption =
            reportbuilder::get_single_item($columnoptions, $type, $value)) {

            // make sure joins are defined before adding column
            if (!reportbuilder::check_joins($joinlist, $coloption->joins)) {
                $a = new stdClass();
                $a->type = $coloption->type;
                $a->value = $coloption->value;
                $a->source = get_class($this);
                throw new ReportBuilderException(get_string('error:joinsfortypexandvalueynotfoundinz', 'local_reportbuilder', $a));
            }

            if ($heading === null) {
                $heading = ($coloption->defaultheading !== null) ?
                    $coloption->defaultheading : $coloption->name;
            }

            return new rb_column(
                $type,
                $value,
                $heading,
                $coloption->field,
                array(
                    'joins' => $coloption->joins,
                    'displayfunc' => $coloption->displayfunc,
                    'extrafields' => $coloption->extrafields,
                    'required' => false,
                    'capability' => $coloption->capability,
                    'noexport' => $coloption->noexport,
                    'grouping' => $coloption->grouping,
                    'grouporder' => $coloption->grouporder,
                    'nosort' => $coloption->nosort,
                    'style' => $coloption->style,
                    'class' => $coloption->class,
                    'hidden' => $hidden,
                    'customheading' => $customheading,
                    'transform' => $transform,
                    'aggregate' => $aggregate,
                    'extracontext' => $coloption->extracontext
                )
            );
        } else {
            $a = new stdClass();
            $a->type = $type;
            $a->value = $value;
            $a->source = get_class($this);
            throw new ReportBuilderException(get_string('error:columnoptiontypexandvalueynotfoundinz', 'local_reportbuilder', $a));
        }
    }

    /**
     * Returns list of used components.
     *
     * The list includes frankenstyle component names of the
     * current source and all parents.
     *
     * @return string[]
     */
    public function get_used_components() {
        return $this->usedcomponents;
    }

    //
    //
    // Generic column display methods
    //
    //

    /**
     * Format row record data for display.
     *
     * @param stdClass $row
     * @param string $format
     * @param reportbuilder $report
     * @return array of strings usually, values may be arrays for Excel format for example.
     */
    public function process_data_row(stdClass $row, $format, reportbuilder $report) {
        $results = array();
        $isexport = ($format !== 'html');

        foreach ($report->columns as $column) {
            if (!$column->display_column($isexport)) {
                continue;
            }

            $type = $column->type;
            $value = $column->value;
            $field = strtolower("{$type}_{$value}");

            if (!property_exists($row, $field)) {
                $results[] = get_string('unknown', 'local_reportbuilder');
                continue;
            }

            $classname = $column->get_display_class($report);
            $results[] = $classname::display($row->$field, $format, $row, $column, $report);
        }

        return $results;
    }

    /**
     * Reformat a timestamp into a time, showing nothing if invalid or null
     *
     * @param integer $date Unix timestamp
     * @param object $row Object containing all other fields for this row
     *
     * @return string Time in a nice format
     */
    function rb_display_nice_time($date, $row) {
        if ($date && is_numeric($date)) {
            return userdate($date, get_string('strftimeshort', 'langconfig'));
        } else {
            return '';
        }
    }

    /**
     * Reformat a timestamp and timezone into a datetime, showing nothing if invalid or null
     *
     * @param integer $date Unix timestamp
     * @param object $row Object containing all other fields for this row (which should include a timezone field)
     *
     * @return string Date and time in a nice format
     */
    function rb_display_nice_datetime_in_timezone($date, $row) {

        if ($date && is_numeric($date)) {
            if (empty($row->timezone)) {
                $targetTZ = core_date::get_user_timezone();
                $tzstring = get_string('nice_time_unknown_timezone', 'local_reportbuilder');
            } else {
                $targetTZ = core_date::normalise_timezone($row->timezone);
                $tzstring = core_date::get_localised_timezone($targetTZ);
            }
            $date = userdate($date, get_string('strftimedatetime', 'langconfig'), $targetTZ) . ' ';
            return $date . $tzstring;
        } else {
            return '';
        }
    }

    function rb_display_delimitedlist_date_in_timezone($data, $row) {
        $format = get_string('strftimedate', 'langconfig');
        return $this->format_delimitedlist_datetime_in_timezone($data, $row, $format);
    }

    function rb_display_delimitedlist_datetime_in_timezone($data, $row) {
        $format = get_string('strftimedatetime', 'langconfig');
        return $this->format_delimitedlist_datetime_in_timezone($data, $row, $format);
    }

    // Assumes you used a custom grouping with the self::$uniquedelimiter to concatenate the fields.
    function format_delimitedlist_datetime_in_timezone($data, $row, $format) {
        $delimiter = self::$uniquedelimiter;
        $items = explode($delimiter, $data);
        $output = array();
        foreach ($items as $date) {
            if ($date && is_numeric($date)) {
                if (empty($row->timezone)) {
                    $targetTZ = core_date::get_user_timezone();
                    $tzstring = get_string('nice_time_unknown_timezone', 'local_reportbuilder');
                } else {
                    $targetTZ = core_date::normalise_timezone($row->timezone);
                    $tzstring = core_date::get_localised_timezone($targetTZ);
                }
                $date = userdate($date, get_string('strftimedatetime', 'langconfig'), $targetTZ) . ' ';
                $output[] = $date . $tzstring;
            } else {
                $output[] = '-';
            }
        }

        return implode($output, "\n");
    }

    /**
     * Reformat two timestamps and timezones into a datetime, showing only one date if only one is present and
     * nothing if invalid or null.
     *
     * @param integer $date Unix timestamp
     * @param object $row Object containing all other fields for this row (which should include a timezone field)
     *
     * @return string Date and time in a nice format
     */
    function rb_display_nice_two_datetime_in_timezone($startdate, $row) {

        $finishdate = $row->finishdate;
        $startdatetext = $finishdatetext = $returntext = '';

        if (empty($row->timezone)) {
            $targetTZ = core_date::get_user_timezone();
            $tzstring = get_string('nice_time_unknown_timezone', 'local_reportbuilder');
        } else {
            $targetTZ = core_date::normalise_timezone($row->timezone);
            $tzstring = core_date::get_localised_timezone($targetTZ);
        }

        if ($startdate && is_numeric($startdate)) {
            $startdatetext = userdate($startdate, get_string('strftimedatetime', 'langconfig'), $targetTZ) . ' ' . $targetTZ;
        }

        if ($finishdate && is_numeric($finishdate)) {
            $finishdatetext = userdate($finishdate, get_string('strftimedatetime', 'langconfig'), $targetTZ) . ' ' . $targetTZ;
        }

        if ($startdatetext && $finishdatetext) {
            $returntext = get_string('datebetween', 'local_reportbuilder', array('from' => $startdatetext, 'to' => $finishdatetext));
        } else if ($startdatetext) {
            $returntext = get_string('dateafter', 'local_reportbuilder', $startdatetext);
        } else if ($finishdatetext) {
            $returntext = get_string('datebefore', 'local_reportbuilder', $finishdatetext);
        }

        return $returntext;
    }


    /**
     * Reformat a timestamp into a date and time (including seconds), showing nothing if invalid or null
     *
     * @param integer $date Unix timestamp
     * @param object $row Object containing all other fields for this row
     *
     * @return string Date and time (including seconds) in a nice format
     */
    function rb_display_nice_datetime_seconds($date, $row) {
        if ($date && is_numeric($date)) {
            return userdate($date, get_string('strftimedateseconds', 'langconfig'));
        } else {
            return '';
        }
    }

    // convert floats to 2 decimal places
    function rb_display_round2($item, $row) {
        return ($item === null or $item === '') ? '-' : sprintf('%.2f', $item);
    }

    // converts number to percentage with 1 decimal place
    function rb_display_percent($item, $row) {
        return ($item === null or $item === '') ? '-' : sprintf('%.1f%%', $item);
    }

    // Displays a comma separated list of strings as one string per line.
    // Assumes you used "'grouping' => 'comma_list'", which concatenates with ', ', to construct the string.
    function rb_display_list_to_newline($list, $row) {
        $items = explode(', ', $list);
        foreach ($items as $key => $item) {
            if (empty($item)) {
                $items[$key] = '-';
            }
        }
        return implode($items, "\n");
    }

    /**
     * Displays a delimited list of strings as one string per line.
     * Assumes you used "'grouping' => 'sql_aggregate'", which concatenates with $uniquedelimiter to construct a pre-ordered string.
     *
     * @deprecated Since 9.0
     * @param $list
     * @param $row
     * @return string
     */
    function rb_display_orderedlist_to_newline($list, $row) {
        debugging('The orderedlist_to_newline report builder display function has been deprecated and replaced by local_reportbuilder\rb\display\orderedlist_to_newline', DEBUG_DEVELOPER);

        $output = array();
        $items = explode(self::$uniquedelimiter, $list);
        foreach ($items as $item) {
            $item = trim($item);
            if (empty($item) || $item === '-') {
                $output[] = '-';
            } else {
                $output[] = format_string($item);
            }
        }
        return implode($output, "\n");
    }

    // Assumes you used a custom grouping with the self::$uniquedelimiter to concatenate the fields.
    function rb_display_delimitedlist_to_newline($list, $row) {
        $delimiter = self::$uniquedelimiter;
        $items = explode($delimiter, $list);
        $output = array();
        foreach ($items as $item) {
            $item = trim($item);
            if (empty($item) || $item === '-') {
                $output[] = '-';
            } else {
                $output[] = format_string($item);
            }
        }
        return implode($output, "\n");
    }

    // Assumes you used a custom grouping with the self::$uniquedelimiter to concatenate the fields.
    function rb_display_delimitedlist_multi_to_newline($list, $row) {
        $delimiter = self::$uniquedelimiter;
        $items = explode($delimiter, $list);
        $output = array();
        foreach ($items as $item) {
            $inline = array();
            $item = (array)json_decode($item);
            if ($item === '-' || empty($item)) {
                $output[] = '-';
            } else {
                foreach ($item as $option) {
                    $inline[] = format_string($option->option);
                }
                $output[] = implode($inline, ', ');
            }
        }
        return implode($output, "\n");
    }

    // Assumes you used a custom grouping with the self::$uniquedelimiter to concatenate the fields.
    function rb_display_delimitedlist_url_to_newline($list, $row) {
        $delimiter = self::$uniquedelimiter;
        $items = explode($delimiter, $list);
        $output = array();
        foreach ($items as $item) {
            $item = json_decode($item);
            if ($item === '-' || empty($item)) {
                $output[] = '-';
            } else {
                $text = s(empty($item->text) ? $item->url : format_string($item->text));
                $target = isset($item->target) ? array('target' => '_blank', 'rel' => 'noreferrer') : null;
                $output[] = html_writer::link($item->url, $text, $target);
            }
        }
        return implode($output, "\n");
    }

    // Assumes you used a custom grouping with the self::$uniquedelimiter to concatenate the fields.
    function delimitedlist_files_to_newline($data, $row, $type, $isexport) {
        global $CFG;
        require_once($CFG->dirroot . '/user/profile/field/file/field.class.php');

        $delimiter = self::$uniquedelimiter;
        $items = explode($delimiter, $data);
        $extradata = array(
            'prefix' => $type,
            'isexport' => $isexport
        );

        $output = array();
        foreach ($items as $item) {
            if ($item === '-' || empty($item)) {
                $output[] = '-';
            } else {
                $output[] = customfield_file::display_item_data($item, $extradata);
            }
        }
        return implode($output, "\n");
    }

    // Assumes you used a custom grouping with the self::$uniquedelimiter to concatenate the fields.
    function rb_display_delimitedlist_location_to_newline($list, $row) {
        $delimiter = self::$uniquedelimiter;
        $items = explode($delimiter, $list);
        $output = array();
        foreach ($items as $item) {
            $item = json_decode($item);
            if ($item === '-' || empty($item)) {
                $output[] = '-';
            } else {
                $location = trim(str_replace("\r\n", " ", $item->address));
                $output[] = $location;
            }
        }
        return implode($output, "\n");
    }

    // Displays a comma separated list of ints as one nice_date per line.
    // Assumes you used "'grouping' => 'comma_list'", which concatenates with ', ', to construct the string.
    function rb_display_list_to_newline_date($datelist, $row) {
        $items = explode(', ', $datelist);
        foreach ($items as $key => $item) {
            if (empty($item) || $item === '-') {
                $items[$key] = '-';
            } else {
                $items[$key] = $this->rb_display_nice_date($item, $row);
            }
        }
        return implode($items, "\n");
    }

    // Displays a delimited list of ints as one nice_date per line, based off nice_date_list.
    // Assumes you used "'grouping' => 'sql_aggregate'", which concatenates with $uniquedelimiter to construct a pre-ordered string.
    function rb_display_orderedlist_to_newline_date($datelist, $row) {
        $output = array();
        $items = explode(self::$uniquedelimiter, $datelist);
        foreach ($items as $item) {
            if (empty($item) || $item === '-') {
                $output[] = '-';
            } else {
                $output[] = userdate($item, get_string('strfdateshortmonth', 'local_reportbuilder'));
            }
        }
        return implode($output, "\n");
    }

    // Assumes you used a custom grouping with the self::$uniquedelimiter to concatenate the fields.
    function rb_display_delimitedlist_to_newline_date($datelist, $row) {
        $delimiter = self::$uniquedelimiter;
        $items = explode($delimiter, $datelist);
        $output = array();
        foreach ($items as $item) {
            if (empty($item) || $item === '-') {
                $output[] = '-';
            } else {
                $output[] = userdate($item, get_string('strfdateshortmonth', 'local_reportbuilder'));
            }
        }
        return implode($output, "\n");
    }

    /**
     * Display address from location stored as json object
     * @param string $location
     * @param stdClass $row
     * @param bool $isexport
     */
    public function rb_display_location($location, $row, $isexport = false) {
        global $CFG;
        require_once($CFG->dirroot . '/user/profile/field/location/define.class.php');
        $output = array();

        $location = customfield_define_location::convert_location_json_to_object($location);

        if (is_null($location)){
            return get_string('notapplicable', 'facetoface');
        }

        $output[] = $location->address;

        return implode('', $output);
    }

    /**
     * Display correct course grade via grade or RPL as a percentage string
     *
     * @param string $item A number to convert
     * @param object $row Object containing all other fields for this row
     *
     * @return string The percentage with 1 decimal place
     */
    function rb_display_course_grade_percent($item, $row) {
        if (!empty($row->maxgrade) && !empty($item)) {

            $maxgrade = (float)$row->maxgrade;
            $mingrade = 0.0;
            if (!empty($row->mingrade)) {
                $mingrade = (float)$row->mingrade;
            }

            // Create a percentage using the max grade.
            $percent = ((($item - $mingrade) / ($maxgrade - $mingrade)) * 100);

            return sprintf('%.1f%%', $percent);
        } else if ($item !== null && $item !== '') {
            // If the item has a value show it.
            return $item;
        } else {
            // Otherwise show a '-'
            return '-';
        }
    }

    /**
     * A rb_column_options->displayfunc helper function for showing a user's name and links to their profile.
     * To pass the correct data, first:
     *      $usednamefields = totara_get_all_user_name_fields_join($base, null, true);
     *      $allnamefields = totara_get_all_user_name_fields_join($base);
     * then your "field" param should be:
     *      $DB->sql_concat_join("' '", $usednamefields)
     * to allow sorting and filtering, and finally your extrafields should be:
     *      array_merge(array('id' => $base . '.id'),
     *                  $allnamefields)
     * When exporting, only the user's full name is displayed (without link).
     *
     * @param string $user Unused
     * @param object $row All the data required to display a user's name
     * @param boolean $isexport If the report is being exported or viewed
     * @return string
     */
    function rb_display_link_user($user, $row, $isexport = false) {

        // Process obsolete calls to this display function.
        if (isset($row->user_id)) {
            $fullname = $user;
        } else {
            $fullname = fullname($row);
        }

        // Don't show links in spreadsheet.
        if ($isexport) {
            return $fullname;
        }

        if ($row->id == 0) {
            // No user id means no link, most likely the fullname is empty anyway.
            return $fullname;
        }

        $url = new moodle_url('/user/view.php', array('id' => $row->id));
        if ($fullname === '') {
            return '';
        } else {
            return html_writer::link($url, $fullname);
        }
    }

    /**
     * A rb_column_options->displayfunc helper function for showing a user's profile picture, name and links to their profile.
     * To pass the correct data, first:
     *      $usednamefields = totara_get_all_user_name_fields_join($base, null, true);
     *      $allnamefields = totara_get_all_user_name_fields_join($base);
     * then your "field" param should be:
     *      $DB->sql_concat_join("' '", $usednamefields)
     * to allow sorting and filtering, and finally your extrafields should be:
     *      array_merge(array('id' => $base . '.id',
     *                        'picture' => $base . '.picture',
     *                        'imagealt' => $base . '.imagealt',
     *                        'email' => $base . '.email'),
     *                  $allnamefields)
     * When exporting, only the user's full name is displayed (without icon or link).
     *
     * @param string $user Unused
     * @param object $row All the data required to display a user's name, icon and link
     * @param boolean $isexport If the report is being exported or viewed
     * @return string
     */
    function rb_display_link_user_icon($user, $row, $isexport = false) {
        global $OUTPUT;

        // Process obsolete calls to this display function.
        if (isset($row->userpic_picture)) {
            $picuser = new stdClass();
            $picuser->id = $row->user_id;
            $picuser->picture = $row->userpic_picture;
            $picuser->imagealt = $row->userpic_imagealt;
            $picuser->firstname = $row->userpic_firstname;
            $picuser->firstnamephonetic = $row->userpic_firstnamephonetic;
            $picuser->middlename = $row->userpic_middlename;
            $picuser->lastname = $row->userpic_lastname;
            $picuser->lastnamephonetic = $row->userpic_lastnamephonetic;
            $picuser->alternatename = $row->userpic_alternatename;
            $picuser->email = $row->userpic_email;
            $row = $picuser;
        }

        if ($row->id == 0) {
            return '';
        }

        // Don't show picture in spreadsheet.
        if ($isexport) {
            return fullname($row);
        }

        $url = new moodle_url('/user/view.php', array('id' => $row->id));
        return $OUTPUT->user_picture($row, array('courseid' => 1)) . "&nbsp;" . html_writer::link($url, $user);
    }

    /**
     * A rb_column_options->displayfunc helper function for showing a user's profile picture.
     * To pass the correct data, first:
     *      $usernamefields = totara_get_all_user_name_fields_join($base, null, true);
     *      $allnamefields = totara_get_all_user_name_fields_join($base);
     * then your "field" param should be:
     *      $DB->sql_concat_join("' '", $usednamefields)
     * to allow sorting and filtering, and finally your extrafields should be:
     *      array_merge(array('id' => $base . '.id',
     *                        'picture' => $base . '.picture',
     *                        'imagealt' => $base . '.imagealt',
     *                        'email' => $base . '.email'),
     *                  $allnamefields)
     * When exporting, only the user's full name is displayed (instead of picture).
     *
     * @param string $user Unused
     * @param object $row All the data required to display a user's name and icon
     * @param boolean $isexport If the report is being exported or viewed
     * @return string
     */
    function rb_display_user_picture($user, $row, $isexport = false) {
        global $OUTPUT;

        // Process obsolete calls to this display function.
        if (isset($row->userpic_picture)) {
            $picuser = new stdClass();
            $picuser->id = $user;
            $picuser->picture = $row->userpic_picture;
            $picuser->imagealt = $row->userpic_imagealt;
            $picuser->firstname = $row->userpic_firstname;
            $picuser->firstnamephonetic = $row->userpic_firstnamephonetic;
            $picuser->middlename = $row->userpic_middlename;
            $picuser->lastname = $row->userpic_lastname;
            $picuser->lastnamephonetic = $row->userpic_lastnamephonetic;
            $picuser->alternatename = $row->userpic_alternatename;
            $picuser->email = $row->userpic_email;
            $row = $picuser;
        }

        // Don't show picture in spreadsheet.
        if ($isexport) {
            return fullname($row);
        } else {
            return $OUTPUT->user_picture($row, array('courseid' => 1));
        }
    }

    /**
     * A rb_column_options->displayfunc helper function for showing a user's name.
     * To pass the correct data, first:
     *      $usednamefields = totara_get_all_user_name_fields_join($base, null, true);
     *      $allnamefields = totara_get_all_user_name_fields_join($base);
     * then your "field" param should be:
     *      $DB->sql_concat_join("' '", $usednamefields)
     * to allow sorting and filtering, and finally your extrafields should be:
     *      $allnamefields
     *
     * @param string $user Unused
     * @param object $row All the data required to display a user's name
     * @param boolean $isexport If the report is being exported or viewed
     * @return string
     */
    function rb_display_user($user, $row, $isexport = false) {
        return fullname($row);
    }

    /**
     * Convert a course name into an expanding link.
     *
     * @param string $course
     * @param array $row
     * @param bool $isexport
     * @return html|string
     */
    public function rb_display_course_expand($course, $row, $isexport = false) {
        if ($isexport) {
            return format_string($course);
        }

        $attr = array('class' => totara_get_style_visibility($row, 'course_visible'));
        $alturl = new moodle_url('/course/view.php', array('id' => $row->course_id));
        return $this->create_expand_link($course, 'course_details', array('expandcourseid' => $row->course_id), $alturl, $attr);
    }

    /**
     * Convert a program/certification name into an expanding link.
     *
     * @param string $program
     * @param array $row
     * @param bool $isexport
     * @return html|string
     */
    public function rb_display_program_expand($program, $row, $isexport = false) {
        if ($isexport) {
            return format_string($program);
        }

        $attr = array('class' => totara_get_style_visibility($row, 'prog_visible'));
        $alturl = new moodle_url('/totara/program/view.php', array('id' => $row->prog_id));
        return $this->create_expand_link($program, 'prog_details',
                array('expandprogid' => $row->prog_id), $alturl, $attr);
    }

    /**
     * Certification display the certification path as string.
     *
     * @param string $certifpath    CERTIFPATH_X constant to describe cert or recert coursesets
     * @param array $row            The record used to generate the table row
     * @return string
     */
    function rb_display_certif_certifpath($certifpath, $row) {
        global $CERTIFPATH;
        if ($certifpath && isset($CERTIFPATH[$certifpath])) {
            return get_string($CERTIFPATH[$certifpath], 'local_custom_certification');
        }
    }

    /**
     * Expanding content to display when clicking a course.
     * Will be placed inside a table cell which is the width of the table.
     * Call required_param to get any param data that is needed.
     * Make sure to check that the data requested is permitted for the viewer.
     *
     * @return string
     */
    public function rb_expand_course_details() {
        global $CFG, $DB, $USER;
        require_once($CFG->dirroot . '/local/reportbuilder/report_forms.php');
        require_once($CFG->dirroot . '/course/renderer.php');
        require_once($CFG->dirroot . '/lib/coursecatlib.php');

        $courseid = required_param('expandcourseid', PARAM_INT);
        $userid = $USER->id;

        $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

        $chelper = new coursecat_helper();

        $formdata = array(
            // The following are required.
            'summary' => $chelper->get_course_formatted_summary(new course_in_list($course)),
            'status' => null,
            'courseid' => $courseid,

            // The following are optional, and depend upon state.
            'inlineenrolmentelements' => null,
            'enroltype' => null,
            'progress' => null,
            'enddate' => null,
            'grade' => null,
            'action' => null,
            'url' => null,
        );

        $coursecontext = context_course::instance($course->id, MUST_EXIST);
        $enrolled = is_enrolled($coursecontext);

        $inlineenrolments = array();
        if ($enrolled) {
            $ccompl = new completion_completion(array('userid' => $userid, 'course' => $courseid));
            $complete = $ccompl->is_complete();
            if ($complete) {
                $sql = 'SELECT gg.*
                          FROM {grade_grades} gg
                          JOIN {grade_items} gi
                            ON gg.itemid = gi.id
                         WHERE gg.userid = ?
                           AND gi.courseid = ?';
                $grade = $DB->get_record_sql($sql, array($userid, $courseid));
                $coursecompletion = $DB->get_record('course_completions', array('userid' => $userid, 'course' => $courseid));
                $coursecompletedon = userdate($coursecompletion->timecompleted, get_string('strfdateshortmonth', 'local_reportbuilder'));

                $formdata['status'] = get_string('coursestatuscomplete', 'local_reportbuilder');
                $formdata['progress'] = get_string('coursecompletedon', 'local_reportbuilder', $coursecompletedon);
                if ($grade) {
                    if (!isset($grade->finalgrade)) {
                        $formdata['grade'] = '-';
                    } else {
                        $formdata['grade'] = get_string('xpercent', 'local_reportbuilder', $grade->finalgrade);
                    }
                }
            } else {
                $formdata['status'] = get_string('coursestatusenrolled', 'local_reportbuilder');

                $progress = totara_display_course_progress_bar($userid, $courseid);
                $formdata['progress'] = $progress;

                // Course not finished, so no end date for course.
                $formdata['enddate'] = '';
            }
            $formdata['url'] = new moodle_url('/course/view.php', array('id' => $courseid));
            $formdata['action'] =  get_string('launchcourse', 'totara_program');
        } else {
            $formdata['status'] = get_string('coursestatusnotenrolled', 'local_reportbuilder');

            $instances = enrol_get_instances($courseid, true);
            $plugins = enrol_get_plugins(true);

            $enrolmethodlist = array();
            foreach ($instances as $instance) {
                if (!isset($plugins[$instance->enrol])) {
                    continue;
                }
                $plugin = $plugins[$instance->enrol];
                if (enrol_is_enabled($instance->enrol)) {
                    $enrolmethodlist[] = $plugin->get_instance_name($instance);
                    // If the enrolment plugin has a course_expand_hook then add to a list to process.
                    if (method_exists($plugin, 'course_expand_get_form_hook')
                        && method_exists($plugin, 'course_expand_enrol_hook')) {
                        $enrolment = array ('plugin' => $plugin, 'instance' => $instance);
                        $inlineenrolments[$instance->id] = (object) $enrolment;
                    }
                }
            }
            $enrolmethodstr = implode(', ', $enrolmethodlist);
            $realuser = \core\session\manager::get_realuser();

            $inlineenrolmentelements = $this->get_inline_enrolment_elements($inlineenrolments);
            $formdata['inlineenrolmentelements'] = $inlineenrolmentelements;
            $formdata['enroltype'] = $enrolmethodstr;

            if (is_viewing($coursecontext, $realuser->id) || is_siteadmin($realuser->id)) {
                $formdata['action'] = get_string('viewcourse', 'local_reportbuilder');
                $formdata['url'] = new moodle_url('/course/view.php', array('id' => $courseid));
            }
        }

        $mform = new report_builder_course_expand_form(null, $formdata);

        if (!empty($inlineenrolments)) {
            $this->process_enrolments($mform, $inlineenrolments);
        }

        return $mform->render();
    }

    /**
     * @param $inlineenrolments array of objects containing matching instance/plugin pairs
     * @return array of form elements
     */
    private function get_inline_enrolment_elements(array $inlineenrolments) {
        global $CFG;

        require_once($CFG->dirroot . '/lib/pear/HTML/QuickForm/button.php');
        require_once($CFG->dirroot . '/lib/pear/HTML/QuickForm/static.php');

        $retval = array();
        foreach ($inlineenrolments as $inlineenrolment) {
            $instance = $inlineenrolment->instance;
            $plugin = $inlineenrolment->plugin;
            $enrolform = $plugin->course_expand_get_form_hook($instance);

            $nameprefix = 'instanceid_' . $instance->id . '_';

            // Currently, course_expand_get_form_hook check if the user can self enrol before creating the form, if not, it will
            // return the result of the can_self_enrol function which could be false or a string.
            if (!$enrolform || is_string($enrolform)) {
                $retval[] = new HTML_QuickForm_static(null, null, $enrolform);
                continue;
            }

            if ($enrolform instanceof moodleform) {
                $form = \local_reportbuilder\form\formaccesshack::getform($enrolform);
                foreach ($form->_elements as $element) {
                    if ($element->_type == 'button' || $element->_type == 'submit') {
                        continue;
                    } else if ($element->_type == 'group') {
                        $newelements = array();
                        foreach ($element->getElements() as $subelement) {
                            if ($subelement->_type == 'button' || $subelement->_type == 'submit') {
                                continue;
                            }
                            $elementname = $subelement->getName();
                            $newelement  = $nameprefix . $elementname;
                            $subelement->setName($newelement);
                            if (!empty($form->_types[$elementname]) && $subelement instanceof MoodleQuickForm_hidden) {
                                $subelement->setType($form->_types[$elementname]);
                            }
                            $newelements[] = $subelement;
                        }
                        if (count($newelements)>0) {
                            $element->setElements($newelements);
                            $retval[] = $element;
                        }
                    } else {
                        $elementname = $element->getName();
                        $newelement  = $nameprefix . $elementname;
                        $element->setName($newelement);
                        if (!empty($form->_types[$elementname]) && $element instanceof MoodleQuickForm_hidden) {
                            $element->setType($form->_types[$elementname]);
                        }
                        $retval[] = $element;
                    }
                }
            }

            if (count($inlineenrolments) > 1) {
                $enrollabel = get_string('enrolusing', 'local_reportbuilder', $plugin->get_instance_name($instance->id));
            } else {
                $enrollabel = get_string('enrol', 'local_reportbuilder');
            }
            $name = $instance->id;

            $retval[] = new HTML_QuickForm_button($name, $enrollabel, array('class' => 'expandenrol'));
        }
        return $retval;
    }

    // convert a course name into a link to that course
    function rb_display_link_course($course, $row, $isexport = false) {
        global $CFG;
        require_once($CFG->dirroot . '/cohort/lib.php');

        if ($isexport) {
            return format_string($course);
        }

        $courseid = $row->course_id;
        $attr = array('class' => totara_get_style_visibility($row, 'course_visible'));
        $url = new moodle_url('/course/view.php', array('id' => $courseid));
        return html_writer::link($url, $course, $attr);
    }

    // convert a course name into a link to that course and shows
    // the course icon next to it
    function rb_display_link_course_icon($course, $row, $isexport = false) {
        global $CFG, $OUTPUT;
        require_once($CFG->dirroot . '/cohort/lib.php');

        if ($isexport) {
            return format_string($course);
        }

        $courseid = $row->course_id;
        $courseicon = !empty($row->course_icon) ? $row->course_icon : 'default';
        $cssclass = totara_get_style_visibility($row, 'course_visible');
        $icon = html_writer::empty_tag('img', array('src' => totara_get_icon($courseid, TOTARA_ICON_TYPE_COURSE),
            'class' => 'course_icon', 'alt' => ''));
        $link = $OUTPUT->action_link(
            new moodle_url('/course/view.php', array('id' => $courseid)),
            $icon . $course, null, array('class' => $cssclass)
        );
        return $link;
    }

    // display an icon based on the course icon field
    function rb_display_course_icon($icon, $row, $isexport = false) {
        if ($isexport) {
            return format_string($row->course_name);
        }

        $coursename = format_string($row->course_name);
        $courseicon = html_writer::empty_tag('img', array('src' => totara_get_icon($row->course_id, TOTARA_ICON_TYPE_COURSE),
            'class' => 'course_icon', 'alt' => $coursename));
        return $courseicon;
    }

    // convert a course category name into a link to that category's page
    function rb_display_link_course_category($category, $row, $isexport = false) {
        if ($isexport) {
            return format_string($category);
        }

        $catid = $row->cat_id;
        $category = format_string($category);
        if ($catid == 0 || !$catid) {
            return '';
        }
        $attr = (isset($row->cat_visible) && $row->cat_visible == 0) ? array('class' => 'dimmed') : array();
        $columns = array('coursecount' => 'course', 'programcount' => 'program', 'certifcount' => 'certification');
        foreach ($columns as $field => $viewtype) {
            if (isset($row->{$field})) {
                break;
            }
        }
        switch ($viewtype) {
            case 'program':
            case 'certification':
                $url = new moodle_url('/totara/program/index.php', array('categoryid' => $catid, 'viewtype' => $viewtype));
                break;
            default:
                $url = new moodle_url('/course/index.php', array('categoryid' => $catid));
                break;
        }
        return html_writer::link($url, $category, $attr);
    }


    public function rb_display_audience_visibility($visibility, $row, $isexport = false) {
        global $COHORT_VISIBILITY;

        if (!isset($COHORT_VISIBILITY[$visibility])) {
            return $visibility;
        }

        return $COHORT_VISIBILITY[$visibility];
    }

    function rb_display_yes_no($item, $row) {
        if ($item === null or $item === '') {
            return '';
        } else if ($item) {
            return get_string('yes');
        } else {
            return get_string('no');
        }
    }

    function rb_display_delimitedlist_yes_no($data, $row) {
        $delimiter = self::$uniquedelimiter;
        $items = explode($delimiter, $data);
        $output = array();
        foreach ($items as $item) {
            if (!isset($item) || $item === '' || $item === '-') {
                $output[] = '-';
            } else if ($item) {
                $output[] = get_string('yes');
            } else {
                $output[] = get_string('no');
            }
        }
        return implode($output, "\n");
    }

    /**
     * Display duration in human readable format
     * @param integer $seconds
     * @param stdClass $row
     */
    public function rb_display_duration($seconds, $row) {
        if (empty($seconds)) {
            return '';
        }
        return format_time($seconds);
    }

    /**
     * Convert an integer number of minutes into a
     * formatted duration (e.g. 90 mins => 1h 30m).
     *
     * @deprecated Since 9.0
     * @param $mins
     * @param $row
     * @return mixed
     */
    function rb_display_hours_minutes($mins, $row) {
        debugging('The hours_minutes report builder display function has been deprecated and replaced by local_reportbuilder\rb\display\duration_hours_minutes', DEBUG_DEVELOPER);
        return $mins;
    }

    // convert a 2 digit country code into the country name
    function rb_display_country_code($code, $row) {
        $countries = get_string_manager()->get_list_of_countries();

        if (isset($countries[$code])) {
            return $countries[$code];
        }
        return $code;
    }

    /**
     * Indicates if the user is deleted or not.
     *
     * @deprecated Since Totara 10.5.
     *
     * @param string $status User status value from the column data.
     * @param stdclass $row The data from the report row.
     * @return string Text denoting status.
     */
    function rb_display_deleted_status($status, $row) {
        debugging("The report builder display function 'deleted_status' has been deprecated. Please use 'user_status' instead.", DEBUG_DEVELOPER);

        switch($status) {
            case 1:
                return get_string('deleteduser', 'local_reportbuilder');
            case 2:
                return get_string('suspendeduser', 'local_reportbuilder');
            default:
                return get_string('activeuser', 'local_reportbuilder');
        }
    }

    /**
     * Column displayfunc to convert a language code to a human-readable string
     * @param $code Language code
     * @param $row data row - unused in this function
     * @return string
     */
    function rb_display_language_code($code, $row) {
            global $CFG;
        static $languages = array();
        $strmgr = get_string_manager();
        // Populate the static variable if empty
        if (count($languages) == 0) {
            // Return all languages available in system (adapted from stringmanager->get_list_of_translations()).
            $langdirs = get_list_of_plugins('', '', $CFG->langotherroot);
            $langdirs = array_merge($langdirs, array("{$CFG->dirroot}/lang/en"=>'en'));
            $curlang = current_language();
            // Loop through all langs and get info.
            foreach ($langdirs as $lang) {
                if (isset($languages[$lang])){
                    continue;
                }
                if (strstr($lang, '_local') !== false) {
                    continue;
                }
                if (strstr($lang, '_utf8') !== false) {
                    continue;
                }
                $string = $strmgr->load_component_strings('langconfig', $lang);
                if (!empty($string['thislanguage'])) {
                    $languages[$lang] = $string['thislanguage'];
                    // If not the current language, provide the English translation also.
                    if(strpos($lang, $curlang) === false) {
                        $languages[$lang] .= ' ('. $string['thislanguageint'] .')';
                    }
                }
                unset($string);
            }
        }

        if (empty($code)) {
            return get_string('notspecified', 'local_reportbuilder');
        }
        if (strpos($code, '_') !== false) {
            list($langcode, $langvariant) = explode('_', $code);
        } else {
            $langcode = $code;
        }

        // Now see if we have a match in "localname (English)" format.
        if (isset($languages[$code])) {
            return $languages[$code];
        } else {
            // Not an installed language - may have been uninstalled, as last resort try the get_list_of_languages silly function.
            $langcodes = $strmgr->get_list_of_languages();
            if (isset($langcodes[$langcode])) {
                $a = new stdClass();
                $a->code = $langcode;
                $a->name = $langcodes[$langcode];
                return get_string('uninstalledlanguage', 'local_reportbuilder', $a);
            } else {
                return get_string('unknownlanguage', 'local_reportbuilder', $code);
            }
        }
    }

    /**     *
     * @deprecated Since 10.2; replaced by local/reportbuilder/classes/rb/display/user_email class.
     */
    function rb_display_user_email($email, $row, $isexport = false) {
        debugging('rb_base_source::rb_display_user_email has been deprecated. Please use the local/reportbuilder/classes/rb/display/user_email class instead', DEBUG_DEVELOPER);
        if (empty($email)) {
            return '';
        }
        $maildisplay = $row->maildisplay;
        $emaildisabled = $row->emailstop;

        // respect users email privacy setting
        // at some point we may want to allow admins to view anyway
        if ($maildisplay != 1) {
            return get_string('useremailprivate', 'local_reportbuilder');
        }

        if ($isexport) {
            return $email;
        } else {
            // obfuscate email to avoid spam if printing to page
            return obfuscate_mailto($email, '', (bool) $emaildisabled);
        }
    }

    /**     *
     * @deprecated Since 10.2; replaced by local/reportbuilder/classes/rb/display/user_email_unobscured class.
     */
    public function rb_display_user_email_unobscured($email, $row, $isexport = false) {
        debugging('rb_base_source::rb_display_user_email_unobscured has been deprecated. Please use the local/reportbuilder/classes/rb/display/user_email_unobscured class instead', DEBUG_DEVELOPER);
        if ($isexport) {
            return $email;
        } else {
            // Obfuscate email to avoid spam if printing to page.
            return obfuscate_mailto($email);
        }
    }

    public function rb_display_orderedlist_to_newline_email($list, $row, $isexport = false) {
        $output = array();
        $emails = explode(self::$uniquedelimiter, $list);
        foreach ($emails as $email) {
            if ($isexport) {
                $output[] = $email;
            } else if ($email === '!private!') {
                $output[] = get_string('useremailprivate', 'local_reportbuilder');
            } else if ($email !== '-') {
                // Obfuscate email to avoid spam if printing to page.
                $output[] = obfuscate_mailto($email);
            } else {
                $output[] = '-';
            }
        }

        return implode($output, "\n");
    }

    /**
     * Generates the HTML to display the due/expiry date of a program/certification.
     *
     * @deprecated since 2.7 - use $this->usedcomponents[] = 'totara_program' and 'displayfunc' => 'programduedate' instead
     * @param int $time     The duedate of the program
     * @param record $row   The whole row, including some required fields
     * @return html
     */
    public function rb_display_certification_duedate($time, $row, $isexport = false) {
        // Get the necessary fields out of the row.
        $duedate = $time;
        $userid = $row->userid;
        $progid = $row->id;
        $status = $row->status;
        $certifpath = isset($row->certifpath) ? $row->certifpath : null;
        $certifstatus = isset($row->certifstatus) ? $row->certifstatus : null;

        return prog_display_duedate($duedate, $progid, $userid, $certifpath, $certifstatus, $status, $isexport);
    }

    // Display grade along with passing grade if it is known.
    function rb_display_grade_string($item, $row) {
        $passgrade = isset($row->gradepass) ? sprintf('%d', $row->gradepass) : null;

        $usergrade = (int)$item;
        $grademin = 0;
        $grademax = 100;
        if (isset($row->grademin)) {
            $grademin = $row->grademin;
        }
        if (isset($row->grademax)) {
            $grademax = $row->grademax;
        }

        $usergrade = sprintf('%.1f', ((($usergrade - $grademin) / ($grademax - $grademin)) * 100));

        if ($item === null or $item === '') {
            return '';
        } else if ($passgrade === null) {
            return "{$usergrade}%";
        } else {
            $a = new stdClass();
            $a->grade = $usergrade;
            $a->pass = sprintf('%.1f', ((($passgrade - $grademin) / ($grademax - $grademin)) * 100));
            return get_string('gradeandgradetocomplete', 'local_reportbuilder', $a);
        }
    }

    //
    //
    // Generic select filter methods
    //
    //

    function rb_filter_yesno_list() {
        $yn = array();
        $yn[1] = get_string('yes');
        $yn[0] = get_string('no');
        return $yn;
    }

    function rb_filter_modules_list() {
        global $DB, $OUTPUT, $CFG;

        $out = array();
        $mods = $DB->get_records('modules', array('visible' => 1), 'id', 'id, name');
        foreach ($mods as $mod) {
            if (get_string_manager()->string_exists('pluginname', $mod->name)) {
                $mod->localname = get_string('pluginname', $mod->name);
            }
        }

        core_collator::asort_objects_by_property($mods, 'localname');

        foreach ($mods as $mod) {
            if (file_exists($CFG->dirroot . '/mod/' . $mod->name . '/pix/icon.gif') ||
                file_exists($CFG->dirroot . '/mod/' . $mod->name . '/pix/icon.png')) {
                $icon = $OUTPUT->pix_icon('icon', $mod->localname, $mod->name) . '&nbsp;';
            } else {
                $icon = '';
            }

            $out[$mod->name] = $icon . $mod->localname;
        }
        return $out;
    }

    /**
     * @deprecated Since 10.0
     */
    function rb_filter_tags_list() {
        global $DB, $OUTPUT, $CFG;

        debugging('rb_filter_tags_list() is deprecated. This function was used in the tags filter and is no longer needed.', DEBUG_DEVELOPER);

        return $DB->get_records_menu('tag', array('isstandard' => 1), 'name', 'id, name');
    }

    function rb_filter_course_categories_list() {
        global $CFG;
        require_once($CFG->libdir . '/coursecatlib.php');
        $cats = coursecat::make_categories_list();

        return $cats;
    }

    function rb_filter_course_languages() {
        global $DB;
        $out = array();
        $langs = $DB->get_records_sql("SELECT DISTINCT lang
            FROM {course} ORDER BY lang");
        foreach ($langs as $row) {
            $out[$row->lang] = $this->rb_display_language_code($row->lang, array());
        }

        return $out;
    }

    //
    //
    // Generic grouping methods for aggregation
    //
    //

    function rb_group_count($field) {
        return "COUNT($field)";
    }

    function rb_group_unique_count($field) {
        return "COUNT(DISTINCT $field)";
    }

    function rb_group_sum($field) {
        return "SUM($field)";
    }

    function rb_group_average($field) {
        return "AVG($field)";
    }

    function rb_group_max($field) {
        return "MAX($field)";
    }

    function rb_group_min($field) {
        return "MIN($field)";
    }

    function rb_group_stddev($field) {
        return "STDDEV($field)";
    }

    // can be used to 'fake' a percentage, if matching values return 1 and
    // all other values return 0 or null
    function rb_group_percent($field) {
        global $DB;

        return $DB->sql_round("AVG($field*100.0)", 0);
    }

    /**
     * This function calls the databases native implementations of
     * group_concat where possible and requires an additional $orderby
     * variable. If you create another one you should add it to the
     * $sql_functions array() in the get_fields() function in the rb_columns class.
     *
     * @param string $field         The expression to use as the select
     * @param string $orderby       The comma deliminated fields to order by
     * @return string               The native sql for a group concat
     */
    function rb_group_sql_aggregate($field, $orderby) {
        global $DB;

        return \local_reportbuilder\dblib\base::getbdlib()->sql_group_concat($field, self::$uniquedelimiter, $orderby);
    }

    // return list as single field, separated by commas
    function rb_group_comma_list($field) {
        global $DB;

        return \local_reportbuilder\dblib\base::getbdlib()->sql_group_concat($field, ', ');
    }

    // Return list as single field, without a separator delimiter.
    function rb_group_list_nodelimiter($field) {
        global $DB;

        return \local_reportbuilder\dblib\base::getbdlib()->sql_group_concat($field, '');
    }

    // return unique list items as single field, separated by commas
    function rb_group_comma_list_unique($field) {
        global $DB;

        return \local_reportbuilder\dblib\base::getbdlib()->sql_group_concat_unique($field, ', ');
    }

    // return list as single field, one per line
    function rb_group_list($field) {
        global $DB;

        return \local_reportbuilder\dblib\base::getbdlib()->sql_group_concat($field, html_writer::empty_tag('br'));
    }

    // return unique list items as single field, one per line
    function rb_group_list_unique($field) {
        global $DB;

        return \local_reportbuilder\dblib\base::getbdlib()->sql_group_concat_unique($field, html_writer::empty_tag('br'));
    }

    // return list as single field, separated by a line with - on (in HTML)
    function rb_group_list_dash($field) {
        global $DB;

        return \local_reportbuilder\dblib\base::getbdlib()->sql_group_concat($field, html_writer::empty_tag('br') . '-' . html_writer::empty_tag('br'));
    }

    //
    //
    // Methods for adding commonly used data to source definitions
    //
    //

    //
    // Wrapper functions to add columns/fields/joins in one go
    //
    //

    /**
     * Returns true if global report restrictions can be used with this source.
     *
     * @return bool
     */
    protected function can_global_report_restrictions_be_used() {
        global $CFG;
        return (!empty($CFG->enableglobalrestrictions) && $this->global_restrictions_supported()
                && $this->globalrestrictionset);
    }

    /**
     * Returns global restriction SQL fragment that can be used in complex joins for example.
     *
     * @return string SQL fragment
     */
    protected function get_global_report_restriction_query() {
        // First ensure that global report restrictions can be used with this source.
        if (!$this->can_global_report_restrictions_be_used()) {
            return '';
        }

        list($query, $parameters) = $this->globalrestrictionset->get_join_query();

        if ($parameters) {
            $this->globalrestrictionparams = array_merge($this->globalrestrictionparams, $parameters);
        }

        return $query;
    }

    /**
     * Adds global restriction join to the report.
     *
     * @param string $join Name of the join that provides the 'user id' field
     * @param string $field Name of user id field to join on
     * @param mixed $dependencies join dependencies
     * @return bool
     */
    protected function add_global_report_restriction_join($join, $field, $dependencies = 'base') {
        // First ensure that global report restrictions can be used with this source.
        if (!$this->can_global_report_restrictions_be_used()) {
            return false;
        }

        list($query, $parameters) = $this->globalrestrictionset->get_join_query();

        if ($query === '') {
            return false;
        }

        static $counter = 0;
        $counter++;
        $joinname = 'globalrestrjoin_' . $counter;

        $this->globalrestrictionjoins[] = new rb_join(
            $joinname,
            'INNER',
            "($query)",
            "$joinname.id = $join.$field",
            REPORT_BUILDER_RELATION_ONE_TO_MANY,
            $dependencies
        );

        if ($parameters) {
            $this->globalrestrictionparams = array_merge($this->globalrestrictionparams, $parameters);
        }

        return true;
    }

    /**
     * Get global restriction join SQL to the report. All parameters will be inline.
     *
     * @param string $join Name of the join that provides the 'user id' field
     * @param string $field Name of user id field to join on
     * @return string
     */
    protected function get_global_report_restriction_join($join, $field) {
        // First ensure that global report restrictions can be used with this source.
        if (!$this->can_global_report_restrictions_be_used()) {
            return  '';
        }

        list($query, $parameters) = $this->globalrestrictionset->get_join_query();

        if (empty($query)) {
            return '';
        }

        if ($parameters) {
            $this->globalrestrictionparams = array_merge($this->globalrestrictionparams, $parameters);
        }

        static $counter = 0;
        $counter++;
        $joinname = 'globalinlinerestrjoin_' . $counter;

        $joinsql = " INNER JOIN ($query) $joinname ON ($joinname.id = $join.$field) ";
        return $joinsql;
    }

    /**
     * Adds the user table to the $joinlist array
     *
     * @param array &$joinlist Array of current join options
     *                         Passed by reference and updated to
     *                         include new table joins
     * @param string $join Name of the join that provides the
     *                     'user id' field
     * @param string $field Name of user id field to join on
     * @param string $alias Use custom user table alias
     * @return boolean True
     */
    protected function add_user_table_to_joinlist(&$joinlist, $join, $field, $alias = 'auser') {
        if (isset($this->addeduserjoins[$alias])) {
            debugging("User join '{$alias}' was already added to the source", DEBUG_DEVELOPER);
        } else {
            $this->addeduserjoins[$alias] = array('join' => $join);
        }

        // join uses 'auser' as name because 'user' is a reserved keyword
        $joinlist[] = new rb_join(
            $alias,
            'LEFT',
            '{user}',
            "{$alias}.id = $join.$field",
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            $join
        );

        // join uses 'auser' as name because 'user' is a reserved keyword
        $joinlist[] = new rb_join(
            $alias.'staff',
            'INNER',
            'SQLHUB.ARUP_ALL_STAFF_V',
            "{$alias}staff.LEAVER_FLAG = 'n' AND {$alias}staff.EMPLOYEE_NUMBER = $alias.idnumber AND $alias.deleted = 0",
                REPORT_BUILDER_RELATION_ONE_TO_MANY,
                $alias
        );

        // Add cohort tables
        $this->add_cohort_user_tables_to_joinlist($joinlist, $join, $field, $alias.'cohort');

        return true;
    }

    /**
     * Adds the user table to the $joinlist array
     *
     * @param array &$joinlist Array of current join options
     *                         Passed by reference and updated to
     *                         include new table joins
     * @param string $join Name of the join that provides the
     *                     'user id' field
     * @param string $field Name of user id field to join on
     * @param string $alias Use custom user table alias
     * @return boolean True
     */
    protected function add_user_table_to_joinlist_on_idnumber(&$joinlist, $join, $field, $alias = 'auser') {
        if (isset($this->addeduserjoins[$alias])) {
            debugging("User join '{$alias}' was already added to the source", DEBUG_DEVELOPER);
        } else {
            $this->addeduserjoins[$alias] = array('join' => $join);
        }

        // join uses 'auser' as name because 'user' is a reserved keyword
        $joinlist[] = new rb_join(
                "{$alias}staff",
                'INNER',
                'SQLHUB.ARUP_ALL_STAFF_V',
                "{$alias}staff.LEAVER_FLAG = 'n' AND {$alias}staff.EMPLOYEE_NUMBER = $join.$field",
                REPORT_BUILDER_RELATION_ONE_TO_MANY,
                $alias
        );

        // join uses 'auser' as name because 'user' is a reserved keyword
        $joinlist[] = new rb_join(
            $alias,
            'LEFT',
            '{user}',
            "{$alias}.idnumber = $join.$field",
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            $join
        );

        // Add cohort tables
        $this->add_cohort_user_tables_to_joinlist($joinlist, $join, $field, $alias.'cohort');

        return true;
    }


    /**
     * Adds some common user field to the $columnoptions array
     *
     * @param array &$columnoptions Array of current column options
     *                              Passed by reference and updated by
     *                              this method
     * @param string $join Name of the join that provides the 'user' table
     * @param string $groupname The group to add fields to. If you are defining
     *                          a custom group name, you must define a language
     *                          string with the key "type_{$groupname}" in your
     *                          report source language file.
     * @param boolean $$addtypetoheading Add the column type to the column heading
     *                          to differentiate between fields with the same name.
     *
     * @return True
     */
    protected function add_user_fields_to_columns(&$columnoptions,
        $join='auser', $groupname = 'user', $addtypetoheading = false) {
        global $DB, $CFG;

        if ($join === 'base' and !isset($this->addeduserjoins['base'])) {
            $this->addeduserjoins['base'] = array('join' => 'base');
        }

        if (!isset($this->addeduserjoins[$join])) {
            debugging("Add user join '{$join}' via add_user_table_to_joinlist() before calling add_user_fields_to_columns()", DEBUG_DEVELOPER);
        } else if (isset($this->addeduserjoins[$join]['groupname'])) {
            debugging("User columns for {$join} were already added to the source", DEBUG_DEVELOPER);
        } else {
            $this->addeduserjoins[$join]['groupname'] = $groupname;
            $this->addeduserjoins[$join]['addtypetoheading'] = $addtypetoheading;
        }

        $usednamefields = totara_get_all_user_name_fields_join($join);
        $allnamefields = totara_get_all_user_name_fields_join($join);

        $columnoptions[] = new rb_column_option(
                $groupname,
                'fullname',
                get_string('userfullname', 'local_reportbuilder'),
                $DB->sql_concat_join("' '", $usednamefields),
                array('joins' => $join,
                      'dbdatatype' => 'char',
                      'outputformat' => 'text',
                      'extrafields' => $allnamefields,
                      'displayfunc' => 'user',
                      'addtypetoheading' => $addtypetoheading
                )
        );
        $columnoptions[] = new rb_column_option(
            $groupname,
            'namelink',
            get_string('usernamelink', 'local_reportbuilder'),
                $DB->sql_concat_join("' '", $usednamefields),
            array(
                'joins' => $join,
                'displayfunc' => 'link_user',
                'defaultheading' => get_string('userfullname', 'local_reportbuilder'),
                'extrafields' => array_merge(array('id' => "$join.id"), $allnamefields),
                'addtypetoheading' => $addtypetoheading
            )
        );
        $columnoptions[] = new rb_column_option(
            $groupname,
            'namelinkicon',
            get_string('usernamelinkicon', 'local_reportbuilder'),
                $DB->sql_concat_join("' '", $usednamefields),
            array(
                'joins' => $join,
                'displayfunc' => 'link_user_icon',
                'defaultheading' => get_string('userfullname', 'local_reportbuilder'),
                'extrafields' => array_merge(array('id' => "$join.id",
                                                   'picture' => "$join.picture",
                                                   'imagealt' => "$join.imagealt",
                                                   'email' => "$join.email"),
                                             $allnamefields),
                'style' => array('white-space' => 'nowrap'),
                'addtypetoheading' => $addtypetoheading
            )
        );
        $columnoptions[] = new rb_column_option(
            $groupname,
            'email',
            get_string('useremail', 'local_reportbuilder'),
            // use CASE to include/exclude email in SQL
            // so search won't reveal hidden results
                "CASE WHEN $join.maildisplay <> 1 THEN '-' ELSE $join.email END",
            array(
                'joins' => $join,
                'displayfunc' => 'user_email',
                'extrafields' => array(
                    'emailstop' => "$join.emailstop",
                    'maildisplay' => "$join.maildisplay",
                ),
                'dbdatatype' => 'char',
                'outputformat' => 'text',
                'addtypetoheading' => $addtypetoheading
            )
        );
        // Only include this column if email is among fields allowed by showuseridentity setting or
        // if the current user has the 'moodle/site:config' capability.
        $canview = !empty($CFG->showuseridentity) && in_array('email', explode(',', $CFG->showuseridentity));
        $canview |= has_capability('moodle/site:config', context_system::instance());
        if ($canview) {
            $columnoptions[] = new rb_column_option(
                $groupname,
                'emailunobscured',
                get_string('useremailunobscured', 'local_reportbuilder'),
                "$join.email",
                array(
                    'joins' => $join,
                    'displayfunc' => 'user_email_unobscured',
                    // Users must have viewuseridentity to see the
                    // unobscured email address.
                    'capability' => 'moodle/site:viewuseridentity',
                    'dbdatatype' => 'char',
                    'outputformat' => 'text',
                    'addtypetoheading' => $addtypetoheading
                )
            );
        }
        $columnoptions[] = new rb_column_option(
            $groupname,
            'lastlogin',
            get_string('userlastlogin', 'local_reportbuilder'),
            // See MDL-22481 for why currentlogin is used instead of lastlogin.
            "$join.currentlogin",
            array(
                'joins' => $join,
                'displayfunc' => 'nice_date',
                'dbdatatype' => 'timestamp',
                'addtypetoheading' => $addtypetoheading
            )
        );
        $columnoptions[] = new rb_column_option(
            $groupname,
            'lastloginrelative',
            get_string('userlastloginrelative', 'local_reportbuilder'),
            // See MDL-22481 for why currentlogin is used instead of lastlogin.
            "$join.currentlogin",
            array(
                'joins' => $join,
                'displayfunc' => 'relative_time_text',
                'dbdatatype' => 'timestamp',
                'addtypetoheading' => $addtypetoheading
            )
        );
        $columnoptions[] = new rb_column_option(
            $groupname,
            'firstaccess',
            get_string('userfirstaccess', 'local_reportbuilder'),
            "$join.firstaccess",
            array(
                'joins' => $join,
                'displayfunc' => 'nice_datetime',
                'dbdatatype' => 'timestamp',
                'addtypetoheading' => $addtypetoheading
            )
        );
        $columnoptions[] = new rb_column_option(
            $groupname,
            'firstaccessrelative',
            get_string('userfirstaccessrelative', 'local_reportbuilder'),
            "$join.firstaccess",
            array(
                'joins' => $join,
                'displayfunc' => 'relative_time_text',
                'dbdatatype' => 'timestamp',
                'addtypetoheading' => $addtypetoheading
            )
        );
        $columnoptions[] = new rb_column_option(
            $groupname,
            'lang',
            get_string('userlang', 'local_reportbuilder'),
            "$join.lang",
            array(
                'joins' => $join,
                'displayfunc' => 'language_code',
                'addtypetoheading' => $addtypetoheading
            )
        );
        // auto-generate columns for user fields
        $fields = array(
            'firstname' => get_string('userfirstname', 'local_reportbuilder'),
            'firstnamephonetic' => get_string('userfirstnamephonetic', 'local_reportbuilder'),
            'middlename' => get_string('usermiddlename', 'local_reportbuilder'),
            'lastname' => get_string('userlastname', 'local_reportbuilder'),
            'lastnamephonetic' => get_string('userlastnamephonetic', 'local_reportbuilder'),
            'alternatename' => get_string('useralternatename', 'local_reportbuilder'),
            'username' => get_string('username', 'local_reportbuilder'),
            'phone1' => get_string('userphone', 'local_reportbuilder'),
            'institution' => get_string('userinstitution', 'local_reportbuilder'),
            'department' => get_string('userdepartment', 'local_reportbuilder'),
            'address' => get_string('useraddress', 'local_reportbuilder'),
            'city' => get_string('usercity', 'local_reportbuilder'),
        );
        foreach ($fields as $field => $name) {
            $columnoptions[] = new rb_column_option(
                $groupname,
                $field,
                $name,
                "$join.$field",
                array('joins' => $join,
                      'displayfunc' => 'plaintext',
                      'dbdatatype' => 'char',
                      'outputformat' => 'text',
                      'addtypetoheading' => $addtypetoheading
                )
            );
        }

        $columnoptions[] = new rb_column_option(
            $groupname,
            'idnumber',
            get_string('useridnumber', 'local_reportbuilder'),
            "$join.idnumber",
            array('joins' => $join,
                'displayfunc' => 'plaintext',
                'dbdatatype' => 'char',
                'outputformat' => 'text')
        );

        $columnoptions[] = new rb_column_option(
            $groupname,
            'id',
            get_string('userid', 'local_reportbuilder'),
            "$join.id",
            array('joins' => $join,
                  'addtypetoheading' => $addtypetoheading
            )
        );

        // add country option
        $columnoptions[] = new rb_column_option(
            $groupname,
            'country',
            get_string('usercountry', 'local_reportbuilder'),
            "$join.country",
            array(
                'joins' => $join,
                'displayfunc' => 'country_code',
                'addtypetoheading' => $addtypetoheading
            )
        );

        // add auth option
        $columnoptions[] = new rb_column_option(
            $groupname,
            'auth',
            get_string('userauth', 'local_reportbuilder'),
            "$join.auth",
            array(
                'joins' => $join,
                'displayfunc' => 'user_auth_method',
                'addtypetohead' => $addtypetoheading
            )
        );

        // add deleted option
        $columnoptions[] = new rb_column_option(
            $groupname,
            'deleted',
            get_string('userstatus', 'local_reportbuilder'),
            "CASE WHEN $join.deleted = 0 AND $join.suspended = 0 AND $join.confirmed = 1 THEN 0
                WHEN $join.deleted = 1 THEN 1
                WHEN $join.suspended = 1 THEN 2
                WHEN $join.confirmed = 0 THEN 3
                ELSE 0
            END",
            array(
                'joins' => $join,
                'displayfunc' => 'user_status',
                'addtypetoheading' => $addtypetoheading
            )
        );
        $columnoptions[] = new rb_column_option(
            $groupname,
            'timecreated',
            get_string('usertimecreated', 'local_reportbuilder'),
            "$join.timecreated",
            array(
                'joins' => $join,
                'displayfunc' => 'nice_datetime',
                'dbdatatype' => 'timestamp',
                'addtypetoheading' => $addtypetoheading
            )
        );
        $columnoptions[] = new rb_column_option(
            $groupname,
            'timemodified',
            get_string('usertimemodified', 'local_reportbuilder'),
            // Check whether the user record has been updated since it was created.
            // The timecreated field is 0 for guest and admin accounts, so this guest
            // username can be used to identify them. The site admin's username can
            // be changed so this can't be relied upon.
            "CASE WHEN {$join}.username = 'guest' AND {$join}.timecreated = 0 THEN 0
                  WHEN {$join}.username != 'guest' AND {$join}.timecreated = 0 AND {$join}.firstaccess < {$join}.timemodified THEN {$join}.timemodified
                  WHEN {$join}.timecreated != 0 AND {$join}.timecreated < {$join}.timemodified THEN {$join}.timemodified
                  ELSE 0 END",
            array(
                'joins' => $join,
                'displayfunc' => 'nice_datetime',
                'dbdatatype' => 'timestamp',
                'addtypetoheading' => $addtypetoheading
            )
        );

        $this->add_cohort_user_fields_to_columns($columnoptions, $join.'cohort', $groupname);

        return true;
    }

    protected function add_staff_details_to_columns(&$columnoptions,
            $join='auserstaff', $groupname = 'arupstaff', $addtypetoheading = false) {

        $fields = ['FIRST_NAME',
                'MIDDLE_NAMES',
                'LAST_NAME',
                'KNOWN_AS',
                'FULL_NAME',
                'EMAIL_ADDRESS',
                'INTERNAL_LOCATION',
                'LATEST_HIRE_DATE',
                'GRADE',
                'CORE_JOB_TITLE',
                'GEO_REGION',
                'COMPANY_CODE',
                'CENTRE_CODE',
                'CENTRE_NAME',
                'COMPANYCENTREARUPUNIT',
                'EMPLOYMENT_CATEGORY',
                'ASSIGNMENT_STATUS',
                'REGION_NAME',
                'LOCATION_NAME',
                'LEAVER_FLAG',
                'ACTUAL_TERMINATION_DATE',
                'DISCIPLINE_CODE',
                'DISCIPLINE_NAME',
                'GROUP_CODE',
                'GROUP_NAME'];

        foreach ($fields as $field) {
            $columnoptions[] = new rb_column_option(
                    $groupname,
                    $field,
                    get_string($field, 'local_reportbuilder'),
                    "{$join}.$field",
                    array('joins' => "{$join}",
                          'displayfunc' => 'plaintext',
                          'dbdatatype' => 'char',
                          'outputformat' => 'text',
                          'addtypetoheading' => $addtypetoheading
                    )
            );
        }
        $columnoptions[] = new rb_column_option(
                $groupname,
                'EMPLOYEE_NUMBER',
                get_string('EMPLOYEE_NUMBER', 'local_reportbuilder'),
                "{$join}.EMPLOYEE_NUMBER",
                array('joins' => "{$join}",
                      'displayfunc' => 'plaintext',
                      'dbdatatype' => 'int',
                      'outputformat' => 'text',
                      'addtypetoheading' => $addtypetoheading
                )
        );
        $columnoptions[] = new rb_column_option(
                $groupname,
                'SUP_EMPLOYEE_NUMBER',
                get_string('SUP_EMPLOYEE_NUMBER', 'local_reportbuilder'),
                "{$join}.SUP_EMPLOYEE_NUMBER",
                array('joins' => "{$join}",
                      'displayfunc' => 'plaintext',
                      'dbdatatype' => 'int',
                      'outputformat' => 'text',
                      'addtypetoheading' => $addtypetoheading
                )
        );
    }


    /**
     * Adds some common user field to the $filteroptions array
     *
     * @param array &$filteroptions Array of current filter options
     *                              Passed by reference and updated by
     *                              this method
     * @param string $groupname Name of group to filter. If you are defining
     *                          a custom group name, you must define a language
     *                          string with the key "type_{$groupname}" in your
     *                          report source language file.
     * @return True
     */
    protected function add_staff_fields_to_filters(&$filteroptions, $groupname = 'user', $addtypetoheading = false, $join='auserstaff') {

        $select_width_options = rb_filter_option::select_width_limiter();

        $filteroptions[] = new rb_filter_option(
                $groupname,
                'region',
                get_string('REGION_NAME', 'local_reportbuilder'),
                "select",
                array(
                        'selectchoices' => $this->rb_filter_staffcolumn('REGION_NAME'),
                        'attributes' => $select_width_options
                ),
                "{$join}.REGION_NAME",
                $join
        );

        $filteroptions[] = new rb_filter_option(
                $groupname,
                'georegion',
                get_string('GEO_REGION', 'local_reportbuilder'),
                "select",
                array(
                        'selectchoices' => $this->rb_filter_staffcolumn('GEO_REGION'),
                        'attributes' => $select_width_options
                ),
                "{$join}.GEO_REGION",
                $join
        );

        $filteroptions[] = new rb_filter_option(
                $groupname,
                'location',
                get_string('LOCATION_NAME', 'local_reportbuilder'),
                "select",
                array(
                        'selectchoices' => $this->rb_filter_staffcolumn('LOCATION_NAME'),
                        'attributes' => $select_width_options
                ),
                "{$join}.LOCATION_NAME",
                $join
        );

        $filteroptions[] = new rb_filter_option(
                $groupname,
                'groupname',
                get_string('GROUP_NAME', 'local_reportbuilder'),
                "select",
                array(
                        'selectchoices' => $this->rb_filter_staffcolumn('GROUP_NAME'),
                        'attributes' => $select_width_options
                ),
                "{$join}.GROUP_NAME",
                $join
        );

        $filteroptions[] = new rb_filter_option(
                $groupname,
                'centrename',
                get_string('CENTRE_NAME', 'local_reportbuilder'),
                "select",
                array(
                        'selectchoices' => $this->rb_filter_staffcolumn('CENTRE_NAME'),
                        'attributes' => $select_width_options
                ),
                "{$join}.CENTRE_NAME",
                $join
        );

        $filteroptions[] = new rb_filter_option(
                $groupname,
                'centrecode',
                get_string('CENTRE_CODE', 'local_reportbuilder'),
                "select",
                array(
                        'selectchoices' => $this->rb_filter_staffcolumn('CENTRE_CODE'),
                        'attributes' => $select_width_options
                ),
                "{$join}.CENTRE_CODE",
                $join
        );

        $filteroptions[] = new rb_filter_option(
                $groupname,
                'companycode',
                get_string('COMPANY_CODE', 'local_reportbuilder'),
                "select",
                array(
                        'selectchoices' => $this->rb_filter_staffcolumn('COMPANY_CODE'),
                        'attributes' => $select_width_options
                ),
                "{$join}.COMPANY_CODE",
                $join
        );

        $fields = array(
                'FIRST_NAME' => get_string('FIRST_NAME', 'local_reportbuilder'),
                'LAST_NAME' => get_string('LAST_NAME', 'local_reportbuilder'),
                'EMAIL_ADDRESS' => get_string('EMAIL_ADDRESS', 'local_reportbuilder'),
        );

        foreach ($fields as $field => $name) {
            $filteroptions[] = new rb_filter_option(
                    $groupname,
                    $field,
                    $name,
                    'text',
                    array('addtypetoheading' => $addtypetoheading),
                    "{$join}.$field",
                    $join
            );
        }

        $filteroptions[] = new rb_filter_option(
                $groupname,
                'EMPLOYEE_NUMBER',
                get_string('EMPLOYEE_NUMBER', 'local_reportbuilder'),
                'number',
                array('addtypetoheading' => $addtypetoheading),
                "{$join}.EMPLOYEE_NUMBER",
                $join
        );

        $filteroptions[] = new rb_filter_option(
                $groupname,
                'SUP_EMPLOYEE_NUMBER',
                get_string('SUP_EMPLOYEE_NUMBER', 'local_reportbuilder'),
                'number',
                array('addtypetoheading' => $addtypetoheading),
                "{$join}.SUP_EMPLOYEE_NUMBER",
                $join
        );
        // zero pad staff id
    }


    /**
     * Adds some common user field to the $filteroptions array
     *
     * @param array &$filteroptions Array of current filter options
     *                              Passed by reference and updated by
     *                              this method
     * @param string $groupname Name of group to filter. If you are defining
     *                          a custom group name, you must define a language
     *                          string with the key "type_{$groupname}" in your
     *                          report source language file.
     * @return True
     */
    protected function add_user_fields_to_filters(&$filteroptions, $groupname = 'user', $addtypetoheading = false) {
        global $CFG;

        $found = false;
        foreach ($this->addeduserjoins as $join => $unused) {
            if (!isset($this->addeduserjoins[$join]['groupname'])) {
                continue;
            }
            if ($this->addeduserjoins[$join]['groupname'] === $groupname) {
                $this->addeduserjoins[$join]['filters'] = true;
                $found = true;
                break;
            }
        }
        if (!$found) {
            debugging("Add user join with group name '{$groupname}' via add_user_table_to_joinlist() before calling add_user_fields_to_filters()", DEBUG_DEVELOPER);
        }

        // auto-generate filters for user fields
        $fields = array(
            'fullname' => get_string('userfullname', 'local_reportbuilder'),
            'firstname' => get_string('userfirstname', 'local_reportbuilder'),
            'firstnamephonetic' => get_string('userfirstnamephonetic', 'local_reportbuilder'),
            'middlename' => get_string('usermiddlename', 'local_reportbuilder'),
            'lastname' => get_string('userlastname', 'local_reportbuilder'),
            'lastnamephonetic' => get_string('userlastnamephonetic', 'local_reportbuilder'),
            'alternatename' => get_string('useralternatename', 'local_reportbuilder'),
            'username' => get_string('username'),
            'idnumber' => get_string('useridnumber', 'local_reportbuilder'),
            'phone1' => get_string('userphone', 'local_reportbuilder'),
            'institution' => get_string('userinstitution', 'local_reportbuilder'),
            'department' => get_string('userdepartment', 'local_reportbuilder'),
            'address' => get_string('useraddress', 'local_reportbuilder'),
            'city' => get_string('usercity', 'local_reportbuilder'),
            'email' => get_string('useremail', 'local_reportbuilder'),
        );
        // Only include this filter if email is among fields allowed by showuseridentity setting or
        // if the current user has the 'moodle/site:config' capability.
        $canview = !empty($CFG->showuseridentity) && in_array('email', explode(',', $CFG->showuseridentity));
        $canview |= has_capability('moodle/site:config', context_system::instance());
        if ($canview) {
            $fields['emailunobscured'] = get_string('useremailunobscured', 'local_reportbuilder');
        }

        foreach ($fields as $field => $name) {
            $filteroptions[] = new rb_filter_option(
                $groupname,
                $field,
                $name,
                'text',
                array('addtypetoheading' => $addtypetoheading)
            );
        }

        // pulldown with list of countries
        $select_width_options = rb_filter_option::select_width_limiter();
        $filteroptions[] = new rb_filter_option(
            $groupname,
            'country',
            get_string('usercountry', 'local_reportbuilder'),
            'select',
            array(
                'selectchoices' => get_string_manager()->get_list_of_countries(),
                'attributes' => $select_width_options,
                'simplemode' => true,
                'addtypetoheading' => $addtypetoheading
            )
        );

        $filteroptions[] = new rb_filter_option(
            $groupname,
            'auth',
            get_string('userauth', 'local_reportbuilder'),
            "select",
            array(
                'selectchoices' => $this->rb_filter_auth_options(),
                'attributes' => $select_width_options,
                'addtypetoheading' => $addtypetoheading
            )
        );

        $filteroptions[] = new rb_filter_option(
            $groupname,
            'deleted',
            get_string('userstatus', 'local_reportbuilder'),
            'select',
            array(
                'selectchoices' => array(0 => get_string('activeonly', 'local_reportbuilder'),
                    1 => get_string('deletedonly', 'local_reportbuilder'),
                    2 => get_string('suspendedonly', 'local_reportbuilder'),
                    3 => get_string('unconfirmedonly', 'local_reportbuilder')
                ),
                'attributes' => $select_width_options,
                'simplemode' => true,
                'addtypetoheading' => $addtypetoheading
            )
        );

        $filteroptions[] = new rb_filter_option(
            $groupname,
            'lastlogin',
            get_string('userlastlogin', 'local_reportbuilder'),
            'date',
            array(
                'addtypetoheading' => $addtypetoheading
            )
        );

        $filteroptions[] = new rb_filter_option(
            $groupname,
            'lastloginrelative',
            get_string('userlastloginrelative', 'local_reportbuilder'),
            'date',
            array(
                'includetime' => true,
                'includenotset' => true,
                'addtypetoheading' => $addtypetoheading
            )
        );

        $filteroptions[] = new rb_filter_option(
            $groupname,
            'firstaccess',
            get_string('userfirstaccess', 'local_reportbuilder'),
            'date',
            array(
                'includetime' => true,
                'includenotset' => true,
                'addtypetoheading' => $addtypetoheading
            )
        );

        $filteroptions[] = new rb_filter_option(
            $groupname,
            'firstaccessrelative',
            get_string('userfirstaccessrelative', 'local_reportbuilder'),
            'date',
            array(
                'includetime' => true,
                'includenotset' => true,
                'addtypetoheading' => $addtypetoheading
            )
        );

        $filteroptions[] = new rb_filter_option(
            $groupname,
            'timecreated',
            get_string('usertimecreated', 'local_reportbuilder'),
            'date',
            array(
                'includetime' => true,
                'addtypetoheading' => $addtypetoheading
            )
        );

        $filteroptions[] = new rb_filter_option(
            $groupname,
            'timemodified',
            get_string('usertimemodified', 'local_reportbuilder'),
            'date',
            array(
                'includetime' => true,
                'includenotset' => true,
                'addtypetoheading' => $addtypetoheading
            )
        );

        $filteroptions[] = new rb_filter_option(
            $groupname,
            'totarasync',
            get_string('totarasyncenableduser', 'local_reportbuilder'),
            'select',
            array(
                'selectchoices' => array(0 => get_string('no'), 1 => get_string('yes')),
                'simplemode' => true,
                'addtypetoheading' => $addtypetoheading
            )
        );

        $this->add_cohort_user_fields_to_filters($filteroptions, $groupname);

        return true;
    }

    public function rb_filter_auth_options() {
        $authoptions = array();

        $auths = core_component::get_plugin_list('auth');

        foreach ($auths as $auth => $something) {
            $authinst = get_auth_plugin($auth);

            $authoptions[$auth] = get_string('pluginname', "auth_{$auth}");
        }

        return $authoptions;
    }

    public function rb_filter_staffcolumn($columnname) {
        global $DB;

        $val = $DB->get_records_sql("select distinct($columnname) from SQLHUB.ARUP_ALL_STAFF_V where $columnname IS NOT NULL AND $columnname <> '' ORDER BY $columnname");
        return array_combine(array_keys($val), array_keys($val));
    }

    /**
     * Adds the course table to the $joinlist array
     *
     * @param array &$joinlist Array of current join options
     *                         Passed by reference and updated to
     *                         include new table joins
     * @param string $join Name of the join that provides the
     *                     'course id' field
     * @param string $field Name of course id field to join on
     * @param string $jointype Type of Join (INNER, LEFT, RIGHT)
     * @return boolean True
     */
    protected function add_course_table_to_joinlist(&$joinlist, $join, $field, $jointype = 'LEFT') {

        $joinlist[] = new rb_join(
            'course',
            $jointype,
            '{course}',
            "course.id = $join.$field",
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            $join
        );
    }

    /**
     * Adds the course table to the $joinlist array
     *
     * @param array &$joinlist Array of current join options
     *                         Passed by reference and updated to
     *                         include new table joins
     * @param string $join Name of the join that provides the
     *                     'course id' field
     * @param string $field Name of course id field to join on
     * @param int $contextlevel Name of course id field to join on
     * @param string $jointype Type of join (INNER, LEFT, RIGHT)
     * @return boolean True
     */
    protected function add_context_table_to_joinlist(&$joinlist, $join, $field, $contextlevel, $jointype = 'LEFT') {

        $joinlist[] = new rb_join(
            'ctx',
            $jointype,
            '{context}',
            "ctx.instanceid = $join.$field AND ctx.contextlevel = $contextlevel",
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            $join
        );
    }


    /**
     * Adds some common course info to the $columnoptions array
     *
     * @param array &$columnoptions Array of current column options
     *                              Passed by reference and updated by
     *                              this method
     * @param string $join Name of the join that provides the 'course' table
     *
     * @return True
     */
    protected function add_course_fields_to_columns(&$columnoptions, $join='course') {
        global $DB;

        $columnoptions[] = new rb_column_option(
            'course',
            'fullname',
            get_string('coursename', 'local_reportbuilder'),
            "$join.fullname",
            array('joins' => $join,
                  'dbdatatype' => 'char',
                  'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
            'course',
            'courselink',
            get_string('coursenamelinked', 'local_reportbuilder'),
            "$join.fullname",
            array(
                'joins' => $join,
                'displayfunc' => 'link_course',
                'defaultheading' => get_string('coursename', 'local_reportbuilder'),
                'extrafields' => array('course_id' => "$join.id",
                                       'course_visible' => "$join.visible")
            )
        );
        $columnoptions[] = new rb_column_option(
            'course',
            'courseexpandlink',
            get_string('courseexpandlink', 'local_reportbuilder'),
            "$join.fullname",
            array(
                'joins' => $join,
                'displayfunc' => 'course_expand',
                'defaultheading' => get_string('coursename', 'local_reportbuilder'),
                'extrafields' => array(
                    'course_id' => "$join.id",
                    'course_visible' => "$join.visible"
                )
            )
        );
        $coursevisiblestring = get_string('coursevisible', 'local_reportbuilder');
        $columnoptions[] = new rb_column_option(
            'course',
            'visible',
            $coursevisiblestring,
            "$join.visible",
            array(
                'joins' => $join,
                'displayfunc' => 'yes_no'
            )
        );
        $columnoptions[] = new rb_column_option(
            'course',
            'shortname',
            get_string('courseshortname', 'local_reportbuilder'),
            "$join.shortname",
            array('joins' => $join,
                  'dbdatatype' => 'char',
                  'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
            'course',
            'idnumber',
            get_string('courseidnumber', 'local_reportbuilder'),
            "$join.idnumber",
            array('joins' => $join,
                  'displayfunc' => 'plaintext',
                  'dbdatatype' => 'char',
                  'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
            'course',
            'id',
            get_string('courseid', 'local_reportbuilder'),
            "$join.id",
            array('joins' => $join)
        );
        $columnoptions[] = new rb_column_option(
            'course',
            'timecreated',
            get_string('coursedatecreated', 'local_reportbuilder'),
            "$join.timecreated",
            array(
                'joins' => $join,
                'displayfunc' => 'nice_date',
                'dbdatatype' => 'timestamp'
            )
        );
        $columnoptions[] = new rb_column_option(
            'course',
            'startdate',
            get_string('coursestartdate', 'local_reportbuilder'),
            "$join.startdate",
            array(
                'joins' => $join,
                'displayfunc' => 'nice_date',
                'dbdatatype' => 'timestamp'
            )
        );
        $columnoptions[] = new rb_column_option(
            'course',
            'name_and_summary',
            get_string('coursenameandsummary', 'local_reportbuilder'),
            // Case used to merge even if one value is null.
            "CASE WHEN $join.fullname IS NULL THEN $join.summary
                WHEN $join.summary IS NULL THEN $join.fullname
                ELSE " . $DB->sql_concat("$join.fullname", "'" . html_writer::empty_tag('br') . "'",
                    "$join.summary") . ' END',
            array(
                'joins' => $join,
                'displayfunc' => 'editor_textarea',
                'extrafields' => array(
                    'filearea' => '\'summary\'',
                    'component' => '\'course\'',
                    'context' => '\'context_course\'',
                    'recordid' => "$join.id"
                )
            )
        );
        $columnoptions[] = new rb_column_option(
            'course',
            'summary',
            get_string('coursesummary', 'local_reportbuilder'),
            "$join.summary",
            array(
                'joins' => $join,
                'displayfunc' => 'editor_textarea',
                'extrafields' => array(
                    'format' => "$join.summaryformat",
                    'filearea' => '\'summary\'',
                    'component' => '\'course\'',
                    'context' => '\'context_course\'',
                    'recordid' => "$join.id"
                ),
                'dbdatatype' => 'text',
                'outputformat' => 'text'
            )
        );
        // add language option
        $columnoptions[] = new rb_column_option(
            'course',
            'language',
            get_string('courselanguage', 'local_reportbuilder'),
            "$join.lang",
            array(
                'joins' => $join,
                'displayfunc' => 'language_code'
            )
        );

        return true;
    }


    /**
     * Adds some common course filters to the $filteroptions array
     *
     * @param array &$filteroptions Array of current filter options
     *                              Passed by reference and updated by
     *                              this method
     * @return True
     */
    protected function add_course_fields_to_filters(&$filteroptions) {
        $filteroptions[] = new rb_filter_option(
            'course',
            'fullname',
            get_string('coursename', 'local_reportbuilder'),
            'text'
        );
        $filteroptions[] = new rb_filter_option(
            'course',
            'shortname',
            get_string('courseshortname', 'local_reportbuilder'),
            'text'
        );
        $filteroptions[] = new rb_filter_option(
            'course',
            'idnumber',
            get_string('courseidnumber', 'local_reportbuilder'),
            'text'
        );
        $coursevisiblestring = get_string('coursevisible', 'local_reportbuilder');
        $filteroptions[] = new rb_filter_option(
            'course',
            'visible',
            $coursevisiblestring,
            'select',
            array(
                'selectchoices' => array(0 => get_string('no'), 1 => get_string('yes')),
                'simplemode' => true
            )
        );
        $filteroptions[] = new rb_filter_option(
            'course',
            'timecreated',
            get_string('coursedatecreated', 'local_reportbuilder'),
            'date',
            array('castdate' => true)
        );
        $filteroptions[] = new rb_filter_option(
            'course',
            'startdate',
            get_string('coursestartdate', 'local_reportbuilder'),
            'date',
            array('castdate' => true)
        );
        $filteroptions[] = new rb_filter_option(
            'course',
            'name_and_summary',
            get_string('coursenameandsummary', 'local_reportbuilder'),
            'textarea'
        );
        $filteroptions[] = new rb_filter_option(
            'course',
            'language',
            get_string('courselanguage', 'local_reportbuilder'),
            'select',
            array(
                'selectfunc' => 'course_languages',
                'attributes' => rb_filter_option::select_width_limiter(),
            )
        );
        $filteroptions[] = new rb_filter_option(
            'course',
            'id',
            get_string('coursemultiitem', 'local_reportbuilder'),
            'course_multi'
        );
        return true;
    }

    /**
     * Adds the certification table to the $joinlist array
     *
     * @param array &$joinlist Array of current join options
     *                         Passed by reference and updated to
     *                         include new table joins
     * @param string $join Name of the join that provides the
     *                     'certif id' field
     * @param string $field Name of table containing program id field to join on
     */
    protected function add_certification_table_to_joinlist(&$joinlist, $join, $field) {

        $joinlist[] = new rb_join(
                'certif',
                'inner',
                '{certif}',
                "certif.id = $join.$field",
                REPORT_BUILDER_RELATION_ONE_TO_ONE,
                $join
        );
    }

    /**
     * Adds the course_category table to the $joinlist array
     *
     * @param array &$joinlist Array of current join options
     *                         Passed by reference and updated to
     *                         include course_category
     * @param string $join Name of the join that provides the 'course' table
     * @param string $field Name of category id field to join on
     * @return boolean True
     */
    protected function add_course_category_table_to_joinlist(&$joinlist,
        $join, $field) {

        $joinlist[] = new rb_join(
            'course_category',
            'LEFT',
            '{course_categories}',
            "course_category.id = $join.$field",
            REPORT_BUILDER_RELATION_MANY_TO_ONE,
            $join
        );

        return true;
    }


    /**
     * Adds some common program info to the $columnoptions array
     *
     * @param array &$columnoptions Array of current column options
     *                              Passed by reference and updated by
     *                              this method
     * @param string $join Name of the join that provides the 'program' table
     * @param string 'local_custom_certification' Source for translation, totara_program or local_certification
     *
     * @return True
     */
    protected function add_certification_fields_to_columns(&$columnoptions, $join = 'certif') {
        global $DB;

        $columnoptions[] = new rb_column_option(
                'certif',
                'fullname',
                get_string('certificationname', 'local_custom_certification'),
                "$join.fullname",
                array('joins' => $join,
                      'dbdatatype' => 'char',
                      'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
                'certif',
                'shortname',
                get_string('certificationshortname', 'local_custom_certification'),
                "$join.shortname",
                array('joins' => $join,
                      'dbdatatype' => 'char',
                      'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
                'certif',
                'idnumber',
                get_string('certificationidnumber', 'local_custom_certification'),
                "$join.idnumber",
                array('joins' => $join,
                      'displayfunc' => 'plaintext',
                      'dbdatatype' => 'char',
                      'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
                'certif',
                'id',
                get_string('certificationid', 'local_custom_certification'),
                "$join.id",
                array('joins' => $join)
        );
        $columnoptions[] = new rb_column_option(
                'certif',
                'summary',
                get_string('certificationsummary', 'local_custom_certification'),
                "$join.summary",
                array(
                        'joins' => $join,
                        'displayfunc' => 'editor_textarea',
                        'extrafields' => array(
                                'filearea' => '\'summary\'',
                                'component' => '\'local_certification\'',
                                'context' => '\'context_certification\'',
                                'recordid' => "$join.id",
                                'fileid' => 0
                        ),
                        'dbdatatype' => 'text',
                        'outputformat' => 'text'
                )
        );
        $columnoptions[] = new rb_column_option(
                'certif',
                'certifexpandlink',
                get_string('certificationexpandlink', 'local_custom_certification'),
                "$join.fullname",
                array(
                        'joins' => $join,
                        'displayfunc' => 'certification_expand',
                        'defaultheading' => get_string('certificationname', 'local_custom_certification'),
                        'extrafields' => array(
                                'certif_id' => "$join.id",
                                'certif_visible' => "$join.visible")
                )
        );

        $columnoptions[] = new rb_column_option(
                'certif',
                'visible',
                get_string('certificationvisible', 'local_custom_certification'),
                "$join.visible",
                array(
                        'joins' => $join,
                        'displayfunc' => 'yes_no'
                )
        );

        $columnoptions[] = new rb_column_option(
                'certif',
                'activeperiod',
                get_string('activeperiod', 'local_custom_certification'),
                "$join.activeperiodtime",
                array('joins' => $join,
                      'dbdatatype' => 'char',
                      'outputformat' => 'text')
        );

        $columnoptions[] = new rb_column_option(
                'certif',
                'windowperiod',
                get_string('windowperiod', 'local_custom_certification'),
                "$join.windowperiod",
                array('joins' => $join,
                      'dbdatatype' => 'char',
                      'outputformat' => 'text')
        );
        return true;
    }

    /**
     * Adds some common certification filters to the $filteroptions array
     *
     * @param array &$filteroptions Array of current filter options
     *                              Passed by reference and updated by
     *                              this method
     * @param string 'local_custom_certification' Source for translation, totara_program or local_certification
     * @return boolean
     */
    protected function add_certification_fields_to_filters(&$filteroptions) {

        $filteroptions[] = new rb_filter_option(
                'certif',
                'recertifydatetype',
                get_string('recertdatetype', 'local_custom_certification'),
                'select',
                array(
                        'selectfunc' => 'recertifydatetype',
                )
        );

        $filteroptions[] = new rb_filter_option(
                'certif',
                'activeperiod',
                get_string('activeperiod', 'local_custom_certification'),
                'text'
        );

        $filteroptions[] = new rb_filter_option(
                'certif',
                'windowperiod',
                get_string('windowperiod', 'local_custom_certification'),
                'text'
        );

        return true;
    }

    /**
     * Adds some common course category info to the $columnoptions array
     *
     * @param array &$columnoptions Array of current column options
     *                              Passed by reference and updated by
     *                              this method
     * @param string $catjoin Name of the join that provides the
     *                        'course_categories' table
     * @param string $coursejoin Name of the join that provides the
     *                           'course' table
     * @return True
     */
    protected function add_course_category_fields_to_columns(&$columnoptions, $catjoin='course_category') {
        $columnoptions[] = new rb_column_option(
            'course_category',
            'name',
            get_string('coursecategory', 'local_reportbuilder'),
            "$catjoin.name",
            array('joins' => $catjoin,
                  'dbdatatype' => 'char',
                  'outputformat' => 'text')
        );
        $columnoptions[] = new rb_column_option(
            'course_category',
            'namelink',
            get_string('coursecategorylinked', 'local_reportbuilder'),
            "$catjoin.name",
            array(
                'joins' => $catjoin,
                'displayfunc' => 'link_course_category',
                'defaultheading' => get_string('category', 'local_reportbuilder'),
                'extrafields' => array('cat_id' => "$catjoin.id",
                                        'cat_visible' => "$catjoin.visible")
            )
        );
        $columnoptions[] = new rb_column_option(
            'course_category',
            'idnumber',
            get_string('coursecategoryidnumber', 'local_reportbuilder'),
            "$catjoin.idnumber",
            array(
                'joins' => $catjoin,
                'displayfunc' => 'plaintext',
                'dbdatatype' => 'char',
                'outputformat' => 'text'
            )
        );
        $columnoptions[] = new rb_column_option(
            'course_category',
            'id',
            get_string('coursecategoryid', 'local_reportbuilder'),
            "$catjoin.id",
            array('joins' => $catjoin)
        );
        return true;
    }


    /**
     * Adds some common course category filters to the $filteroptions array
     *
     * @param array &$columnoptions Array of current filter options
     *                              Passed by reference and updated by
     *                              this method
     * @return True
     */
    protected function add_course_category_fields_to_filters(&$filteroptions) {
        $filteroptions[] = new rb_filter_option(
            'course_category',
            'id',
            get_string('coursecategory', 'local_reportbuilder'),
            'select',
            array(
                'selectfunc' => 'course_categories_list',
                'attributes' => rb_filter_option::select_width_limiter(),
            )
        );
        $filteroptions[] = new rb_filter_option(
            'course_category',
            'path',
            get_string('coursecategorymultichoice', 'local_reportbuilder'),
            'category',
            array(),
            'course_category.path',
            'course_category'
        );
        return true;
    }

    /**
     * Converts a list to an array given a list and a separator
     * duplicate values are ignored
     *
     * Example;
     * list_to_array('some-thing-some', '-'); =>
     * array('some' => 'some', 'thing' => 'thing');
     *
     * @param string $list List of items
     * @param string $sep Symbol or string that separates list items
     * @return array $result array of list items
     */
    function list_to_array($list, $sep) {
        $base = explode($sep, $list);
        return array_combine($base, $base);
    }

    /**
     * Generic function for adding custom fields to the reports
     * Intentionally optimized into one function to reduce number of db queries
     *
     * @param string $cf_prefix - prefix for custom field table e.g. everything before '_info_field' or '_info_data'
     * @param string $join - join table in joinlist used as a link to main query
     * @param string $joinfield - joinfield in data table used to link with main table
     * @param array $joinlist - array of joins passed by reference
     * @param array $columnoptions - array of columnoptions, passed by reference
     * @param array $filteroptions - array of filters, passed by reference
     * @param string $suffix - instead of custom_field_{$id}, column name will be custom_field_{$id}{$suffix}. Use short prefixes
     *                         to avoid hiting column size limitations
     * @param bool $nofilter - do not create filter for custom fields. It is useful when customfields are dynamically added by
     *                         column generator
     */
    protected function add_custom_fields_for($cf_prefix, $join, $joinfield,
        array &$joinlist, array &$columnoptions, array &$filteroptions, $suffix = '', $nofilter = false) {

        global $CFG, $DB;

        if (strlen($suffix)) {
            if (!preg_match('/^[a-zA-Z]{1,5}$/', $suffix)) {
                throw new coding_exception('Suffix for add_custom_fields_for must be letters only up to 5 chars.');
            }
        }

        $seek = false;
        $jointable = false;
        foreach ($joinlist as $object) {
            $seek = ($object->name == $join);
            if ($seek) {
                $jointable = $object->table;
                break;
            }
        }

        if ($join == 'base') {
            $seek = 'base';
            $jointable = $this->base;
        }

        if (!$seek) {
            $a = new stdClass();
            $a->join = $join;
            $a->source = get_class($this);
            throw new ReportBuilderException(get_string('error:missingdependencytable', 'local_reportbuilder', $a));
        }

        if ($cf_prefix === 'user') {
            return $this->add_custom_user_fields($joinlist, $columnoptions, $filteroptions, $join, 'user', false, $nofilter);
        }

        // Build the table names for this sort of custom field data.
        $fieldtable = $cf_prefix.'_info_field';
        $datatable = $cf_prefix.'_info_data';


        if ($cf_prefix === 'facetoface_session') {
            $fieldtable = $cf_prefix.'_field';
            $datatable = $cf_prefix.'_data';
        }

        // Check if there are any visible custom fields of this type.
        $items = $DB->get_recordset($fieldtable);

        if (empty($items)) {
            $items->close();
            return false;
        }
        foreach ($items as $record) {
            $id = $record->id;
            $joinname = "{$cf_prefix}_{$id}{$suffix}";
            $value = "custom_field_{$id}{$suffix}";
            $name = isset($record->fullname) ? $record->fullname : $record->name;
            $column_options = array('joins' => $joinname);
            $filtertype = 'text'; // default filter type
            $filter_options = array();

            $columnsql = "{$joinname}.data";

            switch ($record->type ?? $record->datatype) {
                case 'file':
                    $column_options['displayfunc'] = 'customfield_file';
                    $column_options['extrafields'] = array(
                            "itemid" => "{$joinname}.id"
                    );
                    break;

                case 'textarea':
                    $filtertype = 'textarea';
                    $column_options['displayfunc'] = 'customfield_textarea';
                    $column_options['extrafields'] = array(
                        "itemid" => "{$joinname}.id"
                    );
                    $column_options['dbdatatype'] = 'text';
                    $column_options['outputformat'] = 'text';
                    break;

                case 'menu':
                    $default = $record->defaultdata;
                    if ($default !== '' and $default !== null) {
                        // Note: there is no safe way to inject the default value into the query, use extra join instead.
                        $fieldjoin = $joinname . '_fielddefault';
                        $joinlist[] = new rb_join(
                            $fieldjoin,
                            'INNER',
                            "{{$fieldtable}}",
                            "{$fieldjoin}.id = {$id}",
                            REPORT_BUILDER_RELATION_MANY_TO_ONE
                        );
                        $columnsql = "COALESCE({$columnsql}, {$fieldjoin}.defaultdata)";
                        $column_options['joins'] = (array)$column_options['joins'];
                        $column_options['joins'][] = $fieldjoin;
                    }
                    $filtertype = 'menuofchoices';
                    $filter_options['selectchoices'] = $this->list_to_array($record->param1,"\n");
                    $filter_options['simplemode'] = true;
                    $column_options['dbdatatype'] = 'text';
                    $column_options['outputformat'] = 'text';
                    break;

                case 'checkbox':
                    $default = $record->defaultdata;
                    $columnsql = "CASE WHEN ( {$columnsql} IS NULL OR {$columnsql} = '' ) THEN {$default} ELSE " . $DB->sql_cast_char2int($columnsql, true) . " END";
                    $filtertype = 'select';
                    $filter_options['selectchoices'] = array(0 => get_string('no'), 1 => get_string('yes'));
                    $filter_options['simplemode'] = true;
                    $column_options['displayfunc'] = 'yes_no';
                    break;

                case 'datetime':
                    $filtertype = 'date';
                    $columnsql = "CASE WHEN {$columnsql} = '' THEN NULL ELSE " . $DB->sql_cast_char2int($columnsql, true) . " END";
                    if ($record->param3) {
                        $column_options['displayfunc'] = 'nice_datetime';
                        $column_options['dbdatatype'] = 'timestamp';
                        $filter_options['includetime'] = true;
                    } else {
                        $column_options['displayfunc'] = 'nice_date';
                        $column_options['dbdatatype'] = 'timestamp';
                    }
                    break;

                case 'date': // Midday in UTC, date without timezone.
                    $filtertype = 'date';
                    $columnsql = "CASE WHEN {$columnsql} = '' THEN NULL ELSE " . $DB->sql_cast_char2int($columnsql, true) . " END";
                    $column_options['displayfunc'] = 'nice_date_no_timezone';
                    $column_options['dbdatatype'] = 'timestamp';
                    break;

                case 'text':
                    $default = $record->defaultdata;
                    if ($default !== '' and $default !== null) {
                        // Note: there is no safe way to inject the default value into the query, use extra join instead.
                        $fieldjoin = $joinname . '_fielddefault';
                        $joinlist[] = new rb_join(
                            $fieldjoin,
                            'INNER',
                            "{{$fieldtable}}",
                            "{$fieldjoin}.id = {$id}",
                            REPORT_BUILDER_RELATION_MANY_TO_ONE
                        );
                        $columnsql = "COALESCE({$columnsql}, {$fieldjoin}.defaultdata)";
                        $column_options['joins'] = (array)$column_options['joins'];
                        $column_options['joins'][] = $fieldjoin;
                    }
                    $column_options['dbdatatype'] = 'text';
                    $column_options['outputformat'] = 'text';
                    break;

                case 'url':
                    $filtertype = 'url';
                    $column_options['dbdatatype'] = 'text';
                    $column_options['outputformat'] = 'text';
                    $column_options['displayfunc'] = 'customfield_url';
                    break;

                case 'location':
                    $column_options['displayfunc'] = 'location';
                    $column_options['outputformat'] = 'text';
                    break;

                default:
                    // Unsupported customfields. e.g multiselect
                    continue 2;
            }

            $joinlist[] = new rb_join(
                    $joinname,
                    'LEFT',
                    "{{$datatable}}",
                    "{$joinname}.{$joinfield} = {$join}.id AND {$joinname}.fieldid = {$id}",
                    REPORT_BUILDER_RELATION_ONE_TO_ONE,
                    $join
                );
            $columnoptions[] = new rb_column_option(
                    $cf_prefix,
                    $value,
                    $name,
                    $columnsql,
                    $column_options
                );

            if ($record->datatype == 'file') {
                // No filter options for files yet.
                continue;
            } else {
                if (!$nofilter) {
                    $filteroptions[] = new rb_filter_option(
                        $cf_prefix,
                        $value,
                        $name,
                        $filtertype,
                        $filter_options
                    );
                }
            }
        }

        $items->close();

        return true;

    }

    /**
     * Dynamically add all customfields to columns
     * It uses additional suffix 'all' for column names generation . This means, that if some customfield column was generated using
     * the same suffix it will be shadowed by this method.
     * @param rb_column_option $columnoption should have public string property "type" which value is the type of customfields to show
     * @param bool $hidden should all these columns be hidden
     * @return array
     */
    public function rb_cols_generator_allcustomfields(rb_column_option $columnoption, $hidden) {
        $result = array();
        $columnoptions = array();

        // add_custom_fields_for requires only one join.
        if (!empty($columnoption->joins) && !is_string($columnoption->joins)) {
            throw new coding_exception('allcustomfields column generator requires none or only one join as string');
        }

        $join = empty($columnoption->joins) ? 'base' : $columnoption->joins;

        $this->add_custom_fields_for($columnoption->type, $join, $columnoption->field, $this->joinlist,
                $columnoptions, $this->filteroptions, 'all', true);
        foreach($columnoptions as $option) {
            $result[] = new rb_column(
                    $option->type,
                    $option->value,
                    $option->name,
                    $option->field,
                    (array)$option
            );
        }

        return $result;
    }

    /**
     * Adds user custom fields to the report.
     *
     * @param array $joinlist
     * @param array $columnoptions
     * @param array $filteroptions
     * @param string $basejoin
     * @param string $groupname
     * @param bool $addtypetoheading
     * @param bool $nofilter
     * @return boolean
     */
    protected function add_custom_user_fields(array &$joinlist, array &$columnoptions,
        array &$filteroptions, $basejoin = 'auser', $groupname = 'user', $addtypetoheading = false, $nofilter = false) {

        global $DB;

        if (!empty($this->addeduserjoins[$basejoin]['processed'])) {
            // Already added.
            return false;
        }

        $jointable = false;
        if ($basejoin === 'base') {
            $jointable = $this->base;
        } else {
            foreach ($joinlist as $object) {
                if ($object->name === $basejoin) {
                    $jointable = $object->table;
                    break;
                }
            }
        }

        // Check if there are any visible custom fields of this type.
        $items = $DB->get_recordset('user_info_field');
        foreach ($items as $record) {
            $id = $record->id;
            $joinname = "{$basejoin}_cf_{$id}";
            $value = "custom_field_{$id}";
            $name = isset($record->fullname) ? $record->fullname : $record->name;

            $column_options = array();
            $column_options['joins'] = array($joinname);
            $column_options['extracontext'] = (array)$record;
            $column_options['addtypetoheading'] = $addtypetoheading;
            $column_options['displayfunc'] = 'userfield_' . $record->datatype;

            if ($record->visible != PROFILE_VISIBLE_ALL) {
                // If the field is not visible to all we need the userid to enable visibility checks.
                if ($jointable === '{user}') {
                    $column_options['extrafields'] = array('userid' => "{$basejoin}.id");
                } else {
                    $column_options['extrafields'] = array('userid' => "{$joinname}.userid");
                }
            }

            if ($record->visible == PROFILE_VISIBLE_NONE) {
                // If profile field isn't available to everyone require a capability to display the column.
                $column_options['capability'] = 'moodle/user:viewalldetails';
            }

            $filter_options = array();
            $filter_options['addtypetoheading'] = $addtypetoheading;

            $columnsql = "{$joinname}.data";

            switch ($record->datatype) {
                case 'textarea':
                    $column_options['extrafields']["format"] = "{$joinname}.dataformat";
                    $column_options['dbdatatype'] = 'text';
                    $column_options['outputformat'] = 'text';
                    $filtertype = 'textarea';
                    break;

                case 'menu':
                    $default = $record->defaultdata;
                    if ($default !== '' and $default !== null) {
                        // Note: there is no safe way to inject the default value into the query, use extra join instead.
                        $fieldjoin = $joinname . '_fielddefault';
                        $joinlist[] = new rb_join(
                            $fieldjoin,
                            'INNER',
                            "{user_info_field}",
                            "{$fieldjoin}.id = {$id}",
                            REPORT_BUILDER_RELATION_MANY_TO_ONE
                        );
                        $columnsql = "COALESCE({$columnsql}, {$fieldjoin}.defaultdata)";
                        $column_options['joins'][] = $fieldjoin;
                    }
                    $column_options['dbdatatype'] = 'text';
                    $column_options['outputformat'] = 'text';
                    $filtertype = 'menuofchoices';
                    $filter_options['selectchoices'] = $this->list_to_array($record->param1,"\n");
                    $filter_options['simplemode'] = true;
                    break;

                case 'checkbox':
                    $default = (int)$record->defaultdata;
                    $columnsql = "CASE WHEN ( {$columnsql} IS NULL OR {$columnsql} = '' ) THEN {$default} ELSE " . $DB->sql_cast_char2int($columnsql, true) . " END";
                    $filtertype = 'select';
                    $filter_options['selectchoices'] = array(0 => get_string('no'), 1 => get_string('yes'));
                    $filter_options['simplemode'] = true;
                    break;

                case 'datetime':
                    $columnsql = "CASE WHEN {$columnsql} = '' THEN NULL ELSE " . $DB->sql_cast_char2int($columnsql, true) . " END";
                    $column_options['dbdatatype'] = 'timestamp';
                    $filtertype = 'date';
                    if ($record->param3) {
                        $filter_options['includetime'] = true;
                    }
                    break;

                case 'date': // Midday in UTC, date without timezone.
                    $columnsql = "CASE WHEN {$columnsql} = '' THEN NULL ELSE " . $DB->sql_cast_char2int($columnsql, true) . " END";
                    $column_options['dbdatatype'] = 'timestamp';
                    $filtertype = 'date';
                    break;

                case 'text':
                    $default = $record->defaultdata;
                    if ($default !== '' and $default !== null) {
                        // Note: there is no safe way to inject the default value into the query, use extra join instead.
                        $fieldjoin = $joinname . '_fielddefault';
                        $joinlist[] = new rb_join(
                            $fieldjoin,
                            'INNER',
                            "{user_info_field}",
                            "{$fieldjoin}.id = {$id}",
                            REPORT_BUILDER_RELATION_MANY_TO_ONE
                        );
                        $columnsql = "COALESCE({$columnsql}, {$fieldjoin}.defaultdata)";
                        $column_options['joins'][] = $fieldjoin;
                    }
                    $column_options['dbdatatype'] = 'text';
                    $column_options['outputformat'] = 'text';
                    $filtertype = 'text';
                    break;

                default:
                    // Unsupported customfields.
                    continue 2;
            }

            $joinlist[] = new rb_join(
                $joinname,
                'LEFT',
                "{user_info_data}",
                "{$joinname}.userid = {$basejoin}.id AND {$joinname}.fieldid = {$id}",
                REPORT_BUILDER_RELATION_ONE_TO_ONE,
                $basejoin
            );
            $columnoptions[] = new rb_column_option(
                $groupname,
                $value,
                $name,
                $columnsql,
                $column_options
            );

            if (!$nofilter) {
                $filteroptions[] = new rb_filter_option(
                    $groupname,
                    $value,
                    $name,
                    $filtertype,
                    $filter_options
                );
            }
        }

        $items->close();

        return true;
    }

    /**
     * Adds course custom fields to the report
     *
     * @param array $joinlist
     * @param array $columnoptions
     * @param array $filteroptions
     * @param string $basetable
     * @return boolean
     */
    protected function add_custom_course_fields(array &$joinlist, array &$columnoptions,
        array &$filteroptions, $basetable = 'course') {
        return $this->add_custom_fields_for('coursemetadata',
                                            $basetable,
                                            'courseid',
                                            $joinlist,
                                            $columnoptions,
                                            $filteroptions);
    }


    /**
     * DEPRECATED: The tag API has changed and your code needs to be updated.
     *
     * Please call add_core_tag_tables_to_joinlist instead.
     *
     * @deprecated since Totara 10
     */
    protected function add_tag_tables_to_joinlist() {
        throw new coding_exception('add_tag_tables_to_joinlist has been deprecated due to tag API changes, please upgrade your code to call add_core_tag_tables_to_joinlist instead.', DEBUG_DEVELOPER);
    }


    /**
     * Adds the tag tables to the $joinlist array
     *
     * @param string $component component for the tag
     * @param string $itemtype tag itemtype
     * @param array &$joinlist Array of current join options
     *                         Passed by reference and updated to
     *                         include new table joins
     * @param string $join Name of the join that provides the
     *                     $type table
     * @param string $field Name of course id field to join on
     * @return boolean True
     */
    protected function add_core_tag_tables_to_joinlist($component, $itemtype, &$joinlist, $join, $field) {
        global $DB;


        $idlist = \local_reportbuilder\dblib\base::getbdlib()->sql_group_concat(\local_reportbuilder\dblib\base::getbdlib()->sql_cast_2char('t.id'), '|');
        $joinlist[] = new rb_join(
            'tagids',
            'LEFT',
            // subquery as table name
            "(SELECT til.id AS tilid, {$idlist} AS idlist
                FROM {{$itemtype}} til
           LEFT JOIN {tag_instance} ti ON til.id = ti.itemid AND ti.itemtype = '{$itemtype}'
           LEFT JOIN {tag} t ON ti.tagid = t.id AND t.isstandard = '1'
            GROUP BY til.id)",
            "tagids.tilid = {$join}.{$field}",
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            $join
        );


        $namelist = \local_reportbuilder\dblib\base::getbdlib()->sql_group_concat(\local_reportbuilder\dblib\base::getbdlib()->sql_cast_2char('t.name'), ', ');
        $joinlist[] = new rb_join(
            'tagnames',
            'LEFT',
            // subquery as table name
            "(SELECT tnl.id AS tnlid, {$namelist} AS namelist
                FROM {{$itemtype}} tnl
           LEFT JOIN {tag_instance} ti ON tnl.id = ti.itemid AND ti.itemtype = '{$itemtype}'
           LEFT JOIN {tag} t ON ti.tagid = t.id AND t.isstandard = '1'
            GROUP BY tnl.id)",
            "tagnames.tnlid = {$join}.{$field}",
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            $join
        );

        // Create a join for each tag in the collection.
        $tagcollectionid = core_tag_area::get_collection($component, $itemtype);
        $tags = self::get_tags($tagcollectionid);
        foreach ($tags as $tag) {
            $tagid = $tag->id;
            $name = "{$itemtype}_tag_$tagid";
            $joinlist[] = new rb_join(
                $name,
                'LEFT',
                '{tag_instance}',
                "($name.itemid = $join.$field AND $name.tagid = $tagid " .
                    "AND $name.itemtype = '{$itemtype}')",
                REPORT_BUILDER_RELATION_ONE_TO_ONE,
                $join
            );
        }

        return true;
    }


    /**
     * DEPRECATED: The tag API has changed and your code needs to be updated.
     *
     * Please call add_core_fields_to_columns instead.
     *
     * @deprecated since Totara 10
     */
    protected function add_tag_fields_to_columns() {
        throw new coding_exception('add_tag_fields_to_columns has been deprecated due to tag API changes, please upgrade your code to call add_core_tag_fields_to_columns instead.', DEBUG_DEVELOPER);
    }


    /**
     * Adds some common tag info to the $columnoptions array
     *
     * @param string $component component for the tag
     * @param string $itemtype tag itemtype
     * @param array &$columnoptions Array of current column options
     *                              Passed by reference and updated by
     *                              this method
     * @param string $tagids name of the join that provides the 'tagids' table.
     * @param string $tagnames name of the join that provides the 'tagnames' table.
     *
     * @return True
     */
    protected function add_core_tag_fields_to_columns($component, $itemtype, &$columnoptions, $tagids='tagids', $tagnames='tagnames') {
        global $DB;

        $columnoptions[] = new rb_column_option(
            'tags',
            'tagids',
            get_string('tagids', 'local_reportbuilder'),
            "$tagids.idlist",
            array('joins' => $tagids, 'selectable' => false)
        );
        $columnoptions[] = new rb_column_option(
            'tags',
            'tagnames',
            get_string('tags', 'local_reportbuilder'),
            "$tagnames.namelist",
            array('joins' => $tagnames,
                  'dbdatatype' => 'char',
                  'outputformat' => 'text')
        );

        // Only get the tags in the collection for this item type.
        $tagcollectionid = core_tag_area::get_collection($component, $itemtype);
        $tags = self::get_tags($tagcollectionid);

        // Create a on/off field for every official tag.
        foreach ($tags as $tag) {
            $tagid = $tag->id;
            $name = $tag->name;
            $join = "{$itemtype}_tag_$tagid";
            $columnoptions[] = new rb_column_option(
                'tags',
                $join,
                get_string('taggedx', 'local_reportbuilder', $name),
                "CASE WHEN $join.id IS NOT NULL THEN 1 ELSE 0 END",
                array(
                    'joins' => $join,
                    'displayfunc' => 'yes_no',
                )
            );
        }
        return true;
    }


    /**
     * DEPRECATED: The tag API has changed and your code needs to be updated.
     *
     * Please call add_core_fields_to_filters instead.
     *
     * @deprecated since Totara 10
     */
    protected function add_tag_fields_to_filters($component, $itemtype, &$filteroptions) {
        throw new coding_exception('add_tag_fields_to_filters has been deprecated due to tag API changes, please upgrade your code to call add_core_tag_fields_to_filters instead.', DEBUG_DEVELOPER);
    }


    /**
     * Adds some common tag filters to the $filteroptions array
     *
     * @param string $component component for the tag
     * @param string $itemtype tag itemtype
     * @param array &$filteroptions Array of current filter options
     *                              Passed by reference and updated by
     *                              this method
     * @return True
     */
    protected function add_core_tag_fields_to_filters($component, $itemtype, &$filteroptions) {
        global $DB;

        // Only get the tags in the collection for this item type.
        $tagcollectionid = core_tag_area::get_collection($component, $itemtype);
        $tags = self::get_tags($tagcollectionid);

        // Create a yes/no filter for every official tag
        foreach ($tags as $tag) {
            $tagid = $tag->id;
            $name = $tag->name;
            $join = "{$itemtype}_tag_{$tagid}";
            $filteroptions[] = new rb_filter_option(
                'tags',
                $join,
                get_string('taggedx', 'local_reportbuilder', $name),
                'select',
                array(
                    'selectchoices' => array(1 => get_string('yes'), 0 => get_string('no')),
                    'simplemode' => true,
                )
            );
        }

        // Build filter list from tag list.
        $tagoptions = array();
        foreach ($tags as $tag) {
            $tagoptions[$tag->id] = $tag->name;
        }

        // create a tag list selection filter
        $filteroptions[] = new rb_filter_option(
            'tags',         // type
            'tagids',           // value
            get_string('tags', 'local_reportbuilder'), // label
            'multicheck',     // filtertype
            array(            // options
                'selectchoices' => $tagoptions,
                'concat' => true, // Multicheck filter needs to know that we are working with concatenated values
                'showcounts' => array(
                        'joins' => array("LEFT JOIN (SELECT ti.itemid, ti.tagid FROM {{$itemtype}} base " .
                                                      "LEFT JOIN {tag_instance} ti ON base.id = ti.itemid " .
                                                            "AND ti.itemtype = '{$itemtype}'" .
                                                      "LEFT JOIN {tag} tag ON ti.tagid = tag.id " .
                                                            "AND tag.isstandard = '1')\n {$itemtype}_tagids_filter " .
                                                "ON base.id = {$itemtype}_tagids_filter.itemid"),
                        'dataalias' => $itemtype.'_tagids_filter',
                        'datafield' => 'tagid')
            )
        );
        return true;
    }


    /**
     * Adds the cohort user tables to the $joinlist array
     *
     * @param array &$joinlist Array of current join options
     *                         Passed by reference and updated to
     *                         include new table joins
     * @param string $join Name of the join that provides the
     *                     'user' table
     * @param string $field Name of user id field to join on
     * @return boolean True
     */
    protected function add_cohort_user_tables_to_joinlist(&$joinlist, $join, $field, $alias = 'ausercohort') {
        global $DB;

        $idlist = \local_reportbuilder\dblib\base::getbdlib()->sql_group_concat_unique(\local_reportbuilder\dblib\base::getbdlib()->sql_cast_2char('cm.cohortid'),'|');
        $joinlist[] = new rb_join(
            $alias,
            'LEFT',
            // subquery as table name
            "(SELECT cm.userid AS userid, {$idlist} AS idlist
                FROM {cohort_members} cm
            GROUP BY cm.userid)",
            "{$alias}.userid = $join.$field",
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            $join
        );

        return true;
    }

    /**
     * Adds the cohort course tables to the $joinlist array
     *
     * @param array &$joinlist Array of current join options
     *                         Passed by reference and updated to
     *                         include new table joins
     * @param string $join Name of the join that provides the
     *                     'course' table
     * @param string $field Name of course id field to join on
     * @return boolean True
     */
    protected function add_cohort_course_tables_to_joinlist(&$joinlist, $join, $field) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/cohort/lib.php');

        $idlist = \local_reportbuilder\dblib\base::getbdlib()->sql_group_concat_unique(\local_reportbuilder\dblib\base::getbdlib()->sql_cast_2char('customint1'), '|');
        $joinlist[] = new rb_join(
            'cohortenrolledcourse',
            'LEFT',
            // subquery as table name
            "(SELECT courseid AS course, {$idlist} AS idlist
                FROM {enrol} e
               WHERE e.enrol = 'cohort'
            GROUP BY courseid)",
            "cohortenrolledcourse.course = $join.$field",
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            $join
        );

        return true;
    }


    /**
     * Adds the cohort program tables to the $joinlist array
     *
     * @param array &$joinlist Array of current join options
     *                         Passed by reference and updated to
     *                         include new table joins
     * @param string $join Name of the join that provides the
     *                     table containing the program id
     * @param string $field Name of program id field to join on
     * @return boolean True
     */
    protected function add_cohort_certification_tables_to_joinlist(&$joinlist, $join, $field) {
        global $CFG, $DB;

        require_once($CFG->dirroot . '/cohort/lib.php');

        $idlist = \local_reportbuilder\dblib\base::getbdlib()->sql_group_concat_unique(\local_reportbuilder\dblib\base::getbdlib()->sql_cast_2char('assignmenttypeid'), '|');
        $joinlist[] = new rb_join(
            'cohortenrolledcertification',
            'LEFT',
            // subquery as table name
            "(SELECT certifid AS certification, {$idlist} AS idlist
                FROM {certif_assignments} pa
               WHERE assignmenttype = " . \local_custom_certification\certification::ASSIGNMENT_TYPE_AUDIENCE . "
            GROUP BY certifid)",
            "cohortenrolledcertification.certification = $join.$field",
            REPORT_BUILDER_RELATION_ONE_TO_ONE,
            $join
        );

        return true;
    }


    /**
     * Adds some common cohort user info to the $columnoptions array
     *
     * @param array &$columnoptions Array of current column options
     *                              Passed by reference and updated by
     *                              this method
     * @param string $join Name of the join that provides the
     *                          'cohortuser' table.
     *
     * @return True
     */
    protected function add_cohort_user_fields_to_columns(&$columnoptions,
                                                         $join='ausercohort', $groupname = 'user',
                                                         $addtypetoheading = false) {

        $columnoptions[] = new rb_column_option(
            $groupname,
            'usercohortids',
            get_string('usercohortids', 'local_reportbuilder'),
            "$join.idlist",
            array(
                'joins' => $join,
                'selectable' => false,
                'addtypetoheading' => $addtypetoheading
            )
        );

        return true;
    }


    /**
     * Adds some common cohort course info to the $columnoptions array
     *
     * @param array &$columnoptions Array of current column options
     *                              Passed by reference and updated by
     *                              this method
     * @param string $cohortenrolledids Name of the join that provides the
     *                          'cohortenrolledcourse' table.
     *
     * @return True
     */
    protected function add_cohort_course_fields_to_columns(&$columnoptions, $cohortenrolledids='cohortenrolledcourse') {
        $columnoptions[] = new rb_column_option(
            'cohort',
            'enrolledcoursecohortids',
            get_string('enrolledcoursecohortids', 'local_reportbuilder'),
            "$cohortenrolledids.idlist",
            array('joins' => $cohortenrolledids, 'selectable' => false)
        );

        return true;
    }

    /**
     * Adds some common user cohort filters to the $filteroptions array
     *
     * @param array &$columnoptions Array of current filter options
     *                              Passed by reference and updated by
     *                              this method
     * @return True
     */
    protected function add_cohort_user_fields_to_filters(&$filteroptions, $groupname = 'user', $addtypetoheading = false) {

        if (!has_capability('moodle/cohort:view', context_system::instance())) {
            return true;
        }

        $filteroptions[] = new rb_filter_option(
            $groupname,
            'usercohortids',
            get_string('userincohort', 'local_reportbuilder'),
            'cohort',
            array('addtypetoheading' => $addtypetoheading)
        );
        return true;
    }

    /**
     * Adds some common course cohort filters to the $filteroptions array
     *
     * @param array &$columnoptions Array of current filter options
     *                              Passed by reference and updated by
     *                              this method
     * @return True
     */
    protected function add_cohort_course_fields_to_filters(&$filteroptions) {

        if (!has_capability('moodle/cohort:view', context_system::instance())) {
            return true;
        }

        $filteroptions[] = new rb_filter_option(
            'cohort',
            'enrolledcoursecohortids',
            get_string('courseenrolledincohort', 'local_reportbuilder'),
            'cohort'
        );

        return true;
    }

    /**
     * @return array
     */
    protected function define_columnoptions() {
        return array();
    }

    /**
     * @return array
     */
    protected function define_filteroptions() {
        return array();
    }

    /**
     * @return array
     */
    protected function define_defaultcolumns() {
        return array();
    }

    /**
     * @return array
     */
    protected function define_defaultfilters() {
        return array();
    }

    /**
     * @return array
     */
    protected function define_contentoptions() {
        return array();
    }

    /**
     * @return array
     */
    protected function define_paramoptions() {
        return array();
    }

    /**
     * @return array
     */
    protected function define_requiredcolumns() {
        return array();
    }

    /**
     * Called after parameters have been read, allows the source to configure itself,
     * such as source title, additional tables, column definitions, etc.
     *
     * If post_params fails it needs to set redirect.
     *
     * @param reportbuilder $report
     */
    public function post_params(reportbuilder $report) {
    }

    /**
     * This method is called at the very end of reportbuilder class constructor
     * right before marking it ready.
     *
     * This method allows sources to add extra restrictions by calling
     * the following method on the $report object:
     *  {@link $report->set_post_config_restrictions()}    Extra WHERE clause
     *
     * If post_config fails it needs to set redirect.
     *
     * NOTE: do NOT modify the list of columns here.
     *
     * @param reportbuilder $report
     */
    public function post_config(reportbuilder $report) {
    }

    /**
     * Returns an array of js objects that need to be included with this report.
     *
     * @return array(object)
     */
    public function get_required_jss() {
        return array();
    }

    protected function get_advanced_aggregation_classes($type) {
        global $CFG;

        $classes = array();

        foreach (scandir("{$CFG->dirroot}/local/reportbuilder/classes/rb/{$type}") as $filename) {
            if (substr($filename, -4) !== '.php') {
                continue;
            }
            if ($filename === 'base.php') {
                continue;
            }
            $name = str_replace('.php', '', $filename);
            $classname = "\\local_reportbuilder\\rb\\{$type}\\$name";
            if (!class_exists($classname)) {
                debugging("Invalid aggregation class $name found", DEBUG_DEVELOPER);
                continue;
            }
            $classes[$name] = $classname;
        }

        return $classes;
    }

    /**
     * Get list of allowed advanced options for each column option.
     *
     * @return array of group select column values that are grouped
     */
    public function get_allowed_advanced_column_options() {
        $allowed = array();

        foreach ($this->columnoptions as $option) {
            $key = $option->type . '-' . $option->value;
            $allowed[$key] = array('');

            $classes = $this->get_advanced_aggregation_classes('transform');
            foreach ($classes as $name => $classname) {
                if ($classname::is_column_option_compatible($option)) {
                    $allowed[$key][] = 'transform_'.$name;
                }
            }

            $classes = $this->get_advanced_aggregation_classes('aggregate');
            foreach ($classes as $name => $classname) {
                if ($classname::is_column_option_compatible($option)) {
                    $allowed[$key][] = 'aggregate_'.$name;
                }
            }
        }
        return $allowed;
    }

    /**
     * Get list of grouped columns.
     *
     * @return array of group select column values that are grouped
     */
    public function get_grouped_column_options() {
        $grouped = array();
        foreach ($this->columnoptions as $option) {
            if ($option->grouping !== 'none') {
                $grouped[] = $option->type . '-' . $option->value;
            }
        }
        return $grouped;
    }

    /**
     * Returns list of advanced aggregation/transformation options.
     *
     * @return array nested array suitable for groupselect forms element
     */
    public function get_all_advanced_column_options() {
        $advoptions = array();
        $advoptions[get_string('none')][''] = '-';

        foreach (array('transform', 'aggregate') as $type) {
            $classes = $this->get_advanced_aggregation_classes($type);
            foreach ($classes as $name => $classname) {
                $advoptions[$classname::get_typename()][$type . '_' . $name] = get_string("{$type}type{$name}_name",
                            'local_reportbuilder');
            }
        }

        foreach ($advoptions as $k => $unused) {
            \core_collator::asort($advoptions[$k]);
        }

        return $advoptions;
    }

    /**
     * Set up necessary $PAGE stuff for columns.php page.
     */
    public function columns_page_requires() {
        \local_reportbuilder\rb\aggregate\base::require_column_heading_strings();
        \local_reportbuilder\rb\transform\base::require_column_heading_strings();
    }

    /**
     * @param $mform
     * @param $inlineenrolments
     */
    private function process_enrolments($mform, $inlineenrolments) {
        global $CFG;

        if ($formdata = $mform->get_data()) {
            $submittedinstance = required_param('instancesubmitted', PARAM_INT);
            $inlineenrolment = $inlineenrolments[$submittedinstance];
            $instance = $inlineenrolment->instance;
            $plugin = $inlineenrolment->plugin;
            $nameprefix = 'instanceid_' . $instance->id . '_';
            $nameprefixlength = strlen($nameprefix);

            $valuesforenrolform = array();
            foreach ($formdata as $name => $value) {
                if (substr($name, 0, $nameprefixlength) === $nameprefix) {
                    $name = substr($name, $nameprefixlength);
                    $valuesforenrolform[$name] = $value;
                }
            }
            $enrolform = $plugin->course_expand_get_form_hook($instance);

            $enrolform->_form->updateSubmission($valuesforenrolform, null);

            $enrolled = $plugin->course_expand_enrol_hook($enrolform, $instance);
            if ($enrolled) {
                $mform->_form->addElement('hidden', 'redirect', $CFG->wwwroot . '/course/view.php?id=' . $instance->courseid);
            }

            foreach ($enrolform->_form->_errors as $errorname => $error) {
                $mform->_form->_errors[$nameprefix . $errorname] = $error;
            }
        }
    }

    /**
     * Allows report source to override page header in reportbuilder exports.
     *
     * @param reportbuilder $report
     * @param string $format 'html', 'text', 'excel', 'ods', 'csv' or 'pdf'
     * @return mixed|null must be possible to cast to string[][]
     */
    public function get_custom_export_header(reportbuilder $report, $format) {
        return null;
    }

    /**
     * Get the uniquedelimiter.
     *
     * @return string
     */
    public function get_uniquedelimiter() {
        return self::$uniquedelimiter;
    }

    /**
     * Inject column_test data into database.
     * @param local_reportbuilder_column_testcase $testcase
     */
    public function phpunit_column_test_add_data(local_reportbuilder_column_testcase $testcase) {
       if (!PHPUNIT_TEST) {
           throw new coding_exception('phpunit_column_test_add_data() cannot be used outside of unit tests');
       }
       // Nothing to do by default.
    }

    /**
     * Returns expected result for column_test.
     * @param rb_column_option $columnoption
     * @return int
     */
    public function phpunit_column_test_expected_count($columnoption) {
        if (!PHPUNIT_TEST) {
            throw new coding_exception('phpunit_column_test_expected_count() cannot be used outside of unit tests');
        }
        return 1;
    }

    public function rb_display_certif_status($status, $row) {
        if ($status == \local_custom_certification\completion::COMPLETION_STATUS_COMPLETED) {
            return get_string('complete', 'local_custom_certification');
        } else if ($status == \local_custom_certification\completion::COMPLETION_STATUS_STARTED) {
            return get_string('incomplete', 'local_custom_certification');
        } else {
            return get_string('notstarted', 'local_custom_certification');
        }
    }
    /**
     * Returns a list of tags in a collection.
     *
     *
     * @param int $tagcollid
     * @param null|bool $isstandard return only standard tags
     * @param int $limit maximum number of tags to retrieve, tags are sorted by the instance count
     *            descending here regardless of $sort parameter
     * @param string $sort sort order for display, default 'name' - tags will be sorted after they are retrieved
     * @param string $search search string
     *
     * @return array List of tags in the collection
     */
    public static function get_tags($tagcollid, $isstandard = false, $limit = 150, $sort = 'name',
            $search = '') {

        global $DB;

        $fromclause = 'FROM {tag} tg';
        $whereclause = 'WHERE 1=1';
        list($sql, $params) = $DB->get_in_or_equal($tagcollid ? array($tagcollid) :
                array_keys(core_tag_collection::get_collections(true)));
        $whereclause .= ' AND tg.tagcollid ' . $sql;
        if ($isstandard) {
            $whereclause .= ' AND tg.isstandard = 1';
        }

        if (strval($search) !== '') {
            $whereclause .= ' AND tg.name LIKE ?';
            $params[] = '%' . core_text::strtolower($search) . '%';
        }

        $tags = array();

        $tags = $DB->get_records_sql(
                "SELECT tg.id, tg.rawname, tg.name, tg.isstandard, tg.flag, tg.tagcollid
            $fromclause
            $whereclause
            ORDER BY tg.name ASC",
                $params, 0, $limit);

        return $tags;
    }
}
