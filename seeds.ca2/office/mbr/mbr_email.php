<?php
// Language selection should be more intuitive.  English only really means is the mail in English.
// Then French, English, and All should pick up 'B'.  We're not looking for B in mbr_contacts.  Get rid of '' default to B.

/* Email List Dump
 *
 * Dumps a list of email addresses from various sources
 */
define( "SITEROOT", "../../" );
include_once( SITEROOT."site2.php" );
include_once( STDINC."SEEDForm.php" );
include_once( SEEDCOMMON."console/console01.php" );
include_once( "_mbr.php" );
include_once( SEEDLIB."mbr/QServerMbr.php" );


list($kfdb, $sess) = SiteStartSessionAccount( array("R MBRMAIL") );  // and "BULLETIN"=>"R"

$oApp = SEEDConfig_NewAppConsole( ['db'=>'seeds2',
                                   'sessPermsRequired'=>['R MBRMAIL'] ]
);

$kfrelMbr = MbrContacts::KfrelBase( $kfdb, $sess->GetUID() );

$oConsole = new Console01( $kfdb, $sess, array( 'HEADER' => "Seeds of Diversity Email Lists") );
echo $oConsole->Style()
    .$oConsole->DrawConsole( "" );  // put the rest of the output below into this string for best results


$year = intval(date("Y"));

$bMbrCurr =  SEEDInput_Int('gMbrcurr');
$bMbrPrev =  SEEDInput_Int('gMbrprev');
$bMbrPrev2 = SEEDInput_Int('gMbrprev2');
$bEbull    = SEEDInput_Int('gEbull');
$bSEDCurr  = SEEDInput_Int('gSEDCurr');
$bSEDCurr2 = SEEDInput_Int('gSEDCurrNotDone');


$bOverrideNoEmail  = SEEDInput_Int('override_noemail');

$lang = SEEDInput_Smart( 'mbrLang', ['', 'EN', 'FR'] );
$p_listFormat = SEEDInput_Smart( 'listFormat', ['email', 'mbrid'] );


echo "<STYLE>"
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
    ."</STYLE>";

$oForm = new SEEDCoreForm('Plain');
$oForm->Load();


echo "<TABLE border='0' cellpadding='20' cellspacing='0'><TR>"
    ."<TD valign='top' width='60%'>"
    ."<P>Choose the combination of email lists that you want. Click the List button.<BR/>Copy/paste addresses from the box on the right.</P>"
    ."<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
    ."<DIV class='box1'>"
    ."<P>Choose one or more groups</P>"
    .$oForm->Select( 'yMbrExpires', ["-- No Members by Expiry --"=>0,
                                     "Current Members ($year or greater)"=>$year,
                                     ("Non-current members expired in ".($year-1))=>($year-1),
                                     ("Non-current members expired in ".($year-2))=>($year-2)] )
    ."</br/><br/>"
    .$oForm->Select( 'yMbrExpiresSince',  ["-- No Members Since --"=>0,
                                           ("All members since ".($year-1))=>($year-1),
                                           ("All members since ".($year-2))=>($year-2)] )
    ."<BLOCKQUOTE>"
    .SEEDForm_Checkbox( "gMbrcurr",  $bMbrCurr,  "Current members ($year or greater)" )."<BR/>"
    .SEEDForm_Checkbox( "gMbrprev",  $bMbrPrev,  "Members expired in ".($year-1) )."<BR/>"
    .SEEDForm_Checkbox( "gMbrprev2", $bMbrPrev2, "Members expired in ".($year-2) )."<BR/><BR/>"
    .SEEDForm_Checkbox( "gEbull",    $bEbull,    "e-Bulletin subscribers who signed up on the web site" )."<BR/><BR/>"
    .SEEDForm_Checkbox( "gSEDCurr",  $bSEDCurr,  "Members currently active or skipped in Member Seed Directory" )."<BR/>"
    .SEEDForm_Checkbox( "gSEDCurrNotDone", $bSEDCurr2,  "Members currently active or skipped in Member Seed Directory, and not bDone" )."<BR/>"
    ."</BLOCKQUOTE>"
    ."<BR/>"

    ."<P>Language</P><BLOCKQUOTE>"
    .SEEDForm_Radio( "mbrLang", "",   $lang, "All" )."</BR>"
    .SEEDForm_Radio( "mbrLang", "EN", $lang, "English Only" )."</BR>"
    .SEEDForm_Radio( "mbrLang", "FR", $lang, "French Only" )."</BR>"
    ."</BLOCKQUOTE>"
    ."<BR/>"

    ."<P>Format to Show</P><BLOCKQUOTE>"
    .SEEDForm_Radio( "listFormat", "email", $lang, "Email address" )."</BR>"
    .SEEDForm_Radio( "listFormat", "mbrid", $lang, "Member id (only if they have email addresses)" )."</BR>"
    ."</BLOCKQUOTE>"
    ."<BR/>"

    ."<DIV style='color:gray; margin-left:3em;border:thin solid grey;padding-left:1em;'>"
    ."<P>Don't check this unless you really mean it</P><BLOCKQUOTE>"  // don't use SEEDForm because it's actually good if the check isn't sticky
    ."<INPUT type='checkbox' name='override_noemail' value='1'> Include members who said they <U>don't want</U> email / e-Bulletin<BR><BR>"
    ."</BLOCKQUOTE>"
    ."</DIV>"

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

