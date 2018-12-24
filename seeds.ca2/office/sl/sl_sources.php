<?php

/*
insert into sl_sources (
    _key,_created,_created_by,_updated,_updated_by,
    sourcetype,name_en,name_fr,addr_en,addr_fr,city,prov,country,postcode,phone,fax,web,web_alt,email,email_alt,desc_en,desc_fr,year_est,
    comments,bSupporter
) select
    rl_cmp_id,_created,1499,_updated,1499,
    'company',name_en,name_fr,addr_en,addr_fr,city,prov,country,postcode,phone,fax,web,web_alt,email,email_alt,desc_en,desc_fr,year_est,
    comments,supporter
from rl_companies where _disabled=0;


// COPY sl_sources to xlsupload

create table seeds2.xlsupload (
k integer,
_deleted integer,
name text,
addr text,
city text,
prov text,
country text,
postcode text,
phone text,
email text,
web text,
desc_en text,
desc_fr text,
year_est integer,
score_quality char(1),
score_locality char(1),
score_bulk char(1),
score_diversity integer,
score_capacity integer,
latitude decimal(12,7),
longitude decimal(12,7) );

insert into seeds2.xlsupload select
_key,
_status,
name_en,
addr_en,
city,
prov,
country,
postcode,
phone,
email,
web,
desc_en,
desc_fr,
year_est,
score_quality,
score_locality,
score_bulk,
score_diversity,
score_capacity,
latitude,
longitude
from sl_sources;

 */

/*
 * sl_sources
 *
 * Copyright 2012-2016 Seeds of Diversity Canada
 *
 * Manage information about Sources of seeds
 *
 * Source    = anybody who provides seeds: companies, seed banks, individual collectors
 * CVSource  = a map between Cultivars and Sources, with metadata about when and how each tuple was obtained
 */

define( "SITEROOT", "../../" );
include_once( SITEROOT."site2.php" );
include_once( SEEDCOMMON."console/console01kfui.php" );
//include_once( STDINC."SEEDCSV.php" ); not used anymore?
include_once( "_sl_source_sources.php" );
include_once( "_sl_source_download.php" );
include_once( "_sl_source_edit.php" );
include_once( "_sl_source_rosetta.php" );
include_once(SEEDCOMMON."sl/sl_sources_common.php");

list($kfdb2, $sess) = SiteStartSessionAccount( array( array("SLSources" => "W"), array("SL"=>"A") ) );  // SLSources-W OR SL-A
$kfdb1 = SiteKFDB( SiteKFDB_DB_seeds1 ) or die( "Cannot connect to database" );

//$kfrel = new KeyFrameRelation( $kfdb, $kfreldef_CVSources, 0 );
//$raKFParms = array( "kfLogFile"=>SITE_LOG_ROOT."slsources.log",
//                    "bReadonly"=> !($sess->CanWrite( "SL" )) );




//var_dump($_REQUEST);
//var_dump($_SESSION);
$kfdb1->SetDebug(1);
$kfdb2->SetDebug(1);


if( @$_REQUEST['cmd'] == 'company_download' && ($kCompany = SEEDSafeGPC_GetInt('kCompany')) ) { // kluge for xls - the right way would be to make this a QServer command

    if( $kCompany == -1 ) {
        // All companies
        $sCond = "";
        $sCompany = "All Seed Companies";
    } else {
        $sCond = "SRC._key='$kCompany'";
        $sCompany = "";
    }

    $raRows = array();
    $oSLDBSrc = new SLDB_Sources( $kfdb1, 0 );
    if( ($kfrc = $oSLDBSrc->GetKFRC( "SRCCVxSRCxPxS", $sCond, array('sSortCol'=>'S_name_en ASC,P_name') )) ) {
        while( $kfrc->CursorFetch() ) {
            $raRows[] = array( 'k'        =>             $kfrc->Value('_key'),
                               'company'  => utf8_encode($kfrc->Value('SRC_name_en')),
                               'species'  => utf8_encode($kfrc->Value('S_name_en')),
                               'cultivar' => utf8_encode($kfrc->Value('P_name')),
                               'organic'  =>             $kfrc->Value('bOrganic') );
            if( !$sCompany && $kCompany != -1 ) {
                $sCompany = utf8_encode($kfrc->Value('SRC_name_en'));
            }
        }
    }

    if( count($raRows) ) {
        SEEDTable_OutputXLSFromRARows( $raRows, array( 'columns' => array('k','company','species','cultivar','organic'),
                                                       'filename'=>"$sCompany.xls",
                                                       'created_by'=>$sess->GetName(), 'title'=>"$sCompany Seed Listing" ) );
    } else {
        echo "There is no information to download for this company";
    }
}




class MyConsole extends Console01KFUI
{
    public $kfdb1;
    public $oSLSrcCommon;
    private $oSLDBSrc;
    private $oCCv;
    private $oSources;
    private $oDownload;
    private $oRosetta;


    private $oW;    // centralize storage of objects here please, and pass this to worker classes instead of all the separate objects

    function __construct( KeyFrameDB $kfdb1, KeyFrameDB $kfdb2, SEEDSessionAccount $sess, $raParms )
    {
// oW should have kfdb2 and sql should refer to seeds.* tables
        $this->oW = new SEEDApp_WorkerC( $this, $kfdb1, $sess, "EN" );

        $this->kfdb1 = $kfdb1;
        parent::__construct( $kfdb2, $sess, $raParms );
        $this->oSLSrcCommon = new SLSourcesCommon( $kfdb1, $sess->GetUID(), SITE_LOG_ROOT."sl_sources.log" );
        $this->oSLDBSrc = new SLDB_Sources( $this->oW->kfdb, $this->oW->sess->GetUID() );
    }

