<?php

/*
alter table mbr_order_pending add bDoneAccounting integer not null default 0;
alter table mbr_order_pending add bDoneRecording integer not null default 0;
update mbr_order_pending set bDoneAccounting=1,bDoneRecording=1 where _key<=17967;
 */

// todo: flag unpaid entries that have later entries (paid or unpaid) with the same name | address | phone | email

define("SITEROOT", "../../");
include_once( SITEROOT."site2.php" );
include_once( STDINC."SEEDLocal.php" );
include_once( SEEDCOMMON."mbr/mbrOrder.php" );
include_once( SEEDAPP."basket/sodBasketFulfil.php" );
include_once( SEEDLIB."mbr/QServerMbr.php" );

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
        return( $kfr2 ? $this->DrawOrderSummaryRow( $kfr2, $sConciseSummary, $oOrder->raOrder ) : "" );
    }

    function statusForm( KeyframeRecord $kfrOrder )
    {
        $row = $kfrOrder->Key();

// this part has to be modernized before moving this method to SodOrderFulfil
        $oMbrOrder = new MbrOrder( $this->kfdb, "EN", $row );
        $sCol1 = $oMbrOrder->DrawTicket();
        $sCol2 = "";

        $s = "";

        $raMbr = [];
        if( ($kMbr = $kfrOrder->UrlParmGet('sExtra','mbrid')) ) {
            $oMbr = new QServerMbr( $this->oApp, ['config_bUTF8'=>false] ); // !utf8 because this whole form gets utf8-encoded at the end
            $rQ = $oMbr->Cmd('mbr-getOffice',['kMbr'=>$kMbr]);
            if( $rQ['bOk'] ) {
                $raMbr = $rQ['raOut'];
            }
        }

        if( $this->bCanWrite ) {
            /* Draw the header for the status form
             */
            switch( $kfrOrder->value('eStatus') ) {
                case MBRORDER_STATUS_FILLED:     $sState = "Filled";                    $raActions = ['Change to Pending'];  break;
                case MBRORDER_STATUS_CANCELLED:  $sState = "Cancelled";                 $raActions = ['Change to Pending'];  break;
                case MBRORDER_STATUS_PAID:       $sState = "paid, needs to be filled";  $raActions = ['Fill','Cancel'];      break;
                case MBRORDER_STATUS_NEW:        $sState = "awaiting payment";          $raActions = ['Fill','Cancel'];      break;
                default:
                    die( "<h3><font color='red'>Undefined payment status.  Inform Bob immediately, with the order number ($row).</font></h3>" );
            }
            $sCol2 = "<h3>This order is $sState - last update ".$kfrOrder->value("_updated")."</h3>";

            /* This tool manages the eStatus of the order, independently of the rest of this form
             */
            $sCol2 .= $this->drawStatusFormEStatus( $row, $raActions );

            $sCol2 .= "<hr style='border-color:#aaa;margin:30px 0px'/>";

            /* This tool controls the kMbr of the order, independently of the rest of this form
             */
            $sCol2 .= "<h4>Contact in database</h4>".$this->drawStatusFormMbrSelect( $kfrOrder, $raMbr );

            if( $kMbr ) {
                $sCol2 .= "<hr style='border-color:#aaa;margin:30px 0px'/>";

                /* This tool matches the MbrOrder information with the MbrContacts record
                 */
                $sCol2 .= $this->drawStatusFormContactData( $kfrOrder, $raMbr );
            }
        }

        $s .= "<div class='container-fluid'><div class='row'>"
                 ."<div class='col-sm-6'>$sCol1</div>"
                 ."<div class='col-sm-6'>$sCol2</div>"
             ."</div></div>";

        return( $s );
    }

    private function drawStatusFormEStatus( $row, $raActions )
    {
        // The eStatus-changing buttons (Fill, Cancel, Pending) will pick up the note via JS, but since only the Add Note
        // button is in a <form> it is the only one that will be activated by hitting enter in the input control. We assume
        // that you might do this when adding a note but you might not intend to change the eStatus
        $s = "<div class='statusForm'>";
        foreach( $raActions as $sAction ) {
            $s .= "<button onclick='doSubmitStatus(\"$sAction\", $row, ".'$(this)'.")'>$sAction</button>"
                     ."&nbsp;&nbsp;&nbsp;";
        }
        $s .= "<div style='margin-top:15px'>"
                 ."<form onsubmit='return false;'>"
                 ."<button onclick='doSubmitStatus(\"Add Note\", $row, ".'$(this)'.")'>Add Note</button>"
                 ."&nbsp;<input type='text' size='50' id='action_note'/>"
                 ."</form>"
                 ."</div>"
                 ."</div>";
        return( $s );
    }

    private function drawStatusFormMbrSelect( KeyframeRecord $kfrOrder, $raMbr )
    {
        $kOrder = $kfrOrder->Key();
        $oForm = new SEEDCoreForm('A');
        $s = "<div class='mbroMbrSelect' style='position:relative'>"
            ."<span id='mbr-label'>".(@$raMbr['_key'] ? SEEDCore_ArrayExpand($raMbr, "[[firstname]] [[lastname]] in [[city]] ([[_key]])") : "")."</span>"
            ."&nbsp;&nbsp;"
            .$oForm->Text( 'dummy_kMbr', '', ['size'=>10, 'attrs'=>"placeholder='Search'"] )
            ."&nbsp;&nbsp;"
            ."<button onclick='doMbrSelect(".'$(this)'.",$kOrder)'>".(@$raMbr['_key'] ? "Change" : "Select")." Contact</button>"
            .$oForm->Hidden('kMbr')
            ."</div>";

        $urlQ = SITEROOT_URL."app/q/q2.php";    // same as q/index.php but authenticates on seeds2

        $s .= "<script>
               function setupMbrSelector() {
               // 'o' is not used anywhere; this just sets up the MbrSelector control to run independently
               let oMS = new MbrSelector( { urlQ:'".$urlQ."',
                                            idTxtSearch:'sfAp_dummy_kMbr',
                                            idOutReport:'mbr-label',
                                            idOutKey:'sfAp_kMbr' } );
               }
               setupMbrSelector();
               </script>";

        return( $s );
    }

    private function drawStatusFormContactData( KeyframeRecord $kfrOrder, $raMbr )
    {
        $s = "";

        $oForm = new SEEDCoreForm('M');
        foreach( $raMbr as $k => $v ) { $oForm->SetValue( $k, $v ); }
        $oDFC = new drawFormContact( $oForm, $kfrOrder->ValuesRA(), $raMbr );
        $s .= "<form class='mbroContactForm' onsubmit='return(false);'>" // accept-charset='ISO-8859-1'>"
             ."<div>".$oDFC->DrawItem('firstname')." ".$oDFC->DrawItem('lastname')."</div>"
             ."<div>".$oDFC->DrawItem('firstname2')." ".$oDFC->DrawItem('lastname2')."</div>"
             ."<div>".$oDFC->DrawItem('company')." ".$oDFC->DrawItem('dept')."</div>"
             ."<div>".$oDFC->DrawItem('address')." ".$oDFC->DrawItem('city')." ".$oDFC->DrawItem('province')."<div>"
             ."<div>".$oDFC->DrawItem('postcode')." ".$oDFC->DrawItem('country')."</div>"
             ."<div>".$oDFC->DrawItem('email')." ".$oDFC->DrawItem('phone')."</div>"
             ."<div>&nbsp;</div>"
             ."<div>".$oDFC->DrawItem('lang')."</div>"
             ."<div>".$oDFC->DrawItem('referral')." Referral</div>"
             ."<div>".$oDFC->DrawItem('expires')." Expires</div>"
             ."<div>".$oDFC->DrawItem('lastrenew')." Last Renewal</div>"
             ."<div>".$oDFC->DrawItem('startdate')." Start Date</div>"
             ."<div>".$oDFC->DrawItem('bNoEBull')." No E-bulletin</div>"
             ."<div>".$oDFC->DrawItem('bNoDonorAppeals')." No Donor Appeals</div>"
//             ."<div>".$oDFC->DrawItem('bNoSED')." Online MSD</div>"
             ."<div>".$oDFC->DrawItem('bPrintedMSD')." Printed MSD</div>"

             ."<button onclick='doContactFormSubmit(".'$(this)'.",${raMbr['_key']},".$kfrOrder->Key()." )'>Save</button>"
             ."</form>"
             ."<div class='mbroContactForm_feedback'></div>";

        return( $s );
    }
}

