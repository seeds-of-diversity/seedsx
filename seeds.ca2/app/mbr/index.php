<?php
/*
 * Seed Directory member interface
 *
 * Copyright (c) 2011-2016 Seeds of Diversity Canada
 *
 * Show the listings in the Member Seed Directory
 */
header( "Location: /app/seedexchange" );


die( "Coming soon" );

define( "SITEROOT", "../../" );
include( SITEROOT."site.php" );
include_once( SEEDCOMMON."console/console01.php" );
include_once( SEEDCOMMON."sl/sed/sedCommon.php" );
include_once( SEEDCOMMON."sitePipeCommon.php" );

// Don't ask to login here, and allow the page to be viewed if no login
list($kfdb, $sess, $lang) = SiteStartSessionAccountNoUI( array("sed" => "R") );

//$kfdb->SetDebug(2);
//var_dump($_REQUEST);
//var_dump($_SESSION);

// This is now MSDView so use that instead
class SEDView extends SEDCommon
/******************************
    Derivation for the read-only public and members view of the Seed Directory
 */
{
    function __construct( KeyFrameDB $kfdb, SEEDSessionAccount $sess, $lang )
    {
        parent::__construct( $kfdb, $sess, $lang, $sess->CanRead("sed") ? 'VIEW-MBR' : 'VIEW-PUB' );
    }

    protected function GetMbrContactsRA( $kMbr )   // SEDCommon::drawGrowerBlock calls back here to get the MbrContacts array for the given member
    {
// use mbrSitePipe.php:MbrSitePipeGetContactsRA()
        $oPipe = new SitePipe( $this->kfdb );
        list( $kPipeRow, $sPipeSignature ) = $oPipe->CreatePipeRequest( array('cmd'=>'GetMbrContactsRA', 'kMbr'=>$kMbr) );

        list( $bOk, $hdr, $resp ) = $oPipe->SendPipeRequest( array( "kPipeRow"=>$kPipeRow, "sPipeSignature"=>$sPipeSignature ) );

        if( $bOk ) {
// remote server should indicate success of its processing, because it always sends a 200 http response
            $ra = $oPipe->GetAndDeletePipeResponse( $kPipeRow );
        }
        return( $ra );
    }
}


/* Normally, we create the console first and the logic object second (or inside the console).
 * Here is a case where the console parms need a SEEDLocal object created in the logic object. To solve this catch-22, the HEADER is set later.
 * This is probably still the right order to do things, since the logic object wants oC to draw some things like DrawInABox, and conceivably to
 * set C01Form parameters correctly (if that were encapsulated in oC).
 */
$oC = new Console01( $kfdb, $sess,
                     array( 'HEADER' => "",               // replaced below
                            'CONSOLE_NAME' => "SEDMbr",
                            'HEADER_LINKS' => array(),    // replaced below
                            'EnableC01Form' => true,
                            'lang' => $lang,
                            'bLogo' => true,
                            'bBootstrap' => true ) );

$oMySed = new MySED( $kfdb, $sess, $oC, $lang );



header( "Content-type: text/html; charset=ISO-8859-1");    // this should be on all pages so accented chars look right (on Linux anyway)


/* Get C01Form parms, manage their persistence, and establish the current state of the SEDViewer
 */
$pCat = $oC->oSVA->VarGet( 'pCat' );
$pType = $oC->oSVA->VarGet( 'pType' );
$kSeed = $oC->oSVA->VarGet( 'kSeed' );
$raState = $oC->oSVA->VarGetAllRA( 'bCat_' );  // get all SVA vars bCat_*
$bSrchReset = @$_REQUEST['bSrchReset'];

switch( SEEDSafeGPC_GetStrPlain('c01FormAction') ) {
    case 'SetType':
        // Remember that there are duplicate pTypes under different categories, so (pCat,pType) is a unique tuple
        if( ($p_Cat  = SEEDSafeGPC_GetStrPlain('c01FormArg1')) &&
            ($p_Type = SEEDSafeGPC_GetStrPlain('c01FormArg2')) )   // MySed checks that pType is in pCat
        {
            if( $p_Cat != $pCat || $p_Type != $pType )  $kSeed = 0;

            $pCat = $p_Cat;
            $pType = $p_Type;

            // Reset the search form when someone clicks on a type
            $bSrchReset = true;
        }
        break;
    case 'ToggleCat':
        if( ($pCatToggle = SEEDSafeGPC_GetStrPlain('c01FormArg1')) ) {
            $sVar = "bCat_$pCatToggle";
            $raState[$sVar] = !intval(@$raState[$sVar]);
            $pType = "";
        }
        break;
    case 'ClickSeed':
        $kSeed = SEEDSafeGPC_GetInt('c01FormArg1');
        break;
}


