<?php

// DrawSeedsControl should have a button that takes you to the current grower's edit (in Show Growers Seeds mode)

// New Seed Offer is appearing on the Seeds-Search screen, which has no active grower

// When you unskip a previously skipped variety, the year is not updated to current year

// Indicate on non-report grower list whether phone/email is unlisted.  You currently have to click edit to find out.

// Edit Grower:
//      Should look like the form
// Edit Seeds:
//      Should look like the form
// Delete/Skip grower should delete/skip all seeds too
//      Should undelete/unskip undelete/unskip all seeds?
// Bug: In GrowerSeed mode, with Hide Detail, the grower codes appear beside the NEXT variety on each row.

// The "fixes" at top of soft integ check should go in the seditUpdate and geditUpdate.  e.g. force upper case, trim names, set current year.
// Leave them in the integ check too, so the whole database gets "fixed" now and then.



/* Seed Directory Admin interface
 *
 * Copyright 2005-2018 Seeds of Diversity Canada
 */

define("SITEROOT", "../../");
include_once( SITEROOT."site2.php" );
include_once( "_sed.php" );
include_once( SEEDCOMMON."console/console01.php" );

/* kfdb  is seeds  (sed_seeds, sed_growers are here for HPD; sed_curr_seeds, sed_curr_growers are here for member update)
 * kfdb2 is seeds2
 */
list($kfdb2, $sess, $dummyLang) = SiteStartSessionAccount( ["W sedadmin"] );
$kfdb = SiteKFDB( SiteKFDB_DB_seeds1 ) or die( "Cannot connect to database" );

$kfdb->SetDebug(1);
$kfdb2->SetDebug(1);


//TODO since this is not persistent in stepper UI, make it a persistent control.
// OR not because it's scary to make it a changeable but persistent parameter that could be the "wrong" value in a SEEDSessionVar
// It's easier to uncomment the manual override below
if( !($CurrentYear = SEEDSafeGPC_GetInt("CurrentYear")) ) {
    $CurrentYear = date( "Y", time() + (3600*24*120) );      // the year of 120 days hence
}
//$CurrentYear=2014;

//var_dump($_REQUEST);
//echo "<BR/><BR/>";
//var_dump($_SESSION);



class SEDOfficeGrower extends Console01_Worker1
{
    function __construct( Console01 $oC, KeyFrameDB $kfdb, SEEDSessionAccount $sess )
    {
        parent::__construct( $oC, $kfdb, $sess );
    }

    function DrawGrowerControl()
    {
        $s = "";

        return( $s );
    }

    function DrawGrowerContent()
    {
        global $sed;

        $s = "";
        $s .= $sed->DrawGrowers();

        return( $s );
    }
}

class SEDOfficeSeeds extends SEDSeedsWorker
{
    private $p_seedCat = "";
    private $p_seedType = "";

    function __construct( Console01 $oC, KeyFrameDB $kfdb, SEEDSessionAccount $sess )
    {
        parent::__construct( $oC, $kfdb, $sess );

        /* Category screen uses links seedCat and seedType.  This picks up those parms in $_REQUEST and stores them in the console's SVA.
         */
        $this->p_seedCat = $this->oC->oSVA->SmartGPC('seedCat');
        $this->p_seedType = $this->oC->oSVA->SmartGPC('seedType');
    }

    private function searchControlConfig()
    {
/* TODO: Some of this is parameterized in KFUIComponent:SearchTool, which uses SEEDForm:SearchControl
 *       Move that logic into SEEDForm:SearchControl, and simplify this code
 */
        $raT = array( 'Species'=>'type',
                      'Cultivar'=>'variety',
                      'Botanical name'=>'bot_name',
                      'Origin'=>'origin',
                      'Description'=>'description');
        $raSearchControlConfig =
                array( 'filters' => array( $raT, $raT, $raT ),
                       'template' => "<STYLE>#sedSeedSearch,#sedSeedSearch input,#sedSeedSearch select { font-size:9pt;}"
                                    ."</STYLE>"
                                    ."<DIV id='sedSeedSearch'>"
                                    ."<DIV style='width:4ex;display:inline-block;'>&nbsp;</DIV>[[fields1]] [[op1]] [[text1]]<BR/>"
                                    ."<DIV style='width:4ex;display:inline-block;'>and&nbsp;</DIV>[[fields2]] [[op2]] [[text2]]<BR/>"
                                    ."<DIV style='width:4ex;display:inline-block;'>and&nbsp;</DIV>[[fields3]] [[op3]] [[text3]]<BR/>"
                                    ."</DIV>" );

        return( $raSearchControlConfig );
    }

