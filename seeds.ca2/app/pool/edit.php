<?php
define( 'SITEROOT', "../../");
include( SITEROOT."site.php" );
include( "lib.php" );

echo "<html><head><link rel='shortcut icon' href='//seeds.ca/app/pool/favicon.png'></head>";






list($kfdb) = SiteStart();

$oBucket = new SEEDMetaTable_StringBucket( $kfdb );



/* Process any form parameters
*/

$raParms = array();

if( @$_REQUEST['Reason'] == 'Super Chlorinated' ) {
    // schedule the current status and reason to resume in 12 hours
    $oldStatus = $oBucket->GetStr( "PoolController", "sStatus" );
    $oldReason = $oBucket->GetStr( "PoolController", "sReason" );
    $iTime = time() + 12 * 3600;

    $oTable = new SEEDMetaTable_TablesLite( $kfdb );
    $kTable = $oTable->OpenTable( "EricPoolScheduler" );

    $oTable->PutRow( $kTable, 0, array( 'iTimeUTC' => $iTime,
                                        'status'=>$oldStatus,
                                        'reason'=>$oldReason,
                                        'done' => 0 ) );

    // changing status and reason for super chlorination
    $oBucket->PutStr( "PoolController", "sStatus", "Temporarily Closed" );
    $oBucket->PutStr( "PoolController", "sReason", $_REQUEST['Reason'] );


    $raParms["Status"] = "closed-temp";
    $raParms["Reason"] = $_REQUEST['Reason'];
    $raParms["Other"] = $oBucket->GetStr( "PoolController", "sOther" );


} else {
    foreach( array( 'Status', 'Reason', 'Other' ) as $k ) {
        if( isset($_REQUEST[$k]) ) {
            $p = SEEDSafeGPC_GetStrPlain( $k );
            $oBucket->PutStr( "PoolController", "s".$k, $p );
        } else {
            $p = $oBucket->GetStr( "PoolController", "s".$k );
        }
        $raParms[$k] = $p;
    }
    if( ($p = SEEDSafeGPC_GetStrPlain('Reason-other') ) ) {
        $oBucket->PutStr( "PoolController", "sReason", $p );
        $raParms["Reason"] = $p;
    }
}


/* Draw the form
*/
echo "<form method='post'>"
    ."<input type='hidden' name='action' value='update'/>";

echo "Status<br/>"
    .SelectStatus( $raParms['Status'] );


echo "<br/><br/>";

$raReasons = array( "N/A" => "no-reason",
                              "Private function" => 'Private function',
                              "Chlorine" => "Super Chlorinated",
                              "Maintenance" => "Maintenance",
			      "Algae" => "Algae",
                              "Leak" => "Leak" );
if( !in_array($raParms['Reason'], $raReasons) ) {
    $raReasons[$raParms['Reason']] = $raParms['Reason'];
}

echo "Reason<br/>"
    .SEEDForm_Select2( 'Reason',
                       $raReasons,
                       $raParms['Reason'] );

echo " or other: <input name='Reason-other' value=''/>"
    ." <b>".$raParms['Reason']."</b>"
    ."<br/><br/>";

echo "Other<br/><input type='text' name='Other' value='".$raParms['Other']."'/><br/><br/>";

echo "<input type='submit'/>"
    ."</form>";

?>
