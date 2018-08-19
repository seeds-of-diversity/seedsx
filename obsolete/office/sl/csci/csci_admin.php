<?php
/*
 * CSCI Administration
 */

define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );

include_once( SEEDCOMMON."siteStart.php" );
include_once( SEEDCOMMON."sl/csci.php" );
//include_once( STDINC."KeyFrame/KFUIAppSimple.php" );
include_once( STDINC."KeyFrame/KFUIForm.php" );
include_once( SEEDCOMMON."console/console01.php" );

list($kfdb2, $sess) = SiteStartSessionAccount( array("CSCI" => "W") );
$kfdb1 = SiteKFDB( SiteKFDB_DB_seeds1 ) or die( "Cannot connect to database" );

$raKFParms = array( "kfLogFile"=>SITE_LOG_ROOT."csci_admin.log",
                    "bReadonly"=> !($sess->CanWrite( "CSCI" )) );

//var_dump($_REQUEST);
//var_dump($_SESSION);
//$kfdb1->SetDebug(2);
//$kfdb2->SetDebug(2);


class MyConsole extends Console01
{
    public  $sOut = ""; // for TabSetContentDraw
    private $kfdb1;
    private $oCCv;

    function __construct( KeyFrameDB $kfdb1, KeyFrameDB $kfdb2, SEEDSessionAccount $sess, $raParms )
    {
        $this->kfdb1 = $kfdb1;
        parent::__construct( $kfdb2, $sess, $raParms );
    }

    function TabSetInit( $tsid, $tabname )
    {
        if( $tsid == 'main' && $tabname = 'Cultivars' ) {
            $this->oCCv = new CSCICultivars( $this->kfdb1, $this->sess );
        }
    }

    function TabSetControlDraw( $tsid, $tabname )
    {
        $s = "";
        switch( $tabname ) {
            case 'Companies':
                break;

            case 'Cultivars':
                if( $this->oCCv )  $s = $this->oCCv->DrawCultivarControls();
                break;

            case 'Edit':
                break;
        }

        return( $s );
    }

    function TabSetContentDraw( $tsid, $tabname )
    {
        switch( $tabname ) {
            case 'Companies':
                $this->sOut .= "Company editing coming soon";
                break;
            case 'Cultivars':
                if( $this->oCCv )  $this->sOut .= $this->oCCv->DrawCultivars();
                break;

            case 'Edit':
                $this->sOut .= "Edit function coming soon";
            break;
        }

        return( "<DIV style='margin:15px'>".$this->sOut."</DIV>" );
    }
}


$raConsoleParms = array(
    'HEADER' => "Canadian Seed Catalogue Inventory",
    'CONSOLE_NAME' => "CSCIAdmin",
//    'HEADER_LINKS' => array( array( 'href' => 'mbr_email.php',    'label' => "Email Lists",  'target' => '_blank' ),
//                             array( 'href' => 'mbr_mailsend.php', 'label' => "Send 'READY'", 'target' => '_blank' ) ),

    'TABSETS' => array( "main" => array( 'labels' => array( "Companies", "Cultivars", "Edit" ) ) )
);
$oC = new MyConsole( $kfdb1, $kfdb2, $sess, $raConsoleParms );

header( "Content-type: text/html; charset=ISO-8859-1");    // this should be on all pages so accented chars look right (on Linux anyway)

$oC->sOut .= "<STYLE>"
            .".csciForm, .csciForm td, .csciForm input   { font-size:10pt; font-family:verdana,helvetica,sans-serif; }"
            //.".slAdminConsoleListItem { font-size:10pt; font-family:verdana,helvetica,sans-serif; margin-bottom:10px; border-bottom:1px solid #ccc;padding-bottom:10px; }"
            ."</STYLE>";

echo $oC->DrawConsole( "[[TabSet: main]]" );

// end


class CSCICultivars
{
    var $kfdb;    // kfdb1 on seeds db
    var $sess;
    var $oCSCI;
    private $oFormCtrl;  // SessionNS form in the control area - searching, sorting, and filtering
    private $oForm;      // KFUI form in the content area for data rows

    function CSCICultivars( &$kfdb, &$sess )
    {
        $this->kfdb = &$kfdb;
        $this->sess = &$sess;
        $this->oCSCI = new SL_CSCI( $kfdb );
        //$this->kfrelCompanies = new KeyFrameRelation( $kfdb, $kfreldef_CSCI_Company, $sess->GetUID() );
        //$this->kfrelCultivars = new KeyFrameRelation( $kfdb, $kfreldef_CSCI_Seeds,   $sess->GetUID() );


        $this->oForm = new KeyFrameUIForm( $this->oCSCI->kfrelSeeds, "A",
                               array( 'formdef' => array( 'company_name'=> array( 'label'=>"Company",  'type'=>"text", 'readonly'=>true, 'presetOnInsert'=>true),
                                                          'psp'         => array( 'label'=>"Species",  'type'=>"text", 'size'=>30),
                                                          'icv'         => array( 'label'=>"Cultivar", 'type'=>"text", 'size'=>30)
                                                        )) );

        $this->oFormCtrl = new SEEDFormSession( $this->sess, "csci_cv", "C" );  // this binds form elements to the namespace

        // these forms have separate submission, so only one will update at a time (therefore, the order doesn't matter)
        $this->oForm->Update();       // update data rows submitted from the content area
        $this->oFormCtrl->Update();   // update control states (sort, filter) in the control area
    }

