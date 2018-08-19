<?php
define( 'SITEROOT', "../../");
include( SITEROOT."site.php" );
include( "lib.php" );

list($kfdb) = SiteStart();

$oBucket = new SEEDMetaTable_StringBucket( $kfdb );
$t = $oBucket->GetStr( "PoolController", "sReason" );

if( $t == 'no-reason' )  $t = "";

header("Content-type:image/png");

$im = makeTextBox( $t, 50 );

imagepng($im);
imagedestroy($im);

?>
