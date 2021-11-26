<?php

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site.php" );

include_once( SEEDCOMMON."console/console01kfui.php" );
include_once( SEEDCOMMON."sl/sl_db.php" );
include_once( "_sl_admin_accession.php" );
include_once( "_sl_admin_adoption.php" );


$raPerms = array( 'Reports'       => array('R SL'),
                  'Adoptions'     => ['W SL']//array('W SLAdopt'),
);

list($kfdb1, $sess) = SiteStartSessionAccount( $raPerms );
$kfdb2 = SiteKFDB( SiteKFDB_DB_seeds2 ) or die( "Cannot connect to database" );

$oApp = SEEDConfig_NewAppConsole_LoginNotRequired( ['db'=>'seeds1'] ); // , 'sessPermsRequired' => ["W SLCollectionReport"] ] );
$oApp->kfdb->SetDebug(1);

$raKFParms = array( "kfLogFile"=>SITE_LOG_ROOT."sl_admin.log",
                    "bReadonly"=> !($sess->CanWrite( "SL" )) );

class MyConsole extends Console01KFUI
{
    public $oW = null;

    public $oUGP;
    var $sOut = ""; // for TabSetContentDraw

    private $oA = null;   // the application class for the current tab

    function __construct( KeyFrameDB $kfdb, SEEDSessionAccount $sess, $raParms ) { parent::__construct( $kfdb, $sess, $raParms ); }

    function TabSetInit( $tsid, $tabname )
    {
        global $oApp;

        if( $tsid != 'TFmain' ) return;

        $this->oW = new Console01_Worker( $this, $this->kfdb, $this->sess, "EN" );

        switch( $tabname ) {
            case 'Reports':   $this->oA = new SLAdminReports( $this->oW, $oApp );  break;
            case 'Adoptions': $this->oA = new SLAdminAdoption( $this );  break;
        }
    }

    function TabSetPermission( $tsid, $tabname )
    {
        global $raPerms;

        return( ($tsid == 'TFmain' && is_array($ra = @$raPerms[$tabname]) && $this->sess->TestPermRA( $ra ))
                ? Console01::TABSET_PERM_SHOW
                //: Console01::TABSET_PERM_GHOST );
                : Console01::TABSET_PERM_HIDE );
    }

	function TFmainAdoptionsControl()        { return( "<DIV>".$this->oComp->SearchToolDraw()."</DIV>" ); }
    function TFmainReportsControl()          { return( "" ); }
    function TabSetContentDraw( $tsid, $tabname )
    {
       global $kfdb1, $kfdb2, $sess;

       $this->sOut .= "<style>"
                     .".slAdminForm textarea { width:450px }"
                     ."</style>";

       switch( $tabname ) {
            case 'Adoptions':       $this->sOut .= "<div style='font-size:14pt;padding:5px;background-color:#ddd;width:30%;text-align:center'>If you need to add a cultivar go to <a href='http://office.seeds.ca/sl/rosetta.php' target='_blank'>Rosetta</a></div>"
                                                  .$this->CompListTable( array( 'bEdit'=>true ) );  break;
            case 'Reports':         $this->sOut .= $this->oA->ReportsContentDraw();               break;
       }
        return( "<DIV style='margin:15px'>".$this->sOut."</DIV>" );
    }

/*
	function AdoptionsFormDraw( $oForm )
	{
	    $s = "<TABLE class='slAdminForm' border='0'>"
        ."<TR>".$oForm->TextTD('fk_sl_pcv','PCV')."<FONT size='2'>".$oForm->Text('P_psp','','readonly')." : ".$oForm->Text('P_name','','readonly')."</FONT></TR>"
        ."</TR>".$oForm->TextTD( 'donor_name',  'Real Donor')."</TR>"
        ."</TR>".$oForm->TextTD( 'public_name', 'Public name')."</TR>"
        ."</TR>".$oForm->TextTD( 'amount', 'Amount')."</TR>"
        ."</TR>".$oForm->TextTD( 'd_donation', 'Donation Date')."</TR>"
        ."</TR>".$oForm->TextTD( 'sPCV_request', 'Request')."</TR>"
        ."<TR>".$oForm->TextAreaTD( 'notes', 'Notes' )."</TR>"

        ."<TR><TD colspan='2'><BR/><B>Correspondence with Donor:</B></TD></TR>"
        ."<TR>".$oForm->TextTD( 'corr_ack','Acknowledged' )."</TR>"
        ."<TR>".$oForm->TextTD( 'corr_backup','Backup' )."</TR>"
        ."<TR>".$oForm->TextTD( 'corr_avail','Available')."</TR>"
        ."<TR>".$oForm->TextTD( 'corr_seeds','Seeds sent' )."</TR>"

        ."<TR><TD><BR/><HR/></TD><TD>&nbsp;</TD></TR>"

        ."<TR>".$oForm->TextTD( 'x_d_donation','X Donation date' )."</TR>"
        ."</TABLE>"
        ."<INPUT type='submit' value='Save'>";
	    return( $s );
	}
*/
}


$raConsoleParms = array(
    'HEADER' => "Seed Library on ${_SERVER['SERVER_NAME']}",
    'CONSOLE_NAME' => "SLAdmin",
    'TABSETS' => array( "TFmain" => array( 'tabs' => array( 'Reports' => array( 'label' => "Reports" ),
    														'Adoptions' => array( 'label' => "Adoptions" ),
                                                            ))),
    'bBootstrap'=>true,
    );
$oC = new MyConsole( $kfdb2, $sess, $raConsoleParms );

header( "Content-type: text/html; charset=ISO-8859-1");    // this should be on all pages so accented chars look right (on Linux anyway)

echo $oC->DrawConsole( "[[TabSet: TFmain]]" );

?>
