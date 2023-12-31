<?php

/* Administrate the Member Seed Directory database
 *
 * Copyright (c) 2009-2018 Seeds of Diversity Canada
 */

include_once( STDINC."SEEDProblemSolver.php" );

class sedAdmin {
    private $sed;
    private $sResults = "";

    private $yearMaxSedGrowers = 0;
    private $yearMaxSedSeeds = 0;
    private $yearMaxSedCurrGrowers = 0;
    private $yearMaxSedCurrSeeds = 0;

    public $oPS;    // SEEDProblemSolver - used by the classes that use the Console01Stepper, so has to be visible to them

    function __construct( sedList $sedList )
    {
        $this->sed = $sedList;
        $this->oPS = new SEEDProblemSolver( $this->psDefs(), array( 'bShowSql' => true,
                                                                    // user parms propagated through oPS
                                                                    'kfdb' => $this->sed->kfdb2,
                                                                  ) );
    }

    function Main()
    {
        $s = "";

        $this->yearMaxSedGrowers     = $this->sed->kfdb->Query1("SELECT MAX(year) FROM seeds.sed_growers");
        $this->yearMaxSedSeeds       = $this->sed->kfdb->Query1("SELECT MAX(year) FROM seeds.sed_seeds");
        $this->yearMaxSedCurrGrowers = $this->sed->kfdb->Query1("SELECT MAX(year) FROM seeds.sed_curr_growers");
        $this->yearMaxSedCurrSeeds   = $this->sed->kfdb->Query1("SELECT MAX(year) FROM seeds.sed_curr_seeds");

        $sAction = SEEDSafeGPC_Smart( "action", array("", "checkIntegrity", "checkIntegrityHard", "checkIntegritySoft", "normalizeStuff",
                                                      "checkSummerBeforeDataEntry", "checkWinterBeforePublish", "command"));
        switch( $sAction ) {
            case "checkIntegrity":               $this->doCheckIntegrity();               break;
            case "checkIntegrityHard":           $this->doCheckIntegrityHard();           break;
            case "checkIntegritySoft":           $this->doCheckIntegritySoft();           break;
            case "normalizeStuff":               $this->doNormalizeStuff();               break;
            case "checkSummerBeforeDataEntry":   $this->doCheckSummerBeforeDataEntry();   break;
            case "checkWinterBeforePublish":     $this->doCheckWinterBeforePublish();     break;
            case "command":
                if(($k = SEEDSafeGPC_GetStrPlain("commandCode"))) {
                    $s .= "Do command: <A HREF='{$_SERVER['PHP_SELF']}?doRemedy2=$k'>".$this->remedyUpdate($k)."</A><BR/><BR/>";
                }
                break;
            default:
                break;
        }

        /* Handle requests for fixes
         */
        list($sRemedy,$sRemedyErr) = $this->oPS->DrawRemedyUI();
// use Console::UserMsg() and ErrMsg()
        if( $sRemedy )    $this->sResults .= "<div class='well'>$sRemedy</div>";
        if( $sRemedyErr ) $this->sResults .= "<div class='alert alert-warning'>$sRemedyErr</div>";

        if(($eRemedy = SEEDSafeGPC_GetStrPlain("doRemedy1"))) {
            // User has clicked on a remedy link. Confirm.
            $this->sResults .= "<P>You have requested the following remedy. Confirm by clicking the link</P>".$this->drawRemedyLink( $eRemedy, 2 );
        }
        if(($eRemedy = SEEDSafeGPC_GetStrPlain("doRemedy2"))) {
            // User has clicked on a remedy confirmation.  Do the remedy.
            $sql = $this->remedyUpdate($eRemedy);
            if(!empty($sql)) {
                $this->sResults .= "Remedy $eRemedy <FONT color='blue'>$sql</FONT> "
                                  .($this->sed->kfdb->KFDB_Execute($sql) ? "successful" :("failed: ".$this->sed->kfdb->KFDB_GetErrMsg()));
            }
        }

        $s .= "<STYLE>"
             .".sed_admin td { font-family:verdana,helvetica,sans serif; font-size:9pt; vertical-align:top; }"
             .".summary td { border:1px solid #888; padding:2px; }"
             ."</STYLE>"
             ."<TABLE class='sed_admin' border='1' cellpadding='15'><TR>"
             /* Results of last command
              */
             ."<TD valign='top'>".$this->sResults."</TD>"
             /* Summary status
              */
             ."<TD valign='top'>"

             ."<h4>Summer: before data entry</h4>"
             ."<div style='margin-left:40px'>"
             ."<a href='{$_SERVER['PHP_SELF']}?action=checkSummerBeforeDataEntry'>Five steps to check that the database is ready for data entry</a><br/>"
             ."</div>"

             ."<h4>Winter: after data entry</h4>"
             ."<div style='margin-left:40px'>"
             ."<A HREF='{$_SERVER['PHP_SELF']}?action=checkWinterBeforePublish'>Six steps to check that the database is ready for publishing</a><br/>"
             ."</div>"

             ."<P><A HREF='{$_SERVER['PHP_SELF']}?action=normalizeStuff'>Normalize data: Update offer counts for growers, Trim all strings</A></P>"


             ."<P><A HREF='{$_SERVER['PHP_SELF']}?action=checkIntegrity'>Check integrity</A></P>"
             ."<P><A HREF='{$_SERVER['PHP_SELF']}?action=checkIntegrityHard'>Check integrity hard</A></P>"
             ."<P><A HREF='{$_SERVER['PHP_SELF']}?action=checkIntegritySoft'>Check integrity soft</A></P>"
             ."<P>&nbsp;</P>"
             ."<P>Current Year is {$this->sed->currentYear}</P>"
             ."<H4>sed_curr_growers</H4>"
             ."<TABLE><TR><TD>Status"
             .$this->summaryByStatus("sed_curr_growers")
             ."</TD><TD style='padding-left:3em;'>Final year active"
             .$this->summaryByYear("sed_curr_growers")
             ."</TD><TD style='padding-left:3em;'>Expiry<BR/><BR/>";

        if(($dbc = $this->sed->kfdb2->KFDB_CursorOpen("SELECT YEAR(M.expires) AS y,count(*) AS c FROM seeds_2.mbr_contacts M, seeds.sed_curr_growers G WHERE G._status=0 and G.mbr_id=M._key GROUP BY 1 ORDER BY 1 DESC"))) {
            while($ra = $this->sed->kfdb2->KFDB_CursorFetch($dbc)) {
                $s .= $ra['y'].":&nbsp;".$ra['c']."<BR/>";
            }
        }

        $s .= "</TD></TR></TABLE>"
             ."<BR/>"
             ."<H4>sed_curr_seeds</H4>"
             ."<TABLE><TR><TD>Status"
             .$this->summaryByStatus("sed_curr_seeds")
             ."</TD><TD style='padding-left:3em;'>Final year active"
             .$this->summaryByYear("sed_curr_seeds")
             ."</TD><TD style='padding-left:3em;'>Current non-skipped non-deleted"
             .$this->summarySeedStats("sed_curr_seeds")
             ."</TD></TR></TABLE>"

             ."<H4>sed_growers</H4>"
             ."Year"
             .$this->summaryByYear("sed_growers")

             ."<H4>sed_seeds</H4>"
             ."Year"
             .$this->summaryByYear("sed_seeds")

             ."</TD></TR>"
             ."</TABLE>";

         return( $s );
    }