class drawFormContact
{
    private $oForm;
    private $raOrder;
    private $raMbr;

    function __construct( SEEDCoreForm $oForm, $raOrder, $raMbr )
    {
        $this->oForm = $oForm;
        $this->raOrder = $raOrder;
        $this->raMbr = $raMbr;
    }

    private $raItems = [
        'firstname'  => ['First name',   'mail_firstname', 'firstname'],
        'lastname'   => ['Last name',    'mail_lastname',  'lastname'],
        'firstname2' => ['First name 2', 'mail_firstname', 'firstname2'],
        'lastname2'  => ['Last name 2',  'mail_lastname',  'lastname2'],
        'company'    => ['Company',      'mail_company',   'company'],
        'dept'       => ['Dept',         '',               'dept'],
        'address'    => ['Address',      'mail_addr',      'address'],
        'city'       => ['City',         'mail_city',      'city'],
        'province'   => ['Province',     'mail_prov',      'province', 5],
        'postcode'   => ['Postal code',  'mail_postcode',  'postcode'],
        'country'    => ['Country',      'mail_country',   'country'],
        'email'      => ['Email',        'mail_email',     'email'],
        'phone'      => ['Phone',        'mail_phone',     'phone'],
        'lang'       => ['Language',     '',               'lang'],
        'referral'   => ['Referral',     '',               'referral'],
        'expires'       => ['Expires',     '',               'expires'],
        'lastrenew'       => ['Last Renewal',     '',               'lastrenew'],
        'startdate'       => ['Start Date',     '',               'startdate'],
        'bNoEBull'   => ['No E-bulletin','',               'bNoEBull', 3],
        'bNoDonorAppeals' => ['No Donor Appeals',     '',               'bNoDonorAppeals', 3],
//        'bNoSED'       => ['Online MSD',     '',               'bNoSED', 3],
        'bPrintedMSD'       => ['Printed MSD',     '',               'bPrintedMSD', 3],
    ];

