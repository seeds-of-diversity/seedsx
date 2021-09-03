<?php

/* Login as another user
 */

define( "SITEROOT", "../../" );
include_once( SITEROOT."site2.php" );
include_once( SEEDCOMMON."siteStart.php" );

//list($kfdb, $sess, $lang) = SiteStartSessionAccount( ["A SEEDSessionUGP"] );
$oApp = SEEDConfig_NewAppConsole( ['db'=>'seeds2', 'sessPermsRequired'=>['A SEEDSessionUGP']] );

if( ($u = SEEDInput_Str('u')) ) {
    if( $oApp->sess->LoginAsUser( $u ) ) {
        header( "Location: index.php" );
    } else {
        die( "No." );
    }
    exit;
}

echo "<form><input type='text' value='' name='u'/>&nbsp;<input type='submit' value='Go'/></form>";
