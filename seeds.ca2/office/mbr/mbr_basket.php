<?php

/* Basket manager
 *
 * Copyright (c) 2016-2018 Seeds of Diversity Canada
 */

define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );            // move app out of office
include_once( SEEDCORE."SEEDBasket.php" );
include_once( SEEDAPP."basket/basketProductHandlers.php" );
include_once( SEEDAPP."basket/basketProductHandlers_seeds.php" );
include_once( STDINC."SEEDTemplateMaker.php" );
include_once( SEEDCOMMON."console/console01.php" );
include_once( SEEDAPP."seedexchange/msdedit.php" );


list($kfdb, $sess) = SiteStartSessionAccount( array("MBRORDER" => "R") );
$bCanWrite = $sess->CanWrite('MBRORDER');

$oApp = new SEEDAppConsole( $config_KFDB['seeds1']
                            + array( 'sessPermsRequired' => array(),
                                     'logdir' => SITE_LOG_ROOT )
);

class SEEDBasketFulfilment
{
    private $oBasketDB;

    var $kfdb;

    function __construct( KeyFrameDB $kfdb, SEEDSessionAccount $sess )
    {
        $this->oBasketDB = new SEEDBasketDB( $kfdb, $sess->GetUID(), SITE_LOG_ROOT );
        $this->kfdb = $kfdb;
    }

    function DrawOrderFulfilment( $raBasket )
    {
        $s = "";
        $s .= SEEDStd_ArrayExpand( $raBasket, "<tr><td valign='top'>[[buyer_firstname]] [[buyer_lastname]]</td><td valign='top'>" );

        if( ($kfrc = $this->oBasketDB->GetKFRC( "BxP", "B._key='{$raBasket['_key']}'" )) ) {
            while( $kfrc->CursorFetch() ) {
                $s .= $this->drawProductFromBxP( $kfrc->ValuesRA() );
            }
        }

        $s .= "</td></tr>";

        return( $s );
    }

    private function drawProductFromBxP( $raP )
    {
        $eStatus = $raP['BP_eStatus'];
        switch( $eStatus ) {
            case 'PAID':      $colour = "#eea";   $c = "warning";  break;
            case 'FILLED':    $colour = "#beb";   $c = "success";  break;
            case 'CANCELLED': $colour = "#ecc";   $c = "danger";   break;
            default:          $colour = "#eee";   $c = "";         break;
        }

        $amount = $raP['P_quant_type'] == 'MONEY' ? $raP['BP_f'] : $raP['BP_n'];

        // overflow:hidden prevents the div from being zero-height due to its children floating, so the background colour appears
        $s = "<div class='' style='clear:both;background-color:$colour;overflow:hidden'>"
                ."<div style='float:left'>{$raP['P_title']} : $amount</div>"

                ."<div style='float:right;width:100px;display:inline-block;'>";
        switch( $eStatus ) {
            case "PAID":
                $s .= $this->drawFulfilButton( "Cancel", "prodCancel", $raP['BP__key'] )
                     .$this->drawFulfilButton( "Fill", "prodFill", $raP['BP__key'] );
                break;
            case "FILLED":
                $s .= $this->drawFulfilButton( "Un-fill", "prodUnfill", $raP['BP__key'] );
                break;
            case "CANCELLED":
                $s .= $this->drawFulfilButton( "Un-cancel", "prodUncancel", $raP['BP__key'] );
                break;
        }
        $s .= "</div>";
        $s .= "<div style='float:right;width:100px;display:inline-block;'>"
             .($raP['BP_bAccountingDone'] ? "<div style='text-align:center' onclick='doFulfilButton(\"prodUnaccount\",{$raP['BP__key']});'>"
                                           ."<img style='margin-left:40px' src='".W_ROOT."img/checkmark01.png' height='20'/></div>"
                                          : $this->drawFulfilButton( "Account", "prodAccount", $raP['BP__key']))
             ."</div>";

        $s .= "</div>";

        return( $s );
    }

