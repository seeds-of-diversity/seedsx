<?php


include_once( "../site2.php" );
include_once( SEEDCOMMON."ev/_ev.php" );

list($kfdb, $sess, $dummyLang) = SiteStartSessionAccount( array( "R events" ) );

$oApp = SiteAppConsole( ['db'=>'seeds2', 'sessPermsRequired'=>'R events'] );

header( "Content-type: text/html; charset=ISO-8859-1");


$year = intval(date("Y",time()+3600*24*60));

$oEv = new EV_Events( $kfdb, $sess->GetUID() );

