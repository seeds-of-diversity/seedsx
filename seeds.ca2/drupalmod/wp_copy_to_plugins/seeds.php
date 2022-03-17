<?php

/***
 * Plugin Name:   Seeds
 *
 * Copyright 2021-2022 Seeds of Diversity Canada
 *
 * Customization for the Seeds of Diversity web sites.
 *
 * 1. Copy this file into wp-content/plugins
 * 2. Activate the Seeds plugin in wp admin interface
 * 3. Don't put any code here. Put it in SEEDAPP/website/seedsWPPlugin.php so you don't have to keep copying this file.
 */

//define("SEED_display_errors",1);

define( "SITEROOT", "/home/seeds/public_html/" );
define( "SEEDW_URL", "/wcore/" );
include_once( SITEROOT."site.php" );
include_once( SEEDAPP."website/seedsWPPlugin.php" );

if( function_exists('seedsWPStart') ) {
    seedsWPStart();
} else {
    die( "seedsWPStart not found" );
}
