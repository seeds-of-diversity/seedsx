<?php

// Weirdest thing: other apps send parms like addorder=12345; when POST those parms appear in the url like get, so script is used to clean that up.
// Somehow _REQUEST is empty if you do that. Using GET from other apps and script to clean it up.


/* Label maker for Contacts and Orders

    cmd=pdf                     : output the labels to pdf
    cmd=clear                   : clear the label list

    pdf: offset=n               : skip n label positions
    pdf: label_format=          : e.g. 5160

    orderadd=k                  : add order k
    orderadd[]=k1&orderadd[]=k2 : add orders k1 and k2
    orderdel=k                  : remove order k
    orderdel[]=k1&orderdel[]=k2 : remove orders k1 and k2

    mbradd=k                    : add mbr k
    mbradd[]=k1&mbradd[]=k2     : add mbrs k1 and k2
    mbrdel=k                    : remove mbr k
    mbrdel[]=k1&mbrdel[]=k2     : remove mbrs k1 and k2
 */

define( "SITEROOT", "../../" );
include_once( SITEROOT."site2.php" );
include_once( SEEDCOMMON."mbr/mbrCommon.php" ); // MbrDrawAddressBlock

list($kfdb,$sess,$lang) = SiteStartSessionAccount( ['R MBR', '&', 'R MBRORDER'] );   // both are required because both are revealed

if( @$_REQUEST['cmd'] == 'pdf' )  goto drawPDF_Labels;

/* UI for setting up labels
 */

//var_dump($_REQUEST);

$oSVA = new SEEDSessionVarAccessor( $sess, "MbrLabels" );

if( @$_REQUEST['cmd'] == 'clear' ) {
    $oSVA->VarUnSetAll();
}

if( ($p = @$_REQUEST['orderadd']) ) {
    $ra = $oSVA->VarGet( 'raOrders' );
    if( !is_array($ra) ) $ra = array();

    if( is_array($p) ) {
        foreach( $p as $k ) {
            $ra[] = $k;
        }
    } else if( ($p = intval($p)) ) {
         $ra[] = $p;
    }
    $oSVA->VarSet( 'raOrders', $ra );
}
if( ($p = @$_REQUEST['mbradd']) ) {
    $ra = $oSVA->VarGet( 'raMbrs' );
    if( !is_array($ra) ) $ra = array();

    if( is_array($p) ) {
        foreach( $p as $k ) {
            list($raRange,$sRange) = SEEDCore_ParseRangeStr( $k );
            $ra = array_merge( $ra, $raRange );
        }
    } else { // if( ($p = intval($p)) ) {
         list($raRange,$sRange) = SEEDCore_ParseRangeStr( $p );
         $ra = array_merge( $ra, $raRange );
    }
    $oSVA->VarSet( 'raMbrs', $ra );
}
if( ($p = @$_REQUEST['orderdel']) ) {
}
if( ($p = @$_REQUEST['mbrdel']) ) {
}


//var_dump(@$_SESSION['MbrLabels']);


$raOrders = $oSVA->VarGet( "raOrders" );
$raMbrs   = $oSVA->VarGet( "raMbrs" );

$sList = "<div>"
        ."<h4>Orders</h4>"
        .(is_array($raOrders) ? SEEDCore_ArrayExpandSeries( $raOrders, "[[]]<br/>" ) : "")
        ."</div><div>"
        ."<h4>Contacts</h4>"
        .(is_array($raMbrs) ? SEEDCore_ArrayExpandSeries( $raMbrs, "[[]]<br/>" ) : "")
        ."</div><div style='margin-top:10px'>"
        ."<form method='post'>"
        ."<input type='hidden' name='cmd' value='clear'/>"
        ."<input type='submit' value='Clear all'/>"
        ."</form>"
        ."</div>";

$sForm = "<div><form method='post'>"
        ."<input type='text' name='orderadd' /> <input type='submit' value='Add Order'/>"
        ."</form></div>"
        ."<div><form method='post'>"
        ."<input type='text' name='mbradd' /> <input type='submit' value='Add Contact'/>"
        ."</form></div>";

