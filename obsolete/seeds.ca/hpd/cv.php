<?

// When a search yields no results in the left-hand frame, maybe there's a way for that frame to generate a reload to the
// _top frame, telling it to show the same search but to put appropriate "no results" content in the right-hand frame.



/* Top-level frame set for cultivar display
 *
 * Invocations:
 *      species=$s              :   cultivars of $s listed in left frame, linked to cvdetail in right frame
 *      species=$s&cultivar=$c  :   same as above but cvdetail initialized to $c
 *      query=$q                :   query results on cultivars listed in left frame, linked to cvdetail in right frame
 */

include_once( "../site.php" );
include_once( "_hpd.php" );


$species  = @$_REQUEST['species'];
$cultivar = @$_REQUEST['cultivar'];
$query    = @$_REQUEST['query'];
$qtype    = @$_REQUEST['qtype'];    // only used with query

if( get_magic_quotes_gpc() ) {
    // strip slashes from parms because magic_quotes is going to re-escape quotes in the child frames
    $species  = stripslashes( $species );
    $cultivar = stripslashes( $cultivar );
    $query    = stripslashes( $query );
    $qtype    = stripslashes( $qtype );
}



if( empty($species) && empty($qtype) )  BXStd_HTTPRedirect( HPD_PAGE_START );

if( $qtype == 'catalog' )  { include( "search.php" );  exit; }


?>

<frameset rows="165,*" border="0" frameborder="no" framespacing="0" border="0" resize="no">
    <frame name="title" src="title.php" border="0" frameborder="no" framespacing="0" border="0" scrolling="no">
    <frameset cols="*,3*" border="0" frameborder="no" framespacing="0" border="0" resize="no">
        <?
        if( !empty($qtype) ) {
            if( $qtype == 'source' ) {
                $sodc = @$_REQUEST['sodc'];             // don't have to stripslashes or urlencode these because they
                $commerce = @$_REQUEST['commerce'];     // shouldn't have quotes and sourceresults.php strips them safely
                $genebank = @$_REQUEST['genebank'];

                $url1 = "sourceresults.php?qtype=$qtype&species=".urlencode($species)."&sodc=$sodc&commerce=$commerce&genebank=$genebank";
                $url2 = "cvdetail.php?qtype=$qtype";
            } else {
                $url1 = "search.php?query=".urlencode($query)."&qtype=$qtype";
                $url2 = "cvdetail.php?qtype=$qtype";
            }
        } else {
            $url1 = "cvlist.php?species=". urlencode($species) ."&limit=500";
            $url2 = "cvdetail.php?species=". urlencode($species) ."&cultivar=". urlencode($cultivar);
        }
        echo "<frame name='cultivarlist' src='$url1' frameborder='no' framespacing='0' border='0' scrolling='yes'>";
        echo "<frame name='cultivar'     src='$url2' frameborder='no' framespacing='0' border='0'>";
        ?>
    </frameset>
</frameset>
<noframes>
    <P>This page uses frames.</P>
</noframes>
