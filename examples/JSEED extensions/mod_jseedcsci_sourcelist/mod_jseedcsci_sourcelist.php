<?php

defined( '_JEXEC' ) or die( 'Restricted access' );  // no direct access

define("SITEROOT", "../");   // because one of the searches is relative to the primary script at seeds.ca/Joomla/index.php
include_once( SITEROOT."site.php" );
include_once(SEEDCOMMON."siteStart.php");
include_once(SEEDCOMMON."sl/csci.php" );


$kfdb = SiteKFDB() or die( "Cannot connect to database" );
$oCSCI = new SL_CSCI( $kfdb );

$sPsp = SEEDSafeGPC_GetStrPlain('psp');
$sOutput = $sPsp ? $oCSCI->DrawSeedSourceList($sPsp) : "";

require( JModuleHelper::getLayoutPath( 'mod_jseedcsci_sourcelist' ) );
?>
