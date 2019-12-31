<?php

/* Basket manager
 *
 * Copyright (c) 2016-2019 Seeds of Diversity Canada
 */

define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );            // move app out of office
include_once( SEEDCORE."SEEDBasket.php" );
include_once( SEEDAPP."basket/basketProductHandlers.php" );
include_once( SEEDAPP."basket/basketProductHandlers_seeds.php" );
include_once( SEEDCORE."SEEDTemplateMaker.php" );
include_once( SEEDAPP."seedexchange/msdedit.php" );

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



$oApp = SEEDConfig_NewAppConsole(
                ['db'=>'seeds1',
                 'sessPermsRequired' => $consoleConfig['TABSETS']['main']['perms'],
                 'consoleConfig' => $consoleConfig,
                 'lang' => 'EN' ] );


class SEEDBasketFulfilment
{
    private $oApp;
    private $oBasketDB;

    function __construct( SEEDAppSession $oApp )
    {
        $this->oApp = $oApp;
        $this->oBasketDB = new SEEDBasketDB( $oApp->kfdb, $oApp->sess->GetUID(), SITE_LOG_ROOT );
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
                ."<div style='float:left'>{$raP['P_title_en']} : $amount</div>"

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
             .($raP['BP_bAccountingDone'] ? "<div style='text-align:center' onclick='doFulfilButton(\"basketPurchaseUnaccount\",{$raP['BP__key']});'>"
                                           ."<img style='margin-left:40px' src='".W_ROOT."img/checkmark01.png' height='20'/></div>"
                                          : $this->drawFulfilButton( "Account", "basketPurchaseAccount", $raP['BP__key']))
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
    private $oMSDAppSeedEdit;

    function __construct( SEEDAppConsole $oApp, SEEDBasketCore $oSB )
    {
        $this->oApp = $oApp;
        $this->oSB = $oSB;
        $this->oMSDAppSeedEdit = new MSDAppSeedEdit( $oSB );
    }

    function DrawContent()
    {
        $s = "";

        $oMSDAppSeedEdit = new MSDAppSeedEdit( $this->oSB );
        $s .= $oMSDAppSeedEdit->Draw( 1, "" ); //$this->kCurrGrower, $this->kCurrSpecies );

        $sList = $sForm = "";

        $kCurrProd = SEEDInput_Int('kP');

        // creates a special KeyframeForm that figures out which product_type it has to update
        $oForm = new SEEDBasket_ProductKeyframeForm( $this->oSB, '' );  // blank product_type means figure it out
        $oForm->Update();

        // Draw the form (if any) first because it Updates the db
        if( ($newProdType = SEEDInput_Str( 'newProdType' )) ) {
            $oCurrProd = new SEEDBasket_Product( $this->oSB, 0 );
            $oCurrProd->SetProductType( $newProdType );
            $sForm = $oCurrProd->DrawProductForm();
        } else if( $kCurrProd ) {
            if( ($oCurrProd = new SEEDBasket_Product( $this->oSB, $kCurrProd )) ) {
                $sForm = $oCurrProd->DrawProductForm();
            }
        }

        // Draw the Add New control
        $raPT = array();
        foreach( SEEDBasketProducts_SoD::$raProductTypes as $k => $ra ) {
            $raPT[$ra['label']] = $k;
        }
        $oForm = new SEEDCoreForm('Plain');
        $sSelect = $oForm->Select( "newProdType", $raPT, "", array() );
        $sList .= "<div><form method='post'>Add a new $sSelect <input type='submit' value='Add'/></form></div>";

        // Draw the list
        if( ($oCursor = $this->oSB->CreateCursor( 'product', "uid_seller='1'", ['sSortCol'=>'product_type'] )) ) {
            while( ($oProduct = $oCursor->GetNext()) ) {
                $kP = $oProduct->GetKey();
                $bCurr = ($kCurrProd && $kP == $kCurrProd);
                $sStyleCurr = $bCurr ? "border:2px solid blue;" : "";

                if( false ) { // $oProduct->FormIsAjax() ) {

                } else {
                    $sList .= "<div class='well' style='padding:5px;margin:5px;$sStyleCurr' onclick='location.replace(\"?kP=$kP\")' ".($bCurr ? "style='border:1px solid #333'" : "").">"
                             .$oProduct->Draw( $bCurr ? SEEDBasketProductHandler::DETAIL_ALL : SEEDBasketProductHandler::DETAIL_TINY, ['bUTF8'=>false] )
                             ."</div>";
                }
            }
        }

        $s .= "<div>$sForm</div><div>$sList</div>";

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


$oBasket = new SEEDBasketFulfilment( $this->oApp );

$s .= "<table style='width:100%;border:1px solid #aaa'>";
$raOrders = array();
if( ($dbc = $this->oApp->kfdb->CursorOpen( "SELECT * FROM seeds.SEEDBasket_Baskets WHERE _status='0' AND eStatus<>'NEW'" ) ) ) {
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

        $this->oApp = $oApp;
        $this->oSB = new SEEDBasketCore( $oApp->kfdb, $oApp->sess, $oApp, SEEDBasketProducts_SoD::$raProductTypes, array('logdir'=>$oApp->logdir) );
    }

    function TabSetInit( $tsid, $tabname )
    {
        if( $tsid == 'main' ) {
            switch( $tabname ) {
                case 'products':    $this->oW = new mbrBasket_Products( $this->oApp, $this->oSB );     break;
                case 'store':       $this->oW = new mbrBasket_Store( $this->oApp, $this->oSB );        break;
                case "fulfilment":  $this->oW = new mbrBasket_Fulfilment( $this->oApp, $this->oSB );  break;
            }
        }
    }

    function TabSetControlDraw( $tsid, $tabname )
    {
        $s = "";
        if( $tsid == 'main' ) {
            $s = "<style>.console02-tabset-controlarea { padding:15px; }</style>"
                ."AAA";
            switch( $tabname ) {
                case 'products':    break;
                case 'store':       break;
                case 'fulfilment':  break;
            }
        }
        return( "" );
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

$s .= "<script>var qURL = '".Q_URL."';</script>";

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
    o = SEEDJXSync( qURL+"index.php", jxData );

    location.reload();

    if( o && typeof o != 'undefined' && typeof o['raOut'] != 'undefined' ) {

    }

}

</script>

SCRIPT;

echo Console02Static::HTMLPage( utf8_encode($s), "", 'EN',                    // sCharset defaults to utf8
                                ['consoleSkin'=>'green',
                                 'raScriptFiles' => [W_CORE_URL."js/SEEDCore.js", W_CORE_URL."js/console02.js"],
                                ] );


?>
