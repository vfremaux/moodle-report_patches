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

defined('MOODLE_INTERNAL') || die();

/**
 * @author Valery Fremaux valery@valeisti.fr
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package report_patches
 * @category report
 */

define('IDLE', 0);
define('START_MATCHED', 1);
define('END_MATCHED', 2);

/**
 * scans the complete code base for patches
 */
function report_patches_scan($path) {
    global $CFG, $DB;

    $DIR = opendir($path);
    while ($entry = readdir($DIR)) {
        if (preg_match('/^\./', $entry)) {
            continue;
        }

        // Make some exludes for optimization.
        $excludepatterns = explode(' ', @$CFG->report_patches_scanexcludes);

        // Some standard.
        $excludepatterns[] = '^x_.*';
        $excludepatterns[] = '^pix$';
        $excludepatterns[] = '^CVS$';
        $excludepatterns[] = '\.git$';
        $excludepatterns[] = '\.(jpg|png|gif|log|txt|swf|pdf)$';
        $excludepatterns[] = 'README|readme';

        foreach ($excludepatterns as $apattern) {
            if (!empty($apattern) && preg_match("/$apattern/", $entry)) {
                // echo " => rejecting $entry on pattern $apattern<br/>";
                continue 2;
            }
        }

        if (is_dir("$path/$entry")) {
            // echo "found dir : $path/$entry<br/>";
            report_patches_scan("$path/$entry");
        } else {
            $buffer = file("$path/$entry");
            $buffer = str_replace('\r', '', $buffer); // normalize to unix code

            $state = 0;
            $maxline = count($buffer);
 
            if (empty($CFG->report_patches_openpattern)) {
                set_config('report_patches_openpattern', '//!? PATCH');
                set_config('report_patches_closepattern', '//!? /PATCH');
            }

            $openpattern = str_replace('/', '\\/', $CFG->report_patches_openpattern);
            $closepattern = str_replace('/', '\\/', $CFG->report_patches_closepattern);
            for ($i = 0  ; $i < $maxline - 1; $i++) {
                switch ($state) {
                    case IDLE:
                        while($i <= $maxline - 1 && !preg_match("/{$openpattern}\\s*:\\s*(.*)/", $buffer[$i], $matches)) {
                            $i++;
                        }
                        if ($i < $maxline) {
                            $state = START_MATCHED;
                            $patchrec = new StdClass();
                            $patchrec->plugin = '';
                            $patchrec->path = str_replace($CFG->dirroot.'/', '', "$path/$entry");
                            $patchrec->linestart = $i + 1;
                            $patchrec->comment = addslashes($matches[1]);
                        }
                        break;
                    case START_MATCHED:
                        while ($i <= $maxline - 1 && !preg_match("/{$closepattern}/", $buffer[$i])) {
                            $i++;
                        }
                        if ($i < $maxline) {
                            $state = END_MATCHED;
                            $patchrec->lineend = $i + 1;
                            if (!$DB->insert_record('patches', $patchrec)) {
                                print_error('errorcouldnotinsert', 'report_patches');
                            }
                        }
                        break;
                    case END_MATCHED : 
                        while($i <= $maxline - 1 && !preg_match("/{$openpattern}\\s*:\\s*(.*)/", $buffer[$i], $matches)) {
                            $i++;
                        }
                        if ($i < $maxline) {
                            $state = START_MATCHED;
                            $patchrec = new StdClass();
                            $patchrec->plugin = '';
                            $patchrec->path = str_replace($CFG->dirroot.'/', '', "$path/$entry");
                            $patchrec->linestart = $i + 1;
                            $patchrec->comment = $matches[1];
                        }
                        break;
                    default:
                }
            }

            unset($buffer);
        }
    }
}
