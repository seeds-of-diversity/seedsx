<?php

/* SEEDStd
 *
 * Copyright 2006-2020 Seeds of Diversity Canada
 *
 * Standard functions useful to most applications
 */


function SEEDStd_HTTPRequest( $host, $page, $raReqParms )
{
    $sResponseHeader = "";
    $sResponseContent = "";

    $reqparms = SEEDStd_ParmsRa2URL( $raReqParms );

    $errno = 0;
    $errstr = "";
    $ok = false;

    $sMessage = "POST $page HTTP/1.0\r\n"
               ."Host: $host\r\n"                      // this didn't used to be required by older servers but now seems necessary
// HTTP/1.1 requires Host:, Connection:close, chunked transfer encoding, and handle the 100 response
               //."Accept: text/plain\r\n"
               ."Content-Type: application/x-www-form-urlencoded\r\n"
               ."Content-Length: ".strlen($reqparms)."\r\n"
               ."\r\n"
               .$reqparms;
//    $sMessage = "GET /int/traductions.php?mode=REST&ns=SED&k=MSD+title&lang=EN HTTP/1.1\r\nHost: \r\n\r\n";

    if( ($fp = @fsockopen( $host, 80, $errno, $errstr, 30 )) ) {  // suppress php error messages when connections fail, host not found, etc
        fputs( $fp, $sMessage );

        $bHeader = true;
        $bFirst = true;
        while( !feof($fp) ) {
            // get each line of the header and content.  The last line is the response.
            $s = fgets( $fp, 1024 );
            if( $bHeader ) {
                $sResponseHeader .= $s;
                if( trim($s) == "" )  $bHeader = false;
            } else {
                $sResponseContent .= ($bFirst ? "" : "\n").trim($s);
                $bFirst = false;
            }
        }
        fclose ($fp);
        $ok = true;
//var_dump($host,$sMessage,$sResponseHeader,$sResponseContent);exit;
    } else {
        //$this->log( "HTTP Error: $errno, $errstr\n$req" );
    }
    return( array( $ok, $sResponseHeader, $sResponseContent ) );
}

function SEEDStd_Ent( $s )
/*************************
    Since the default charset used by htmlentities depends on the php version, standardize the charset by using this instead
 */
{
    return( htmlentities( $s, ENT_QUOTES, 'cp1252') );
}

function SEEDStd_HSC( $s )
/*************************
    Since the default charset used by htmlspecialchars depends on the php version, standardize the charset by using this instead
 */
{
    return( htmlspecialchars( $s, ENT_QUOTES, 'cp1252') );
}


function SEEDStd_StrNBSP( $s, $n = 0 )
/*************************************
    Replace spaces in $s with "&nbsp;", then append $n x "&nbsp;"

    e.g.    ("foo bar")     returns "foo&nbsp;bar"
            ("foo bar",5)   returns "foo&nbsp;bar&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
            ("",5)          returns "&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;"
 */
{
    if( !empty($s) ) {
        $s = str_replace( " ", "&nbsp;", $s );
    } else {
        $s = "";
    }
    while( $n-- > 0 ) $s .= "&nbsp;";
    return( $s );
}

function SEEDStd_Range( $i, $floor = NULL, $ceiling = NULL )    // called SEEDCore_Bound() now because Range is a specific data structure
/***********************************************************
    Constrain $i within the range of floor and ceiling
 */
{
    if( $floor   !== NULL && $i < $floor )   $i = $floor;
    if( $ceiling !== NULL && $i > $ceiling ) $i = $ceiling;

    return( $i );
}

function SEEDStd_ExpandIfNotEmpty( $s, $sTemplate, $bEnt = true )
/****************************************************************
    Return template string with all [[]] replaced by $s, if $s is not empty
 */
{
    if( !empty($s) )  return( str_replace( "[[]]", ($bEnt ? SEEDStd_HSC($s) : $s), $sTemplate ) );
}

/**
 * Replace "[[foo]]" in template with $ra['foo']
 */
