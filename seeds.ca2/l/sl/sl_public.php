<?php
include_once( STDINC."SEEDForm.php" );
include_once( SEEDCOMMON."sl/sl_db.php" );

class SL_Public
{
    private $kfdb;
    private $oSVA;
    private $lang;

    private $p_sMode = '';
    private $p_kPCV = 0;
    private $p_iPage = 0;
    private $p_kSp = 0;
    private $p_sPCVFlt = '';

    private $oSLDB;

    function __construct( KeyFrameDB $kfdb, SEEDSession $sess, $lang )
    {
        $this->kfdb = $kfdb;
        $this->oSVA = new SEEDSessionVarAccessor( $sess, 'SLPub' );
        $this->lang = $lang;

        $this->oSLDB = new SLDB_Master( $kfdb, 0 ); // uid 0 because this only uses readonly db methods

        $this->getParms();
    }

    private function getParms()
    {
        $this->p_sMode = SEEDSafeGPC_Smart( 'm', array('list','srch','cv') );  //$this->oSVA->SmartGPC( 'm', array( "home", "list", "intro", "cv", "srch" ) );

        // if k is specified, use mode cv (this allows nice urls like ?k=123)
        if( ($this->p_kPCV = SEEDSafeGPC_GetInt("k")) ) {
            $this->p_sMode = "cv";
        } else if( $this->p_sMode == 'cv' ) {  // can't have this mode if k=0
            $this->p_sMode = "list";
        }
        //$this->oSVA->VarSet( 'm', $this->sMode );

        // Non-persistent parms. These are not stored in sess, so have to be propagated per-page.
        if( ($this->p_iPage = SEEDSafeGPC_GetInt('p')) < 0 ) $this->p_iPage = 0;


        // Persistent parms. If the http parms are blank, only change the stored parms if the previous page was the same mode (i.e. parms came from the appropriate form).
        if( in_array($this->p_sMode, array('list','srch')) ) {
            $this->p_kSp = $this->oSVA->SmartGPC( 'kSp' );
            $this->p_sPCVFlt = $this->oSVA->SmartGPC( 'pcvflt' );
        }
    }


    function Draw()
    {
        $s = "";

        $s = $this->drawSearch();

        //$mode = SEEDSafeGPC_Smart( 'm', array('','srch','cv') );
        switch( $this->p_sMode ) {
            case 'srch':
                break;
            case 'cv':
                $s .= $this->drawPcv();
                break;
            default:
                $sPCVCond = "";
                if( !empty($this->p_sPCVFlt) ) {
                    $sPCVCond = "P.name LIKE '%".addslashes($this->p_sPCVFlt)."%'";
                }

                $s .= $this->drawCVPaged( $this->p_kSp, $sPCVCond );
                break;
        }

        return( $s );
    }

    private function drawSearch()
    {
        $raSp = array("--- All Species ---"=>0);
        $ra1 = $this->getSpeciesList();
        foreach( $ra1 as $ra ) {
            $raSp[$ra['S_name']] = $ra['_key'];
        }

        $s = "<form method='get' action='".Site_path_self()."'>".SEEDForm_Hidden("m","list")
            ."Show species ".SEEDForm_Select2( "kSp", $raSp, $this->p_kSp, array("selectAttrs"=>"onChange='submit();'") )
            .SEEDStd_StrNBSP("",15)."Variety name ".SEEDForm_Text( "pcvflt", $this->p_sPCVFlt, "", 20 )." <INPUT type='submit' value='Match'>"
            ."</form><br/>";

        return( $s );
    }

