<?

if( !defined("SITEROOT") )  define("SITEROOT", "../");
include_once( SITEROOT."site.php" );
include_once( STDINC."SEEDForm.php" );
include_once( STDINC."SEEDTickList.php" );
include_once( STDINC."SEEDPerms.php" );
include_once( STDINC."KeyFrame/KFUIAppSimple.php" );
include_once( STDINC."DocRep/DocRepWiki.php" );
include_once( SEEDCOMMON."siteApp.php" );
include_once( SEEDCOMMON."doc/docUtil.php" );
include_once( SEEDCOMMON."console/_console01.php" );
include_once( "_gcgc.php" );

list($kfdb, $sess) = SiteStartSessionAccount( array("gcgcadmin" => "W") );
//$kfdb->KFDB_SetDebug(2);
//print_r($_REQUEST);

$oDocRepDB = New_DocRepDB_WithMyPerms( $kfdb, $sess->GetUID(), array('bReadonly'=>true) );
$oDocRepWiki = new DocRepWiki( $oDocRepDB, "" );

include_once(SITEROOT."mbr/_mbr.php");  // mbr_contacts

$kfrelMbr = new KeyFrameRelation( $kfdb, $kfrelDef_mbr_contacts, $sess->GetUID() );
$kfrelG   = new KeyFrameRelation( $kfdb, $kfrelDef_GCGC_GrowersXContacts_withAliasG, $sess->GetUID() );



$docrepid = SEEDSafeGPC_Smart("docrepid", array( "gcgc_letter_july2008_en", "gcgc_letter_july2008_fr" ) );
$lang     = SEEDSafeGPC_Smart("lang", array("EN","FR") );
$pFmt     = SEEDSafeGPC_Smart("fmt", array("Test","Email","Print") );

$mbrRadio = SEEDSafeGPC_Smart( "mbrRadio", array("","mbr1","mbrAIorPA") );
$mbr1num = SEEDSafeGPC_GetInt("mbr1num");


$langCond = ($lang =="EN" ? "<>'F'" : "='F'");

echo console01_style();
echo console01_header("Garlic Mailing");

echo "<FORM action='${_SERVER['PHP_SELF']}'>"
    ."<BR/><BR/>"
    ."<DIV class='console01_controlbox' style='width:50%'>"
    ."<DIV class='console01_controlbox_label'>Prepare the Document</DIV>"
    ."<P><B>1. Choose the document template</B></P>"
    ."<P style='margin-left:3em'>This is the name of the template from Office Documents: ".SEEDForm_Text( "docrepid", $docrepid, "", 30 )."</P>"
    ."<P><B>2. Choose the language</B></P>"
    ."<P style='margin-left:3em'>This formats the letter for the growers who prefer this language. It also formats the substitution fields in this language. The language should match the template."
    .SEEDForm_Select( "lang", array("EN"=>"English", "FR"=>"Fran�ais"), $lang )."</P>"
    ."<P><B>3. How do you want to format it?</B></P>"
    ."<P style='margin-left:3em'>You'll have a chance to confirm this, don't worry "
    .SEEDForm_Select( "fmt", array("Test"=>"Test on this screen", "Email"=>"Email", "Print"=>"Print"), $pFmt)."</P>"
    ."<P><B>4. To whom do you want to send the mailing?</B></P>"
    ."<P style='margin-left:3em'><INPUT type='radio' name='mbrRadio' value='mbr1'".($mbrRadio=="mbr1" ? " CHECKED" : "")."/> Show letter for one member (enter membership number here): "
    ."<INPUT type='text' name='mbr1num' value='".($mbr1num?$mbr1num:"")."' size='6'/>"
    ."<BR/>"
    ."<INPUT type='radio' name='mbrRadio' value='mbrAIorPA'".($mbrRadio=="mbrAIorPA" ? " CHECKED" : "")."/> Show letter for all garlic growers with status ACTIVE, INACTIVE, or PENDING-ACTIVE (who prefer the above language)"
    ."</P>"
    ."<BR/>"
    ."<INPUT type='submit' value='Show what it will look like >>'/>".SEEDStd_StrNBSP("",10)."<A HREF='${_SERVER['PHP_SELF']}'>RESET</A>"
    ."</DIV>";

