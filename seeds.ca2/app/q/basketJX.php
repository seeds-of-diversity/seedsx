<?php

/* basketJX
 *
 * Ajax entry point for a SEEDBasket (public access, no login required)
 */

define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( SEEDCORE."SEEDBasket.php" );
include_once( SEEDAPP."basket/basketProductHandlers.php" );
include_once( SEEDAPP."basket/basketProductHandlers_seeds.php" );
include_once( SITEROOT."l/sl/msd.php" );

list($kfdb, $sess, $lang) = SiteStartSessionAccountNoUI();     // you don't have to be logged in to use a basket

$oW = new SEEDApp_Worker( $kfdb, $sess, $lang );
$oSB = new MSDBasketCore( $oW );

//$kfdb->SetDebug(2);

$raJX = array( 'bOk'=>false, 'sOut'=>"", 'sErr'=>"", 'raOut'=>array() );

if( ($cmd = SEEDSafeGPC_GetStrPlain( "cmd" )) ) {

    $raCmd = $oSB->Cmd( $cmd, $_REQUEST );
    if( $raCmd['bHandled'] ) {
        $raJX = array_merge( $raJX, $raCmd );
        $raJX['sOut'] = utf8_encode( $raJX['sOut'] );
        $raJX['sErr'] = utf8_encode( $raJX['sErr'] );
        goto done;
    }

    switch( $cmd ) {
        case "prodUnfill":
            $kfdb->Execute( "UPDATE seeds.SEEDBasket_BP SET eStatus='PAID' WHERE _key='$k'" );
            $raJX['bOk'] = true;
            break;

        case "prodCancel":
            $kfdb->Execute( "UPDATE seeds.SEEDBasket_BP SET eStatus='CANCELLED' WHERE _key='$k'" );
            $raJX['bOk'] = true;
            break;

        case "prodUncancel":
            $kfdb->Execute( "UPDATE seeds.SEEDBasket_BP SET eStatus='PAID' WHERE _key='$k'" );
            $raJX['bOk'] = true;
            break;

        case "prodAccount":
            $kfdb->Execute( "UPDATE seeds.SEEDBasket_BP SET bAccountingDone=1 WHERE _key='$k'" );
            $raJX['bOk'] = true;
            break;
        case "prodUnaccount":
            $kfdb->Execute( "UPDATE seeds.SEEDBasket_BP SET bAccountingDone=0 WHERE _key='$k'" );
            $raJX['bOk'] = true;
            break;

        case "msdSearch":
            //include_once( "_QServerCollection.php" );
            //$o = new QServerCollection( $this, array( ) );
            //$rQ = $o->Cmd( $cmd, $parms );

            $dbSrch = addslashes(SEEDInput_Str( "srch" ));

            /* Get sp/cv of seeds where variety or description contains the search term
             *
             * The way to use PxPE is to specify the PE.k for each ProdExtra. Then you get one row for each product with three PE tuples
             * to the right that you can use to filter the rows.
             */
//$oSB->oDB->kfdb->SetDebug(2);
            if( ($kfrP = $oSB->oDB->GetKFRC( "PxPE3",
                                             "product_type='seeds' AND "
                                            ."PE1.k='species' AND "
                                            ."PE2.k='variety' AND "
                                            ."PE3.k='description' AND "
                                            ."(PE2.v LIKE '%$dbSrch%' OR PE3.v LIKE '%$dbSrch%')",
                                             array('sSortCol'=>'PE1_v,PE2_v') )) )
            {
                $nLimit = 100;
                $nRows = $kfrP->CursorNumRows();

                $raJX['raOut']['numrows-found'] = $nRows;
                $raJX['raOut']['numrows-returned'] = max($nRows,$nLimit);

                if( !$nRows ) {
                    $raJX['sOut'] .= "<p>No results found.</p>";
                } else if( $nRows > $nLimit ) {
                    $raJX['sOut'] .= "<p>$nRows results found. Showing the first $nLimit.</p>";
                } else {
                    $raJX['sOut'] .= "<p>$nRows results found.</p>";
                }

                while( $nLimit-- && $kfrP->CursorFetch() ) {
                    $raJX['sOut'] .= $oSB->DrawProduct( $kfrP, SEEDBasketProductHandler::DETAIL_ALL )
                                    .drawMSDOrderInfo( $oSB, $kfrP );
                }
                $raJX['sOut'] = utf8_encode( $raJX['sOut'] );
                $raJX['bOk'] = true;
            }
            break;

        case "msdSeedsFromGrower":
            //include_once( "_QServerCollection.php" );
            //$o = new QServerCollection( $this, array( ) );
            //$rQ = $o->Cmd( $cmd, $parms );

            $kG = SEEDInput_Int( "kG" );

            // get seeds from kG, also get the species and variety for sorting
            $raP = $oSB->oDB->GetList( "PxPE2",
                                       "uid_seller='$kG' AND product_type='seeds' AND PE1.k='species' AND PE2.k='variety'",
                                       array('sSortCol'=>'PE1_v,PE2_v') );

            foreach( $raP as $ra ) {
                $kfrP = $oSB->oDB->GetKFR( 'P', $ra['_key'] );

                $sP = $oSB->DrawProduct( $kfrP, SEEDBasketProductHandler::DETAIL_ALL )
                     .drawMSDOrderInfo( $oSB, $kfrP );

                $raJX['sOut'] .= utf8_encode( $sP );
                $raJX['bOk'] = true;
            }
            break;

        case "msdVarietyListFromSpecies":
            //include_once( "_QServerCollection.php" );
            //$o = new QServerCollection( $this, array( ) );
            //$rQ = $o->Cmd( $cmd, $parms );

            $kSp = SEEDInput_Int('kSp');

            if( ($dbSp = addslashes($oSB->GetKlugeTypeNameFromKey($kSp))) ) {

//$oSB->oDB->kfdb->SetDebug(2);
                $raP = $oSB->oDB->GetList( "PxPE2",
                                           "product_type='seeds' AND PE1.k='species' AND PE1.v='$dbSp' AND PE2.k='variety'",
                                           array('sSortCol'=>'PE2_v') );
//$oSB->oDB->kfdb->SetDebug(0);
                foreach( $raP as $ra ) {
                    $kfrP = $oSB->oDB->GetKFR( 'P', $ra['_key'] );

                    $sP = $oSB->DrawProduct( $kfrP, SEEDBasketProductHandler::DETAIL_SUMMARY )
                         .drawMSDOrderInfo( $oSB, $kfrP );

                    $raJX['sOut'] .= utf8_encode( $sP );
                    $raJX['bOk'] = true;
                }
            }
            break;

    }
}