    private function drawPcv()
    /*************************
     */
    {
        $ra = $this->kfdb->QueryRA( "SELECT S.name_en as S_name,P.name as P_name FROM sl_species S,sl_pcv P WHERE P._key='{$this->p_kPCV}' AND S._key=P.fk_sl_species" );

        $s = "<TABLE border='0' cellspacing='0' cellpadding='0' width='100%'>"
            ."<TR><TD valign='top' colspan='2'>"
            ."<H3>{$ra['S_name']} : {$ra['P_name']}</H3>"
            //.$this->drawPcvPhotos()
            ."</TD></TR>"
            ."<TR><TD valign='top'>"
            .$this->drawPcvAccessions()
            //.$this->drawSEDInfo()	// Seed Directory information
            ."<BR/><BR/>"
            //.$this->drawPcvData()."<BR/><BR/>"
            //.$this->drawPcvHistory()
            ."</TD>"
            ."<TD valign='top' width='200'>"
            .$this->drawPcvStatus()
            ."<br/>"
            .$this->drawPcvGetSeeds()."<BR/><BR/>"
            ."</TD></TR>"
            ."</TABLE>";
        return( $s );
    }

    private function drawPcvAccessions()
    {
        $s = "";

        /*
        $raAcc = $this->getRaAcc( $this->p_kPCV );
        if( count($raAcc) ) {
            $s .= "<DIV class='slAccBox'>We have ".count($raAcc)." sample".(count($raAcc)>1 ? "s":"")." of this variety in our collection.";
            foreach( $raAcc as $ra ) {
                $s .= $this->drawAcc($ra);
            }
            $s .= "</DIV>";
        }
        */

        $nSamples = 0;
// TODO: really want to parameterize and encapsulate this e.g. so we don't forget to limit to our collection
        $raIAPS = $this->oSLDB->GetList( "IxAxPxS", "fk_sl_pcv='{$this->p_kPCV}' AND fk_sl_collection='1' AND NOT I.bDeAcc AND NOT A.bDeAcc", array('sSortCol'=>"A._key") );
        $fAdopt = $this->kfdb->Query1( "SELECT sum(amount) FROM sl_adoption WHERE _status='0' AND fk_sl_pcv='{$this->p_kPCV}'" );
        $sInv = "";
        $raDraw = array();
        foreach( $raIAPS as $ra ) {
            $g = floatval($ra['g_weight']);

            // not sure what to do re 0.1g
            // If an accession has two samples: 0.1 and 0.1, then it will be shown in the cv list because total is >0.1
            // but if we hide <=0.1 here, then the record will appear blank.
            // Maybe it's better to just draw everything here, and limit the cv list to exclude those that have less then 0.1g total
            //if( $g < 0.1 && $fAdopt == 0.0 ) continue;

            $kAcc = $ra['A__key'];
            $raDraw["acc$kAcc"]['kAcc'] = $kAcc;
            $raDraw["acc$kAcc"]['g_original'] = floatval(@$ra['A_g_original']);
            $raDraw["acc$kAcc"]['g_backup'] = floatval(@$ra['A_g_pgrc']);
            $raDraw["acc$kAcc"]['x_member'] = $ra['A_x_member'];
            $raDraw["acc$kAcc"]['sInv'][] = $this->drawInv( $ra );
            $nSamples++;
        }
        foreach( $raDraw as $raDrawAcc ) {
            $sInv .= "<div class='well' style='width:80%'>"  // Accession
                    .SEEDStd_ExpandIfNotEmpty( $raDrawAcc['x_member'], "Grower/Source: [[]]<br/>" )
                    .SEEDStd_ExpandIfNotEmpty( $raDrawAcc['g_original'], "Original quantity: [[]] grams<br/>" )
                    .SEEDStd_ExpandIfNotEmpty( $raDrawAcc['g_backup'], "Backup quantity: [[]] grams<br/>" )
                    ."<div style='margin-left:20px;'>".implode("",$raDrawAcc['sInv'])."</div>"
                    ."</div>";
        }
        if( $sInv ) {
            $s .= "<div class='slAccBox'>"
                 ."<p>We have $nSamples sample".($nSamples==1 ? "":"s")." of this variety in our collection.</p>"
                 .$sInv
                 ."</div>";
        } else {
            $s .= "<div class='slAccBox'>We have only a small amount of seeds of this variety, awaiting processing. Please check back on this status later.</div>";
        }
        return( $s );
    }

    function drawPcvStatus()
    {
        $s = "<DIV class='slStatusBox'>".$this->drawAdoptionBar($this->p_kPCV, true)."</DIV>";

        return( $s );
    }