echo "</TD>"
    ."<TD valign='top'><DIV style='color:gray;font-size:9pt;font-family:verdana,helvetica,sans serif;'>";

$raEmail = array();
$raMbrid = array();

/* Look up emails/mbrids in mbr_contacts
 */
$raExp = array();
if( ($v = $oForm->ValueInt('yMbrExpires')) )       $raExp[] = "YEAR(expires) = '$v'";
if( ($v = $oForm->ValueInt('yMbrExpiresSince')) )  $raExp[] = "YEAR(expires) >= '$v'";
if( $bMbrCurr )  $raExp[] = "YEAR(expires) >= '$year'";
if( $bMbrPrev )  $raExp[] = "YEAR(expires) = '".($year-1)."'";
if( $bMbrPrev2 ) $raExp[] = "YEAR(expires) = '".($year-2)."'";
// uncomment this temporarily if it's January and you want to include people from 2.1 years ago
//if( $bMbrPrev2 ) $raExp[] = "YEAR(expires) = '".($year-3)."'";

if( count($raExp) ) {
    $raMbrCond = array();
    $raMbrCond[] = "(".implode( " OR ", $raExp ).")";
    $raMbrCond[] = "email IS NOT NULL AND email <> ''";

    if( !$bOverrideNoEmail )  $raMbrCond[] = "bNoEBull=0";
    if( $lang == "EN" )       $raMbrCond[] = "lang IN ('','E')";
    if( $lang == "FR" )       $raMbrCond[] = "lang IN ('','F')";

    if( false ) { // Toronto region
        $raMbrCond[] = "(LEFT(postcode,1) IN ('M','L') OR LEFT(postcode,2) IN ('N1','N2','N3','K9'))";
    }

    if( false ) { // Southern Ontario
        $raMbrCond[] = "(LEFT(postcode,1) IN ('K','L','M','N'))";
    }

    if( false ) { // ON, QC, NB, NS, PEI, NF (and NL)
        $raMbrCond[] = "(province in ('ON','QC','NB','NS','PE','NF','NL'))";
    }

    $sCond = "(".implode( " AND ", $raMbrCond ).")";

    echo "Members:<BR/>$sCond<BR/>";

    if( ($kfr = $kfrelMbr->CreateRecordCursor( $sCond )) ) {
        while( $kfr->CursorFetch() ) {
            if( $p_listFormat == "mbrid" ) {
                $raMbrid[] = $kfr->Key();
            } else {
                $raEmail[] = $kfr->value('email');
            }
        }
    }
    echo "Found ".($p_listFormat == "mbrid" ? (count($raMbrid)." members") : (count($raEmail)." emails"))."<BR/><BR/>";
}

