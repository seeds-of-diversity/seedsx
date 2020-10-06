<?php

/* Basket manager
 *
 * Copyright (c) 2016-2020 Seeds of Diversity Canada
 */

define( "SITEROOT", "../../" );
include_once( SITEROOT."site2.php" );            // authenticating on seeds2.SEEDSession* but uses seeds.SEEDBasket*
include_once( SEEDCORE."SEEDBasket.php" );
include_once( SEEDAPP."basket/basketProductHandlers.php" );
include_once( SEEDAPP."basket/basketProductHandlers_seeds.php" );
include_once( SEEDCORE."SEEDTemplateMaker.php" );
include_once( SEEDAPP."seedexchange/msdedit.php" );
include_once( SEEDLIB."mbr/MbrContacts.php" );

//var_dump($_REQUEST);

$consoleConfig = [
    'CONSOLE_NAME' => "basketman",
    'HEADER' => "Seeds of Diversity Basket Manager",
//    'HEADER_LINKS' => array( array( 'href' => 'mbr_email.php',    'label' => "Email Lists",  'target' => '_blank' ),
//                             array( 'href' => 'mbr_mailsend.php', 'label' => "Send 'READY'", 'target' => '_blank' ) ),
    'TABSETS' => ['main'=> ['tabs' => [ 'products'        => ['label'=>'Products'],
                                        'store'           => ['label'=>'Store'],
                                        'fulfilment'      => ['label'=>'Fulfilment'],
                                      ],
                            'perms' =>[ 'products'        => [ "W MBRORDER", "A SEEDBasket", "|" ],
                                        'store'           => [ "W MBRORDER", "A SEEDBasket", "|" ],
                                        'fulfilment'      => [ "W MBRORDER", "A SEEDBasket", "|" ],
                                        '|'  // allows screen-login even if some tabs are ghosted
                           ],
                  ],
    ],
    'pathToSite' => '../../',

    'bLogo' => true,
    'consoleSkin' => 'green',
];


// Connecting to seeds2 and authenticating with that (same as mbr_order.php)
// but SEEDBasketDB uses db=>seeds

$oApp = SEEDConfig_NewAppConsole(
                ['db'=>'seeds2',
                 'sessPermsRequired' => $consoleConfig['TABSETS']['main']['perms'],
                 'consoleConfig' => $consoleConfig,
                 'lang' => 'EN' ] );
$oApp->kfdb->SetDebug(1);

class SEEDBasketFulfilment
{
    private $oApp;
    private $oBasketDB;

    function __construct( SEEDAppSessionAccount $oApp )
    {
        $this->oApp = $oApp;
        $this->oBasketDB = new SEEDBasketDB( $oApp->kfdb, $oApp->sess->GetUID(), SITE_LOG_ROOT, ['db'=>'seeds'] );
    }

