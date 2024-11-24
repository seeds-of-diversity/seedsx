<?php

/*
 * Seed Directory public interface
 *
 * Copyright (c) 2017-2023 Seeds of Diversity Canada
 *
 * Show the listings in the Member Seed Directory
 */

define( "SITEROOT", "../../" );
include( SITEROOT."site.php" );
include_once( STDINC."DocRep/DocRepDB.php" );
include_once( SEEDCOMMON."console/console01.php" );
include_once( SEEDCOMMON."doc/docUtil.php" );           // New_DocRepDB_WithMyPerms

include_once( SITEROOT."l/sl/msd.php" );
include_once( SEEDAPP."seedexchange/msdCommon.php" );

include_once( SEEDCORE."SEEDBasketUI.php" );

// Don't ask to login here, and allow the page to be viewed if no login.
// But $sess->IsLogin() will only be true if the user has sed=>R (i.e. they are a current member)
list($kfdb, $sess, $lang) = SiteStartSessionAccountNoUI( ["R sed"] );
// use SEEDConfig_NewAppConsole_LoginNotRequired()
$oApp = new SEEDAppConsole( $config_KFDB['seeds1']
                            + array( 'sessPermsRequired' => ['R sed'],
                                     'sessUIConfig' => ['bLoginNotRequired'=>true],
                                     'logdir' => SITE_LOG_ROOT,
                                     'lang' => $lang )
);

// Implement Post-Redirect-Get paradigm.
SEEDPRG();


class SEEDBasketStore_Old
{
    protected $oW;
    protected $oDraw;
    protected $oSB;
    protected $oTmpl;

    function __construct()
    {
        // make oSB, oDraw via factory methods
    }

    function StoreStateChange( $eStatusChange )
    {
        $eStatus = $this->oSB->BasketStatusGet();

        if( ($kBasket = $this->oSB->GetBasketKey()) ) {
            switch( $eStatus ) {
                case 'Open':
                    if( $eStatusChange == 'Confirmed' ) {
                        $oB = new SEEDBasket_Basket( $this->oSB, $kBasket );
                        if( count($oB->GetPurchasesInBasket()) ) {
                            $this->oSB->BasketStatusSet( $eStatusChange );
                        } else {
                            var_dump("Basket is empty");
                        }
                    }
                    break;
                case 'Confirmed':
                    if( $eStatusChange == 'Open' ) {
                        $this->oSB->BasketStatusSet( $eStatusChange );
                    }
                    break;
            }
        }
    }
}


class msdBasket extends SEEDBasketStore_Old
{
    private $kfdb_for_DocRepDB;
    private $oApp;
    private $oMSDLib;

    function __construct( KeyFrameDB $kfdb, SEEDSession $sess, SEEDAppConsole $oApp, $lang )
    {
        parent::__construct();
        $this->oApp = $oApp;
        $this->kfdb_for_DocRepDB = $kfdb;
        $this->oW = new SEEDApp_Worker( $kfdb, $sess, $lang );
        $this->oSB = new MSDBasketCore( $oApp );
        $this->oDraw = new MSDCommonDraw( $this->oSB );
        $this->oMSDLib = new MSDLib( $oApp );

        $raTmplParms = array(
            'fTemplates' => array( SEEDAPP."templates/msd.html" ),
            'sFormCid'   => 'Plain',
            'raResolvers'=> array( array( 'fn'=>array($this,'ResolveTag'), 'raParms'=>array() ) ),
            'raVars'      => array( 'msdYear'=> $this->oMSDLib->GetCurrYear() )
        );
        $this->oTmpl = SEEDTemplateMaker( $raTmplParms );

        if( ($eStateChange = SEEDInput_Str("msdStateChange")) ) {
            $this->StoreStateChange( $eStateChange );
        }
    }

    function ResolveTag( $raTag, SEEDTagParser $oTagDummy, $raParms )
    {
        $bHandled = true;
        $s = "";

        switch( strtolower($raTag['tag']) ) {
            case 'basket_contents':
                // could parse parms out of target for this method (use a standard seedtag method with the same format that seedform uses)
                $s = $this->oSB->DrawBasketContents();
                break;
            default:
                $bHandled = false;
                break;
        }
        return( array( $bHandled, $s ) );
    }

