<?php

define("SITEROOT", "../../");
include_once( SITEROOT."site2.php" );
include_once( STDINC."SEEDForm.php" );
include_once( SEEDCORE."SEEDXLSX.php" );
include_once( SEEDCOMMON."mbr/mbrOrder.php" );

// kfdb is seeds2 to segregate SEEDSession privileges there
list($kfdb, $sess) = SiteStartSessionAccount( array("R MBRORDER") );
$bCanWrite = $sess->CanWrite('MBRORDER');

SEEDPRG();

$s = "";

//$oMOR = new MbrOrderReport( new SEEDApp_Worker( $kfdb, $sess, 'EN' ) );
$oApp = new SEEDAppDB( $config_KFDB['seeds1'] );
$oOrder = new MbrOrder( $oApp, $kfdb, "EN" );

$cmd = SEEDInput_Str('cmd');
if( $cmd == 'new' ) {
    list($raRange, $sRangeNormal) = SEEDCore_ParseRangeStr( SEEDInput_Str('range') );
    if( !count($raRange) ) {
        $s .= "<div class='alert alert-danger'>No Range</alert>";
        goto showForm;
    }
    if( !($sDepositCode = SEEDInput_Str('depositCode')) ) {
        $s .= "<div class='alert alert-danger'>No Deposit Code</alert>";
        goto showForm;
    }

    $raOut = array();
    $raKfr = $oOrder->kfrelOrder->GetRecordSet( SEEDCore_RangeStrToDB( $sRangeNormal, "_key" ), array('sSortCol'=>'_key') );

    // Verify that the range does not include an order that is already in another deposit
    foreach( $raKfr as $kfr ) {
        if( $kfr->Value('depositCode') ) {
            $s .= "<div class='alert alert-danger'>Order ".$kfr->Key()." is already part of deposit ".$kfr->Value('depositCode')."</div>";
            goto showForm;
        }
    }

    // Record the depositCode for each order in the range
    foreach( $raKfr as $kfr ) {
        $kfr->SetValue('depositCode', $sDepositCode);
        $kfr->PutDBRow();
    }

} else
if( $cmd == "xls" ) {
    if( !($sDepositCode = SEEDInput_Str('depositCode')) ) {
        $s .= "<div class='alert alert-danger'>No Deposit Code</alert>";
        goto showForm;
    }

    $raOut = array();
    $raRows = $oOrder->kfrelOrder->GetRecordSetRA( "depositCode='".addslashes($sDepositCode)."'", ['sSortCol'=>'_key'] );

    foreach( $raRows as $raR ) {
        list($ra,$fSubtotal) = order2table( $oOrder, $raR );
        $raOut[] = $ra;
    }
//var_dump($raOut);
//exit;

    $cols = array('order','name','membership','seed-directory','donation','sladoption','books','seeds','misc');
    $oXls = new SEEDXlsWrite( ['filename'=>"deposit $sDepositCode.xlsx"] );
    $oXls->WriteHeader( 0, $cols );

    $row = 2;
    foreach( $raOut as $ra ) {
        $oXls->WriteRow( 0, $row,
                       // A             B            C                  D           E                F                  G             H             I            J   K
                         [$ra['order'], $ra['name'], $ra['membership'], $ra['sed'], $ra['donation'], $ra['sladoption'], $ra['books'], $ra['seeds'], $ra['misc'], "", "=sum(C$row:I$row)"] );
        // bold the total on right
        $oXls->SetCellStyle( 0, $row, 'K', ['font'=>['bold'=>true]] );

        $row++;
    }

    // compute the totals at the bottom
    $row++;
    $rowMinus2 = $row - 2;
    $oXls->WriteRow( 0, $row, ["", "", "=sum(C2:C$rowMinus2)", "=sum(D2:D$rowMinus2)", "=sum(E2:E$rowMinus2)", "=sum(F2:F$rowMinus2)",
                               "=sum(G2:G$rowMinus2)", "=sum(H2:H$rowMinus2)", "=sum(I2:I$rowMinus2)", "", "=sum(K2:K$rowMinus2)"] );

    // bold the totals on the bottom
    foreach( ['C','D','E','F','G','H','I','K'] as $c ) {
        $oXls->SetCellStyle( 0, $row, $c, ['font'=>['bold'=>true]] );
    }
    $oXls->OutputSpreadsheet();

    exit;
}

showForm:

