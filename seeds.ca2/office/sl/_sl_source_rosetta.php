<?php

/* _sl_source_rosetta.php
 *
 * Copyright 2015-2016 Seeds of Diversity Canada
 *
 * Implement the user interface for RosettaSEED tests and updates in the Seed Source tables
 */

include_once( STDINC."SEEDProblemSolver.php" );
include_once( SEEDCOMMON."sl/q/_QServerPCV.php" );

class SLSourceRosetta extends Console01_Worker
{
    private $oPS;
    private $raPSDefs;    // have to define dynamically because of variable content

    private $oQRosetta;

    function __construct( Console01 $oC, KeyFrameDB $kfdb, SEEDSession $sess )
    {
        parent::__construct( $oC, $kfdb, $sess, "EN" );

        $this->oSVA = $oC->TabSetGetSVA( 'main', 'Rosetta' );
        $this->oSVA->SmartGPC( 'SPSTest' );

        $this->getPSDefs();
        $this->oPS = new SEEDProblemSolverUI( $this->raPSDefs, array( 'kfdb'=>$this->kfdb, 'SPSTest'=>$this->oSVA->VarGet('SPSTest') ) );

        $oQ = new Q( $this->kfdb, $this->sess, array() );
        $this->oQRosetta = new QServerPCV( $oQ, array() );
    }

    function Main()
    {
        list($sRemedy,$sRemedyErr) = $this->oPS->DrawRemedyUI();
        $this->oC->UserMsg( $sRemedy );
        $this->oC->ErrMsg( $sRemedyErr );

        $sCmds = $this->drawCommands();
        list($sTabs,$sCurrTest) = $this->oPS->DrawTests( '' );

        $s = "";

        $s .= "<div class='container-fluid'><div class='row'>"
             ."<div class='col-sm-3'>$sTabs<br/><br/><div class='well'>$sCmds</div></div>"
             ."<div class='col-sm-9'>$sCurrTest</div>"
             ."</div></div>";

        return( $s );
    }

    private function style()
    {
        $s = "<style>"
            .".mystripe:nth-of-type(even) { background: #eee; } .mystripe:nth-of-type(odd) { background: #fff; }"
            ."</style>";
        return( $s );
    }

    private function drawCommands()
    {
        $s = "";
        $sResult = "";

        $cmd = SEEDSafeGPC_GetStrPlain('cmd');
        switch( $cmd ) {
            case 'rebuild':  $sResult = $this->doRebuild();    break;
            case 'pcvAdd':   $sResult = $this->doPCVAdd();     break;
        }


        $s .= $sResult
             ."<p><a href='{$_SERVER['PHP_SELF']}?cmd=rebuild'>Rebuild Names Index</a></p>";

        return( $s );
    }


    private function doRebuild()
    {
        $s = "";

        $s .= "<h4>Rebuilding the names index</h4>";

        $s .= "<p style='margin-left:10px'>";

        /* Delete the sp and cv keys from sl_cv_sources
         */
        $c = $this->kfdb->Query1( "SELECT count(*) as c FROM seeds.sl_cv_sources WHERE _status='0' AND (fk_sl_species OR fk_sl_pcv)" );
        SLSourceRosetta_BuildDB::ClearIndex( $this->kfdb );
        $s .= "Index deleted ($c entries)<br/>";

        /* Species: fill in all the fk_sl_species keys that we can find in RosettaSEED
         */
        SLSourceRosetta_BuildDB::BuildSpeciesIndex( $this->kfdb, "seeds.sl_cv_sources", "" );
        $c = $this->kfdb->Query1( "SELECT count(*) as c FROM sl_cv_sources WHERE _status='0' AND fk_sl_species" );
        $s .= "Species index rebuilt ($c entries)<br/>";

        /* Cultivars: fill in all the cv keys that we can find in RosettaSEED (for SrcCV records that have species keys now)
         */
        SLSourceRosetta_BuildDB::BuildCultivarIndex( $this->kfdb, "seeds.sl_cv_sources", "" );

        /* Compute soundex and metaphone for unmatched names
         */
        SLSourceRosetta_BuildDB::BuildSoundIndex( $this->kfdb );
// move these two lines to a SLRosetta_BuildDB class
        $this->kfdb->Execute( "UPDATE seeds.sl_pcv SET sound_soundex=soundex(name) WHERE sound_soundex=''" );
        $this->kfdb->Execute( "UPDATE seeds.sl_pcv SET sound_metaphone=metaphone(name) WHERE sound_metaphone=''" );

        $c = $this->kfdb->Query1( "SELECT count(*) as c FROM sl_cv_sources WHERE _status='0' AND fk_sl_pcv" );
        $s .= "Cultivar index rebuilt ($c entries)<br/>";

        $s .= "</p><br/>";


        return( $s );
    }


