<?
/* User portal to HPD
 *
 * The primary search page
 */
include_once( "../site.php" );
include_once( SITEINC."sitedb.php" );
include_once( "_hpd.php" );

hpd_page_header( "Heritage Plants Database" );
std_banner1( "Heritage Plants Database" );

if( !empty( $_REQUEST['help'] ) ) {
    include( "hpd_help.php" );
    echo "<BR><HR><BR>";
}

?>
<TR><TD width="100%" align="center">
<P><FONT size=-1 color=red>NEW!</FONT>&nbsp;&nbsp&nbsp;<a href="../sl/csci">Browse Canadian Seed Catalogue Inventory</a>&nbsp;&nbsp&nbsp;<FONT size=-1 color=red>NEW!</FONT></P>
<P><a href="specieslist.php">Browse Plant Species Index</a></P>
<P><a href="cataloglist.php">Browse Historic Seed Catalogues</a></P>
<P><a href="sourcesearch.php">Search by Seed Availability</a></P>
<BR>
<form name="search" action="cv.php" method="get">
<table cellpadding="5" border="0" bgcolor="#efcd8d" bordercolor="#000000">

<tr><td>
<font color="#57711a">Search for
<select name="qtype">
<option selected value="seed">Seeds</option>
<option value="catalog">Catalogues</option>
</select>
: </font>
</td><td>
<input type="text" name="query" size="15" maxlength="50">
<input type="submit" name="action" value="Go">
</td></tr>
<!-- difficult because the query can't find the word boundaries
    <tr><td>&nbsp;</td><td>
    <font color="#57711a" size=-1><input type=checkbox name="matchword" value=1> Match Whole Word</font>
    </td></tr>
-->

<tr><td>&nbsp;</td><td colspan="2" align="right"><font size="-1">example: <CODE>tomato brandywine</CODE><BR><a href="<?= $_SERVER["PHP_SELF"] ?>?help=1">see search help</a></font></td></tr>
</table>
</form>

<?
    hpd_page_footer();
?>
