<?php

define( SITEROOT, "../../" );
include_once( SITEROOT."site.php" );
include_once( STDINC."SEEDForm.php" );
include_once( STDINC."SEEDMetaTable.php" );
include_once( SEEDCOMMON."siteStart.php" );

list($kfdb) = SiteStart();

$oBucket = new SEEDMetaTable_StringBucket( $kfdb );

if( SEEDSafeGPC_GetStrPlain('action') == 'Save' ) {
    $raETmp = SEEDStd_EnumTuplesUnpack( $_REQUEST, array('rec_e','rec_d'), array('bGPC'=>true, 'bSkipEmpty'=>true) );
    $raE = array();
    $i = 1;
    foreach( $raETmp as $ra ) {
        $raE['e'.$i] = @$ra['rec_e'];
        $raE['d'.$i] = @$ra['rec_d'];
// use a standard SEEDDate function to normalize this to yyyy-mm-dd or yyyy/mm/dd
        ++$i;
    }

    $oBucket->PutStr( 'erbst_snack', 'email_list', SEEDStd_ParmsRA2URL( $raE ) );
    $oBucket->PutStr( 'erbst_snack', 'email_subj', SEEDSafeGPC_GetStrPlain('email_subj') );
    $oBucket->PutStr( 'erbst_snack', 'email_from', SEEDSafeGPC_GetStrPlain('email_from') );
    $oBucket->PutStr( 'erbst_snack', 'email_bcc',  SEEDSafeGPC_GetStrPlain('email_bcc') );
    $oBucket->PutStr( 'erbst_snack', 'email_body', SEEDSafeGPC_GetStrPlain('email_body') );
    $oBucket->PutStr( 'erbst_snack', 'notes',      SEEDSafeGPC_GetStrPlain('notes') );
    $oBucket->PutStr( 'erbst_snack', 'bEnable',    SEEDSafeGPC_GetInt('bEnable') );
}


$sEmails = $oBucket->GetStr( 'erbst_snack', 'email_list' );
$sSubj   = $oBucket->GetStr( 'erbst_snack', 'email_subj' );
$sFrom   = $oBucket->GetStr( 'erbst_snack', 'email_from' );
$sBcc    = $oBucket->GetStr( 'erbst_snack', 'email_bcc' );
$sBody   = $oBucket->GetStr( 'erbst_snack', 'email_body' );
$sNotes  = $oBucket->GetStr( 'erbst_snack', 'notes' );
$bEnable = intval( $oBucket->GetStr( 'erbst_snack', 'bEnable' ) );

// $sEmails looks like e1=foo&d1=bar&e2=blart&d2=gnarley
// so this decodes the string into an enumerated-tuples array, and unpacks that to array( array('e'=>'foo','d'=>'bar'), array('e'=>'blart','d'=>'gnarley')
$raEmails = SEEDStd_EnumTuplesUnpack( SEEDStd_ParmsURL2RA( $sEmails ), array('e','d') );


$s = "<STYLE>"
    ."h2,h3,p,body {font-family:verdana,helvetica,sans serif;}"
    ."th,td {font-family:verdana,helvetica,sans serif; font-size:11pt;}"
    ."</STYLE>";


$s .= "<H2>Erb Street Snack Reminder</H2>"
    ."<P>The email message below will be sent every Wednesday to the address(es) on the right whose date(s) are less than a week in the future.</P>"
    ."<UL>"
    ."<LI>Change the message, email addresses, and dates any time you like, by clicking the Save button.</LI>"
    ."<LI>If you want to stop the email, uncheck the Enable checkbox. No email will be sent until you check it again.</LI>"
    ."</UL>"
    ."<FORM method='post' action='${_SERVER['PHP_SELF']}'>"
    ."<DIV style='width:90%;border:thin solid #777;background-color:#ddd;padding:1em;margin:2em;'>"
    ."<TABLE border='0' cellspacing='0' cellpadding='10' width='100%'><TR valign='top'>"
    ."<TD>"
    ."Subject<BR/>"
    ."<INPUT type='text' name='email_subj' value='".SEEDCore_HSC($sSubj)."' style='width:100%'/>"
    ."<BR/><BR/>"
    ."From<BR/>"
    ."<INPUT type='text' name='email_from' value='".SEEDCore_HSC($sFrom)."' style='width:100%'/>"
    ."<BR/><BR/>"
    ."Bcc<BR/>"
    ."<INPUT type='text' name='email_bcc' value='".SEEDCore_HSC($sBcc)."' style='width:100%'/>"
    ."<BR/><BR/>"
    ."Email message<BR/>"
    ."<TEXTAREA style='width:100%' name='email_body' rows='15'>".SEEDCore_HSC($sBody)."</TEXTAREA>"
    ."<BR/><BR/>"
//    ."Recipients (email addresses separated by commas)<BR/>"
//    ."<TEXTAREA style='width:100%' name='email_list' rows='10'>".SEEDCore_HSC($sEmails)."</TEXTAREA>"
//    ."<BR/><BR/>"
    .SEEDForm_Checkbox( 'bEnable', $bEnable, '' )
    ."Enable (emails will only be sent if this is checked)"
    ."<BR/><BR/>"
    ."<INPUT type='submit' name='action' value='Save'/>"
    ."<BR/><BR/>"
    ."Notes<BR/>"
    ."<TEXTAREA style='width:100%' name='notes' rows='15'>".SEEDCore_HSC($sNotes)."</TEXTAREA>"
    ."</TD><TD width='40%'>"
    ."<TABLE border='0' cellspacing='0' cellpadding='10'>"
    ."<TR valign='top'><TH>Recipient Email</TH><TH>Sunday Date</TH>";

$i = 1;
foreach( $raEmails as $ra ) {
    $name = "rec_d$i";
    $s .= "<TR valign='top'>"
         ."<TD>".SEEDForm_Text('rec_e'.$i,$ra['e'],"",30)."</TD>"
         ."<TD><input name='$name' id='$name' type='date' value='${ra['d']}'/></TD>"
         ."</TR>";
    ++$i;
}
$name = 'rec_d$i';
$s .= "<TR valign='top'>"
     ."<TD>".SEEDForm_Text('rec_e'.$i,"","",30)."</TD>"
     ."<TD><input name='$name' id='$name' type='date' value=''/></TD>"
     ."</TR>";

$name = 'rec_d'.($i+1);
$s .= "<TR valign='top'>"
     ."<TD>".SEEDForm_Text('rec_e'.($i+1),"","",30)."</TD>"
     ."<TD><input name='$name' id='$name' type='date' value=''/></TD>"
     ."</TR>";

$s .= "</TABLE></TD>"
     ."</DIV></FORM>";

echo $s ;

?>
