<?php

// Weirdest thing: other apps send parms like addorder=12345; when POST those parms appear in the url like get, so script is used to clean that up.
// Somehow _REQUEST is empty if you do that. Using GET from other apps and script to clean it up.


/* Label maker for Contacts and Orders

    cmd=pdf                     : output the labels to pdf
    cmd=clear                   : clear the label list

    orderadd=k                  : add order k
    orderadd[]=k1&orderadd[]=k2 : add orders k1 and k2
    orderdel=k                  : remove order k
    orderdel[]=k1&orderdel[]=k2 : remove orders k1 and k2

    mbr k can be: k, email, "k,k,..", "email,email,..."

    mbradd=k                    : add mbr k
    mbradd[]=k1&mbradd[]=k2     : add mbrs k1 and k2
    mbrdel=k                    : remove mbr k
    mbrdel[]=k1&mbrdel[]=k2     : remove mbrs k1 and k2
 */

define( "SITEROOT", "../../" );
include_once( SITEROOT."site2.php" );
include_once( SEEDCOMMON."mbr/mbrCommon.php" ); // MbrDrawAddressBlock

include_once( SEEDCORE."SEEDCoreFormSession.php" );


//list($kfdb,$sess,$lang) = SiteStartSessionAccount( ['R MBR', '&', 'R MBRORDER'] );   // both are required because both are revealed

$oApp = SEEDConfig_NewAppConsole( ['db'=>'seeds2', 'sessPermsRequired' => ['R MBR', '&', 'R MBRORDER'] ] );  // both are required because both are revealed
$kfdb = $oApp->kfdb;
$sess = $oApp->sess;
$lang = $oApp->lang;


class MbrLabelsApp
{
    private $oApp;
    private $oForm;

    function __construct( SEEDAppConsole $oApp )
    {
        $this->oApp = $oApp;

        /* Orders and Mbrs are stored in SVA:MbrLabels
         * First update the oForm to get any changes made by user on the labels form.
         * Then check cmds to get any additions/deletions from other apps.
         */
        $this->oForm = new SEEDCoreFormSession( $oApp->sess, "MbrLabels", "A" );
        $this->oForm->Update();

        if( SEEDInput_Str('cmd') == 'clear' ) {
            $this->oForm->SetValue( 'mbrorders', "" );
            $this->oForm->SetValue( 'mbrcontacts', "" );
            $this->oForm->SetValue( 'nSkip', "" );
        }
        if( ($p = @$_REQUEST['orderadd']) )  $this->add( $p, 'mbrorders' );
        if( ($p = @$_REQUEST['mbradd']) )    $this->add( $p, 'mbrcontacts' );
    }

    private function add( $p, $fld )
    {
        if( is_array($p) ) {
            foreach( $p as $v ) {
                //list($raRange,$sRange) = SEEDCore_ParseRangeStr( $v );
                //$ra = array_merge( $ra, $raRange );
                $this->oForm->SetValue( $fld, $this->oForm->Value($fld)."\n$v" );
            }
        } else if( ($v = intval($p)) ) {
            //list($raRange,$sRange) = SEEDCore_ParseRangeStr( $p );
            //$ra = array_merge( $ra, $raRange );
            $this->oForm->SetValue( $fld, $this->oForm->Value($fld)."\n$v" );
        }
    }


