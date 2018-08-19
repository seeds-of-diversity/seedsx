<?php
define( 'SITEROOT', "../../");
include( SITEROOT."site.php" );
include( "lib.php" );

list($kfdb) = SiteStart();

$oBucket = new SEEDMetaTable_StringBucket( $kfdb );
$t = $oBucket->GetStr( "PoolController", "t" );

$im = makeTextBox( $t, 150 );

header("Content-type:image/png");
imagepng($im);
imagedestroy($im);

?>
