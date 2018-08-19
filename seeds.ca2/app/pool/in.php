<?php
define( 'SITEROOT', "../../");
include( SITEROOT."site.php" );

list($kfdb) = SiteStart();

$t = @$_REQUEST['t'] or die;

$oBucket = new SEEDMetaTable_StringBucket( $kfdb );
$oBucket->PutStr( "PoolController", "t", $t );
$oBucket->PutStr( "Dunbar", "myip", $_SERVER['REMOTE_ADDR'] );

// check the time in EricPoolScheduler and change the status etc


//$oBucket->PutStr( "PoolController", "t-".time(), $t );

echo $t;

?>