function SEEDStd_ArrayExpand( $ra, $sTemplate, $bEnt = true )
/************************************************************
 */
{
    foreach( $ra as $k => $v ) {
        $sTemplate = str_replace( "[[$k]]", ($bEnt ? SEEDStd_HSC($v) : $v), $sTemplate );
    }
// recursive expansions are not implemented
    return( $sTemplate );

/*
// isn't this the same thing as str_replace( "[[$k]]", ($bEnt ? ...), $sTemplate )

	foreach( $ra as $k => $v ) {
		// Also remember to repeat the whole process until no matches found, to allow recursive expansions
		while( ($s1 = strpos( $sTemplate, "[[$k]]")) !== false ) {
			$sTemplate = substr( $sTemplate, 0, $s1 )
			.($bEnt ? htmlspecialchars($v,ENT_QUOTES) : $v)
			.substr( $sTemplate, $s1+strlen($k)+4 );
		}
	}
*/

	/* Did not work for HTML templates that contained processing instructions with ]]
	for(;;) {
        $s1 = strpos( $sTemplate, "[[" );
        $s2 = strpos( $sTemplate, "]]" );
        if( $s1 === false || $s2 === false )  break;
        $k = substr( $sTemplate, $s1 + 2, $s2 - $s1 - 2 );
        if( empty($k) ) break;

        $sTemplate = substr( $sTemplate, 0, $s1 )
                    .($bEnt ? SEEDStd_HSC(@$ra[$k]) : @$ra[$k])
                    .substr( $sTemplate, $s2+2 );
	}
	*/

	return( $sTemplate );
}


/**
 *  if $raAllowed contains 1 value, then $raParms[$k] is unconstrained (except for empty or !isset) and $raAllowed[0] is the default:
 *      { Return $raParms[$k] if isset() and not empty, or isset() and empty and $bEmptyAllowed : else return $raAllowed[0] }
 *
 *  if $raAllowed contains >1 values, then $raParms[$k] is constrained to that set and $raAllowed[0] is the default ($bEmptyAllowed is not used):
 *      { Return $raParms[$k] if it is in $raAllowed; return $raAllowed[0] if $raParms[$k] is not in that list or not set }
 */
function SEEDStd_ArraySmartVal( $raParms, $k, $raAllowed, $bEmptyAllowed = true )
/********************************************************************************
    raParms is a set of values provided from some input e.g. function argument parms, http parms, user input, etc
    raAllowed is the set of all allowed values (or a single default value)

    if $raAllowed contains 1 value:
        if $bEmptyAllowed
            $raParms[$k] is allowed to be any value, including empty
            if $raParms[$k] is not set, return $raAllowed[0]
        else
            $raParms[$k] is allowed to be any value, except empty
            if $raParms[$k] is empty or not set, return $raAllowed[0]


    if $raAllowed contains >1 values:
        $raParms[$k] is constrained to that set of values
        if $raParms[$k] is not set, return $raAllowed[0]
        if $raParms[$k] is allowed, return it
        if $raParms[$k] is not allowed, return $raAllowed[0]
 */
{
    if( !isset($raParms[$k]) )  return( $raAllowed[0] );

    if( count($raAllowed) == 1 ) {
        return( (empty($raParms[$k]) && !$bEmptyAllowed ) ? $raAllowed[0] : $raParms[$k] );
    } else {
        return( SEEDStd_SmartVal( $raParms[$k], $raAllowed ) );
    }
}

/**
 * $v is constrained to the set of $raAllowed. Return $v if it is in the array or $raAllowed[0] if not
 */
function SEEDStd_SmartVal( $v, $raAllowed )
/******************************************
    Return $v if it is in $raAllowed.  Else return $raAllowed[0]
 */
{
    return( in_array( $v, $raAllowed, true ) ? $v : $raAllowed[0] );
}

function SEEDSafeGPC_GetStrPlain( $parm, $raGPC = null, $bGPC = true )
/*********************************************************************
    Use this only if the parm is just for display.  If it is ever used in a db query, use *Str->DB()

    if raGPC==null, always use _REQUEST and MagicStripSlashes
    if raGPC is provided, the caller can use bGPC to indicate whether it is _REQUEST/_GET/_POST/_COOKIE/etc
        or just an ordinary array

 */
{
    $s = ($raGPC==null ? @$_REQUEST[$parm] : @$raGPC[$parm] );
    return( ($raGPC==null || $bGPC) ? SEEDSafeGPC_MagicStripSlashes( $s ) : $s );
}

function SEEDSafeGPC_GetStrDB( $parm, $raGPC = NULL )
/****************************************************
    This is the same as SEEDSafeGPC_Get(parm)['db'] but that syntax is not allowed in PHP until sometime later
 */
{
    $s = ($raGPC !== NULL ? @$raGPC[$parm] : @$_REQUEST[$parm]);
    return( SEEDSafeGPC_MagicAddSlashes( $s ) );
}

function SEEDSafeGPC_GetInt( $parm, $raGPC = NULL )
/**************************************************
 */
{
    return( intval( $raGPC ? @$raGPC[$parm] : @$_REQUEST[$parm] ) );
}

