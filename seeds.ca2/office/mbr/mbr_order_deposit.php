<?php

define("SITEROOT", "../../");
include_once( SITEROOT."site2.php" );
include_once( SEEDCORE."SEEDCoreFormSession.php" );
include_once( SEEDCORE."SEEDXLSX.php" );
include_once( SEEDCOMMON."mbr/mbrOrder.php" );

// kfdb is seeds2 for SEEDSession authentication- same as mbr_order.php because they go together
$oApp = SEEDConfig_NewAppConsole( ['db'=>'seeds2', 'sessPermsRequired'=>['R MBRORDER'] ] );
list($kfdb,$sess,$lang) = SiteStartSessionAccountNoUI( ["R MBRORDER"] );

SEEDPRG();

$s = "";

//$oMOR = new MbrOrderReport( new SEEDApp_Worker( $kfdb, $sess, 'EN' ) );
$oOrder = new MbrOrder( $oApp, $kfdb, "EN" );
$oMD = new MbrOrderDeposit($oApp, $oOrder);

list($cmd,$oldCode,$newCode,$raRange,$sRangeNormal,$sErr) = $oMD->GetParms();
if( $sErr ) {
    $s .= "<div class='alert alert-danger'>$sErr</div>";
    goto showForm;
}
if( $cmd ) {
    $hsOldCode = SEEDCore_HSC($oldCode);
    $hsNewCode = SEEDCore_HSC($newCode);

    if( in_array($cmd,['new','edit']) ) {
        $raKfr = $oOrder->kfrelOrder->GetRecordSet( SEEDCore_RangeStrToDB($sRangeNormal, "_key"), ['sSortCol'=>'_key'] );
    }

    switch($cmd) {
        case 'new':
            // Verify that all orders in the range are not already in a deposit
            list($bOk,$sErr) = $oMD->VerifyCodeNonoverwriting($raKfr, ['']);
            if( !$bOk ) { $s .= $sErr; goto showForm; }

            // Record the new depositCode for each order in the range
            $oMD->StoreDepositCode($newCode, $sRangeNormal);
            $s .= "<div class='alert alert-success'>Added deposit <b>$hsNewCode</b></div>";
            break;

        case 'edit':
            // Verify that all orders in the range are either not in a deposit, in the current deposit, or in one that we're merging to via rename
            list($bOk,$sErr) = $oMD->VerifyCodeNonoverwriting($raKfr, ['',$oldCode,$newCode]);
            if( !$bOk ) { $s .= $sErr; goto showForm; }

            // Clear all deposits that have old depositCode
            $oMD->ClearDepositCode($oldCode);
            // Record the new depositCode for each order in the range
            $oMD->StoreDepositCode($newCode, $sRangeNormal);

            if( $oldCode != $newCode ) {
                $s .= "<div class='alert alert-success'>Changed deposit code <b>$hsOldCode</b> to <b>$hsNewCode</b> and updated order list</div>";
            } else {
                $s .= "<div class='alert alert-success'>Updated order list for deposit <b>$hsNewCode</b></div>";
            }
            break;

        case 'delete':
            // Clear all deposits that have old code
            $oMD->ClearDepositCode($oldCode);
            $s .= "<div class='alert alert-success'>Deleted deposit <b>$hsOldCode</b></div>";
            break;

        case 'xls':
            $oMD->CreateXLS($oldCode);
            exit;
    }
}

showForm:

$oForm = new SEEDCoreForm('Plain');
//$oForm->Load();  don't really want to retain values in the form after submit
$oFormSrch = new SEEDCoreFormSVA($oApp->oC->GetSVA('srchDepositCode'));
$oFormSrch->Update();

