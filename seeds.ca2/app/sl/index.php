<?php

define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( SEEDCORE."SEEDCoreForm.php" );
include_once( SEEDLIB."sl/sldb.php" );

require SEEDROOT."vendor/autoload.php";     // FPDF

$oApp = SiteApp();  // no perms required for labels but might need perms for further functionality

$oForm = new SEEDCoreForm( 'A' );
$oForm->Update();

if( SEEDInput_Str('cmd') == 'Make Labels' )  goto drawPDF_Labels;

/* UI for setting up labels
 */

//var_dump($_REQUEST);

$oForm = new SEEDCoreForm( 'A' );
$oForm->Update();

$oSLDB = new SLDBCollection( $oApp );

if( (!$oForm->Value('cvName') || !$oForm->Value('desc')) && $oForm->Value('kLot') ) {
    $kfrLot = $oSLDB->GetKFR( 'IxAxPxS', $oForm->Value('kLot') );
    if( !$oForm->Value('cvName') ) $oForm->SetValue( 'cvName', $kfrLot->Value('P_name') );

    if( !$oForm->Value('desc') ) $oForm->SetValue( 'desc', $kfrLot->Value('S_label_desc') );
}

if( !$oForm->Value('nLabels') )  $oForm->SetValue( 'nLabels', 30 );

$oFE = new SEEDFormExpand( $oForm );
$s = "<h3>Seed Labels</h3>"
    ."<form method='post' target='_blank'>"
    ."<div class='container'>"
    .$oFE->ExpandForm(
         "|||BOOTSTRAP_TABLE(class='col-md-1' | class='col-md-3' | class='col-md-1' | class='col-md-3')"
        ."||| Lot # || [[kLot]] || Cultivar name || [[cvName]]"
        ."||| Description || [[TextArea:desc]] || || "
        ."||| # labels || [[nLabels]] || Skip first || [[offset]]"
        ."||| <input type='submit' name='cmd' value='Update'/> || <input type='submit' name='cmd' value='Make Labels'/>"

    )
    ."</form></div>";

echo Console01Static::HTMLPage( $s, "", "" );
exit;


/*
// for some reason when other apps launch this page via a post with a target, the url keeps the parameters like a get
?>
<script>
var clean_uri = location.protocol + "//" + location.host + location.pathname;
window.history.replaceState({}, document.title, clean_uri);
</script>

<?php
*/

drawPDF_Labels:

include_once( SEEDLIB."fpdf/PDF_Label.php" );

/*------------------------------------------------
To create the object, 2 possibilities:
either pass a custom format via an array
or use a built-in AVERY name
------------------------------------------------*/

// Example of custom format
// $pdf = new PDF_Label(array('paper-size'=>'A4', 'metric'=>'mm', 'marginLeft'=>1, 'marginTop'=>1, 'NX'=>2, 'NY'=>7, 'SpaceX'=>0, 'SpaceY'=>0, 'width'=>99, 'height'=>38, 'font-size'=>14));

// Standard format

class MyPDF_Label extends PDF_Label
{
    function __construct( $format )
    {
        parent::__construct( $format );
    }

    function AddLabel1()
    {
        // This is the first part of Add_Label, which moves the label counter forward.
        $this->_COUNTX++;
        if ($this->_COUNTX == $this->_X_Number) {
            // Row full, we start a new one
            $this->_COUNTX=0;
            $this->_COUNTY++;
            if ($this->_COUNTY == $this->_Y_Number) {
                // End of page reached, we start a new one
                $this->_COUNTY=0;
                $this->AddPage();
            }
        }
    }

    function AddLabel2( $xMargin = 0 )
    {
        // This is the second part of Add_Label, which positions the fpdf x/y at the top-left of the current label
        $_PosX = $this->_Margin_Left + $this->_COUNTX*($this->_Width+$this->_X_Space) + $this->_Padding + $xMargin;
        $_PosY = $this->_Margin_Top + $this->_COUNTY*($this->_Height+$this->_Y_Space) + $this->_Padding;
        $this->SetXY($_PosX, $_PosY);
    }

    function AddLabel3( $text, $xMargin = 0 )
    {
        // This is the third part of Add_Label, which writes text to the current x/y
        $this->MultiCell($this->_Width - $this->_Padding - $xMargin, $this->_Line_Height, $text, 0, 'L');
    }
}


$pdf = new MyPDF_Label( '5160' );
$pdf->Set_Font_Size(8);  // default is 8pt which is pretty small; though this might be too big for long addresses
$pdf->AddPage();

// Skip the offset number
if( ($n = intval($oForm->Value('offset'))) ) {
    for( $i = 0; $i < $n; ++$i ) {
        $pdf->AddLabel1();
    }
}

// Print the labels
for( $i = 0; $i < intval($oForm->Value('nLabels')); ++$i ) {
    $cvName = $oForm->Value('cvName').(($kLot = $oForm->Value('kLot')) ? " ($kLot)" : "");
    $desc = $oForm->Value('desc');
    $leftMargin = 17;
    $pdf->AddLabel1();
    $pdf->AddLabel2( $leftMargin );
    $pdf->SetFont( '', 'B', 8 );
    $pdf->AddLabel3( "\n".$cvName, $leftMargin );
    $pdf->SetFont( '', '', 8 );
    $pdf->AddLabel2( $leftMargin );
    $pdf->AddLabel3( "\n\n".$desc, $leftMargin );

    $pdf->AddLabel2( 0 );
    $pdf->Image( SITEROOT."i/img/logo/logoA_v-en-300.jpg", $pdf->GetX(), $pdf->GetY(), 17.14, 20 );  // image is 300x350

}

$pdf->Output();
