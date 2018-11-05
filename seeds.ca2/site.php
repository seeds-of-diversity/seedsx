<?php

// www8  : 104.200.28.125
// www12 :  66.228.32.253

if( $_SERVER['PHP_SELF'] == "/site.php" ) exit;  // don't let anyone look at this directly


if( !defined("SITEROOT") )  { define("SITEROOT", "../"); }

define("STDROOT", SITEROOT."../");
define("SEEDCOMMON", STDROOT."seedcommon/");    // application files shared by sites
include_once( STDROOT."std.php" );
include_once( SEEDCOMMON."siteStart.php" );
include_once( SEEDCOMMON."siteCommon.php" );

SiteCommon_init( array(
    "SITE_DB_DEF_FILE"  => (CONFIG_DIR."seeds_def1.php"),  // this file is unversioned - it contains installation-specific defs
    "SITE_LOG_ROOT"     => (STDROOT."seeds_log/"),
    "DOCREP_UPLOAD_DIR" => (STDROOT."docrep_upload1/"),
    "DOCREP_UPLOAD_REALDIR" => (STDROOT_REALDIR."docrep_upload1/"),
    "SITE_LOGIN_ROOT"   => (SITEROOT."login/")
    ) );

define( "Q_ROOT", STD_isLocal ? (SITEROOT."app/q/") : "https://seeds.ca/app/q/" );

$SEEDSessionAuthUI_Config
    = array( 'urlActivation'          => 'https://www.seeds.ca/login',   // link sent in Create Account activation email
             'urlSendPasswordSite'    => 'https://www.seeds.ca/login',   // 'web site' in Send Password email
             'iActivationInitialGid1' => 3,                              // Public
             'bEnableCreateAccount'   => true /*false*/                  // Random people may create accounts
           );


// credentials for your seeds database (assuming host==localhost)
$SEEDKFDB1 = array( 'kfdbUserid' => SiteKFDB_USERID_seeds1,
                    'kfdbPassword' => SiteKFDB_PASSWORD_seeds1,
                    'kfdbDatabase' => SiteKFDB_DB_seeds1 );


// put this in siteCommon? That would only be useful if other sites used drupal
function Site_path_self()
/************************
    Get the path to the current page. Does the right thing if you're on a drupal page,
    and also if drupal is not present
 */
{
    if( function_exists('base_path') && ($path = @base_path()) ) {
        if( substr( $path, -5 ) == 'swww/' )  $path = substr( $path, 0, -5 );

        $path .= drupal_get_path_alias();
    } else {
// this is unsafe because page requests can look like seeds.ca/foo/index.php/"><script>alert(1);</script><span class="
// also "" is not always desired because it propagates GET parms
// so a safer alternative is html_specialchars($_SERVER['PHP_SELF']); because the above hack will be rendered non-parseable to js
        $path = $_SERVER['PHP_SELF'];
    }

    return( $path );
}


?>