$sOut = "";
$raNoEmail = array();   // Email: reports the members who don't have email addresses
$sEmailSubject = ($lang=='EN' ? "Seeds of Diversity's Great Canadian Garlic Collection" : "Semences du patrimoine: la Grande Collection Canadienne d'Ails");

$kfrG = NULL;
if( $mbrRadio == "mbr1" ) {
    if( $mbr1num ) {
        $kfrG = $kfrelG->CreateRecordCursor( "M._key='$mbr1num'" );
    }
} else if( $mbrRadio == "mbrAIorPA" ) {
    $kfrG = $kfrelG->CreateRecordCursor( "G.status IN ('ACTIVE','INACTIVE','PENDING-ACTIVE') AND M.lang{$langCond}", array('sSortCol'=>'G.fk_mbr_contacts') );
}
if( !$kfrG )  exit;


while( $kfrG->CursorFetch() ) {

    $mbrid = $kfrG->Value("M__key");

    $raVars = array();

    // Var:mbr_drawAddress
    $raVars['mbr_drawAddress'] = mbr_drawAddress( $kfdb, $mbrid, array("bEmail"=>1) );

    // Var:expiry_notice
    $sExpires = $kfrG->Value('M_expires');
    $yExpires = intval(substr($sExpires,0,4));
    $raVars['expiry_notice'] =
                 ( $lang == "EN"
                        ?      ( $yExpires >= '2020' ? "Your Seeds of Diversity Membership is in good standing.<BR/>Thankyou for your support."
                             : ( $yExpires >= date('Y') ? "Your Seeds of Diversity Membership is valid until $sExpires.<BR/>Thankyou for your support."
                             : ( "<FONT color='red'>Your Seeds of Diversity Membership ".($yExpires ? "expired on $sExpires." : "is not up to date.")
                                ."<BR/>Please renew online at <A HREF='http://www.seeds.ca/mbr'>http://www.seeds.ca/mbr</A>"
                                ."<BR/>or by contacting our office at 1-866-509-SEED.</FONT>")))
                        :
                               ( $yExpires >= '2020' ? "Votre adh�sion de Semences du patrimoine est valide.<BR/>Merci pour votre soutien."
                             : ( $yExpires >= date('Y') ? "Votre adh�sion de Semences du patrimoine est valide jusqu'� $sExpires.<BR/>Merci pour votre soutien."
                             : ( "<FONT color='red'>Votre adh�sion de Semences du patrimoine ".($yExpires ? "a expir� le $sExpires." : "n'est pas � jour.")
                                ."<BR/>SVP renouvellez � <A HREF='http://www.semences.ca/mbr'>http://www.semences.ca/mbr</A>"
                                ."<BR/>ou t�l�phonez 1-866-509-7333.</FONT>")))
                 );


    // Var:varieties
    $kfrelS = new KeyFrameRelation( $kfdb, $kfrelDef_GCGC_Samples, $sess->GetUID() );

    $s = "<TABLE border='0' cellspacing='5' cellpadding='0'>";
    if( ($kfrS = $kfrelS->CreateRecordCursor( "G.fk_mbr_contacts='".$mbrid."' AND S.status='ACTIVE'", array('sSortCol'=>'V_index_name') ) ) ) {
        while( $kfrS->CursorFetch() ) {
            $s .= "<TR>"
                 ."<TD valign='top'>".SEEDStd_StrNBSP($kfrS->Value("V_index_name"))."<BR><FONT size=1>"
                 .$kfrS->Value("year_start")."-".($kfrS->Value("year_last_verified")?$kfrS->Value("year_last_verified"):"")."</FONT></TD>"
                 ."<TD valign='top' align='center'><INPUT type='checkbox' name='gcgc".$kfrS->Key()."' value='1'></TD>"
                 ."</TR>";
        }
        $kfrS->CursorClose();
    }
    $s .= "</TABLE>";

    $raVars['varieties'] = $s;



    $oDocRepWiki->SetVars( $raVars );
    $sDocOutput = $oDocRepWiki->TranslateDoc( $docrepid );



    switch( $pFmt ) {
        case "Test":
            $sOut .= "<DIV style='margin-left:100px; padding:1.5em; border:thin groove black;width:75%'>"
                    .$sDocOutput
                    ."</DIV><BR/><BR/>";
            break;

        case "Email":
            if( !$kfrG->IsEmpty("M_email") ) {

                if( SEEDSafeGPC_GetInt("bEmailConfirm") == 1 ) {
                    $pEmail = SEEDSafeGPC_Smart("pEmail", array("Test","Normal","BccMe") );
                    $sEmailTo = "";
                    $raParms = array();

                    switch( $pEmail ) {
                        case "Test":
                            $sEmailTo = $sess->GetEmail();
                            break;
                        case "Normal":
                            $sEmailTo = $kfrG->Value("M_email");
                            break;
                        case "BccMe":
                            $sEmailTo = $kfrG->Value("M_email");
                            $raParms['bcc'][] = $sess->GetEmail();
                            $raParms['bcc'][] = "garlic@seeds.ca";      // pretty sure the system removes duplicates, so put these here anyway
                            $raParms['bcc'][] = "bob@seeds.ca";
                            break;
                    }

                    $raParms['from'][0] = ($lang =='EN' ? "garlic@seeds.ca" : "ail@semences.ca");
                    $raParms['from'][1] = ($lang =='EN' ? "\"Brian Woods, Garlic Project Coordinator\"" : "\"Brian Woods, Coordinateur du projet d'ail\"");
                    if( MailFromOffice( $sEmailTo, $sEmailSubject, "", $sDocOutput, $raParms ) ) {
                        $sOut .= "Sent to $sEmailTo<BR/>";
                    } else {
                        $sOut .= "Failed sending to $sEmailTo<BR/>";
                    }

                } else {
                    $sOut .= "<P>This will be emailed to: ".$kfrG->value("M_email")."</P>";
                    $sOut .= "<DIV style='margin-left:100px; padding:1.5em; border:thin groove black;width:75%'>"
                            .$sDocOutput
                            ."</DIV><BR/><BR/>";
                }
            } else {
                $raNoEmail[] = $kfrG->value("M_firstname")." ".$kfrG->value("M_lastname")." ".$kfrG->value("M_company")." (".$kfrG->value("M__key").")";
            }
            break;

        case "Print":
            break;
    }

}
$kfrG->CursorClose();