    private function drawFulfilButton( $label, $jxCmd, $kX )
    {
        $s = "";

        $s .= "<button style='float:right;height:20px;font-size:12px' onclick='doFulfilButton(\"$jxCmd\",$kX);'>$label</button>";

        return( $s );
    }
}


class mbrBasket_Products extends Console01_Worker1
{
    private $oMSDAppSeedEdit;

    function __construct( MyBasketConsole $oC, KeyFrameDB $kfdb, SEEDSessionAccount $sess )
    {
        parent::__construct( $oC, $kfdb, $sess );
        $this->oMSDAppSeedEdit = new MSDAppSeedEdit( $oC->oSB );
    }

    function Init()
    {
    }

    function DrawContent()
    {
        $sList = $sForm = "";

        $kCurrProd = SEEDSafeGPC_GetInt('kP');

        // Draw the form (if any) first because it Updates the db
        if( ($newProdType = SEEDSafeGPC_GetStrPlain( 'newProdType' )) ) {
            $sForm = $this->oC->oSB->DrawProductNewForm( $newProdType );
        } else if( $kCurrProd ) {
            $sForm = $this->oC->oSB->DrawProductForm( $kCurrProd );
        }

        // Draw the Add New control
        $raPT = array();
        foreach( SEEDBasketProducts_SoD::$raProductTypes as $k => $ra ) {
            $raPT[$ra['label']] = $k;
        }
        $sSelect = SEEDForm_Select2( "newProdType", $raPT, "", array() );
        $sList .= "<div><form method='post'>Add a new $sSelect <input type='submit' value='Add'/></form></div>";

        // Draw the list
/*
        if( ($kfrcP = $this->oC->oSB->oDB->GetProductKFRC("uid_seller='1'")) ) {
            while( $kfrcP->CursorFetch() ) {
                $kP = $kfrcP->Key();
                $bCurr = ($kCurrProd && $kfrcP->Key() == $kCurrProd);
                $sStyleCurr = $bCurr ? "border:2px solid blue;" : "";
                $sList .= "<div class='well' style='padding:5px;margin:5px;$sStyleCurr' onclick='location.replace(\"?kP=$kP\")' ".($bCurr ? "style='border:1px solid #333'" : "").">"
                         .$this->oC->oSB->DrawProduct( $kfrcP, $bCurr ? SEEDBasketProductHandler::DETAIL_ALL : SEEDBasketProductHandler::DETAIL_TINY )
                         ."</div>";
            }
        }
*/

        $s = $this->oMSDAppSeedEdit->Draw();

        return( $s );
    }
}

class mbrBasket_Store extends Console01_Worker1
{
    function __construct( MyBasketConsole $oC, KeyFrameDB $kfdb, SEEDSessionAccount $sess )
    {
        parent::__construct( $oC, $kfdb, $sess );

        $raTmplParms = array(
            'fTemplates' => array( SEEDAPP."templates/store-sod.html", SEEDAPP."templates/msd.html" ),
            'sFormCid'   => 'Plain',
            'raResolvers'=> array( array( 'fn'=>array($this,'ResolveTag'), 'raParms'=>array() ) ),
            'vars'       => array()
        );
        $this->oTmpl = SEEDTemplateMaker( $raTmplParms );
    }

    function Init()
    {

    }

    function DrawContent()
    {
        $s = $this->oTmpl->ExpandTmpl( 'storeMain' );

        return( $s );
    }

    function ResolveTag( $raTag, SEEDTagParser $oTagDummy_same_as_this_oTmpl_oSeedTag, $raParms )
    {
        $bHandled = true;
        $s = "";

        switch( strtolower($raTag['tag']) ) {
            case 'basket_contents':
                // could parse parms out of target for this method (use a standard seedtag method with the same format that seedform uses)
                $s = $this->oC->oSB->DrawBasketContents();
                break;
            case 'basket_purchase0':
                $s = $this->oC->oSB->DrawPurchaseForm( $raTag['target'] );
                break;
            default:
                $bHandled = false;
                break;
        }
        return( array( $bHandled, $s ) );
    }
}

