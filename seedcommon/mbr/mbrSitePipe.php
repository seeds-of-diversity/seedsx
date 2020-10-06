<?php

include_once( SEEDCOMMON."sitePipeCommon.php" );

/*
function MbrSitePipeGetContactsRA( KeyFrameDB $kfdb, $kMbr )    // DEPRECATE, use the function below
[***********************************************************
    For use by clients of SitePipe to retrieve mbr_contacts data from seeds2 SitePipe server
 *]
{
    $ra = null;

    $oPipe = new SitePipe( $kfdb );
    list( $kPipeRow, $sPipeSignature ) = $oPipe->CreatePipeRequest( array('cmd'=>'GetMbrContactsRA', 'kMbr'=>$kMbr) );

    list( $bOk, $hdr, $resp ) = $oPipe->SendPipeRequest( array( "kPipeRow"=>$kPipeRow, "sPipeSignature"=>$sPipeSignature ) );

    if( $bOk ) {
// remote server should indicate success of its processing, because it always sends a 200 http response
        $ra = $oPipe->GetAndDeletePipeResponse( $kPipeRow );
//  var_dump( $kPipeRow, $ra );
    }
    return( $ra );
}
*/

function MbrSitePipeGetContactsRA2( $kfdb1, $id )
/************************************************
    Use a db connection to seeds1 and an id==( kMbr or email ) to fetch an mbr_contact row through a SitePipe
 */
{
    static $raMbrRows = array();

    $raOut = null;

    if( !$id )  goto done;

    // cache is keyed by mbr _key and email
    if( isset($raMbrRows[$id]) ) {
        $raOut = $raMbrRows[$id];
        goto done;
    }

    $kfdb2 = SiteKFDB( SiteKFDB_DB_seeds2 );
    if( is_numeric($id) ) {
        $raOut = $kfdb2->QueryRA( "SELECT * FROM seeds_2.mbr_contacts WHERE _key='$id'" );
    } else {
        $raOut = $kfdb2->QueryRA( "SELECT * FROM seeds_2.mbr_contacts WHERE email='".addslashes($id)."'" );
    }
    if( !$raOut ) $raOut = array();

/*

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
        if( $raOut && @$raOut['_key'] ) {
            $raMbrRows[$raOut['_key']] = $raOut;
            if( @$raOut['email'] ) {
                $raMbrRows[$raOut['email']] = $raOut;
            }
        }
    }
*/

    done:
    return( $raOut );
}
?>