if( $pFmt == "Email" ) {
    echo "<DIV class='console01_controlbox' style='width:50%'>"
        ."<DIV class='console01_controlbox_label'>Ready to Send : Email Options</DIV>"
        ."<P><B>5. Choose the send method</B></P>"
        ."<P style='margin-left:3em'>The email(s) will be formatted as shown below. How do you want to send them?"
        .SEEDForm_Select("pEmail", array("Test"=>"Test: Send all emails to me (".$sess->GetEmail().") instead of the recipients",
                                         "Normal"=>"Normal: Send all to recipients",
                                         "BccMe"=>"Double-check: Send all to recipients and Bcc me (".$sess->GetEmail().") so I can see what was sent" ) )
        ."</P>"
        ."<INPUT type='hidden' name='bEmailConfirm' value='1'/>"
        ."<INPUT type='submit' value='Send the Email'/>"
        ."</DIV>";

    if( count($raNoEmail) ) {
        echo "<DIV style='border:thin solid red;margin:1em;padding:1em;width:50%;'><B>The following people do not have email addresses listed. Email will not be sent to them.</B><BR/><BR/>"
            .implode("<BR/>", $raNoEmail )
            ."</DIV><BR/><BR/>";
    }

}
echo "</FORM>";





echo $sOut;


?>
