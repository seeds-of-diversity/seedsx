<?php

// todo: flag unpaid entries that have later entries (paid or unpaid) with the same name | address | phone | email

define("SITEROOT", "../../");
include_once( SITEROOT."site2.php" );
include_once( STDINC."SEEDLocal.php" );
include_once( SEEDCOMMON."mbr/mbrOrder.php" );
include_once( "_mbr_order_report.php" );

header( "Content-Type:text/html; charset=ISO-8859-1" );

// kfdb is seeds2 to segregate SEEDSession privileges there
list($kfdb, $sess) = SiteStartSessionAccount( array("R MBRORDER") );
$bCanWrite = $sess->CanWrite('MBRORDER');

$oMOR = new MbrOrderReport( new SEEDApp_Worker( $kfdb, $sess, 'EN' ) );


define( "MBR_ADMIN", "1" ); // DrawTicket shows all the internal stuff


$oOrder = new MbrOrderCommon( $kfdb, "EN", $sess->GetUID() );
$kfrel = $oOrder->kfrelOrder;

if( ($jx = @$_REQUEST['jx']) ) {
    $rQ = array( 'bOk'=>false, 'sOut'=>"", 'sErr'=>"" );

    if( $jx == 'changeStatus2ToMailed' ) {
        if( ($id = intval(@$_REQUEST['id'])) ) {
            if( !($kfr = $kfrel->GetRecordFromDBKey( $id )) ) { $rQ['sErr'] = "Couldn't load $id"; goto jxDone; }

            $kfr->SetValue( "eStatus2", 1 );
            $kfr->SetValue( "dMailed", date('Y-m-d') );
            if( !($kfr->PutDBRow()) ) { $rQ['sErr'] = "Couldn't store"; goto jxDone; }

            $rQ['sOut'] = "Order mailed ".$kfr->Value('dMailed');
            $rQ['bOk'] = true;
        }
    }
    if( $jx == 'changeStatus2ToNothingToMail' ) {
        if( ($id = intval(@$_REQUEST['id'])) ) {
            if( !($kfr = $kfrel->GetRecordFromDBKey( $id )) ) { $rQ['sErr'] = "Couldn't load $id"; goto jxDone; }

            $kfr->SetValue( "eStatus2", 2 );
            if( !($kfr->PutDBRow()) ) { $rQ['sErr'] = "Couldn't store"; goto jxDone; }

            $rQ['sOut'] = "";
            $rQ['bOk'] = true;
        }
    }

    if( $jx == 'drawTicket' ) {
        if( ($id = intval(@$_REQUEST['id'])) ) {
            if( !($kfr = $kfrel->GetRecordFromDBKey( $id )) ) { $rQ['sErr'] = "Couldn't load $id"; goto jxDone; }

            $oMbrOrder = new MbrOrder( $kfdb, "EN", $id );
            $rQ['sOut'] = $oMbrOrder->DrawTicket();

            $rQ['bOk'] = true;
        }
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
if( ($row = $oMOR->pRow) ) {
    $kfr = $kfrel->GetRecordFromDBKey( $row );

    if( $bCanWrite ) {
        $action = $oMOR->pAction;
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
$s .= $oMOR->DrawFormFilters();

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
$s .= "<h3>".($oMOR->fltStatus==MBRORDER_STATUS_FILLED ? "Filled" :
             ($oMOR->fltStatus==MBRORDER_STATUS_CANCELLED ? "Cancelled" : "Pending"))
     ." Orders</h3>";

if( $oMOR->fltStatus ) {
    // Filled or Cancelled
    $cond = "(eStatus='".$oMOR->fltStatus."') AND ".getYearCond( $oMOR->fltYear );
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

    if( $kfr->value('eStatus') == MBRORDER_STATUS_PAID ) {
        $style = "style='color:green;background-color:#efe'";
    } else {
        $style = "";
    }

    $sSummary = $oOrder->conciseSummary( $kfr->Key() );     // this also computes $oOrder->raOrder for details
// kluge to make the membership labels easier to differentiate
$sSummary = str_replace( "One Year Membership with on-line Seed Directory", "One Year Membership", $sSummary );
$sSummary = str_replace( "One Year Membership with printed and on-line Seed Directory", "One Year Membership with printed Seed Directory", $sSummary );

    $ra = SEEDCore_ParmsURL2RA( $kfr->value('sExtra') );
    $to = @$ra['mbrid'] ?: $kfr->value('mail_email');
    $sOnClick =  "window.open(\"../int/emailme.php?to=$to\",\"_blank\",\"width=900,height=800,scrollbars=yes\")";

    $s .= "<tr>"
          // Order #
         ."<td valign='top'>"
         .$kfr->Expand( "<a href='".$_SERVER['PHP_SELF']."?row=[[_key]]'>[[_key]]</a>" )
         ."<br/><br/>"
         ."<div><form action='http://seeds.ca/office/mbr/mbr_labels.php' target='MbrLabels' method='get'>"
             ."<input type='hidden' name='orderadd' value='".$kfr->Key()."'/><input type='submit' value='Add to Label Maker'/>"
         ."</form></div>"
         .$kfr->Expand( "<div class='mbrOrderShowTicket' data-kOrder='[[_key]]' data-expanded='0'><a href='#'>Show Ticket</a></div>" )
         ."</td>"
          // Name
         ."<td valign='top' $style>"
         .$kfr->Expand( "[[mail_firstname]] [[mail_lastname]]<br/>[[mail_company]]<br/>" )
         .$kfr->ExpandIfNotEmpty( 'pp_name', "([[]] on credit card)<br/>" )
         ."</td>"
          // Address / Phone Email
         ."<td valign='top'>"
         .$kfr->Expand( "[[mail_addr]]<br/>[[mail_city]] [[mail_prov]] [[mail_postcode]]" )
         .($kfr->Value('mail_country') != 'Canada' ? ("<br/>".$kfr->Value('mail_country')) : "" )
         ."<br/>"
         ."<br/>"
         .$kfr->Expand( "[[mail_phone]]<br/>[[mail_email]]" )
         ."<div><a href='#' onclick='$sOnClick'>Send Email</a></div>"
         ."</td>"
          // Language / eBulletin
         ."<td valign='top'>"
         .($kfr->value('mail_lang') ? 'French' : 'En')
         ."<br/><br/>"
         .($kfr->value('mail_eBull') ? "<span style='color:green'>Y</span>" : "<span style='color:red'>N</span>")
         ."</td>"
          // Order
         ."<td valign='top'>"
         .$sSummary
         .officeMailed( $kfr, $oOrder )
         ."</td>"
          // Payment
         ."<td valign='top' $style>"
         .SEEDCore_Dollar($kfr->value('pay_total'))." by ".$kfr->value('ePayType')."<br/>"
         //."<b>".@$mbr_PayStatus[$kfr->value('pay_status')]."</b><br/>"
         ."<b>".$kfr->value('eStatus')."</b>"
         .($kfr->value('eStatus')=='New' ? changeToPaidButton($kfr->Key()): "")
         .officeFilled( $kfr, $oOrder )
         ."<br/><br/>".substr($kfr->value('_created'),0,10)
         ."</td>"
         ."</tr>";
}
$s .= "</table>";




$s .= <<<MbrOrderScript
<script>
$(document).ready( function() {
    $('.mbrOrderShowTicket').click( function (e) {
        let t = $(this);
        let k = $(this).attr( 'data-kOrder' );
        let x = $(this).attr( 'data-expanded' );

        e.preventDefault();

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
});

function     FormValInt( k )   { return( parseInt(k) || 0 ); }

</script>
MbrOrderScript;

echo Console01Static::HTMLPage( $s, "", 'EN', array( 'sCharset'=>'cp1252', 'bBodyMargin'=>true,
                                                     'raScriptFiles' => array( W_ROOT."std/js/SEEDStd.js" )
) );


function getYearCond( $y )
{
    if( !$y )  return( "1=1" );

    if( $y <= 2010 )  return( "year(_created) <= '2010'" );

    return( "year(_created)='$y'" );
}


function changeToPaidButton( $kOrder )
{
    $s = "";

    $s .= "<form method='post' action='".Site_path_self()."'>"
         ."<input type='hidden' name='row' value='$kOrder'/>"
         ."<input type='hidden' name='action' value='changeStatusToPaid'/>"
         ."<input type='submit' value='Change to Paid'/>"
         ."</form>";

    return( $s );
}


function officeFilled( KFRecord $kfr, MbrOrder $oOrder )
{
    $s = "";

    $uid = $kfr->kfrel->uid;
    $bMailed = $kfr->Value( 'eStatus2' )==1;
    $bNothingToMail = $kfr->Value( 'eStatus2' )==2;

    if( !in_array( $uid, array( 1, 1499, 10914 ) ) ) {
        if( count($oOrder->raOrder['pubs']) ) {
            $s .= "<br/>Order ".($bMailed ? "": "not")." mailed ".$kfr->Value('dMailed');
        }
    } else if( !$bNothingToMail ) {
        $s .= "<br/>Order ".($bMailed ? "": "not")." mailed ".($bMailed ? $kfr->Value('dMailed') : "");
    }

    return( "<div id='mailed".$kfr->Key()."'>$s</div>" );
}


function officeMailed( KFRecord $kfr, MbrOrder $oOrder )
{
    $s = "";

    $uid = $kfr->kfrel->uid;
    $bFilled = $kfr->Value( 'eStatus2' ) != 0;

    if( !in_array( $uid, array( 1, 1499, 10914 ) ) ) {
        if( count($oOrder->raOrder['pubs']) ) {
//            $s = "<br/>Order ".($bFilled ? "": "not")." mailed";
        }
    } else {
        if( $bFilled ) {
//            $s .= "<br/>Order mailed ".$kfr->Value('dMailed');
        } else {
            $kOrder = $kfr->Key();
            $s .= "<div id='status2_$kOrder' class='status2'><button>Mailed Today</button></div>&nbsp;";
            $s .= "<div id='status2x_$kOrder' class='status2x'><button>Nothing to mail</button></div>";
        }
    }

    return( $s );
}


function mbr_header_style()
/**************************
 */
{
    ?>
    <style type='text/css'>
        body, p, input, td, th
                              { font-family: verdana,arial,helvetica,sans-serif;
                                font-size: 10pt;
                              }
        #mbr_form1            { font-size:x-small;
                              }
        #mbr_form1 h3         { color: green;
                              }

        .mbr_form1col_order   {
                              }

        .mbr_form1col_contactinfo {
                                border-left: medium ridge #CCCCCC;
                                border-bottom: medium ridge #CCCCCC;
                                padding-left: 2em;
                                padding-bottom: 2em;
                              }
        .mbr_form_box         {
                              }

        .mbr_form_boxheader   { background-color:#AAAAAA;  color: white; text-align:center; padding:4px; font-weight:bold; font-size:11pt;
                              }
        .mbr_form_boxbody     { background-color:#EEEEEE; padding:4px;
                              }
        .mbr_form_boxbody td, .mbr_form_boxbody input
                              { font-size:9pt; font-family: verdana,helvetica,sans-serif;
                              }
        .mbr_form_help        { font-size:8pt; margin-bottom:1em;
                              }
        .mbr_form_help p      { font-size:8pt;
                              }
        .form_sect_title      { font-size: medium;
                                font-weight: bold;
                                color:green;
                              }
        .form_sect_body       { font-size: medium;
                                margin-left: 3em;
                              }
        .form_sect_help       { font-size:8pt; }

        .instructions h3      { color:green; }
        .form_items           { font-size:12px; }
        .form_items_small     { font-size:9px; }

        #table_mbr2           { border-width: 1px 1px 1px 1px;
                                border: grey solid thin;
                              }
        #table_mbr2 th        { color:white;
                                background-color:green;

                              }
        #table_mbr2 td        { border: grey solid thin;
                                padding: 3px 3px 3px 3px;
                              }

    </style>
    <?php
}

?>

<script>

$(document).ready(function() {
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
            $('#status2x_'+thisId).html("");			// remove the other button
            $("#mailed"+thisId).html(o['sOut']);    // "Order not mailed" changes to "Order mailed YYYY-MM-DD"
        }
    });

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
            $('#status2_'+thisId).html("");			// remove the other button
            $("#mailed"+thisId).html("");    // "Order not mailed" changes to ""
        }
    });
});

</script>
