<?php

/* RosettaSEED entry point
 *
 * Copyright (c) 2014-2019 Seeds of Diversity Canada
 *
 */

if( !defined("SITEROOT") )  define("SITEROOT", "../../");

/*****
 * Using seeds1 session permissions so we can switch between My Collection without getting logged out
 */
include_once( SITEROOT."site.php" );

include_once( SEEDCORE."SEEDProblemSolver.php" );
include_once( SEEDCOMMON."console/console01kfui.php" );
include_once( SEEDCOMMON."sl/sl_db.php" );
include_once( SEEDCOMMON."sl/sl_db_admin.php" );    // get stats on referenced sp and cv

// Access to the application is given if any of the tabs are accessible
// Inaccessible tabs are Ghosted
$raPerms = array( 'Cultivars'    => array('W SL'),
                  'Species'      => array('W SLRosetta'),
                  'CultivarsSyn' => array('W SL'),
                  'SpeciesSyn'   => array('W SL'),
                  'Admin'        => array('A SLRosetta'),
                                    '|'   // the above are disjunctions for application access
);
list($kfdb, $sess) = SiteStartSessionAccount( $raPerms );

//var_dump($_REQUEST);
$kfdb->SetDebug(1);


class MyConsole extends Console01KFUI
{
    public $oW;
    private $oSLDB;
    private $oSLDBRosetta;

    function __construct( KeyFrameDB $kfdb, SEEDSession $sess, $raParms )
    {
        parent::__construct( $kfdb, $sess, $raParms );
        $this->oSLDB = new SLDB_Master( $kfdb, $sess->GetUID() );
        $this->oSLDBRosetta = new SLDB_Rosetta( $kfdb, $sess->GetUID() );
    }

    function TFmainCultivarsInit()           { $this->myInit( 'cultivars' ); }
    function TFmainSpeciesInit()             { $this->myInit( 'species' ); }
    function TFmainCultivarsSynInit()        { $this->myInit( 'cultivars_syn' ); }
    function TFmainSpeciesSynInit()          { $this->myInit( 'species_syn' ); }
    function TFmainAdminInit()               { $this->oW = new Rosetta_Admin( $this, $this->kfdb, $this->sess );
                                               $this->oW->Init();
                                             }

    function myInit( $k )
    {
        switch( $k ) {
            case 'cultivars':      $kfrel = $this->oSLDBRosetta->GetKfrel('PxS');  break;
            case 'species':        $kfrel = $this->oSLDBRosetta->GetKfrel('S');  break;
            case 'cultivars_syn':  $kfrel = $this->oSLDBRosetta->GetKfrel('PYxPxS');  break;
            case 'species_syn':    $kfrel = $this->oSLDBRosetta->GetKfrel('SYxS');  break;
            default:               die( "No kfrel in init" );
        }

        $raCompParms = array(
            'cultivars'=> array(
                  "Label" => "Cultivar",
                  "ListCols" => array( array( "label"=>"Cultivar #",    "colalias"=>"_key",      "w"=>100 ),
                                       array( "label"=>"Species",       "colalias"=>"S_name_en", "w"=>200 ),
                                       array( "label"=>"Cultivar name", "colalias"=>"name",      "w"=>300 ),
                                     ),
                  "ListSize" => 15,
                  "fnFormDraw"      => array($this,"CultivarsFormDraw"),
                  "fnPreDelete"     => array($this,"CultivarsPreDelete"),
                  "raSEEDFormParms" => array( "DSParms" => array('fn_DSPreStore'=>array($this,'CultivarsDSPreStore') ) )
            ),
            'species' => array(
                  "Label" => "Species",
                  "ListCols" => array( array( "label"=>"Sp #",      "colalias"=>"_key",      "w"=>30 ),
                                       array( "label"=>"psp",       "colalias"=>"psp",       "w"=>80),
                                       array( "label"=>"Name EN",   "colalias"=>"name_en",   "w"=>120),
                                       array( "label"=>"Name FR",   "colalias"=>"name_fr",   "w"=>120 ), //, "colsel" => array("filter"=>"")),
                                       array( "label"=>"Index EN",  "colalias"=>"iname_en",  "w"=>120),
                                       array( "label"=>"Index FR",  "colalias"=>"iname_fr",  "w"=>120),
                                       array( "label"=>"Botanical", "colalias"=>"name_bot",  "w"=>120),
                                       array( "label"=>"Family EN", "colalias"=>"family_en", "w"=>120),
                                       array( "label"=>"Family FR", "colalias"=>"family_fr", "w"=>120),
                                       array( "label"=>"Category",  "colalias"=>"category",  "w"=>60, "colsel" => array("filter"=>"")),
                                     ),
                  "ListSize" => 15,
                  "ListSizePad" => 1,
                  "fnFormDraw" => array($this,"SpeciesFormDraw"),
                  "fnPreDelete"     => array($this,"SpeciesPreDelete"),
            ),
            'cultivars_syn' => array(
                  "Label" => "Cultivar Synonym",
                  "ListCols" => array( array( "label"=>"Primary cultivar", "colalias"=>"P_name",    "w"=>300 ),
                                       array( "label"=>"Synonym",          "colalias"=>"name",      "w"=>300),
                                       array( "label"=>"T",                "colalias"=>"t",         "w"=>50),
                  ),
                  "ListSize" => 15,
                  "ListSizePad" => 1,
                  "fnListRowTranslate" => array($this,"CultivarsSynListRowTranslate"),
                  "fnFormDraw" => array($this,"CultivarsSynFormDraw"),
            ),
            'species_syn' => array(
                  "Label" => "Species Synonym",
                  "ListCols" => array( array( "label"=>"id",        "colalias"=>"_key",      "w"=>30 ),
                                       array( "label"=>"psp",       "colalias"=>"S_psp",       "w"=>80),
                                       array( "label"=>"Synonym",   "colalias"=>"name",      "w"=>120),
                                     ),
                  "ListSize" => 15,
                  "ListSizePad" => 1,
                  "fnFormDraw" => array($this,"SpeciesSynFormDraw"),
            ),
        );

        $this->CompInit( $kfrel, $raCompParms[$k] );
    }


