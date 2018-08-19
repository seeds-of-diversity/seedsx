<?php

include("../site.php");
include_once( STDINC."SEEDForm.php" );
include_once( SEEDCOMMON."siteStart.php" );
include_once( PAGE1_TEMPLATE );


$lang = site_define_lang();

list($kfdb,$sess) = SiteStartSession() or die( "Cannot connect to database" );


$page1parms = array (
                "lang"      => $lang,
                "title"     => ($lang == "EN" ? "Canadian Seed Library" : "" ),
                "tabname"   => "HPD",
//              "box1title" => "Box1Title",
//              "box1text"  => "Box2Text",
//              "box1fn"    => "Box1Fn",
//              "box2title" => "Box2Title",
//              "box2text"  => "Box2Text",
//              "box2fn"    => "Box2Fn",

             );


class SL_Public
{
    var $kfdb;
    var $sess;
    var $lang;

    private $oSVA;
    var $sMode = "";
    var $kPCV = 0;

    var $sPSP = "";
    var $sPCVFlt = "";
    var $iPage = 0;
    var $iPCVLimit = 20;

    function SL_Public( &$kfdb, &$sess, $lang )
    {
        $this->kfdb = &$kfdb;
        $this->sess = &$sess;
        $this->lang = $lang;

        $this->oSVA = new SEEDSessionVarAccessor( $sess, 'SLPub' );
        $this->marshalParms();
    }

    function marshalParms()
    /**********************
        Everything depends on mode.  Mode has to be propagated explicitly per-page, never assumed to be the same as previous, otherwise Back/History doesn't work.
        Some parms are persistent across modes, so they're still in play on return to their home mode.
        Modes:
            intro   : show the intro slides using p as a page iterator (not persistent)
            cv      : show all details of a cultivar using k as pcv key (not persistent)
            list    : list pcv names using psp and pcvflt as filters (both persistent) and p as page iterator (not persistent)
            srch    : advanced search same as list with additional persistent filters
     */
    {
//var_dump($_REQUEST); echo "<BR/><BR/>"; var_dump($_SESSION);

        $this->sMode = $this->oSVA->SmartGPC( 'm', array( "home", "list", "intro", "cv", "srch" ) );
// blank mode defaults to stored previous mode. If both blank, start at intro.
        //$this->sMode = SEEDSafeGPC_Smart( 'm', array( "list", "intro", "cv", "srch" ) );

        // if k is specified, use mode cv (this makes nice urls for each variety)
        if( ($this->kPCV = SEEDSafeGPC_GetInt("k")) ) {
            $this->sMode = "cv";
        } else if( $this->sMode == 'cv' ) {  // can't have this mode if k=0
            $this->sMode = "list";
        }
        $this->oSVA->VarSet( 'm', $this->sMode );

        // Non-persistent parms. These are not stored in sess, so have to be propagated per-page.
        $this->iPage = SEEDSafeGPC_GetInt('p');
        if( $this->iPage < 0 ) $this->iPage = 0;


        // Persistent parms. If the http parms are blank, only change the stored parms if the previous page was the same mode (i.e. parms came from the appropriate form).
        if( in_array($this->sMode, array('list','srch')) ) {
            $this->sPSP = $this->oSVA->SmartGPC( 'psp' );
            $this->sPCVFlt = $this->oSVA->SmartGPC( 'pcvflt' );
        }
//echo "<BR/><BR/>"; var_dump($_SESSION);
    }

    function Style()
    {
        echo "<STYLE>"
            .".slFind      { font-family:verdana,helvetica,sans serif;font-size:9pt; }"
            .".slAccBox    { font-size:10pt;margin-left:2em;}"
            .".slUserFeedback {font-size:10pt;}"
            .".slUserTitle	  {font-size:11pt;}"
            .".slAccTable  { font-size:10pt;margin-left:3em;}"
            .".slHome      { font-size:10pt; }"
            .".slStatusBox { background-color:white;margin-bottom:20px;padding:5px;border:2px groove #777;font-size:9pt; }"
            .".slCVPagedList { margin-left:3em; font-size:10pt; }"
            .".slWeblinks  	 { font-size:8pt;}"
            ."</STYLE>";
    }

    function Draw()
    {
        echo "<TABLE border='0' cellspacing='0' cellpadding='0' width='100%' style='margin-left:50px'><TR><TD valign='top'>"
            ."<DIV ".($this->sMode == 'intro' ? "style='text-align:center;'" : "style='border:thin solid grey;background-color:#EEFFEE;margin:0px;padding:0px'").">"
            ."<DIV style='margin:10px;'>";  // inner div to do padding via margins so IE doesn't get confused
        switch( $this->sMode ) {
            case "home":    echo $this->drawHome();     break;
            case "list":    echo $this->drawList();     break;
            case "srch":    echo $this->drawSrch();     break;
            case "cv":      echo $this->drawPcv();      break;
            default:        echo $this->drawIntro();    break;
        }
        echo "</DIV></DIV></TD>"
            ."<TD valign='top' width='200'>"
            .$this->drawRightCol()
            ."</TD></TR></TABLE>";
    }

