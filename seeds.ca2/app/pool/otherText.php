<?php
define( 'SITEROOT', "../../");
include( SITEROOT."site.php" );

header( "Access-Control-Allow-Origin: *" );

list($kfdb) = SiteStart();

$oBucket = new SEEDMetaTable_StringBucket( $kfdb );
$t = $oBucket->GetStr( "PoolController", "sOther" );

echo $t;

?>

