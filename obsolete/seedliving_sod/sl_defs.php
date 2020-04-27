<?php

if( !defined("DEV") ) {
    define( "DEV", "0" );
}
if( DEV ) {
    error_reporting(1);
    //ini_set('display_errors', 1);
    //error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
    //error_reporting(E_ALL ^ E_NOTICE ^ E_WARNING);
}


define( "SITEROOT", SEEDLIVING_ROOT."../" );
include_once( SITEROOT."site.php" );
//include_once( STDINC."KeyFrame/KFDB.php" );
include_once( SEEDCOMMON."siteStart.php" );


define("SEEDLIVING_ROOT_DIR", STD_SCRIPT_REALDIR );

require_once(SEEDLIVING_ROOT."lib/lib.php");


//define("ADMIN_EMAIL","mieka@jumpci.com");
//define("SITEEMAIL","sunshine@seedliving.ca");
define("SITEEMAIL","seedliving@seeds.ca");
if( DEV ) {
    define("ADMIN_EMAIL","seedliving_dev@seeds.ca");
} else {
    define("ADMIN_EMAIL","seedliving@seeds.ca");
}


if( DEV ) {
    define("PHPNAME","sl_dev.php");
    define("SEONAME","sl_dev");
} else {
    define("PHPNAME","sl.php");

//    if( ($n = strpos( $_SERVER['REQUEST_URI'], "/sl2/" )) !== false ||
//        ($n = strpos( $_SERVER['REQUEST_URI'], "/index.html" )) !== false ||
//        ($n = strpos( $_SERVER['REQUEST_URI'], "/index.php" )) !== false ||
//        ($n = strpos( $_SERVER['REQUEST_URI'], "/sl.php" )) !== false )
    if( ($n = strpos( $_SERVER['REQUEST_URI'], "/seedliving/" )) !== false )
    {
        $urlPrefix = substr( $_SERVER['REQUEST_URI'], 0, $n+strlen('/seedliving/') );
    } else {
        $urlPrefix = $_SERVER['REQUEST_URI'];
    }
    $urlPrefix = rtrim( $urlPrefix, '/' );               // e.g. /seeds_trunk/seedliving  if url is localhost/seeds_trunk/seedliving/foo
    define( "SL2URL", $urlPrefix );
    define( "SL2WROOT", $urlPrefix."/".W_ROOT );
    define( "SEONAME",  ltrim($urlPrefix,"/") ); //."/sl2" );  // e.g.  seeds_trunk/seedliving/sl2
    define( "SEONAME2", $urlPrefix ); //."/sl2" );             // e.g. /seeds_trunk/seedliving/sl2
}

define("TMPLNAME","index.html");


define("MYSQL_SERVER", SiteKFDB_HOST_seedliving ); //"internal-db.s154977.gridserver.com");
define("MYSQL_LOGIN", SiteKFDB_USERID_seedliving ); //"db154977_sldb");
define("MYSQL_PASS", SiteKFDB_PASSWORD_seedliving ); //"s@@dLiv1ngDB");
define("MYSQL_DB", SiteKFDB_DB_seedliving );  //"db154977_sldb");

// this is the maxvalue parm to most (all?) tkntbl_snprintf but it is not used in that function
define( "MAX_RESULTS", 1000000 );  // Bob's arbitrary value

// stdout is used on most (all?) tmplt_proc_ex but it isn't defined unless you have CLI and it isn't used if it's NULL
define( "stdout", NULL );
?>