    function drawHome()
    {
        $s = "";

        $nCV = $this->kfdb->Query1( "SELECT count(*) FROM sl_pcv WHERE _status=0" );
        $nAdoptAmount = $this->kfdb->Query1( "SELECT sum(amount) FROM sl_adoption WHERE _status=0" );

        $s .= "<TABLE cellpadding='5' cellspacing='5' border='0' width='100%'><TR valign='top'>"
             ."<TD>"
             ."<IMG src='http://www.seeds.ca/int/doc/docpub.php?n=web/main_web_image_root/sl/sl004.jpg' width='150'><BR/><BR/>"
             ."<IMG src='http://www.seeds.ca/int/doc/docpub.php?n=web/main_web_image_root/sl/sl001.jpg' width='150'><BR/><BR/>"
             ."</TD><TD>"
             ."<P class='slHome'><B style='color:green'>Seeds of Diversity's Canadian Seed Library</B><BR/> is a collection of seeds that backs up the work of our member seed "
            ."savers and Canadian heritage seed companies.<BR/><BR/> As a not-for-profit project, we store back-up samples of Canada's rarest seeds (and some not so rare) in low-humidity freezers "
            ."to keep them viable and available for future gardeners and farmers. These are your seeds, your collection, brought to you by the work and donations "
            ."of hundreds of people.</P>";
        $s .= "<BLOCKQUOTE>"
             ."<P class='slHome'>Now storing ".$this->kfdb->Query1( "SELECT count(*) FROM sl_accession WHERE _status=0" )." samples of seeds.</P>"
             ."<P class='slHome'>".intval($nAdoptAmount/($nCV*2.5))."% permanently adopted through donations from ".$this->kfdb->Query1( "SELECT count( distinct fk_mbr_contacts ) FROM sl_adoption WHERE _status=0" )
             ." people and companies,<BR/> totalling $".$this->kfdb->Query1( "SELECT sum(amount) FROM sl_adoption WHERE _status=0" )." so far. "
             ."<A href='http://www.seeds.ca/mbr'>Adopt a Variety today!</A></P>"
             ."<P class='slHome'>Backing up ".$this->kfdb->Query1( "SELECT count(*) FROM sl_pcv WHERE _status=0" )." varieties from "
             .$this->kfdb->Query1( "SELECT count(distinct psp) FROM sl_pcv WHERE _status=0" )." plant species.</P>"
             ."<P class='slHome'>What does it look like? Imagine ".$this->kfdb->Query1( "SELECT ROUND((sum(g_have)+sum(g_pgrc))/1000, 2) FROM sl_accession WHERE _status=0" )." kg of seeds carefully dried and stored in airtight jars in big freezers!</P>"

;
        $s .= "</BLOCKQUOTE>"
             ."<P><A href='{$_SERVER['PHP_SELF']}?m=intro' style='color:green'>Learn About the Canadian Seed Library</A></P>"
             ."<P><A href='{$_SERVER['PHP_SELF']}?m=list' style='color:green'>Complete List of Varieties</A></P>"
             ."<P><SPAN style='color:green'>Now Offering these Seeds to the Public</SPAN></P>"
             ."<BLOCKQUOTE>"
             ."<P class='slHome'>We offer seed samples to gardeners and farmers when we have seeds in enough quantity, and they are not available "
             ."from any Canadian seed company (that we know of - please tell us if you know otherwise!).</P>"
             ."<P class='slHome'>That way, more seed varieties are available to people. "
             ."They should be, because these are the people's seeds!</P>"
             ."<P class='slHome'>Contact us at ".SEEDStd_EmailAddress( 'library', 'seeds.ca' )." about requesting seed samples.</P>";

//        if( ($dbc = $this->kfdb->CursorOpen( "SELECT * FROM sl_pcv WHERE _status=0" )) ) {
//            while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
//                $raCSCI = $this->getRaCSCI( $ra['_key'] );
//                if( count($raCSCI)==0 && $this->canDistribute( $ra['_key'] ) ) {
//                    $s .= $ra['psp']." : ".$ra['name']."<BR/>";
//                }
//            }
//        }

        $s .= "<P class='slHome'>";
        if( ($dbc = $this->kfdb->CursorOpen( "SELECT * FROM sl_distribute WHERE bDist" )) ) {
            while( $ra = $this->kfdb->CursorFetch( $dbc ) ) {
                $raCV = $this->getPCV( $ra['fk_sl_pcv'] );
                $s .= "<A href='{$_SERVER['PHP_SELF']}?k={$ra['fk_sl_pcv']}'>{$raCV['psp']} : {$raCV['name']}<BR/>";
            }
        }
        $s .= "</P>";

        $s .= "</BLOCKQUOTE>"
             ."</TD><TD>"
             ."<IMG src='http://www.seeds.ca/int/doc/docpub.php?n=web/main_web_image_root/sl/sl002.jpg' width='150'><BR/><BR/>"
             ."<IMG src='http://www.seeds.ca/int/doc/docpub.php?k=82c64d8d76e1c0d21a08d477d7887af800000275' width='150'><BR/><BR/>"
             ."</TD></TR></TABLE>";


        return( $s );
    }

