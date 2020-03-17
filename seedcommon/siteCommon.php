<?php

/* siteCommon
 *
 * common functions and definitions for all sites
 */

include_once( STDINC."SEEDStd.php" );
include_once( SEEDCORE."SEEDCore.php" );
include_once( SEEDCORE."SEEDApp.php" );
include_once( SEEDCOMMON."siteStart.php" );

function SiteCommon_init( $raParms )
{
    define( "SITEROOT_REAL", ($_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF'])."/".SITEROOT) );
    define( "SITEIMG", SITEROOT."img/");
    define( "SITEINC", SITEROOT."inc/");

    define( "SITE_LOGIN_ROOT", $raParms['SITE_LOGIN_ROOT'] );

    define( "DOCREP_SEEDPERMS_APP", "DocRep" );              // SEEDPerms app name for DocRep permissions
    define( "DOCREP_KEY_HASH_SEED", "Lagenaria" );           // really nice for this to be the same for seeds and seeds2 because mail created on seeds2 has hashed docpub.php links to seeds.ca
    define( "DOCREP_ICON_DIR",      W_ROOT."std/img/dr/" );  // location of icons for DocRepMgr
    define( "DOCREP_UPLOAD_DIR",    $raParms['DOCREP_UPLOAD_DIR'] );
    define( "DOCREP_UPLOAD_REALDIR",$raParms['DOCREP_UPLOAD_REALDIR'] );
}

function New_SiteAppDB()
{
    // Normally an app would make this in its init code but we have some old code that uses seedcore
    // classes so it suddenly needs one of these. Deprecate when no longer needed.
    return( new SEEDAppDB( array( 'kfdbUserid' => SiteKFDB_USERID,
                                  'kfdbPassword' => SiteKFDB_PASSWORD,
                                  'kfdbDatabase' => SiteKFDB_DB,
                                  'logdir' => SITE_LOG_ROOT ) ) );
}

function site_define_lang( $lang = "" )
/**************************************
    Defines SITE_LANG = ("EN" | "FR")

    Order of overrides:
    1) SITE_LANG already defined (because PHP does not allow constants to be changed or undefined)
    2) GPC contains lang parm (allows manual override, mostly for testing)
    3) Parm (must be EN or FR else defaults to EN)
    4) SERVER_NAME identifies English or French site
    5) Default is EN
 */
{
    if( defined("SITE_LANG") )  return( SITE_LANG );

    if( ($s = SEEDSafeGPC_GetStrPlain("lang")) && in_array($s, array("EN","FR")) ) {
        $lang = $s;
    } else if( !empty($lang) && in_array($lang, array("EN","FR")) ) {
        // use $lang
    } else if( strpos($_SERVER['SERVER_NAME'], "semences.ca") !== false ||
               strpos($_SERVER['SERVER_NAME'], "pollinisation") !== false ||
               strpos($_SERVER['SERVER_NAME'], "pollinisateur") !== false ) {
        $lang = "FR";
    }
    define("SITE_LANG", ($lang=="FR" ? $lang : "EN") );
    return( SITE_LANG );
}


function Site_QRoot()
/********************
    The url that the current site should use to access QServer.

    Q should always be accessed with https on sites that support it, because it tries to establish a SEEDSessionAccount
    which forces https in production environments by returning a header(Location) to the same page with https. Ajax doesn't like that.
 */
{
    if( STD_isLocal ) {
        return( "http://".$_SERVER['SERVER_NAME'].SITEROOT_URL
               ."../seeds.ca2/"    // get to the right place from any site
               ."app/q/" );
    } else {
        // This should work regardless of your domain (seeds.ca vs www.seeds.ca) and your mode (http vs https)
        // as long as your js is on a seeds.ca source
        return( "/app/q/" );
    }
}

function Site_UrlQ( $file = "index.php" )
/****************************************
 */
{
    return( Site_QRoot().$file );
}

// deprecated
function SiteAppConsole( $raConfig = array() )
{
    return( SEEDConfig_NewAppConsole( $raConfig ) );
/*
    global $config_KFDB;
    //$config_KFDB = $GLOBALS['config_KFDB'];   Drupal loads seeds_def1.php in a function scope so that file has to set the var in $GLOBALS. But the global keyword still works here.

    $db = @$raConfig['db'] ?: 'seeds1';
    $lang = @$raConfig['lang'] ?: 'EN';
    $perms = @$raConfig['sessPermsRequired'] ?: array();

    $oApp = new SEEDAppConsole( $config_KFDB[$db]
                                + array( 'sessPermsRequired' => $perms,
                                         'logdir' => SITE_LOG_ROOT,
                                         'lang' => $lang ) );
    return( $oApp );
*/
}

