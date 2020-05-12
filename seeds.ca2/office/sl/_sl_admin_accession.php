<?php

/* _sl_admin_accession.php
 *
 * Implement the user interface for Accessions
 */




/*

// Transition accessions to inventory

DELETE FROM sl_inventory;
INSERT INTO sl_inventory (_key,fk_sl_accession,g_weight,location,dCreation,bDeAcc) select _key,_key,g_have,location,_created,bDeAcc from sl_accession;



 */

//Reports
include_once( SEEDCOMMON."sl/sl_db_adoption.php" );    // DoReports

//Admin
include_once( SEEDCORE."SEEDUI.php" );
include_once( SEEDCOMMON."sl/q/_QServerSourceCV.php" );
include_once( SEEDCOMMON."sl/q/Q.php" );
include_once( SEEDCOMMON."console/console01ui.php" );   // DownloadUpload


class SLAdminReports
{
    public $oW;
    private $oApp;

    function __construct( Console01_Worker $oW, $oApp )
    {
        $this->oW = $oW;
        $this->oApp = $oApp;
    }

    function ReportsContentDraw()
    {$this->oW->kfdb->SetDebug(1);
        $s = "";

        $s .= "<style>"
             ."h3   { font-family:verdana,arial,sans serif; }"
             .".td0 { background-color:#fff; }"
             .".td1 { background-color:#eee; }"
             .".td0, .td1 { font-family:verdana,arial,sans serif; font-size:10pt; vertical-align:top; }"
             ."</style>";

        $s .= "<div>"
             ."<a href='".Site_path_self()."?report=cultivar-summary'>Summary of All Varieties in the Seed Library Collection</a><br/>"
             ."<a href='".Site_path_self()."?report=cultivar-summary-including-csci-cultivars'>"
                 ."Summary of All Varieties in the Seed Library Collection + Seed Finder</a><br/>"
             // redundant with above but broken anyway with a GROUP BY error ."<a href='".Site_path_self()."?report=adopted-summary'>Summary of Adopted Varieties</a><br/>"
             ."<a href='".Site_path_self()."?report=germ-summary'>Germination Tests</a></br>"
             ."</div>";

        switch( SEEDInput_Str('report') ) {
            case 'cultivar-summary':                            $s .= $this->cultivarSummary( false );  break;
            case 'cultivar-summary-including-csci-cultivars':   $s .= $this->cultivarSummary( true );   break;
            //case 'adopted-summary':     $s .= $this->adoptedSummary();   break;
            case 'germ-summary':                                $s .= $this->germSummary();             break;
            default:
        }

        return( $s );
    }

    private function cultivarSummary( $bUnionCSCI )
    {
        $s = "";

        $qCmd = $bUnionCSCI ? 'collreport-cultivarsummaryUnionCSCI' : 'collreport-cultivarsummary';
        $sTitle = "Summary of All Varieties in the Seed Library Collection" . ($bUnionCSCI ? " + Seed Finder" : "");

        $Q = new Qold( $this->oW->kfdb, $this->oW->sess, $this->oApp, array() );
        $rQ = $Q->Cmd( $qCmd, array('kCollection'=>1) );

        if( $rQ['bOk'] ) {
            $s .= "<div><h3 style='display:inline-block;margin-right:3em;'>$sTitle</h3>"
                      ."<a style='display:inline-block' href='".Site_UrlQ('q2.php')."?qcmd=$qCmd&kCollection=1&qfmt=xls' target='_blank'><img src='".W_ROOT."std/img/dr/xls.png' height='25'/></a>"
                 ."</div>"

                 ."<table cellpadding='5'>"
                 ."<tr><th>&nbsp;</th><th>&nbsp;</th><th>Companies</th><th>Adoption</th><th>Newest</th><th>Total grams</th><th>&nbsp;</th></tr>";
            $c = 0;
            foreach( $rQ['raOut'] as $ra ) {
                $sTDClass = "class='td$c'";
                $s .= "<tr><td $sTDClass>{$ra['species']}</td><td $sTDClass>{$ra['cultivar']}</td>"
                     ."<td $sTDClass>{$ra['csci_count']}</td><td $sTDClass>{$ra['adoption']}</td>"
                     ."<td $sTDClass>{$ra['year_newest']}</td><td $sTDClass>{$ra['total_grams']}</td>"
                     ."<td $sTDClass>".str_replace( " | ", "<br/>", $ra['notes'] )."</td></tr>";
                $c = $c ? 0 : 1;
            }
            $s .= "</table>";
        } else {
            $this->oW->oC->ErrMsg( $rQ['sErr'] );
        }
        return( $s );
    }

