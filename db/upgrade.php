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
 * Manage patches in Moodle code.
 *
 * @author Valery Fremaux valery@edunao.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package report_patches
 * @category report
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_report_patches_upgrade($oldversion = 0) {
    global $DB, $CFG;

    $result = true;

    $dbman = $DB->get_manager();

    if ($oldversion < 2016103000) {

        // Convert settings to component scoped settings.
        set_config('openpattern', 'report_patches', $CFG->report_patches_openpattern);
        set_config('closepattern', 'report_patches', $CFG->report_patches_closepattern);
        set_config('scanexcludes', 'report_patches', $CFG->report_patches_scanexludes);

        // Cleanup site wide settings.
        set_config('report_patches_openpattern', null);
        set_config('report_patches_closepattern', null);
        set_config('report_patches_scanexcludes', null);

        // Restore some defaults if missing.
        $config = get_config('report_patches');
        if (empty($config->openpattern)) {
            set_config('openpattern', '// PATCH+', 'report_patches');
            set_config('closepattern', '// PATCH-', 'report_patches');
        }

        $table = new xmldb_table('patches');
        if ($dbman->table_exists($table)) {
            $dbman->rename_table($table, 'report_patches');
        }

        upgrade_plugin_savepoint(true, 2016103000, 'report', 'patches');
    }

    return $result;
}
