<?php

// todo: flag unpaid entries that have later entries (paid or unpaid) with the same name | address | phone | email

define("SITEROOT", "../../");
include_once( SITEROOT."site2.php" );
include_once( STDINC."SEEDLocal.php" );
include_once( SEEDCOMMON."mbr/mbrOrder.php" );
include_once( SEEDAPP."basket/sodBasketFulfil.php" );


// kfdb is seeds2
list($kfdb, $sess) = SiteStartSessionAccount( array("R MBRORDER") );

$oApp = SiteAppConsole( ['db'=>'seeds2', 'sessPermsRequired'=>['R MBRORDER'] ] );

define( "MBR_ADMIN", "1" ); // DrawTicket shows all the internal stuff



// move stuff to SodOrderFulfilUI from here
class mbrOrderFulfilUI extends SodOrderFulfilUI
{
    private $kfdb;
    //private $sess;
    public $bCanWrite = false;

    function __construct( KeyFrameDB $kfdb, $sess, SEEDAppConsole $oApp )
    {
        parent::__construct( $oApp );
        $this->kfdb = $kfdb;
        $this->bCanWrite = $oApp->sess->CanWrite('MBRORDER');
    }

    function drawRow( $k )
    {
        $oOrder = new MbrOrder( $this->kfdb, "EN", $k );
        $sConciseSummary = $oOrder->conciseSummary( $k );     // this also computes $oOrder->raOrder for DrawOrderSummaryRow()
        $kfr2 = $this->KfrelOrder()->GetRecordFromDBKey( $k );
        return( $this->DrawOrderSummaryRow( $kfr2, $sConciseSummary, $oOrder->raOrder ) );
    }

    function statusForm( KeyframeRecord $kfr )
    {
        $row = $kfr->Key();

// this part has to be modernized before moving this method to SodOrderFulfil
        $oMbrOrder = new MbrOrder( $this->kfdb, "EN", $row );
        $sCol1 = $oMbrOrder->DrawTicket();
        $sCol2 = "";

        $s = "";

        if( $this->bCanWrite ) {
            /* Draw the header for the ticket and controls for changing the order's status
             */
            switch( $kfr->value('eStatus') ) {
                case MBRORDER_STATUS_FILLED:     $sState = "Filled";                    $raActions = ['Change to Pending'];  break;
                case MBRORDER_STATUS_CANCELLED:  $sState = "Cancelled";                 $raActions = ['Change to Pending'];  break;
                case MBRORDER_STATUS_PAID:       $sState = "paid, needs to be filled";  $raActions = ['Fill','Cancel'];      break;
                case MBRORDER_STATUS_NEW:        $sState = "awaiting payment";          $raActions = ['Fill','Cancel'];      break;
                default:
                    die( "<h3><font color='red'>Undefined payment status.  Inform Bob immediately, with the order number ($row).</font></h3>" );
            }
            $sCol2 = "<h3>This order is $sState - last update ".$kfr->value("_updated")."</h3>";

            // The eStatus-changing buttons (Fill, Cancel, Pending) will pick up the note via JS, but since only the Add Note
            // button is in a <form> it is the only one that will be activated by hitting enter in the input control. We assume
            // that you might do this when adding a note but you might not intend to change the eStatus
            $sCol2 = "<div class='statusForm'>";
            foreach( $raActions as $sAction ) {
                $sCol2 .= "<button onclick='doSubmitStatus(\"$sAction\", $row, ".'$(this)'.")'>$sAction</button>"
                         ."&nbsp;&nbsp;&nbsp;";
            }
            $sCol2 .= "<div style='margin-top:15px'>"
                     ."<form onsubmit='return false;'>"
                     ."<button onclick='doSubmitStatus(\"Add Note\", $row, ".'$(this)'.")'>Add Note</button>"
                     ."&nbsp;<input type='text' size='50' id='action_note'/>"
                     ."</form>"
                     ."</div>"
                     ."</div>";
        }

        $s .= "<div class='container-fluid'><div class='row'>"
                 ."<div class='col-sm-6'>$sCol1</div>"
                 ."<div class='col-sm-6'>$sCol2</div>"
             ."</div></div>";

        return( $s );
    }

}