    private function adoptedSummary()
    {
        $s = "";

        $Q = new Qold( $this->oW->kfdb, $this->oW->sess, $this->oApp, array() );
        $rQ = $Q->Cmd( 'collreport-adoptedsummary', array('kCollection'=>1) );

        if( $rQ['bOk'] ) {
            $s .= "<div><h3 style='display:inline-block;margin-right:3em;'>Summary of Adopted Varieties</h3>"
                      ."<a style='display:inline-block' href='".Site_UrlQ('q2.php')."?qcmd=collreport-adoptedsummary&kCollection=1&qfmt=xls' target='_blank'><img src='".W_ROOT."std/img/dr/xls.png' height='25'/></a>"
                 ."</div>"

                 ."<table cellpadding='5'>"
                 ."<tr><th>&nbsp;</th><th>&nbsp;</th><th>Adoption</th><th>Newest</th><th>Total grams</th><th>&nbsp;</th></tr>";
            $c = 0;
            foreach( $rQ['raOut'] as $ra ) {
                $sTDClass = "class='td$c'";
                $s .= "<tr><td $sTDClass>{$ra['species']}</td><td $sTDClass>{$ra['cultivar']}</td><td $sTDClass>{$ra['adoption']}</td>"
                     ."<td $sTDClass>{$ra['year_newest']}</td><td $sTDClass>{$ra['total_grams']}</td>"
                     ."<td $sTDClass>".str_replace( " | ", "<br/>", $ra['notes'] )."</td></tr>";
                $c = $c ? 0 : 1;
            }
            $s .= "</table>";
        } else {
            $this->oW->oC->ErrMsg( $rQ['sErr'] );
        }
        return( $s );
    }

    private function germSummary()
    {
        $s = "";

        $Q = new Qold( $this->oW->kfdb, $this->oW->sess, $this->oApp, array() );
        $rQ = $Q->Cmd( 'collreport-germsummary', array('kCollection'=>1) );

        if( $rQ['bOk'] ) {
            $s .= "<div><h3 style='display:inline-block;margin-right:3em;'>Germination Tests</h3>"
                      ."<a style='display:inline-block' href='".Site_UrlQ('q2.php')."?qcmd=collreport-germsummary&kCollection=1&qfmt=xls' target='_blank'><img src='".W_ROOT."std/img/dr/xls.png' height='25'/></a>"
                 ."</div>"

                 ."<table cellpadding='5'>"
                 ."<tr><th>&nbsp;</th><th>&nbsp;</th><th>Lot</th><th>Grams</th><th>Tests</th></tr>";
            $c = 0;
            foreach( $rQ['raOut'] as $ra ) {
                $sTDClass = "class='td$c'";
                $s .= "<tr><td $sTDClass>{$ra['species']}</td><td $sTDClass>{$ra['cultivar']}</td><td $sTDClass>{$ra['lot']}</td>"
                     ."<td $sTDClass>{$ra['g_weight']}</td>"
                     ."<td $sTDClass>".str_replace( " | ", "<br/>", $ra['tests'] )."</td></tr>";
                $c = $c ? 0 : 1;
            }
            $s .= "</table>";
        } else {
            $this->oW->oC->ErrMsg( $rQ['sErr'] );
        }
        return( $s );
    }
}


class SLAdmin_Admin
{
    public $oW;

    private $collectionTableDef = array( 'headers-required' => array('inv-num','species','cultivar','grams'),
                                         'headers-optional' => array() );

    function __construct( Console01_Worker $oW )
    {
// kluge
        $this->oW = new SEEDApp_WorkerC( $oW->oC, $oW->kfdb, $oW->sess, $oW->lang );
    }