    function summaryByYear($table)
    {
        $s = "<TABLE class='summary'>";

        $n = 0;
        if(($dbc = $this->sed->kfdb->KFDB_CursorOpen("SELECT year,count(*) AS c FROM $table WHERE _status=0 GROUP BY year ORDER BY year DESC"))) {
            while($ra = $this->sed->kfdb->KFDB_CursorFetch($dbc)) {
                $s .= "<TR><TD>".($ra['year'] ? $ra['year'] : "NULL")."</TD><TD>".$ra['c']."</TD></TR>";
                $n += intval($ra['c']);
            }
            $s .= "<TR><TD>Total</TD><TD>$n</TD></TR>";
        }
        $s .= "</TABLE>";
        return( $s );
    }

    function summaryByStatus($table)
    {
        $s = "<TABLE class='summary'>"
            ."<TR><TD>Active</TD>"
                ."<TD>".$this->sed->kfdb->KFDB_Query1("SELECT count(*) FROM $table WHERE _status=0 AND NOT bSkip AND NOT bDelete")."</TD></TR>"
            ."<TR><TD>bSkip</TD>"
                ."<TD>".$this->sed->kfdb->KFDB_Query1("SELECT count(*) FROM $table WHERE _status=0 AND bSkip")."</TD></TR>"
            ."<TR><TD>bDelete</TD>"
                ."<TD>".$this->sed->kfdb->KFDB_Query1("SELECT count(*) FROM $table WHERE _status=0 AND bDelete")."</TD></TR>"
            ."<TR><TD>Total</TD>"
                ."<TD>".$this->sed->kfdb->KFDB_Query1("SELECT count(*) FROM $table WHERE _status=0")."</TD></TR>"
            ."</TABLE>";
        return( $s );
    }

    function summarySeedStats($table)
    {
        $s = "<TABLE class='summary'>"
            ."<TR><TD>Listings</TD>"
                ."<TD>".$this->sed->kfdb->KFDB_Query1("SELECT count(*) FROM $table WHERE _status=0 AND NOT bSkip AND NOT bDelete")."</TD></TR>"
            ."<TR><TD>Distinct Categories</TD>"
                ."<TD>".$this->sed->kfdb->KFDB_Query1("SELECT count(*) FROM (SELECT category FROM $table WHERE _status=0 AND NOT bSkip AND NOT bDelete GROUP BY category) C")."</TD></TR>"
            ."<TR><TD>Distinct Cat/Types</TD>"
                ."<TD>".$this->sed->kfdb->KFDB_Query1("SELECT count(*) FROM (SELECT category,type FROM $table WHERE _status=0 AND NOT bSkip AND NOT bDelete GROUP BY category,type) CT")."</TD></TR>"
            ."<TR><TD>Distinct Cat/Type/Varieties</TD>"
                ."<TD>".$this->sed->kfdb->KFDB_Query1("SELECT count(*) FROM (SELECT category,type,variety FROM $table WHERE _status=0 AND NOT bSkip AND NOT bDelete GROUP BY category,type,variety) CVT")."</TD></TR>"
            ."</TABLE>";
        return( $s );
    }

    function doArchiveSeeds()
    /************************
     */
    {
        return( [true, "Moved to seeds/app/mbr/msd-edit Office tab"] );
    }

    function doCheckIntegrity()
    /**************************
        Return true if sed_curr_seeds and sed_curr_growers is in perfect shape (ignoring rows where bDelete==true, and sometimes where bSkip==true)
        Set $this->sResults with an error message if return value is false.
     */
    {
        return( $this->doCheckIntegrityHard() && $this->doCheckIntegritySoft() );
    }




    function doCheckIntegrityHard()
    /******************************
        Return true if sed_curr_seeds and sed_curr_growers have structural integrity (mostly even for rows where bDelete==true)
     */
    {
        $ok = $this->oPS->DoTests( 'integ_' );    // default second parm gives boolean return
        $this->sResults .= $this->oPS->GetOutput();

        if( $ok ) {
            $this->sResults .= "<P style='color:green'>Structural integrity okay</P>";
        }
        return( $ok );
    }

    function doCheckIntegritySoft()
    /******************************
        Return true if sed_curr_seeds and sed_curr_growers have no irregularities in content
     */
    {
        // Workflow integrity tests
        $this->sResults .= "<h3>Workflow Tests</h3>";
        $ok = $this->oPS->DoTests( 'workflow_' );        // default second parm gives boolean return
        $this->sResults .= $this->oPS->GetOutput();

        // Data integrity tests
        if( $ok ) {
            $this->oPS->Clear();                         // clears GetOutput - here's how we insert the headings into sResults
            $this->sResults .= "<h3>Data Tests</h3>";
            $ok = $this->oPS->DoTests( 'data_' );
            $this->sResults .= $this->oPS->GetOutput();
        }

        // Content integrity tests
        if( $ok ) {
            $this->oPS->Clear();                         // clears GetOutput - here's how we insert the headings into sResults
            $this->sResults .= "<h3>Content Tests</h3>";
            $ok = $this->oPS->DoTests( 'content_' );
            $this->sResults .= $this->oPS->GetOutput();
        }

        if( $ok ) {
            $this->sResults .= "<P style='color:green'>Workflow, Data, and Content integrity okay</P>";
        }
        return( $ok );
    }

