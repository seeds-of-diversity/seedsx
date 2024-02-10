<?php

function DocRep_Key2Hash( $k, $seed )
/************************************
    Prevent people from exploring the repository. Make "user" keys that are non-sequential hashes of doc keys

    $k    = doc key
    $seed = any string

    Returns a hash string that no one can use to guess other document keys
    The string is an md5 suffixed by the doc key.
 */
{
    $k1 = sprintf( "%08d", $k );
    return( md5($k1).$k1 );
}

function DocRep_Key2HashTrack( $k, $seed, $trackCode )
/*****************************************************
    Prevent people from exploring the repository. Make "user" keys that are non-sequential hashes of doc keys

    $k         = doc key
    $seed      = any string
    $trackCode = a code embedded in the hash to identify something (like a user, an email, a session, etc)

    Returns a hash string that no one can use to guess other document keys
    The string is an md5 suffixed by the doc key.
 */
{
    $trackCode = intval($trackCode);

    $t1 = $trackCode ? "-a$trackCode" : "";
    $k1 = sprintf( "%08d", $k );

    return( substr(md5($k1.$seed),0,8).$k1.$t1 );
}


function DocRep_Hash2Key( $hash, $seed )
/***************************************
    $hash = string from DocRep_Key2Hash
    $seed = same string that was used in DocRep_Key2Hash

    returns the original document key, or 0 if the hash is not valid
 */
{
    $h1 = substr( $hash, strlen($hash)-8 );     // the original key
    $h2 = substr( $hash, 0, strlen($hash)-8 );  // the md5

    return( md5($h1) == $h2 ? intval($h1) : 0 );
}

function DocRep_HashTrack2Key( $hash, $seed )
/********************************************
    $hash = string from DocRep_Key2Hash
    $seed = same string that was used in DocRep_Key2Hash

    returns the original document key, or 0 if the hash is not valid, and the tracking code or 0 if not present
 */
{
    $k = 0;
    $t = 0;

    if( !$hash ) goto done;

    $ra = explode( '-a', $hash, 2 );
    if( strlen($ra[0]) != 16 ) goto done;

    // $k is the intval of the last 8 chars if the first 8 chars matches the first portion of the md5
    $k1 = substr( $ra[0], -8 );       // the original key
    $md5_8 = substr( $ra[0], 0, 8 );  // first 8 chars of the md5
    $k = substr(md5($k1.$seed),0,8) == $md5_8 ? intval($k1) : 0;

    // $t is the intval after -a
    $t = count($ra) == 2 ? intval($ra[1]) : 0;

    done:
    return( array( $k, $t ) );
}



function DR_link( $href, $title, $desc, $parms = array() )
/*********************************************************
    Format a link to a reference

    parms:
        author
        date
        icon
        target
 */
{
    echo "<P>";
    $icon = "";
    if( !isset( $parms['icon'] ) ) {
        /* Find the appropriate icon by file extension.
         */
        $ext = strtolower(substr(strrchr( $href, "." ),1));
        $fnameIcon = SITEIMG_STDIMG."icon-$ext-l.gif";
        if( file_exists( $fnameIcon ) ) {
            $icon = $fnameIcon;
        }
    } else {
        $icon = @$parms['icon'];    // override with another file, or "" for no icon
    }
    if( !empty($icon) ) {
        echo "<IMG src='$icon' border=0> ";
    }


    echo "<A HREF='$href'";
    if( !empty($parms['target']) ) {
        echo " target='{$parms['target']}'";
    }
    echo "><B>$title</B></A>";
    if( !empty($desc) || !empty($parms['author']) || !empty($parms['date']) ) {
        echo "<BLOCKQUOTE style='font-size:80%'>";
        $bBR = false;
        if( !empty($parms['author']) ) {
            echo $parms['author'];
            $bComma = true;
            $bBR = true;
        }
        if( !empty($parms['date']) ) {
            if( $bComma )  echo ", ";
            echo $parms['date'];
            $bComma = true;
            $bBR = true;
        }
        if( !empty($parms['pub']) ) {
            if( $bComma )  echo ", ";
            echo $parms['pub'];
            $bComma = true;
            $bBR = true;
        }
        if( $bBR )  echo "<BR><BR>";

        echo $desc;
        echo "</BLOCKQUOTE>";
    }
    echo "</P>";
}



class DocRepTextTypes
{
    static public $raTextTypes = array("TEXTTYPE_PLAIN", "TEXTTYPE_PLAIN_SOD",
                                       "TEXTTYPE_HTML",  "TEXTTYPE_HTML_SOD",
                                       "TEXTTYPE_WIKI",  "TEXTTYPE_WIKILINK"         // deprecate
    );
    static public $raFullNames = array("TEXTTYPE_PLAIN"     =>"Plain Text",
                                       "TEXTTYPE_PLAIN_SOD" =>"Plain (SoD markup)",
                                       "TEXTTYPE_HTML"      =>"HTML",
                                       "TEXTTYPE_HTML_SOD"  =>"HTML (SoD markup)",
                                       "TEXTTYPE_WIKI"=>"Wiki", "TEXTTYPE_WIKILINK"=>"Wiki (Links Only)"    // deprecate
    );

