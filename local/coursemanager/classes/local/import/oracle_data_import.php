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
 * Oracle data import class.
 *
 * @package     local_coursemanager
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
namespace local_coursemanager\local\import;

defined('MOODLE_INTERNAL') || die();

use stdClass;
use Exception;

class oracle_data_import {

    /** @var stdClass Oracle configuration. */
    private $oracle;
    /** @var resource Oracle connection. */
    private $connection;
    /** @var string Oracle connection error. */
    private $connectionerror;

    /**
     * Constructor.
     *
     * @return void
     */
    public function __construct(stdClass $oracle) {
        if (!defined('CLI_SCRIPT') || !CLI_SCRIPT) {
            throw new Exception('Can only be utilised from CLI script');
        }
        $this->oracle = $oracle;
        $this->connect();
    }

    /**
     * Destructor.
     *
     * @return void
     */
    public function __destruct() {
        if (extension_loaded('oci8') && $this->is_connected()) {
            oci_close($this->connection);
        }
    }

    /**
     * Magic getter.
     *
     * @param string $name
     * @return mixed
     * @throws Exception
     */
    public function __get($name) {
        if (method_exists($this, "get_{$name}")) {
            return $this->{"get_{$name}"}();
        }
        if (!isset($this->{$name})) {
            throw new Exception('Undefined property ' .$name. ' requested');
        }
        return $this->{$name};
    }

    /**
     * Create connection to Oracle DB.
     *
     * @return mixed
     */
    private function connect() {
        if (!extension_loaded('oci8')) {
            $this->connection = false;
            $this->connectionerror = 'Required extension (oci8) not loaded.';
            return;
        }

        $db = <<<EOS
(DESCRIPTION =
    (ADDRESS =
        (PROTOCOL = {$this->oracle->protocol})
        (HOST = {$this->oracle->host})
        (PORT = {$this->oracle->port})
    )
    (CONNECT_DATA = (SID = {$this->oracle->sid}))
)
EOS;
        ob_start();
        $this->connection = oci_connect($this->oracle->username, $this->oracle->password, $db);
        if (!$this->is_connected()) {
            $this->connectionerror = oci_error()->message;
        }
        ob_end_clean();
    }

    /**
     * Is Oracle connection set up?
     *
     * @return bool
     */
    public function is_connected() {
        return is_resource($this->connection);
    }

    public function import($what) {
        global $DB;
        $method = "import_{$what}";
        if (method_exists($this, $method) && is_callable([$this, $method])) {
            $result = call_user_func([$this, $method]);
        }
        return $result;
    }

    private function import_courses() {
        $s = oci_parse($this->connection, oracle_course::get_query());

        cli_writeln('    Executing Oracle query.');
        oci_execute($s);
        
        $courses = [];
        $categories = [];
        $totalcount = 0;
        $batchcount = 0;
        cli_writeln('      Inserting data.');
        cli_write('      ');
        while($row = oci_fetch_array($s, OCI_ASSOC+OCI_RETURN_NULLS)) {
            $totalcount++;
            $batchcount++;

            $course = new oracle_course($row);
            $courses[] = $course->get_keyed_data_array($batchcount);

            $category = [];
            $category["courseid_{$batchcount}"] = $course->courseid;
            $category["categoryhierarchy_{$batchcount}"] = $row['COURSE_PRIM_CAT'];
            $category["primaryflag_{$batchcount}"] = 'Y';
            $categories[] = $category;

            if ($batchcount >= 100) {
                // Batch insert.
                // Courses.
                $this->batch_insert(oracle_course::$table, oracle_course::get_fields(false, true), $courses);
                // Categories.
                $this->batch_insert('local_taps_course_category', ['courseid', 'categoryhierarchy', 'primaryflag'], $categories);
                cli_write('.');
                // And reset everything.
                $courses = [];
                $categories = [];
                $batchcount = 0;
            }
        }

        if ($batchcount) {
            // Necessary to sweep up last few.
            // Courses.
            $this->batch_insert(oracle_course::$table, oracle_course::get_fields(false, true), $courses);
            // Categories.
            $this->batch_insert('local_taps_course_category', ['courseid', 'categoryhierarchy', 'primaryflag'], $categories);
            cli_write('.');
        }

        oci_free_statement($s);
        
        cli_writeln('');
        cli_writeln('    Oracle query results processed.');

        return $totalcount;
    }

