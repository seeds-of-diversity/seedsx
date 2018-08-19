<?php

define( SITEROOT, "../../" );
require( SITEROOT."site.php");
require(SEEDCOMMON."siteSetup.php");

SiteSetup( "seeds" ) or die();    // seedcommon, sets up all the common stuff like SEEDSession, SEEDPerms, DocRep

?>
