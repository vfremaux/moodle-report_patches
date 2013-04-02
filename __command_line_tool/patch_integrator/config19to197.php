<?php

/**
* The patch reintegrator is a tool for massively reimport patches 
* from a customised codebase to an update codebase. It relies on the
* explicit marking of patch locations that would have to replace an
* original code segment (whathever it is), between unchanged known
* and recognizable patterns in the code. This assumes patches are not
* too close one from each other (at least 5 code lines).
*
* The tool allows generating a full updated codebase over the updated
* distribution, or generate a diffed or merged codebase from the updated
* reference. Diff will only produce patched verison of updated files
* where needed, though merge will produce the complete codebase. 
*/

global $CFG;

// CODE LOCATIONS

// The absolute baseroot for working in
$CFG->baseroot = "D:/wwwroot";

//the root for customized source code	
$CFG->sourcepath = 'WWW-MOODLE1_9-PHP';
$CFG->sourceroot = $CFG->baseroot.'/'.$CFG->sourcepath;

// the root for the clean distribution
$CFG->destpath = 'MOODLE_1.9_STABLE/moodle1_9_7';
$CFG->destroot = $CFG->baseroot.'/'.$CFG->destpath;

// the root for the merged source
$CFG->mergepath = 'WWW-MOODLE1_9_7-PHP';
$CFG->mergeroot = $CFG->baseroot.'/'.$CFG->mergepath;

// if recurse, will recurse in all subdirs
$CFG->recurse = true;

// OUTPUT CONTROL

//mode of reintegration generation
// - upgrade : upgrades target with changes
// - xmerge : produces a merged version in a third location
// - xdiff : produces a diffed version in a third location (only altered files)
$CFG->mode = 'xmerge';

// if nowrite, just gives report
$CFG->nowrite = true;

// the log mode ('output' or 'file')
$CFG->log = 'output';

// the log mode
// $CFG->logitems = 'patches,errors';
$CFG->logitems = 'errors,patches';

// the output file for logging
$CFG->logfile = 'patchreport.log';

// switches on the special debugging of the file system library.
$CFG->filedebug = false;

// FILTERS

// A filter to exlude some patterns in the scanner (passthrough)
$CFG->scanexclusions = 'CVS,x_tmp,x_prod,x_tools,x_save,x_sql,pix,mails,Zend,pear';

// A filter to exlude some patterns in the merge
$CFG->mergeexclusions = 'CVS,x_tmp,x_prod,x_tools,x_save,x_sql';

// Defines all known plugin roots in a Moodle installation
// any missing dir in the lost or immediate subdirs should be reported from source
// as being azn additional plugin.
$CFG->pluginroots = array('auth', 
                          'admin/report', 
                          'blocks', 
                          'mod', 
                          'filter', 
                          'course/format', 
                          'question/format', 
                          'question/type',
                          'enrol',
                          'local',
                          'theme',
                          'cms',
                          'lib/editor/htmlare/plugins');

// PATTERNS AND DETECTOR PARAMETERS

//start pattern consider a full line patch mark pattern 
$CFG->patchstartpattern = "\\s*\/\/\/?\\s*PATCH [^\\n]*\\n";

//end pattern consider a full line patch mark pattern 
$CFG->patchendpattern = "\\n*\\s*\/\/\/?\\s*\/PATCH\\s*";

//the number of lines over the start patch pattern for reintegration location
$CFG->scanaroundlinesover = 5;

//the number of lines beneath the end patch pattern for reintegration location
$CFG->scanaroundlinesbeneath = 5;

//start pattern consider a full line patch mark pattern will need /s modifier
$CFG->scanaroundupperpattern = "(?:[^\\n]*\\n){1,{$CFG->scanaroundlinesover}}";

//end pattern consider a full line patch mark pattern 
$CFG->scanaroundlowerpattern = "(?:[^\\n]*\\n){1,{$CFG->scanaroundlinesbeneath}}";
?>

