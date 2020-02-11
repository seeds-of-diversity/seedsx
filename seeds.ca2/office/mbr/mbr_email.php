<?php

// Language selection should be more intuitive.  English only really means is the mail in English.
// Then French, English, and All should pick up 'B'.  We're not looking for B in mbr_contacts.  Get rid of '' default to B.

/* Mailing List Generator
 *
 * Copyright 2013-2020 Seeds of Diversity Canada
 *
 * Generates mail and email lists for various categories of members and subscribers
 */
define( "SITEROOT", "../../" );
include_once( SITEROOT."site2.php" );
include_once( SEEDLIB."mbr/QServerMbr.php" );

$oApp = SEEDConfig_NewAppConsole( ['db'=>'seeds2',
                                   'sessPermsRequired'=>['R MBR'],  // and R EBULLETIN
                                   'consoleConfig' => ['HEADER' => "Seeds of Diversity Mailing Lists"] ]
);

SEEDPRG();

$sLeft = $sRight = "";


$year = intval(date("Y"));

$bEbull    = SEEDInput_Int('gEbull');
$bSEDCurr  = SEEDInput_Int('gSEDCurr');
$bSEDCurr2 = SEEDInput_Int('gSEDCurrNotDone');


$bOverrideNoEmail  = SEEDInput_Int('override_noemail');


$oForm = new SEEDCoreForm('Plain');
$oForm->Load();

$p_lang = SEEDInput_Smart( 'eLang', ['', 'EN', 'FR'] );                     // also available as $oForm->Value('eLang')
$p_outFormat = SEEDInput_Smart( 'outFormat', ['email', 'mbrid','xls'] );
$p_mbrFilter = $oForm->Value( 'eMbrFilter' );

$yMinus1 = $year-1;
$yMinus2 = $year-2;

$sLeft .=
     "<form method='post' action='".$oApp->PathToSelf()."'>"
    ."<div class='box1'>"
    ."<p>Choose Members</p>"
    ."<div style='margin-bottom:10px'>"
    .$oForm->Select( 'yMbrExpires', ["-- No Members --" => 0,
                                     "Current Members ($year and greater)" => "$year+",
                                     "All members since $yMinus1 ($yMinus1 and greater)" => "$yMinus1+",
                                     "All members since $yMinus2 ($yMinus2 and greater)" => "$yMinus2+",
                                     "Non-current members expired in $yMinus2 or $yMinus1" => "$yMinus2,$yMinus1",
                                     "Non-current members expired in $yMinus1" => $yMinus1,
                                     "Non-current members expired in $yMinus2" => $yMinus2 ] )
    ."</div><div style='margin-bottom:10px'>"
    .$oForm->Select( 'eMbrFilter', ["-- No filter --" => 0,
                                    "Who receive the e-bulletin"             => "getEbulletin",
                                    "Who receive the magazine"               => "getMagazine",
                                    "Who receive the printed Seed Directory" => "getPrintedMSD",
                                    "Who list seeds in the Seed Directory (active or skipped)" => "msdGrowers",
                                    "Who list seeds in the Seed Directory (active or skipped) but are not Done this year" => "msdGrowersNotDone"] )
    ."</div><br/>"
    ."<p>Language</p>"
    .$oForm->Select( 'eLang', ["-- All languages --" => 0,
                               "English" => 'EN',
                               "French"  => 'FR'] )
    ."</br/><br/>"

    ."<p>"
    .SEEDForm_Checkbox( "gEbull",    $bEbull,    "e-Bulletin subscribers who signed up on the web site" )."<BR/><BR/>"
    .SEEDForm_Checkbox( "gSEDCurr",  $bSEDCurr,  "Members currently active or skipped in Member Seed Directory" )."<BR/>"
    .SEEDForm_Checkbox( "gSEDCurrNotDone", $bSEDCurr2,  "Members currently active or skipped in Member Seed Directory, and not bDone" )."<BR/>"
    ."</p>"
    ."<br/>"

    ."<p>Output Format</p>"
    .$oForm->Select( 'outFormat', ["Email addresses" => 'email',
                                   "Member numbers" => 'mbrid',
                                   "Full spreadsheet" => 'xls'] )
    ."</br/><br/>"

    ."<div style='color:gray; border:thin solid grey;padding-left:1em;'>"
    ."<p>Don't check this unless you really mean it -- e.g. for magazines and Seed Directories</p>"
    ."<p style='margin-left:30px'>"
    // don't use SEEDForm because it's actually good if the check isn't sticky
    ."<input type='checkbox' name='override_noemail' value='1'> Include members who said they <U>don't want</U> email / e-Bulletin"
    ."</p>"
    ."</div>"

    ."<BR/>"
    ."<INPUT type='submit' value='List'>"
    ."</DIV></FORM>";  // box1




