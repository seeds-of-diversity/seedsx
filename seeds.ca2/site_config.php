<?php

/* Definitions common to all sites contained in this directory
 *
 * Includer must define: SITEROOT="path to the seeds.ca2 directory with trailing backslash"
 */

//die( "Sorry, Seeds of Diversity is down for maintenance for about an hour. Please come back soon!");

if( !defined("SITEROOT") )  die( "You have to define SITEROOT (path from your script to seeds.ca2)" );

//define("SEEDROOT", SITEROOT."../../seeds-wt/");


define("STD_isLocal", (($_SERVER["SERVER_NAME"] == "localhost") ? true : false));

// path to seedsx directory
if( !defined("SEEDSX_ROOT") )  define( "SEEDSX_ROOT", SITEROOT."../" );

if( !defined("SEEDROOT") ) {
    if( !STD_isLocal ) {
        // On typical production systems the seeds directory is a sibling of public_html
        // SITEROOT (aka seeds.ca2) is public_html
        define( "SEEDROOT", SITEROOT."../seeds/" );
    } else {
        // On typical development systems the seeds and seedsx directories are siblings at public_html/seeds and public_html/seedsx
        // SITEROOT is public_html/seedsx/seeds.ca2

        // However some dev installations currently have the seeds directory at public_html/../seeds so do some looking
        if( file_exists( SEEDSX_ROOT."../seeds/seedcore" ) ) {
            define( "SEEDROOT", SEEDSX_ROOT."../seeds/" );
        }
        else if( file_exists( SEEDSX_ROOT."../../seeds/seedcore" ) ) {
            define( "SEEDROOT", SEEDSX_ROOT."../../seeds/" );
        } else {
            die( "site_config.php can't find seedroot" );
        }
    }
}
if( !defined("W_CORE") ) {
    if( !STD_isLocal ) {
        // On typical production sytems the seeds/wcore directory has to be copied to public_html/wcore so browsers can see .css, .js, images
        define( "W_CORE", SITEROOT."wcore/" );
    } else {
        // On typical development systems wcore doesn't have to be copied because it's in public_html

        // look for wcore
        if( !file_exists($f = SEEDSX_ROOT."../wcore/") &&       // wcore is copied as a sibling of seedsx (possibly shared by other sibling sites)
            !file_exists($f = SEEDROOT."wcore/") )              // wcore is in seeds, which should be under the docroot
        {
            die( "site_config.php can't find wcore" );
        }
        define( "W_CORE", $f );
    }
}
if( !defined("W_CORE_URL") ) {
    define( "W_CORE_URL", W_CORE );
}


if( !defined("CONFIG_DIR") ) {
    // should be ~/_config on both dev and prod installations
    define( "CONFIG_DIR", STD_isLocal ? (SEEDSX_ROOT."../../_config/") : (SEEDSX_ROOT."_config/") );
}

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
if( STD_isLocal ) {
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

/* activate full error reporting in development environments, not in production
 */
if( STD_isLocal ) {
    error_reporting(E_ALL | E_STRICT);
    ini_set('display_errors', 1);
    ini_set('html_errors', 1);

//    include( SEEDSX_ROOT."std/os/php_error.php" );
//    \php_error\reportErrors();
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('html_errors', 0);
}

$tz = @date_default_timezone_get();
if( empty($tz) || $tz == 'UTC' ) {
    date_default_timezone_set('America/Winnipeg');
}

define("STDINC",  SEEDSX_ROOT."std/");
define("SEEDCORE", SEEDROOT."seedcore/");
define("SEEDLIB", SEEDROOT."seedlib/");
define("SEEDAPP", SEEDROOT."seedapp/");
define("STDIMG",  SITEROOT."std/img");  // must be within Apache DocRoot

if( !defined("W_ROOT") ) {
    define("W_ROOT", (STD_isLocal ? (SEEDSX_ROOT."w/") : (SITEROOT."w/") ) );
}
define("W_ROOT_STD",        W_ROOT."std/");         // stuff that std needs to be visible in the web root
define("W_ROOT_SEEDCOMMON", W_ROOT."seedcommon/");  // stuff that seedcommon needs to be visible in the web root


// locations of components that need to be visible to the web server

// [move these to a seeds_config.php in seeds]
define("W_CORE_JQUERY_3_3_1", W_CORE_URL."os/jquery/jquery-3-3-1.min.js");  // use this if you need this specific version
define("W_CORE_JQUERY", W_CORE_JQUERY_3_3_1 );                              // use this if you want the latest version (it will change)

define("TINYMCE_DIR", W_ROOT."os/TinyMCE3-3-2/" );
define("TINYMCE_4_DIR", W_ROOT."os/TinyMCE4/" );
define("W_ROOT_JQUERY_1_11_0", W_ROOT."os/jquery/jquery-1.11.0.min.js");  // use this if you need this specific version
define("W_ROOT_JQUERY",        W_ROOT_JQUERY_1_11_0);                     // use this if you just want the latest version (it will change)
define("W_ROOT_JQUERY_UI_1_11_4", W_ROOT."os/jquery/jquery-ui-1.11.4.min.js");  // use this if you need this specific version
define("W_ROOT_JQUERY_UI",        W_ROOT_JQUERY_UI_1_11_4);                     // use this if you just want the latest version (it will change)
define("W_ROOT_JQUERY_UI_THEME_SMOOTHNESS", W_ROOT_JQUERY_UI."/../jquery-ui-1.11.4-smoothness.css"); // same directory as the js

define("W_ROOT_FPDF", W_ROOT."os/fpdf181/" );

// locations of components that are shared in the STD directory (typically hidden from direct access by the web server)
define("WRITE_EXCEL_DIR", STDINC."os/write_excel/" );