    function drawPcvGetSeeds()
    {
        $s = "<DIV class='slStatusBox'>";

        $raCSCI = $this->getRaCSCI( $this->p_kPCV );
        if( count($raCSCI) ) {
            $s .= "This variety was available from the following Canadian seed compan".(count($raCSCI)==1?"y":"ies")
                 ." within the past few years."
                 ."<DIV style='margin-left:20px;'>";
            foreach( $raCSCI as $ra ) {
                $s .= "<div style='margin-top:5px;'><a href='http://{$ra["web"]}' target='_blank'>{$ra["company_name"]}</a></div>";
            }
            $s .= "</DIV><BR/>Please support our local heritage seed companies.";
        } else if( false ) { // || $this->canDistribute( $this->p_kPCV ) ) {    // if our quantities allow
            $s .= "According to our records, no Canadian seed companies are selling this variety.<BR/><BR/>"
                 ."You can request a sample of these seeds from Seeds of Diversity by contacting ".SEEDCore_EmailAddress('library','seeds.ca').".";
        } else {
            $s .= "According to our records, no Canadian seed companies are selling this variety.<BR/><BR/>"; //"Unfortunately, we are not able to offer samples of these seeds at this time.";
        }
        $s .= "</DIV>";
    	return( $s );
    }

// deprecate in favour of drawInv
    private function drawAcc($ra)
    /****************************
     */
    {
        $s = "<TABLE border='0' cellspacing='0' cellpadding='2' class='slAccTable'>"
            .$this->drawAccTR( "Accession no.", $ra['_key'] );
        // some of the amounts were weighed to a 1g precision, so show them as integers instead of X.000
        $sHave = ($ra['g_have']     < 1.0 ? $ra['g_have']     : intval($ra['g_have']));
        $sOrig = ($ra['g_original'] < 1.0 ? $ra['g_original'] : intval($ra['g_original']));
        $sPGRC = ($ra['g_pgrc']     < 1.0 ? $ra['g_pgrc']     : intval($ra['g_pgrc']));
        if( @$ra['g_have'] > 0.0 )     $s .= $this->drawAccTR( "Quantity in storage:", "$sHave grams" );
        if( @$ra['g_original'] > 0.0 ) $s .= $this->drawAccTR( "Original quantity:", "$sOrig grams" );
        if( @$ra['g_pgrc'] > 0.0 )     $s .= $this->drawAccTR( "Back-up quantity:", "$sPGRC grams" );
      //if( @$ra['x_d_harvested'] )    $s .= $this->drawAccTR( "Date harvested:",   $ra['x_d_harvested']." grams" );
      //if( @$ra['x_d_received'] )     $s .= $this->drawAccTR( "Date received:",   $ra['x_d_received']." grams" );

        $s .= "</TABLE><BR/>";

        return( $s );
    }

    private function drawInv( $raIAPS )
    {
        $s = "";

        $g = floatval(@$raIAPS['g_weight']);
        $g = $g < 1.0 ? $g : intval($raIAPS['g_weight']);
        $s .= "<div style='margin-top:10px;'>Sample #{$raIAPS['inv_number']}"
             ."<div style='margin-left:20px;'>Quantity in storage: $g grams</div>"
             ."</div>";

        return( $s );
    }


    private function drawAccTR( $t1, $t2 )
    {
        return( "<TR><TD valign='top'>$t1</TD><TD>&nbsp;&nbsp;&nbsp;</TD><TD valign='top'>$t2</TD></TR>" );
    }