    function TabSetPermission( $tsid, $tabname )
    {
        global $raPerms;

        return( ($tsid == 'TFmain' && is_array($ra = @$raPerms[$tabname]) && $this->sess->TestPermRA( $ra ))
                ? Console01::TABSET_PERM_SHOW
                : Console01::TABSET_PERM_GHOST );
    }

    function TabSetControlDraw( $tsid, $tabname )
    {
        return( $tabname == 'Admin' ? "" : ("<div>".$this->oComp->SearchToolDraw()."</div>") );
    }

    function TabSetContentDraw( $tsid, $tabname )
    {
        return( $tabname == 'Admin' ? $this->oW->GetOutput()
                                    : $this->CompListForm_Vert() );    // Cultivars/Syn, Species/Syn
    }

    function CultivarsDSPreStore( KeyFrameDataStore $oDS )
    {
        if( !($kSp = $oDS->value('fk_sl_species')) )  return( false );
        if( !($psp = $this->kfdb->Query1( "SELECT psp FROM seeds.sl_species WHERE _key='$kSp'" )) )  return( false );
// deprecate, just rely on fk_sl_species because people can easily change sl_species.psp
$oDS->SetValue( 'psp', $psp );

        //$oStats = new SLDB_Admin_Stats( $this->kfdb );
        //$raRef = $oStats->GetReferencesToPCV( $)

        return( true );
    }

    function CultivarsPreDelete( KFRecord $kfr )
    {
        $ok = false;

        // don't share oStats object with FormDraw because it caches its information, which possibly changes during update/delete
        $oStats = new SLDB_Admin_Stats( $this->kfdb );
        $raRef = $oStats->GetReferencesToPCV( $kfr->Key() );
        if( $raRef['nTotal'] ) {
            $this->ErrMsg( "Can't delete this cultivar because it's referenced in the Seed Library or a Source Record" );
        } else {
            $this->UserMsg( $kfr->Expand( "Deleted [[psp]] : [[name]]" ) );
            $ok = true;
        }
        return( $ok );
    }

    private function drawStats( $ra )
    {
        $s = "Seed Library accessions: {$ra['nSLAcc']}<br/>"
            ."Source list records: "
            .($ra['nSLCV1'] ? "PGRC, " : "")
            .($ra['nSLCV2'] ? "NPGS, " : "")
            .("{$ra['nSLCV3']} compan".($ra['nSLCV3'] == 1 ? "y" : "ies"));

        return( $s );
    }

