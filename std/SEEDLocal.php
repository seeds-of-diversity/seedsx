<?php

/* SEEDLocal
 *
 * Copyright 2006-2017 Seeds of Diversity Canada
 *
 * Localize strings to facilitate multi-language applications.
 *
 * Strings are stored as array( array( 'ns'=>ns1, 'strs'=> array( key => array( lang1 => "String in lang1", lang2 => "String in lang2", ... ) ... )
 *                              array( 'ns'=>ns2, 'strs'=> array( key => array(...)
 * where ns1, ns2, etc do not have to be unique.
 *
 * Any number of string sets can be added. They are searched in reverse order so later keys override earlier keys.
 *
 * e.g. array( "seed"             => array( "EN" => "seed",    "FR" => "semence" ),
 * e.g. array( "English"          => array( "EN" => "English", "FR" => "Anglais" ),
 *             "native_lang_name" => array( "EN" => "English", "FR" => "Francais" ) )
 */

include_once(SEEDCORE."SEEDTag.php");    // SEEDTagParser


class SEEDLocal {
    const CODE_FOUND = 1;
    const CODE_NOT_FOUND = 2;
    const CODE_BAD_HOST = 3;

    private $lang;
    private $nsDefault = "";
    private $raStrSets; // array( array( 'ns'=ns1, 'strs'=array( key => array( lang => "String", ...
                        //        array( 'ns'=ns2, 'strs'=array( key ...
                        // where ns1, ns2, etc do not have to be unique
    protected $raParms = array();
    private   $bDebug = false;

    protected $oTagParser;    // a SEEDLocal_TagParser for processing [[..]] tags

    function __construct( $strs, $lang, $nsDefault = "", $raParms = array() )
    {
        $this->raStrSets = array();
        $this->lang = $lang;
        $this->nsDefault = $nsDefault;
        $this->raParms = $raParms;
        if( is_array($strs) && count($strs) ) $this->AddStrs( $strs, $nsDefault );

        $this->bDebug = (STD_isLocal || @$this->raParms['Testing']);

        $this->oTagParser = new SEEDLocal_TagParser( $this );
    }

    function AddStrs( $strs, $ns = "" )  { $this->raStrSets[] = array('ns'=>$ns, 'strs'=>$strs ); }
    function AddStrsCopy( $strs )  { $this->AddStrs($strs); }  // deprecate

    function GetLang()          { return( $this->lang ); }

    function S( $key, $raSubst = array(), $ns = NULL )
    /*************************************************
        Retrieve the string that corresponds to the key.
        Substitute elements of the given array into %1%, %2%, %3%, etc
     */
    {
        $ret = "";

        if( $ns === NULL ) { $ns = $this->nsDefault; }

        $lookup = $this->_SLookup( $ns, $key );   // Derived classes override this method to retrieve string sets from various places

        switch( $lookup['code'] ) {
            case SEEDLocal::CODE_FOUND:
                $ret = @$lookup[$this->lang];
                if( !$ret ) {
                    /* The string set was found, but it doesn't contain the language we need.
                     * In dev or testing mode, show an error message. In prod mode, show the other language.
                     */
                    if( $this->bDebug ) {
                        $ret = "<FONT color='red'>__TRANSLATE_${key}_</FONT>";
                    } else {
                        $ret = @$lookup[ $this->lang == 'EN' ? 'FR' : 'EN' ];
                    }
                }
                for( $i = 0; $i < count($raSubst); ++$i ) {
                    $ret = str_replace( "%".($i+1)."%", $raSubst[$i], $ret );
                }
                break;
            case SEEDLocal::CODE_NOT_FOUND:
                $ret = $this->bDebug ? "<FONT color='red'>__NOT_FOUND_{$key}_</FONT>" : "";
                break;
            case SEEDLocal::CODE_BAD_HOST:
                $ret = $this->bDebug ? "<FONT color='red'>__HOST_{$key}_</FONT>" : "";
                break;
        }
        return( $ret );
    }