    function DrawCultivarControls()
    {
        $raCompanies = array( "" => "--- All Companies ---" );
        if( ($kfr = $this->oCSCI->kfrelCompany->CreateRecordCursor("", array('sSortCol'=>'name_en')) ) ) {
            while( $kfr->CursorFetch() ) {
                $raCompanies[$kfr->value('name_en')] = $kfr->value('name_en');
            }
        }

        $s =  "<FORM class='csciForm' method='post' action='${_SERVER['PHP_SELF']}'>"
             ."<TABLE border='0' cellpadding='3' cellspacing='0'>"
             ."<TR valign='center'><TD>"
             ."Show: ".$this->oFormCtrl->Select( 'company', "", $raCompanies, array('attrs'=>"onChange='submit();'") )
             .SEEDStd_StrNBSP("",10)
             ."</TD><TD>Find species&nbsp;</TD>"
             //.SEEDForm_Select( 'csci_cv_find', $raSelectFind, $this->sess->VarGet('csci_cv_find') )
             ."<TD>containing ".$this->oFormCtrl->Text( 'findsp' )."</TD>"
//             .SEEDStd_StrNBSP("",10)
//             ."kPCV: ".SEEDForm_Text( 'sladopt_kpcv', $this->sess->VarGet('sladopt_kpcv'), "", 5 )
//             .SEEDStd_StrNBSP("",10)
//             ."kMbr: ".SEEDForm_Text( 'sladopt_kmbr', $this->sess->VarGet('sladopt_kmbr'), "", 5 )
             ."</TD><TD>"
             .SEEDStd_StrNBSP("",5)
             ."<INPUT type='submit' value='Search'>"
             ."</TD></TR>"
             ."<TR valign='center'><TD>&nbsp;</TD>"
             ."<TD>Find cultivar</TD>"
             ."<TD>containing ".$this->oFormCtrl->Text( 'findcv' )."</TD>"
             ."</TR></TABLE></FORM>";
        return( $s );
    }

    function DrawCultivars()
    {
        $s = "";

        $raCond = array();
        if( !$this->oFormCtrl->oDS->IsEmpty('company') ) {
            $raCond[] = "company_name='".$this->oFormCtrl->oDS->ValueDB('company')."'";
        }
        if( !$this->oFormCtrl->oDS->IsEmpty('findsp') ) {
            $raCond[] = "psp LIKE '%".$this->oFormCtrl->oDS->ValueDB('findsp')."%'";
        }
        if( !$this->oFormCtrl->oDS->IsEmpty('findcv') ) {
            $raCond[] = "icv LIKE '%".$this->oFormCtrl->oDS->ValueDB('findcv')."%'";
        }


        $sCond = implode( " AND ", $raCond );
//echo $sCond;

        $raKFParms = array('sSortCol'=>'psp,icv');
        if( empty($sCond) )  $raKFParms['iLimit'] = 20;   // truncate the list if there are no filters

        if( ($kfr = $this->oCSCI->kfrelSeeds->CreateRecordCursor($sCond, $raKFParms) )) {
            $this->oForm->SetKFR( $kfr );

            $s .= "<FORM class='csciForm' method='post' action='${_SERVER['PHP_SELF']}'>"
                 ."<DIV style='float:left'>"
                 .$this->oForm->FormTableStart();  // "<TABLE border='0' cellspacing='5' cellpadding='5'>";
            while( $kfr->CursorFetch() ) {
                $s .= $this->oForm->FormTableRow();
            }

            // draw a blank row at the bottom if company is set
            if( !$this->oFormCtrl->oDS->IsEmpty('company') ) {
                $kfrBlank = $this->oCSCI->kfrelSeeds->CreateRecord();
                $kfrBlank->SetValue('company_name', $this->oFormCtrl->oDS->Value('company') );
                $this->oForm->SetKFR( $kfrBlank );
                $s .= $this->oForm->FormTableRow();
            }

            $s .= $this->oForm->FormTableEnd();  // "</TABLE>"
            $s .= "</DIV>"
                 ."<DIV style='float:left;margin-left:5em;'><INPUT type='submit' value='Save' style='float:left'/></DIV>"
                 ."</FORM>"
                 ."<BR style='clear:both'/>";  // make the console border extend down below the floated form (else it ignores the floats and only encloses the controls at the top)
        }

        return( $s );
    }
}

?>
