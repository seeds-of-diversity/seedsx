<?php

/*
 * Seed Directory public interface
 *
 * Copyright (c) 2017-2019 Seeds of Diversity Canada
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

// Don't ask to login here, and allow the page to be viewed if no login.
// But $sess->IsLogin() will only be true if the user has sed=>R (i.e. they are a current member)
list($kfdb, $sess, $lang) = SiteStartSessionAccountNoUI( ["R sed"] );
$oApp = new SEEDAppConsole( $config_KFDB['seeds1']
                            + array( 'sessPermsRequired' => ['R sed'],
                                     'sessUIConfig' => ['bLoginNotRequired'=>true],
                                     'logdir' => SITE_LOG_ROOT,
                                     'lang' => $lang )
);

class SEEDBasketStore
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

        switch( $eStatus ) {
            case 'Open':
                if( $eStatusChange == 'Confirmed' ) {
                    $ra = $this->oSB->ComputeBasketSummary();
                    if( @count($ra['raSellers']) ) {
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


class msdBasket extends SEEDBasketStore
{

    function __construct( KeyFrameDB $kfdb, SEEDSession $sess, SEEDAppConsole $oApp, $lang )
    {
        parent::__construct();
        $this->oW = new SEEDApp_Worker( $kfdb, $sess, $lang );
        $this->oSB = new MSDBasketCore( $this->oW, $oApp );
        $this->oDraw = new MSDCommonDraw( $this->oSB );

        $raTmplParms = array(
            'fTemplates' => array( SEEDAPP."templates/msd.html" ),
            'sFormCid'   => 'Plain',
            'raResolvers'=> array( array( 'fn'=>array($this,'ResolveTag'), 'raParms'=>array() ) ),
            'vars'       => array()
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
                'lang' => $this->oW->lang,
                'bMbrLogin'=> $this->oSB->bIsMbrLogin,
                'siteroot' => SITEROOT,
                'qUrl'    => Site_QRoot()."basketJX.php",
                'sessionRealname' => $this->oW->sess->GetRealname(),
                'sessionNameUID' => $this->oW->sess->GetHTTPNameUID(),
                'sessionNamePWD' => $this->oW->sess->GetHTTPNamePWD(),
        );

        switch( $this->oSB->BasketStatusGet() ) {
            case 'Open':
                $raParms['jsonInstructions'] = $this->drawInstructions();
                $raParms['msdList'] = $this->oDraw->DrawMSDList();
                $s = $this->oTmpl->ExpandTmpl( 'msdMain', $raParms );
                break;

            case 'Confirmed':
                $s = $this->drawConfirmed( $raParms );
                break;

            default:
                // shouldn't happen
                $s = "Seeds of Diversity's Member Seed Directory";
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
        $oDocRepDB = New_DocRepDB_WithMyPerms( $this->oW->kfdb, 0, array('bReadonly'=>true) );
        if( !($kDoc = $oDocRepDB->GetDocFromName( $this->oW->lang == 'FR' ? "web/main/home/msd/msd-instructions-fr" : "web/main/home/msd/msd-instructions-en" )) ||
            !($oDoc = new DocRepDoc( $oDocRepDB, $kDoc )) ||
            !($s = $oDoc->GetText("PUB")) )
        {
            // Nope, this is probably a dev installation, so just use this
            $s = "<p>Seeds of Diversity's Member Seed Directory is a listing of seeds and plants that our member seed savers offer through our national seed exchange.</p>"
                ."<p>All members can request seeds directly from the growers, and payment is usually made with "
                ."stamps, cash, cheques, or Canadian Tire money. We invite all members to request seeds, regardless of whether they offer seeds, "
                ."because participation in the seed exchange is the best way to get to know other members from coast to coast, and to get acquainted "
                ."with the larger seed saving community. We hope that everyone will try saving their own seeds, and when they feel ready to "
                ."share their saved seeds with other members, they will offer them here.</p>"
                ."<p>Members, please login to your Seeds of Diversity account to make your seed requests.</p>"
                ."<p>If you are not a member of Seeds of Diversity, <a href='http://seeds.ca/store' target='_blank'>you can join today</a>!</p>";
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

        $raSummary = $this->oSB->ComputeBasketSummary();

        $s .= "<table style='100%'>";

        foreach( $raSummary['raSellers'] as $uidSeller => $raSeller ) {
            $s1 = "";

            $sSeller = $this->oSB->cb_SellerNameFromUid( $uidSeller );
            $s1 .= "<div style='margin-top:10px;font-weight:bold'>$sSeller (total ".$this->oSB->dollar($raSeller['fTotal']).")</div>";

            $kfrG = $oMSD->kfrelG->GetRecordFromDB( "mbr_id='$uidSeller'" );   // sed_growers
            $s1 .= "<div style='width:100%;margin:20px auto;padding:10px;max-width:80%;border:1px solid #777;background-color:#f8f8f8'>"
                    .$oMSD->drawGrowerBlock( $kfrG, true )
                    ."</div>";

            foreach( $raSeller['raItems'] as $raItem ) {

                if( !$raItem['kBP'] ) continue;     // skip discounts

                // I need PxBP to do this
                $kfrBP = $this->oSB->oDB->GetKFR( 'BP', $raItem['kBP']);
                $kfrP = $this->oSB->oDB->GetKFR( 'P', $kfrBP->Value('fk_SEEDBasket_Products') );

                $s1 .= "<div style='width:100%;margin:20px auto;padding:10px;max-width:80%;border:0px;background-color:#fff'>"
                      .$this->oSB->DrawProduct( $kfrP, SEEDBasketProductHandler_Seeds::DETAIL_VIEW_NO_SPECIES )
                      ."</div>";

            }

            $s2 = "<a href='index.php?kG=$uidSeller' target='_blank' style='text-decoration:none;'>"
                 ."<div style='margin-left:30px;background:#eee;border:1px solid #aaa;padding:10px;text-align:center;width:120px;height:160px;'>"
                     ."Click here to print your Seed Request Form"
                     ."<br/><br/><img src='//seeds.ca/i/img/logo/logoA-300x.png' width='60' height='54'/>"
                 ."</div>"
                 ."</a>";

            $s .= "<tr><td width='60%'><div style='border:1px solid #aaa;margin-top:20px;'>$s1</div></td><td>$s2</td></tr>";
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
                .($raItem['kBP'] ? ("<img height='14' onclick='RemoveFromBasket(".$raItem['kBP'].");' src='http://seeds.ca/w/img/ctrl/delete01.png'/>") : "")
                ."</div>"
                        ."</div>";
            }
            $s .= "</div>";
        }

        $s .= "</table>"
             ."<br/><br/>";

        $s .= $this->oTmpl->ExpandTmpl( 'msdConfirmedFooter', $raTmplParms );

        return( $s );
    }

    function PrintGrower( $kG )
    /**************************
         Print the Seed Request Form for current orders for the given grower
     */
    {
        $s = "";

        // Confirm that this user is logged in.
        if( !$this->oW->sess->IsLogin() ) goto done;

        // Confirm that the current basket is Confirmed.

        $oMSD = new MSDView( $this->oW );

        $raSummary = $this->oSB->ComputeBasketSummary();

        $sSeeds = "<table style='width:100%' border='1' >";
        foreach( $raSummary['raSellers'] as $uidSeller => $raSeller ) {
            if( $uidSeller != $kG )  continue;

            $kfrG = $oMSD->kfrelG->GetRecordFromDB( "mbr_id='$uidSeller'" );   // sed_curr_growers
            $sSeeds .= "<p style='font-weight:bold;font-size:11pt;'>This grower accepts payment by: ".$oMSD->drawPaymentMethod($kfrG)."</p>";

            foreach( $raSeller['raItems'] as $raItem ) {
                //if( $raItem['kBP'] ) {
                    // I need PxBP to do this
                    //$kfrBP = $this->oSB->oDB->GetKFR( 'BP', $raItem['kBP']);
                    //$kfrP = $this->oSB->oDB->GetKFR( 'P', $kfrBP->Value('fk_SEEDBasket_Products') );
                    $sSeeds .= "<tr><td width='75%' valign='top'>{$raItem['sItem']}</td><td valign='top'>".$this->oSB->dollar($raItem['fAmount'])."</td></tr>";
            }

            $sSeeds .= "<tr><td><p style='font-size:12pt'>Total</p></td><td><p style='font-size:12pt;'>".$this->oSB->dollar($raSeller['fTotal'])."</p></td></tr>";
            $sSeeds .= "</table>";
            /*
                        $s1 .= "<div style='width:100%;margin:20px auto;padding:10px;max-width:80%;border:0px;background-color:#fff'>"
                                .$this->oSB->DrawProduct( $kfrP, SEEDBasketProductHandler::DETAIL_SUMMARY )
                                ."</div>";

                    }

                    $s2 = "<a href='index.php?kG=$uidSeller' target='_blank' style='text-decoration:none;'>"
                    ."<div style='margin-left:30px;background:#ccc;padding:10px;text-align:center;width:120px;height:160px;'>Click here to print your Seed Request Form</div>"
                            ."</a>";

                            $s .= "<tr><td width='60%'><div style='border:1px solid #aaa;margin-top:20px;'>$s1</div></td><td>$s2</td></tr>";
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
                                .($raItem['kBP'] ? ("<img height='14' onclick='RemoveFromBasket(".$raItem['kBP'].");' src='http://seeds.ca/w/img/ctrl/delete01.png'/>") : "")
                                ."</div>"
                                        ."</div>";
                            }
                            $s .= "</div>";
            */
        }

        include_once( SEEDCOMMON."mbr/mbrSitePipe.php" );
        $ra = MbrSitePipeGetContactsRA2( $this->oW->kfdb, $kG );
        $sGrowerAddr1 = SEEDCore_ArrayExpand( $ra, "[[firstname]] [[lastname]]<br/>" )
                       .SEEDStd_ExpandIfNotEmpty( @$ra['company'], "[[]]<br/>" )
                       .SEEDCore_ArrayExpand( $ra, "[[address]]<br/>[[city]] [[province]] [[postcode]]" );
        $sGrowerAddr2 = "Grower code: ".$kfrG->Value('mbr_code')."<br/>"
                       .($kfrG->Value('unlisted_email') ? "" : SEEDStd_ExpandIfNotEmpty( @$ra['email'], "Email: [[]]<br/>" ))
                       .($kfrG->Value('unlisted_phone') ? "" : SEEDStd_ExpandIfNotEmpty( @$ra['phone'], "Tel: [[]]<br/>" ));
                       $sGrowerAddr = "<table border='0' width='100%'><tr>"
                          ."<td valign='top' style='width:50%'>$sGrowerAddr1</td>"
                          ."<td valign='top' style='font-weight:normal'>$sGrowerAddr2</td>"
                      ."</tr></table>";

        $ra = MbrSitePipeGetContactsRA2( $this->oW->kfdb, $this->oW->sess->GetUID() );
        $kfrGReq = $oMSD->kfrelG->GetRecordFromDB( "mbr_id='".$this->oW->sess->GetUID()."'" );
        $sRequestAddrLabel = SEEDCore_ArrayExpand( $ra, "[[firstname]] [[lastname]]<br/>" )
                            .SEEDStd_ExpandIfNotEmpty( @$ra['company'], "[[]]<br/>" )
                            .SEEDCore_ArrayExpand( $ra, "[[address]]<br/>[[city]] [[province]] [[postcode]]" );
        $sRequestAddrExtra = $sRequestAddrLabel."<br/>"
                            .SEEDStd_ExpandIfNotEmpty( @$ra['email'], "Email: [[]]<br/>" )
                            .SEEDStd_ExpandIfNotEmpty( @$ra['phone'], "Tel: [[]]<br/>" );
        if( $kfrGReq && $kfrGReq->Value('nTotal') ) {
            $sRequestAddrExtra .= "Grower member ".$kfrGReq->Value('mbr_code')." (offering seeds in this year's directory)";
        }

        $raTmplParms = array(
                'lang' => $this->oW->lang,
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
    $sBody = $oMSD->PrintGrower( $kPrintGrower );
} else {
    $sBody = $oMSD->Draw();
}

$raParms = array( 'sTitle' => ($lang=='EN' ? "Seeds of Diversity - Member Seed Directory"
                                           : "Semences du patrimoine - Catalogue de semences" ),
                  'sCharset' => 'cp1252' );

echo Console01Static::HTMLPage( $sBody, $sHead, $lang, $raParms );

?>