    function drawIntro()
    {
    	$maxPage = 12;     // todo: enumerate the images in the sl/img directory but this is fine

        $p = $this->iPage;
        if( $p < 0 || $p > $maxPage )  $p = 0;

        $s = "<TABLE border='0'><TR>"
            ."<TD valign='center'>";
        if( $p > 1 )  $s .= "<FORM method='get' action='${_SERVER['PHP_SELF']}'><INPUT type='hidden' name='p' value='".($p-1)."'/><INPUT style='font-size:12pt' type='submit' value=' &lt; '/></FORM>";
        $s .= "&nbsp;</TD>"
            ."<TD valign='center'><A HREF='{$_SERVER['PHP_SELF']}?".( ($p < $maxPage) ? ("p=".($p+1)) : "m=list" )."' style='text-decoration:none;'>"
            ."<IMG src='img/sl".sprintf("%02d",$p).".png' width='600' border='0'/>"
            ."</A></TD>"
            ."<TD valign='center'>";
        if( $p < $maxPage )   $s .= "<FORM method='get' action='${_SERVER['PHP_SELF']}'><INPUT type='hidden' name='p' value='".($p+1)."'/><INPUT style='font-size:12pt' type='submit' value='Next &gt; '/></FORM>";
        if( $p == $maxPage )  $s .= "<FORM method='get' action='${_SERVER['PHP_SELF']}'><INPUT type='hidden' name='m' value='list'/><INPUT style='font-size:12pt' type='submit' value=' &gt; '/></FORM>";
        $s .= "</TD></TR></TABLE>";

//            ."<DIV style='width:600px;'><BR/>";
//        if( $p > 1 )        $s .= "<SPAN style='text-align:left;'><A href='{$_SERVER['PHP_SELF']}?p=".($p-1)."'>&lt;&lt; Previous Page</A></SPAN>";
//        $s .= SEEDStd_StrNBSP("", 20 );
//        if( $p < $nPages )  $s .= "<SPAN style='text-align:right;'><A href='{$_SERVER['PHP_SELF']}?p=".($p+1)."'>Next Page &gt;&gt;</A></SPAN>";
//        $s .= "</DIV>";
        return( $s );
    }

    function drawPcv()
    {
        $ra = $this->getPCV( $this->kPCV );

        $s = "<TABLE border='0' cellspacing='0' cellpadding='0' width='100%'>"
            ."<TR><TD valign='top' colspan='2'>"
            ."<H3>{$ra['psp']} : {$ra['name']}</H3>"
            .$this->drawPcvPhotos()
            ."</TD></TR>"
            ."<TR><TD valign='top'>"
            .$this->drawPcvAccessions()
            .$this->drawSEDInfo()	// Seed Directory information
            ."<BR/><BR/>"
            .$this->drawPcvData()."<BR/><BR/>"
            .$this->drawPcvHistory()
            ."</TD>"
            ."<TD valign='top' width='200'>"
            .$this->drawPcvStatus()
            .$this->drawPcvGetSeeds()."<BR/><BR/>"
            ."</TD></TR>"
            ."</TABLE>";
        return( $s );
    }


    function drawList()
    {
        $raPSP = array(""=>"--- All Species ---");
        $raPSP1 = $this->getRaPSP();
        foreach( $raPSP1 as $p ) {
            $raPSP[$p] = $p;
        }

        $sPCVCond = "";
        if( !empty($this->sPCVFlt) ) {
            $sPCVCond = "P.name LIKE '%".addslashes($this->sPCVFlt)."%'";
        }

// problem using POST: the Page links leave p=N on the address bar, which is found by GPC.
// - search for pcv that has two page
// - go to page 2 (p=1)
// - search for pcv that has one page, iPage is internally reset to 0 because of pcvflt change, so it works
// - click on Match button again, p=1 is still in the Get part of GPC, so screen moves to page 2 (OFFSET 20) - nothing found
        $s = "<FORM method='get' action='${_SERVER['PHP_SELF']}'>".SEEDForm_Hidden("m","list")
            ."Show species ".SEEDForm_Select( "psp", $raPSP, $this->sPSP, array("selectAttrs"=>"onChange='submit();'") )
            .SEEDStd_StrNBSP("",15)."Variety name ".SEEDForm_Text( "pcvflt", $this->sPCVFlt, "", 20 )." <INPUT type='submit' value='Match'>"
            ."</FORM><BR/>"
            .$this->drawCVPaged( $this->sPSP, $sPCVCond );
        return( $s );
    }

    function drawSrch()
    {
        return( "Show the search criteria<BR/><BR/>".$this->drawCVPaged() );
    }

    function drawCVPaged( $sPSP = NULL, $sPCVCond = NULL )
    /*
     */
    {
        $nPagesNext = 2;  // this is the number of pages after the current page that we try to detect

        $page = $this->iPage;

        // fetch more than iLimit, so we can tell how many pages come later.  Could just sample with n more queries, but this is just as efficient, maybe more
        $raPCV = $this->getRaPCV( $sPSP, $sPCVCond, $page * $this->iPCVLimit, $this->iPCVLimit * ($nPagesNext + 1) );

        // page parm is origin-0, number shown is origin-1
        if( count($raPCV) ) {
            $s = "<P>Page:&nbsp;";
            if( $page > 0 ) {
                $s .= "&nbsp;".$this->_drawCVPageLink( "&lt;&lt;", $page-1 );
            }
            for( $i = -2; $i <=2; ++$i ) {
                if( $page + $i >= 0 && count($raPCV) > $i * $this->iPCVLimit ) {
        	        $s .= "&nbsp;".$this->_drawCVPageLink( $page+$i+1, $page+$i );
                }
            }
            if( count($raPCV) > $this->iPCVLimit ) {
                $s .= "&nbsp;".$this->_drawCVPageLink( "&gt;&gt;", $page+1 );
            }
            $s .= "</P>";

            $s .= "<TABLE class='slCVPagedList' border='0' cellspacing='0' cellpadding='5'>"
                 ."<TR><TH>&nbsp;</TH><TH>Adoption</TH></TR>";
            $i = 0;
            foreach( $raPCV as $k => $v ) {
                if( $i++ >= $this->iPCVLimit )  break;
                $s .= "<TR><TD valign='center'><A HREF='{$_SERVER['PHP_SELF']}?k=$k'>$v</A></TD>"
                      ."<TD valign='top'>".$this->drawAdoptionBar( $k )."</TD>"
                      ."</TR>";
            }
            $s .= "</TABLE>";
        } else {
            $s = "No varieties match your search.";
        }
        return( $s );
    }

