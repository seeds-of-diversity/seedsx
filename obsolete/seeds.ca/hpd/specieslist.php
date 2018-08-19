<?
/* Show a list of species.
 * $limit and $offset control the list.
 */

include_once( "../site.php" );
include_once( SITEINC."sitedb.php" );
include_once( HPD_ROOT."_hpd.php" );
include_once( HPD_ROOT."_func.php" );

$limit  = BXStd_SafeGPCGetInt( 'limit' );
$offset = BXStd_SafeGPCGetInt( 'offset' );

if( $limit == 0 )  $limit = 100;


function html_before_results() {
    hpd_page_header();
    std_banner1( "Species List" );
    echo "<CENTER>";
}

function html_after_results() {
    echo "</CENTER>";
    hpd_page_footer();
}

function format_species( $record, $parms ) {
    if( $parms['i'] && ($parms['i'] % ($parms['n'] / 4)) == 0 ) { echo "</TD><TD valign=top>"; }
    echo '<P><A href="'.HPD_PAGE_CVFRAME.'?species=' . urlencode( $record["species"] ) . '">';
    echo $record["species"] . "</A> (" . $record['number'] . ")";
    echo "</P>";
}

function start_species_list() {
    echo '<TABLE cellpadding=20><TR><TD>';
}

function stop_species_list() {
    echo '</TD></TR></TABLE>';
}

$opts['recordcount'] = '<P>The database contains %d species.</P>';
$opts['format'] = 'species';
$opts['limit'] = $limit;
$opts['offset'] = $offset;
paged_result_set( "specieslist", array(), $opts );

?>
