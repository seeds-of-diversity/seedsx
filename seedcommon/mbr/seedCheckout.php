<?php

// javascript could offer and add up the various membership options

// this file belongs in seeds.ca/mbr or equivalent.  it is only in seedcommon while moving to drupal.  point _DMod_Seeds_PageContent to the right place

/* seedCheckout
 *
* Copyright (c) 2009-2015 Seeds of Diversity Canada
*
* Derived implementation for Seeds of Diversity's online checkout system
*/

include_once( SEEDCOMMON."siteStart.php" );
include_once( SEEDCOMMON."mbr/mbrOrderCheckout.php" );


class SoDMbrOrderCheckout extends MbrOrderCheckout
{
    private $bDrupal = false;

    function __construct( KeyFrameDB $kfdb, SEEDSessionAccount $sess, $lang, $bDrupal = false )
    {
        parent::__construct( $kfdb, $sess, $lang );
        $this->bDrupal = $bDrupal;
        $this->addLocalText();
    }

    function FormDrawOrderCol()
    {
        $s = "";


        $oReg = new MbrRegistrations();
// Here's how to promote an event at the top (be sure to exclude it from below too, or controls from two form boxes will conflict)
//        $s .= $this->FormBox( "Register for our 30th Anniversary Party in Victoria",
//                              $this->drawRegBody( 'Victoria2014', $oReg->raRegistrations['Victoria2014'] ) );
//        $s .= "<br/>";

        /*** Membership and Donation ***
         */
        $s .= "<div class='mbro_box'>" //  mbro_expand'>"   floating login makes the expand button appear at level of bottom of login box
             ."<div class='mbro_boxheader'>".$this->oL->S('Annual_Membership_and_Donation')
           //."<div class='mbro_expand-button'><img src='".W_ROOT."img/expand_button.gif'/></div>"
             ."</div>"
             ."<div class='mbro_boxbody' style='display:block'>";

        /* Donation
         */
        $raDon = array( 30, 60, 90 );
        $bDonX = !in_array($this->oKForm->oDS->Value('donation'), $raDon);     // using the type-your-own-number box
        $s .= "<br/><div class='mbro_ctrl'>"
             ."<div style='font-weight:bold;margin-bottom:5px'>".$this->oL->S('Give a Charitable Donation')."</span></div>"
             ."<div class='mbro_help'>".$this->oL->S('donation_desc')."</div><br/>";
        $s .= "<TABLE border='0' cellspacing='0' cellpadding='10' style='margin-left:-10px;margin-top:-10px;'><TR>";
        foreach( $raDon as $d ) {
            $s .= "<TD style='padding-left:15px;'>".$this->oKForm->Radio("donation","", $d ).$this->oL->Dollar($d)."</TD>";
        }
        $s .= "<TD style='padding-left:15px'><INPUT type='radio' name='".$this->oKForm->oFormParms->sfParmField("donation")."' value='X'".($bDonX ? " checked" : "").">"
             ."<INPUT type='text' size='8' name='".$this->oKForm->oFormParms->sfParmField('donationX')."' value='".($bDonX ? $this->myNumber($this->oKForm->oDS->valueEnt('donationX')) : "")."'></TD>"
             ."</TR></TABLE>"
             ."</div><br/>";    // donation ctrl

//$s .= "<div style='margin:-20px 30px 30px 20px'><table><tr><td><img src='http://seeds.ca/photos/upload/2020/10/14/20201014174744-1771830c.jpg' width='250'/></td><td style='padding-left:20px'>To designate a donation to our <a target='_blank' href='http://schoolfoodgardens.ca'>School Food Gardens</a> fall campaign, just tell us in the notes section at the bottom.</td></tr></table></div>";

        /* Gift Membership
         */
/*
        $s .= "<div class='mbro_help' style='border:1px solid #555;padding:10px;position:relative'>"
                 ."<img src='http://seeds.ca/d?n=print/giftofseeds/stocking.png' width='40' hspace='10' align='left' style='position:absolute;top:5px;left:5px;'/>"
                 ."<p style='text-align:left;margin:0px auto;width:100%;font-size:12pt; margin-left:60px'>Give the Gift of Seeds!</p>"
                 ."<p style='margin-left:60px'>This holiday season, give your friends and family a membership to Seeds of Diversity. It's the gift that just keeps growing!</p>"
                 ."<p>To give a gift membership,"
                 ."<ul><li>Enter <b>your gift recipient's</b> name, address, and email in the section to the right, so we can record them as a member, and send them their magazine and e-bulletin subscriptions.</li>"
                 ."<li>In the Notes section at the bottom of this form, enter \"Gift Membership\" and <b>your contact information</b>.</li></ul>"
                 ."<p>You can also download and print a gift card from our <a href='http://www.seeds.ca/giftofseeds' target='_blank'>Gift of Seeds</a> page.</p>"
             ."</div>"
             ."<br/>";
*/

        /* Membership
         */
$s .= "<p style='font-weight:bold'>We're reviewing some new plans about membership for the next year. Check back here soon for details about 2022 memberships.<br/><br/></p>";
/*
        $s .= "<div class='mbro_ctrl'>"
             ."<div style='font-weight:bold;margin-bottom:4px'>".$this->oL->S('membership')."</div>"
             ."<div class='mbro_help'>".$this->oL->S('Please note what mbr fee covers')."</div><br/>"
             .$this->oKForm->Radio('mbr_type',"",'mbr1_35')."&nbsp;&nbsp;&nbsp;".$this->oL->S('One Year Membership form line - no SED')."<br/>"
             .$this->oKForm->Radio('mbr_type',"",'mbr1_45sed')."&nbsp;&nbsp;&nbsp;".$this->oL->S('One Year Membership form line')."<br/>"
             .$this->oKForm->Radio('mbr_type',"",'', array('bNoBlankMatches'=>1))."&nbsp;&nbsp;&nbsp;".$this->oL->S('mbr_none')
             ."</div>"
             ."<br/>";


        $s .= "<DIV class='mbro_help'>".$this->oL->S('membership_desc')."</DIV>"
             ."<DIV class='mbro_help'>".$this->oL->S('mbr_calendar_year')."</DIV>"
             ."<br/>"
             ."</div></div>\n"       // membership and donation
             ."<BR/>";
*/

        /*** Garlic bulbils ***
         */
// If you click, next, back, unclick, next - the checkbox is not unset.  Tried setting the form value to 0 here but that didn't
// work because the value is stored in a SessionVarAccessor, which feeds the form and is not reset if the http doesn't contain this parm.
// So in order to reset the checkbox we'd have to reset the form and the SessionVarAccessor['sExtra']['bBulbils'] which is inconvenient
// to do because you have to do a whole str->urlparms->str.  Find a way to fix checkboxes right.
        //$this->oKForm->SetValue('bBulbils',0);
        //$_SESSION['mbrocdata']['sExtra'] = "";

        $bGarlicAdvertised = true;
        $bGarlicAdvertisedButGone = false;

        if( $bGarlicAdvertised ) {
            $s .= "<a name='garlic'></a>"
                 .$this->FormBox(
                     $this->oL->S("Garlic bulbils available for planting"),
                     ($bGarlicAdvertisedButGone
                      ? ("<p style='border:1px solid #822;color:#822;padding:10px'>Sorry! Our garlic bulbils are sold out for ".date("Y").".  Thank you for your interest!</p>")
                      : ($this->oL->S("Garlic-bulbils-instr")
                         ."<p>".$this->oKForm->Checkbox( 'bBulbils', $this->oL->S("Please send samples of garlic bulbils for $15") )."</p>")
                        )
                     ."<br/><br/>",
                     true );
        }

        /*** Conference Registrations ***
         */
// Comment out the line below if you're promoting an event to the top, because controls will conflict if they're in the form twice
        $s .= $this->RegistrationForm();
        // Here's how you show a single event
        //$s .= $this->FormBox( $this->oL->GetLang() != 'FR' ? "Register for the ECOSGN 2014 Conference"
        //                                                   : "Enregistre pour le symposium de semences ECOSGN 2014",
        //                      $this->drawRegBody( 'ecosgn2014-late', $oReg->raRegistrations['ecosgn2014-late'] ),
        //                      true )
        //     ."<br/>";


        /*** SL Adoption ***
         */
        $s .= "<a name='adoptions'></a>"
             .$this->FormBox(
                 $this->oL->S("Adopt a Variety into the Canadian Seed Library"),
                 $this->formBodyAdoption(),
                 true );

        /*** Publications ***
         */
        $s .= $this->FormBox(
                 "Publications",        // same in EN and FR
                 $this->formBodyPubs(),
                 true );

        /*** Other Payments ***
         */
        $s .= $this->FormBox(
                 $this->oL->S('Misc Payment'),
                 "<p>".$this->oL->S('Misc_payment_instructions').$this->oKForm->Text('fMisc',"",["size"=>5])."</p>",
                 true );


        return( $s );
    }