    function GetItems() { return($this->raItems); }
    public function DrawItem( $fld )
    {
        $placeholder = $this->raItems[$fld][0];
        $valOrder = @$this->raOrder[$this->raItems[$fld][1]];
        $valMbr = @$this->raMbr[$this->raItems[$fld][2]];

        $ra = ['attrs'=>"placeholder='$placeholder'"];
        if( @$this->raItems[$fld][3] ) { $ra['size'] = $this->raItems[$fld][3]; }
        if( $valMbr && $valOrder == $valMbr ) {
            // disable the control if it is not blank and it matches the value in the order form (if blank we might want to enter something there)
            //$ra['disabledAddHidden'] = 1;   // disabled controls look right but don't report values; this appends a hidden element too
            $ra['disabled'] = 1;              // $().find() reads values of disabled controls though
        }
        $s = $this->oForm->Text( $fld, "", $ra );

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
        case 'doAccountingDone':
            $kfr2->SetValue('bDoneAccounting', 1);
            $rQ['bOk'] = $kfr2->PutDBRow();
            break;
        case 'doRecordingDone':
            $kfr2->SetValue('bDoneRecording', 1);
            $rQ['bOk'] = $kfr2->PutDBRow();
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
            $rQ['bOk'] = true;
            break;
        case 'doSetMbrKey':
            if( ($kMbr = SEEDInput_Int('kMbr')) ) {
                $kfr2->UrlParmSet( 'sExtra', 'mbrid', $kMbr );
                $rQ['bOk'] = $kfr2->PutDBRow();
            }
            break;
        case 'doContactFormSubmit':
            if( ($kMbr = SEEDInput_Int('kMbr')) ) {
                $oQ = new QServerMbr( $oApp, ['config_bUTF8'=>true] );
                $rM = $oQ->Cmd('mbr-getFldsOffice');
                $raFlds = $rM['raOut'];

                $raMbr = ['kMbr'=>$kMbr];
                $oForm = new SEEDCoreForm('M');
                $oForm->Load();
//$x = $oForm->GetValuesRA();
//$x = $_REQUEST;
//$oApp->Log('tmp',SEEDCore_ArrayExpandSeries( $x, "[[k]] = [[v]]\n") );

                foreach( $raFlds as $k => $raDummy ) {
                    $raMbr[$k] = $oForm->Value($k);
                }
                $rM = $oQ->Cmd('mbr--putOffice', $raMbr);
                $rQ['bOk'] = $rM['bOk'];
                $rQ['sErr'] = $rM['sErr'];
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
    $s .= utf8_encode($oUI->drawRow($kfr->Key()));
}
$s .= "</table>";


$s .= mbrSearchJS();

$raConsoleParms = [
    'sCharset'=>'utf-8', //'ISO-8859-1',
    'bBodyMargin'=>true,
    'raScriptFiles' => [ W_ROOT."std/js/SEEDStd.js",W_CORE."js/SEEDCore.js", W_CORE."js/SFUTextComplete.js", W_CORE."js/MbrSelector.js" ]
];

echo Console01Static::HTMLPage( $s, "", 'EN', $raConsoleParms );


function mbrSearchJS()
{
    $urlQ = SITEROOT_URL."app/q/q2.php";    // same as q/index.php but authenticates on seeds2

    $s = "<script>
$(document).ready( function() {
    // 'o' is not used anywhere; this just sets up the MbrSelector control to run independently
    let o = new MbrSelector( { urlQ:'".$urlQ."', idTxtSearch:'sfAp_dummy_kMbr', idOutReport:'vol-label', idOutKey:'sfAp_vol_kMbr' } );
});
</script>";

    return( $s );
}

?>

<script>

function FormValInt( k )   { return( parseInt(k) || 0 ); }

class MbrOrderFulfil
{
    constructor()
    {
    }

    static MailToday( k )
    {
        if( !k ) return;

        let jxData = { jx   : 'changeStatus2ToMailed',
                       k    : k,
                       lang : "EN"
                     };

        SEEDJXAsync2( "mbr_order.php", jxData, function(o) {
                if( o['bOk'] ) {
                    $('#status2x_'+k).html("");        // remove the Mail Nothing button
                    $("#status2_"+k).html(o['sOut']);  // Mail Today button changes to "Order mailed YYYY-MM-DD"
                }
            });
    }

    static MailNothing( k )
    {
        let jxData = { jx   : 'changeStatus2ToNothingToMail',
                       k    : k,
                       lang : "EN"
                     };

        let o = SEEDJX( "mbr_order.php", jxData );
        if( o['bOk'] ) {
            $("#status2x_"+k).html(""); // remove the Mail Nothing button
            $('#status2_'+k).html("");  // remove the Mail Today button
        }

    }
}

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
        MbrOrderFulfil.MailToday( this.id.substr(8) )
    });

    /* Nothing to Mail button click
     */
    $(".status2x").click(function(event){
        event.preventDefault();
        MbrOrderFulfil.MailNothing( this.id.substr(9) )
    });

    /* Accounting Done button
     */
    $(".doAccountingDone").click(function(event){
        event.preventDefault();
        let k = $(this).attr('data-kOrder');

        let jxData = { jx   : 'doAccountingDone',
                       k    : k,
                       lang : "EN"
                     };

        let o = SEEDJX( "mbr_order.php", jxData );
        if( o['bOk'] ) {
// better to get html from o['sOut'] for true confirmation
            $(this).html("Bookkeeping done");
        }
    });

    /* Recording Done button
     */
    $(".doRecordingDone").click(function(event){
        event.preventDefault();
        let k = $(this).attr('data-kOrder');

        let jxData = { jx   : 'doRecordingDone',
                       k    : k,
                       lang : "EN"
                     };

        let o = SEEDJX( "mbr_order.php", jxData );
        if( o['bOk'] ) {
// better to get html from o['sOut'] for true confirmation
            $(this).html("Database record done");
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
        fillTmpRowDiv( tmpTr.find(".tmpRowDiv"), k, "" );
        t.attr( 'data-expanded', 1 );
    }
}

function fillTmpRowDiv( tmpRowDiv, kOrder, feedback )
{
    $.get( 'mbr_order.php',
            "jx=drawStatusForm&k="+kOrder,
            function (data) {
                let d = SEEDJX_ParseJSON( data );
                //console.log(d);
                if( d['bOk'] ) {
                    tmpRowDiv.html( d['sOut'] );
                    tmpRowDiv.find('.mbroContactForm_feedback').html(feedback).show().delay(5000).fadeOut();
                }
     });
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
    fillTmpRowDiv( tmpRowDiv, kRow, "" );

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

function doMbrSelect( jButton, kOrder )
{
    let jContainer = jButton.closest(".mbroMbrSelect");
    let kMbr = jContainer.find('#sfAp_kMbr').val();

    if( kMbr ) {
        let jxData = { jx   : 'doSetMbrKey',
                       k    : kOrder,
                       kMbr : kMbr
                     };
        o = SEEDJX( "mbr_order.php", jxData );

        let tmpRowDiv = jButton.closest(".tmpRowDiv");
        fillTmpRowDiv( tmpRowDiv, kOrder, "" );
    }

    return( false );
}

function doContactFormSubmit( jButton, kMbr, kOrder )
{
    let jContactForm = jButton.closest(".mbroContactForm");
    let jxData = { jx    : 'doContactFormSubmit',
                   k     : kOrder,
                   kMbr  : kMbr,
                 };
    jContactForm.find('select, textarea, input').each( function() {
        jxData[$(this).attr('id')] = $(this).val();
    });
    //console.log(jxData);
    o = SEEDJX( "mbr_order.php", jxData );
    let feedback = o['bOk'] ? "<div class='alert alert-success' style='font-size:small; margin-top:5px;padding:5px;width:5em;text-align:center'>Saved</div>"
                            : ("<div class='alert alert-danger' style='font-size:small'>Error:"+o['sErr']+"</div>");

    // replace the statusForm with its new state
    let tmpRowDiv = jButton.closest(".tmpRowDiv");
    fillTmpRowDiv( tmpRowDiv, kOrder, feedback );
}

</script>
