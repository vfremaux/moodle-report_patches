<?php

/**
*
*/

include_once "patchlib.php";
include_once "filesystemlib.php";


/// decoding entry parameters and parameter check

global $CFG;
global $ARGS_ARG;

// get sufficient parameters from command line
$ARGS_ARG = array('-D' => array('bind' => 'xbaseroot', 'arg' => 1, 'mandatory' => FALSE),  // The starting path
             '-S' => array('bind' => 'xsourceroot', 'arg' => 1, 'mandatory' => FALSE), // The absolute source path (where patches are)
             '-T' => array('bind' => 'xdestroot', 'arg' => 1, 'mandatory' => FALSE),  // The absolute target path (where upgrade is)
             '-M' => array('bind' => 'xmergeroot', 'arg' => 1, 'mandatory' => FALSE),  // The absolute root where to merge or diff
             '-s' => array('bind' => 'xsourcepath', 'arg' => 1, 'mandatory' => FALSE), // The relative path where source is (using baseroot)
             '-t' => array('bind' => 'xdestpath', 'arg' => 1, 'mandatory' => FALSE),  // The relative target where upgrzde is (using baseroot)
             '-m' => array('bind' => 'xmergepath', 'arg' => 1, 'mandatory' => FALSE),  // The relative merge path (using baseroot)
             '-f' => array('bind' => 'xconfig', 'arg' => 1, 'mandatory' => FALSE)  // An alternate configuration file
             );

$ARGS->argumentNeeded = 0;
$ARGS->argCaptureCount = 0;
$ARGS->lastArg = '';
$ARGS->realArgs = array();

if (in_array('-h', $_SERVER['argv'])){
    echo ("Usage : php.exe main.php -D <baseroot> -s <dir> -t <dir> -m <dir>\n");
    echo ("      : php.exe main.php -S <sourceroot> -T <targetroot> -M <mergeroot>\n");
    echo ("      : php.exe main.php -f <configfilename>\n");
    echo ("\t -D : the base root where to apply, use with -s, -t, and -m\n");
    echo("\t -S : The absolute merge path where patches are in\n");
    echo("\t -T : The absolute target path where upgrade is\n");
    echo("\t -M : The absolute merge path where to put result in\n");
    echo("\t -s : The relative source path with patches (using baseroot, needs -D)\n");
    echo("\t -t : The relative target path (using baseroot, needs -D)\n");
    echo("\t -m : The relative merge path (using baseroot, needs -D)\n");
    echo("\t -f : An alternate configuration file that overrides all settings\n");
    echo("\n");
    exit(0);
}

$ARGS->commandLine = $_SERVER['argv'];
array_shift($ARGS->commandLine);

foreach($ARGS->commandLine as $anArg){
    if (preg_match("/^-/", $anArg)){
        if ($ARGS->argumentNeeded == 0) {
            $CFG->{$ARGS_ARG[$anArg]['bind']} = TRUE;
        }
        if ($ARGS->argumentNeeded > 0) die("missing argument for {$ARGS->lastArg}\nAborting.\n");
        if ($ARGS->argumentNeeded > 0 && $ARGS->argumentNeeded == $ARGS->argCaptureCount) die("not enough arguments for {$ARGS->lastArg}\nAborting.\n");
        if (in_array($anArg, array_keys($ARGS_ARG))){
            // echo "setarg "; 
            $ARGS->argumentNeeded = $ARGS_ARG[$anArg]['arg'];
            $ARGS->argCaptureCount = 0;
            $ARGS->lastArg = $anArg;
            $ARGS->realArgs[] = $ARGS->lastArg;
        }
    } else {
       // echo "setval "; 
       if ($ARGS->argumentNeeded > 1){
          if ($ARGS->argCaptureCount == 0){
             $CFG->{$ARGS_ARG[$ARGS->lastArg]['bind']} = array($anArg);
             $ARGS->argCaptureCount++;
             if ($ARGS->argCaptureCount == $ARGS->argumentNeeded){
                $ARGS->argumentNeeded = 0;
             }
          } else {
             $CFG->{$ARGS_ARG[$ARGS->lastArg]['bind']}[] = $anArg;
             $ARGS->argCaptureCount++;
          }
       } else {
          $CFG->{$ARGS_ARG[$ARGS->lastArg]['bind']} = $anArg;
          $ARGS->argumentNeeded = 0;
       }
    }
}
// check last arg position.
if ($ARGS->argCaptureCount != $ARGS->argumentNeeded){
    die("missing argument for {$ARGS->lastArg}\nAborting.\n");
}

function mandatories($a){
    global $CFG;
    global $ARGS_ARG;
    return $ARGS_ARG[$a]['mandatory'];
}

$mandatoryArgs = array_filter(array_keys($ARGS_ARG), 'mandatories');
$ARGS->diffArgs = array_diff($mandatoryArgs, $ARGS->realArgs);
if (!empty($ARGS->diffArgs)){
    die('missing parameters ' . implode(",", $ARGS->diffArgs) . "\nAborting.\n");
}

/// Resolving parameter situations

