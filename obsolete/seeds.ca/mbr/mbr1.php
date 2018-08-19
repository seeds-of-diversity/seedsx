<?

/* JOIN -> new membership or renewal
 * DONATE -> just make a donation
 * BUY -> see publications
 *
 * These filter the screen to show the desired item.  Join also gives an option to Buy. Buy gives an option to join and/or donate.
 * Donate gives an option to Buy.
 */



// Put our logo on PayPal page

// Gift subscriptions: you enter your own name separately from the recipient?

// use ENUM fields or CHAR fields for pay_status because I can never remember what the numbers are

// seeds.ca seems to have session_register on, so the initial $lang set by member.php and membre.php are
// overwritten.  The result is that you can switch from one to the other without a language change.

// go to French paypal site, use French PayPal button
// ppipn should check things like txn_id, etc.  These are listed there.
/*
     *  1. Connect to the application specific preprocessing hook.
     *  2. Verify the IPN with Paypal.
     *  3. If the IPN is verified, Check for a completed transaction.
     *  4. If the transaction was completed, check that the transaction ID isn't a duplicate.
     *  5. If the transaction ID is unique, check that the receiver email address is for the local site.
     *  6. If the receiver email address matches, check that the item details presented make sense.
     *  7. If the item details make sense, process the payment.
*/
// probably lots of other paypal options that I should look at


// add a shortcut at the top: if you want to make an online donation, click here to use CanadaHelps.org.
// CanadaHelps charges us a smaller transaction fee than PayPal.  Please use the form below for memberships and
// to purchase publications

// mbr_order_report.php
// show new pp_* values (pp_txn_id, pp_receipt_id, pp_payer_email, pp_payment_status)
// update pay_status using the various FAILED payment_status codes listed in Pineapple's ppipn code
// make the report table look nicer, with more compact fonts and visual cues



function myNumber( $n )
/**********************
    Output nothing if $n==0
 */
{
    return( $n ? $n : "" );
}


