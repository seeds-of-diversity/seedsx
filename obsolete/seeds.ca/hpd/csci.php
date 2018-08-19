<?

// TODO:  join cat_catalog into the main SELECT
//        use _status in the SELECTs


/* Canadian Seed Catalogue Inventory
 *
 * Parms:
 *     species: (empty)  - List the species in the CSCI
 *              (!empty) - List the cultivars of that species that are available in current catalogues
 */

include_once( "../site.php" );
include_once( SITEINC."sitedb.php" );
include_once( "_hpd.php" );
include_once( "_csci.php" );

$species = BXStd_SafeGPCGetStr( "species" );

if( empty($species['plain']) ) {
    /* Show Species list
     */
    hpd_page_header();

    echo "<H2>Canadian Seed Catalogue Inventory</H2>";
    echo "<BLOCKQUOTE>";
    echo "<P>These vegetables and fruit were offered by Canadian seed and plant companies in 2004-2008.  ";
    echo "Click on a name to see the varieties available, and the companies that sold them.</P>";
    echo "</BLOCKQUOTE><BR>";

    echo "<H3>Vegetable and Fruit Seeds Available in Canada</H3>";
    echo "<TABLE>";

    // $rec = new dbPhrameRecord( $CSCI_Item_Simple_Recorddef, 0 );
    // It would be nice to use dPR_Cursor, but it can't do DISTINCT
    if( $dbc = db_open( "SELECT DISTINCT(pspecies) AS sp FROM cat_item WHERE _status=0 ORDER BY sp" ) ) {
        /* List the species names in columns, reading down.
         */
        $tableCols = 4;
        $tableRows = intval( (db_numrows($dbc) + $tableCols - 1) / $tableCols );

        $raSp = array();
        for( $i = 0; $i < $tableCols; ++$i ) {
            for( $j = 0; $j < $tableRows; ++$j ) {
                if( $ra = db_fetch($dbc) ) {
                    $raSp[$i][$j] = $ra['sp'];
                }
            }
        }

        for( $j = 0; $j < $tableRows; ++$j ) {
            echo "<TR>";
            for( $i = 0; $i < $tableCols; ++$i ) {
                $sp = @$raSp[$i][$j];
                echo "<TD><A HREF='${_SERVER['PHP_SELF']}?species=".urlencode($sp)."'>$sp</A>";
                echo "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</TD>";
            }
            echo "</TR>";
        }
    }
    echo "</TABLE>";

    hpd_page_footer();

} else {
    /* Show Cultivar list
     */
    // use dPR_CursorOpen to ensure that _status and other future data model implementations are used correctly
    $dbc = db_open( "SELECT DISTINCT(pname) AS cv FROM cat_item WHERE _status=0 AND pspecies='${species['db']}' ORDER BY cv" );
    if( !$dbc )  header( "Location: csci.php" );

    hpd_page_header();

    echo "<H2>Canadian Seed Catalogue Inventory</H2>";
    echo "<BLOCKQUOTE>";
    echo "<P>According to our records, the following varieties were offered by Canadian seed and plant companies in 2004-2008.</P>";
    echo "<P>This information is provided as is, to further our knowledge of garden biodiversity and the ";
    echo "conservation of heritage plants.  Seeds of Diversity takes no responsibility for errors or omissions, ";
    echo "but we appreciate any updates that you can provide.</P>";
    echo "</BLOCKQUOTE><BR>";

    echo "<H3>${species['plain']} - Varieties Sold in Canada</H3><BR>";
    for( $n = 0; $ra = db_fetch( $dbc ); ++$n ) {

        csci_drawCompanyList( $species['plain'], $ra['cv'] );

    }
    if( !$n )  echo "No records";

    hpd_page_footer();
}

?>