    function CultivarsFormDraw( $oForm )
    {
        $raOpts = $this->getPSPOpts();

        $s = "";

        if( ($kPCV = $oForm->GetKey()) ) {
            $raSyn = $this->kfdb->QueryRowsRA( "SELECT * FROM seeds.sl_pcv_syn WHERE _status='0' AND fk_sl_pcv='$kPCV'" );
            $sTmpl = "[[name]]";
            $sSyn = SEEDCore_ArrayExpandRows( $raSyn, ", $sTmpl", true, array('sTemplateLast'=>$sTmpl) );

            // don't share this with PreStore because it caches its information, which possibly changes during update (after PreStore)
            $oStats = new SLDB_Admin_Stats( $this->kfdb );

            $s .= "<div style='float:right;width:30%;border:1px solid #aaa;padding:10px'>"
                 .($sSyn ? ("Synonyms: $sSyn<br/>") : "")
                 .$oStats->DrawReferencesToPCV( $kPCV )
                 ."</div>";
        }

        $s .= "<table>"
// see it in the list  .($oForm->GetKey() ? ("<tr><td colspan='2'><b>Cultivar #".$oForm->GetKey()."</b></td></tr>") : "")
            ."<tr><td><b>Species</b></td><td>".$oForm->Select2( 'fk_sl_species', $raOpts )
            .SEEDStd_StrNBSP('',5)
            ."(psp is ".$oForm->Text( "psp", "", array('readonly'=>true) ).")"
            ."</td></tr>"
            ."<tr>".$oForm->TextTD( "name", "Name" )."</tr>"
            ."<tr>".$oForm->TextAreaTD( "notes", "Notes" )."</tr>"
            ."</table>"
            ."<input type='submit' value='Save'>";

        return( $s );
    }

    function SpeciesFormDraw( $oForm )
    {
        $sStats = "";
        if( ($kSp = $oForm->GetKey()) ) {
            $sStats = "<div style='border:1px solid #aaa;display:inline-block;margin-left:10px;padding:10px'>"
                     .$this->drawStats( $this->speciesGetStats( $kSp ) )
                     ."</div>";
        }

        $s = "<TABLE class='slAdminForm' width='100%' border='0'>"
            ."<TR>".$oForm->TextTD("psp", "psp").$oForm->TextTD("name_en", "Name EN").$oForm->TextTD("name_fr", "Name FR")."</TR>"
            ."<TR>".$oForm->TextTD("name_bot", "Botanical").$oForm->TextTD("iname_en", "Index EN").$oForm->TextTD("iname_fr", "Index FR")."</TR>"
            ."<TR>".$oForm->TextTD("category", "Category").$oForm->TextTD("family_en", "Family EN").$oForm->TextTD("family_fr", "Family FR")."</TR>"
            ."<TR>".$oForm->TextAreaTD( "notes", "Notes", 80, 5, array('width'=>'90%','attrsTD2'=>"colspan='3'") )
            ."<td colspan='2'>".$sStats."&nbsp;</td>"
            ."</TR>"
            ."</TABLE>"
            ."<INPUT type='submit' value='Save'>";
        return( $s );
    }

    function SpeciesPreDelete( KFRecord $kfr )
    {
        $delOk = false;

        if( !$kfr->Key() )  goto done;

// also sl_species_syn and sl_species_meta
// put speciesGetReferences, speciesDrawReferences, speciesNotUsed in SLDB_Admin
// CultivarsGetReferences should include sl_pcv_syn and sl_pcv_meta
        $raStats = $this->speciesGetStats( $kfr->Key() );
        if( $raStats['nSLAcc'] || $raStats['nSLCV1'] || $raStats['nSLCV2'] || $raStats['nSLCV3'] ) {
            $this->ErrMsg( "Cannot delete this species record because it is referenced in Library or Sources" );
            goto done;
        }

        $delOk = true;

        done:
        return( $delOk );
    }

    private function speciesGetStats( $kSp )
    {
        $ra = array();
        $ra['nSLAcc'] = $this->kfdb->Query1( "SELECT count(*) FROM seeds.sl_accession A,seeds.sl_pcv P WHERE P.fk_sl_species='$kSp' AND P._key=A.fk_sl_pcv" );

        $sSql = "SELECT count(*) FROM seeds.sl_cv_sources CV,seeds.sl_pcv P WHERE P._key=CV.fk_sl_pcv AND P.fk_sl_species='$kSp'";
        $ra['nSLCV1'] = $this->kfdb->Query1( $sSql." AND fk_sl_sources='1'" );
        $ra['nSLCV2'] = $this->kfdb->Query1( $sSql." AND fk_sl_sources='2'" );
        $ra['nSLCV3'] = $this->kfdb->Query1( $sSql." AND fk_sl_sources>='3'" );

        return( $ra );
    }