    function SExpand( $sTemplate, $raVars = array() )    // deprecate, use S2 instead
    /************************************************
        String contains tags of the form [[ns:key | subst1 | subst2]]
        ns: and substN are optional

        sTemplate can contain tags of the forms:
            [[code]]
            [[code whose text contains %1% and %2% | subst to 1 | subst to 2]]
            [[code whose text contains "[[code2]]" ]]   -- recursively expand [[code2]] with same raVars
            [[Var:X]]                                   -- subst with $raVars['X']
            [[If:X | str1 | str2]]                      -- if($raVars['X']) process str1 else process str2

     */
    {
        for(;;) {
            $s1 = strpos( $sTemplate, "[[" );
            $s2 = strpos( $sTemplate, "]]" );
            if( $s1 === false || $s2 === false )  break;

            $tag = substr( $sTemplate, $s1 + 2, $s2 - $s1 - 2 );
            $raTag = explode( '|', $tag );
            $first = array_shift( $raTag );  // shifts the items down so $raTag contains subst values, returns the first item
            if( empty($first) ) break;

            $ra1 = explode( ':', $first );
            if( count($ra1) == 1 ) {
                $ns = $this->nsDefault;
                $k = trim($ra1[0]);
            } else {
                $ns = trim($ra1[0]);
                $k = trim($ra1[1]);
            }

            $sTemplate = substr( $sTemplate, 0, $s1 )
                        .$this->S( $k, $raTag, $ns )
                        .substr( $sTemplate, $s2 + 2 );
        }
        return( $sTemplate );
    }

    function S2( $sTemplate, $raVars = array() )
    {
        $this->oTagParser->SetVars( $raVars );
        return( $this->oTagParser->ProcessTags( $sTemplate ) );
    }

    protected function _SLookup( $ns, $key )
    {
        /* Changed this function to return EN and FR explicitly, because it helps with caching and diagnosing translation deficiencies,
         * but at the expense of language-set independence. So this is an English-French translation system because of the implementation
         * of this method.
         */
        $lookup = array();
        $lookup['code'] = SeedLocal::CODE_NOT_FOUND;
        $lookup['EN'] = "";
        $lookup['FR'] = "";

        /* Search from the last str set to the first, allowing later additions to override.
         */
        for( $i = count($this->raStrSets) - 1; $i >= 0; --$i ) {
            if( @$this->raStrSets[$i]['ns'] != $ns ) continue;

            $str = @$this->raStrSets[$i]['strs'][$key];
            if( is_array($str) ) {
                $lookup['EN'] = @$str['EN'];
                $lookup['FR'] = @$str['FR'];
                $lookup['code'] = SeedLocal::CODE_FOUND;
                break;
            }
        }
        return( $lookup );
    }

    function Dollar( $d )
    {
        return( SEEDCore_Dollar( $d, $this->GetLang() ) );
    }


    function ResolveTag( $raTag, SEEDTagParser $oTagDummy, $raParms )
    /****************************************************************
        Call here from SEEDTagParser::HandleTag to resolve tags having to do with SEEDLocal codes

        bRequireLocalPrefix: [[FR_De:]] etc have to be [[LocalFR_De:]]
                             Use this in template contexts where multiple resolvers could compete.
                             Most importantly, [[foo]] is not a synonym for [[Local:foo] ; you have to use the namespace
     */
    {
        $s = "";
        $bHandled = true;

        $bRequirePrefix = SEEDStd_ArraySmartVal( $raParms, 'bRequireLocalPrefix', array(false,true) );

        if( $bRequirePrefix && substr(strtolower($raTag['tag']), 0, 5) != 'local' ) {
            $bHandled = false;
            goto done;
        }

        $t = $raTag['target'];

        switch( $raTag['tag'] ) {
            case 'LocalFR_de':
            case 'FR_de':
                // if target starts with a vowel, prepend "d'" else "de "
                if( $t ) {
                    $s = in_array( strtolower(substr($t,0,1)), array('a','e','i','o','u') )
                           ? "d'$t" : "de $t";
                }
                break;

            case 'LocalFR_De':
            case 'FR_De':
                // if target starts with a vowel, prepend "D'" else "De "
                if( $t ) {
                    $s = in_array( strtolower(substr($t,0,1)), array('a','e','i','o','u') )
                           ? "D'$t" : "De $t";
                }
                break;

// deprecate for a basic tag [[plural_s:target | n]]
            case 'LocalPlural_s':
            case 'Plural_s':
                // if p1 == 0 or p1 > 1 append an 's' to target.   e.g. 0 results, 1 result, 2 results, ...
                $s = $t.(intval($raTag['raParms'][1]) == 1 ? "" : "s");
                break;

// deprecate for a basic tag
            case 'LocalPlural_es':
            case 'Plural_es':
                // if p1 == 0 or p1 > 1 append an 'es' to target.  e.g. 0 matches, 1 match, 2 matches, ...
                $s = $t.(intval($raTag['raParms'][1]) == 1 ? "" : "es");
                break;

// deprecate for a basic tag
            case 'LocalPlural_y':
            case 'Plural_y':
                // if p1 == 1 append a 'y' to target, else 'ies'.   e.g. 0 companies, 1 company, 2 companies, ...
                $s = $t.(intval($raTag['raParms'][1]) == 1 ? "y" : "ies");
                break;

            case 'LocalLang':
            case 'Lang':
                $s = $this->GetLang() == 'EN' ? @$raTag['raParms'][0] : @$raTag['raParms'][1];
                break;

            case 'Local':
            case '': // no tag: means the target is a S-code
                $s = $this->S($t);
                break;

            default:
                $bHandled = false;
                break;
        }

        done:
        return( array($bHandled,$s) );
    }
}