$oUI = new mbrOrderFulfilUI( $kfdb, $sess, $oApp );


$oOrder = new MbrOrderCommon( $kfdb, "EN", $sess->GetUID() );
$kfrel = $oOrder->kfrelOrder;

if( ($jx = SEEDInput_Str('jx')) ) {
    $rQ = ['bOk'=>false, 'sOut'=>"", 'sErr'=>""];

    if( !($k = SEEDInput_Int('k')) ||
        !($kfr = $kfrel->GetRecordFromDBKey( $k )) ||
        !($kfr2 = $oUI->KfrelOrder()->GetRecordFromDBKey( $k )) )
    {
        $rQ['sErr'] = "Couldn't load $k";
        goto jxDone;
    }

    /* Readonly commands
     */
    switch( $jx ) {
/*
        case 'drawTicket':
            if( ($id = intval(@$_REQUEST['id'])) ) {
                if( !($kfr = $oUI->KfrelOrder()->GetRecordFromDBKey( $id )) ) { $rQ['sErr'] = "Couldn't load $id"; goto jxDone; }

                $oMbrOrder = new MbrOrder( $kfdb, "EN", $id );
                $rQ['sOut'] = utf8_encode($oMbrOrder->DrawTicket());
                $rQ['bOk'] = true;

                header( "Content-Type:text/html; charset=utf8" );
            }
            break;
*/
        case 'drawStatusForm':
            $rQ['sOut'] = utf8_encode($oUI->statusForm( $kfr2 ));
            $rQ['bOk'] = true;
            header( "Content-Type:text/html; charset=utf8" );
            break;
        case 'drawOrderSummaryRow':
            $rQ['sOut'] = utf8_encode($oUI->drawRow($k));
            $rQ['bOk'] = true;
            header( "Content-Type:text/html; charset=utf8" );
            break;
    }

    if( !$oUI->bCanWrite )  goto jxDone;

    /* Write commands
     */
    switch( $jx ) {
        case 'changeStatus2ToMailed':
            if( !$oUI->SetMailedToday( $kfr2 ) ) { $rQ['sErr'] = "Couldn't store"; goto jxDone; }
            $rQ['sOut'] = "Order mailed ".$kfr2->Value('dMailed');
            $rQ['bOk'] = true;
            break;
        case 'changeStatus2ToNothingToMail':
            if( !$oUI->SetMailedNothing( $kfr2 ) ) { $rQ['sErr'] = "Couldn't store"; goto jxDone; }
            $rQ['sOut'] = "";
            $rQ['bOk'] = true;
            break;
        case 'doBuildBasket':
            $o = new SoDOrder_MbrOrder( $oApp );
            $o->CreateFromMbrOrder( $k );
            $rQ['sOut'] = "";
            $rQ['bOk'] = true;
            break;
        case 'doSubmitStatus':
            $sAction = SEEDInput_Str('action');
            $sNote   = SEEDInput_Str('note');
            doSubmitForm( $kfr, $sAction, $sNote );
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
/*
if( ($row = $oUI->GetCurrOrderKey()) ) {
    $kfr = $kfrel->GetRecordFromDBKey( $row );

    $kfr2 = $oUI->KfrelOrder()->GetRecordFromDBKey( $row );


    if( $bCanWrite ) {
        $action = $oUI->pAction;
        $action_notes = SEEDSafeGPC_GetStrPlain('action_note');
        doSubmitForm( $kfr, $action, $action_notes );
    }
}
*/

function doSubmitForm( $kfr, $action, $action_notes )
{
    global $sess;

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

/* Filter Form
 */
$s .= $oUI->DrawFormFilters();

//if( $kfr ) {
//    $s .= $oUI->statusForm( $kfr, $bCanWrite );
//}



/* Fetch table of orders
 */
list($fltLabel,$fltCond,$fltSortDown) = $oUI->GetFilterDetails();

$s .= "<h3>$fltLabel Orders</h3>";

$kfr = $kfrel->CreateRecordCursor( $fltCond, array('sSortCol'=>'_key','bSortDown'=>$fltSortDown) );


/* Draw table of orders
 */
$s .= "<table border='1' width='100%' cellpadding='2' style='border-collapse:collapse'><tr>"
     ."<th>Order #</th>"
     ."<th>Name</th>"
     ."<th>Address<br/>Phone/Email</th>"
     ."<th>Language<br/>eBulletin</th>"
     ."<th>Order</th>"
     ."<th>Payment</th>"
     ."<th>Fulfilment</th>"
     ."</tr>";

while( $kfr->CursorFetch() ) {
    $s .= $oUI->drawRow($kfr->Key());
}
$s .= "</table>";

$s .= mbrSearchJS();


echo Console01Static::HTMLPage( $s, "", 'EN', [ 'sCharset'=>'cp1252', 'bBodyMargin'=>true,
                                                'raScriptFiles' => [ W_ROOT."std/js/SEEDStd.js",W_CORE."js/SEEDCore.js", W_CORE."js/SFUTextComplete.js" ]
] );



// same as ev_admin
function mbrSearchJS()
{
    $urlQ = SITEROOT_URL."app/q/q2.php";    // same as q/index.php but authenticates on seeds2

    $s = <<<volSearchJS
<script>
var urlQ = "$urlQ";
var cp1_pcvSearch = [];
SFU_TextCompleteVars['sfAp_dummy_kMbr'] = {
    'fnFillSelect' :
            function( sSearch ) {
                let raRet = [];

                let jxData = { qcmd    : 'mbr-search',
                               lang    : "EN",
                               sSearch : sSearch
                             };
                let o = SEEDJXSync( urlQ, jxData );console.log(o);
                if( !o || !o['bOk'] || !o['raOut'] ) {
                    alert( "Sorry there is a server problem" );
                } else {
                    //var bOk = o['bOk'];
                    //var sOut = o['sOut'];
                    for( let i = 0; i < o['raOut'].length; ++i ) {
                        r = o['raOut'][i];
                        raRet[i] = { val: r['_key'],
                                     label: r['firstname']+" "+r['lastname']+" ("+r['_key']+")" };
                    }
                    cp1_pcvSearch = o['raOut'];   // save this so we can look it up in fnSelectChoose
                }
                return( raRet );
            },
    'fnSelectChoose' :
            function( val ) {
                for( let i = 0; i < cp1_pcvSearch.length; ++i ) {
                    let r = cp1_pcvSearch[i];
                    if( r['_key'] == val ) {
                        $("#vol-label").html( r['firstname']+" "+r['lastname']+" ("+r['_key']+")"+" in "+r['city'] );
                        $("#sfAp_vol_kMbr").val( r['_key'] );
                        break;
                    }
                }
            }
};
</script>
volSearchJS;

    return( $s );
}

?>

<script>

function FormValInt( k )   { return( parseInt(k) || 0 ); }


$(document).ready(function() {
    /* Show Ticket click
     */
     $('.mbrOrderShowTicket').click( function (event) {
         event.preventDefault();
         initClickShowTicket( $(this) );
     });

    /* Mailed Today button click
     */
    $(".status2").click(function(event){
        event.preventDefault();
        let k = this.id.substr(8);

        let jxData = { jx   : 'changeStatus2ToMailed',
                       k    : k,
                       lang : "EN"
                     };

        let o = SEEDJX( "mbr_order.php", jxData );
        if( o['bOk'] ) {
            $(this).html("");
            $('#status2x_'+k).html("");        // remove the other button
            $("#status2_"+k).html(o['sOut']);  // "Order not mailed" changes to "Order mailed YYYY-MM-DD"
        }
    });

    /* Nothing to Mail button click
     */
    $(".status2x").click(function(event){
        event.preventDefault();
        let k = this.id.substr(9);

        let jxData = { jx   : 'changeStatus2ToNothingToMail',
                       k    : k,
                       lang : "EN"
                     };

        let o = SEEDJX( "mbr_order.php", jxData );
        if( o['bOk'] ) {
            $(this).html("");
            $('#status2_'+k).html("");  // remove the other button
            $("#mailed"+k).html("");    // "Order not mailed" changes to ""
        }
    });

    /* Build basket button click
     */
    $(".doBuildBasket").click(function(event){
        event.preventDefault();
        let k = $(this).attr('data-kOrder');

        jxData = { jx     : 'doBuildBasket',
                   k      : k,
                   lang   : "EN"
                 };

        o = SEEDJX( "mbr_order.php", jxData );
        if( o['bOk'] ) {
//            $(this).html("");
//            $('#status2_'+thisId).html("");  // remove the other button
//            $("#mailed"+thisId).html("");    // "Order not mailed" changes to ""
        }
    });

    /* Membership item click
     */
    $(".doShowMembershipForm").click(function(event){
        event.preventDefault();
        let tr = $(this).closest(".mbro-row");
        tr.after("<tr><td colspan='7'><div style='position:relative'>"
                +"<input type='text' name='sfAp_dummy_kMbr' id='sfAp_dummy_kMbr' size='10' class='SFU_TextComplete' placeholder='Search'/>"
                +"</div></td></tr>");
        //SFU_TextComplete_Init();    // activate the search control
    });
});


function initClickShowTicket( jDiv )
/***********************************
    Tell the Show Ticket link to open the status form
 */
{
    let t = jDiv;
    let k = t.attr( 'data-kOrder' );
    let x = t.attr( 'data-expanded' );

    if( FormValInt(x) ) {
        t.html( "Show Ticket" );
        t.attr( 'data-expanded', 0 );
        $(".mbro-tmp-row").remove();
        $(".mbrOrderShowTicket").attr( 'data-expanded', 0 );  // the line above removes all .mbro-tmp-row so mark them all unexpanded
    } else {
        // insert a temporary <tr> beneath the clicked row, where controls will be inserted into .tmpRowDiv
        let mbrTr = t.closest(".mbro-row");
        let tmpTr = $("<tr class='mbro-tmp-row'><td colspan='7'><div class='tmpRowDiv'></div></td></tr>");
        tmpTr.insertAfter(mbrTr);    // inserts into the dom after the current <tr> but keeps its object identity
        $.get( 'mbr_order.php',
               "jx=drawStatusForm&k="+k,
               function (data) {
                   let d = SEEDJX_ParseJSON( data );
                   //console.log(d);
                   if( d['bOk'] ) {
                       tmpTr.find(".tmpRowDiv").html( d['sOut'] );
                       t.attr( 'data-expanded', 1 );
                   }
        });
    }
}

function doSubmitStatus( sAction, kRow, jButton )
{
    let form = jButton.closest(".statusForm");
    let note = form.find('#action_note').val();

    // update the selected record
    let jxData = { jx     : 'doSubmitStatus',
                   k      : kRow,
                   action : sAction,
                   note   : note,
                   lang   : "EN"
             };
    o = SEEDJX( "mbr_order.php", jxData );

    // replace the statusForm with its new state
    let tmpRowDiv = jButton.closest(".tmpRowDiv");
    $.get( 'mbr_order.php',
           "jx=drawStatusForm&k="+kRow,
           function (data) {
               let d = SEEDJX_ParseJSON( data );
               //console.log(d);
               if( d['bOk'] ) {
                   tmpRowDiv.html( d['sOut'] );
               }
           } );

    // replace the previous <tr> with its new state
    let prevTr = tmpRowDiv.closest("tr").prev();
    $.get( 'mbr_order.php',
           "jx=drawOrderSummaryRow&k="+kRow,
           function (data) {
               let d = SEEDJX_ParseJSON( data );
               //console.log(d);
               if( d['bOk'] ) {
                   newTr = $(d['sOut']);
                   prevTr.replaceWith( newTr );
                   // rebind the Show Ticket link in the replaced <tr>
                   newTr.find('.mbrOrderShowTicket').click( function (event) {
                       event.preventDefault();
                       initClickShowTicket( $(this) );
                   });
                   // remind it that the submitForm is open
                   newTr.find('.mbrOrderShowTicket').attr('data-expanded',1);
               }
           });

    return( false );
}
</script>