    private function psDefs()
    /************************
        Defs for SEEDProblemSolver
     */
    {
        return( TmpPSDefs($this->sed->currentYear) );
    }

/*
    function _integ( $sHead, $sql, $sTemplate, &$bOk, $raParms = array() )
    {
        $bFatal   = isset($raParms['bFatal'])    ? $raParms['bFatal']    : true;
        $bKFDB2   = isset($raParms['bKFDB2'])    ? $raParms['bKFDB2']    : false;
        $bIgnore0 = isset($raParms['bIgnore0'])  ? $raParms['bIgnore0']  : false;	// e.g. "SELECT count(*)" returns one row of 0 if no problem
        $eRemedy  = isset($raParms['eRemedy'])   ? $raParms['eRemedy']   : '';
        $sNoDup   = isset($raParms['sNoDup'])    ? $raParms['sNoDup']    : "";      // ArrayExpand template whose result will not be duplicated

        $raNoDup = array();
        $raRows = $bKFDB2 ? $this->sed->kfdb2->QueryRowsRA($sql) : $this->sed->kfdb->QueryRowsRA($sql);
        if( count($raRows) && !($bIgnore0 && count($raRows)==1 && $raRows[0][0]==0) ) {
            $this->setErr($sHead);
            foreach( $raRows as $ra ) {
                if( $sNoDup ) {
                    $s = SEEDCore_ArrayExpand( $ra, $sNoDup );
                    if( in_array( $s, $raNoDup) ) {
                        $this->sResults .= "* ";
                        continue;
                    }
                    $raNoDup[] = $s;
                }
                $this->sResults .= SEEDCore_ArrayExpand( $ra, $sTemplate."<BR/>" );
            }
            if( $eRemedy ) {
            	$ra = $this->getRemedy( $eRemedy );
            	$this->sResults .= $this->drawRemedyLink( $eRemedy );
            }
            $this->showSQL( $sql );
            if( $bFatal )  $bOk = false;
        }
    }

function linkGrowerPage($mbr_id, $label) {
    return("<A HREF='./sed.php?mode=growers&submode=growerSeeds&iGrower=$mbr_id' target='_blank'>$label</A>");
}
*/
    function doNormalizeStuff()
    /******************************
        Normalize data that doesn't mind being normalized at any arbitrary time, and repeatedly.

            Update the offer counts in non-skipped, non-deleted growers to reflect their non-skipped, non-deleted seeds.
            Trim all category, type, variety strings
            Force UPPER CASE on names.
            Reset NULL values
     */
    {
        /* Sometimes NULL values have crept in, and interfered with integrity tests.
         * Legacy database tables weren't made with NOT NULL; this could go away when that is amended.
         */
        $this->sed->kfdb2->Execute("UPDATE seeds.sed_curr_growers SET year=0 WHERE year IS NULL");
        $this->sed->kfdb2->Execute("UPDATE seeds.sed_curr_seeds   SET year=0 WHERE year IS NULL");

        $this->sed->kfdb2->Execute("UPDATE seeds.sed_curr_seeds SET category='' WHERE category IS NULL");
        $this->sed->kfdb2->Execute("UPDATE seeds.sed_curr_seeds SET type=''     WHERE type     IS NULL");
        $this->sed->kfdb2->Execute("UPDATE seeds.sed_curr_seeds SET variety=''  WHERE variety  IS NULL");

        $this->sed->kfdb2->Execute("UPDATE seeds.sed_curr_growers SET mbr_code=UPPER(mbr_code)");
        $this->sed->kfdb2->Execute("UPDATE seeds.sed_curr_seeds   SET category=UPPER(category),type=UPPER(type),variety=UPPER(variety)");

        $this->sed->kfdb2->Execute("UPDATE seeds.sed_curr_growers SET mbr_code=TRIM(mbr_code)");
        $this->sed->kfdb2->Execute("UPDATE seeds.sed_curr_seeds   SET category=TRIM(category),type=TRIM(type),variety=TRIM(variety)");

        /* Update offer counts in growers.
         *
         * This is a very brute force way to do the job, but until MySQL has an UPDATE...SELECT...GROUP BY construct, there probably isn't
         * another way that is substantially better.
         */

        if( ($dbc = $this->sed->kfdb2->CursorOpen( "SELECT mbr_id FROM seeds.sed_curr_growers" )) ) {
            $i = 0;
            while( $ra = $this->sed->kfdb2->CursorFetch($dbc) ) {
                $sCond = "mbr_id='{$ra['mbr_id']}' AND _status='0' AND NOT bSkip AND NOT bDelete";

                $nTotal  = $this->sed->kfdb2->Query1("SELECT count(*) FROM seeds.sed_curr_seeds WHERE $sCond");
                $nFlower = $this->sed->kfdb2->Query1("SELECT count(*) FROM seeds.sed_curr_seeds WHERE $sCond AND category='FLOWERS AND WILDFLOWERS'");
                $nFruit  = $this->sed->kfdb2->Query1("SELECT count(*) FROM seeds.sed_curr_seeds WHERE $sCond AND category='FRUIT'");
                $nGrain  = $this->sed->kfdb2->Query1("SELECT count(*) FROM seeds.sed_curr_seeds WHERE $sCond AND category='GRAIN'");
                $nHerb   = $this->sed->kfdb2->Query1("SELECT count(*) FROM seeds.sed_curr_seeds WHERE $sCond AND category='HERBS AND MEDICINALS'");
                $nTree   = $this->sed->kfdb2->Query1("SELECT count(*) FROM seeds.sed_curr_seeds WHERE $sCond AND category='TREES AND SHRUBS'");
                $nVeg    = $this->sed->kfdb2->Query1("SELECT count(*) FROM seeds.sed_curr_seeds WHERE $sCond AND category='VEGETABLES'");
                $nMisc   = $this->sed->kfdb2->Query1("SELECT count(*) FROM seeds.sed_curr_seeds WHERE $sCond AND category='MISC'");

                $this->sed->kfdb2->Execute( "UPDATE seeds.sed_curr_growers "
                                           ."SET nTotal='$nTotal',nFlower='$nFlower',nFruit='$nFruit',"
                                           ."nGrain='$nGrain',nHerb='$nHerb',nTree='$nTree',nVeg='$nVeg',nMisc='$nMisc' "
                                           ."WHERE mbr_id='{$ra['mbr_id']}'");
                ++$i;
            }
            $this->sResults = "<P>Removed NULLs, trimmed and upper-cased strings. Updated offer counts for $i growers.</P>";
            $this->sed->kfdb2->CursorClose($dbc);
        }
    }

    function doCheckWinterBeforePublish()
    /************************************
     */
    {
        $oW = new SedAdmin_WinterBeforePublish( $this );

        $stepDef = array( "Title_EN" => "Winter Before Publish",
                          "Steps" => array( array( "fn"=>array($oW,'Step1_IntegrityHard'), "Title_EN"=>"Check Integrity Structural" ),
                                            array( "fn"=>array($oW,'Step2_Workflow'),      "Title_EN"=>"Check Integrity Workflow" ),
                                            array( "fn"=>array($oW,'Step3_DataNormalize'), "Title_EN"=>"Check Normalized Data" ),
                                            array( "fn"=>array($oW,'Step4_ContentDups'),   "Title_EN"=>"Check Duplicate Content" ),
                                            array( "fn"=>array($oW,'Step5_Archive'),       "Title_EN"=>"Archive Final Records" ),
                                            array( "fn"=>array($oW,'Step6_Delete'),        "Title_EN"=>"Delete Old Records" ),
                        ) );
        $oStep = new Console01_Stepper( $stepDef,
                                        array( 'kfdb' => $this->sed->kfdb,
                                               'sess' => $this->sed->sess ) );
        $this->sResults = $oStep->DrawStep( -1 );  // -1 == use the Console class's own http parm to increment the step

        return( true );
    }

