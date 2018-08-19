<?
/*
VISA, MasterCard or Paypal:
(You do not need a Paypal account to pay by VISA or MasterCard.
On the next screen, just click "you do not currently have a PayPal account".)

https://www.paypal.com/en_US/i/btn/x-click-butcc.gif
*/

function mbr_Pay( $lang, $kfr, $sess, $mL )
/******************************************
 */
{
    global $mbr_Pubs;

    /* Set pay_status to CONFIRM, if it isn't already past that point.
     * Allow change of pay_type during the NEW or CONFIRM stage.
     */
    if( $kfr->value('pay_status') == MBR_PS_NEW ) {
        $kfr->SetValue( 'pay_status', MBR_PS_CONFIRMED );
    }
    if( $kfr->value('pay_status') == MBR_PS_CONFIRMED ) {
        $payType = intval(@$_POST['pay_type']);
        if( $payType != MBR_PT_PAYPAL )  $payType = MBR_PT_CHEQUE;
        $kfr->SetValue( 'pay_type', $payType );

        /* Update the row.  This is executed when the prior pay_status was NEW or CONFIRMED.
         */
        $kfr->PutDBRow() or mbr_dberr_die();
    }


    if( $kfr->value('pay_status') == MBR_PS_PAID ) {
        echo "<H2>".$mL->S('Order_paid')." - ".$mL->S('Thankyou')."!</H2>";
        echo $mL->S('assistance');
    } else if( $kfr->value('pay_status') == MBR_PS_FILLED ) {
        echo "<H2>".$mL->S('Order_filled')." - ".$mL->S('Thankyou')."!</H2>";
        echo $mL->S('assistance');
    } else if( $kfr->value('pay_status') == MBR_PS_CANCELLED ) {
        echo "<H2>".$mL->S('Order_cancelled')."</H2>";
        echo $mL->S('assistance');

    // else MBR_PS_CONFIRMED
    } else if( $kfr->value('pay_type') == MBR_PT_CHEQUE ) {
        echo "<H2>".$mL->S('Order_confirmed')." - ".$mL->S('Thankyou')."!</H2>";
        echo $mL->S('cheque_instructions');

        echo "<FORM action='${_SERVER['PHP_SELF']}' method='post'>";
        echo $sess->FormHidden();
        echo "<INPUT type='hidden' name='mbr_state' value='".MBRSTATE_PAY."'>";
        echo "<INPUT type='hidden' name='pay_type' value='".MBR_PT_PAYPAL."'>";
        echo "<INPUT type='submit' value='".$mL->S('pay_by_credit_card_instead')."'>";
        echo "</FORM>";
    } else {
        echo "<H2>".$mL->S('Pay_by_credit')."</H2>";
        echo $mL->S('paypal_instructions1');

        $raSExtra = SEEDStd_ParmsURL2RA( $kfr->value('sExtra') );

        $paypalDesc = "";
        if( !$kfr->IsEmpty('mbr_type') )  $paypalDesc .= $mL->S('membership')." ";
        if( $kfr->value('donation') )   $paypalDesc .= $mL->S('donation')." ";
        $iPubs = 0;
        foreach( $mbr_Pubs as $k ) {
            $iPubs += $kfr->value('pub_'.$k[0]);
        }
        $iPubs += @$raSExtra['nPubEverySeed'];
        if( $iPubs == 1 )  $paypalDesc .= "Publication ";
        if( $iPubs > 1 )   $paypalDesc .= "Publications ";

        if( @$raSExtra['fMisc'] || empty($paypalDesc) )  $paypalDesc .= "Misc ";

        echo "<P>";

        $raVars = array();
        if( $lang == "EN" ) {
        } else {
            $raVars["lc"] = "FR";   // a horrible kluge
        }

        _mbr_PP_form( $kfr, $mL, $paypalDesc, $raVars );

        echo "</P>";

        echo $mL->S('paypal_instructions2');

        echo "<FORM action='${_SERVER['PHP_SELF']}' method='post'>"
             .$sess->FormHidden()
             ."<INPUT type='hidden' name='mbr_state' value='".MBRSTATE_PAY."'>"
             ."<INPUT type='hidden' name='pay_type' value='".MBR_PT_CHEQUE."'>"
             ."<INPUT type='submit' value='".$mL->S('pay_by_cheque_instead')."'>"
             ."</FORM>";
    }


    mbr_order_summary( $kfr, $mL );

    echo "<BR><P>"
//       ."<A HREF='".SITEROOT.($lang=="EN"?"en.php":"fr.php")."'>".$mL->S('to_home')."</A>"
//       .SEEDStd_StrNBSP("", 10)
         ."<FORM action='${_SERVER['PHP_SELF']}' method='post'>"
         ."<INPUT type='hidden' name='mbr_state' value='".MBRSTATE_START."'>"
         .$sess->FormHidden()
         ."<INPUT type='submit' value='".$mL->S('Start_a_New_Order')."'>"
         ."</FORM></P>";
}


