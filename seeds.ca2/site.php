<?php

// www8  : 104.200.28.125
// www12 :  66.228.32.253

if( $_SERVER['PHP_SELF'] == "/site.php" ) exit;  // don't let anyone look at this directly


if( !defined("SITEROOT") )  { define("SITEROOT", "../"); }
include_once( "site_config.php" );


define("SEEDCOMMON", SEEDSX_ROOT."seedcommon/");    // application files shared by sites
include_once( SEEDCOMMON."siteCommon.php" );

SiteCommon_init( array(
    "SITE_DB_DEF_FILE"  => (CONFIG_DIR."seeds_def1.php"),  // this def is used by SEEDSetup and below
    "DOCREP_UPLOAD_DIR" => (SEEDSX_ROOT."docrep_upload1/"),
    "DOCREP_UPLOAD_REALDIR" => (SEEDSX_ROOT_REALDIR."docrep_upload1/"),
    "SITE_LOGIN_ROOT"   => (SITEROOT."login/")
    ) );
//included in site_seedapp.php
//include_once( SITE_DB_DEF_FILE );  // cannot be included by the function above because variables within it would be local there instead of global

$SEEDSessionAuthUI_Config
    = array( 'urlActivation'          => 'https://www.seeds.ca/login',   // link sent in Create Account activation email
             'urlSendPasswordSite'    => 'https://www.seeds.ca/login',   // 'web site' in Send Password email
             'iActivationInitialGid1' => 3,                              // Public
             'bEnableCreateAccount'   => true /*false*/                  // Random people may create accounts
           );


// put this in siteCommon? That would only be useful if other sites used drupal
function Site_path_self()
/************************
    Get the path to the current page. Does the right thing if you're on a drupal page,
    and also if drupal is not present
 */
{
    if( function_exists('base_path') && ($path = @base_path()) ) {
        if( function_exists('drupal_get_path_alias') ) {
            // drupal 7
            if( substr( $path, -5 ) == 'swww/' )  $path = substr( $path, 0, -5 );

            $path .= drupal_get_path_alias();
        } else {
//remove this when we rebase to seeds.ca/
              // $path has a trailing-/ and page has a leading-/
//            if( substr( $path, -9 ) == '/sw8/web/' )  $path = substr( $path, 0, -9 );
if( substr( $path, -1 ) == '/' )  $path = substr( $path, 0, -1 );

            $current_path = \Drupal::service('path.current')->getPath();
            $page = \Drupal::service('path.alias_manager')->getAliasByPath($current_path);
            $path = $path.$page;
        }
    } else {
// this is unsafe because page requests can look like seeds.ca/foo/index.php/"><script>alert(1);</script><span class="
// also "" is not always desired because it propagates GET parms
// so a safer alternative is html_specialchars($_SERVER['PHP_SELF']); because the above hack will be rendered non-parseable to js
        $path = $_SERVER['PHP_SELF'];
    }

    return( $path );
}

