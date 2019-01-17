<?php

/* Entry point for Office Document Repository functions
 *
 *  mode={empty}    : serve a text or binary document
 *  mode=diff       : do docdiff
 */

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site2.php" );
include_once( SEEDCOMMON."doc/docUtil.php" );

if( @$_REQUEST['mode'] == 'diff' ) {
    include_once( SEEDCOMMON."doc/DocMgrDiff.php" );
    exit;
}

// Serve the maxVer version of the document
list($kfdb, $sess, $lang) = SiteStartSessionAccount( array("R DocRepMgr") );
DocServeDoc( $kfdb, $sess, "" );    // flag == maxver

?>
