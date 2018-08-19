<?
// here are all the SQL queries.  Words with underscores at beginning and
// end (like _this_) are replaced by actual arguments when the query is
// executed.

$querylist['specieslist']     = "SELECT species, count(pname) as number from hvd_pnames group by species order by species _bounds_";
$querylist['cultivarlist']    = "SELECT iname, species, pname from hvd_pnames where species = '_species_' order by iname _bounds_";
$querylist['seedsearch']      = "SELECT species, pname, oname from hvd_onames where _condition_ order by species, pname, oname _bounds_";
$querylist['catalogsearch']   = "SELECT * FROM hvd_refs where _condition_ _bounds_";
$querylist['sourcesearch']    = "SELECT * FROM hvd_sourcelist where _condition_ order by species, pname _bounds_";
$querylist['cultivar']        = "SELECT * FROM hvd_catlist WHERE species='_species_' AND pname='_cultivar_' and oname = '*'";
$querylist['catalogrefs']     = "SELECT * FROM hvd_catlist t1,hvd_refs t2 WHERE species='_species_' AND pname='_cultivar_' AND type='seedcat' and t1.refcode = t2.refcode ORDER BY refdate";
$querylist['miscrefs']        = "SELECT * FROM hvd_catlist t1,hvd_refs t2 WHERE species='_species_' AND pname='_cultivar_' AND type <> 'seedcat' AND oname <> '*' and t1.refcode = t2.refcode ORDER BY refdate";
$querylist['memberrefs']      = "SELECT * FROM hvd_sodclist WHERE species='_species_' AND pname='_cultivar_' ORDER BY year";
$querylist['catalog']         = "SELECT * FROM hvd_refs WHERE refcode = '_catalog_'";
$querylist['cataloglist']     = "SELECT * FROM hvd_refs WHERE type='seedcat' ORDER by _order_ _bounds_";
$querylist['catalogcontents'] = "SELECT * FROM hvd_catlist WHERE refcode='_catalog_' ORDER BY species, oname";
$querylist['sourceinfo']      = "SELECT * FROM hvd_sourcelist WHERE species='_species_' and pname='_cultivar_'";
$querylist['sourcecount']     = "SELECT count(*) FROM hvd_sourcelist WHERE species='_species_'";


function garg_replace( $matches )
/********************************
    Replace a variable name by its value

    Cannot escape quotes in parms here because there are expressions that are replaced into the queries - they have quoted parms that should not be escaped.
 */
{
    global $gargs;
    $varname = substr( $matches[0], 2, strlen( $matches[0] ) - 3 );
    // echo $varname . "<BR>";
    $value = substr( $matches[0], 0, 1 ) . $gargs[$varname];
    // . substr( $matches[0], strlen($matches[0]) - 1 );
    //echo $value . "<BR>";
    return $value;
}


function resolve_query( $query, $args )
/**************************************
 */
{
    global $querylist;
    global $gargs;

    $query = $querylist[$query];
    if( empty( $query ) ) {
        die( "<P>Query not found.</P>" );
    }

    /* Replace all variable names by the values that were passed in
     */
    if( function_exists( "preg_replace_callback" ) ) {
        $gargs = $args;
        for( $i = 0; $i < count( $args ); $i++ ) {
            $query = preg_replace_callback( "/\W_\w*_/", "garg_replace", $query, 1 );
            //echo $query . "<BR>";
        }
    } else {
        // preg_replace_callback isn't available; better use something else.
        for( $i = 0; $i < count( $args ); $i++ ) {
            if( ereg( "[^a-zA-Z0-9_](_[a-zA-Z0-9]{1,}_)([^a-zA-Z0-9_]|$)", $query, $regs ) ) {
                //echo $regs[1] . "<BR>";
                $varname = substr( $regs[1], 1, strlen( $regs[1] ) - 2 );
                //echo $varname . "<BR>";
                $query = str_replace( $regs[1], $args[$varname], $query );
                //echo $query . "<BR>";
            }
        }
    }
    return $query;
}


function query( $query, $args )
/******************************
 */
{
    //echo $query . "<BR>";
    $query = resolve_query( $query, $args );
    //echo $query . "<BR>";
    if( !($dbc = db_open( $query )) ) {
        echo( "<P>Sorry, unable query database.  Please try again or contact our ".MAILTO_Webmaster." for assistance</P>" );
        if( STD_isLocal ) {
            echo "<FONT color=red>".db_errmsg($query)."</FONT>";
        }
        return false;
    }
    $list = array();
    for( $i = 0; $ra = db_fetch( $dbc ); $i++ ) {
        $list[$i] = $ra;            // in a separate step so we don't store a NULL element in $ra
    }
    return( $list );
}


function querysize( $query, $args )
/**********************************
 */
{
    $query = resolve_query( $query, $args );
    if( !($result = db_exec( $query )) ) {
        return false;
    }
    $rows = db_numrows( $result );
    return( $rows );
}


