<?php

if( !defined("SITEROOT") )  define("SITEROOT", "../../");
include_once( SITEROOT."site2.php" );

include_once( SEEDCOMMON."console/console01kfui.php" );
include_once( SEEDCOMMON."sl/sl_db.php" );
include_once( "_sl_admin_accession.php" );
include_once( "_sl_admin_adoption.php" );


// Access to the application is given if any of the tabs are accessible
// Inaccessible tabs are Ghosted
$raPerms = array( 'Reports'       => array('R SL'),
                  'Accessions'    => array('W SL-do-not-use-this-tab'),
                  'Adoptions'     => array('W SLAdopt'),
                  'Adoptions2'    => array('W SLAdopt'),
                  'Cultivars'     => array('W SL-do-not-use-this-tab'),
                  'Species'       => array('W SL-do-not-use-this-tab'),
                  //'Germination'   => array('W SL'),
                  'admin'         => array('A SL-not-implemented'),
                                  '|'   // the above are disjunctions for application access
);


list($kfdb2, $sess) = SiteStartSessionAccount( $raPerms );
$kfdb1 = SiteKFDB( SiteKFDB_DB_seeds1 ) or die( "Cannot connect to database" );

$raKFParms = array( "kfLogFile"=>SITE_LOG_ROOT."sl_admin.log",
                    "bReadonly"=> !($sess->CanWrite( "SL" )) );

//var_dump($_REQUEST);

class MyConsole extends Console01KFUI
{
    public $oW = null;

    public $oUGP;
    var $sOut = ""; // for TabSetContentDraw

    private $oApp = null;   // the application class for the current tab
    private $oAdopt = NULL;     // use oApp instead

    function __construct( KeyFrameDB $kfdb, SEEDSessionAccount $sess, $raParms ) { parent::__construct( $kfdb, $sess, $raParms ); }

    function TabSetInit( $tsid, $tabname )
    {
        if( $tsid != 'TFmain' ) return;

        $this->oW = new Console01_Worker( $this, $this->kfdb, $this->sess, "EN" );

        switch( $tabname ) {
            case 'Reports': $this->oApp = new SLAdminReports( $this->oW );  break;
            case 'admin':   $this->oApp = new SLAdmin_Admin( $this->oW );   break;
        }
    }

    function TFmainAccessionsInit()          { $this->oW = new SLAdmin_Accession( $this, $this->kfdb, $this->sess );
                                               $this->oW->Init();
                                             }
	function TFmainAdoptionsInit()			 { $this->oAdopt = new SLAdminAdoption( $this ); }
	function TFmainAdoptions2Init()			 { $this->myInit( 'adoptions2' ); }
	function TFmainCultivarsInit()			 { $this->myInit( 'cultivars'); }
	function TFmainSpeciesInit()			 { $this->myInit( 'species'); }
	function TFmainGerminationInit()         { $this->myInit( 'germination'); }
	function TFmainAdoptionsFormInit()		 { $this->myInit( 'adoptionsform' ); }
	function TFmainToDoInit()			 	 { $this->myInit( 'todo' ); }


    function TabSetPermission( $tsid, $tabname )
    {
        global $raPerms;

        return( ($tsid == 'TFmain' && is_array($ra = @$raPerms[$tabname]) && $this->sess->TestPermRA( $ra ))
                ? Console01::TABSET_PERM_SHOW
                //: Console01::TABSET_PERM_GHOST );
                : Console01::TABSET_PERM_HIDE );
    }

    function TFmainAccessionsControl()       { return( $this->oW->ControlDraw() ); }
    function TFmainCultivarsControl()		 { return( "<DIV>".$this->oComp->SearchToolDraw()."</DIV>" ); }
    function TFmainSpeciesControl()			 { return( "<DIV>".$this->oComp->SearchToolDraw()."</DIV>" ); }
    function TFmainGerminationControl()      { return( "<DIV>".$this->oComp->SearchToolDraw()."</DIV>" ); }
	function TFmainAdoptionsControl()        { return( "<DIV>".$this->oComp->SearchToolDraw()."</DIV>" ); }
	function TFmainAdoptionsFormControl()    { return( "<DIV>".$this->oComp->SearchToolDraw()."</DIV>" ); }
    function TFmainReportsControl()          { return( "" ); }