    function _drawCVPageLink( $label, $p )
    {
        $sUrl = "";
    	if( !empty($this->sPSP) )     $sUrl .= "&psp=".urlencode($this->sPSP);
    	if( !empty($this->sPCVFlt) )  $sUrl .= "&pcvflt=".urlencode($this->sPCVFlt);

        return( ($p == $this->iPage) ? $label : "<A HREF='{$_SERVER['PHP_SELF']}?m={$this->sMode}&p=$p".$sUrl."'>$label</A>" );
    }

    function drawAdoptionBar( $kPCV, $bTextBelow = false )
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
            ."<TD valign='top'>".($iBar == 2 ? $dAdopt : "&nbsp")."</TD>"
            ."<TD valign='top'>".($iBar == 3 ? $dAdopt : "&nbsp")."</TD>"
            ."<TD valign='top'>".($iBar == 4 ? $dAdopt : "&nbsp")."</TD>"
            ."<TD valign='top'>".($iBar >= 5 ? $dAdopt : "&nbsp")."</TD>"
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
                $s .= "<P class='slAdoptionThanks'>This variety has been adopted by the permanent donation of <UL>";
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

    function drawPcvAccessions()
    {
        $raAcc = $this->getRaAcc( $this->kPCV );

        $s = "";
        if( count($raAcc) ) {
            $s .= "<DIV class='slAccBox'>We have ".count($raAcc)." sample".(count($raAcc)>1 ? "s":"")." of this variety in our collection.";
            foreach( $raAcc as $ra ) {
                $s .= $this->drawAcc($ra);
            }
            $s .= "</DIV>";
        }
        return( $s );
    }
    function drawPcvData()
    {
        return( "" ); //"[Data]" );
    }
    function drawPcvHistory()
    {
        return( "" ); //"[History]" );
    }
    function drawPcvStatus()
    {
        $s = "<DIV class='slStatusBox'>".$this->drawAdoptionBar($this->kPCV, true)."</DIV>";

        return( $s );
    }
    function drawPcvGetSeeds()
    {
    	$s = "<DIV class='slStatusBox'>";

        $raCSCI = $this->getRaCSCI( $this->kPCV );
        if( count($raCSCI) ) {
            $s .= "This variety was available from the following Canadian seed compan".(count($raCSCI)==1?"y":"ies")
                 ." within the past few years."
                 ."<DIV style='margin-left:20px;'>";
            foreach( $raCSCI as $ra ) {
                $s .= "<div style='margin-top:5px;'><a href='http://{$ra["web"]}' target='_blank'>{$ra["company_name"]}</a></div>";
            }
            $s .= "</DIV><BR/>Please support our local heritage seed companies.";
        } else if( $this->canDistribute( $this->kPCV ) ) {    // if our quantities allow
            $s .= "According to our records, no Canadian seed companies are selling this variety.<BR/><BR/>"
                 ."You can request a sample of these seeds from Seeds of Diversity by contacting ".SEEDStd_EmailAddress('library','seeds.ca').".";
        } else {
            $s .= "Unfortunately, we are not able to offer samples of these seeds at this time.";
        }
        $s .= "</DIV>";
    	return( $s );
    }

    function canDistribute( $kPCV )
    {
        $gHave = $this->kfdb->Query1( "SELECT sum(g_have) FROM sl_accession WHERE fk_sl_pcv='$kPCV' "
                                     ."AND LEFT(location,1)<>'P' AND _status=0 AND Not bDeAcc" );
        // The largest samples are 25g and we won't distribute more than 10% of the remaining sample.
        // To be conservative, start with a threshold of 10*25g. Then allow for smaller samples. That way if a species is not listed here,
        // the error will be on not distributing it, not over-distributing.
        $bCanDist = ( $gHave > 250 );
        $raPCV = $this->getPCV( $kPCV );
        if( in_array( $raPCV['psp'], array('Tomato', 'Pepper', 'Broccoli', 'Lettuce' ) ) )      $bCanDist = ($gHave > 4);
        if( in_array( $raPCV['psp'], array('Barley', 'Oat', 'Wheat', 'Cucumber', 'Melon' ) ) )  $bCanDist = ($gHave > 50);

        return( $bCanDist );
    }

    function drawAcc($ra)
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

    function drawRightCol()
    {
        $s = "<DIV style='margin-left:40px;font-size:10pt;'>"
            ."<P><A HREF='{$_SERVER['PHP_SELF']}?m=home'>Home</A></P>"
            ."<P><A HREF='{$_SERVER['PHP_SELF']}?m=intro'>Learn About the Canadian Seed Library</A></P>"
            ."<P><A HREF='{$_SERVER['PHP_SELF']}?m=list'>List of Varieties</A></P>"
 //         ."<P><A HREF='{$_SERVER['PHP_SELF']}?m=src'>Advanced Search</A></P>"
            ."</DIV>";

        return( $s );
    }


    function drawAccTR( $t1, $t2 )
    {
        return( "<TR><TD valign='top'>$t1</TD><TD>&nbsp;&nbsp;&nbsp;</TD><TD valign='top'>$t2</TD></TR>" );
    }

