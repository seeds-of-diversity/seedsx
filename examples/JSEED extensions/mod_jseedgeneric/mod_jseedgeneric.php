<?php

defined( '_JEXEC' ) or die( 'Restricted access' );  // no direct access

define("SITEROOT", "../");   // because one of the searches is relative to the primary script at seeds.ca/Joomla/index.php
include_once( SITEROOT."site.php" );
include_once(SEEDCOMMON."siteStart.php");
include_once(SEEDCOMMON."joomla/seedJoomlaModule.php");


$kfdb = SiteKFDB() or die( "Cannot connect to database" );
$oJMod = new SEEDJoomlaModule( $kfdb );
$oJMod->DoModule( $params );  // pass the object that contains module parameters (set in the Joomla administrator module manager)

require( JModuleHelper::getLayoutPath( 'mod_jseedgeneric' ) );

?>