$oMySed->Init( $pCat, $pType, $kSeed, $raState, $bSrchReset );

// Store parms as normalized by Init
$oC->oSVA->VarSet( 'pCat',  $oMySed->pCat );
$oC->oSVA->VarSet( 'pType', $oMySed->pType );
$oC->oSVA->VarSet( 'kSeed', $oMySed->kSeed );
foreach( $oMySed->raState as $k => $v ) {
    $oC->oSVA->VarSet( $k, $v );
}


$s = "";
//$s .= "<div class='alert alert-warning'>The 2016 Member Seed Directory will be ready in January. We'll contact members soon. Happy Holidays!</div>";

// Now oMySed has a normalized state, so draw the viewer
$s .= "<BR/><TABLE cellpadding='5' cellspacing='0' border='0' width='100%'><TR valign='top'>"
    ."<TD style='padding-right:3em'>"
    .$oMySed->drawLeft()
    ."</TD>"
    ."<TD valign='top'>"
    .$oMySed->drawSeedList()
    ."</TD>"
    ."</TR></TABLE>";


$raConfig = array( 'HEADER' => $oMySed->oSed->S("MainHeading") );
if( !$oMySed->OnHomeScreen() ) {
    $raConfig['HEADER_LINKS'][] = array( 'label'=>'Instructions', 'href'=>$_SERVER['PHP_SELF']."?h=instructions" );
}
if( $sess->IsLogin() ) {
    $raConfig['HEADER_LINKS'][] = array( 'label'=>$oMySed->oSed->S('Seed Request Form'), 'href'=>'./SeedRequestForm2016.pdf', 'target'=>'_blank' );
} else {
    $raConfig['bLogin'] = false;
}

$oC->SetConfig( $raConfig );
echo $oC->DrawConsole( $s, false );



class MySED
{
    public $oSed;
    private $oC;  // DrawInABox()

    // Viewer state set by Init()
    public $pCat = "";    // pCat is tied to pType: it is never set unless pType is also set : (pCat,pType) is an indivisible tuple
    public $pType = "";   //     because there are duplicate types in multiple categories
    public $kSeed = 0;
    public $raState = array();

    private $oSearch;

    function MySED( KeyFrameDB $kfdb, SEEDSessionAccount $sess, Console01 $oC, $lang )
    {
        $this->oSed = new SEDView( $kfdb, $sess, $lang );
        $this->oC = $oC;

        $this->oSearch = new SEDSearchControl( $this->oSed->sess );
    }

    function Init( $pCat, $pType, $kSeed, $raState, $bSrchReset )
    {
        // Instructions link resets the Type navigation
        if( SEEDSafeGPC_GetStrPlain('h') == 'instructions' ) {
            $pType = "";
            $kSeed = 0;
            $bSrchReset = true;
        }

        // Various actions reset the UI so the search control should be cleared
        if( $bSrchReset ) {
            $this->oSearch->Clear();
        }


        $this->pCat = $pCat;
        $this->pType = $pType;
        $this->kSeed = $kSeed;
        $this->raState = $raState;

        $bOk = false;
        if( $kSeed ) {
            /* A seed is selected - validate everything
             */
            $kfrS = $this->oSed->GetKfrS_Key( $kSeed );  // oSed should be in VIEW mode
            if( $kfrS && $kfrS->Key() ) {
                // This is a valid seed item that is not skipped or deleted.
                // Force the category and type parms to conform, in case they don't for some reason.
                $this->pCat  = $this->oSed->CategoryDB2K($kfrS->Value('category'));
                $this->pType = $kfrS->Value('type');
                $bOk = true;
            }
        } else {
            /* A seed is not selected - validate the browsing.
             *
             * You can't force category from type, because there are duplicate types with different categories.
             * But you can validate that pCat is a real category and that pType is in pCat.
             * pCat and pType are only valid if both set or both blank.
             */
            if( $this->pCat && $this->pType ) {
                // Test that pCat is a standardized category (hereby assuming that the office has normalized all seeds to known categories)
                if( isset($this->oSed->raCategories[$this->pCat]) ) {
                    // Test that there is at least one seed with pCat/pType
                    if( ($kfrc = $this->oSed->GetKfrcS_CatType( $this->pCat, $this->pType )) ) {
                        $kfrc->CursorFetch();
                        if( $kfrc->Key() )  $bOk = true;
                    }
                }
            }
        }
        if( !$bOk )  $this->Reset();
    }

