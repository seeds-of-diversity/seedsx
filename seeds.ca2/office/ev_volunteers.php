<?php


include_once( "../site2.php" );
include_once( SEEDCOMMON."ev/_ev.php" );

list($kfdb, $sess, $dummyLang) = SiteStartSessionAccount( array( "R events" ) );

$oApp = SiteAppConsole( ['db'=>'seeds2', 'sessPermsRequired'=>['R events'] ] );

$year = intval(date("Y",time()+3600*24*60));

$oEv = new EV_Events( $kfdb, $sess->GetUID() );

$raEvents = $oEv->GetKfrelEvents()->GetRecordSetRA( "YEAR(date_start)>='$year' AND type IN ('SS','EV')", ['sSortCol'=>'date_start'] );

$s = "<table border='1' cellpadding='3'>"
    ."<tr><th>Date</th><th>City</th><th>Volunteer</th><th>Materials</th><th>Sent</th></tr>";
foreach( $raEvents as $ra ) {
    $sMbrName = "";
    $sMbrLabel = "";
    if( ($kMbr = $ra['vol_kMbr']) ) {
        $raMbr = $oApp->kfdb->QueryRA( "SELECT * FROM seeds2.mbr_contacts WHERE _key='$kMbr'" );
        $sMbrName = SEEDCore_ArrayExpand( $raMbr, "[[firstname]] [[lastname]]" );

        $sMbrLabel = "<div style='display:inline-block;margin-left:10px;font-size:9pt'>"
                    ."<form action='http://seeds.ca/office/mbr/mbr_labels.php' target='MbrLabels' method='get'>"
                    ."<input type='hidden' name='mbradd' value='$kMbr'/><input type='submit' value='Label'/></form>"
                    ."</div>";
    }

    $s .= SEEDCore_ArrayExpand( $ra,
                "<tr>"
               ."<td>[[date_start]]</td>"
               ."<td>[[city]], [[province]]</td>"
               ."<td><span style='white-space:nowrap'>$sMbrName&nbsp;($kMbr)&nbsp;$sMbrLabel</span></td>"
               ."<td style='font-size:9pt'>[[vol_notes]]</td>"
               ."<td>[[vol_dSent]]</td>"
               ."</tr>"
        );
}
$s .= "</table>";

$oApp->oC->SetConfig( ['HEADER'=>'Event Volunteers'] );

echo Console02Static::HTMLPage( $oApp->oC->DrawConsole( $s ), "", "EN", ['sCharset'=>'cp1252', 'consoleSkin'=>'green'] );

