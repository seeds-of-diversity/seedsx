<?
// urlencode the login_get_auth_urlparms because the username contains spaces - won't work on Netscape 4.7

header("Location: https://office.seeds.ca");

define( "SITEROOT", "../../" );


/* Show an event and allow it to be edited.
 *
 * $i = event item code
 *
 * OR
 *
 * $i = "new"
 * $p = event page code
 *
 * $who = a user name
 */


include_once( SITEROOT."site.php" );
include_once( EV_ROOT."_ev_inc.php" );
include_once( SITEINC."sodlogin.php" );


$la = new SoDLoginAuth;
if( !$la->SoDLoginAuth_Authenticate( $_REQUEST, "W EV" ) ) { exit; }


$kfdb = SiteKFDB() or die( "Cannot connect to database" );
$kfr = new KeyFrameRecord( $kfdb, $kfrdef_EVItems, $la->LoginAuth_UID() );
//$kfdb->KFDB_SetDebug(2);

$i = @$_REQUEST["i"];
if( $i == "new" ) {
    $p = intval(@$_REQUEST["p"]);
    if( $p < 1 ) {
        BXStd_HttpRedirect( EV_ROOT."events.php" );
    }
    $kfr->kfr_SetDefault( array( "ev_pages"=> $p ) );
} else {
    $i = intval($i);
    if( $i < 1 ) {
        BXStd_HttpRedirect( EV_ROOT."events.php" );
    }
    if( !$kfr->kfr_GetDBRow( $i ) ) {
        die( "Cannot locate event record in database." );
    }
    $p = $kfr->value("Page__rowid");
}


$title = ($i=="new" ? "Add Event to" : "Edit Event in") ."&nbsp;&nbsp; [". $kfr->value('Page_name') ."] ";
$title .= ($kfr->value('Page_bEN') && $kfr->value('Page_bFR')) ? "(Bilingual)" :
          ($kfr->value('Page_bFR') ? "(French only)" : "(English only)");
std_banner1( $title );

echo "<P align=center><FONT color=green>Using ". ($kfr->value('Page_type')=="SS" ? "Seedy Saturday" : "Regular Events") ." format.</FONT></P>";
?>


<?
function option_months( $sel ) {
    for( $i = 1; $i <= 12; ++$i ) {
        echo "<OPTION value='". $i ."'". ( $i==$sel ? " SELECTED" : "") .">". strftime( "%B", mktime(0,0,0,$i,1) ). "</OPTION>";
    }
}

function option_days( $sel ) {
    for( $i = 1; $i <= 31; ++$i ) {
        echo "<OPTION value='". $i ."'". ( $i==$sel ? " SELECTED" : "") .">". $i ."</OPTION>";
    }
}

function option_province( $province ) {
    echo "<OPTION value='AB'". ($province=='AB' ? " SELECTED" : "") .">AB</OPTION>";
    echo "<OPTION value='BC'". ($province=='BC' ? " SELECTED" : "") .">BC</OPTION>";
    echo "<OPTION value='MB'". ($province=='MB' ? " SELECTED" : "") .">MB</OPTION>";
    echo "<OPTION value='NB'". ($province=='NB' ? " SELECTED" : "") .">NB</OPTION>";
    echo "<OPTION value='NF'". ($province=='NF' ? " SELECTED" : "") .">NF</OPTION>";
    echo "<OPTION value='NS'". ($province=='NS' ? " SELECTED" : "") .">NS</OPTION>";
    echo "<OPTION value='ON'". ($province=='ON' ? " SELECTED" : "") .">ON</OPTION>";
    echo "<OPTION value='PE'". ($province=='PE' ? " SELECTED" : "") .">PE</OPTION>";
    echo "<OPTION value='QC'". ($province=='QC' ? " SELECTED" : "") .">QC</OPTION>";
    echo "<OPTION value='SK'". ($province=='SK' ? " SELECTED" : "") .">SK</OPTION>";
    echo "<OPTION value='YK'". ($province=='YK' ? " SELECTED" : "") .">YK</OPTION>";
    echo "<OPTION value='NT'". ($province=='NT' ? " SELECTED" : "") .">NT</OPTION>";
    echo "<OPTION value='NU'". ($province=='NU' ? " SELECTED" : "") .">NU</OPTION>";
}

