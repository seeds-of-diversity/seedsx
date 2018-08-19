<?php

// SS text
// translation flag


/* User (read-only) portal to Events in English or French
 *
 * Lists the Event Pages
 *
 * $lang is defined by an including page.  If we get here directly, EN is default.
 */
include_once( "../site.php" );
include_once( STDINC."SEEDForm.php" );
include_once( SEEDCOMMON."ev/_ev.php" );
include_once( EV_ROOT."_ev_inc.php" );
include_once( PAGE1_TEMPLATE );

/* This is not defined by GPC.  We're supposed to get here via events.php or evenements.php, which set this.
 */
if( @$lang != "FR" )  $lang = "EN";

$kfdb = SiteKFDB() or die( "Cannot connect to database" );




$page1parms = array (
                "lang"      => $lang,
                "title"     => ($lang == "EN" ? "Seedy Events" : "&Eacute;v&eacute;nements" ),
                "tabname"   => "EV",
//              "box1title" => "Box1Title",
//              "box1text"  => "Box2Text",
//              "box1fn"    => "Box1Fn",
//              "box2title" => "Box2Title",
//              "box2text"  => "Box2Text",
//              "box2fn"    => "Box2Fn",

             );



Page1( $page1parms );


function Page1Body() {
	global $lang, $kfdb;
    echo DrawEvents( $kfdb, $lang );
}


?>