$s .= "<h3>Deposits</h3>"
     ."<ol>"
     ."<li>Enter the range of order numbers for your deposit e.g. 29002,29004,29006-29010,29014.</li>"
     ."<li>Enter a code for this deposit. We use the letter C for cheques followed by the date. e.g. today's cheque deposit is called <b>C".date('Y-m-d')."</b></li>"
     ."<li>The deposit will be shown below. If it's wrong, click Edit and change it.</li>"
     ."</ol>"
     ."<h4>Some tips</h4>
       <ol>
       <li>You can get the range of orders from a deposit by clicking Edit and copying the numbers in the box.</li>
       <li>The numbers in a range don't have to be in order. e.g. 3,6,2,1 is the same as 1,2,3,6, which is also 1-3,6</li>
       <li>You can add an order to an existing deposit by using Edit and adding that number in the range.</li>
       <li>You won't be allowed to add an order to a deposit if it's already in another deposit. First remove that order number from the other deposit using Edit.</li>
       </ol>"

     ."<div style='width:auto;margin:10px;padding:10px;border:1px solid #aaa;border-radius:5px'>
       <form method='post'>"
     .$oForm->Hidden('cmd', ['value'=>'new'])
     ."<table cellspacing='10'><tr>".$oForm->TextTD('range', "Range<br/>&nbsp;", ['size'=>50])."</tr>
              <tr>".$oForm->TextTD('newCode', "Deposit Code&nbsp;<br/>&nbsp;", ['size'=>50])."</tr>
       </table>
       <input type='submit' value='Save Deposit'/>
       </form></div>
       <br/><hr style='border:2px solid #aaa'/>
       <form method='post'><p style='margin:20px'><input type='submit' value='Search for deposit code'/> {$oFormSrch->Text('srchCode','')}</p></form>";


/* Show all the deposits that match the filter; default to all non-blank depositCodes (blank depositCode means an order is not in a deposit)
 */
$cond = ($sSrch = $oFormSrch->Value('srchCode')) ? ("depositCode LIKE '%".addslashes($sSrch)."%'") : "depositCode<>''";
$raCodes = $kfdb->QueryRowsRA1( "SELECT depositCode FROM {$oApp->DBName('seeds1')}.mbr_order_pending WHERE $cond GROUP BY 1 ORDER BY 1 DESC" );
foreach( $raCodes as $code ) {
    $fTotal = 0.0;
    $raOrders = $oOrder->kfrelOrder->GetRecordSetRA( "depositCode='".addslashes($code)."'" );

    $sT = "";
    foreach( $raOrders as $raR ) {
        list($ra,$fSubtotal) = $oMD->order2table($raR);
        $fTotal += $fSubtotal;
        $sT .= SEEDCore_ArrayExpand( $ra, "<tr><td>[[order]]</td><td style='text-align:left'>[[name]]</td>"
                                            ."<td>[[membership]]</td><td>[[sed]]</td><td>[[donation]]</td><td>[[sladoption]]</td>"
                                            ."<td>[[books]]</td><td>[[seeds]]</td><td>[[misc]]</td></tr>" );
    }

    $hsCode = SEEDCore_HSC($code);
    // make range string for Edit control
    $raRange = [];
    foreach( $raOrders as $raR ) { $raRange[] = $raR['_key']; }
    $sRange = SEEDCore_MakeRangeStr($raRange);
    $s .= "<div class='mbrod_deposit'>
           <div class='container-fluid'><div class='row'>
             <div class='col-md-8'>
               <b>$hsCode".SEEDCore_NBSP("",10)."$ $fTotal</b>
               <table><tr><th>&nbsp;</th><th>&nbsp</th><th>membership</th><th>seed-directory</th><th>donation</th><th>sladoption</th>
                          <th>books</th><th>seeds</th><th>misc</th></tr>
               $sT
               </table>
             </div>
             <div class='col-md-3'>
               <button class='btnDel' style='float:right;margin-left:20px' data-code='$hsCode'>Delete</button>
               <button class='btnEdit' style='float:right' data-code='$hsCode' data-range='$sRange'>Edit</button>
             </div>
             <div class='col-md-1'>
               <a href='?cmd=xls&oldCode=$hsCode'><img src='".W_CORE_URL."img/icons/xls.png' height='25'/></a>
             </div>
           </div></div>
           </div>";
}

echo Console02Static::HTMLPage($s, "", "EN");

class MbrOrderDeposit
{
    private $oApp;
    private $oOrder;

    function __construct( SEEDAppDB $oApp, MbrOrder $oOrder )
    {
        $this->oApp = $oApp;
        $this->oOrder = $oOrder;
    }

