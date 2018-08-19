<?php

// todo: flag unpaid entries that have later entries (paid or unpaid) with the same name | address | phone | email

define("SITEROOT", "../");
include_once( SITEROOT."site.php" );
include_once( STDINC."SEEDLocal.php" );
include_once( SEEDCOMMON."mbr/mbrOrder.php" );
include_once( "_mbr_order_report.php" );

header( "Content-Type:text/html; charset=ISO-8859-1" );


list($kfdb, $sess) = SiteStartSessionAccount( array("MBRORDER" => "R") );
$bCanWrite = $sess->CanWrite('MBRORDER');

$oMOR = new MbrOrderReport( new SEEDApp_Worker( $kfdb, $sess, 'EN' ) );


define( "MBR_ADMIN", "1" ); // DrawTicket shows all the internal stuff


function dollar( $d, $lang = "EN" )     { return( SEEDStd_Dollar($d,$lang) ); }


$oOrder = new MbrOrderCommon( $kfdb, "EN", $sess->GetUID() );
$kfrel = $oOrder->kfrelOrder;

$s = "<style>"
    ."body, p, td, th { font-family:verdana,helvetica,sans serif; font-size:10pt; }"
    ."</style>";

$s .=  MbrOrderStyle();

$s .= "<table border='0' width='100%'><tr><td><h2>Online Order Summary</h2></td>"
     ."<td align='right'><a href='http://office.seeds.ca'>Home</a>&nbsp;&nbsp;&nbsp;<a href='mbr_order_stats.php'>Statistics</a></td></tr></table>";

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
    $s .= "<tr>"
          // Order #
         ."<td valign='top'>"
         .$kfr->Expand( "<a href='".$_SERVER['PHP_SELF']."?row=[[_key]]'>[[_key]]</a>" )
         ."<br/><br/>"
         ."<div><form action='http://seeds.ca/office/mbr/mbr_labels.php' target='MbrLabels' method='post'>"
             ."<input type='hidden' name='orderadd' value='".$kfr->Key()."'/><input type='submit' value='Add to Label Maker'/>"
         ."</form></div>"
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
         ."</td>"
          // Language / eBulletin
         ."<td valign='top'>"
         .($kfr->value('mail_lang') ? 'French' : 'En')
         ."<br/><br/>"
         .($kfr->value('mail_eBull') ? "<span style='color:green'>Y</span>" : "<span style='color:red'>N</span>")
         ."</td>"
          // Order
         ."<td valign='top'>"
         .$oOrder->conciseSummary( $kfr->Key() )
         ."</td>"
          // Payment
         ."<td valign='top' $style>"
         .dollar($kfr->value('pay_total'))." by ".$kfr->value('ePayType')."<br/>"
         //."<b>".@$mbr_PayStatus[$kfr->value('pay_status')]."</b><br/>"
         ."<b>".$kfr->value('eStatus')."</b>"
         .($kfr->value('eStatus')=='New' ? changeToPaidButton($kfr->Key()): "")
         ."<br/><br/>".substr($kfr->value('_created'),0,10)
         ."</td>"
         ."</tr>";
}
$s .= "</table>";

echo Console01Static::HTMLPage( $s, "", 'EN', array('sCharset'=>'cp1252', 'bBodyMargin'=>true) );


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



</body>
</html>