    const maxshow_no_kSpecies = 100;
    const maxshow_no_kPCV = 40;
    const maxshow_soundslike = 100;

    private function getPSDefs()
    {
        $this->raPSDefs = array(
            'slcv_overview' => array(
                'title'    => "Overview",
                'testType' => 'report-nofail',
                'testFn'   => array($this,'fn_overview_Test'),
            ),

            'slcv_no_kSpecies' => array(
                'title'    => "Unknown species",
                'testType' => 'rows0',
                'testSql'  => "SELECT osp FROM seeds.sl_cv_sources WHERE _status='0' AND fk_sl_species='0' GROUP BY osp ORDER BY osp",
                'failLabel' => "[[n]] species unknown",
                'failShowFn' => array($this,'fn_no_kSpecies_FailShow'),
                'remedyFn'   => array($this,'fn_no_kSpecies_Remedy'),
                'bNonFatal' => true,
            ),

            'slcv_no_kPCV' => array(
                'title'    => "Unknown cultivars",
                'testType' => 'rows0',
                'testSql'  => "SELECT ocv,fk_sl_species FROM seeds.sl_cv_sources WHERE _status='0' AND fk_sl_species AND fk_sl_pcv='0' GROUP BY fk_sl_species,ocv ORDER BY ocv DESC",
                'failLabel' => "[[n]] cultivars unknown",
                'failShowFn' => array($this,'fn_no_kPCV_FailShow'),
                'remedyFn'   => array($this,'fn_no_kPCV_Remedy'),
                'bNonFatal' => true,
            ),

            'slcv_soundslike' => array(
                'title'    => "Unmatched cultivars with similar names",
                'testType' => 'rows0',
                'testSql'  => "SELECT C.ocv as C_ocv,S.name_en as S_name_en,P.name as P_name,C._key as C__key,P._key as P__key "
                             ."FROM seeds.sl_cv_sources C, seeds.sl_pcv P, seeds.sl_species S "
                             ."WHERE C._status='0' AND P._status='0' AND S._status='0' AND "
                             ."C.fk_sl_species<>'0' AND C.fk_sl_pcv='0' AND "   // known sp but unknown cv
                             ."C.fk_sl_species=P.fk_sl_species AND "
                             ."C.ocv<>'' AND "                                  // skip blank names
                             ."C.sound_soundex<>'' AND C.sound_soundex=P.sound_soundex AND "
                             ."P.fk_sl_species=S._key "
                             ."GROUP BY C.ocv,P._key",
                'failLabel' => "[[n]] cultivars have similar names",
                'failShowFn' => array($this,'fn_soundslike_FailShow'),
                'remedyFn'   => array($this,'fn_soundslike_Remedy'),
            ),
        );
    }

