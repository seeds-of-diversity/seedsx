<?php

/* Basket manager
 *
 * Copyright (c) 2016-2018 Seeds of Diversity Canada
 */

define( "SITEROOT", "../../" );
include_once( SITEROOT."site2.php" );
include_once( SEEDCORE."SEEDBasket.php" );
include_once( SEEDAPP."basket/basketProductHandlers.php" );
include_once( SEEDAPP."basket/basketProductHandlers_seeds.php" );
include_once( STDINC."SEEDTemplateMaker.php" );
include_once( SEEDCOMMON."console/console01.php" );

list($kfdb, $sess) = SiteStartSessionAccount( array("MBRORDER" => "R") );
$bCanWrite = $sess->CanWrite('MBRORDER');

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
    function __construct( MyBasketConsole $oC, KeyFrameDB $kfdb, SEEDSessionAccount $sess )
    {
        parent::__construct( $oC, $kfdb, $sess );
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

        $kfrcP = $this->oC->oSB->oDB->GetKFRC( "PxPE3", "product_type='seeds' AND uid_seller='1' "
                                                   ."AND PE1.k='category' "
                                                   ."AND PE2.k='species' "
                                                   ."AND PE3.k='variety' ",
                                                   array('sSortCol'=>'PE1_v,PE2_v,PE3_v') );
        if( $kfrcP ) {
            while( $kfrcP->CursorFetch() ) {
                $kP = $kfrcP->Key();
                $bCurr = ($kCurrProd && $kfrcP->Key() == $kCurrProd);
                $sStyleCurr = $bCurr ? "border:2px solid blue;" : "";
                $sList .= "<div id='msdSeed$kP' class='well msdSeedContainer' style='margin:5px'><div class='msdSeedText' style='padding:0px;$sStyleCurr'>"  // onclick='location.replace(\"?kP=$kP\")'>"
                         .$this->oC->oSB->DrawProduct( $kfrcP, /*$bCurr*/true ? SEEDBasketProductHandler::DETAIL_ALL : SEEDBasketProductHandler::DETAIL_TINY )
                         ."</div></div>";
            }
        }


        if( $sForm ) {
            $sForm = "<form method='post'>"
                    ."<div>$sForm</div>"
                    ."<div><input type='submit' value='Save' style='margin:20px 0px 0px 20px'/></div>"
                    ."</form>";
        }
        $s = "<div class='container-fluid'><div class='row'>"
                ."<div class='col-sm-6'>$sList</div>"
                ."<div class='col-sm-6'>$sForm</div>"
            ."</div></div>";

//$s .= $this->oC->oSB->DrawProductNewForm( 'base-product' );
//$s .= $this->oC->oSB->DrawProductNewForm( 'membership' );
//$s .= $this->oC->oSB->DrawProductNewForm( 'donation' );
//$s .= $this->oC->oSB->DrawProductNewForm( 'book' );
//$s .= $this->oC->oSB->DrawProductNewForm( 'seeds' );
$s .= <<<basketStyle
<style>
.msdSeedEdit { width:100%;display:none;margin-top:5px;padding-top:10px;border-top:1px dashed #888 }
</style>
basketStyle;

$s .= <<<basketScript
<script>
var msdSeedContainerCurr = null;  // the current msdSeedContainer

$(document).ready( function() {
    $(".msdSeedText").click( function(e) {
        // only one msdSeedContainer can be selected at a time
        if( msdSeedContainerCurr != null ) return;

        let id = $(this).parent().attr("id");
        let k = 0;

        if( id.substring(0,7) == 'msdSeed' && (k=parseInt(id.substring(7))) ) {
            msdSeedContainerCurr = $(this).parent();

            let msdSeedEdit = $("<div class='msdSeedEdit'><form ><nobr><input type='text' name='species'/><br/><br/><br/><br/><br/> <input type='text' name='variety'/>&nbsp;<input type='submit' value='Save'/> <button class='msdSeedEditCancel' type='button'>Cancel</button></nobr></form></div>");

            // Add the form inside msdSeedContainer, after msdSeedText. It is initially non-displayed, but fadeIn shows it.
            msdSeedContainerCurr.append(msdSeedEdit);
            msdSeedEdit.fadeIn(500);

            msdSeedEdit.find("form").submit( function(e) { e.preventDefault(); SeedEditSubmit(k); } );
            msdSeedEdit.find(".msdSeedEditCancel").click( function(e) { e.preventDefault(); SeedEditCancel(); } );
        }
    });
});

function SeedEditSubmit(k)
{
    if( msdSeedContainerCurr == null ) return;

    let p = "cmd=msdSeedEditUpdate&kS="+k+"&"+msdSeedContainerCurr.find('select, textarea, input').serialize();
    //alert(p);

    let oRet = SEEDJXSync( "http://localhost/~bob/seedsx/seeds.ca2/app/q/basketJX.php", p );
console.log(oRet);
    msdSeedContainerCurr.css({border:"1px solid blue"});

    SeedEditCancel();
}

function SeedEditCancel()
{
    if( msdSeedContainerCurr == null ) return;

    msdSeedEdit = msdSeedContainerCurr.find('.msdSeedEdit');
    msdSeedEdit.fadeOut(500, function() { msdSeedEdit.remove(); } );     // wait for the fadeOut to complete before removing the msdSeedEdit
    msdSeedContainerCurr = null;
}

</script>
basketScript;

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

    function __construct( KeyFrameDB $kfdb, SEEDSessionAccount $sess, $raParms )
    {
        parent::__construct( $kfdb, $sess, $raParms );

        $this->oSB = new SEEDBasketCore( $kfdb, $sess, SEEDBasketProducts_SoD::$raProductTypes, array('logdir'=>SITE_LOG_ROOT) );
    }

    function TabSetInit( $tsid, $tabname )
    {
        if( $tsid == 'main' ) {
            switch( $tabname ) {
                case "Products":    $this->oW = new mbrBasket_Products( $this, $this->kfdb, $this->sess ); break;
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

$oC = new MyBasketConsole( $kfdb, $sess, $raConsoleParms );

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