    function TabSetInit( $tsid, $tabname )
    {
        if( $tsid != 'TFmain' )  return;
        switch( $tabname ) {
            case 'Sources':
                $this->oSources = new SLSourceSources( $this );
                break;
            case 'Seeds':
                //$this->oCCv = new CSCICultivars( $this->kfdb1, $this->sess );
                $raCompParms = array(
                    "Label"=>"Seeds",
                    "ListCols" => array( array( "label"=>"Source",   "colalias"=>"fk_sl_sources", "w"=>120 ),
                                         array( "label"=>"Species",  "colalias"=>"osp",           "w"=>120, 'colsel'=>array('filter'=>'') ),
                                         array( "label"=>"Cultivar", "colalias"=>"ocv",           "w"=>120 ) ),
                    "ListSize" => 20,
                    "fnListRowTranslate" => array($this,"SeedsListRowTranslate"),

                );
                //$this->oSources = new SLSourceSources( $this );
                //$this->CompInit( $this->oSLSrcCommon->kfrelCVSources, $raCompParms );
                $this->CompInit( $this->oSLDBSrc->GetKfrel("SRCCVxSRC_P"), $raCompParms );
                break;
            case 'Edit':
                $this->oEdit = new SLSourceEdit( $this, $this->kfdb1, $this->sess );
                break;
            case 'Download':
                $this->oDownload = new SLSourceDownload( $this->oW );
                break;
            case 'Rosetta':
                $this->oRosetta = new SLSourceRosetta( $this, $this->kfdb1, $this->sess );
                break;
        }
    }

    function TabSetControlDraw( $tsid, $tabname )
    {
        $s = "";
        switch( $tabname ) {
            case 'Sources':
                $s .= $this->oSources->DrawControlArea();
                break;
            case 'Seeds':
                // if( $this->oCCv )  $s = $this->oCCv->DrawCultivarControls();
                $s .= "<DIV>".$this->oComp->SearchToolDraw()."</DIV>";
                break;
            case 'Edit':
                break;
            case 'Download':
                break;
            case 'Rosetta':
                break;
        }

        return( $s );
    }

    function TabSetContentDraw( $tsid, $tabname )
    {
        $s = "";

        switch( $tabname ) {
            case 'Sources':
                $s .= $this->CompListTable( array( 'bEdit'=>true ) );
                break;
            case 'Seeds':
                // if( $this->oCCv )  $s .= $this->oCCv->DrawCultivars();
                $s .= $this->CompListForm_Horz();
                break;
            case 'Edit':
                $s .= $this->oEdit->Main();
                break;
            case 'Download':
                $s .= $this->oDownload->Main();
                break;
            case 'Rosetta':
                $s .= $this->oRosetta->Main();
                break;
        }

        return( "<DIV style='margin:15px'>$s</DIV>" );
    }

    function SeedsListRowTranslate( $kfr )
    {
        $ra = $kfr->ValuesRA();
        $kSrc = intval($ra['fk_sl_sources']);

        switch( $kSrc ) {
            case 1:  $ra['fk_sl_sources'] = "PGRC";   break;
            case 2:  $ra['fk_sl_sources'] = "NPGS";   break;
            default:
                $ra['fk_sl_sources'] = $this->oW->kfdb->Query1( "SELECT name_en FROM seeds.sl_sources WHERE _key='$kSrc'" );
        }

        return( $ra );
    }

}



$bBootstrap = in_array(@$_SESSION['console01SLSourcesTFTFmain'],array('Edit','Download','Rosetta'));

$raConsoleParms = array(
    'HEADER' => "Seed Sources",
    'CONSOLE_NAME' => "SLSources",
//    'HEADER_LINKS' => array( array( 'href' => 'mbr_email.php',    'label' => "Email Lists",  'target' => '_blank' ),
//                             array( 'href' => 'mbr_mailsend.php', 'label' => "Send 'READY'", 'target' => '_blank' ) ),

    'TABSETS' => array( "TFmain" => array( 'tabs' => array( 'Sources' => array( 'label' => "Sources" ),
                                                             'Seeds' => array( 'label' => "Seeds" ),
                                                             'Edit' => array( 'label' => "Edit" ),
                                                             'Download' => array( 'label' => "Download/Upload" ),
                                                             'Rosetta' => array( 'label' => "Rosetta" ),
    ))),
    'bBootstrap'=>$bBootstrap,
    //'EnableC01Form' => true,
);



$oC = new MyConsole( $kfdb1, $kfdb2, $sess, $raConsoleParms );

header( "Content-type: text/html; charset=ISO-8859-1");    // this should be on all pages so accented chars look right (on Linux anyway)

echo "<STYLE>"
    .".csciForm, .csciForm td, .csciForm input   { font-size:10pt; font-family:verdana,helvetica,sans-serif; }"
    //.".slAdminConsoleListItem { font-size:10pt; font-family:verdana,helvetica,sans-serif; margin-bottom:10px; border-bottom:1px solid #ccc;padding-bottom:10px; }"
    ."</STYLE>";

echo $oC->DrawConsole( "[[TabSet: TFmain]]" );

?>
<script>
// remove trailing GET parms so they don't get re-POSTed
var clean_uri = location.protocol + "//" + location.host + location.pathname;
window.history.replaceState({}, document.title, clean_uri);
</script>
<?php

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
