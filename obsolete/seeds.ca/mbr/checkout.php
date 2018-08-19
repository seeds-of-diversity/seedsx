<?php

// TODO: This is a renewal.  If you know your member number, please enter it here to help us find your record.

// mbr_PPsuccess: say "thanks for your order, you should receive a confirmation email from paypal." Show the summary.
// mbr_PPcancel:  say "Your order has been cancelled."  Show the order number, cancelled.
// mbr_PPipn:     tell me more

include_once( "../site.php" );
include_once( "seedCheckout.php" );
include_once( PAGE1_TEMPLATE );


list($kfdb, $sess) = SiteStartSession();

site_define_lang(@$_SESSION['lang']);


//var_dump($_REQUEST);
//echo "<BR/><BR/>";
//var_dump($_SESSION);

$sCheckout = DrawMbr( NULL, SITE_LANG, false );

Page1( array( "lang"      => SITE_LANG,
              "title"     => "Checkout",                    //$mL->S('form_title'),
              "tabname"   => "MBR",
));




function Page1Body()
{
    global $sCheckout;    // render DrawMbr before Page1 because it has a side-effect of
                          // sometimes logging in the user, which sets headers (a cookie) via SEEDSession
    echo $sCheckout;
    //echo DrawMbr( NULL, SITE_LANG );

//    global $kfdb, $sess;

//    $oMbrOC = new SoDMbrOrderCheckout( $kfdb, $sess, SITE_LANG );
//    echo $oMbrOC->Checkout();
}

?>
