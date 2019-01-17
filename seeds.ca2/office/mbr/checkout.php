<?php

define( "SITEROOT", "../../" );
include_once( SITEROOT."site2.php" );
include_once( SEEDCOMMON."mbr/seedCheckout.php" );
include_once( SEEDCOMMON."console/console01.php" );

define( "MbrOrderCheckoutOffice", 1 );

list($kfdb2, $sess) = SiteStartSessionAccount( array( "W MBRORDER" ) );
$kfdb1 = SiteKFDB( SiteKFDB_DB_seeds1 ) or die( "Cannot connect to database" );

//echo DrawMbr( $kfdb1 );    oddly enough this re-creates a kfdb for the default db

$oMbrOC = new SoDMbrOrderCheckout( $kfdb1, $sess, "EN" );
$s = $oMbrOC->Checkout();


echo Console01Static::HTMLPage( $s, "", "EN", array('sCharset'=>'cp1252') );

?>
