<?php

if( $_SERVER['PHP_SELF'] == "/site2.php" ) exit;  // don't let anyone look at this directly


if( !defined("SITEROOT") )  { define("SITEROOT", "../"); }

define("STDROOT", SITEROOT."../");
define("SEEDCOMMON", STDROOT."seedcommon/");    // application files shared by sites
include_once( STDROOT."std.php" );
include_once( SEEDCOMMON."siteCommon.php" );

SiteCommon_init( array(
    "SITE_DB_DEF_FILE"  => (CONFIG_DIR."seeds_def2.php"),  // this def is used by SEEDSetup and below
    "SITE_LOG_ROOT"     => (STDROOT."seeds_log/"),
    "DOCREP_UPLOAD_DIR" => (STDROOT."docrep_upload2/"),
    "DOCREP_UPLOAD_REALDIR" => (STDROOT_REALDIR."docrep_upload2/"),
    "SITE_LOGIN_ROOT"   => (SITEROOT."office/login/")
    ) );
include_once( SITE_DB_DEF_FILE );  // cannot be included by the function above because variables within it would be local there instead of global

define("SITEIMG_STDIMG", SITEINC."std/img/");   // STDINC/img or a copy that the web server can see

define( "CLR_BG_editEN","#e0e0e0");
define( "CLR_BG_editFR","#e0e0ff");

$SEEDSessionAuthUI_Config = array( 'urlActivation'          => 'https://office.seeds.ca/login',   // link sent in Create Account activation email
                                   'urlSendPasswordSite'    => 'https://office.seeds.ca/login',   // 'web site' in Send Password email
                                   'iActivationInitialGid1' => 2,                                 // Members
                                   'bEnableCreateAccount'   => false );                           // Random people can't create accounts here


function Site_path_self()
{
    return( "" );
}

?>