    function Init()
    {

    }

    function AdminContentDraw()
    {$this->oW->kfdb->SetDebug(1);
        $s = "";


        $raPills = array( 'download/upload' => array( "Download / Upload"),
                          'pgrc'      => array( "Canada: Plant Gene Resources (PGRC)" ),
                          'npgs'      => array( "USA: National Plant Germplasm System (NPGS)" ),
                          'sound'     => array( "Sound Tests" ),
        );


        $s .= "<style>"
             // Bootstrap uses quirks.css to disallow font-size inheritance inside tables.
             // So li outside tables has font-size 14px but inside tables it's 16px
             .".nav-pills > li { font-size:14px; }"
             // Bootstrap puts a 20px top margin on <h> which is too much
             .".DownloadBodyHeading { margin-top:5px }"
             ."</style>";


        $oSVA = new SEEDSessionVarAccessor( $this->oW->sess, 'SLAdmin' );
        $oUIPills = new SEEDUIWidgets_Pills( $raPills, 'pMode', array( 'oSVA' => $oSVA, 'ns' => '' ) );
        $sLeftCol = $oUIPills->DrawPillsVertical();


        $pMode = $oUIPills->GetCurrPill();
        switch( $pMode ) {
            case 'download/upload':
                $sDownloadCtrl = "Range of inventory numbers <input type='text' name='rngKInv'/>";
                $raParms = array( 'label'=>"Seed Collection contents",
                                  'downloadaction'=>$_SERVER['PHP_SELF'],
                                  'downloadctrl'=>$sDownloadCtrl,
                                  'uploadaction'=>$_SERVER['PHP_SELF'],
                                  'uploadctrl'=> "<input type='hidden' name='cmd' value='collection_upload' />",
                                  'seedTableDef'=>$this->collectionTableDef );
                $s .= Console01UI_DownloadUpload( $this->oW, $raParms );
                break;

        }

        return( $s );
    }
}


class SLAdmin_Accession extends Console01_Worker1
{
    private $oSLDB_AP;    // A left join P, so we can see accessions that don't have pcv
    private $oSLDB_IxAP;  // I x A left join P

    function __construct( Console01 $oC, KeyFrameDB $kfdb, SEEDSessionAccount $sess )  // kfdb is kfdb2
    {
        parent::__construct( $oC, $kfdb, $sess );
        $this->oSLDB_AP   = new SLDB_A_P( $this->kfdb, $this->sess->GetUID() );
        $this->oSLDB_IxAP = new SLDB_IxA_P( $this->kfdb, $this->sess->GetUID() );
    }

    function Init()
    {
        $raCompParmsA = array(
            "Label" => "Accession",

            "ListCols" => array( array( "label"=>"Accession",      "colalias"=>"_key",       "w"=>50 ),
                                 array( "label"=>"pcv",            "colalias"=>"fk_sl_pcv",  "w"=>200 ), //"colsel" => array("filter"=>"")),
                                 array( "label"=>"oname",          "colalias"=>"oname",      "w"=>200 ),
                                 array( "label"=>"spec",           "colalias"=>"spec",       "w"=>75 ),
                                 array( "label"=>"Batch",          "colalias"=>"batch_id",   "w"=>50 ),
                                 array( "label"=>"Location",       "colalias"=>"location",   "w"=>50 ),
                                 array( "label"=>"g",              "colalias"=>"g_have",     "w"=>50 ),
                                 array( "label"=>"g Backup",       "colalias"=>"g_pgrc",     "w"=>50 ),
                                 array( "label"=>"DeAcc",          "colalias"=>"bDeAcc",     "w"=>10 ),

                               ),
            "ListSize" => 10,
//          "ListSizePad" => 1,
            "fnListRowTranslateRA" => array($this,"Acc_ListRowTranslateRA"),
            "fnFormDraw"      => array($this,"Acc_FormDraw"),
            "SearchToolCols"  => array( array("Key"=>"A._key","OName"=>"oname","Spec"=>"spec","Batch"=>"batch_id","Location"=>"location") ),
            "raSEEDFormParms" => array('DSParms'=>array('fn_DSPostStore'=>array(&$this,'Acc_DSPostStore'))),
            "fnListFilter"    => array($this,'fnListFilter'),

            "bReadonly"=> !($this->sess->CanWrite( "SL" ))
        );

        $raCompParmsB = array(
            "SearchToolCols"  => array( array("Inventory #"=>"I._key","Accession #"=>"A._key","PCV #"=>"P._key","OName"=>"A.oname","PName"=>"P.name","Location"=>"I.location","Notes"=>'A.notes') ),
            "raSEEDFormParms" => array('DSParms'=>array('fn_DSPreStore'=>array(&$this,'Inv_DSPreStore'))),
        );

        $kfrelA = $this->oSLDB_AP->GetKFRel();
        $kfrelB = $this->oSLDB_IxAP->GetKFRel();

        // Initialize two components: A is the main accession stuff, B is used to process the Inventory subforms
        // Inventory subforms write sfB* fields, and this function processes an Update on those when it does a ComponentInit(B).
        $this->oC->CompInitB( $kfrelA, $raCompParmsA, $kfrelB, $raCompParmsB );
    }

