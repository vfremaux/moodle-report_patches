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
 * @author Valery Fremaux valery@valeisti.fr
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package report_patches
 * @category report
 */

define('NO_OUTPUT_BUFFERING', true);

require('../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/report/patches/locallib.php');

// Security.

require_login();
require_capability('moodle/site:config', context_system::instance());

ini_set('memory_limit', '2048M');
@set_time_limit(0);

// MVC.
$action = optional_param('what', '', PARAM_TEXT);

if ($action == 'scan') {
    $memlimit = ini_get('memory_limit');
    ini_set('memory_limit', '512M');
    $DB->delete_records('patches');
    report_patches_scan($CFG->dirroot);
    redirect(new moodle_url('/report/patches/index.php'));
}

// Print the header.
admin_externalpage_setup('reportsecurity', '', null, '', array('pagelayout' => 'report'));
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('patchesreport', 'report_patches'));

echo $OUTPUT->heading(get_string('patchlist', 'report_patches'), 2);

$order = optional_param('order', 'path', PARAM_TEXT);

$patches = $DB->get_records('patches', array(), $order);

$locationstr = get_string('location', 'report_patches');
$startlinestr = get_string('startline', 'report_patches');
$endlinestr = get_string('endline', 'report_patches');
$purposestr = get_string('purpose', 'report_patches');
$orderbypathstr = get_string('orderbypath', 'report_patches');
$orderbyfeaturestr = get_string('orderbyfeature', 'report_patches');

if ($order != 'path') {
    $locationurl = new moodle_url('/report/patches/index.php', array('order' => 'path'));
    $locationlink = '<a href="'.$locationurl.'" title="'.$orderbypathstr.'" >'.$locationstr.'</a>';
} else {
    $locationlink = $locationstr;
}

if ($order != 'comment') {
    $purposeurl = new moodle_url('/report/patches/index.php', array('order' => 'comment'));
    $purposelink = '<a href="'.$purposeurl.'" title="'.$orderbyfeaturestr.'" >'.$purposestr.'</a>';
} else {
    $purposelink = $locationstr;
}

$table = new html_table();

$table->head = array("<b>$locationlink</b>", "<b>$startlinestr</b>", "<b>$endlinestr</b>", "<b>$purposelink</b>");
$table->width = "100%";
$table->size = array('50%', '5%', '5%', '40%');
$table->align = array('left', 'left', 'left', 'left');

$buffer = '';

if (!empty($patches)) {
    foreach ($patches as $patch) {
        if ($patch->$order == $buffer) {
            $patch->$order = '';
        } else {
            $buffer = $patch->$order;
        }
        $table->data[] = array($patch->path, $patch->linestart, $patch->lineend, $patch->comment);
    }
    echo html_writer::table($table);
} else {
    print_string('nopatches', 'report_patches');
}

echo '<p><center>';
$buttonurl = new moodle_url('/report/patches/index.php', array('what' => 'scan'));
echo $OUTPUT->single_button($buttonurl, get_string('scan', 'report_patches'));
echo '</center></p>';

// Footer.
echo $OUTPUT->footer();