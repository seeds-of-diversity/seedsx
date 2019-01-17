<?php

/* Login as another user
 */

define( "SITEROOT", "../../" );
include_once( SITEROOT."site2.php" );
include_once( SEEDCOMMON."siteStart.php" );

list($kfdb, $sess, $lang) = SiteStartSessionAccount( ["A SEEDSessionUGP"] );

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