    function Reset()
    /***************
        Doesn't reset $raState because that contains UI navigational state like which categories are expanded
     */
    {
        $this->pCat = "";
        $this->pType = "";
        $this->kSeed = 0;  // redundant, already ==0
    }

    function OnHomeScreen()
    {
        $sSearchCond = $this->oSearch->GetDBCond();
        return( (!$this->pCat || !$this->pType) && !$sSearchCond );
    }

    function drawLeft()
    {
        $sFontFamily = "";//font-family:arial,helvetica,sans serif;";
        $s = $this->oSed->SEDStyle()
            ."<STYLE>"
            .".sedTypelist h4 { font-size:12pt; cursor:pointer; display:inline; $sFontFamily }"
            .".sedTypelist .sedTypename { margin-left:2em;font-size:10pt; color:blue;cursor:pointer; $sFontFamily }"
            .".sed_type { text-decoration:underline; }"
            .".sed_chosenseed { font-size:10pt; padding:0 1em;background-color:#eee; $sFontFamily }"
            .".sed_instructions2 { margin:0px 1em 0px 3em;padding:15px; font-size:10pt; background-color:#eee; $sFontFamily}"
            .".sed_instructions, .sed_instructions td, .sed_instructions th { font-size:12pt; $sFontFamily }"
            ."</STYLE>"
            ."<DIV class='sedTypelist well-lg' style='background-color:#d2eeb8'>"
            .$this->drawCat("flowers")
            .$this->drawCat("vegetables")
            .$this->drawCat("fruit")
            .$this->drawCat("herbs")
            .$this->drawCat("grain")
            .$this->drawCat("trees")
            .$this->drawCat("misc")
            ."</DIV>";
        //$s = $this->oC->DrawInABox( $s );

        return( $s );
    }

    function drawCat( $cat )
    {
        $bExpanded = intval(@$this->raState["bCat_$cat"]);
        $sOnClick = "onclick='console01FormSubmit( \"ToggleCat\", \"$cat\" );'";
        $s = "<IMG src='".W_ROOT_STD."img/triangle_black_".($bExpanded ? 'down.png' : 'right.png')."' border='0' style='margin-right:10px' "
             ."$sOnClick "
             ."style='cursor:pointer'"
             ."/>"
             ."<H style='font-size:12pt;font-weight:bold;cursor:pointer' $sOnClick >".$this->oSed->raCategories[$cat][$this->oC->lang]."</H>"
             ."<BR/><BR/>" // break the h4's inline style
             .($bExpanded ? ("<DIV style='margin-bottom:1em'>".$this->oSed->DrawTypes( $cat, true, false )."</DIV>") : "");
        return( $s );
    }

