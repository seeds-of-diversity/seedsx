<?
/* Show a list of catalogues
 * $limit and $offset control the list.
 */

include_once( "../site.php" );
include_once( SITEINC."sitedb.php" );
include_once( "_hpd.php" );
include_once( "_func.php" );
include_once( "_catalog.php" );

$limit  = BXStd_SafeGPCGetInt( 'limit' );
$offset = BXStd_SafeGPCGetInt( 'offset' );
$order  = @$_REQUEST['order'];
if( $order != "name" && $order != "refdate" )  $order = "refdate";
if( $limit == 0 )  $limit = 100;

function html_before_results() {
    hpd_page_header();
    std_banner1( "Historic Seed and Nursery Catalogues" );
    echo "<CENTER>";
    echo "<P>Sort by <A href='". phpself_modify_parms("order=name&offset=0") ."'>Name</A>";
    echo " or <A href='". phpself_modify_parms("order=refdate&offset=0") ."'>Year of Publication</A>";
    echo " or <A href='".HPD_PAGE_START."?help=1'>Search by keyword</A></P>";
}

function html_after_results() {
    echo "</CENTER>";
    hpd_page_footer();
}


$opts['recordcount'] = '<P>The database contains %d historic catalogues.</P>';
$opts['format'] = 'catalog';
$opts['limit'] = $limit;
$opts['offset'] = $offset;
$args['order'] = $order;
paged_result_set( "cataloglist", $args, $opts );

?>
