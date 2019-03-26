<?php

define("SITEROOT", "../../");
include_once( SITEROOT."site2.php" );
include_once( STDINC."SEEDForm.php" );
include_once( SEEDCORE."SEEDXLSX.php" );
include_once( SEEDCOMMON."mbr/mbrOrder.php" );

// kfdb is seeds2 to segregate SEEDSession privileges there
list($kfdb, $sess) = SiteStartSessionAccount( array("R MBRORDER") );
$bCanWrite = $sess->CanWrite('MBRORDER');

$s = "";

//$oMOR = new MbrOrderReport( new SEEDApp_Worker( $kfdb, $sess, 'EN' ) );
$oOrder = new MbrOrder( $kfdb, "EN" );

if( SEEDInput_Str('cmd') == "xls" ) {
    list($raRange, $sRangeNormal) = SEEDCore_ParseRangeStr( SEEDInput_Str('range') );
    if( !count($raRange) ) {
        $s .= "<div class='alert alert-warning'>No Range</alert>";
        goto showForm;
    }

    $raOut = array();
    $raRows = $oOrder->kfrelOrder->GetRecordSetRA( SEEDCore_RangeStrToDB( $sRangeNormal, "_key" ), array('sSortCol'=>'_key') );

    foreach( $raRows as $raR ) {
        $ra = array();

        $oOrder->setKOrder( $raR['_key'] );
        $oOrder->computeOrder();
        $raOrder = $oOrder->raOrder;
//var_dump($raOrder);
        $ra['order'] = $raR['_key'];
        if( !($ra['name'] = trim(utf8_encode($raR['mail_firstname']." ".$raR['mail_lastname']))) ) {    // trim removes the " " if no first/last
            $ra['name'] = utf8_encode($raR['mail_company']);
        }
        $ra['membership'] = (@$raOrder['mbr']=='mbr1_45sed' ? 45 : (@$raOrder['mbr']=='mbr1_35' ? 35 : "") );
        $ra['donation'] = @$raOrder['donation'];
        $ra['sladoption'] = @$raOrder['slAdopt_amount'];

        $ra['books'] = "";
        if( $raOrder['pubs'] ) {
            $ra['books'] = 0;   // prevent non-numeric warning with +=
            foreach( $raOrder['pubs'] as $raPub ) {
                $ra['books'] += $raPub[3];  // total price of n copies
            }
        }
        $ra['seeds'] = "";
        if( @$raOrder['seeds'] ) {
            $ra['seeds'] = 0;   // prevent non-numeric warning with +=
            foreach( $raOrder['seeds'] as $raSeeds ) {
                $ra['seeds'] += $raSeeds['amount'];
            }
        }

        $ra['misc'] = @$raOrder['misc'] + @$raOrder['everyseed_shipping'];
        if( !$ra['misc'] )  $ra['misc'] = "";


        $raOut[]= $ra;
    }
//var_dump($raOut);
//exit;

    $cols = array('order','name','membership','donation','sladoption','books','seeds','misc');
    $oXls = new SEEDXlsWrite( array('filename'=>"deposit $sRangeNormal.xlsx") );
    $oXls->WriteHeader( 0, $cols );

    $row = 2;
    foreach( $raOut as $ra ) {
        $oXls->WriteRow( 0, $row++, array( $ra['order'], $ra['name'], $ra['membership'], $ra['donation'], $ra['sladoption'], $ra['books'], $ra['seeds'], $ra['misc'] ) );
    }
    $oXls->OutputSpreadsheet();

//    SEEDTable_OutputXLSFromRARows( $raOut,
//                                   array( 'columns' => $cols,
//                                          'filename'=>"deposit $sRangeNormal.xls",
//                                          'created_by'=>$sess->GetName(), 'title'=>"Deposit $sRangeNormal" ) );
    exit;
}

showForm:


$s .= "<h3>Deposits</h3>"
     ."<form method='post'>"
     .SEEDForm_Hidden( 'cmd', 'xls' )
     .SEEDForm_Text( 'range', "", "Range" )
     ."<input type='submit'/>"
     ."</form>";



echo $s;

?>
