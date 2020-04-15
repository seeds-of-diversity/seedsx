<?php

/*
include_once( SEEDCOMMON."sitePipeCommon.php" );

function MbrPipeGetContactRA( $kfdb1, $id )
[******************************************
    Use a db connection to seeds1 and an id==( kMbr or email ) to fetch an mbr_contact row through a SitePipe
 *]
{
    $raOut = null;

    $oPipe = new SitePipe( $kfdb1 );

    $parms = array( 'cmd'=>'GetMbrContactsRA' );
    if( is_numeric($id) ) {
        $parms['kMbr'] = $id;
    } else {
        $parms['sEmail'] = $id;
    }

    list( $kPipeRow, $sPipeSignature ) = $oPipe->CreatePipeRequest( $parms );

    list( $bOk, $hdr, $resp ) = $oPipe->SendPipeRequest( array( "kPipeRow"=>$kPipeRow, "sPipeSignature"=>$sPipeSignature ) );

    if( $bOk ) {
// remote server should indicate success of its processing, because it always sends a 200 http response
        $raOut = $oPipe->GetAndDeletePipeResponse( $kPipeRow );
    }
    return( $raOut );
}
*/

?>