    function DrawSeedsControl()
    {
        $oSed = $this->oC->oSed;

        $s = "<form method='post' action='${_SERVER['PHP_SELF']}'>"
            ."<table border='0' cellpadding='10' style='border:1px solid gray;background-color:#eee;'><TR valign='top'>"
            ."<td style='font-family:verdana,helvetica,sans serif;font-size:10pt;color:green;'>"
            .$this->oSNavForm->Radio( 'mode', "Show Categories", '', array('attrs'=>'onchange="submit();"')  )."<br/>"
            .$this->oSNavForm->Radio( 'mode', "Show by Grower", 'grower', array('attrs'=>'onchange="submit();"')  )."<br/>"
            .$this->oSNavForm->Radio( 'mode', "Search", 'search', array('attrs'=>'onchange="submit();"') )
            ."</td><td>";

        $mode = $this->oSNavForm->Value('mode');
$oSed->klugeSeedsMode = $mode;
        switch( $mode ) {
            case 'grower':
                // draw the Grower select control

                // In this mode, the current grower is chosen by the form, but that can be overridden by
                // the url parms sfSp_mode=grower&sfSp_kGrower={k}
                // The Grower screen does this to "Edit this grower's seeds"
                //

                //if( ($kGrower = SEEDSafeGPC_GetInt('seedKGrower')) ) {
                //    $this->oSNavForm->oDS->SetValue('kGrower', $kGrower);
                // }


                // This is how SedCommon knows which grower is being edited.
// Problem: if the Office Editor gets to a list with multiple growers via Search or Categories there is no "active grower" for that screen.
//          this would be a problem for Add Seed, but not for Edit Seed
                $oSed->kGrowerActive = intval($this->oSNavForm->Value('kGrower'));
                $oSed->oNavSVA->VarSet( 'seedKGrower', intval($this->oSNavForm->Value('kGrower')) );

                $ra = array( 0 => "--- Choose Grower ---" );
                if( ($kfrGxM = $oSed->kfrelGxM->CreateRecordCursor( "G.mbr_id=M._key", array("sSortCol"=>"country,mbr_code") )) ) {
                    while( $kfrGxM->CursorFetch() ) {
                        $ra[$kfrGxM->value('mbr_id')] = $kfrGxM->Expand( "[[mbr_code]] [[M_firstname]] [[M_lastname]] ([[mbr_id]])" );
                    }
                }
                $s .= $this->oSNavForm->Select( 'kGrower', "", $ra, array('attrs'=>"onChange='submit();'") );
                break;

            case 'search':
                $s .= $this->oSNavForm->SearchControl( $this->searchControlConfig() )
                     ."</td><td><input type='submit' value='Search'/>";
                break;

            default:
                if( $this->p_seedCat ) {
                    $s .= SEEDForm_Hidden( 'seedCat', '' )   // don't use oSNavForm because we propagate the parms without sfSp_*, just like the links in the categories screen
                         .SEEDForm_Hidden( 'seedType', '' )
                         ."<input type='submit' value='Back to all categories'/>";
                }
                break;
        }

        $s .= "</td></tr></table></form>";

        return( $s );
    }

