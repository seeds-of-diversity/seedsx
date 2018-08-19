<?

// pay_total is a decimal(5,2), but the kfrdef says it's an int.  Need an N type, that does floatval() in KFR._getValFromRA

// If you are paying by credit card, please type the exact name that appears on the credit card here.  This helps our staff to match your order with PayPal's transaction records.
// "name on credit card" helps Judy to identify PayPal transactions that use a different name
//   - could solve this if the PayPal transaction had our order number in it, or our list had a PayPal id
// checkbox for renewal to help Judy - problem: what if people don't check it and Judy just adds them with a new mbr_id
// xls download from report
// french
// send a confirmation email when order is placed if !empty(email)
// send a confirmation email when order is filled if !empty(email)

// donation expects intval, not decimal - so for now we truncate it to the dollar

// enforce a page-order by looking at the referrer?  If we jump to mbr3, not from mbr2, abort to a friendly "do you need help" page.

// do we enforce US dollars for US/overseas members?  Do this in PayPal, remind on summary and cheque instructions.  State
// this in a div beside the country on mbr1


function mbr_Validate( $lang, &$kfr, $sess, $mL )
/************************************************
    Process POST parms, normalize values, store in kfr.
    If not valid, write error message, return false.
 */
{
    $raErrors = array();
    $raSExtra = array();

    $total = 0;

    $raMailKeys = array( array( "firstname" ),
                         array( "lastname" ),
                         array( "company" ),
                         array( "addr" ),
                         array( "city" ),
                         array( "prov" ),
                         array( "postcode" ),
                     //  array( "country" ),
                         array( "phone" ),
                         array( "email" ),
                         array( "eBull" ),
                         array( "where" ) );


    /*  Mailing Address
     */
    $raMail = array();
    foreach( $raMailKeys as $k ) {
        $raMail[$k[0]] = SEEDSafeGPC_GetStrPlain( "mail_".$k[0] );
    }

    /* Normalize input fields
     */
    if( strlen($raMail['prov']) == 3 ) {
        $raMail['country'] = (substr( $raMail['prov'], 2, 1 ) == '2' ? "USA" : "Canada" );
        $raMail['prov']    = substr( $raMail['prov'], 0, 2 );
    } else {
        $raMail['prov'] = $raMail['country'] = "";
    }

    /* Contact info and preferences
     */
    $kfr->SetValue( 'mail_firstname', $raMail['firstname'] );
    $kfr->SetValue( 'mail_lastname',  $raMail['lastname'] );
    $kfr->SetValue( 'mail_company',   $raMail['company'] );
    $kfr->SetValue( 'mail_addr',      $raMail['addr'] );
    $kfr->SetValue( 'mail_city',      $raMail['city'] );
    $kfr->SetValue( 'mail_prov',      $raMail['prov'] );
    $kfr->SetValue( 'mail_postcode',  $raMail['postcode'] );
    $kfr->SetValue( 'mail_country',   $raMail['country'] );
    $kfr->SetValue( 'mail_phone',     $raMail['phone'] );
    $kfr->SetValue( 'mail_email',     $raMail['email'] );
    $kfr->SetValue( 'mail_lang',      ($lang == "FR" ? 1 : 0) );
    $kfr->SetValue( 'mail_eBull',     (intval($raMail['eBull']) ? 1 : 0) );
    $kfr->SetValue( 'mail_where',     $raMail['where'] );


    /* Membership
     */
    global $mbr_MbrTypes;
    $raMbrType = @$mbr_MbrTypes[ @$_REQUEST['mbr_type'] ];
    if( is_array($raMbrType) ) {
        $total += $raMbrType['n'];

        $kfr->SetValue( 'mbr_type', $_REQUEST['mbr_type'] );
    } else {
        $kfr->SetValue( 'mbr_type', '' );
    }

    /* Donation
     *
     * 'donation' is the value of the radio button, which is a number for preset choices, and 'X' for the type-your-own-number choice
     * 'donationX' is the type-your-own-number
     */
    $donation = @$_REQUEST['donation'];
    $donation = floatval($donation == 'X' ? @$_REQUEST['donationX'] : $donation);
    $total += $donation;
    $kfr->SetValue( 'donation', $donation );    // allows reset to 0 if user resets donation to blank

    /* Publications
     */
    global $mbr_Pubs;
    foreach( $mbr_Pubs as $k ) {
        $n = intval( @$_REQUEST['pub_'.$k[0]] );
    //  if( $n ) {          must allow $n to be zero so user can reset
            $total += ($n * $k[2]);
            $kfr->SetValue( "pub_".$k[0], $n );
    //  }
    }

    if( ($nPubEverySeed = SEEDSafeGPC_GetInt('pub_everyseed')) ) {
        $total += $nPubEverySeed * 35;
        $raSExtra['nPubEverySeed'] = $nPubEverySeed;

        if( $nPubEverySeed >= 5 ) {
            $shipping = 0;
        } else {
        	$ra = array( 0, 10, 12, 15, 18 );
            $shipping = $ra[$nPubEverySeed];
        }
        $total += $shipping;
        $raSExtra['nPubEverySeed_shipping'] = $shipping;
    }

    /* Misc
     */
    $fMisc = floatval(@$_REQUEST['fMisc']);
    if( $fMisc ) {
        $total += $fMisc;
        $raSExtra['fMisc'] = $fMisc;
    }
//echo "Total is $total";

    /* Special
     */
//    $nTorontoReg = intval(@$_REQUEST['nTorontoReg']);
//    if( $nTorontoReg ) {
//        $total += $nTorontoReg * 35;
//        $raSExtra['nTorontoReg'] = $nTorontoReg";
//    }


    /* Save the sExtra values.  If none, save it anyway to overwrite (remove) any previous values
     */
    $kfr->SetValue( 'sExtra', SEEDStd_ParmsRA2URL( $raSExtra ) );


    /* Total Payment
     */
    $kfr->SetValue( "pay_total", $total );

    /* Payment Method
     */
    $pt = SEEDSafeGPC_Smart( "pay_type", array(MBR_PT_PAYPAL, MBR_PT_CHEQUE) );
    $kfr->SetValue( "pay_type", $pt );

    /* Notes
     */
    $notes = SEEDSafeGPC_GetStrPlain( "notes" );
    $kfr->SetValue( "notes", $notes );

    /* Store order
     */
    $kfr->PutDBRow(true) or mbr_dberr_die();


    /* Validate input fields
     *
     * Do this after storing the fields so the current fields will be populated in the form.
     */
    // REQUIRE (firstname | lastname | company)
    // REQUIRE (addr & city & prov & postcode & country)

    if( $kfr->IsEmpty('mail_firstname') && $kfr->IsEmpty('mail_lastname') && $kfr->IsEmpty('mail_company') ) {
        $raErrors[] = $mL->S('name_or_company_needed');
    }
    if( $kfr->IsEmpty('mail_addr') || $kfr->IsEmpty('mail_city') ||
        $kfr->IsEmpty('mail_prov') || $kfr->IsEmpty('mail_postcode') )
    {
        $raErrors[] = $mL->S('address_needed');
    }
/*
 *    if( !$bValid ) {
 *        echo "<P><A HREF='javascript:history.back();'>".$mbr_Text['back_button'][$lang]."</A>";
 *    //  echo "<P><A HREF='mbr1.php?".SID."'>Back</A>";  doesn't preserve the posted fields
 *    //  reload_mbr1( $raMail );
 *        exit;
 *    }
 */

    return( $raErrors );
}