done:
echo json_encode( $raJX );


function drawMSDOrderInfo( SEEDBasketCore $oSB, KFRecord $kfrP )
{
    include_once( SITEROOT."l/sl/msd.php" );
    $oW = new SEEDApp_Worker( $oSB->oDB->kfdb, $oSB->sess, "EN" );  // someday this will be in oSB
    $oMSD = new MSDView( $oW );

    $kP = $kfrP->Key();
    $kM = $kfrP->Value('uid_seller');
    $raM = $oMSD->GetMbrContactsRA( $kM );                      // mbr_contacts via mbrsitepipe
    $raPE = $oSB->oDB->GetProdExtraList( $kP );                 // prodExtra
    $kfrG = $oMSD->kfrelG->GetRecordFromDB( "mbr_id='$kM'" );   // sed_growers

    if( ($bIAmAMember = $oSB->sess->CanRead("sed")) ) {
        $who = SEEDCore_ArrayExpand( $raM, "[[firstname]] [[lastname]] in [[city]] [[province]]" );
    } else {
        $who = SEEDCore_ArrayExpand( $raM, "a Seeds of Diversity member in [[province]]" );
    }

    // make this false to prevent people from ordering
    $bEnableAddToBasket = true;

    $sPayment = $oMSD->drawPaymentMethod( $kfrG );
    $sMbrCode = $kfrG->Value('mbr_code');
    $sButton1Attr = $bIAmAMember && $bEnableAddToBasket ? "onclick='AddToBasket_Name($kP);'"
                                                        : "disabled='disabled'";
    $sButton2Attr = $bIAmAMember ? "onclick='msdShowSeedsFromGrower($kM,\"$sMbrCode\");'"
                                 : "disabled='disabled'";

    $s = "<div style='display:none' class='msd-order-info msd-order-info-$kP'>"
         //   ."<div>"
                .SEEDCore_ArrayExpand( $raPE, "<p><b>[[species]] - [[variety]]</b></p>" )
                ."<p>This is offered by $who for $".$kfrP->Value('item_price')." in $sPayment.</p>"
                .($bIAmAMember ? "": "<p>Members can login to request these seeds.</p>")
                ."<p><button $sButton1Attr>Add this request to your basket</button>&nbsp;&nbsp;&nbsp;"
                   ."<button $sButton2Attr>Show other seeds from this grower</button></p>"
                .($bIAmAMember ? drawGrower( $kfrG ) : "")
         //   ."</div>"
        ."</div>";

    return( $s );
}

function drawGrower( KFRecord $kfrG )
{
    global $oW;

    $oSed = new MSDView( $oW );

    $s = "<div style='width:100%;margin:20px auto;max-width:80%;border:1px solid #777;background-color:#f8f8f8'>"
        .$oSed->drawGrowerBlock( $kfrG, true )
        ."</div>";

    return( $s );
}

?>
