<?php
define( "SITEROOT", "./" );
include( "site.php" );

SiteStartSessionAccount( array('DocRepMgr'=>'A') );     // this has nothing to do with DocRep but it means you're somebody

include( STDINC."os/phpinfo.php" );
echo "<pre>";
var_dump($_SERVER);
echo "</pre>";

?>
