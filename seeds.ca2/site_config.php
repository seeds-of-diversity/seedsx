<?php

/* Definitions common to all sites contained in this directory
 *
 * Includer must define: SITEROOT="path to the seeds.ca2 directory with trailing backslash"
 */

//die( "Sorry, Seeds of Diversity is down for maintenance for about an hour. Please come back soon!");

if( !defined("SITEROOT") )  die( "You have to define SITEROOT (path from your script to seeds.ca2)" );

//define("SEEDROOT", SITEROOT."../../seeds-wt/");


// path to seedsx directory
if( !defined("SEEDSX_ROOT") )    define( "SEEDSX_ROOT", SITEROOT."../" );

// SEEDROOT/SEEDAPP config specific to this site, independent of seedsx
include_once( SITEROOT."site_seedapp.php" );

define( "STD_isLocal", SEED_isLocal );

// Always make seeds_log a sibling of seeds.ca because on prod it's the sibling of public_html and on dev it's in seedsx so git creates the empty dir.
// Don't try to make a common log dir for seeds and cats because they should really have their separate logs
if( !defined("SITE_LOG_ROOT") )  define( "SITE_LOG_ROOT", SEEDSX_ROOT."seeds_log/" );  // SEED_isLocal ? (SEEDSX_ROOT."../seeds_log/") : (SEEDSX_ROOT."seeds_log/") );
define( "SEED_LOG_DIR", SITE_LOG_ROOT );


/* full filesystem locations of SEEDSX_ROOT and the current script
 */
define("SEEDSX_ROOT_REALDIR", realpath(dirname($_SERVER['SCRIPT_FILENAME'])."/".SEEDSX_ROOT)."/" );
define("SITEROOT_REALDIR", realpath(dirname($_SERVER['SCRIPT_FILENAME'])."/".SITEROOT)."/" );
define("STD_SCRIPT_REALDIR", realpath(dirname($_SERVER['SCRIPT_FILENAME']))."/" );

/* url location of SITEROOT
 *     - contains leading and trailing / (because on production there's only one
 *     - does not contain server_name because things like curl want them separate
 *     - use http://{$_SERVER['SERVER_NAME']}{SITEROOT_URL}foo  where foo has no leading /
 */
if( SEED_isLocal ) {
    // this has to be updated if you use any other top-level site directories in development repository
    $url = '/unknown/';
    if( ($n = strpos( $_SERVER['REQUEST_URI'], "/seeds.ca2/" )) ) {
        // get the url components up to the site directory e.g. /~user/repo1/seeds.ca2/
        $url = substr( $_SERVER['REQUEST_URI'], 0, $n )."/seeds.ca2/";
    }
    define("SITEROOT_URL", $url );
} else {
    define("SITEROOT_URL", "/" );
}


$tz = @date_default_timezone_get();
if( empty($tz) || $tz == 'UTC' ) {
    date_default_timezone_set('America/Winnipeg');
}

define("STDINC",  SEEDSX_ROOT."std/");
define("STDIMG",  SITEROOT."std/img");  // must be within Apache DocRoot

if( !defined("W_ROOT") ) {
    define("W_ROOT", (SEED_isLocal ? (SEEDSX_ROOT."w/") : (SITEROOT."w/") ) );
}
define("W_ROOT_STD",        W_ROOT."std/");         // stuff that std needs to be visible in the web root
define("W_ROOT_SEEDCOMMON", W_ROOT."seedcommon/");  // stuff that seedcommon needs to be visible in the web root


// locations of components that need to be visible to the web browser
define("TINYMCE_DIR", W_ROOT."os/TinyMCE3-3-2/" );
define("TINYMCE_4_DIR", W_ROOT."os/TinyMCE4/" );

define("W_ROOT_FPDF", W_ROOT."os/fpdf181/" );

// locations of components that are shared in the STD directory (typically hidden from direct access by the web server)
define("WRITE_EXCEL_DIR", STDINC."os/write_excel/" );