    private function formBodyAdoption()
    {
        $s =  $this->oL->S("You can adopt etc")
             ."<table class='mbro_ctrl' border='0' cellspacing='0' cellpadding='0'><tr valign='top'>"
             ."<td class='mbro_ctrl'>".$this->oL->S('Choose a variety to adopt').":</td>"
             ."<td class='mbro_ctrl'>".$this->oKForm->Radio('slcvchoose',"","as_needed")." ".$this->oL->S('as needed')."<br/>"
             .$this->oKForm->Radio('slcvchoose',"","")." ".$this->oKForm->Text('slAdopt_cv',"",array("size"=>15))."&nbsp;&nbsp;&nbsp;<A href='https://seeds.ca/diversity/seed-library/list' target='_blank'>See Varieties Here</A>"
             ."</td></tr></table>"
             ."<hr style='width:50%;margin-left:10'/>";

        $s .= "<table class='mbro_ctrl' border='0' cellspacing='0' cellpadding='0'>"
            ."<tr valign='top'>"
            ."<td>".$this->oL->S("Full adoption")."</td>"
            ."<td style='padding-left:15px'>".$this->oKForm->Radio("slAdopt_amount","", 250 )." ".$this->oL->Dollar(250)."</td>"
            ."</tr><tr>"
            ."<td>".$this->oL->S("Partial adoption")."</td><td>";

        $raAdopt = array( 50, 100, 150, 200, 250 );
        $bAdoptX = !in_array($this->oKForm->oDS->Value('slAdopt_amount'), $raAdopt);     // using the type-your-own-number box
        $s .= "<table border='0' cellspacing='0' cellpadding='10'><tr>";
        foreach( $raAdopt as $d ) {
            if( $d == 250 ) continue;
            $s .= "<td style='padding-left:15px;'>".$this->oKForm->Radio("slAdopt_amount","", $d ).$this->oL->Dollar($d)."</td>";
        }
        $s .= "<td style='padding-left:15px'><input type='radio' name='".$this->oKForm->oFormParms->sfParmField("slAdopt_amount")."' value='X'".($bAdoptX ? " checked" : "")."/>"
             ."<INPUT type='text' size='8' name='".$this->oKForm->oFormParms->sfParmField('slAdopt_amountX')."' value='".($bAdoptX ? $this->myNumber($this->oKForm->oDS->valueEnt('slAdopt_amountX')) : "")."'/></td>"
             ."</tr></table>"
             ."</td></tr></table>"
             ."<hr style='width:50%;margin-left:10'/>";

        $s .= "<table class='mbro_ctrl' border='0' cellspacing='0' cellpadding='0'><tr valign='top'>"
             ."<td>".$this->oL->S('Adopt in the name of').":</td>"
             ."<td>"
             .$this->oKForm->Radio('slnamechoose',"",0)." ".$this->oL->S('my name')."<br/>"
             .$this->oKForm->Radio('slnamechoose',"",1)." ".$this->oL->S('anonymous')."<br/>"
             .$this->oKForm->Radio('slnamechoose',"",2)." ".$this->oL->S('as a gift to')." ".$this->oKForm->Text('slAdopt_name',"",array("size"=>25))
             //."<div class='mbro_infobox'>Gift donors will be sent a card in the recipient's name, describing the adopted variety. Please make your donation by Dec 14 for holiday delivery.</div>"
             ."</td></tr></table>";

        return( $s );
    }

