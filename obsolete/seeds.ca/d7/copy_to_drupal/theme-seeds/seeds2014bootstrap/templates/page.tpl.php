<?php

/* Drupal looks for a file called page.tpl.php to make general pages for the site.
 *
 * The whole template is included from the codebase for version control, and so developers don't have to copy this file more than once.
 *
 * See drupalmod/lib/theme-seeds2014/page-tpl.php for drupal variables that are available here
 */

// includes are based from the drupal directory
if( !defined("SITEROOT") ) define( "SITEROOT", "../" );     // probably already set when seeds.module runs but who knows
include_once( SITEROOT."site_config.php" );                 // actually seeds.module probably already ran site.php too
include_once( "../drupalmod/lib/theme-seeds2014/page-tpl.php" );

?>