    function DrawSeedsContent()
    {
        $s = "";

        global $sed;

        $oSed = $this->oC->oSed;

        $oKForm = $this->NewSeedForm();    // form with our DSPreStore

        $oKForm->Update();

        $this->oC->oSed->oConsoleTable = new SedSeedConsole01Table( $oSed, $oKForm );


        $raDrawParms = array( 'bEdit' => true,
                              'bAllowDelete' => false,
                              'sLabelNew' => $oSed->S('Add New Seed') );

        // Kluge: if a new row was added by oKForm->Update, point the SEEDFormUI at it.
        //        There should be some integration somewhere that makes this happen, or least makes it easier.
        if( $oKForm->GetKey() ) { //&& $oKForm->GetKey() != $oTable->Get_kCurr() ) {
            $raDrawParms['kCurr'] = $oKForm->GetKey();
        }

// kluge: get these parms to the oConsoleTable->DrawTableKFRCursor call
$this->oC->oSed->oConsoleTableDrawParms = $raDrawParms;


        $bDefault = false;
        switch( $this->oSNavForm->Value('mode') ) {
            case "grower":
                $kGrower = intval($this->oSNavForm->oDS->Value('kGrower'));
                if( $kGrower &&
                    ($kfrGxM = $oSed->kfrelGxM->GetRecordFromDB( "G.mbr_id='$kGrower' AND G.mbr_id=M._key" )) )
                {
                    $s .= $kfrGxM->Expand( "<h2>Seeds Listed by [[M_firstname]] [[M_lastname]] [[M_company]] : [[mbr_code]] ($kGrower)</h2>" )
                         .$sed->drawSeedsByGrower( $kGrower );
                }
                break;

            case "search":
                if( ($sCond = $this->oSNavForm->SearchControlDBCond( $this->searchControlConfig() )) ) {
                    $s .= $sed->drawSeedSearch( "", $sCond );
                }
                break;

            default:
                if( $this->p_seedCat ) {
                    if( $this->p_seedType ) {
                        $s .= $sed->drawTypeContent( $this->p_seedCat, $this->p_seedType );
                    } else {
                        $s .= $sed->drawCategoryContent( $this->p_seedCat );
                    }
                } else {
                    /* Show the list of categories and types that are available
                     */
                    $s .= $sed->drawCategories();
                }
        }

        return( $s );
    }
}