    function CultivarsSynListRowTranslate( $kfr )
    {
        $ra = $kfr->ValuesRA();

        $ra['P_name'] = SEEDStd_ArrayExpand($ra, "[[S_name_en]] : [[P_name]] ([[P__key]])" );

        return( $ra );
    }

    function CultivarsSynFormDraw( $oForm )
    {
        $s = "<TABLE class='slAdminForm SFUAC_Anchor' width='100%' border='0' cellpadding='0' style='position:relative'>"
            .$oForm->ExpandForm(
               "||| *Primary cultivar* "
                  ."|| <span id='cultivarText' style='font-size:9pt'>[[Value:P_psp]] : [[Value:P_name]] ([[Value:P__key]])</span> "
                     ."[[dummy_pcv | size:10 class:SFU_AutoComplete | placeholder='Search']] "
                     ."[[hidden:fk_sl_pcv]]"
// this is the selection box that gets appended to the SFU_AutoComplete
// should be created dynamically
                     ."<select class='SFUAC_Select'></select>"
                  ."|| *Synonym* || [[name]] || *T* || [[t]]"
             )
            ."<TR>".$oForm->TextAreaTD( "notes", "Notes", 80, 5, array('width'=>'90%','attrsTD2'=>"colspan='5'") )."</TR>"
            ."</TABLE>"
            ."<INPUT type='submit' value='Save'>";

// for dev, this overrides the seeds.ca url
        $s .= "<script type='text/javascript'>SEEDFormUIParms['urlQ']='".Site_UrlQ()."';</script>";

        return( $s );
    }

    function SpeciesSynFormDraw( $oForm )
    {
        $raOpts = $this->getPSPOpts();

        $s = "<TABLE class='slAdminForm' width='100%' border='0'>"
            ."<TR><td valign='top'><label>Species</label></td><td valign='top'>".$oForm->Select2('fk_sl_species', $raOpts, "", array())."</td>".$oForm->TextTD('name', "Name").$oForm->TextTD('t', "T")."</TR>"
            ."<TR>".$oForm->TextAreaTD( "notes", "Notes", 80, 5, array('width'=>'90%','attrsTD2'=>"colspan='5'") )."</TR>"
            ."</TABLE>"
            ."<INPUT type='submit' value='Save'>";
        return( $s );
    }

    private function getPSPOpts()
    {
        $raSp = $this->oSLDB->GetList( 'S', "", array('sSortCol'=>'psp') );    // get all psp
        $raOpts = array( "-- Choose --" => 0 );
        foreach( $raSp as $ra ) {
            if( $ra['psp'] ) {    // !psp is not allowed in Rosetta, but that rule can be broken though invalid
                $raOpts[$ra['psp']] = $ra['_key'];
            }
        }
        return( $raOpts );
    }
}

class Rosetta_Admin extends Console01_Worker1
{
    private $oPS;

    function __construct( Console01 $oC, KeyFrameDB $kfdb, SEEDSession $sess )
    {
        parent::__construct( $oC, $kfdb, $sess );
    }

    function Init()
    {
        $raParms = array( 'kfdb' => $this->kfdb,
                          'bShowSql' => true );

        $this->oPS = new SEEDProblemSolverUI( $this->getPSDefs(), $raParms );
        $this->oPS->TestAll();
    }

    function GetOutput()
    {
        list($sTabs,$sCurrTest) = $this->oPS->DrawTests( '' );

        $s = "<div class='container-fluid'><div class='row'>"
            ."<div class='col-sm-4'>$sTabs</div>"
            ."<div class='col-sm-8'>$sCurrTest</div>"
            ."</div></div>";

        return( $s );
    }