class SEEDLocalDB extends SEEDLocal
{
    protected $kfdb;

    function __construct( KeyFrameDB $kfdb, $lang, $nsDefault = "", $raParms = array() )
    {
        $this->kfdb = $kfdb;
        $dummyStrs = array(); // SEEDLocal needs this, but we have no strings in an array
        parent::__construct( $dummyStrs, $lang, $nsDefault, $raParms );
    //    if( !empty($nsDefault) )  $this->AddStrsDB( $nsDefault );
    }

    protected function _SLookup( $ns, $key )
    /***************************************
        You don't call this to get strings.  You call S(), which calls this.
     */
    {
        $lookup = parent::_SLookup( $ns, $key );

        if( $lookup['code'] != SEEDLocal::CODE_FOUND && $ns ) {
            $ra = $this->kfdb->QueryRA( "SELECT * FROM SEEDLocal WHERE _status='0' AND ns='".addslashes($ns)."' AND k='".addslashes($key)."'" );
            if( @$ra['_key'] ) {
                $lookup['code'] = SEEDLocal::CODE_FOUND;
                $lookup['EN'] = trim($ra['en']);
                $lookup['FR'] = trim($ra['fr']);
                $lookup['content_type'] = $ra['content_type'];
            }
        }
        return( $lookup );
    }

    function AddStrsDB( $ns )
    {
        $raStrs = array();
        if( ($dbc = $this->kfdb->CursorOpen("SELECT * FROM SEEDLocal WHERE _status=0 AND ns='".addslashes($ns)."'") )) {
            while( $ra = $this->kfdb->CursorFetch($dbc) ) {
                $raStrs[$ra['k']] = array( 'EN' => $ra['en'], 'FR' => $ra['fr'] );
            }
        }
        $this->AddStrs( $raStrs, $ns );
    }

    public function SLookupDB( $ns, $key )
    /*************************************
        Special accessor to a SEEDLocal DB row, e.g. used by translation servers.
     */
    {
        return( $this->_SLookup( $ns, $key ) );
    }
}

class SEEDLocalDBServer extends SEEDLocalDB
{
    /* If this machine is a production server, get the localized strings from the database.
     * If this machine is a development server, call the production server to get the strings.
     */
    private $sServer;

    function __construct( KeyFrameDB $kfdb, $lang, $sServer = "", $nsDefault = "", $raParms = array() )
    {
        $this->sServer = $sServer;
        $dummyStrs = array(); // SEEDLocal needs this, but we have no strings in an array
        parent::__construct( $kfdb, $lang, $nsDefault, $raParms );
    }

    protected function _SLookup( $ns, $key )
    /***************************************
        You don't call this to get strings.  You call S(), which calls this.
     */
    {
        // production server: always reading from local database
        // development server: try the local database, then call the production server
        $lookup = parent::_SLookup( $ns, $key );

        if( STD_isLocal && $lookup['code'] != SEEDLocal::CODE_FOUND ) {
            $url = "http://{$this->sServer}/app/traductions.php?mode=REST&ns=".urlencode($ns)."&k=".urlencode($key);

// this was returning 404, no idea why, all looked good
//            list( $ok, $sResponseHeader, $sResponseContent )
//                = SEEDStd_HttpRequest( $this->sServer, '/int/traductions.php',
//                                       array( 'mode'=>'REST', 'ns'=>$ns, 'k'=>$key, 'lang'=>$this->GetLang() ) );
//            $s = trim($sResponseHeader);

            $s = file_get_contents( $url );  // default context is GET and HTTP/1.0, but other context can be set here

            if( substr( $s, 0, 10 ) == "<SEEDLocal" ) {
                if( strpos( $s, '<SEEDLocal:error' ) === false ) {
                    $raEN = SEEDStd_BookEnds( $s, "<SEEDLocal:en>", "</SEEDLocal:en>" );
                    $raFR = SEEDStd_BookEnds( $s, "<SEEDLocal:fr>", "</SEEDLocal:fr>" );
                    $raCT = SEEDStd_BookEnds( $s, "<SEEDLocal:ct>", "</SEEDLocal:ct>" );

                    $lookup['code'] = SEEDLocal::CODE_FOUND;
                    $lookup['EN'] = @$raEN[0];
                    $lookup['FR'] = @$raFR[0];
                    $lookup['content_type'] = @$raCT[0];

// this only happens on dev installations, kind of nice to see when it happens
echo "<P>Downloaded translation for: $ns:$key</P>";
//var_dump($lookup);

                    // cache the string in the local development database
                    $this->kfdb->Execute( "INSERT INTO SEEDLocal (ns,k,en,fr,content_type) "
                                         ."VALUES ('".addslashes($ns)."','".addslashes($key)."',"
                                         ."'".addslashes($lookup['EN'])."',"
                                         ."'".addslashes($lookup['FR'])."',"
                                         ."'".addslashes($lookup['content_type'])."')" );
                } else {
                    $lookup['code'] = SEEDLocal::CODE_NOT_FOUND;  // or this could relay the error
                }
            } else {
                $lookup['code'] = SEEDLocal::CODE_BAD_HOST;
            }
        }

        return( $lookup );
    }
}