class mbrBasket_Fulfilment extends Console01_Worker1
{
    function __construct( MyBasketConsole $oC, KeyFrameDB $kfdb, SEEDSessionAccount $sess )
    {
        parent::__construct( $oC, $kfdb, $sess );
    }

    function Init()
    {

    }

    function DrawContent()
    {
$s = "";


$oBasket = new SEEDBasketFulfilment( $this->kfdb, $this->sess );

$s .= "<table style='width:100%;border:1px solid #aaa'>";
$raOrders = array();
if( ($dbc = $this->kfdb->CursorOpen( "SELECT * FROM seeds.SEEDBasket_Baskets WHERE _status='0' AND eStatus<>'NEW'" ) ) ) {
    while( ($raB = $this->kfdb->CursorFetch($dbc)) ) {
        $s .= $oBasket->DrawOrderFulfilment( $raB );
    }
}
$s .= "</table>";


        return( $s );
    }
}


class MyBasketConsole extends Console01
{
    public $oW;
    public $oSB;
    public $oApp;

    function __construct( KeyFrameDB $kfdb, SEEDSessionAccount $sess, SEEDAppConsole $oApp, $raParms )
    {
        parent::__construct( $kfdb, $sess, $raParms );
        $this->oApp = $oApp;

        $this->oSB = new SEEDBasketCore( $kfdb, $sess, $oApp, SEEDBasketProducts_SoD::$raProductTypes, array('logdir'=>SITE_LOG_ROOT) );
    }

    function TabSetInit( $tsid, $tabname )
    {
        if( $tsid == 'main' ) {
            switch( $tabname ) {
                case "Products":    $this->oW = new mbrBasket_Products( $this, $this->kfdb, $this->sess, $this->oApp ); break;
                case "Store":       $this->oW = new mbrBasket_Store( $this, $this->kfdb, $this->sess ); break;
                case "Fulfilment":  $this->oW = new mbrBasket_Fulfilment( $this, $this->kfdb, $this->sess ); break;
            }
        }
    }

    function TabSetControlDraw( $tsid, $tabname )
    {
        if( $tsid == 'main' ) {
            switch( $tabname ) {
                case 'Products':    break;
                case 'Store':       break;
                case 'Fulfilment':  break;
            }
        }
        return( "" );
    }

    function TabSetContentDraw( $tsid, $tabname )
    {
        $s = "";

        if( $tsid == 'main' ) {
            switch( $tabname ) {
                case 'Products':   $s = $this->oW->DrawContent();  break;
                case 'Store':      $s = $this->oW->DrawContent();  break;
                case 'Fulfilment': $s = $this->oW->DrawContent();  break;
            }
        }
        return( $s );
    }
}




header( "Content-type: text/html; charset=ISO-8859-1");    // this should be on all pages so accented chars look right (on Linux anyway)

$kfdb->SetDebug(1);


$raConsoleParms = array(
    'HEADER' => "Seeds of Diversity Basket Manager",
    'CONSOLE_NAME' => "mbrBasket",
    'TABSETS' => array( "main" => array( 'tabs'=> array( "Products" => array('label' => "Products" ),
                                                         "Store" => array('label' => "Store" ),
                                                         "Fulfilment"  => array('label' => "Fulfilment" ),
                                                          ) ) ),
    'bLogo' => true,
    'bBootstrap' => true,
    'script_files' => array( W_ROOT."std/js/SEEDStd.js", W_CORE."js/SEEDCore.js" )
);

$oC = new MyBasketConsole( $kfdb, $sess, $oApp, $raConsoleParms );

echo $oC->DrawConsole( "[[TabSet: main]]" );



?>

<script type='text/javascript'>
$(document).ready( function(){  });

function doFulfilButton( jxCmd, kBxP )
{
    var jxData = { cmd : jxCmd,
                   prod : kBxP,
                   lang : "EN"

          };
    //SEEDJX_bDebug = true;
    o = SEEDJX( "http://localhost/~bob/office/mbr/mbrJX.php", jxData );

    location.reload();

    if( o && typeof o != 'undefined' && typeof o['raOut'] != 'undefined' ) {

    }

}

</script>

<?php



?>