    function myInit( $k )
    {
    	global $kfreldef_SL_Adoption;
    	global $kfreldef_SL_Cultivars;
    	global $kfreldef_SL_Species;
    	global $kfreldef_SL_Germination;
        switch( $k ) {
            case 'adoptions':     global $kfdb1; $kfrel = new KeyFrameRelation( $kfdb1, $kfreldef_SL_Adoption, $this->sess->GetUID() ); break;
            case 'adoptions2':    global $kfdb1; $kfrel = new KeyFrameRelation( $kfdb1, $kfreldef_SL_Adoption, $this->sess->GetUID() ); break;
            case 'cultivars':     global $kfdb1; $kfrel = new KeyFrameRelation( $kfdb1, $kfreldef_SL_Cultivars, $this->sess->GetUID() ); break;
            case 'species':       global $kfdb1; $kfrel = new KeyFrameRelation( $kfdb1, $kfreldef_SL_Species, $this->sess->GetUID() ); break;
            case 'germination':   global $kfdb1; $kfrel = new KeyFrameRelation( $kfdb1, $kfreldef_SL_Germination, $this->sess->GetUID() ); break;
            case 'adoptionsform': global $kfdb1; $kfrel = new KeyFrameRelation( $kfdb1, $kfreldef_SL_Adoption, $this->sess->GetUID() ); break;
            case 'todo': 		  global $kfdb1; $kfrel = new KeyFrameRelation( $kfdb1, $kfreldef_SL_Accession, $this->sess->GetUID() ); break;
        }

        $raCompParms = array(
            'adoptions'=> array(
            			  "Label"=>"Adoption",
            ),
            'adoptions2'=> array(
            			  "Label"=>"Adoption2",
            ),
            'cultivars'=> array(
            			  "Label" => "Cultivar",

            			  "ListCols" => array( array( "label"=>"k",  "colalias"=>"_key", "w"=>100 ),
                                       array( "label"=>"psp",  "colalias"=>"psp", "w"=>100 ),
                                       array( "label"=>"name", "colalias"=>"name","w"=>200 ),
                                     ),
                  		  "ListSize" => 15,
//                		  "ListSizePad" => 1,
//                		  "SearchToolCols"  => array( "Company"=>"R.name_en",
//                                            "Index Species"=>"pspecies", "Orig Species"=>"ospecies",
//                                            "Index Cultivar"=>"pname",   "Orig Cultivar"=>"oname" ),
//                		  "fnListFilter"    => "Item_rowFilter",
                  		  "fnFormDraw"      => array($this,"CultivarsFormDraw"),
            ),
            'species' => array(

				  "Label" => "Species",
                  "ListCols" => array( array( "label"=>"id",      "colalias"=>"_key",    "w"=>30 ),
                                       array( "label"=>"Name EN", "colalias"=>"name_en", "w"=>120),
                                       array( "label"=>"Name FR", "colalias"=>"name_fr", "w"=>120 ), //, "colsel" => array("filter"=>"")),
                                       array( "label"=>"Index EN", "colalias"=>"iname_en", "w"=>120),
                                       array( "label"=>"Index FR", "colalias"=>"iname_fr", "w"=>120),
                                       array( "label"=>"Botanical", "colalias"=>"name_bot", "w"=>120),
                                       array( "label"=>"Family EN", "colalias"=>"family_en", "w"=>120),
                                       array( "label"=>"Family FR", "colalias"=>"family_fr", "w"=>120),
                                       array( "label"=>"Category", "colalias"=>"category", "w"=>60, "colsel" => array("filter"=>"")),
                                     ),
                  "ListSize" => 15,
                  "ListSizePad" => 1,
                  "fnFormDraw" => array($this,"SpeciesFormDraw"),

            ),
            'germination' => array(
						  "Label" => "Germination",
                  		  "ListCols" => array( array( "label"=>"kInv", "colalias"=>"fk_sl_inventory", "w"=>100 ),
                                       array( "label"=>"psp",  "colalias"=>"P_psp", "w"=>100 ),
                                       array( "label"=>"name", "colalias"=>"P_name","w"=>150 ),
                                       array( "label"=>"date", "colalias"=>"dStart","w"=>100 ),
                                       array( "label"=>"nGerm", "colalias"=>"nGerm","w"=>50 ),
                                       array( "label"=>"nSown", "colalias"=>"nSown","w"=>50 ),
                                     ),
                  		  "ListSize" => 15,
//                		  "ListSizePad" => 1,
//                		  "SearchToolCols"  => array( "Company"=>"R.name_en",
//                                            "Index Species"=>"pspecies", "Orig Species"=>"ospecies",
//                                            "Index Cultivar"=>"pname",   "Orig Cultivar"=>"oname" ),
//                		  "fnListFilter"    => "Item_rowFilter",
                		  "fnFormDraw"  => array($this,"GerminationFormDraw"),
            ),
            'adoptionsform' => array(
            			  "Label" => "AdoptionsForm",

            			  "ListCols" => array( array( "label"=>"Donor",  "colalias"=>"donor_name", "w"=>300 ),
                                       array( "label"=>"amount", "colalias"=>"amount",     "w"=>100 ),
                                       array( "label"=>"pcv",    "colalias"=>"fk_sl_pcv",  "w"=>300 ),
                                     ),
                  		  "ListSize" => 15,
                          "fnFormDraw"      => array($this,"AdoptionsFormDraw"),
                          "fnListRowTranslateRA" => array($this,"PCVUpdateRA"),
            ),
            'todo'=> array(
            			  "Label"=>"ToDo",
            )
        );
        $this->CompInit( $kfrel, $raCompParms[$k] );
    }