    private function getPSDefs()
    {
        $raDefs = array(
            'species_undefined'
                => array( 'title' => "Check for blank species identifiers",
                          'testType' => 'rows0',
// fill in all the psp, make nonfatal=false because later tests rely on this test to prevent silly matches
                          'bNonFatal' => true,
                          'failLabel' => "Species identifiers missing",
                          'failShowRow' => "k=[[_key]], psp=[[psp]], en=[[name_en]], fr=[[name_fr]], bot=[[name_bot]]",
                          'testSql' =>
                              "SELECT _key,psp,name_en,name_fr,name_bot FROM seeds.sl_species"
                             ." WHERE _status=0 AND (psp='' OR name_en='' OR name_fr='' OR name_bot='')" ),
            'species_unique'
                // Join sl_species to itself to find names duplicated across rows
                //      Since S1._key<S2._key:
                //      - it is okay for psp==name_en==name_fr==name_bot in the same row
                //      - necessary to test both S1.x=S2.y and also S1.y=S2.x because the key restriction means they aren't symmetrical
                => array( 'title' => "Check for duplicate species names",
                          'testType' => 'rows0',
                          //'bNonFatal' => true,
                          'failLabel' => "Species names not unique",
                          'failShowRow' => "psp1=[[psp1]], en1=[[en1]], fr1=[[fr1]], bot1=[[bot1]], psp2=[[psp2]], en2=[[en2]], fr2=[[fr2]], bot2=[[bot2]]",
                          'testSql' =>
                              "SELECT S1.psp as psp1,S1.name_en as en1,S1.name_fr as fr1,S1.name_bot as bot1, "
                                    ."S2.psp as psp2,S2.name_en as en2,S2.name_fr as fr2,S2.name_bot as bot2 "
                             ." FROM seeds.sl_species S1, seeds.sl_species S2"
                             ." WHERE S1._key < S2._key AND "
                                    ."S1._status='0' AND S2._status='0' AND"
                                    ."(S1.psp=S2.psp OR"
                                    ." S1.psp=S2.name_en OR"
                                    ." S1.psp=S2.name_fr OR"
                                    ." S1.psp=S2.name_bot OR"

                                    ." S1.name_en=S2.psp OR"
                                    ." S1.name_en=S2.name_en OR"
                                    ." S1.name_en=S2.name_fr OR"
                                    ." S1.name_en=S2.name_bot OR "

                                    ." S1.name_fr=S2.psp OR"
                                    ." S1.name_fr=S2.name_en OR"
                                    ." S1.name_fr=S2.name_fr OR"
                                    ." S1.name_fr=S2.name_bot OR "

                                    ." S1.name_bot=S2.psp OR"
                                    ." S1.name_bot=S2.name_en OR"
                                    ." S1.name_bot=S2.name_fr OR"
                                    ." S1.name_bot=S2.name_bot)"

// fill in all the psp, make species_undefined nonfatal=false and uncomment this
                ." AND S1.psp<>'' AND S1.name_en<>'' AND S1.name_fr<>'' AND S1.name_bot<>''" ),

            'species_syn_unique'
                // Join sl_species_syn to itself to find duplicates
                => array( 'title' => "Check for duplicate species synonyms",
                          'testType' => 'rows0',
                          //'bNonFatal' => true,
                          'failLabel' => "Species synonyms not unique",
                          'failShowRow' => "syn=[[name]], psp1=[[psp1]], psp2=[[psp2]], k1=[[k1]], k2=[[k2]]",
                          'testSql' =>
                              "SELECT SY1._key as k1,SY2._key as k2, SY1.name as name, S1.psp as psp1, S2.psp as psp2"
                             ." FROM seeds.sl_species_syn SY1, seeds.sl_species_syn SY2, seeds.sl_species S1, seeds.sl_species S2"
                             ." WHERE SY1._key < SY2._key AND "
                                    ."SY1.name=SY2.name AND "
                                    ."S1._key=SY1.fk_sl_species AND "
                                    ."S2._key=SY2.fk_sl_species AND "
                                    ."SY1._status='0' AND SY2._status='0' AND S1._status='0' AND S2._status='0'" ),

            'species_syn_not_copy'
                // Join sl_species X sl_species_syn to check that no synonyms are simply copies of sl_species names
                => array( 'title' => "Check for species synonym copies",
                          'testType' => 'rows0',
                          //'bNonFatal' => true,
                          'failLabel' => "Species synonyms same as species names",
                          'failShowRow' => "syn=[[syn]], psp=[[psp]], name_en=[[name_en]], name_fr=[[name_fr]], name_bot=[[name_bot]], kSyn=[[kSyn]]",
                          'testSql' =>
                              "SELECT SY._key as kSyn,SY.name as syn,S.psp as psp,S.name_en as name_en,S.name_fr as name_fr,S.name_bot as name_bot"
                             ." FROM seeds.sl_species_syn SY,seeds.sl_species S"
                             ." WHERE (SY.name=S.psp OR "
                                     ."SY.name=S.name_en OR "
                                     ."SY.name=S.name_fr OR "
                                     ."SY.name=S.name_bot) AND "
                                     ."SY._status='0' AND S._status='0'" ),

            'pcv_undefined'
                => array( 'title' => "Check for blank pcv identifiers",
                          'testType' => 'rows0',
                          'bNonFatal' => true,
                          'failLabel' => "pcv identifiers missing",
                          'failShowRow' => "k=[[_key]], psp=[[psp]], name=[[name]]",
                          'testSql' =>
                              "SELECT _key,psp,name FROM seeds.sl_pcv WHERE _status=0 AND (psp='' OR name='')" ),
            'pcv_unique'
                // Join sl_pcv to itself to find (fk_sl_species,name) duplicated
                => array( 'title' => "Check for duplicate cultivar names",
                          'testType' => 'rows0',
                          'bNonFatal' => true,
                          'failLabel' => "Cultivar names not unique",
                          'failShowRow' => "psp=[[psp]], name=[[name]], k1=[[k1]], k2=[[k2]]",
                          'testSql' =>
                              "SELECT P1._key as k1,P2._key as k2, P1.name as name, S.psp as psp"
                             ." FROM seeds.sl_pcv P1, seeds.sl_pcv P2, seeds.sl_species S"
                             ." WHERE P1._key < P2._key AND "
                                    ."P1.name=P2.name AND P1.fk_sl_species=P2.fk_sl_species AND "
                                    ."P1.fk_sl_species=S._key AND "
                                    ."P1._status='0' AND P2._status='0' AND S._status='0'" ),

            'pcv_syn_unique'
                // Join sl_pcv_syn to itself to find (fk_sl_pcv,name) duplicated
                => array( 'title' => "Check for duplicate cultivar synonyms",
                          'testType' => 'rows0',
                          //'bNonFatal' => true,
                          'failLabel' => "Cultivar synonyms not unique",
                          'failShowRow' => "syn=[[syn]] ([[psp]]:[[pcv]]) k1=[[k1]], k2=[[k2]]",
                          'testSql' =>
                              "SELECT SY1._key as k1,SY2._key as k2, SY1.name as syn, P.name as pcv, S.psp as psp"
                             ." FROM seeds.sl_pcv_syn SY1, seeds.sl_pcv_syn SY2, seeds.sl_pcv P, seeds.sl_species S"
                             ." WHERE SY1._key < SY2._key AND "
                                    ."SY1.name=SY2.name AND SY1.fk_sl_pcv=SY2.fk_sl_pcv AND "
                                    ."SY1.fk_sl_pcv=P._key AND "
                                    ."P.fk_sl_species=S._key AND "
                                    ."SY1._status='0' AND SY2._status='0' AND P._status='0' AND S._status='0'" ),

        );

        return( $raDefs );
    }



}


$raConsoleParms = array(
    'HEADER' => "RosettaSEED on ${_SERVER['SERVER_NAME']}",
    'CONSOLE_NAME' => "SLRosetta",
    'TABSETS' => array( "TFmain" => array( 'tabs' => array( 'Cultivars'    => array( 'label' => "Cultivars" ),
                                                            'Species'      => array( 'label' => "Species" ),
                                                            'CultivarsSyn' => array( 'label' => "Cultivar Synonyms" ),
                                                            'SpeciesSyn'   => array( 'label' => "Species Synonyms" ),
                                                            'Admin'        => array( 'label' => "Admin" )))),
    'script_files' => array( W_ROOT."std/js/SEEDStd.js", W_ROOT."std/js/SEEDFormUI.js" ),

    'bBootstrap' => true
);
$oC = new MyConsole( $kfdb, $sess, $raConsoleParms );

header( "Content-type: text/html; charset=ISO-8859-1");    // this should be on all pages so accented chars look right (on Linux anyway)

echo $oC->DrawConsole( "[[TabSet: TFmain]]" );

?>