function obsolete_SED_Seeds()
{
    global $sed;

    $s = "";

    // Parms for each mode are persistent $sess across modes, but each is ignored when mode not active
    //     "":          show the categories/types
    //     seedKGrower: show the seed listings for one grower
    //     seedCat:     show the seed listings for the given category
    //     seedType:    show the seed listings for the given type (seedCat must be specified too because some types occur in more than one category)

    $sSeedMode = $sed->sess->SmartGPC( 'seedMode', array("", "seedKGrower", "seedCat", "seedType", "seedSearch") );

    $raT = array('Species'=>'type',
                 'Cultivar'=>'variety',
                 'Botanical name'=>'bot_name',
                 'Origin'=>'origin',
                 'Description'=>'description');
/* TODO: A lot of this is parameterized in KFUIComponent:SearchTool, which uses SEEDForm:SearchControl
 *       Move that logic into SEEDForm:SearchControl, and simplify this code
 */
    $raSearchControlConfig =
        array( 'filters' => array( $raT, $raT, $raT ),
               'template' => "<STYLE>#sedSeedSearch,#sedSeedSearch input,#sedSeedSearch select { font-size:9pt;}"
                            ."</STYLE>"
                            ."<DIV id='sedSeedSearch'>"
                            ."<DIV style='width:4ex;display:inline-block;'>&nbsp;</DIV>[[fields1]] [[op1]] [[text1]]<BR/>"
                            ."<DIV style='width:4ex;display:inline-block;'>and&nbsp;</DIV>[[fields2]] [[op2]] [[text2]]<BR/>"
                            ."<DIV style='width:4ex;display:inline-block;'>and&nbsp;</DIV>[[fields3]] [[op3]] [[text3]]<BR/>"
                            ."</DIV>" );


    // submode navigation bar for Seeds
    $oSNavForm = new SEEDFormSession( $sed->sess, 'sedSNav', 'S' );
    $oSNavForm->Update();

    // The model is: SEEDSessionForms manage the state of each screen.  Links can jump to other screens using url parms that override
    //               the session vars in the forms.
    switch( $sSeedMode ) {
        case 'seedKGrower':
            // In this mode, the current grower is chosen by the form, but that can be overridden by
            // the url parms seedMode=seedKGrower&seedKGrower={k}
            // Also, the current kGrower is a key piece of info for the app.  We store it in the sedNavGlobal.  This must be
            // kept in sync or else the app will do things to other peoples' records.
            if( ($kGrower = SEEDSafeGPC_GetInt('seedKGrower')) ) {
                $oSNavForm->oDS->SetValue('kGrower', $kGrower);
            }
            $sed->oNavSVA->VarSet( 'seedKGrower', intval($oSNavForm->oDS->Value('kGrower')) );
            break;
    }


    $s .= "<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
                ."<TABLE border='0' cellpadding='10' style='border:1px solid gray;background-color:#eee;'><TR valign='top'>"
                ."<TD style='font-family:verdana,helvetica,sans serif;font-size:10pt;color:green;'>";
    foreach( array( ""            => "Show Categories",
                    "seedKGrower" => "Show by Grower",
                    "seedSearch"  => "Search" ) as $k => $label )
    {
        if( $sSeedMode == $k ) {
            $s .= "<SPAN style='font-size:12pt'>$label</SPAN>";
        } else {
            $s .= "<A style='text-decoration:none' HREF='{$_SERVER['PHP_SELF']}?seedMode=$k'>$label</A>";
        }
        $s .= SEEDStd_StrNBSP("",5);
    }
    if( $sSeedMode == 'seedKGrower' ) {
        // draw the Grower select control
        $ra = array( 0 => "--- Choose Grower ---" );
        if( ($kfrGxC = $sed->kfrelGxC->CreateRecordCursor( "G.mbr_id=M._key", array("sSortCol"=>"country,mbr_code"))) ) {
            while( $kfrGxC->CursorFetch() ) {
                $ra[$kfrGxC->value('mbr_id')] = $kfrGxC->Expand( "[[mbr_code]] [[M_firstname]] [[M_lastname]] ([[mbr_id]])" );
            }
        }
        $s .= SEEDStd_StrNBSP("",5)
                    .$oSNavForm->Select( 'kGrower', "", $ra, array('attrs'=>"onChange='submit();'") );
    } else if( $sSeedMode == 'seedSearch') {
        // $oC->sOut .= SEEDStd_StrNBSP("",5).$oSNavForm->Text( 'seedSearch', "", array('size'=>20) );
        $s .= "</TD><TD>"
                    .$oSNavForm->SearchControl( $raSearchControlConfig )
                    ."</TD><TD><INPUT type='submit' value='Search'/><BR/><INPUT type='reset'/>";
    }
    $s .= "</TD></TR></TABLE></FORM>";


    $bDefault = false;
    switch( $sSeedMode ) {
        case "seedKGrower":
            $kGrower = intval($oSNavForm->oDS->Value('kGrower'));
            if( $kGrower &&
                ($kfrGxC = $sed->kfrelGxC->GetRecordFromDB( "G.mbr_id='$kGrower' AND G.mbr_id=M._key" )) )
            {
                $s .= $kfrGxC->Expand( "<H2>Seeds Listed by [[M_firstname]] [[M_lastname]] [[M_company]] : [[mbr_code]] ($kGrower)</H2>" )
                            .$sed->drawSeedsByGrower( $kGrower );
            }
            break;
        case "seedSearch":
            if( ($sCond = $oSNavForm->SearchControlDBCond( $raSearchControlConfig )) ) {
                $s .= $sed->drawSeedSearch( "", $sCond );
            }
            break;
        case "seedCat":
            if( ($s1 = $sed->sess->SmartGPC('seedCat')) ) {
                $s .= $sed->drawCategoryContent( $s1 );
            } else {
                $bDefault = true;
            }
            break;
        case "seedType":
            if( ($s1 = $sed->sess->SmartGPC('seedType')) ) {
            	$seedCat = $sed->sess->SmartGPC('seedCat');
    		    $s .= $sed->drawTypeContent( $seedCat, $s1 );
            } else {
                $bDefault = true;
            }
            break;
        default:
            $bDefault = true;
            break;
    }

    if( $bDefault ) {
        /* Show the list of categories and types that are available
         */
        $s .= $sed->drawCategories();
    }
    return( $s );
}