    private function drawCVPaged( $kSp, $sPCVCond )
    /**********************************************
     */
    {
        $nPageLimit = 20;
        $nPagesNext = 2;  // this is the number of pages after the current page that we try to detect

        $page = $this->p_iPage;

        // fetch more than iLimit, so we can tell how many pages come later.  Could just sample with n more queries, but this is just as efficient, maybe more
        $raPCV = $this->getRaPCV( $kSp, $sPCVCond, $page * $nPageLimit, $nPageLimit * ($nPagesNext + 1) );

        // page parm is origin-0, number shown is origin-1
        if( count($raPCV) ) {
            $s = "<P>Page:&nbsp;";
            if( $page > 0 ) {
                $s .= "&nbsp;".$this->drawCVPageLink( "&lt;&lt;", $page-1 );
            }
            for( $i = -2; $i <=2; ++$i ) {
                if( $page + $i >= 0 && count($raPCV) > $i * $nPageLimit ) {
        	        $s .= "&nbsp;".$this->drawCVPageLink( $page+$i+1, $page+$i );
                }
            }
            if( count($raPCV) > $nPageLimit ) {
                $s .= "&nbsp;".$this->drawCVPageLink( "&gt;&gt;", $page+1 );
            }
            $s .= "</P>";

            $s .= "<TABLE class='slCVPagedList' border='0' cellspacing='0' cellpadding='5'>"
                 ."<TR><TH>&nbsp;</TH><TH>Adoption</TH></TR>";
            $i = 0;
            foreach( $raPCV as $k => $v ) {
                if( $i++ >= $nPageLimit )  break;
                $s .= "<TR><TD valign='center'><A HREF='".Site_path_self()."?k=$k'>$v</A></TD>"
                      ."<TD valign='top'>".$this->drawAdoptionBar( $k )."</TD>"
                      ."</TR>";
            }
            $s .= "</TABLE>";
        } else {
            $s = "No varieties match your search.";
        }
        return( $s );
    }

    private function drawCVPageLink( $label, $p )
    {
        $sUrl = "";
        if( $this->p_kSp )              $sUrl .= "&kSp=".$this->p_kSp;
        if( !empty($this->p_sPCVFlt) )  $sUrl .= "&pcvflt=".urlencode($this->p_sPCVFlt);

        return( ($p == $this->p_iPage) ? $label : ("<A HREF='".Site_path_self()."?m={$this->p_sMode}&p=$p".$sUrl."'>$label</A>") );
    }

    private function drawAdoptionBar( $kPCV, $bTextBelow = false )
    {
        $raAdopt = $this->getRaAdopt($kPCV);
        $dAdopt = 0.0;
        foreach( $raAdopt as $ra ) {
            $dAdopt += floatval($ra['amount']);
        }

        $iBar = intval( ($dAdopt + 49.0) / 50.0 );   // the bar from 0 to 5 that is the last bar adopted (0 means zero)

        $s = "<STYLE>"
            .".slAdoptionBar1 TD  { width:20px;height:7px;font-size:8pt; }"
            .".slAdoptionBar2 TD  { border-right:1px solid #888;width:20px;height:7px;font-size:1px; }"
            .".slAdoptionBar3 TD  { border-right:1px solid #888;border-top:1px solid #888;border-bottom:1px solid #888;width:20px;height:7px;font-size:1px; }"
            .".slAdoptionBarAdopted { background-color:#0b0;width:50px;}"
            .".slAdoptionLabel    { font-size:10pt;font-weight:bold;margin-left:20px;;}"
            .".slAdoptionLink     { font-size:9pt;font-weight:normal;margin-left:20px;}"
            .".slAdoptionThanks   { font-size:9pt;font-weight:normal;margin-left:20px;}"
            .".slAdoptionThanksLI { font-size:9pt;font-weight:normal;}"
            ."</STYLE>";

        $s .= "<TABLE border='0' cellpadding='0' cellspacing='0' style='margin-left:20px;'>"
            ."<TR class='slAdoptionBar1'>"
            ."<TD valign='top'>$"."&nbsp;".($iBar <= 1 ? $dAdopt : "")."</TD>"
            ."<TD valign='top'>".($iBar == 2 ? $dAdopt : "&nbsp;")."</TD>"
            ."<TD valign='top'>".($iBar == 3 ? $dAdopt : "&nbsp;")."</TD>"
            ."<TD valign='top'>".($iBar == 4 ? $dAdopt : "&nbsp;")."</TD>"
            ."<TD valign='top'>".($iBar >= 5 ? $dAdopt : "&nbsp;")."</TD>"
            ."</TR>"
            ."<TR class='slAdoptionBar2'>"
            ."<TD valign='top'>&nbsp;</TD>"
            ."<TD valign='top'>&nbsp;</TD>"
            ."<TD valign='top'>&nbsp;</TD>"
            ."<TD valign='top'>&nbsp;</TD>"
            ."<TD valign='top'>&nbsp;</TD>"
            ."</TR>"
            ."<TR class='slAdoptionBar3'>"
            ."<TD valign='top'".($iBar>=1 ? "class='slAdoptionBarAdopted'" : "")." style='border-left:1px solid #888;'>&nbsp;</TD>"
            ."<TD valign='top'".($iBar>=2 ? "class='slAdoptionBarAdopted'" : "").">&nbsp;</TD>"
            ."<TD valign='top'".($iBar>=3 ? "class='slAdoptionBarAdopted'" : "").">&nbsp;</TD>"
            ."<TD valign='top'".($iBar>=4 ? "class='slAdoptionBarAdopted'" : "").">&nbsp;</TD>"
            ."<TD valign='top'".($iBar>=5 ? "class='slAdoptionBarAdopted'" : "").">&nbsp;</TD>"
            ."</TR>"
            ."</TABLE>";

        if( $bTextBelow ) {
            if( $dAdopt >= 250.0 ) {
                $s .= "<P class='slAdoptionLabel' style='color:green'>Fully Adopted</P>";
            } else if( $dAdopt == 0 ) {
                $s .= "<P class='slAdoptionLabel'>Not Adopted Yet</P>";
            } else {
                $s .= "<P class='slAdoptionLabel'>Partially Adopted</P>";
            }
            if( $dAdopt < 250.0 ) {
                $s .= "<P class='slAdoptionLink'>Make a donation to<BR/>adopt this variety</P>";
            }
            if( count($raAdopt) ) {
                $s .= "<P class='slAdoptionThanks'>This variety has been permanently adopted by the donation of <UL>";
                foreach( $raAdopt as $ra ) {
                    if( !empty($ra['public_name']) ) {
                        $s .= "<LI class='slAdoptionThanksLI'>".$ra['public_name']."</LI>";
                    }
                }
                $s .= "</UL>";
            }
        }
        return( $s );
    }


