<?php

define( SITEROOT, "../../" );
require( SITEROOT."site2.php");
require(SEEDCOMMON."siteSetup.php");

SiteSetup( "office" ) or die();    // seedcommon, sets up all the common stuff like SEEDSession, SEEDPerms, DocRep

?>
