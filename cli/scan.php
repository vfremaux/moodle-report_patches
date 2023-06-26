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
 * This script creates config.php file and prepares database.
 *
 * This script is not intended for beginners!
 * Potential problems:
 * - su to apache account or sudo before execution
 * - not compatible with Windows platform
 *
 * @package    report_patches
 * @category report
 * @subpackage cli
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

global $CLI_VMOODLE_PRECHECK;

define('CLI_SCRIPT', true);
define('CACHE_DISABLE_ALL', true);
$CLI_VMOODLE_PRECHECK = true;

// Force first config to be minimal.

require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php');

if (!isset($CFG->dirroot)) {
    die ('$CFG->dirroot must be explicitely defined in moodle config.php for this script to be used');
}

require_once($CFG->dirroot.'/lib/clilib.php');

// Cli only functions.

// Now get cli options.
list($options, $unrecognized) = cli_get_params(
    array('host'              => false,
          'verbose'           => false,
          'help'              => false,
          'config'            => false),
    array('h' => 'help',
          'H' => 'host',
          'v' => 'verbose',
          'c' => 'config')
);

if ($unrecognized) {
    $unrecognized = implode("\n  ", $unrecognized);
    cli_error(get_string('cliunknowoption', 'admin', $unrecognized));
}

if ($options['help']) {
    $help = "
Command line : Scans code for patch marks.

Options:
-H, --host            Switches to this host virtual configuration before processing
-h, --help            Print out this help
-v, --verbose         Verbose mode
-c, --config          See config

Example:
\$sudo -u www-data /usr/bin/php report/patches/cli/scan.php --host=http://my.virtual.moodle.org [--verbose]
"; // TODO: localize - to be translated later when everything is finished.

    echo $help;
    die;
}

if (!empty($options['host'])) {
    // Arms the vmoodle switching.
    echo('Arming for '.$options['host']."\n"); // Mtrace not yet available.
    define('CLI_VMOODLE_OVERRIDE', $options['host']);
}

// Replay full config whenever. If vmoodle switch is armed, will switch now config.

if (!defined('MOODLE_INTERNAL')) {
    // If we are still in precheck, this means this is NOT a VMoodle install and full setup has already run.
    // Otherwise we only have a tiny config at this location, sso run full config again forcing playing host if required.
    require(dirname(dirname(dirname(dirname(__FILE__)))).'/config.php'); // Global moodle config file.
}
echo 'Config check : playing for '.$CFG->wwwroot."\n";

require_once($CFG->dirroot.'/report/patches/locallib.php');

$memlimit = ini_get('memory_limit');
ini_set('memory_limit', '2048M');
$DB->delete_records('report_patches');

$config = get_config('report_patches');
if (empty($config->openpattern)) {
    set_config('openpattern', '// PATCH+', 'report_patches');
    set_config('closepattern', '// PATCH-', 'report_patches');
}
$openpattern = str_replace('/', '\\/', preg_quote($config->openpattern));
$closepattern = str_replace('/', '\\/', preg_quote($config->closepattern));
echo "Start scan : {$CFG->dirroot}\n";
echo "Scanning with <$openpattern> <$closepattern>\n";

if (!empty($options['config'])) {
    exit(0);
}

report_patches_scan($CFG->dirroot, $options['verbose'], true);

echo "Done.\n";
exit(0); // 0 means success.
