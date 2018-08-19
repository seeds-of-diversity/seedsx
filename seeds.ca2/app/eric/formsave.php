<?php
define( 'SITEROOT', "../../");
include( SITEROOT."site.php" );

list($kfdb) = SiteStart();

$oBucket = new SEEDMetaTable_StringBucket( $kfdb );
$oTable = new SEEDMetaTable_TablesLite( $kfdb );

$n = intval($oBucket->GetStr( "EricForm", "N" ));
$n++;
$oBucket->PutStr( "EricForm", "N", $n );    // record the highest numbered row stored in TablesLite

$sTH = $sTD = "";
$row = array();
foreach( $_REQUEST as $k=>$v ) {
    if( $k == '_email' )  continue;
    $v = SEEDSafeGPC_MagicStripSlashes($v);

    $row[$k] = $v;
    //$oBucket->PutStr( "EricForm", "$n--$k", $v );
    $sTH .= "<th>$k</th>";
    $sTD .= "<td>$v</td>";
}
if( count($row) > 0 ) {
    if( ($kTable = $oTable->OpenTable( "EricForm" )) ) {
        $oTable->PutRow( $kTable, 0, $row );
    }
}
$s = "<table border='1'><tr>$sTH</tr><tr>$sTD</tr></table>";

if( @$_REQUEST['_email'] ) {
    MailFromOffice( $_REQUEST['_email'], "Your form has been posted", "", $s, array( 'from'=>"Your Form <formsave@seeds.ca>" ) );

}

$v = @$_REQUEST['type'];
echo "<h3>Thank You!</h3><p>Your custom $v order has been submitted.</p>";

?>
