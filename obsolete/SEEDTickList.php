<?

/* SEEDTickList
 *
 * Copyright (c) 2007 Seeds of Diversity Canada
 *
 * Manage lists of process control statements (Ticks).
 *
 * A TickList looks like this:
 *
 *      TickA:parms\n
 *      TickB:parms\n
 *      TickC:parms\n
 *      TickA:parms\n
 *      ...
 *
 * Prefixes don't have to be unique. Order is retained when Ticks are edited.
 */

function SEEDTickList_GetRAParmsByTickPrefix( $sTL, $prefix )
/************************************************************
    return array of parms where Tick starts with $prefix

 */
{
    $raRet = array();
    foreach( explode( "\n", $sTL ) as $t ) {
        if( substr($t, 0, strlen($prefix)) == $prefix ) {
            $r = explode( ":", $t );
            $raRet[] = trim(@$r[1]);
        }
    }
    return( $raRet );
}

?>