    private function formBodyPubs()
    {
        $s = "<p align='center'>".$this->oL->S('see_descriptions_here')."</p>"
             ."<table class='mbro_ctrl' cellspacing='0' cellpadding='0' border='0'><tr valign='top'>"
             ."<td width='60'>&nbsp;</td>"
             ."<td width='500'><b>".$this->oL->S('title')."</b></td>"
             ."<td width='200'><b>".$this->oL->S('price')."</b></td>"
             ."<td width='60'><b>".$this->oL->S('quantity')."</b></td>"
             ."</tr>";
        //$s .= $this->mbr_pub( $this->oL->GetLang() == "EN" ? "ssh_en" : "ssh_fr" )
        //     .$this->mbr_pub( $this->oL->GetLang() == "EN" ? "ssh_fr" : "ssh_en" )
        $s .= $this->mbr_pub( "ssh_en6" );
        $s .= $this->mbr_pub( "ssh_fr6" );
        $s .= $this->mbr_pub( "suechan2012" );
        $s .= $this->mbr_pub( "kent2012" );

        if( true ) { //$this->oL->GetLang() == "EN" ) {
            $s .= "<tr><td>".$this->oL->S('vend_everyseed')."</td>"
                 ."<td>Every Seed Tells a Tale<br/><span style='color:red'>Sorry, out of stock</span></td>"
                 ."<td>$35 plus shipping</td>"
                 ."<td><input disabled type=text name='".$this->oKForm->oFormParms->sfParmField('pub_everyseed')."' "
                 ."value='".$this->myNumber($this->oKForm->oDS->ValueEnt('pub_everyseed'))."' size='3'/></td></tr>"
                 ."<tr><td colspan='4'><hr/></td></tr>";
        }

        // .$this->mbr_pub( "nmd" )
        // .$this->mbr_pub( "shc" )
        $s .= "</table>"
             ."<p>U.S. orders: please add $5 for shipping in the Miscellaneous Payment box below</p>"
             ."<p>".$this->oL->S('contact_for_bulk_rates')."</p>";

        return( $s );
    }

    function mbr_pub( $type )
    {
        $s = "<TR><TD>".$this->oL->S('vend_'.$type)."</TD>"
            ."<TD>".$this->oL->S('pub_'.$type)."</TD>"
            ."<TD>".$this->oL->Dollar( $this->oMbrOrder->raPubs[$type]['price'] )."</TD>"
            ."<TD><INPUT type=text name='".$this->oKForm->oFormParms->sfParmField('pub_'.$type)."' "
            ."value='".$this->myNumber($this->oKForm->oDS->ValueEnt('pub_'.$type))."' size='3''></TD></TR>"
            ."<TR><TD colspan='4'><HR></TD></TR>";
        return( $s );
    }

    function myNumber( $n )
    {
        return( $n ? $n : "" );
    }

    function ValidateParmsOrderValid( $oSVar )
    {
        $bOk = true;

        // if( test fails ) {
        //     $bOk = false;
        //     $this->raFormErrors[] = "Message to user";
        // }

        $bOk = $this->RegistrationValid( $oSVar ) && $bOk;
        return( $bOk );
    }

