<?
/* Form page for searching by source availability.
 */

include_once( "../site.php" );
include_once( SITEINC."sitedb.php" );
include_once( "_hpd.php" );
include_once( "_func.php" );

hpd_page_header( "Seed Availability Search" );
std_banner1( "Seed Availability Search" );


if( !empty( $_REQUEST['help'] ) ) {
?>
<TABLE width='<?= ALT_PAGE_WIDTH ?>' align='center' border=1>
<TR><TD align="left">
<!--
<OL>
<LI>Select the <B>Species</B> that you want to search, or <B>All Species</B>.
<LI>Enter <B>one or more words</B>, separated by spaces, into the search box:
    <UL>
    <LI>Entering more words will help narrow the search<BR>(eg. <CODE>tomato sunset allegheny</CODE>)
    <LI>You may enter parts of words if you don't know how to spell a word (eg. enter "all" if you're not sure how to spell "allegheny")
    <LI>Searching is case-insensitive.  Capitalization does not matter.
    </UL>
<LI>Click "Go" to see the results of the search.
</OL>
<HR>
-->
</TD></TR></TABLE>
<BR><HR><BR>
<?
    }
?>

<FORM name="search" action="cv.php" method="get">
<INPUT TYPE=HIDDEN NAME="qtype" VALUE="source">
<TABLE width='<?= ALT_PAGE_WIDTH ?>' cellpadding="5" border="0" bgcolor="#efcd8d" bordercolor="#000000">

<TR><TD><FONT color="#57711a">Find cultivars of
<SELECT name="species">
<OPTION selected value="NULL">All Species</OPTION>
<?
/*
 *$species = query( "specieslist", array('bounds'=>'') );
 *for( $i = 0; !empty( $species[$i]['species'] ); $i++ ) {
 *    echo '<OPTION value="' . urlencode( $species[$i]['species'] ) . '">' . $species[$i]['species'] . '</OPTION>';
 *}
 */
$dbc = db_open( "SELECT species FROM hvd_sourcelist GROUP BY species" );
while( $ra = db_fetch( $dbc ) ) {
    echo '<OPTION value="' . urlencode( $ra['species'] ) . '">' . $ra['species'] . '</OPTION>';
}
?>
</SELECT></FONT></TD>
<TD>
<FONT size="-1"><A href="<?= $_SERVER['PHP_SELF'] ?>?help=1">see search help</A></FONT>
</TD>
</TR>

<TR><TD colspan="2">
that
<SELECT name="sodc">
<OPTION selected value="NULL">(no restriction)</OPTION>
<OPTION value="1">are listed by Seeds of Diversity members</OPTION>
<OPTION value="0">are not listed by Seeds of Diversity members</OPTION>
</SELECT>
</TD></TR>

<TR><TD colspan="2">
and
<SELECT name="commerce">
<OPTION selected value="NULL">(no restriction)</OPTION>
<OPTION value="0">are not commercially available</OPTION>
<OPTION value="4">are sold by fewer than 5 seed companies</OPTION>
<OPTION value="9">are sold by fewer than 10 seed companies</OPTION>
<OPTION value="10">are sold by 10 or more seed companies</OPTION>
</SELECT>
</TD></TR>

<TR><TD colspan="2">
and
<SELECT name="genebank">
<OPTION selected value="NULL">(no restriction)</OPTION>
<OPTION value="pgrc">are maintained by the Canadian gene bank</OPTION>
<OPTION value="npgs">are maintained by the U.S. gene bank</OPTION>
<OPTION value="either">are maintained by either the Canadian or U.S. gene banks</OPTION>
<OPTION value="both">are maintained by both the Canadian and U.S. gene banks</OPTION>
<OPTION value="neither">are maintained by neither gene bank</OPTION>
</SELECT>
</TD></TR>

<TR>

<TD>Note: This only searches through cultivars for which source information
is available.  Use the <A href="<?= HPD_PAGE_START ?>">regular search form</A> to search the <EM>entire</EM> database.</TD>
<TD colspan="2" align="right">
<INPUT type="submit" name="action" value="Go">
</TD>
</TR>
</TABLE>
</FORM>

<?
    hpd_page_footer();
?>
