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
include_once( SEEDLIB."msd/msdq.php" );


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



        $oProdHandler = $this->oC->oSB->GetProductHandler( "seeds" ) or die( "Seeds ProductHandler not defined" );
        $oMSDQ = new MSDQ( $this->oC->oSB->oApp, array() );
        $oMSDCore = new MSDCore( $this->oC->oSB->oApp, array() );

        $raSeeds = array();
//        $kfrcP = $this->oC->oSB->oDB->GetKFRC( "PxPE3", "product_type='seeds' AND uid_seller='1' "
//                                                   ."AND PE1.k='category' "
//                                                   ."AND PE2.k='species' "
//                                                   ."AND PE3.k='variety' ",
//                                                   array('sSortCol'=>'PE1_v,PE2_v,PE3_v') );
        if( ($kfrcP = $oMSDCore->SeedCursorOpen( "uid_seller='1'" )) ) {
            $category = "";
            while( $oMSDCore->SeedCursorFetch( $kfrcP ) ) { // $kfrcP->CursorFetch() ) {
                $kP = $kfrcP->Key();
                $bCurr = ($kCurrProd && $kfrcP->Key() == $kCurrProd);
                $sStyleCurr = $bCurr ? "border:2px solid blue;" : "";
                if( $category != $kfrcP->Value('PE1_v') ) {
                    $category = $kfrcP->Value('PE1_v');
                    $sList .= "<div><h2>".@$oMSDCore->GetCategories()[$category]['EN']."</h2></div>";
                }
                $sList .= "<div id='msdSeed$kP' class='well msdSeedContainer' style='margin:5px'>"
                             ."<div class='msdSeedMsg'></div>"
                             ."<div class='msdSeedText' style='padding:0px;$sStyleCurr'>"
                                 .$this->oC->oSB->DrawProduct( $kfrcP, SEEDBasketProductHandler::DETAIL_ALL )
                             ."</div>"
                         ."</div>";
                $raSeeds[$kP] = $oProdHandler->GetProductValues( $kfrcP );
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

$msdSeedEditForm = <<<msdSeedEditForm
    <table><tr>
        <td><input type='text' id='msdSeedEdit_species' name='species' class='msdSeedEdit_inputText' placeholder='Species e.g. LETTUCE'/></td><td>&nbsp;</td>
    </tr><tr>
        <td><input type='text' id='msdSeedEdit_variety' name='variety' class='msdSeedEdit_inputText' placeholder='Variety e.g. Grand Rapids'/></td><td>&nbsp;</td>
    </tr><tr>
        <td><input type='text' id='msdSeedEdit_bot_name' name='bot_name' class='msdSeedEdit_inputText' placeholder='botanical name (optional)'/></td><td>&nbsp;</td>
    </tr><tr>
        <td colspan='2'><textarea style='width:100%' rows='4' id='msdSeedEdit_description' name='description' placeholder='Describe the produce, the plant, how it grows, and its uses'></textarea></td>
    </tr><tr>
        <td><input type='text' id='msdSeedEdit_days_maturity' name='days_maturity' size='5'/>&nbsp;&nbsp;&nbsp;<input type='text' id='msdSeedEdit_days_maturity_seeds' name='days_maturity_seeds' size='5'/></td>
        <td><div class='msdSeedEdit_instruction'><b>Days to maturity</b>: In the first box, estimate how many days after sowing/transplanting it takes for the produce to ripen for best eating. In the second box estimate the number of days until the seed is ripe to harvest. Leave blank if not applicable.</div></td>
    </tr><tr>
        <td><input type='text' id='msdSeedEdit_origin' name='origin' class='msdSeedEdit_inputText'/></td>
        <td><div class='msdSeedEdit_instruction'><b>Origin</b>: Record where you got the original seeds. e.g. another member, a seed company, a local Seedy Saturday.</div></td>
    </tr><tr>
        <td><select id='msdSeedEdit_quantity' name='quantity'><option value=''></option><option value='LQ'>Low Quantity</option><option value='PR'>Please Re-offer</option></select></td>
        <td><div class='msdSeedEdit_instruction'><b>Quantity</b>: If you have a low quantity of seeds, or if you want to ask requestors to re-offer seeds, indicate that here.</div></td>
    </tr><tr>
        <td><select id='msdSeedEdit_eOffer' name='eOffer'><option value='member'>All Members</option><option value='grower-member'>Only members who also list seeds</option><option value='public'>General public</option></select></td>
        <td><p class='msdSeedEdit_instruction'><b>Who may request these seeds from you</b>: <span id='msdSeedEdit_eOffer_instructions'></span></p></td>
    </tr><tr>
        <td><nobr>$<input type='text' id='msdSeedEdit_price' name='price' class='msdSeedEdit_inputText'/></nobr></td>
        <td><div class='msdSeedEdit_instruction'><b>Price</b>: We recommend $3.50 for seeds and $12.00 for roots and tubers. That is the default if you leave this field blank. Members who offer seeds (like you!) get an automatic discount of $1 per item.</div></td>
    </tr></table>
    <input type='submit' value='Save'/> <button class='msdSeedEditCancel' type='button'>Cancel</button>
<br/>Category<br/>Year first listed
msdSeedEditForm;
$msdSeedEditForm = str_replace("\n","",$msdSeedEditForm);   // jquery doesn't like linefeeds in its selectors

$s .= <<<basketStyle
<style>
.msdSeedText_species { font-size:14pt; font-weight:bold; }
.sed_seed_offer  { font-family: helvetica,arial,sans-serif;font-size:10pt; padding:2px; float:right; background-color:#fff; }
.sed_seed_offer_member       { color: #484; border:2px solid #484 }
.sed_seed_offer_growermember { color: #08f; border:2px solid #08f }
.sed_seed_offer_public       { color: #f80; border:2px solid #f80 }
.sed_seed_mc     { font-weight:bold;text-align:right }

.msdSeedEdit { width:100%;display:none;margin-top:5px;padding-top:10px;border-top:1px dashed #888 }
.msdSeedEdit_inputText   { width:95%;margin:3px 0px }
.msdSeedEdit_instruction { background-color:white;border:1px solid #aaa;margin:3px 0px 0px 30px;padding:3px }
</style>
basketStyle;

$s .= "<script>
       var raSeeds = ".json_encode($raSeeds).";
       var qURL = '".SITEROOT_URL."app/q/';
       </script>";

$s .= <<<basketScript
<script>
var msdSeedContainerCurr = null;  // the current msdSeedContainer

$(document).ready( function() {
    $(".msdSeedText").click( function(e) {
        // only one msdSeedContainer can be selected at a time
        if( msdSeedContainerCurr != null ) return;

        $(".msdSeedContainer").css({border:"1px solid #e3e3e3"});
        $(".msdSeedMsg").html("");

        let id = $(this).parent().attr("id");
        let k = 0;

        if( id.substring(0,7) == 'msdSeed' && (k=parseInt(id.substring(7))) ) {
            msdSeedContainerCurr = $(this).parent();
            msdSeedContainerCurr.css({border:"1px solid blue"});

            let msdSeedEdit = $("<div class='msdSeedEdit'><form>$msdSeedEditForm</form></div>");

            // Add the form inside msdSeedContainer, after msdSeedText. It is initially non-displayed, but fadeIn shows it.
            msdSeedContainerCurr.append(msdSeedEdit);

            SeedEditSelectEOffer( msdSeedEdit );
            msdSeedEdit.find('#msdSeedEdit_eOffer').change( function() { SeedEditSelectEOffer( msdSeedEdit ); } );

            for( var i in raSeeds[k] ) {
                msdSeedEdit.find('#msdSeedEdit_'+i).val(raSeeds[k][i]);
            }
            msdSeedEdit.fadeIn(500);

            msdSeedEdit.find("form").submit( function(e) { e.preventDefault(); SeedEditSubmit(k); } );
            msdSeedEdit.find(".msdSeedEditCancel").click( function(e) { e.preventDefault(); SeedEditCancel(); } );
        }
    });
});

function SeedEditSelectEOffer( msdSeedEdit )
{
    switch( msdSeedEdit.find("#msdSeedEdit_eOffer").val() ) {
        case 'member':
        case 'grower-member':
            msdSeedEdit.find('#msdSeedEdit_eOffer_instructions').html( "The name and description of these seeds will be visible to the public on the web-based Seed Directory, but only members will be able to see your contact information to request seeds." );
            break;
        case 'public':
            msdSeedEdit.find('#msdSeedEdit_eOffer_instructions').html( "Anyone who visits the online Seed Directory will be able to request these seeds, whether or not they are a member of Seeds of Diversity. <b>Your name and contact information will be visible to the public.</b> The printed Seed Directory is still only available to members." );
            break;
     }
}

function SeedEditSubmit(k)
{
    if( msdSeedContainerCurr == null ) return;

    let p = "cmd=msdSeed--Update&kS="+k+"&"+msdSeedContainerCurr.find('select, textarea, input').serialize();
    //alert(p);

    let oRet = SEEDJXSync( qURL+"basketJX.php", p );
    console.log(oRet);
    let ok = oRet['bOk'];

    if( ok ) {
        // raOut contains the validated seed data as stored in the database - save that here so it appears if you open the form again
        raSeeds[k]=oRet['raOut'];

        // sOut contains the revised msdSeedText
        msdSeedContainerCurr.find(".msdSeedText").html( oRet['sOut'] );
    }

    SeedEditClose( ok );

    return( ok );
}

function SeedEditCancel()
{
    SeedEditClose( false );
}

function SeedEditClose( ok )
{
    if( msdSeedContainerCurr == null ) return;

    msdSeedEdit = msdSeedContainerCurr.find('.msdSeedEdit');
    msdSeedEdit.fadeOut(500, function() {
            msdSeedEdit.remove();      // wait for the fadeOut to complete before removing the msdSeedEdit
            if( ok ) {
                // do this after fadeOut because it looks better afterward
                msdSeedContainerCurr.find(".msdSeedMsg").html( "<div class='alert alert-success' style='font-size:10pt;margin-bottom:5px;padding:3px 10px;display:inline-block'>Saved</div>" );
            }
            // allow another block to be clicked
            msdSeedContainerCurr = null;
        } );
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