    function TabSetContentDraw( $tsid, $tabname )
    {
       global $kfdb1, $kfdb2, $sess;

       $this->sOut .= "<style>"
                     .".slAdminForm textarea { width:450px }"
                     ."</style>";

       switch( $tabname ) {
            case 'Accessions':      $this->sOut .= $this->oW->ContentDraw();                        break;
            case 'Adoptions':       $this->sOut .= "<div style='font-size:14pt;padding:5px;background-color:#ddd;width:30%;text-align:center'>If you need to add a cultivar go to <a href='http://office.seeds.ca/sl/rosetta.php' target='_blank'>Rosetta</a></div>"
                                                  .$this->CompListTable( array( 'bEdit'=>true ) );  break;
            case 'Adoptions2':      $this->sOut .= SL_AdoptionAdmin( $kfdb1, $kfdb2, $sess );       break;
            case 'Germination':     $this->sOut .= $this->CompListForm_Vert();                      break;
            case 'AdoptionsForm':   $this->sOut .= $this->CompListForm_Vert();                      break;
            case 'ToDo':            $this->sOut .= $this->SL_ToDoAdmin( $kfdb1, $kfdb2, $sess );    break;
            case 'Reports':         $this->sOut .= $this->oApp->ReportsContentDraw();               break;
            case 'admin':           $this->sOut .= $this->oApp->AdminContentDraw();                 break;

            case 'Cultivars':
                $this->sOut .= "<h1>Don't use this anymore - use <a href='http://office.seeds.ca/sl/rosetta.php'>office.seeds.ca/sl/rosetta.php</a> instead</h1>";

                if( $sess->CanAdmin( "SL" ) ) {
                    $this->sOut .= $this->CompListForm_Vert();
                }
                break;
            case 'Species':
                $this->sOut .= "<h1>Don't use this anymore - use <a href='http://office.seeds.ca/sl/rosetta.php'>office.seeds.ca/sl/rosetta.php</a> instead</h1>";

                if( $sess->CanAdmin( "SL" ) ) {
                    $this->sOut .= $this->CompListForm_Vert();
                }
                break;

       }
        return( "<DIV style='margin:15px'>".$this->sOut."</DIV>" );
    }