    function getPCV( $kPCV )
    {
        return( $this->kfdb->QueryRA( "SELECT * FROM sl_pcv WHERE _key='$kPCV'") );
    }

    function getRaPSP()   // N.B. This does not retrieve psp:pcv for non-collected adoptions.  i.e. where a pcv is adopted but not accessioned
    {
        $raPSP = array();
        if( ($dbc = $this->kfdb->CursorOpen( "SELECT distinct(P.psp) FROM sl_pcv P,sl_accession A"
                                            ." WHERE P._key=A.fk_sl_pcv AND P._status=0 AND A._status=0 AND NOT A.bDeAcc ORDER BY 1" )) ) {
            while( $ra = $this->kfdb->CursorFetch($dbc) ) {
                $raPSP[] = $ra['psp'];
            }
        }
        return( $raPSP );
    }

    function getRaPCV( $sPSP, $sPCVCond, $iOffset, $iLimit )
    /*******************************************************
        Return the psp and pcv of all accessions that are NOT bDeAcc AND (filtered by given parms)

        iOffset is origin-0, which is how SQL OFFSET works
     */
    {
//$this->kfdb->SetDebug(2);

        /* Get the psp:pcv of all non-deAcc accessions UNION DISTINCT the psp:pcv of all adoptions
         * This gives us the names of all adopted accessions + all non-adopted accessions + all non-collected adoptions
         * Unfortunately there's no way to implement iOffset in an SQL UNION, so we fetch all the rows up to iOffset+iLimit and only keep the good ones
         */
        $raPCV = array();
        $actualLimit = $iOffset + $iLimit;  // iOffset is origin-0 like in SQL

        $raCond = array();
        $raCond[] = "P._status=0 AND A._status=0 AND P._key=A.fk_sl_pcv AND A.fk_sl_pcv<>0 AND A.fk_sl_pcv IS NOT NULL";  // by coincidence works for both queries
        if( !empty($sPSP) )      $raCond[] = "(P.psp='".addslashes($sPSP)."')";
        if( !empty($sPCVCond) )  $raCond[] = "($sPCVCond)";
        $sCond = "(".implode(" AND ",$raCond).")";

        if( ($dbc = $this->kfdb->CursorOpen( "(SELECT P._key as _key,P.psp as psp,P.name as name FROM sl_pcv P,sl_accession A"
                                            ." WHERE $sCond AND NOT A.bDeAcc)"
                                            ." UNION DISTINCT "
                                            ."(SELECT P._key as _key,P.psp as psp,P.name as name FROM sl_pcv P,sl_adoption A"
                                            ." WHERE $sCond) "
                                            ."ORDER BY psp,name LIMIT $actualLimit" )) ) {
            while( $ra = $this->kfdb->CursorFetch($dbc) ) {
            	if( $iOffset ) {
            	    --$iOffset;
            	} else {
                    $raPCV[$ra['_key']] = $ra['psp']." : ".$ra['name'];
            	}
            }
        }

        return( $raPCV );
    }

    function getRaAcc( $kPCV )
    /***************************
        Return an array of sl_accession rows for the given pcv
     */
    {
        $raAcc = array();
        if( ($dbc = $this->kfdb->CursorOpen( "SELECT * FROM sl_accession WHERE _status=0 AND NOT bDeAcc AND fk_sl_pcv='$kPCV'" )) ) {
            while( $ra = $this->kfdb->CursorFetch($dbc) ) {
                $raAcc[] = $ra;
            }
        }
        return( $raAcc );
    }

    function getRaAdopt( $kPCV )
    /***************************
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

    function getRaCSCI( $kPCV )
    /**************************
     */
    {
        $raCSCI = array();
        if( ($dbc = $this->kfdb->CursorOpen( "SELECT C.web as web, C.name_en as company_name "
                                            ."FROM sl_pcv P,csci_seeds S,csci_company C WHERE P._status=0 AND S._status=0 AND C._status=0 "
                                            ."AND P.psp=S.psp AND P.name=S.icv "
                                            ."AND S.company_name=C.name_en "
                                            ."AND P._key='$kPCV'" )) ) {
            while( $ra = $this->kfdb->CursorFetch($dbc) ) {
                $raCSCI[] = $ra;
            }
        }
        return( $raCSCI );
    }