function mbr_Form( $lang, $kfr, $sess, $mL )
/*******************************************
 */
{
//  mbr_header( $mL );


// TESTING
if( $kfr->IsEmpty('mail_firstname') && @$_REQUEST['bobtest']=='mytest' ) {
    $kfr->SetValue('mail_firstname', 'Bob');
    $kfr->SetValue('mail_lastname',  'Wildfong');
    $kfr->SetValue('mail_company',   'Seeds of Diversity');
    $kfr->SetValue('mail_addr',      '68 Dunbar Rd South');
    $kfr->SetValue('mail_city',      'Waterloo');
    $kfr->SetValue('mail_prov',      'ON');
    $kfr->SetValue('mail_country',   'Canada');
    $kfr->SetValue('mail_postcode',  'N2L2E3');
    $kfr->SetValue('mail_phone',     '519-886-7542');
    $kfr->SetValue('mail_email',     'bob@seeds.ca');
}

    $raSExtra = array();
    if( !$kfr->IsEmpty( 'sExtra' ) ) {
        $raSExtra = SEEDStd_ParmsURL2RA( $kfr->value('sExtra') );
    }


    function mbr1_donRadio( $kfr, $mL )
    {
        $raDon = array( 30, 60, 90 );
        $bDonX = !in_array($kfr->value('donation'), $raDon);     // using the type-your-own-number box

        echo "<TABLE border='0' cellspacing='0' cellpadding='10'><TR>";
        foreach( $raDon as $d ) {
            echo "<TD><INPUT type='radio' name='donation' value='$d'".($kfr->value('donation')==$d ? " checked" : "").">".mbr_dollar($d,$mL->GetLang())."</TD>";
        }
        echo "<TD><INPUT type='radio' name='donation' value='X'".($bDonX ? " checked" : "").">"
            ."<INPUT type='text' size=8 name='donationX' value='".($bDonX ? myNumber($kfr->valueEnt('donation')) : "")."'></TD>";
        echo "</TR></TABLE>";
    }


    echo "<FORM id='mbr_form1' action='${_SERVER['PHP_SELF']}' method='post'>";
    $sess->FormHidden();
    echo "<INPUT type='hidden' name='mbr_state' value='".MBRSTATE_VALIDATE."'>";

    echo "<TABLE border=0 cellspacing=0 cellpadding='20' width='100%'><TR><TD valign='top'>";

    /***** ORDER COLUMN
     */
    echo "<DIV class='mbr_form1col_order'>";


//  echo "<DIV class='form_sect_title'>".$mL->S('secthdr_membership')."</DIV>";

    /***** MEMBERSHIP AND DONATION
     */
    echo "<DIV class='mbr_form_box'>"
        ."<DIV class='mbr_form_boxheader'>".$mL->S('Annual_Membership_and_Donation')."</DIV>"
        ."<DIV class='mbr_form_boxbody'>";

    echo "<TABLE border='0' cellspacing='0' cellpadding='0' style='padding-left:10'>"
        ."<TR><TD style='padding-bottom:4px;'><B>".$mL->S('membership')."</B></TD></TR>"
        ."<TR><TD><INPUT type='radio' name='mbr_type' value='reg1'".($kfr->value('mbr_type')=='reg1' ? " checked" : "")
        .">&nbsp;&nbsp;&nbsp;".$mL->S('One Year Membership form line')."</TD></TR>"
        ."<TR><TD><INPUT type='radio' name='mbr_type' value='' ".(($kfr->Key() && $kfr->value('mbr_type')=='') ? " checked" : "").">&nbsp;&nbsp;&nbsp;"
        .$mL->S('mbr_none')
        ."</TD></TR>"
        ."<TR><TD>&nbsp;</TD></TR>"
        ."<TR><TD><B>".$mL->S('Add a Charitable Donation')."</B></TD></TR>"
        ."</TABLE>";
//  echo "<P><B>".$mL->S('Annual_Membership_and_Donation')."</B></P>";

    mbr1_donRadio( $kfr, $mL );
    echo "</DIV></DIV>\n";    // mbr_form_boxbody, mbr_form_box


    echo "<DIV class='mbr_form_help'>".$mL->S('membership_desc')."</DIV>";
    echo "<DIV class='mbr_form_help'>".$mL->S('donation_desc')."</DIV>";
    echo "<DIV class='mbr_form_help'>".$mL->S('mbr_calendar_year')."</DIV>";
//  echo "<DIV class='mbr_form_help'>".$mL->S('CRA notice')."</DIV>";

/*
 *    function mbr1_mbrcheck( $type, $cost, $mL )
 *    {
 *        global $lang, $kfr;
 *        echo "<TR><TD><INPUT type='radio' name=mbr_type value=$type".($kfr->value('mbr_type')==$type ? " checked" : "").
 *             ">&nbsp;&nbsp;&nbsp;". $mL->S('mbr_'.$type)."</TD><TD>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
 *        if( $lang == "EN" )  echo "$".$cost.".00";
 *        else                 echo $cost.",00 $";
 *        echo "</TD></TR>";
 *    }
 */


//  echo "<TABLE><TR><TD colspan='2'><B>".$mL->S('please_check_one')."</B></TD></TR>";




//  mbr1_mbrcheck( "reg1", 30, $mL );
//  mbr1_mbrcheck( "reg3", 75, $mL );
//    mbr1_mbrcheck( "fixed", 25 );
//  mbr1_mbrcheck( "overseas", 50, $mL );
//  echo "<TR><TD colspan='2'><INPUT type='radio' name='mbr_type' value='' "
//       .(($kfr->Key() && $kfr->value('mbr_type')=='') ? " checked" : "").">&nbsp;&nbsp;&nbsp;"
//       .$mL->S('mbr_none')."</TD></TR>";
//  echo "</TABLE>";

      echo "<BR><BR>";

/*
 *    echo "<DIV class='form_sect_title'>".$mL->S('secthdr_donation')."</DIV>\n";
 *    echo "<DIV class=form_sect_body>";
 *    echo "<DIV class=form_sect_help><P>".$mL->S('donation_desc')."</P></DIV>";
 *    echo "<P>".$mL->S('donation')."&nbsp;&nbsp;&nbsp;"
 *         ."$ <INPUT type='text' name='donation' value='".myNumber($kfr->valueEnt('donation'))."' size='6'></P>";
 *    echo "</DIV>\n";    // form_sect_body
 *
 *    echo "<BR><BR>";
 */



    /**** SPECIAL
     */
    if( 0 && $mL->GetLang() == "EN" ) {

        echo "<DIV class='mbr_form_box'>"
            ."<DIV class='mbr_form_boxheader'>". "25th Anniversary Conference (Toronto)" ."</DIV>"
            ."<DIV class='mbr_form_boxbody'>";

        echo "<H3>Celebrate our 25th Anniversary<BR> on Sunday April 5, 2009<BR>9:00 - 4:00<BR>at the Toronto Botanical Gardens</H3>"
            ."<P><A HREF='http://www.seeds.ca/en.php?n=event_toronto_090405' target='_blank'>Click here for details</A></P>"
            ."<P>Admission includes organic lunch: $35 before March 24, $40 after March 24</P>"
            ."<P>Register now: <INPUT type='text' name='nTorontoReg' value='' size='3'><BR/>"   // would fill value from raSExtra
            ."Please enter the number of people here and type their names in the Notes area below.</P>";

        echo "</DIV></DIV>\n";    // mbr_form_boxbody, mbr_form_box
        echo "<BR><BR>";
    }


    /**** PUBLICATIONS
     */
    echo "<DIV class='mbr_form_box'>"
        ."<DIV class='mbr_form_boxheader'>Publications</DIV>"       // same in EN and FR
        ."<DIV class='mbr_form_boxbody'>";
    echo "<P align='center'>".$mL->S('see_descriptions_here')."</P>";


    function mbr1_pubcheck( $type, $cost, $mL )
    {
        global $lang, $kfr;

        echo "<TR><TD>".$mL->S('vend_'.$type)."</TD>";
        echo "<TD>".$mL->S('pub_'.$type)."</TD>";
        echo "<TD>";
        if( $lang == "EN" ) {
            if( $cost == 5.5 )  echo "$5.50";
            else                echo "$".$cost.".00";
        } else {
            if( $cost == 5.5 )  echo "5,50 $";
            else                echo $cost.",00 $";
        }
        echo "</TD>";
        echo "<TD><INPUT type=text name=pub_$type value='".myNumber($kfr->valueEnt('pub_'.$type))."' size=3></TD></TR>";
        echo "<TR><TD colspan='4'><HR></TD></TR>";
    }


    echo "<TABLE cellspacing='0' cellpadding='0' border='0'><TR>"
         ."<TD width='60'>&nbsp;</TD>"
         ."<TD width='500'><B>".$mL->S('title')."</B></TD>"
         ."<TD width='200'><B>".$mL->S('price')."</B></TD>"
         ."<TD width='60'><B>".$mL->S('quantity')."</B></TD>"
         ."</TR>";
    if( $lang == "EN" ) {
        echo "<TR><TD><A HREF='".SITEROOT."vend/forsale.php#every_seed' target='_blank'><IMG src='".SITEIMG."vend/EverySeed150.png' height='50'></A></TD>"
            ."<TD>Every Seed Tells a Tale</TD>"
            ."<TD>$35 plus shipping</TD>"
            ."<TD><INPUT type=text name='pub_everyseed' value='".@$raSExtra['nPubEverySeed']."' size='3'</TD></TR>" /* .myNumber($kfr->valueEnt('pub_'.$type))."' size=3></TD></TR>" */
            ."<TR><TD colspan='4'><HR/></TD></TR>";
    }
    mbr1_pubcheck( $lang == "EN" ? "ssh_en" : "ssh_fr", 12, $mL );
    mbr1_pubcheck( $lang == "EN" ? "ssh_fr" : "ssh_en", 12, $mL );
    mbr1_pubcheck( "nmd",    6, $mL );
    mbr1_pubcheck( "shc",    8, $mL );
    //mbr1_pubcheck( "rl",     2, $mL );
    echo "</TABLE>";
    echo "<DIV class='mbr_form_help'><P>".$mL->S('contact_for_bulk_rates')."</P></DIV>";
    echo "</DIV></DIV>\n";    // mbr_form_boxbody, mbr_form_box

    echo "<BR><BR>";

    echo $mL->S('form_end_info')."<BR>";

    echo "<DIV class='mbr_form_box'>"
        ."<DIV class='mbr_form_boxheader'>".$mL->S('Misc Payment')."</DIV>"
        ."<DIV class='mbr_form_boxbody'>"
        ."<P>".$mL->S('Misc_payment_instructions')." <INPUT type='text' name='fMisc' value='".@$raSExtra['fMisc']."' size='5'></P>"
	    ."</DIV></DIV>\n"     // mbr_form_boxbody, mbr_form_box
        ."<BR><BR>";

    $kfr->SmartValue( 'pay_type', array( MBR_PT_PAYPAL, MBR_PT_CHEQUE ) );
    echo "<DIV class='mbr_form_box'>"
        ."<DIV class='mbr_form_boxheader'>".$mL->S('Method of Payment')."</DIV>"
        ."<DIV class='mbr_form_boxbody'>";
    echo "<DIV style='padding-left:10px;'><B>".$mL->S("Select a method of payment")."</B><BR><BR>";
    echo "<INPUT type=radio name='pay_type' value='".MBR_PT_PAYPAL."'".($kfr->value('pay_type')==MBR_PT_PAYPAL ? " checked" : "").">"
        .$mL->S("credit_card")."</INPUT>"
        ."<DIV class='mbr_form_help' style='padding-left:6em;'>".$mL->S('credit_card_desc')."</DIV>";
    echo "<INPUT type=radio name='pay_type' value='".MBR_PT_CHEQUE."'".($kfr->value('pay_type')==MBR_PT_CHEQUE ? " checked" : "").">"
        .$mL->S("cheque_mo")."</INPUT>"
        ."<DIV class='mbr_form_help' style='padding-left:6em;'>".$mL->S('cheque_desc')."</DIV>"
        ."</DIV>";
    echo "</DIV></DIV>\n";    // mbr_form_boxbody, mbr_form_box


    echo "<BR><BR>";

    echo "<DIV class='mbr_form_box'>"
        ."<DIV class='mbr_form_boxheader'>".$mL->S('mail_note')."</DIV>"
        ."<DIV class='mbr_form_boxbody' style='text-align:center'>";
    echo "<TEXTAREA name='notes' cols='60' rows='5'>".$kfr->valueEnt('notes')."</TEXTAREA>";
    echo "</DIV></DIV>\n";    // mbr_form_boxbody, mbr_form_box

    echo "<BR><BR>";


    echo "<INPUT type='submit' value='".$mL->S('next_button')." >>'>";

//  echo "<H2 style='color:green'>".$mL->S('form_title')."</H2>";
//  echo "<BR><BR>";
//  echo "<TABLE width='80%' cellpadding='20' align='center' border='1'><TR>";
//  echo "<TD valign='top' width='50%'>".$mL->S('to_pay_online')."</TD>";
//  echo "<TD valign='top' width='50%'>".$mL->S('to_pay_cheque')."</TD>";
//  echo "</TR></TABLE><BR><BR>";

    echo "</DIV>";  // mbr_form1col_order
    echo "</TD>";


    /***** mbr_formcol_contactinfo
     */
    function mbr1_mail_line( $name, $mL, $size = 30 )
    {
        global $kfr;
        return( "<TD>".$mL->S($name)."</TD><TD><INPUT type=text name='$name' value='".$kfr->valueEnt($name)."'". ($size ? " size=$size" : "") ."></TD>" );
    }

    echo "<TD valign='top' width='30%'>";
    echo "<DIV class='mbr_form1col_contactinfo'>";

    global $raMbrFormErrors;

    if( count($raMbrFormErrors) ) {
        echo "<P style='color:red; font-weight:bold;'>";
        foreach( $raMbrFormErrors as $s ) {
            echo $s."<BR><BR>";
        }
        echo "</P>";
    }

    echo "<DIV class='mbr_form_box'>"
        ."<DIV class='mbr_form_boxheader'>".$mL->S('your_address')."</DIV>"
        ."<DIV class='mbr_form_boxbody'>";

    echo "<TABLE><TR>";
    echo "<TR>".mbr1_mail_line( "mail_firstname", $mL )."</TR>";
    echo "<TR>".mbr1_mail_line( "mail_lastname",  $mL )."</TR>";
    echo "<TR>".mbr1_mail_line( "mail_company",   $mL )."</TR>";
    echo "<TR>".mbr1_mail_line( "mail_addr",      $mL )."</TR>";
    echo "<TR>".mbr1_mail_line( "mail_city",      $mL )."</TR>";

    echo "<TR><TD>".$mL->S('mail_prov')."</TD><TD>";
    draw_province( $lang, (!$kfr->IsEmpty('mail_prov') ? ($kfr->value('mail_prov').($kfr->value('mail_country')=='Canada' ? 1 : 2)) : "" ) );
    echo "</TD></TR><TR>";

    echo mbr1_mail_line( "mail_postcode", $mL )."</TR><TR>";
    echo mbr1_mail_line( "mail_phone", $mL )."</TR><TR>";
    echo mbr1_mail_line( "mail_email", $mL )."</TR>";
    echo "</TABLE>";

    echo "<P>".$mL->S('mail_where')."<BR><INPUT type=text name=mail_where value='".$kfr->valueEnt('mail_where')."' size=40></P>";

    echo "<P>".$mL->S('ebull_desc')."</P>";
    echo "<P style='margin-left:1em'>";
    echo "<INPUT type=radio name=mail_eBull value=1".( $kfr->value('mail_eBull') ? " CHECKED" : "").">&nbsp;".$mL->S('send_ebull')."<BR>";
    echo "<INPUT type=radio name=mail_eBull value=0".(!$kfr->value('mail_eBull') ? " CHECKED" : "").">&nbsp;".$mL->S('no_thanks')."</P>";

    echo "</DIV></DIV>";    // mbr_form_boxbody, mbr_form_box


    echo "<DIV class='mbr_form_help'>".$mL->S('overseas_instructions')."</DIV>";
    echo "<DIV class='mbr_form_help' style='color:green'>".$mL->S('privacy_policy')."</DIV>";

    echo "</TD></TR></TABLE>";
    echo "</FORM>";
}

?>