/*

    echo "<P>Choose a category and click <B>List</B> to get a list of email addresses in a separate window. "
         ."Duplicate addresses are removed from the list. "
         ."You should be able to Copy + Paste the addresses into your email program.</P>";
    echo "<P><U>Remember to always use BCC when sending bulk emails.</U></P>";

    echo "<FORM action='${_SERVER['PHP_SELF']}' method=get target='_blank'>";
    echo $sess->FormHidden();
    echo "<INPUT type=hidden name=step value=1>";
    echo "<BLOCKQUOTE>";
    echo "<P><B>e-Bulletin</B> : includes $year and ".($year-1)
         ." members, minus those who told us they don't want it, plus people who signed up on the web site.</P>";
    echo "<INPUT type=radio name=cat value=ebullall> e-Bulletin recipients - All<BR><BR>";
    echo "<INPUT type=radio name=cat value=ebullen> e-Bulletin recipients - English<BR><BR>";
    echo "<INPUT type=radio name=cat value=ebullfr> e-Bulletin recipients - French<BR><BR>";

    echo "<P><B>Membership</B><P>";
    echo "<INPUT type=radio name=cat value=mbrrenew DISABLED> Renewal reminder - non-renewed members from ".($year-2)." and ".($year-1)."<BR><BR>";
//  echo "<INPUT type=radio name=e_mbr2004nr value=1> 2004 members who have not renewed<BR><BR>";

    echo "<P><B>Mix and Match</B></P>";
    echo "<INPUT type=radio name=cat value=mix> Make your own custom list using the checkboxes below<BR><BR>";
    echo "<DIV style='padding:1em; border:thin solid grey;'>";

    echo "<TABLE border='0' width='100%'><TR><TD width='40%' valign='top'>";
    echo "</TD><TD valign='top'>";
    echo "<INPUT type=checkbox name=mix_mbren value=1> English members<BR><BR>";
    echo "<INPUT type=checkbox name=mix_mbrfr value=1> French members<BR><BR>";
    echo "</TD></TR></TABLE>";

    echo "</DIV>";

*/

$raEmail = array();
$raMbrid = array();
$raMbr = [];


/* Look up mbr_contacts
 */
if( ($yMbrExpires = $oForm->Value('yMbrExpires')) && !SEEDCore_StartsWith($p_mbrFilter,'msdGrower') ) {     // msdGrower filters are handled below

    $qParms = ['yMbrExpires' => $yMbrExpires];

    if( $p_lang )                $qParms['lang'] = $p_lang;
    if( $p_outFormat=='email' )  $qParms['bExistsEmail'] = true;

    switch( $p_mbrFilter ) {
        case 'getMagazine':     $qParms['bEbulletin'] = false;               break;  // don't limit only to members who allow email
        case 'getEbulletin':    $qParms['bEbulletin'] = !$bOverrideNoEmail;          // limit to members who allow email, unless the override box is checked
                                $qParms['bExistsEmail'] = true;              break;
        case 'getPrintedMSD':   $qParms['bPrintedMSD'] = true;               break;
    }

    // Toronto region   "(LEFT(postcode,1) IN ('M','L') OR LEFT(postcode,2) IN ('N1','N2','N3','K9'))";
    // Southern Ontario "(LEFT(postcode,1) IN ('K','L','M','N'))";
    // Eastern Canada   "(province in ('ON','QC','NB','NS','PE','NF','NL'))";

    $oQ = new QServerMbr( $oApp, ['config_bUTF8'=>false] );
    $rQ = $oQ->Cmd( 'mbr-!-getListOffice', $qParms );
    $sRight .= "Members:<br/>{$rQ['sOut']}<br/>Found ".count($rQ['raOut'])." members<br/><br/>";

    $raMbr += $rQ['raOut'];
}


/* Look up bull_list
 */