    function fn_overview_Test( $ePS, $raParms )  // ePS is the raPSDefs key, raParms is the array given to SEEDProblemSolverUI
    {                                           // note that this method can be a generic "test" method multiplexed by ePS if you prefer
        $result = SEEDProblemSolver::SPS_ERROR;
        $sTestOut = $sTestErr = "";

        if( $ePS != 'slcv_overview' ) goto done;

        $result = SEEDProblemSolver::SPS_OK;

        $sTestOut .= "<h4>Sources</h4>"
                    ."<p>There are "
                    .$this->kfdb->Query1( "SELECT count(*) FROM seeds.sl_cv_sources WHERE _status='0'" )
                    ." seed source records.</p>"
                    ."<ul>"
                    ."<li>"
                    .$this->kfdb->Query1( "SELECT count(*) FROM seeds.sl_cv_sources WHERE _status='0' AND fk_sl_sources>=3" )
                    ." from seed companies"
                    ."</li><li>"
                    .$this->kfdb->Query1( "SELECT count(*) FROM seeds.sl_cv_sources WHERE _status='0' AND fk_sl_sources=1" )
                    ." from PGRC "
                    ."</li><li>"
                    .$this->kfdb->Query1( "SELECT count(*) FROM seeds.sl_cv_sources WHERE _status='0' AND fk_sl_sources=2" )
                    ." from NPGS"
                    ."</li></ul>"
                    ."<h4>Species</h4>"
                    ."<p>"
                    .$this->kfdb->Query1( "SELECT count(*) FROM seeds.sl_cv_sources WHERE _status='0' AND fk_sl_species='0'" )
                    ." don't have species keys.</p>"
                    ."<p>Those involve "
                    .$this->kfdb->Query1( "SELECT count(distinct osp) FROM seeds.sl_cv_sources WHERE _status='0' AND fk_sl_species='0'" )
                    ." distinct unknown species names.</p>"
                    ."<h4>Cultivars</h4>"
                    ."<p>"
                    .$this->kfdb->Query1( "SELECT count(*) FROM seeds.sl_cv_sources WHERE _status='0' AND fk_sl_species AND fk_sl_pcv='0'" )
                    ." have species keys but not cultivar keys.</p>"
                    ."<p>Those involve "
                    .$this->kfdb->Query1( "SELECT count(distinct fk_sl_species,ocv) FROM seeds.sl_cv_sources WHERE _status='0' AND fk_sl_species AND fk_sl_pcv='0'" )
                    ." distinct unknown cultivar names.</p>"
                    ;

        done:
        return( array($result,$sTestOut,$sTestErr) );
    }

    function fn_no_kSpecies_FailShow( $raRows, $kTest, $raParmsDummy )
    /*****************************************************************
        raRows contains species names that aren't indexed in sl_species.
        Allow the user to map them to existing species, causing the Remedy method to add them to sl_species_syn
     */
    {
        $s = "<br/>"
            .$this->style()
            ."<form action='{$_SERVER['PHP_SELF']}' method='post'>"
            ."<input type='hidden' name='spsSolveConfirmed' value='$kTest'>"    // spsSolveConfirmed overrides the confirmation link of SEEDProblemSolver
            ."<div class='row'>"
                ."<div class='col-sm-6'><b>These are the unknown names</b></div>"
                ."<div class='col-sm-6'><b>add as synonym of psp code</b></div>"
            ."</div>";

        $rQ = $this->oQRosetta->Cmd( 'rosettaSpeciesList', array('fmt'=>'psp-key') );   // fmt for <select>
        $raPsp = array_merge( array(''=>0), $rQ['raOut'] );

        $i = 0;
        foreach( $raRows as $ra ) {
            $s .= "<div class='row mystripe'>"
                     ."<div class='col-sm-6'>{$ra['osp']}</div>"
                     ."<div class='col-sm-6'>"
                     .SEEDForm_Hidden( "osp$i", $ra['osp'] )
                     .SEEDForm_Select2( "kSp$i", $raPsp, "" )
                     ."</div>"
                 ."</div>";

            if( ++$i == self::maxshow_no_kSpecies )  break;    // limit to 10 rows at a time
        }

        $s .= "<div class='row'>"
                 ."<div class='col-sm-6'>&nbsp;</div>"
                 ."<div class='col-sm-6'><br/><input type='submit' value='Add Species Synonym'/></div>"
             ."</div>"
             ."</form>";

        return( $s );
    }
    function fn_no_kSpecies_Remedy( $kTest, $raParms )
    /*************************************************
        Pick up the form values from the FailShow method, and add species names to sl_species_syn
     */
    {
        $s = "";
        $sErr = "";
        $result = null;
        $bDidSomething = false;

        $oSLDB = new SLDB_Master( $this->kfdb, $this->sess->GetUID() );

        for( $i = 0; $i < self::maxshow_no_kSpecies; ++$i ) {
            if( ($kSp = SEEDSafeGPC_GetInt("kSp$i")) && ($osp = SEEDSafeGPC_GetStrPlain("osp$i")) ) {
                // Species name $osp has to map to $kSp. Add it to sl_species_syn unless it's somehow already there.
                $dbOsp = addslashes($osp);

                if( !$this->kfdb->Query1( "SELECT _key FROM seeds.sl_species_syn WHERE name='$dbOsp' AND _status='0'") ) {
                    if( ($kfr = $oSLDB->GetKfrel( "SY" )->CreateRecord()) ) {
                        $kfr->SetValue( 'fk_sl_species', $kSp );
                        $kfr->SetValue( 'name', $osp );
                        $kfr->SetValue( 't', 1 );
                        $kfr->SetValue( 'notes', "Added via Source-Rosetta by ".$this->sess->GetName() );
                        if( $kfr->PutDBRow() ) {
                            $s .= "<p>Added $osp as synonym of $kSp</p>";

                            // Also update sl_cv_sources with the new kSpecies
                            $this->kfdb->Execute( "UPDATE sl_cv_sources SET fk_sl_species='".$kfr->Key()."' WHERE osp='$dbOsp'" );
                        }
                    }
                } else {
                    $sErr .= "<p>$osp is already in the species synonym table</p>";
                }
            }
        }

        return( array( $result, $s, $sErr ) );
    }