function mbr_Confirm( $lang, $kfr, $sess, $mL )
/**********************************************
 */
{
    function mbr2_button( $sess, $mL, $mlButtonText, $pt )
    {
        echo "<FORM action='${_SERVER['PHP_SELF']}' method='post'>";
        echo $sess->FormHidden();
        echo "<INPUT type='hidden' name='mbr_state' value='".MBRSTATE_PAY."'>";
        echo "<INPUT type='hidden' name='pay_type' value='$pt'>";
        echo "<INPUT type='submit' value='".$mL->S($mlButtonText)." >>'>";
        echo "</FORM>";
    }

    /* Draw screen
     */
    echo "<H2>".$mL->S('confirm_order')."</H2>";
    echo "<BR/>";

    mbr_order_summary( $kfr, $mL );

    echo "<TABLE cellspacing=50><TR><TD valign='top' style='font-size:9pt;'>".$mL->S('if_order_not_correct')."<BR/><BR/>";
    echo "<FORM action='${_SERVER['PHP_SELF']}' method='post'>";
    echo $sess->FormHidden();
    echo "<INPUT type='hidden' name='mbr_state' value='".MBRSTATE_FORM."'>";
    echo "<INPUT type='submit' value='<< ".$mL->S('change')."'></FORM></TD>";

    echo "<TD valign='top' style='font-size:9pt;'>";

    if( $kfr->value("pay_type") == MBR_PT_PAYPAL ) {
        echo $mL->S('click_here_paypal')."<BR/><BR/>";
        mbr2_button( $sess, $mL, "Pay Now", MBR_PT_PAYPAL );

        echo "<BR/><BR/>";
        echo $mL->S('click_here_cheque')."<BR/><BR/>";
        mbr2_button( $sess, $mL, "pay_by_cheque_instead", MBR_PT_CHEQUE );
    } else {
        echo $mL->S('click_here_cheque')."<BR/><BR/>";
        mbr2_button( $sess, $mL, "Pay Now", MBR_PT_PAYPAL );

        echo "<BR/><BR/>";
        echo $mL->S('click_here_paypal')."<BR/><BR/>";
        mbr2_button( $sess, $mL, "pay_by_credit_card_instead", MBR_PT_CHEQUE );
    }
    echo "</TD></TR></TABLE>";
}

?>
