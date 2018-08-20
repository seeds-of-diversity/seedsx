<?php
define( "SITEROOT", "./" );
include( "site.php" );

SiteStartSessionAccount( array('DocRepMgr'=>'A') );     // this has nothing to do with DocRep but it means you're somebody


$x = null;
echo "whoami: ";
system("/usr/bin/whoami", $x);
echo "<br/>$x<br/><br/><br/>";


include( SEEDCORE."os/phpinfo.php" );
echo "<pre>";
var_dump($_SERVER);
echo "</pre>";

?>
