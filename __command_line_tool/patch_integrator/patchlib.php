<?php

/**
* entering in the patch reintegrztor assumes we have checked that poth roots are actual Moodle distributions
* scanning will be operated from the customized source
*/
function patchintegrator($sourcedir, &$res, $skipped = false) {
    global $CFG;
    
	$sourcedir = rtrim($sourcedir, '/'); // on vire un eventuel slash mis par l'utilisateur de la fonction a droite du repertoire

	if (is_dir ($CFG->sourceroot.'/'.$sourcedir)){
		$sourcehandle = opendir($CFG->sourceroot.'/'.$sourcedir);
	} else {
	    report('errors', "no dir in $CFG->sourceroot/$sourcedir");
	    return false;
    }
    
    $sourcefiles = array();
    
    while (($source = readdir($sourcehandle)) !== false ) { //boucle pour parcourir le repertoire $dir1
    	if (preg_match("/^\\./", $source)) continue; // silently ignore hidden files
    	
    	$skipped = false;
    	
        if (!empty($sourcedir)){
    	    $sourcepath = $sourcedir.'/'.$source;
    	} else {
    	    $sourcepath = $source;
    	}

    	if (preg_match("/\\b{$source}\\b/", $CFG->scanexclusions)){
    	    report('debug', "Skipping entry $sourcepath (SCAN EXCLUDE). will browse through for merge.");
    	    $skipped = true; // propagate a skipped marker to quicker browse unscanned branches 
    	}

        // collect source fiels to get new entries from target.
	    $sourcefiles[] = $source;

    	if (is_dir($CFG->sourceroot.'/'.$sourcepath)) { //si on tombe sur un sous-repertoire
    	    if ($CFG->recurse){
    	        // only dives when needs to get all merged files
    	        // in diff mode, the branch should not be examined
    	        // in upgrade mode, leave destination code unchanged. 
        	    if ($CFG->mode == 'xmerge' || !$skipped){
                    // if destination does not exist and we are in some pluggable locations, report all source plugin
                    // without discussion (there will be no reportable patch anyway here).
                    if ($CFG->mode == 'xmerge' && !is_dir($CFG->destroot.'/'.$sourcepath)){
                        $prefixpattern = str_replace("/", "\\/", implode("|", $CFG->pluginroots)); // all prefixes in locations we can copy something.
                        if (preg_match("/^($prefixpattern)/", $sourcepath)){
                            if (!$CFG->nowrite)
                                filesystem_copy_tree($CFG->sourceroot.'/'.$sourcepath, $CFG->mergeroot.'/'.$sourcepath, '');
            	            report('patches', "Adding component $sourcepath");
                            continue; // don't go lower !!
                        }
                    }

            	    report('browse', "Diving in $sourcepath");
            		$result = patchintegrator($sourcepath, $res, $skipped); // appel recursif pour lire a l'interieur de ce sous-repertoire
            	}
        	} else {
        	    report('all', "Skipping dir $sourcepath (NO RECURSE)");
        	}
    	} else {
        	if (!preg_match("/(\\.php|\\.html|\\.js|\\.css)$/", $source)) {
        	    // concerns all non 'code' files
        	    // if they are in dest and we are merging, copy them from the dest location, or ignore them.
        	    if ($CFG->mode == 'xmerge' && file_exists($CFG->destroot.'/'.$sourcepath)){
                    if (!$CFG->nowrite)
            	        filesystem_copy_file($CFG->destroot.'/'.$sourcepath, $CFG->mergeroot.'/'.$sourcepath, '');
        	    }
        	    continue;
        	} else {
        	    $skipped = $skipped || false;
        	}
        	report('browse', ">>>>>>> $sourcepath");
        	if ($result = processpatchsinfile($sourcepath, $skipped)){        	
            	$res->patchcount += $result->patchcount;
            	$res->notfoundcount += $result->notfoundcount;
            	$res->toomanyfoundcount += $result->toomanyfoundcount;
            }
        }        
    }
    
    if ($CFG->mode == 'xmerge'){    
        // scan for all new files (in upgrade target) and dirs at that level and copy them to merged
        if (is_dir($CFG->destroot.'/'.$sourcedir)){
            $DESTDIR = opendir($CFG->destroot.'/'.$sourcedir);
            while($destfile = readdir($DESTDIR)){
 
                // filter some files or dirs we don't want to copy
    	        if (preg_match("/^\\./", $destfile)) continue;
    	        if (preg_match("/\\b{$destfile}\\b/", $CFG->mergeexclusions)) continue;
                
                if (!in_array($destfile, $sourcefiles)){
                    if (is_dir($CFG->destroot.'/'.$sourcedir.'/'.$destfile)){
                        if ($CFG->recurse && !$CFG->nowrite)
                            filesystem_copy_tree($CFG->destroot.'/'.$sourcedir.'/'.$destfile, $CFG->mergeroot.'/'.$sourcedir.'/'.$destfile, ''); // use with absolute roots
                    } else {
                        if (!$CFG->nowrite)
                            filesystem_copy_file($CFG->destroot.'/'.$sourcedir.'/'.$destfile, $CFG->mergeroot.'/'.$sourcedir.'/'.$destfile, ''); // use with absolute roots
                    }
                }
            }
        }
    }

    closedir($sourcehandle); // on ferme le repertoire courant
    return $res;
} 