$sPrintButton =
    "<form method='post' target='MbrLabelsPDF'>"
   ."<input type='hidden' name='cmd' value='pdf'/>"
   .(is_array($raOrders) ? SEEDCore_ArrayExpandSeries( $raOrders, "<input type='hidden' name='orders[]' value='[[]]'/>" ) : "")
   .(is_array($raMbrs)   ? SEEDCore_ArrayExpandSeries( $raMbrs,   "<input type='hidden' name='mbrs[]'   value='[[]]'/>" ) : "")
   ."<div style='margin:5px'>"
   ."Skip # labels <input type='text' name='offset' />"
   ."</div>"
   ."<input type='submit' value='Format Labels'/>"
   ."</form>";


$s = "<div class='container'><div class='row'>"
    ."<div class='col-sm-4'>$sList</div>"
    ."<div class='col-sm-4'>$sForm</div>"
    ."<div class='col-sm-4'>$sPrintButton</div>"
    ;

echo Console01Static::HTMLPage( $s, "", "" );


// for some reason when other apps launch this page via a post with a target, the url keeps the parameters like a get
?>
<script>
var clean_uri = location.protocol + "//" + location.host + location.pathname;
window.history.replaceState({}, document.title, clean_uri);
</script>

<?php
exit;

drawPDF_Labels:

/* Draw the labels using PDF_Label
 *
 * $_REQUEST:  orders => array( kOrder1, kOrder2, ... )
 *             mbrs   => array( kMbr1, kMbr2, ... )
 *             offset => n ; skip n label positions
 */

//include( "fpdf.php" );
include( SEEDLIB."fpdf/PDF_Label.php" );

$raOrders = array();
if( isset($_REQUEST['orders']) ) {
    foreach( $_REQUEST['orders'] as $k ) {
        if( ($n = intval($k)) )  $raOrders[] = $n;
    }
}

$raMbrs = array();
if( isset($_REQUEST['mbrs']) ) {
    foreach( $_REQUEST['mbrs'] as $k ) {
        if( ($n = intval($k)) )  $raMbrs[] = $n;
    }
}

/*------------------------------------------------
To create the object, 2 possibilities:
either pass a custom format via an array
or use a built-in AVERY name
------------------------------------------------*/

// Example of custom format
// $pdf = new PDF_Label(array('paper-size'=>'A4', 'metric'=>'mm', 'marginLeft'=>1, 'marginTop'=>1, 'NX'=>2, 'NY'=>7, 'SpaceX'=>0, 'SpaceY'=>0, 'width'=>99, 'height'=>38, 'font-size'=>14));

// Standard format

$pdf = new PDF_Label( '5160' );
$pdf->Set_Font_Size(9);  // default is 8pt which is pretty small; though this might be too big for long addresses
$pdf->AddPage();

if( ($n = intval(@$_REQUEST['offset']) ) ) {
    for( $i = 0; $i < $n; ++$i ) {
        $pdf->Add_Label("");
    }
}

foreach( $raOrders as $k ) {
    $ra = $kfdb->QueryRA( "SELECT * FROM seeds_1.mbr_order_pending WHERE _key='{$k}'" );
    if( $ra['_key'] ) {
        $text = MbrDrawAddressBlock( $ra['mail_firstname'], $ra['mail_lastname'], "", "", $ra['mail_company'], "",
                                     $ra['mail_addr'], $ra['mail_city'], $ra['mail_prov'], $ra['mail_postcode'], $ra['mail_country'], "PDF" );
        $pdf->Add_Label($text);
    }
}

foreach( $raMbrs as $k ) {
    $ra = $kfdb->QueryRA( "SELECT * FROM seeds_2.mbr_contacts WHERE _key='{$k}'" );
    if( $ra['_key'] ) {
        $text = MbrDrawAddressBlock( $ra['firstname'], $ra['lastname'], $ra['firstname2'], $ra['lastname2'], $ra['company'], $ra['dept'],
                                     $ra['address'], $ra['city'], $ra['province'], $ra['postcode'], $ra['country'], "PDF" );
        $pdf->Add_Label($text);
    }
}


$pdf->Output();

?>
