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

list($kfdb, $sess, $lang) = SiteStartSessionAccountNoUI();
$oApp = SEEDConfig_NewAppConsole_LoginNotRequired( ['db'=>'seeds1', 'lang'=>$lang ] );     // you don't have to be logged in to use a basket

$oW = new SEEDApp_Worker( $kfdb, $sess, $lang );

//$kfdb->SetDebug(2);

/*
This might solve the issue where http://seeds.ca cannot use http://www.seeds.ca as the ajax domain

// Allow any domain to make ajax requests - see CORS
// Note that this is even necessary for http://www.seeds.ca to access https://www.seeds.ca/.../q because the
// CORS access control policy is per (scheme|domain|port)
header( "Access-Control-Allow-Origin: *" );

*/

$oSB = new MSDBasketCore( $oApp );
$oMSDLib = new MSDLib($oApp);

$raJX = array( 'bOk'=>false, 'sOut'=>"", 'sErr'=>"", 'raOut'=>array() );

if( ($cmd = SEEDInput_Str( "cmd" )) ) {

    $raCmd = $oSB->Cmd( $cmd, $_REQUEST );
    if( $raCmd['bHandled'] ) {
        $raJX = array_merge( $raJX, $raCmd );
        $raJX['sOut'] = SEEDCore_utf8_encode( $raJX['sOut'] );
        $raJX['sErr'] = SEEDCore_utf8_encode( $raJX['sErr'] );
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
                    $raJX['sOut'] .= SEEDCore_utf8_encode($oSB->DrawProduct( $kfrP, SEEDBasketProductHandler_Seeds::DETAIL_ALL, ['bUTF8'=>false] ));
                    //$raJX['sOut'] .= SEEDCore_utf8_encode(drawMSDOrderInfo( $oSB, $kfrP ));
                    $raJX['sOut'] .= "<div style='display:none' class='msd-order-info msd-order-info-".$kfrP->Key()."'></div>";
                }
                $raJX['bOk'] = true;
            }
            break;

        case "msdSeedsFromGrower":
            if( ($kG = SEEDInput_Str('kG')) ) {
                $rQ = $oMSDQ->Cmd('msdSeedList-GetData', ['kUidSeller'=>$kG,'eFilter'=>'LISTABLE','eDrawMode'=>'VIEW_REQUESTABLE VIEW_SHOWCATEGORY VIEW_SHOWSPECIES']);
                foreach( $rQ['raOut'] as $ra ) {
                    $raJX['sOut'] .= $ra['sSeedDraw']
                                    ."<div style='display:none' class='msd-order-info msd-order-info-{$ra['_key']}'></div>";
                    $raJX['bOk'] = true;
                }
            }
            break;

        case "msdVarietyListFromSpecies":
            if( ($kSp = SEEDInput_Str('kSp')) ) {
                $rQ = $oMSDQ->Cmd('msdSeedList-GetData', ['kSp'=>$kSp,'eFilter'=>'LISTABLE','eDrawMode'=>'VIEW_REQUESTABLE']);
                foreach( $rQ['raOut'] as $ra ) {
                    $raJX['sOut'] .= $ra['sSeedDraw']
                                    ."<div style='display:none' class='msd-order-info msd-order-info-{$ra['_key']}'></div>";
                    $raJX['bOk'] = true;
                }
            }
            break;

        case 'msdOrderInfo':
            // when you click on a variety description this order info slides open
            if( ($kP = SEEDInput_Int('kP')) && ($kfrP = $oSB->oDB->GetKFR( 'P', $kP )) ) {
                $raJX['sOut'] .= SEEDCore_utf8_encode($oMSDLib->DrawOrderSlide( $oSB, $kfrP ));
                $raJX['bOk'] = true;
            }
            break;
    }
}

done:
echo json_encode( $raJX );

