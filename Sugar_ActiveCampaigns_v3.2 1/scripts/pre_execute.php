<?php
/**
 * Copyright (c) 2013 DocuSign Incorporated
 * All rights reserved.
 *
 * The information contained herein is confidential and proprietary to
 * ET_DocuSign Incorporated, and considered a trade secret
 * as defined under civil and criminal statutes.  ET_DocuSign
 * Incorporated shall pursue its civil and criminal remedies in the event
 * of unauthorized use or misappropriation of its trade secrets.  Use
 * of this information by anyone other than authorized employees of
 * ET_DocuSign Incorporated is granted only under a written
 * non-disclosure agreement, expressly prescribing the scope and manner
 * of such use.
 **/
if (!defined('sugarEntry')) define('sugarEntry', true);

global $sugar_config;
global $db;



require_once('modules/Configurator/Configurator.php');
$cfg = new Configurator();
$cfg->config['uninstallOnError'] = false;
$cfg->handleOverride();

require_once('modules/Administration/QuickRepairAndRebuild.php');
$modules = array(
    'Administration',
);
$actions = array(
    'clearAll',
    'rebuildAuditTables',
    'rebuildExtensions',
    'repairDatabase',
);
$autoexecute = true; // execute the SQL
$show_output = false; // don't output to the screen

$rnc = new RepairAndClear();
$rnc->repairAndClearAll($actions, $modules, $autoexecute, $show_output);
