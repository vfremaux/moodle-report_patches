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
 * @author Valery Fremaux valery@valeisti.fr
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package report_patches
 * @category report
 */

defined('MOODLE_INTERNAL') || die;

if ($hassiteconfig) {

    $label = get_string('patches', 'report_patches');
    $pageurl = new moodle_url('/report/patches/index.php');
    $ADMIN->add('reports', new admin_externalpage('reportpatchesaccess', $label, $pageurl, 'moodle/site:config'));

    $key = 'report_patches/openpattern';
    $label = get_string('config_patches_openpattern', 'report_patches');
    $desc = get_string('desc_patches_openpattern', 'report_patches');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '// PATCH+'));

    $key = 'report_patches/closepattern';
    $label = get_string('config_patches_closepattern', 'report_patches');
    $desc = get_string('desc_patches_closepattern', 'report_patches');
    $settings->add(new admin_setting_configtext($key, $label, $desc, '// PATCH-'));

    $key = 'report_patches/scanexcludes';
    $label = get_string('config_patches_scanexcludes', 'report_patches');
    $desc = get_string('desc_patches_scanexcludes', 'report_patches');
    $settings->add(new admin_setting_configtext($key, $label, $desc, ''));
}
