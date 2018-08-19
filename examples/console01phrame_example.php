<?php
if( !defined("SITEROOT") )  define("SITEROOT", "../office/");
include_once( SITEROOT."site.php" );

include_once( SEEDCOMMON."console/console01.php" );

$kfdb = SiteKFDB();
$sess = new SEEDSession();

$oC = new Console01( $kfdb, $sess, array() );   // kfdb should be phased out - then this example won't need to create a db connection just to draw stuff in boxes

$raParms = array( 'v' => array(
    array( 'id'=>'top1', 'kContent'=>'A', 'style'=>'Gray' ),
    array( 'id'=>'top2', 'kContent'=>'B', 'style'=>'Gray' ),
    array( 'h' => array(
        array( 'v' => array( array( 'id'=>'left1', 'kContent'=>'C' ),
                             array( 'id'=>'left2', 'kContent'=>'D' ),
                           ),
               'width'=>'25%' ),
        array( 'v' => array( array( 'id'=>'middle1', 'kContent'=>'E' ),
                             array( 'id'=>'middle2', 'kContent'=>'F' ),
                           ) ),
        array( 'v' => array( array( 'id'=>'right1', 'kContent'=>'G' ),
                             array( 'id'=>'right2', 'kContent'=>'H' ),
                           ),
               'width'=>'25%' ),
    ) ),
    array( 'id'=>'bottom1', 'kContent'=>'I' ),
    array( 'id'=>'bottom2', 'kContent'=>'J' ),
) );


$raContent = array(
    'A' => "The first top header",
    'B' => "The second top header",
    'C' => "The first left sidebar",
    'D' => "The second left sidebar",
    'E' => "The first middle section",
    'F' => "The second middle section",
    'G' => "The first right sidebar",
    'H' => "The second right sidebar",
    'I' => "The first footer",
    'J' => "The first footer",
);


$s = $oC->DrawPhrameSet( $raContent, $raParms );

echo $oC->DrawConsole( $s );   // this makes the <head> with the needed styles - can't just echo DrawPhrameSet.


?>