$oForm = new SEEDCoreForm('Plain');
//$oForm->Load();  don't really want to retain values in the form after submit
$s .= "<h3>Deposits</h3>"
     ."<p><b>NEW</b> No more spreadsheets to email! Just record the deposited orders here</p>"
     ."<ol>"
     ."<li>Enter the range of order numbers for your cheque deposit as usual e.g. 19002,19004,19006-19010,19014.</li>"
     ."<li>Enter a code for this deposit. We use the letter C for cheques followed by the date. e.g. today's cheque deposit is called <b>C".date('Y-m-d')."</b></li>"
     ."<li>The deposit will be shown below. If it's wrong, delete it and enter again.</li>"
     ."<li>That's it. No need to download or email any spreadsheet because we can all see the information here.</li>"
     ."</ol>"
     ."<form method='post'>"
     .$oForm->Hidden( 'cmd', ['value'=>'new'] )
     ."<table><tr>"
     .$oForm->TextTD( 'range', "Range" )
     ."</tr><tr>"
     .$oForm->TextTD( 'depositCode', "Deposit Code" )
     ."</tr></table>"
     ."<input type='submit'/>"
     ."</form>";


/* Show all the deposits
 */
$raCodes = $kfdb->QueryRowsRA1( "SELECT depositCode FROM {$oApp->DBName('seeds1')}.mbr_order_pending WHERE depositCode<>'' GROUP BY 1 ORDER BY 1 DESC" );
foreach( $raCodes as $code ) {
    $fTotal = 0.0;
    $raOrders = $oOrder->kfrelOrder->GetRecordSetRA( "depositCode='".addslashes($code)."'" );

    $sT = "";
    foreach( $raOrders as $raR ) {
        list($ra,$fSubtotal) = order2table( $oOrder, $raR );
        $fTotal += $fSubtotal;
        $sT .= SEEDCore_ArrayExpand( $ra, "<tr><td>[[order]]</td><td style='text-align:left'>[[name]]</td>"
                                            ."<td>[[membership]]</td><td>[[sed]]</td><td>[[donation]]</td><td>[[sladoption]]</td>"
                                            ."<td>[[books]]</td><td>[[seeds]]</td><td>[[misc]]</td></tr>" );
    }

    $s .= "<div class='mbrod_deposit'>"
         ."<div style='float:right'><a href='?cmd=xls&depositCode=$code'><img src='".W_CORE_URL."img/icons/xls.png' height='25'/></a></div>"
         //."<div style='float:right'><a href='?cmd=delete&depositCode=$code'><img src='".W_CORE_URL."img/ctrl/delete01.png' height='25'/></a></div>"
         ."<b>$code".SEEDCore_NBSP("",10)."$ $fTotal</b>"
         ."<table><tr><th>&nbsp;</th><th>&nbsp</th><th>membership</th><th>seed-directory</th><th>donation</th><th>sladoption</th>"
                    ."<th>books</th><th>seeds</th><th>misc</th></tr>"
         .$sT
         ."</table>"
         ."</div>";
}

$s .= "
<style>
.mbrod_deposit {
    margin: 10px;
    padding: 10px;
    border: 1px solid #aaa;
    background-color: #eee;
}
.mbrod_deposit table th {
    text-align:right;
    padding-left:10px;
}

.mbrod_deposit table td {
    text-align:right;
    padding-right:10px;
}

</style>";


echo $s;


function order2table( MbrOrder $oOrder, $raR )
{
    $ra = array();

    $oOrder->setKOrder( $raR['_key'] );
    $oOrder->computeOrder();
    $raOrder = $oOrder->raOrder;
//var_dump($raOrder);
    $ra['order'] = $raR['_key'];
    if( !($ra['name'] = trim(SEEDCore_utf8_encode($raR['mail_firstname']." ".$raR['mail_lastname']))) ) {    // trim removes the " " if no first/last
        $ra['name'] = SEEDCore_utf8_encode($raR['mail_company']);
    }
    $ra['membership'] = (@$raOrder['mbr']=='mbr1_45sed' ? 45 : (@$raOrder['mbr']=='mbr1_35' ? 35 : "") );
    $ra['sed'] = @$raOrder['mbr']=='mbr1_15sed' ? 15 : (@$raOrder['mbr']=='mbr1_10sed' ? 10 : "");
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

    $fTotal = floatval($ra['membership']) + floatval($ra['sed']) + floatval($ra['donation']) + floatval($ra['sladoption'])
            + floatval($ra['books']) + floatval($ra['seeds']) + floatval($ra['misc']);

    return( [$ra,$fTotal] );
}