    function fnListFilter()
    {
        /* How to show a list of Accessions, and be able to search it on Inventory?
         * 1) Show a list of Accessions, with subforms that query Inventory. The list has one row per Accession.
         * 2) Instead of an Accession SearchTool, draw a IxA SearchTool (this is available from oCompB)
         * 3) Comp A's SearchToolDBCond will return nothing because it is not drawn.
         * 4) Set this method in Comp A to return a condition that will be ANDed with the list conditions
         * 5) Here read the CompB SearchTool parms (Inventory X Accession) and get a list of all matching IxA rows
         * 6) Extract the Accession numbers, uniquify (technically optional) and return "IN (set of A numbers)" to Comp A's List
         */
        $sCond = "";
        $raKAcc = array();

        $sSearch = $this->oC->oCompB->SearchToolGetCond();

        if( !empty($sSearch) && ($kfr = $this->oC->oCompB->kfrel->CreateRecordCursor( $sSearch )) ) {
            while( $kfr->CursorFetch() ) {
                $raKAcc[] = $kfr->Value( "A__key" );
            }
            if( count($raKAcc) ) {
                $sCond = " (A._key IN (".implode( ',', $raKAcc ).") ) ";
            }
        }

        return( $sCond );
    }

    function ControlDraw()
    {
        $s = "<table border='0' width='100%'><tr valign='top'>"
                // draw the CompB search control and use it to obtain a list of acc_id   See fnListFilter   .$this->oC->oComp->SearchToolDraw()
                ."<td>".$this->oC->oCompB->SearchToolDraw()."</td>"
                ."<td style='text-align:right'>"
                ."<a href='sl_dump.php?cmd=accessions' target='_blank'><img src='".W_ROOT."std/img/dr/xls.png' height='25'/></a>"
                ."</td></tr></table>";
        return( $s );
    }

    function ContentDraw()
    {
        $s = "<h1>Don't use this anymore - use <a href='http://seeds.ca/app/collection'>seeds.ca/app/collection</a> instead</h1>";

        if( $this->sess->CanAdmin( "SL" ) ) {
            $s .= $this->oC->CompListForm_Vert( array( 'bAllowDelete'=>false ) );
        }
        return( $s );
    }