    function ValidateParmsOrderMakeKFR( $oSVar )
    {
        /*** Membership and Donation ***/
        if( $oSVar->VarGet("mbr_type") == 'mbr1_35' )     $this->kfrOC->SetValue( "mbr_type", "mbr1_35" );
        if( $oSVar->VarGet("mbr_type") == 'mbr1_45sed' )  $this->kfrOC->SetValue( "mbr_type", "mbr1_45sed" );

        $fDonation = floatval($oSVar->VarGet("donation") == 'X' ? $oSVar->VarGet("donationX") : $oSVar->VarGet("donation") );
        if( $fDonation > 0 ) {  // don't let them type a negative number
            $this->kfrOC->SetValue( "donation", strval($fDonation) );
        }

        /*** Publications ***/
        foreach( $this->oMbrOrder->raPubs as $k => $raV ) {
            $n = intval($oSVar->VarGet('pub_'.$k));
            if( $n > 0 ) {  // don't let them type negative numbers
                if( $k == 'everyseed' ) {
                    $this->kfrOC->UrlParmSet( "sExtra", 'nPubEverySeed', $n );
                    if( $n >= 5 ) {
                        $shipping = 0;
                    } else {
                        $ra = array( 0, 10, 12, 15, 18 );
                        $shipping = $ra[$n];
                    }
                    $this->kfrOC->UrlParmSet( "sExtra", 'nPubEverySeed_Shipping', $shipping );
                } else if( $k == 'ssh_en6' ) {
                    $this->kfrOC->UrlParmSet( "sExtra", 'nPubSSH-EN6', $n );
                } else if( $k == 'ssh_fr6' ) {
                    $this->kfrOC->UrlParmSet( "sExtra", 'nPubSSH-FR6', $n );
                } else if( $k == 'suechan2012' ) {
                    $this->kfrOC->UrlParmSet( "sExtra", 'nPubSueChan2012', $n );
                } else if( $k == 'kent2012' ) {
                    $this->kfrOC->UrlParmSet( "sExtra", 'nPubKent2012', $n );
                } else {
                    $this->kfrOC->SetValue( 'pub_'.$k, $n );
                }
            }
        }

        /*** Garlic bulbils ***/
        if( $oSVar->VarGet('bBulbils') ) {
            $this->kfrOC->UrlParmSet( "sExtra", 'bBulbils15', 1 );
        }

        /*** SL Adoption ***/
        $slAdopt_amount = $oSVar->VarGet("slAdopt_amount") == 'X' ? floatval($oSVar->VarGet("slAdopt_amountX")) : $oSVar->VarGet("slAdopt_amount");
        if( $slAdopt_amount > 0 ) {
            $this->kfrOC->UrlParmSet( "sExtra", 'slAdopt_amount', $slAdopt_amount );

            $slAdopt_cv = $oSVar->VarGet("slAdopt_cv");
            if( $oSVar->VarGet("slcvchoose") == "as_needed" )  $slAdopt_cv = "as needed";
            $this->kfrOC->UrlParmSet( "sExtra", 'slAdopt_cv', $slAdopt_cv );

            switch( $oSVar->VarGet("slnamechoose") ) {
                case 1:     $name = "anonymous";                               break;
                case 2:     $name = "gift:".$oSVar->VarGet('slAdopt_name');    break;
                case 0:
                default:    $name = "";                                        break;
            }
            $this->kfrOC->UrlParmSet( "sExtra", 'slAdopt_name', $name );
        }


        /*** Registrations ***/
        $this->RegistrationMakeKFR( $oSVar );
    }


    /********** Registrations **********
     Centralize conference registration code here
    Also add definitions in mbrOrder so tickets will show correctly
    */
    function RegistrationForm()
    {
        $s = "";

        $oReg = new MbrRegistrations();
        foreach( $oReg->raRegistrations as $code => $raReg ) {
            if( !$raReg['bActive'] )  continue;

            $s .= $this->drawReg( $code, $raReg );
        }

        return( $s );
    }

    function drawReg( $code, $raReg )
    {
        $s = "<DIV class='mbro_box mbro_expand'>"
        ."<DIV class='mbro_boxheader'>".$this->oL->S('Registration')
        ."<div class='mbro_expand-button'><img src='".W_ROOT."img/expand_button.gif'/></div>"
        ."<div class='mbro_expand-note'>".$this->oL->S('Click to show')."</div>"
        ."</div>"
        ."<div class='mbro_boxbody'>"
        .$this->drawRegBody( $code, $raReg )
        ."<br/></div></div>\n<br/>";    // mbro_boxbody, mbro_box

        return( $s );
    }

    function drawRegBody( $code, $raReg )
    {
        $s = $this->oMbrOrder->RegistrationText( $raReg, 'header' )
            ."<table border='1' cellpadding='5' cellspacing='5' style='margin-left:1em;border-collapse:collapse'>";


        if( @$raReg['nametag'] ) {
            // Translate
            $s .= "<tr valign='top'><td colspan='2' class='mbro_boxheader'>Your Nametag</td></tr>"
            ."<tr valign='top'><td class='mbro_ctrl'>Name (as you would like it to appear on your nametag)<br/><br/>Organization (optional)<br/><br/>"
            ."<p>if you are registering more than one person please enter the other registrants' nametag information in the 'Send us a Note' area below</p></td>"
            ."<td class='mbro_ctrl'>".$this->oKForm->Text("s${code}_NametagName","",array("size"=>15))
            ."<br/><br/>".$this->oKForm->Text("s${code}_NametagOrg","",array("size"=>15))."</td></tr>";
        }
        $s .= "<tr valign='top'><td colspan='2' class='mbro_boxheader'>".$this->oL->S("reg # tickets")."</td></tr>";

        $nDinner = 0;
        foreach( $raReg['tickets'] as $ticketcode => $raTicket ) {
            if( $raTicket['type'] == 'Dinner' )  ++$nDinner;
            if( $raTicket['type'] != 'Ticket' )  continue;

            $s .= "<tr valign='top'><td class='mbro_ctrl'>".$this->oMbrOrder->RegistrationText( $raTicket, 'formtext' )."</td>"
                 ."<td class='mbro_ctrl'>".$this->oKForm->Text("n${code}_{$ticketcode}","",array("size"=>5))." $ {$raTicket['price']} ea.</td></tr>";
        }

        if( $nDinner ) {
// Translate
$s .= "<tr valign='top'><td colspan='2' class='mbro_boxheader'>Fundraising Dinner : # of tickets</td></tr>";
            foreach( $raReg['tickets'] as $raTicket ) {
                if( $raTicket['type'] != 'Dinner' )  continue;

                $s .= "<tr valign='top'><td class='mbro_ctrl'>".$this->oMbrOrder->RegistrationText( $raTicket, 'formtext' )."</td>"
                     ."<td class='mbro_ctrl'>".$this->oKForm->Text("n${code}_{$ticketcode}","",array("size"=>5))." $ {$raTicket['price']}</td></tr>";
            }
        }

        $s .= "</table>";

        /* If there are radio button choices, draw them now
         */
        if( isset($raReg['radio']) ) {
            // this is a set of radio groups
            foreach( $raReg['radio'] as $radiokey => $raRadioGroup ) {
                $s .= "<div>Please choose:<br/>";
                foreach( $raRadioGroup as $radioval => $raRadio ) {
                    $s .= $this->oKForm->Radio( $radiokey, $this->oMbrOrder->RegistrationText($raRadio, 'formtext'), $radioval )."<br/>";
                }
                $s .= "</div>";
            }
        }

        $s .= $this->oMbrOrder->RegistrationText( $raReg, 'footer' );

        return( $s );
    }


