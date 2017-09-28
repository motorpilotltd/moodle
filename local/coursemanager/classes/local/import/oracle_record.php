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
 * The local_coursemanager\local abstract Oracle record object.
 *
 * @package     local_coursemanager
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursemanager\local\import;

defined('MOODLE_INTERNAL') || die();

use Exception;
use DateTime;
use DateTimeZone;

interface i_oracle_record {
    // To workaround needing an abstract static method.
    public static function get_query();
}

/**
 * The local_coursemanager\local abstract Oracle record object class.
 */
abstract class oracle_record implements i_oracle_record {
    /** @var array Moodle table. */
    public static $table;

    /** @var array field mappings. */
    public static $fields = [];

    /** @var array incoming data row. */
    protected $row;
    /** @var array outgoing data row. */
    protected $data = [];
    /** @var array required conversions. */
    protected $conversions = [];
    /** @var DateTimeZone timezone. */
    protected $timezone;

    /**
     * Constructor.
     * Load in data row and process.
     *
     * @param array $row
     * @return void
     */
    public function __construct($row) {
        // Load in row data.
        $this->row = $row;

        // Setup data array.
        $this->data = array_fill_keys(static::get_fields(false, true), null);

        // Start processing.
        $this->preprocessing();

        $tz = isset($this->data['usedtimezone']) ? $this->data['usedtimezone'] : 'UTC';
        try {
            $this->timezone = new DateTimeZone($tz);
        } catch (Exception $e) {
            $this->timezone = new DateTimeZone('UTC');
        }

        foreach (static::get_fields(true, true) as $infield => $outfield) {
            // Only grab from Oracle row if they exist and have an explicit mapping.
            if (!isset($this->data[$outfield])) {
                $this->data[$outfield] = isset($this->row[$infield]) ? $this->row[$infield] : null;
            }
            if (array_key_exists($outfield, $this->conversions)) {
                $this->convert($outfield);
            }
        }
        $this->postprocessing();
    }

    /**
     * Magic getter.
     *
     * @param string $param
     * @return mixed
     */
    public function __get($param) {
        if (isset($this->data[$param])) {
            return $this->data[$param];
        } else {
            return null;
        }
    }

    /**
     * Is data property set?
     *
     * @param string $param
     * @return mixed
     */
    public function __isset($param) {
        if (isset($this->data[$param])) {
            return $this->data[$param];
        } else {
            return null;
        }
    }

    /**
     * Magic setter.
     *
     * @param string $param
     * @param mixed $value
     * @return void
     */
    public function __set($param, $value) {
        $this->data[$param] = $value;
    }

    /**
     * Return outgoing data as object.
     *
     * @return object
     */
    public function get_data_object() {
        return (object) $this->data;
    }

    /**
     * Returns the outgoing data array with keys suffixed with current count.
     * 
     * @param int $count Current counter value.
     * @return array
     */
    public function get_keyed_data_array($count) {
        $data = [];
        foreach ($this->data as $index => $value) {
            $data["{$index}_{$count}"] = $value;
        }
        return $data;
    }

    /**
     * Perform field data conversion.
     *
     * @param string $field
     * @return void
     */
    protected function convert($field) {
        if (!is_null($this->conversions[$field])) {
            switch ($this->conversions[$field]) {
                case 'padstaffid' :
                    $this->data[$field] = str_pad($this->data[$field], 6, '0', STR_PAD_LEFT);
                    break;
                case 'datetotimestampstart' :
                    // DD-MON-YYYY to timestamp of midnight at the start of the day.
                    if (!is_null($this->data[$field])) {
                        $datetime = new DateTime($this->data[$field].' 00:00:00', $this->timezone);
                        $this->data[$field] = $datetime->getTimestamp();
                    } else {
                        $this->data[$field] = 0;
                    }
                    break;
                case 'datetotimestampend' :
                    // DD-MON-YYYY to timestamp of (almost) midnight at the end of the day.
                    if (!is_null($this->data[$field])) {
                        $datetime = new DateTime($this->data[$field].' 23:59:59', $this->timezone);
                        $this->data[$field] = $datetime->getTimestamp();
                    } else {
                        $this->data[$field] = 0;
                    }
                    break;
                case 'datetimetotimestamp' :
                    // YYYY/MM/DD HH:II:SS to timestamp.
                    if (!is_null($this->data[$field])) {
                        $datetime = new DateTime($this->data[$field], $this->timezone);
                        $this->data[$field] = $datetime->getTimestamp();
                    } else {
                        $this->data[$field] = 0;
                    }
                    break;
            }
        }
    }

    /**
     * Pre-process incoming data.
     * Override in subclass.
     *
     * @return void
     */
    abstract protected function preprocessing();

    /**
     * Post-process incoming data.
     * Override in subclass if necessary.
     *
     * @return void
     */
    protected function postprocessing() {
        if (in_array('timemodified', static::get_fields(false, true))) {
            $this->data['timemodified'] = time();
        }
    }

    /**
     * Return field mappings.
     * 
     * @param bool $notlocal Filter out fields with 'LOCAL_*' key.
     * @param bool $notnull Filter out fields with null mapping.
     * @return array
     */
    public static function get_fields($notlocal = false, $notnull = false) {
        $fields = static::$fields;
        if ($notlocal) {
            $fields = array_filter($fields, function($k) {
                return stripos($k, 'LOCAL_') !== 0;
            }, ARRAY_FILTER_USE_KEY);
        }
        
        if ($notnull) {
            $fields = array_filter($fields, function($v) {
                return !is_null($v);
            });
        }

        return $fields;
    }
}