    function Acc_FormDraw( $oForm )
    {
        $s = "";

      //  $s = $this->jsStuff();

        $s .= "<TABLE class='slAdminForm' border='0' cellpadding='10'>"
        	."<TR valign='top'>"

             // Left column
            ."<TD>"
            .$this->accFormDraw_Left( $oForm )
            ."</TD>"


        	// Right column
            ."<TD style='border-left:1px solid #555'>"

        	."<TABLE border='0'>";

        $s .= $oForm->ExpandForm(
                "||| g Original || [[g_original]]"
               ."||| g PGRC     || [[g_pgrc]]"
               ."||| {colspan='2'} <HR/>"
               ."||| Spec       || [[spec]]"
               ."||| g Have     || [[g_have | disabled]]"
               ."||| Location   || [[location | disabled]]"
               ."||| Deaccessioned || [[bDeAcc | disabled]]"
               ."||| psp (obsolete) || [[psp_obsolete | disabled]]"

        );

        $s .= "</TABLE>"

            ."</TD>"

            // Inventory
            ."<TD style='padding-left:3em'>"
            .$this->accFormDraw_Inventory( $oForm )

            ."</TD></TR>"
        	."<TR><TD valign='top'>Notes</TD></TR>"
        	."<TR><TD valign='top' colspan='2'>".$oForm->TextArea('notes', "")."</TD></TR>"
            ."</TABLE>"
            ."<BR/>"
            ."<script type='text/javascript'>\n"
	 		."GenerateList();\n"
	 		."</script>\n"
            ."<INPUT type='submit' value='Save'>";

        return( $s );
    }

    function accFormDraw_Left( $oForm )
    {
        $s = "<TABLE border='0'>";

        $raP = $this->sess->CanAdmin('SL') ? array('sRightTail'=>" (Admin)", 'size'=>10) : array('readonly'=>true);
        $s .= "<TR>".$oForm->TextTD('_key',"Accession #", $raP )."</TR>";

            //."<select id='pcvlist' onchange='FillForm()'>"
            //."<option>PCV List</option>\n"
            //."</select>\n"
            //."<input type='submit' value='Search' onclick='GenerateList()'>"
            //."<br/>"

        $s .= $oForm->ExpandForm(
                "||| PCV            || [[fk_sl_pcv | size:10]] <FONT size='2'>[[Value:P_psp]] : [[Value:P_name]]"
               ."||| Original Name  || [[oname]]"
               ."||| {colspan='2'} <HR/>"

               ."||| Batch          || [[batch_id]]"
               ."||| Grower/Source  || [[x_member]]"
               ."||| Date Harvested || [[x_d_harvest]]"
               ."||| {colspan='2'} <HR/>"

               ."||| Parent Desc    || [[parent_src]]"
               ."||| {colspan='2'} <B>External Origin:</B>"
               ."||| Date Received  || [[x_d_received]]"
               ."||| {colspan='2'} <B>Internal Origin:</B>"
               ."||| Parent Inv#    || [[parent_acc]]"
            );

            //global $kfdb1;
            //if( ($p = $oForm->Value('parent_acc')) ) {
            //  $ra = $kfdb1->QueryRA( "SELECT P.psp as psp,P.name as pcv FROM sl_accession A,sl_pcv P WHERE A.fk_sl_pcv=P._key AND A._key='$p'" );
            //  $s .= "<TD>".$ra['psp']." : ".$ra['pcv']."</TD>";
            //}
        $s .= "</TABLE>";

        return( $s );
    }


    function accFormDraw_Inventory( $oForm )
    {
        $s = "";

        //$oI = new SLDB_IxAxP( $this->kfdb, 0 );
        //$kfrelInv = $oI->GetKFRel();
        //$oB = new KeyFrameUIForm( $kfrelInv, 'B' );

        $oB = $this->oC->oCompB->oForm;

        if( $oForm->GetKey() ) {
            $raKFR = $this->oC->oCompB->kfrel->GetRecordSet( "A._key='".$oForm->GetKey()."'" );
            $raKFR[] = $this->oC->oCompB->kfrel->CreateRecord();
        } else {
            // New Accession:  make two empty inventory subforms
            $raKFR = array();
            $raKFR[] = $this->oC->oCompB->kfrel->CreateRecord();
            $raKFR[] = $this->oC->oCompB->kfrel->CreateRecord();
        }

        $iRow = 0;
        foreach( $raKFR as $kfr ) {
            $oB->SetKFR( $kfr );
            $oB->SetRowNum( $iRow++ );

            $oB->SetValue( 'fk_sl_accession', $oForm->GetKey() );

            $s .= "<fieldset>" //"<DIV style='border:1px solid #333;margin:20px;padding:10px;'>"
                 ."<legend>".($oB->GetKey() ? ("Inventory #".$oB->GetKey()) : "Add New Inventory" )."</legend>"
                 ."<br/>"
                 .$oB->HiddenKey()
                 .$oB->Hidden( 'fk_sl_accession' )
                 ."<TABLE border='0'>"
                 .$oB->ExpandForm(
                     "||| Weight (g)    || [[g_weight]]"
                    ."||| Location      || [[location]]"
                    ."||| Split from    || [[parent_kInv]]"
                    ."||| Split date    || [[dCreation]]"
                    ."||| Deaccessioned || [[bDeAcc]]"
                 )
                 ."</TABLE>"
                 ."</fieldset><P>&nbsp;</P>"; //."</DIV>";
        }

        return( $s );
    }