if( $bEbull ) {
    $n = 0;
    switch( $p_lang ) {
        case 'EN': $sCond = "lang IN ('','B','E')";      break;
        //case 'FR': $sCond = "lang IN ('','B','F')";      break;
        case 'FR': $sCond = "lang IN ('B','F')";      break;
        case '':
        default:   $sCond = "lang IN ('','B','E','F')";  break;
    }

    $sRight .= "e-Bulletin:<BR/>$sCond<BR/>";

    if( ($dbc = $oApp->kfdb->CursorOpen( "SELECT email FROM seeds.bull_list WHERE status>0 AND $sCond" ) ) ) {
        while( $ra = $oApp->kfdb->CursorFetch( $dbc ) ) {
            $raEmail[] = $ra[0];
            ++$n;
        }
    }
    $sRight .= "Found $n emails<BR/><BR/>";
}


/* Look up sed_grower_curr
 */
if( SEEDCore_StartsWith($p_mbrFilter,'msdGrower') ) {
    include( SEEDLIB."msd/msdlib.php" );

    $raM = [];
    $raE = [];

    $raCond = [];
    $raCond[] = "NOT G.bDelete";
    $raCond[] = "M.email IS NOT NULL AND M.email <> ''";
    if( $p_lang == "EN" )  $raCond[] = "M.lang IN ('','E')";
    if( $p_lang == "FR" )  $raCond[] = "M.lang IN ('','F')";
    if( $bSEDCurr2 )     $raCond[] = "(NOT G.bDone AND NOT G.bDoneMbr AND NOT G.bDoneOffice)";  // these are independent, shouldn't be

    $sCond = "(".implode( " AND ", $raCond ).")";

    $sRight .= "Seed Directory Growers:<br/>$sCond<br/>";

    $oMSDLib = new MSDLib( $oApp );
    if( ($kfr = $oMSDLib->KFRelGxM()->CreateRecordCursor($sCond)) ) {
        while( $kfr->CursorFetch() ) {
            if( $p_outFormat == "mbrid" ) {
                $raM[] = $kfr->value('mbr_id');
            } else {
                $raE[] = $kfr->value('M_email');
            }
        }
    }

    $sRight .= "Found ".($p_outFormat == "mbrid" ? (count($raM)." members") : (count($raE)." emails"))."<br/><br/>";
    $raMbrid = array_merge( $raMbrid, $raM );
    $raEmail = array_merge( $raEmail, $raE );
}


switch( $p_outFormat ) {
    case 'email':
        // get the emails out of the raMbr array
        $raEmail += array_map( function($ra){ return($ra['email']); }, $raMbr );
        break;
    case 'mbrid':
        // get the _keys out of the raMbr array
        $raMbrid += array_map( function($ra){ return($ra['_key']); }, $raMbr );
        break;
    case 'xls':
        break;
}





$n = count($raEmail);
$raEmail = array_unique( $raEmail );
$sRight .= "Removed ".($n-count($raEmail))." duplicate emails.<BR/>Listing ".count($raEmail)." addresses below.<BR/>";
$n = count($raMbrid);
$raMbrid = array_unique( $raMbrid );
$sRight .= "Removed ".($n-count($raMbrid))." duplicate member ids.<BR/>Listing ".count($raMbrid)." member ids below.<BR/>";

sort( $raEmail, SORT_STRING );
sort( $raMbrid, SORT_NUMERIC );

$sRight .= "<DIV style='border:solid thin gray;padding:1em;font-family:courier new,monospace;font-size:10pt;color:black'>"
     ."<textarea style='width:100%' rows='50'>";

foreach( $raEmail as $v ) {
    $sRight .= $v."\n"; //echo "$v<BR/>";
}
foreach( $raMbrid as $v ) {
    $sRight .= $v."\n"; //echo "$v<BR/>";
}

$sRight .= "</textarea></div>";


$s = "<STYLE>"
    .".box1 { "
        ."border:3px grey solid;"
        ."background-color:#dddddd;"
        ."padding:1em 2em;"
        ."font-family:verdana,helvetica,sans serif;"
        ."font-size:11pt"
    ."}"
    ."p { "
        ."font-family:verdana,helvetica,sans serif;"
        ."font-size:11pt"
    ."}"
    ."</STYLE>"
    ."<div class='container-fluid'><div class='row'>"
    ."<div class='col-md-7'>$sLeft</div>"
    ."<div class='col-md-5' style='font-size:small;color:gray'>$sRight</div>"
    ."</div></div>";

echo Console02Static::HTMLPage( utf8_encode($oApp->oC->DrawConsole($s)), "", 'EN', array( 'consoleSkin'=>'green') );   // sCharset defaults to utf8

?>