    function drawSeedList()
    {
        $s = "";

        $sSearchCond = $this->oSearch->GetDBCond();
        //var_dump($sSearchCond);

        $s .= "<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
             ."<TABLE border='0' cellpadding='10' style='border:1px solid gray;background-color:#eee;'><TR valign='top'>"
             ."<TD style='font-family:verdana,helvetica,sans serif;font-size:10pt;color:green;'>"
             .$this->oSearch->DrawSearchControl()
             ."</TD><TD>"
             ."<INPUT type='submit' value='Search'/><BR/><INPUT type='reset' onclick='document.getElementById(\"bSrchReset\").value=1;submit();'/>"
             ."<INPUT type='hidden' id='bSrchReset' name='bSrchReset' value='0'/>"
             ."</TD></TR></TABLE>"
             ."</FORM>";

        // if the user didn't click the categories list and didn't send a search request, just show the initial instructions
        if( $this->OnHomeScreen() ) {
            $s .= $this->drawInstructionsMain();
            return( $s );
        }

        $s .= "<TABLE cellpadding='0' cellspacing='0' border='0' width='100%'>";
//        $s .= "<H3>".$this->oSed->raCategories[$pCat]['EN']." : $pType</H3>";

        $raKfParms = array("sSortCol"=>"category,type,variety");  // need to sort by cat,type too for search results
        $bFirst = true;
        if( ($kfrS = $sSearchCond ? $this->oSed->GetKfrcS( $sSearchCond, $raKfParms )    // oSed should be in VIEW mode
                                  : $this->oSed->GetKfrcS_CatType( $this->pCat, $this->pType, "", $raKfParms )) ) {
            while( $kfrS->CursorFetch() ) {
                $s .= "<TR valign='top'><TD width='70%'>"
                     ."<div style='margin-bottom:0.8em'>";

                $b = false;
                if( $this->kSeed && $kfrS->Key() == $this->kSeed ) {
                    $s .= "<DIV style='background-color:#eee'>";
                    $b = true;
                }

                $raSeedParms = array();
                $s .= $this->oSed->DrawSeedFromKFR( $kfrS, $raSeedParms );
                if( $b ) $s .= "</DIV>";

                $s .= "</div></TD>";

                if( $this->kSeed ) {
                    if( $kfrS->Key() == $this->kSeed ) {
                        $s .= "<TD rowspan='10'>";
                        $kG = $kfrS->value('mbr_id');
                        $kfrG = $this->oSed->kfrelG->GetRecordFromDB( "mbr_id='$kG'" );
                        $s .= "<DIV class='sed_chosenseed' style='padding-top:10px'>"
                             ."<form method='post'>"
                             .$this->oSearch->DrawHidden( 'mbr_id', 'eq', $kG )
                             ."<input type='submit' value='See all seeds offered by this grower'/>"
                             ."</form>"
                             .($kfrG ? $this->oSed->drawGrowerBlock( $kfrG ) : "Grower #$kG")
                             ."<BR/>"
                             ."<BR/>".$this->drawInstructionsList()
                             ."</DIV>";
                    } else {
                        $s .= "<TD>";
                    }
                } else if( $bFirst ) {
                    if( $this->oSed->bLogin ) {
                        $s1 = $this->drawInstructionsList();
                    } else {
                        $s1 = $this->drawNoLoginInstructions();
                    }
                    $s .= "<TD rowspan='10'><div class='sed_instructions2'>".$s1."</div>";
                } else {
                    $s .= "<TD>";
                }
                $bFirst = false;
                $s .= "&nbsp;</TD></TR>";
            }
        }
        $s .= "</TABLE>";
        return( $s."&nbsp;" );
    }