    function __construct()  {}

    static function NormalizeTextType( $tt, $ttDefault = "TEXTTYPE_PLAIN" )
    /**********************************************************************
        Return $tt if valid, $ttDefault if not
     */
    {
        return( in_array($tt, self::$raTextTypes) ? $tt : $ttDefault );
    }

    static function GetFromTagStr( $s )
    /**********************************
        Parse the texttype out of the given tag str (string with a texttype surrounded by spaces
     */
    {
        $eTextType = "";
        if( ($s = strstr($s, ' TEXTTYPE_')) !== false ) {       // $s is the tagstr starting at ' TEXTTYPE_'...
            if( ($s = strtok( $s, " " )) !== false ) {          // $s is the texttype after ' TEXTTYPE_'
                $eTextType = $s;
            }
        }
        return( $eTextType );
    }

    static function GetFullName( $tt )
    /*********************************
     */
    {
        return( isset($this->raFullNames[$tt]) ? $this->raFullNames[$tt] : "Unknown Type" );
    }

    static function IsHTML( $tt )    { return( in_array($tt, array("TEXTTYPE_HTML","TEXTTYPE_HTML_SOD")) ); }
    static function IsPlain( $tt )   { return( in_array($tt, array("TEXTTYPE_PLAIN","TEXTTYPE_PLAIN_SOD")) ); }

    static function TagStrContainsWiki( $s )
    /***************************************
        Return true if $s contains a WIKI texttype surrounded by spaces
     */
    {
        return( strstr($s, ' TEXTTYPE_WIKI ')     !== false ||
                strstr($s, ' TEXTTYPE_WIKILINK ') !== false );
    }
}



include( STDINC."os/simplediff.php");

class DocRepDiff
{
    function __construct() {}

    static function DiffVersions( DocRepDB $oDocRepDB, $kDataOld, $kDataNew )
    /************************************************************************
        Show the diff between two versions of the same doc
     */
    {
        $s = "";

        $sOld = $oDocRepDB->kfdb->Query1("SELECT data_text FROM docrep_docdata WHERE _key='$kDataOld'");
        $sNew = $oDocRepDB->kfdb->Query1("SELECT data_text FROM docrep_docdata WHERE _key='$kDataNew'");

        $s .= "<style>del {color:red} ins {color:green}</style>";

        if( strlen($sOld) > 2000 || strlen($sNew) > 2000 ) {
            // chop the strings into chunks so diff doesn't run out of memory
            $old = self::chop( $sOld );
            $new = self::chop( $sNew );
        } else {
            // explode the strings into arrays of single characters
            $old = array();
            $new = array();
            for( $i = 0; $i < strlen($sOld); ++$i )  $old[] = $sOld[$i];
            for( $i = 0; $i < strlen($sNew); ++$i )  $new[] = $sNew[$i];
        }
        //var_dump($old,$new);

        $raDiff = diff( $old, $new );

        $s1 = "";
        foreach( $raDiff as $v ) {
            if( is_array( $v ) ) {
                $s1 .= (!empty($v['d']) ? ("<del>".SEEDCore_HSC(implode('',$v['d']))."</del> ") : "")
                      .(!empty($v['i']) ? ("<ins>".SEEDCore_HSC(implode('',$v['i']))."</ins> ") : "");
            } else {
                $s1 .= SEEDCore_HSC($v);
            }
        }
        $s .= "<pre>".wordwrap( $s1, 120, "\n" )."</pre>";

        return( $s );
    }

    static private function chop( $str )
    /***********************************
        Chop the string into chunks of 20 or more characters so diff() doesn't run out of memory
     */
    {
        $ra = array();
        $off = $start = 0;  // $start is the starting offset of each chunk, $off is the offset after the most recent " >"
        for(;;) {
            $i1 = strpos( $str, " ", $off );
            $i2 = strpos( $str, ">", $off );
            if( $i1 === false && $i2 === false ) {
                // reached the end. dump out and quit
                $ra[] = substr( $str, $start );
                break;
            } else if( $i1 === false ) {
                $i = $i2;
            } else if( $i2 === false ) {
                $i = $i1;
            } else {
                $i = min( $i1, $i2 );
            }
            // kluge: chop into arbitrary chunks >= 20 chars so diff() doesn't run out of memory
            if( substr($str,$i,1)==" " && ($i - $start < 20) ) {
                $off = $i + 1;
                continue;
            }

            $ra[] = substr( $str, $start, $i - $start + 1 );
            $off = $start = $i + 1;
        }

        return( $ra );
    }
}

?>
