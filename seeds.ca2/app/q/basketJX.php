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
include_once( SEEDLIB."msd/msdq.php" );

list($kfdb, $sess, $lang) = SiteStartSessionAccountNoUI();     // you don't have to be logged in to use a basket

$oW = new SEEDApp_Worker( $kfdb, $sess, $lang );

//$kfdb->SetDebug(2);

$oApp = new SEEDAppConsole( $config_KFDB['seeds1']
                            + array( 'sessPermsRequired' => array(),
                                     'logdir' => SITE_LOG_ROOT,
                                     'lang' => $lang )
);

/*
This might solve the issue where http://seeds.ca cannot use http://www.seeds.ca as the ajax domain

// Allow any domain to make ajax requests - see CORS
// Note that this is even necessary for http://www.seeds.ca to access https://www.seeds.ca/.../q because the
// CORS access control policy is per (scheme|domain|port)
header( "Access-Control-Allow-Origin: *" );

*/

$oSB = new MSDBasketCore( $oW, $oApp );    // rewrite MSDBasketCore so it extends MSDQ?

$raJX = array( 'bOk'=>false, 'sOut'=>"", 'sErr'=>"", 'raOut'=>array() );

if( ($cmd = SEEDInput_Str( "cmd" )) ) {

    $raCmd = $oSB->Cmd( $cmd, $_REQUEST );
    if( $raCmd['bHandled'] ) {
        $raJX = array_merge( $raJX, $raCmd );
        $raJX['sOut'] = utf8_encode( $raJX['sOut'] );
        $raJX['sErr'] = utf8_encode( $raJX['sErr'] );
        goto done;
    }

    // By convention, $_REQUEST parms that start with 'config_' go to the constructor, and the rest go to Cmd()
    $oMSDQ = new MSDQ( $oApp, $_REQUEST );
    $raQ = $oMSDQ->Cmd( $cmd, $_REQUEST );
    if( $raQ['bHandled'] ) {
        $raJX = $raQ;
        goto done;
    }


    switch( $cmd ) {

        case "msdSearch":
// Can probably use MSDQ::msdSeedList-GetData with an added srch parameter to do this fetch. No because it doesn't join on description like below (it joins on category instead)

            $dbSrch = addslashes(SEEDInput_Str( "srch" ));

            /* Get sp/cv of seeds where variety or description contains the search term
             *
             * The way to use PxPE is to specify the PE.k for each ProdExtra. Then you get one row for each product with three PE tuples
             * to the right that you can use to filter the rows.
             */
//$oSB->oDB->kfdb->SetDebug(2);
            if( ($kfrP = $oSB->oDB->GetKFRC( "PxPE3",
                                             "product_type='seeds' AND "
                                            ."eStatus='ACTIVE' AND "
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
                    // DrawProduct always returns utf8 now - construct MSDQ with $raConfig['config_bUTF8']=false to get cp1252.
                    // So just utf8_encode the order info
                    $raJX['sOut'] .= utf8_encode($oSB->DrawProduct( $kfrP, SEEDBasketProductHandler::DETAIL_ALL, ['bUTF8'=>false] ))
                                    .utf8_encode(drawMSDOrderInfo( $oSB, $kfrP ));
                }
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
                                       "product_type='seeds' AND "
                                      ."eStatus='ACTIVE' AND "
                                      ."uid_seller='$kG' AND "
                                      ."PE1.k='species' AND PE2.k='variety'",
                                       array('sSortCol'=>'PE1_v,PE2_v') );

            foreach( $raP as $ra ) {
                $kfrP = $oSB->oDB->GetKFR( 'P', $ra['_key'] );

                // DrawProduct always returns utf8 now - construct MSDQ with $raConfig['config_bUTF8']=false to get cp1252.
                // So just utf8_encode the order info
                $raJX['sOut'] .= utf8_encode($oSB->DrawProduct( $kfrP, SEEDBasketProductHandler::DETAIL_ALL, ['bUTF8'=>false] ))
                                .utf8_encode(drawMSDOrderInfo( $oSB, $kfrP ));
                $raJX['bOk'] = true;
            }
            break;

        case "msdVarietyListFromSpecies":
            include_once( SEEDLIB."msd/msdcore.php" );
            $oMSDCore = new MSDCore( $oApp, array() );

            $kSp = SEEDInput_Int('kSp');

            if( ($dbSp = addslashes($oMSDCore->GetKlugeSpeciesNameFromKey($kSp))) ) {

//$oSB->oDB->kfdb->SetDebug(2);
                $raP = $oSB->oDB->GetList( "PxPE2",
                                           "product_type='seeds' AND "
                                          ."eStatus='ACTIVE' AND "
                                          ."PE1.k='species' AND PE1.v='$dbSp' AND PE2.k='variety'",
                                           array('sSortCol'=>'PE2_v') );
//$oSB->oDB->kfdb->SetDebug(0);
                foreach( $raP as $ra ) {
                    $kfrP = $oSB->oDB->GetKFR( 'P', $ra['_key'] );

                    // DrawProduct always returns utf8 now - construct MSDQ with $raConfig['config_bUTF8']=false to get cp1252.
                    // So just utf8_encode the order info
                    $raJX['sOut'] .= utf8_encode($oSB->DrawProduct( $kfrP, SEEDBasketProductHandler_Seeds::DETAIL_VIEW_NO_SPECIES, ['bUTF8'=>false] ))
                                    .utf8_encode(drawMSDOrderInfo( $oSB, $kfrP ));
                    $raJX['bOk'] = true;
                }
            }
            break;

    }
}

done:
echo json_encode( $raJX );


function drawMSDOrderInfo( SEEDBasketCore $oSB, KeyframeRecord $kfrP )
{
    global $oApp;

    include_once( SITEROOT."l/sl/msd.php" );
    $oW = new SEEDApp_Worker( $oSB->oDB->kfdb, $oSB->sess, "EN" );  // someday this will be in oSB
    $oMSD = new MSDView( $oW );

    include_once( SEEDLIB."msd/msdcore.php" );
    $oMSDCore = new MSDCore( $oApp );
    $bRequestable = $oMSDCore->IsRequestableByUser( $kfrP );

    $kP = $kfrP->Key();
    $kM = $kfrP->Value('uid_seller');
    $raM = $oMSD->GetMbrContactsRA( $kM );                      // mbr_contacts via mbrsitepipe
    $raPE = $oSB->oDB->GetProdExtraList( $kP );                 // prodExtra
    $kfrG = $oMSD->kfrelG->GetRecordFromDB( "mbr_id='$kM'" );   // sed_growers

    if( $bRequestable ) {
        $who = SEEDCore_ArrayExpand( $raM, "[[firstname]] [[lastname]] in [[city]] [[province]]" );
    } else {
        $who = SEEDCore_ArrayExpand( $raM, "a Seeds of Diversity member in [[province]]" );
    }

    // make this false to prevent people from ordering
    $bEnableAddToBasket = true;

    $sPayment = $oMSD->drawPaymentMethod( $kfrG );
    $sMbrCode = $kfrG->Value('mbr_code');
    $sButton1Attr = $bRequestable && $bEnableAddToBasket ? "onclick='AddToBasket_Name($kP);'"
                                                           : "disabled='disabled'";
    $sButton2Attr = $bRequestable ? "onclick='msdShowSeedsFromGrower($kM,\"$sMbrCode\");'"
                                    : "disabled='disabled'";

    $s = "<div style='display:none' class='msd-order-info msd-order-info-$kP'>"
         //   ."<div>"
                .SEEDCore_ArrayExpand( $raPE, "<p><b>[[species]] - [[variety]]</b></p>" )
                ."<p>This is offered by $who for $".$kfrP->Value('item_price')." in $sPayment.</p>"
                .($bRequestable ? "": "<p>Members can login to request these seeds.</p>")
                ."<p><button $sButton1Attr>Add this request to your basket</button>&nbsp;&nbsp;&nbsp;"
                   ."<button $sButton2Attr>Show other seeds from this grower</button></p>"
                .($bRequestable ? drawGrower( $kfrG ) : "")
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
