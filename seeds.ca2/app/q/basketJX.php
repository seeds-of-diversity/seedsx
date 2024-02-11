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
            $sSrch = SEEDInput_Str("srch");
            $rQ = $oMSDQ->Cmd('msdSeedList-GetData-Search', ['sSrch'=>$sSrch, 'eFilter'=>'LISTABLE','eDrawMode'=>"VIEW_REQUESTABLE VIEW_SHOWCATEGORY VIEW_SHOWSPECIES"]);
            if( $rQ['bOk'] ) {
                $raJX['bOk'] = true;
                $nFound = $raJX['raOut']['numrows-found'] = intval(@$rQ['raMeta']['numrows-found']);
                $nReturned = $raJX['raOut']['numrows-returned'] = @intval($rQ['raMeta']['numrows-returned']);

                if( !$nFound ) {
                    $raJX['sOut'] .= "<p>No results found.</p>";
                } else if( $nFound > $nReturned ) {
                    $raJX['sOut'] .= "<p>$nFound results found. Showing the first $nReturned.</p>";
                } else {
                    $raJX['sOut'] .= "<p>$nFound results found.</p>";
                }
                foreach( $rQ['raOut'] as $ra ) {
                    $raJX['sOut'] .= $ra['sSeedDraw']
                                    ."<div style='display:none' class='msd-order-info msd-order-info-{$ra['_key']}'></div>";
                }
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