    function drawInstructionsMain()
    {
        $sNoSkipDel = "_status='0' AND NOT bSkip AND NOT bDelete";
        $nGrowers = $this->oSed->kfdb->Query1( "SELECT count(*) FROM sed_curr_growers WHERE $sNoSkipDel" );
        $nVarieties = $this->oSed->kfdb->Query1( "SELECT count(*) FROM "
                                                ."(SELECT * FROM sed_curr_seeds WHERE $sNoSkipDel GROUP BY category,type,variety) g" );

        if( $this->oSed->bLogin ) {
            $sMiddle = $this->oSed->oL->S('InstructionList');
        } else {
            $sMiddle = "<div class='sed_instructions2'>".$this->drawNoLoginInstructions()."</div>";
        }
        $s = $this->oSed->oL->S2('[[InstructionsFrontPage]]', array('nMbr'=>$nGrowers, 'nVar'=>$nVarieties, 'sMiddle'=>$sMiddle) );

        return( $s );

        return( "<div class='sed_instructions'>"

//       ."<div style='border:2px solid green;background-color:#e8e8e8;padding:10px;float:right; font-size:10pt;width:30%'>"
//       ."<H4>This is the first edition of Seeds of Diversity's On-line Member Seed Directory</H4>"
//       ."<p>We wanted to get this out there as soon as possible, so here's what we're still working on:</p>"
//       ."<ul>"
//       ."<li>a full French-language version</li>"
//       ."<li>search by keyword, grower, days to maturity, etc</li>"
//       ."<li>auto-fill Seed Request Form (this is a computer, so why are you filling in a form by hand?)"
//       ."</ul>"
//       ."<p>Let us know what improvements you'd like to see. Tell us how we're doing!</p>"
//       ."</div>"

       ."<H3>Welcome to Your Member Seed Directory!</H3>"
       ."<P>121 Seeds of Diversity members are offering 3489 different kinds of seeds to other members.</P>"
       ."<P>Click on the categories on the left to start.</P>"
       .$this->drawInstructionsList()
       ."<TABLE width='80%' border='1' cellpadding='5' cellspacing='5'>"
       ."<TR valign='top'><TH colspan='3'><B>Prices</B><BR/><SPAN style='font-size:8pt'>Members who offer seeds receive a discount on seed requests</SPAN></TH></TR>"
       ."<TR valign='top'><TH>&nbsp;</TH><TH>If you are a regular member</TH><TH>If you are a grower member (offering seeds in this directory)</TH></TR>"
       ."<TR valign='top'><TD>Small seeds e.g. tomatoes, peppers, lettuce, etc</TD><TD>$2.50</TD><TD>$2.00</TD></TR>"
       ."<TR valign='top'><TD>Large seeds e.g. beans, peas, corn, squash, grains, etc</TD><TD>$3.00</TD><TD>$2.00</TD></TR>"
       ."<TR valign='bottom'><TD>Roots and cuttings e.g. potatoes, garlic, onions, Jerusalem artichokes, etc<BR/>In your province<BR/>Out of province</TD>"
       ."<TD>&nbsp;<BR/>$9.00<BR/>$12.00</TD><TD>&nbsp;<BR/>$8.00<BR/>$11.00</TD></TR>"
       ."</TABLE>"
       ."<P>Some members may accept payment by cash, stamps, or \"<I>Canadian Tire</I> money\".</P>"
       ."</div>" );
    }

    function drawInstructionsList()
    {
        return( $this->oSed->oL->S('InstructionList') );
    }

    function drawNoLoginInstructions()
    {
        $s = "";

        $s .= "<h4>These seeds are offered by our members, directly to other members.</h4>"
             ."<p>Seeds of Diversity is a membership organization that runs Canada's largest seed exchange. "
             ."Every year, our grower members save seeds from their gardens and farms, and offer them here to other members.</p>"
             ."<p><b>Are you a member?</b>  Login to find out who is offering your favourites, and how to request them.</p>"
             ."<p><b>Not a member yet?</b>  Feel free to explore the list, then join Seeds of Diversity at "
             ."<a href='http://seeds.ca/member'>seeds.ca/member</a> to start requesting seeds from other members!</p>";


        /* User is not logged in.  $this->oSed->sess is a plain SEEDSession, so it can't be used to login
         */
        $sessAuth = new SEEDSessionAuth( $this->oSed->kfdb );

        $s .= "<div class='well container' style='width:80%;min-width:150px;max-width:400px;margin-bottom:10px;border-color:#F07020;background-color:#ffa;padding:10px;'>"
               ."<h4>Member Login</h4>"
               ."<form action='".Site_path_self()."' method='post' accept-charset='ISO-8859-1'>"  // use 1252 in case people have accents in passwords
               ."Email address or member #<br/><input type='text' name='".$sessAuth->httpNameUID."' value='' style='width:100%'/>"
               ."<br/>"
               ."Password<br/><input type='password' name='".$sessAuth->httpNamePWD."' value='' style='width:100%'/>"
               ."<br/><br/>"
               ."<input type='submit' value='Login'/>"
               ."<input type='hidden' name='p_nCDBodyCurrBox' value='2'/>"  // force the UI to activate this box again
             ."</form></div>"

             ."<p>&nbsp;</p>"
             ."<p>Forgot your password? "
             ."<a href='http://www.seeds.ca/login?sessioncmd=sendpwd' target='_blank'>Click here to get it back</a>"

             ."<p>Not a member? <a href='http://seeds.ca/member' target='_blank'>Join Seeds of Diversity today.</a></p>"

             ."<p>&nbsp;</p>";

        return( $s );
    }
}

?>