if (!empty($CFG->xconfig)){
    // override any other argument settings
    if (file_exists($CFG->xconfig)){
        include_once $CFG->xconfig;
    } else {
        die("Alternate configuration file not found. terminating...\n");
    }
    
} else {
    include_once "config.php";
}

if (!empty($CFG->xbaseroot)){
    if (!empty($CFG->xsourceroot) || !empty($CFG->xtargetroot) || !empty($CFG->xmergeroot)){
        die("You are using a baseroot. Tou need using options -s, -t, and -m and not -S, -T, -M\n");
    }
    if (!empty($CFG->xsourcepath)){
        $CFG->sourcepath = $CFG->xsourcepath;
    }
    if (!empty($CFG->xdestpath)){
        $CFG->destpath = $CFG->xdestpath;
    }
    if (!empty($CFG->xmergepath)){
        $CFG->mergepath = $CFG->xmergepath;
    }
} else {
    if (!empty($CFG->xsourcepath) || !empty($CFG->xdestpath) || !empty($CFG->xmergepath)){
        die("You are overriding roots. Tou need using options -S, -T, and -M and not -s, -t, -m\n");
    }
    if (!empty($CFG->xsourceroot)){
        $CFG->sourceroot = $CFG->xsourceroot;
    }
    if (!empty($CFG->xdestroot)){
        $CFG->destpath = $CFG->xdestroot;
    }
    if (!empty($CFG->xmergeroot)){
        $CFG->mergeroot = $CFG->xmergeroot;
    }
}

/// Starting really the job

// check if sourcepath and destpath are Moodle code instances

if ($CFG->recurse){
    // perform all checks only in recurse mode when trying to operate on a full codebase.
    if (!file_exists($CFG->sourceroot.'/version.php')){
        die("Source directory has no version file. Source is probably not a Moodle component. Terminating...");
    }
    
    if (!file_exists($CFG->destroot.'/version.php')){
        die("Target directory has no version file. Target is probably not a Moodle component. Terminating...");
    }
    
    $sourceversionfile = $CFG->sourceroot.'/version.php';
    
    $versioncontent = file($sourceversionfile);
    if (!preg_grep('/MOODLE/i', $versioncontent)){
        die("We guessed source IS NOT a moodle codebase. please check your config.php file. Terminating...");
    }
    
    $sourcerelease = get_moodle_release($sourceversionfile);
    $sourceversion = get_moodle_version($sourceversionfile);
    
    $destversionfile = $CFG->destroot.'/version.php';
    
    $versioncontent = file($destversionfile);
    if (!preg_grep('/MOODLE/i', $versioncontent)){
        die("We guessed target IS NOT a moodle codebase. please check your config.php file. Terminating...");
    }
    
    $destrelease = get_moodle_release($destversionfile);
    $destversion = get_moodle_version($destversionfile);
    
    if (moodle_compare_versions($sourceversion, $destversion) > 0){
        die("The target is running an older code than the customized codebase. Patch reports cannot be performed. Terminating...");
    }

    if (moodle_compare_versions($sourceversion, $destversion) == 0){
        die("The target is running same version code than the customized codebase. Patch reports cannot be performed. Terminating...");
    }
}

echo "Starting working...\n";
if ($CFG->nowrite){
    report('all', "Running in NON WRITE MODE, for testing...");
}
if ($CFG->recurse){
    report('all', "Running in RECURSIVE mode...");
} else {
    report('all', "Running in NON RECURSIVE mode...");
}

$res->patchcount = 0;
$res->notfoundcount = 0;
$res->toomanyfoundcount = 0;

// some eventual cleanup
$CFG->sourceroot = rtrim ($CFG->sourceroot, '/');
$CFG->destroot = rtrim ($CFG->destroot, '/');
$CFG->mergeroot = rtrim ($CFG->mergeroot, '/');

if (!patchintegrator('', $res)){
    report('all', "no integration...");
}

report('allforceout', "Successful reintegrations : ". $res->patchcount);
report('allforceout', "Missed reintegrations : ". $res->notfoundcount);
report('allforceout', "Too many locations : ". $res->toomanyfoundcount);

echo "Done...";

/**
* this function get a version of a distrribution by locally including the version file.
*/
function get_moodle_release($versionpath){
    include $versionpath;
    
    return $release;
}

/**
* this function get a version of a distrribution by locally including the version file.
*/
function get_moodle_version($versionpath){
    include $versionpath;
    
    return $version;
}

function moodle_compare_versions($src, $dst){
	if (strstr($src, '.') !== false){
		list($srcmajor,$srcminor) = explode('.', $src);
	} else {
		$srcmajor = $src;
		$srcminor = 0;
	}
	if (strstr($dst, '.') !== false){
		list($dstmajor,$dstminor) = explode('.', $dst);
	} else {
		$dstmajor = $src;
		$dstminor = 0;
	}
	
	if ($srcmajor > $dstmajor) return 1;
	if (($srcmajor == $dstmajor) && ($srcminor > $dstminor)) return 1;
	if (($srcmajor == $dstmajor) && ($srcminor == $dstminor)) return 0;
	return -1;	
}
?>