function paged_result_set( $query, $args, $opts )
/************************************************
 */
{
    global $records, $PHP_SELF;

    $offset = $opts['offset'];  if( empty($offset) )  $offset = 0;
    $limit  = $opts['limit'];   if( empty($limit) )   $limit = 20;

    html_before_results();
    if( empty( $records ) ) {
        $args['bounds'] = "";
        $records = querysize( $query, $args );
    }
    $bounds = "limit $offset, $limit";

    $pages = floor( $records / $limit );
    if( $records % $limit > 0 )
    {
        $pages += 1;
    }

    if( !empty( $opts["recordcountfunc"] ) ) {
        $opts["recordcountfunc"]( $records );
    } else if( !empty( $opts["recordcount"] ) ) {
        printf( $opts["recordcount"], $records );
    }

    // create page number links, if more than one page
    if( $pages > 1 ) {
        $numpagelinks = 10;
        if( !empty( $opts['numpagelinks'] ) ) {
            $numpagelinks = $opts['numpagelinks'];
        }

        if( !empty( $opts["pagelabel"] ) ) {
            echo $opts["pagelabel"];
        } else {
            echo "Page: ";
        }
        $curpage = floor( $offset / $limit );

        // unless we're on the first page, put a "prev" link
        if( $offset > 0 ) {
            $vars = "limit=$limit&records=$records&offset=". ($offset - $limit);
            echo '&lt;<a href="' . phpself_modify_parms( $vars ) . '">Prev</a> ';
        }

        for( $i = max( 0, min( $pages - $numpagelinks, $curpage - ($numpagelinks/2) ) ); $i < min( $pages, max( $curpage + ($numpagelinks/2)+1, $numpagelinks ) ); $i++ ) {
            if( $i == $curpage ) {
                echo "<B>" . ( $i + 1 ) . "</B> ";
            } else {
                $vars = "limit=$limit&records=$records&offset=". ($i * $limit);
                echo '<a href="' . phpself_modify_parms( $vars ) . '">';
                echo ( $i + 1 ) . "</a>&nbsp;";
            }
        }
        // unless we're on the last page, put a "next" link
        if( $offset + $limit < $records ) {
            $vars = "limit=$limit&records=$records&offset=". ($offset + $limit);
            echo '<a href="' . phpself_modify_parms( $vars ) . '">Next</a>&gt;<BR>';
        }
    }
    $args['bounds'] = $bounds;
    $list = query( $query, $args );

    if( !empty( $opts['format'] ) ) {
        $formatfunc = 'format_' . $opts['format'];
        $startlist = 'start_' . $opts['format'] . '_list';
        $stoplist = 'stop_' . $opts['format'] . '_list';
    } else {
        $formatfunc = 'format_record';
        $startlist = 'start_list';
        $stoplist = 'stop_list';
    }

    if( !empty( $list[0] ) ) {
        $startlist();
        $p['n'] = count($list);
        for( $i = 0; $i < $limit && !empty( $list[$i] ); $i++ ) {
            $p['i'] = $i;
            $formatfunc( $list[$i], $p );
        }
        $stoplist();
    }
    html_after_results();
}


function parse_querystring( $qstring, &$vars ) {

// isn't this just the same as parse_str() - added in php 4.0.3

    $params = urldecode( $qstring );
    $pairs = split( "&", $params );
    for( $i = 0; !empty( $pairs[$i] ); $i++ ) {
        $varname = strtok( $pairs[$i], "=" );
        $value = strtok( "=" );
        //echo "Var = $varname, Value = $value <BR>";
        $vars[$varname] = $value;
    }
}


// this function constructs a URL for the current page, and
// automatically adds the variables listed so that they are preserved.
function makephpself( $params )
{
//  global $sessionvars;
    //echo $params . "<BR>";
    $vars = array();

//  for( $i = 0; !empty( $sessionvars[$i] ); $i++ ) {
//      $varname = $sessionvars[$i];
//      global $$varname;
//  }

    // outsourced querystring parsing to function and added parsing of $QUERY_STRING
    // values in $QUERY_STRING get overwritten by $params. This allows us to pass
    // old values on while retaining the ability to negate them.
    parse_querystring( $_SERVER['QUERY_STRING'], $vars );
    parse_querystring( $params , $vars );


    // generally, PHP_SELF will not have question marks in it. By default,
    // PHP_SELF is the name of the file referenced without any of the
    // querystring parameters, but sometimes the user may want to specify
    // the parameters, and then we need to be able to see that they did.
    $url = $_SERVER['PHP_SELF'] . "?";

//  for( $i = 0; !empty( $sessionvars[$i] ); $i++ ) {
//      $varname = $sessionvars[$i];
//      if( $vars[ $varname ] != "" ) {
//          $url .= $varname . '=' . $vars[ $varname ] . '&';
//      } else if( $$varname != "" ) {
//          $url .= $varname . '=' . $$varname . '&';
//      }
//  }


    // and add in any additional parameters not noticed by sessionvars
    while( list($key, $value) = each($vars) ) {
        if( ($vars[ $key ] != "") /* && ( $$key == "" ) */ ) {          // this is supposed to allow removal of QUERY_STRING vars, by setting the global var to "".
            $url .= "$key=$value&";                                     //     could be done by setting that var to "" in an array passed into this function.
        }
    }
    return( $url );
}


function phpself_modify_parms( $parms )
/**************************************
    Return an URL to the current page, with the current query_string modified by $parms.
    $parms is an url-style parm string

    $parms with non-empty values are added/changed in the URL.
    $parms that are empty are removed from the URL.
 */
{
    $ra_parms = array();
    parse_querystring( $parms, $ra_parms );
    return( phpself_modify_parms_ra( $ra_parms ) );
}

function phpself_modify_parms_ra( $ra_parms )
/********************************************
    Same as phpself_modify_parms, but takes an array of parms
 */
{
    $vars = array();
    $url = $_SERVER['PHP_SELF'] . "?";

    parse_querystring( $_SERVER['QUERY_STRING'], $vars );

    while( list($k, $v) = each($ra_parms) ) {
        $vars[$k] = $v;
    }

    $b = false;
    while( list($k, $v) = each($vars) ) {
        if( !empty($v) ) {
            if( $b ) $url .= "&";
            $b = true;
            $url .= "$k=$v";
        }
    }
    return( $url );
}


?>
