<?php

/* Entry point for Seeds Document Repository functions
 *
 *  mode={empty}    : serve a text or binary document
 *  mode=diff       : do docdiff
 */

include_once( "../site.php" );
include_once( SEEDCOMMON."doc/docUtil.php" );

if( @$_REQUEST['mode'] == 'diff' ) {
    include_once( SEEDCOMMON."doc/DocMgrDiff.php" );
    exit;
}

// Serve the maxVer version of the document
list($kfdb, $sess, $lang) = SiteStartSessionAccount( array("DocRepMgr"=>"R") );
DocServeDoc( $kfdb, $sess, "" );    // flag == maxver

?>
