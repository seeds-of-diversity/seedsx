<?
header("Location: https://office.seeds.ca");

define( "SITEROOT", "../../" );
include_once( SITEROOT."site.php" );
include_once( EV_ROOT."_ev_inc.php" );
include_once( SITEINC ."sodlogin.php" );


$la = new SoDLoginAuth;
if( !$la->SoDLoginAuth_Authenticate( $_REQUEST, "W EV" ) ) { exit; }

$kfdb = SiteKFDB() or die( "Cannot connect to database" );
$kfr = new KeyFrameRecord( $kfdb, $kfrdef_EVPages, 0 );

?>


<FORM ACTION="page.php">
<?= $la->LoginAuth_GetHidden(); ?>
<TABLE align=center>
<TR><TD>
<H2>Update Events on SEEDS.CA and <FONT color=blue>SEMENCES.CA</FONT></H2>
<BR>
<BR>

<TR><TD align="left">
<TABLE>
<TR><TD>&nbsp;</TD><TD align=center>SEEDS.CA</TD><TD align=center><FONT color=blue>SEMENCES.CA</FONT></TD></TR>
<?
    $i = 0;

    $kfr->kfr_CursorOpen( "", array("sSortCol"=>"_rowid","bSortDown"=>1) );
    while( $kfr->kfr_CursorFetch() ) {
        echo "<TR><TD><P><INPUT TYPE=RADIO NAME=p VALUE='". $kfr->value("_rowid") ."'". ($i==0 ? " CHECKED='CHECKED'" : "") ."></TD>";
        echo "<TD bgcolor=#e0e0e0>".$kfr->value("name") ."</TD><TD bgcolor=#e0e0ff>".$kfr->value("name_fr")."</TD>";
        echo "<TD>".(($kfr->value('bEN') && $kfr->value('bFR')) ? "(Bilingual)" : ($kfr->value('bFR') ? "(Fran&ccedil;ais seulement)" : "(English only)"))  ."</TD></TR>\n";
        $i = 1;
    }
?>
</TABLE>
<BR>
<P><INPUT TYPE=SUBMIT VALUE="Edit Page">
</TD></TR></TABLE>
</FORM>
