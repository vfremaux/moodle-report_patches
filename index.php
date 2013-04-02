<?php
/**
 * Manage patches in Moodle code.
 *
 * @copyright &copy; 2010 valeisti
 * @author Valery Fremaux valery@valeisti.fr
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package patches
 */

define('NO_OUTPUT_BUFFERING', true);

/** */
require_once(dirname(__FILE__).'/../../config.php');
require_once($CFG->libdir.'/adminlib.php');
require_once($CFG->dirroot.'/report/patches/locallib.php');

require_login();
require_capability('moodle/site:config', get_context_instance(CONTEXT_SYSTEM));

raise_memory_limit(MEMORY_EXTRA);
@set_time_limit(0);

// MVC
$action = optional_param('what', '', PARAM_TEXT);

if ($action == 'scan'){
    $memlimit = ini_get('memory_limit');
    ini_set('memory_limit', '512M');
    $DB->delete_records('report_patches');    
    report_patches_scan($CFG->dirroot);
    redirect($CFG->wwwroot.'/report/patches/index.php');
}

// Print the header.
// Print the header.
admin_externalpage_setup('reportsecurity', '', null, '', array('pagelayout'=>'report'));
echo $OUTPUT->header();

echo $OUTPUT->heading(get_string('patchesreport', 'report_patches'));

echo $OUTPUT->heading(get_string('patchlist', 'report_patches'), 2);

$order = optional_param('order', 'path', PARAM_TEXT);

$patches = $DB->get_records('report_patches', array(), $order);

$locationstr = get_string('location', 'report_patches');
$startlinestr = get_string('startline', 'report_patches');
$endlinestr = get_string('endline', 'report_patches');
$purposestr = get_string('purpose', 'report_patches');
$orderbypathstr = get_string('orderbypath', 'report_patches');
$orderbyfeaturestr = get_string('orderbyfeature', 'report_patches');

if ($order != 'path'){
    $locationlink = "<a href=\"{$CFG->wwwroot}/report/patches/index.php?order=path\" title=\"$orderbypathstr\" >$locationstr</a>";
} else {
    $locationlink = $locationstr;
}

if ($order != 'comment'){
    $purposelink = "<a href=\"{$CFG->wwwroot}/report/patches/index.php?order=comment\" title=\"$orderbyfeaturestr\" >$purposestr</a>";
} else {
    $purposelink = $locationstr;
}

$table = new html_table();

$table->head = array("<b>$locationlink</b>", "<b>$startlinestr</b>", "<b>$endlinestr</b>", "<b>$purposelink</b>");
$table->width = "100%"; 
$table->size = array('50%', '5%', '5%', '40%'); 
$table->align = array('left', 'left', 'left', 'left'); 

$buffer = '';

if(!empty($patches)){
    foreach($patches as $patch){
        if ($patch->$order == $buffer){
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
echo $OUTPUT->single_button($CFG->wwwroot.'/report/patches/index.php?what=scan', get_string('scan', 'report_patches'));
echo '</center></p>';

// Footer.
echo $OUTPUT->footer();

?>
