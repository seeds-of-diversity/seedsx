<?php
define( 'SITEROOT', "../../");
include( SITEROOT."site.php" );

list($kfdb) = SiteStart();

$oBucket = new SEEDMetaTable_StringBucket( $kfdb );
$t = $oBucket->GetStr( "Dunbar", "myip" );


echo "<h2>$t</h2>";

?>