/**
* analyses a single file and scan for patches in
*/
function processpatchsinfile($filepath, $skipped = false){
    global $CFG;
    
    if (!file_exists($CFG->destroot.'/'.$filepath)){
        if (!$skipped)
            report('warning', "Warning : destination file does not exist : {$CFG->destroot}/{$filepath}");
        return;
    }
    
    $code = preg_replace("/\\r/", '', implode('', file($CFG->sourceroot.'/'.$filepath))); // forcing to unix
    
    //pattern is :
    //matches[1] : prescanaround
    //matches[2] : complete patch with markers
    //matches[3] : postscanaround

    $offset = 0;

    $originaldestcode = $destcode = preg_replace("/\\r/", '', implode('', file($CFG->destroot.'/'.$filepath))); // force to unix
    $res->patchcount = 0;
    $res->notfoundcount = 0;
    $res->toomanyfoundcount = 0;
        
    if ($skipped == false){
        while ($patch = preg_match("/({$CFG->scanaroundupperpattern})({$CFG->patchstartpattern}.*?{$CFG->patchendpattern})({$CFG->scanaroundlowerpattern})/s", $code, $matches, PREG_OFFSET_CAPTURE, $offset)){
    
            $offset = $matches[2][1] + strlen($matches[2][0]);        
            $prepattern = $matches[1][0];
            $patchlocation = $matches[0][1] + strlen($prepattern);
            $patchline = substr_count(substr($code, 0, $patchlocation), "\n"); // counts number of lines before patch
            $quotedprepattern = preg_quote($prepattern);
            $quotedprepattern = str_replace("/", "\\/", $quotedprepattern);
            $patchcontent = $matches[2][0];
            $postpattern = $matches[3][0];
            $quotedpostpattern = preg_quote($postpattern);
            $quotedpostpattern = str_replace("/", "\\/", $quotedpostpattern);

            // any number of blank lines before patch or after patch should be insignificant
            $quotedprepattern = preg_replace("/(\\s*\\n)*$/", '', $quotedprepattern); // trim right prepattern
            $quotedpostpattern = preg_replace("/^(\\s*\\n)*/", '', $quotedpostpattern); // trim right prepattern

            $quotedprepattern = preg_replace("/\\n/", "\\\\n", $quotedprepattern); // convert endline into preg endline escapes
            $quotedpostpattern = preg_replace("/\\n/", "\\\\n", $quotedpostpattern); 
            
            $destpattern = '/'.$quotedprepattern.'(.*?)'.$quotedpostpattern.'/su';
            
            // test match
            report('pattern', "prematch : ".preg_match("/$quotedprepattern/su", $destcode));
            report('pattern', "prematch pattern : ".$quotedprepattern);
            report('pattern', "postmatch : ".preg_match("/$quotedpostpattern/su", $destcode));
            
            // We search in destcode where the patch could be inserted.
            $locations = preg_match_all($destpattern, $destcode, $matches, PREG_PATTERN_ORDER);
            if (empty($locations)){
                report('errors', "Error : no location found in {$filepath}");
                report('errors', "Original location : {$filepath} §{$patchline}");
                $res->notfoundcount++;
            }
            elseif (count($locations) > 1){
                report('errors', "Error : too many locations in cdest file: {$filepath}");
                report('errors', "Original location : {$filepath} §{$patchline}");
                $res->toomanyfoundcount++;
            } else { // we have a single location !!
                report('all', "Patch found : Original location : {$filepath} §{$patchline}");
                report('patches', "\n*******\nPATCH\n*******\n$patchcontent");
                $destcode = preg_replace($destpattern, "{$prepattern}{$patchcontent}{$postpattern}", $destcode);
                $res->patchcount++;
            }
        }
    } else {
        $res->patchcount = 0;
    }
    
    if (!$CFG->nowrite){
        if ($res->patchcount){
            if ($CFG->mode == 'upgrade'){
                if ($CFG->makebackup){
                    rename($CFG->destroot.'/'.$filepath, $CFG->destroot.'/'.$filepath.'.bak');
                }
                // destination is upgrade
                $DEST = fopen($CFG->destroot.'/'.$filepath, 'w');
            } else {
                // if merge destination dir does not exist, make it
                $newdir = dirname($CFG->mergeroot.'/'.$filepath);
                if (!is_dir($newdir)){
                    if (!filesystem_create_dir($newdir, FS_RECURSIVE, '')){
                        die("error creating directory $newdir");
                    }
                }
                // destination is merge location
                $DEST = fopen($CFG->mergeroot.'/'.$filepath, 'w');
            }
            fputs($DEST, $destcode);
            fclose($DEST);
        } elseif ($CFG->mode == 'xmerge'){
            // in xmerge mode create file from dest even if not patched after creating the directory
            if (!is_dir(dirname($CFG->mergeroot.'/'.$filepath))){
                filesystem_create_dir(dirname($CFG->mergeroot.'/'.$filepath), FS_RECURSIVE, '');
            }
            copy($CFG->destroot.'/'.$filepath, $CFG->mergeroot.'/'.$filepath);
        }
    }
    // if (preg_match("/mnet\\/jump\\.php/", $filepath)) die('Debug stopping');
    unset($destcode); // liberate big buffers
    unset($originaldestcode); // liberate big buffers
    unset($code); // liberate big buffers
    return $res;
}

/**
* do some reports
*/
function report($mode, $str){
    global $CFG;
    
    if (preg_match("/\\b$mode\\b/", $CFG->logitems) || $mode == 'all' || $mode == 'allforceout'){
        if ($CFG->log == 'file'){
            if (empty($CFG->LOGSTREAM)){
                if ($CFG->mode == 'xmerge'|| $CFG->mode == 'xdiff'){
                    $CFG->LOGSTREAM = fopen($CFG->mergeroot.'/'.$CFG->logfile, 'w');
                } else {
                    $CFG->LOGSTREAM = fopen($CFG->destroot.'/'.$CFG->logfile, 'w');
                }
            }
            fputs($CFG->LOGSTREAM, "$str\n");
			if ($mode == 'allforceout'){
                echo $str."\n";
            }
        } elseif ($CFG->log == 'output'){
            echo $str."\n";
        }
    }
    // else do not log anything
}

?>