class MyConsole extends Console01
{
    public  $oW;
    public  $oSed;

    function __construct( $oSed, $raParms ) { $this->oSed = $oSed; parent::__construct( $oSed->kfdb, $oSed->sess, $raParms ); }

    function TabSetInit( $tsid, $tabname )
    {
        switch( $tabname ) {
            case 'Growers':  $this->oW = new SEDOfficeGrower( $this, $this->kfdb, $this->sess );  break;
            case 'Seeds':    $this->oW = new SEDOfficeSeeds( $this, $this->kfdb, $this->sess );  break;
        }
    }

    function TabSetPermission( $tsid, $tabname )
    {
        $eRet = Console01::TABSET_PERM_HIDE;

        $perms = array( "Growers" => "W",
                        "Seeds"   => "W",
                        "Edit"    => "W",
                        "Reports" => "R",
                        "Admin"   => "A" );

        if( $tsid == 'main' && isset($perms[$tabname]) ) {
            if( $this->sess->TestPerm( 'sedadmin', $perms[$tabname] ) )  $eRet = Console01::TABSET_PERM_SHOW;
        }
        return( $eRet );
    }

    function TabSetControlDraw( $tsid, $tabname )
    {
        switch( $tabname ) {
            case 'Growers':  return( $this->oW->DrawGrowerControl() );
            case 'Seeds':    return( $this->oW->DrawSeedsControl() );
        }
        return( "" );
    }

    function TabSetContentDraw( $tsid, $tabname )
    {
        $s = "";

        switch( $tabname ) {
            case 'Growers':  return( $this->oW->DrawGrowerContent() );
            case 'Seeds':    return( $this->oW->DrawSeedsContent() );
            case 'Edit':     $s = SED_Edit();       break;
            case 'Reports':  $s = SED_Reports();    break;
            case 'Admin':    $s = SED_Admin();      break;
        }

        return( $s );
    }
}


$raConsoleParms = array(
    'HEADER' => "Seed Directory",
    'CONSOLE_NAME' => "SEDAdmin",

    'TABSETS' => array( "main" => array( 'tabs' => array( 'Growers' => array( 'label' => "Growers" ),
                                                          'Seeds'   => array( 'label' => "Seeds" ),
                                                          'Edit'    => array( 'label' => "Edit" ),
                                                          'Reports' => array( 'label' => "Reports" ),
                                                          'Admin'   => array( 'label' => "Admin" ) ) ) ),
    'EnableC01Form' => true,
    'bBootstrap' => true
);

$p_doReport = SEEDSafeGPC_GetStrPlain('doReport');
$eReportMode = ($p_doReport ? "LAYOUT" : "EDIT");
$oSed = new SEDOffice( $kfdb, $kfdb2, $sess, "EN", $eReportMode );

$oC = new MyConsole( $oSed, $raConsoleParms );

$sed = new sedList( $kfdb, $kfdb2, $sess, $oC, $oSed, $CurrentYear, $eReportMode );

if( $p_doReport ) {
    // Normally, this is launched in a new window.  Output the report and exit.
    include_once( "sed_report.php" );
    $oReport = new sedReport( $sed );
    $oReport->Report();
    exit;
}


// This is the office version - disables some hacking checks
$sed->oSed->bOffice = true;
$sed->update();    // get parms and update db


header( "Content-type: text/html; charset=ISO-8859-1");    // this should be on all pages so accented chars look right (on Linux anyway)


echo $oC->DrawConsole( sed_style()
                      .$sed->oSed->SEDStyle()
                      ."[[TabSet: main]]" );