	/*
	 * Name:	userFeedback()
	 * Purpose:	Retrieves any available feedback in the database and displays
	 * 			it within the page.
	 * Inputs:	none
	 * Outputs:	A table containing user feedback
	 * Returns:	A string containing the table data
	 */
	function drawSEDInfo()
	{
		$memberList = array();

		$psp = $this->kfdb->Query1("SELECT psp FROM sl_pcv WHERE _key='{$this->kPCV}'");

		$query = "SELECT sl_pcv._key, sed_seeds.mbr_id, sed_seeds.description, sed_seeds.days_maturity, sed_seeds.year, sed_seeds.mbr_code"
                ." FROM sl_pcv, sed_seeds WHERE sl_pcv._key='{$this->kPCV}' AND sed_seeds.variety=sl_pcv.name AND sed_seeds.type LIKE '$psp%'"
                ." ORDER BY sed_seeds.mbr_id";
		$table = "";
		if( ($dbc = $this->kfdb->CursorOpen($query)) ) {
			$num = $this->kfdb->CursorGetNumRows($dbc);  // get number of rows returned

			if ($num > 0)  // if there are rows...
			{
				$table = "<br /><p class='slUserTitle'><b>What our members wrote about this variety:</b></p>"
				        ."<table class='slUserFeedback'>";

				while($row = $this->kfdb->CursorFetch($dbc))
				{
					if ( ($row["description"] == "") || ($row["description"] == null) )
					{
						continue;
					}

					$newMember = new Member();
					$newMember->SetId($row["mbr_id"]);					// save member's id
					$newMember->SetDescription($row["description"]);	// save description

					$yearArray = array();
					array_push($yearArray, $row["year"]);	// save the current year in the array
					$newMember->SetYears($yearArray);		// save the array as member's array

					if ($row["days_maturity"])
					{
						$newMember->SetMaturity($row["days_maturity"]);  // save maturity if available
					}

					$province = $newMember->extractProvince($row["mbr_code"]);  // get the member's province
					$newMember->SetProvince($province);							// set province

					array_push($memberList, $newMember);  // add member to $memberList
				}

				$tempMember = new Member();  	// will be used in the following loop
				$counter = 0;					// used for tracking loop position
				$count = count($memberList);	// get the size of $memberList array for tracking purposes

				foreach ($memberList as $member)  // loop through all members in list
				{
					$counter++;  		// increase counter to reflect current number of iterations through loop

					if ($counter == 1)  // first time through loop
					{
						$tempMember = $member; 		// just copy over this member
					}
					else if ($counter == $count)  	// last time through loop. Send out $tempMember and possibly $member after doing checks on description.
					{
						if ($tempMember->GetId() == $member->GetId()) // last member is same member as before
						{
							if ($tempMember->GetDescription() == $member->GetDescription())  // same description from this member as last time
							{
								$tempArray = $tempMember->GetYears();					// get the $tempMember's array
								array_push($tempArray, current($member->GetYears()));	// append the next year from $member into $temparray
								$tempMember->SetYears($tempArray);						// reset $tempMember's year array
								$table .= $this->displayMemberFeedback($tempMember);  	// add feedback from $tempMember to page
							}
							else // different descriptions. Send out both members
							{
								$table .= $this->displayMemberFeedback($tempMember);  	// add feedback from $tempMember to page
								$table .= $this->displayMemberFeedback($member);  		// add feedback from $member to page
							}
						}
						else  // different members. Send out both members
						{
							$table .= $this->displayMemberFeedback($tempMember);  		// add feedback from $tempMember to page
							$table .= $this->displayMemberFeedback($member);  			// add feedback from $member to page
						}

					}
					else  // somewhere in the middle of $memberList
					{
						if ($tempMember->GetId() == $member->GetId()) // same member as before
						{
							if ($tempMember->GetDescription() == $member->GetDescription())  // same description from this member as last time
							{
								$tempArray = $tempMember->GetYears();					// get the $tempMember's array
								array_push($tempArray, current($member->GetYears()));	// append the next year from $member into $temparray
								$tempMember->SetYears($tempArray);						// reset $tempMember's year array
							}
							else  // different description. send out current $tempMember and save $member as new $tempMember
							{
								$table .= $this->displayMemberFeedback($tempMember);  // add feedback from $tempMember to page
								$tempMember = $member;
							}
						}
						else  // different member. Send out $tempMember and save $member as new $tempMember
						{
							$table .= $this->displayMemberFeedback($tempMember);  // add feedback from $tempMember and $member to page
							$tempMember = $member;
						}
					}
				}

				$table .= "</table>";  // close off table. end of page data from this function.
			}
		}
		return $table;
	}


	/*
	 * Name:	displayMemberFeedback()
	 * Purpose:	Creates table rows with member feedback data
	 * Inputs:	$member - a member object containing all needed values to display
	 * Outputs:	none
	 * Returns:	A string containing all of the table row data
	 */
	function displayMemberFeedback($member)
	{
		$id = $member->GetId();						// get the member's id attribute
		$description = $member->GetDescription();	// access the member's description attribute
		$maturity = $member->GetMaturity();			// access member's maturity attribute
		$province = $member->GetProvince();			// access member's province
		$years = $member->GetYears();				// get member's year array
		$range = "";

		$count = count($years);  	// count number of elements in the array

		$tableData = "<tr><td><b>";	// start member information row

		if ($count == 1)  			// only one year, no duplicate descriptions
		{
			$tableData .= "In " . current($years);
		}
		else  // numerous years of duplicate feedback
		{
			$range = current($years) . " - " . end($years); // create a string with the range of years in which this user made duplicate comments
			$tableData .= "From " . $range;
		}

		if ($province != "")  // province available
		{
			$tableData .= " a member from " . $province . " said:";
		}
		else  // not canadian
		{
			$tableData .= " one of our members said:";
		}

		$tableData .= "</b></td></tr>"; // close information row
		$tableData .= "<tr><td><i>";	// start feedback row
		$tableData .= $description;		// add description

		if ($maturity != "") 			// there's a value for maturity
		{
			$tableData .= " - Days to maturity: " . $maturity;
		}

		$tableData .= "</i></td></tr>"; // close off feedback row

		return $tableData;	// return the page info
	}