    function DrawOrderFulfilment( $raBasket )
    {
        $s = "";

        $s .= SEEDCore_ArrayExpand( $raBasket, "<tr><td valign='top'>[[buyer_firstname]] [[buyer_lastname]]</td><td valign='top'>" );

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
        $eStatus = $raP['PUR_eStatus'];
        switch( $eStatus ) {
            case 'PAID':      $colour = "#eea";   $c = "warning";  break;
            case 'FILLED':    $colour = "#beb";   $c = "success";  break;
            case 'CANCELLED': $colour = "#ecc";   $c = "danger";   break;
            default:          $colour = "#eee";   $c = "";         break;
        }

        $amount = $raP['P_quant_type'] == 'MONEY' ? $raP['PUR_f'] : $raP['PUR_n'];

        // overflow:hidden prevents the div from being zero-height due to its children floating, so the background colour appears
        $s = "<div class='' style='clear:both;background-color:$colour;overflow:hidden'>"
                ."<div style='float:left'>{$raP['P_title_en']} : $amount</div>"

                ."<div style='float:right;width:100px;display:inline-block;'>";
        switch( $eStatus ) {
            case "PAID":
                $s .= $this->drawFulfilButton( "Cancel", "prodCancel", $raP['PUR__key'] )
                     .$this->drawFulfilButton( "Fill", "prodFill", $raP['PUR__key'] );
                break;
            case "FILLED":
                $s .= $this->drawFulfilButton( "Un-fill", "prodUnfill", $raP['PUR__key'] );
                break;
            case "CANCELLED":
                $s .= $this->drawFulfilButton( "Un-cancel", "prodUncancel", $raP['PUR__key'] );
                break;
        }
        $s .= "</div>";

        $bAccountingDone = $raP['PUR_flagsWorkflow'] & 1;
        $s .= "<div style='float:right;width:100px;display:inline-block;'>"
             .($bAccountingDone ? ("<div style='text-align:center' onclick='doFulfilButton(\"basketPurchaseUnaccount\",{$raP['PUR__key']});'>"
                                  ."<img style='margin-left:40px' src='".W_CORE_URL."img/ctrl/checkmark01.png' height='20'/></div>")
                                : $this->drawFulfilButton( "Account", "basketPurchaseAccount", $raP['PUR__key']))
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



class mbrBasket_Products
{
    private $oApp;
    private $oSB;
    private $oSVA;              // SVA for this TabSet tab
    private $oMSDAppSeedEdit;
    private $uidSeller;

    function __construct( SEEDAppConsole $oApp, SEEDBasketCore $oSB, SEEDSessionVarAccessor $oSVA )
    {
        $this->oApp = $oApp;
        $this->oSB = $oSB;
        $this->oSVA = $oSVA;
        $this->oMSDAppSeedEdit = new MSDAppSeedEdit( $oSB );

        $this->uidSeller = intval($this->oSVA->SmartGPC( 'uidSeller', [$this->oApp->sess->GetUID()] ));
    }

    function DrawControl()
    {
        $s = "";

        $oMbrContacts = new Mbr_Contacts($this->oApp);

        $raUid = $this->oApp->kfdb->QueryRowsRA1( "SELECT uid_seller FROM seeds_1.SEEDBasket_Products WHERE _status='0' GROUP BY 1 ORDER BY 1" );
        $raSellers = [];
        foreach( $raUid as $uid ) {
            $name = $oMbrContacts->GetContactName($uid)." ($uid)";
            $raSellers[$name] = $uid;
        }
        //ksort($raSellers);
        $oForm = new SEEDCoreForm( 'Plain' );
        return( "<form method='post'>".$oForm->Select( 'uidSeller', $raSellers, "", ['selected'=>$this->uidSeller, 'attrs'=>"onChange='submit();'"] )."</form>" );
    }

    function DrawContent()
    {
        $s = "";

        $sList = $sForm = $sDel = "";

        $kCurrProd = SEEDInput_Int('kP');

        // creates a special KeyframeForm that figures out which product_type it has to update
        $oForm = new SEEDBasket_ProductKeyframeForm( $this->oSB, '' );  // blank product_type means figure it out
        $oForm->Update();

        if( $oForm->GetKey() ) $kCurrProd = $oForm->GetKey();

        // Draw the form (if any) first because it Updates the db
        $oCurrProd = null;
        if( ($newProdType = SEEDInput_Str( 'newProdType' )) ) {
            $oCurrProd = new SEEDBasket_Product( $this->oSB, 0, ['product_type'=>$newProdType] );
        } else if( $kCurrProd ) {
            $oCurrProd = new SEEDBasket_Product( $this->oSB, $kCurrProd );
            $sDel = "<form method='post'><input type='hidden' name='sfAk' value='$kCurrProd'/><input type='hidden' name='sfAd' value='1'/><input type='submit' value='Delete'/></form>";
        }
        if( $oCurrProd ) {
            $oCurrProd->SetValue( 'uid_seller', $this->uidSeller );
            $sForm = $oCurrProd->DrawProductForm();
        }

        // Draw the Add New control
        $raPT = array();
        foreach( SEEDBasketProducts_SoD::$raProductTypes as $k => $ra ) {
            $raPT[$ra['label']] = $k;
        }
        $oForm = new SEEDCoreForm('Plain');
        $sSelect = $oForm->Select( "newProdType", $raPT, "", array() );
        $sList .= "<div><form method='post'>Add a new $sSelect <input type='submit' value='Show Form'/></form></div>";

        // Draw the list
        if( ($oCursor = $this->oSB->CreateCursor( 'product', "uid_seller='{$this->uidSeller}'", ['sSortCol'=>'product_type'] )) ) {
            while( ($oProduct = $oCursor->GetNext()) ) {
                $kP = $oProduct->GetKey();
                $bCurr = ($kCurrProd && $kP == $kCurrProd);
                $sStyleCurr = $bCurr ? "border:2px solid blue;" : "";

                if( $oProduct->FormIsAjax() ) {

                } else {
                    $sList .= "<div class='well' style='padding:5px;margin:5px;$sStyleCurr' onclick='location.replace(\"?kP=$kP\")' ".($bCurr ? "style='border:1px solid #333'" : "").">"
                             .$oProduct->Draw( $bCurr ? SEEDBasketProductHandler::DETAIL_ALL : SEEDBasketProductHandler::DETAIL_TINY, ['bUTF8'=>false] )
                             ."</div>";
                }
            }
        }

        $s .= "<div>$sForm</div><div>$sDel</div><div>$sList</div>";

        // Draw the seeds
        $oMSDAppSeedEdit = new MSDAppSeedEdit( $this->oSB );
        $s .= $oMSDAppSeedEdit->Draw( $this->uidSeller, "" ); //$this->kCurrGrower, $this->kCurrSpecies );

        return( $s );
    }
}


class mbrBasket_Store
{
    private $oApp;
    private $oSB;

    function __construct( SEEDAppConsole $oApp, SEEDBasketCore $oSB )
    {
        $this->oApp = $oApp;
        $this->oSB = $oSB;

        $raTmplParms = array(
            'fTemplates' => array( SEEDAPP."templates/store-sod.html", SEEDAPP."templates/msd.html" ),
            'sFormCid'   => 'Plain',
            'raResolvers'=> array( array( 'fn'=>array($this,'ResolveTag'), 'raParms'=>array() ) ),
            'vars'       => array()
        );
        $this->oTmpl = SEEDTemplateMaker2( $raTmplParms );
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
                $s = $this->oSB->DrawBasketContents();
                break;
            case 'basket_purchase0':
                $s = $this->oSB->DrawPurchaseForm( $raTag['target'] );
                break;
            default:
                $bHandled = false;
                break;
        }
        return( array( $bHandled, $s ) );
    }
}


class mbrBasket_Fulfilment
{
    private $oApp;
    private $oSB;