function SED_Edit()
{
    global $sed;

    $s = "";

    if( @$_REQUEST['seedEditActionReplaceType'] ) {
        $p_t1 = SEEDSafeGPC_GetStrPlain('seedEditType1');
        $p_t2 = SEEDSafeGPC_GetStrPlain('seedEditType2');
        if( !empty($p_t1) && !empty($p_t2) ) {
            $sql = "UPDATE sed_curr_seeds SET type='".addslashes($p_t2)."' WHERE type='".addslashes($p_t1)."'";
            $s .= "<P style='color:gray;font-size:10pt'>$sql</P>";
            $s .= $sed->kfdb->Execute( $sql ) ? "Successful" :("Error: ".$sed->kfdb->GetErrMsg());
        }
    }

    if( @$_REQUEST['seedEditActionReplaceVariety'] ) {
        $p_t1 = SEEDSafeGPC_GetStrPlain('seedEditType1');
        $p_v1 = SEEDSafeGPC_GetStrPlain('seedEditVariety1');
        $p_v2 = SEEDSafeGPC_GetStrPlain('seedEditVariety2');
        if( !empty($p_t1) && !empty($p_v1) && !empty($p_v2) ) {
            $sql = "UPDATE sed_curr_seeds SET variety='".addslashes($p_v2)."' WHERE type='".addslashes($p_t1)."' AND variety='".addslashes($p_v1)."'";
            $s .= "<P style='color:gray;font-size:10pt'>$sql</P>";
            $s .= $sed->kfdb->Execute( $sql ) ? "Successful" :("Error: ".$sed->kfdb->GetErrMsg());
        }
    }
    if( @$_REQUEST['seedEditActionMoveVariety'] ) {
        $p_v1 = SEEDSafeGPC_GetStrPlain('seedEditVariety1');
        $p_t1 = SEEDSafeGPC_GetStrPlain('seedEditType1');
        $p_t2 = SEEDSafeGPC_GetStrPlain('seedEditType2');
        if( !empty($p_v1) && !empty($p_t1) && !empty($p_t2) ) {
            $sql = "UPDATE sed_curr_seeds SET type='".addslashes($p_t2)."' WHERE type='".addslashes($p_t1)."' AND variety='".addslashes($p_v1)."'";
            $s .= "<P style='color:gray;font-size:10pt'>$sql</P>";
            $s .= $sed->kfdb->Execute( $sql ) ? "Successful" :("Error: ".$sed->kfdb->GetErrMsg());
        }
    }


    $s .= "<P>These operations can have consequences that are not reversible. An error could cause <U>a lot</U> of problems. "
                ."If you don't know exactly what you're doing, don't use these. If you do know exactly what you're doing, think twice before doing anything anyway.</P>"
                ."<P>&nbsp;</P>";

    if( ($kfrS = $sed->oSed->kfrelS->CreateRecordCursor( "", array('sGroupCol'=>'type','sSortCol'=>'type') )) ) {
        $raTypes = array();
        while( $kfrS->CursorFetch() ) {
            $raTypes[$kfrS->value('type')] = $kfrS->value('type');
        }
        $s .= "<FORM action='${_SERVER['PHP_SELF']}'><P>Replace Types".SEEDStd_StrNBSP("",5)
                    .SEEDForm_Select( 'seedEditType1', $raTypes ).SEEDStd_StrNBSP("",5)." with ".SEEDStd_StrNBSP("",5)
                    .SEEDForm_Text( 'seedEditType2', "", "", 20 ).SEEDStd_StrNBSP("",5)
                    ."<INPUT type='submit' name='seedEditActionReplaceType' value='Replace Types'/></P></FORM>";

$s .= "<P>Would be great to have a Replace Types [list] with [list]</P>";

        $s .= "<FORM action='${_SERVER['PHP_SELF']}'><P>Replace Varieties".SEEDStd_StrNBSP("",5)
                    .SEEDForm_Select( 'seedEditType1', $raTypes ).SEEDStd_StrNBSP("",5)
                    .SEEDForm_Text( 'seedEditVariety1', "", "", 20 ).SEEDStd_StrNBSP("",5)
                    ." with ".SEEDStd_StrNBSP("",5)
                    .SEEDForm_Text( 'seedEditVariety2', "", "", 20 ).SEEDStd_StrNBSP("",5)
                    ."<INPUT type='submit' name='seedEditActionReplaceVariety' value='Replace Varieties'/></P></FORM>";
        $s .= "<FORM action='${_SERVER['PHP_SELF']}'><P>Move Variety".SEEDStd_StrNBSP("",5)
                    .SEEDForm_Text( 'seedEditVariety1', "", "", 20 ).SEEDStd_StrNBSP("",5)
                    ." from type "
                    .SEEDForm_Select( 'seedEditType1', $raTypes ).SEEDStd_StrNBSP("",5)
                    ." to type ".SEEDStd_StrNBSP("",5)
                    .SEEDForm_Select( 'seedEditType2', $raTypes ).SEEDStd_StrNBSP("",5)
                    ."<INPUT type='submit' name='seedEditActionMoveVariety' value='Move Varieties'/></P></FORM>";
    }
    return( $s );
}

