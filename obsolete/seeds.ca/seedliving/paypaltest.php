<?php

include_once( "lib/paypalplatform_dev.php" );


$sPP_Seller_Email = "sandbox_seller2@seeds.ca";

$sPP_Buyer_Email = "sandbox_buyer@seeds.ca";

$sPP_App_Email = "sandbox_app@seeds.ca";
$sPP_App_User  = "sandbox_app_api1.seeds.ca";
$sPP_App_Pwd   = "1368634570";
$sPP_App_Sig   = "AyiVaks0elqn2FrChgZT8yo3b37jAlEX80RJFmAq4vAAqq9MsqpHxtD3";


echo $API_AppID;


$sResult = sendPost( "https://api-3t.sandbox.paypal.com/nvp",
                array( "USER"      => $sPP_App_User,
                       "PWD"       => $sPP_App_Pwd,
                       "SIGNATURE" => $sPP_App_Sig,
                       "METHOD"    => "SetExpressCheckout",
                       "VERSION"   => 98,
                       "PAYMENTREQUEST_0_AMT" => 10, 
                       "PAYMENTREQUEST_0_CURRENCYCODE" => "USD",
                       "PAYMENTREQUEST_0_PAYMENTACTION" => "SALE",
                       "cancelUrl"    => "http://www.example.com/cancel.html",
                       "returnUrl"    => "http://www.example.com/success.html"                      
) );
echo showResult( "SetExpressCheckout", $sResult );                      




function sendPost( $url, $raPost )
{
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url );
	curl_setopt($ch, CURLOPT_VERBOSE, 1);
	
	//turning off the server and peer verification(TrustManager Concept).
	curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
	curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
	
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_POST, 1);
	
	$sPost = "";
	
	foreach( $raPost as $k => $v ) { $sPost .= $k.'='.urlencode($v).'&'; }
	rtrim( $sPost, '&' );
	
	curl_setopt($ch,CURLOPT_POST, count($raPost) );
	curl_setopt($ch,CURLOPT_POSTFIELDS, $sPost );
	
	//execute post
	$result = curl_exec($ch);
	
	//close connection
	curl_close($ch);
	
	return( $result );
}

function showResult( $sTitle, $sResult )
{
    $s = "<div style='border:1px solid #555'><h3>$sTitle</h3>";
    
    $raRes = explode( '&', $sResult );
    foreach( $raRes as $p ) {
        list($k,$v) = explode( '=', $p );
        $s .= "<br/>$k = ".urldecode($v);
    }
    $s .= "</div>";
    
    return( $s );
}

?>