    function __construct( SEEDAppConsole $oApp, SEEDBasketCore $oSB )
    {
        $this->oApp = $oApp;
        $this->oSB = $oSB;
    }

    function DrawContent()
    {
$s = "";


        if( SEEDInput_Int('doRebuildAllBaskets') ) {
            include_once( SEEDAPP."basket/sodBasketFulfil.php" );
            $o = new SoDOrder_MbrOrder( $this->oApp );
            $n = $o->UpdateBasketsForAllOrders();
            $s .= "<div class='alert alert-success'>Updated $n baskets</div>";
        }

        $s .= "<p><a href='?doRebuildAllBaskets=1'><button>Update All Baskets</button></p>";



$oBasket = new SEEDBasketFulfilment( $this->oApp );

$s .= "<table style='width:100%;border:1px solid #aaa'>";
$raOrders = array();
if( ($dbc = $this->oApp->kfdb->CursorOpen( "SELECT * FROM seeds_1.SEEDBasket_Baskets WHERE _status='0' AND eStatus<>'NEW'" ) ) ) {
    while( ($raB = $this->oApp->kfdb->CursorFetch($dbc)) ) {
        $s .= $oBasket->DrawOrderFulfilment( $raB );
    }
}
$s .= "</table>";


        return( $s );
    }
}


class MyConsole02TabSet extends Console02TabSet
{
    private $oApp;
    private $oSB;
    private $oW;    // object that does the work for the chosen tab

    function __construct( SEEDAppConsole $oApp )
    {
        global $consoleConfig;
        parent::__construct( $oApp->oC, $consoleConfig['TABSETS'] );

        // oApp->kfdb is seeds2 but SEEDBasketDB is created on seeds here
        $this->oApp = $oApp;
        $this->oSB = new SEEDBasketCore( $oApp->kfdb, $oApp->sess, $oApp, SEEDBasketProducts_SoD::$raProductTypes,
                                         ['logdir'=>$oApp->logdir, 'db'=>'seeds'] );
    }

    function TabSetInit( $tsid, $tabname )
    {
        $oT = new Console02TabSet_TabInfo( $this, $tsid, $tabname );    // clearly should be the argument from TabSetInit
        if( $tsid == 'main' ) {
            switch( $tabname ) {
                case 'products':    $this->oW = new mbrBasket_Products( $this->oApp, $this->oSB, $oT->oSVA );     break;
                case 'store':       $this->oW = new mbrBasket_Store( $this->oApp, $this->oSB );        break;
                case "fulfilment":  $this->oW = new mbrBasket_Fulfilment( $this->oApp, $this->oSB );  break;
            }
        }
    }

    function TabSetControlDraw( $tsid, $tabname )
    {
        $s = "";
        if( $tsid == 'main' ) {
            $s = "<style>.console02-tabset-controlarea { padding:15px; }</style>";
            switch( $tabname ) {
                case 'products':    $s .= $this->oW->DrawControl();  break;
                case 'store':       break;
                case 'fulfilment':  break;
            }
        }
        return( $s );
    }

    function TabSetContentDraw( $tsid, $tabname )
    {
        $s = "";
        if( $tsid == 'main' ) {
            $s = "<style>.console02-tabset-contentarea { padding:15px; }</style>";
            switch( $tabname ) {
                case 'products':   $s .= $this->oW->DrawContent();  break;
                case 'store':      $s .= $this->oW->DrawContent();  break;
                case 'fulfilment': $s .= $this->oW->DrawContent();  break;
            }
        }
        return( $s );
    }
}


$oCTS = new MyConsole02TabSet( $oApp );

$s = $oApp->oC->DrawConsole( "[[TabSet:main]]", ['oTabSet'=>$oCTS] );

$s .= "<script>var qURL = '".$oApp->UrlQ('index.php')."';</script>";

$s .= <<<SCRIPT

<script type='text/javascript'>
$(document).ready( function(){  });

function doFulfilButton( jxCmd, kBP )
{
    let jxData = { qcmd : jxCmd,
                   kBP  : kBP,
                   lang : "EN"
                 };
    console.log(jxData);
    SEEDJX_bDebug = true;

    o = SEEDJXSync( qURL, jxData );

    location.reload();

    if( o && typeof o != 'undefined' && typeof o['raOut'] != 'undefined' ) {

    }

}

</script>

SCRIPT;

echo Console02Static::HTMLPage( SEEDCore_utf8_encode($s), "", 'EN',                    // sCharset defaults to utf8
                                ['consoleSkin'=>'green',
                                 'raScriptFiles' => [W_CORE_URL."js/SEEDCore.js", W_CORE_URL."js/console02.js"],
                                ] );


?>