function draw_field( $name, $label, $kfr, $size )
{
    echo "<TR><TD align='left'>$label:</TD><TD align='left'>";
    echo "<TABLE cellpadding=5>";
    if( $kfr->value("Page_bEN") )  echo "<TR><TD bgcolor=".CLR_BG_editEN.">(English) <INPUT TYPE=TEXT NAME=$name VALUE='".$kfr->valueEnt($name)."' size=$size></TD></TR>\n";
    if( $kfr->value("Page_bFR") )  echo "<TR><TD bgcolor=".CLR_BG_editFR.">(Fran&ccedil;ais) <INPUT TYPE=TEXT NAME={$name}_fr VALUE='".$kfr->valueEnt($name.'_fr')."' size=$size></TD></TR>\n";
    echo "</TABLE>";
    if( !$kfr->value("Page_bEN") )  echo "<INPUT TYPE=HIDDEN NAME=$name VALUE='".$kfr->valueEnt($name)."'>";
    if( !$kfr->value("Page_bFR") )  echo "<INPUT TYPE=HIDDEN NAME={$name}_fr VALUE='".$kfr->valueEnt($name.'_fr')."'>";
    echo "</TD></TR>";
}

?>


<FORM action='edit2.php' method='post'>
<?= $la->LoginAuth_GetHidden(); ?>
<INPUT TYPE=HIDDEN NAME=i    VALUE='<?= $i ?>'>
<INPUT TYPE=HIDDEN NAME=p    VALUE='<?= $p ?>'>

<TABLE cellpadding=5 width="50%" align="center">

<? // type=EV: title is the title of the event, city is the location
   // type=SS: city/prov is the title of the event, title is repurposed as the location

if( $kfr->value('Page_type')=="EV" ) {
    draw_field( "title", "Title", $kfr, 50 );
}

// both types have city and province here
?>
<TR><TD align="left">City:</TD>      <TD align="left"><INPUT TYPE=TEXT NAME=city     VALUE='<?= $kfr->valueEnt('city') ?>'     size=20>
                                                      <SELECT          NAME=province><? option_province( $kfr->value('province') ); ?></SELECT></TD></TR>

<?
if( $kfr->value('Page_type')=="SS" ) {
    draw_field( "title", "Location", $kfr, 50 );
}
?>

<TR><TD align="left">Date:</TD>      <TD align="left"><SELECT NAME=month><? option_months( $kfr->value('month') ); ?></SELECT>
                                                      <SELECT NAME=day><? option_days( $kfr->value('day') ); ?></SELECT>, <?= $kfr->value('Page_year') ?></TD></TR>
<? draw_field( "date_alt", "Alternate Date&nbsp;Text", $kfr, 50 ) ?>
<TR><TD align="left">Time:</TD>      <TD align="left"><INPUT TYPE=TEXT NAME=time     VALUE='<?= $kfr->valueEnt('time') ?>'     size=70></TD></TR>
<?
    echo "<TR><TD align='left' valign=top>Details:</TD><TD align='left'>";
    echo "<TABLE cellpadding=5>";
    if( $kfr->value('Page_bEN') )  echo "<TR><TD bgcolor='".CLR_BG_editEN."'>(English) <TEXTAREA NAME=details COLS=52 ROWS=5 WRAP=SOFT>".$kfr->valueEnt('details')."</TEXTAREA></TD></TR>";
    if( $kfr->value('Page_bFR') )  echo "<TR><TD bgcolor='".CLR_BG_editFR."'>(Fran&ccedil;ais) <TEXTAREA NAME=details_fr COLS=52 ROWS=5 WRAP=SOFT>".$kfr->valueEnt('details_fr')."</TEXTAREA></TD></TR>";
    echo "</TABLE>";
    if( !$kfr->value('Page_bEN') )  echo "<INPUT TYPE=HIDDEN NAME=details    VALUE='".$kfr->valueEnt('details')."'>";
    if( !$kfr->value('Page_bFR') )  echo "<INPUT TYPE=HIDDEN NAME=details_fr VALUE='".$kfr->valueEnt('details_fr')."'>";
    echo "</TD></TR>\n";
    echo "<TR><TD align=center colspan=2><INPUT TYPE=SUBMIT VALUE='".(($i=="new") ? "Add":"Update")."'>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;";
    echo "<A HREF='page.php?p=".$p."&".$la->LoginAuth_GetUrlParms()."'>Cancel</A></TD></TR>\n";
?>
</TABLE>

</FORM>

<DL>
<DT>Date</DT><DD>Required.  This is the date used for sorting.  If Alternate Date Text is blank, then this is also the date shown in the listing.</DD>
<DT>Alternate Date Text</DT><DD>Use this field if an event occurs over a range of dates, on multiple dates, or if the date has not been announced,
                      The Date field must still be specified for sorting purposes, but the Alternate Date Text will be shown in the listing instead.</DD>
</DL>

<P>E.g. Say there are three events in a row on Oct 12, Oct 19 and Oct 26, 2004.
You want the date field to say "October 12, 19 and 26, 2004", but you can only specify one date.
Enter the text that you want in the Alternate Date Text box, and it will appear in place of the date on the formatted event item.
But then, the "Sort by Date" feature needs a specific date to sort on.  Set a date in the regular Date field too - it won't
appear in the event item, but Sort by Date will use it to put the items in order.</P>


</BODY>
</HTML>