function _mbr_PP_form( $kfr, $mL, $itemDesc, $raVars )
/*****************************************************
 */
{
    $rowid = $kfr->Key();
    $amount = $kfr->value('pay_total');
    $currency_code = ($kfr->value('mail_country')=='Canada'? "CAD" : "USD");
    $submitButtonAlt = $mL->S('secure_payment_paypal');

    echo "\n<FORM action='https://www.paypal.com/cgi-bin/webscr' method='post'>";
    echo "\n<INPUT type='hidden' name='cmd'      value='_xclick'>";                         // Buy Now button  - see value='_donations' for donation button
    echo "\n<INPUT type='hidden' name='business' value='mail@seeds.ca'>";
    echo "\n<INPUT type='hidden' name='quantity' value='1'>";                               // multiplies the 'amount' to make a bulk payment!  Must be 1.


    // PayPal "sends data to you" in the charset specified here. It also seems to expect data to be sent to it in this charset.
    // We used to set this to UTF-8, and accented characters in our posted parms (like Adhésion in item_name) caused immediate PayPal fatal error
    // about character encoding.
//  echo "\n<INPUT type='hidden' name='charset' value='utf-8'>";

    echo "\n<INPUT type='hidden' name='no_shipping' value='1'>";            // the buyer is not prompted for a shipping address (in addition to billing address)
    echo "\n<INPUT type='hidden' name='no_note' value='1'>";                // the buyer is not prompted to enter a note

    // Return here on success/cancel.  These can also be set as an option in the PayPal account.
    // The advantage here is that the URLs can contain parms specific to this transaction - like a session id.
    echo "\n<INPUT type='hidden' name='return'        value='http://www.seeds.ca/mbr/mbr_PPsuccess.php'>";
    echo "\n<INPUT type='hidden' name='cancel_return' value='http://www.seeds.ca/mbr/mbr_PPcancel.php'>";
    echo "\n<INPUT type='hidden' name='rm'            value='2'>";                      // Return Method: how parms sent to return page: 1:GET, 2:POST
//  echo "\n<INPUT type='hidden' name='cbt'           value=''>";                       // The text on the Continue button on completion (e.g. Go to Seeds of Diversity)

    // Send Instant Payment Notification here
    echo "\n<INPUT type='hidden' name='notify_url'    value='http://www.seeds.ca/mbr/mbr_PPipn.php'>";

    // Decoration
//  echo "\n<INPUT type='hidden' name='image_url'              value='http://www.seeds.ca/img/logo/logo02_EN'>";  // logo image max 150x50
//  echo "\n<INPUT type='hidden' name='cpp_header_image'       value='http://www.seeds.ca/img/logo/logo02_EN'>";  // header image max 750x90
//  echo "\n<INPUT type='hidden' name='cpp_headerback_color'   value='FFFFFF'>";                                  // header background colour
//  echo "\n<INPUT type='hidden' name='cpp_headerborder_color' value='555555'>";                                  // header border colour
//  echo "\n<INPUT type='hidden' name='cpp_payflow_color'      value='AAAAAA'>";                                  // page background colour below header


    // passthrough variables: PayPal doesn't use these, but transmits them to IPN for our use.
    // Though PayPal docs say that they are neither used nor recorded by PayPal, we've seen PayPal complain when an invoice number duplicates
    // one that was used previously (e.g. development platform reused a _key that was already used on production platform. Not a conflict in our database,
    // only a conflict on PayPal's records).
    echo "\n<INPUT type='hidden' name='item_number' value='$rowid'>";
    echo "\n<INPUT type='hidden' name='invoice'     value='$rowid'>";
//  echo "\n<INPUT type='hidden' name='custom' value='merchant_custom_value'>";


    // Transaction info
    echo "\n<INPUT type='hidden' name='item_name'     value='".htmlspecialchars($itemDesc,ENT_QUOTES)."'>";
    echo "\n<INPUT type='hidden' name='amount'        value='$amount'>";
    echo "\n<INPUT type='hidden' name='currency_code' value='$currency_code'>";


    // Prepopulate the billing address
    echo "\n<INPUT type='hidden' name='first_name' value='".$kfr->value('mail_firstname')."'>";
    echo "\n<INPUT type='hidden' name='last_name'  value='".$kfr->value('mail_lastname')."'>";
    echo "\n<INPUT type='hidden' name='address1'   value='".$kfr->value('mail_addr')."'>";
    echo "\n<INPUT type='hidden' name='city'       value='".$kfr->value('mail_city')."'>";
    echo "\n<INPUT type='hidden' name='state'      value='".$kfr->value('mail_prov')."'>";
    echo "\n<INPUT type='hidden' name='zip'        value='".$kfr->value('mail_postcode')."'>";
    echo "\n<INPUT type='hidden' name='country'    value='".($kfr->value('mail_country')=='Canada'? "CA" : "US")."'>";
//  echo "\n<INPUT type='hidden' name='night_phone_a' value='519'>";
//  echo "\n<INPUT type='hidden' name='night_phone_b' value='886'>";
//  echo "\n<INPUT type='hidden' name='night_phone_c' value='7542'>";

/*
    <input type="hidden" name="tax" value="0">
    <input type="hidden" name="shipping" value="5.00">
    <input type="hidden" name="lc" value="US">
    <input type="hidden" name="bn" value="PP-DonationsBF">
    <input type="image" src="https://www.paypal.com/en_US/i/btn/x-click-but04.gif" border="0" name="submit" alt="Make payments with PayPal - it's fast, free and secure!">
    <img alt="" border="0" src="https://www.paypal.com/de_DE/i/scr/pixel.gif" width="1" height="1">
*/

// Watch for values with quotes in them, especially when pre-populating fields

//  echo "\n<INPUT type='hidden' name='image_url' value='http://www.seeds.ca/img/logo_EN.gif'>";

// Uncomment this to try it - causes form to be Canadian
//  echo "\n<INPUT type='hidden' name='lc' value='CA'>";  // I think it's a language code - should be US or FR

// Canada/French
// see http://www.paypaldev.org/topic.asp?TOPIC_ID=11777
//
// country=CA forces the language to English because the Canadian server is EN only
// country=CA,lc=FR gives English because FR is not available on the Canadian server
// lc=FR defaults to the France server, so your default country is France
// there is no workaround until PayPal's Canadian server provides French
//
// What we do:
// If lang=EN, set country=CA or US.    This gives the right country dialog, but always in English.
// If lang=FR, set lc=FR.               This gives France as the default country, but at least the page is in French.



// What's this? From PP button generate <input type="hidden" name="bn" value="PP-BuyNowBF">


    /* Modify these PayPal parameters
     */

    echo "\n<INPUT type='image' src='https://www.paypal.com/en_US/i/btn/x-click-but6.gif' name='submit' alt='$submitButtonAlt'>";

    /* parms from caller
     */
    foreach( $raVars as $k => $v ) {
        echo "\n<INPUT type='hidden' name='$k' value='".htmlspecialchars($v,ENT_QUOTES)."'>";
    }

    echo "</FORM>";
}

?>
