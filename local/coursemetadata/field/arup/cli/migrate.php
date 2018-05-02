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
 * Enable or disable maintenance mode.
 *
 * @package    core
 * @subpackage cli
 * @copyright  2009 Petr Skoda (http://skodak.org)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define('CLI_SCRIPT', true);

require(__DIR__ . '/../../../../../config.php');
require_once("$CFG->libdir/clilib.php");
require_once("$CFG->libdir/adminlib.php");

// Now get cli options.
list($options, $unrecognized) = cli_get_params(array('domigration' => false, 'help' => false),
        array('h' => 'help'));

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

$help =
        "Migrate.

Options:
--domigration         Do the migration
-h, --help            Print out this help

Example:
\$ sudo -u www-data /usr/bin/php admin/cli/maintenance.php
";

if ($options['help']) {

    echo $help;
    die;
}

if (!$options['domigration']) {
    echo $help . "\n";
    exit(0);
}

\coursemetadatafield_arup\migrate::deprecate_arupadvert_tap();