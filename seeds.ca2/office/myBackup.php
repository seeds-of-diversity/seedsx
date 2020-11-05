<?php

$bLocal = (substr(@$_SERVER["SERVER_NAME"],0,9) == "localhost");

define( "SITEROOT", $bLocal ? "/home/bob/public_html/seedsx/seeds.ca2/" : "/home/seeds/public_html/" );
include(SITEROOT."site2.php");

include( SEEDAPP."backup/myBackup.php" );

?>