    function Draw()
    {
        $raParms = array(
                'lang' => $this->oApp->lang,
                'bMbrLogin'=> $this->oSB->bIsMbrLogin,
                'siteroot' => SITEROOT,
                'qUrl'    => Site_UrlQ('basketJX.php'),
                'sessionRealname' => $this->oApp->sess->GetRealname(),
                'sessionNameUID' => $this->oApp->sess->GetHTTPNameUID(),
                'sessionNamePWD' => $this->oApp->sess->GetHTTPNamePWD(),
        );

        switch( $this->oSB->BasketStatusGet() ) {
            case 'Open':
                $raParms['jsonInstructions'] = $this->drawInstructions();
                $raParms['msdList'] = $this->oDraw->DrawMSDList();
// change this to true to show the Closed for the Season message
// also set bShutdown in msdcore.php
                $raParms['mseClosed'] = false;
                $s = $this->oTmpl->ExpandTmpl( 'msdMain', $raParms );
                break;

            case 'Confirmed':
                $s = $this->drawConfirmed( $raParms );
                break;

            default:
                // shouldn't happen
                $s = "Seeds of Diversity's Member Seed Exchange";
                break;
        }

        return( $s );
    }

    function drawInstructions()
    /**************************
        Fetch instructions from DocRep. If not available, return something else.
     */
    {
        // The production installation should have instructions in DocRep, available to user 0 (e.g. main web site permclass)
        $oDocRepDB = New_DocRepDB_WithMyPerms( $this->kfdb_for_DocRepDB /*$this->oApp->kfdb*/, 0, ['bReadonly'=>true] );
        if( !($kDoc = $oDocRepDB->GetDocFromName( $this->oApp->lang == 'FR' ? "web/main/home/msd/msd-instructions-fr" : "web/main/home/msd/msd-instructions-en" )) ||
            !($oDoc = new DocRepDoc( $oDocRepDB, $kDoc )) ||
            !($s = $oDoc->GetText("PUB")) )
        {
            // Nope, this is probably a dev installation, so just use this
            $s = "<p>Seeds of Diversity's Member Seed Exchange is a program for members to save and share seeds with each other.</p>"
                ."<p>All members can request seeds directly from the growers, and payment is usually made with e-transfers, "
                ."stamps, cash, cheques, or Canadian Tire money. We invite all members to request seeds, regardless of whether they offer seeds, "
                ."because participation in the seed exchange is the best way to get to know other members from coast to coast, and to get acquainted "
                ."with the larger seed saving community. We hope that everyone will try saving their own seeds, and when they feel ready to "
                ."share their saved seeds with other members, they will offer them here.</p>"
                ."<p>Members, please login to your Seeds of Diversity account to make your seed requests.</p>"
                ."<p>If you are not a member of Seeds of Diversity, <a href='https://seeds.ca/store' target='_blank'>you can join today</a>!</p>";
        }

        $s = json_encode( $s );
        return( $s );
    }