function SEEDSafeGPC_Smart( $k, $raAllowed )
/*******************************************
    $k = the name of a GPC parm
    $raAllowed = allowable values ([0]=default)

    if count($raAllowed) == 1:    any non-empty values are accepted, but empty input forces the given value
    if count($raAllowed) > 1:     values are constrained to those in the array, empty input defaults to the first value
 */
{
    $p = SEEDSafeGPC_GetStrPlain($k);

    if( !$p )  return( $raAllowed[0] );

    return( count($raAllowed) == 1 ? $p : SEEDStd_SmartVal( $p, $raAllowed ) );
}

function SEEDSafeGPC_MagicAddSlashes( $s )
/*****************************************
    Escape quotes in the given string if magic quotes are turned off
 */
{
    return($s); // no longer available return( get_magic_quotes_gpc() ? $s : addslashes($s) );
}

function SEEDSafeGPC_MagicStripSlashes( $s )
/*******************************************
    Unescape quotes in the given string if magic quotes are turned on
 */
{
    return($s); // no longer available return( get_magic_quotes_gpc() ? stripslashes($s) : $s );
}

function SEEDStd_UniqueId( $iTruncateLen = 0 )
/*********************************************
    An unguessable string. From PHP docs for uniqid.

    Optional truncation for a less unique, less unguessable, but shorter string
 */
{
    $s = md5(uniqid(rand(), true));

    return( $iTruncateLen ? substr( $s, 0, $iTruncateLen ) : $s );
}

function SEEDStd_TagStrAdd( $str, $tag, $raMutex = array() )
/***********************************************************
    $str is a string of space-delimited tags like " A B C "
    Add $tag to the list, if it is not there already
    $sMutex is a list of tags that are mutually exclusive to $tag, to be removed from $str.  It may or may not contain $tag.
 */
{
    if( strstr( $str, $tag ) !== false ) {
        /* tag is already there
         */
        $sOut = $str;
    } else if( !empty($str) && count($raMutex) ) {
        /* remove mutex tags and add new tag
         */
        $sOut = " ";
        $ra = explode(" ", $str);
        foreach( $ra as $v ) {
            if( $v && !in_array( $v, $raMutex ) ) {
                $sOut .= $v." ";
            }
        }
        $sOut .= $tag." ";
    } else {
        $sOut = $str.(empty($str) ? " " : "").$tag." ";
    }
    return( $sOut );
}

function SEEDStd_ParseAttrs( $sAttrs )
/*************************************
    Given the string ( border=1 cellpadding='5' style="background-color:green;" )
    return the array( 'border'=>'1', 'cellpadding'=>'5', 'style'=>'background-color:green;' )

    This is useful for marshalling and modifying sets of attributes, and rewriting them in a
    consistent format e.g. XHTML compliantly
 */
{
    $raAttrs = array();
    $matches = array();

    $found = preg_match_all(
                '#\s*([^\s=]+)\s*=\s*(\'([^<\']*)\'|"([^<"]*)"|([^\s]*))#',   // not sure why the ^< are here
                $sAttrs, $matches, PREG_SET_ORDER );
    if( $found != 0 ) {
        foreach( $matches as $ra ) {
            /* $ra[0] is the whole matched string segment
             * $ra[1] is the attr name
             * $ra[2] is the whole value string
             * $ra[3] is the value inside '' if any
             * $ra[4] is the value inside "" if any
             * $ra[5] is the value with no '' or "" if any
             */
            $raAttrs[$ra[1]] =  !empty($ra[3]) ? $ra[3] :
                               (!empty($ra[4]) ? $ra[4] :
                                                 $ra[5]);
        }
    }
    return( $raAttrs );
}


function SEEDStd_ParmsRA2URL( $raParms )
/***************************************
    Return an urlencoded string containing the parms in the given array
 */
{
    $s = "";
    foreach( $raParms as $k => $v ) {
        if( !empty($s) )  $s .= "&";
        $s .= $k."=".urlencode($v);
    }
    return( $s );
}

function SEEDStd_ParmsURL2RA( $sUrlParms, $bDecode = true )
/**********************************************************
    Return an array containing the parms in the given urlencoded string
 */
{
    $raOut = array();
    if( !empty($sUrlParms) ) {   // the code below works properly with an empty string, but with display_errors turned on it throws a notice at the second explode
        $ra = explode( "&", $sUrlParms );
        foreach( $ra as $m ) {
            @list($k,$v) = explode( '=', $m, 2 );  // list() needs a @ because an empty string or a string
                                                   // with no '=' throws a notice that the second index doesn't exist
            if( $k )  $raOut[$k] = $bDecode ? urldecode($v) : $v;
        }
    }
    return( $raOut );
}

