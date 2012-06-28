<?php

$ADMIN->add('reports', new admin_externalpage('reportpatches', get_string('patches', 'report_patches'), "$CFG->wwwroot/report/patches/index.php", 'moodle/site:config'));

$temp = new admin_settingpage('patches', get_string('patchessettings', 'report_patches'));
$temp->add(new admin_setting_configtext('report_patches_openpattern', 
                                               get_string('config_patches_openpattern', 'report_patches'),
                                               get_string('desc_patches_openpattern', 'report_patches'),
                                               null));

$temp->add(new admin_setting_configtext('report_patches_closepattern', 
                                               get_string('config_patches_closepattern', 'report_patches'),
                                               get_string('desc_patches_closepattern', 'report_patches'),
                                               null));

$temp->add(new admin_setting_configtext('report_patches_scanexcludes', 
                                               get_string('config_patches_scanexcludes', 'report_patches'),
                                               get_string('desc_patches_scanexcludes', 'report_patches'),
                                               null));
$ADMIN->add('server', $temp);

?>