    function AccessionsFormDraw_Intake( $oForm )
    /*******************************************
     */
    {
        if( $oForm->GetKey() )  return( $this->AccessionsFormDraw_Std( $oForm ) );

        $s = $oForm->Hidden( 'bIntakeInsert', 1 )
            ."<TABLE class='slAdminForm' border='0' cellpadding='10'>"
            ."<TR><TD valign='top'>"
            // Left column
            .$this->AccessionsFormDraw_Left( $oForm )
            // End left column

            ."</TD><TD valign='top' style='border-left:1px solid #555'>"

            // Right column
            ."<TABLE border='0'>"
            ."<TR>".$oForm->TextTD('g_have','g Have')."</TR>"
            ."<TR>".$oForm->TextTD('g_original','g Original')."</TR>"
            ."<TR>".$oForm->TextTD('g_pgrc','g PGRC')."</TR>"
            ."<TR>".$oForm->TextTD('location','Location')."</TR>"
            ."<TR>".$oForm->TextTD('bDeAcc','Deaccessioned')."</TR>"

            ."<TR><TD colspan='2'><HR/></TD></TR>"
            ."<TR><TD colspan='2'><H3>Split</H3></TD></TR>"

            ."<TR>".$oForm->TextTD('split_g_have','g Have')."</TR>"
            ."<TR>".$oForm->TextTD('split_g_original','g Original')."</TR>"
            ."<TR>".$oForm->TextTD('split_location','Location')."</TR>"
            ."<TR>".$oForm->TextTD('split_bDeAcc','Deaccessioned')."</TR>"

            ."</TABLE>"
            //End Right column

            ."</TD></TR>"
            ."<TR><TD valign='top'>Notes</TD></TR>"
            ."<TR><TD valign='top' colspan='2'>".$oForm->TextArea('notes', "")."</TD></TR>"
            ."</TABLE>"
        ."<INPUT type='submit' value='Save'>";


        return( $s );
    }




    function Acc_ListRowTranslateRA( $raValues )
    {
        if( $raValues['fk_sl_pcv']) {

            $raValues['fk_sl_pcv'] = $raValues['P_psp']. " : ".$raValues['P_name'];
        }
        return( $raValues );
    }

    function Acc_DSPostStore()
    {
//        if( $oForm->CtrlGlobal('persist_accmode') == "Intake" && $oForm->oDS->Value('bIntakeInsert') && $oForm->oDS->Value('split_g_have') ) {
//            // This should only happen if an insert was made from the Accessions Intake mode
//            $kfr = $kfr->Copy();
//            $kfr->SetKey( 0 );
//            $kfr->SetValue( 'g_pgrc', 0 );
//            $kfr->SetValue( 'g_have',     $oForm->oDS->Value('split_g_have') );
//            $kfr->SetValue( 'g_original', $oForm->oDS->Value('split_g_original') );
//            $kfr->SetValue( 'location',   $oForm->oDS->Value('split_location') );
//            $kfr->SetValue( 'bDeAcc',     $oForm->oDS->Value('split_bDeAcc') );
//            $kfr->PutDBRow();
//        }

    }

