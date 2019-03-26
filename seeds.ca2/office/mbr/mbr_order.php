<?php

// todo: flag unpaid entries that have later entries (paid or unpaid) with the same name | address | phone | email

define("SITEROOT", "../../");
include_once( SITEROOT."site2.php" );
include_once( STDINC."SEEDLocal.php" );
include_once( SEEDCOMMON."mbr/mbrOrder.php" );
include_once( SEEDAPP."basket/sodBasketFulfil.php" );


// kfdb is seeds2
list($kfdb, $sess) = SiteStartSessionAccount( array("R MBRORDER") );
$bCanWrite = $sess->CanWrite('MBRORDER');

$oApp = SiteAppConsole( ['db'=>'seeds2', 'sessPermsRequired'=>['R MBRORDER'] ] );



define( "MBR_ADMIN", "1" ); // DrawTicket shows all the internal stuff

$oUI = new SodOrderFulfilUI( $oApp );


$oOrder = new MbrOrderCommon( $kfdb, "EN", $sess->GetUID() );
$kfrel = $oOrder->kfrelOrder;

if( ($jx = @$_REQUEST['jx']) ) {
    $rQ = array( 'bOk'=>false, 'sOut'=>"", 'sErr'=>"" );

    switch( $jx ) {
        case 'changeStatus2ToMailed':
            if( ($id = intval(@$_REQUEST['id'])) ) {
                if( !($kfr = $oUI->KfrelOrder()->GetRecordFromDBKey( $id )) ) { $rQ['sErr'] = "Couldn't load $id"; goto jxDone; }

                if( !$oUI->SetMailedToday( $kfr ) ) { $rQ['sErr'] = "Couldn't store"; goto jxDone; }

                $rQ['sOut'] = "Order mailed ".$kfr->Value('dMailed');
                $rQ['bOk'] = true;
            }
            break;
        case 'changeStatus2ToNothingToMail':
            if( ($id = intval(@$_REQUEST['id'])) ) {
                if( !($kfr = $oUI->KfrelOrder()->GetRecordFromDBKey( $id )) ) { $rQ['sErr'] = "Couldn't load $id"; goto jxDone; }

                if( !$oUI->SetMailedNothing( $kfr ) ) { $rQ['sErr'] = "Couldn't store"; goto jxDone; }

                $rQ['sOut'] = "";
                $rQ['bOk'] = true;
            }
            break;

        case 'drawTicket':
            if( ($id = intval(@$_REQUEST['id'])) ) {
                if( !($kfr = $oUI->KfrelOrder()->GetRecordFromDBKey( $id )) ) { $rQ['sErr'] = "Couldn't load $id"; goto jxDone; }

                $oMbrOrder = new MbrOrder( $kfdb, "EN", $id );
                $rQ['sOut'] = utf8_encode($oMbrOrder->DrawTicket());
                $rQ['bOk'] = true;

                header( "Content-Type:text/html; charset=utf8" );
            }
            break;
    }

    jxDone:
    echo json_encode($rQ);
    exit;
}



$s = "<style>"
    ."body, p, td, th { font-family:verdana,helvetica,sans serif; font-size:10pt; }"
    ."</style>";

$s .=  MbrOrderStyle();

$s .= "<table border='0' width='100%'><tr><td><h2>Online Order Summary</h2></td>"
     ."<td align='right'><a href='".SITE_LOGIN_ROOT."'>Home</a>&nbsp;&nbsp;&nbsp;<a href='mbr_order_stats.php'>Statistics</a>&nbsp;&nbsp;&nbsp;<a href='mbr_order_deposit.php'>Deposit</a></td></tr></table>";

