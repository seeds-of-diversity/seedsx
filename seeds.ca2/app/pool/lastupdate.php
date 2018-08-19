<?php
define( 'SITEROOT', "../../");
include( SITEROOT."site.php" );
include( "lib.php" );


list($kfdb) = SiteStart();

$oBucket = new SEEDMetaTable_StringBucket( $kfdb );
$t = $oBucket->GetStr( "PoolController", "t" );

$t = $kfdb->Query1( "SELECT _updated FROM seeds.SEEDMetaTable_StringBucket WHERE ns='PoolController' AND k='t'" );

$t = strtotime($t);
$t = $t - 14400;  // four hours time zone difference

$t = date( "Y-M-d g:i a", $t );

$im = makeTextBox( $t, 14 );  // fontsize 14

header("Content-type:image/png");
imagepng($im);
imagedestroy($im);

?>
