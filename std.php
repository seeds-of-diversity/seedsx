<?php

die( "As far as we know std.php is no longer used" );

/* Definitions that are common to all applications that use the standard shared libraries.
 *
 * All definitions here are compatible with any combination of shared installations, since this file is shared
 * by all site installations that may be present below this directory.
 *
 * Includer must define: STDROOT="this directory with backslash"
 *                       SITEROOT="root of site directory with backslash"
 */
//die( "Sorry, Seeds of Diversity is down for maintenance for about an hour. Please come back soon!");
// This is https://ssl.peaceworks.ca/svn/seeds/trunk/std.php

define("STD_isLocal", (($_SERVER["SERVER_NAME"] == "localhost") ? true : false));

if( !defined("STDROOT") )  define( "STDROOT", "../../" );

if( !defined("SEEDROOT") ) {
    // On typical prod systems the seeds directory is a sibling of std.php and the SITEROOT folder (which is public_html).
    // On typical dev systems the seeds directory is a sibling of public_html which either contains
    //      std.php and the SITEROOT folder, or it contains another level with std.php and the SITEROOT folder
    if( !STD_isLocal ) {
        define( "SEEDROOT", STDROOT."seeds/" );
        if( !defined("W_CORE") ) {
            define("W_CORE", SITEROOT."wcore/");    // copy wcore as a child of public_html
        }
    } else {
        // look for seedroot
        if( file_exists( STDROOT."../seeds/seedcore" ) ) {
            define( SEEDROOT, STDROOT."../seeds/" );
        }
        else if( file_exists( STDROOT."../../seeds/seedcore" ) ) {
            define( SEEDROOT, STDROOT."../../seeds/" );
        } else {
            die( "std.php can't find seedroot" );
        }
        // look for wcore
        if( !defined("W_CORE") ) {
            if( !file_exists($f = STDROOT."../wcore/") &&       // wcore is copied as a sibling of seedsx (possibly shared by other sibling sites)
                !file_exists($f = STDROOT."../seeds/wcore/") )  // seeds is installed as a sibling of seedsx
            {
                die( "std.php can't find wcore" );
            }
            define( "W_CORE", $f );
        }
    }
}

if( !defined("SITEROOT") )  define( "SITEROOT", "./" );

if( !defined("CONFIG_DIR") ) {
    // should be ~/_config on both dev and prod installations
    define( "CONFIG_DIR", STD_isLocal ? (STDROOT."../../_config/") : (STDROOT."_config") );
}

/* full filesystem locations of STDROOT and the current script
 */
define("STDROOT_REALDIR", realpath(dirname($_SERVER['SCRIPT_FILENAME'])."/".STDROOT)."/" );
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

//    include( STDROOT."std/os/php_error.php" );
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

define("STDINC",  STDROOT."std/");
define("SEEDCORE", SEEDROOT."seedcore/");
define("SEEDLIB", SEEDROOT."seedlib/");
define("SEEDAPP", SEEDROOT."seedapp/");
define("STDIMG",  SITEROOT."std/img");  // must be within Apache DocRoot

if( !defined("W_ROOT") ) {
    define("W_ROOT", (STD_isLocal ? (STDROOT."w/") : (SITEROOT."w/") ) );
}
define("W_ROOT_STD",        W_ROOT."std/");         // stuff that std needs to be visible in the web root
define("W_ROOT_SEEDCOMMON", W_ROOT."seedcommon/");  // stuff that seedcommon needs to be visible in the web root


// locations of components that are shared in the W_ROOT (need to be visible to the web server)
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

?>