    function doCheckSummerBeforeDataEntry()
    /**************************************
     */
    {
        $oW = new SedAdmin_SummerBeforeDataEntry( $this );

        $stepDef = array( "Title_EN" => "Summer Before Data Entry",
                          "Steps" => array( array( "fn"=>array($oW,'Step1_IntegrityHard'), "Title_EN"=>"Check Integrity Structural" ),
                                            array( "fn"=>array($oW,'Step2_Workflow'),      "Title_EN"=>"Check Integrity Workflow" ),
                                            array( "fn"=>array($oW,'Step3_Archive'),       "Title_EN"=>"Check Records Archived" ),
                                            array( "fn"=>array($oW,'Step4_PurgeDeleted'),  "Title_EN"=>"Purge Deleted Records" ),
                                            array( "fn"=>array($oW,'Step5_ClearFlags'),    "Title_EN"=>"Clear Editing Flags" ),
                        ) );
        $oStep = new Console01_Stepper( $stepDef,
                                        array( 'kfdb' => $this->sed->kfdb,
                                               'sess' => $this->sed->sess ) );
        $this->sResults = $oStep->DrawStep( -1 );  // -1 == use the Console class's own http parm to increment the step

        return( true );


        // make sure that items are copied and flags are cleared
        $bOk = false;
        if( $this->yearMaxSedSeeds != $this->yearMaxSedCurrSeeds ) {
            $this->setErr( "It looks like you haven't copied sed_curr_seeds to sed_seeds yet. "
	                  ."Max year in sed_seeds is {$this->yearMaxSedSeeds}, max year in sed_curr_seeds is {$this->yearMaxSedCurrSeeds}." );
        } else {
// the remedy numbers are wrong, but it doesn't matter because dCA_query doesn't show the remedy link anyway
            $bOk = $this->dCA_query($kfdb, "sed_curr_seeds",   "bDelete",  "delete", 1) &&
	           $this->dCA_query($kfdb, "sed_curr_growers", "bDelete",  "delete", 2) &&

// bDone has been replaced by bDoneMbr and bDoneOffice

		   $this->dCA_query($kfdb, "sed_curr_seeds",   "bDone",    "clear",  3) &&
		   $this->dCA_query($kfdb, "sed_curr_growers", "bDone",    "clear",  4) &&
		   $this->dCA_query($kfdb, "sed_curr_seeds",   "bChanged", "clear",  5) &&
		   $this->dCA_query($kfdb, "sed_curr_growers", "bChanged", "clear",  6) &&
		   $this->dCA_query($kfdb, "sed_curr_growers", "bSkip",    "clear",  7);
        }
        if( !$bOk )
            return(false);

        $this->sResults .= "<P>Database is ready for summer data entry</P>";

        return(true);
    }

    function dCA_query($kfdb,$table, $flag, $remedy, $nRemedy) {
        if( ($n = $this->sed->kfdb->KFDB_Query1("SELECT count(*) FROM $table WHERE $flag")) ) {
            $this->setErr("You need to $remedy $n rows from $table where $flag");
            $this->sResults .= "<BR><A HREF='{$_SERVER['PHP_SELF']}?doRemedy=$nRemedy'>".$this->remedyUpdate($nRemedy)."</A><BR>";
            return(false);
        }
        return(true);
    }

    function remedyLabel( $eRemedy )   { $ra = $this->getRemedy( $eRemedy );  return( $ra[0] ); }
    function remedySelect( $eRemedy )  { $ra = $this->getRemedy( $eRemedy );  return( $ra[1] ); }
    function remedyUpdate( $eRemedy )  { $ra = $this->getRemedy( $eRemedy );  return( $ra[2] ); }

    function drawRemedyLink( $eRemedy, $i = 1 )
    {
        // doRemedy1 requests confirmation, doRemedy2 does the job
        $ra = $this->getRemedy( $eRemedy );
        return( "<P style='color:blue'>{$ra[0]}<BR/>".SEEDStd_StrNBSP("",10)."<A HREF='{$_SERVER['PHP_SELF']}?doRemedy$i=$eRemedy'>{$ra[2]}</A><P/>" );
    }

    function getRemedy( $eRemedy )
    {
        $sNotSkipDelete = " NOT bSkip AND NOT bDelete";


        $raRemedy = array(
        '1_drop_bDelete_seeds'                 => array( "Set _status=1 for bDelete Seeds",
                                                         "SELECT count(*) FROM sed_curr_seeds WHERE bDelete AND _status=0",
                                                         "UPDATE sed_curr_seeds SET _status=1 WHERE bDelete" ),

        '1g_drop_bDelete_growers'              => array( "Set _status=1 for bDelete Growers",
                                                         "SELECT count(*) FROM sed_curr_growers WHERE bDelete AND _status=0",
                                                         "UPDATE sed_curr_growers SET _status=1 WHERE bDelete" ),

        '2_drop_bDelete_growers_and_seeds'     => array( "Set G._status=1,S._status=1 for deleted Growers and their Seeds (does not delete bDelete seeds of non-bDelete Growers)",
                                                         "SELECT count(*) FROM sed_curr_growers G, sed_curr_seeds S "
                                                            ."WHERE G.mbr_id=S.mbr_id AND G.bDelete AND (G._status=0 OR S._status=0)",
                                                         "UPDATE sed_curr_growers G, sed_curr_seeds S SET G._status=1,S._status=1 "
                                                            ."WHERE G.mbr_id=S.mbr_id AND G.bDelete" ),

//        '3_clear_bDone_seeds'                  => array( "Clear bDone on All Seeds",
//                                                         "SELECT count(*) FROM sed_curr_seed where bDone",
//                                                         "UPDATE sed_curr_seeds SET bDone=0" ),

        '4_clear_bDone_growers'                => array( "Clear bDoneMbr/Office on All Growers",
                                                         "SELECT count(*) FROM sed_curr_growers where bDoneMbr OR bDoneOffice",
                                                         "UPDATE sed_curr_growers SET bDoneMbr=0,bDoneOffice=0" ),

        '5_clear_bChanged_seeds'               => array( "Clear bChanged on All Seeds",
                                                         "SELECT count(*) FROM sed_curr_seeds where bChanged",
                                                         "UPDATE sed_curr_seeds SET bChanged=0" ),

        '6_clear_bChanged_growers'             => array( "Clear bChanged on All Growers",
                                                         "SELECT count(*) FROM sed_curr_growers where bChanged",
                                                         "UPDATE sed_curr_growers SET bChanged=0" ),

        '7_clear_bSkip_growers'                => array( "Clear bSkip on All Growers",
                                                         "SELECT count(*) FROM sed_curr_growers where bSkip",
                                                         "UPDATE sed_curr_growers SET bSkip=0" ),

        '8_delete_seeds_for_bDelete_growers'   => array( "Set bDelete on Seeds Owned by bDeleted Growers",
                                                         "SELECT count(*) FROM sed_curr_growers G, sed_curr_seeds S "
                                                            ."WHERE G.mbr_id=S.mbr_id AND G.bDelete AND NOT S.bDelete",
                                                         "UPDATE sed_curr_seeds S, sed_curr_growers G SET S.bDelete=1 WHERE S.mbr_id=G.mbr_id AND G.bDelete" ),



        '11_set_year_current_seeds'            => array( "Set Current Year for Seeds not bSkip and not bDelete",
                                                         "SELECT count(*) from sed_curr_seeds WHERE year<>'".$this->sed->currentYear."' AND $sNotSkipDelete",
                                                         "UPDATE sed_curr_seeds SET year='".$this->sed->currentYear."' WHERE $sNotSkipDelete" ),
        );
        return( $raRemedy[$eRemedy] );
    }

