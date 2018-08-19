<?
// todo: summarize the query at the top of the results


/* Search-results page.
 *
 * Perform a search by $qtype and list the results.
 */

include_once( "../site.php" );
include_once( SITEINC."sitedb.php" );
include_once( "_hpd.php" );
include_once( "_func.php" );
include_once( "_seed.php" );
include_once( "_catalog.php" );


function html_before_results() {
    echo "<CENTER>";
}

function html_after_results() {
    echo "</CENTER>";
}

function record_count_hook( $records ) {
    printf( "<P>Your search matched %d record(s).", $records );
    if( $records > 50 ) {
        echo '<BR><B>Try adding <a href="hpd.php?help=1">more words</a> to narrow the search.</B></P>';
    } else {
        echo "</P>";
    }
}



/**********/

$query = BXStd_MagicAddSlashes(@$_REQUEST["query"]);
$qtype = @$_REQUEST["qtype"];
$limit   = BXStd_SafeGPCGetInt( 'limit' );
$offset  = BXStd_SafeGPCGetInt( 'offset' );
$cond  = "";  //unused  @$_REQUEST["cond"];        // UNSAFE - parameterize the conditions that calling pages need

if( empty( $query ) )  BXStd_HTTPRedirect( HPD_PAGE_START );

if( $qtype != "catalog" )  $qtype = "seed";


if( $qtype == 'catalog' ) {
    hpd_page_header();
}


// Build a where-clause that is a conjunction of disjunctions.
// ie. it has the form ( a or b or c ) and ( d or e or f ) and ( g or h or i )
// Each i'th disjunction ensures that the i'th word of the search query is found in one of the designated fields
for( $tok = strtok( $query, " " ); !empty($tok); $tok = strtok( " " ) ) {
    if( !empty( $cond ) )  $cond .= " AND ";
    $cond .= ($qtype == "seed") ? "(species LIKE '%$tok%' OR pname LIKE '%$tok%' OR oname LIKE '%$tok%') "
                               : "(name LIKE '%$tok%' OR place LIKE '%$tok%' OR publisher LIKE '%$tok%') ";
}

$opts['format'] = $qtype;
$opts['recordcountfunc'] = "record_count_hook";
$opts['limit'] = $limit;
$opts['offset'] = $offset;
$args['condition'] = $cond;
paged_result_set( $qtype . "search", $args, $opts );

if( $qtype == 'catalog' ) {
    hpd_page_footer();
}
?>