    function fn_no_kPCV_FailShow( $raRows, $kTest, $raParmsDummy )
    /*************************************************************
     */
    {
        $s = "<br/>"
            .$this->style()
            ."<form action='".Site_path_self()."' method='post'>"
            ."<input type='hidden' name='spsSolveConfirmed' value='$kTest'>"    // spsSolveConfirmed overrides the confirmation link of SEEDProblemSolver
            ."<div class='row'>"
                ."<div class='col-sm-5'><b>These are the unknown names</b></div>"
                ."<div class='col-sm-5'><b>add as synonym of known cultivar</b></div>"
                ."<div class='col-sm-2'><b>add as a new pcv</b></div>"
                ."</div>";

        $i = 0;
        foreach( $raRows as $ra ) {
            $kSp = $ra['fk_sl_species'];

            $rQ = $this->oQRosetta->Cmd( 'rosettaSpecies', array('kSp'=>$kSp) );
            $sSpName = $rQ['bOk'] ? $rQ['raOut']['name_en'] : "Unknown Species";

            $rQ = $this->oQRosetta->Cmd( 'rosettaPCVList', array('kSp'=>$kSp,'fmt'=>'psp-key') );
            $raCV = array_merge( array(''=>0), $rQ['raOut'] );

            $raPsp = array();
            $s .= "<div class='row mystripe'>"
                     ."<div class='col-sm-5'>[$sSpName] {$ra['ocv']}</div>"
                     ."<div class='col-sm-5'>"
                     .SEEDForm_Hidden( "ocv$i", $ra['ocv'] )
                     .SEEDForm_Hidden( "kSp$i", $kSp )
                     .SEEDForm_Select2( "kPCV$i", $raCV, "" )
                     ."</div>"
                     ."<div class='col-sm-2'>".SEEDForm_Checkbox( "bAdd$i", false, '' )."</div>"
                 ."</div>";

            if( ++$i == self::maxshow_no_kPCV )  break;    // limit to 10 rows at a time
        }

        $s .= "<div class='row'><div class='col-sm-7'>&nbsp;</div><div class='col-sm-5'><br/><input type='submit' value='Add Cultivars'/></div></div>"
             ."</form>";

        return( $s );
    }
    function fn_no_kPCV_Remedy( $kTest, $raParms )
    /*********************************************
     * The form parms give kSp{N} and non-blank ocv{N} for N=[0..?] so loop until ocvN==''
     * If kPCV{N}, we're adding the ocv as a synonym to kPCV.
     * If bAdd{N}, we're adding the ocv as a new row in sl_pcv.
     */
    {
        $resultDummy = null;
        $sErr = "";
        $s = "";

        for( $i = 0; ; ++$i ) {
            $ocv  = SEEDSafeGPC_GetStrPlain( "ocv$i" );
            $kSp  = SEEDSafeGPC_GetStrPlain( "kSp$i" );
            $kPCV = SEEDSafeGPC_GetStrPlain( "kPCV$i" );
            $bAdd = SEEDSafeGPC_GetInt( "bAdd$i" );

            if( !$kSp || !$ocv )  goto done;    // end of the http parms

            if( !$kPCV && !$bAdd )  continue;   // no action on this cultivar

            if( $kPCV && $bAdd ) {              // can't do both actions
                $s .= "<p style='color:red'>Not allowed to add $ocv to both tables</p>";
                continue;
            }

            // Make sure the name isn't already there.
            // This shouldn't happen unless someone simultaneously added the name.
            // S_name_en|P_name is the pcv name, not necessarily ocv.
            $rQFind = $this->oQRosetta->Cmd( 'rosettaPCV', array('kSp'=>$kSp, 'name'=>$ocv, 'eTable'=>'pcv+syn') );
            if( $rQFind['bOk'] ) {
                $s .= "<p style='color:red'>$ocv is already a known cultivar: {$rQFind['raOut']['S_name_en']} | {$rQFind['raOut']['P_name']}</p>";
                continue;
            }

            $updateKPCV = 0;

            if( $kPCV ) {
                $s .= $this->addPCVSyn( $kPCV, $ocv, $kSp );
            }

            if( $bAdd ) {
                /* Add ocv to sl_pcv
                 */
                $rQAdd = $this->oQRosetta->Cmd( 'rosettaPCV--Add',
                                                array( 'kSp'=>$kSp, 'name'=>$ocv, 't'=>1,
                                                       'notes'=>"Added via Source-Rosetta by ".$this->sess->GetName() ) );
                $s .= $rQAdd['bOk']
                      ? "<p>Added $ocv as a new pcv</p>"
                      : "<p style='color:red'>Error adding $ocv as a new pcv : {$rQAdd['sErr']}</p>";

                // if successful update sl_cv_sources with the new kPCV
                if( $rQAdd['bOk'] ) {
                    $newKPCV = $rQAdd['raOut']['kPCV'];
                    $this->kfdb->Execute( "UPDATE sl_cv_sources SET fk_sl_pcv='$newKPCV' WHERE fk_sl_species='$kSp' AND ocv='".addslashes($ocv)."'" );
                }
            }
        }

        done:
        return( array( $resultDummy, $s, $sErr ) );
    }