    function setErr($errmsg)
    /*************************
     */
    {
        $this->sResults .= "<HR/><P style='color:red'>$errmsg</P>";
    }

    function showSQL($sql)
    /*********************
     */
    {
        $this->sResults .= "<BR/><SPAN style='color:gray;'><I>$sql</I></SPAN><BR/>";
    }
}


class SedAdmin_WinterBeforePublish
{
    private $sedAdmin;

    function __construct( $sedAdmin )
    {
        $this->sedAdmin = $sedAdmin;
    }

        // POLICY:  If a grower doesn't respond, skip them and all their seeds.
        //          If they do return the form, but they skip ALL the seeds, skip the grower too.
        //          These amount to the same thing because we omit them from the directory, and send them the same
        //          skipped grower package next August.

    function Step1_IntegrityHard()    { return( $this->stepTest( 'integ_') ); }
    function Step2_Workflow()
    {
        // Some workflow tests in winter, e.g. flags, require a "finalized" state that is removed by the summer process.
        // So the winter workflow tests are separated.
        if( ($ok = $this->sedAdmin->oPS->DoTests( 'workflow-winter_' )) ) {
            $ok = $this->sedAdmin->oPS->DoTests( 'workflow_' );
        }
        $s = $this->sedAdmin->oPS->GetOutput();    // output from test 1 plus possibly test 2, because they are appended internally
        return( $this->stepperRet( $ok, $s ) );
    }

    function Step3_DataNormalize()    { return( $this->stepTest( 'data_' ) ); }
    function Step4_ContentDups()      { return( $this->stepTest( 'content_' ) ); }

    function Step6_Delete()           { return( $this->stepTest( 'delete_', true ) ); }

    function Step5_Archive()
    {
        list( $ok, $s ) = $this->sedAdmin->doArchiveSeeds();
        return( $this->stepperRet( $ok, $s ) );
    }

    private function stepTest( $prefix, $bLast = false )
    {
        return( $this->stepperRet( $this->sedAdmin->oPS->DoTests( $prefix ), // ok
                                   $this->sedAdmin->oPS->GetOutput(),        // s
                                   $bLast ) );
    }

    private function stepperRet( $ok, $s, $bLast = false )
    {
        return( array( 's' => $s,
                       'btnHiddenParms' => array( 'action'=>$_REQUEST['action'] ),
                       'buttons' => (($ok && !$bLast ? "next " : "")." repeat cancel") ) );
    }
}

class SedAdmin_SummerBeforeDataEntry
{
    private $sedAdmin;

    function __construct( $sedAdmin )
    {
        $this->sedAdmin = $sedAdmin;
    }

    function Step1_IntegrityHard()    { return( $this->stepTest( 'integ_') ); }
    function Step2_Workflow()         { return( $this->stepTest( 'workflow_' ) ); }
    function Step3_Archive()
    {
        list( $ok, $s ) = $this->sedAdmin->doArchiveSeeds();
        return( $this->stepperRet( $ok, $s ) );
    }
    function Step4_PurgeDeleted()     { return( $this->stepTest( 'purge_' ) ); }
    function Step5_ClearFlags()       { return( $this->stepTest( 'clearflags_', true ) ); }

    private function stepTest( $prefix, $bLast = false )
    {
        return( $this->stepperRet( $this->sedAdmin->oPS->DoTests( $prefix ), // ok
                                   $this->sedAdmin->oPS->GetOutput(),        // s
                                   $bLast ) );
    }

    private function stepperRet( $ok, $s, $bLast = false )
    {
        return( array( 's' => $s,
                       'btnHiddenParms' => array( 'action'=>$_REQUEST['action'] ),
                       'buttons' => (($ok && !$bLast ? "next " : "")." repeat cancel") ) );
    }
}