$kfr = null;
if( ($row = $oUI->GetCurrOrderKey()) ) {
    $kfr = $kfrel->GetRecordFromDBKey( $row );

    $kfr2 = $oUI->KfrelOrder()->GetRecordFromDBKey( $row );


    if( $bCanWrite ) {
        $action = $oUI->pAction;
        $action_notes = SEEDSafeGPC_GetStrPlain('action_note');

        $sStamp = "[".$sess->GetName()." at ".date( "Y-M-d h:i")."]";

        $bUpdate = false;
        $sNoteExtra = "";
        switch( $action ) {
            case "Change to Pending":
                // Pending is represented as (eStatus in (New,Paid)), so for PayPal orders use pp_payment_status=='Completed'
                // to decide which one to use.  For Cheques, just go back to New and hope for the best.
                $eNewStatus = $kfr->value('pp_payment_status')=='Completed' ? MBRORDER_STATUS_PAID : MBRORDER_STATUS_NEW;
                $kfr->SetValue( 'eStatus', $eNewStatus );
                //$kfr->SetValue( 'pay_status', MBR_PS_CONFIRMED );
                $sNoteExtra = "Changed status to Pending ($eNewStatus)";
                $bUpdate = true;
                break;
            case "Fill":
                $kfr->SetValue( 'eStatus', MBRORDER_STATUS_FILLED );
                //$kfr->SetValue( 'pay_status', MBR_PS_FILLED );
                $sNoteExtra = "Changed status to Filled";
                $bUpdate = true;
                break;
            case "Cancel":
                $kfr->SetValue( 'eStatus', MBRORDER_STATUS_CANCELLED );
                //$kfr->SetValue( 'pay_status', MBR_PS_CANCELLED );
                $sNoteExtra = "Changed status to Cancelled";
                $bUpdate = true;
                break;
            case "Add Note":
                $bUpdate = true;
                break;

            case "changeStatusToPaid":
                $kfr->SetValue( 'eStatus', MBRORDER_STATUS_PAID );
                $bUpdate = true;
                break;

            default:
                break;
        }
        if( $bUpdate ) {
            $kfr->SetValue( 'notes', "$sStamp "
                                    .($action_notes ? ($action_notes."\n") : "")
                                    .($sNoteExtra ? ($sNoteExtra."\n") : "")
                                    .$kfr->value('notes') );
            $kfr->PutDBRow();
        }
    }
}

/* Filter Form
 */
$s .= $oUI->DrawFormFilters();

if( $kfr ) {
    $oMbrOrder = new MbrOrder( $kfdb, "EN", $row );
    $sCol1 = $oMbrOrder->DrawTicket();
    $sCol2 = "";

    if( $bCanWrite ) {
        /* Draw the header for the ticket and controls for changing the order's status
         */
        switch( $kfr->value('eStatus') ) {
            case MBRORDER_STATUS_FILLED:
                $sState = "Filled";
                $raActions = array('Change to Pending');
                break;
            case MBRORDER_STATUS_CANCELLED:
                $sState = "Cancelled";
                $raActions = array('Change to Pending');
                break;
            case MBRORDER_STATUS_PAID:
                $sState = "paid, needs to be filled";
                $raActions = array('Fill', 'Cancel');
                break;
            case MBRORDER_STATUS_NEW:
                $sState = "awaiting payment";
                $raActions = array('Fill','Cancel');
                break;
            default:
                die( "<h3><font color='red'>Undefined payment status.  Inform Bob immediately, with the order number ($row).</font></h3>" );
        }
        $sCol2 = "<h3>This order is $sState - last update ".$kfr->value("_updated")."</h3>"
                ."<form action='${_SERVER['PHP_SELF']}'>"
                .SEEDForm_Hidden( 'row', $row );
        foreach( $raActions as $sAction ) {
            $sCol2 .= "<input type='submit' name='action' value='$sAction'>"
                     ."&nbsp;&nbsp;&nbsp;";
        }
        $sCol2 .= "<input type='submit' name='action' value='Add Note'>&nbsp;&nbsp;&nbsp;"
                 .SEEDForm_Text( 'action_note', "", "Note", 50 )
                 ."</form>";
    }

    $s .= "<div class='container-fluid'><div class='row'>"
             ."<div class='col-sm-6'>$sCol1</div>"
             ."<div class='col-sm-6'>$sCol2</div>"
         ."</div></div>";
}


/* Fetch table of orders
 */
$s .= "<h3>".($oUI->fltStatus==MBRORDER_STATUS_FILLED ? "Filled" :
             ($oUI->fltStatus==MBRORDER_STATUS_CANCELLED ? "Cancelled" : "Pending"))
     ." Orders</h3>";