    function RegistrationValid( $oSVar )
    {
        $bOk = true;

    // if( test fails ) {
    //     $bOk = false;
    //     $this->raFormErrors[] = "Message to user";
    // }

    //        $n = intval($oSVar->VarGet("nMontrealFete2009"));
    //        if( $n < 0 ) $bOk = false;

    //        foreach( array( 'nMontreal2012_Friday', 'nMontreal2012_Sat', 'nMontreal2012_Sun', 'nMontreal2012_SatSun', 'nMontreal2012_SatSun_Student', 'nMontreal2012_SatDinner') as $k ) {
    //            if( intval($oSVar->VarGet($k)) < 0 )  $bOk = false;
    //        }

        // Anything is valid except negative numbers of tickets.  Check all active ticketcodes and ensure there isn't one < 0
        $oReg = new MbrRegistrations();
        foreach( $oReg->raRegistrations as $code => $raReg ) {
            if( !$raReg['bActive'] )  continue;

            if( isset( $raReg['tickets']) ) {
                foreach( $raReg['tickets'] as $ticketcode => $raTicket ) {
                    $k = "n{$code}_{$ticketcode}";
                    if( intval($oSVar->VarGet($k)) < 0 )  $bOk = false;
                }
            }
        }

        return( $bOk );
    }

    function RegistrationMakeKFR( $oSVar )
    {
    //        if( ($n = intval($oSVar->VarGet("nMontrealFete2009")) ) ) {
    //            $this->kfrOC->UrlParmSet( "sExtra", "nMontrealFete2009", $n );
    //        }

    //        foreach( array( 'sMontreal2012_NametagName', 'sMontreal2012_NametagOrg' ) as $k ) {
    //            if( ($s = $oSVar->VarGet($k)) ) {
    //                $this->kfrOC->UrlParmSet( "sExtra", $k, $s );
    //            }
    //        }

        // Gather name tag parms
        $oReg = new MbrRegistrations();
        foreach( $oReg->raRegistrations as $code => $raReg ) {
            if( !$raReg['bActive'] )  continue;

            if( @$raReg['nametag'] ) {
                $k = "s{$code}_NametagName";
                if( ($s = $oSVar->VarGet($k)) ) {
                    $this->kfrOC->UrlParmSet( "sExtra", $k, $s );
                }

                $k = "s{$code}_NametagOrg";
                if( ($s = $oSVar->VarGet($k)) ) {
                    $this->kfrOC->UrlParmSet( "sExtra", $k, $s );
                }
            }

            // store the number of tickets of each type
            foreach( $raReg['tickets'] as $ticketcode => $raTicket ) {
                $k = "n{$code}_{$ticketcode}";
                if( ($n = intval($oSVar->VarGet($k)) ) && $n > 0 ) {
                    $this->kfrOC->UrlParmSet( "sExtra", $k, $n );
                }
            }

            if( isset($raReg['radio']) ) {
                foreach( $raReg['radio'] as $radiokey => $raRadioGroup ) {
                    if( ($v = $oSVar->VarGet($radiokey) ) ) {
                        $this->kfrOC->UrlParmSet( "sExtra", $radiokey, $v );
                    }
                }
            }
        }
    }


