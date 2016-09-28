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

defined('MOODLE_INTERNAL') || die;

// if ($hassiteconfig) { // needs this condition or there is error on login page
$hasadmin = false;
if (is_dir($CFG->dirroot.'/local/adminsettings')) {
    // This is AdminSettings Edunao driven administration 
    if (has_capability('local/adminsettings:nobody', $systemcontext)) {
        $hasadmin = true;
    }
} else {
    // this is Moodle Standard
    if ($ADMIN->fulltree) {
        $hasadmin = true;
    }
}

if ($hasadmin) {

    $ADMIN->add('reports', new admin_externalpage('reportpatchesaccess', get_string('patches', 'report_patches'), new moodle_url('/report/patches/index.php'), 'moodle/site:config'));

    $temp = new admin_settingpage('patches', get_string('patchessettings', 'report_patches'));
    $temp->add(new admin_setting_configtext('report_patches_openpattern', 
                                                   get_string('config_patches_openpattern', 'report_patches'),
                                                   get_string('desc_patches_openpattern', 'report_patches'),
                                                   '// PATCH'));

    $temp->add(new admin_setting_configtext('report_patches_closepattern', 
                                                   get_string('config_patches_closepattern', 'report_patches'),
                                                   get_string('desc_patches_closepattern', 'report_patches'),
                                                   '// /PATCH'));

    $temp->add(new admin_setting_configtext('report_patches_scanexcludes', 
                                                   get_string('config_patches_scanexcludes', 'report_patches'),
                                                   get_string('desc_patches_scanexcludes', 'report_patches'),
                                                   ''));
    $ADMIN->add('server', $temp);
}