    function CultivarsFormDraw( $oForm )
    {
        $s = "<table class='slAdminForm' border='0'>"
            ."<tr><td>Key:</td><td>".$oForm->GetKey()."</td></tr>"
            .$oForm->ExpandForm(
                "||| PSP || [[psp]]"
               ."||| Name || [[name]]"
               ."||| Notes || [[textarea:notes | nRows:5]]"
             )
            ."</table>"
            ."<input type='submit' value='Save'>";
        return( $s );
    }

    function SpeciesFormDraw( $oForm )
    {
        $s = "<table class='slAdminForm' border='0'>"
        ."<TR>".$oForm->TextTD("name_en", "Name EN")."</TR>"
		."<TR>".$oForm->TextTD("name_fr", "Name FR")."</TR>"
		."<TR>".$oForm->TextTD("iname_en", "Index EN")."</TR>"
		."<TR>".$oForm->TextTD("iname_fr", "Index FR")."</TR>"
		."<TR>".$oForm->TextTD("name_bot", "Botanical")."</TR>"
		."<TR>".$oForm->TextTD("family_en", "Family EN")."</TR>"
		."<TR>".$oForm->TextTD("family_fr", "Family FR")."</TR>"
		."<TR>".$oForm->TextTD("category", "Category")."</TR>"
		."<TR>".$oForm->TextAreaTD( "notes", "Notes" )."</TR>"
		."</TABLE>"
		."<INPUT type='submit' value='Save'>";
		return( $s );
	}

    function GerminationFormDraw( $oForm )
    {
        $s = "<table class='slAdminForm' border='0'>"
            .$oForm->ExpandForm(
                "||| Accession || [[fk_sl_accession]]"
               ."||| Date      || [[date:dSown]]"
               ."||| n Sown    || [[nSown]]"
               ."||| n Germ    || [[nGerm]]"
               ."||| Notes     || [[textarea:notes | nRows:5]]"
            )
            ."</table>"
            ."<input type='submit' value='Save'>";
        return( $s );
    }

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

	function SL_ToDoAdmin( $kfdb1, $kfdb2, $sess )
	{
    	$s = "";

    	$oSLPDA = new SLDB_PDA( $kfdb1 );

    	$raPDA = $oSLPDA->GetPDA( );

    	function sortByNAdoption( $ra1, $ra2 )  { return( -$ra1['nAdoption'] + $ra2['nAdoption'] ); } // sort backwards by inverting the return code
    	function sortByGHave( $ra1, $ra2 )      { return( -$ra1['nGHave'] + $ra2['nGHave'] ); }

	    usort( $raPDA, 'sortByGHave' );
	    $s .= "<TABLE style='float:left' border='1' cellspacing='5' cellpadding='5'>";
	    foreach( $raPDA as $ra ) {
        	$s .= SEEDStd_ArrayExpand( $ra, "<TR valign='top'><TD>[[psp]] : [[name]]<BR/>adoption = [[nAdoption]]<BR/>g_have = [[nGHave]]</TD></TR>" );
    	}
    	$s .= "</TABLE>";

	    usort( $raPDA, 'sortByNAdoption' );
	    $s .= "<TABLE style='float:right' border='1' cellspacing='5' cellpadding='5'>";
	    foreach( $raPDA as $ra ) {
        	$s .= SEEDStd_ArrayExpand( $ra, "<TR valign='top'><TD>[[psp]] : [[name]]<BR/>adoption = [[nAdoption]]<BR/>g_have = [[nGHave]]</TD></TR>" );
    	}
    	$s .= "</TABLE>";


    // 	Links to commands
    	$s .= "<P><A href='sl_dump.php?cmd=adoptions_xls' target='_blank'>Dump all adoptions to XLS</A></P>";


	    return( $s );
	}

/*	function ReportsAdmin( $oForm )
	{
    	$s = ""
		."This is the Reports screen";
	    return( $s );
	}

	function AdminAdmin( $oForm )
	{
    	$s = "";

    	$s .= "This is the admin screen";


    	return( $s );
	}*/
}