    private function import_classes() {
        $s = oci_parse($this->connection, oracle_class::get_query());

        cli_writeln('    Executing Oracle query.');
        oci_execute($s);

        $classes = [];
        $totalcount = 0;
        $batchcount = 0;
        cli_writeln('      Inserting data.');
        cli_write('      ');
        while($row = oci_fetch_array($s, OCI_ASSOC+OCI_RETURN_NULLS)) {
            $totalcount++;
            $batchcount++;

            $class = new oracle_class($row);
            $classes[] = $class->get_keyed_data_array($batchcount);

            if ($batchcount >= 100) {
                // Batch insert classes.
                $this->batch_insert(oracle_class::$table, oracle_class::get_fields(false, true), $classes);
                cli_write('.');
                if ($totalcount % 5000 === 0) {
                    cli_writeln('');
                    cli_write('      ');
                }
                // And reset everything.
                $classes = [];
                $batchcount = 0;
            }
        }

        if ($batchcount) {
            // Necessary to sweep up last few classes.
            $this->batch_insert(oracle_class::$table, oracle_class::get_fields(false, true), $classes);
            cli_write('.');
        }

        oci_free_statement($s);

        cli_writeln('');
        cli_writeln('    Oracle query results processed.');

        return $totalcount;
    }

    private function import_enrolments() {
        $s = oci_parse($this->connection, oracle_enrolment::get_query());

        cli_writeln('    Executing Oracle query.');
        oci_execute($s);

        $enrolments = [];
        $totalcount = 0;
        $batchcount = 0;
        cli_writeln('      Inserting data.');
        cli_write('      ');
        while($row = oci_fetch_array($s, OCI_ASSOC+OCI_RETURN_NULLS)) {
            $totalcount++;
            $batchcount++;

            $enrolment = new oracle_enrolment($row);
            $enrolments[] = $enrolment->get_keyed_data_array($batchcount);

            if ($batchcount >= 100) {
                // Batch insert enrolments.
                $this->batch_insert(oracle_enrolment::$table, oracle_enrolment::get_fields(false, true), $enrolments);
                cli_write('.');
                if ($totalcount % 5000 === 0) {
                    cli_writeln('');
                    cli_write('      ');
                }
                // And reset everything.
                $enrolments = [];
                $batchcount = 0;
            }
        }

        if ($batchcount) {
            // Necessary to sweep up last few enrolments.
            $this->batch_insert(oracle_enrolment::$table, oracle_enrolment::get_fields(false, true), $enrolments);
            cli_write('.');
        }

        oci_free_statement($s);

        cli_writeln('');
        cli_writeln('    Oracle query results processed.');

        return $totalcount;
    }

    private function import_cpds() {
        $s = oci_parse($this->connection, oracle_cpd::get_query());

        cli_writeln('    Executing Oracle query.');
        oci_execute($s);

        $cpds = [];
        $totalcount = 0;
        $batchcount = 0;
        cli_writeln('      Inserting data.');
        cli_write('      ');
        while($row = oci_fetch_array($s, OCI_ASSOC+OCI_RETURN_NULLS)) {
            $totalcount++;
            $batchcount++;

            $cpd = new oracle_cpd($row);
            $cpds[] = $cpd->get_keyed_data_array($batchcount);

            if ($batchcount >= 100) {
                // Batch insert cpds.
                $this->batch_insert(oracle_cpd::$table, oracle_cpd::get_fields(false, true), $cpds);
                cli_write('.');
                if ($totalcount % 5000 === 0) {
                    cli_writeln('');
                    cli_write('      ');
                }
                // And reset everything.
                $cpds = [];
                $batchcount = 0;
            }
        }

        if ($batchcount) {
            // Necessary to sweep up last few cpds.
            $this->batch_insert(oracle_cpd::$table, oracle_cpd::get_fields(false, true), $cpds);
            cli_write('.');
        }

        oci_free_statement($s);
        
        cli_writeln('');
        cli_writeln('    Oracle query results processed.');

        return $totalcount;
    }

    /**
     * Batch insert SQL.
     * NB. Possibly not DB agnostic.
     *
     * @global \moodle_database $DB
     * @param string $table
     * @param array $fields
     * @param array $dataarray
     */
    private function batch_insert($table, $fields, $dataarray) {
        global $DB;

        $params = [];

        $sql = "INSERT INTO {{$table}} (";
        $sql .= implode(', ', $fields);
        $sql .= ') VALUES ';

        $datasql = [];
        foreach ($dataarray as $data) {
            $params += $data;
            $datasql[] = '(:' . implode(', :', array_keys($data)) . ')';
        }

        $sql .= implode(', ', $datasql);

        $DB->execute($sql, $params);
    }
}