<?php

/*
 * Matt Potts
 * 2011-08-04
 * mod_showSuppliers.php
 * This page builds the module using the ResourceList class
 */

//defined( '_JEXEC' ) or die( 'Restricted access' );  // no direct access
define("SITEROOT", "../");   // because one of the searches is relative to the primary script at seeds.ca/Joomla/index.php
include_once(SITEROOT."seeds.ca/site.php" );
include_once(SEEDCOMMON."siteStart.php");
include_once(SEEDCOMMON."sl/csci.php" );
include_once( PAGE1_TEMPLATE );
//require_once(SITEROOT . "JSEED extensions/mod_showSuppliers/helper.php");

$webPage = executePage();

require_once(JModuleHelper::getLayoutPath('mod_showSuppliers'));
?>