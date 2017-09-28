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
 * Oracle data import.
 *
 * @package     local_coursemanager
 * @copyright   2016 Motorpilot Ltd / Sonsbeekmedia.nl
 * @author      Bas Brands, Simon Lewis
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../config.php');
require_once("$CFG->libdir/clilib.php");

cli_logo();
cli_writeln('');
cli_heading('Arup Oracle historical data import script');
cli_writeln('');

if (!CLI_MAINTENANCE) {
    cli_writeln('CLI maintenance mode _MUST_ be active, to run this script.');
    exit(1);
}

if (moodle_needs_upgrading()) {
    cli_writeln('Moodle upgrade pending, cannot execute script.');
    exit(1);
}

list($options, $unrecognized) = cli_get_params(
    ['help' => false, 'truncate' => false, 'import' => false,
        'confirm' => false,
        'protocol' => false, 'host' => false, 'port' => false, 'sid' => false, 'username' => false, 'password' => false],
    ['h' => 'help', 't' => 'truncate', 'i' => 'import']
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help'] || (!$options['truncate'] and !$options['import'])) {
    $help =
"Imports historical data from Oracle complete data views to Moodle local data cache.

Options:

-h, --help            Print out this help
-t, --truncate        Truncate existing tables ready for import
-i, --import          Import data

For truncation:
--confirm             Do not prompt for confirmation before truncating

For importing (if not supplied will be prompted for):
--protocol            Oracle DB connection protocol
--host                Oracle DB connection host
--port                Oracle DB connection port
--sid                 Oracle DB connection SID
--username            Oracle DB connection username
--password            Oracle DB connection pasword


Example:
\$sudo -u www-data /usr/bin/php local/coursemanager/cli/oracle_data_import.php --import --protocol=TCP

";
    echo $help;
    die;
}

// Increase memory limit.
raise_memory_limit(MEMORY_EXTRA);

$predbqueries = $DB->perf_get_queries();
$pretime = microtime(true);

$tables = ['local_taps_course', 'local_taps_course_category', 'local_taps_class', 'local_taps_enrolment'];

if ($options['truncate']) {
    cli_heading('Table truncation');

    // Are they sure?
    if (!$options['confirm']) {
        $yesorno = cli_input('Are you sure you wish to truncate the existing local_taps_* tables?', 'n', ['y', 'n'], false);
        if ($yesorno !== 'y') {
            cli_writeln('Aborting table truncation, thank you for stopping by.');
            exit(0);
        }
    }
    
    // Reset time to allow for variable time taken to input parameters.
    $pretime = microtime(true);

    // Are we here? Well, let's truncate.
    cli_writeln('Beginning table truncation...');
    
    foreach ($tables as $table) {
        $DB->delete_records($table);
        cli_writeln("Truncated: {$table}");
    }
    cli_writeln('Completed table truncation.');

    cli_writeln('DB queries: ' . ($DB->perf_get_queries() - $predbqueries));
    cli_writeln('Time: ' . (microtime(true) - $pretime) . ' seconds');

    exit(0);
}

if ($options['import']) {
    cli_heading('Import Oracle data');
    $oraclecfg = new stdClass();
    $oraclecfg->protocol = !empty($options['protocol']) ? $options['protocol'] : cli_input('Oracle DB connection protocol');
    $oraclecfg->host = !empty($options['host']) ? $options['host'] : cli_input('Oracle DB connection host');
    $oraclecfg->port = !empty($options['port']) ? $options['port'] : cli_input('Oracle DB connection port');
    $oraclecfg->sid = !empty($options['sid']) ? $options['sid'] : cli_input('Oracle DB connection SID');
    $oraclecfg->username = !empty($options['username']) ? $options['username'] : cli_input('Oracle DB connection username');
    $oraclecfg->password = !empty($options['password']) ? $options['password'] : cli_input('Oracle DB connection password');

    // Reset time to allow for variable time taken to input parameters.
    $pretime = microtime(true);

    try {
        $oracle = new local_coursemanager\local\import\oracle_data_import($oraclecfg);

        if (!$oracle->is_connected()) {
            cli_writeln("Connection error: {$oracle->connectionerror}");
        } else {
            cli_writeln('Beginning Oracle data import...');

            cli_writeln('  Importing courses/category mappings.');
            $insertedcourses = $oracle->import('courses');
            cli_writeln("  {$insertedcourses} courses/category mappings inserted.");


            cli_writeln('  Importing classes.');
            $insertedclasses = $oracle->import('classes');
            cli_writeln("  {$insertedclasses} classes inserted.");

            cli_writeln('  Importing enrolment records.');
            $insertedenrolments = $oracle->import('enrolments');
            cli_writeln("  {$insertedenrolments} enrolment records inserted.");

            cli_writeln('  Importing CPD records.');
            $insertedcpds = $oracle->import('cpds');
            cli_writeln("  {$insertedcpds} CPD records inserted.");

            cli_writeln('Completed Oracle data import.');
        }

        cli_writeln('DB queries: ' . ($DB->perf_get_queries() - $predbqueries));
        cli_writeln('Time: ' . (microtime(true) - $pretime) . ' seconds');

        exit(0);
    } catch (Exception $e) {
        if ($DB->is_transaction_started()) {
            $DB->force_transaction_rollback();
        }

        cli_writeln('');
        cli_writeln('Oracle data import failed: ' . $e->getMessage());
        cli_writeln('');

        cli_writeln('DB queries: ' . ($DB->perf_get_queries() - $predbqueries));
        cli_writeln('Time: ' . (microtime(true) - $pretime) . ' seconds');
        

        if ($CFG->debugdeveloper) {
            cli_writeln('');
            if (!empty($e->debuginfo)) {
                cli_writeln('Debug info:');
                cli_writeln($e->debuginfo);
            }
            cli_writeln('Backtrace:');
            cli_writeln(format_backtrace($e->getTrace(), true));
        }

        exit(1);
    }
}