    function drawConfirmed( $raTmplParms )
    {
        $s = "";

        $kBPHighlight = 0;

        $oMSD = new MSDView( $this->oW );

        $s .= $this->oTmpl->ExpandTmpl( 'msdConfirmed', $raTmplParms );

        if( !($kBasket = $this->oSB->GetBasketKey()) ) goto done;
        $raSummary = (new SEEDBasket_Basket($this->oSB, $kBasket))->ComputeBasketContents();
        if( !@$raSummary['raSellers'] )  goto done;

        $s .= "<div style='max-width:800px;margin:auto'>"; //"<table style='100%'>";

        foreach( $raSummary['raSellers'] as $uidSeller => $raSeller ) {
            $s1 = "";

// don't use this for seller name; SEEDSession_Users.realname is not updated as reliably as mbr_contacts
            $sSeller = $this->oSB->cb_SellerNameFromUid( $uidSeller );
            $s1 .= "<div style='margin-top:10px;padding:10px;font-weight:bold'>$sSeller (total ".$this->oSB->dollar($raSeller['fSellerTotal']).")</div>";

            $kfrGxM = $this->oMSDLib->KFRelGxM()->GetRecordFromDB( "G.mbr_id='$uidSeller'" );
            $kfrG = $oMSD->kfrelG->GetRecordFromDB( "mbr_id='$uidSeller'" );   // sed_growers
            $s1 .= "<div style='width:100%;margin:20px auto;padding:10px;max-width:80%;border:1px solid #777;background-color:#f8f8f8'>"
                    //.$oMSD->drawGrowerBlock( $kfrG, true )
                    .$this->oMSDLib->DrawGrowerBlock( $kfrGxM, true )
                    ."</div>";

            foreach( $raSeller['raPur'] as $pur ) {
                $kfrP = $this->oSB->oDB->GetKFR( 'P', $pur['oPur']->GetProductKey() );

                $s1 .= "<div style='width:100%;margin:20px auto;padding:10px;max-width:80%;border:0px;background-color:#fff'>"
                      .$this->oSB->DrawProduct( $kfrP, SEEDBasketProductHandler_Seeds::DETAIL_VIEW_WITH_SPECIES, ['bUTF8'=>false] )
                      ."</div>";
            }

//should be MSDGrower class with $oGrower->DrawGrowerBlock(), $oGrower->bEPaymentOnly()

            $s2 = "";
            if( in_array($kfrGxM->Value('eReqClass'), ['mail_email','mail']) ) {
                $s2 .= "<td valign='top' style='padding:10px'>
                            <h4>To send your request by mail</h4>
                            <p>Open the Seed Request Form to the left, print it, and mail it with payment directly to the member offering seeds.
                               Please use a form of payment listed above.</p></td>";
            }
            if( in_array($kfrGxM->Value('eReqClass'), ['mail_email','email']) ) {
                $s2 .= "<td valign='top'  style='padding:10px'>
                            <h4>To send your request by email</h4>
                            <p>Open the Seed Request Form to the left, save it to a PDF file, and email it to the member offering seeds.
                               Tell them how to expect your payment, then make a digital or physical payment as listed above.</p></td>";
            }

            $s2 = "<table border='0' width='100%'><tr>
                   <td style='padding:10px'>
                       <a href='".Site_path_self()."?kG=$uidSeller' target='_blank' style='text-decoration:none;'>
                       <div style='margin-left:30px;background:#eee;border:1px solid #aaa;padding:10px;text-align:center;width:120px;height:160px;'>
                       Click here to print/save your Seed Request Form
                       <br/><br/><img src='//seeds.ca/i/img/logo/logoA-300x.png' width='60' height='54'/>
                       </div>
                       </a>
                   </td>
                   $s2
                   </tr></table>";


            $s .= "<div style='border:1px solid #aaa;margin-top:20px;'>$s1 $s2</div>";
            continue;

            $s .= "<div class='sb_basket_table'>";
            foreach( $raSeller['raItems'] as $raItem ) {
                $sClass = ($kBPHighlight && $kBPHighlight == $raItem['kBP']) ? " sb_bp-change" : "";
                $s .= "<div class='sb_basket_tr sb_bp$sClass'>"
                ."<div class='sb_basket_td'>".$raItem['sItem']."</div>"
                        ."<div class='sb_basket_td'>".$this->oSB->dollar($raItem['fAmount'])."</div>"
                                ."<div class='sb_basket_td'>"
                                        // Use full url instead of W_ROOT because this html can be generated via ajax (so not a relative url)
                // Only draw the Remove icon for items with kBP because discounts, etc, are coded with kBP==0 and those shouldn't be removable on their own
                .($raItem['kBP'] ? ("<img height='14' onclick='RemoveFromBasket(".$raItem['kBP'].");' src='https://seeds.ca/wcore/img/ctrl/delete01.png'/>") : "")
                ."</div>"
                        ."</div>";
            }
            $s .= "</div>";
        }

        $s .= "</div>" //"</table>"
             ."<br/><br/>";

        done:

        $s .= $this->oTmpl->ExpandTmpl( 'msdConfirmedFooter', $raTmplParms );

        return( $s );
    }