    function fn_soundslike_FailShow( $raRows, $kTest, $raParmsDummy )
    {
        $s = "<br/>"
            .$this->style()
            ."<form action='".Site_path_self()."' method='post'>"
            ."<input type='hidden' name='spsSolveConfirmed' value='$kTest'>"    // spsSolveConfirmed overrides the confirmation link of SEEDProblemSolver
            ."<div class='row'>"
                ."<div class='col-sm-5'><b>This cultivar name</b></div>"
                ."<div class='col-sm-5'><b>looks a lot like</b></div>"
                ."<div class='col-sm-2'><b>Add as a synonym</b></div>"
            ."</div>";

        $i = 0;
        foreach( $raRows as $ra ) {
            $s .= "<div class='row mystripe'>"
                     ."<div class='col-sm-5'>[{$ra['S_name_en']}] {$ra['C_ocv']}</div>"
                     ."<div class='col-sm-5'> {$ra['P_name']}</div>"
                     .SEEDForm_Hidden( "kP$i", $ra['P__key'] )
                     .SEEDForm_Hidden( "kC$i", $ra['C__key'] )
                     ."<div class='col-sm-2'>".SEEDForm_Checkbox( "bAdd$i", false, '' )."</div>"
                 ."</div>";

            if( ++$i == self::maxshow_soundslike )  break;    // limit to 10 rows at a time
        }

        $s .= "<div class='row'><div class='col-sm-7'>&nbsp;</div><div class='col-sm-5'><br/><input type='submit' value='Add Cultivars'/></div></div>"
             ."</form>";

        return( $s );

    }

