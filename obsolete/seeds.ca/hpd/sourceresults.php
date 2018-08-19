<?

// shorter "no results" blurb.  Current one doesn't fit well into the left-hand frame


/* List the results of the Source Search.
 * The list is controlled by $limit and $offset
 */

include_once( "../site.php" );
include_once( SITEINC."sitedb.php" );
include_once( HPD_ROOT."_hpd.php" );
include_once( HPD_ROOT."_func.php" );


$gpc_species = BXStd_SafeGPCGetStr( 'species' );
$limit       = BXStd_SafeGPCGetInt( 'limit' );      if( empty($limit) )  $limit = 100;
$offset      = BXStd_SafeGPCGetInt( 'offset' );

/* Translate "NULL" to -1 because in PHP "any string"==0 is always true, so we cannot differentiate "NULL" and intval("0")
 */
$sodc        = @$_REQUEST['sodc'];                  $sodc     = ($sodc=="NULL"     ? -1 : intval($sodc));
$commerce    = @$_REQUEST['commerce'];              $commerce = ($commerce=="NULL" ? -1 : intval($commerce));
$genebank    = @$_REQUEST['genebank'];              // $genebank is validated by explicit comparisons below

if( empty($gpc_species['plain']) )  BXStd_HTTPRedirect( HPD_PAGE_START );


function html_before_results() {
    echo "<CENTER>";
}

function html_after_results() {
    echo "</CENTER>";
    //std_footer();
}

function record_count_hook( $records ) {
    global $gpc_species, $sodc, $commerce, $genebank;

    echo "<P><FONT size=+1><B>".($gpc_species['plain']=="NULL"?"All plants":$gpc_species['plain'])." that are ";
    if( $sodc == -1 && $commerce == -1 && $genebank == "NULL" ) {
        echo "listed in our database";
    } else {
        echo "<UL>";
        if( $sodc     != -1 )  echo "<LI>".($sodc ?     "" : "not ")."grown by Seeds of Diversity members</LI>";
        if( $commerce != -1 )  echo "<LI>".($commerce ? "" : "not ")."available commercially</LI>";
        if( $genebank != "NULL" ) {
            if( $genebank == "neither" ) {
                echo "<LI>not maintained by the Canadian or U.S. gene bank</LI>";
            } else {
                echo "<LI>maintained by the ";
                switch( $genebank ) {
                    case "pgrc":    echo "Canadian";            break;
                    case "npgs":    echo "U.S.";                break;
                    case "both":    echo "Canadian and U.S.";   break;
                    case "either":
                    default:        echo "Canadian and/or U.S.";break;
                }
                echo " gene bank</LI>";
            }
        }
        echo "</UL>";
    }
    echo "</FONT></P>";

    printf( "<P>Your search matched %d record(s).", $records );
    if( $records > 100 ) {
        echo '<BR><B>Try making your search <a href="sourcesearch.php?help=1" target="_top">more specific</a> to reduce the number of matching records.</B></P>';
    } else if( $records == 0 ) {
        ?>
        <BR><BR>
        <table width="50%" align="center">
        <tr><td>
        <B>Note:</B> This only searched through cultivars for which source information is
        available.  For many cultivars the source information is unknown or has not
        been entered in the database.  Click
        <?
        echo '<a href="'.HPD_PAGE_CVFRAME.'?species=' . urlencode($gpc_species['plain']) . '" target="_top">';
        ?>
        here</a> to view all cultivars of <B><? echo $gpc_species['plain'] ?></B> in the database,
        including those for which no source information is available.
        </td></tr></table>
        <?
    } else {
        echo "</P>";
    }
}

function format_seedsource( $record, $parms ) {
//  echo "<TR><TD><A href='".HPD_PAGE_CVFRAME."?species=". urlencode($record["species"]) ."'>${record["species"]}</A> : ";
    echo "<TR><TD>${record["species"]} : ";
//There isn't an oname in sourcelist anymore
//  if( strcasecmp( $record["oname"], $record["pname"] ) != 0 ) {
//      echo $record["oname"] . " (";
//  }
    echo "<A href='".HPD_PAGE_CVDETAIL."?species=". urlencode($record["species"]) .
         "&cultivar=". urlencode($record["pname"]) ."' target=cultivar>${record["pname"]}</A>";
    echo "</TD></TR>";
}

function start_seedsource_list() {
    echo '<TABLE>';
}

function stop_seedsource_list() {
    echo '</TABLE>';
}



$opts['format'] = "seedsource";
$opts['recordcountfunc'] = "record_count_hook";

$conds = array();
if( $gpc_species['plain'] != "NULL" ) {
    $conds[] = "(species = '".$gpc_species['db']."')";
}

switch( $sodc ) {
    case -1:    // not specified
        break;
    case 0:
        $conds[] = "(sodc = 0)";
        break;
    default:
        $conds[] = "(sodc > 0)";
}

switch( $commerce ) {
    case -1:    // not specified
        break;
    case 10:
        $conds[] = "(gsi >= 10)";
        break;
    default:
        $conds[] = "(gsi <= $commerce)";
}

switch( $genebank ) {
    case "NULL":    break;  // not specified
    case "both":    $conds[] = "( pgrc AND npgs )";         break;
    case "either":  $conds[] = "( pgrc OR npgs )";          break;
    case "neither": $conds[] = "( NOT ( pgrc OR npgs ))";   break;
    case "pgrc":
    case "npgs":    $conds[] = "( $genebank )";             break;  // Unsafe if not validated by the case statements
}

$cond = join( " AND ", $conds );
if( empty( $cond ) ) {
    $cond = "1 = 1";
}
$opts['limit'] = $limit;
$opts['offset'] = $offset;
$args['condition'] = $cond;
paged_result_set( "sourcesearch", $args, $opts );
?>