    function PrintGrower( $uidSeller )
    /*********************************
         Print the Seed Request Form for current orders for the given grower
     */
    {
        $s = "";

        // Confirm that this user is logged in.
        if( !$this->oApp->sess->IsLogin() ) goto done;

        // Confirm that the current basket is Confirmed.

        $kfrGxM = $this->oMSDLib->KFRelGxM()->GetRecordFromDB("mbr_id='$uidSeller'");   // sed_curr_growers

        $sSeeds = "";
        if( ($kBasket = $this->oSB->GetBasketKey()) ) {
            $raSummary = (new SEEDBasket_Basket($this->oSB, $kBasket))->ComputeBasketContents();
            if( isset($raSummary['raSellers'][$uidSeller]) ) {
                $sSeeds .= "<p style='font-weight:bold;font-size:11pt;'>This grower accepts payment by: ".$this->oMSDLib->DrawPaymentMethod($kfrGxM)."</p>";

                $sSeeds .= "<table style='width:100%' border='1' >";
                foreach( $raSummary['raSellers'][$uidSeller]['raPur'] as $pur ) {
                    $kfrP = $this->oSB->oDB->GetKFR( 'P', $pur['oPur']->GetProductKey() );
                    $sItem = $this->oSB->DrawProduct( $kfrP, SEEDBasketProductHandler_Seeds::DETAIL_TINY, ['bUTF8'=>false] );
                    $price = $pur['oPur']->GetPrice();

                    $sSeeds .= "<tr><td width='75%' valign='top'>$sItem</td><td valign='top'>".$this->oSB->dollar($price)."</td></tr>";
                }
                foreach( $raSummary['raSellers'][$uidSeller]['raExtraItems'] as $xtra ) {
                    $sSeeds .= "<tr><td width='75%' valign='top'>{$xtra['sLabel']}</td><td valign='top'>".$this->oSB->dollar($xtra['fAmount'])."</td></tr>";
                }

                $sSeeds .= "<tr><td><p style='font-size:12pt'>Total</p></td><td><p style='font-size:12pt;'>"
                          .$this->oSB->dollar($raSummary['raSellers'][$uidSeller]['fSellerTotal'])."</p></td></tr>"
                          ."</table>";
            }
        }

// this should probably be done by MSDLib::DrawGrowerBlock()
        include_once( SEEDCOMMON."mbr/mbrSitePipe.php" );
        $sGrowerAddr1 = $kfrGxM->Expand( "[[M_firstname]] [[M_lastname]]<br/>" )
                       .$kfrGxM->ExpandIfNotEmpty( 'M_company', "[[]]<br/>" )
                       .($kfrGxM->Value('eReqClass') != 'email' ? $kfrGxM->Expand("[[M_address]]<br/>[[M_city]] [[M_province]] [[M_postcode]]" ) : "");
        $sGrowerAddr2 = $kfrGxM->Expand( "Grower code: [[mbr_code]]<br/>" )
                       .($kfrGxM->Value('unlisted_email') ? "" : $kfrGxM->ExpandIfNotEmpty( 'M_email', "Email: [[]]<br/>" ))
                       .($kfrGxM->Value('unlisted_phone') ? "" : $kfrGxM->ExpandIfNotEmpty( 'M_phone', "Tel: [[]]<br/>" ));
        $sGrowerAddr = "<table border='0' width='100%'><tr>"
                          ."<td valign='top' style='width:50%'>$sGrowerAddr1</td>"
                          ."<td valign='top' style='font-weight:normal'>$sGrowerAddr2</td>"
                      ."</tr></table>";

        $ra = (new Mbr_Contacts($this->oApp))->GetBasicValues($this->oW->sess->GetUID());
        $kfrGReq = $this->oMSDLib->KFRelGxM()->GetRecordFromDB( "mbr_id='".$this->oW->sess->GetUID()."'" );
        $sRequestAddrLabel = SEEDCore_ArrayExpand( $ra, "[[firstname]] [[lastname]]<br/>" )
                            .SEEDCore_ArrayExpandIfNotEmpty( $ra, 'company', "[[]]<br/>" )
                            .SEEDCore_ArrayExpand( $ra, "[[address]]<br/>[[city]] [[province]] [[postcode]]" );
        $sRequestAddrExtra = ($kfrGReq && $kfrGReq->Value('nTotal') ? "Grower member {$kfrGReq->Value('mbr_code')} (offering seeds in this year's seed exchange)<br/><br/>" : "")
                            .$sRequestAddrLabel."<br/>"
                            .SEEDCore_ArrayExpandIfNotEmpty( $ra, 'email', "Email: [[]]<br/>" )
                            .SEEDCore_ArrayExpandIfNotEmpty( $ra, 'phone', "Tel: [[]]<br/>" );

        $raTmplParms = array(
                'lang' => $this->oApp->lang,
                'bMbrLogin'=> $this->oSB->bIsMbrLogin,
                'siteroot' => SITEROOT,

                'grower-address' => $sGrowerAddr,
                'request-address-label' => $sRequestAddrLabel,
                'request-address-extra' => $sRequestAddrExtra,
                'seed-request' => $sSeeds,
        );

        $s = $this->oTmpl->ExpandTmpl( 'msdSeedRequestForm', $raTmplParms );

        done:
        return( $s );
    }
}


$oMSD = new msdBasket( $kfdb, $sess, $oApp, $lang );

$sHead = "";

if( ($kPrintGrower = SEEDInput_Int('kG')) ) {
    // Sometimes the session logs out on the confirmation screen, so when you click for a Seed Request Form you get a blank page.
    // That's because this app doesn't force login at the top, but operates in both IsLogin() and !IsLogin() modes.
    // You can't just go back to msd.php though; you have to do a state transition.
    if( !$oApp->sess->IsLogin() ) {
        header( "Location: {$oApp->PathToSelf()}?msdStateChange=Open" );
    }

    $sBody = $oMSD->PrintGrower( $kPrintGrower );
} else {
    $sBody = $oMSD->Draw();
}

$raParms = array( 'sTitle' => ($lang=='EN' ? "Seeds of Diversity - Member Seed Directory"
                                           : "Semences du patrimoine - Catalogue de semences" ),
                  'sCharset' => 'cp1252' );

echo Console01Static::HTMLPage( $sBody, $sHead, $lang, $raParms );

?>