function SED_Reports()
{
    global $sed;

    $s = "";

	$s .= "<P>Does the pdf of the Aug 2009 package listings have page numbers at the bottom of the pages? "
    ."They did not appear on the paper, which is either an oversight in the Firefox page setup, or "
    ."a print-margin issue.</P>";

    $s .= "<P>".$sed->sess->GetName()." is logged in.</P>"
         ."<DIV>";

    $cond = "_status=0 and not bSkip and not bDelete";
    $s .= $sed->kfdb->Query1( "SELECT count(*) FROM sed_curr_growers where $cond" )." Growers<BR>"
                .$sed->kfdb->Query1( "SELECT count(*) FROM sed_curr_seeds   where $cond" )." Seed Listings<BR>"
                .$sed->kfdb->Query1( "SELECT count(distinct type) FROM sed_curr_seeds where $cond" )." Types<BR>"
                .$sed->kfdb->Query1( "SELECT count(distinct type,variety) FROM sed_curr_seeds where $cond" )." Varieties<BR>"
                ."</DIV>";

    $s .= "<H2>Member Seed Directory Reports</H2>"
                ."<H4>January listing</H4>"
                ."<P><A HREF='{$_SERVER['PHP_SELF']}?doReport=jan_g' target='sed_report'>Grower listing</A></P>"
                ."<P><A HREF='{$_SERVER['PHP_SELF']}?doReport=jan_s' target='sed_report'>Seeds listing</A></P>"

                ."<BR/>"
                ."<H4>August grower package</H4>"
                ."<P style='font-size:9pt'>"
                ."1) Print Grower info sheets to pdf with no header/footer. Each should fit on one page unless the Notes section is very long.<BR/>"
                ."2) Print Seed listings to pdf, with no header, and page numbers on footer.<BR/>"
                ."3) Use Mailing labels spreadsheet to print mailing labels. Should be in the same order as the others.</P>"
                ."<P><A HREF='{$_SERVER['PHP_SELF']}?doReport=aug_g' target='sed_report_aug_g'>Grower info sheets - print to pdf - sorted by country / grower code</A></P>"
                ."<P><A HREF='{$_SERVER['PHP_SELF']}?doReport=aug_s' target='sed_report_aug_s'>Seed listings per grower - print to pdf - sorted by country / grower code</A></P>"
                ."<P><A HREF='{$_SERVER['PHP_SELF']}?doReport=aug_gxls' target='sed_report'>Mailing labels - Growers & grower data in spreadsheet format, sorted by country / grower code</A></P>";

    return( $s );
}

function SED_Admin()
{
    global $sed;

    $s = "";

    if( !$sed->sess->CanAdmin('sedadmin') )  return( "" );

    include_once( "sed_admin.php" );

    $oAdmin = new sedAdmin( $sed );
    return( $oAdmin->Main() );
}

?>
