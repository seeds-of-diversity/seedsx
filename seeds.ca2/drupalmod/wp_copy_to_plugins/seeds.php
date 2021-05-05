<?php

/***
 * Plugin Name:   Seeds
 */

//define("SEED_display_errors",1);

define( "SITEROOT", "/home/seeds/public_html/" );
define( "SEEDW_URL", "/wcore/" );
include_once( SITEROOT."site.php" );
include_once( SEEDAPP."website/seedsWPPlugin.php" );
if( function_exists('seedsWPPlugin_Filter') ) {
    add_filter( 'the_content', 'seedsWPPlugin_Filter' );
} else {
    die( "seedsWPPlugin_Filter not found" );
}