	/*
	 * Name:	drawPhotos()
	 * Purpose:	Using highslide and jCarousel libraries (JavaScript), this function will display a filmstrip
	 * 			of photos from a database. Dynamic interaction is included.
	 * Inputs:	none
	 * Outputs:	all photos from the database relating to the current page
	 * Returns:	a string containing the page data
	 */
    function drawPcvPhotos()
    {
		$highslideEffect = "

		<script type=\"text/javascript\" src=\"" . W_ROOT . "highslide/highslide-with-gallery.js\"></script>
		<link rel=\"stylesheet\" type=\"text/css\" href=\"" . W_ROOT . "highslide/highslide.css\" />

		<script type=\"text/javascript\">
			hs.graphicsDir = '" . W_ROOT . "highslide/graphics/';
			hs.align = 'center';
			hs.transitions = ['expand', 'crossfade'];
			hs.fadeInOut = true;
			hs.dimmingOpacity = 0.8;
			hs.wrapperClassName = 'borderless floating-caption';
			hs.captionEval = 'this.a.title';
			hs.marginLeft = 100; // make room for the thumbstrip
			hs.marginBottom = 80; // make room for the controls and the floating caption
			hs.numberPosition = 'caption';
			hs.lang.number = '%1/%2';

			// Add the slideshow providing the controlbar and the thumbstrip
			hs.addSlideshow({
				//slideshowGroup: 'group1',
				interval: 5000,
				repeat: false,
				useControls: true,
				overlayOptions: {
					className: 'text-controls',
					position: 'bottom center',
					relativeTo: 'viewport',
					offsetX: 50,
					offsetY: -5

				},
				thumbstrip: {
					position: 'center',
					mode: 'horizontal',
					relativeTo: 'viewport'
				}
			});

			// Add the simple close button
			hs.registerOverlay({
				html: '<div class=\"closebutton\" onclick=\"return hs.close(this)\" title=\"Close\"></div>',
				position: 'top right',
				fade: 2 // fading the semi-transparent overlay looks bad in IE
			});
		</script>

		<!-- jQuery library -->
		<script type=\"text/javascript\" src=\"" . W_ROOT . "jcarousel/jquery-1.2.3.pack.js\"></script>

		<!-- jCarousel library -->
		<script type=\"text/javascript\" src=\"" . W_ROOT . "jcarousel/jquery.jcarousel.pack.js\"></script>

		<!-- jCarousel core stylesheet -->
		<link rel=\"stylesheet\" type=\"text/css\" href=\"" . W_ROOT . "jcarousel/jquery.jcarousel-1.css\" />

		<!-- jCarousel skin stylesheet -->
		<link rel=\"stylesheet\" type=\"text/css\" href=\"" . W_ROOT . "jcarousel/skins/tango/skin-1.css\" />

		<script type=\"text/javascript\">
		jQuery(document).ready(function()
		{
		    jQuery('#mycarousel').jcarousel();
		} );
		</script>

		<style type=\"text/css\">
		.highslide img
		{
			border-width: 1px;
		}

		.highslide-active-anchor img
		{
			visibility: visible;
		}
		</style>


		 ";

        $s = "";
        $imageArray = array();  // stores images and associated thumbnails

        $query = "SELECT * FROM sl_photos WHERE sl_photos.fk_sl_pcv='{$this->kPCV}'";

        if( ($result = $this->kfdb->CursorOpen($query)) ) {
            while( $row = $this->kfdb->CursorFetch($result) ) {
                $imageArray[$row["filePath"]] = $row["caption"];
            }
        }
        if( count($imageArray) ) {
            // insert page elements
            $photos = "<div style=\"width: 722px; margin: 10px auto;\">

                    <!-- Element used as thumbnailId for the image popups to expand from and close to -->
                    <span id=\"thumb-target\" style=\"width: 1px; margin-left: 361px; height: 1px;\"></span>

                    <ul id=\"mycarousel\" class=\"jcarousel-skin-tango highslide-gallery\"> ";

            while (list($image, $text) = each($imageArray)) // walk through each key/data pair
            {
                $photos .= "<li>
                                    <a href=\"" . $image . "\" class=\"highslide\"
                                    onclick=\"return hs.expand(this, {thumbnailId: 'thumb-target'})\" title=\"" . htmlentities($text,ENT_QUOTES) . "\">";

                $thumbnail = $image;
                $photos .= "<img src=\"" . $thumbnail . "\"  alt=\"\" /></a></li>";  // make the image within the anchor become the thumbnail
            }

            $photos .= "</ul></div>";  // close off the elements
            $s = $highslideEffect . $photos;
        }

//     * **** It is recommended to use thumbnails at a size of 120x80 pixels or something close to that.

// "<DIV style='width:100%;background-color:#777;color:white;text-align:center;font-size:14pt;'>Photos</DIV>"
// ."<DIV style='text-align:center;padding:10px 0px;'>Upload a photo!</DIV>" );

		return $s;

	}

} // SL_Public class



/*
 * This class is used to hold member details for easier organization
 */
class Member
{
	// attributes
	var $id;
	var $province;
	var $maturity;
	var $description;
	var $years;

	/*
	 * This constructor initializes all attributes to a blank state.
	 */
	function Member()
	{
		$this->id = "";
		$this->province = "";
		$this->maturity = "";
		$this->description = "";
		$this->years = array();
	}

	/*** Accessors ***/

	function GetId()
	{
		return $this->id;
	}

	function GetProvince()
	{
		return $this->province;
	}

	function GetMaturity()
	{
		return $this->maturity;
	}

	function GetDescription()
	{
		return $this->description;
	}

	function GetYears()
	{
		return $this->years;
	}

	/*** Mutators ***/

	function SetId($newId)
	{
		$this->id = $newId;
	}

	function SetProvince($newProvince)
	{
		$this->province = $newProvince;
	}

	function SetMaturity($newMaturity)
	{
		$this->maturity = $newMaturity;
	}

