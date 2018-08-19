<?php
// the store saves state and shows a thank you screen with the ticket, if IPN was successful

header( "Location: /store" );
exit;

include_once( "../site.php" );
include_once( SEEDCOMMON."siteStart.php" );
include_once( STDINC."KeyFrame/KFRelation.php" );
include_once( STDINC."SEEDForm.php" );
include_once( STDINC."SEEDLocal.php" );
include_once( PAGE1_TEMPLATE );
include_once( "_mbr.php" );

$lang = "EN";   // get the correct language from the session
$mL = new SEEDLocal( $mbr_Text, $lang );


Page1( array( "lang"      => $lang,
              "title"     => $mL->S('form_title'),
              "tabname"   => "MBR",
//            "box1title" => "What's New",
//            "box1fn"    => "box1fn",
//            "box2title" => "Contact Us",
//            "box2fn"    => "box2fn",
            ) );


function Page1Body()
{
    echo "<H2>Thankyou for your order</H2>";
//  echo "<P align=center><A HREF='".SITEROOT."en.php'>Back to home page.</A></P>";
}


// print_r($_REQUEST);
/* [txn_type] => web_accept
   [payment_date] => 10:33:19 Jan 16, 2006 PST
   [last_name] => Wildfong
   [receipt_id] => 2398-2384-8571-8532
   [item_name] => Publications
   [payment_gross] =>
   [mc_currency] => CAD
   [business] => mail@seeds.ca
   [payment_type] => instant
   [verify_sign] => Aqkjta0pKgY30F4bY8yEGHVO4gyWAFi2yBdVTEDNRU.1mEKT4srsM5gq
   [payer_status] => unverified [tax] => 0.00 [payer_email] => bob@seeds.ca
   [txn_id] => 9TV293359L5205411
   [quantity] => 1
   [receiver_email] => mail@seeds.ca
   [first_name] => Robert
   [invoice] => 251
   [payer_id] => NRX3SY7XKWAWL
   [receiver_id] => S3NMZ3H5BYNYW
   [item_number] => 251
   [payment_status] => Completed
   [payment_fee] =>
   [mc_fee] => 4.47
   [shipping] => 0.00
   [mc_gross] => 135.00
   [custom] =>
   [charset] => windows-1252
   [notify_version] => 1.9
*/

?>
