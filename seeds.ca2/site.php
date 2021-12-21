<?php

if( $_SERVER['PHP_SELF'] == "/site.php" ) exit;  // don't let anyone look at this directly


if( !defined("SITEROOT") )  { define("SITEROOT", "../"); }
include_once( "site_config.php" );


define("SEEDCOMMON", SEEDSX_ROOT."seedcommon/");    // application files shared by sites
include_once( SEEDCOMMON."siteCommon.php" );

SiteCommon_init( array(
    "DOCREP_UPLOAD_DIR" => (SEEDSX_ROOT."docrep_upload1/"),
    "DOCREP_UPLOAD_REALDIR" => (SEEDSX_ROOT_REALDIR."docrep_upload1/"),
    "SITE_LOGIN_ROOT"   => (SITEROOT."login/")
    ) );
//included in site_seedapp.php
//include_once( SITE_DB_DEF_FILE );  // cannot be included by the function above because variables within it would be local there instead of global

global $SEEDSessionAuthUI_Config;   // so this is global in Drupal's scope so it can be used in semences.ca/boutique
$SEEDSessionAuthUI_Config
    = array( 'urlActivation'          => 'https://www.seeds.ca/login',   // link sent in Create Account activation email
             'urlSendPasswordSite'    => 'https://www.seeds.ca/login',   // 'web site' in Send Password email
             'iActivationInitialGid1' => 2,                              // 2=Members, 3=Public
             'bEnableCreateAccount'   => false /*true*/                  // Random people may NOT create accounts
           );

define("SITE_DB_DEF_FILE", CONFIG_DIR."seeds_def1.php" );  // this def is used by SEEDSetup and below
define("SiteKFDB_HOST",     SiteKFDB_HOST_seeds1);
define("SiteKFDB_DB",       SiteKFDB_DB_seeds1);
define("SiteKFDB_USERID",   SiteKFDB_USERID_seeds1);
define("SiteKFDB_PASSWORD", SiteKFDB_PASSWORD_seeds1);



// put this in siteCommon? That would only be useful if other sites used drupal
function Site_path_self()
/************************
    Get the path to the current page. Does the right thing if you're on a drupal page, and also if drupal is not present.

    Note <form action=""> is not desirable because it defaults to the current browser address including any GET parms that are currently there
 */
{
    if( function_exists('base_path') && ($path = @base_path()) ) {
        if( function_exists('drupal_get_path_alias') ) {
            // drupal 7
            if( substr( $path, -5 ) == 'swww/' )  $path = substr( $path, 0, -5 );

            $path .= drupal_get_path_alias();
        } else {
            // drupal 8

            // In general base_path() has a leading and trailing / or is "/"

            // base_path() ends with /sw8/web/ on dev, or is exactly that on prod (unless this is changed in settings.php). Suppress showing that path.
            if( substr( $path, -9 ) == '/sw8/web/' )  $path = substr( $path, 0, -9 );

            // $page below has a leading-/ so remove any trailing-/ on $path
            if( substr( $path, -1 ) == '/' )  $path = substr( $path, 0, -1 );

            $current_path = \Drupal::service('path.current')->getPath();			// current internal route
            $page = \Drupal::service('path.alias_manager')->getAliasByPath($current_path);	// alias of current page
            $path = $path.$page;
        }
    } else if( function_exists('get_permalink') && ($path = get_permalink()) ) {
        // wordpress
    } else {
        // not in drupal

        // PHP_SELF is unsafe because page requests can look like seeds.ca/foo/index.php/"><script>alert(1);</script><span class="
        // Use htmlspecialchars to make injected js non-parseable
        $path = SEEDCore_HSC($_SERVER['PHP_SELF']);
    }

    return( $path );
}