    function fn_soundslike_Remedy( $kTest, $raParmsDummy )
    {
        $resultDummy = null;
        $sErr = "";
        $s = "";

        for( $i = 0; ; ++$i ) {
            $kC = SEEDSafeGPC_GetStrPlain( "kC$i" );
            $kPCV = SEEDSafeGPC_GetStrPlain( "kP$i" );
            $bAdd = SEEDSafeGPC_GetInt( "bAdd$i" );

            if( !$kC || !$kPCV )  goto done;    // end of the http parms

            if( !$bAdd )  continue;           // no action on this cultivar

            // The ocv-synonym is propagated via kSrcCV. Get the name info from that.
            list($kSp,$ocv) = $this->kfdb->QueryRA( "SELECT fk_sl_species,ocv FROM seeds.sl_cv_sources WHERE _key='$kC'" );

            // Make sure the name isn't already there.
            // This shouldn't happen unless someone simultaneously added the name.
            // S_name_en|P_name is the pcv name, not necessarily ocv.
            $rQFind = $this->oQRosetta->Cmd( 'rosettaPCV', array('kSp'=>$kSp, 'name'=>$ocv, 'eTable'=>'pcv+syn') );
            if( $rQFind['bOk'] ) {
                $s .= "<p style='color:red'>$ocv is already a known cultivar: {$rQFind['raOut']['S_name_en']} | {$rQFind['raOut']['P_name']}</p>";
                continue;
            }

            $s .= $this->addPcvSyn( $kPCV, $ocv );
        }

        done:
        return( array( $resultDummy, $s, $sErr ) );
    }

    private function addPCVSyn( $kPCV, $ocv, $kSp = 0 )
    /**************************************************
        Add a synonym to sl_pcv_syn and update sl_cv_sources if kSp is given (because it's needed to find the SrcCV row).
TODO: if kSp is not given, look it up from kPCV
     */
    {
        $s = "";

        /* Make ocv a synonym of kPCV. Check if it is already there, and add it if not.
         */
        $rQAdd = $this->oQRosetta->Cmd( 'rosettaPCV--AddSyn',
                                        array( 'kPCV'=>$kPCV, 'name'=>$ocv, 't'=>1,
                                               'notes'=>"Added via Source-Rosetta by ".$this->sess->GetName() ) );
        $s .= $rQAdd['bOk']
              ? "<p>Added $ocv as synonym of $kPCV</p>"
              : "<p style='color:red'>Error adding $ocv as synonym of $kPCV : {$rQAdd['sErr']}</p>";

        if( $rQAdd['bOk'] && $kSp ) {
            $this->kfdb->Execute( "UPDATE seeds.sl_cv_sources SET fk_sl_pcv='$kPCV' WHERE fk_sl_species='$kSp' AND ocv='".addslashes($ocv)."'" );
        }

        return( $s );
    }

}


class SLSourceRosetta_BuildDB
/****************************
    Static methods for trying to build the cv-src index.
    This can be used on sl_cv_sources and sl_tmp_cv_sources
 */