    function Inv_DSPreStore()
    /************************
        When a new inventory item is created for an existing accession, the fk_sl_accession is set via Hidden().
        But when a new inventory item is created simultaneously with a NEW accession, we must create the accession then
        set the fk_sl_accession here.
     */
    {
        $ok = true;

        $oB = $this->oC->oCompB->oForm;

        if( !$oB->GetKey() ) {
            // New inventory item: disallow if weight is zero, ensure that fk_sl_accession is set

//var_dump($oB->oDS->kfr->_values);
            if( $oB->Value( 'g_weight' ) == 0.0 ) return( false );  // this is an empty row

            if( !$oB->Value( 'dCreation') )  $oB->SetValue( 'dCreation', date('Y-m-d') );

            if( ($kAcc = $this->oC->oComp->oForm->GetKey()) ) {
                $oB->SetValue( 'fk_sl_accession', $kAcc );
            } else {
                $ok = false;
                $this->oC->ErrMsg( "Cannot add inventory: accession id is zero" );
            }
        }
        return( $ok );
    }




    function jsStuff()
    {
        $s = "\n<script type='text/javascript'>\n"
    		."function loadXMLDoc(XMLname)
			{
  				var xmlDoc;
  				if (window.XMLHttpRequest)
 				{
    				xmlDoc=new window.XMLHttpRequest();
    				xmlDoc.open('GET',XMLname,false);
    				xmlDoc.send('');
    				return xmlDoc.responseXML;
  				}
  				else if (ActiveXObject('Microsoft.XMLDOM'))
  				{
    				xmlDoc=new ActiveXObject('Microsoft.XMLDOM');
    				xmlDoc.async=false;
    				xmlDoc.load(XMLname);
    				return xmlDoc;
  				}
  				alert('Error loading document!');
  				return null;
			}\n"
			."function AddItem(V,i)
			{
   				var x=document.getElementById('pcvlist');
				var opt = document.createElement('option');
        		opt.text = V;
        		opt.value = i;
        		x.add(opt);
			}\n"
			."function GenerateList()
			{
    			var L = document.getElementById('pcvlist');
    			var K = document.getElementById('sfAp_fk_sl_pcv').value;

			    var N = document.getElementById('sfAp_oname').value;

    			var K2 = new RegExp(K, 'i');

    			var N2 = new RegExp(N, 'i');
				L.options.length = 0;
				AddItem('Results');

				xmlDoc=loadXMLDoc('pcv.xml') // Path to the XML file;
				var M = xmlDoc.getElementsByTagName('entry');
				for (i=0;i<M.length;i++){
					try{
						x = xmlDoc.getElementsByTagName('key')[i].childNodes[0].nodeValue;
						y = xmlDoc.getElementsByTagName('psp')[i].childNodes[0].nodeValue;
						z = xmlDoc.getElementsByTagName('name')[i].childNodes[0].nodeValue;
						if( (x.match(K2) && z.match(N2))||(x.match(K2) && N == '')||((K == ''||K == '0') && z.match(N2))||((K == ''||K == '0') && N == '')){
							AddItem(x+' '+y+':'+z,i);
						}
					}catch(err){
						x = xmlDoc.getElementsByTagName('key')[i].childNodes[0].nodeValue;
						y = xmlDoc.getElementsByTagName('psp')[i].childNodes[0].nodeValue;
						if(  (x.match(K2) && N == '')||(K == '' && N == '')){
							AddItem(x+' '+y+': ',i);
    					}
					}
				}
			}\n"
			."function FillForm()
			{
  				var k = document.getElementById('sfAp_fk_sl_pcv');
  				var p = document.getElementById('sfAp_P_psp');
  				var n = document.getElementById('sfAp_P_name');
  				var o = document.getElementById('sfAp_oname');


				var l = document.getElementById('pcvlist');
  				var v = l.options[l.selectedIndex].value;

  				k.value = xmlDoc.getElementsByTagName('key')[v].childNodes[0].nodeValue;
 				p.value = xmlDoc.getElementsByTagName('psp')[v].childNodes[0].nodeValue;
 				try{
  					n.value = xmlDoc.getElementsByTagName('name')[v].childNodes[0].nodeValue;
  					o.value = xmlDoc.getElementsByTagName('name')[v].childNodes[0].nodeValue;
  				}catch(err){
     				n.value = '';
     				o.value = '';
  				}

			}"
			."</script>";
        return( $s );
    }

}

?>
