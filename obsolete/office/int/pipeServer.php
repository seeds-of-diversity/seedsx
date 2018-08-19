<?php

/* pipeServer:  serve pipe requests from other sites through a shared database
 */

include( "../site.php" );
include( SEEDCOMMON."sitePipeCommon.php" );

$kPipeRow = SEEDSafeGPC_GetInt( "kPipeRow" );
$sPipeSignature = SEEDSafeGPC_GetStrPlain( "sPipeSignature" );

$kfdb1 = SiteKFDB( SiteKFDB_DB_seeds1 );  // the communication is done through seeds1.SEEDMetaTable
$oPipe = new SitePipe( $kfdb1 );

if( ($raRequest = $oPipe->GetPipeRequest( $kPipeRow, $sPipeSignature )) ) {
    $raResponse = array();

    $cmd = $raRequest['cmd'];
    switch( $cmd ) {
        case 'GetMbrContactsRA':
            $raResponse = getMbrContactsRA( intval(@$raRequest['kMbr']), @$raRequest['sEmail'] );
            break;
    }

    echo $oPipe->StorePipeResponse( $kPipeRow, $raResponse );
}

function getMbrContactsRA( $kMbr, $sEmail )
{
    if( !$kMbr && !$sEmail ) return( array() );

    $kfdb2 = SiteKFDB( SiteKFDB_DB_seeds2 );
    if( $kMbr ) {
        $ra = $kfdb2->QueryRA( "SELECT * FROM mbr_contacts WHERE _key='$kMbr'" );
    } else {
        $ra = $kfdb2->QueryRA( "SELECT * FROM mbr_contacts WHERE email='".addslashes($sEmail)."'" );
    }
    return( @$ra['_key'] ? $ra : array() );
}

?>