function TmpPSDefs( $currentYear )
{
        $sGNoSkipDel = "G._status='0'   AND NOT G.bSkip  AND NOT G.bDelete";
        $sSNoSkipDel = "S._status='0'   AND NOT S.bSkip  AND NOT S.bDelete";
        $sS1NoSkipDel = "S1._status='0' AND NOT S1.bSkip AND NOT S1.bDelete";
        $sS2NoSkipDel = "S2._status='0' AND NOT S2.bSkip AND NOT S2.bDelete";
        $yearCurrent = $currentYear; // $this->currentYear;

        return( array(
            /* Hard structural integrity tests
             */
            'integ_gmbr_id_unique' =>
                array( 'title' => "Check for duplicate grower ids in sed_curr_growers",
                       'testType' => 'rows0',
                       'failLabel' => "Grower ids duplicated",
                       'failShowRow' => "mbr_id=[[mbr_id]]",
                       'testSql' =>
                           "SELECT G1.mbr_id as mbr_id FROM seeds.sed_curr_growers G1,seeds.sed_curr_growers G2 "
                          ."WHERE G1.mbr_id=G2.mbr_id AND G1._key<G2._key",
                     ),

            'integ_gmbr_code_notblank' =>
                array( 'title' => "Check for blank grower codes in sed_curr_growers",
                       'testType' => 'rows0',
                       'failLabel' => "Grower codes blank",
                       'failShowRow' => "mbr_id=[[mbr_id]]",
                       'testSql' =>
                           "SELECT mbr_id FROM seeds.sed_curr_growers WHERE mbr_code='' OR mbr_code IS NULL",
                     ),

            'integ_gmbr_code_unique1' =>
                array( 'title' => "Check for duplicate grower codes in sed_curr_growers",
                       'testType' => 'rows0',
                       'failLabel' => "Grower codes duplicated",
                       'failShowRow' => "mbr_code=[[mbr_code]]",
                       'testSql' =>
                           "SELECT G1.mbr_code as mbr_code FROM seeds.sed_curr_growers G1,seeds.sed_curr_growers G2 "
                          ."WHERE G1.mbr_code=G2.mbr_code AND G1._key<G2._key",
                     ),

            'integ_grower_code_unique2' =>
                array( 'title' => "Check for grower codes that have changed from previous years",
                       'testType' => 'rows0',
                       'failLabel' => "Warning: Grower codes changed",
                       'failShowRow' => "Member [[mbr_id]] was [[G2_mbr_code]] in [[G2_year]], but is now [[G_mbr_code]]",
                       'bNonFatal' => true,
                       'testSql' =>
                           "SELECT G.mbr_id as mbr_id,G.year as G_year,G2.year as G2_year,G.mbr_code as G_mbr_code,G2.mbr_code as G2_mbr_code "
                          ."FROM seeds.sed_curr_growers G, seeds.sed_growers G2 "
                          ."WHERE (G.mbr_id=G2.mbr_id) AND G.mbr_code <> G2.mbr_code ORDER BY G.mbr_id",
                     ),

            'integ_grower_code_badly_reused' =>
                array( 'title' => "Check for reused grower codes that are the same as someone else's",
                       'testType' => 'rows0',
                       'failLabel' => "Warning: Grower codes same as someone else's",
                       'failShowRow' => "[[mc]] : [[mid1]] [[fn1]] [[ln1]] ([[y1]]) and [[mid2]] [[fn2]] [[ln2]] ([[y2]])",
                       'bNonFatal' => true,
                       'testSql' =>
                           "SELECT G.mbr_code as mc, G.mbr_id as mid1, G2.mbr_id as mid2, 'current' as y1, G2.year as y2, M1.firstname as fn1,M1.lastname as ln1,M2.firstname as fn2,M2.lastname as ln2 "
                              ."FROM seeds.sed_curr_growers G, seeds.sed_growers G2, seeds_2.mbr_contacts M1, seeds_2.mbr_contacts M2 "
                              ."WHERE G.mbr_code=G2.mbr_code AND G.mbr_id <> G2.mbr_id AND M1._key=G.mbr_id AND M2._key=G2.mbr_id"
                          ." UNION "
                          ."SELECT G.mbr_code as mc, G.mbr_id as mid1, G2.mbr_id as mid2, 'current' as y1, 'current' as y2, M1.firstname as fn1,M1.lastname as ln1,M2.firstname as fn2,M2.lastname as ln2 "
                              ."FROM seeds.sed_curr_growers G, seeds.sed_curr_growers G2, seeds_2.mbr_contacts M1, seeds_2.mbr_contacts M2 "
                              ."WHERE G.mbr_code=G2.mbr_code AND G.mbr_id <> G2.mbr_id AND M1._key=G.mbr_id AND M2._key=G2.mbr_id ORDER BY 1",
                     ),

            'integ_gmbr_in_contacts' =>
                array( 'title' => "Check that growers are known in mbr_contacts",
                       'testType' => 'rows0',
                       'failLabel' => "Growers are not in mbr_contacts",
                       'failShowRow' => "mbr_id=[[mbr_id]]",
                       'testSql' =>
                           "SELECT G.mbr_id as mbr_id FROM seeds.sed_curr_growers G LEFT JOIN seeds_2.mbr_contacts M "
                          ."ON (G.mbr_id=M._key) WHERE M._key IS NULL OR G.mbr_id=0 OR M._status<>0",
                     ),

            'integ_seeds_orphaned' =>
                array( 'title' => "Check for orphaned seeds",
                       'testType' => 'rows0',
                       'failLabel' => "Seeds have no grower",
                       'failShowRow' => "kSeed [[kS]] : mbr_id=[[mbr_id]], [[cat]] - [[type]] - [[var]]",
                       'testSql' =>
                           "SELECT S._key as kS, S.mbr_id as mbr_id, S.category as cat, S.type as type, S.variety as var "
                          ."FROM seeds.sed_curr_seeds S LEFT JOIN seeds.sed_curr_growers G ON (S.mbr_id=G.mbr_id) "
                          ."WHERE S._status=0 AND (G.mbr_id IS NULL OR G._status<>0)",
                     ),


            /* Soft content integrity tests
             */
            // Do this test in winter before publish, not summer before data entry, because the summer procedure clears the flags
            // that this is requiring
            'workflow-winter_growers_not_done_skip_delete' =>
                array( 'title' => "Check for growers that are not Done, Skipped, or Deleted (Later fixes might not work as expected)",
                       'testType' => 'rows0',
                       'failLabel' => "Growers do not have finalized state",
                       'failShowRow' => "mbr_id [[m]] : [[mc]]",
                       'bNonFatal' => true,
                       'testSql' =>
                           "SELECT G.mbr_id as m,G.mbr_code as mc FROM seeds.sed_curr_growers G "
                          ."WHERE NOT (G.bDoneMbr OR G.bDoneOffice) AND $sGNoSkipDel",
                ),

            // Do these for winter and summer
            'workflow_grower_delete_with_nondelete_seeds' =>
                array( 'title' => "Check for deleted growers that have non-deleted seeds",
                       'testType' => 'rows0',
                       'failLabel' => "Deleted growers have non-deleted seeds (solution: delete the seeds)",
                       'failShowRow' => "mbr_id [[m]] : [[mc]]",
                       'testSql' =>
                           "SELECT G.mbr_id as m,ANY_VALUE(G.mbr_code) as mc FROM seeds.sed_curr_growers G, seeds.sed_curr_seeds S "
                          ."WHERE G.mbr_id=S.mbr_id AND G.bDelete AND NOT S.bDelete GROUP BY G.mbr_id",
                       'remedyLabel' => "Delete seeds for deleted growers",
                       'remedySql' =>
                           "UPDATE seeds.sed_curr_seeds S, seeds.sed_curr_growers G SET S.bDelete=1 WHERE S.mbr_id=G.mbr_id AND G.bDelete",
                ),

            'workflow_grower_skip_with_nonskip_seeds' =>
                array( 'title' => "Check for skipped growers that have non-skipped seeds",
                       'testType' => 'rows0',
                       'failLabel' => "Skipped growers have non-skipped seeds (solution: skip the seeds)",
                       'failShowRow' => "mbr_id [[m]] : [[mc]]",
                       'testSql' =>
                           "SELECT G.mbr_id as m,ANY_VALUE(G.mbr_code) as mc FROM seeds.sed_curr_growers G, seeds.sed_curr_seeds S "
                          ."WHERE G.mbr_id=S.mbr_id AND G.bSkip AND NOT (S.bSkip OR S.bDelete) GROUP BY G.mbr_id",
                       'remedyLabel' => "Skip seeds for skipped growers",
                       'remedySql' =>
                           "UPDATE seeds.sed_curr_seeds S, seeds.sed_curr_growers G SET S.bSkip=1 WHERE S.mbr_id=G.mbr_id AND G.bSkip",
                ),

            'workflow_grower_with_no_seeds'
                => array( 'title' => "Check for active growers that are offering no seeds",
                          'testType' => 'rows0',
                          'failLabel' => "Growers offering no seeds (solution: skip the growers)",
                          'failShowRow' => "mbr_id [[m]] : [[mc]]",
                          'testSql' =>
                              "SELECT G.mbr_id as m, G.mbr_code as mc FROM seeds.sed_curr_growers G WHERE $sGNoSkipDel "
                             ."AND NOT EXISTS (SELECT * FROM seeds.sed_curr_seeds S WHERE G.mbr_id=S.mbr_id AND $sSNoSkipDel)",
                          'remedyLabel' => "Skip active growers who have no active seeds",
                          'remedySql' =>
                              "UPDATE seeds.sed_curr_growers G SET G.bSkip=1 WHERE $sGNoSkipDel "
                             ."AND NOT EXISTS (SELECT * FROM seeds.sed_curr_seeds S WHERE G.mbr_id=S.mbr_id AND $sSNoSkipDel)",
                ),

            /* Check that the data is normalized
             */
            'data_mbrcode_8chars' =>
                array( 'title' => "Check for non-standard mbrcode format",
                       'testType' => 'rows0',
                       'failLabel' => "Mbr codes don't have 8 characters",
                       'failShowRow' => "[[m]] : [[mc]]",
                       'bNonFatal' => true,
                       'testSql' =>
                           "SELECT G.mbr_id as m,G.mbr_code as mc FROM seeds.sed_curr_growers G WHERE LENGTH(G.mbr_code)<>8 AND G.mbr_code<>'SODC/SDPC'",
                     ),

            'data_category_normal' =>
                array( 'title' => "Check for non-standard categories",
                       'testType' => 'rows0',
                       'failLabel' => "Seeds have non-standard categories",
                       'failShowRow' => "[[n]] seeds have category '[[category]]'",
                       'testSql' =>
                           "SELECT S.category as category,count(*) as n FROM seeds.sed_curr_seeds S "
                          ."WHERE $sSNoSkipDel AND "
                          ."category NOT IN ('FLOWERS AND WILDFLOWERS','FRUIT','GRAIN','HERBS AND MEDICINALS','TREES AND SHRUBS','VEGETABLES','MISC') "
                          ."GROUP BY category",
                     ),

            'data_type_empty' =>
                array( 'title' => "Check for blank Types (the only value that is not allowed)",
                       'testType' => 'rows0',
                       'failLabel' => "Seeds have blank Type",
                       'failShowRow' => "category=[[category]], variety=[[variety]]",
                       'testSql' =>
                           "SELECT S.category as category,S.variety as variety FROM seeds.sed_curr_seeds S WHERE S.type='' and $sSNoSkipDel",
                     ),

            'data_year_growers' =>
                array( 'title' => "Check for current year in all growers not skipped or deleted",
                       'testType' => 'n0',
                       'failLabel' => "[[n]] rows in sed_curr_growers that are neither bSkip nor bDelete, but don't have the current year",
                       'testSql' =>
                           "SELECT count(*) FROM seeds.sed_curr_growers G WHERE G.year<>'$yearCurrent' AND $sGNoSkipDel",
                       'remedyLabel' => "Set current year for growers",
                       'remedySql' =>
                           "UPDATE seeds.sed_curr_growers G SET G.year='$yearCurrent' WHERE $sGNoSkipDel",
                     ),

            'data_year_seeds' =>
                array( 'title' => "Check for current year in all seeds not skipped or deleted",
                       'testType' => 'n0',
                       'failLabel' => "[[n]] rows in sed_curr_seeds that are neither bSkip nor bDelete, but don't have the current year",
                       'testSql' =>
                           "SELECT count(*) FROM seeds.sed_curr_seeds S WHERE S.year<>'$yearCurrent' AND $sSNoSkipDel",
                       'remedyLabel' => "Set current year for seeds",
                       'remedySql' =>
                           "UPDATE seeds.sed_curr_seeds S SET S.year='$yearCurrent' WHERE $sSNoSkipDel",
                     ),

            'data_count_nTotal' =>
                array( 'title' => "Check that the grower seed-count totals equal sums of category counts",
                       'testType' => 'rows0',
                       'failLabel' => "Grower total count does not match sum of Flower,Fruit,Grain,Herb,Tree,Veg,Misc counts",
                       'failShowRow' => "[[m]] : [[mc]]",
                       'testSql' =>
                           "SELECT G.mbr_id as m, G.mbr_code as mc FROM seeds.sed_curr_growers G WHERE $sGNoSkipDel AND "
                          ."G.nTotal <> G.nFlower + G.nFruit + G.nGrain + G.nHerb + G.nTree + G.nVeg + G.nMisc",
                     ),

            'data_count_seeds' =>
                array( 'title' => "Check that the grower seed-count totals equal the number of seed listings",
                       'testType' => 'rows0',
                       'failLabel' => "Grower total count does not match number of seeds offered",
                       'failShowRow' => "[[m]] : [[mc]] does not really have [[nTotal]] active seed listings",
                       'testSql' =>
                           "SELECT G.mbr_id as m, G.mbr_code as mc, G.nTotal as nTotal FROM seeds.sed_curr_growers G "
                              ."WHERE $sGNoSkipDel AND "
                              ."G.nTotal <> (SELECT count(*) FROM seeds.sed_curr_seeds S WHERE S.mbr_id=G.mbr_id AND $sSNoSkipDel)",
                     ),

            'data_count_sumsGandS' =>
                array( 'title' => "Check that sum of grower seed-count totals equals the total number of seeds listed",
                       'testType' => 'n0',
                       'failLabel' => "Sum of grower totals - count of seeds = [[n]]",
                       'testSql' =>
                           "SELECT (SELECT sum(G.nTotal) FROM seeds.sed_curr_growers G WHERE $sGNoSkipDel) "
                              ." - (SELECT count(*) FROM seeds.sed_curr_seeds S WHERE $sSNoSkipDel) as n",
                     ),


            /* Check for duplicated entries (not fatal since this often happens legitimately)
             */
            'content_dup_types' =>
                array( 'title' => "Check for duplicate Types in different Categories",
                       'testType' => 'rows0',
                       'failLabel' => "Warning: Types duplicated in different Categories",
                       'failShowRow' => "Type [[t]] is in [[c1]] and [[c2]]",
                       'bNonFatal' => true,
                       'testSql' =>
                           "SELECT S1.type as t,S1.category as c1,S2.category as c2 "
                              ."FROM seeds.sed_curr_seeds S1,seeds.sed_curr_seeds S2 "
                              ."WHERE S1.type=S2.type AND S1.category<>S2.category "
                              ."AND $sS1NoSkipDel AND $sS2NoSkipDel GROUP BY 1,2,3 ORDER BY 1,2,3",
                     ),

            'content_dup_var_per_grower' =>
                array( 'title' => "Check for duplicate Type/Varieties from the same grower",
                       'testType' => 'rows0',
                       'failLabel' => "Warning: Varieties duplicated per grower",
                       'failShowRow' => "[[mc]] ([[m]]) has duplicate [[t]] - [[v]] : keys [[ks1]] and [[ks2]]",
                       'bNonFatal' => true,
                       'testSql' =>
                           "SELECT G.mbr_id as m,G.mbr_code as mc,S1.type as t,S1.variety as v,S1._key as ks1,S2._key as ks2 "
                              ."FROM seeds.sed_curr_seeds S1,seeds.sed_curr_seeds S2,seeds.sed_curr_growers G "
                              ."WHERE (G.mbr_id=S1.mbr_id) AND (S1.mbr_id=S2.mbr_id) AND "
                              ."S1._key<S2._key AND S1.type=S2.type AND S1.variety=S2.variety "
                              ."AND $sS1NoSkipDel AND $sS2NoSkipDel ORDER BY 2,3,4",
                     ),

            'content_dup_var_by_type' =>
                array( 'title' => "Check for duplicate Varieties in different Types",
                       'testType' => 'rows0',
                       'failLabel' => "Warning: Varieties duplicated in different Types",
                       'failShowFn' => 'fnContentDupVarByType',//array($this,"fnContentDupVarByType"),     use global function until this function is a method
                       'bNonFatal' => true,
                       'testSql' =>
                           "SELECT S1.variety AS v,S1.type AS t1,S2.type AS t2 FROM seeds.sed_curr_seeds S1,seeds.sed_curr_seeds S2 "
                          ."WHERE S1.variety=S2.variety AND S1.type<>S2.type "
                          ."AND S1.variety NOT IN ('','COMMON','ANNUAL','MIXED','SINGLE') "
                          ."AND S1.variety NOT LIKE '%UNKNOWN%' "
                          ."AND $sS1NoSkipDel AND $sS2NoSkipDel ORDER BY 1,2,3",
                     ),

            /* Check for growers and seeds that have been bDeleted but not actually deleted (though it actually just sets _status=1)
             */
            'delete_old_seeds' =>
                array( 'title' => "Check for deleted seeds",
                       'testType' => 'rows0',
                       'failLabel' => "Seeds deleted but not kfr-deleted",
                       'failShowRow' => "kSeed [[_key]] : mbr_id=[[mbr_id]], [[category]] - [[type]] - [[variety]]",
                       'testSql' => "SELECT _key,mbr_id,category,type,variety FROM seeds.sed_curr_seeds WHERE bDelete AND _status=0 ORDER BY mbr_id,category,type,variety",
                       'remedyLabel' => 'Kfr-delete all deleted seeds',
                       'remedySql' => "UPDATE seeds.sed_curr_seeds SET _status=1 WHERE bDelete"
                     ),

            'delete_old_growers' =>
                array( 'title' => "Check for deleted growers",
                       'testType' => 'rows0',
                       'failLabel' => "Growers deleted but not kfr-deleted",
                       'failShowRow' => "Grower [[mc]] ([[m]])",
                       'testSql' => "SELECT mbr_code as mc, mbr_id as m FROM seeds.sed_curr_growers WHERE bDelete AND _status=0 ORDER BY mbr_code",
                       'remedyLabel' => 'Kfr-delete all deleted growers',
                       'remedySql' => "UPDATE seeds.sed_curr_growers SET _status=1 WHERE bDelete"
                     ),

            /* Purge the records that have been set to _status=1
             */
            'purge_deleted_seeds' =>
                array( 'title' => "Check for deleted seeds",
                       'testType' => 'n0',
                       'failLabel' => "[[n]] seed records are at _status=1 ready to purge",
                       'testSql' => "SELECT count(*) FROM seeds.sed_curr_seeds WHERE _status=1",
                       'remedyLabel' => 'Purge all deleted seed records',
                       'remedySql' => "DELETE FROM seeds.sed_curr_seeds WHERE _status=1"
                     ),

            'purge_deleted_growers' =>
                array( 'title' => "Check for deleted growers",
                       'testType' => 'n0',
                       'failLabel' => "[[n]] grower records are at _status=1 ready to purge",
                       'testSql' => "SELECT count(*) FROM seeds.sed_curr_growers WHERE _status=1",
                       'remedyLabel' => 'Purge all deleted grower records',
                       'remedySql' => "DELETE FROM seeds.sed_curr_growers WHERE _status=1"
                     ),

            /* Clear the workflow flags for a new data entry session
             */
            'clearflags_bDone' =>
                array( 'title' => "Check flags clear - bDone,bDoneMbr,bDoneOffice",
                       'testType' => 'n0',
                       'failLabel' => "[[n]] grower records have bDone, bDoneMbr, or bDoneOffice flag set",
                       'testSql' => "SELECT count(*) FROM seeds.sed_curr_growers WHERE bDone OR bDoneMbr OR bDoneOffice",
                       'remedyLabel' => 'Clear grower.bDone,bDoneMbr,bDoneOffice',
                       'remedySql' => "UPDATE seeds.sed_curr_growers SET bDone=0,bDoneMbr=0,bDoneOffice=0"
                     ),
            'clearflags_bChanged_growers' =>
                array( 'title' => "Check flags clear - bChanged for growers",
                       'testType' => 'n0',
                       'failLabel' => "[[n]] grower records have bChanged flag",
                       'testSql' => "SELECT count(*) FROM seeds.sed_curr_growers WHERE bChanged",
                       'remedyLabel' => 'Clear curr_grower.bChanged',
                       'remedySql' => "UPDATE seeds.sed_curr_growers SET bChanged=0"
                     ),
            'clearflags_bChanged_seeds' =>
                array( 'title' => "Check flags clear - bChanged for seeds",
                       'testType' => 'n0',
                       'failLabel' => "[[n]] seeds records have bChanged flag",
                       'testSql' => "SELECT count(*) FROM seeds.sed_curr_seeds WHERE bChanged",
                       'remedyLabel' => 'Clear curr_seeds.bChanged',
                       'remedySql' => "UPDATE seeds.sed_curr_seeds SET bChanged=0"
                     ),
        ) );
}

    /*private*/ function fnContentDupVarByType( $raRows, $kTestDummy, $raParmsDummy )
    {
        $s = "";

        $raOut = array();
        foreach( $raRows as $ra ) {
            if( !is_array(@$raOut[$ra['v']]) || !in_array( $ra['t1'], $raOut[$ra['v']] ) ) {
                $raOut[$ra['v']][] = $ra['t1'];
            }
            if( !is_array($raOut[$ra['v']]) || !in_array( $ra['t2'], $raOut[$ra['v']] ) ) {
                $raOut[$ra['v']][] = $ra['t2'];
            }
        }
        foreach( $raOut as $cv=>$raTypes ) {
            $sDiv = "<div style='display:inline-block;width:100px'>"
                   ."<a href='{$_SERVER['PHP_SELF']}?c01tf_main=Seeds&sfSp_mode=search&sfSx_srch_val1=".urlencode($cv)."'>";
            $s .= "Variety ${sDiv}$cv</a></div> is in types ".implode("&nbsp;&nbsp;|&nbsp;&nbsp; ",$raTypes)."<br/>";
        }
        return( $s );
    }

?>
