<?php

/* Set up SEEDAPP with no seedsx defs.
 *
 * The only input def is SITEROOT
 */

if( !defined('SITEROOT') )  { die("SITEROOT has to be defined"); }

// ironically we do define this in order to clarify the searches below
if( !defined("SEEDSX_ROOT") )  define( "SEEDSX_ROOT", SITEROOT."../" );

if( !defined("SEEDROOT") ) {
    if( substr($_SERVER["SERVER_NAME"],0,9) != "localhost" ) {  // same as SEED_isLocal but that is not defined yet
        // On typical production systems the seeds directory is a sibling of public_html
        define( "SEEDROOT", SEEDSX_ROOT."seeds/" );
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
    if( substr($_SERVER["SERVER_NAME"],0,9) != "localhost" ) {  // same as SEED_isLocal but that is not defined yet
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

if( !defined("Q_URL") )  define( 'Q_URL', SITEROOT."app/q2/" ); // files that include SEEDAPP/q/*

/* Based on SEEDROOT, define everything about seedapp, seedlib, seedcore, wcore
 */
include_once( SEEDROOT."seedConfig.php" );

if( !defined("CONFIG_DIR") ) {
    // should be ~/_config on both dev and prod installations
    define( "CONFIG_DIR", SEED_isLocal ? (SEEDSX_ROOT."../../_config/") : (SEEDSX_ROOT."_config/") );
}
include_once( CONFIG_DIR."seeds_def1.php" );