    function DrawUI()
    {
        /* Draw the textarea controls for Orders and Contacts
         */
        $sInputs =
            "<div>"
               ."<h4>Orders</h4>"
               .$this->oForm->TextArea( 'mbrorders', ['width'=>'350px'] )
           ."</div><div>"
               ."<h4>Contacts</h4>"
               ."<p style='font-size:8pt'>Member numbers or email addresses: separated by commas or spaces</p>"
               .$this->oForm->TextArea( 'mbrcontacts', ['width'=>'350px'] )
           ."</div>";

        /* Draw the Skip control and Update button
         */
        $sCtrl =
            "<div style='margin:5px; text-align:right'>"
           ."Skip # labels ".$this->oForm->Text( 'nSkip', "", ['size'=>10] )
           ."</div>"
           ."<div style='text-align:right'><input type='submit' value='Update'/></div>";

        /* Draw preview table showing the addresses.
         */
        $sPreviewTable = "";
        $raAddresses = $this->getAddressBlocks('HTML');
        $skip = $this->oForm->ValueInt('nSkip');
        for( $r = 0; $r < 10; ++$r ) {
            $sPreviewTable .= "<tr>";
            for( $c = 0; $c < 3; ++$c ) {
                $sPreviewTable .= "<td style='width:300px;height:60px;font-size:9pt;padding:0px 3px'>";
                if( $skip > 0 ) {
                    --$skip;
                } else if( ($v = current($raAddresses)) !== false ) {
                    $sPreviewTable .= $v;
                    next($raAddresses);
                }
                $sPreviewTable .= "</td>";
            }
            $sPreviewTable .= "</tr>";
        }
        $sPreviewTable = "<table border='1' style='width:100%'>$sPreviewTable</table>";

        /* Buttons
         */
        $clearButton =
            "<div style='margin-top:10px'>"
           ."<form method='post'>"
           ."<input type='hidden' name='cmd' value='clear'/>"
           ."<input type='submit' value='Clear all'/>"
           ."</form>"
           ."</div>";
        $printButton =
            "<div style='margin-top:10px'>"
           ."<form method='post' target='_blank'>"
           ."<input type='hidden' name='cmd' value='pdf'/>"
           ."<input type='submit' value='Print Labels'/>"
           ."</form>"
           ."</div>";

        /* Put it together
         */
        $s = "<div class='container'>"
            ."<div class='row'>"
                ."<div class='col-sm-6'>$clearButton</div>"
                ."<div class='col-sm-6'>$printButton</div>"
            ."</div>"
            ."<div class='row'>"
                ."<form method='post'>"
                    ."<div class='col-sm-3'>$sInputs</div>"
                    ."<div class='col-sm-3'>$sCtrl</div>"
                    ."<div class='col-sm-6'>$sPreviewTable</div>"
                ."</form>"
            ."</div>"
            ."</div>";

        return( $s );
    }

    private function getAddressBlocks( $format = "HTML" )
    /****************************************************
        Return array of formatted address blocks for the union of Orders and Contacts
     */
    {
        $raOut = [];
        $oMbr = new Mbr_Contacts( $this->oApp );

        foreach( preg_split('/\s+/', $this->oForm->Value('mbrorders')) as $v ) {
            $ra = $this->oApp->kfdb->QueryRA( "SELECT * FROM seeds_1.mbr_order_pending WHERE _key='{$v}'" );
            if( @$ra['_key'] ) {
                $ra['mail_address'] = $ra['mail_addr'];
                $ra['mail_province'] = $ra['mail_prov'];
                $ra['mail_firstname2'] = $ra['mail_lastname2'] = $ra['mail_dept'] = "";
                $raOut[] = $oMbr->DrawAddressBlockFromRA( $ra, $format, 'mail_' );
            }
        }
        foreach( preg_split('/\s+/', $this->oForm->Value('mbrcontacts')) as $v ) {
            if( ($a = $oMbr->DrawAddressBlock( $v, $format )) ) {
                $raOut[] = $a;
            }
        }

        return( $raOut );
    }

    function DrawPDFLabels()
    {
        /* Draw the labels using PDF_Label
         */
        include( SEEDLIB."fpdf/PDF_Label.php" );

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

        if( ($n = $this->oForm->ValueInt('nSkip') ) > 0 ) {
            while( $n-- ) {
                $pdf->Add_Label("");
            }
        }

        foreach( $this->getAddressBlocks('PDF') as $sBlock ) {
            $pdf->Add_Label($sBlock);
        }

        $pdf->Output();
        exit;
    }
}


$oLA = new MbrLabelsApp( $oApp );

if( @$_REQUEST['cmd'] == 'pdf' ) {
    $oLA->DrawPDFLabels();
    exit;                       // DrawPDFLabels exits but this is a good reminder
}

echo Console02Static::HTMLPage( utf8_encode($oLA->DrawUI()), "", "EN" );


// for some reason when other apps launch this page via a post with a target, the url keeps the parameters like a get
?>
<script>
var clean_uri = location.protocol + "//" + location.host + location.pathname;
window.history.replaceState({}, document.title, clean_uri);
</script>
