<?
/* Show a list of cultivars for the given $species.
 * $limit and $offset control the list.
 * This is the left frame of the CV frameset.
 */

include_once( "../site.php" );
include_once( SITEINC."sitedb.php" );
include_once( "_hpd.php" );
include_once( "_func.php" );


$gpc_species = BXStd_SafeGPCGetStr( 'species' );
$limit       = BXStd_SafeGPCGetInt( 'limit' );
$offset      = BXStd_SafeGPCGetInt( 'offset' );
if( empty($gpc_species['plain']) )  BXStd_HTTPRedirect( HPD_PAGE_START );



function html_before_results() {
    global $gpc_species;

    echo "<HTML><HEAD></HEAD><BODY bgcolor='#ffffff'>";
    echo "<H3>{$gpc_species['plain']} Cultivars</H3>";
}

function html_after_results() {
    echo "</BODY></HTML>";
}

function format_cultivar( $record, $parms ) {
    global $gpc_species;
    if( !empty( $record['pname'] ) && !empty( $record['iname'] ) ) {
        echo "*&nbsp;<A HREF='cvdetail.php?species=" . urlencode($gpc_species['plain']) .
             "&cultivar=" . urlencode($record['pname']) . "' target='cultivar'>" . $record['iname'] . "</A><BR>";
    }
}

function start_cultivar_list() {
    global $offset;
    global $records;
    global $limit;
    global $gpc_species;

    if( $records > $limit ) {
        echo 'There are ' . ceil( $records / $limit ) . " pages in total.<P>";
    }
    if( empty( $offset ) ) {
        // cultivar=NULL means Unnamed, not unspecified
        echo "*&nbsp;<a href='cvdetail.php?species=". urlencode($gpc_species['plain']) ."&cultivar=NULL' target='cultivar'>(Unnamed)</a><BR>";
    }
}

function stop_cultivar_list() {
}

function record_count_hook( $records ) {
    echo "<P>$records in database</P>";
}


//for( $i = 0; !empty( $list[$i] ); $i++ ) {
    //if( !empty( $list[$i]['pname'] ) && !empty( $list[$i]['iname'] ) ) {
        //echo "*&nbsp;" . '<a href="cvdetail.php?species=' . urlencode( $gpc_species ) .
            //'&cultivar=' . urlencode( $list[$i]['pname'] ) . '" target="cultivar">'
            //. $list[$i]['iname'] . "</a><BR>";
    //}
//}


$opts['format'] = "cultivar";
$opts['recordcountfunc'] = "record_count_hook";
$opts['numpagelinks'] = 4;
$opts['limit'] = $limit;
$opts['offset'] = $offset;
$args['species'] = $gpc_species['db'];
paged_result_set( "cultivarlist", $args, $opts );
?>