$qParms = ['bExistsEmail'=>true, 'bEbulletin'=>true];
if( ($v = $oForm->Value('yMbrExpires')) )       $qParms['yExpiresIn'] = $v;
if( ($v = $oForm->Value('yMbrExpiresSince')) )  $qParms['yExpiresSince'] = $v;

    $oQ = new QServerMbr( $oApp, ['config_bUTF8'=>false] );
    $rQ = $oQ->Cmd( 'mbr-!-getListOffice', $qParms );
    echo "<p style='color:#888;font-size:small'>{$rQ['sOut']}</p>";
    var_dump(count($rQ['raOut']));


/* Look up emails in bull_list
 */
if( $bEbull ) {
    $n = 0;
    switch( $lang ) {
        case 'EN': $sCond = "lang IN ('','B','E')";      break;
        //case 'FR': $sCond = "lang IN ('','B','F')";      break;
        case 'FR': $sCond = "lang IN ('B','F')";      break;
        case '':
        default:   $sCond = "lang IN ('','B','E','F')";  break;
    }

    echo "e-Bulletin:<BR/>$sCond<BR/>";

    if( ($dbc = $kfdb->CursorOpen( "SELECT email FROM seeds.bull_list WHERE status>0 AND $sCond" ) ) ) {
        while( $ra = $kfdb->CursorFetch( $dbc ) ) {
            $raEmail[] = $ra[0];
            ++$n;
        }
    }
    echo "Found $n emails<BR/><BR/>";
}

/* Look up emails/mbrids in sed_grower_curr
 */
if( $bSEDCurr || $bSEDCurr2 ) {
    include( SEEDLIB."msd/msdlib.php" );

    $raM = [];
    $raE = [];

    $raCond = [];
    $raCond[] = "NOT G.bDelete";
    $raCond[] = "M.email IS NOT NULL AND M.email <> ''";
    if( $lang == "EN" )  $raCond[] = "M.lang IN ('','E')";
    if( $lang == "FR" )  $raCond[] = "M.lang IN ('','F')";
    if( $bSEDCurr2 )     $raCond[] = "(NOT G.bDone AND NOT G.bDoneMbr AND NOT G.bDoneOffice)";  // these are independent, shouldn't be

    $sCond = "(".implode( " AND ", $raCond ).")";

    echo "Seed Directory Growers:<br/>$sCond<br/>";

    $oMSDLib = new MSDLib( $oApp );
    if( ($kfr = $oMSDLib->KFRelGxM()->CreateRecordCursor($sCond)) ) {
        while( $kfr->CursorFetch() ) {
            if( $p_listFormat == "mbrid" ) {
                $raM[] = $kfr->value('mbr_id');
            } else {
                $raE[] = $kfr->value('M_email');
            }
        }
    }

    echo "Found ".($p_listFormat == "mbrid" ? (count($raM)." members") : (count($raE)." emails"))."<br/><br/>";
    $raMbrid = array_merge( $raMbrid, $raM );
    $raEmail = array_merge( $raEmail, $raE );
}


$n = count($raEmail);
$raEmail = array_unique( $raEmail );
echo "Removed ".($n-count($raEmail))." duplicate emails.<BR/>Listing ".count($raEmail)." addresses below.<BR/>";
$n = count($raMbrid);
$raMbrid = array_unique( $raMbrid );
echo "Removed ".($n-count($raMbrid))." duplicate member ids.<BR/>Listing ".count($raMbrid)." member ids below.<BR/>";

sort( $raEmail, SORT_STRING );
sort( $raMbrid, SORT_NUMERIC );

echo  "</DIV><DIV style='border:solid thin gray;padding:1em;font-family:courier new,monospace;font-size:10pt;'>"
     ."<textarea style='width:100%' rows='50'>";

foreach( $raEmail as $v ) {
    echo $v."\n"; //echo "$v<BR/>";
}
foreach( $raMbrid as $v ) {
    echo $v."\n"; //echo "$v<BR/>";
}

echo "</textarea></TD></TR></TABLE>";

?>