    private function getSpeciesList()
    {
        /* Get alphabetized list of species for accessioned and adopted species
         * [name] => [kSp]  for SEEDForm_Select
         */
        $sCond = "S._status='0' AND P._status='0' AND A._status='0' AND "
                ."P._key=A.fk_sl_pcv AND S._key=P.fk_sl_species AND "
                ."A.fk_sl_pcv<>0 AND A.fk_sl_pcv IS NOT NULL";  // by coincidence works for both queries

        $ra = $this->kfdb->QueryRowsRA(
                    "(SELECT S.name_en as S_name, S._key as _key FROM sl_species S,sl_pcv P,sl_accession A "
                   ."WHERE $sCond AND NOT A.bDeAcc)"
                   ." UNION DISTINCT "
                   ."(SELECT S.name_en, S._key as _key FROM sl_species S,sl_pcv P,sl_adoption A "
                   ."WHERE $sCond) "
                   ."ORDER BY S_name" );

        return( $ra );
    }

    private function getRaPCV( $kSp, $sPCVCond, $iOffset, $iLimit )
    /**************************************************************
        Return the species and cultivar name of all accessions that are NOT bDeAcc AND (filtered by given parms)

        iOffset is origin-0, which is how SQL OFFSET works
     */
    {
//$this->kfdb->SetDebug(2);

        /* Get the pcv of all non-deAcc accessions UNION DISTINCT the pcv of all adoptions
         * This gives us the names of all adopted accessions + all non-adopted accessions + all non-collected adoptions
         * Unfortunately there's no way to implement iOffset in an SQL UNION, so we fetch all the rows up to iOffset+iLimit and only keep the good ones
         */
        $raPCV = array();
        $actualLimit = $iOffset + $iLimit;  // iOffset is origin-0 like in SQL

        $raCond = array();
        $raCond[] = "S._status='0' AND P._status='0' AND A._status='0' AND "
                   ."P._key=A.fk_sl_pcv AND S._key=P.fk_sl_species AND "
                   ."A.fk_sl_pcv<>0 AND A.fk_sl_pcv IS NOT NULL";  // by coincidence works for both queries
        if( $kSp )               $raCond[] = "(S._key='$kSp')";
        if( !empty($sPCVCond) )  $raCond[] = "($sPCVCond)";
        $sCond = "(".implode(" AND ",$raCond).")";

        $sCols = "P._key as _key,P.name as name,S.name_en as S_name";
        if( ($dbc = $this->kfdb->CursorOpen( "(SELECT $sCols FROM sl_species S,sl_pcv P,sl_accession A"
                                            ." WHERE $sCond AND NOT A.bDeAcc)"
                                            ." UNION DISTINCT "
                                            ."(SELECT $sCols FROM sl_species S,sl_pcv P,sl_adoption A"
                                            ." WHERE $sCond) "
                                            ."ORDER BY S_name,name" )) ) {  //  LIMIT $actualLimit
            while( $ra = $this->kfdb->CursorFetch($dbc) ) {
                // The query above just gets a list of names of varieties that are accessioned and/or adopted
                $bAdopt = $this->kfdb->Query1( "SELECT 1 FROM sl_adoption WHERE fk_sl_pcv='{$ra['_key']}'" );

                $g = $this->kfdb->Query1( "SELECT sum(I.g_weight) FROM sl_inventory I,sl_accession A "
                                         ."WHERE I._status='0' AND A._status='0' AND I.fk_sl_accession=A._key AND "
                                                ."NOT I.bDeAcc AND NOT A.bDeAcc AND "
                                                // include varieties adopted OR that have seeds less than 6 years old
                                                .(!$bAdopt ? "(year(now())-year(I.dCreation) <= 6) AND " : "")
                                                ."A.fk_sl_pcv='{$ra['_key']}'" );

                if( !$bAdopt && floatval($g) <= 0.1 ) continue;

                if( $iOffset ) {
                    --$iOffset;
                } else {
                    $raPCV[$ra['_key']] = $ra['S_name']." : ".$ra['name'];
                }
            }
        }

        return( $raPCV );
    }

