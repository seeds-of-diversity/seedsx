<?

    function my_urlencode( $f )
    {
    // do spaces break urls in firefox and IE?
        return( str_replace(" ","%20",$f));
    }


function BXStd_HTTPRedirect( $url )
/**********************************
    N.B. some clients require this to be an absolute URL with http:// prefix

    This can be done with: "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF'])."/".$relative_url
 */
{
    if( headers_sent() ) {
        echo '<BR><BR>Click <a href="' . $url . '">here</a> to continue.';
    } else {
        header( "Location: " . $url );
    }
}


function BXStd_StrNoBreak( $s )
/******************************
 */
{
    return( str_replace( " ", "&nbsp;", $s ) );
}


class BXStd_SafeGPCStr
/*********************
    Use this when both forms are needed.
    Use one of the simpler functions when only one form is needed.
 */
{
    var $_sPlain;
    var $_sDB;

    function BXStd_SafeGPCStr( $parm = "" )
    {
        if( !empty($parm) ) {
            $s = BXStd_SafeGPCGetStr( $parm );
            $this->_sPlain = $s['plain'];
            $this->_sDB    = $s['db'];
        } else {
            $this->Clear();
        }
    }

    function Clear()   { $this->_sPlain = $this->_sDB = ""; }
    function IsEmpty() { return( empty($this->_sPlain) ); }
    function Plain()   { return( $this->_sPlain ); }
    function DB()      { return( $this->_sDB ); }
}


function BXStd_SafeGPCGetStr( $parm )
/************************************
 */
{
    $s = @$_REQUEST[$parm];
    if( get_magic_quotes_gpc() ) {
        $ret['plain'] = stripslashes($s);
        $ret['db'] = $s;
    } else {
        $ret['plain'] = $s;
        $ret['db'] = addslashes($s);
    }
    return( $ret );
}


function BXStd_SafeGPCGetStrPlain( $parm )
/*****************************************
    Use this only if the parm is just for display.  If it is ever used in a db query, use *GetStr['db']
 */
{
    $s = @$_REQUEST[$parm];
    return( get_magic_quotes_gpc() ? stripslashes($s) : $s );
}


function BXStd_SafeGPCGetInt( $parm )
/************************************
 */
{
    return( intval( @$_REQUEST[$parm] ) );
}


function BXStd_SafeGPCSetStr( &$s, $val )
/****************************************
 */
{
    $s['plain'] = $val;
    $s['db'] = addslashes($val);
}


function BXStd_MagicAddSlashes( $s )
/***********************************
    Escape quotes in the given string if magic quotes are turned off
 */
{
    return( get_magic_quotes_gpc() ? $s : addslashes($s) );
}

function BXStd_MagicStripSlashes( $s )
/*************************************
    Unescape quotes in the given string if magic quotes are turned on
 */
{
    return( get_magic_quotes_gpc() ? stripslashes($s) : $s );
}


function BXStd_Log( $filename, $s )
/**********************************
 */
{
    if( $fp = fopen( SITEROOT."log/".$filename, "a" ) ) {
        fputs( $fp, $s."\n" );
        fclose( $fp );
    }
}

function BXStd_EmailAddress( $s1, $s2, $label = "" )
/***************************************************
 */
{
    $s = "<SCRIPT language='javascript'>var a=\"$s1\";var b=\"$s2\";";
    if( empty($label) ) {
        $s .= "var l=a+\"@\"+b;";
    } else {
        $s .= "var l=\"$label\";";
    }
    $s .= "document.write(\"<A HREF='mailto:\"+a+\"@\"+b+\"'>\"+l+\"</A>\");</SCRIPT>";
    return( $s );
}

?>