function SEEDStd_ParmsURLGet( $sUrlParms, $k )
/*********************************************
    Return the named parm from the string
 */
{
    $ra = SEEDStd_ParmsURL2RA( $sUrlParms );
    return( @$ra[$k] );
}

function SEEDStd_ParmsURLAdd( $sUrlParms, $k, $v )
/*************************************************
    Return an array with a parm added or changed
 */
{
    $ra = SEEDStd_ParmsURL2RA( $sUrlParms );
    $ra[$k] = $v;
    return( SEEDStd_ParmsRA2URL( $ra ) );
}

function SEEDStd_ParmsURLRemove( $sUrlParms, $k )
/************************************************
    Return an array with a parm removed
 */
{
    $ra = SEEDStd_ParmsURL2RA( $sUrlParms );
    if( isset($ra[$k]) )  unset($ra[$k]);
    return( SEEDStd_ParmsRA2URL( $ra ) );
}

function SEEDStd_BookEnds( $s, $sB1, $sB2 )
/******************************************
   Return an array of the portion(s) of $s contained within b1 and b2
   e.g. b1=<tag>, b2=</tag> "don't return me <tag>return me</tag>don't return me"
        b1=[[, b2=]] "ignore me [[return me]] ignore me [[return me too]]"
 */
{
    if( empty($s) || empty($sB1) || empty($sB2) ) return( array() );

    $ra = array();
    while( ($p1 = strpos($s, $sB1)) !== false && ($p2 = strpos($s, $sB2)) !== false && $p1<$p2 ) {
        $ra[] = substr( $s, $p1 + strlen($sB1), $p2 - $p1 - strlen($sB1) );
        $s = substr( $s, $p2 + strlen($sB2) );
    }
    return( $ra );
}

function SEEDStd_EnumTuplesUnpack( $raIn, $raKeys, $raParms = array() )
/**********************************************************************
    Convert an enumerated-tuple array into an unordered array of tuples

    Where raKeys=array('a','b','c') transform raIn  = array( 'a1'=>1, 'b1'=>2, 'c1'=>3, 'a2'=>4, 'c2'=>5 )
                                           to raOut = array( array('a'=>1, 'b'=>2, 'c'=>3),
                                                             array('a'=>4, 'b'=>null, 'c'=>5) )

    It is best to avoid keys that are prefixes of other keys.

    raParms:
        bGPC       = stripslashes from the input values
        bSkipEmpty = ignore empty tuples
 */
{
    $raOut = array();

    $raTmp = array();
    foreach( $raIn as $k => $v ) {
        foreach( $raKeys as $sKey ) {
            if( substr( $k, 0, strlen($sKey) ) == $sKey ) {
                $n = intval( substr($k,strlen($sKey)) );
                $raTmp[$n][$sKey] = @$raParms['bGPC'] ? SEEDSafeGPC_MagicStripSlashes($v) : $v;
            }
        }
    }
    foreach( $raTmp as $ra ) {
        $raO = array();
        $bEmpty = true;
        foreach( $raKeys as $sKey ) {
            $raO[$sKey] = isset($ra[$sKey]) ? $ra[$sKey] : NULL;
            if( !empty($ra[$sKey]) )  $bEmpty = false;
        }
        if( @$raParms['bSkipEmpty'] && $bEmpty )  continue;  // all values of the given keyset are either absent or empty

        $raOut[] = $raO;
    }

    return( $raOut );
}


function SEEDStd_EnumTuplesPack( $raIn )
/***************************************
    Convert an unordered array of tuples into an enumerated-tuple array

    Where raIn = array( array('a'=>1, 'b'=>2, 'c'=>3),
                        array('a'=>4,         'c'=>5) )
    return raOut = array( 'a1'=>1, 'b1'=>2, 'c1'=>3, 'a2'=>4, 'c2'=>5 )

    It is best to avoid keys that are prefixes of other keys.

    N.B. The enumeration numbers are meaningless, apart from grouping the tuple values
 */
{
    $raOut = array();

    $i = 1;
    foreach( $raIn as $ra ) {
        foreach( $ra as $k => $v ) {
            $raOut[$k.$i] = $v;
        }
        ++$i;
    }
    return( $raOut );
}

function SEEDStd_MkDirForFile( $fname, $fmode = 0755 )
/*****************************************************
    For the given absolute filename, create its directory if necessary
 */
{
    $ok = true;

    if( ($i = strrpos( $fname, '/' ) ) !== false ) {
        // found a directory in the filename
        $dir = substr( $fname, 0, $i );
        if( !is_dir($dir) ) {
            $ok = mkdir( $dir, $fmode, true );
        }
    }
    return( $ok );
}

?>