class SEEDLocal_TagParser extends SEEDTagParser
{
    private $oL;

    function __construct( $oL )
    {
        $this->oL = $oL;
        parent::__construct();
    }

    function HandleTag( $raTag )
    {
        $s = "";
        //var_dump( $raTag );

//TODO: this should probably just call SEEDLocal::ResolveTag
        switch( $raTag['tag'] ) {
            case 'FR_de':
                // if target starts with a vowel, prepend "d'" else "de "
                if( ($t = @$raTag['target']) ) {
                    $s = in_array( strtolower(substr($t,0,1)), array('a','e','i','o','u') )
                           ? ("d'".$t) : ("de ".$t);
                }
                return( $s );

            case 'FR_De':
                // if target starts with a vowel, prepend "D'" else "De "
                if( ($t = @$raTag['target']) ) {
                    $s = in_array( strtolower(substr($t,0,1)), array('a','e','i','o','u') )
                           ? ("D'".$t) : ("De ".$t);
                }
                return( $s );

            case 'Plural_s':
                // if p1 == 0 or p1 > 1 append an 's' to target.   e.g. 0 results, 1 result, 2 results, ...
                return( $raTag['target'].(intval($raTag['raParms'][1]) == 1 ? "" : "s") );

            case 'Plural_es':
                // if p1 == 0 or p1 > 1 append an 'es' to target.  e.g. 0 matches, 1 match, 2 matches, ...
                return( $raTag['target'].(intval($raTag['raParms'][1]) == 1 ? "" : "es") );

            case 'Plural_y':
                // if p1 == 1 append a 'y' to target, else 'ies'.   e.g. 0 companies, 1 company, 2 companies, ...
                return( $raTag['target'].(intval($raTag['raParms'][1]) == 1 ? "y" : "ies") );

            case '':
                // no tag: means the target is a S-code
                return( $this->ProcessTags( $this->oL->S($raTag['target']) ) );
            default:
                return( parent::HandleTag( $raTag ) );
        }

    }
}


define("SEEDLOCAL_DB_TABLE_SEEDLOCAL",
"
CREATE TABLE IF NOT EXISTS SEEDLocal (

        _key        INTEGER NOT NULL PRIMARY KEY AUTO_INCREMENT,
        _created    DATETIME,
        _created_by INTEGER,
        _updated    DATETIME,
        _updated_by INTEGER,
        _status     INTEGER DEFAULT 0,

    ns VARCHAR(200) NOT NULL DEFAULT '',   # namespace for this translation
    k  VARCHAR(200) NOT NULL,              # key for this translation
    en TEXT NOT NULL,
    fr TEXT NOT NULL,
    content_type ENUM ('PLAIN','HTML') NOT NULL DEFAULT 'PLAIN',
    comment TEXT,

    INDEX (ns(20)),
    INDEX (ns(20),k(20))
);
"
);


function SEEDLocal_Setup( $oSetup, &$sReport, $bCreate = false )
/**************************************************************
    Test whether the tables exist.
    bCreate: create the tables and insert initial data if they don't exist.

    Return true if exists (or create is successful if bCreate); return a text report in sReport

    N.B. $oSetup is a SEEDSetup.  This file doesn't include SEEDSetup.php because the setup code is very rarely used.
         Instead, the code that calls this function knows about SEEDSetup.
 */
{
    return( $oSetup->SetupTable( "SEEDLocal", SEEDLOCAL_DB_TABLE_SEEDLOCAL, $bCreate, $sReport ) );
}


?>