if( $oUI->fltStatus ) {
    // Filled or Cancelled
    $cond = "(eStatus='".$oUI->fltStatus."') AND ".getYearCond( $oUI->fltYear );
    $bSortDown = true;
} else {
    // Pending (New or Paid) - show the pending items from all years (don't want to miss any)
    $cond = "(eStatus<>'".MBRORDER_STATUS_FILLED."' AND eStatus<>'".MBRORDER_STATUS_CANCELLED."')";
    $bSortDown = false;
}
$kfr = $kfrel->CreateRecordCursor( $cond, array('sSortCol'=>'_key','bSortDown'=>$bSortDown) );


/* Draw table of orders
 */
$s .= "<table border='1' width='100%' cellpadding='2' style='border-collapse:collapse'><tr>"
     ."<th>Order #</th>"
     ."<th>Name</th>"
     ."<th>Address<br/>Phone/Email</th>"
     ."<th>Language<br/>eBulletin</th>"
     ."<th>Order</th>"
     ."<th>Payment</th>"
     ."</tr>";

while( $kfr->CursorFetch() ) {
    $oOrder = new MbrOrder( $kfdb, "EN", $kfr->Key() );
    $sConciseSummary = $oOrder->conciseSummary( $kfr->Key() );     // this also computes $oOrder->raOrder for details

    $kfr2 = $oUI->KfrelOrder()->GetRecordFromDBKey( $kfr->Key() );
    $s .= $oUI->DrawOrderSummaryRow( $kfr2, $sConciseSummary, $oOrder->raOrder );
}
$s .= "</table>";




echo Console01Static::HTMLPage( $s, "", 'EN', array( 'sCharset'=>'cp1252', 'bBodyMargin'=>true,
                                                     'raScriptFiles' => array( W_ROOT."std/js/SEEDStd.js" )
) );


function getYearCond( $y )
{
    if( !$y )  return( "1=1" );

    if( $y <= 2010 )  return( "year(_created) <= '2010'" );

    return( "year(_created)='$y'" );
}


?>

<script>

function FormValInt( k )   { return( parseInt(k) || 0 ); }


$(document).ready(function() {
    /* Show Ticket click
     */
    $('.mbrOrderShowTicket').click( function (event) {
        let t = $(this);
        let k = $(this).attr( 'data-kOrder' );
        let x = $(this).attr( 'data-expanded' );

        event.preventDefault();

        if( FormValInt(x) ) {
            $(this).html( "Show Ticket" );
            $(this).attr( 'data-expanded', 0 );
        } else {
            $.get( 'mbr_order.php',
                   "jx=drawTicket&id="+k,
                   function (data) {
                       let d = SEEDJX_ParseJSON( data );
                       //console.log(d);
                       if( d['bOk'] ) {
                           t.html( d['sOut'] );
                           t.attr( 'data-expanded', 1 );
                       }
                   } );
        }
    });

    /* Mailed Today button click
     */
    $(".status2").click(function(event){
        event.preventDefault();
        var thisId = this.id.substr(8);

        jxData = { jx     : 'changeStatus2ToMailed',
                   id     : thisId,
                   lang   : "EN"
                 };

        o = SEEDJX( "mbr_order.php", jxData );
        if( o['bOk'] ) {
            $(this).html("");
            $('#status2x_'+thisId).html("");        // remove the other button
            $("#mailed"+thisId).html(o['sOut']);    // "Order not mailed" changes to "Order mailed YYYY-MM-DD"
        }
    });

    /* Nothing to Mail button click
     */
    $(".status2x").click(function(event){
        event.preventDefault();
        var thisId = this.id.substr(9);

        jxData = { jx     : 'changeStatus2ToNothingToMail',
                   id     : thisId,
                   lang   : "EN"
                 };

        o = SEEDJX( "mbr_order.php", jxData );
        if( o['bOk'] ) {
            $(this).html("");
            $('#status2_'+thisId).html("");  // remove the other button
            $("#mailed"+thisId).html("");    // "Order not mailed" changes to ""
        }
    });
});

</script>
