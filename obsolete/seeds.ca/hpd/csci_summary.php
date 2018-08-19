<?
define( "SITEROOT", "../" );
include_once( SITEROOT."site.php" );
include_once( SITEINC."sitedb.php" );
include_once( HPD_ROOT."_csci.php" );


/* Clean up the names.  Then I'm going to move this to the main CSCI page.
 */

$nPSpecies      = db_query1("SELECT count(distinct pspecies) FROM cat_item WHERE _status=0");
$nPNames        = db_query1("SELECT count(distinct pspecies,pname) FROM cat_item WHERE _status=0");
$nCompanies     = db_query1("SELECT count(distinct cat_catalog_id) FROM cat_item WHERE _status=0");

echo "<IMG src='".SITEIMG."logo_EN.gif'>";
echo "<H2>Canadian Seed Catalogue Inventory - Summary</H2>";
echo "<P>Seeds of Diversity's <I>Canadian Seed Catalogue Inventory</I> lists <B>$nPSpecies</B> species and <B>$nPNames</B> varieties of vegetables and fruit sold by <B>$nCompanies</B> mail-order seed companies in Canada during 2004-2005.</P>";

/* Number of varieties available from one source, two sources, etc.
 */
$nSrc1 = 0;
$nSrc2 = 0;
$nSrc3_5 = 0;
$nSrc6_10 = 0;
$nSrc11_x = 0;
$i = 0;

echo "<H3>The ten most commonly found vegetables in Canadian seed catalogues</H3><TABLE>";
$dbc = db_open( "SELECT pspecies,pname,count(*) FROM cat_item WHERE _status=0 GROUP BY pspecies,pname ORDER BY 3 DESC" );
while( $ra = db_fetch($dbc) ) {
    if( $ra[2] == 1 )       ++$nSrc1;
    else if( $ra[2] == 2 )  ++$nSrc2;
    else if( $ra[2] <= 5 )  ++$nSrc3_5;
    else if( $ra[2] <= 10)  ++$nSrc6_10;
    else                    ++$nSrc11_x;

    if( $i < 10 ) {
        ++$i;
        echo "<TR><TD>${ra[0]}</TD><TD>${ra[1]}</TD><TD>${ra[2]} companies</TD></TR>";
    }
}
echo "</TABLE>";


echo "<H3>Distribution of listed varieties</H3>";
echo "<P>Of <B>$nPNames</B> varieties listed in the CSCI:<BR>";
echo "<B>$nSrc1</B>    varieties (".(intval($nSrc1    / $nPNames * 1000) / 10)."%) are sold by only one listed company<BR>";
echo "<B>$nSrc2</B>    varieties (".(intval($nSrc2    / $nPNames * 1000) / 10)."%) are sold by only two listed companies<BR>";
echo "<B>$nSrc3_5</B>  varieties (".(intval($nSrc3_5  / $nPNames * 1000) / 10)."%) are sold by three to five listed companies<BR>";
echo "<B>$nSrc6_10</B> varieties (".(intval($nSrc6_10 / $nPNames * 1000) / 10)."%) are sold by six to ten listed companies<BR>";
echo "<B>$nSrc11_x</B> varieties (".(intval($nSrc11_x / $nPNames * 1000) / 10)."%) are sold by more than ten listed companies<BR>";
echo "</P>";


if( empty($_REQUEST['extra'] ) ) {
    exit;
}


/* Number of varieties of each species listed in various inventories
 */
echo "<H3>The number of varieties of each species listed in various inventories</H3>";
echo "<TABLE border=1>";
echo "<TR><TH>Species</TH><TH>Varieties in CSCI<BR>(Canadian)</TH><TH>Varieties in GSI v5<BR>(Canadian and U.S.)</TH><TH>Total Varieties Known<BR>(includes Gene Banks)</TH></TR>";
$dbc = db_open( "SELECT pspecies,count(distinct pname) FROM cat_item WHERE _status=0 GROUP BY pspecies ORDER BY 2 DESC" );
while( $ra = db_fetch($dbc) ) {
    echo "<TR><TD>${ra[0]}</TD><TD>${ra[1]}</TD>";
    echo "<TD>".db_query1("SELECT count(*) FROM hvd_sourcelist WHERE species='".addslashes($ra[0])."' AND gsi>0")."</TD>";
    echo "<TD>".db_query1("SELECT count(*) FROM hvd_sourcelist WHERE species='".addslashes($ra[0])."'")."</TD>";
    echo "</TR>";
}
echo "</TABLE>";


?>