    function GetParms()
    /******************
        Return standardized parameters for new, edit, delete, xls commands
     */
    {
        $sErr = "";

        $cmd = SEEDInput_Str('cmd');
        $oldCode = SEEDInput_Str('oldCode');
        $newCode = SEEDInput_Str('newCode');
        list($raRange, $sRangeNormal) = SEEDCore_ParseRangeStr( SEEDInput_Str('range') );

        if( !in_array($cmd, ['','new','edit','delete','xls']) ) { $sErr = "Unknown Command"; goto done; }

        // new and edit must have a valid range string
        if( in_array($cmd,['new','edit']) && (!count($raRange) || $sRangeNormal=='0') ) {
            $sErr = "No Range";
            goto done;
        }

        // new and edit need a newCode; edit, delete, xls need an oldCode
        if( !$newCode && in_array($cmd,['new','edit']) ||
            !$oldCode && in_array($cmd,['edit','delete','xls']) )
        {
            $sErr = "<div class='alert alert-danger'>No Deposit Code</div>";
            goto done;
        }

        done:
        return( [$cmd,$oldCode,$newCode,$raRange,$sRangeNormal,$sErr] );
    }

    function VerifyCodeNonoverwriting( array $raKfr, array $raAllowedCodes )
    {
        /* Return false if any of the orders in raKfr have a depositCode that is not in raAllowedCodes
         */
        $bOk = false;
        $sErr = "";

        foreach( $raKfr as $kfr ) {
            if( !in_array($kfr->Value('depositCode'),$raAllowedCodes) ) {
                $sErr = "<div class='alert alert-danger'>Order {$kfr->Key()} is already part of deposit {$kfr->Value('depositCode')}</div>";
                goto done;
            }
        }

        $bOk = true;

        done:
        return( [$bOk,$sErr] );
    }

    function ClearDepositCode( string $code )
    {
        // Clear all deposits that have given code
        $dbCode = addslashes($code);
        $this->oApp->kfdb->Execute( "UPDATE {$this->oApp->DBName('seeds1')}.mbr_order_pending SET depositCode='' WHERE depositCode='$dbCode'");
    }

    function StoreDepositCode( string $newCode, string $sRangeNormal)
    {
        $dbCode = addslashes($newCode);
        $this->oApp->kfdb->Execute( "UPDATE {$this->oApp->DBName('seeds1')}.mbr_order_pending SET depositCode='$dbCode' WHERE (".SEEDCore_RangeStrToDB($sRangeNormal, "_key").")");
    }

    function CreateXLS( $code )
    {
        $raRows = $this->oOrder->kfrelOrder->GetRecordSetRA( "depositCode='".addslashes($code)."'", ['sSortCol'=>'_key'] );

        $raOut = [];
        foreach( $raRows as $raR ) {
            list($ra,$fSubtotal) = $this->order2table($raR);
            $raOut[] = $ra;
        }
    //var_dump($raOut);
    //exit;

        $cols = ['order','name','membership','seed-directory','donation','sladoption','books','seeds','misc'];
        $oXls = new SEEDXlsWrite( ['filename'=>"deposit $code.xlsx"] );
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

    function order2table( array $raR )
    {
        $ra = array();

        $this->oOrder->setKOrder( $raR['_key'] );
        $this->oOrder->computeOrder();
        $raOrder = $this->oOrder->raOrder;
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
}

?>

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

</style>

<script>
$(document).ready(function() {
    $(".btnEdit").click( function(e) {
        let oldCode = $(this).attr('data-code');
        $(this).parent().html(
                `<div><form method='post'>`
               +`Change code to <input name='newCode' value='${oldCode}' style='width:100%'/><br/><br/>`
               +`Change range to <textarea name='range' rows='3' style='display:inline-block;vertical-align:top;width:100%'>`+$(this).attr('data-range')+`</textarea><br/><br/>`
               +`<input type='hidden' name='oldCode' value='${oldCode}'/>`
               +`<input type='hidden' name='cmd' value='edit'/>`
               +`<input type='submit' value='Change'/>`
               +`</form></div>`
        );
    });
    $(".btnDel").click( function(e) {
        let oldCode = $(this).attr('data-code');
        $(this).parent().html(
                `<div><form method='post'>`
               +`Delete this deposit? (does not delete the orders)<br/><br/>`
               +`<input type='hidden' name='oldCode' value='${oldCode}'/>`
               +`<input type='hidden' name='cmd' value='delete'/>`
               +`<input type='submit' value='Delete'/>`
               +`</form></div>`
        );
    });
});
</script>