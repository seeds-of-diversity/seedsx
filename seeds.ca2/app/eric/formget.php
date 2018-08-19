<?php
define( 'SITEROOT', "../../");
include( SITEROOT."site.php" );

list($kfdb) = SiteStart();

$oBucket = new SEEDMetaTable_StringBucket( $kfdb );
$oTable = new SEEDMetaTable_TablesLite( $kfdb );
if( ($kTable = $oTable->OpenTable( "EricForm" )) &&
    ($raRows = $oTable->GetRows( $kTable )) )
{
    $raK = array();
    // Get a complete union of keys for all values in all rows. Some rows might have different key sets than others.
    foreach( $raRows as $kTLR => $ra ) {
        $raK = array_unique(array_merge($raK,array_keys($ra['vals'])));
    }

    $s = "<table border='1' cellpadding='2' style='border-collapse:collapse'>"
        ."<tr><th>#</th><th>".implode( "</th><th>", $raK )."</th></tr>";
    $n = 1;
    foreach( $raRows as $kTLR => $ra ) {
        $s .= "<tr><td>".($n++)."</td>";
        foreach( $raK as $k ) {
            $s .= "<td>".@$ra['vals'][$k]."&nbsp;</td>";
        }
        $s .= "</tr>";
    }
    $s .= "</table>";

    echo $s;
}

?>
