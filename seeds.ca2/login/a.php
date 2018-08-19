<?php

/* Login as another user
 */

include_once( "../site.php" );
include_once( SEEDCOMMON."siteStart.php" );

list($kfdb, $sess, $lang) = SiteStartSessionAccount( array("SEEDSessionUGP"=>"A") );

if( ($u = SEEDSafeGPC_GetStrPlain('u')) ) {
    if( $sess->LoginAsUser( $u ) ) {
        header( "Location: index.php" );
    } else {
        die( "No." );
    }
    exit;
}

echo "<form><input type='text' value='' name='u'/>&nbsp;<input type='submit' value='Go'/></form>";

?>
