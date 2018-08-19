<?php

define( "SITEROOT", "../../../" );
include_once( SITEROOT."site2.php" );
include_once( SEEDCOMMON."console/console01.php" );
include_once( SEEDCORE."SEEDGrid.php" );
include_once( "profiles.php" );

$oApp = new SEEDAppConsole( array( 'kfdbUserid' => SiteKFDB_USERID, 'kfdbPassword' => SiteKFDB_PASSWORD, 'kfdbDatabase' => SiteKFDB_DB,
                                   'sessPermsRequired' => array() ) );

$oCP = new CropProfiles( $oApp );

$sp = SEEDInput_Str( 'sp' );
$cv = SEEDInput_Str( 'cv' );

$s = "";

if( ($kfr = $oCP->oProfilesDB->GetKFRCond( "VISite", "osp='".addslashes($sp)."' AND oname='".addslashes($cv)."'" )) ) {
    $s = $oCP->oProfilesReport->DrawVIRecord( $kfr->Key(), false );
}

echo json_encode(utf8_encode( $s ));

?>
