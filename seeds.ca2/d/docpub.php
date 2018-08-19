<?php
/* Serve PUB versions of documents to the public
 */

if( !defined("SITEROOT") )  define("SITEROOT", "../");
include_once( SITEROOT."site.php" );
include_once( SEEDCOMMON."siteStart.php" );
include_once( SEEDCOMMON."doc/docUtil.php" );

list($kfdb) = SiteStart();
DocServeDocPub( $kfdb );    // flag == 'PUB'

?>