	function SetDescription($newDescription)
	{
		$this->description = $newDescription;
	}

	function SetYears($newYear)
	{
		$this->years = $newYear;
	}


	/*
	 * Name:	extractProvince
	 * Purpose:	Gets the appropriate province based on the member code passed in as an argument.
	 * Inputs:	$memberCode - Comes from the mbr_code field in the sed_seeds table.
	 * Outputs:	None
	 * Returns:	$provinceName - A string containing the actual province based on the member code.
	 */
	function extractProvince($memberCode)
	{
		$provinceName = "";
		$provinces = array ("B.C" => "British Columbia" 	, "ALB" => "Alberta",
							"SAS" => "Saskatchewan"			, "MAN" => "Manitoba",
							"ONT" => "Ontario"				, "QUE" => "Quebec",
							"N.B" => "New Brunswick"		, "N.S" => "Nova Scotia",
							"PEI" => "Prince Edward Island"	, "NFL" => "Newfoundland",
							"YUK" => "Yukon"				, "NWT" => "Northwest Territories",
							"NUN" => "Nunavut");

		while (list($key, $value) = each($provinces) )  	// separate the key and value from each index
		{
			$compResult = strncmp($key, $memberCode, 3);	// compare the first 3 characters to identify the province code

			if ($compResult == 0)
			{
				$provinceName = $value;  // save the province value
				break;
			}
		}

		return $provinceName;
	}
}

/********************************************* End of Matt's additional code ************************************************/


/*
    CREATE TABLE sl_photos (
        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    fk_sl_pcv  INTEGER NOT NULL,
    caption    TEXT,
    filePath   TEXT
    );
 */




$oSL = new SL_Public( $kfdb, $sess, $lang );


Page1( $page1parms );


function Page1Body() {
    global $oSL;

    $oSL->Style();

    echo "<TABLE border='0'><TR><TD valign='center'><H2>Canadian Seed Library</H2></TD>"
        ."<TD>".SEEDStd_StrNBSP("",10)."</TD>";
    if( $oSL->sMode != 'list' && $oSL->sMode != 'srch' ) {
        echo "<TD valign='center' class='slFind'><FORM method='get' action='${_SERVER['PHP_SELF']}'>".SEEDForm_Hidden('m','list')
            ."<B>Find a variety</B><BR/>"
            .SEEDForm_Text( 'pcvflt', "", "", 30, "class='slfind" )." <INPUT type='submit' value='Search' class='slFind'/>"
            ."</FORM></TD>";
    }
    echo "</TR></TABLE>";

    $oSL->Draw();

//    echo "<DIV style='margin:3em 5em'>Thanks to our Canadian Seed Library Partners:<BLOCKQUOTE>"
//        ."<A href='http://www.prseeds.ca'>Prairie Garden Seeds</A><BR/><BR/>"
//        ."<A href='http://www.heritageharvestseed.com'>Heritage Harvest Seed</A><BR/><BR/>"
//        ."</BLOCKQUOTE></DIV>";




exit;

    $raAcc = array();
    $sCond = "";
    if( !$osPSP->IsEmpty() ) {
        $sCond = "psp='".$osPSP->DB()."' AND pname='".$osPCV->DB()."'";
    } else if( !$osSrch->IsEmpty() ) {
        $sCond = "pname LIKE '%".$osSrch->DB()."%'";
    }
    if( !empty($sCond) ) {
//$kfdb->SetDebug(2);
        if( ($dbc = $kfdb->CursorOpen( "SELECT * FROM SL_Accession WHERE $sCond" )) ) {
            while( $ra = $kfdb->CursorFetch($dbc) ) {
                $raAcc[$ra['psp']."|".$ra['pname']][] = $ra;
            }
        }
    }
    if( count($raAcc) == 1 ) {
        $raCV = current($raAcc);
        echo "<DIV style='border:thin solid grey;background-color:#EEFFEE;margin-left:50px;margin-right:30px;'>"
            ."<H3>".$raCV[0]['psp']." : ".$raCV[0]['pname']."</H3>"
            ."<TABLE border='0'><TR><TD valign='top'>";
        foreach( $raCV as $ra ) {
            echo drawAcc($ra);
        }
        echo "<TD valign='top' width='40%'>";

        $raAdopt = array();
        if( ($dbc = $kfdb->CursorOpen( "SELECT * FROM SL_Adoption WHERE psp='".addslashes($raCV[0]['psp'])."' AND pcv='".addslashes($raCV[0]['pname'])."'" )) ) {
            while( $ra = $kfdb->CursorFetch($dbc) ) {
                $s = $ra['public_name'];
                if( $ra['x_d_donation'] ) $s .= ", ".$ra['x_d_donation'];
                $raAdopt[] = $s;
            }
        }
        if( count($raAdopt) > 0 ) {
            echo "<H3>Adopted by:</H3><UL style='margin-left:1em'>";
            foreach( $raAdopt as $s )  echo "<LI>$s</LI>";
            echo "</UL>";
        }
        echo "</TD></TR></TABLE>";

        echo "</DIV>";
    } else if( count($raAcc) > 1 ) {
        foreach( $raAcc as $k => $raCV ) {
            echo "<P style='margin-left:5em;'><A HREF='{$_SERVER['PHP_SELF']}?psp=".urlencode($raCV[0]['psp'])."&pcv=".urlencode($raCV[0]['pname'])."'> {$raCV[0]['psp']} : {$raCV[0]['pname']} </A></P>";
        }
    }
}

?>