{
    function __construct() {}

    static function BuildSpeciesIndex( KeyFrameDB $kfdb, $dbTable, $sCond = "" )
    /***************************************************************************
        Fill in the fk_sl_species keys for any matching names anywhere in RosettaSEED
     */
    {
        if( !in_array( $dbTable, array("seeds.sl_cv_sources", "seeds.sl_tmp_cv_sources") ) )  die( "$dbTable not allowed" );

        $ok =
        // sl_species
        $kfdb->Execute( "UPDATE $dbTable SrcCV,seeds.sl_species S "
                       ."SET SrcCV.fk_sl_species=S._key "
                       ."WHERE SrcCV._status='0' AND S._status='0' "
                       ."AND SrcCV.fk_sl_species='0' "
                       ."AND SrcCV.osp<>'' "
                       ."AND (SrcCV.osp=S.name_en OR SrcCV.osp=S.name_fr OR SrcCV.osp=S.name_bot OR SrcCV.osp=S.psp OR "
                            ."SrcCV.osp=S.iname_en OR SrcCV.osp=S.iname_fr)"
                       .($sCond ? " AND ($sCond)" : "" ) )
        &&
        // sl_species_syn
        $kfdb->Execute( "UPDATE $dbTable SrcCV,seeds.sl_species_syn SY "
                       ."SET SrcCV.fk_sl_species=SY.fk_sl_species "
                       ."WHERE SrcCV._status='0' AND SY._status='0' "
                       ."AND SrcCV.fk_sl_species='0' "
                       ."AND SrcCV.osp<>'' "
                       ."AND SrcCV.osp=SY.name"
                       .($sCond ? " AND ($sCond)" : "" ) );

        return( $ok );
    }


    static function BuildCultivarIndex( KeyFrameDB $kfdb, $dbTable, $sCond = "" )
    /****************************************************************************
        Fill in the fk_sl_pcv keys for any matching names anywhere in RosettaSEED
     */
    {
        if( !in_array( $dbTable, array("seeds.sl_cv_sources", "seeds.sl_tmp_cv_sources") ) )  die( "$dbTable not allowed" );

        // Skip rows where fk_sl_species is 0:  these are either rows to be deleted (species is blank) or where species was not found in sl_species*
        // Also skip rows where cultivar is empty, because we don't support unnamed cultivars in Rosetta. Sorry, you can't search for those in the seed finder.
        $ok =
        // sl_pcv
        $kfdb->Execute( "UPDATE $dbTable SrcCV,seeds.sl_pcv P "
                       ."SET SrcCV.fk_sl_pcv=P._key "
                       ."WHERE SrcCV._status='0' AND P._status='0' "
                       ."AND SrcCV.fk_sl_species AND SrcCV.fk_sl_pcv='0' "
                       ."AND SrcCV.ocv<>'' AND P.name<>'' "
                       ."AND SrcCV.fk_sl_species=P.fk_sl_species "
                       ."AND SrcCV.ocv=P.name "
                       .($sCond ? " AND ($sCond)" : "" ) )

        &&
        // sl_pcv_syn
        $kfdb->Execute( "UPDATE $dbTable SrcCV,seeds.sl_pcv P,seeds.sl_pcv_syn PY "
                       ."SET SrcCV.fk_sl_pcv=PY.fk_sl_pcv "
                       ."WHERE SrcCV._status='0' AND P._status='0' AND PY._status='0' "
                       ."AND SrcCV.fk_sl_species AND SrcCV.fk_sl_pcv='0' "
                       ."AND SrcCV.ocv<>'' AND PY.name<>'' "                // AND P.name<>''  -- not relevant
                       ."AND SrcCV.fk_sl_species=P.fk_sl_species "
                       ."AND P._key=PY.fk_sl_pcv "
                       ."AND SrcCV.ocv=PY.name "
                       .($sCond ? " AND ($sCond)" : "" ) );

        return( $ok );
    }

    static function BuildSoundIndex( KeyFrameDB $kfdb )
    {
//        $kfdb->Execute( "UPDATE seeds.sl_cv_sources SET sound_soundex=soundex(ocv) WHERE sound_soundex=''" );
//        $kfdb->Execute( "UPDATE seeds.sl_cv_sources SET sound_metaphone=metaphone(ocv) WHERE sound_metaphone=''" );
    }

    static function ClearIndex( KeyFrameDB $kfdb )
    {
        $kfdb->Execute( "UPDATE seeds.sl_cv_sources SET fk_sl_species='0',fk_sl_pcv='0'" );
        self::ClearSoundIndex( $kfdb );
    }

    static function ClearSoundIndex( KeyFrameDB $kfdb )
    {
        $kfdb->Execute( "UPDATE seeds.sl_cv_sources SET sound_soundex='',sound_metaphone=''" );
    }
}

?>