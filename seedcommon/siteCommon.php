<?php

/* siteCommon
 *
 * common functions and definitions for all sites
 */

include_once( STDINC."SEEDStd.php" );
include_once( SEEDCORE."SEEDCore.php" );

function SiteCommon_init( $raParms )
{
    /* non-parameterized
     */
    define( "SITEROOT_REAL", ($_SERVER['DOCUMENT_ROOT'].dirname($_SERVER['PHP_SELF'])."/".SITEROOT) );
    define("SITEIMG", SITEROOT."img/");
    define("SITEINC", SITEROOT."inc/");

    /* parameterized
     */
    define( "SITE_DB_DEF_FILE", $raParms['SITE_DB_DEF_FILE'] );
    require_once( SITE_DB_DEF_FILE );

    define( "SITE_LOG_ROOT",   $raParms['SITE_LOG_ROOT'] );
    define( "SITE_LOGIN_ROOT", $raParms['SITE_LOGIN_ROOT'] );


    define( "DOCREP_SEEDPERMS_APP", "DocRep" );              // SEEDPerms app name for DocRep permissions
    define( "DOCREP_KEY_HASH_SEED", "Lagenaria" );           // really nice for this to be the same for seeds and seeds2 because mail created on seeds2 has hashed docpub.php links to seeds.ca
    define( "DOCREP_ICON_DIR",      W_ROOT."std/img/dr/" );  // location of icons for DocRepMgr
    define( "DOCREP_UPLOAD_DIR",    $raParms['DOCREP_UPLOAD_DIR'] );
    define( "DOCREP_UPLOAD_REALDIR",$raParms['DOCREP_UPLOAD_REALDIR'] );

    include_once( SEEDAPP."SEEDApp.php" );
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


function Site_UrlQ()
/*******************
    The url that the current site should use to access QServer.

    Q should always be accessed with https on sites that support it, because it tries to establish a SEEDSessionAccount
    which forces https in production environments by returning a header(Location) to the same page with https. Ajax doesn't like that.
 */
{
    if( STD_isLocal ) {
        return( "http://".$_SERVER['SERVER_NAME'].SITEROOT_URL
               ."../seeds.ca2/"    // get to the right place from any site
               ."app/q/index.php" );
    } else {
        // This works if your ajax is from www.seeds.ca, but not if it's from a different domain (see CORS).
        // If you fake out the CORS you'll still have to set the prefix to http/https depending on what SEEDSessionAccount::doSSL needs.
        return( "https://www.seeds.ca/app/q/index.php" );
    }
}

?>
