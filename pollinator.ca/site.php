<?

if( $_SERVER['PHP_SELF'] == "/site.php" ) exit;  // don't let anyone look at this directly


if( !defined("SITEROOT") )  { define("SITEROOT", "../"); }

define("STDROOT", SITEROOT."../");
include_once( STDROOT."std.php" );



define("SITEIMG", SITEROOT."img/");
define("SITEINC", SITEROOT."inc/");
define("SITEINC_STDJS", SITEINC."std/js/");     // STDINC/js or a copy that the web server can see
define("SITEIMG_STDIMG", SITEINC."std/img/");   // STDINC/img or a copy that the web server can see
define("SEEDCOMMON", STDROOT."seedcommon/");    // application files shared by seeds.ca and office.seeds.ca
include_once(SEEDCOMMON."siteCommon.php");

define("SITE_DB_DEF_FILE", STDROOT."seeds3_def.php" );    // this file is unversioned - it contains installation-specific defs
include_once( SITE_DB_DEF_FILE );
include_once( STDINC."SEEDStd.php" );
include_once( SEEDCOMMON."siteStart.php" );


define("SITE_LOG_ROOT", STDROOT."seeds3_log/");
define("LOG_ROOT", SITE_LOG_ROOT );     // deprecated
//define("ADMIN_HOME", SITEROOT."login/index.php");

define( "DOCREP_FCKEDITOR_DIR", SITEINC."os/fckeditor/" );

define( "DOCREP_SEEDPERMS_APP", "DocRep" );         // the SEEDPerms app name for DocRep permissions
define( "DOCREP_UPLOAD_DIR", SITEROOT."../docrep_upload3/" );
define( "DOCREP_ICON_DIR",   SITEROOT."inc/std/img/dr/" );
define( "DOCREP_KEY_HASH_SEED", "Lychnis" );

?>