$kfreldef_SL_Cultivars =
    array( "Tables"=>array( array( "Table" => 'sl_pcv',
                                   "Type"  => 'Base',
                                   "Fields" => array( array("col"=>"psp",   "type"=>"S"),
                                                      array("col"=>"name",  "type"=>"S"),
                                                      array("col"=>"notes", "type"=>"S") ) ) ) );

$kfreldef_SL_Species =
	array( "Tables"=>array( array( "Table" => 'sl_species',
                                   "Type"  => 'Base',
                                   "Fields" => "Auto" ) ) ) ;

$kfreldef_SL_Germination =
    array( "Tables"=>array( array( "Table" => 'sl_germ',
                                   "Alias" => 'G',
                                   "Type"  => 'Base',
                                   "Fields" => "Auto" ),
                            array( "Table" => 'sl_inventory',
                                   "Alias" => 'I',
                                   "Type" => 'Parent',
                                   "Fields" => "Auto" ),
                            array( "Table" => 'sl_accession',
                                   "Alias" => 'A',
                                   "Type" => 'Grandparent',
                                   "Fields" => "Auto" ),
                            array( "Table" => 'sl_pcv',
                                   "Alias" => 'P',
                                   "Type" => 'Related',
                                   "Fields" => array( array("col"=>'psp', "type"=>"S"),
                                                      array("col"=>"name",  "type"=>"S") )) ) );

$kfreldef_SL_Adoption =
    array( "Tables"=>array( array( "Table" => 'sl_adoption',
                                   "Type"  => 'Base',
                                   "Fields" => "Auto" ),
                            array( "Table" => 'sl_pcv',
                                   "Alias" => 'P',
                                   "Type" => 'LEFT JOIN',
                                   "LeftJoinOn" => "T1.fk_sl_pcv=P._key",
                                   "Fields" => array( array("col"=>'psp', "type"=>"S"),
                                                      array("col"=>"name",  "type"=>"S") )) ) );

$raConsoleParms = array(
    'HEADER' => "Seed Library on ${_SERVER['SERVER_NAME']}",
    'CONSOLE_NAME' => "SLAdmin",
    'TABSETS' => array( "TFmain" => array( 'tabs' => array( 'Reports' => array( 'label' => "Reports" ),
    														'Accessions' => array( 'label' => "Accessions" ),
    														'Cultivars' => array( 'label' => "Cultivars" ),
    														'Species' => array( 'label' => "Species" ),
                                                            'Adoptions' => array( 'label' => "Adoptions" ),
                                                            //'Adoptions2' => array( 'label' => "Adoptions2" ),
    														'Germination' => array( 'label' => "Germination" ),
    														'AdoptionsForm' => array( 'label' => "Adoptions Form" ),
    														'ToDo' => array( 'label' => "To Do" ),
    														'admin' => array( 'label' => "Admin" )))),
    'bBootstrap'=>true,
    );
$oC = new MyConsole( $kfdb2, $sess, $raConsoleParms );

header( "Content-type: text/html; charset=ISO-8859-1");    // this should be on all pages so accented chars look right (on Linux anyway)

echo $oC->DrawConsole( "[[TabSet: TFmain]]" );

?>