    function addLocalText()
    {
        $sSiteImg = $this->bDrupal ? ("https://seeds.ca/i/img/") : SITEIMG;
        $sPubPageEN = "//seeds.ca/books";     //$this->bDrupal ? (SITEROOT."publications") : (SITEROOT."vend/forsale.php");
        $sPubPageFR = "//semences.ca/livres"; //$this->bDrupal ? (SITEROOT."publications_fr") : (SITEROOT."vend/vendre.php");

$sGarlicVarieties =
"
<style>#garlictable td {padding:5px}</style>
<table id='garlictable' style='border: 1px solid #aaa;margin-bottom:10px;' border='0' cellspacing='10'>
<tbody style='font-size: 9pt;'>
<tr>
<td colspan='6'><strong>Varieties available / Les vari&eacute;t&eacute;s disponibles</strong>
</tr>
<tr>
<td>Alison's</td>
<td>California Late</td>
<td>Fauquier</td>
<td>Killarney<br /></td>
<td>Mennonite<br /></td>
<td>Red Rezan</td>
</tr>
<tr>
<td>Armenian Porcelain</td>
<td>Cedar Creek</td>
<td>French Red<br /></td>
<td>Korean Purple</td>
<td>Metechi<br /></td>
<td>Russian Giant</td>
</tr>
<tr>
<td>Baba Franchuk's</td>
<td>Central Siberian<br /></td>
<td>Gaia's Joy<br /></td>
<td>Lavigna<br /></td>
<td>Music<br /></td>
<td>Shatilli<br /></td>
</tr>
<tr>
<td>Belarus</td>
<td>Chesnuk Red</td>
<td>Georgian Crystal<br /></td>
<td>Legacy<br /></td>
<td>Persian Star<br /></td>
<td>Siberian<br /></td>
</tr>
<tr>
<td>
<p>Beletic Croatian</p>
</td>
<td>Chet's Italian</td>
<td>German Red<br /></td>
<td>Leningrad<br /></td>
<td>Portugal Azores 1</td>
<td>Sicilian</td>
</tr>
<tr>
<td>Bogatyr</td>
<td>Chiloe</td>
<td>German Stiffneck<br /></td>
<td>Lukak<br /></td>
<td>Purple Glazer<br /></td>
<td>Slovak<br /></td>
</tr>
<tr>
<td>Brown Rose</td>
<td>Chinese 1</td>
<td>Khabar</td>
<td>Malpasse<br /></td>
<td>Racey</td>
<td>Wild Buff<br /></td>
</tr>
<tr>
<td>Brown Tempest</td>
<td>Chinese 2</td>
<td>Kiev</td>
<td>Mediterranean</td>
<td>Railway Creek</td>
<td>Yugoslavian</td>
</tr>
</tbody>
</table>
<p>Sorry, we are not able to ship garlic to the U.S.</p>";


        $sL = array(
            "Annual_Membership_and_Donation"
                => array( "EN" => "Membership and Donation",
                          "FR" => "Adh&eacute;sion et dons" ),
            "One Year Membership form line - no SED"
                => array( "EN" => "$35&nbsp;&nbsp;&nbsp;Membership for one year with on-line Seed Directory",
                          "FR" => "35$&nbsp;&nbsp;&nbsp;Adh&eacute;sion pour un an avec acc&egraves au Catalogue de semences en ligne" ),
            "One Year Membership form line"
                => array( "EN" => "$45&nbsp;&nbsp;&nbsp;Membership for one year with printed and on-line Seed Directory",
                          "FR" => "45$&nbsp;&nbsp;&nbsp;Adh&eacute;sion pour un an avec une version imprim&eacute;e du Catalogue de semences (acc&egrave;s &agrave; la version Web inclus)" ),
            "Give a Charitable Donation"
                => array( "EN" => "Give a charitable donation",
                          "FR" => "Faire un don" ),
            "membership"
                => array( "EN" => "Membership (new or renewal)",
                          "FR" => "Devenir membre ou renouveler votre adh&eacute;sion" ),
            "mbr_none"
                => array( "EN" => "No membership at this time",
                          "FR" => "Pas d'adh&eacute;sion pour le moment" ),

            "membership_desc"
                => array( "EN" => "<P>Membership includes:</P>"
                                 ."<UL compact='compact'>"
                                 ."<LI>a full year subscription to <I>Seeds of Diversity</I> magazine</LI>"
                                 ."<li>our monthly e-bulletin</li>"
                                 ."<LI>our annual Member Seed Directory, which lists "
                                 ."the seeds offered from member-to-member in our annual seed exchange (over 2400 varieties!)</LI></UL>",
                          "FR" => "<P>Votre adh&eacute;sion comprend:</P>"
                                 ."<UL compact='compact'>"
                                 ."<LI>l'abonnement &agrave; notre revue <I>Semences du patrimoine</I></LI>"
                                 ."<LI>le Catalogue de semences, soit la liste de toutes les semences offertes par les membres (environ 2400 vari&eacute;t&eacute;s!)</LI></UL>" ),
            "donation_desc"
                => array( "EN" => "Seeds of Diversity Canada is a registered Canadian charity (89650 8157 RR0001). "
                                 ."We depend on donations to do our work. Please support our horticultural preservation and educational projects by making a tax-creditable donation.",
                          "FR" => "Les Semences du patrimoine Canada (Le Programme semencier du patrimoine Canada) est un organisme "
                                 ."de bienfaisance enregistr&eacute; (89650 8157 RR0001).  Vos dons sont appr&eacute;ci&eacute;s puisqu'ils nous soutiennent dans nos projets "
                                 ."de pr&eacute;servation et de sensibilisation." ),
            "mbr_calendar_year"
                => array( "EN" => "Membership is for the calendar year, January through December. If it is late in the year, "
                                 ."your membership will begin in the next new year, unless you request otherwise.",
                          "FR" => "Toutes les adh&eacute;sions d&eacute;butent le 1er janvier et se terminent le 31 d&eacute;cembre. "
                                 ."Si votre inscription se fait tard au cours de l'ann&eacute;e, votre adh&eacute;sion d&eacute;butera d&eacute;s le "
                                 ."d&eacute;but de l'ann&eacute;e suivante, &agrave; moins que vous ne le sp&eacute;cifiez autrement." ),

            /*** Publications ***/

            "see_descriptions_here"
                => array( "EN" => "<A HREF='$sPubPageEN' target='_blank'>See descriptions here</A>",
                          "FR" => "<A HREF='$sPubPageFR'  target='_blank'>Voir les descriptions en cliquant ici</A>" ),

            "title"
                => array( "EN" => "Title",
                          "FR" => "Titre" ),
            "price"
                => array( "EN" => "Price",
                          "FR" => "Prix" ),
            "quantity"
                => array( "EN" => "Quantity",
                          "FR" => "Quantit&eacute;" ),
            "contact_for_bulk_rates"
                => array( "EN" => "Contact our office for discount rates on bulk orders of 10 or more.",
/*****/                   "FR" => "Plus de 10?  Contactez nous pour un bon prix!" ),
            "pub_ssh_en"
                => array( "EN" => "How to Save Your Own Seeds, 5th edition",
                          "FR" => "How to Save Your Own Seeds (Anglais)" ),
            "pub_ssh_en6"
                => array( "EN" => "How to Save Your Own Seeds, 6th edition",
                          "FR" => "How to Save Your Own Seeds (Anglais)" ),
            "pub_ssh_fr6"
                => array( "EN" => "La conservation des semences (French)",
                          "FR" => "La conservation des semences, une nouvelle &eacute;dition" ),

"pub_ssh_fr"
=> array( "EN" => "La conservation des semences du patrimoine",
          "FR" => "La conservation des semences du patrimoine" ),

"pub_nmd"
=> array( "EN" => "Niche Market Development and Business Planning",
          "FR" => "Niche Market Development and Business Planning (anglais seulement)" ),

"pub_shc"
=> array( "EN" => "Selling Heritage Crops",
          "FR" => "Selling Heritage Crops (anglais seulement)" ),

            "pub_suechan2012"
                => array( "EN" => "Conserving Native Pollinators in Ontario, by Sue Chan",
                          "FR" => "Conserving Native Pollinators in Ontario, by Sue Chan (anglais seulement)" ),
            "pub_kent2012"
                => array( "EN" => "How to Make a Pollinator Garden, by Clement Kent",
                          "FR" => "How to Make a Pollinator Garden, by Clement Kent (anglais seulement)" ),

            "vend_ssh_en"
                => array( "EN" => "<A HREF='${sPubPageEN}#ssh_e' target='_blank'><IMG src='${sSiteImg}vend/ssh_cv.gif' height='50'></A>",
                          "FR" => "<A HREF='${sPubPageFR}#ssh_e' target='_blank'><IMG src='${sSiteImg}vend/ssh_cv.gif' height='50'></A>" ),
            "vend_ssh_en6"
                => array( "EN" => "<A HREF='${sPubPageEN}#ssh_e' target='_blank'><IMG src='${sSiteImg}vend/ssh6en150.jpg' height='50'></A>",
                          "FR" => "<A HREF='${sPubPageFR}#ssh_e' target='_blank'><IMG src='${sSiteImg}vend/ssh6en150.jpg' height='50'></A>" ),
            "vend_ssh_fr6"
                => array( "EN" => "<A HREF='${sPubPageEN}#ssh_f' target='_blank'><IMG src='${sSiteImg}vend/ssh6fr150.jpg' height='50'></A>",
                          "FR" => "<A HREF='${sPubPageFR}#ssh_f' target='_blank'><IMG src='${sSiteImg}vend/ssh6fr150.jpg' height='50'></A>" ),
            "vend_ssh_fr"
                => array( "EN" => "<A HREF='${sPubPageEN}#ssh_f' target='_blank'><IMG src='${sSiteImg}vend/ssh_f_cv.gif' height='50'></A>",
                          "FR" => "<A HREF='${sPubPageFR}#ssh_f' target='_blank'><IMG src='${sSiteImg}vend/ssh_f_cv.gif' height='50'></A>" ),
            "vend_nmd"
                => array( "EN" => "<A HREF='${sPubPageEN}#niche1' target='_blank'><IMG src='${sSiteImg}vend/niche1_cv.gif' height='50'></A>",
                          "FR" => "<A HREF='${sPubPageFR}#niche1' target='_blank'><IMG src='${sSiteImg}vend/niche1_cv.gif' height='50'></A>" ),
            "vend_shc"
                => array( "EN" => "<A HREF='${sPubPageEN}#niche2' target='_blank'><IMG src='${sSiteImg}vend/niche2_cv.gif' height='50'></A>",
                          "FR" => "<A HREF='${sPubPageFR}#niche2' target='_blank'><IMG src='${sSiteImg}vend/niche2_cv.gif' height='50'></A>" ),
            "vend_everyseed"
                => array( "EN" => "<A HREF='${sPubPageEN}#every_seed' target='_blank'><IMG src='${sSiteImg}vend/EverySeed150.png' height='50'></A>",
                          "FR" => "<A HREF='${sPubPageFR}#every_seed' target='_blank'><IMG src='${sSiteImg}vend/EverySeed150.png' height='50'></A>" ),

            "vend_suechan2012"
                => array( "EN" => "<A HREF='${sPubPageEN}#suechan2012' target='_blank'><IMG src='//www.seeds.ca/d?n=pubs/cover-conserving-native-pollinators--600.jpg' height='50'></A>",
                          "FR" => "<A HREF='${sPubPageEN}#suechan2012' target='_blank'><IMG src='//www.seeds.ca/d?n=pubs/cover-conserving-native-pollinators--600.jpg' height='50'></A>" ),
            "vend_kent2012"
                => array( "EN" => "<A HREF='${sPubPageEN}#kent2012' target='_blank'><IMG src='//www.seeds.ca/d?n=pubs/cover-how-to-make-a-pollinator-garden--420.jpg' height='50'></A>",
                          "FR" => "<A HREF='${sPubPageEN}#kent2012' target='_blank'><IMG src='//www.seeds.ca/d?n=pubs/cover-how-to-make-a-pollinator-garden--420.jpg' height='50'></A>" ),

            "Registration"
                => array( "EN" => "Register for an Event",
    /* ?? */                      "FR" => "Conf&eacute;rences, &Eacute;v&eacute;nements" ),

            "Garlic bulbils available for planting"
                => array( "EN" => "Garlic bulbils available for planting",
                          "FR" => "Bulbilles d'ails disponibles" ),

            "Garlic-bulbils-instr"
                => array( "EN" => "<p>Garlic bulbils are available for autumn planting. These are not garlic bulbs! See our article "
                                 ."<a href='http://www.seeds.ca/d?n=web/ebulletin/2016-08-en/articles/garlic' target='_blank'>"
                                 ."How to Grow Garlic from Bulbils</a> if you're not sure what bulbils are or what to do with them.</p>"
                                 ."<p>We will send 5 varieties for $15, with at least 10 bulbils of each variety. We cannot ensure "
                                 ."special requests but if you have favourites, please mention them in the Notes section below, and "
                                 ."we will try to include them.</p>"
                                 .$sGarlicVarieties,

                          "FR" => "<p>Nous offrons 48 vari&eacute;t&eacute;s de bulbilles d'ail pour la plantation d&egrave;s cet automne! "
                                 ."Attention, ce sont des bulbilles et non pas de gros ca&iuml;eu auquel vous &ecirc;tes habitu&eacute;, "
                                 ."mais le tout petit bulbe qui pousse dans la fleur &agrave; la cime des plantes.</p>"
                                 ."<p>Consultez notre article "
                                 ."<a href='http://www.semences.ca/d?n=web/ebulletin/2016-08-fr/articles/ail' target='_blank'>"
                                 ."La culture des bulbilles d'ail</a></p>"
                                 ."<p>Nous enverrons au moins 10 de bulbilles de 5 vari&eacute;t&eacute;s diff&eacute;rentes pour 15$ frais de poste inclus. "
                                 ."Nous ne serons peut-&ecirc;tre pas en mesure d'offrir des vari&eacute;t&eacute;s sur demande, mais si vous connaissez "
                                 ."votre vari&eacute;t&eacute; pr&eacute;f&eacute;r&eacute;e, n'h&eacute;sitez pas &agrave; la demander et nous ferons "
                                 ."tout notre possible pour vous la procurer.</p>"
                                 .$sGarlicVarieties,
                        ),
            "Please send samples of garlic bulbils for $15"
                => array( "EN" => "Please send samples of garlic bulbils for $15",
                          "FR" => "$15 &nbsp;&nbsp;Envoyez 5 &eacute;chantillons diff&eacute;rentes des bulbilles d'ails" ),



            "Adopt a Variety into the Canadian Seed Library"
                => array( "EN" => "Adopt a Variety into the Canadian Seed Library",
                          "FR" => "Adoptez une vari&eacute;t&eacute; &agrave; la Biblioth&egrave;que canadienne des semences" ),
            "You can adopt etc"
                => array( "EN" => "<P>You can adopt a heritage seed variety into our Seed Library forever, with a donation!</P>"
                                 ."<P>A full adoption of $250 will preserve a seed variety for all time. You can also make a partial adoption of any amount. "
                                 ."Donations of $50 or more will be permanently recognized in the Seed Library, and the "
                                 ."full amount of every adoption is a tax-receiptable charitable donation.</P>",
/* TODO */                "FR" => "<P>Vous pouvez adopter une vari&eacute;t&eacute; dans notre Biblioth&egrave;que des semences, en permanence.</P>"
                                 ."<P>Chaque don de 250$ nous permet de rendre une vari&eacute;t&eacute; disponible pour les jardiniers pour toujours. "
                                 ."Vous pouvez aussi partager une adoption partielle. Tous les dons au montant de 50$ ou de plus seront identifi&eacute;es "
                                 ."pour toujours dans la Biblioth&egrave;que des semences, et le montant total de chaque adoption est un don charitable "
                                 ."pour lequel le donneur recevra un re&ccedil;u.</P>" ),
            "Full adoption"
                => array( "EN" => "Full adoption",
                          "FR" => "Adoption compl&egrave;ve" ),
            "Partial adoption"
                => array( "EN" => "Partial adoption",
                          "FR" => "Adoption partielle" ),
            "as needed"
                => array( "EN" => "as needed",
                          "FR" => "selon les besoins" ),
            "my name"
                => array( "EN" => "my name",
                          "FR" => "en mon nom" ),
            "anonymous"
                => array( "EN" => "anonymous",
                          "FR" => "de fa&ccedil;on anonyme" ),
            "as a gift to"
                => array( "EN" => "as a gift to",
                          "FR" => "en cadeau &agrave;" ),
            "Adopt in the name of"
                => array( "EN" => "Adopt in the name of",
                          "FR" => "Adoptez" ),
            "Choose a variety to adopt"
                => array( "EN" => "Choose a variety to adopt",
                          "FR" => "Choisissez une vari&eacute;t&eacute; pour adopter" ),
            "Please note what mbr fee covers"
                => array( "EN" => "Please note that your membership fee only covers the cost of publications that you receive. "
                         ."If you would like to contribute to Seeds of Diversity's projects and administrative expenses, "
                         ."please consider adding a charitable donation.",
                         "FR" => "Veuillez prendre note que le montant de l'adh&eacute;sion ne couvre que les frais reli&eacute;s aux publications que vous recevez. Si vous souhaitez contribuer financi&egrave;rement &agrave; nos projets et &agrave; nos frais d'administration, nous vous remercions de penser &agrave; faire un don." ),


            "reg # tickets"
                => array( "EN" => "Registration : # of tickets",
                          "FR" => "Registration : combien de billets" ),

        );
        $this->oL->AddStrsCopy( $sL );
    }
}



function DrawMbr( $kfdb, $lang = "EN", $bDrupal = false )
{
    list($kfdb, $sess, $langDummy) = SiteStartSessionAccountNoUI();

    $oMbrOC = new SoDMbrOrderCheckout( $kfdb, $sess, $lang, $bDrupal );
    return( $oMbrOC->Checkout() );
}

?>
