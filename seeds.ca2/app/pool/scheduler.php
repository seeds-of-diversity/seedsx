<?php

define( 'SITEROOT', "../../");
include( SITEROOT."site.php" );
include( "lib.php" );

echo "<html><head><link rel='shortcut icon' href='//seeds.ca/app/pool/favicon.png'></head>";


list($kfdb) = SiteStart();

$oTable = new SEEDMetaTable_TablesLite( $kfdb );
$kTable = $oTable->OpenTable( "EricPoolScheduler" );

// If the form was submitted save a new row in the schedule
if( @$_REQUEST['k'] && ($iTime = StrTime2UTC(@$_REQUEST['time'])) ) {
    $oTable->PutRow( $kTable, 0, array( 'iTimeUTC' => $iTime,
                                        'status'=>$_REQUEST['Status'],
                                        'reason'=>$_REQUEST['reason'],
                                        'done'  => 0
    ) );
}

// If a delete button was clicked, delete the row from the schedule
if( @$_REQUEST['delete'] ) {
    $oTable->DeleteRow( intval($_REQUEST['delete']) );
}


$raRows = $oTable->GetRows( $kTable );

// Check for schedules that should be activated
foreach( $raRows as $k => $ra ) {
    $iTime = @$ra['vals']['iTimeUTC'];
    if( $iTime <= time() && !$ra['vals']['done'] ) {
        $oBucket = new SEEDMetaTable_StringBucket( $kfdb );
        $oBucket->PutStr( "PoolController", "sStatus", $ra['vals']['status'] );
        $oBucket->PutStr( "PoolController", "sReason", $ra['vals']['reason'] );

        $ra['vals']['done'] = 1;
        $oTable->PutRow( $kTable, $k, $ra['vals'] );
    }
}

$nPast = 0;
foreach( $raRows as $k => $ra ) {
    $iTime = @$ra['vals']['iTimeUTC'];
    if( $iTime <= time() ) {
        $nPast++;
    }
}



$kActive = 0;
$nPast2 = 0;
// Draw the table of schedules
$s = "<table border='1'><tr><th>Time</th><th>Internal time (now ".time().")</th><th>Pool Status</th><th>Reason</th><th>Status</th><th>Done</th></tr>";
foreach( $raRows as $k => $ra ) {
    $iTime = @$ra['vals']['iTimeUTC'];

    //$iTime = strtotime($sTime);
    if( $iTime ) {
        $iTimeEDT = $iTime - 18000;
        $sTime = date( "Y-M-d H:iA", $iTimeEDT );
        $sUntil = "(".($iTime - time())." seconds to go)";
        $sColor = ($iTime > time()) ? "white" : "#fee";

        $sActive = "Pending";
        if( $iTime <= time() ) {
            $nPast2++;

            if( $nPast2 < $nPast ) {
                $sActive = "Past";
                $kActive = $k;
            } else {
                $sActive = "Active";
            }
        }
    } else {
        $sUntil = "";
        $sColor = "#eee";
    }

    $s .= "<tr style='background-color:$sColor'>"
         ."<td>$sTime</td><td>$iTime $sUntil</td>"
         ."<td>".@$ra['vals']['status']."</td>"
         ."<td>".@$ra['vals']['reason']. "</td>"
         ."<td>$sActive</td>"
//         ."<td>".@$ra['vals']['done']. "</td>"
         ."<td><form method='post'><input type='hidden' name='delete' value='$k'><button onclick='submit();'>Delete</button></form></td>"
         ."</tr>";
}
$s .= "</table><br/><br/>";
echo $s;



echo "<form method='post'>"
    ."Time <input required name='time' type='datetime-local'/> <br/>"
    ."Pool Status ".SelectStatus( "" )." <br/>"
    ."Reason <input name='reason'/> <br/>"
    ."<input type='hidden' name='k' value='1'/>"
    ."<input type='hidden' name='status' value='pending'/>"
    ."<input type='submit'/>"
    ."</form>";

?>