    private function getRaAcc( $kPCV )
    /************&********************
        Return an array of sl_accession rows for the given pcv
     */
    {
// TODO: this should probably filter via sl_inventory:fk_sl_collection
        $raAcc = array();
        if( ($dbc = $this->kfdb->CursorOpen( "SELECT * FROM sl_accession WHERE _status=0 AND NOT bDeAcc AND fk_sl_pcv='$kPCV'" )) ) {
            while( $ra = $this->kfdb->CursorFetch($dbc) ) {
                $raAcc[] = $ra;
            }
        }
        return( $raAcc );
    }

    private function getRaAdopt( $kPCV )
    /***********************************
        Return an array of sl_adoption rows for the given pcv
     */
    {
        $raAdopt = array();
        if( ($dbc = $this->kfdb->CursorOpen( "SELECT * FROM sl_adoption WHERE _status=0 AND fk_sl_pcv='$kPCV'" )) ) {
            while( $ra = $this->kfdb->CursorFetch($dbc) ) {
                $raAdopt[] = $ra;
            }
        }
        return( $raAdopt );
    }

    private function getRaCSCI( $kPCV )
    /**********************************
     */
    {
        $raCSCI = array();
        if( ($dbc = $this->kfdb->CursorOpen( "SELECT SRC.web as web, SRC.name_en as company_name "
                                            ."FROM sl_pcv P,sl_cv_sources C,sl_sources SRC WHERE P._status=0 AND SRC._status=0 AND C._status=0 "
                                            ."AND (P._key=C.fk_sl_pcv OR (P.psp AND P.psp=C.osp AND P.name=C.ocv)) "
                                            ."AND C.fk_sl_sources=SRC._key "
                                            ."AND P._key='$kPCV'" )) ) {
            while( $ra = $this->kfdb->CursorFetch($dbc) ) {
                $raCSCI[] = $ra;
            }
        }
        return( $raCSCI );
    }


}

?>
