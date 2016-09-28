<?php
// This file keeps track of upgrades to
// the chat module
//
// Sometimes, changes between versions involve
// alterations to database structures and other
// major things that may break installations.
//
// The upgrade function in this file will attempt
// to perform all the necessary actions to upgrade
// your older installtion to the current version.
//
// If there's something it cannot do itself, it
// will tell you what you need to do.
//
// The commands in here will all be database-neutral,
// using the functions defined in lib/ddllib.php

/**
 * Manage patches in Moodle code.
 *
 * @author Valery Fremaux valery@edunao.com
 * @license http://www.gnu.org/copyleft/gpl.html GNU Public License
 * @package report_patches
 * @category report
 */

defined('MOODLE_INTERNAL') || die();

function xmldb_report_patches_upgrade($oldversion=0) {
    global $CFG, $THEME, $db;

    $result = true;

    return $result;
}